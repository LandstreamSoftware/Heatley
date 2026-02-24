<?php
declare(strict_types=1);

include_once '../config.php';
include_once '../main.php';
require '../vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xls;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;

$con = mysqli_connect(db_host, db_user, db_pass, db_name);

$stmt = $con->prepare('SELECT companyID FROM accounts_view WHERE id = ?');
$stmt->bind_param('i', $_SESSION['account_id']);
$stmt->execute();
$stmt->bind_result($companyid);
$stmt->fetch();
$stmt->close();

$myNewToken = check_xero_token_expiry($con, $companyid);

// 2) Fetch rows from Xero (replace this with your actual Xero call)
// Each row should be an associative array with keys:
// date_paid (Y-m-d or DateTime), paid_to, ref, description, amount

if ($_SERVER["REQUEST_METHOD"] == "POST") {
  if (!empty($_POST["m"])) {
    $m = (int)$_POST["m"];
  }
  if (!empty($_POST["y"])) {
    $y = (int)$_POST["y"];
  }

    if (isset($myNewToken)) {
    $accesstoken = $myNewToken;
    }

    $config = XeroAPI\XeroPHP\Configuration::getDefaultConfiguration()->setAccessToken($accesstoken);

    // Initialize Identity API
    $identityApi = new XeroAPI\XeroPHP\Api\IdentityApi(
        new GuzzleHttp\Client(),
        $config // Your standard configuration with the access token
    );

    // Get all connections
    $connections = $identityApi->getConnections();

    foreach ($connections as $connection) {
        $xeroTenantId = $connection->getTenantId();
        $xeroTenantName = $connection->getTenantName();
    }

    $apiInstance = new XeroAPI\XeroPHP\Api\AccountingApi(
        new GuzzleHttp\Client(),
        $config
    );

    //use XeroAPI\XeroPHP\Models\Accounting\Invoice;

    // The following values must match the ones in xero-bills-paid.php. They coul dbe included in the Export button's form to be sure.
    $ifModifiedSince = date('Y-m-d', mktime(0,0,0,$m,1,$y));
    $today = date('d M Y');
    $where = 'Type="ACCPAY"';
    $order = 'FullyPaidOnDate DESC';
    $iDs = null;
    $invoiceNumbers = null;
    $contactIDs = null;
    $statuses = array("PAID, AUTHORISED");
    $page = 1;
    $includeArchived = null;
    $createdByMyApp = null;
    $unitdp = null;
    $summaryOnly = false;
    $pageSize = 100;
    $searchTerm = null;

    try {
        $result = $apiInstance->getInvoices($xeroTenantId, $ifModifiedSince, $where, $order, $iDs, $invoiceNumbers, $contactIDs, $statuses, $page, $includeArchived, $createdByMyApp, $unitdp, $summaryOnly, $pageSize, $searchTerm);

        $invoices = $result->getinvoices();

        $searchDate = date('Y-m-d', mktime(0,0,0,$m,1,$y));

        $i = 0;
        $count = count($invoices);

        while ($i < $count) {
            $xeroDate = $invoices[$i]->getDate();
            preg_match('/\d+/', $xeroDate, $matches);
            $milliseconds = $matches[0];
            $seconds = $milliseconds / 1000;
            $formattedDate = date('d M Y', $seconds);

            $xeroDueDate = $invoices[$i]->getDueDate();
            preg_match('/\d+/', $xeroDueDate, $matchesDD);
            $millisecondsDD = $matchesDD[0];
            $secondsDD = $millisecondsDD / 1000;
            $formattedDueDate = date('d M Y', $secondsDD);

            $xeroDatePaid = $invoices[$i]->getFullyPaidOnDate();
            preg_match('/\d+/', $xeroDatePaid, $matchesPD);
            $millisecondsPD = $matchesPD[0];
            $secondsPD = $millisecondsPD / 1000;
            $formattedDatePaid = date('d M Y', $secondsPD);
            $matchDatePaid = date('Y-m-d', $secondsPD);

            $xeroLastModified = $invoices[$i]->getUpdatedDateUTC();
            if (preg_match('/Date\((\d+)([+-]\d{4})?\)/', $xeroLastModified, $matchesLM)) {
                $millisecondsLM = (int) $matchesLM[1];
                $secondsLM = (int) floor($millisecondsLM / 1000);

                $dt = new DateTime('@' . $secondsLM); // timestamp
                $dt->setTimezone(new DateTimeZone('UTC'));

                $formattedLastModified = $dt->format('d M Y');
            }
            
        //    $SearchDateEnd = date_format($end, "Y-m-d");

            $xeroContact = $invoices[$i]->getContact();

            $lineItems = $invoices[$i]->getLineItems();
            $i++;
        }

        //$rows = fetchXeroPaymentRows(); // <-- implement or call your existing function

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Payments');

        // Headers
        $headers = ['Date Paid', 'Paid To', 'Reference', 'Description', 'Amount'];
        $sheet->fromArray($headers, null, 'A1');

        // Data
        $r = 2;
        //foreach ($rows as $row) {
        foreach ($result as $row) {
            // Normalize date into Excel date serial by writing a PHP DateTime and applying date format
            // If you only have a string, you can keep it as string; Excel will still display it.
            $date = $row['date_paid'] ?? '';
            if ($date instanceof DateTimeInterface) {
                $sheet->setCellValue("A{$r}", \PhpOffice\PhpSpreadsheet\Shared\Date::PHPToExcel($date));
                $sheet->getStyle("A{$r}")->getNumberFormat()->setFormatCode('yyyy-mm-dd');
            } else {
                // Expecting Y-m-d string
                $sheet->setCellValue("A{$r}", (string)$date);
            }

            $sheet->setCellValue("B{$r}", (string)($row['paid_to'] ?? ''));
            $sheet->setCellValue("C{$r}", (string)($row['ref'] ?? ''));
            $sheet->setCellValue("D{$r}", (string)($row['description'] ?? ''));
            $sheet->setCellValue("E{$r}", (float)($row['amount'] ?? 0));

            $r++;
        }

        // Format amount column
        $lastRow = max(2, $r - 1);
        $sheet->getStyle("E2:E{$lastRow}")
            ->getNumberFormat()
            ->setFormatCode(NumberFormat::FORMAT_NUMBER_00);

        // Basic usability niceties
        foreach (range('A', 'E') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }
        $sheet->freezePane('A2');

        // 3) Stream as XLS download
        // IMPORTANT: ensure no output has been sent before these headers.
        $filename = 'xero-payments-' . date('Y-m-d') . '.xls';

        header('Content-Type: application/vnd.ms-excel');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Cache-Control: max-age=0');
        header('Pragma: public');

        $writer = new Xls($spreadsheet);
        $writer->save('php://output');
        exit;

        // ---- Example stub ----
/*        function fetchXeroPaymentRows(): array {
            // Replace with actual Xero API call + mapping.
            return [
                [
                    'date_paid'   => '2026-01-28',
                    'paid_to'     => 'Acme Ltd',
                    'ref'         => 'INV-1001',
                    'description' => 'Office supplies',
                    'amount'      => 123.45,
                ],
            ];
        } */
    } catch (Exception $e) {
    echo 'Exception when calling AccountingApi->getInvoices: ', $e->getMessage(), PHP_EOL;
    }
}
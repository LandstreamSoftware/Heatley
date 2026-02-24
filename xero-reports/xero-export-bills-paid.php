<?php
declare(strict_types=1);

require '../vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xls;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use PhpOffice\PhpSpreadsheet\Style\Alignment;

// 2) Fetch rows from Xero (replace this with your actual Xero call)
// Each row should be an associative array with keys:
// date_paid (Y-m-d or DateTime), paid_to, ref, description, amount

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $m = $_POST["m"];
    $y = $_POST["y"];
    
    $fromDate = $_POST["from"];
    $toDate = $_POST["to"];
    

    $ifModifiedSince = $_POST["ifModifiedSince"];
    $where = 'Type="ACCPAY" && FullyPaidOnDate >= DateTime('.$fromDate.') && FullyPaidOnDate < DateTime('.$toDate.')';
    $order = $_POST["order"];
    $iDs = null;
    $invoiceNumbers = null;
    $contactIDs = null;
    $statuses = array("PAID, AUTHORISED");
    $page = $_POST["page"];
    $includeArchived = null;
    $createdByMyApp = null;
    $unitdp = null;
    $summaryOnly = false;
    $pageSize = $_POST["pageSize"];
    $searchTerm = null;

    $accesstoken = $_POST["token"];

    try {

        //$rows = fetchXeroPaymentRows(); // <-- implement or call your existing function

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

        $result = $apiInstance->getInvoices($xeroTenantId, $ifModifiedSince, $where, $order, $iDs, $invoiceNumbers, $contactIDs, $statuses, $page, $includeArchived, $createdByMyApp, $unitdp, $summaryOnly, $pageSize, $searchTerm);

        $invoices = $result->getinvoices() ?? [];

         $i = 0;
        $count = count($invoices);



        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Payments');

        // Headers
        $headers = ['Date Paid', 'Paid To', 'Reference', 'Description', 'Amount'];
        $sheet->fromArray($headers, null, 'A1');

        // Data
        $r = 2;

       while ($i < $count) {
            // Invoice date
            $xeroDate = $invoices[$i]->getDate();
            preg_match('/\d+/', $xeroDate, $matches);
            $milliseconds = $matches[0];
            $seconds = $milliseconds / 1000;
            $formattedDate = date('d M Y', $seconds);

            // Due date
            $xeroDueDate = $invoices[$i]->getDueDate();
            preg_match('/\d+/', $xeroDueDate, $matchesDD);
            $millisecondsDD = $matchesDD[0];
            $secondsDD = $millisecondsDD / 1000;
            $formattedDueDate = date('d M Y', $secondsDD);

            // Date fully paid
            $xeroDatePaid = $invoices[$i]->getFullyPaidOnDate();
            preg_match('/\d+/', $xeroDatePaid, $matchesPD);
            $millisecondsPD = $matchesPD[0];
            $secondsPD = $millisecondsPD / 1000;
            $formattedDatePaid = date('d M Y', $secondsPD);
            $matchDatePaid = date('Y-m-d', $secondsPD);

            // Last modified on
            $xeroLastModified = $invoices[$i]->getUpdatedDateUTC();
            if (preg_match('/Date\((\d+)([+-]\d{4})?\)/', $xeroLastModified, $matchesLM)) {
                $millisecondsLM = (int) $matchesLM[1];
                $secondsLM = (int) floor($millisecondsLM / 1000);

                $dt = new DateTime('@' . $secondsLM); // timestamp
                $dt->setTimezone(new DateTimeZone('UTC'));

                $formattedLastModified = $dt->format('d M Y');
            }

            // Invoiced from
            $xeroContact = $invoices[$i]->getContact();

            // The line items
            $lineItems = $invoices[$i]->getLineItems();




            // Normalize date into Excel date serial by writing a PHP DateTime and applying date format
            // If you only have a string, you can keep it as string; Excel will still display it.
    /*        $date = $row['date_paid'] ?? '';
            if ($date instanceof DateTimeInterface) {
                $sheet->setCellValue("A{$r}", \PhpOffice\PhpSpreadsheet\Shared\Date::PHPToExcel($date));
                $sheet->getStyle("A{$r}")->getNumberFormat()->setFormatCode('yyyy-mm-dd');
            } else {
                // Expecting Y-m-d string
                $sheet->setCellValue("A{$r}", (string)$date);
            } */
            $sheet->setCellValue("A{$r}", $formattedDatePaid);
            $sheet->setCellValue("B{$r}", $xeroContact->getName());
            $sheet->setCellValue("C{$r}", $invoices[$i]->getInvoiceNumber());
            if (!$lineItems || count($lineItems) === 0) {
                $sheet->setCellValue("D{$r}", '');
            } else {
                foreach ($lineItems as $li) {
                    $desc = $li->getDescription();
                    $description = htmlspecialchars($desc);
                }
                $sheet->setCellValue("D{$r}", $description);
            }
            $sheet->setCellValue("E{$r}", number_format($invoices[$i]->getAmountPaid(),2));
            $r++;
            $i++;
        }

        // Format amount column
        $lastRow = max(2, $r - 1);
        $sheet->getStyle("E2:E{$lastRow}")
            ->getNumberFormat()
            ->setFormatCode(NumberFormat::FORMAT_NUMBER_00);

        $sheet->getStyle("E1:E{$lastRow}")
            ->getAlignment()
            ->setHorizontal(Alignment::HORIZONTAL_RIGHT);

        $sheet->getStyle("C2:C{$lastRow}")
            ->getAlignment()
            ->setHorizontal(Alignment::HORIZONTAL_LEFT);

        $sheet->getStyle('A1:E1')
            ->getFont()
            ->setBold(true);

        // Basic usability niceties
        foreach (range('A', 'E') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }
        $sheet->freezePane('A2');

        $sheet->setSelectedCell('A2');

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
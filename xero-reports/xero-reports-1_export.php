<?php
declare(strict_types=1);

// Include the main.php file
include '../main.php';
// Check if the user is logged in, if not then redirect to login page
check_loggedin($con);

require '../vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xls;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\PageSetup;

use XeroAPI\XeroPHP\Api\AccountingApi;

// 2) Fetch rows from Xero (replace this with your actual Xero call)
// Each row should be an associative array with keys:
// date_paid (Y-m-d or DateTime), paid_to, ref, description, amount


if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $filename = $_POST["filename"];
    
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

        $config = XeroAPI\XeroPHP\Configuration::getDefaultConfiguration()->setAccessToken($accesstoken);

        // Initialize Identity API
        $identityApi = new XeroAPI\XeroPHP\Api\IdentityApi(
            new GuzzleHttp\Client(),
            $config // Your standard configuration with the access token
        );

        // Get all connections
        $connections = $identityApi->getConnections();

        $accountingApi = new XeroAPI\XeroPHP\Api\AccountingApi(
            new GuzzleHttp\Client(),
            $config
        );


        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Payments');

        // Headers
        $headers = ['Date Paid', 'Invoice Number', 'Paid To', 'Description', 'Currency', 'Amount'];
        $sheet->fromArray($headers, null, 'A1');

        // Data
        $r = 2;


        function xss($v): string {
            return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8');
        }

        // Xero-style date: "/Date(1769644800000+0000)/" -> Y-m-d (UTC)
        function formatXeroDate(?string $xeroDate, string $format = 'd F Y'): string {
            if (!$xeroDate) return '';
            if (preg_match('/\/Date\((\d+)(?:[+-]\d+)?\)\//', $xeroDate, $m)) {
                $ms = (int)$m[1];
                $dt = (new DateTimeImmutable('@' . (int) floor($ms / 1000)))->setTimezone(new DateTimeZone('UTC'));
                return $dt->format($format);
            }
            return $xeroDate; // fallback if it isn't in the expected format
        }

        
        foreach ($connections as $connection) {

            $tenantId   = $connection->getTenantId();
            $tenantName = $connection->getTenantName();

            // Ensure tenant bucket
            if (!isset($groupedData[$tenantId])) {
                $groupedData[$tenantId] = [
                    'Tenants' => [
                        'Name'     => $tenantName,
                        'TenantID' => $tenantId,
                    ],
                ];
            }

            // 1) Fetch active BANK accounts
            $bankAccounts = [];
            $accountsResults = $accountingApi->getAccounts(
                $tenantId,
                null,
                'Status=="ACTIVE" AND Type=="BANK"'
            );

            foreach ($accountsResults->getAccounts() as $account) {
                $accountId = $account->getAccountId();

                $bankAccounts[$accountId] = true;

                // Ensure account bucket WITH Payments container
                if (!isset($groupedData[$tenantId][$accountId])) {
                    $groupedData[$tenantId][$accountId] = [
                        'Accounts' => [
                            'AccountID' => $accountId,
                            'Type'      => $account->getType(),
                            'Name'      => $account->getName(),
                            'Status'    => $account->getStatus(),
                        ],
                        'Payments' => [],   // <-- this is the important part
                    ];
                } elseif (!isset($groupedData[$tenantId][$accountId]['Payments'])) {
                    $groupedData[$tenantId][$accountId]['Payments'] = [];
                }
            }

            // 2) Fetch payments
            $paymentsResults = $accountingApi->getPayments(
                $tenantId,
                null,
                'Date>=DateTime('.$fromDate.') AND Date<=DateTime('.$toDate.')'
            );

            foreach ($paymentsResults->getPayments() as $payment) {

                $paymentInvoiceStub = $payment->getInvoice();
                if (!$paymentInvoiceStub) continue;
                if ($paymentInvoiceStub->getType() !== 'ACCPAY') continue;
                if (!$payment->getAccount()) continue;

                $accountId = $payment->getAccount()->getAccountId();
                if (!isset($bankAccounts[$accountId])) continue;

                // Fetch full invoice(s) for this payment's invoice id
                $invoicesResult = $accountingApi->getInvoices(
                    $tenantId,
                    null,  // ifModifiedSince
                    null,  // where
                    null,  // order
                    $paymentInvoiceStub->getInvoiceID(), // ids
                    null, null, null, null,
                    false, // includeArchived
                    null, null,
                    false  // summaryOnly
                );

                $fullInvoices = $invoicesResult->getInvoices() ?? [];

                $invoicesPayload = [];
                foreach ($fullInvoices as $inv) {

                    $lineItemsPayload = [];
                    $lineItems = $inv->getLineItems() ?? [];
                    foreach ($lineItems as $li) {
                        $lineItemsPayload[] = [
                            'Description' => $li->getDescription(),
                            'Quantity'    => $li->getQuantity(),
                            'UnitAmount'  => $li->getUnitAmount(),
                        ];
                    }

                    $invoicesPayload[] = [
                        'Type'          => $inv->getType(),
                        'InvoiceID'     => $inv->getInvoiceID(),
                        'InvoiceNumber' => $inv->getInvoiceNumber(),
                        'Reference'     => $inv->getReference(),
                        'CurrencyCode'  => $inv->getCurrencyCode(),
                        'Url'           => $inv->getUrl(),
                        'Contact'       => $inv->getContact() ? $inv->getContact()->getName() : '',
                        'LineItems'     => $lineItemsPayload, // <-- multiple line items
                    ];
                }

                // Append Payment UNDER the account's Payments array
                $groupedData[$tenantId][$accountId]['Payments'][] = [
                    'Date'         => $payment->getDate(),
                    'CurrencyRate' => $payment->getCurrencyRate(),
                    'Amount'       => number_format($payment->getAmount(), 2),
                    'Invoices'     => $invoicesPayload, // <-- multiple invoices
                ];
            }
        }



    // Build the spreadsheet data
    foreach ($groupedData as $tenantId => $bucket) {

        // Tenant
        $tenantName = $bucket['Tenants']['Name'] ?? '';
        $sheet->mergeCells("A{$r}:F{$r}");
        $sheet->setCellValue("A{$r}", xss($tenantName));
        $sheet->getRowDimension($r)->setRowHeight(20);
        $sheet->getStyle("A{$r}:F{$r}")->applyFromArray([
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => [
                    'rgb' => 'BEE5EB',
                ],
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_LEFT,
                'vertical'   => Alignment::VERTICAL_CENTER,
            ],
            'font' => [
                'bold' => true,
                'size' => 12,
            ],
        ]);
        
        $r++;

        // Accounts under tenant
        foreach ($bucket as $accountId => $accountNode) {
            if ($accountId === 'Tenants' || !is_array($accountNode)) {
                continue;
            }

            // Account
            $accountName = $accountNode['Accounts']['Name'] ?? '';

            $sheet->mergeCells("A{$r}:F{$r}");
            $sheet->setCellValue("A{$r}", xss($accountName));
            $sheet->getStyle("A{$r}:F{$r}")->applyFromArray([
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => [
                        'rgb' => 'D6D8DB',
                    ],
                ],
                'alignment' => [
                    'horizontal' => Alignment::HORIZONTAL_LEFT,
                ],
//                'font' => [
//                    'bold' => true,
//                    'size' => 14,
//                ],
            ]);

            $r++;

            $payments = $accountNode['Payments'] ?? [];
            foreach ($payments as $p) {

                $paymentDate  = formatXeroDate($p['Date'] ?? '');
                $amount       = $p['Amount'] ?? '';
                $invoices     = $p['Invoices'] ?? [];


                $sheet->setCellValue("A{$r}", xss($paymentDate));

                // Invoices under payment
                foreach ($invoices as $inv) {
                    $invoiceNo    = $inv['InvoiceNumber'] ?? '';
                    $currencyCode = $inv['CurrencyCode'] ?? '';
                    $contact      = $inv['Contact'] ?? '';
                    $lineItems    = $inv['LineItems'] ?? [];


                    $sheet->setCellValue("B{$r}", xss($invoiceNo));
                    $sheet->setCellValue("C{$r}", xss($contact));

                    // Line items under invoice
                    $lineitemtext = '';               
                    foreach ($lineItems as $li) {
                        $desc       = $li['Description'] ?? '';
                        $qty        = $li['Quantity'] ?? '';
                        $unitAmount = $li['UnitAmount'] ?? '';
                        if (count($lineItems) > 1) {
                            $lineitemtext = $lineitemtext . xss($desc) . " (" . $qty . " x $" . $unitAmount . ")\n";
                        } else { 
                            $lineitemtext = xss($desc);
                        }
                        
                    }
                    $lineitemtext = rtrim($lineitemtext, "\r\n");

                    $sheet->setCellValue("D{$r}", xss($lineitemtext));
                }

                $sheet->setCellValue("E{$r}", xss($currencyCode));

                $sheet->setCellValue("F{$r}", xss($amount));
                $r++;
            }
            $r++;
        }
        $r++;
    }




        // Format amount column
        $lastRow = max(2, $r - 1);
        $sheet->getStyle("F2:F{$lastRow}")
            ->getNumberFormat()
            ->setFormatCode(NumberFormat::FORMAT_NUMBER_00);

        $sheet->getStyle("F1:F{$lastRow}")
            ->getAlignment()
            ->setHorizontal(Alignment::HORIZONTAL_RIGHT);

        $sheet->getStyle("B2:C{$lastRow}")
            ->getAlignment()
            ->setHorizontal(Alignment::HORIZONTAL_LEFT);

        $sheet->getStyle("A1:F1")
            ->getFont()
            ->setBold(true);

        $sheet->getStyle("D2:D{$lastRow}")
            ->getAlignment()
            ->setWrapText(true);

        $sheet->getStyle("A2:F{$lastRow}")
            ->getAlignment()
            ->setVertical(Alignment::VERTICAL_CENTER);

        // Basic usability niceties
        foreach (range('A', 'F') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }
        $sheet->freezePane('A2');

        $sheet->setSelectedCell('A2');

        $sheet->getPageSetup()
            ->setPrintArea("A1:F{$r}")
            ->setPaperSize(PageSetup::PAPERSIZE_A4)
            ->setFitToWidth(1)
            ->setFitToHeight(0);


        // 3) Stream as XLS download
        // IMPORTANT: ensure no output has been sent before these headers.

        // Headers for download
        header('Content-Type: application/vnd.ms-excel');
        header("Content-Disposition: attachment; filename=\"$filename\"");
        header('Cache-Control: max-age=0');


        ob_clean();
        flush();

        // Output
        $writer = new Xls($spreadsheet);
        $writer->save('php://output');
        exit;

    } catch (Exception $e) {
        echo 'Exception when calling AccountingApi->getInvoices: ', $e->getMessage(), PHP_EOL;
    }
}
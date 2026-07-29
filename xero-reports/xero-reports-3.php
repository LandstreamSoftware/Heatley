<?php
// Include the main.php file
include '../main.php';
include_once '../encryption_helper.php';
// Check if the user is logged in, if not then redirect to login page
check_loggedin($con);
// Template code below



function h($v) {
    return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8');
}

function fmtDate($dt) {
    if (!$dt) return '';
    // Xero SDK often returns DateTime objects; if it’s a string this still works if parseable.
    try {
        if ($dt instanceof DateTimeInterface) return $dt->format('Y-m-d');
        return (new DateTime($dt))->format('Y-m-d');
    } catch (Exception $e) {
        return (string)$dt;
    }
}

$accountid = $_SESSION['account_id'] ?? null;
if (!is_int($accountid) && !ctype_digit($accountid)) {
    exit('Invalid account ID');
}
$accountid = (int)$accountid;

$stmt = $con->prepare("SELECT * FROM accesscontrol WHERE accountID = ?");
$stmt->bind_param("i", $accountid); // "i" = integer
$stmt->execute();
$resultAccess = $stmt->get_result();

$accessto = -1;

if ($resultAccess->num_rows > 0) {
    while($rowAccess = $resultAccess->fetch_assoc()) {
       $accessto .= "," . $rowAccess["companyID"]; 
    }
}

$searchperiod = 0;

require '../vendor/autoload.php';

$nowD = date('d');
$nowM = date('m');
$nowY = date('Y');
$tenantid = '0';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
  if (!empty($_POST["fromDate"]) && !empty($_POST["toDate"])) {
    $from = $_POST["fromDate"];
    $to = $_POST["toDate"];
    $fromDateObject = new DateTime($_POST["fromDate"]);
    $fromDate = $fromDateObject->format('Y,m,d');
    $fromDisplay = date_format($fromDateObject, 'd F Y');
    $toDateObject = new DateTime($_POST["toDate"]);
    $toDate = $toDateObject->format('Y,m,d');
    $toDisplay = date_format($toDateObject, 'd F Y');
  } elseif (!empty($_POST["searchperiod"])) {
    $searchperiod = $_POST["searchperiod"];
  } else { // Manually set the from and to dates based on the search period
    if ($searchperiod == 12) { // This financial year
        if ($nowM < 4) {
            $y = $nowY - 1;
        } else {
            $y = $nowY;
        }
        $endDate = $nowY."-".$nowM."-".$nowD; //Start with today
        $end = date_create($endDate);
        //date_add($end,date_interval_create_from_date_string("1 month"));
        $fromDate = sprintf('%04d,%02d,%02d', $y, 4, 1);
        $toDate = date_format($end, "Y,m,d");
    } elseif ($searchperiod == 2) { // Two months ago
        if ($nowM  == 2) {
            $m = 12;
            $y = $nowY - 1;
        } elseif ($nowM == 1) {
            $m = 11;
            $y = $nowY - 1;
        } else {
            $m = $nowM - 2;
            $y = $nowY;
        }
        $endInit = $nowY."-".$nowM."-1"; //Start with the 1st of this month
        $end = date_create($endInit);
        date_sub($end,date_interval_create_from_date_string("1 day")); //Subtract 1 day to get the last day of last month
        date_sub($end,date_interval_create_from_date_string("1 month")); //Subtract 1 month
        $fromDate = sprintf('%04d,%02d,%02d', $y, $m, 1);
        $toDate = date_format($end, "Y,m,d");
    } elseif ($searchperiod == 1 || $searchperiod == null) { // Last month
        if ($nowM  == 1) {
            $m = 12;
            $y = $nowY - 1;
        } else {
            $m = $nowM - 1;
            $y = $nowY;
        }
        $endDate = $nowY."-".$nowM."-1"; //Start with the 1st of this month
        $end = date_create($endDate);
        date_sub($end,date_interval_create_from_date_string("1 day")); //Subtract 1 day to get last day of last month
        $fromDate = sprintf('%04d,%02d,%02d', $y, $m, 1);
        $toDate = date_format($end, "Y,m,d");
    } else { //$searchperiod = 0 (this month)
        $fromDate = sprintf('%04d,%02d,%02d', $nowY, $nowM, 1);
        $toDate = sprintf('%04d,%02d,%02d', $nowY, $nowM, $nowD);
    }
  }
  if (!empty($_POST["tenantid"])) {
    $tenantid = $_POST["tenantid"];
  } else {
    $tenantid = '0';
  }

} else {
    $startInit = $nowY."-".$nowM."-1"; //Start with the 1st of this month
 
//    $start = date_create($startInit);
$start = date_create("first day of last month");
    $fromDate = date_format($start, "Y,m,d");
    $endInit = $nowY."-".$nowM."-".$nowD;
//    $end = date_create($endInit);
$end = date_create("last day of last month");
    $toDate = date_format($end, "Y,m,d");

//    $fromDateObject = new DateTime($startInit);
$fromDateObject = new DateTime("first day of last month 00:00:00");
//    $toDateObject = new DateTime($endInit);
$toDateObject = new DateTime("last day of last month");

    $fromDisplay = date_format($fromDateObject, 'd F Y');
    $toDisplay = date_format($toDateObject, 'd F Y');

//    $from = sprintf('%04d-%02d-%02d', $nowY, $nowM, 1);
$from = $fromDateObject->format('Y-m-d');
//    $to = $endInit;
$to = $toDateObject->format('Y-m-d');
}


$sql1 = "SELECT * 
         FROM accounts 
         WHERE id = ?";
$stmt = $con->prepare($sql1);
if (!$stmt) {
    die("Prepare failed: " . $con->error);
}
$stmt->bind_param("i", $accountid);
$stmt->execute();
$result1 = $stmt->get_result();

if ($result1->num_rows > 0) {
    while($row1 = $result1->fetch_assoc()) {
       $companyid = $row1["companyID"]; 
    }
}

// Allow this request more time (e.g. 600s)
@ini_set('max_execution_time', '60');
@set_time_limit(60);


$myNewToken = check_xero_token_expiry($con, $companyid);

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

use XeroAPI\XeroPHP\Api\AccountingApi;

$accountingApi = new XeroAPI\XeroPHP\Api\AccountingApi(
    new GuzzleHttp\Client(),
    $config
);


$ifModifiedSince = null;
$today = date('d M Y');
$where = implode(' AND ', [
    'Date >= DateTime('.$fromDate.')',
    'Date <= DateTime('.$toDate.')'
]);
$order = 'Date DESC';
$iDs = null;
$invoiceNumbers = null;
$contactIDs = null;
$status = 'AUTHORISED';
$page = 1;
$includeArchived = null;
$createdByMyApp = null;
$unitdp = 2;
$summaryOnly = false;
$pageSize = 100;
$searchTerm = null;

?>

<?=template_header('Xero Bills Paid')?>

<div class="page-title">
	<div class="icon">
		<svg width="40" height="40" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 640 640"><!--!Font Awesome Free v7.1.0 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license/free Copyright 2025 Fonticons, Inc.--><path d="M128 128C128 92.7 156.7 64 192 64L341.5 64C358.5 64 374.8 70.7 386.8 82.7L493.3 189.3C505.3 201.3 512 217.6 512 234.6L512 512C512 547.3 483.3 576 448 576L192 576C156.7 576 128 547.3 128 512L128 128zM336 122.5L336 216C336 229.3 346.7 240 360 240L453.5 240L336 122.5zM192 152C192 165.3 202.7 176 216 176L264 176C277.3 176 288 165.3 288 152C288 138.7 277.3 128 264 128L216 128C202.7 128 192 138.7 192 152zM192 248C192 261.3 202.7 272 216 272L264 272C277.3 272 288 261.3 288 248C288 234.7 277.3 224 264 224L216 224C202.7 224 192 234.7 192 248zM304 324L304 328C275.2 328.3 252 351.7 252 380.5C252 406.2 270.5 428.1 295.9 432.3L337.6 439.3C343.6 440.3 348 445.5 348 451.6C348 458.5 342.4 464.1 335.5 464.1L280 464C269 464 260 473 260 484C260 495 269 504 280 504L304 504L304 508C304 519 313 528 324 528C335 528 344 519 344 508L344 503.3C369 499.2 388 477.6 388 451.5C388 425.8 369.5 403.9 344.1 399.7L302.4 392.7C296.4 391.7 292 386.5 292 380.4C292 373.5 297.6 367.9 304.5 367.9L352 367.9C363 367.9 372 358.9 372 347.9C372 336.9 363 327.9 352 327.9L344 327.9L344 323.9C344 312.9 335 303.9 324 303.9C313 303.9 304 312.9 304 323.9z"/></svg>
	</div>	
	<div class="wrap">
		<h2>Xero Bills Paid</h2>
	</div>
</div>

<div class="row">
    <div class="col-sm-6" style="margin:auto;">
        <h5>For the period <?php echo $fromDisplay;?> to <?php echo $toDisplay;?></h5>
        Sorted by Currency then Date
    </div>
    <div class="col-sm-6">
        <form class="form" method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]);?>" style="display:flex;">
        <div class="form-group justify-content-end">
            <label class="form-label" for="fromDate">From:</label>
            <div class="col-sm-3 px-3">
                <input class="form-control" id="fromDate" type="date" name="fromDate" value="<?php echo $from;?>">
            </div>
            <label class="form-label" for="toDate">To:</label>
            <div class="col-sm-3 px-3">
                <input class="form-control" id="toDate" type="date" name="toDate" value="<?php echo $to;?>">
            </div>

            <label class="form-label" for="tenantid">Company:</label>
            <div class="col-sm-4 px-3">
                <select class="form-control" id="tenantid" name="tenantid">
                <?php
                    echo "<option value=\"0\"> - Select an Organisation - </option>";
                    foreach ($connections as $company) {
                        $companyId   = $company->getTenantId();
                        $companyName = $company->getTenantName();
                        if($companyId == $tenantid){
                            echo "<option value=\"" . $companyId . "\" selected>". $companyName . "</option>";
                        } else {
                            echo "<option value=\"" . $companyId . "\">". $companyName . "</option>";
                        }
                    }
                ?>
                </select>
            </div>

            <div class="col-sm-1">
            <input type="submit" value="Search" class="btn btn-primary">
            </div>
        </div>
        </form>
    </div>
</div>



<div class="row">
<table class="table">
    <thead>
        <tr>
            <th style="text-align:left;">Date Paid</th>
            <th>Invoice Number</th>
            <th>Paid to</th>
            <th>Description</th>
            <th style="text-align:center;">Currency</th>
            <th style="text-align:right; padding-right:25px;">Amount (incl GST)</th>
        </tr>
    </thead>
    <tbody id="myTable">

<?php

$groupedData = [];

try {
    foreach ($connections as $connection) {

        $tenantId   = $connection->getTenantId();
        $tenantName = $connection->getTenantName();

        // Skip this tenant if it has not been selected
        if ($tenantId !== $tenantid) {
            continue;
        }

        // Ensure tenant bucket
        if (!isset($groupedData[$tenantId])) {
            $groupedData[$tenantId] = [
                'Tenants' => [
                    'Name'     => $tenantName,
                    'TenantID' => $tenantId,
                ],              
            ];
        }

        // 2) Fetch payments
try {
        $paymentsResults = $accountingApi->getPayments(
            $tenantId,
            null,
            'Date>=DateTime('.$fromDate.') AND Date<=DateTime('.$toDate.') AND Status!="DELETED"'
        );
} catch (\XeroAPI\XeroPHP\ApiException $e) {

    $headers = method_exists($e, 'getResponseHeaders') ? $e->getResponseHeaders() : [];

    $dayRemaining = (int)($headers['X-DayLimit-Remaining'][0] ?? -1);
    $minRemaining = (int)($headers['X-MinLimit-Remaining'][0] ?? -1);
    $retryAfter   = (int)($headers['Retry-After'][0] ?? 0);
    $retryAfterMinutes = (int) floor($retryAfter / 60);
    $problem      = $headers['X-Rate-Limit-Problem'][0] ?? 'unknown';

//    echo "<pre>";
//    echo "Day remaining: $dayRemaining\n";
//    echo "Minute remaining: $minRemaining\n";
//    echo "Problem: $problem\n";
//    echo "Retry after: $retryAfterMinutes minutes\n";
//    echo "</pre>";

    if ($problem === 'day') {
        echo "<h6 style=\"color: red;\">Xero daily limit reached for this Organisation. Try again in {$retryAfterMinutes} minutes.</h6>";
        exit;
    }
    if ($problem === 'minute') {
        echo "<h6 style=\"color: red;\">Xero minute limit reached for this Organisation. Try again in {$retryAfterMinutes} minutes.</h6>";
        exit;
    }
    if ($problem === 'concurrent') {
        echo "<h6 style=\"color: red;\">Xero concurrent limit reached for this Organisation. Try again in {$retryAfterMinutes} minutes.</h6>";
        exit;
    }

    echo "Xero API error: " . $problem;
//    echo "Xero API error: " . $e->getMessage();
    exit;
}


        foreach ($paymentsResults->getPayments() as $payment) {

            $paymentInvoiceStub = $payment->getInvoice();
            if (!$paymentInvoiceStub) continue;
            if ($paymentInvoiceStub->getType() !== 'ACCPAY') continue;
            if (!$payment->getAccount()) continue;

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


        $groupedData[$tenantId]['Payments'][] = [
            'PaymentID'  => $payment->getPaymentID(),
            'BatchPaymentID'  => $payment->getBatchPaymentID(),
            'PaymentType'  => $payment->getPaymentType(),
            'Status'  => $payment->getStatus(),
                        'Date'         => $payment->getDate(),
                        'CurrencyRate' => $payment->getCurrencyRate(),
                        'Amount'       => number_format($payment->getAmount(), 2),
                        'Invoices'     => $invoicesPayload, // <-- multiple invoices
                    ];
                }
            }




//echo '<pre>';
//    print_r($groupedData);
//echo '</pre>';





        

    //4) Render / build your grouped output

    // Build <tr> rows from $groupedData
    // Assumes $groupedData is already in memory.

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


    // Sort the array by currency
    foreach ($groupedData as $tenantId => &$bucket) {
        if (!empty($bucket['Payments']) && is_array($bucket['Payments'])) {
            usort($bucket['Payments'], function ($a, $b) {
                $currencyA = $a['Invoices'][0]['CurrencyCode'] ?? '';
                $currencyB = $b['Invoices'][0]['CurrencyCode'] ?? '';
                return $currencyA <=> $currencyB;
            });
        }
    }
    unset($bucket);





    $rowsHtml = '';

    foreach ($groupedData as $tenantId => $bucket) {

        // Tenant
        $tenantName = $bucket['Tenants']['Name'] ?? '';
        $rowsHtml .= "<tr class=\"table-info\"><td colspan=\"6\"><strong>" . xss($tenantName) . "</strong></td></tr>\n";

        // Payments directly under tenant
        $payments = $bucket['Payments'] ?? [];

        foreach ($payments as $p) {
            $paymentId   = $p['PaymentID'] ?? '';
            $paymentType   = $p['PaymentType'] ?? '';
            $batchPaymentId   = $p['BatchPaymentID'] ?? '';
            $paymentDate = formatXeroDate($p['Date'] ?? '');
            $status   = $p['Status'] ?? '';
            $amount      = $p['Amount'] ?? '';
            $invoices    = $p['Invoices'] ?? [];

            if (empty($invoices)) {
                $rowsHtml .= "<tr style=\"vertical-align:middle;\">"
                    . "<td style=\"font-weight:400; text-align:left;\">" . xss($paymentDate) . "</td>"
                    . "<td></td>"
                    . "<td></td>"
                    . "<td></td>"
                    . "<td></td>"
                    . "<td style=\"font-weight:400; text-align:right; padding-right:20px;\">" . xss($amount) . "</td>"
                    . "</tr>\n";
                continue;
            }

            foreach ($invoices as $inv) {
                $invoiceNo    = $inv['InvoiceNumber'] ?? '';
                $currencyCode = $inv['CurrencyCode'] ?? '';
                $contact      = $inv['Contact'] ?? '';
                $lineItems    = $inv['LineItems'] ?? [];

                $lineItemsHtml = "<ul style=\"list-style-type:none; margin:0; padding:5px 0;\">";
                foreach ($lineItems as $li) {
                    $desc       = $li['Description'] ?? '';
                    $qty        = $li['Quantity'] ?? '';
                    $unitAmount = $li['UnitAmount'] ?? '';

                    if (count($lineItems) > 1) {
                        $lineItemsHtml .= "<li style=\"font-weight:400; text-align:left;\">"
                            . xss($desc) . " (" . xss($qty) . " x $" . xss($unitAmount) . ")</li>";
                    } else {
                        $lineItemsHtml .= "<li style=\"font-weight:400; text-align:left;\">"
                            . xss($desc) . "</li>";
                    }
                }
                $lineItemsHtml .= "</ul>";

                $rowsHtml .= "<tr style=\"vertical-align:middle;\">"
                    . "<td style=\"font-weight:400; text-align:left;\">" . xss($paymentDate) . "</td>"
                    . "<td style=\"font-weight:400; text-align:left;\">" . xss($invoiceNo) . "</td>"
                    . "<td style=\"font-weight:400; text-align:left;\">" . xss($contact) . "</td>"
                    . "<td style=\"font-weight:400; text-align:left;\">" . $lineItemsHtml . "</td>"
                    . "<td style=\"font-weight:400; text-align:center;\">" . xss($currencyCode) . "</td>"
                    . "<td style=\"font-weight:400; text-align:right; padding-right:20px;\">" . xss($amount) . "</td>"
                    . "</tr>\n";
            }
        }
    }


    // Render the results to HTML
    echo $rowsHtml;

    echo "</tbody>
        </table>

    </div>";
    ?>

    <div class="row">
    <div class="col-sm-9">
    </div>

    <div class="col-sm-3">
        <form class="form" method="post" action="xero-reports-3_export.php">

            <input type="hidden" name="ifModifiedSince" value="<?= $ifModifiedSince instanceof DateTimeInterface ? htmlspecialchars($ifModifiedSince->format(DateTime::ATOM)) : null ?>">
            <input type="hidden" name="from" value="<?= htmlspecialchars($fromDate ?? '') ?>">
            <input type="hidden" name="to" value="<?= htmlspecialchars($toDate ?? '') ?>">
            <input type="hidden" name="order" value="<?= htmlspecialchars($order ?? '') ?>">
            <input type="hidden" name="page" value="<?= htmlspecialchars((string)($page ?? 1)) ?>">
            <input type="hidden" name="pageSize" value="<?= htmlspecialchars((string)($pageSize ?? 100)) ?>">
            <input type="hidden" name="token" value="<?= htmlspecialchars((string)($accesstoken)) ?>">
            <input type="hidden" name="tenantid" value="<?= $tenantid ?>">

            <!-- Label -->
            <label for="filename">
                File Name:
            </label>

            <div class="d-flex gap-2 align-items-center">
                <input type="text"
                       id="filename"
                       name="filename"
                       value="<?php echo 'paid_bills_' . $from . '_' . $to . '.xls'?>"
                       class="form-control form-control-sm">

                <button type="submit" class="btn btn-primary btn-sm">
                    Export
                </button>
            </div>

        </form>
    </div>
</div>

<?php

} catch (Exception $e) {
    $headers = method_exists($e, 'getResponseHeaders') ? $e->getResponseHeaders() : [];

    $dayRemaining = (int)($headers['X-DayLimit-Remaining'][0] ?? -1);
    $minRemaining = (int)($headers['X-MinLimit-Remaining'][0] ?? -1);
    $retryAfter   = (int)($headers['Retry-After'][0] ?? 0);
    $retryAfterMinutes = (int) floor($retryAfter / 60);
    $problem      = $headers['X-Rate-Limit-Problem'][0] ?? 'unknown';

//    echo "<pre>";
//    echo "Day remaining: $dayRemaining\n";
//    echo "Minute remaining: $minRemaining\n";
//    echo "Problem: $problem\n";
//    echo "Retry after: $retryAfterMinutes minutes\n";
//    echo "</pre>";

    if ($problem === 'day') {
        echo "<h6 style=\"color: red;\">Xero daily limit reached for this Organisation. Try again in {$retryAfterMinutes} - 1 minutes.</h6>";
        exit;
    }
    if ($problem === 'minute') {
        echo "<h6 style=\"color: red;\">Xero minute limit reached for this Organisation. Try shortening the date range or waiting for 60 seconds before trying again.</h6>";
        exit;
    }
    if ($problem === 'concurrent') {
        echo "<h6 style=\"color: red;\">Xero concurrent limit reached for this Organisation. Try again in {$retryAfterMinutes} minutes.</h6>";
        exit;
    }
    echo 'Xero API exception: ' . htmlspecialchars($e->getMessage());
}
?>
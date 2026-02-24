<?php
// Include the main.php file
include '../main.php';
// Check if the user is logged in, if not then redirect to login page
check_loggedin($con);
// Template code below

$accountid = $_SESSION['account_id'];

$sqlAccess = "SELECT * FROM accesscontrol WHERE accountID = $accountid";
$resultAccess = $con->query($sqlAccess);

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
  } else { // Manually set the from and to dates based on teh search period
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
} else {
    $startInit = $nowY."-".$nowM."-1"; //Start with the 1st of this month
    $start = date_create($startInit);
    $fromDate = date_format($start, "Y,m,d");

    $endInit = $nowY."-".$nowM."-".$nowD;
    $end = date_create($endInit);
    $toDate = date_format($end, "Y,m,d");

    $fromDateObject = new DateTime($startInit);
    $toDateObject = new DateTime($endInit);

    $fromDisplay = date_format($fromDateObject, 'd F Y');
    $toDisplay = date_format($toDateObject, 'd F Y');

    $from = sprintf('%04d-%02d-%02d', $nowY, $nowM, 1);
    $to = $endInit;
}


$sql1 = "SELECT * FROM accounts WHERE id = $accountid";
$result1 = $con->query($sql1);
if ($result1->num_rows > 0) {
    while($row1 = $result1->fetch_assoc()) {
       $companyid = $row1["companyID"]; 
    }
}


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
use GuzzleHttp\Client;




$accountingApi = new XeroAPI\XeroPHP\Api\AccountingApi(
    new GuzzleHttp\Client(),
    $config
);


$ifModifiedSince = null;
$today = date('d M Y');
//$where = implode(' AND ', [
//    'Date >= DateTime('.$fromDate.')',
//    'Date <= DateTime('.$toDate.')'
//]);
$where = 'Date >= DateTime('.$fromDate.') && Date < DateTime('.$toDate.')';
$order = 'Date DESC';
$iDs = null;
$invoiceNumbers = null;
$contactIDs = null;
$status = "AUTHORISED";
$page = 1;
$includeArchived = null;
$createdByMyApp = null;
$unitdp = 2;
$summaryOnly = false;
$pageSize = 100;
$searchTerm = null;

?>

<?=template_header('List Xero Paid Bills')?>

<div class="page-title">
	<div class="icon">
		<svg width="40" height="40" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 640 640"><!--!Font Awesome Free v7.1.0 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license/free Copyright 2025 Fonticons, Inc.--><path d="M128 128C128 92.7 156.7 64 192 64L341.5 64C358.5 64 374.8 70.7 386.8 82.7L493.3 189.3C505.3 201.3 512 217.6 512 234.6L512 512C512 547.3 483.3 576 448 576L192 576C156.7 576 128 547.3 128 512L128 128zM336 122.5L336 216C336 229.3 346.7 240 360 240L453.5 240L336 122.5zM192 152C192 165.3 202.7 176 216 176L264 176C277.3 176 288 165.3 288 152C288 138.7 277.3 128 264 128L216 128C202.7 128 192 138.7 192 152zM192 248C192 261.3 202.7 272 216 272L264 272C277.3 272 288 261.3 288 248C288 234.7 277.3 224 264 224L216 224C202.7 224 192 234.7 192 248zM304 324L304 328C275.2 328.3 252 351.7 252 380.5C252 406.2 270.5 428.1 295.9 432.3L337.6 439.3C343.6 440.3 348 445.5 348 451.6C348 458.5 342.4 464.1 335.5 464.1L280 464C269 464 260 473 260 484C260 495 269 504 280 504L304 504L304 508C304 519 313 528 324 528C335 528 344 519 344 508L344 503.3C369 499.2 388 477.6 388 451.5C388 425.8 369.5 403.9 344.1 399.7L302.4 392.7C296.4 391.7 292 386.5 292 380.4C292 373.5 297.6 367.9 304.5 367.9L352 367.9C363 367.9 372 358.9 372 347.9C372 336.9 363 327.9 352 327.9L344 327.9L344 323.9C344 312.9 335 303.9 324 303.9C313 303.9 304 312.9 304 323.9z"/></svg>
	</div>	
	<div class="wrap">
		<h2>Xero Bill Payments (not filtered by SPEND)</h2>
	</div>
</div>


<div class="row">
    <div class="col-sm-6" style="margin:auto;">
        <h5>For the period <?php echo $fromDisplay;?> to <?php echo $toDisplay;?></h5>
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
            <th>Paid to</th>
            <th>Reference</th>
            <th>Type</th>
            <th>Description</th>
            <th style="text-align:center;">Currency</th>
            <th style="text-align:right; padding-right:25px;">Amount (incl GST)</th>
        </tr>
    </thead>
    <tbody id="myTable">

<?php

try {
foreach ($connections as $connection) {
    $xeroTenantId   = $connection->getTenantId();
    $xeroTenantName = $connection->getTenantName();

    echo "<tr class=\"table-info\">
            <td colspan=\"7\" style=\"font-weight:600; font-size:1.2em; text-align:left;\">"
            . htmlspecialchars($xeroTenantName) .
            "</td>
        </tr>";

    /*
     * 1) Fetch active BANK accounts
     */
    $bankAccounts = [];
    $accountsResponse = $accountingApi->getAccounts(
        $xeroTenantId,
        'Status=="ACTIVE" AND Type=="BANK"'
    );

    foreach ($accountsResponse->getAccounts() as $account) {
        if ($account->getStatus() === 'ACTIVE' && $account->getType() === 'BANK') {
            $bankAccounts[$account->getAccountId()] = [
                'account'  => $account,
                'payments' => []
            ];
        }

        if ($account->getType() == 'BANK') {
            echo "<tr class=\"table-secondary\">
                <td  colspan=\"7\" style=\"font-weight:300; font-size:.75em; text-align:left;\">"
                . htmlspecialchars($account->getName()) .
                "</td>";
            //    <td style=\"font-weight:300; font-size:.75em; text-align:left;\">"
            //    . htmlspecialchars($account->getStatus()) .
            //    "</td>
            //    <td colspan=\"4\" style=\"font-weight:300; font-size:.75em; text-align:left;\">"
            //    . htmlspecialchars($account->getType()) .
            //    "</td>
            echo "</tr>";
        

            /*
            * 2) Fetch Payments (this is the DRIVER dataset)
            *    Filter by date range + ACCPAY invoices
            */
            $paymentsResponse = $accountingApi->getPayments(
                $xeroTenantId,
                null, // ifModifiedSince
                $where, //'Date>=DateTime(2025,12,1) AND Date<=DateTime(2025,12,31)'
                $order
            );

            $invoiceIds = [];

            foreach ($paymentsResponse->getPayments() as $payment) {

                // Defensive checks
                if (!$payment->getInvoice()) {
                    continue;
                }

                if ($payment->getInvoice()->getType() !== 'ACCPAY') {
                    continue;
                }

                if (!$payment->getAccount()) {
                    continue;
                }

                $accountId = $payment->getAccount()->getAccountId();

                // Only group if it's a BANK account we fetched earlier
                if (!isset($bankAccounts[$accountId])) {
                    continue;
                }

                $bankAccounts[$accountId]['payments'][] = $payment;
                $invoiceIds[$payment->getInvoice()->getInvoiceId()] = true;
            }

            /*
            * 3) Bulk-fetch referenced invoices
            */
            $invoiceMap = [];

            if (!empty($invoiceIds)) {
                $invoiceIdChunks = array_chunk(array_keys($invoiceIds), 50);

                foreach ($invoiceIdChunks as $chunk) {
                    $where2 = implode(
                        ' OR ',
                        array_map(fn($id) => 'InvoiceID=Guid("' . $id . '")', $chunk)
                    );

                    $invoicesResponse = $accountingApi->getInvoices(
                        $xeroTenantId,
                        null,
                        $where2
                    );

                    foreach ($invoicesResponse->getInvoices() as $invoice) {
                        $invoiceMap[$invoice->getInvoiceId()] = $invoice;
                    }
                }
            }

            /*
            * 4) Render / build your grouped output
            */
            foreach ($bankAccounts as $bankAccountId => $group) {

                if (empty($group['payments'])) {
                    continue;
                }

                $bankAccount = $group['account'];

                foreach ($group['payments'] as $payment) {

                    $invoiceId = $payment->getInvoice()->getInvoiceId();
                    $invoice   = $invoiceMap[$invoiceId] ?? null;
                    $paymentType = $payment->getPaymentType();

                    $paymentDate = $payment->getDate(); // often YYYY-MM-DD for payments
                    // If your SDK returns /Date(...)/ style, keep your existing parser.
                    if ($paymentDate && preg_match('/\d+/', $paymentDate, $matches)) {
                        $seconds = ((int)$matches[0]) / 1000;
                        $formattedDate = date('d M Y', (int)$seconds);
                    }

                    $descriptions = [];

                    foreach ($invoice->getLineItems() as $lineItem) {
                        $description = trim((string) $lineItem->getDescription());
                        if ($description !== '') {
                            $descriptions[] = $description;
                        }
                    }

                    $lineItemsContent = implode(' • ', $descriptions);

                    // Optional: protect UI + Excel column width
                    $lineItemsContent = mb_strimwidth($lineItemsContent, 0, 500, '…');


                    $lineItemsQuantity = implode(' | ', array_map(
                        fn($li) => sprintf(
                            '%s ',
                            $li->getQuantity(),
                        ),
                        array_filter($invoice->getLineItems(), fn($li) => $li->getDescription())
                    ));





                    echo "<tr>
                        <td style=\"font-weight:300; font-size:.75em; text-align:left;\">" . $formattedDate ."</td>";
                        if ($invoice) {
                            echo "<td style=\"font-weight:300; font-size:.75em; text-align:left;\">" . $invoice->getContact()->getName() . "</td>";
                        // echo "  Invoice Date: " . $invoice->getDate() . "\n";
                        // echo "  Due Date: " . $invoice->getDueDate() . "\n";
                        //    echo "  Invoice Total: " . $invoice->getTotal() . "\n";
                        }
                        echo "<td style=\"font-weight:300; font-size:.75em; text-align:left;\">" . $payment->getReference() ."</td>";
                        echo "<td style=\"font-weight:300; font-size:.75em; text-align:left;\">" . $invoiceId ."</td>";
                        echo "<td style=\"font-weight:300; font-size:.75em; text-align:left;\">" . $lineItemsContent . "</td>";
                     //   echo "<td style=\"font-weight:300; font-size:.75em; text-align:left;\">" . $lineItemsQuantity . "</td>";
                        echo "<td style=\"font-weight:300; font-size:.75em; text-align:center;\">" . $invoice->getCurrencyCode(). "</td>";
                        echo"<td style=\"font-weight:300; font-size:.75em; text-align:right; padding-right:25px;\">" . $payment->getAmount() ."</td>
                        </tr>";

                }


            }


        }


    }
  
}

echo "</tbody>
    </table>

</div>";
?>

<div class="row">
    <div class="col-sm-11">

    </div>
    <div class="col-sm-1">
        <form class="form form-medium" method="post" action="xero-export-bills-paid.php" style="display:flex;">
        <div class="form-group">
                <input type="number" name="m" id="m" value="<?php echo (int)$m;?>" hidden>
                <input type="number" name="y" id="y" value="<?php echo $y;?>" hidden>
                <input type="hidden" name="ifModifiedSince" value="<?= $ifModifiedSince instanceof DateTimeInterface ? htmlspecialchars($ifModifiedSince->format(DateTime::ATOM)) : null ?>">
                <input type="hidden" name="from" value="<?= htmlspecialchars($fromDate ?? '') ?>">
                <input type="hidden" name="to" value="<?= htmlspecialchars($toDate ?? '') ?>">
                <input type="hidden" name="order" value="<?= htmlspecialchars($order ?? '') ?>">
                <input type="hidden" name="page" value="<?= htmlspecialchars((string)($page ?? 1)) ?>">
                <input type="hidden" name="pageSize" value="<?= htmlspecialchars((string)($pageSize ?? 100)) ?>">
                <input type="hidden" name="token" value="<?= htmlspecialchars((string)($accesstoken)) ?>">

                <input type="submit" value="Export" class="btn btn-primary">
        </div>
        </form> 
    </div>
</div>

<?php
} catch (Exception $e) {
  echo 'Xero API exception: ' . htmlspecialchars($e->getMessage());
}
?>


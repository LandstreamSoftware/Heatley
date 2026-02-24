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

if ($_SERVER["REQUEST_METHOD"] == "POST") {
  if (!empty($_POST["searchperiod"])) {
    $searchperiod = $_POST["searchperiod"];
  }
}

$nowD = date('d');
$nowM = date('m');
$nowY = date('Y');

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
} elseif ($searchperiod == 1) { // Last month
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

foreach ($connections as $connection) {
    $xeroTenantId = $connection->getTenantId();
    $xeroTenantName = $connection->getTenantName();
}





$apiInstance = new XeroAPI\XeroPHP\Api\AccountingApi(
    new GuzzleHttp\Client(),
    $config
);

use XeroAPI\XeroPHP\Models\Accounting\Invoice;


//$d = 1;
//$fromDate = sprintf('%04d,%02d,%02d', $y, $m, $d);
//$toDate = date_format($end, "Y,m,d");


$ifModifiedSince = null;
$today = date('d M Y');
$where = 'Type="SPEND" && Date >= DateTime('.$fromDate.') && Date < DateTime('.$toDate.')';
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

<?=template_header('List Xero Paid Bills')?>

<div class="page-title">
	<div class="icon">
		<svg width="40" height="40" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 640 640"><!--!Font Awesome Free v7.1.0 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license/free Copyright 2025 Fonticons, Inc.--><path d="M128 128C128 92.7 156.7 64 192 64L341.5 64C358.5 64 374.8 70.7 386.8 82.7L493.3 189.3C505.3 201.3 512 217.6 512 234.6L512 512C512 547.3 483.3 576 448 576L192 576C156.7 576 128 547.3 128 512L128 128zM336 122.5L336 216C336 229.3 346.7 240 360 240L453.5 240L336 122.5zM192 152C192 165.3 202.7 176 216 176L264 176C277.3 176 288 165.3 288 152C288 138.7 277.3 128 264 128L216 128C202.7 128 192 138.7 192 152zM192 248C192 261.3 202.7 272 216 272L264 272C277.3 272 288 261.3 288 248C288 234.7 277.3 224 264 224L216 224C202.7 224 192 234.7 192 248zM304 324L304 328C275.2 328.3 252 351.7 252 380.5C252 406.2 270.5 428.1 295.9 432.3L337.6 439.3C343.6 440.3 348 445.5 348 451.6C348 458.5 342.4 464.1 335.5 464.1L280 464C269 464 260 473 260 484C260 495 269 504 280 504L304 504L304 508C304 519 313 528 324 528C335 528 344 519 344 508L344 503.3C369 499.2 388 477.6 388 451.5C388 425.8 369.5 403.9 344.1 399.7L302.4 392.7C296.4 391.7 292 386.5 292 380.4C292 373.5 297.6 367.9 304.5 367.9L352 367.9C363 367.9 372 358.9 372 347.9C372 336.9 363 327.9 352 327.9L344 327.9L344 323.9C344 312.9 335 303.9 324 303.9C313 303.9 304 312.9 304 323.9z"/></svg>
	</div>	
	<div class="wrap">
		<h2>Xero Bank Transactions for Paid Bills</h2>
        <p><?php echo "nowD: ".$nowD."<br>nowM:".$nowM."<br>nowY:".$nowY."<br>searchPeriod:".$searchperiod."<br>
        fromDate:".$fromDate."<br>toDate:".$toDate;?></p>
	</div>
</div>

<div class="row">
    <div class="col-sm-6">
        <h3 style="padding:15px 0 15px 0;"><?php echo $xeroTenantName; ?>:</h3>
        <p style="padding:15px 0 15px 0;">For the period <?php echo $fromDate;?> to <?php echo $toDate;?></p>
    </div> 
</div>
<div class="row">
    <div class="col-sm-6">

    </div>
    <div class="col-sm-6">
        <form class="form form-medium" method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]);?>" style="display:flex;">
        <div class="form-group">
            <label class="form-label" for="searchperiod">Search period:</label>
            <div class="col-sm-7">
            <select class="form-control" name="searchperiod" id="searchperiod">
                <?php if ($searchperiod == 0) {
                    echo "<option value =\"0\" selected>This Month</option>";
                } else {
                    echo "<option value =\"0\">This Month</option>";
                }
                if ($searchperiod == 1) {
                    echo "<option value =\"1\" selected>Last Month</option>";
                } else {
                    echo "<option value =\"1\">Last Month</option>";
                }
                if ($searchperiod == 2) {
                    echo "<option value =\"2\" selected>2 Months ago</option>";
                } else {
                    echo "<option value =\"2\">2 Months ago</option>";
                }
                if ($searchperiod == 12) {
                    echo "<option value =\"12\" selected>This financial year</option>";
                } else {
                    echo "<option value =\"12\">This financial year</option>";
                }
                ?>
            </select>
            </div>
            <div class="col-sm-3">
            <input type="submit" value="Refresh" class="btn btn-primary">
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
            <th>Description</th>
            <th>Currency</th>
            <th style="text-align:right; padding-right:25px;">Amount (incl GST)</th>
        </tr>
    </thead>
    <tbody id="myTable">

<?php

try {
/* invoices
  $result = $apiInstance->getInvoices($xeroTenantId, $ifModifiedSince, $where, $order, $iDs, $invoiceNumbers, $contactIDs, $statuses, $page, $includeArchived, $createdByMyApp, $unitdp, $summaryOnly, $pageSize, $searchTerm);
  $invoices = $result->getinvoices() ?? [];
end invoice */

  $result = $apiInstance->getBankTransactions($xeroTenantId, $ifModifiedSince, $where, $order, $iDs, $status, $page, $unitdp, $pageSize);
  $transactions = $result->getBankTransactions() ?? [];

  $i = 0;
  $count = count($transactions);
  $numTransactions = 0;
  $previousBankAccountName = '';

  while ($i < $count) {
    // Xero returns dates like /Date(1700000000000+0000)/
    $xeroDate = $transactions[$i]->getDate();
    $formattedDate = '';
    if ($xeroDate && preg_match('/\d+/', $xeroDate, $matches)) {
        $seconds = ((int)$matches[0]) / 1000;
        $formattedDate = date('d M Y', (int)$seconds);
    }

    // Bank account is an Account object on the transaction
    $bankAccountObj  = $transactions[$i]->getBankAccount();
    $bankAccountName = $bankAccountObj ? $bankAccountObj->getName() : '';

    if ($bankAccountName != $previousBankAccountName) {
        echo "<tr>
        <td colspan=\"6\" style=\"font-weight:600; font-size:1.2em; text-align:left;\">".htmlspecialchars($bankAccountName)."</td>
        </tr>";
        $previousBankAccountName = $bankAccountName;
    }

    $xeroContact = $transactions[$i]->getContact();
    $contactName = $xeroContact ? $xeroContact->getName() : '';

    $reference    = $transactions[$i]->getReference() ?? '';
    $currencyCode = $transactions[$i]->getCurrencyCode() ?? '';
    $total        = $transactions[$i]->getTotal();
    if ($total === null) {
        // Fallback if Total not populated for some reason
        $subTotal = $transactions[$i]->getSubTotal() ?? 0;
        $taxTotal = $transactions[$i]->getTotalTax() ?? 0;
        $total = $subTotal + $taxTotal;
    }

    $lineItems = $transactions[$i]->getLineItems(); // ArrayObject

    echo "<tr>
        <td style=\"vertical-align:middle; text-align:left;\">" . htmlspecialchars($formattedDate) . "</td>
        <td style=\"vertical-align:middle;\">" . htmlspecialchars($contactName) . "</td>
        <td style=\"vertical-align:middle;\">" . htmlspecialchars($reference) . "</td>";

    // Description column (line item descriptions)
    if (!$lineItems || count($lineItems) === 0) {
        echo "<td style=\"vertical-align:middle;\">&nbsp;</td>";
    } else {
        echo "<td style=\"vertical-align:middle;\"><ul style=\"list-style-type:none; margin:0px; padding:5px 0 5px 0;\">";
        foreach ($lineItems as $li) {
            $desc = $li->getDescription();
            if ($desc !== null && $desc !== '') {
                echo "<li>" . htmlspecialchars($desc) . "</li>";
            }
        }
        echo "</ul></td>";
    }

    echo "<td style=\"vertical-align:middle; text-align:center;\">" . htmlspecialchars($currencyCode) . "</td>
        <td style=\"vertical-align:middle; text-align:right; padding-right:25px;\">" . number_format((float)$total, 2) . "</td>
    </tr>";

    $numTransactions++;
    $i++;
  }
  echo "</tbody>
    </table>

</div>";
?>

<div class="row">
    <div class="col-sm-11">
        <?php echo $numTransactions . " Transactions";?>
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
  echo 'Exception when calling AccountingApi->getInvoices: ', $e->getMessage(), PHP_EOL;
}
?>


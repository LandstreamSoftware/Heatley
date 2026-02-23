<?php
// Include the main.php file
include 'main.php';
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

<<<<<<< HEAD
$searchperiod = 0;
=======
$searchperiod = 1;
>>>>>>> 7ba9896ab7466376c6725dff0da138a8bc4d77d7

require 'vendor/autoload.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
  if (!empty($_POST["searchperiod"])) {
    $searchperiod = $_POST["searchperiod"];
  }
}

<<<<<<< HEAD
$nowD = date('d');
$nowM = date('m');
$nowY = date('Y');

if ($searchperiod == 12) { // This financial year
=======
$nowM = date('m');
$nowY = date('Y');

if ($searchperiod == 12) {
>>>>>>> 7ba9896ab7466376c6725dff0da138a8bc4d77d7
    if ($nowM < 4) {
        $y = $nowY - 1;
    } else {
        $y = $nowY;
    }
    $m = 4;
<<<<<<< HEAD
    $endDate = $nowY."-".$nowM."-".$nowD;
    $end = date_create($endDate);
    date_add($end,date_interval_create_from_date_string("1 month"));
} elseif ($searchperiod == 1) { // Last month
=======
    $endDate = $nowY."-".$nowM."-1";
    $end = date_create($endDate);
    date_add($end,date_interval_create_from_date_string("1 month"));
} elseif ($searchperiod == 1) {
>>>>>>> 7ba9896ab7466376c6725dff0da138a8bc4d77d7
    if ($nowM  == 1) {
        $m = 12;
        $y = $nowY - 1;
    } else {
<<<<<<< HEAD
        $m = $nowM - 1;
=======
        $m = $m - 1;
>>>>>>> 7ba9896ab7466376c6725dff0da138a8bc4d77d7
        $y = $nowY;
    }
    $endDate = $nowY."-".$nowM."-1";
    $end = date_create($endDate);
    date_sub($end,date_interval_create_from_date_string("1 day"));
<<<<<<< HEAD
} elseif ($searchperiod == 2) { // Two months ago
    if ($nowM  == 2) {
        $m = 12;
        $y = $nowY - 1;
=======
} elseif ($searchperiod == 2) {
    if ($nowM  == 2) {
        $m = 12;
        $y = $y - 1;
>>>>>>> 7ba9896ab7466376c6725dff0da138a8bc4d77d7
    } elseif ($nowM == 1) {
        $m = 11;
        $y = $nowY - 1;
    } else {
<<<<<<< HEAD
        $m = $nowM - 2;
=======
        $m = $m - 2;
>>>>>>> 7ba9896ab7466376c6725dff0da138a8bc4d77d7
        $y = $nowY;
    }
    $endDate = $nowY."-".$nowM."-1";
    $end = date_create($endDate);
    date_sub($end,date_interval_create_from_date_string("1 month"));
} else { //$searchperiod = 0 (this month)
    $m = $nowM;
    $y = $nowY;
<<<<<<< HEAD
    $endDate = $nowY."-".$nowM."-".$nowD;
=======
    $endDate = $nowY."-".$nowM."-1";
>>>>>>> 7ba9896ab7466376c6725dff0da138a8bc4d77d7
    $end = date_create($endDate);
    date_add($end,date_interval_create_from_date_string("1 month"));
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

$d = 1;
$fromDate = sprintf('%04d,%02d,%02d', $y, $m, $d);
$toDate = date_format($end, "Y,m,d");



$ifModifiedSince = null; //date('Y-m-d', mktime(0,0,0,$m,1,$y)); //null; //date_format(new DateTime('2026-01-15T10:30:00Z'), 'Y-m-d');
$today = date('d M Y');
$where = 'Type="ACCPAY" && FullyPaidOnDate >= DateTime('.$fromDate.') && FullyPaidOnDate < DateTime('.$toDate.')'; //'DueDate < ' . date("y-m-d",$today) . ''; 'AmountDue>0'  Type="ACCPAY"
$order = 'FullyPaidOnDate DESC'; //"InvoiceNumber ASC"; 'Contact.Name'
$iDs = null; //array("00000000-0000-0000-0000-000000000000");
$invoiceNumbers = null; //array("INV-001", "INV-002");
$contactIDs = null; //array("00000000-0000-0000-0000-000000000000");
$statuses = array("PAID, AUTHORISED"); //array("AUTHORISED");
$page = 1;
$includeArchived = null; //true;
$createdByMyApp = null; //false;
$unitdp = null; //4;
$summaryOnly = false;
$pageSize = 100;
$searchTerm = null; //"SearchTerm=REF12";

?>

<?=template_header('List Xero Paid Bills')?>

<div class="page-title">
	<div class="icon">
		<svg width="40" height="40" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 640 640"><!--!Font Awesome Free v7.1.0 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license/free Copyright 2025 Fonticons, Inc.--><path d="M128 128C128 92.7 156.7 64 192 64L341.5 64C358.5 64 374.8 70.7 386.8 82.7L493.3 189.3C505.3 201.3 512 217.6 512 234.6L512 512C512 547.3 483.3 576 448 576L192 576C156.7 576 128 547.3 128 512L128 128zM336 122.5L336 216C336 229.3 346.7 240 360 240L453.5 240L336 122.5zM192 152C192 165.3 202.7 176 216 176L264 176C277.3 176 288 165.3 288 152C288 138.7 277.3 128 264 128L216 128C202.7 128 192 138.7 192 152zM192 248C192 261.3 202.7 272 216 272L264 272C277.3 272 288 261.3 288 248C288 234.7 277.3 224 264 224L216 224C202.7 224 192 234.7 192 248zM304 324L304 328C275.2 328.3 252 351.7 252 380.5C252 406.2 270.5 428.1 295.9 432.3L337.6 439.3C343.6 440.3 348 445.5 348 451.6C348 458.5 342.4 464.1 335.5 464.1L280 464C269 464 260 473 260 484C260 495 269 504 280 504L304 504L304 508C304 519 313 528 324 528C335 528 344 519 344 508L344 503.3C369 499.2 388 477.6 388 451.5C388 425.8 369.5 403.9 344.1 399.7L302.4 392.7C296.4 391.7 292 386.5 292 380.4C292 373.5 297.6 367.9 304.5 367.9L352 367.9C363 367.9 372 358.9 372 347.9C372 336.9 363 327.9 352 327.9L344 327.9L344 323.9C344 312.9 335 303.9 324 303.9C313 303.9 304 312.9 304 323.9z"/></svg>
	</div>	
	<div class="wrap">
		<h2>Xero Paid Bills</h2>
<<<<<<< HEAD
    <!--    <p><?php echo "from: ".$fromDate." to: ".$toDate." (".$searchperiod.")";?></p>  -->
=======
    <!--    <p><?php echo "filter: ".$where;?></p>  -->
>>>>>>> 7ba9896ab7466376c6725dff0da138a8bc4d77d7
	</div>
</div>

<div class="row">
    <div class="col-sm-6">
    <h3 style="padding:15px 0 15px 0;"><?php echo $xeroTenantName; ?>:</h3>
    </div> 

    <div class="col-sm-6">
<<<<<<< HEAD
        <form class="form form-medium" method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]);?>" style="display:flex;">
=======
        <form class="form form-medium" method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"].'?searchperiod='.$searchperiod);?>" style="display:flex;">
>>>>>>> 7ba9896ab7466376c6725dff0da138a8bc4d77d7
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
<table class="table table-striped">
    <thead>
        <tr>
            <th style="text-align:center;">Date Paid</th>
            <th>Paid to</th>
            <th>Reference</th>
            <th>Description</th>
            <th style="text-align:right; padding-right:25px;">Amount (incl GST)</th>
        </tr>
    </thead>
    <tbody id="myTable">

<?php

try {
  $result = $apiInstance->getInvoices($xeroTenantId, $ifModifiedSince, $where, $order, $iDs, $invoiceNumbers, $contactIDs, $statuses, $page, $includeArchived, $createdByMyApp, $unitdp, $summaryOnly, $pageSize, $searchTerm);

  $invoices = $result->getinvoices() ?? [];

  $i = 0;
  $count = count($invoices);

  $searchDate = date('Y-m-d', mktime(0,0,0,$m,1,$y));

//  echo "Search from: ".$ifModifiedSince." to ".date_format($end, "Y-m-d");

  $numInvoices = 0;

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
    

    $SearchDateEnd = date_format($end, "Y-m-d");

    $xeroContact = $invoices[$i]->getContact();

//    if ($matchDatePaid >= $searchDate && $matchDatePaid < $SearchDateEnd){ // only show last month's paid bills

        $lineItems = $invoices[$i]->getLineItems(); // usually an array / ArrayObject

        echo "<tr>
            <td style=\"vertical-align:middle; text-align:center;\">" . $formattedDatePaid . "</a></td>
            <td style=\"vertical-align:middle;\">" . $xeroContact->getName() . "</a></td>        
            <td style=\"vertical-align:middle;\">" . $invoices[$i]->getInvoiceNumber() . "</a></td>";

        if (!$lineItems || count($lineItems) === 0) {
            echo "<td>&nbsp;</td>";
            continue;
        } else {
            echo "<td style=\"vertical-align:middle;\"><ul style=\"list-style-type:none; margin:0px; padding:5px 0 5px 0;\">";
            foreach ($lineItems as $li) {
                $desc = $li->getDescription();
                echo "<li>" . htmlspecialchars($desc ?? '') . "</li>";
            }
            echo "</ul></td>";
        }

    //    echo "<td style=\"vertical-align:middle; text-align:center;\">" . $formattedDate . "</td>
        echo "<td style=\"vertical-align:middle; text-align:right; padding-right:60px;\">" . number_format($invoices[$i]->getAmountPaid(),2) . "</td>
        </tr>";
        $numInvoices++;
//    }
    $i++;
  }
  echo "</tbody>
    </table>

</div>";
?>

<div class="row">
    <div class="col-sm-11">
        <?php echo $numInvoices . " Invoices";?>
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


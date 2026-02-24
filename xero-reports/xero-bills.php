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


require '../vendor/autoload.php';

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

//$accesstoken = "eyJhbGciOiJSUzI1NiIsImtpZCI6IjFDQUY4RTY2NzcyRDZEQzAyOEQ2NzI2RkQwMjYxNTgxNTcwRUZDMTkiLCJ0eXAiOiJKV1QiLCJ4NXQiOiJISy1PWm5jdGJjQW8xbkp2MENZVmdWY09fQmsifQ.eyJuYmYiOjE3Njc4MTY3NzksImV4cCI6MTc2NzgxODU3OSwiaXNzIjoiaHR0cHM6Ly9pZGVudGl0eS54ZXJvLmNvbSIsImF1ZCI6Imh0dHBzOi8vaWRlbnRpdHkueGVyby5jb20vcmVzb3VyY2VzIiwiY2xpZW50X2lkIjoiRjdCODFGRjZGMkE2NEVGQkE0QzNGOENFRkM4QkUzQzciLCJzdWIiOiJhYWU5YmMxYTg3MGI1Y2UzYjllZjYyYzhhMzg1M2ZiOCIsImF1dGhfdGltZSI6MTc2NzcyNzUyOSwieGVyb191c2VyaWQiOiJkZmFiMjA4NS1hZWI4LTQzN2EtYjk0NC02M2ViNmUxZDc3NGUiLCJnbG9iYWxfc2Vzc2lvbl9pZCI6IjNiMjU0ZTg4NzhlNzQ2ODU4ZWYyOWUxODBhZTk4NDc5Iiwic2lkIjoiM2IyNTRlODg3OGU3NDY4NThlZjI5ZTE4MGFlOTg0NzkiLCJhdXRoZW50aWNhdGlvbl9ldmVudF9pZCI6ImQzNGI5M2ZkLTg1MGQtNGFmNS04YzNjLWNmNjYyYzU3NjIxNyIsImp0aSI6IjVFNDA0NkNCMTBCRTVBQjA2MTlBNUU1ODhGMTUxMDhBIiwic2NvcGUiOlsiZW1haWwiLCJwcm9maWxlIiwib3BlbmlkIiwiYWNjb3VudGluZy5jb250YWN0cyIsImFjY291bnRpbmcuc2V0dGluZ3MiLCJhY2NvdW50aW5nLnNldHRpbmdzLnJlYWQiLCJhY2NvdW50aW5nLnJlcG9ydHMucmVhZCIsInByb2plY3RzIiwiYWNjb3VudGluZy5hdHRhY2htZW50cyIsImFjY291bnRpbmcuam91cm5hbHMucmVhZCIsImFzc2V0cyIsImFjY291bnRpbmcudHJhbnNhY3Rpb25zIiwib2ZmbGluZV9hY2Nlc3MiXSwiYW1yIjpbInB3ZCJdfQ.KCeQyUAtnwRLTQlvgNDHMr7a2Mi_CIzLkBArDZCZqtKOwcs0ohNDvE67rI7fkQ57pVo-vpdueyEDTtBf2zV6E3IiHKHlz7tN__5TFqWzP9K0nIFQGs55iyx-wJbO1KtQPv4TK3crMPIxlsEF5RIv3xg5zkjQ2h5G-9MhycfSfMOU7gG1Kw60GBW0m6CbI17qy9ddmdumtCP0aHb8apLAceZ4KZGWZ_bWaKv0FbhXrYKYIgveUmHTbsgvkH0dWZJLWSWzf8Mk9FjakN9SUSKwJLyttRUIG_30fPHv9CvYR1CAWpfDvvDs_4SlzYuN3Y_eX4ygwi90u8JEC97CMTp_XQ";

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


$ifModifiedSince = null; //new DateTime("2020-02-06T12:17:43.202-08:00");
$today = date('d M Y');
$where = 'Type="ACCPAY"'; //'DueDate < ' . date("y-m-d",$today) . ''; 'AmountDue>0'
$order = 'Date DESC'; //"InvoiceNumber ASC"; 'Contact.Name'
$iDs = null; //array("00000000-0000-0000-0000-000000000000");
$invoiceNumbers = null; //array("INV-001", "INV-002");
$contactIDs = null; //array("00000000-0000-0000-0000-000000000000");
$statuses = array("AUTHORISED"); //array("AUTHORISED");
$page = 1;
$includeArchived = null; //true;
$createdByMyApp = null; //false;
$unitdp = null; //4;
$summaryOnly = false;
$pageSize = 100;
$searchTerm = null; //"SearchTerm=REF12";

?>

<?=template_header('List Xero Unpaid Bills')?>

<div class="page-title">
	<div class="icon">
		<svg width="40" height="40" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 640 640"><!--!Font Awesome Free v7.1.0 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license/free Copyright 2025 Fonticons, Inc.--><path d="M128 128C128 92.7 156.7 64 192 64L341.5 64C358.5 64 374.8 70.7 386.8 82.7L493.3 189.3C505.3 201.3 512 217.6 512 234.6L512 512C512 547.3 483.3 576 448 576L192 576C156.7 576 128 547.3 128 512L128 128zM336 122.5L336 216C336 229.3 346.7 240 360 240L453.5 240L336 122.5zM192 152C192 165.3 202.7 176 216 176L264 176C277.3 176 288 165.3 288 152C288 138.7 277.3 128 264 128L216 128C202.7 128 192 138.7 192 152zM192 248C192 261.3 202.7 272 216 272L264 272C277.3 272 288 261.3 288 248C288 234.7 277.3 224 264 224L216 224C202.7 224 192 234.7 192 248zM304 324L304 328C275.2 328.3 252 351.7 252 380.5C252 406.2 270.5 428.1 295.9 432.3L337.6 439.3C343.6 440.3 348 445.5 348 451.6C348 458.5 342.4 464.1 335.5 464.1L280 464C269 464 260 473 260 484C260 495 269 504 280 504L304 504L304 508C304 519 313 528 324 528C335 528 344 519 344 508L344 503.3C369 499.2 388 477.6 388 451.5C388 425.8 369.5 403.9 344.1 399.7L302.4 392.7C296.4 391.7 292 386.5 292 380.4C292 373.5 297.6 367.9 304.5 367.9L352 367.9C363 367.9 372 358.9 372 347.9C372 336.9 363 327.9 352 327.9L344 327.9L344 323.9C344 312.9 335 303.9 324 303.9C313 303.9 304 312.9 304 323.9z"/></svg>
	</div>	
	<div class="wrap">
		<h2>Xero Unpaid Bills</h2>
	</div>
</div>

<div class="row">
    <div class="col-sm-8">
    <h3 style="padding:15px 0 15px 0;"><?php echo $xeroTenantName; ?>:</h3>
    </div> 
</div>

<table class="table table-striped">
    <thead>
        <tr>
            <th>From</th>
            <th>Ref</th>
            <th>Date</th>
            <th>Due Date</th>
            <th>Paid</th>
            <th>Due</th>
        </tr>
    </thead>
    <tbody id="myTable">

<?php

try {
  $result = $apiInstance->getInvoices($xeroTenantId, $ifModifiedSince, $where, $order, $iDs, $invoiceNumbers, $contactIDs, $statuses, $page, $includeArchived, $createdByMyApp, $unitdp, $summaryOnly, $pageSize, $searchTerm);

  $invoices = $result->getinvoices();

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

    $xeroContact = $invoices[$i]->getContact();

    //if($formattedDueDate < $today) {
    echo "<tr>
    <td>" . $xeroContact->getName() . "</a></td>        
            <td>" . $invoices[$i]->getInvoiceNumber() . "</a></td>
            <td>" . $formattedDate . "</td>
            <td>" . $formattedDueDate . "</td>
            <td>" . number_format($invoices[$i]->getAmountPaid(),2) . "</td>
            <td>" . number_format($invoices[$i]->getAmountDue(),2) . "</a></td>
        </tr>
        </tbody>";
    //}

    $i++;
  }
  echo "<table><tr>
    <td>" . $count . " invoices</td>
    </tr>
    </table>";

 // echo $result;
  
} catch (Exception $e) {
  echo 'Exception when calling AccountingApi->getInvoices: ', $e->getMessage(), PHP_EOL;
}
?>


<?php
include 'main.php';
require 'vendor/autoload.php';

$accountid = $_SESSION['account_id'];

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

echo $accesstoken;
?>
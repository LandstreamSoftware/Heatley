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

$leasetermgrouping = "";

?>



	<head>
		<meta charset="utf-8">
		<meta name="viewport" content="width=device-width,minimum-scale=1">
		<title>FURTHER TERMS OF LEASE</title>
        <meta name="author" content="Barry Pyle" />
		<link href="style.css" rel="stylesheet" type="text/css">
		<link href="css/bootstrap.css" rel="stylesheet" type="text/css">
        <style>
            body {
                background-color: white !important;
            }
        </style>
	</head>

<?php

$Q = explode("/", $_SERVER['QUERY_STRING']);
parse_str($Q[0],$QueryParameters);
if(!empty($QueryParameters['leaseid'])) {
    $QPleaseid = $QueryParameters['leaseid'];
}

//Use to list the Additional clauses
$sql = "SELECT * FROM leaseterms_view WHERE leaseid = $QPleaseid and recordownerid IN ($accessto) ORDER BY clausenumber";
$result = $con->query($sql);




echo " 
<div class=\"notification\">
    <div class=\"block\">";


echo "<table style=\"width:100%; margin-left:auto; margin-right:auto;\">
    <tr>
        <td colspan=\"2\" style=\"font-size:26px; text-align:center; padding-bottom:20px;\">FURTHER TERMS OF LEASE</td>
    </tr>
    <tr>
        <td colspan=\"2\" style=\"padding:0px 10px 20px 10px; text-align:left;\">If there is any conflict between the amendments contained in this Schedule and the clauses in the First and Second Schedules, the amendments in these Further Terms of Lease shall apply.</td>
    </tr>";


//List the clauses  
while($row = $result->fetch_assoc()) {
    $leasetermtext = $row["leasetermtext"]; 

    if($leasetermgrouping != $row["leasetermgrouping"]) {
        $leasetermgrouping = $row["leasetermgrouping"];
        echo "<th colspan= \"2\" class=\"printbggrey\" style=\"background-color:#eee; padding: 10px 10px; width:20%\">" . $leasetermgrouping . "</th>";
    }
       echo" <tr>
            <td style=\"padding-top:10px; vertical-align:top;\">" . ltrim($row["clausenumber"],"0") . "</td>
            <td style=\"padding: 10px; text-align:left;\"><p>" . nl2br(htmlspecialchars($leasetermtext)) . "</p></td>
        </tr>";
}
echo    "</table>
    </div>";

$con->close();
?>
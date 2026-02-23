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

?>

<?=template_header('Expired OPEX')?>

<div class="page-title">
	<div class="icon">
		<svg width="20" height="20" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 576 512"><!--!Font Awesome Free 6.5.1 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license/free Copyright 2024 Fonticons, Inc.--><path d="M575.8 255.5c0 18-15 32.1-32 32.1h-32l.7 160.2c0 2.7-.2 5.4-.5 8.1V472c0 22.1-17.9 40-40 40H456c-1.1 0-2.2 0-3.3-.1c-1.4 .1-2.8 .1-4.2 .1H416 392c-22.1 0-40-17.9-40-40V448 384c0-17.7-14.3-32-32-32H256c-17.7 0-32 14.3-32 32v64 24c0 22.1-17.9 40-40 40H160 128.1c-1.5 0-3-.1-4.5-.2c-1.2 .1-2.4 .2-3.6 .2H104c-22.1 0-40-17.9-40-40V360c0-.9 0-1.9 .1-2.8V287.6H32c-18 0-32-14-32-32.1c0-9 3-17 10-24L266.4 8c7-7 15-8 22-8s15 2 21 7L564.8 231.5c8 7 12 15 11 24z"/></svg>
	</div>	
	<div class="wrap">
		<h2>Premises</h2>
<!--		<p>Welcome back, <?=htmlspecialchars($_SESSION['account_name'], ENT_QUOTES)?>!</p>  -->
	</div>
</div>

<div class="block">





<?php

$sql = "SELECT * FROM opex_view WHERE opexstatusid = 3 and recordOwnerID IN ($accessto) ORDER BY opexdate DESC";
$result = $con->query($sql);

$sql2 = "SELECT * FROM opexstatus";
$result2 = $con->query($sql2);

if ($result->num_rows > 0) {
    echo
    "<div class=\"container-fluid\" style=\"margin:10px 0 10px 0;\">
        <div class=\"row\" style=\"margin:3px 0 10px 0;\">
        <div class=\"col-sm-2\"><strong>Date</strong></div>
        <div class=\"col-sm-3\"><strong>Building Name</strong></div>
        <div class=\"col-sm-4\"><strong>Annual OPEX Cost</strong></div>
        <div class=\"col-sm-2\"><strong>Status</strong></div>
        <div class=\"col-sm-1\"><strong>&nbsp;</strong></div>
    </div>";
    // output data of each row
    while($row = $result->fetch_assoc()) {
        echo "<div class=\"row alert-warning\" style=\"border-top:solid 1px #ccc;\">
            <div class=\"col-sm-2\" style=\"margin:10px 0 10px 0; \">" . date_format(date_create($row["opexdate"]),"j F Y"). "</div>
            <div class=\"col-sm-3\" style=\"margin:10px 0 10px 0; \">" . $row["buildingname"]. "</div>
            <div class=\"col-sm-4\" style=\"margin:10px 0 10px 0; \">" . $row["annualopexcost"] . "</div>
            <div class=\"col-sm-2\" style=\"margin:10px 0 10px 0; \">" . $row["opexstatus"] . "</div>
            <div class=\"col-sm-1\" style=\"margin:10px 0 10px 0; \"><a href=\"/viewopex.php?opexid=" . $row["opexid"] . "\">VIEW</a></div>
        </div>";
    }
} else {
    echo "0 results";
}
  
echo
    "<div class=\"row\" style=\"margin-top:20px;\">
          <div class=\"col-sm-2\"><a href=\"listopex.php\" class=\"btn btn-primary\">Back to OPEX</a></div>
    </div>";

        $con->close();
?>

</div>

<?=template_footer()?>
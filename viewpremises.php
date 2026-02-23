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

<?=template_header('View Premises')?>

<div class="page-title">
	<div class="icon">
		<svg width="20" height="20" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 576 512"><!--!Font Awesome Free 6.5.1 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license/free Copyright 2024 Fonticons, Inc.--><path d="M575.8 255.5c0 18-15 32.1-32 32.1h-32l.7 160.2c0 2.7-.2 5.4-.5 8.1V472c0 22.1-17.9 40-40 40H456c-1.1 0-2.2 0-3.3-.1c-1.4 .1-2.8 .1-4.2 .1H416 392c-22.1 0-40-17.9-40-40V448 384c0-17.7-14.3-32-32-32H256c-17.7 0-32 14.3-32 32v64 24c0 22.1-17.9 40-40 40H160 128.1c-1.5 0-3-.1-4.5-.2c-1.2 .1-2.4 .2-3.6 .2H104c-22.1 0-40-17.9-40-40V360c0-.9 0-1.9 .1-2.8V287.6H32c-18 0-32-14-32-32.1c0-9 3-17 10-24L266.4 8c7-7 15-8 22-8s15 2 21 7L564.8 231.5c8 7 12 15 11 24z"/></svg>
	</div>	
	<div class="wrap">
		<h2>Premises Detials</h2>
<!--		<p>Welcome back, <?=htmlspecialchars($_SESSION['account_name'], ENT_QUOTES)?>!</p>  -->
	</div>
</div>

<div class="block">

<?php

$Q = explode("/", $_SERVER['QUERY_STRING']);
parse_str($Q[0],$QueryParameters);
//$Query = $QueryParameters['premisesid'];
if(empty($QueryParameters['premisesid'])){
    $QPpremisesid = "";
}else{
    $QPpremisesid = $QueryParameters['premisesid'];
}

?>

<table class="table">

<?php
$sql = "SELECT * FROM premises_details_view WHERE idpremises = $QPpremisesid and recordOwnerID IN ($accessto)";
$result = $con->query($sql);

$sql2 = "SELECT * FROM leases_view WHERE premisesid = $QPpremisesid and leasestatusid in (2,3) and recordOwnerID IN ($accessto)";
$result2 = $con->query($sql2);

if ($result->num_rows > 0) {
  // output data of each row
  while($row = $result->fetch_assoc()) {
    echo "<tr>
            <td style=\"width:25%\">Building:</td>
            <td style=\"width:25%\"><h4><a class=\"h4link\" href=\"/viewbuilding.php?buildingid=" . $row["idbuildings"] . "\">" . $row["buildingname"] . "</a></h4</td>
            <td style=\"width:25%\"></td>
            <td style=\"width:25%\"></td>
        </tr>
        <tr>
            <td>Unit:</td>
            <td colspan=\"3\">" . $row["unitname"]. "</td>
        </tr>
        <tr>
            <td>Address 1:</td>
            <td colspan=\"3\">" . $row["premisesaddress1"]. "</td>
        </tr>
        <tr>
            <td>Address 2:</td>
            <td colspan=\"3\">" . $row["premisesaddress2"]. "</td>
        </tr>
        <tr>
            <td>Suburb:</td>
            <td colspan=\"3\">" . $row["premisessuburb"]. "</td>
        </tr>
        <tr>
            <td>City:</td>
            <td colspan=\"3\">" . $row["premisescity"]. "</td>
        </tr>
        <tr>
            <td>Post Code:</td>
            <td colspan=\"3\">" . $row["premisespostcode"]. "</td>
        </tr>
        <tr>
            <td>Floor Area:</td>
            <td colspan=\"3\">" . $row["floorarea"] . "</td>
        </tr>
        <tr>
            <td>OPEX:</td>
            <td colspan=\"3\">" . $row["opexpercentage"] . "%</td>
        </tr>
        <tr>
            <td>Management Fee:</td>
            <td colspan=\"3\">" . $row["managementfeepercent"] . "%</td>
        </tr>
        <tr>
            <td>Notes:</td>
            <td colspan=\"3\">" . $row["notes"] . "</td>
        </tr>
        <tr>
            <td>Link to rates details:</td>
            <td colspan=\"3\">" . $row["ratesurl"] . "</td>
        </tr>";
  }
} else {
  echo "0 results";
}

echo "</tbody></table>";

echo
"<div class=\"row\">
        <div class=\"col-sm-2\" style=\"padding-top:20px; padding-bottom:20px\"><a href=\"editpremises.php?premisesid=" . $QPpremisesid . "\" class=\"btn btn-primary\">Edit Premises</a></div>
</div>";

//List the Tenant in this building
echo "<div>
    <h3 style=\"padding:15px 0 15px 0;\">Leased to:</h3>
</div>
<div class=\"container-fluid\" style=\"margin:10px 0 10px 0;\">
    <div class=\"row\" style=\"margin:3px 0 10px 0;\">
        <div class=\"col-sm-3\"><strong>Tenant</strong></div>
        <div class=\"col-sm-3\"><strong>Premises Address</strong></div>
        <div class=\"col-sm-2\"><strong>Commencement Date</strong></div>
        <div class=\"col-sm-2\"><strong>Expiry Date</strong></div>
        <div class=\"col-sm-1\"><strong>Status</strong></div>
        <div class=\"col-sm-1\">&nbsp;</div>
    </div>";

while($row2 = $result2->fetch_assoc()) {
    echo "<div class=\"row\"  style=\"border-top:solid 1px #ccc;\">
    <div class=\"col-sm-3\" style=\"margin:10px 0 10px 0; \">" . $row2["tenantname"]. "</div>
    <div class=\"col-sm-3\" style=\"margin:10px 0 10px 0; \">" . $row2["unitname"] . ", " . $row2["premisesaddress1"]. "</div>
    <div class=\"col-sm-2\" style=\"margin:10px 0 10px 0; \">" . date_format(date_create($row2["commencement"]), "j F Y"). "</div>
    <div class=\"col-sm-2\" style=\"margin:10px 0 10px 0; \">" . date_format(date_create($row2["leaseexpirydate"]), "j F Y"). "</div>
    <div class=\"col-sm-1\" style=\"margin:10px 0 10px 0; \">" . $row2["leasestatus"]. "</div>
    <div class=\"col-sm-1\" style=\"margin:10px 0 10px 0; \"><a href=\"/viewlease.php?leaseid=" . $row2["idlease"] . "&tenantid=" . $row2["tenantid"] . "\">VIEW</a></div>
</div>";

}

        $con->close();
?>

</div>

<?=template_footer()?>
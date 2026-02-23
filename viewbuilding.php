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

<?=template_header('View Building')?>

<div class="page-title">
	<div class="icon">
		<svg width="20" height="20" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 576 512"><!--!Font Awesome Free 6.5.1 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license/free Copyright 2024 Fonticons, Inc.--><path d="M575.8 255.5c0 18-15 32.1-32 32.1h-32l.7 160.2c0 2.7-.2 5.4-.5 8.1V472c0 22.1-17.9 40-40 40H456c-1.1 0-2.2 0-3.3-.1c-1.4 .1-2.8 .1-4.2 .1H416 392c-22.1 0-40-17.9-40-40V448 384c0-17.7-14.3-32-32-32H256c-17.7 0-32 14.3-32 32v64 24c0 22.1-17.9 40-40 40H160 128.1c-1.5 0-3-.1-4.5-.2c-1.2 .1-2.4 .2-3.6 .2H104c-22.1 0-40-17.9-40-40V360c0-.9 0-1.9 .1-2.8V287.6H32c-18 0-32-14-32-32.1c0-9 3-17 10-24L266.4 8c7-7 15-8 22-8s15 2 21 7L564.8 231.5c8 7 12 15 11 24z"/></svg>
	</div>	
	<div class="wrap">
		<h2>Building Details</h2>
<!--		<p>Welcome back, <?=htmlspecialchars($_SESSION['account_name'], ENT_QUOTES)?>!</p>  -->
	</div>
</div>

<div class="block">

<?php
$Q = explode("/", $_SERVER['QUERY_STRING']);
parse_str($Q[0],$QueryParameters);
$QPbuildingid = $QueryParameters['buildingid'];

?>

<table class="table">

<?php
$sql = "SELECT * FROM buildings_view WHERE idbuildings = $QPbuildingid and recordOwnerID IN ($accessto)";
$result = $con->query($sql);

$sql2 = "SELECT * FROM opex_view WHERE buildingid = $QPbuildingid and opexstatusid = 2 and recordOwnerID IN ($accessto) ORDER BY opexstatusid DESC";
$result2 = $con->query($sql2);




if ($result->num_rows > 0) {
  // output data of each row
  while($row = $result->fetch_assoc()) {
    echo "<tr>
            <td style=\"width:25%\">Name:</td>
            <td style=\"width:25%\"><h4>" . $row["buildingname"]. "<h4></td>
            <td style=\"width:25%\"></td>
            <td style=\"width:25%\"></td>
        </tr>
        <tr>
            <td>Address:</td>
            <td>" . $row["buildingaddress"] . "</td>
            <td></td>
            <td></td>
        </tr>
        <tr>
            <td>Owner:</td>
            <td><a href=\"/viewcompany.php?companyid=" . $row["idcompany"] . "\">" . $row["buildingowner"] . "</a></td>
            <td></td>
            <td></td>
        </tr>";
  }
} else {
  echo "0 results";
}

echo "</tbody></table>";

if (isset($_SESSION['account_role']) && $_SESSION['account_role'] !== 'View Only') {
    echo
    "<div class=\"row\">
            <div class=\"col-sm-2\" style=\"padding-top:20px; padding-bottom:20px\"><a href=\"editbuilding.php?buildingid=" . $QPbuildingid . "\" class=\"btn btn-primary\">Edit Building</a></div>
    </div>";
}


echo "<div>
    <h3 style=\"padding:15px 0 15px 0;\">OPEX Budget</h3>
</div>

<div class=\"container-fluid\" style=\"margin:10px 0 10px 0;\">
    <div class=\"row\" style=\"margin:3px 0 10px 0;\">
        <div class=\"col-sm-2\"><strong>Date</strong></div>
        <div class=\"col-sm-2\"><strong>Building Name</strong></div>
        <div class=\"col-sm-3\"><strong>Annual OPEX Cost</strong></div>
        <div class=\"col-sm-3\"><strong>Annual OPEX incl GST</strong></div>
        <div class=\"col-sm-1\"><strong>Status</strong></div>
        <div class=\"col-sm-1\"><strong>&nbsp;</strong></div>
</div>";
while($row2 = $result2->fetch_assoc()) {
    $opexid = $row2["opexid"];
    $annualopexcostexcl = number_format($row2["annualopexcost"],2);
    $annualopexcostincl = number_format($row2["annualopexcost"] * 1.15,2);
    if ($row2["opexstatusid"] == 2) {
        echo "<div class=\"row alert-success\" style=\"border-top:solid 1px #ccc;\">";
    } else {
        echo "<div class=\"row\" style=\"border-top:solid 1px #ccc;\">";
    }
    echo
        "<div class=\"col-sm-2\" style=\"margin:10px 0 10px 0; \">" . date_format(date_create($row2["opexdate"]),"j F Y"). "</div>
        <div class=\"col-sm-2\" style=\"margin:10px 0 10px 0; \">" . $row2["buildingname"]. "</div>
        <div class=\"col-sm-3\" style=\"margin:10px 0 10px 0; \">$" . number_format($row2["annualopexcost"],2) . "</div>
        <div class=\"col-sm-3\" style=\"margin:10px 0 10px 0; \">$" . number_format($row2["annualopexcost"] * 1.15,2) . "</div>
        <div class=\"col-sm-1\" style=\"margin:10px 0 10px 0; \">" . $row2["opexstatus"] . "</div>
        <div class=\"col-sm-1\" style=\"margin:10px 0 10px 0; \"><a href=\"/viewopex.php?opexid=" . $row2["opexid"] . "\">VIEW</a></div>
    </div>";
}
echo
"</div>";

//List the Premises in this building
$totalfloorarea = 0;
$totalopexpercent = 0;
echo "<div>
    <h3 style=\"padding:15px 0 15px 0;\">Premises</h3>
</div>
<div class=\"container-fluid\" style=\"margin:10px 0 10px 0;\">
    <div class=\"row\" style=\"margin:3px 0 10px 0;\">
        <div class=\"col-sm-4\"><strong>Unit</strong></div>
        <div class=\"col-sm-2\"><strong>Floor Area m<sup>2</sup></strong></div>
        <div class=\"col-sm-2\"><strong>% of Common<br>Expenses</strong></div>
        <div class=\"col-sm-2\"><strong>OPEX Allocation<br>(excl GST)</strong></div>
        <div class=\"col-sm-2\"><strong>OPEX Allocation<br>(incl GST)</strong></div>
    </div>";

    $sql3 = "SELECT * FROM premises_view WHERE idbuildings = $QPbuildingid and idopex = $opexid and recordOwnerID IN ($accessto) ORDER BY buildingname, unitName";
    $result3 = $con->query($sql3);

    while($row3 = $result3->fetch_assoc()) {
        echo "<div class=\"row\"  style=\"border-top:solid 1px #ccc;\">
            <div class=\"col-sm-4\" style=\"margin:10px 0 10px 0; \"><a href=\"/viewpremises.php?premisesid=" . $row3["idpremises"] . "\">" . $row3["unitname"] . ", " . $row3["premisesaddress1"] . "</a></div>
            <div class=\"col-sm-2\" style=\"margin:10px 0 10px 0; \">" . $row3["floorarea"] . "</div>
            <div class=\"col-sm-2\" style=\"margin:10px 0 10px 0; \">" . $row3["opexpercentage"] . "</div>
            <div class=\"col-sm-2\" style=\"margin:10px 0 10px 0; \">$" . number_format($row3["sumopexitems"],2) . "</div>
            <div class=\"col-sm-2\" style=\"margin:10px 0 10px 0; \">$" . number_format($row3["sumopexitems"] * 1.15,2) . "</div>
        </div>";
        $totalfloorarea += $row3["floorarea"];
        $totalopexpercent += $row3["opexpercentage"];
    }
    echo "<div class=\"row\"  style=\"border-top:solid 1px #ccc;\">
        <div class=\"col-sm-4\" style=\"margin:10px 0 10px 0; \">&nbsp;</div>
        <div class=\"col-sm-2\" style=\"margin:10px 0 10px 0; \"><strong>" . $totalfloorarea . "m<sup>2</sup></strong></div>
        <div class=\"col-sm-2\" style=\"margin:10px 0 10px 0; \"><strong>" . number_format($totalopexpercent,2) . "%</strong></div>
        <div class=\"col-sm-2\" style=\"margin:10px 0 10px 0; \"><strong>$" . $annualopexcostexcl . "</strong></div>
        <div class=\"col-sm-2\" style=\"margin:10px 0 10px 0; \"><strong>$" . $annualopexcostincl . "</strong></div>
    </div";

echo "</div";

        $con->close();
?>

</div>

<?=template_footer()?>
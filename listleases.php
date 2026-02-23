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

$searchstring = "";
$searchstringErr = "";

$totalrent = 0;

if ($_SERVER["REQUEST_METHOD"] == "POST") {
  if (empty($_POST["searchstring"])) {
    $searchstring = "";
  } else {
    $searchstring = test_input($_POST["searchstring"]);
    //check if the field only contains letters dash or white space
    if (!preg_match("/^[a-zA-Z-0-9āēīōūĀĒĪŌŪ' .-\/]*$/", $searchstring)) {
        $searchstringErr = "Prohibited characters used in search sting";
        $searchstring = "";
    } //else {
    //  $searchstring = "%".$searchstring."%";
    //}
  }
}

function test_input($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
  }

?>

<?=template_header('List Leases')?>

<div class="page-title">
	<div class="icon">
		<svg width="20" height="20" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 576 512"><!--!Font Awesome Free 6.5.1 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license/free Copyright 2024 Fonticons, Inc.--><path d="M575.8 255.5c0 18-15 32.1-32 32.1h-32l.7 160.2c0 2.7-.2 5.4-.5 8.1V472c0 22.1-17.9 40-40 40H456c-1.1 0-2.2 0-3.3-.1c-1.4 .1-2.8 .1-4.2 .1H416 392c-22.1 0-40-17.9-40-40V448 384c0-17.7-14.3-32-32-32H256c-17.7 0-32 14.3-32 32v64 24c0 22.1-17.9 40-40 40H160 128.1c-1.5 0-3-.1-4.5-.2c-1.2 .1-2.4 .2-3.6 .2H104c-22.1 0-40-17.9-40-40V360c0-.9 0-1.9 .1-2.8V287.6H32c-18 0-32-14-32-32.1c0-9 3-17 10-24L266.4 8c7-7 15-8 22-8s15 2 21 7L564.8 231.5c8 7 12 15 11 24z"/></svg>
	</div>	
	<div class="wrap">
		<h2>Leases</h2>
<!--		<p>Welcome back, <?=htmlspecialchars($_SESSION['account_name'], ENT_QUOTES)?>!</p>  -->
	</div>
</div>

<div class="block">

<div class="row">
    <div class="col-sm-8">
    </div> 
    <div class="col-sm-4" style="text-align:right;">
        <input class="form-control" id="myInput" type="text" placeholder="Filter leases">
    </div>
</div>

<script>
$(document).ready(function(){
  $("#myInput").on("keyup", function() {
	var value = $(this).val().toLowerCase();
	$("#myTable tr").filter(function() {
  	$(this).toggle($(this).text().toLowerCase().indexOf(value) > -1)
	});
  });
});
</script>


<table class="table">
    <thead>
        <tr>
            <th>Tenant</th>
            <th>Premises Address</th>
            <th>Area</th>
            <th>Commencement</th>
            <th>Renewal Date</th>
            <th style="text-align:center;">Term</th>
            <th>Annual Rent</th>
            <th>Status</th>
            <th style="text-align:center;">per sq m</th>
        </tr>
    </thead>
    <tbody id="myTable">

<?php
$sql = "SELECT * FROM leases_view_with_current_rent WHERE leasestatusid = 2 and recordOwnerID IN ($accessto) ORDER BY unitname";
$result = $con->query($sql);

$sql2 = "SELECT * FROM leases_draft_view WHERE leasestatusid = 1 and recordOwnerID IN ($accessto)";
$result2 = $con->query($sql2);

$date = date_create("now");

if ($result->num_rows > 0) {
  // Display the active leases
  while($row = $result->fetch_assoc()) {
    if(date_create($row["leaseexpirydate"]) > date_add(date_create("now"),date_interval_create_from_date_string("90 days"))) {
        echo "<tr>
            <td><a href=\"/viewlease.php?leaseid=" . $row["idlease"] . "&tenantid=" . $row["tenantid"] . "\">" . $row["tenantname"] . "</a></td>
            <td>" . $row["unitname"] . ", " . $row["premisesaddress1"] . ", " . $row["buildingname"] . "</td>
            <td>" . $row["floorarea"] . "</td>
            <td>" . date_format(date_create($row["commencement"]),"j F Y") . "</td>
            <td>" . date_format(date_create($row["renewalenddate"]),"j F Y") . "</td>
            <td style=\"text-align:center;\">" . $row["term"] . "</td>
            <td style=\"text-align:center;\">$" . number_format($row["currentrentpremises"],2) . "</td>
            <td style=\"text-align:center;\">" . $row["leasestatus"] . "</td>";
            if ($row["persqm"] == 0) {
                echo "<td style=\"text-align:center;\">$0.00</td>";
            } else {
                echo "<td style=\"text-align:center;\">$" . number_format($row["persqm"],2) . "</td>";
            }
            $totalrent += $row["currentrentpremises"];

        echo "</tr>";
    } else {
        echo "<tr class=\"table-warning\">
            <td><a href=\"/viewlease.php?leaseid=" . $row["idlease"] . "&tenantid=" . $row["tenantid"] . "\">" . $row["tenantname"] . "</a></td>
            <td>" . $row["unitname"] . ", " . $row["premisesaddress1"] . ", " . $row["buildingname"] . "</td>
            <td>" . $row["floorarea"] . "</td>
            <td>" . date_format(date_create($row["commencement"]),"j F Y") . "</td>
            <td>" . date_format(date_create($row["renewalenddate"]),"j F Y") . "</td>
            <td style=\"text-align:center;\">" . $row["term"] . "</td>
            <td style=\"text-align:center;\">$" . number_format($row["currentrentpremises"],2) . "</td>
            <td style=\"text-align:center;\">Expiring<br>" . $row["leaseexpirydate"] . "</td>
            <td style=\"text-align:center;\">$" . number_format($row["persqm"],2) . "</td>
        </tr>";
        $totalrent += $row["currentrentpremises"];
    }
  }
  // Display the total rent
  echo "<tr>
    <td colspan=\"6\">&nbsp;</td>
    <td style=\"text-align:center;\"><b>$" . number_format($totalrent,2) . "</b></td>
    <td colspan=\"2\">&nbsp;</td>
  </tr>";
} else {
  echo "0 results";
}

echo "</tbody></table>";

echo
"<div class=\"row\">
        <div class=\"col-sm-2\" style=\"padding-top:20px; padding-bottom:20px;\"><a href=\"addlease.php\" class=\"btn btn-primary\">Add Lease</a></div>
</div>";

?>
<table class="table table-striped">
    <thead>
    <tr>
        <th>Tenant Name</th>
        <th>Premises Address</th>
        <th>Area</th>
        <th>Commencement</th>
        <th>Expiry Date</th>
        <th>Term</th>
        <th>Annual Rent</th>
        <th>Status</th>
        <th style="text-align:center;">per sq m</th>
    </tr>
    </thead>
    <tbody>


<div>
    <h3 style="padding:15px 0 15px 0;">Draft Leases:</h3>
</div>

<?php
if ($result2->num_rows > 0) {
    // Display the draft leases
    while($row2 = $result2->fetch_assoc()) {
        if(empty($row2["currentrentpremises"])) {
            $currentrentpremises = "-";
            $persqm = "";
        } else {
            $currentrentpremises = "$".number_format($row2["currentrentpremises"],2);
            $persqm = "$".number_format($row2["persqm"],2);
        }
        echo "<tr class=\"warning\">
            <td><a href=\"/viewlease.php?leaseid=" . $row2["idlease"] . "&tenantid=" . $row2["tenantid"] . "\">" . $row2["tenantname"]. "</a></td>
            <td>" . $row2["unitname"] . ", " . $row2["premisesaddress1"] . ", " . $row2["buildingname"] . "</td>
            <td>" . $row2["floorarea"] . "</td>
            <td>" . date_format(date_create($row2["commencement"]),"j F Y"). "</td>
            <td>" . date_format(date_create($row2["leaseexpirydate"]),"j F Y"). "</td>
            <td>" . $row2["term"]. "</td>
            <td>" . $currentrentpremises . "</td>
            <td>" . $row2["leasestatus"]. "</td>
            <td style=\"text-align:center;\">" . $persqm . "</td>
        </tr>";
    }
} else {
    echo "0 results";
}
echo
"</tbody></table>";

echo
    "<div class=\"row\" style=\"margin-top:10px; margin-bottom:0px;\">
          <div class=\"col-sm-12\" style=\"padding-top:10px; text-align:right;\"><a href=\"listleasesexpired.php\">Show archived Leases</a></div>
    </div>";

$con->close();
?>


<?=template_footer()?>
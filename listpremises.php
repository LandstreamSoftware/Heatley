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

$sqluser = "SELECT * FROM accounts WHERE id = $accountid";
$resultuser = $con->query($sqluser);

while($rowuser = $resultuser->fetch_assoc()) {
    $recordownerid = $rowuser["companyID"]; 
}
?>

<?=template_header('List Premises')?>

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



<div class="row">
    <div class="col-sm-8">
    </div> 
    <div class="col-sm-4" style="text-align:right;">
        <input class="form-control" id="myInput" type="text" placeholder="Filter premises">
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
      <th>Unit Name</th>
      <th>Address</th>
      <th style="text-align:center;">Floor Area</th>
      <th>Current Tenant</th>
      <th style="text-align:center;">Current Rent</th>
      <th style="text-align:center;">per sq m</th>
    </tr>
  </thead>
  <tbody id="myTable">

<?php
    $sql = "SELECT * FROM leases_view_with_current_rent WHERE leasestatusid = 2 and recordOwnerID IN ($accessto) ORDER BY buildingname, unitName";
    $result = $con->query($sql);

    
if ($result->num_rows > 0) {
    $building = $lastbuilding = "";
    // output data of each row
    while($row = $result->fetch_assoc()) {
      if ($building != $row["buildingname"]) {
        //Add a heading row
        echo "<tr class=\"table-active\">
          <td colspan=\"8\" style=\"font-weight:500;\">" . $row["buildingname"] . "</td>
        </tr>";
      }
      if ($row["persqm"] == null) {
        $persqm = 0.00;
      } else {
        $persqm = $row["persqm"];
      }
      echo "<tr>
        <td><a href=\"/viewpremises.php?premisesid=" . $row["premisesid"] . "\">" . $row["unitname"]. "</a></td>
        <td>" . $row["unitname"] . ", " . $row["premisesaddress1"] . "</td>
        <td style=\"text-align:center;\">" . $row["floorarea"] . "</td>
        <td>" . $row["tenantname"] . "</td>
        <td style=\"text-align:center;\">$" . number_format($row["currentrentpremises"],2) . "</td>
        <td style=\"text-align:center;\">$" . number_format($persqm,2) . "</td>
      </tr>";

      $building = $row["buildingname"];
    }
  } else {
    echo "0 results";
  }
  
  echo "</tbody></table>";
  
  echo
  "<div class=\"row\">
          <div class=\"col-sm-2\" style=\"padding-top:20px;\"><a href=\"addpremises.php\" class=\"btn btn-primary\">Add Premises</a></div>
  </div>";

        $con->close();
?>


<?=template_footer()?>
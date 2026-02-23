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
$buildingid = 0;



if ($_SERVER["REQUEST_METHOD"] == "POST") {
  if (empty($_POST["searchstring"])) {
    $searchstring = "";
  } else {
    $searchstring = test_input($_POST["searchstring"]);
    //check if the field only contains letters dash or white space
    if (!preg_match("/^[a-zA-Z-0-9āēīōūĀĒĪŌŪ' .-\/]*$/", $searchstring)) {
        $searchstringErr = "Prohibited characters used in search sting";
        $searchstring = "";
    }
  }
  if (!empty($_POST["buildingid"])) {
    $buildingid = $_POST["buildingid"];
  }
  if (!empty($_POST["order"])) {
    $order = $_POST["order"];
  }
}

function test_input($data) {
  $data = trim($data);
  $data = stripslashes($data);
  $data = htmlspecialchars($data);
  return $data;
}

$Q = explode("/", $_SERVER['QUERY_STRING']);
parse_str($Q[0],$QueryParameters);
if (!empty($QueryParameters['order'])) {
  $QPorder = $QueryParameters['order'];
} else {
  $QPorder = "inspectiondate DESC, unitname";
}
if (!empty($QueryParameters['searchstring'])) {
  $searchstring = $QueryParameters['searchstring'];
}
if (!empty($QueryParameters['buildingid'])) {
  $buildingid = $QueryParameters['buildingid'];
}


template_header('List Inspections');

$sql = "SELECT idbuildings, buildingName FROM buildings WHERE recordOwnerID IN ($accessto) ORDER BY buildingName";
$result = $con->query($sql);

?>

<div class="page-title">
	<div class="icon">
		<svg width="20" height="20" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 576 512"><!--!Font Awesome Free 6.5.1 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license/free Copyright 2024 Fonticons, Inc.--><path d="M575.8 255.5c0 18-15 32.1-32 32.1h-32l.7 160.2c0 2.7-.2 5.4-.5 8.1V472c0 22.1-17.9 40-40 40H456c-1.1 0-2.2 0-3.3-.1c-1.4 .1-2.8 .1-4.2 .1H416 392c-22.1 0-40-17.9-40-40V448 384c0-17.7-14.3-32-32-32H256c-17.7 0-32 14.3-32 32v64 24c0 22.1-17.9 40-40 40H160 128.1c-1.5 0-3-.1-4.5-.2c-1.2 .1-2.4 .2-3.6 .2H104c-22.1 0-40-17.9-40-40V360c0-.9 0-1.9 .1-2.8V287.6H32c-18 0-32-14-32-32.1c0-9 3-17 10-24L266.4 8c7-7 15-8 22-8s15 2 21 7L564.8 231.5c8 7 12 15 11 24z"/></svg>
	</div>	
	<div class="wrap">
		<h2>List Inspections</h2>
	</div>
</div>

<div class="block">

<div class="row">
  <div class="col-sm-5">

    <form class="form form-medium" method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"].'?order='.$QPorder.'&searchstring='.$searchstring);?>" style="display:flex;">
      <div class="form-group">
        <label class="form-label" for="buildingid">Building:</label>
        <div class="col-sm-7">
          <select class="form-control" name="buildingid" id="buildingid">
          <?php 
          if ($buildingid == 0) {
            echo "<option value =\"0\" selected>All Buildings</option>";
          } else {
            echo "<option value =\"0\">All Buildings</option>";
          }
          while ($row = $result->fetch_assoc()) {
            if ($row["idbuildings"] == $buildingid) {
              echo "<option value=\"" . $row["idbuildings"] . "\" selected>". $row["buildingName"] . "</option>";
            } else {
              echo "<option value=\"" . $row["idbuildings"] . "\">". $row["buildingName"] . "</option>";
            }
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

  <div class="col-sm-2" style="text-align:right; padding-top:5px;"><span class="error"><span class="text-danger"><?php echo $searchstringErr;?></span></div>
    <div class="col-sm-5" style="text-align:right;">
      <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"].'?order='.$QPorder.'&buildingid='.$buildingid);?>" style="display:flex;">
        <input class="form-control" id="searchstring" type="text" name="searchstring" value="<?php echo $searchstring;?>" placeholder="Search in Invoice Number, Date, Company, Ref, Unit, Amount or Total" style="border-radius:3px 0px 0px 3px;">
        <button class="btn btn-primary" style="border-radius:0px;" type="submit">GO</button>
      </form>
    </div>
  </div>



<table class="table table-striped">
  <thead>
    <tr>
      <th><?php if ($QPorder == 'inspectiondate') {
        echo "<a href=\"?order=inspectiondate DESC&searchstring=".$searchstring."&buildingid=".$buildingid."\" style=\"text-decoration:none; color:inherit;\">";
      } else {
        echo "<a href=\"?order=inspectiondate&searchstring=".$searchstring."&buildingid=".$buildingid."\" style=\"text-decoration:none; color:inherit;\">";
      }?>Inspection Date</a></th>
      <th><?php if ($QPorder == 'buildingname') {
        echo "<a href=\"?order=buildingname DESC&searchstring=".$searchstring."&buildingid=".$buildingid."\" style=\"text-decoration:none; color:inherit;\">";
      } else {
        echo "<a href=\"?order=buildingname&searchstring=".$searchstring."&buildingid=".$buildingid."\" style=\"text-decoration:none; color:inherit;\">";
      }?>Building</a></th>
      <th><?php if ($QPorder == 'unitname') {
        echo "<a href=\"?order=unitname DESC&searchstring=".$searchstring."&buildingid=".$buildingid."\" style=\"text-decoration:none; color:inherit;\">";
      } else {
        echo "<a href=\"?order=unitname&searchstring=".$searchstring."&buildingid=".$buildingid."\" style=\"text-decoration:none; color:inherit;\">";
      }?>Unit</a></th>

      <th><?php if ($QPorder == 'tenant') {
        echo "<a href=\"?order=tenant DESC&searchstring=".$searchstring."&buildingid=".$buildingid."\" style=\"text-decoration:none; color:inherit;\">";
      } else {
        echo "<a href=\"?order=tenant&searchstring=".$searchstring."&buildingid=".$buildingid."\" style=\"text-decoration:none; color:inherit;\">";
      }?>Tenant</a></th>

      <th><?php if ($QPorder == 'inspectorfirstname') {
        echo "<a href=\"?order=inspectorfirstname DESC&searchstring=".$searchstring."&buildingid=".$buildingid."\" style=\"text-decoration:none; color:inherit;\">";
      } else {
        echo "<a href=\"?order=inspectorfirstname&searchstring=".$searchstring."&buildingid=".$buildingid."\" style=\"text-decoration:none; color:inherit;\">";
      }?>Inspected By</a></th>
      <th><?php if ($QPorder == 'inspectiontype') {
        echo "<a href=\"?order=inspectiontype DESC&searchstring=".$searchstring."&buildingid=".$buildingid."\" style=\"text-decoration:none; color:inherit;\">";
      } else {
        echo "<a href=\"?order=inspectiontype&searchstring=".$searchstring."&buildingid=".$buildingid."\" style=\"text-decoration:none; color:inherit;\">";
      }?>Inspection Type</a></th>
      <th><?php if ($QPorder == 'conditionid') {
        echo "<a href=\"?order=conditionid DESC&searchstring=".$searchstring."&buildingid=".$buildingid."\" style=\"text-decoration:none; color:inherit;\">";
      } else {
        echo "<a href=\"?order=conditionid&searchstring=".$searchstring."&buildingid=".$buildingid."\" style=\"text-decoration:none; color:inherit;\">";
      }?>Condition</a></th>
    </tr>
  </thead>
  <d id="myTable">

  <?php


// Pagination variables
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$results_per_page = 20; // Number of results per page
$offset = ($page - 1) * $results_per_page;
// Get total number of records
$total_query = "SELECT COUNT(*) AS total FROM inspections_view WHERE recordownerid IN ($accessto) AND ";
if ($buildingid > 0) {
  $total_query = $total_query."buildingid = $buildingid AND ";
}
$total_query = $total_query."(inspectiondate LIKE '%$searchstring%' OR
areaname LIKE '%$searchstring%' OR
inspectorfirstname LIKE '%$searchstring%' OR
inspectorlastname LIKE '%$searchstring%' OR
buildingname LIKE '%$searchstring%' OR
notes LIKE '%$searchstring%' OR
unitname LIKE '%$searchstring%' OR
inspectiontype LIKE '%$searchstring%' OR
conditionname LIKE '%$searchstring%' OR
inspectionstatus LIKE '%$searchstring%')";
$resultCount = $con->query($total_query);
$rowCount = $resultCount->fetch_assoc();
$total_results = $rowCount['total'];
// Calculate total number of pages
$total_pages = ceil($total_results / $results_per_page);

$sql = "SELECT * FROM inspections_view WHERE recordownerid IN ($accessto) AND ";
if ($buildingid > 0) {
  $sql = $sql."buildingid = $buildingid AND ";
}
$sql = $sql."(inspectiondate LIKE '%$searchstring%' OR
areaname LIKE '%$searchstring%' OR
inspectorfirstname LIKE '%$searchstring%' OR
inspectorlastname LIKE '%$searchstring%' OR
buildingname LIKE '%$searchstring%' OR
notes LIKE '%$searchstring%' OR
unitname LIKE '%$searchstring%' OR
inspectiontype LIKE '%$searchstring%' OR
conditionname LIKE '%$searchstring%' OR
inspectionstatus LIKE '%$searchstring%')
ORDER BY $QPorder LIMIT $results_per_page OFFSET $offset";
$result = $con->query($sql);

if ($result->num_rows > 0) {
  // output data of each row
  while($row = $result->fetch_assoc()) {
    $invoiceid = $row["idinspection"];

    echo "<tr>
      <td><a href=\"viewinspection.php?id=" . $row["idinspection"] . "&searchstring=" . $searchstring . "\">" . date_format(date_create($row["inspectiondate"]),"d F Y  ") . "</a>&nbsp;&nbsp;" . date_format(date_create($row["inspectiondate"])," g:i a") . "</td>
      <!--<td>" . date_format(date_create($row["inspectiondate"]),"H:i:s") . "</td>-->
      <td>" . $row["buildingname"] . "</td>
      <td>" . $row["unitname"] . "</td>
      <td>" . $row["tenantname"] . "</td>
      <td>" . $row["inspectorfirstname"] . " " . $row["inspectorlastname"] ."</td>
      <td>" . $row["inspectiontype"] . "</td>
      <td>" . $row["conditionname"] . "</td>
    </tr>";
  }
}

echo "</tbody></table>";

// Display pagination links
$first_displayed_record_number = $offset + 1;
if ($total_results < $offset + $results_per_page) {
    $last_displayed_record_number = $total_results;
} else {
    $last_displayed_record_number = $offset + $results_per_page;
}

echo
"<div class=\"row\">
<div class=\"col-sm-2\" style=\"padding-top:5px;\">";
  if ($last_displayed_record_number > 0) {
    echo $first_displayed_record_number." to ".$last_displayed_record_number." of ";
  }
  echo $total_results." items</div>
  <div class=\"col-sm-3 pagination\">";
//version 2

for ($x = 1; $x <= $total_pages; $x++) {
  if ($page == $x) {
    echo '<a class="active">' . $x . '</a>';
  } else {
    echo '<a href="?page=' . ($x) . '&order=' . $QPorder . '&searchstring=' . $searchstring . '&buildingid=' . $buildingid . '">' . ($x) . '</a>';
  }

}
echo "</div>
</div>";
  $con->close();
?>

<?=template_footer()?>
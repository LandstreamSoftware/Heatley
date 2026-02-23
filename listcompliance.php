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
$status = 0;

$sql2 = "SELECT * FROM compliancestatus ORDER BY idcompliancestatus";
$result2 = $con->query($sql2);

if ($_SERVER["REQUEST_METHOD"] == "POST") {
  if (!empty($_POST["status"])) {
    $status = $_POST["status"];
    //$pagetitle = "Upcomming Compliance Tasks";
  } else {
    $status = 0;
    //$pagetitle = "Completed Compliance Tasks";
  }
}

function test_input($data) {
  $data = trim($data);
  $data = stripslashes($data);
  $data = htmlspecialchars($data);
  return $data;
}



?>

<?=template_header('List Compliance Tasks')?>

<div class="page-title">
	<div class="icon">
		<svg width="20" height="20" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 576 512"><!--!Font Awesome Free 6.5.1 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license/free Copyright 2024 Fonticons, Inc.--><path d="M575.8 255.5c0 18-15 32.1-32 32.1h-32l.7 160.2c0 2.7-.2 5.4-.5 8.1V472c0 22.1-17.9 40-40 40H456c-1.1 0-2.2 0-3.3-.1c-1.4 .1-2.8 .1-4.2 .1H416 392c-22.1 0-40-17.9-40-40V448 384c0-17.7-14.3-32-32-32H256c-17.7 0-32 14.3-32 32v64 24c0 22.1-17.9 40-40 40H160 128.1c-1.5 0-3-.1-4.5-.2c-1.2 .1-2.4 .2-3.6 .2H104c-22.1 0-40-17.9-40-40V360c0-.9 0-1.9 .1-2.8V287.6H32c-18 0-32-14-32-32.1c0-9 3-17 10-24L266.4 8c7-7 15-8 22-8s15 2 21 7L564.8 231.5c8 7 12 15 11 24z"/></svg>
	</div>	
	<div class="wrap">
		<h2>Compliance Tasks</h2>
	</div>
</div>

<div class="block">


<div class="row">
    <div class="col-sm-8">
    <form class="form form-medium col-sm-5" method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]);?>" style="display:flex;">
      <div class="form-group">
        <label class="form-label" for="month">Status:</label>
        <div class="col-sm-8">
          <select class="form-control" name="status" id="status">
            <option value="0">All Outstanding Tasks</option>
          <?php while ($row2 = $result2->fetch_assoc()) {
            if ($row2["idcompliancestatus"] == $status) {
              echo "<option value=\"" . $row2["idcompliancestatus"] . "\" selected>". $row2["complianceStatusName"] . "</option>";
            } else {
              echo "<option value=\"" . $row2["idcompliancestatus"] . "\">". $row2["complianceStatusName"] . "</option>";
            }
          }
          ?> 
          </select>
        </div>
        
        <div class="col-sm-2">
          <input type="submit" value="Refresh" class="btn btn-primary">
        </div>
        </div>
      </form>
    </div> 
    <div class="col-sm-4" style="text-align:right;">
        <input class="form-control" id="myInput" type="text" placeholder="Filter compliance">
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


<table class="table table-striped">
    <thead>
      <tr>
        <th>Date</th>
        <th>Name</th>
        <th>Building</th>
        <th>Premises</th>
        <th>Actioned By</th>
        <th>Status</th>
        <th></th>
      </tr>
    </thead>
    <tbody id="myTable">

    <?php
    if ($status == 0) {
      $sql = "SELECT * FROM compliance_view WHERE statusid <> 4 and recordownerid IN ($accessto) ORDER BY dateactionable";
    } else {
     $sql = "SELECT * FROM compliance_view WHERE statusid = $status and recordownerid IN ($accessto) ORDER BY dateactionable";
    }
    $result = $con->query($sql);

if ($result->num_rows > 0) {
  // output data of each row
  while($row = $result->fetch_assoc()) {
    $complianceid = $row["idcompliance"];
    $sql3 = "SELECT idfile FROM files WHERE bucketName = 'lease-manager-compliance-reports' AND recordID = '$complianceid'";
    $result3 = $con->query($sql3);
    echo "<tr>
      <td>" . date_format(date_create($row["dateactionable"]),"j F Y") . "</td>
      <td><a href=\"/viewcompliance.php?id=" . $row["idcompliance"] . "\">" . $row["compliancename"] . "</a></td>
      <td>" . $row["buildingname"] . "</td>
      <td>" . $row["unitname"] . "</td>
      <td>" . $row["firstname"] . " " . $row["lastname"] . "</td>
      <td>" . $row["compliancestatusname"] . "</td>";
      if ($result3->num_rows > 0) {
        echo "<td><img src='img/pdf_logo.png' height='20px'</td>";
      } else {
        echo "<td></td>";
      }
    echo "</tr>";
  }
} else {
  echo "0 results";
}

echo "</tbody></table>";

echo
"<div class=\"row\">
        <div class=\"col-sm-2\" style=\"padding-top:20px;\"><a href=\"addcompliance.php\" class=\"btn btn-primary\">Add Compliance Item</a></div>
</div>";

        $con->close();
?>


<?=template_footer()?>
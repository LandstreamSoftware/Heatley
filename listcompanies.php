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

//Get the list of Company types
$sql3 = "SELECT * FROM companytype";
$result3 = $con->query($sql3);

?>

<?=template_header('List Companies')?>

<div class="page-title">
	<div class="icon">
		<svg width="20" height="20" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 576 512"><!--!Font Awesome Free 6.5.1 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license/free Copyright 2024 Fonticons, Inc.--><path d="M575.8 255.5c0 18-15 32.1-32 32.1h-32l.7 160.2c0 2.7-.2 5.4-.5 8.1V472c0 22.1-17.9 40-40 40H456c-1.1 0-2.2 0-3.3-.1c-1.4 .1-2.8 .1-4.2 .1H416 392c-22.1 0-40-17.9-40-40V448 384c0-17.7-14.3-32-32-32H256c-17.7 0-32 14.3-32 32v64 24c0 22.1-17.9 40-40 40H160 128.1c-1.5 0-3-.1-4.5-.2c-1.2 .1-2.4 .2-3.6 .2H104c-22.1 0-40-17.9-40-40V360c0-.9 0-1.9 .1-2.8V287.6H32c-18 0-32-14-32-32.1c0-9 3-17 10-24L266.4 8c7-7 15-8 22-8s15 2 21 7L564.8 231.5c8 7 12 15 11 24z"/></svg>
	</div>	
	<div class="wrap">
		<h2>Companies</h2>
<!--		<p>Welcome back, <?=htmlspecialchars($_SESSION['account_name'], ENT_QUOTES)?>!</p>  -->
	</div>
</div>

<div class="block">


<div class="row">
<div class="col-sm-6" style="text-align:right; padding-top:5px;"><span class="error"><span class="text-danger"><?php echo $searchstringErr;?></span></div>
  <div class="col-sm-6" style="text-align:right;">
    <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]);?>" style="display:flex;">
      <input class="form-control" id="searchstring" type="text" name="searchstring" value="<?php echo $searchstring;?>" placeholder="Search in Company Name, Address or Type" style="border-radius:3px 0px 0px 3px;">
      <button class="btn btn-primary" style="border-radius:0px;" type="submit">GO</button>
    </form>
  </div>

  <!--<div class="col-sm-8">
  </div>
  <div class="col-sm-4" style="text-align:left;">
    <input class="form-control" id="myInput" type="text" placeholder="Search in company name, address or company type field">
  </div>-->
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
      <th><a href="?order=companyname" style="text-decoration:none; color:inherit;">Name</a></th>
      <th><a href="?order=address1" style="text-decoration:none; color:inherit;">Address</a></th>
      <th style="text-align:center;"><a href="?order=companytypeid" style="text-decoration:none; color:inherit;">Type</a></th>
      <th style="text-align:center;"><a href="?order=bankaccountnumber DESC" style="text-decoration:none; color:inherit;">Bank Account</a></th>
    </tr>
  </thead>
  <tbody id="myTable">

<?php
$Q = explode("/", $_SERVER['QUERY_STRING']);
parse_str($Q[0],$QueryParameters);
if (!empty($QueryParameters['order'])) {
  $QPorder = $QueryParameters['order'];
} else {
  $QPorder = "companyname";
}

// Pagination variables
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$results_per_page = 20; // Number of results per page
$offset = ($page - 1) * $results_per_page;
// Get total number of records
$total_query = "SELECT COUNT(*) AS total FROM companies_view WHERE recordownerid IN ($accessto) and (
companyname LIKE '%$searchstring%' OR
address1 LIKE '%$searchstring%' OR
companytype LIKE '%$searchstring%')";
$resultCount = $con->query($total_query);
$rowCount = $resultCount->fetch_assoc();
$total_results = $rowCount['total'];
// Calculate total number of pages
$total_pages = ceil($total_results / $results_per_page);

$sql = "SELECT * FROM companies_view WHERE recordOwnerID IN ($accessto) and (
companyname LIKE '%$searchstring%' OR
address1 LIKE '%$searchstring%' OR
companytype LIKE '%$searchstring%') 
ORDER BY $QPorder LIMIT $results_per_page OFFSET $offset";
$result = $con->query($sql);

if ($result->num_rows > 0) {
  // output data of each row
  while($row = $result->fetch_assoc()) {
    echo "<tr>
    <td><a href=\"/viewcompany.php?companyid=" . $row["idcompany"] . "\">" . $row["companyname"] . "</a></td>
    <td>";
  if($row["address1"]){
    echo $row["address1"] . ", ";
  }
  if($row["address2"]){
    echo $row["address2"] . ", ";
  }
  if($row["suburb"]){
    echo $row["suburb"] . ", ";
  }
  echo $row["city"] . ", ".$row["postcode"] . "</td>
    <td style=\"text-align:center;\">" . $row["companytype"]. "</td>
    <td style=\"text-align:center;\">";
    if ($row["bankaccountnumber"] != NULL) {
      echo "<svg xmlns=\"http://www.w3.org/2000/svg\" height=\"20\" height=\"20\" viewBox=\"0 0 448 512\"><!--!Font Awesome Free 6.7.2 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license/free Copyright 2025 Fonticons, Inc.--><path d=\"M438.6 105.4c12.5 12.5 12.5 32.8 0 45.3l-256 256c-12.5 12.5-32.8 12.5-45.3 0l-128-128c-12.5-12.5-12.5-32.8 0-45.3s32.8-12.5 45.3 0L160 338.7 393.4 105.4c12.5-12.5 32.8-12.5 45.3 0z\"/></svg>
";
    } else {
      echo "&nbsp;";
    }
    echo "</td>";
  echo "</tr>";
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
  for ($x = 1; $x <= $total_pages; $x++) {
    if ($page == $x) {
      echo '<a class="active">' . $x . '</a>';
    } else {
      echo '<a href="?page=' . ($x) . '&order=' . $QPorder .'">' . ($x) . '</a>';
    }
  }
  echo "</div>
</div>";

echo
"<div class=\"row\">
  <div class=\"col-sm-2\" style=\"padding-top:20px;\"><a href=\"addcompany.php\" class=\"btn btn-primary\">Add Company</a></div>
</div>";

$con->close();
?>




<?=template_footer()?>
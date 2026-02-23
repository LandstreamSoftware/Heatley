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

?>

<?=template_header('List Contacts')?>

<div class="page-title">
	<div class="icon">
    <svg width="30" height="30" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 640 512"><!--!Font Awesome Free 6.7.2 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license/free Copyright 2025 Fonticons, Inc.--><path d="M144 0a80 80 0 1 1 0 160A80 80 0 1 1 144 0zM512 0a80 80 0 1 1 0 160A80 80 0 1 1 512 0zM0 298.7C0 239.8 47.8 192 106.7 192l42.7 0c15.9 0 31 3.5 44.6 9.7c-1.3 7.2-1.9 14.7-1.9 22.3c0 38.2 16.8 72.5 43.3 96c-.2 0-.4 0-.7 0L21.3 320C9.6 320 0 310.4 0 298.7zM405.3 320c-.2 0-.4 0-.7 0c26.6-23.5 43.3-57.8 43.3-96c0-7.6-.7-15-1.9-22.3c13.6-6.3 28.7-9.7 44.6-9.7l42.7 0C592.2 192 640 239.8 640 298.7c0 11.8-9.6 21.3-21.3 21.3l-213.3 0zM224 224a96 96 0 1 1 192 0 96 96 0 1 1 -192 0zM128 485.3C128 411.7 187.7 352 261.3 352l117.3 0C452.3 352 512 411.7 512 485.3c0 14.7-11.9 26.7-26.7 26.7l-330.7 0c-14.7 0-26.7-11.9-26.7-26.7z"/></svg>
	</div>	
	<div class="wrap">
		<h2>Contacts</h2>
<!--		<p>Welcome back, <?=htmlspecialchars($_SESSION['account_name'], ENT_QUOTES)?>!</p>  -->
	</div>
</div>

<div class="block">

<div class="row">
<div class="col-sm-6" style="text-align:right; padding-top:5px;"><span class="error"><span class="text-danger"><?php echo $searchstringErr;?></span></div>
  <div class="col-sm-6" style="text-align:right;">
    <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]);?>" style="display:flex;">
      <input class="form-control" id="searchstring" type="text" name="searchstring" value="<?php echo $searchstring;?>" placeholder="Search in Name, Company, Company Type or Email" style="border-radius:3px 0px 0px 3px;">
      <button class="btn btn-primary" style="border-radius:0px;" type="submit">GO</button>
    </form>
  </div>
  
  <!--
    <div class="col-sm-8">
    </div> 
    <div class="col-sm-4" style="text-align:right;">
        <input class="form-control" id="myInput" type="text" placeholder="Search in contact name, email etc">
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
            <th><a href="?order=firstname" style="text-decoration:none; color:inherit;">Name</a></th>
            <th><a href="?order=companyname" style="text-decoration:none; color:inherit;">Company</a></th>
            <th><a href="?order=companytype" style="text-decoration:none; color:inherit;">Company Type</a></th>
            <th><a href="?order=emailaddress" style="text-decoration:none; color:inherit;">Email</a></th>
            <th>Mobile</th>
            <th>Phone</th>
        </tr>
    </thead>
    <tbody id="myTable">

    <?php
$Q = explode("/", $_SERVER['QUERY_STRING']);
parse_str($Q[0],$QueryParameters);
if (!empty($QueryParameters['order'])) {
  $QPorder = $QueryParameters['order'];
} else {
  $QPorder = "firstname";
}

// Pagination variables
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$results_per_page = 20; // Number of results per page
$offset = ($page - 1) * $results_per_page;
// Get total number of records
$total_query = "SELECT COUNT(*) AS total FROM contacts_view WHERE  recordownerid IN ($accessto) and (
firstname LIKE '%$searchstring%' OR
lastname LIKE '%$searchstring%' OR
emailaddress LIKE '%$searchstring%' OR
companyname LIKE '%$searchstring%' OR
companytype LIKE '%$searchstring%')";
$resultCount = $con->query($total_query);
$rowCount = $resultCount->fetch_assoc();
$total_results = $rowCount['total'];
// Calculate total number of pages
$total_pages = ceil($total_results / $results_per_page);

$sql = "SELECT * FROM contacts_view WHERE recordownerid IN ($accessto) and (
firstname LIKE '%$searchstring%' OR
lastname LIKE '%$searchstring%' OR
emailaddress LIKE '%$searchstring%' OR
companyname LIKE '%$searchstring%' OR
companytype LIKE '%$searchstring%')
 ORDER BY $QPorder LIMIT $results_per_page OFFSET $offset";
$result = $con->query($sql);

if ($result->num_rows > 0) {
  // output data of each row
  while($row = $result->fetch_assoc()) {
    echo "<tr>
      <td><a href=\"/viewcontact.php?contactid=" . $row["idcontact"] . "\">" . $row["firstname"] . " " . $row["lastname"] . "</a></td>
      <td>" . $row["companyname"]. "</td>
      <td>" . $row["companytype"]. "</td>
      <td>" . $row["emailaddress"]. "</td>
      <td>" . $row["mobilenumber"]. "</td>
      <td>" . $row["phonenumber"]. "</td>
      
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
        <div class=\"col-sm-2\" style=\"padding-top:20px;\"><a href=\"addcontact.php\" class=\"btn btn-primary\">Add Contact</a></div>
</div>";

        $con->close();
?>


<?=template_footer()?>
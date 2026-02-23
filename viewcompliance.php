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

<?=template_header('View Compliance')?>

<div class="page-title">
	<div class="icon">
		<svg width="20" height="20" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 576 512"><!--!Font Awesome Free 6.5.1 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license/free Copyright 2024 Fonticons, Inc.--><path d="M575.8 255.5c0 18-15 32.1-32 32.1h-32l.7 160.2c0 2.7-.2 5.4-.5 8.1V472c0 22.1-17.9 40-40 40H456c-1.1 0-2.2 0-3.3-.1c-1.4 .1-2.8 .1-4.2 .1H416 392c-22.1 0-40-17.9-40-40V448 384c0-17.7-14.3-32-32-32H256c-17.7 0-32 14.3-32 32v64 24c0 22.1-17.9 40-40 40H160 128.1c-1.5 0-3-.1-4.5-.2c-1.2 .1-2.4 .2-3.6 .2H104c-22.1 0-40-17.9-40-40V360c0-.9 0-1.9 .1-2.8V287.6H32c-18 0-32-14-32-32.1c0-9 3-17 10-24L266.4 8c7-7 15-8 22-8s15 2 21 7L564.8 231.5c8 7 12 15 11 24z"/></svg>
	</div>	
	<div class="wrap">
		<h2>Compliance Task Details</h2>
	</div>
</div>

<div class="block">

<?php
$Q = explode("/", $_SERVER['QUERY_STRING']);
parse_str($Q[0],$QueryParameters);
$QPcomplianceid = $QueryParameters['id'];

$bucketname = gcloud_bucket_compliance_reports;

?>

<table class="table">

<?php
$sql = "SELECT * FROM compliance_view WHERE idcompliance = $QPcomplianceid and recordOwnerID IN ($accessto)";
$result = $con->query($sql);


if ($result->num_rows > 0) {
  // output data of each row
  while($row = $result->fetch_assoc()) {
  	$sql1 = "SELECT * from files WHERE bucketName = '$bucketname' and recordID = '$QPcomplianceid'";
  	$result1 = $con->query($sql1);
    echo "<tr>
            <td style=\"width:25%\">Date:</td>
            <td style=\"width:50%\"><h4>" . date_format(date_create($row["dateactionable"]),"j F Y"). "<h4></td>
            <td style=\"width:25%\"></td>
        </tr>
        <tr>
            <td>Name:</td>
            <td>" . $row["compliancename"] . "</td>
            <td></td>
        </tr>
        <tr>
            <td>Description:</td>
            <td>" . $row["compliancedescription"] . "</td>
            <td></td>
        </tr>
        <tr>
            <td>Building:</td>
            <td>" . $row["buildingname"] . "</td>
            <td></td>
        </tr>
        <tr>
            <td>Premises:</td>
            <td>" . $row["unitname"] . "</td>
            <td></td>
        </tr>
        <tr>
            <td>Supplier:</td>
            <td>" . $row["companyname"] . "</td>
            <td></td>
        </tr>
        <tr>
            <td>Contact:</td>
            <td>" . $row["firstname"] . " " . $row["lastname"] . "</td>
            <td></td>
        </tr>
        <tr>
            <td>Status:</td>
            <td>" . $row["compliancestatusname"] . "</td>
            <td></td>
        </tr>
        <tr>
            <td>Completion Date:</td>
            <td>" . $row["datecompleted"] . "</td>
            <td></td>
        </tr>
        <tr>
            <td style=\"padding-top:15px;\">Documents:</td>
            <td>";
        while($row1 = $result1->fetch_assoc()) {
            $filepath = $row1["filePath"];
            $originalname = $row1["originalName"];
            $file_url = "https://storage.cloud.google.com/" . gcloud_bucket_compliance_reports . "/" . $filepath;
        	echo "
            	<img src='img/pdf_logo.png' height='25px' style='margin:5px 10px 5px 0;'>  <a href=" . $file_url . ">" . $originalname . "</a><br>";
        }
        echo "</td>
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
            <div class=\"col-sm-2\" style=\"padding-top:20px; padding-bottom:20px;\"><a href=\"editcompliance.php?id=" . $QPcomplianceid . "\" class=\"btn btn-primary\">Edit Compliance Item</a></div>
            <div class=\"col-sm-10\" style=\"padding-top:20px; padding-bottom:20px; text-align:right;\"><a href=\"listcompliance.php?status=0\" class=\"btn btn-primary\">Back to Compliance list</a></div>
    </div>";
} else {
   echo
    "<div class=\"row\">
            <div class=\"col-sm-10\" style=\"padding-top:20px; padding-bottom:20px; text-align:right;\"><a href=\"listcompliance.php?status=0\" class=\"btn btn-primary\">Back to Compliance list</a></div>
    </div>";
}

        $con->close();
?>


<?=template_footer()?>
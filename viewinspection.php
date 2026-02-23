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

<?=template_header('View Inspection')?>

<div class="page-title">
	<div class="icon">
		<svg width="20" height="20" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 576 512"><!--!Font Awesome Free 6.5.1 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license/free Copyright 2024 Fonticons, Inc.--><path d="M575.8 255.5c0 18-15 32.1-32 32.1h-32l.7 160.2c0 2.7-.2 5.4-.5 8.1V472c0 22.1-17.9 40-40 40H456c-1.1 0-2.2 0-3.3-.1c-1.4 .1-2.8 .1-4.2 .1H416 392c-22.1 0-40-17.9-40-40V448 384c0-17.7-14.3-32-32-32H256c-17.7 0-32 14.3-32 32v64 24c0 22.1-17.9 40-40 40H160 128.1c-1.5 0-3-.1-4.5-.2c-1.2 .1-2.4 .2-3.6 .2H104c-22.1 0-40-17.9-40-40V360c0-.9 0-1.9 .1-2.8V287.6H32c-18 0-32-14-32-32.1c0-9 3-17 10-24L266.4 8c7-7 15-8 22-8s15 2 21 7L564.8 231.5c8 7 12 15 11 24z"/></svg>
	</div>	
	<div class="wrap">
		<h2>Inspection Details</h2>
	</div>
</div>

<div class="block">

<?php
$Q = explode("/", $_SERVER['QUERY_STRING']);
parse_str($Q[0],$QueryParameters);
$QPinspectionid = $QueryParameters['id'];


$sql = "SELECT * FROM inspections_view WHERE idinspection = $QPinspectionid and recordOwnerID IN ($accessto)";
$result = $con->query($sql);

$sql2 = "SELECT * FROM inspectionmedia WHERE inspectionID = $QPinspectionid";
$result2 = $con->query($sql2);


if ($result->num_rows > 0) {
    echo '<table class="table">';
  // output data of each row
  while($row = $result->fetch_assoc()) {
    $unitname = $row["unitname"];
    $areaname = $row["areaname"];
    $tenantname = $row["tenantname"];
    echo "<tr>
            <td style=\"width:25%\">Building:</td>
            <td style=\"width:75%\"><h4>" . $row["buildingname"]. "<h4></td>
        </tr>
        <tr>
            <td>Premises:</td>
            <td>" . $row["unitname"] . "</td>
        </tr>
        <tr>
            <td>Tenant:</td>
            <td>" . $row["tenantname"] . "</td>
        </tr>
        <tr>
            <td>Inspection Type:</td>
            <td>" . $row["inspectiontype"] . "</td>
        </tr>
        <tr>
            <td>Inspection Area:</td>
            <td>" . $row["areaname"] . "</td>
        </tr>
        <tr>
            <td>Condition:</td>
            <td>" . $row["conditionname"] . "</td>
        </tr>
        <tr>
            <td>Inspected By:</td>
            <td>" . $row["inspectorfirstname"] . " " . $row["inspectorlastname"] . "</a></td>
        </tr>
        <tr>
            <td>Inspection Date/Time:</td>
            <td>" . date_format(date_create($row["inspectiondate"]), "d F Y H:i:s") . "</td>
        </tr>
        <tr>
            <td>Status:</td>
            <td>" . $row["inspectionstatus"] . "</a></td>
        </tr>
        <tr>
            <td>Notes:</td>
            <td>" . nl2br($row["notes"]) . "</a></td>
        </tr>";
  }
} else {
  echo "0 results";
}
echo "</table>";

// Display the images
?>

<div class="row">
<?php
if ($result2->num_rows > 0) { 
    // output data of each media record
    $filecount = 0;
  while($row2 = $result2->fetch_assoc()) {
    $filecount++;
    ?>
    <div class="gallery">
        <a target="_blank" href="<?php echo "https://storage.googleapis.com/" . gcloud_bucket_inspection_media . "/" . $row2["fileURL"];?>">
            <img src="<?php echo "https://storage.googleapis.com/" . gcloud_bucket_inspection_media . "/" . $row2["fileURL"];?>">
        </a>
        <div class="desc"><?php echo "<strong>" . $filecount . ". " . $areaname . "</strong><br>" . $unitname . "<br>" . $tenantname . "<br>" ?></div>
    </div>
    <?php 
  }
}

echo
    "<div class=\"row\">
            <div class=\"col-sm-2\" style=\"padding-top:20px; padding-bottom:20px\"><a href=\"editinspection.php?id=" . $QPinspectionid . "\" class=\"btn btn-primary\">Edit Inspection</a></div>
    </div>";
?>


</div>

<?php
$con->close();
?>

<?=template_footer()?>
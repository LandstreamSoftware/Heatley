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

<?=template_header('Edit Rent Review')?>

<div class="page-title">
	<div class="icon">
		<svg width="20" height="20" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 576 512"><!--!Font Awesome Free 6.5.1 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license/free Copyright 2024 Fonticons, Inc.--><path d="M575.8 255.5c0 18-15 32.1-32 32.1h-32l.7 160.2c0 2.7-.2 5.4-.5 8.1V472c0 22.1-17.9 40-40 40H456c-1.1 0-2.2 0-3.3-.1c-1.4 .1-2.8 .1-4.2 .1H416 392c-22.1 0-40-17.9-40-40V448 384c0-17.7-14.3-32-32-32H256c-17.7 0-32 14.3-32 32v64 24c0 22.1-17.9 40-40 40H160 128.1c-1.5 0-3-.1-4.5-.2c-1.2 .1-2.4 .2-3.6 .2H104c-22.1 0-40-17.9-40-40V360c0-.9 0-1.9 .1-2.8V287.6H32c-18 0-32-14-32-32.1c0-9 3-17 10-24L266.4 8c7-7 15-8 22-8s15 2 21 7L564.8 231.5c8 7 12 15 11 24z"/></svg>
	</div>	
	<div class="wrap">
		<h2>Edit Rent Review</h2>
	</div>
</div>

<div class="block">

<?php
// define variables and set to empty values

$Q = explode("/", $_SERVER['QUERY_STRING']);
parse_str($Q[0],$QueryParameters);
$QPrentreviewid = $QueryParameters['rentreviewid'];

$sql = "SELECT * from rentreviews WHERE idrentreview = $QPrentreviewid and recordOwnerID IN ($accessto)";
$result = $con->query($sql);

if ($result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        $rentreviewtypeid = $row["rentReviewTypeID"];
        $rentreviewdetails = $row["rentReviewDetails"];
        $leaseid = $row["leaseID"];
    } 
} else {
    $rentreviewtypeid = $rentreviewdetails = "";
}
$sql2 = "SELECT * from rentreviewtype";
$result2 = $con->query($sql2);

$sql3 = "SELECT unitname, premisesaddress1, tenantname from leases_view where idlease = $leaseid and recordOwnerID IN ($accessto) ORDER BY unitname";
$result3 = $con->query($sql3);

$sql4 = "SELECT * from leases_view WHERE idlease = $leaseid";
$result4 = $con->query($sql4);
while($row4 = $result4->fetch_assoc()) {
    $tenantname = $row4["tenantname"];
    $unitname = $row4["unitname"];
    $premisesaddress = $row4["premisesaddress1"];
}


    $rentreviewtypeidErr = $rentreviewdetailsErr = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {

  if (empty($_POST["rentreviewdetails"])) {
    $brentreviewdetailsErr = "Rent review details are required";
  } else {
    $rentreviewdetails = test_input($_POST["rentreviewdetails"]);
    //check if the field only contains letters dash or white space
    if (!preg_match("/^[a-zA-Z-0-9āēīōūĀĒĪŌŪ()%,.'\/ ]*$/", $rentreviewdetails)) {
        $rentreviewdetailsErr = "Only letters, dash and spaces allowed";
    }
  }

    $rentreviewtypeid = test_input($_POST["rentreviewtypeid"]);
    if (!preg_match("/^[0-9' ]*$/", $rentreviewtypeid)) {
        $rentreviewtypeidErr = "Only numbers allowed";
    }
}

function test_input($data) {
  $data = trim($data);
  $data = stripslashes($data);
  $data = htmlspecialchars($data);
  return $data;
}


if ($_SERVER["REQUEST_METHOD"] == "POST" and $rentreviewtypeidErr == NULL and $rentreviewdetailsErr == NULL) {

    //prepare and bind
$sql4 = "UPDATE rentreviews SET rentReviewTypeID = '$rentreviewtypeid', rentReviewDetails = '$rentreviewdetails' WHERE idrentreview = $QPrentreviewid";

    if ($con->query($sql4) === TRUE) {
        echo '<table class="table table-hover">
        <tbody>
            <tr class="success">
                <td>Success!</td>
            </tr>
        </tbody>
        </table>';

        echo "<div class=\"row\">
           <div class=\"col-sm-2\"><a href=\"viewlease.php?leaseid=".$leaseid."\" class=\"btn btn-primary\">Back to Lease</a></div>
        </div>
        <div class=\"row\">";
    } else {
    echo 'Error updating record: ' . $con->error;
}
} else {
    ?>
    <form class="form" method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"].'?rentreviewid='.$QPrentreviewid.'&leaseid=');?>">
    <div class="form-group">
        <label class="form-label col-sm-2">Tenant Name:</label>
        <div class="col-sm-3"><input class="form-control" id="tenantname" type="text" name="tenantname" value="<?php echo $tenantname;?>" readonly></div>
        <div class="col-sm-7"></div>
    </div>
    <div class="form-group" style="padding-bottom:40px;">
        <label class="form-label col-sm-2">Premises:</label>
        <div class="col-sm-3"><input class="form-control" id="tenantname" type="text" name="tenantname" value="<?php echo $unitname.", ".$premisesaddress;?>" readonly></div>
        <div class="col-sm-7"></div>
    </div>

    <div class="form-group">
        <label class="form-label col-sm-2" for="rentreviewtypeid">Rent Review Type: <span class="text-danger">*</span></label>
        <div class="col-sm-3">
            <select class="form-control" id="rentreviewtypeid" name="rentreviewtypeid">
            <?php
                echo "<option value=\"\"> - Select a rent review type - </option>";
            while($row2 = $result2->fetch_assoc()) {
                if($row2["idrentreviewtype"] == $rentreviewtypeid){
                    echo "<option value=\"" . $row2["idrentreviewtype"] . "\" selected>". $row2["rentReviewTypeName"] . "</option>";
                } else {
                    echo "<option value=\"" . $row2["idrentreviewtype"] . "\">". $row2["rentReviewTypeName"] . "</option>";
                }
                
            }
            ?>
            </select>
        </div>
        <div class="col-sm-7"><span class="error"><span class="text-danger"><?php echo $rentreviewtypeidErr;?></span></div>
    </div>
    <div  class="form-group">
        <label class="form-label col-sm-2" for="rentreviewdetails">Details: <span class="text-danger">*</span></label>
        <div class="col-sm-7"><input class="form-control" id="rentreviewdetails" type="text" name="rentreviewdetails" value="<?php echo $rentreviewdetails;?>"></div>
        <div class="col-sm-3"><span class="error"><span class="text-danger"><?php echo $rentreviewdetailsErr;?></span></div>
    </div>
    
    
    
    <div class="form-group">
        <div class="col-sm-1" style="padding-top:40px;"><input type="submit" value="Submit" class="btn btn-primary" style="width:100px"></div>
    </div>
    </form>



<div class="row">
<?php
}

$con->close();
?>




</div>

<?=template_footer()?>
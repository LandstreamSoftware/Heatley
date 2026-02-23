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

$sql9 = "SELECT * FROM accounts WHERE id = $accountid";
$result9 = $con->query($sql9);

while($row9 = $result9->fetch_assoc()) {
    $recordownerid = $row9["companyID"];
}
?>

<?=template_header('Add Building')?>

<div class="page-title">
	<div class="icon">
		<svg width="20" height="20" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 576 512"><!--!Font Awesome Free 6.5.1 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license/free Copyright 2024 Fonticons, Inc.--><path d="M575.8 255.5c0 18-15 32.1-32 32.1h-32l.7 160.2c0 2.7-.2 5.4-.5 8.1V472c0 22.1-17.9 40-40 40H456c-1.1 0-2.2 0-3.3-.1c-1.4 .1-2.8 .1-4.2 .1H416 392c-22.1 0-40-17.9-40-40V448 384c0-17.7-14.3-32-32-32H256c-17.7 0-32 14.3-32 32v64 24c0 22.1-17.9 40-40 40H160 128.1c-1.5 0-3-.1-4.5-.2c-1.2 .1-2.4 .2-3.6 .2H104c-22.1 0-40-17.9-40-40V360c0-.9 0-1.9 .1-2.8V287.6H32c-18 0-32-14-32-32.1c0-9 3-17 10-24L266.4 8c7-7 15-8 22-8s15 2 21 7L564.8 231.5c8 7 12 15 11 24z"/></svg>
	</div>	
	<div class="wrap">
		<h2>Add Building</h2>
<!--		<p>Welcome back, <?=htmlspecialchars($_SESSION['account_name'], ENT_QUOTES)?>!</p>  -->
	</div>
</div>

<div class="block">    

<?php
    // define variables and set to empty values
$buildingname = $buildingaddress1 = $buildingaddress2 = $buildingsuburb = $buildingcity = $buildingpostcode = $buildingownerid = "";
$buildingnameErr = $buildingaddress1Err = $buildingaddress2Err = $buildingsuburbErr = $buildingcityErr = $buildingpostcodeErr = $buildingowneridErr = "";

$Q = explode("/", $_SERVER['QUERY_STRING']);
parse_str($Q[0],$QueryParameters);
if(empty($QueryParameters['buildingid'])){
    $QPbuildingid = "";
}else{
    $QPbuildingid = $QueryParameters['buildingid'];
    $buildingid = $QueryParameters['buildingid'];
}
$opexstatusid = 1;

$sql1 = "SELECT * FROM companies  where companyTypeID = 5 and  recordOwnerID IN ($accessto) ORDER BY companyName";
    $result1 = $con->query($sql1);

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (empty($_POST["buildingname"])) {
        $buildingnameErr = "Building Name is required";
    } else {
        $buildingname = test_input($_POST["buildingname"]);
        //check if the field only contains letters dash or white space
        if (!preg_match("/^[a-zA-Z-0-9' ]*$/", $buildingname)) {
            $buildingnameErr = "Only letters, dash and spaces allowed";
        }
    }

    if (empty($_POST["buildingaddress1"])) {
        $buildingaddress1Err = "Building Address 1 is required";
      } else {
        $buildingaddress1 = test_input($_POST["buildingaddress1"]);
        //check if the field only contains letters dash or white space
        if (!preg_match("/^[a-zA-Z-0-9āēīōūĀĒĪŌŪ' ]*$/", $buildingaddress1)) {
            $buildingaddress1Err = "Only letters, dash and spaces allowed";
        }
      }
    
        $buildingaddress2 = test_input($_POST["buildingaddress2"]);
        //check if the field only contains letters dash or white space
        if (!preg_match("/^[a-zA-Z-0-9āēīōūĀĒĪŌŪ' ]*$/", $buildingaddress2)) {
            $buildingaddress2Err = "Only letters, dash and spaces allowed";
        }
    
      if (empty($_POST["buildingsuburb"])) {
        $buildingsuburbErr = "Suburb is required";
      } else {
        $buildingsuburb = test_input($_POST["buildingsuburb"]);
        //check if the field only contains letters dash or white space
        if (!preg_match("/^[a-zA-Z-0-9āēīōūĀĒĪŌŪ' ]*$/", $buildingsuburb)) {
            $buildingsuburbErr = "Only letters, dash and spaces allowed";
        }
      }
    
      if (empty($_POST["buildingcity"])) {
        $buildingcityErr = "Town/City is required";
      } else {
        $buildingcity = test_input($_POST["buildingcity"]);
        //check if the field only contains letters dash or white space
        if (!preg_match("/^[a-zA-Z-0-9āēīōūĀĒĪŌŪ' ]*$/", $buildingcity)) {
            $buildingcityErr = "Only letters, dash and spaces allowed";
        }
      }
      
      if (empty($_POST["buildingpostcode"])) {
        $buildingpostcodeErr = "Post Code is required";
      } else {
        $buildingpostcode = test_input($_POST["buildingpostcode"]);
        //check if the field only contains numbers
        if (!preg_match("/^[0-9' ]*$/", $buildingpostcode)) {
            $buildingpostcodeErr = "Only numbers allowed";
        }
      }

        $buildingownerid = test_input($_POST["buildingownerid"]);
        //check if the field only contains numbers
        if (!preg_match("/^[0-9' ]*$/", $buildingownerid)) {
            $buildingowneridErr = "Only numbers allowed";
        }
}

function test_input($data) {
  $data = trim($data);
  $data = stripslashes($data);
  $data = htmlspecialchars($data);
  return $data;
}



if ($_SERVER["REQUEST_METHOD"] == "POST" and $buildingnameErr == NULL and $buildingaddress1Err == NULL and $buildingaddress2Err == NULL and $buildingsuburbErr == NULL and $buildingcityErr == NULL and $buildingpostcodeErr == NULL and $buildingowneridErr == NULL) {

    //prepare and bind
    $stmt = $con->prepare("INSERT INTO buildings (buildingName, buildingAddress1, buildingAddress2, buildingSuburb, buildingCity, buildingPostCode, buildingOwnerID, recordOwnerID) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("sssssiii", $buildingname, $buildingaddress1, $buildingaddress2, $buildingsuburb, $buildingcity, $buildingpostcode, $buildingownerid, $buildingownerid);

    if ($stmt->execute()) {
        echo '<div class=\"row\">
        <table class="table table-hover">
            <tbody>
                <tr class="success">
                    <td>Success!</td>
                </tr>
            </tbody>
        </table>';
    } else {
        echo 'Error creating record: ' . $con->error;
    }

    echo "<div class=\"row\">
            <div class=\"col-sm-2\"><a href=\"listbuildings.php\" class=\"btn btn-primary\">Back to BUILDINGS</a></div>
        </div>";
    

} else {

    ?>
    <form class="form form-medium" method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"].'?buildingid='.$QPbuildingid);?>">
    <div  class="form-group">
        <label class="form-label col-sm-4" for="buildingname" style="padding-top:5px">Building Name: <span class="text-danger">*</span></label>
        <div class="col-sm-6"><input class="form-control" type="text" name="buildingname" value="<?php echo $buildingname;?>"></div>
        <div class="col-sm-2"><span class="error"><span class="text-danger"><?php echo $buildingnameErr;?></span></div>
    </div>
    <div  class="form-group">
        <label class="form-label col-sm-4" for="buildingaddress1" style="padding-top:5px">Building Address 1: <span class="text-danger">*</span></label>
        <div class="col-sm-6"><input class="form-control" type="text" name="buildingaddress1" value="<?php echo $buildingaddress1;?>"></div>
        <div class="col-sm-2"><span class="error"><span class="text-danger"><?php echo $buildingaddress1Err;?></span></div>
    </div>
    <div  class="form-group">
        <label class="form-label col-sm-4" for="buildingaddress2" style="padding-top:5px">Building Address 2:</label>
        <div class="col-sm-6"><input class="form-control" type="text" name="buildingaddress2" value="<?php echo $buildingaddress2;?>"></div>
        <div class="col-sm-2"><span class="error"><span class="text-danger"><?php echo $buildingaddress2Err;?></span></div>
    </div>
    <div  class="form-group">
        <label class="form-label col-sm-4" for="buildingsuburb" style="padding-top:5px">Suburb: <span class="text-danger">*</span></label>
        <div class="col-sm-6"><input class="form-control" type="text" name="buildingsuburb" value="<?php echo $buildingsuburb;?>"></div>
        <div class="col-sm-2"><span class="error"><span class="text-danger"><?php echo $buildingsuburbErr;?></span></div>
    </div>
    <div  class="form-group">
        <label class="form-label col-sm-4" for="buildingcity" style="padding-top:5px">City: <span class="text-danger">*</span></label>
        <div class="col-sm-6"><input class="form-control" type="text" name="buildingcity" value="<?php echo $buildingcity;?>"></div>
        <div class="col-sm-2"><span class="error"><span class="text-danger"><?php echo $buildingcityErr;?></span></div>
    </div>
    <div  class="form-group">
        <label class="form-label col-sm-4" for="buildingpostcode" style="padding-top:5px">Post Code: <span class="text-danger">*</span></label>
        <div class="col-sm-6"><input class="form-control" type="text" name="buildingpostcode" value="<?php echo $buildingpostcode;?>"></div>
        <div class="col-sm-2"><span class="error"><span class="text-danger"><?php echo $buildingpostcodeErr;?></span></div>
    </div>
    <div class="form-group">
        <label class="form-label col-sm-4" for="buildingownerid" style="padding-top:5px">Building Owner: <span class="text-danger">*</span></label>
        <div class="col-sm-6">
            <select class="form-control" id="buildingownerid" name="buildingownerid">
            <?php
                echo "<option value=\"\"> - Select a Company - </option>";
            while($row = $result1->fetch_assoc()) {
                echo "<option value=\"" . $row["idcompany"] . "\">". $row["companyName"] . "</option>";
            }
            ?>
            </select>
        </div>
        <div class="col-sm-2"><span class="error"><span class="text-danger"><?php echo $buildingowneridErr;?></span></div>
    </div>

    <div class="row">
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
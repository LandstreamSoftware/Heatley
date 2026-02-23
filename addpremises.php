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

//while($rowuser = $resultuser->fetch_assoc()) {
//    $recordownerid = $rowuser["companyID"]; 
//}
?>

<?=template_header('Add Premises')?>

<div class="page-title">
	<div class="icon">
		<svg width="20" height="20" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 576 512"><!--!Font Awesome Free 6.5.1 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license/free Copyright 2024 Fonticons, Inc.--><path d="M575.8 255.5c0 18-15 32.1-32 32.1h-32l.7 160.2c0 2.7-.2 5.4-.5 8.1V472c0 22.1-17.9 40-40 40H456c-1.1 0-2.2 0-3.3-.1c-1.4 .1-2.8 .1-4.2 .1H416 392c-22.1 0-40-17.9-40-40V448 384c0-17.7-14.3-32-32-32H256c-17.7 0-32 14.3-32 32v64 24c0 22.1-17.9 40-40 40H160 128.1c-1.5 0-3-.1-4.5-.2c-1.2 .1-2.4 .2-3.6 .2H104c-22.1 0-40-17.9-40-40V360c0-.9 0-1.9 .1-2.8V287.6H32c-18 0-32-14-32-32.1c0-9 3-17 10-24L266.4 8c7-7 15-8 22-8s15 2 21 7L564.8 231.5c8 7 12 15 11 24z"/></svg>
	</div>	
	<div class="wrap">
		<h2>Add Premises</h2>
<!--		<p>Welcome back, <?=htmlspecialchars($_SESSION['account_name'], ENT_QUOTES)?>!</p>  -->
	</div>
</div>

<div class="block">    

<?php
    // define variables and set to empty values
$buildingid = $unitname = $premisesaddress1 = $premisesaddress2 = $premisessuburb = $premisescity = $premisespostcode = "";
$floorarea = $opexpercentage = $managementfeepercentage = '0.00';
$buildingidErr = $unitnameErr = $floorareaErr = $premisesaddress1Err = $premisesaddress2Err = $premisessuburbErr = $premisescityErr = $premisespostcodeErr = $opexpercentageErr = $managementfeepercentageErr = "";

$Q = explode("/", $_SERVER['QUERY_STRING']);
parse_str($Q[0],$QueryParameters);
if(empty($QueryParameters['premisesid'])){
    $QPpremisesid = "";
}else{
    $QPpremisesid = $QueryParameters['premisesid'];
    $premisesid = $QueryParameters['premisesid'];
}

//Query for dropdowns
$sql2 = "SELECT idbuildings, buildingName, buildingOwnerID FROM buildings WHERE recordOwnerID IN ($accessto) ORDER BY buildingName";
    $result2 = $con->query($sql2);

if ($_SERVER["REQUEST_METHOD"] == "POST") {
  if (empty($_POST["buildingid"])) {
    $buildingidErr = "Building is required";
  } else {
    $buildingid = test_input($_POST["buildingid"]);
    //check if the field only contains numbers
    if (!preg_match("/^[0-9' ]*$/", $buildingid)) {
      $buildingidErr = "Only numbers allowed";
    }
    $sql3 = "SELECT idbuildings, buildingOwnerID FROM buildings WHERE idbuildings = $buildingid";
    $resultrecordownler = $con->query($sql3);
    while($rowrecordowner = $resultrecordownler->fetch_assoc()) {
      $recordownerid = $rowrecordowner["buildingOwnerID"]; 
    }
  }  

  if (empty($_POST["unitname"])) {
    $unitnameErr = "Unit Name is required";
  } else {
    $unitname = test_input($_POST["unitname"]);
    //check if the field only contains letters dash or white space
    if (!preg_match("/^[a-zA-Z-0-9&-āēīōūĀĒĪŌŪ\/' ]*$/", $unitname)) {
      $unitnameErr = "Only letters, dash and spaces allowed";
    }
  }

  if (empty($_POST["floorarea"])) {
    $floorareaErr = "Floor Area is required";
  } else {
    $floorarea = test_input($_POST["floorarea"]);
    //check if the field only contains letters dash or white space
    if (!preg_match("/^[0-9.' ]*$/", $floorarea)) {
      $floorareaErr = "Only numbers and dot allowed";
    }
  }

  if (empty($_POST["premisesaddress1"])) {
    $premisesaddress1Err = "Address is required";
  } else {
    $premisesaddress1 = test_input($_POST["premisesaddress1"]);
    //any characters are allowed in the address field
  }

  $premisesaddress2 = test_input($_POST["premisesaddress2"]);
    //any characters are allowed in the address field

  if (empty($_POST["premisessuburb"])) {
    $premisessuburbErr = "Name is required";
  } else {
    $premisessuburb = test_input($_POST["premisessuburb"]);
    //check if the field only contains letters dash or white space
    if (!preg_match("/^[a-zA-Z-0-9āēīōūĀĒĪŌŪ' ]*$/", $premisessuburb)) {
      $premisessuburbErr = "Only letters, dash and spaces allowed";
    }
  }

  if (empty($_POST["premisescity"])) {
    $premisescityErr = "Town/City is required";
  } else {
    $premisescity = test_input($_POST["premisescity"]);
    //check if the field only contains letters dash or white space
    if (!preg_match("/^[a-zA-Z-0-9āēīōūĀĒĪŌŪ' ]*$/", $premisescity)) {
      $premisescityErr = "Only letters, dash and spaces allowed";
    }
  }

  if (empty($_POST["premisespostcode"])) {
    $premisespostcodeErr = "Post Code is required";
  } else {
    $premisespostcode = test_input($_POST["premisespostcode"]);
    //check if the field only contains letters dash or white space
    if (!preg_match("/^[0-9' ]*$/", $premisespostcode)) {
      $premisespostcodeErr = "Only numbers allowed";
    }
  }

  if (empty($_POST["opexpercentage"])) {
    $opexpercentageErr = "OPEX Percentage is required";
  } else {
    $opexpercentage = test_input($_POST["opexpercentage"]);
    //check if the field only contains letters dash or white space
    if (!preg_match("/^[0-9.' ]*$/", $opexpercentage)) {
      $opexpercentageErr = "Only numbers and dot allowed";
    }
  }

  $managementfeepercentage = test_input($_POST["managementfeepercentage"]);
  //check if the field only contains letters dash or white space
  if (!preg_match("/^[0-9.' ]*$/", $managementfeepercentage)) {
      $managementfeepercentageErr = "Only numbers and dot allowed";
  }
}

function test_input($data) {
  $data = trim($data);
  $data = stripslashes($data);
  $data = htmlspecialchars($data);
  return $data;
}

if ($_SERVER["REQUEST_METHOD"] == "POST" and $buildingidErr == NULL and $unitnameErr == NULL and $floorareaErr == NULL and $premisesaddress1Err == NULL and $premisesaddress2Err == NULL and $premisessuburbErr == NULL and $premisescityErr == NULL and $premisespostcodeErr == NULL) {

  //prepare and bind
  $stmt = $con->prepare("INSERT INTO premises (buildingID, unitName, floorArea, premisesAddress1, premisesAddress2, premisesSuburb, premisesCity, premisesPostCode, opexPercentage, managementFeePercent, recordOwnerID) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
  $stmt->bind_param("dsdsssssddi", $buildingid, $unitname, $floorarea, $premisesaddress1, $premisesaddress2, $premisessuburb, $premisescity, $premisespostcode, $opexpercentage, $managementfeepercentage, $recordownerid);

  if ($stmt->execute()) {
    echo '<table class="table table-hover">
     <tbody>
        <tr class="success">
          <td>Success!</td>
        </tr>
     </tbody>
    </table>';

    echo "<div class=\"row\">
      <div class=\"col-sm-2\"><a href=\"listpremises.php\" class=\"btn btn-primary\">Back to Premises</a></div>
     </div>";
  } else {
    echo 'Error creating record: ' . $con->error;
  }

} else {

?>
    <form class="form form-medium" method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]);?>">
    <div class="form-group">
        <label class="form-label col-sm-4" for="buildingid" style="padding-top:5px">Building: <span class="text-danger">*</span></label>
        <div class="col-sm-6">
            <select class="form-control" id="buildingid" name="buildingid">
            <?php
                echo "<option value=\"\"> - Select a building - </option>";
            while($row = $result2->fetch_assoc()) {
                echo "<option value=\"" . $row["idbuildings"] . "\">". $row["buildingName"] . "</option>";
            }
            ?>
            </select>
        </div>
        <div class="col-sm-2"><span class="error"><span class="text-danger"><?php echo $buildingidErr;?></span></div>
    </div>
    <div class="form-group">
        <label class="form-label col-sm-4" for="floorarea" style="padding-top:5px">Floor Area: <span class="text-danger">*</span></label>
        <div class="col-sm-6"><input class="form-control" id="floorarea" type="text" name="floorarea" value="<?php echo $floorarea;?>"></div>
        <div class="col-sm-2"><span class="error"><span class="text-danger"><?php echo $floorareaErr;?></span></div>
    </div>
    <div class="form-group">
        <label class="form-label col-sm-4" for="unitname" style="padding-top:5px">Unit Name: <span class="text-danger">*</span></label>
        <div class="col-sm-6"><input class="form-control" id="unitname" type="text" name="unitname" value="<?php echo $unitname;?>"></div>
        <div class="col-sm-2"><span class="error"><span class="text-danger"><?php echo $unitnameErr;?></span></div>
    </div>
    <div class="form-group">
        <label class="form-label col-sm-4" for="premisesaddress1" style="padding-top:5px">Address 1: <span class="text-danger">*</span></label>
        <div class="col-sm-6"><input class="form-control" id="premisesaddress1" type="text" name="premisesaddress1" value="<?php echo $premisesaddress1;?>"></div>
        <div class="col-sm-2"><span class="error"><span class="text-danger"><?php echo $premisesaddress1Err;?></span></div>
    </div>
    <div class="form-group">
        <label class="form-label col-sm-4" for="premisesaddress2" style="padding-top:5px">Address 2:</label>
        <div class="col-sm-6"><input class="form-control" id="premisesaddress2" type="text" name="premisesaddress2" value="<?php echo $premisesaddress2;?>"></div>
        <div class="col-sm-2"><span class="error"><span class="text-danger"><?php echo $premisesaddress2Err;?></span></div>
    </div>
    <div class="form-group">
        <label class="form-label col-sm-4" for="premisessuburb" style="padding-top:5px">Suburb: <span class="text-danger">*</span></label>
        <div class="col-sm-6"><input class="form-control" id="premisessuburb" type="text" name="premisessuburb" value="<?php echo $premisessuburb;?>"></div>
        <div class="col-sm-2"><span class="error"><span class="text-danger"><?php echo $premisessuburbErr;?></span></div>
    </div>
    <div class="form-group">
        <label class="form-label col-sm-4" for="premisescity" style="padding-top:5px">City: <span class="text-danger">*</span></label>
        <div class="col-sm-6"><input class="form-control" id="premisescity" type="text" name="premisescity" value="<?php echo $premisescity;?>"></div>
        <div class="col-sm-2"><span class="error"><span class="text-danger"><?php echo $premisescityErr;?></span></div>
    </div>
    <div class="form-group">
        <label class="form-label col-sm-4" for="premisespostcode" style="padding-top:5px">Post Code: <span class="text-danger">*</span></label>
        <div class="col-sm-6"><input class="form-control" id="premisespostcode" type="text" name="premisespostcode" value="<?php echo $premisespostcode;?>"></div>
        <div class="col-sm-2"><span class="error"><span class="text-danger"><?php echo $premisespostcodeErr;?></span></div>
    </div>
    <div class="form-group">
        <label class="form-label col-sm-4" for="opexpercentage" style="padding-top:5px">OPEX Percentage: <span class="text-danger">*</span></label>
        <div class="col-sm-6"><input class="form-control" id="opexpercentage" type="text" name="opexpercentage" value="<?php echo $opexpercentage;?>"></div>
        <div class="col-sm-2"><span class="error"><span class="text-danger"><?php echo $opexpercentageErr;?></span></div>
    </div>
    <div class="form-group">
        <label class="form-label col-sm-4" for="managementfeepercentage">Management Fee Percentage: <span class="text-danger">*</span></label>
        <div class="col-sm-6"><input class="form-control" id="managementfeepercentage" type="text" name="managementfeepercentage" value="<?php echo $managementfeepercentage;?>"></div>
        <div class="col-sm-2"><span class="error"><span class="text-danger"><?php echo $opexpercentageErr;?></span></div>
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


<?=template_footer()?>
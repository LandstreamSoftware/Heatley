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

<?=template_header('Edit Premises')?>

<div class="page-title">
	<div class="icon">
		<svg width="20" height="20" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 576 512"><!--!Font Awesome Free 6.5.1 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license/free Copyright 2024 Fonticons, Inc.--><path d="M575.8 255.5c0 18-15 32.1-32 32.1h-32l.7 160.2c0 2.7-.2 5.4-.5 8.1V472c0 22.1-17.9 40-40 40H456c-1.1 0-2.2 0-3.3-.1c-1.4 .1-2.8 .1-4.2 .1H416 392c-22.1 0-40-17.9-40-40V448 384c0-17.7-14.3-32-32-32H256c-17.7 0-32 14.3-32 32v64 24c0 22.1-17.9 40-40 40H160 128.1c-1.5 0-3-.1-4.5-.2c-1.2 .1-2.4 .2-3.6 .2H104c-22.1 0-40-17.9-40-40V360c0-.9 0-1.9 .1-2.8V287.6H32c-18 0-32-14-32-32.1c0-9 3-17 10-24L266.4 8c7-7 15-8 22-8s15 2 21 7L564.8 231.5c8 7 12 15 11 24z"/></svg>
	</div>	
	<div class="wrap">
		<h2>Edit Premises</h2>
<!--		<p>Welcome back, <?=htmlspecialchars($_SESSION['account_name'], ENT_QUOTES)?>!</p>  -->
	</div>
</div>

<div class="block">

<?php
// define variables and set to empty values

$Q = explode("/", $_SERVER['QUERY_STRING']);
parse_str($Q[0],$QueryParameters);
$QPpremisesid = $QueryParameters['premisesid'];

$sql = "SELECT * from premises WHERE idpremises = $QPpremisesid and recordOwnerID IN ($accessto)";
$result = $con->query($sql);

$sql2 = "SELECT * from buildings WHERE recordOwnerID IN ($accessto)";
$result2 = $con->query($sql2);

if ($result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        $buildingid = $row["buildingID"];
        $unitname = $row["unitName"];
        $floorarea = $row["floorArea"];
        $premisesaddress1 = $row["premisesAddress1"];
        $premisesaddress2 = $row["premisesAddress2"];
        $premisessuburb = $row["premisesSuburb"];
        $premisescity = $row["premisesCity"];
        $premisespostcode = $row["premisesPostCode"];
        $opexpercentage = $row["opexPercentage"];
        $managementfeepercentage = $row["managementFeePercent"];
        $notes = $row["notes"];
        $ratesurl = $row["ratesURL"];
    }
} else {
    $buildingid = $unitname = $floorarea = $premisesaddress1 = $premisesaddress2 = $premisessuburb = $premisescity = $premisespostcode = $opexpercentage = $managementfeepercentage = $notes = $ratesurl = "";
}
    $buildingidErr = $unitnameErr = $floorareaErr = $premisesaddress1Err = $premisesaddress2Err = $premisessuburbErr = $premisescityErr = $premisespostcodeErr = $opexpercentageErr = $managementfeepercentageErr = $notesErr = $ratesurlErr = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    if (empty($_POST["buildingid"])) {
        $buildingidErr = "Building is required";
    } else {
        $buildingid = test_input($_POST["buildingid"]);
    //check if the field only contains numbers
    if (!preg_match("/^[0-9' ]*$/", $buildingid)) {
        $buildingidErr = "Only numbers allowed";
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
    $premisessuburbErr = "Suburb is required";
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

    $notes = test_input($_POST["notes"]);
    //check if the field only contains letters dash or white space
    if (!preg_match("/^[a-zA-Z-0-9āēīōūĀĒĪŌŪ:.' ]*$/", $notes)) {
        $notesErr = "Only letters, dash and spaces allowed";
    }

    $ratesurl = test_input($_POST["ratesurl"]);
    //check if the field only contains letters dash or white space
    if (!preg_match("/^[A-Za-z0-9:;\/?&=._%$-]*$/", $ratesurl)) {
        $ratesurlErr = "Disallowed characters used";
    }
}

function test_input($data) {
  $data = trim($data);
  $data = stripslashes($data);
  $data = htmlspecialchars($data);
  return $data;
}


if ($_SERVER["REQUEST_METHOD"] == "POST" and $buildingidErr == NULL and $unitnameErr == NULL and $floorareaErr == NULL and $premisesaddress1Err == NULL and $premisesaddress2Err == NULL and $premisessuburbErr == NULL and $premisescityErr == NULL and $premisespostcodeErr == NULL and $opexpercentageErr == NULL and $managementfeepercentageErr == NULL and $notesErr == NULL and $ratesurlErr == NULL) {

    //prepare and bind
$sql3 = "UPDATE premises SET buildingID = '$buildingid', unitName = '$unitname', floorArea = '$floorarea', premisesAddress1 = '$premisesaddress1', premisesAddress2 = '$premisesaddress2', premisesSuburb = '$premisessuburb', premisesCity = '$premisescity', premisesPostCode = '$premisespostcode', opexPercentage = '$opexpercentage', managementFeePercent = '$managementfeepercentage', notes = '$notes', ratesURL = '$ratesurl' WHERE idpremises=$QPpremisesid";

if ($con->query($sql3) === TRUE) {
   echo '<table class="table table-hover">
        <tbody>
            <tr class="success">
                <td>Success!</td>
            </tr>
        </tbody>
    </table>';

    echo "<div class=\"row\">
           <div class=\"col-sm-2\"><a href=\"listpremises.php\" class=\"btn btn-primary\">Back to Premises</a></div>
        </div>
        <div class=\"row\">";
} else {
    echo 'Error updating record: ' . $con->error;
}
} else {
    ?>
    <form class="form form-medium" method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"].'?premisesid='.$QPpremisesid);?>">
    <div class="form-group">
        <label class="form-label col-sm-4" for="buildingid">Building: <span class="text-danger">*</span></label>
        <div class="col-sm-6">
            <select class="form-control" id="buildingid" name="buildingid">
            <?php
                echo "<option value=\"\"> - Select a building - </option>";
            while($row = $result2->fetch_assoc()) {
                if($row["idbuildings"] === $buildingid){
                    echo "<option value=\"" . $row["idbuildings"] . "\" selected>". $row["buildingName"] . "</option>";
                } else {
                    echo "<option value=\"" . $row["idbuildings"] . "\">". $row["buildingName"] . "</option>";
                }
            }
            ?>
            </select>
        </div>
        <div class="col-sm-2"><span class="error"><span class="text-danger"><?php echo $buildingidErr;?></span></div>
    </div>
    <div class="form-group">
        <label class="form-label col-sm-4" for="unitname">Unit Name: <span class="text-danger">*</span></label>
        <div class="col-sm-6"><input class="form-control" id="unitname" type="text" name="unitname" value="<?php echo $unitname;?>"></div>
        <div class="col-sm-2"><span class="error"><span class="text-danger"><?php echo $unitnameErr;?></span></div>
    </div>
    <div class="form-group">
        <label class="form-label col-sm-4" for="premisesaddress1">Address 1: <span class="text-danger">*</span></label>
        <div class="col-sm-6"><input class="form-control" id="premisesaddress1" type="text" name="premisesaddress1" value="<?php echo $premisesaddress1;?>"></div>
        <div class="col-sm-2"><span class="error"><span class="text-danger"><?php echo $premisesaddress1Err;?></span></div>
    </div>
    <div class="form-group">
        <label class="form-label col-sm-4" for="premisesaddress2">Address 2:</label>
        <div class="col-sm-6"><input class="form-control" id="premisesaddress2" type="text" name="premisesaddress2" value="<?php echo $premisesaddress2;?>"></div>
        <div class="col-sm-2"><span class="error"><span class="text-danger"><?php echo $premisesaddress2Err;?></span></div>
    </div>
    <div class="form-group">
        <label class="form-label col-sm-4" for="premisessuburb">Suburb: <span class="text-danger">*</span></label>
        <div class="col-sm-6"><input class="form-control" id="premisessuburb" type="text" name="premisessuburb" value="<?php echo $premisessuburb;?>"></div>
        <div class="col-sm-2"><span class="error"><span class="text-danger"><?php echo $premisessuburbErr;?></span></div>
    </div>
    <div class="form-group">
        <label class="form-label col-sm-4" for="premisescity">City: <span class="text-danger">*</span></label>
        <div class="col-sm-6"><input class="form-control" id="premisescity" type="text" name="premisescity" value="<?php echo $premisescity;?>"></div>
        <div class="col-sm-2"><span class="error"><span class="text-danger"><?php echo $premisescityErr;?></span></div>
    </div>
    <div class="form-group">
        <label class="form-label col-sm-4" for="premisespostcode">Post Code: <span class="text-danger">*</span></label>
        <div class="col-sm-6"><input class="form-control" id="premisespostcode" type="text" name="premisespostcode" value="<?php echo $premisespostcode;?>"></div>
        <div class="col-sm-2"><span class="error"><span class="text-danger"><?php echo $premisespostcodeErr;?></span></div>
    </div>
    <div class="form-group">
        <label class="form-label col-sm-4" for="floorarea">Floor Area (m<sup>2</sup>): <span class="text-danger">*</span></label>
        <div class="col-sm-6"><input class="form-control" id="floorarea" type="text" name="floorarea" value="<?php echo $floorarea;?>"></div>
        <div class="col-sm-2"><span class="error"><span class="text-danger"><?php echo $floorareaErr;?></span></div>
    </div>
    <div class="form-group">
        <label class="form-label col-sm-4" for="opexpercentage">OPEX Percentage: <span class="text-danger">*</span></label>
        <div class="col-sm-6"><input class="form-control" id="opexpercentage" type="text" name="opexpercentage" value="<?php echo $opexpercentage;?>"></div>
        <div class="col-sm-2"><span class="error"><span class="text-danger"><?php echo $opexpercentageErr;?></span></div>
    </div>
    <div class="form-group">
        <label class="form-label col-sm-4" for="managementfeepercentage">Management Fee Percentage: <span class="text-danger">*</span></label>
        <div class="col-sm-6"><input class="form-control" id="managementfeepercentage" type="text" name="managementfeepercentage" value="<?php echo $managementfeepercentage;?>"></div>
        <div class="col-sm-2"><span class="error"><span class="text-danger"><?php echo $opexpercentageErr;?></span></div>
    </div>
    <div class="form-group">
        <label class="form-label col-sm-4" for="notes">Notes:</label>
        <div class="col-sm-6"><input class="form-control" id="notes" type="text" name="notes" value="<?php echo $notes;?>"></div>
        <div class="col-sm-2"><span class="error"><span class="text-danger"><?php echo $notesErr;?></span></div>
    </div>
    <div class="form-group">
        <label class="form-label col-sm-4" for="notes">Link to rates details:</label>
        <div class="col-sm-6"><input class="form-control" id="ratesurl" type="text" name="ratesurl" value="<?php echo $ratesurl;?>"></div>
        <div class="col-sm-2"><span class="error"><span class="text-danger"><?php echo $ratesurlErr;?></span></div>
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
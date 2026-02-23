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

<?=template_header('Edit Building')?>

<div class="page-title">
	<div class="icon">
		<svg width="20" height="20" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 576 512"><!--!Font Awesome Free 6.5.1 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license/free Copyright 2024 Fonticons, Inc.--><path d="M575.8 255.5c0 18-15 32.1-32 32.1h-32l.7 160.2c0 2.7-.2 5.4-.5 8.1V472c0 22.1-17.9 40-40 40H456c-1.1 0-2.2 0-3.3-.1c-1.4 .1-2.8 .1-4.2 .1H416 392c-22.1 0-40-17.9-40-40V448 384c0-17.7-14.3-32-32-32H256c-17.7 0-32 14.3-32 32v64 24c0 22.1-17.9 40-40 40H160 128.1c-1.5 0-3-.1-4.5-.2c-1.2 .1-2.4 .2-3.6 .2H104c-22.1 0-40-17.9-40-40V360c0-.9 0-1.9 .1-2.8V287.6H32c-18 0-32-14-32-32.1c0-9 3-17 10-24L266.4 8c7-7 15-8 22-8s15 2 21 7L564.8 231.5c8 7 12 15 11 24z"/></svg>
	</div>	
	<div class="wrap">
		<h2>Edit Building</h2>
<!--		<p>Welcome back, <?=htmlspecialchars($_SESSION['account_name'], ENT_QUOTES)?>!</p>  -->
	</div>
</div>

<div class="block">

<?php
// define variables and set to empty values

$Q = explode("/", $_SERVER['QUERY_STRING']);
parse_str($Q[0],$QueryParameters);
$QPbuildingid = $QueryParameters['buildingid'];

$sql = "SELECT * from buildings WHERE idbuildings = $QPbuildingid and recordOwnerID IN ($accessto)";
$result = $con->query($sql);

$sql3 = "SELECT idcompany, companyName from companies where companyTypeID = 5 and recordOwnerID IN ($accessto) ORDER BY companyName";
$result3 = $con->query($sql3);

if ($result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        $buildingname = $row["buildingName"];
        $buildingaddress1 = $row["buildingAddress1"];
        $buildingaddress2 = $row["buildingAddress2"];
        $buildingsuburb = $row["buildingSuburb"];
        $buildingcity = $row["buildingCity"];
        $buildingpostcode = $row["buildingPostCode"];
        $buildingownerid = $row["buildingOwnerID"];
    } 
} else {
    $buildingname = $buildingaddress1 = $buildingaddress2 = $buildingsuburb = $buildingcity = $buildingpostcode = $buildingownerid = "";
}
    $buildingnameErr = $buildingaddress1Err = $buildingaddress2Err = $buildingsuburbErr = $buildingcityErr = $buildingpostcodeErr = $buildingowneridErr = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {

  if (empty($_POST["buildingname"])) {
    $buildingnameErr = "Building Name is required";
  } else {
    $buildingname = test_input($_POST["buildingname"]);
    //check if the field only contains letters dash or white space
    if (!preg_match("/^[a-zA-Z-0-9āēīōūĀĒĪŌŪ' ]*$/", $buildingname)) {
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
    //check if the field only contains letters dash or white space
    if (!preg_match("/^[0-9' ]*$/", $buildingpostcode)) {
        $buildingpostcodeErr = "Only numbers allowed";
    }
  }

    $buildingownerid = test_input($_POST["buildingownerid"]);
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
$sql2 = "UPDATE buildings SET buildingName = '$buildingname', buildingAddress1 = '$buildingaddress1', buildingAddress2 = '$buildingaddress2', buildingSuburb = '$buildingsuburb', buildingCity = '$buildingcity', buildingPostCode = '$buildingpostcode', buildingOwnerID = '$buildingownerid' WHERE idbuildings = $QPbuildingid";

    if ($con->query($sql2) === TRUE) {
        echo '<table class="table table-hover">
        <tbody>
            <tr class="success">
                <td>Success!</td>
            </tr>
        </tbody>
        </table>';

        echo "<div class=\"row\">
           <div class=\"col-sm-2\"><a href=\"listbuildings.php\" class=\"btn btn-primary\">Back to Buildings</a></div>
        </div>
        <div class=\"row\">";
    } else {
    echo 'Error updating record: ' . $con->error;
}
} else {
    ?>
    <form class="form form-medium" method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"].'?buildingid='.$QPbuildingid);?>">
    <div class="form-group">
        <label class="form-label col-sm-4" for="buildingname">Building Name: <span class="text-danger">*</span></label>
        <div class="col-sm-6"><input class="form-control" id="buildingname" type="text" name="buildingname" value="<?php echo $buildingname;?>"></div>
        <div class="col-sm-2"><span class="error"><span class="text-danger"><?php echo $buildingnameErr;?></span></div>
    </div>
    <div  class="form-group">
        <label class="form-label col-sm-4" for="buildingaddress1">Building Address 1: <span class="text-danger">*</span></label>
        <div class="col-sm-6"><input class="form-control" id="buildingaddress1" type="text" name="buildingaddress1" value="<?php echo $buildingaddress1;?>"></div>
        <div class="col-sm-2"><span class="error"><span class="text-danger"><?php echo $buildingaddress1Err;?></span></div>
    </div>
    <div  class="form-group">
        <label class="form-label col-sm-4" for="buildingaddress2">Building Address 2:</label>
        <div class="col-sm-6"><input class="form-control" id="buildingaddress2" type="text" name="buildingaddress2" value="<?php echo $buildingaddress2;?>"></div>
        <div class="col-sm-2"><span class="error"><span class="text-danger"><?php echo $buildingaddress2Err;?></span></div>
    </div>
    <div  class="form-group">
        <label class="form-label col-sm-4" for="buildingsuburb">Suburb: <span class="text-danger">*</span></label>
        <div class="col-sm-6"><input class="form-control" id="buildingsuburb" type="text" name="buildingsuburb" value="<?php echo $buildingsuburb;?>"></div>
        <div class="col-sm-2"><span class="error"><span class="text-danger"><?php echo $buildingsuburbErr;?></span></div>
    </div>
    <div  class="form-group">
        <label class="form-label col-sm-4" for="buildingcity">City: <span class="text-danger">*</span></label>
        <div class="col-sm-6"><input class="form-control" id="buildingcity" type="text" name="buildingcity" value="<?php echo $buildingcity;?>"></div>
        <div class="col-sm-2"><span class="error"><span class="text-danger"><?php echo $buildingcityErr;?></span></div>
    </div>
    <div  class="form-group">
        <label class="form-label col-sm-4" for="buildingpostcode">Post Code: <span class="text-danger">*</span></label>
        <div class="col-sm-6"><input class="form-control" id="buildingpostcode" type="text" name="buildingpostcode" value="<?php echo $buildingpostcode;?>"></div>
        <div class="col-sm-2"><span class="error"><span class="text-danger"><?php echo $buildingpostcodeErr;?></span></div>
    </div>
    <div class="form-group">
        <label class="form-label col-sm-4" for="buildingownerid">Building Owner: <span class="text-danger">*</span></label>
        <div class="col-sm-6">
            <select class="form-control" id="buildingownerid" name="buildingownerid">
            <?php
                echo "<option value=\"\"> - Select an owner - </option>";
            while($row3 = $result3->fetch_assoc()) {
                if($row3["idcompany"] == $buildingownerid){
                    echo "<option value=\"" . $row3["idcompany"] . "\" selected>". $row3["companyName"] . "</option>";
                } else {
                    echo "<option value=\"" . $row3["idcompany"] . "\">". $row3["companyName"] . "</option>";
                }
                
            }
            ?>
            </select>
        </div>
        <div class="col-sm-2"><span class="error"><span class="text-danger"><?php echo $buildingowneridErr;?></span></div>
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
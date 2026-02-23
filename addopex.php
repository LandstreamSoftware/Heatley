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

<?=template_header('Add OPEX')?>

<div class="page-title">
	<div class="icon">
		<svg width="20" height="20" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 576 512"><!--!Font Awesome Free 6.5.1 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license/free Copyright 2024 Fonticons, Inc.--><path d="M575.8 255.5c0 18-15 32.1-32 32.1h-32l.7 160.2c0 2.7-.2 5.4-.5 8.1V472c0 22.1-17.9 40-40 40H456c-1.1 0-2.2 0-3.3-.1c-1.4 .1-2.8 .1-4.2 .1H416 392c-22.1 0-40-17.9-40-40V448 384c0-17.7-14.3-32-32-32H256c-17.7 0-32 14.3-32 32v64 24c0 22.1-17.9 40-40 40H160 128.1c-1.5 0-3-.1-4.5-.2c-1.2 .1-2.4 .2-3.6 .2H104c-22.1 0-40-17.9-40-40V360c0-.9 0-1.9 .1-2.8V287.6H32c-18 0-32-14-32-32.1c0-9 3-17 10-24L266.4 8c7-7 15-8 22-8s15 2 21 7L564.8 231.5c8 7 12 15 11 24z"/></svg>
	</div>	
	<div class="wrap">
		<h2>Add OPEX</h2>
<!--		<p>Welcome back, <?=htmlspecialchars($_SESSION['account_name'], ENT_QUOTES)?>!</p>  -->
	</div>
</div>

<div class="block">     

<?php
    // define variables and set to empty values
$opexdate = $buildingid = $opexstatusid = "";
$opexdateErr = $buildingidErr = $opexstatusidErr = "";

$sql2 = "SELECT * FROM buildings WHERE recordOwnerID IN ($accessto)";
    $result2 = $con->query($sql2);

$sql3 = "SELECT * FROM opexstatus";
    $result3 = $con->query($sql3);

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (empty($_POST["opexdate"])) {
        $opexdateErr = "OPEX Date is required";
      } else {
        $opexdate = test_input($_POST["opexdate"]);
        //check if the field only contains letters dash or white space
        if (!preg_match("/^[-0-9' ]*$/", $opexdate)) {
            $opexdateErr = "Only numbers and dash allowed";
        }
      }

    if (empty($_POST["buildingid"])) {
        $buildingidErr = "Building is required";
    } else {
        $buildingid = test_input($_POST["buildingid"]);
        //check if the field only contains letters dash or white space
        if (!preg_match("/^[0-9' ]*$/", $buildingid)) {
            $buildingidErr = "Only numbers allowed";
        }
        $sql3 = "SELECT idbuildings, buildingOwnerID FROM buildings WHERE idbuildings = $buildingid";
        $resultrecordownler = $con->query($sql3);
        while($rowrecordowner = $resultrecordownler->fetch_assoc()) {
            $recordownerid = $rowrecordowner["buildingOwnerID"]; 
        }
    }

    if (empty($_POST["opexstatusid"])) {
        $opexstatusidErr = "Status is required";
    } else {
        $opexstatusid = test_input($_POST["opexstatusid"]);
        //check if the field only contains letters dash or white space
        if (!preg_match("/^[0-9' ]*$/", $opexstatusid)) {
            $opexstatusidErr = "Only numbers allowed";
        }
    }
}

function test_input($data) {
  $data = trim($data);
  $data = stripslashes($data);
  $data = htmlspecialchars($data);
  return $data;
}



if ($_SERVER["REQUEST_METHOD"] == "POST" and $opexdateErr == NULL and $buildingidErr == NULL) {

    //prepare and bind
    $stmt = $con->prepare("INSERT INTO opex (opexDate, buildingID, opexStatusID, recordOwnerID) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("siii", $opexdate, $buildingid, $opexstatusid, $recordownerid);

    if ($stmt->execute()) {
        echo '<div class=\"row\">
        <table class="table table-hover">
            <tbody>
                <tr class="success">
                    <td>Success!</td>
                </tr>
            </tbody>
        </table>';
     
         echo "<div class=\"row\">
            <div class=\"col-sm-2\"><a href=\"listopex.php\" class=\"btn btn-primary\">Back to OPEX</a></div>
        </div>";
    } else {
        echo 'Error creating record: ' . $con->error;
    }
    

} else {

    ?>
    <form class="form form-medium" method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]);?>">
    <div  class="form-group">
        <label class="form-label col-sm-4" for="opexdate" style="padding-top:5px">OPEX Date: <span class="text-danger">*</span></label>
        <div class="col-sm-6"><input class="form-control" id="opexdate" type="date" name="opexdate" value="<?php echo $opexdate;?>"></div>
        <div class="col-sm-2"><span class="error"><span class="text-danger"><?php echo $opexdateErr;?></span></div>
    </div>
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
        <label class="form-label col-sm-4" for="opexstatusid" style="padding-top:5px">Status: <span class="text-danger">*</span></label>
        <div class="col-sm-6">
            <select class="form-control" id="opexstatusid" name="opexstatusid">
            <?php
                echo "<option value=\"\"> - Select a status - </option>";
            while($row = $result3->fetch_assoc()) {
                echo "<option value=\"" . $row["idopexstatus"] . "\">". $row["opexStatus"] . "</option>";
            }
            ?>
            </select>
        </div>
        <div class="col-sm-2"><span class="error"><span class="text-danger"><?php echo $buildingidErr;?></span></div>
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
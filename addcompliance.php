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

<?=template_header('Add Compliance Action')?>

<div class="page-title">
	<div class="icon">
		<svg width="20" height="20" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 576 512"><!--!Font Awesome Free 6.5.1 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license/free Copyright 2024 Fonticons, Inc.--><path d="M575.8 255.5c0 18-15 32.1-32 32.1h-32l.7 160.2c0 2.7-.2 5.4-.5 8.1V472c0 22.1-17.9 40-40 40H456c-1.1 0-2.2 0-3.3-.1c-1.4 .1-2.8 .1-4.2 .1H416 392c-22.1 0-40-17.9-40-40V448 384c0-17.7-14.3-32-32-32H256c-17.7 0-32 14.3-32 32v64 24c0 22.1-17.9 40-40 40H160 128.1c-1.5 0-3-.1-4.5-.2c-1.2 .1-2.4 .2-3.6 .2H104c-22.1 0-40-17.9-40-40V360c0-.9 0-1.9 .1-2.8V287.6H32c-18 0-32-14-32-32.1c0-9 3-17 10-24L266.4 8c7-7 15-8 22-8s15 2 21 7L564.8 231.5c8 7 12 15 11 24z"/></svg>
	</div>	
	<div class="wrap">
		<h2>Add Compliance Action</h2>
	</div>
</div>

<div class="block">    

<?php
    // define variables and set to empty values

$sql2 = "SELECT * from buildings WHERE  recordOwnerID IN ($accessto)";
$result2 = $con->query($sql2);

$sql3 = "SELECT * from premises where recordOwnerID IN ($accessto) ORDER BY unitName";
$result3 = $con->query($sql3);

$sql4 = "SELECT idcompany, companyName from companies where recordOwnerID IN ($accessto) ORDER BY companyName";
$result4 = $con->query($sql4);

$sql5 = "SELECT idcontacts, firstName, lastName from contacts where recordOwnerID IN ($accessto) ORDER BY firstName";
$result5 = $con->query($sql5);

$sql6 = "SELECT * from compliancestatus ORDER BY idcompliancestatus";
$result6 = $con->query($sql6);

$dateactionable = $compliancename = $compliancedescription = $buildingid = $premisesid = $supplierid = $contactid = $compliancestatusid = $datecompleted = "";
$dateactionableErr = $compliancenameErr = $compliancedescriptionErr = $buildingidErr = $premisesidErr = $supplieridErr = $contactidErr = $compliancestatusidErr = $datecompletedErr = "";


if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    if (empty($_POST["dateactionable"])) {
        $dateactionableErr = "Date is required";
      } else {
        $dateactionable = test_input($_POST["dateactionable"]);
        //check if the field only contains numbers or dash
        if (!preg_match("/^[0-9-' ]*$/", $dateactionable)) {
            $dateactionableErr = "Only numbers and dash allowed";
        }
    }

    if (empty($_POST["compliancename"])) {
        $compliancenameErr = "Name is required";
      } else {
        $compliancename = test_input($_POST["compliancename"]);
        //check if the field only contains letters dash or white space
        if (!preg_match("/^[,a-zA-Z-0-9-āēīōūĀĒĪŌŪ\/'., ]*$/", $compliancename)) {
            $compliancenameErr = "Only letters, dash, dot, slash and spaces allowed";
        }
      }

    if (empty($_POST["buildingid"])) {
        $buildingidErr = "Building is required";
    } else {
        $buildingid = test_input($_POST["buildingid"]);
        if (!preg_match("/^[0-9' ]*$/", $buildingid)) {
            $buildingidErr = "Only numbers allowed";
        }
    }

    if (empty($_POST["premisesid"])) {
        $premisesid = 0;
    } else {
        $premisesid = test_input($_POST["premisesid"]);
        if (!preg_match("/^[0-9' ]*$/", $premisesid)) {
            $premisesidErr = "Only numbers allowed";
        }
    }

    if (empty($_POST["supplierid"])) {
        $supplierid = 0;
    } else {
        $supplierid = test_input($_POST["supplierid"]);
        if (!preg_match("/^[0-9' ]*$/", $supplierid)) {
            $supplieridErr = "Only numbers allowed";
        }
    }

    if (empty($_POST["contactid"])) {
        $contactid = 0;
    } else {
        $contactid = test_input($_POST["contactid"]);
        if (!preg_match("/^[0-9' ]*$/", $contactid)) {
            $contactidErr = "Only numbers allowed";
        }
    }

    if (empty($_POST["compliancestatusid"])) {
        $compliancestatusidErr = "Status is required";
    } else {
        $compliancestatusid = test_input($_POST["compliancestatusid"]);
        if (!preg_match("/^[0-9' ]*$/", $compliancestatusid)) {
            $compliancestatusidErr = "Only numbers allowed";
        }
    }

    $datecompleted = test_input($_POST["datecompleted"]);
    if (!preg_match("/^[0-9-' ]*$/", $datecompleted)) {
        $datecompletedErr = "Only numbers and dash allowed";
    }
}

function test_input($data) {
  $data = trim($data);
  $data = stripslashes($data);
  $data = htmlspecialchars($data);
  return $data;
}



if ($_SERVER["REQUEST_METHOD"] == "POST" and $dateactionableErr == NULL and $compliancenameErr == NULL and $compliancedescriptionErr == NULL and $buildingidErr == NULL and $premisesidErr == NULL and $supplieridErr == NULL and $contactidErr == NULL and $compliancestatusidErr == NULL and $datecompletedErr == NULL) {

    //prepare and bind
    if ($datecompleted == '') {
        $stmt = $con->prepare("INSERT INTO compliance (dateActionable, complianceName, complianceDescription, buildingID, premisesID, supplierID, contactID, complianceStatusID, recordOwnerID) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("sssiiiiii", $dateactionable, $compliancename, $compliancedescription, $buildingid, $premisesid, $supplierid, $contactid, $compliancestatusid, $recordownerid);
    } else {
        $stmt = $con->prepare("INSERT INTO compliance (dateActionable, complianceName, complianceDescription, buildingID, premisesID, supplierID, contactID, complianceStatusID, dateCompleted, recordOwnerID) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("sssiiiiisi", $dateactionable, $compliancename, $compliancedescription, $buildingid, $premisesid, $supplierid, $contactid, $compliancestatusid, $datecompleted, $recordownerid);
    }

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
            <div class=\"col-sm-2\"><a href=\"listcompliance.php?status=0\" class=\"btn btn-primary\">Back to Compliance Tasks</a></div>
        </div>";
    

} else {

    ?>
    <form class="form form-medium" method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]);?>">
    <div class="form-group">
        <label class="form-label col-sm-4" for="dateactionable">Action Date: <span class="text-danger">*</span></label>
        <div class="col-sm-6"><input class="form-control" id="dateactionable" type="date" name="dateactionable" value="<?php echo $dateactionable;?>"></div>
        <div class="col-sm-2"><span class="error"><span class="text-danger"><?php echo $dateactionableErr;?></span></div>
    </div>
    <div  class="form-group">
        <label class="form-label col-sm-4" for="compliancename">Name: <span class="text-danger">*</span></label>
        <div class="col-sm-6"><input class="form-control" id="compliancename" type="text" name="compliancename" value="<?php echo $compliancename;?>"></div>
        <div class="col-sm-2"><span class="error"><span class="text-danger"><?php echo $compliancenameErr;?></span></div>
    </div>
    <div  class="form-group">
        <label class="form-label col-sm-4" for="compliancedescription">Description:</label>
        <div class="col-sm-6"><input class="form-control" id="compliancedescription" type="text" name="compliancedescription" value="<?php echo $compliancedescription;?>"></div>
        <div class="col-sm-2"><span class="error"><span class="text-danger"><?php echo $compliancedescriptionErr;?></span></div>
    </div>
    <div class="form-group">
        <label class="form-label col-sm-4" for="buildingid">Building: <span class="text-danger">*</span></label>
        <div class="col-sm-6">
            <select class="form-control" id="buildingid" name="buildingid">
            <?php
                echo "<option value=\"\"> - Select a Building - </option>";
            while($row2 = $result2->fetch_assoc()) {
                if($row2["idbuildings"] == $buildingid){
                    echo "<option value=\"" . $row2["idbuildings"] . "\" selected>". $row2["buildingName"] . "</option>";
                } else {
                    echo "<option value=\"" . $row2["idbuildings"] . "\">". $row2["buildingName"] . "</option>";
                }
            }
            ?>
            </select>
        </div>
        <div class="col-sm-2"><span class="error"><span class="text-danger"><?php echo $buildingidErr;?></span></div>
    </div>
    <div class="form-group">
        <label class="form-label col-sm-4" for="premisesid">Premises:</label>
        <div class="col-sm-6">
            <select class="form-control" id="premisesid" name="premisesid">
            <?php
                echo "<option value=\"0\"></option>";
            while($row3 = $result3->fetch_assoc()) {
                if($row3["idpremises"] == $premisesid){
                    echo "<option value=\"" . $row3["idpremises"] . "\" selected>". $row3["unitName"] . "</option>";
                } else {
                    echo "<option value=\"" . $row3["idpremises"] . "\">". $row3["unitName"] . "</option>";
                }
            }
            ?>
            </select>
        </div>
        <div class="col-sm-2"><span class="error"><span class="text-danger"><?php echo $premisesidErr;?></span></div>
    </div>
    <div class="form-group">
        <label class="form-label col-sm-4" for="supplierid">Supplier:</label>
        <div class="col-sm-6">
            <select class="form-control" id="supplierid" name="supplierid">
            <?php
                echo "<option value=\"0\"></option>";
            while($row4 = $result4->fetch_assoc()) {
                if($row4["idcompany"] == $supplierid){
                    echo "<option value=\"" . $row4["idcompany"] . "\" selected>". $row4["companyName"] . "</option>";
                } else {
                    echo "<option value=\"" . $row4["idcompany"] . "\">". $row4["companyName"] . "</option>";
                } 
            }
            ?>
            </select>
        </div>
        <div class="col-sm-2"><span class="error"><span class="text-danger"><?php echo $supplieridErr;?></span></div>
    </div>
    <div class="form-group">
        <label class="form-label col-sm-4" for="contactid">Contact:</label>
        <div class="col-sm-6">
            <select class="form-control" id="contactid" name="contactid">
            <?php
                echo "<option value=\"0\"></option>";
            while($row5 = $result5->fetch_assoc()) {
                if($row5["idcontacts"] == $contactid){
                    echo "<option value=\"" . $row5["idcontacts"] . "\" selected>". $row5["firstName"] . " " . $row5["lastName"] . "</option>";
                } else {
                    echo "<option value=\"" . $row5["idcontacts"] . "\">". $row5["firstName"] . " " . $row5["lastName"] . "</option>";
                }
            }
            ?>
            </select>
        </div>
        <div class="col-sm-2"><span class="error"><span class="text-danger"><?php echo $contactidErr;?></span></div>
    </div>
    <div class="form-group">
        <label class="form-label col-sm-4" for="compliancestatusid">Status: <span class="text-danger">*</span></label>
        <div class="col-sm-6">
            <select class="form-control" id="compliancestatusid" name="compliancestatusid">
            <?php
                echo "<option value=\"\"> - Select a Status - </option>";
            while($row6 = $result6->fetch_assoc()) {
                if($row6["idcompliancestatus"] == $compliancestatusid){
                    echo "<option value=\"" . $row6["idcompliancestatus"] . "\" selected>". $row6["complianceStatusName"] . "</option>";
                } else {
                    echo "<option value=\"" . $row6["idcompliancestatus"] . "\">". $row6["complianceStatusName"] . "</option>";
                }
                
            }
            ?>
            </select>
        </div>
        <div class="col-sm-2"><span class="error"><span class="text-danger"><?php echo $compliancestatusidErr;?></span></div>
    </div>
    <div class="form-group">
        <label class="form-label col-sm-4" for="datecompleted">Completion Date:</label>
        <div class="col-sm-6"><input class="form-control" id="datecompleted" type="date" name="datecompleted" value="<?php echo $datecompleted;?>"></div>
        <div class="col-sm-2"><span class="error"><span class="text-danger"><?php echo $datecompletedErr;?></span></div>
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
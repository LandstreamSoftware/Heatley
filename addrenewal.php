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

<?=template_header('Add Renewal')?>

<div class="page-title">
	<div class="icon">
		<svg width="20" height="20" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 576 512"><!--!Font Awesome Free 6.5.1 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license/free Copyright 2024 Fonticons, Inc.--><path d="M575.8 255.5c0 18-15 32.1-32 32.1h-32l.7 160.2c0 2.7-.2 5.4-.5 8.1V472c0 22.1-17.9 40-40 40H456c-1.1 0-2.2 0-3.3-.1c-1.4 .1-2.8 .1-4.2 .1H416 392c-22.1 0-40-17.9-40-40V448 384c0-17.7-14.3-32-32-32H256c-17.7 0-32 14.3-32 32v64 24c0 22.1-17.9 40-40 40H160 128.1c-1.5 0-3-.1-4.5-.2c-1.2 .1-2.4 .2-3.6 .2H104c-22.1 0-40-17.9-40-40V360c0-.9 0-1.9 .1-2.8V287.6H32c-18 0-32-14-32-32.1c0-9 3-17 10-24L266.4 8c7-7 15-8 22-8s15 2 21 7L564.8 231.5c8 7 12 15 11 24z"/></svg>
	</div>	
	<div class="wrap">
		<h2>Add Renewal</h2>
<!--		<p>Welcome back, <?=htmlspecialchars($_SESSION['account_name'], ENT_QUOTES)?>!</p>  -->
	</div>
</div>

<div class="block">   

<?php
    // define variables and set to empty values
$renewaltypeid = $leaseid = $startdate = $enddate = $renewalstatusid = $renewalsignedon = $renewalsignedbyid = "";
$renewaltypeidErr = $leaseidErr = $startdateErr = $enddateErr = $rentpremisesErr = $rentcarparksErr = $renewalstatusidErr = $renewalsignedonErr = $renewalsignedbyidErr = $fixedpercentErr = "";
$rentpremises = $rentcarparks = "0.00";
$fixedpercent = "0.00";

$Q = explode("/", $_SERVER['QUERY_STRING']);
parse_str($Q[0],$QueryParameters);
if(empty($QueryParameters['leaseid'])){
    $QPleaseid = "";
}else{
    $QPleaseid = $QueryParameters['leaseid'];
}
if(empty($QPrenewalid)){
    $QPrenewalid ="";
}else{
    $QPrenewalid = $QueryParameters['renewalid'];
}
if(empty($QPtenantid)){
    $QPtenantid ="";
}else{
    $QPtenantid = $QueryParameters['tenantid'];
    $tenantid = $QueryParameters['tenantid'];
}

$sql1 = "SELECT idlease, tenantname FROM leases_view WHERE leasestatusid < 3 and recordOwnerID IN ($accessto) ORDER BY tenantname";
    $result1 = $con->query($sql1);

$sql2 = "SELECT idrenewaltype, renewalType FROM renewaltype ORDER BY idrenewaltype";
    $result2 = $con->query($sql2);

$sql4 = "SELECT * from renewalstatus ORDER BY displayOrder";
    $result4 = $con->query($sql4);

$sql3 = "SELECT * FROM contacts WHERE recordOwnerID IN ($accessto) ORDER BY firstName";
    $result3 = $con->query($sql3);

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    if (empty($_POST["leaseid"])) {
        $leaseidErr = "Lease is required";
    } else {
        $leaseid = test_input($_POST["leaseid"]);
        //check if the field only contains letters dash or white space
        if (!preg_match("/^[0-9.' ]*$/", $leaseid)) {
            $leaseidErr = "Only numbers and dot allowed";
        }
        $sql5 = "SELECT idlease, recordOwnerID FROM leases WHERE idlease = $leaseid";
        $resultrecordownler = $con->query($sql5);
        while($rowrecordowner = $resultrecordownler->fetch_assoc()) {
          $recordownerid = $rowrecordowner["recordOwnerID"]; 
        }
    }

    if (empty($_POST["renewaltypeid"])) {
        $renewaltypeidErr = "Renewal type is required";
    } else {
        $renewaltypeid = test_input($_POST["renewaltypeid"]);
        //check if the field only contains letters dash or white space
        if (!preg_match("/^[0-9.' ]*$/", $renewaltypeid)) {
            $renewaltypeidErr = "Only numbers and dot allowed";
        }
    }

    if (empty($_POST["startdate"])) {
        $startdateErr = "Start Date is required";
    } else {
        $startdate = test_input($_POST["startdate"]);
        //check if the field only contains letters dash or white space
        if (!preg_match("/^[-0-9' ]*$/", $startdate)) {
            $startdateErr = "Only numbers and dash allowed";
        }
    }

    if (empty($_POST["enddate"])) {
        $enddateErr = "End Date is required";
    } else {
        $enddate = test_input($_POST["enddate"]);
        //check if the field only contains letters dash or white space
        if (!preg_match("/^[-0-9' ]*$/", $enddate)) {
            $enddateErr = "Only numbers and dash allowed";
        }
    }

    //Rentpremises is not a required field
    if ($_POST["rentpremises"]) {
        $rentpremises = test_input($_POST["rentpremises"]);
        //check if the field only contains numbers and dots
        if (!preg_match("/^[-0-9.' ]*$/", $rentpremises)) {
            $rentpremisesErr = "Only numbers, dot allowed";
        }
    }

    //Rentcarparks is not a required field
    if ($_POST["rentcarparks"]) {
        $rentcarparks = test_input($_POST["rentcarparks"]);
        //check if the field only contains v
        if (!preg_match("/^[0-9.' ]*$/", $rentcarparks)) {
            $rentcarparksErr = "Only numbers and dot allowed";
        }
    }

    if (empty($_POST["renewalstatusid"])) {
        $renewalstatusidErr = "Status is required";
    } else {
        $renewalstatusid = test_input($_POST["renewalstatusid"]);
        //check if the field only contains v
        if (!preg_match("/^[0-9.' ]*$/", $renewalstatusid)) {
            $renewalstatusidErr = "Only numbers allowed";
        }
    }

    if ($_POST["renewalsignedon"]) {
        $renewalsignedon = test_input($_POST["renewalsignedon"]);
        //check if the field only contains letters dash or white space
        if (!preg_match("/^[-0-9' ]*$/", $renewalsignedon)) {
            $renewalsignedonErr = "Only numbers and dash allowed";
        }
    }

    if ($_POST["renewalsignedbyid"]) {
        $renewalsignedbyid = test_input($_POST["renewalsignedbyid"]);
        //check if the field only contains letters dash or white space
        if (!preg_match("/^[0-9' ]*$/", $renewalsignedbyid)) {
            $renewalsignedbyidErr = "Only numbers allowed";
        }
    }

    //Fixed % is not a required field
    if ($_POST["fixedpercent"]) {
        $fixedpercent = test_input($_POST["fixedpercent"]);
        //check if the field only contains v
        if (!preg_match("/^[0-9.' ]*$/", $fixedpercent)) {
            $fixedpercentErr = "Only numbers and dot allowed";
        }
    }

}

function test_input($data) {
  $data = trim($data);
  $data = stripslashes($data);
  $data = htmlspecialchars($data);
  return $data;
}


if ($_SERVER["REQUEST_METHOD"] == "POST" and $leaseidErr == NULL and $renewaltypeidErr == NULL and $startdateErr == NULL and $enddateErr == NULL and $rentpremisesErr == NULL and $rentcarparksErr == NULL and $renewalstatusidErr == NULL and $renewalsignedonErr == NULL and $renewalsignedbyidErr == NULL and $fixedpercentErr == NULL) {

    //prepare and bind
    $stmt = $con->prepare("INSERT INTO renewals (startDate, endDate, rentPremises, rentCarparks, renewalTypeID, leaseID, renewalStatusID, renewalSignedOn, renewalSignedByID, fixedPercent, recordOwnerID) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ? ,?)");
    $stmt->bind_param("ssddiiisidi", $startdate, $enddate, $rentpremises, $rentcarparks, $renewaltypeid, $leaseid, $renewalstatusid, $renewalsignedon, $renewalsignedbyid, $fixedpercent, $recordownerid);
    //execute
    
    if ($stmt->execute()) {
        echo '<table class="table table-hover">
          <tbody>
            <tr class="success">
                <td>Success!</td>
            </tr>
          </tbody>
        </table>';
     
        echo "<div class=\"row\">
            <div class=\"col-sm-2\"><a href=\"viewlease.php?leaseid=".$leaseid."\" class=\"btn btn-primary\">Back to Lease</a></div>
        </div>";
    } else {
        echo 'Error creating record: ' . $con->error;
    }
        
} else {

?>


<script type="text/javascript">

function MyFunction() {
    var x = document.getElementById("renewaltypeid").selectedIndex;

    const collection1 = document.getElementsByClassName("div1");
    const collection2 = document.getElementsByClassName("div2");
    const collection3 = document.getElementsByClassName("div3");
    const collection4 = document.getElementsByClassName("div4");

    for (let i = 0; i < collection1.length; i++) {
        collection1[i].style.visibility = "collapse";
    }
    for (let j = 0; j < collection2.length; j++) {
        collection2[j].style.visibility = "collapse";
    }
    for (let k = 0; k < collection3.length; k++) {
        collection3[k].style.visibility = "collapse";
    }
    for (let m = 0; m < collection4.length; m++) {
        collection4[m].style.visibility = "collapse";
    }

    switch (x) {
        case 0: //No selection
            for (let i = 0; i < collection1.length; i++) {
                collection1[i].style.visibility = "collapse";
            }
            for (let j = 0; j < collection2.length; j++) {
                collection2[j].style.visibility = "collapse";
            }
            for (let k = 0; k < collection3.length; k++) {
                collection3[k].style.visibility = "collapse";
            }
            for (let m = 0; m < collection4.length; m++) {
                collection4[m].style.visibility = "collapse";
            }
        break;

        case 1: //Rent at commencement
        case 7: //CPI Rent Review
        case 8: //Market Rent Review
            for (let i = 0; i < collection1.length; i++) {
                collection1[i].style.visibility = "visible";
            }
            for (let j = 0; j < collection2.length; j++) {
                collection2[j].style.visibility = "collapse";
            }
            for (let k = 0; k < collection3.length; k++) {
                collection3[k].style.visibility = "visible";
            }
            for (let m = 0; m < collection4.length; m++) {
                collection4[m].style.visibility = "collapse";
            }
        break;

        case 2: //OPEX Budget
            for (let i = 0; i < collection1.length; i++) {
                collection1[i].style.visibility = "visible";
            }
            for (let j = 0; j < collection2.length; j++) {
                collection2[j].style.visibility = "collapse";
            }
            for (let k = 0; k < collection3.length; k++) {
                collection3[k].style.visibility = "collapse";
            }
            for (let m = 0; m < collection4.length; m++) {
                collection4[m].style.visibility = "collapse";
            }
        break;

        
        case 9: //Fixed % Rent Review
            for (let i = 0; i < collection1.length; i++) {
                collection1[i].style.visibility = "visible";
            }
            for (let j = 0; j < collection2.length; j++) {
                collection2[j].style.visibility = "visible";
            }
            for (let k = 0; k < collection3.length; k++) {
                collection3[k].style.visibility = "visible";
            }
            for (let m = 0; m < collection4.length; m++) {
                collection4[m].style.visibility = "collapse";
            }
        break;

        default: //1st, 2nd, 3rd Right of Renewal. (cases 3,4,5,6)
            for (let i = 0; i < collection1.length; i++) {
                collection1[i].style.visibility = "visible";
            }
            for (let j = 0; j < collection2.length; j++) {
                collection2[j].style.visibility = "collapse";
            }
            for (let k = 0; k < collection3.length; k++) {
                collection3[k].style.visibility = "visible";
            }
            for (let m = 0; m < collection4.length; m++) {
                collection4[m].style.visibility = "visible";
            }
    }
}
</script>


    <form class="form form-medium" method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]);?>">
    <div class="form-group" id="div1">
        <label class="form-label col-sm-4" for="renewaltypeid" style="padding-top:5px">Renewal Type: <span class="text-danger">*</span></label>
        <div class="col-sm-6">
            <select class="form-control" id="renewaltypeid" name="renewaltypeid" onchange="MyFunction();">
            <?php
                echo "<option value=\"0\"> - Select a renewal type - </option>";
            while($row = $result2->fetch_assoc()) {
                if($row["idrenewaltype"] == "$renewaltypeid") {
                    echo "<option value=\"" . $row["idrenewaltype"] . "\" selected>". $row["renewalType"] . "</option>";
                }
                    echo "<option value=\"" . $row["idrenewaltype"] . "\">". $row["renewalType"] . "</option>";
            }
            ?>
            </select>
        </div>
        <div class="col-sm-2"><span class="error"><span class="text-danger">
            <?php echo $renewaltypeidErr;?>
            <?php echo $leaseidErr;?>
            <?php echo $startdateErr;?>
            <?php echo $enddateErr;?>
            <?php echo $rentpremisesErr;?>
            <?php echo $rentcarparksErr;?>
            <?php echo $renewalstatusidErr;?>
            <?php echo $renewalsignedonErr;?>
            <?php echo $renewalsignedbyidErr;?>
            <?php echo $fixedpercentErr;?>
            </span>
        </div>
    </div>
    <div class="form-group div1">
        <label class="form-label col-sm-4" for="leaseid" style="padding-top:5px">Lease: <span class="text-danger">*</span></label>
        <div class="col-sm-6">
            <select class="form-control" id="leaseid" name="leaseid">
            <?php
            while($row1 = $result1->fetch_assoc()) {
                if ($row1["idlease"] == "$QPleaseid") {
                    echo "<option value=\"" . $row1["idlease"] . "\" selected>". $row1["tenantname"] . "</option>";
                } else {
                    echo "<option value=\"" . $row1["idlease"] . "\">". $row1["tenantname"] . "</option>";
                }
            }       
            ?>
            </select>
        </div>
        <div class="col-sm-2"><span class="error"><span class="text-danger"><?php echo $leaseidErr;?></span></div>
    </div>
    <div class="form-group div1">
        <label class="form-label col-sm-4" for="renewalstatusid" style="padding-top:5px">Status: <span class="text-danger">*</span></label>
        <div class="col-sm-6">
            <select class="form-control" id="renewalstatusid" name="renewalstatusid">
            <?php
            while($row = $result4->fetch_assoc()) {
                if ($row["idrenewalstatus"] == "$renewalstatusid") {
                    echo "<option value=\"" . $row["idrenewalstatus"] . "\" selected>". $row["renewalStatus"] . "</option>";
                } else {
                    echo "<option value=\"" . $row["idrenewalstatus"] . "\">". $row["renewalStatus"] . "</option>";
                }
            }
            ?>
            </select>
        </div>
        <div class="col-sm-2"><span class="error"><span class="text-danger"><?php echo $renewalstatusidErr;?></span></div>
    </div>
    <div  class="form-group div1">
        <label class="form-label col-sm-4" for="startdate" style="padding-top:5px">Start Date:<span class="text-danger">*</span></label>
        <div class="col-sm-6"><input class="form-control" id="startdate" type="date" name="startdate" value="<?php echo $startdate;?>"></div>
        <div class="col-sm-2"><span class="error"><span class="text-danger"><?php echo $startdateErr;?></span></div>
    </div>
    <div  class="form-group div1">
        <label class="form-label col-sm-4" for="enddate" style="padding-top:5px">End Date:<span class="text-danger">*</span></label>
        <div class="col-sm-6"><input class="form-control" id="enddate" type="date" name="enddate" value="<?php echo $enddate;?>"></div>
        <div class="col-sm-2"><span class="error"><span class="text-danger"><?php echo $enddateErr;?></span></div>
    </div>
    <div  class="form-group div3">
        <label class="form-label col-sm-4" for="rentpremises" style="padding-top:5px">Rent Premises:</label>
        <div class="col-sm-6"><input class="form-control" id="rentpremises" type="text" name="rentpremises" value="<?php echo $rentpremises;?>"></div>
        <div class="col-sm-2"><span class="error"><span class="text-danger"><?php echo $rentpremisesErr;?></span></div>
    </div>
    <div  class="form-group div3">
        <label class="form-label col-sm-4" for="rentcarparks" style="padding-top:5px">Rent Carparks:</label>
        <div class="col-sm-6"><input class="form-control" id="rentcarparks" type="text" name="rentcarparks" value="<?php echo $rentcarparks;?>"></div>
        <div class="col-sm-2"><span class="error"><span class="text-danger"><?php echo $rentcarparksErr;?></span></div>
    </div>
    <div  class="form-group div2">
        <label class="form-label col-sm-4" for="fixedpercent" style="padding-top:5px">Fixed % increase:</label>
        <div class="col-sm-6"><input class="form-control" id="fixedpercent" type="text" name="fixedpercent" value="<?php echo $fixedpercent;?>"></div>
        <div class="col-sm-2"><span class="error"><span class="text-danger"><?php echo $fixedpercentErr;?></span></div>
    </div>
    <div class="form-group div4">
        <label class="form-label col-sm-4" for="renewalsignedon">Signed On:</label>
        <div class="col-sm-6"><input class="form-control" id="renewalsignedon" type="text" name="renewalsignedon" value="<?php echo $renewalsignedon;?>"></div>
        <div class="col-sm-2"><span class="error"><span class="text-danger"><?php echo $renewalsignedonErr;?></span></div>
    </div>
    <div class="form-group div4">
        <label class="form-label col-sm-4" for="renewalsignedbyid">Signed By:</label>
        <div class="col-sm-6">
            <select class="form-control" id="renewalsignedbyid" name="renewalsignedbyid">
            <?php
            echo "<option value=\"0\"> - Select a Signatory - </option>";
            while($row3 = $result3->fetch_assoc()) {
                if ($row3["idcontacts"] == "$renewalsignedbyid") {
                    echo "<option value=\"" . $row3["idcontacts"] . "\" selected>". $row3["firstName"] . " " . $row3["lastName"] . "</option>";
                } else {
                    echo "<option value=\"" . $row3["idcontacts"] . "\">". $row3["firstName"] . " " . $row3["lastName"] . "</option>";
                }
            }       
            ?>
            </select>
        </div>
        <div class="col-sm-2"><span class="error"><span class="text-danger"><?php echo $renewalsignedbyidErr;?></span></div>
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

<script type="text/javascript">
    const collection0 = document.getElementsByClassName("form-group");
//    alert(collection0.length);
    for (let a = 0; a < collection0.length; a++) {
        collection0[a].style.visibility = "collapse";
    }
    document.getElementById("div1").style.visibility = "visible";
</script>

<?=template_footer()?>
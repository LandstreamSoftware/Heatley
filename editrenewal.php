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

<?=template_header('Edit Renewal')?>

<div class="page-title">
	<div class="icon">
		<svg width="20" height="20" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 576 512"><!--!Font Awesome Free 6.5.1 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license/free Copyright 2024 Fonticons, Inc.--><path d="M575.8 255.5c0 18-15 32.1-32 32.1h-32l.7 160.2c0 2.7-.2 5.4-.5 8.1V472c0 22.1-17.9 40-40 40H456c-1.1 0-2.2 0-3.3-.1c-1.4 .1-2.8 .1-4.2 .1H416 392c-22.1 0-40-17.9-40-40V448 384c0-17.7-14.3-32-32-32H256c-17.7 0-32 14.3-32 32v64 24c0 22.1-17.9 40-40 40H160 128.1c-1.5 0-3-.1-4.5-.2c-1.2 .1-2.4 .2-3.6 .2H104c-22.1 0-40-17.9-40-40V360c0-.9 0-1.9 .1-2.8V287.6H32c-18 0-32-14-32-32.1c0-9 3-17 10-24L266.4 8c7-7 15-8 22-8s15 2 21 7L564.8 231.5c8 7 12 15 11 24z"/></svg>
	</div>	
	<div class="wrap">
		<h2>Edit Renewal</h2>
<!--		<p>Welcome back, <?=htmlspecialchars($_SESSION['account_name'], ENT_QUOTES)?>!</p>  -->
	</div>
</div>

<div class="block">

<?php
// define variables and set to empty values

$Q = explode("/", $_SERVER['QUERY_STRING']);
parse_str($Q[0],$QueryParameters);
$QPrenewalid = $QueryParameters['renewalid'];

$sql = "SELECT * from renewals_view WHERE idrenewals = $QPrenewalid and recordOwnerID IN ($accessto)";
$result = $con->query($sql);

$sql2 = "SELECT * from renewaltype";
$result2 = $con->query($sql2);

$sql3 = "SELECT idlease, tenantname from leases_view WHERE recordOwnerID IN ($accessto)";
$result3 = $con->query($sql3);

$sql4 = "SELECT * from renewalstatus ORDER BY displayOrder";
$result4 = $con->query($sql4);


if ($result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        $tenantname = $row["tenantname"];
        $tenantid = $row["tenantid"];
        $startdate = $row["startdate"];
        $enddate = $row["enddate"];
        $rentpremises = $row["rentpremises"];
        $rentcarparks = $row["rentcarparks"];
        $renewaltypeid = $row["renewaltypeid"];
        $leaseid = $row["leaseid"];
        $renewalstatusid = $row["renewalstatusid"];
        $renewalsignedon = $row["renewalsignedon"];
        $renewalsignedbyid = $row["renewalsignedbyid"];
        if(!empty($row["fixedpercent"])) {
            $fixedpercent = $row["fixedpercent"];
        } else {
            $fixedpercent = "0.00";
        }
    }
} else {
    $startdate = $enddate = $rentpremises = $rentcarparks = $renewaltypeid = $leaseid = $renewalstatusid = $renewalsignedon = $renewalsignedbyid = "";
    $fixedpercent = 0.00;
}
$startdateErr = $enddateErr = $rentpremisesErr = $rentcarparksErr = $renewaltypeidErr = $leaseidErr = $renewalstatusidErr = $renewalsignedonErr = $renewalsignedbyidErr = $fixedpercentErr ="";

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    if (empty($_POST["startdate"])) {
        $startdateErr = "Start Date is required";
      } else {
        $startdate = test_input($_POST["startdate"]);
        //check if the field only contains numbers or dash
        if (!preg_match("/^[0-9-' ]*$/", $startdate)) {
            $startdateErr = "Only numbers and dash allowed";
        }
    }

    //if (empty($_POST["enddate"])) {
    //  $enddateErr = "End Date is required";
    //} else {
        $enddate = test_input($_POST["enddate"]);
        //check if the field only contains numbers or dash
        if (!preg_match("/^[0-9-' ]*$/", $enddate)) {
            $enddateErr = "Only numbers and dash allowed";
        }
    //}

    if (empty($_POST["rentpremises"])) {
        $rentpremisesErr = "Annual Rent is required";
      } else {
        $rentpremises = test_input($_POST["rentpremises"]);
        //check if the field only contains letters dash or white space
        if (!preg_match("/^[0-9.' ]*$/", $rentpremises)) {
            $rentpremisesErr = "Only numbers and dot allowed";
        }
    }
    
    if (is_null($_POST["rentcarparks"])) {
        $rentcarparksErr = "Annual Carpark Rent is required";
      } else {
        $rentcarparks = test_input($_POST["rentcarparks"]);
        //check if the field only contains letters dash or white space
        if (!preg_match("/^[0-9.' ]*$/", $rentcarparks)) {
            $rentcarparksErr = "Only numbers and dot allowed";
        }
    }

    if (empty($_POST["renewaltypeid"])) {
        $renewaltypeidErr = "Renewal Type is required";
    } else {
        $renewaltypeid = test_input($_POST["renewaltypeid"]);
        //check if the field only contains letters dash or white space
        if (!preg_match("/^[0-9' ]*$/", $renewaltypeid)) {
            $renewaltypeidErr = "Only numbers allowed";
        }
    }

    if (empty($_POST["leaseid"])) {
        $leaseidErr = "Lease is required";
    } else {
        $leaseid = test_input($_POST["leaseid"]);
        //check if the field only contains letters dash or white space
        if (!preg_match("/^[0-9' ]*$/", $leaseid)) {
            $leaseidErr = "Only numbers allowed";
        }
    }

    if (empty($_POST["renewalstatusid"])) {
        $renewalstatusidErr = "Status is required";
    } else {
        $renewalstatusid = test_input($_POST["renewalstatusid"]);
        //check if the field only contains letters dash or white space
        if (!preg_match("/^[0-9' ]*$/", $renewalstatusid)) {
            $renewalstatusidErr = "Only numbers allowed";
        }
    }

        $renewalsignedon = test_input($_POST["renewalsignedon"]);
        //check if the field only contains numbers or dash
        if (!preg_match("/^[0-9-' ]*$/", $renewalsignedon)) {
            $renewalsignedonErr = "Only numbers and dash allowed";
        }

        $renewalsignedbyid = test_input($_POST["renewalsignedbyid"]);
        //check if the field only contains numbers or dash
        if (!preg_match("/^[0-9-' ]*$/", $renewalsignedbyid)) {
            $renewalsignedbyidErr = "Only numbers allowed";
        }

    if (empty($_POST["fixedpercent"])) {
        $fixedpercentErr = "Fixed Percent is required";
    } else {
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


if ($_SERVER["REQUEST_METHOD"] == "POST" and $startdateErr == NULL and $enddateErr == NULL and $rentpremisesErr == NULL and $rentcarparksErr == NULL and $renewaltypeidErr == NULL and $leaseidErr == NULL and $renewalstatusidErr == NULL and $renewalsignedonErr == NULL and $renewalsignedbyidErr == NULL and $fixedpercentErr == NULL) {

    //prepare and bind
$sqlupdate = "UPDATE renewals SET startDate = '$startdate', endDate = '$enddate', rentPremises = '$rentpremises', rentCarparks = '$rentcarparks', renewalTypeId = '$renewaltypeid', leaseID = '$leaseid', renewalStatusID = '$renewalstatusid', renewalSignedOn = '$renewalsignedon', renewalSignedByID = $renewalsignedbyid, fixedPercent = '$fixedpercent' WHERE idrenewals=$QPrenewalid";

if ($con->query($sqlupdate) === TRUE) {
   echo '<table class="table table-hover">
        <tbody>
            <tr class="success">
                <td>Success!</td>
            </tr>
        </tbody>
    </table>';

    echo "<div class=\"row\">
           <div class=\"col-sm-2\"><a href=\"viewlease.php?leaseid=".$leaseid."\" class=\"btn btn-primary\">Back to lease</a></div>
        </div>
        <div class=\"row\">";

} else {
    echo 'Error updating record: ' . $con->error;
}
} else {
    $sql1 = "SELECT * FROM contacts WHERE recordOwnerID IN ($accessto) and companyID = $tenantid ORDER BY firstName";
    $result1 = $con->query($sql1);

    ?>
    <form class="form form-medium" method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"].'?renewalid='.$QPrenewalid);?>">
    <div class="col-sm-12" style="height:80px;"><h3><?php echo $tenantname;?></h3></div>
    <div class="form-group">
        <label class="form-label col-sm-4" for="renewaltypeid">Renewal Type: <span class="text-danger">*</span></label>
        <div class="col-sm-6">
            <select class="form-control" id="renewaltypeid" name="renewaltypeid">
            <?php
            while($row2 = $result2->fetch_assoc()) {
                if ($row2["idrenewaltype"] == "$renewaltypeid") {
                    echo "<option value=\"" . $row2["idrenewaltype"] . "\" selected>". $row2["renewalType"] . "</option>";
                } else {
                    echo "<option value=\"" . $row2["idrenewaltype"] . "\">". $row2["renewalType"] . "</option>";
                }
            }
            ?>
            </select>
        </div>
        <div class="col-sm-2"><span class="error"><span class="text-danger"><?php echo $renewaltypeidErr;?></span></div>
    </div>
    <div  class="form-group">
        <label class="form-label col-sm-4" for="fixedpercent">Fixed % increase:</label>
        <div class="col-sm-6"><input class="form-control" id="fixedpercent" type="text" name="fixedpercent" value="<?php echo $fixedpercent; ?> "></div>
        <div class="col-sm-2"><span class="error"><span class="text-danger"><?php echo $fixedpercentErr; ?></span></div>
    </div>
    <div class="form-group">
        <label class="form-label col-sm-4" for="leaseid">Lease: <span class="text-danger">*</span></label>
        <div class="col-sm-6">
            <select class="form-control" id="leaseid" name="leaseid">
            <?php
            while($row = $result3->fetch_assoc()) {
                if ($row["idlease"] == "$leaseid") {
                    echo "<option value=\"" . $row["idlease"] . "\" selected>". $row["tenantname"] . "</option>";
                } else {
                    echo "<option value=\"" . $row["idlease"] . "\">". $row["tenantname"] . "</option>";
                }
            }       
            ?>
            </select>
        </div>
        <div class="col-sm-2"><span class="error"><span class="text-danger"><?php echo $leaseidErr;?></span></div>
    </div>
    <div class="form-group">
        <label class="form-label col-sm-4" for="startdate">Start Date: <span class="text-danger">*</span></label>
        <div class="col-sm-6"><input class="form-control" id="startdate" type="date" name="startdate" value="<?php echo $startdate;?>"></div>
        <div class="col-sm-2"><span class="error"><span class="text-danger"><?php echo $startdateErr;?></span></div>
    </div>
    <div class="form-group">
        <label class="form-label col-sm-4" for="enddate">End Date: </label>
        <div class="col-sm-6"><input class="form-control" id="enddate" type="date" name="enddate" value="<?php echo $enddate;?>"></div>
        <div class="col-sm-2"><span class="error"><span class="text-danger"><?php echo $enddateErr;?></span></div>
    </div>
    <div class="form-group">
        <label class="form-label col-sm-4" for="rentpremises">Rent - Premises: <span class="text-danger">*</span></label>
        <div class="col-sm-6"><input class="form-control" id="rentpremises" type="text" name="rentpremises" value="<?php echo $rentpremises;?>"></div>
        <div class="col-sm-2"><span class="error"><span class="text-danger"><?php echo $rentpremisesErr;?></span></div>
    </div>
    <div class="form-group">
        <label class="form-label col-sm-4" for="rentcarparks">Rent - Carparks: <span class="text-danger">*</span></label>
        <div class="col-sm-6"><input class="form-control" id="rentcarparks" type="text" name="rentcarparks" value="<?php echo $rentcarparks;?>"></div>
        <div class="col-sm-2"><span class="error"><span class="text-danger"><?php echo $rentcarparksErr;?></span></div>
    </div>
    <div class="form-group">
        <label class="form-label col-sm-4" for="renewalstatusid">Status: <span class="text-danger">*</span></label>
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
    <div class="form-group">
        <label class="form-label col-sm-4" for="renewalsignedon">Signed On:</label>
        <div class="col-sm-6"><input class="form-control" id="renewalsignedon" type="text" name="renewalsignedon" value="<?php echo $renewalsignedon;?>"></div>
        <div class="col-sm-2"><span class="error"><span class="text-danger"><?php echo $renewalsignedonErr;?></span></div>
    </div>
    <div class="form-group">
        <label class="form-label col-sm-4" for="renewalsignedbyid">Signed By:</label>
        <div class="col-sm-6">
            <select class="form-control" id="renewalsignedbyid" name="renewalsignedbyid">
            <?php
            echo "<option value=\"0\"> - Select a Signatory - </option>";
            while($row1 = $result1->fetch_assoc()) {
                if ($row1["idcontacts"] == "$renewalsignedbyid") {
                    echo "<option value=\"" . $row1["idcontacts"] . "\" selected>". $row1["firstName"] . " " . $row1["lastName"] . "</option>";
                } else {
                    echo "<option value=\"" . $row1["idcontacts"] . "\">". $row1["firstName"] . " " . $row1["lastName"] . "</option>";
                }
            }       
            ?>
            </select>
        </div>
        <div class="col-sm-2"><span class="error"><span class="text-danger"><?php echo $renewalsignedbyidErr;?></span></div>
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
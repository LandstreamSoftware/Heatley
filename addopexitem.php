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

//Get the list of OPEX categories
$sql4 = "SELECT * FROM transactioncategories WHERE transactionTypeID = 2 ORDER BY transactionCategoryName";
$result4 = $con->query($sql4);

$sqluser = "SELECT * FROM accounts WHERE id = $accountid";
$resultuser = $con->query($sqluser);

//while($rowuser = $resultuser->fetch_assoc()) {
//    $recordownerid = $rowuser["companyID"]; 
//}
?>

<?=template_header('Add OPEX Item')?>

<div class="page-title">
	<div class="icon">
		<svg width="20" height="20" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 576 512"><!--!Font Awesome Free 6.5.1 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license/free Copyright 2024 Fonticons, Inc.--><path d="M575.8 255.5c0 18-15 32.1-32 32.1h-32l.7 160.2c0 2.7-.2 5.4-.5 8.1V472c0 22.1-17.9 40-40 40H456c-1.1 0-2.2 0-3.3-.1c-1.4 .1-2.8 .1-4.2 .1H416 392c-22.1 0-40-17.9-40-40V448 384c0-17.7-14.3-32-32-32H256c-17.7 0-32 14.3-32 32v64 24c0 22.1-17.9 40-40 40H160 128.1c-1.5 0-3-.1-4.5-.2c-1.2 .1-2.4 .2-3.6 .2H104c-22.1 0-40-17.9-40-40V360c0-.9 0-1.9 .1-2.8V287.6H32c-18 0-32-14-32-32.1c0-9 3-17 10-24L266.4 8c7-7 15-8 22-8s15 2 21 7L564.8 231.5c8 7 12 15 11 24z"/></svg>
	</div>	
	<div class="wrap">
		<h2>Add OPEX Item</h2>
<!--		<p>Welcome back, <?=htmlspecialchars($_SESSION['account_name'], ENT_QUOTES)?>!</p>  -->
	</div>
</div>

<div class="block"> 

<?php
    // define variables and set to empty values
$opexitemname = $isunitspecific = "";
$opexitemcost = 0;
$opexitemnameErr = $opexitemcostErr = $opexcategoryidErr = $isunitspecificErr = "";

$Q = explode("/", $_SERVER['QUERY_STRING']);
parse_str($Q[0],$QueryParameters);
$QPopexid = $QueryParameters['opexid'];

$sql2 = "SELECT * FROM opex WHERE recordOwnerID IN ($accessto)";
    $result2 = $con->query($sql2);

$sql3 = "SELECT idopex, recordOwnerID FROM opex WHERE idopex = $QPopexid";
    $resultrecordownler = $con->query($sql3);
    while($rowrecordowner = $resultrecordownler->fetch_assoc()) {
      $recordownerid = $rowrecordowner["recordOwnerID"]; 
    }


if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (empty($_POST["opexitemname"])) {
        $opexitemnameErr = "Item Name is required";
      } else {
        $opexitemname = test_input($_POST["opexitemname"]);
        //check if the field only contains letters dash or white space
        if (!preg_match("/^[a-zA-Z-0-9&-āēīōūĀĒĪŌŪ\/' ]*$/", $opexitemname)) {
            $opexitemnameErr = "Only letters, dash and spaces allowed";
        }
      }


    if (empty($_POST["opexitemcost"])) {
        $opexitemcostErr = "Item cost is required";
    } else {
        $opexitemcost = test_input($_POST["opexitemcost"]);
        //check if the field only contains letters dash or white space
        if (!preg_match("/^[0-9.' ]*$/", $opexitemcost)) {
            $opexitemcostErr = "Only numbers an dot allowed";
        }
    }

    $opexitemtotal = test_input($_POST["opexitemtotal"]);
    //check if the field only contains letters dash or white space
    if (!preg_match("/^[0-9.' ]*$/", $opexitemtotal)) {
        $opexitemtotalErr = "Only numbers an dot allowed";
    }

    $opexcategoryid = test_input($_POST["opexcategoryid"]);
    //check if the field only contains numbers
    if (!preg_match("/^[0-9' ]*$/", $opexcategoryid)) {
        $opexcategoryidErr = "Only numbers allowed";
    }

    $isunitspecific = test_input($_POST["isunitspecific"]);
    //check if the field only contains numbers
    if (!preg_match("/^[0-9' ]*$/", $isunitspecific)) {
        $isunitspecificErr = "Only numbers allowed";
    }
}

function test_input($data) {
  $data = trim($data);
  $data = stripslashes($data);
  $data = htmlspecialchars($data);
  return $data;
}



if ($_SERVER["REQUEST_METHOD"] == "POST" and $opexitemnameErr == NULL and $opexitemcostErr == NULL and $opexcategoryidErr == NULL and $isunitspecificErr == NULL) {

    //prepare and bind
    $stmt = $con->prepare("INSERT INTO opexitems (opexItemName, opexItemCost, opexItemTotal, opexID, opexCategoryID, isUnitSpecific, recordOwnerID) VALUES (?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("sddiiii", $opexitemname, $opexitemcost, $opexitemtotal, $QPopexid, $opexcategoryid, $isunitspecific, $recordownerid);

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
            <div class=\"col-sm-2\"><a href=\"viewopex.php?opexid=" . $QPopexid . "\" class=\"btn btn-primary\">Back to OPEX Detail</a></div>
        </div>";
    } else {
        echo 'Error creating record: ' . $con->error;
    }
} else {

    ?>
    <form class="form form-medium" method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"].'?opexid='.$QPopexid);?>">
    <div  class="form-group">
        <label class="form-label col-sm-4" for="opexitemname">Item Name: <span class="text-danger">*</span></label>
        <div class="col-sm-6"><input class="form-control" id="opexitemname" type="text" name="opexitemname" value="<?php echo $opexitemname;?>"></div>
        <div class="col-sm-2"><span class="error"><span class="text-danger"><?php echo $opexitemnameErr;?></span></div>
    </div>

    <div class="form-group">
        <label class="form-label col-sm-4" for="opexcategoryid" style="padding-top:5px">Category:</label>
        <div class="col-sm-6">
            <select class="form-control" id="opexcategoryid" name="opexcategoryid">
            <?php
                echo "<option value=\"0\"> - Select a Category - </option>";
            while($row4 = $result4->fetch_assoc()) {
                if ($row4["idtransactioncategory"] == $opexcategoryid) {
                    echo "<option value=\"" . $row4["idtransactioncategory"] . "\" selected>". $row4["transactionCategoryName"] . "</option>";
                } else {
                    echo "<option value=\"" . $row4["idtransactioncategory"] . "\">". $row4["transactionCategoryName"] . "</option>";
                }
            }
            ?>
            </select>
        </div>
        <div class="col-sm-2"><span class="error"><span class="text-danger"><?php echo $opexcategoryidErr;?></span></div>
    </div>

    <div  class="form-group">
        <label class="form-label col-sm-4" for="opexitemcost">Annual Cost: <span class="text-danger">*</span></label>
        <div class="col-sm-6"><input class="form-control" id="opexitemcost" type="text" name="opexitemcost" value="<?php echo $opexitemcost;?>" onkeyup="myFunction()"></div>
        <div class="col-sm-2"><span class="error"><span class="text-danger"><?php echo $opexitemcostErr;?></span></div>
    </div>
    <div  class="form-group">
        <div class="form-label col-sm-4">Including GST:</div>
        <div class="col-sm-6"><input class="form-control" id="opexitemtotal" type="text" name="opexitemtotal" value="" readonly></div>
        <div class="col-sm-2"></div>
    </div>
    <div  class="form-group">
        <label class="form-label col-sm-4" for="isunitspecific">Unit Specific Allocation: <span class="text-danger">*</span></label>
        <div class="col-sm-6">
            <select class="form-control" id="isunitspecific" name="isunitspecific">
                <option value="0">No</option>
                <option value="1">Yes</option>
            </select>
            </div>
        <div class="col-sm-2"><span class="error"><span class="text-danger"><?php echo $isunitspecificErr;?></span></div>
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
    function myFunction() {
        const options = {
            style: 'decimal',
            minimumFractionDigits: 2,
            maximumFractionDigits: 2,
        }
        var costInclGST1 = Math.round(document.getElementById("opexitemcost").value * 115) / 100;
        var costInclGST = costInclGST1.toLocaleString('en-US', options);
        document.getElementById("opexitemtotal").value = costInclGST;
    }
</script>

</div>

<?=template_footer()?>
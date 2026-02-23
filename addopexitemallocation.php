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

<?=template_header('Add Opex Item Allocatioon')?>

<div class="page-title">
	<div class="icon">
		<svg width="20" height="20" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 576 512"><!--!Font Awesome Free 6.5.1 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license/free Copyright 2024 Fonticons, Inc.--><path d="M575.8 255.5c0 18-15 32.1-32 32.1h-32l.7 160.2c0 2.7-.2 5.4-.5 8.1V472c0 22.1-17.9 40-40 40H456c-1.1 0-2.2 0-3.3-.1c-1.4 .1-2.8 .1-4.2 .1H416 392c-22.1 0-40-17.9-40-40V448 384c0-17.7-14.3-32-32-32H256c-17.7 0-32 14.3-32 32v64 24c0 22.1-17.9 40-40 40H160 128.1c-1.5 0-3-.1-4.5-.2c-1.2 .1-2.4 .2-3.6 .2H104c-22.1 0-40-17.9-40-40V360c0-.9 0-1.9 .1-2.8V287.6H32c-18 0-32-14-32-32.1c0-9 3-17 10-24L266.4 8c7-7 15-8 22-8s15 2 21 7L564.8 231.5c8 7 12 15 11 24z"/></svg>
	</div>	
	<div class="wrap">
		<h2>Add Opex Item Allocatioon</h2>
	</div>
</div>

<div class="block">    

<?php
    // define variables and set to empty values
$opexitemid = $unitid = $premisesid = $allocationpercentage = "";
$unitidErr = $premisesidErr = $allocationpercentageErr = "";

$Q = explode("/", $_SERVER['QUERY_STRING']);
parse_str($Q[0],$QueryParameters);
if(empty($QueryParameters['opexitemid'])){
    $opexitemid = "";
}else{
    $opexitemid = $QueryParameters['opexitemid'];
}

$sql = "SELECT idopexitems, opexitemname, idbuildings FROM opex_budget_view where idopexitems = '$opexitemid' and recordOwnerID IN ($accessto) LIMIT 1";
$result = $con->query($sql);
while($row = $result->fetch_assoc()) {
    $opexitemname = $row["opexitemname"];
    $buildingid = $row["idbuildings"];
}

$sql1 = "SELECT * FROM premises where buildingID = '$buildingid' and recordOwnerID IN ($accessto) ORDER BY unitName";
$result1 = $con->query($sql1);

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (empty($_POST["opexitemid"])) {
        $opexitemidErr = "Opex Item is required";
    } else {
        $opexitemid = test_input($_POST["opexitemid"]);
        //check if the field only contains letters dash or white space
        if (!preg_match("/^[0-9' ]*$/", $opexitemid)) {
            $opexitemidErr = "Only numbers allowed";
        }
    }

    if (empty($_POST["premisesid"])) {
        $premisesidErr = "Premises is required";
      } else {
        $premisesid = test_input($_POST["premisesid"]);
        //check if the field only contains letters dash or white space
        if (!preg_match("/^[0-9' ]*$/", $premisesid)) {
            $premisesidErr = "Only numbers allowed";
        }
      }
    
      if (empty($_POST["allocationpercentage"])) {
        $allocationpercentageErr = "Allocation Percentage is required";
      } else {
        $allocationpercentage = test_input($_POST["allocationpercentage"]);
        //check if the field only contains letters dash or white space
        if (!preg_match("/^[0-9.' ]*$/", $allocationpercentage)) {
            $allocationpercentageErr = "Only numbers and dot allowed";
        }
      }
}

function test_input($data) {
  $data = trim($data);
  $data = stripslashes($data);
  $data = htmlspecialchars($data);
  return $data;
}



if ($_SERVER["REQUEST_METHOD"] == "POST" and $unitidErr == NULL and $premisesidErr == NULL and $allocationpercentageErr == NULL) {

    //prepare and bind
    $stmt = $con->prepare("INSERT INTO opexitemallocation (opexItemID, premisesID, allocationPercentage) VALUES (?, ?, ?)");
    $stmt->bind_param("iid", $opexitemid, $premisesid, $allocationpercentage);

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
            <div class=\"col-sm-2\"><a href=\"editopexitem.php?opexitemid=" . $opexitemid . "\" class=\"btn btn-primary\">Back to Opex Item</a></div>
        </div>";
    

} else {

    ?>
    <form class="form form-medium" method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"].'?opexitemid='.$opexitemid);?>">
    <div  class="form-group">
        <label class="form-label col-sm-4" for="ipexitem" style="padding-top:5px">Opex Item:</label>
        <div class="col-sm-6"><input class="form-control" type="text" name="ipexitem" value="<?php echo $opexitemname;?>" readonly></div>
    </div>
    <div class="form-group">
        <label class="form-label col-sm-4" for="premisesid" style="padding-top:5px">Premises: <span class="text-danger">*</span></label>
        <div class="col-sm-6">
            <select class="form-control" id="premisesid" name="premisesid">
            <?php
                echo "<option value=\"\"> - Select Premises - </option>";
            while($row1 = $result1->fetch_assoc()) {
                echo "<option value=\"" . $row1["idpremises"] . "\">". $row1["unitName"] . "</option>";
            }
            ?>
            </select>
        </div>
        <div class="col-sm-2"><span class="error"><span class="text-danger"><?php echo $premisesidErr;?></span></div>
    </div>
    <div  class="form-group">
        <label class="form-label col-sm-4" for="allocationpercentage" style="padding-top:5px">Allocation Percentage:</label>
        <div class="col-sm-6"><input class="form-control" id="allocationpercentage" type="text" name="allocationpercentage" value="<?php echo $allocationpercentage;?>"></div>
        <div class="col-sm-2"><span class="error"><span class="text-danger"><?php echo $allocationpercentageErr;?></span></div>
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
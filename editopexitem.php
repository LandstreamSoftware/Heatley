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

?>

<?=template_header('Edit OPEX Item')?>

<div class="page-title">
	<div class="icon">
		<svg width="20" height="20" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 576 512"><!--!Font Awesome Free 6.5.1 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license/free Copyright 2024 Fonticons, Inc.--><path d="M575.8 255.5c0 18-15 32.1-32 32.1h-32l.7 160.2c0 2.7-.2 5.4-.5 8.1V472c0 22.1-17.9 40-40 40H456c-1.1 0-2.2 0-3.3-.1c-1.4 .1-2.8 .1-4.2 .1H416 392c-22.1 0-40-17.9-40-40V448 384c0-17.7-14.3-32-32-32H256c-17.7 0-32 14.3-32 32v64 24c0 22.1-17.9 40-40 40H160 128.1c-1.5 0-3-.1-4.5-.2c-1.2 .1-2.4 .2-3.6 .2H104c-22.1 0-40-17.9-40-40V360c0-.9 0-1.9 .1-2.8V287.6H32c-18 0-32-14-32-32.1c0-9 3-17 10-24L266.4 8c7-7 15-8 22-8s15 2 21 7L564.8 231.5c8 7 12 15 11 24z"/></svg>
	</div>	
	<div class="wrap">
		<h2>Edit OPEX Item</h2>
<!--		<p>Welcome back, <?=htmlspecialchars($_SESSION['account_name'], ENT_QUOTES)?>!</p>  -->
	</div>
</div>

<div class="block">

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
    function deleteAllocation(idallocation) {
        Swal.fire({
        title: "Delete Opex Allocation",
        text: "Are you sure?",
        icon: "warning",
        iconColor: "#d33",
        showCancelButton: true,
        confirmButtonColor: "#0d6efd",
        cancelButtonColor: "#aaa",
        confirmButtonText: "Delete",
        cancelButtonText: "Cancel"
        }).then((result) => {
            if (result.isConfirmed) {
                fetch('deleteopexitemallocation.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded'
                    },
                    body: 'opexitemallocationid=' + encodeURIComponent(idallocation)
                })
                .then(response => response.text())
                .then(data => {
                    console.log('Success:', data);
                    //alert('File deleted successfully!');
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Failed to delete the record.');
                });

                // Prevent parent form submission
                event.stopPropagation();
                location.reload();
                return false;
            }
        });
    }
</script>

<?php
// define variables and set to empty values

$Q = explode("/", $_SERVER['QUERY_STRING']);
parse_str($Q[0],$QueryParameters);
$QPopexitemid = $QueryParameters['opexitemid'];

$sql = "SELECT * from opexitems WHERE idopexitems = $QPopexitemid and recordOwnerID IN ($accessto)";
$result = $con->query($sql);

$sql2 = "SELECT * from opexitemallocation_view WHERE opexitemid = $QPopexitemid ORDER BY unitname";
$result2 = $con->query($sql2);

if ($result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        $opexitemname = $row["opexItemName"];
        $opexitemcost = $row["opexItemCost"];
        $opexitemtotal = $row["opexItemTotal"];
        $opexid = $row["opexID"];
        $opexcategoryid = $row["opexCategoryID"];
        $isunitspecific = $row["isUnitSpecific"];
    } 
} else {
    $opexitemname = $opexitemcost = $opexitemtotal = $opexcategoryid = $isunitspecific = "";
}
    $opexitemnameErr = $opexitemcostErr = $opexitemtotalErr = $opexcategoryidErr = $isunitspecificErr = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {

  if (empty($_POST["opexitemname"])) {
    $opexitemnameErr = "OPEX Item Name is required";
  } else {
    $opexitemname = test_input($_POST["opexitemname"]);
    //check if the field only contains letters dash or white space
    if (!preg_match("/^[a-zA-Z-0-9&-āēīōūĀĒĪŌŪ\/' ]*$/", $opexitemname)) {
        $opexitemnameErr = "Only letters, dash and spaces allowed";
    }
  }

  if (empty($_POST["opexitemcost"])) {
    $opexitemcostErr = "OPEX Item Annual Cost is required";
  } else {
    $opexitemcost = test_input($_POST["opexitemcost"]);
    //any characters are allowed in the address field
    if (!preg_match("/^[0-9.' ]*$/", $opexitemcost)) {
        $opexitemcostErr = "Only numbers and dot allowed";
    }
  }

  if (empty($_POST["opexitemtotal"])) {
    $opexitemtotalErr = "OPEX Item Total incl. GST is required";
  } else {
    $opexitemtotal = test_input($_POST["opexitemtotal"]);
    //any characters are allowed in the address field
    if (!preg_match("/^[0-9.' ]*$/", $opexitemtotal)) {
        $opexitemtotalErr = "Only numbers and dot allowed";
    }
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




if ($_SERVER["REQUEST_METHOD"] == "POST" and $opexitemnameErr == NULL and $opexitemcostErr == NULL and $opexitemtotalErr == NULL and $opexcategoryidErr == NULL and $isunitspecificErr == NULL) {

    //prepare and bind
    $sql = "UPDATE opexitems SET opexItemName = '$opexitemname', opexItemCost = '$opexitemcost', opexItemTotal = '$opexitemtotal', opexCategoryID = '$opexcategoryid', isUnitSpecific = '$isunitspecific' WHERE idopexitems = $QPopexitemid";

    if ($con->query($sql) === TRUE) {

        //if the Opex item has been set to non-premises specific, remove all relating records in opexitemallocation
        if ($isunitspecific == 0) {
            $stmt = $con->prepare("DELETE FROM opexitemallocation WHERE opexItemID = ?");
            $stmt->bind_param('i', $QPopexitemid);

            if ($stmt->execute()) {
                echo '<table class="table table-hover">
                <tbody>
                    <tr class="success">
                        <td>Success!</td>
                    </tr>
                </tbody>
                </table>';

                echo "<div class=\"row\">
                    <div class=\"col-sm-2\"><a href=\"viewopex.php?opexid=" . $opexid . "\" class=\"btn btn-primary\">Back to OPEX Details</a></div>
                </div>
                <div class=\"row\">";
            } else {
                echo 'Error updating record: ' . $con->error;
            }
        } else {
            echo '<table class="table table-hover">
            <tbody>
                <tr class="success">
                    <td>Success!</td>
                </tr>
            </tbody>
            </table>';

            echo "<div class=\"row\">
                <div class=\"col-sm-2\"><a href=\"viewopex.php?opexid=" . $opexid . "\" class=\"btn btn-primary\">Back to OPEX Details</a></div>
            </div>
            <div class=\"row\">
                <div class=\"col-sm-2\" style=\"padding-top:20px; padding-bottom:20px;\"><a href=\"addopexitemallocation.php?opexitemid=" . $QPopexitemid . "\" class=\"btn btn-primary\">Add OPEX Item Allocation</a></div>
            </div>
            <div class=\"row\">";
        }
    }

} else {
    ?>

    <form class="form form-medium" method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"].'?opexitemid='.$QPopexitemid);?>">
    <div class="form-group">
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
    <!--
    <div  class="form-group">
        <div class="form-label col-sm-4">Including GST:</div>
        <div class="col-sm-6" style="text-align:left;"><p style="margin-top:8px;"><span id="inclGST" style="padding:5px; border:1px solid transparent; border-radius:4px;">$<?php echo number_format($opexitemcost * 1.15,2)?></span></p></div>
        <div class="col-sm-2"></div>
    </div>
        -->
    <div  class="form-group">
        <label class="form-label col-sm-4" for="opexitemtotal">Annual Cost (incl GST): <span class="text-danger">*</span></label>
        <div class="col-sm-6"><input class="form-control" id="opexitemtotal" type="text" name="opexitemtotal" value="<?php echo $opexitemtotal;?>" readonly></div>
        <div class="col-sm-2"><span class="error"><span class="text-danger"><?php echo $opexitemtotalErr;?></span></div>
    </div>

    <div  class="form-group">
        <label class="form-label col-sm-4" for="isunitspecific">Unit Specific Allocation: <span class="text-danger">*</span></label>
        <div class="col-sm-6">
            <select class="form-control" id="isunitspecific" name="isunitspecific">
                <?php
                if ($isunitspecific == 0) {
                    echo "<option value=\"0\" selected>No</option>";
                } else {
                    echo "<option value=\"0\">No</option>";
                }
                if ($isunitspecific == 1) {
                    echo "<option value=\"1\" selected>Yes</option>";
                } else {
                    echo "<option value=\"1\">Yes</option>";
                }
                ?>
            </select>
            </div>
        <div class="col-sm-2"><span class="error"><span class="text-danger"><?php echo $isunitspecificErr;?></span></div>
    </div>

    <div class="form-group">
        <div class="col-sm-1" style="padding-top:40px;"><input type="submit" value="Submit" class="btn btn-primary" style="width:100px"></div>
    </div>
    </form>

<?php
if ($result2->num_rows > 0) {
    $totalcost = 0;
    $totalcostinclgst = 0;
    $totalpercentage = 0;
    echo "<div class=\"row\">";
        while($row2 = $result2->fetch_assoc()) {
            $opexitemallocationid = $row2["idopexitemallocation"];
            $allocatedcost = $row2["allocatedcost"];
            $totalpercentage += $row2["allocationpercentage"];
            $totalcost += $allocatedcost;
            $totalcostinclgst += $allocatedcost * 1.15;
            echo
            "<div class=\"row\" style=\"margin:3px 0 0 0; border-top:solid 0px #ccc;\">
            <div class=\"col-sm-2\" style=\"margin:6px 0 6px 0;\"><a href=\"editopexitemallocation.php?id=" . $row2["idopexitemallocation"]. "\">" . $row2["unitname"] . "</a></div>
            <div class=\"col-sm-2\" style=\"margin:6px 0 6px 0;\">" . $row2["allocationpercentage"] . "%</div>
            <div class=\"col-sm-1\" style=\"margin:6px 0 6px 0; text-align:right;\">$" . number_format($allocatedcost,2) . "</div>
            <div class=\"col-sm-2\" style=\"margin:6px 0 6px 0; text-align:right;\">$" . number_format($allocatedcost * 1.15,2) . "</div>
            <div class=\"col-sm-2\" style=\"margin:6px 0 6px 0; text-align:right;\"><img src=\"img/delete_icon.png\" alt=\"Delete\" width=\"20px\" height=\"20px\" style=\"cursor: pointer;\" onclick=\"deleteAllocation(" . $opexitemallocationid . ");\"></div>
            </div>";
        }
    echo "</div>
    <div class=\"row\" style=\"margin:3px 0 0 0; border-top:solid 1px #ccc;\">
        <div class=\"col-sm-2\" style=\"margin:6px 0 6px 0;\">&nbsp;</div>
        <div class=\"col-sm-2\" style=\"margin:6px 0 6px 0; font-weight: bold;\">" . number_format($totalpercentage,2) . "%</div>
        <div class=\"col-sm-1\" style=\"margin:6px 0 6px 0; font-weight: bold; text-align:right;\">$" . number_format($totalcost,2) . "</div>
        <div class=\"col-sm-2\" style=\"margin:6px 0 6px 0; font-weight: bold; text-align:right;\">$" . number_format($totalcostinclgst,2) . "</div>
    </div>";
}

if ($isunitspecific == 1) {
    echo "<div class=\"row\">
        <div class=\"col-sm-2\" style=\"padding-top:20px; padding-bottom:20px;\"><a href=\"addopexitemallocation.php?opexitemid=" . $QPopexitemid . "\" class=\"btn btn-primary\">Add OPEX Item Allocation</a></div>
    </div>";
}

?>

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
        document.getElementById("opexitemtotal").value = costInclGST1;

       // document.getElementById("inclGST").innerHTML = costInclGST;
    }
</script>

</div>

<?=template_footer()?>
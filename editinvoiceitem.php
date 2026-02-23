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
$sql4 = "SELECT * FROM invoicecategories ORDER BY invoiceCategoryName";
$result4 = $con->query($sql4);

$newinvoiceamount = 0;
$newinvoicegst = 0;
$newinvoicetotal = 0;

?>

<?=template_header('Edit Invoice Item')?>

<div class="page-title">
	<div class="icon">
		<svg width="20" height="20" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 576 512"><!--!Font Awesome Free 6.5.1 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license/free Copyright 2024 Fonticons, Inc.--><path d="M575.8 255.5c0 18-15 32.1-32 32.1h-32l.7 160.2c0 2.7-.2 5.4-.5 8.1V472c0 22.1-17.9 40-40 40H456c-1.1 0-2.2 0-3.3-.1c-1.4 .1-2.8 .1-4.2 .1H416 392c-22.1 0-40-17.9-40-40V448 384c0-17.7-14.3-32-32-32H256c-17.7 0-32 14.3-32 32v64 24c0 22.1-17.9 40-40 40H160 128.1c-1.5 0-3-.1-4.5-.2c-1.2 .1-2.4 .2-3.6 .2H104c-22.1 0-40-17.9-40-40V360c0-.9 0-1.9 .1-2.8V287.6H32c-18 0-32-14-32-32.1c0-9 3-17 10-24L266.4 8c7-7 15-8 22-8s15 2 21 7L564.8 231.5c8 7 12 15 11 24z"/></svg>
	</div>	
	<div class="wrap">
		<h2>Edit Invoice Item</h2>
	</div>
</div>

<div class="block">



<?php
// define variables and set to empty values

$Q = explode("/", $_SERVER['QUERY_STRING']);
parse_str($Q[0],$QueryParameters);
$QPinvoiceitemid = $QueryParameters['invoiceitemid'];

$sql = "SELECT * from invoiceitems WHERE idinvoiceitem = $QPinvoiceitemid and recordOwnerID IN ($accessto)";
$result = $con->query($sql);

if ($result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        $invoiceitemdescription = $row["invoiceItemDescription"];
        $invoiceitempremises = $row["invoiceItemPremisesID"];
        $invoiceitemquantity = $row["invoiceItemQuantity"];
        $invoiceitemprice = $row["invoiceItemPrice"];
        $invoiceitemsubtotal = $row["invoiceItemSubtotal"];
        $invoiceitemtax = $row["invoiceItemTax"];
        $invoiceitemtotal = $row["invoiceItemTotal"];
        $invoicecategoryid = $row["invoiceCategoryID"];
        $invoiceid = $row["invoiceID"];
    } 
} else {
    $invoiceitemdescription = $invoiceitempremises = $invoiceitemquantity = $invoiceitemprice = $invoiceitemtax = $invoiceitemtotal = $invoicecategoryid = "";
}
    $invoiceitemdescriptionErr = $invoiceitempremisesErr = $invoiceitemquantityErr = $invoiceitempriceErr = $invoiceitemsubtotalErr = $invoiceitemtaxErr = $invoiceitemtotalErr = $invoicecategoryidErr = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {

  if (empty($_POST["invoiceitemdescription"])) {
    $invoiceitemdescriptionErr = "Description is required";
  } else {
    $invoiceitemdescription = test_input($_POST["invoiceitemdescription"]);
    //check if the field only contains letters dash or white space
    if (!preg_match("/^[,a-zA-Z-0-9-āēīōūĀĒĪŌŪ\/'., ]*$/", $invoiceitemdescription)) {
        $invoiceitemdescriptionErr = "Only letters, dash, dot, slash and spaces allowed";
    }
  }

    $invoiceitempremises = test_input($_POST["invoiceitempremises"]);
    //any characters are allowed in the premises
    if (!preg_match("/^[a-zA-Z-0-9-āēīōūĀĒĪŌŪ\/'., ]*$/", $invoiceitempremises)) {
        $invoiceitempremisesErr = "Only letters, dash, dot, slash and spaces allowed";
    }

    $invoiceitemquantity = test_input($_POST["invoiceitemquantity"]);
    //any characters are allowed in the address field
    if (!preg_match("/^[0-9.' ]*$/", $invoiceitemquantity)) {
        $invoiceitemquantityErr = "Only numbers and dot allowed";
    }
  

    $invoiceitemprice = test_input($_POST["invoiceitemprice"]);
    //check if the field only contains numbers
    if (!preg_match("/^[0-9-.' ]*$/", $invoiceitemprice)) {
        $invoiceitempriceErr = "Only numbers and dot allowed";
    }

    $invoiceitemsubtotal = test_input($_POST["invoiceitemsubtotal"]);
    //check if the field only contains numbers
    if (!preg_match("/^[0-9-.' ]*$/", $invoiceitemsubtotal)) {
        $invoiceitemsubtotalErr = "Only numbers and dot allowed";
    }

    $invoiceitemtax = test_input($_POST["invoiceitemtax"]);
    //check if the field only contains numbers
    if (!preg_match("/^[0-9-.' ]*$/", $invoiceitemtax)) {
        $invoiceitemtaxErr = "Only numbers and dot allowed";
    }

    $invoiceitemtotal = test_input($_POST["invoiceitemtotal"]);
    //check if the field only contains numbers
    if (!preg_match("/^[0-9-.' ]*$/", $invoiceitemtotal)) {
        $invoiceitemtotalErr = "Only numbers and dot allowed";
    }

    $invoicecategoryid = test_input($_POST["invoicecategoryid"]);
    //check if the field only contains numbers
    if (!preg_match("/^[0-9' ]*$/", $invoicecategoryid)) {
        $invoicecategoryidErr = "Only numbers allowed";
    }
}

function test_input($data) {
  $data = trim($data);
  $data = stripslashes($data);
  $data = htmlspecialchars($data);
  return $data;
}




if ($_SERVER["REQUEST_METHOD"] == "POST" and $invoiceitemdescriptionErr == NULL and $invoiceitempremisesErr == NULL and $invoiceitemquantityErr == NULL and $invoiceitempriceErr == NULL and $invoiceitemsubtotalErr == NULL and $invoiceitemtaxErr == NULL and $invoiceitemtotalErr == NULL and $invoicecategoryidErr == NULL) {

    //prepare and bind
    $sql = "UPDATE invoiceitems SET invoiceItemDescription = '$invoiceitemdescription', invoiceItemPremisesID = '$invoiceitempremises', invoiceItemQuantity = '$invoiceitemquantity', invoiceItemPrice = '$invoiceitemprice', invoiceItemSubtotal = '$invoiceitemsubtotal', invoiceItemTax = '$invoiceitemtax', invoiceItemTotal = '$invoiceitemtotal', invoiceCategoryID = '$invoicecategoryid' WHERE idinvoiceitem = $QPinvoiceitemid";

    if ($con->query($sql) === TRUE) {
        //calculate a new total and GST for the invoice
        $sql6 = "SELECT * from invoiceitems WHERE invoiceID = $invoiceid and recordOwnerID IN ($accessto)";
        $result6 = $con->query($sql6);

        if ($result6->num_rows > 0) {
            while($row6 = $result6->fetch_assoc()) {
                $newinvoiceamount += $row6["invoiceItemSubtotal"];
                $newinvoicegst += $row6["invoiceItemTax"];
                $newinvoicetotal += $row6["invoiceItemTotal"];
            } 
        }
        $sql2 = "UPDATE transactions SET transactionAmount = '$newinvoiceamount', transactionGST = '$newinvoicegst', transactionTotal = '$newinvoicetotal' WHERE idtransaction = $invoiceid";
        if ($con->query($sql2) === TRUE) {

            echo '<table class="table table-hover">
            <tbody>
                <tr class="success">
                    <td>Success!</td>
                </tr>
            </tbody>
            </table>';

            echo "<div class=\"row\">
            <div class=\"col-sm-2\"><a href=\"viewtransaction.php?id=" . $invoiceid . "\" class=\"btn btn-primary\">Back to Invoice</a></div>
            </div>
            <div class=\"row\">";
        } else {
            echo 'Error updating invoice record: ' . $con->error;
        }
    } else {
    echo 'Error updating record: ' . $con->error;
    }
} else {
    // Get the premisesID from transactions
    $sql3 = "SELECT premisesid, unitname FROM transactions_view WHERE idtransaction = $invoiceid";
    $result3 = $con->query($sql3);

    ?>

    <form class="form form-medium" method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"].'?invoiceitemid='.$QPinvoiceitemid);?>">
    <div class="form-group">
        <label class="form-label col-sm-4" for="invoiceitemdescription">Description: <span class="text-danger">*</span></label>
        <div class="col-sm-6"><input class="form-control" id="invoiceitemdescription" type="text" name="invoiceitemdescription" value="<?php echo $invoiceitemdescription;?>"></div>
        <div class="col-sm-2"><span class="error"><span class="text-danger"><?php echo $invoiceitemdescriptionErr;?></span></div>
    </div>
    <div class="form-group">
        <label class="form-label col-sm-4" for="invoiceitempremises">Premises: <span class="text-danger">*</span></label>
        <div class="col-sm-6">
            <select class="form-control" id="invoiceitempremises" name="invoiceitempremises">
            <?php
            while($row3 = $result3->fetch_assoc()) {
                if ($row3["premisesid"] == $invoiceitempremises) {
                    echo "<option value=\"" . $row3["premisesid"] . "\" selected>". $row3["unitname"] . "</option>";
                } else {
                    echo "<option value=\"" . $row3["premisesid"] . "\">". $row3["unitname"] . "</option>";
                }
            }
            ?>
            </select>
        </div>
        <div class="col-sm-2"><span class="error"><span class="text-danger"><?php echo $invoiceitempremisesErr;?></span></div>
    </div>
    <div class="form-group">
        <label class="form-label col-sm-4" for="invoiceitemquantity">Quantity: <span class="text-danger">*</span></label>
        <div class="col-sm-6"><input class="form-control" id="invoiceitemquantity" type="text" name="invoiceitemquantity" value="<?php echo $invoiceitemquantity;?>" onchange="myFunction()"></div>
        <div class="col-sm-2"><span class="error"><span class="text-danger"><?php echo $invoiceitemquantityErr;?></span></div>
    </div>
    <div  class="form-group">
        <label class="form-label col-sm-4" for="invoiceitemprice">Price: <span class="text-danger">*</span></label>
        <div class="col-sm-6"><input class="form-control" id="invoiceitemprice" type="text" name="invoiceitemprice" value="<?php echo number_format($invoiceitemprice, 2, '.', '');?>" onchange="myFunction()"></div>
        <div class="col-sm-2"><span class="error"><span class="text-danger"><?php echo $invoiceitempriceErr;?></span></div>
    </div>


    <div  class="form-group">
        <label class="form-label col-sm-4" for="invoiceitemsubtotal">Subtotal: <span class="text-danger">*</span></label>
        <div class="col-sm-6"><input class="form-control border-0" id="invoiceitemsubtotal" type="text" name="invoiceitemsubtotal" value="<?php echo number_format($invoiceitemsubtotal, 2, '.', '');?>" readonly></div>
        <div class="col-sm-2"><span class="error"><span class="text-danger"><?php echo $invoiceitemsubtotalErr;?></span></div>
    </div>
    <div  class="form-group">
        <label class="form-label col-sm-4" for="invoiceitemtax">GST: <span class="text-danger">*</span></label>
        <div class="col-sm-6"><input class="form-control border-0" id="invoiceitemtax" type="text" name="invoiceitemtax" value="<?php echo $invoiceitemtax;?>" readonly></div>
        <div class="col-sm-2"><span class="error"><span class="text-danger"><?php echo $invoiceitemtaxErr;?></span></div>
    </div>
    
    <div  class="form-group">
        <label class="form-label col-sm-4" for="invoiceitemtotal">Total: <span class="text-danger">*</span></label>
        <div class="col-sm-6"><input class="form-control border-0" id="invoiceitemtotal" type="text" name="invoiceitemtotal" value="<?php echo $invoiceitemtotal;?>" readonly></div>
        <div class="col-sm-2"><span class="error"><span class="text-danger"><?php echo $invoiceitemtotalErr;?></span></div>
    </div>
    <div class="form-group">
        <label class="form-label col-sm-4" for="invoicecategoryid" style="padding-top:5px">Category:</label>
        <div class="col-sm-6">
            <select class="form-control" id="invoicecategoryid" name="invoicecategoryid">
            <?php
                echo "<option value=\"0\"> - Select a Category - </option>";
            while($row4 = $result4->fetch_assoc()) {
                if ($row4["idinvoicecategory"] == $invoicecategoryid) {
                    echo "<option value=\"" . $row4["idinvoicecategory"] . "\" selected>". $row4["invoiceCategoryName"] . "</option>";
                } else {
                    echo "<option value=\"" . $row4["idinvoicecategory"] . "\">". $row4["invoiceCategoryName"] . "</option>";
                }
            }
            ?>
            </select>
        </div>
        <div class="col-sm-2"><span class="error"><span class="text-danger"><?php echo $invoicecategoryidErr;?></span></div>
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

<script type="text/javascript">
    function myFunction() {
        const options = {
            style: 'decimal',
            minimumFractionDigits: 2,
            maximumFractionDigits: 2,
        }
        var costSubtotal = Math.round(document.getElementById("invoiceitemprice").value * 100) * document.getElementById("invoiceitemquantity").value / 100
        var costGST = Math.round(costSubtotal * 15) / 100;
        var costInclGST = Math.round((costSubtotal + costGST) * 100) / 100;
        
        document.getElementById("invoiceitemtax").value = costGST;
        document.getElementById("invoiceitemtotal").value = costInclGST;
        document.getElementById("invoiceitemsubtotal").value = costSubtotal;
    }
</script>

</div>

<?=template_footer()?>
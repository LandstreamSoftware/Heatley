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
    while ($rowAccess = $resultAccess->fetch_assoc()) {
        $accessto .= "," . $rowAccess["companyID"];
    }
}

$sql9 = "SELECT * FROM accounts WHERE id = $accountid";
$result9 = $con->query($sql9);

while($row9 = $result9->fetch_assoc()) {
    $recordownerid = $row9["companyID"];
}
?>

<?= template_header('Add OPEX Bill') ?>

<div class="page-title">
    <div class="icon">
        <svg onload="updateItems()" width="20" height="20" xmlns="http://www.w3.org/2000/svg"
            viewBox="0 0 576 512"><!--!Font Awesome Free 6.5.1 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license/free Copyright 2024 Fonticons, Inc.-->
            <path
                d="M575.8 255.5c0 18-15 32.1-32 32.1h-32l.7 160.2c0 2.7-.2 5.4-.5 8.1V472c0 22.1-17.9 40-40 40H456c-1.1 0-2.2 0-3.3-.1c-1.4 .1-2.8 .1-4.2 .1H416 392c-22.1 0-40-17.9-40-40V448 384c0-17.7-14.3-32-32-32H256c-17.7 0-32 14.3-32 32v64 24c0 22.1-17.9 40-40 40H160 128.1c-1.5 0-3-.1-4.5-.2c-1.2 .1-2.4 .2-3.6 .2H104c-22.1 0-40-17.9-40-40V360c0-.9 0-1.9 .1-2.8V287.6H32c-18 0-32-14-32-32.1c0-9 3-17 10-24L266.4 8c7-7 15-8 22-8s15 2 21 7L564.8 231.5c8 7 12 15 11 24z" />
        </svg>
    </div>
    <div class="wrap">
        <h2>Add OPEX Bill</h2>
    </div>
</div>


<div class="block">

    <?php
    // define variables and set to empty values
    $invoicenumber = $transactiondate = $transactioncompanyid = $transactionamount = $transactiontotal = $transactiongst = $invoiceduedate = $transactioncategoryid = $premisesid = "";
    $invoicestatusid = 2; //Draft
    $invoicenumberErr = $transactiondateErr = $transactioncompanyidErr = $transactionamountErr = $transactiontotalErr = $transactiongstErr = $invoiceduedateErr = $transactioncategoryidErr = $opexidErr = $premisesidErr = $invoicestatusidErr = "";

    $Q = explode("/", $_SERVER['QUERY_STRING']);
    parse_str($Q[0], $QueryParameters);
    if (empty($QueryParameters['opexid'])) {
        $opexid = "0";
    } else {
        $opexid = $QueryParameters['opexid'];
    }

    // Get a list of the supplier and property manager companies
    $sql1 = "SELECT * FROM companies  where companyTypeID IN (2,6) and  recordOwnerID IN ($accessto) ORDER BY companyName";
    $result1 = $con->query($sql1);

    //Get the list of OPEX budgets
    $sql2 = "SELECT * FROM opex_view  where recordOwnerID IN ($accessto) ORDER BY buildingname, opexdate DESC";
    $result2 = $con->query($sql2);

    $sql3 = "SELECT idopex, buildingID, recordOwnerID FROM opex WHERE idopex = $opexid";
    $resultrecordownler = $con->query($sql3);
    while ($rowrecordowner = $resultrecordownler->fetch_assoc()) {
        $recordownerid = $rowrecordowner["recordOwnerID"];
        $buildingid = $rowrecordowner["buildingID"];
    }

    //Get the list of OPEX categories
    $sql4 = "SELECT * FROM transactioncategories WHERE transactionTypeID = 2 ORDER BY transactionCategoryName";
    $result4 = $con->query($sql4);


    if ($_SERVER["REQUEST_METHOD"] == "POST") {

        if (empty($_POST["opexid"])) {
            $opexidErr = "OPEX Budget is required";
        } else {
            $opexid = test_input($_POST["opexid"]);
            //check if the field only numbers
            if (!preg_match("/^[0-9' ]*$/", $opexid)) {
                $opexidErr = "Only numbers allowed";
            }
        }

        if (empty($_POST["invoicenumber"])) {
            $invoicenumberErr = "Invoice number is required";
        } else {
            $invoicenumber = test_input($_POST["invoicenumber"]);
            //check if the field only contains letters dash or white space
            if (!preg_match("/^[a-zA-Z-0-9' ]*$/", $invoicenumber)) {
                $invoicenumberErr = "Only letters, dash and spaces allowed";
            }
        }

        if (empty($_POST["transactiondate"])) {
            $transactiondateErr = "Invoice date is required";
        } else {
            $transactiondate = test_input($_POST["transactiondate"]);
            //check if the field only contains letters dash or white space
            if (!preg_match("/^[-0-9' ]*$/", $transactiondate)) {
                $transactiondateErr = "Only numbers and dash allowed";
            }
        }

        if (empty($_POST["transactioncompanyid"])) {
            $transactioncompanyidErr = "Supplier is required";
        } else {
            $transactioncompanyid = test_input($_POST["transactioncompanyid"]);
            //check if the field only contains numbers
            if (!preg_match("/^[0-9' ]*$/", $transactioncompanyid)) {
                $transactioncompanyidErr = "Only numbers allowed";
            }
        }

        if (empty($_POST["transactionamount"])) {
            $transactiontotalErr = "Cost (excl GST) is required";
        } else {
            $transactionamount = test_input($_POST["transactionamount"]);
            //check if the field only contains letters dash or white space
            if (!preg_match("/^[-0-9.' ]*$/", $transactionamount)) {
                $transactionamountErr = "Only numbers and dot allowed";
            }
        }

        if (empty($_POST["transactiontotal"])) {
            $transactiontotalErr = "Amount is required";
        } else {
            $transactiontotal = test_input($_POST["transactiontotal"]);
            //check if the field only contains letters dash or white space
            if (!preg_match("/^[-0-9.' ]*$/", $transactiontotal)) {
                $transactiontotalErr = "Only numbers and dot allowed";
            }
        }

        $invoiceduedate = test_input($_POST["invoiceduedate"]);
        //check if the field only contains letters dash or white space
        if (!preg_match("/^[-0-9' ]*$/", $invoiceduedate)) {
            $invoiceduedateErr = "Only numbers and dash allowed";
        }


        $transactioncategoryid = test_input($_POST["transactioncategoryid"]);
        //check if the field only contains numbers
        if (!preg_match("/^[0-9' ]*$/", $transactioncategoryid)) {
            $transactioncategoryidErr = "Only numbers allowed";
        }

        if (empty($_POST["transactiongst"])) {
            $transactiongstErr = "GST is required";
        } else {
            $transactiongst = test_input($_POST["transactiongst"]);
            //check if the field only contains letters dash or white space
            if (!preg_match("/^[0-9.' ]*$/", $transactiongst)) {
                $transactiongstErr = "Only numbers and dot allowed";
            }
        }

        if ($_POST["premisesid"] == 0) {
            $premisesidErr = "Premises is required";
        } else {
            $premisesid = test_input($_POST["premisesid"]);
            //check if the field only contains numbers or NULL - 0 = common to all premises.
            if ($premisesid !== null && !preg_match("/^[0-9-1' ]*$/", $premisesid)) {
                $premisesidErr = "Only numbers allowed";
            }
        }
    }

    function test_input($data)
    {
        $data = trim($data);
        $data = stripslashes($data);
        $data = htmlspecialchars($data);
        return $data;
    }



    if ($_SERVER["REQUEST_METHOD"] == "POST" and $opexidErr == NULL and $invoicenumberErr == NULL and $transactiondateErr == NULL and $transactioncompanyidErr == NULL and $transactionamountErr == NULL and $transactiontotalErr == NULL and $transactiongstErr == NULL and $invoiceduedateErr == NULL and $transactioncategoryidErr == NULL and $premisesidErr == NULL and $invoicestatusidErr == NULL) {

        //prepare and bind
        $stmt = $con->prepare("INSERT INTO transactions (opexID, invoiceNumber, transactionDate, transactionCategoryID, transactionCompanyID, transactionAmount, transactionTotal, transactionGST, invoiceDueDate, premisesID, invoiceStatusID, recordOwnerID) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("issiidddsiii", $opexid, $invoicenumber, $transactiondate, $transactioncategoryid, $transactioncompanyid, $transactionamount, $transactiontotal, $transactiongst, $invoiceduedate, $premisesid, $invoicestatusid, $recordownerid);

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
        <div class=\"col-sm-2\"><a href=\"viewopex.php?opexid=" . $opexid . "\" class=\"btn btn-primary\">Back to OPEX</a></div>
        <div class=\"col-sm-2\"><a href=\"listtransactions.php?type=2&opex=" . $opexid . "\" class=\"btn btn-primary\">Back to OPEX Invoices</a></div>
        <div class=\"col-sm-2\"><a href=\"addopexinvoice.php?opexid=" . $opexid . "\" class=\"btn btn-primary\">Add another Invoice</a></div>
        </div>";
    } else {

        ?>
        <form class="form form-medium" method="post"
            action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"] . '?opexid=' . $opexid); ?>">

            <div class="form-group">
                <label class="form-label col-sm-4" for="opexid" style="padding-top:5px">OPEX Budget: <span class="text-danger">*</span></label>
                <div class="col-sm-6">
                    <select class="form-control" id="opexid" name="opexid" onchange="updateItems()">
                        <?php
                        echo "<option value=\"\"> - Select an OPEX Budget - </option>";
                        while ($row2 = $result2->fetch_assoc()) {
                            if ($row2["opexid"] == $opexid) {
                                echo "<option value=\"" . $row2["opexid"] . "\" selected>" . $row2["buildingname"] . " (" . $row2["opexdate"] . ")</option>";
                            } else {
                                echo "<option value=\"" . $row2["opexid"] . "\">" . $row2["buildingname"] . " (" . $row2["opexdate"] . ")</option>";
                            }
                        }
                        ?>
                    </select>
                </div>
                <div class="col-sm-2"><span class="error"><span class="text-danger"><?php echo $opexidErr; ?></span></div>
            </div>

            <div class="form-group">
                <label class="form-label col-sm-4" for="premisesid" style="padding-top:5px">Premises: <span class="text-danger">*</span></label>
                <div class="col-sm-6">
                    <select class="form-control" id="premisesid" name="premisesid">
                        <?php
                        echo "<option value=\"\"> - Select an Opex Budget - </option>";
                        ?>
                    </select>
                </div>
                <div class="col-sm-2"><span class="error"><span class="text-danger"><?php echo $premisesidErr; ?></span>
                </div>
            </div>

            <div class="form-group">
                <label class="form-label col-sm-4" for="transactioncompanyid" style="padding-top:5px">Supplier Name: <span
                        class="text-danger">*</span></label>
                <div class="col-sm-6">
                    <select class="form-control" id="transactioncompanyid" name="transactioncompanyid">
                        <?php
                        echo "<option value=\"\"> - Select a Company - </option>";
                        while ($row = $result1->fetch_assoc()) {
                            echo "<option value=\"" . $row["idcompany"] . "\">" . $row["companyName"] . "</option>";
                        }
                        ?>
                    </select>
                </div>
                <div class="col-sm-2"><span class="error"><span class="text-danger"><?php echo $transactioncompanyidErr; ?></span></div>
                <div class="col-sm-2"><a href="addcompany.php" target="_blank">Add Company</a></div>
            </div>
            <div class="form-group">
                <label class="form-label col-sm-4" for="invoicenumber" style="padding-top:5px">Invoice Number: <span class="text-danger">*</span></label>
                <div class="col-sm-6"><input class="form-control" type="text" name="invoicenumber" value="<?php echo $invoicenumber; ?>"></div>
                <div class="col-sm-2"><span class="error"><span class="text-danger"><?php echo $invoicenumberErr; ?></span></div>
            </div>
            <div class="form-group">
                <label class="form-label col-sm-4" for="transactiondate" style="padding-top:5px">Invoice Date: <span class="text-danger">*</span></label>
                <div class="col-sm-6"><input class="form-control" type="date" name="transactiondate" value="<?php echo $transactiondate; ?>"></div>
                <div class="col-sm-2"><span class="error"><span class="text-danger"><?php echo $transactiondateErr; ?></span></div>
            </div>
            <div class="form-group">
                <label class="form-label col-sm-4" for="transactionamount" style="padding-top:5px">Amount (excl GST) <span class="text-danger">*</span>:</label>
                <div class="col-sm-6"><input class="form-control" type="text" name="transactionamount" id="transactionamount" value="<?php echo $transactionamount; ?>" onkeyup="myFunction()"></div>
                <div class="col-sm-2"><span class="error"><span class="text-danger"><?php echo $transactionamountErr; ?></span></div>
            </div>
            <div  class="form-group">
                <label class="form-label col-sm-4" for="transactiongst">GST: <span class="text-danger">*</span></label>
                <div class="col-sm-6"><input class="form-control" id="transactiongst" type="text" name="transactiongst" value="<?php echo $transactiongst;?>" readonly></div>
                <div class="col-sm-2"><span class="error"><span class="text-danger"><?php echo $transactiongstErr;?></span></div>
            </div>
            <div class="form-group">
                <label class="form-label col-sm-4" for="transactiontotal" style="padding-top:5px">Total (incl GST) <span class="text-danger">*</span>:</label>
                <div class="col-sm-6"><input class="form-control" type="text" name="transactiontotal" id="transactiontotal" value="<?php echo $transactiontotal; ?>" readonly></div>
                <div class="col-sm-2"><span class="error"><span class="text-danger"><?php echo $transactiontotalErr; ?></span></div>
            </div>
            <div class="form-group">
                <label class="form-label col-sm-4" for="invoiceduedate" style="padding-top:5px">Due Date:</label>
                <div class="col-sm-6"><input class="form-control" type="date" name="invoiceduedate" value="<?php echo $invoiceduedate; ?>"></div>
                <div class="col-sm-2"><span class="error"><span class="text-danger"><?php echo $invoiceduedateErr; ?></span></div>
            </div>
            <div class="form-group">
                <label class="form-label col-sm-4" for="transactioncategoryid" style="padding-top:5px">Category:</label>
                <div class="col-sm-6">
                    <select class="form-control" id="transactioncategoryid" name="transactioncategoryid">
                        <?php
                        echo "<option value=\"0\"> - Select a Category - </option>";
                        while ($row4 = $result4->fetch_assoc()) {
                            echo "<option value=\"" . $row4["idtransactioncategory"] . "\">" . $row4["transactionCategoryName"] . "</option>";
                        }
                        ?>
                    </select>
                </div>
                <div class="col-sm-2"><span class="error"><span class="text-danger"><?php echo $transactioncategoryidErr; ?></span></div>
            </div>


            <div class="row">
                &nbsp;
            </div>

            <div class="row">
                <div class="col-sm-1" style="padding-top:40px;"><input type="submit" value="Submit" class="btn btn-primary"
                        style="width:100px"></div>
            </div>
        </form>

        <div class="row">

            <script>
                function myFunction() {
                    const options = {
                        style: 'decimal',
                        minimumFractionDigits: 2,
                        maximumFractionDigits: 2,
                    }
                    var costGST = Math.round(document.getElementById("transactionamount").value * 15) / 100;
                    var costInclGST = Math.round(document.getElementById("transactionamount").value * 115) / 100;
                    //document.getElementById("invoicegst").value = costGST;
                    document.getElementById("transactiongst").value = costGST;
                    document.getElementById("transactiontotal").value = costInclGST;
                }
            </script>

            <script>
                function updateItems() {
                    const opexId = Number(document.getElementById("opexid").value);
                    const premisesId = document.getElementById("premisesid");
                    const premisesURL = "get_premises.php?opexid=" + opexId;

                    premisesId.innerHTML = '<option value="">-- Select --</option>';

                    if (opexId) {
                        // Add item 0 Common to all Premises
                        //const option = document.createElement("option");
                        //option.value = "0";
                        //option.textContent = "Common to all Premises";
                        //premisesId.appendChild(option);

                        // Get the list of premises based on the opex choice
                        fetch(premisesURL)
                        .then(response => response.json())
                        .then(data => {
                            data.forEach(item => {
                                const option = document.createElement("option");
                                option.value = item.idpremises;
                                option.textContent = item.unitname;
                                premisesId.appendChild(option);
                            });
                        });
                    } else {
                        buildingId.value = 'No opexid';
                    }
                }
                
            </script>

            <?php
    }

    $con->close();
    ?>




    </div>

    <?= template_footer() ?>
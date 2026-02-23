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

?>

<?= template_header('Edit Invoice') ?>

<div class="page-title">
    <div class="icon">
        <svg onload="updatePremises()" width="20" height="20" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 576 512"><!--!Font Awesome Free 6.5.1 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license/free Copyright 2024 Fonticons, Inc.-->
            <path d="M575.8 255.5c0 18-15 32.1-32 32.1h-32l.7 160.2c0 2.7-.2 5.4-.5 8.1V472c0 22.1-17.9 40-40 40H456c-1.1 0-2.2 0-3.3-.1c-1.4 .1-2.8 .1-4.2 .1H416 392c-22.1 0-40-17.9-40-40V448 384c0-17.7-14.3-32-32-32H256c-17.7 0-32 14.3-32 32v64 24c0 22.1-17.9 40-40 40H160 128.1c-1.5 0-3-.1-4.5-.2c-1.2 .1-2.4 .2-3.6 .2H104c-22.1 0-40-17.9-40-40V360c0-.9 0-1.9 .1-2.8V287.6H32c-18 0-32-14-32-32.1c0-9 3-17 10-24L266.4 8c7-7 15-8 22-8s15 2 21 7L564.8 231.5c8 7 12 15 11 24z" />
        </svg>
    </div>
    <div class="wrap">
        <h2>Edit Invoice</h2>
    </div>
</div>

<div class="block">

    <?php
    // define variables and set to empty values

    $Q = explode("/", $_SERVER['QUERY_STRING']);
    parse_str($Q[0], $QueryParameters);
    $QPid = $QueryParameters['id'];

    $sql = "SELECT * from transactions_view WHERE idtransaction = $QPid and recordOwnerID IN ($accessto)";
    $result = $con->query($sql);

    //Get the list of invoice statuses
    $sql2 = "SELECT * from invoicestatus where idinvoicestatus < 5 ORDER BY sortOrder";
    $result2 = $con->query($sql2);





    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $invoicenumber = $row["invoicenumber"];
            $transactioncategoryid = $row["transactioncategoryid"];
            $transactioncompanyid = $row["transactioncompanyid"];
            $transactiondate = $row["transactiondate"];
            $transactionamount = $row["transactionamount"];
            $transactiontotal = $row["transactiontotal"];
            $transactiongst = $row["transactiongst"];
            $invoiceduedate = $row["invoiceduedate"];
            $invoicestatusid = $row["invoicestatusid"];
            $invoicepaiddate = $row["invoicepaiddate"];
            $type = $row["transactiontypeid"];
            if (isset($row["premisesid"])) {
                $premisesid = $row["premisesid"];
            } else {
                $premisesid = 0;
            }
        }
    } else {
        $invoicenumber = $transactioncategoryid = $transactiondate = $transactioncompanyid = $transactionamount = $transactiongst = $transactiontotal = $invoiceduedate = $invoicestatusid = $invoicepaiddate = $premisesid = "";
    }
    $invoicenumberErr = $transactioncategoryidErr = $transactiondateErr = $transactioncompanyidErr = $transactionamountErr = $transactiongstErr = $transactiontotalErr = $invoiceduedateErr = $invoicestatusidErr = $invoicepaiddateErr = $premisesidErr = "";

    if ($type == 1) {
        //Get the list of tenant companies
        $sql3 = "SELECT idcompany, companyName from companies where companyTypeID = 1 and recordOwnerID IN ($accessto) ORDER BY companyName";
        $result3 = $con->query($sql3);
        //Get the list of tenant invoice categories
        $sql4 = "SELECT * from transactioncategories where transactionTypeID = 1 ORDER BY transactionCategoryName";
        $result4 = $con->query($sql4);
    } else {
        //Get the list of opex supplier companies
        $sql3 = "SELECT idcompany, companyName from companies where companyTypeID in (2,3,4,6) and recordOwnerID IN ($accessto) ORDER BY companyName";
        $result3 = $con->query($sql3);
        //Get the list of opex bill categories
        $sql4 = "SELECT * from transactioncategories where transactionTypeID = 2 ORDER BY transactionCategoryName";
        $result4 = $con->query($sql4);
    }

    if ($_SERVER["REQUEST_METHOD"] == "POST") {

        if (!empty($_POST["opex"])) {
            $opex = $_POST["invoicenumber"];
        } else {
            $opex = 0;
        }

        if (empty($_POST["invoicenumber"])) {
            $invoicenumberErr = "Invoice number is required";
        } else {
            $invoicenumber = test_input($_POST["invoicenumber"]);
            //check if the field only contains letters dash or white space
            if (!preg_match("/^[a-zA-Z-0-9āēīōūĀĒĪŌŪ' .\/]*$/", $invoicenumber)) {
                $invoicenumberErr = "Only letters, dash and spaces allowed";
            }
        }

        $transactioncategoryid = test_input($_POST["transactioncategoryid"]);
        //check if the field only contains letters dash or white space
        if (!preg_match("/^[0-9']*$/", $transactioncategoryid)) {
            $transactioncategoryidErr = "Only numbers allowed";
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
            $transactioncompanyidErr = "Tenant is required";
        } else {
            $transactioncompanyid = test_input($_POST["transactioncompanyid"]);
            //check if the field only contains numbers
            if (!preg_match("/^[0-9' ]*$/", $transactioncompanyid)) {
                $transactioncompanyidErr = "Only numbers allowed";
            }
        }

        if (empty($_POST["transactionamount"])) {
            $transactionamountErr = "Amount is required";
        } else {
            $transactionamount = test_input($_POST["transactionamount"]);
            //check if the field only contains letters dash or white space
            if (!preg_match("/^[0-9.' ]*$/", $transactionamount)) {
                $transactionamountErr = "Only numbers and dot allowed";
            }
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

        if (empty($_POST["transactiontotal"])) {
            $transactiontotalErr = "Invoice Total is required";
        } else {
            $transactiontotal = test_input($_POST["transactiontotal"]);
            //check if the field only contains letters dash or white space
            if (!preg_match("/^[0-9.' ]*$/", $transactiontotal)) {
                $transactiontotalErr = "Only numbers and dot allowed";
            }
        }

        if (empty($_POST["invoiceduedate"])) {
            $invoiceduedateErr = "Due Date is required";
        } else {
            $invoiceduedate = test_input($_POST["invoiceduedate"]);
            //check if the field only contains letters dash or white space
            if (!preg_match("/^[-0-9' ]*$/", $invoiceduedate)) {
                $invoiceduedateErr = "Only numbers and dash allowed";
            }
        }

        $invoicestatusid = test_input($_POST["invoicestatusid"]);
        if (!preg_match("/^[0-9' ]*$/", $invoicestatusid)) {
            $invoicestatusidErr = "Only numbers allowed";
        }

        $invoicepaiddate = test_input($_POST["invoicepaiddate"]);
        //check if the field only contains letters dash or white space
        if (!preg_match("/^[-0-9' ]*$/", $invoicepaiddate)) {
            $invoicepaiddateErr = "Only numbers and dash allowed";
        }

        if (empty($_POST["premisesid"])) {
            $premisesidErr = "Premises is required";
        } else {
            $premisesid = test_input($_POST["premisesid"]);
            //check if the field only contains numbers or NULL - 0 = common to all premises.
            if ($premisesid !== null && !preg_match("/^[0-9' ]*$/", $premisesid)) {
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


    if ($_SERVER["REQUEST_METHOD"] == "POST" and $invoicenumberErr == NULL and $transactioncategoryidErr == NULL and $transactiondateErr == NULL and $transactioncompanyidErr == NULL and $transactionamountErr == NULL and $transactiongstErr == NULL and $transactiontotalErr == NULL and $invoiceduedateErr == NULL and $invoicestatusidErr == NULL and $invoicepaiddateErr == NULL and $premisesidErr == NULL) {

        //prepare and bind
        if ($invoicepaiddate == '') {
            $sql2 = "UPDATE transactions SET invoiceNumber = '$invoicenumber', transactionCategoryID = '$transactioncategoryid', transactionDate = '$transactiondate', transactionCompanyID = '$transactioncompanyid', transactionAmount = '$transactionamount', transactionGST = '$transactiongst', transactionTotal = '$transactiontotal', invoiceDueDate = '$invoiceduedate', invoiceStatusID = '$invoicestatusid', premisesID = '$premisesid' WHERE idtransaction = $QPid";
        } else {
            $sql2 = "UPDATE transactions SET invoiceNumber = '$invoicenumber', transactionCategoryID = '$transactioncategoryid', transactionDate = '$transactiondate', transactionCompanyID = '$transactioncompanyid', transactionAmount = '$transactionamount', transactionGST = '$transactiongst', transactionTotal = '$transactiontotal', invoiceDueDate = '$invoiceduedate', invoiceStatusID = '$invoicestatusid', invoicePaidDate = '$invoicepaiddate', premisesID = '$premisesid' WHERE idtransaction = $QPid";
        }
        if ($con->query($sql2) === TRUE) {
            echo '<table class="table table-hover">
        <tbody>
            <tr class="success">
                <td>Success!</td>
            </tr>
        </tbody>
        </table>';

            echo "<div class=\"row\">
           <div class=\"col-sm-2\" style=\"padding-top:20px; padding-bottom:20px\"><a href=\"listtransactions.php?type=" . $type . "&opex=" . $opex . "\" class=\"btn btn-primary\">Back to Invoices</a></div>
        </div>
        <div class=\"row\">";
        } else {
            echo 'Error updating record: ' . $con->error;
        }
    } else {
    ?>
        <form class="form form-medium" method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"] . '?id=' . $QPid); ?>">
            <div class="form-group">
                <label class="form-label col-sm-4" for="transactioncompanyid">Company: <span class="text-danger">*</span></label>
                <div class="col-sm-6">
                    <select class="form-control" id="transactioncompanyid" name="transactioncompanyid" onchange="updatePremises()">
                        <?php
                        echo "<option value=\"\"> - Select an Company - </option>";
                        while ($row3 = $result3->fetch_assoc()) {
                            if ($row3["idcompany"] == $transactioncompanyid) {
                                echo "<option value=\"" . $row3["idcompany"] . "\" selected>" . $row3["companyName"] . "</option>";
                            } else {
                                echo "<option value=\"" . $row3["idcompany"] . "\">" . $row3["companyName"] . "</option>";
                            }
                        }
                        ?>
                    </select>
                </div>
                <div class="col-sm-2"><span class="error"><span class="text-danger"><?php echo $transactioncompanyidErr; ?></span></div>
            </div>

            <div class="form-group">
                <label class="form-label col-sm-4" for="premisesid" style="padding-top:5px">Premises: <span class="text-danger">*</span></label>
                <div class="col-sm-6">
                    <select class="form-control" id="premisesid" name="premisesid">

                    </select>
                </div>
                <div class="col-sm-2"><span class="error"><span class="text-danger"><?php echo $premisesidErr; ?></span>
                </div>
            </div>

            <div class="form-group">
                <label class="form-label col-sm-4" for="transactiondate">Date: <span class="text-danger">*</span></label>
                <div class="col-sm-6"><input class="form-control" id="transactiondate" type="date" name="transactiondate" value="<?php echo $transactiondate; ?>"></div>
                <div class="col-sm-2"><span class="error"><span class="text-danger"><?php echo $transactiondateErr; ?></span></div>
            </div>
            <div class="form-group">
                <label class="form-label col-sm-4" for="invoicenumber">Invoice Number: <span class="text-danger">*</span></label>
                <div class="col-sm-6"><input class="form-control" id="invoicenumber" type="text" name="invoicenumber" value="<?php echo $invoicenumber; ?>"></div>
                <div class="col-sm-2"><span class="error"><span class="text-danger"><?php echo $invoicenumberErr; ?></span></div>
            </div>
            <div class="form-group">
                <label class="form-label col-sm-4" for="transactioncategoryid">Category:</label>
                <div class="col-sm-6">
                    <select class="form-control" id="transactioncategoryid" name="transactioncategoryid">
                        <?php
                        echo "<option value=\"\"> - Select an Category - </option>";
                        while ($row4 = $result4->fetch_assoc()) {
                            if ($row4["idtransactioncategory"] == $transactioncategoryid) {
                                echo "<option value=\"" . $row4["idtransactioncategory"] . "\" selected>" . $row4["transactionCategoryName"] . "</option>";
                            } else {
                                echo "<option value=\"" . $row4["idtransactioncategory"] . "\">" . $row4["transactionCategoryName"] . "</option>";
                            }
                        }
                        ?>
                    </select>
                </div>
                <div class="col-sm-2"><span class="error"><span class="text-danger"><?php echo $transactioncategoryidErr; ?></span></div>
            </div>
            <!--
    <div  class="form-group">
        <label class="form-label col-sm-4" for="invoiceduedate">Unit: <span class="text-danger">*</span></label>
        <div class="col-sm-6"><input class="form-control" id="invoiceduedate" type="date" name="invoiceduedate" value="<?php echo $invoiceduedate; ?>"></div>
        <div class="col-sm-2"><span class="error"><span class="text-danger"><?php echo $invoiceduedateErr; ?></span></div>
    </div>
    <div  class="form-group">
        <label class="form-label col-sm-4" for="invoiceduedate">Building: <span class="text-danger">*</span></label>
        <div class="col-sm-6"><input class="form-control" id="invoiceduedate" type="date" name="invoiceduedate" value="<?php echo $invoiceduedate; ?>"></div>
        <div class="col-sm-2"><span class="error"><span class="text-danger"><?php echo $invoiceduedateErr; ?></span></div>
    </div>
            -->

            <div class="form-group">
                <label class="form-label col-sm-4" for="transactionamount">Amount (excl GST):</label>
                <div class="col-sm-6"><input class="form-control" id="transactionamount" type="text" name="transactionamount" value="<?php echo $transactionamount; ?>" onkeyup="myFunction()"></div>
                <div class="col-sm-2"><span class="error"><span class="text-danger"><?php echo $transactionamountErr; ?></span></div>
            </div>
            <div class="form-group">
                <label class="form-label col-sm-4" for="transactiongst">GST: <span class="text-danger">*</span></label>
                <div class="col-sm-6"><input class="form-control border-0" id="transactiongst" type="text" name="transactiongst" value="<?php echo $transactiongst; ?>" readonly></div>
                <div class="col-sm-2"><span class="error"><span class="text-danger"><?php echo $transactiongstErr; ?></span></div>
            </div>
            <div class="form-group">
                <label class="form-label col-sm-4" for="transactiontotal">Total: <span class="text-danger">*</span></label>
                <div class="col-sm-6"><input class="form-control border-0" id="transactiontotal" type="text" name="transactiontotal" value="<?php echo $transactiontotal; ?>" readonly></div>
                <div class="col-sm-2"><span class="error"><span class="text-danger"><?php echo $transactiontotalErr; ?></span></div>
            </div>
            <div class="form-group">
                <label class="form-label col-sm-4" for="invoiceduedate">Due Date: <span class="text-danger">*</span></label>
                <div class="col-sm-6"><input class="form-control" id="invoiceduedate" type="date" name="invoiceduedate" value="<?php echo $invoiceduedate; ?>"></div>
                <div class="col-sm-2"><span class="error"><span class="text-danger"><?php echo $invoiceduedateErr; ?></span></div>
            </div>
            <div class="form-group">
                <label class="form-label col-sm-4" for="invoicestatusid">Status: <span class="text-danger">*</span></label>
                <div class="col-sm-6">
                    <select class="form-control" id="invoicestatusid" name="invoicestatusid">
                        <?php
                        echo "<option value=\"\"> - Select a Status - </option>";
                        while ($row2 = $result2->fetch_assoc()) {
                            if ($row2["idinvoicestatus"] == $invoicestatusid) {
                                echo "<option value=\"" . $row2["idinvoicestatus"] . "\" selected>" . $row2["invoiceStatus"] . "</option>";
                            } else {
                                echo "<option value=\"" . $row2["idinvoicestatus"] . "\">" . $row2["invoiceStatus"] . "</option>";
                            }
                        }
                        ?>
                    </select>
                </div>
                <div class="col-sm-2"><span class="error"><span class="text-danger"><?php echo $invoicestatusidErr; ?></span></div>
            </div>
            <div class="form-group">
                <label class="form-label col-sm-4" for="invoicepaiddate">Date Paid:</label>
                <div class="col-sm-6"><input class="form-control" id="invoicepaiddate" type="date" name="invoicepaiddate" value="<?php echo $invoicepaiddate; ?>"></div>
                <div class="col-sm-2"><span class="error"><span class="text-danger"><?php echo $invoicepaiddateErr; ?></span></div>
            </div>

            <div class="form-group">
                <div class="col-sm-1" style="padding-top:40px;"><input type="submit" value="Submit" class="btn btn-primary" style="width:100px"></div>
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
                    document.getElementById("transactiongst").value = costGST;
                    document.getElementById("transactiontotal").value = costInclGST;
                }
            </script>

            <script>
                function updatePremises() {
                    const companyId = Number(document.getElementById("transactioncompanyid").value);
                    const premisesId = document.getElementById("premisesid");
                    var premisesIdValue = "<?php echo $premisesid; ?>";
                    const premisesURL = "get_leasepremises.php?companyid=" + companyId;

                    //premisesId.innerHTML = '<option value="">-- Select --</option>';

                    if (companyId) {

                        // Get the list of premises based on the opex choice
                        fetch(premisesURL)
                            .then(response => response.json())
                            .then(data => {
                                data.forEach(item => {
                                    const option = document.createElement("option");
                                    option.value = item.premisesid;
                                    option.textContent = item.unitname;
                                    if (item.idpremises == <?php echo $premisesid; ?>) {
                                        option.selected = true;
                                    }
                                    premisesId.appendChild(option);
                                });
                            });

                        document.getElementById("premisesid").value = premisesIdValue;
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
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

$errors = [];
$row_number = 0;
$count_success_rows = 0;

$sql9 = "SELECT * FROM accounts WHERE id = $accountid";
$result9 = $con->query($sql9);

?>

<?= template_header('Import OPEX Invoices') ?>

<div class="page-title">
    <div class="icon">
        <svg width="20" height="20" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 576 512"><!--!Font Awesome Free 6.5.1 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license/free Copyright 2024 Fonticons, Inc.-->
            <path d="M575.8 255.5c0 18-15 32.1-32 32.1h-32l.7 160.2c0 2.7-.2 5.4-.5 8.1V472c0 22.1-17.9 40-40 40H456c-1.1 0-2.2 0-3.3-.1c-1.4 .1-2.8 .1-4.2 .1H416 392c-22.1 0-40-17.9-40-40V448 384c0-17.7-14.3-32-32-32H256c-17.7 0-32 14.3-32 32v64 24c0 22.1-17.9 40-40 40H160 128.1c-1.5 0-3-.1-4.5-.2c-1.2 .1-2.4 .2-3.6 .2H104c-22.1 0-40-17.9-40-40V360c0-.9 0-1.9 .1-2.8V287.6H32c-18 0-32-14-32-32.1c0-9 3-17 10-24L266.4 8c7-7 15-8 22-8s15 2 21 7L564.8 231.5c8 7 12 15 11 24z" />
        </svg>
    </div>
    <div class="wrap">
        <h2>Import OPEX Invoices</h2>
    </div>
</div>


<div class="block">

    <?php
    // define variables and set to empty values
    $invoicenumber = $invoicedate = $invoicecompanyid = $invoicetotal = $invoiceincludesgst = $invoiceduedate = $opexcategoryid = $invoicedatepaid = $premisesid = "";
    $invoicenumberErr = $invoicedateErr = $invoicecompanyidErr = $invoicetotalErr = $invoiceincludesgstErr = $invoiceduedateErr = $opexcategoryidErr = $opexidErr = $invoicedatepaidErr = $premisesidErr = "";

    $Q = explode("/", $_SERVER['QUERY_STRING']);
    parse_str($Q[0], $QueryParameters);
    if (empty($QueryParameters['opexid'])) {
        $QPopexid = "";
        $opexid = "";
    } else {
        $QPopexid = $QueryParameters['opexid'];
        $opexid = $QueryParameters['opexid'];
    }

    // Get a list of the supplier companies
    $sql1 = "SELECT * FROM companies  where companyTypeID = 6 and  recordOwnerID IN ($accessto) ORDER BY companyName";
    $result1 = $con->query($sql1);

    //Get the list of OPEX budgets
    $sql2 = "SELECT * FROM opex_view  where recordOwnerID IN ($accessto) ORDER BY opexdate";
    $result2 = $con->query($sql2);

    $sql3 = "SELECT idopex, buildingID, recordOwnerID FROM opex WHERE idopex = $QPopexid";
    $resultrecordownler = $con->query($sql3);
    while($rowrecordowner = $resultrecordownler->fetch_assoc()) {
      $recordownerid = $rowrecordowner["recordOwnerID"];
      $buildingid = $rowrecordowner["buildingID"];
    }

    //Get the list of OPEX categories
    $sql4 = "SELECT * FROM transactioncategories ORDER BY transactionCategoryName";
    $result4 = $con->query($sql4);

    //Get a list of the Premises
    $sql5 = "SELECT idpremises, unitname, buildingname FROM premises_view WHERE idbuildings = $buildingid ORDER BY unitname";
    $result5 = $con->query($sql5);


if ($_SERVER["REQUEST_METHOD"] == "POST" and isset($_POST["Import"])) {

    if(isset($_POST["Import"])){
        $filename=$_FILES["file"]["tmp_name"];
        if($_FILES["file"]["size"] > 0) {
            if(($file = fopen($filename, "r")) !== FALSE) {

                //Skip the header row
                fgetcsv($file);
                
            switch ($_POST["filetype"]) {
            case "bayleys":
                //Prepare SQL insert statement
                $stmt = $con->prepare("INSERT INTO opexinvoices (idopexinvoice, invoiceNumber, invoiceDate, invoiceCompanyID, invoiceTotal, invoiceIncludesGST, invoiceDueDate, invoiceDatePaid, opexID, opexCategoryID, premisesID, recordOwnerID) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
                $stmt->bind_param("issidissiiii", $col1,$col2,$col3,$col4,$col5,$col6,$col7,$col8,$col9,$col10,$col11,$col12);
                
                //Read each row in the CSV file and insert into the database
                while (($getData = fgetcsv($file, 10000, ",")) !== FALSE) {
                    $row_number++;
                    $col1 = "0";
                    $col2 = $getData[0]; //invoiceNumber				
                    $col3 = $getData[1]; //invoiceDate
                    $col4 = $getData[2]; //invoiceCompanyID
                    $col5 = $getData[3]; //invoiceTotal
                    $col6 = $getData[4]; //invoiceIncludesGST
                    $col7 = $getData[5]; //invoiceDueDate
                    $col8 = $getData[6]; //invoiceDatePaid
                    $col9 = $opexid;
                    $col10 = $getData[7]; //opexCategoryID
                    $col11 = $getData[8]; //premisesID
                    $col12 = $recordownerid;

                    if (!is_numeric($col4) || intval($col4) != $col4) {
                        $errors[] = "Row $row_number  failed: InvoiceCompanyID is not an integer.";
                        continue; //Skip if invoiceCompanyID is not an integer
                    }
                    if (!is_numeric($col10) || intval($col10) != $col10) {
                        $errors[] = "Row $row_number failed: opexCategoryID is not an integer.";
                        continue; //Skip if opexCategoryID is not an integer
                    }
                    if (!is_numeric($col11) || intval($col11) != $col11) {
                        $errors[] = "Row $row_number failed: premisesID is not an integer.";
                        continue; //Skip if premisesID is not an integer 
                    }

                    if (!preg_match("/^\d{4}-\d{2}-\d{2}$/", $col3)) {
                        $errors[] = "Row $row_number failed: invoiceDate is not in the format YYYY-MM-DD.";
                        continue;  // Skip if invoiceDate is not in the format YYYY-MM-DD
                    }
                    if (!preg_match("/^\d{4}-\d{2}-\d{2}$/", $col7)) {
                        $errors[] = "Row $row_number failed: invoiceDueDate is not in the format YYYY-MM-DD.";
                        continue;  // Skip if invoiceDueDate is not in the format YYYY-MM-DD
                    }
                    if (!preg_match("/^\d{4}-\d{2}-\d{2}$/", $col8)) {
                        $errors[] = "Row $row_number failed: invoiceDatePaid is not in the format YYYY-MM-DD.";
                        continue;  // Skip if invoiceDatePaid is not in the format YYYY-MM-DD
                    }
                    
                    //Sanitise the input to valid SQL
                    //$col1 = "0";
                    $col2 = $con->real_escape_string($col2); //invoiceNumber				
                    $col3 = $con->real_escape_string($col3); //invoiceDate
                    $col4 = $con->real_escape_string($col4); //invoiceCompanyID
                    $col5 = $con->real_escape_string($col5); //invoiceTotal
                    $col6 = $con->real_escape_string($col6); //invoiceIncludesGST
                    $col7 = $con->real_escape_string($col7); //invoiceDueDate
                    $col8 = $con->real_escape_string($col8); //invoiceDatePaid
                    //$col9 = $opexid;
                    $col10 = $con->real_escape_string($col10); //opexCategoryID
                    $col11 = $con->real_escape_string($col11); //premisesID
                    //$col12 = $recordownerid;
                
                    $stmt->execute();
                    $count_success_rows++;
                }
                break;
                
            case "xero":
                //Prepare SQL insert statement
                $stmt = $con->prepare("INSERT INTO opexinvoices (idopexinvoice, invoiceNumber, invoiceDate, invoiceCompanyID, invoiceTotal, invoiceIncludesGST, invoiceDueDate, invoiceDatePaid, opexID, opexCategoryID, premisesID, recordOwnerID) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
                $stmt->bind_param("issidissiiii", $col1,$col2,$col3,$col4,$col5,$col6,$col7,$col8,$col9,$col10,$col11,$col12);
                
                //Read each row in the CSV file and insert into the database
                while (($getData = fgetcsv($file, 10000, ",")) !== FALSE) {
                    $row_number++;
                    $col1 = "0";
                    $col2 = $getData[0]; //invoiceNumber				
                    $col3 = $getData[1]; //invoiceDate
                    $col4 = $getData[2]; //invoiceCompanyID
                    $col5 = $getData[3]; //invoiceTotal
                    $col6 = $getData[4]; //invoiceIncludesGST
                    $col7 = $getData[5]; //invoiceDueDate
                    $col8 = $getData[6]; //invoiceDatePaid
                    $col9 = $opexid;
                    $col10 = $getData[7]; //opexCategoryID
                    $col11 = $getData[8]; //premisesID
                    $col12 = $recordownerid;

                    if (!is_numeric($col4) || intval($col4) != $col4) {
                        $errors[] = "Row $row_number  failed: InvoiceCompanyID is not an integer.";
                        continue; //Skip if invoiceCompanyID is not an integer
                    }
                    if (!is_numeric($col10) || intval($col10) != $col10) {
                        $errors[] = "Row $row_number failed: opexCategoryID is not an integer.";
                        continue; //Skip if opexCategoryID is not an integer
                    }
                    if (!is_numeric($col11) || intval($col11) != $col11) {
                        $errors[] = "Row $row_number failed: premisesID is not an integer.";
                        continue; //Skip if premisesID is not an integer 
                    }

                    if (!preg_match("/^\d{4}-\d{2}-\d{2}$/", $col3)) {
                        $errors[] = "Row $row_number failed: invoiceDate is not in the format YYYY-MM-DD.";
                        continue;  // Skip if invoiceDate is not in the format YYYY-MM-DD
                    }
                    if (!preg_match("/^\d{4}-\d{2}-\d{2}$/", $col7)) {
                        $errors[] = "Row $row_number failed: invoiceDueDate is not in the format YYYY-MM-DD.";
                        continue;  // Skip if invoiceDueDate is not in the format YYYY-MM-DD
                    }
                    if (!preg_match("/^\d{4}-\d{2}-\d{2}$/", $col8)) {
                        $errors[] = "Row $row_number failed: invoiceDatePaid is not in the format YYYY-MM-DD.";
                        continue;  // Skip if invoiceDatePaid is not in the format YYYY-MM-DD
                    }
                    
                    //Sanitise the input to valid SQL
                    //$col1 = "0";
                    $col2 = $con->real_escape_string($col2); //invoiceNumber				
                    $col3 = $con->real_escape_string($col3); //invoiceDate
                    $col4 = $con->real_escape_string($col4); //invoiceCompanyID
                    $col5 = $con->real_escape_string($col5); //invoiceTotal
                    $col6 = $con->real_escape_string($col6); //invoiceIncludesGST
                    $col7 = $con->real_escape_string($col7); //invoiceDueDate
                    $col8 = $con->real_escape_string($col8); //invoiceDatePaid
                    //$col9 = $opexid;
                    $col10 = $con->real_escape_string($col10); //opexCategoryID
                    $col11 = $con->real_escape_string($col11); //premisesID
                    //$col12 = $recordownerid;
                
                    $stmt->execute();
                    $count_success_rows++;
                }
                break;
            }

                $stmt->close();

                echo '<div class=\"row\">
                <table class="table table-hover">
                    <tbody>
                        <tr class="success">
                            <td>';
                            if(!empty($errors)) {
                                echo    $count_success_rows . ' rows imported successfully!<br>
                                The following errors occured during the import:</br>';
                                foreach ($errors as $error) {
                                    echo $error . "</br>";
                                }
                            } else {
                                echo    $count_success_rows . ' rows imported successfully!';
                            }
                        echo '<td>
                        </tr>
                    </tbody>
                </table>';

            } else {
                echo "Error opening the CSV file";
            }
    
            fclose($file);
        }
    }

        echo "<div class=\"row\">
            <div class=\"col-sm-2\"><a href=\"viewopex.php?opexid=" . $opexid . "\" class=\"btn btn-primary\">Back to OPEX</a></div>
            <div class=\"col-sm-2\"><a href=\"addopexinvoice.php?opexid=" . $QPopexid . "\" class=\"btn btn-primary\">Add another Invoice</a></div>
        </div>";
    } else {

    ?>
    <form class="form-horizontal" method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"] . '?opexid=' . $opexid); ?>" name="import_csv" enctype="multipart/form-data">
        <fieldset>

        <!-- Form Name -->
        <legend>Import OPEX transactions from CSV file</legend>

        <!-- File Button -->
        <div class="form-group">
            <label class="form label col-md-4" for="filebutton">Select File</label>
            <div class="col-md-4"><input type="file" name="file" id="file" class="input-large"></div>
        </div>

        <!-- Choose the file source -->
        
        <div class="form-group" style="padding-top:20px;">
                <input id="bayleys" type="radio" name="filetype" value="bayleys">
                <label for="bayleys">Bayleys Wānaka</label></br>
                <input id="xero" type="radio" name="filetype" value="xero">
                <label for="xero">Xero</label><br>
        </div>

        <!-- Button -->
        <div class="form-group">
           <!-- <label class="form label col-md-4" for="singlebutton">Import data</label> -->
            <div class="col-md-4" style="padding-top:40px;"><button type="submit" id="submit" name="Import" class="btn btn-primary button-loading" data-loading-text="Loading...">Import</button></div>
        </div>

        </fieldset>
    </form>

        <div class="row">
        <?php
    }

    $con->close();
        ?>

        </div>

        <?= template_footer() ?>
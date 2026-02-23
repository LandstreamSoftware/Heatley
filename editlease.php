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

<?=template_header('Edit Lease')?>

<div class="page-title">
	<div class="icon">
		<svg width="20" height="20" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 576 512"><!--!Font Awesome Free 6.5.1 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license/free Copyright 2024 Fonticons, Inc.--><path d="M575.8 255.5c0 18-15 32.1-32 32.1h-32l.7 160.2c0 2.7-.2 5.4-.5 8.1V472c0 22.1-17.9 40-40 40H456c-1.1 0-2.2 0-3.3-.1c-1.4 .1-2.8 .1-4.2 .1H416 392c-22.1 0-40-17.9-40-40V448 384c0-17.7-14.3-32-32-32H256c-17.7 0-32 14.3-32 32v64 24c0 22.1-17.9 40-40 40H160 128.1c-1.5 0-3-.1-4.5-.2c-1.2 .1-2.4 .2-3.6 .2H104c-22.1 0-40-17.9-40-40V360c0-.9 0-1.9 .1-2.8V287.6H32c-18 0-32-14-32-32.1c0-9 3-17 10-24L266.4 8c7-7 15-8 22-8s15 2 21 7L564.8 231.5c8 7 12 15 11 24z"/></svg>
	</div>	
	<div class="wrap">
		<h2>Edit Lease</h2>
	</div>
</div>

<div class="block">

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
    function deleteFile(idfile) {
        Swal.fire({
        title: "Delete lease file",
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
                fetch('gcloud-delete_file.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded'
                    },
                    body: 'idfile=' + encodeURIComponent(idfile)
                })
                .then(response => response.text())
                .then(data => {
                    console.log('Success:', data);
                    //alert('File deleted successfully!');
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Failed to delete the file.');
                });

                // Prevent parent form submission
                event.stopPropagation();
                location.reload();
                return false;
            }
        });
    }
</script>

<script>
    function deleteDocumentURL(idfile) {
        Swal.fire({
        title: "Delete Document URL",
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
                fetch('gcloud-delete_document_url.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded'
                    },
                    body: 'idfile=' + encodeURIComponent(idfile)
                })
                .then(response => response.text())
                .then(data => {
                    console.log('Success:', data);
                    //alert('File deleted successfully!');
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Failed to delete the document url.');
                });

                // Prevent parent form submission
                event.stopPropagation();
                location.reload();
                return false;
            }
        });
    }
</script>

<script>
    function validateFile(event) {
        const fileInput = document.getElementById("fileToUpload");
        const file = fileInput.files[0];
        const reader = new FileReader();
        const maxSize = 7 * 1024 * 1024; //5MB
        if (fileInput.files.length > 0) {
            if (fileInput.files[0].size > maxSize) {
                //alert("File size must be less than 7MB!");
                Swal.fire({
                title: "File too big!",
                text: "File size must be less than 7MB",
                icon: "warning",
                iconColor: "#d33",
                showCancelButton: false,
                confirmButtonColor: "#0d6efd",
                confirmButtonText: "OK",
                })

                event.preventDefault();
                return false;
            } else {
                const reader = new FileReader();
                reader.onload = function(e) {
                    const arr = new Uint8Array(e.target.result);
                    const signature = string.fromCharCode(...arr.slice(o,5));
                    //if (signature !== "%PDF-") {
                    //alert("File size must be less than 7MB!");
                    Swal.fire({
                    title: "File not PDF!",
                    text: signature,
                    icon: "warning",
                    iconColor: "#d33",
                    showCancelButton: false,
                    confirmButtonColor: "#0d6efd",
                    confirmButtonText: "OK",
                    })

                    event.preventDefault();
                    return false;
                }
            }
        } else {
            Swal.fire({
            title: "File too small!",
            text: "File size must be less than 7MB",
            icon: "warning",
            iconColor: "#d33",
            showCancelButton: false,
            confirmButtonColor: "#0d6efd",
            confirmButtonText: "OK",
            })
        }

        $handle = fopen($fileInput, 'r');
        $header = fread($handle, 5);
        fclose($handle);

        if ($header !== '%PDF-') return false;


    };
</script>

<?php
// define variables and set to empty values

$bucketname = gcloud_bucket_leases;

$Q = explode("/", $_SERVER['QUERY_STRING']);
parse_str($Q[0],$QueryParameters);
$QPleaseid = $QueryParameters['leaseid'];
$redirect = "editlease.php?leaseid=" . $QPleaseid;

$sql = "SELECT * FROM leases_view WHERE idlease = $QPleaseid and recordOwnerID IN ($accessto)";
$result = $con->query($sql);

if ($result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        $tenantid = $row["tenantid"];
        $tenantname = $row["tenantname"];
        $premisesid = $row["premisesid"];
        $unitname = $row["unitname"];
        $premisesaddress1 = $row["premisesaddress1"];
        $commencement = $row["commencement"];
        $term = $row["term"];
        $rightsofrenewal = $row["rightsofrenewal"];
        $annualrentpremises = $row["annualrentpremises"];
        $annualrentcarparks = $row["annualrentcarparks"];
        $invoicedate = $row["invoicedate"];
        $signedon = $row["signedon"];
        $signedbyid = $row["signedbyid"];
        $guarantorid = $row["guarantorid"];
        $bondamount = $row["bondamount"];
        $leaseexpirydate = $row["leaseexpirydate"];
        $leasestatusid = $row["leasestatusid"];
        $propertymanagercompanyid = $row["propertymanagercompanyid"];
        $propertymanagercontactid = $row["propertymanagercontactid"];

        $sql7 = "SELECT * from files WHERE bucketName = '$bucketname' and recordID = '$QPleaseid'";
  		$result7 = $con->query($sql7);

        $sql9 = "SELECT * from files WHERE bucketName is null and recordID = '$QPleaseid'";
  		$result9 = $con->query($sql9);
    }
} else {
    $tenantid = $tenantname = $premisesid = $unitname = $propertyaddress = $commencement = $term = $rightsofrenewal = $annualrentpremises = $annualrentcarparks = $invoicedate = $signedon = $signedbyid = $guarantorid = $bondamount = $leaseexpirydate = $leasestatusid = $propertymanagercompanyid = $propertymanagercontactid = "";
}
$tenantidErr = $premisesidErr = $unitnameErr = $commencementErr = $termErr = $rightsofrenewalErr = $annualrentpremisesErr = $annualrentcarparksErr = $invoicedateErr = $signedonErr = $signedbyidErr = $guarantoridErr = $bondamountErr = $leaseexpirydateErr = $leasestatusidErr = $propertymanagercompanyidErr = $propertymanagercontactidErr = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
  if (empty($_POST["tenantid"])) {
    $tenantidErr = "Tenant Name is required";
    } else {
    $tenantid = test_input($_POST["tenantid"]);
    //check if the field only contains letters dash or white space
    if (!preg_match("/^[0-9' ]*$/", $tenantid)) {
        $tenantidErr = "Only numbers allowed";
    }
  }

  if (empty($_POST["premisesid"])) {
    $premisesErr = "Premises is required";
    } else {
    $premisesid = test_input($_POST["premisesid"]);
    //check if the field only contains letters dash or white space
    if (!preg_match("/^[0-9' ]*$/", $premisesid)) {
        $premisesidErr = "Only numbers allowed";
    }
  }

  if (empty($_POST["commencement"])) {
    $commencementErr = "Commencement Date is required";
    } else {
    $commencement = test_input($_POST["commencement"]);
    //check if the field only contains letters dash or white space
    if (!preg_match("/^[0-9-' ]*$/", $commencement)) {
        $commencementErr = "Only numbers and dash allowed";
    }
  }

  if (empty($_POST["term"])) {
    $termErr = "Term is required";
    } else {
    $term = test_input($_POST["term"]);
    //check if the field only contains letters dash or white space
    if (!preg_match("/^[0-9.' ]*$/", $term)) {
        $termErr = "Only numbers are allowed";
    }
  }

  if (empty($_POST["rightsofrenewal"])) {
    $rightsofrenewalErr = "Rights of Renewal is required";
  } else {
    $rightsofrenewal = test_input($_POST["rightsofrenewal"]);
    //check if the field only contains letters dash or white space
    if (!preg_match("/^[a-zA-Z-0-9,()' ]*$/", $rightsofrenewal)) {
        $rightsofrenewalErr = "Only letters, dash and spaces allowed";
    }
  }

  if (empty($_POST["annualrentpremises"])) {
    $annualrentpremisesErr = "Annual Rent is required";
    } else {
    $annualrentpremises = test_input($_POST["annualrentpremises"]);
    //check if the field only contains letters dash or white space
    if (!preg_match("/^[0-9.' ]*$/", $annualrentpremises)) {
        $annualrentpremisesErr = "Only numbers and dot allowed";
    }
  }

  if (is_null($_POST["annualrentcarparks"])) {
    $annualrentcarparksErr = "Annual Carpark Rent is required";
    } else {
    $annualrentcarparks = test_input($_POST["annualrentcarparks"]);
    //check if the field only contains letters dash or white space
    if (!preg_match("/^[0-9.' ]*$/", $annualrentcarparks)) {
        $annualrentcarparksErr = "Only numbers and dot allowed";
    }
  }

  if (is_null($_POST["invoicedate"])) {
    $invoicedateErr = "Invoice day is required";
    } else {
    $invoicedate = test_input($_POST["invoicedate"]);
    //check if the field only contains letters dash or white space
    if (!preg_match("/^[0-9' ]*$/", $invoicedate)) {
        $invoicedateErr = "Only numbers allowed";
    }
  }

  if (empty($_POST["signedon"])) {
    $signedon = NULL;
    } else {
    $signedon = test_input($_POST["signedon"]);
    //check if the field only contains letters dash or white space
    if (!preg_match("/^[0-9-' ]*$/", $signedon)) {
        $signedonErr = "Only numbers and dash allowed";
    }
  }

    $signedbyid = test_input($_POST["signedbyid"]);
    //check if the field only contains letters dash or white space
    if (!preg_match("/^[0-9' ]*$/", $signedbyid)) {
          $signedbyidErr = "Only numbers allowed";
    }
  
    $guarantorid = test_input($_POST["guarantorid"]);
    //check if the field only contains letters dash or white space
    if (!preg_match("/^[0-9' ]*$/", $guarantorid)) {
        $guarantoridErr = "Only numbers allowed";
    }

    $bondamount = test_input($_POST["bondamount"]);
    //check if the field only contains letters dash or white space
    if (!preg_match("/^[0-9.' ]*$/", $bondamount)) {
        $bondamountErr = "Only numbers and dot allowed";
    }

    $propertymanagercompanyid = test_input($_POST["propertymanagercompanyid"]);
    //check if the field only contains numbers
    if (!preg_match("/^[0-9' ]*$/", $propertymanagercompanyid)) {
        $propertymanagercompanyidErr = "Only numbers allowed";
    }
    
    $propertymanagercontactid = test_input($_POST["propertymanagercontactid"]);
    //check if the field only contains numbers
    if (!preg_match("/^[0-9' ]*$/", $propertymanagercontactid)) {
        $propertymanagercontactidErr = "Only numbers allowed";
    }


  if (empty($_POST["leaseexpirydate"])) {
    $leaseexpirydateErr = "Expiry Date is required";
    } else {
    $leaseexpirydate = test_input($_POST["leaseexpirydate"]);
    //check if the field only contains letters dash or white space
    if (!preg_match("/^[0-9-' ]*$/", $leaseexpirydate)) {
        $leaseexpirydateErr = "Only numbers and dash allowed";
    }
  }

    //check that there is at least one active renewal record for this lease
    $sql2 = "SELECT count(leaseid) AS count FROM renewals WHERE renewalstatusid = 3 and leaseid = $QPleaseid and recordOwnerID IN ($accessto)";
    $result2 = $con->query($sql2);

    while($row2 = $result2->fetch_assoc()) {
        $activerenewalcount = $row2["count"];
    }
      
    
    if (empty($_POST["leasestatusid"])) {
            $leasestatusidErr = "Status is required";
    } else {
        $leasestatusid = test_input($_POST["leasestatusid"]);
        //check if the field only contains letters dash or white space
        if (!preg_match("/^[0-9' ]*$/", $leasestatusid)) {
            $leasestatusidErr = "Only numbers allowed";
        }
        //If the active renewal count is 0
        if($leasestatusid == 2 and $activerenewalcount == 0) {
            $leasestatusidErr = "There must be at lease 1 active renewal record to be able to set the lease status to Active";
        }
    }

}

function test_input($data) {
  $data = trim($data);
  $data = stripslashes($data);
  $data = htmlspecialchars($data);
  return $data;
}

if ($_SERVER["REQUEST_METHOD"] == "POST" and $tenantidErr == NULL and $premisesidErr == NULL and $commencementErr == NULL and $termErr == NULL and $rightsofrenewalErr == NULL and $annualrentpremisesErr == NULL and $annualrentcarparksErr == NULL and $invoicedateErr == NULL and $guarantoridErr == NULL and $bondamountErr == NULL and $signedonErr == NULL and $signedbyidErr == NULL and $leaseexpirydateErr == NULL and $leasestatusidErr == NULL and $propertymanagercompanyidErr == NULL and $propertymanagercontactidErr == NULL) {

    //prepare and bind
    if ($signedon == NULL) {
        $sql2 = "UPDATE leases SET tenantID = '$tenantid', premisesID = '$premisesid', commencement = '$commencement', term = '$term', rightsOfRenewal = '$rightsofrenewal', annualRentPremises = '$annualrentpremises', annualRentCarparks = '$annualrentcarparks', invoiceDate = '$invoicedate', signedOn = NULL, signedByID = '$signedbyid', guarantorID = '$guarantorid', bondAmount = '$bondamount', leaseExpiryDate = '$leaseexpirydate', leaseStatusID = '$leasestatusid', propertyManagerCompanyID = '$propertymanagercompanyid', propertyManagerContactID = '$propertymanagercontactid' WHERE idlease=$QPleaseid";
    } else {
        $sql2 = "UPDATE leases SET tenantID = '$tenantid', premisesID = '$premisesid', commencement = '$commencement', term = '$term', rightsOfRenewal = '$rightsofrenewal', annualRentPremises = '$annualrentpremises', annualRentCarparks = '$annualrentcarparks', invoiceDate = '$invoicedate', signedOn = '$signedon', signedByID = '$signedbyid', guarantorID = '$guarantorid', bondAmount = '$bondamount', leaseExpiryDate = '$leaseexpirydate', leaseStatusID = '$leasestatusid', propertyManagerCompanyID = '$propertymanagercompanyid', propertyManagerContactID = '$propertymanagercontactid' WHERE idlease=$QPleaseid";
    }
    
    if ($con->query($sql2) === TRUE) {
        echo '<table class="table table-hover">
        <tbody>
            <tr class="success">
                <td>Success!</td>
            </tr>
        </tbody></table>';

        echo "<div class=\"row\">
           <div class=\"col-sm-2\"><a href=\"viewlease.php?leaseid=".$QPleaseid."\" class=\"btn btn-primary\">Back to lease</a></div>
        </div>
        <div class=\"row\">";
    } else {
    echo 'Error updating record: ' . $con->error;
}

} else {
	//Get the list of tenants
    $sqlt = "SELECT idcompany, companyName FROM companies WHERE companyTypeID = 1 and recordOwnerID IN ($accessto) ORDER BY companyName";
    $resultt = $con->query($sqlt);
	//Get the list of premises
    $sqlp = "SELECT idpremises, unitName, premisesAddress1 FROM premises WHERE recordOwnerID IN ($accessto) ORDER BY unitname";
    $resultp = $con->query($sqlp);

    $sqlstatus = "SELECT * FROM leasestatus ORDER BY idleasestatus";
    $resultstatus = $con->query($sqlstatus);
	//Get the list of contacts for guarantors
    $sqlg = "SELECT * FROM contacts WHERE recordOwnerID IN ($accessto) and companyID = $tenantid ORDER BY firstName";
    $resultg = $con->query($sqlg);
	//Get the list of contacts for signatories
    $sqlh = "SELECT * FROM contacts WHERE recordOwnerID IN ($accessto) and companyID = $tenantid ORDER BY firstName";
    $resulth = $con->query($sqlh);
    
    //Get the list of property manager contacts
    $sql5 = "SELECT * FROM contacts_property_manager_view WHERE companyTypeID = 2 and recordOwnerID IN ($accessto) ORDER BY firstName";
    $result5 = $con->query($sql5);
    //Get the list of property manager companies
    $sql6 = "SELECT idcompany, companyName FROM companies WHERE companyTypeID = 2 and recordOwnerID IN ($accessto) ORDER BY companyName";
    $result6 = $con->query($sql6);

    ?>
    <form class="form form-large" method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"].'?leaseid='.$QPleaseid);?>">
    <div class="form-group">
        <label class="form-label col-sm-2" for="tenantid">Tenant: <span class="text-danger">*</span></label>
        <div class="col-sm-4">
            <select class="form-control" id="tenantid" type="text" name="tenantid">
                <?php
                while ($rowt = $resultt->fetch_assoc()) {
                    if($rowt["idcompany"] === $tenantid) {
                        echo "<option value=" . $rowt["idcompany"] . " selected>" . $rowt["companyName"]. "</option>";
                    } else {
                        echo "<option value=" . $rowt["idcompany"] . ">" . $rowt["companyName"] . "</option>";
                    }
                }
                ?>
            </select>
        </div>
        <div class="col-sm-6"><span class="error"><span class="text-danger"><?php echo $tenantidErr;?></span></div>
    </div>
    <div class="form-group">
        <label class="form-label col-sm-2" for="premisesid">Premises: <span class="text-danger">*</span></label>
        <div class="col-sm-4">
            <select class="form-control" id="premisesid" type="text" name="premisesid">
            <?php
                while ($rowp = $resultp->fetch_assoc()) {
                    if($rowp["idpremises"] === $premisesid) {
                        echo "<option value=" . $rowp["idpremises"] . " selected>" . $rowp["unitName"] . ", " . $rowp["premisesAddress1"] . "</option>";
                    } else {
                        echo "<option value=" . $rowp["idpremises"] . ">" . $rowp["unitName"] . ", " . $rowp["premisesAddress1"] . "</option>";
                    }
                }
            ?>
            </select>
        </div>
        <div class="col-sm-6"><span class="error"><span class="text-danger"><?php echo $premisesidErr;?></span></div>
    </div>
    <div class="form-group">
        <label class="form-label col-sm-2" for="commencement">Commencement Date:<span class="text-danger">*</span></label>
        <div class="col-sm-4"><input class="form-control" id="commencement" type="date" name="commencement" value="<?php echo $commencement;?>"></div>
        <div class="col-sm-6"><span class="error"><span class="text-danger"><?php echo $commencementErr;?></span></div>
    </div>
    <div class="form-group">
        <label class="form-label col-sm-2" for="term">Term (years): <span class="text-danger">*</span></label>
        <div class="col-sm-4"><input class="form-control" id="term" type="text" name="term" value="<?php echo $term;?>"></div>
        <div class="col-sm-6"><span class="error"><span class="text-danger"><?php echo $termErr;?></span></div>
    </div>
    <div class="form-group">
        <label class="form-label col-sm-2" for="rightsofrenewal">Rights of Renewal: <span class="text-danger">*</span></label>
        <div class="col-sm-4"><input class="form-control" id="rightsofrenewal" type="text" name="rightsofrenewal" value="<?php echo $rightsofrenewal;?>"></div>
        <div class="col-sm-6"><span class="error"><span class="text-danger"><?php echo $rightsofrenewalErr;?></span></div>
    </div>
    <div class="form-group">
        <label class="form-label col-sm-2" for="leaseexpirydate">Expiry Date: (yyyy-mm-dd)<span class="text-danger">*</span></label>
        <div class="col-sm-4"><input class="form-control" id="leaseexpirydate" type="date" name="leaseexpirydate" value="<?php echo $leaseexpirydate;?>"></div>
        <div class="col-sm-6"><span class="error"><span class="text-danger"><?php echo $leaseexpirydateErr;?></span></div>
    </div>
    <div class="form-group">
        <label class="form-label col-sm-2" for="annualrentpremises">Annual Rent - Premises: <span class="text-danger">*</span></label>
        <div class="col-sm-4"><input class="form-control" id="annualrentpremises" type="text" name="annualrentpremises" value="<?php echo $annualrentpremises;?>"></div>
        <div class="col-sm-6"><span class="error"><span class="text-danger"><?php echo $annualrentpremisesErr;?></span></div>
    </div>
    <div class="form-group">
        <label class="form-label col-sm-2" for="annualrentcarparks">Annual Rent - Carparks: <span class="text-danger">*</span></label>
        <div class="col-sm-4"><input class="form-control" id="annualrentcarparks" type="text" name="annualrentcarparks" value="<?php echo $annualrentcarparks;?>"></div>
        <div class="col-sm-6"><span class="error"><span class="text-danger"><?php echo $annualrentcarparksErr;?></span></div>
    </div>
    <div class="form-group">
        <label class="form-label col-sm-2" for="signedon">Signed On: (yyyy-mm-dd)</label>
        <div class="col-sm-4"><input class="form-control" id="signedon" type="date" name="signedon" value="<?php echo $signedon?>"></div>
        <div class="col-sm-6"><span class="error"><span class="text-danger"><?php echo $signedonErr;?></span></div>
    </div>

    <div class="form-group">
        <label class="form-label col-sm-2" for="signedbyid">Signed By:</label>
        <div class="col-sm-4">
            <select class="form-control" id="signedbyid" type="text" name="signedbyid">
                <?php
                echo "<option value=\"0\"> - Select a Signatory - </option>";
                while ($rowh = $resulth->fetch_assoc()) {
                    if($rowh["idcontacts"] === $signedbyid) {
                        echo "<option value=" . $rowh["idcontacts"] . " selected>" . $rowh["firstName"] . " " . $rowh["middleName"] . " " . $rowh["lastName"] . "</option>";
                    } else {
                        echo "<option value=" . $rowh["idcontacts"] . ">" . $rowh["firstName"] . " " . $rowh["middleName"] . " " . $rowh["lastName"] . "</option>";
                    }
                }
                ?>
            </select>
        </div>
        <div class="col-sm-6"><span class="error"><span class="text-danger"><?php echo $signedbyidErr;?></span></div>
    </div>

    <div class="form-group">
        <label class="form-label col-sm-2" for="guarantorid">Guarantor:</label>
        <div class="col-sm-4">
            <select class="form-control" id="guarantorid" type="text" name="guarantorid">
                <?php
                echo "<option value=\"0\"> - Select a Guarantor if lease has been guaranteed - </option>";
                while ($rowg = $resultg->fetch_assoc()) {
                    if($rowg["idcontacts"] === $guarantorid) {
                        echo "<option value=" . $rowg["idcontacts"] . " selected>" . $rowg["firstName"] . " " . $rowg["middleName"] . " " . $rowg["lastName"] . "</option>";
                    } else {
                        echo "<option value=" . $rowg["idcontacts"] . ">" . $rowg["firstName"] . " " . $rowg["middleName"] . " " . $rowg["lastName"] . "</option>";
                    }
                }
                ?>
            </select>
        </div>
        <div class="col-sm-6"><span class="error"><span class="text-danger"><?php echo $guarantoridErr;?></span></div>
    </div>
    <div class="form-group">
        <label class="form-label col-sm-2" for="bondamount">Bond: <span class="text-danger">*</span></label>
        <div class="col-sm-4"><input class="form-control" id="bondamount" type="text" name="bondamount" value="<?php echo $bondamount;?>"></div>
        <div class="col-sm-6"><span class="error"><span class="text-danger"><?php echo $bondamountErr;?></span></div>
    </div>
    
    <div class="form-group">
        <label class="form-label col-sm-2" for="propertymanagercompanyid">Property Management Company:</label>
        <div class="col-sm-4">
            <select class="form-control" id="propertymanagercompanyid" type="text" name="propertymanagercompanyid">
                <?php
                echo "<option value=\"0\"> - Select a Property Management Company - </option>";
                while ($row6 = $result6->fetch_assoc()) {
                    if($row6["idcompany"] === $propertymanagercompanyid) {
                        echo "<option value=" . $row6["idcompany"] . " selected>" . $row6["companyName"] . "</option>";
                    } else {
                        echo "<option value=" . $row6["idcompany"] . ">" . $row6["companyName"] . "</option>";
                    }
                }
                ?>
            </select>
        </div>
        <div class="col-sm-6"><span class="error"><span class="text-danger"><?php echo $propertymanagercompanyidErr;?></span></div>
    </div>
    
    <div class="form-group">
        <label class="form-label col-sm-2" for="propertymanagercontactid">Property Manager Contact:</label>
        <div class="col-sm-4">
            <select class="form-control" id="propertymanagercontactid" type="text" name="propertymanagercontactid">
                <?php
                echo "<option value=\"0\"> - Select a Property Manager Contact - </option>";
                while ($row5 = $result5->fetch_assoc()) {
                    if($row5["idcontact"] === $propertymanagercontactid) {
                        echo "<option value=" . $row5["idcontact"] . " selected>" . $row5["firstname"] . " " . $row5["lastname"] . "</option>";
                    } else {
                        echo "<option value=" . $row5["idcontact"] . ">" . $row5["firstname"] . " " . $row5["lastname"] . "</option>";
                    }
                }
                ?>
            </select>
        </div>
        <div class="col-sm-6"><span class="error"><span class="text-danger"><?php echo $propertymanagercontactidErr;?></span></div>
    </div>

    <div class="form-group">
        <label class="form-label col-sm-2" for="invoicedate">Invoice Date<span class="text-danger">*</span></label>
        <div class="col-sm-4">
            <select class="form-control" id="invoicedate" type="text" name="invoicedate">"
            <?php
                if($invoicedate === 1) {
                    echo "<option value=1 selected>1st of the rental month</option>";
                } else {
                    echo "<option value=1>1st of the rental month</option>";
                }
                if($invoicedate === 20) {
                    echo "<option value=0 selected>20th of the prior month</option>";
                } else {
                    echo "<option value=0>20th of the prior month</option>";
                }
                if($invoicedate != 1 && $invoicedate != 20) {
                    echo "<option value=" . date("d", strtotime($commencement)) . " selected>Anniversary of commencement date (" . date("d", strtotime($commencement)) . ")</option>";
                } else {
                    echo "<option value=" . date("d", strtotime($commencement)) . ">Anniversary of commencement date (" . date("d", strtotime($commencement)) . ")</option>";
                }
            ?>
            </select>
        </div>
        <div class="col-sm-6"><span class="error"><span class="text-danger"><?php echo $leasestatusidErr;?></span></div>
    </div>
    
    <div class="form-group">
        <label class="form-label col-sm-2" for="leasestatusid">Status<span class="text-danger">*</span></label>
        <div class="col-sm-4">
            <select class="form-control" id="leasestatusid" type="text" name="leasestatusid">"
            <?php
                while ($rowstatus = $resultstatus->fetch_assoc()) {
                    if($rowstatus["idleasestatus"] === $leasestatusid) {
                        echo "<option value=" . $rowstatus["idleasestatus"] . " selected>" . $rowstatus["leaseStatus"] . "</option>";
                    } else {
                        echo "<option value=" . $rowstatus["idleasestatus"] . ">" . $rowstatus["leaseStatus"] . "</option>";
                    }
                }
            ?>
            </select>
        </div>
        <div class="col-sm-6"><span class="error"><span class="text-danger"><?php echo $leasestatusidErr;?></span></div>
    </div>

    <div class="form-group" style="display:flex; align-items:flex-start">
        <label class="form-label col-sm-2">Documents:</label>
        <div class="col-sm-10" style="padding-top:10px;">
        <?php while($row7 = $result7->fetch_assoc()) { 
            $idfile = $row7["idfile"];
            $filepath = $row7["filePath"];
            $originalname = $row7["originalName"];
            $file_url = "https://storage.cloud.google.com/" . gcloud_bucket_leases . "/" . $filepath;
            ?>
            <div class="row">
                <div class="col-sm-9"><img src="img/pdf_logo.png" height="25px" style="margin:5px 10px 5px 0"><a href="<?php echo $file_url;?>"><?php echo $originalname;?></a></div>
                <div class="col-sm-1" style="padding-top: 8px;"><img src="img/delete_icon.png" alt="Delete" width="20px" height="20px" style="cursor: pointer;" onclick="deleteFile(<?php echo $idfile;?>);"></div>
            </div>
        	<?php } ?>
        
        <?php while($row9 = $result9->fetch_assoc()) { 
            $idfile = $row9["idfile"];
            $filepath = $row9["filePath"];
            $originalname = $row9["originalName"];
            $file_url = $filepath;
            ?>
            <div class="row">
                <div class="col-sm-9"><img src="img/document_logo.png" height="25px" style="margin:5px 10px 5px 0"><a href="<?php echo $file_url;?>" target="_blank"><?php echo $originalname;?></a></div>
                <div class="col-sm-1" style="padding-top: 8px;"><img src="img/delete_icon.png" alt="Delete" width="20px" height="20px" style="cursor: pointer;" onclick="deleteDocumentURL(<?php echo $idfile;?>);"></div>
            </div>
        	<?php } ?>

        </div>
    </div>

    

    <div class="form-group">
        <div class="col-sm-1" style="padding-top:40px;"><input type="submit" value="Submit" class="btn btn-primary" style="width:100px"></div>
    </div>
    </form>

    <div class="row">
        <form id="uploadForm" class="form form-large" action="gcloud-upload_file.php" method="post" enctype="multipart/form-data" onsubmit="return validateFile(event)" style="border:1px solid #cececeff; padding-bottom:10px; background-color: #f3f3f3ff;">
            <div class="form-group">
                <label class="form-label col-sm-2" for="fileToUpload">Upload Lease Document:</label>
                <div class="col-sm-6">
                    <input type="file" name="fileToUpload" id="fileToUpload" accept=".pdf"> 
                </div>
            </div>
            <input hidden type="text" name="bucketname" id="bucketname" value="<?php echo $bucketname;?>">
            <input hidden type="text" name="recordid" id="recordid" value="<?php echo $QPleaseid;?>">
            <input hidden type="text" name="redirect" id="redirect" value="editlease.php?leaseid=<?php echo $QPleaseid;?>">
            <div class="col-sm-6">
                <input type="submit" value="Upload File" name="submit" id="submitBtn" class="btn btn-primary">
            </div>
        </form>
    </div>

    <div class="row">
        <form id="addfilelink" class="form form-large" action="addfilelink.php" method="post" enctype="multipart/form-data" style="border:1px solid #cececeff; padding-bottom:10px; background-color: #f3f3f3ff;">
            <div class="form-group">
                <label class="form-label col-sm-2" for="filelink">Add Document URL: <span class="text-danger">*</span></label>
                <div class="col-sm-4"><input class="form-control" type="text" name="filelink" id="filelink"></div>
            </div>
            <div class="form-group">
                <label class="form-label col-sm-2" for="filename">File name: <span class="text-danger">*</span></label>
                <div class="col-sm-4"><input class="form-control" type="text" name="filename" id="filename"></div>
            </div>
            <input hidden type="text" name="recordid" id="recordid" value="<?php echo $QPleaseid;?>">
            <input hidden type="text" name="redirect" id="redirect" value="editlease.php?leaseid=<?php echo $QPleaseid;?>">
            <div class="col-sm-6">
                <input type="submit" value="Save Document URL" name="submit" id="submitBtn" class="btn btn-primary">
            </div>
        </form>
    </div>

<div class="row">
<?php
}

$con->close();
?>

</div>

<?=template_footer()?>
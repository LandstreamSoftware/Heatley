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

<?=template_header('Edit Inspection')?>

<div class="page-title">
	<div class="icon">
		<svg width="20" height="20" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 576 512"><!--!Font Awesome Free 6.5.1 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license/free Copyright 2024 Fonticons, Inc.--><path d="M575.8 255.5c0 18-15 32.1-32 32.1h-32l.7 160.2c0 2.7-.2 5.4-.5 8.1V472c0 22.1-17.9 40-40 40H456c-1.1 0-2.2 0-3.3-.1c-1.4 .1-2.8 .1-4.2 .1H416 392c-22.1 0-40-17.9-40-40V448 384c0-17.7-14.3-32-32-32H256c-17.7 0-32 14.3-32 32v64 24c0 22.1-17.9 40-40 40H160 128.1c-1.5 0-3-.1-4.5-.2c-1.2 .1-2.4 .2-3.6 .2H104c-22.1 0-40-17.9-40-40V360c0-.9 0-1.9 .1-2.8V287.6H32c-18 0-32-14-32-32.1c0-9 3-17 10-24L266.4 8c7-7 15-8 22-8s15 2 21 7L564.8 231.5c8 7 12 15 11 24z"/></svg>
	</div>	
	<div class="wrap">
		<h2>Edit Inspection</h2>
	</div>
</div>

<div class="block">

<?php
// define variables and set to empty values

$bucketname = gcloud_bucket_inspection_media;

$Q = explode("/", $_SERVER['QUERY_STRING']);
parse_str($Q[0],$QueryParameters);
$QPinspectionid = $QueryParameters['id'];
$redirect = "editinspection.php?id=" . $QPinspectionid;

$sql = "SELECT * from inspections_view WHERE idinspection = $QPinspectionid and recordOwnerID IN ($accessto)";
$result = $con->query($sql);
$sql2 = "SELECT * FROM buildings WHERE recordOwnerID IN ($accessto) ORDER BY buildingName";
$result2 = $con->query($sql2);
$sql3 = "SELECT * FROM inspectiontype";
$result3 = $con->query($sql3);
$sql4 = "SELECT * FROM inspectionareas WHERE recordOwnerID = 0 OR recordOwnerID in ($accessto) ORDER BY RecordOwnerID, areaName";
$result4 = $con->query($sql4);
$sql5 = "SELECT * FROM inspectionconditions ORDER BY idinspectioncondition";
$result5 = $con->query($sql5);
$sql6 = "SELECT * FROM inspectionstatus";
$result6 = $con->query($sql6);

$sql7 = "SELECT * FROM inspectionmedia WHERE inspectionID = $QPinspectionid";
$result7 = $con->query($sql7);


if ($result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        $buildingid = $row["buildingid"];
        $premisesid = $row["premisesid"];
        $typeid = $row["inspectiontypeid"];
        $areaid = $row["idinspectionarea"];
        $conditionid = $row["conditionid"];
        $inspectorid = $row["inspectorid"];
        $inspectorname = $row["inspectorfirstname"] . " " . $row["inspectorlastname"];
        $inspectionstatusid = $row["inspectionstatusid"];
        $notes = $row["notes"];
        $unitname = $row["unitname"];
        $areaname = $row["areaname"];
        $tenantname = $row["tenantname"];
    } 
} else {
    $buildingid = $premisesid = $typeid = $areaid = $conditionid = $inspectorid = $inspectionstatusid = $notes = "";
}
    $buildingidErr = $premisesidErr = $typeidErr = $areaidErr = $conditionidErr = $inspectoridErr = $inspectionstatusidErr = $notesErr = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
  if (empty($_POST["buildingid"])) {
    $buildingidErr = "Building is required";
  } else {
    $buildingid = test_input($_POST["buildingid"]);
    //check if the field only contains letters dash or white space
    if (!preg_match("/^[0-9' ]*$/", $buildingid)) {
        $buildingidErr = "Only numbers allowed";
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

  $sql8 = "SELECT idlease, tenantname, leasestatusid FROM leases_view where premisesid = $premisesid and leasestatusid = 2";
    $result8 = $con->query($sql8);
    if ($result8->num_rows > 0) {
      while ($row8 = $result8->fetch_assoc()) {
        $leaseid = $row8["idlease"];
      }
    } else {
        $leaseid = 0;
    }

  if (empty($_POST["typeid"])) {
    $typeidErr = "Inspection Type is required";
  } else {
    $typeid = test_input($_POST["typeid"]);
    //check if the field only contains letters dash or white space
    if (!preg_match("/^[0-9' ]*$/", $typeid)) {
        $typeidErr = "Only numbers allowed";
    }
  }

  if (empty($_POST["areaid"])) {
    $areaidErr = "Area is required";
  } else {
    $areaid = test_input($_POST["areaid"]);
    //check if the field only contains letters dash or white space
    if (!preg_match("/^[0-9' ]*$/", $areaid)) {
        $areaidErr = "Only numbers allowed";
    }
  }
  
  if (empty($_POST["conditionid"])) {
    $conditionidErr = "Condition is required";
  } else {
    $conditionid = test_input($_POST["conditionid"]);
    //check if the field only contains letters dash or white space
    if (!preg_match("/^[0-9' ]*$/", $conditionid)) {
        $conditionidErr = "Only numbers allowed";
    }
  }

  if (empty($_POST["inspectionstatusid"])) {
    $inspectionstatusidErr = "Status is required";
  } else {
    $inspectionstatusid = test_input($_POST["inspectionstatusid"]);
    //check if the field only contains letters dash or white space
    if (!preg_match("/^[0-9' ]*$/", $inspectionstatusid)) {
        $inspectionstatusidErr = "Only numbers allowed";
    }
  }


    $notes = test_input($_POST["notes"]);
    //check if the field only contains letters dash or white space
    if (!preg_match("/&|^[a-zA-Z-0-9āēīōūĀĒĪŌŪ()*.\,\/+?;\:%!@\r\n' ]*$/", $notes)) {
        $notesErr = "Disallowed characters used in notes field";
    }



}

function test_input($data) {
  $data = trim($data);
  $data = stripslashes($data);
  $data = htmlspecialchars($data);
  return $data;
}


if ($_SERVER["REQUEST_METHOD"] == "POST" and $buildingidErr == NULL and $premisesidErr == NULL and $typeidErr == NULL and $areaidErr == NULL and $conditionidErr == NULL and $inspectoridErr == NULL and $inspectionstatusidErr == NULL and $notesErr == NULL) {

    //prepare and bind
$sqlupdate = "UPDATE inspections SET
    premisesID = '$premisesid',
    inspectionTypeID = '$typeid',
    areaID = '$areaid',
    inspectionConditionID = '$conditionid',
    inspectionStatusID = '$inspectionstatusid',
    notes = '$notes',
    leaseID = '$leaseid'
    WHERE idinspection = $QPinspectionid";


    if ($con->query($sqlupdate) === TRUE) {
        echo '<table class="table table-hover">
        <tbody>
            <tr class="success">
                <td>Success!</td>
            </tr>
        </tbody>
        </table>';

        echo "<div class=\"row\">
           <div class=\"col-sm-2\"><a href=\"listinspections.php\" class=\"btn btn-primary\">Back to Inspections</a></div>
        </div>
        <div class=\"row\">";
    } else {
    echo 'Error updating record: ' . $con->error;
}
} else {
    ?>
    <form class="form form-horizontal" method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"].'?id='.$QPinspectionid);?>">
    <div class="form-group">
        <label class="form-label col-sm-2" for="buildingid">Building Name: <span class="text-danger">*</span></label>
        <div class="col-sm-6">
            <select class="form-control" id="buildingid" name="buildingid" onchange="updatePremisesList()">
                <?php echo "<option value=\"\"> - Select a building - </option>";
                while ($row2 = $result2->fetch_assoc()) {
                    if ($buildingid == $row2["idbuildings"]) {
                        echo "<option value=\"" . $row2["idbuildings"] . "\" selected>" . $row2["buildingName"] . "</option>";
                    } else {
                        echo "<option value=\"" . $row2["idbuildings"] . "\">" . $row2["buildingName"] . "</option>";
                    }
                }
                ?>
            </select>
        </div>
        <div class="col-sm-4"><span class="error"><span class="text-danger"><?php echo $buildingidErr;?></span></div>
    </div>
    <div  class="form-group">
        <label class="form-label col-sm-2" for="premisesid">Premises: <span class="text-danger">*</span></label>
        <div class="col-sm-6">
            <select class="form-control" id="premisesid" name="premisesid">
                <?php echo "<option value=\"0\"> - Select premises - </option>"; ?>
            </select>
        </div>
        <div class="col-sm-4"><span class="error"><span class="text-danger"><?php echo $premisesidErr;?></span></div>
    </div>
    <div  class="form-group">
        <label class="form-label col-sm-2" for="typeid">Inspection Type:</label>
        <div class="col-sm-6">
            <select class="form-control" id="typeid" name="typeid">
                <?php echo "<option value=\"\"> - Select an Inspection Type - </option>";
                while ($row3 = $result3->fetch_assoc()) {
                    if ($typeid == $row3["idinspectiontype"]) {
                        echo "<option value=\"" . $row3["idinspectiontype"] . "\" selected>" . $row3["inspectionType"] . "</option>";
                    } else {
                        echo "<option value=\"" . $row3["idinspectiontype"] . "\">" . $row3["inspectionType"] . "</option>";
                    }
                }
                ?>
            </select>
        </div>
        <div class="col-sm-4"><span class="error"><span class="text-danger"><?php echo $typeidErr;?></span></div>
    </div>
    <div  class="form-group">
        <label class="form-label col-sm-2" for="areaid">Area: <span class="text-danger">*</span></label>
        <div class="col-sm-6">
            <select class="form-control" id="areaid" name="areaid">
                <?php echo "<option value=\"\"> - Select an Area - </option>";
                while ($row4 = $result4->fetch_assoc()) {
                    if ($areaid == $row4["idinspectionarea"]) {
                        echo "<option value=\"" . $row4["idinspectionarea"] . "\" selected>" . $row4["areaName"] . "</option>";
                    } else {
                        echo "<option value=\"" . $row4["idinspectionarea"] . "\">" . $row4["areaName"] . "</option>";
                    }
                }
                ?>
            </select>
        </div>
        <div class="col-sm-4"><span class="error"><span class="text-danger"><?php echo $areaidErr;?></span></div>
    </div>
    <div  class="form-group">
        <label class="form-label col-sm-2" for="conditionid">Condition: <span class="text-danger">*</span></label>
        <div class="col-sm-6">
            <select class="form-control" id="conditionid" name="conditionid">
                <?php echo "<option value=\"\"> - Select a Condition - </option>";
                while ($row5 = $result5->fetch_assoc()) {
                    if ($conditionid == $row5["idinspectioncondition"]) {
                        echo "<option value=\"" . $row5["idinspectioncondition"] . "\" selected>" . $row5["conditionName"] . "</option>";
                    } else {
                        echo "<option value=\"" . $row5["idinspectioncondition"] . "\">" . $row5["conditionName"] . "</option>";
                    }
                }
                ?>
            </select>
        </div>
        <div class="col-sm-4"><span class="error"><span class="text-danger"><?php echo $conditionidErr;?></span></div>
    </div>
    <div  class="form-group">
        <label class="form-label col-sm-2" for="inspectorid">Inspected By:</label>
        <div class="col-sm-6"><input class="form-control" id="inspectorid" type="text" name="inspectorid" value="<?php echo $inspectorname;?>" readonly></div>
        <div class="col-sm-4"><span class="error"><span class="text-danger"><?php echo $inspectoridErr;?></span></div>
    </div>
    <div class="form-group">
        <label class="form-label col-sm-2" for="inspectionstatusid">Status: <span class="text-danger">*</span></label>
        <div class="col-sm-6">
            <select class="form-control" id="inspectionstatusid" name="inspectionstatusid">
            <?php
                echo "<option value=\"\"> - Select a Status - </option>";
            while($row6 = $result6->fetch_assoc()) {
                if($row6["idinspectionstatus"] == $inspectionstatusid){
                    echo "<option value=\"" . $row6["idinspectionstatus"] . "\" selected>". $row6["inspectionStatus"] . "</option>";
                } else {
                    echo "<option value=\"" . $row6["idinspectionstatus"] . "\">". $row6["inspectionStatus"] . "</option>";
                }
            }
            ?>
            </select>
        </div>
        <div class="col-sm-4"><span class="error"><span class="text-danger"><?php echo $inspectionstatusidErr;?></span></div>
    </div>
    <div  class="form-group">
        <label class="form-label col-sm-2" for="notes">Notes:</label>
        <div class="col-sm-6">
            <textarea class="form-control"  id="notes" name="notes" rows="4" maxlength="500"><?php echo $notes;?></textarea>
        </div>
        <div class="col-sm-4"><span class="error"><span class="text-danger"><?php echo $notesErr;?></span></div>
    </div>

    <div class="row">
        <?php
            if ($result7->num_rows > 0) { 
                    // output data of each media record
                $filecount = 0;
                while($row7 = $result7->fetch_assoc()) {
                    $idfile = $row7["idinspectionmedia"];
                    $filecount++;
                    ?>
                    <div class="gallery">
                        <a target="_blank" href="<?php echo "https://storage.googleapis.com/" . gcloud_bucket_inspection_media . "/" . $row7["fileURL"];?>">
                            <img src="<?php echo "https://storage.googleapis.com/" . gcloud_bucket_inspection_media . "/" . $row7["fileURL"];?>">
                        </a>
                        <div class="desc">
                            <?php echo "<strong>" . $filecount . ". " . $areaname . "</strong><br>" . $unitname . "<br>" . $tenantname . "<br>" ?>
                        </div>
                        <div class="deletebutton">
                            <p class="btn btn-danger" type="" onclick="deleteFile(<?php echo $idfile;?>);">delete</p>
                        </div>
                    </div>
                    <?php 
                }
            }
            // Add new media file
            ?>

    </div>
    <input hidden type="text" name="redirect" id="redirect" value="editinspection.php?id=<?php echo $QPinspectionid;?>">
    <input hidden type="text" name="leaseid" id="leaseid" value="<?php echo $leaseid;?>"></div>
    <div class="form-group">
        <div class="col-sm-1" style="padding-top:40px;"><input type="submit" value="Submit" class="btn btn-primary" style="width:100px"></div>
    </div>
    </form>

    <div class="row">
        <form id="uploadForm" class="form form-medium" action="gcloud-upload_media.php" method="post" enctype="multipart/form-data" onsubmit="return validateFile(event)">
            <div class="form-group">
                <label class="form-label col-sm-5" for="fileToUpload">Upload Media File:</label>
                <div class="col-sm-6">
                <input type="file" name="fileToUpload" id="fileToUpload" accept=".jpg, .jpeg"> 
                </div>
                <input hidden type="text" name="bucketname" id="bucketname" value=<?php echo $bucketname;?>>
                <input hidden type="text" name="recordid" id="recordid" value="<?php echo $QPinspectionid;?>">
                <input hidden type="text" name="premisesid" id="premisesid" value="<?php echo $premisesid;?>">
                <input hidden type="text" name="redirect" id="redirect" value="editinspection.php?id=<?php echo $QPinspectionid;?>">
                <div class="col-sm-6">
                    <input type="submit" value="Upload File" name="submit" id="submitBtn" class="btn btn-primary">
                </div>
            </div>
        </form>
    </div>




<div class="row">





<?php
}

$con->close();
?>

<script>
    function updatePremisesList(itemId) {
        const buildingId = Number(document.getElementById("buildingid").value);
        //const buildingName = document.getElementById("buildingname");
        //const premisesName = document.getElementById("premisesname");
        const premisesId = document.getElementById("premisesid");
        const premisesURL = "get_leasepremises.php?buildingid=" + buildingId;
        const selectedPremisesId = itemId;
        if (buildingId) {
            fetch(premisesURL)
                .then(response => response.json())
                .then(data => {
                    // Clear any previous values
                    premisesId.value = '';
                    premisesId.innerHTML = '<option value=\"0\"> - Select premises - </option>';
                    // Populate the Premises dropdown list
                    data.forEach(item => {
                        const option = document.createElement("option");
                        option.value = item.idpremises;
                        option.textContent = item.unitname;
                        if (item.idpremises == selectedPremisesId) {
                            option.selected = true;
                        }
                        premisesId.appendChild(option);
                    });
                });

        }
    }
</script>
<script>
    var premisesid = <?php echo json_encode($premisesid); ?>;
    window.addEventListener('load', function () {
        updatePremisesList(premisesid);
    });
</script>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
    function deleteFile(idfile) {
        Swal.fire({
        title: "Delete inspection media file",
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
                fetch('gcloud-delete_media.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded'
                    },
                    body: 'idfile=' + encodeURIComponent(idfile)
                })
                .then(response => response.text())
                .then(data => {
                    console.log('Success:', data);
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

</div>

<?=template_footer()?>
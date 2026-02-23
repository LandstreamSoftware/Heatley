<?php
// Include the appmain.php file
include 'appmain.php';
require_once '../vendor/autoload.php';
use Google\Cloud\Storage\StorageClient;
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

<?= template_header('Add Inspections') ?>

<?php
$typeid = $buildingid = $premisesid = $areaid = $areaname = $conditionid = $conditionname = $notes = $photos = $inspector_id = $recordownerid = $mediatype = "";
$typeidErr = $buildingidErr = $premisesidErr = $areaidErr = $areanameErr = $conditionidErr = $conditionnameErr = $notesErr = $photosErr = $inspector_idErr = $recordowneridErr = $mediatypeErr = "";
$showSuccessMessage = false;

$Q = explode("/", $_SERVER['QUERY_STRING']);
parse_str($Q[0],$QueryParameters);
// The inspection ID when editing an existing inspection
if(empty($QueryParameters['id'])){
    $inspectionid = "";
}else{
    $inspectionid = $QueryParameters['id'];
}
if(empty($QueryParameters['tid'])){
    $typeid = "";
}else{
    $typeid = $QueryParameters['tid'];
}
// the building ID
if(empty($QueryParameters['bid'])){
    $buildingid = "";
}else{
    $buildingid = $QueryParameters['bid'];
}
// the premises ID
if(empty($QueryParameters['pid'])){
    $premisesid = "";
}else{
    $premisesid = $QueryParameters['pid'];
}
if(empty($QueryParameters['aid'])){
    $areaid = "";
}else{
    $areaid = $QueryParameters['aid'];
}
if(empty($QueryParameters['cid'])){
    $conditionid = "";
}else{
    $conditionid = $QueryParameters['cid'];
}
if(empty($QueryParameters['notes'])){
    $notes = "";
}else{
    $notes = $QueryParameters['notes'];
}


if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $id = $_POST["id"];

    if (empty($_POST["typeid"])) {
        $typeidErr = "Type is required";
    } else {
        $typeid = test_input($_POST["typeid"]);
        //check if the field only contains numbers
        if (!preg_match("/^[0-9' ]*$/", $typeid)) {
            $typeidErr = "Only numbers allowed";
        }
    }
    if (empty($_POST["buildingid"])) {
        $buildingidErr = "Building is required";
    } else {
        $buildingid = test_input($_POST["buildingid"]);
        //check if the field only contains numbers
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

    if (empty($_POST["areaid"])) {
        $areaidErr = "Area is required";
    } else {
        $areaid = test_input($_POST["areaid"]);
        //check if the field only contains letters dash or white space
        if (!preg_match("/^[a-zA-Z-0-9āēīōūĀĒĪŌŪ' ]*$/", $areaid)) {
            $areaidErr = "Only letters, numbers, dash and spaces allowed";
        }
    }

    if (!empty($_POST["notes"])) {
        $notes = test_input($_POST["notes"]);
        //check if the field only contains letters dash or white space
        if (!preg_match("/^[a-zA-Z-0-9āēīōūĀĒĪŌŪ'. ]*$/", $notes)) {
            $notesErr = "Only letters, numbers, dash and spaces allowed";
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

    if (empty($_POST["userid"])) {
        $useridErr = "Condition is required";
    } else {
        $userid = test_input($_POST["userid"]);
        //check if the field only contains letters dash or white space
        if (!preg_match("/^[0-9' ]*$/", $userid)) {
            $useridErr = "Only numbers allowed";
        }
    }
}

function test_input($data) {
  $data = trim($data);
  $data = stripslashes($data);
  $data = htmlspecialchars($data);
  return $data;
}

//Query for dropdowns
$sql101 = "SELECT * FROM inspectiontype";
$result101 = $con->query($sql101);
$sql102 = "SELECT * FROM buildings WHERE recordOwnerID in ($accessto) ORDER BY buildingName";
$result102 = $con->query($sql102);
$sql103 = "SELECT * FROM inspectionconditions ORDER BY idinspectioncondition";
$result103 = $con->query($sql103);
$sql104 = "SELECT * FROM inspectionareas WHERE recordOwnerID = 0 OR recordOwnerID in ($accessto)";
$result104 = $con->query($sql104);
$sql105 = "SELECT * FROM premises WHERE buildingID = $buildingid and recordOwnerID in ($accessto) ORDER BY unitName";
$result105 = $con->query($sql105);
?>

<h1>Add Inspection</h1>
    <div class="content">


<?php
if ($_SERVER["REQUEST_METHOD"] == "POST" and $buildingidErr == NULL and $premisesidErr == NULL and $typeidErr == NULL and $areaidErr == NULL and $conditionidErr == NULL) {
    //prepare and bind
    $GoogleCredentials = Google_Application_Creadentials_file;
    putenv('GOOGLE_APPLICATION_CREDENTIALS=' . $GoogleCredentials);

    $bucketname = gcloud_bucket_inspection_media;

    $input = json_decode(file_get_contents('php://input'), true);

    $photos = $_FILES['photos'] ?? [];
    $photoSummaries = [];

    // 1) Check for an inline Data URL upload
    if (!empty($photos['name'])) {





        for ($i = 0; $i < count($photos['name']); $i++) {
            $tmpName = $photos['tmp_name'][$i];
            $name = $photos['name'][$i];
            $size = $photos['size'][$i];
            $type = $photos['type'][$i];

            $content = file_get_contents($tmpName);  // Read image data
            $base64 = base64_encode($content);       // Optional: encode for display or API use

            // Example: collect a summary for each photo
            $photoSummaries[] = [
                'filename' => $name,
                'size_kb' => round($size / 1024, 2),
                'mime' => $type,
                'base64_snippet' => substr($base64, 0, 100) . '...'
            ];
        }

        // Respond with a summary JSON
        header('Content-Type: application/json');
        echo json_encode([
            'status' => 'success',
            'submitted_data' => compact(
                'typeid', 'buildingid', 'premisesid', 'areaid',
                'notes', 'conditionid', 'userid', 'recordownerid'
            ),
            'photo_summary' => $photoSummaries
        ]);






echo "<script>console.log('179. Photos is not empty');</script>";

      //$photos = $input["photos"];

      // Ensure $photos is an array
      if (!is_array($photos)) {
          $photos = [$photos]; // Wrap single string in array
      }

      $storage = new StorageClient();
      $bucket = $storage->bucket($bucketname);
      $uploadedObjects = [];

      foreach ($photos as $index => $photoUrl) {
          // 2) Extract MIME type and Base64 payload
          if (preg_match('#^data:(.+?);base64,(.+)$#', $photoUrl, $matches)) {
              $mimeType = $matches[1];           // e.g. "image/png"
              $b64Payload = $matches[2];

              // 3) Decode
              $rawData = base64_decode($b64Payload, true);
              if ($rawData === false) {
                  http_response_code(400);
                  exit("Invalid Base64 data at index $index.");
              }

              // 4) Derive object name, folders & extension
              $ext = explode('/', $mimeType, 2)[1] ?? 'bin';
              $objectName = sprintf('%s/%s/%s_%d.%s', $premisesid, $id, $id, $index, $ext);

              // 5) Upload to GCS
              $bucket->upload($rawData, [
                  'name' => $objectName,
                  'metadata' => ['contentType' => $mimeType]
              ]);

              $uploadedObjects[] = $objectName;
          } else {
              http_response_code(400);
              exit("Malformed Data URL at index $index.");
          }
echo "<script>console.log('220. File uploaded to GCS');</script>";
          $stmtmedia = $con->prepare("INSERT INTO inspectionmedia (inspectionID, fileURL, mediaType, mediaIndex) VALUES (?, ?, ?, ?)");
          $stmtmedia->bind_param("iss", $id, $gcsfileurl, $mediatype, $index);
          if ($stmtmedia->execute()) {
            //echo "Media record created";
echo "<script>console.log('225. Media record created');</script>";
          }
      }
    } else {
echo "<script>console.log('229. No photos');</script>";
    }

    $timestamp = round($id / 1000, 0); //convert to seconds
    $date = new DateTime();
    $date->setTimestamp($timestamp);
    $inspection_date = date_format($date, 'Y-m-d H:i:s');

    $inspectionStatusID = 3; // Completed

    // Find an existing inspection record
    $sql1 = "SELECT idinspection FROM inspections WHERE idinspection = $id";
    $result1 = $con->query($sql1);
    // Find the lease for the selected premises
    $sql2 = "SELECT idlease, tenantname, leasestatusid FROM leases_view where premisesid = $premisesid and leasestatusid = 2";
    $result2 = $con->query($sql2);
    if ($result2->num_rows > 0) {
      while ($row2 = $result2->fetch_assoc()) {
        $leaseid = $row2["idlease"];
      }
    } else {
        $leaseid = '';
    }

    if ($result1->num_rows == 0) { // If no existing inspection found
      // Add a new inspection record
      $stmt = $con->prepare("INSERT INTO inspections (idinspection, premisesID, areaID, inspectorID, leaseID, inspectionTypeID, inspectionConditionID, inspectionDate, inspectionStatusID, notes, recordOwnerID) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
      $stmt->bind_param("iiiiiiisisi", $id, $premisesid, $areaid, $userid, $leaseid, $typeid, $conditionid, $inspection_date, $inspectionStatusID, $notes, $recordownerid);
      if ($stmt->execute()) {
echo "<script>console.log('258. Inspection record created');</script>";
      }
    } else {
      //Update the existing inspection
      $sql3 = "UPDATE inspections SET 
        premisesID = '$premisesid',
        areaID = '$areaid',
        inspectorID = '$userid',
        leaseID = '$leaseid',
        inspectionTypeID = '$typeid',
        inspectionConditionID = '$conditionid',
        inspectionDate = '$inspection_date',
        inspectionStatusID = '$inspectionStatusID',
        notes = '$notes'
        WHERE idinspection = $id";
      if ($con->query($sql3) === TRUE) {
echo "<script>console.log('274. Inspection updated');</script>";
      }
    }
// POST completed successfully
$showSuccessMessage = true;
}
?>


    <form id="inspection-form" class="panel">
        <label>
            Building:
            <select id="buildingid" name="buildingid" onchange="updatePremisesList()">
                <?php echo "<option value=\"\"> - Select a building - </option>";
                while ($row102 = $result102->fetch_assoc()) {
                    if ($buildingid == $row102["idbuildings"]) {
                        echo "<option value=\"" . $row102["idbuildings"] . "\" selected>" . $row102["buildingName"] . "</option>";
                    } else {
                        echo "<option value=\"" . $row102["idbuildings"] . "\">" . $row102["buildingName"] . "</option>";
                    }
                }
                ?>
            </select>
            <div class="error-message"><?php echo $buildingidErr;?></div>
        </label>

        <label>
            Premises:
            <select id="premisesid" name="premisesid">
                <?php echo "<option value=\"\"> - Select premises - </option>";
                while ($row105 = $result105->fetch_assoc()) {
                    if ($premisesid == $row105["idpremises"]) {
                        echo "<option value=\"" . $row105["idpremises"] . "\" selected>" . $row105["unitName"] . "</option>";
                    } else {
                        echo "<option value=\"" . $row105["idpremises"] . "\">" . $row105["unitName"] . "</option>";
                    }
                }
                ?>
            </select>
            <div class="error-message"><?php echo $premisesidErr;?></div>
        </label>

        <label>
            Area:
            <select id="areaid" name="areaid">
                <?php echo "<option value=\"\"> - Select inspection area - </option>";
                while ($row104 = $result104->fetch_assoc()) {
                    if ($areaid == $row104["idinspectionarea"]) {
                        echo "<option value=\"" . $row104["idinspectionarea"] . "\" selected>" . $row104["areaName"] . "</option>";
                    } else {
                        echo "<option value=\"" . $row104["idinspectionarea"] . "\">" . $row104["areaName"] . "</option>";
                    }
                }
                ?>
            </select>
            <div class="error-message"><?php echo $areaidErr;?></div>
        </label>

        <label>
            Inspection Type:
            <select id="typeid" name="typeid">
                <?php echo "<option value=\"\"> - Select inspection type - </option>";
                while ($row101 = $result101->fetch_assoc()) {
                    if ($typeid == $row101["idinspectiontype"]) {
                        echo "<option value=\"" . $row101["idinspectiontype"] . "\" selected>" . $row101["inspectionType"] . "</option>";
                    } else {
                        echo "<option value=\"" . $row101["idinspectiontype"] . "\">" . $row101["inspectionType"] . "</option>";
                    }
                }
                ?>
            </select>
            <div class="error-message"><?php echo $typeidErr;?></div>
        </label>

        <label>
            Condition:
            <select id="conditionid" name="conditionid">
                <?php echo "<option value=\"\"> - Select a condition - </option>";
                while ($row103 = $result103->fetch_assoc()) {
                    if ($conditionid == $row103["idinspectioncondition"]) {
                        echo "<option value=\"" . $row103["idinspectioncondition"] . "\" selected>" . $row103["conditionName"] . "</option>";
                    } else {
                        echo "<option value=\"" . $row103["idinspectioncondition"] . "\">" . $row103["conditionName"] . "</option>";
                    }
                }
                ?>
            </select>
            <div class="error-message"><?php echo $conditionidErr;?></div>
        </label>

        <label>
            Notes:
            <textarea id="notes" name="notes" rows="4" maxlength="500"><?php echo $notes;?></textarea>
        </label>

        <label>
            Photos:
            <label for="photo-input" class="blue-button">Add New Photo</label>
            <input type="file" id="photo-input" name="photo-input" accept="image/*" capture="camera" style="display:none;">
        </label>

        <div id="photosPreview" class="form-select"></div>

        <!-- Hidden fields -->
        <input type="text" name="userid" id="userid" value="<?php echo $accountid; ?>">
        <input type="text" name="recordownerid" id="recordownerid" value="<?php echo $mycompanyid; ?>">
        <input type="text" name="id" id="id">
        <input type="text" name="index" id="index">

        <div class="center">
            <button class="blue-button btn-action" type="submit">Save Inspection</button>
        </div>
    </form>

    <script src="/app/assets/js/app2.js"></script>

    <script>
        function updatePremisesList() {
            const buildingId = Number(document.getElementById("buildingid").value);
            const buildingName = document.getElementById("buildingname");
            const premisesName = document.getElementById("premisesname");
            const premisesId = document.getElementById("premisesid");
            const premisesURL = "../get_leasepremises.php?buildingid=" + buildingId;
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
                            premisesId.appendChild(option);
                        });
                    });

            }
        }
    </script>

<?php

?>

</div>
<script>
    const d = new Date();
    let time = d.getTime();
    document.getElementById("id").value = time;
</script>

<script>
    <?php if ($showSuccessMessage): ?>
        Swal.fire({
            title: "Success!",
            text: "Inspection saved to the Mother Ship.",
            icon: "success",
            iconColor: "#32b10cff",
            width: "350px",
            showCancelButton: false,
            confirmButtonColor: "#32b10cff",
            confirmButtonText: "OK",
        })
    <?php endif; ?>
</script>

</body>
</html>
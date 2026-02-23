<?php
// Include the appmain.php file
include 'appmain.php';
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
}

function test_input($data) {
  $data = trim($data);
  $data = stripslashes($data);
  $data = htmlspecialchars($data);
  return $data;
}

//Query for dropdowns
$sql1 = "SELECT * FROM inspectiontype";
$result1 = $con->query($sql1);
$sql2 = "SELECT * FROM buildings WHERE recordOwnerID in ($accessto) ORDER BY buildingName";
$result2 = $con->query($sql2);
$sql3 = "SELECT * FROM inspectionconditions ORDER BY idinspectioncondition";
$result3 = $con->query($sql3);
$sql4 = "SELECT * FROM inspectionareas WHERE recordOwnerID = 0 OR recordOwnerID in ($accessto)";
$result4 = $con->query($sql4);
?>

<h1>Add Inspection</h1>
    <div class="content">
<!--    <form id="inspection-form" class="panel" method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]);?>">  -->
    <form id="inspection-form" class="panel">

        <label>
            Building:
            <select id="buildingid" name="buildingid" onchange="updatePremisesList()">
                <?php echo "<option value=\"\"> - Select a building - </option>";
                while ($row = $result2->fetch_assoc()) {
                    if ($buildingid == $row["idbuildings"]) {
                        echo "<option value=\"" . $row["idbuildings"] . "\" selected>" . $row["buildingName"] . "</option>";
                    } else {
                        echo "<option value=\"" . $row["idbuildings"] . "\">" . $row["buildingName"] . "</option>";
                    }
                }
                ?>
            </select>
            <div class="error-message"><?php echo $buildingidErr;?></div>
        </label>

        <label>
            Premises:
            <select id="premisesid" name="premisesid">
                <?php echo "<option value=\"0\"> - Select premises - </option>"; ?>
            </select>
            <div class="error-message"><?php echo $premisesidErr;?></div>
        </label>

        <label>
            Area:
            <select id="areaid" name="areaid">
                <?php echo "<option value=\"\"> - Select inspection area - </option>";
                while ($row4 = $result4->fetch_assoc()) {
                    if ($areaid == $row4["idinspectionarea"]) {
                        echo "<option value=\"" . $row4["idinspectionarea"] . "\" selected>" . $row4["areaName"] . "</option>";
                    } else {
                        echo "<option value=\"" . $row4["idinspectionarea"] . "\">" . $row4["areaName"] . "</option>";
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
                while ($row1 = $result1->fetch_assoc()) {
                    if ($typeid == $row1["idinspectiontype"]) {
                        echo "<option value=\"" . $row1["idinspectiontype"] . "\" selected>" . $row1["inspectionType"] . "</option>";
                    } else {
                        echo "<option value=\"" . $row1["idinspectiontype"] . "\">" . $row1["inspectionType"] . "</option>";
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
                while ($row3 = $result3->fetch_assoc()) {
                    if ($conditionid == $row3["idinspectioncondition"]) {
                        echo "<option value=\"" . $row3["idinspectioncondition"] . "\" selected>" . $row3["conditionName"] . "</option>";
                    } else {
                        echo "<option value=\"" . $row3["idinspectioncondition"] . "\">" . $row3["conditionName"] . "</option>";
                    }
                }
                ?>
            </select>
            <div class="error-message"><?php echo $conditionidErr;?></div>
        </label>

        <label>
            Notes:
            <textarea id="notes" rows="4" maxlength="500"><?php echo $notes;?></textarea>
        </label>

        <label>
            Photos:
            <label for="photo-input" class="blue-button">Add New Photo</label>
            <input type="file" id="photo-input" name="photo-input" accept="image/*" capture="camera" style="display:none;">
        </label>

        <div id="photosPreview" class="form-select"></div>

        <!-- Hidden fields -->
        <input type="hidden" name="userid" id="userid" value="<?php echo $accountid; ?>">
        <input type="hidden" name="recordownerid" id="recordownerid" value="<?php echo $mycompanyid; ?>">
        <input type="hidden" name="id" id="id">
        <input type="hidden" name="index" id="index">
            
        <div class="center">
         <!--   <button class="blue-button btn-action" type="submit" id="save-button">Save Locally</button> -->
            <button class="blue-button btn-action" type="submit" id="upload-button">Upload to Website</button>
        </div>
    </form>
<!--
<div class="center">
    <button class="blue-button btn-action" id="sync-button">Sync Saved Inspections</button>
</div>


<div id="inspections-list"></div>
            

<div class="center">
    <button class="blue-button btn-action" id="clear-saved-list">Clear Saved Inspections</button>
</div>
-->
<script src="/app/assets/js/app.js"></script>

<?php

?>

</div>

<script>
    // Register the service worker
    if ('serviceWorker' in navigator) {
        navigator.serviceWorker.register('/app/service-worker.js').then(() => {
            console.log('Service Worker registered.');
        });
    }
</script>
<script>
    function updatePremisesList(itemId) {
        const buildingId = Number(document.getElementById("buildingid").value);
        const buildingName = document.getElementById("buildingname");
        const premisesName = document.getElementById("premisesname");
        const premisesId = document.getElementById("premisesid");
        const premisesURL = "../get_leasepremises.php?buildingid=" + buildingId;
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
    function updateAreasList() {
        const premisesId = Number(document.getElementById("premisesid").value);
        const unitName = document.getElementById("premisesid").options[document.getElementById("premisesid").selectedIndex].text;
        const premisesName = document.getElementById("premisesname");
        const areaId = document.getElementById("area");
        const premisesURL = "../get_leasepremises.php?premisesid=" + premisesId;
        if (premisesId) {
            //areaId.innerHTML = '<option value="">-- Select Area --</option>';
            fetch(premisesURL)
                .then(response => response.json())
                .then(data => {
                    // Populate the Areas dropdown list
                    data.forEach(item => {
                        //const option = document.createElement("option");
                        //option.value = item.idinspectionroom;
                        //option.textContent = item.roomname;
                        //areaId.appendChild(option);
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
</body>

</html>
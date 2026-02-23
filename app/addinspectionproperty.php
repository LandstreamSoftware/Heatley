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

<?= template_header('Add Inspection') ?>

<?php
//Query for dropdowns
$sql = "SELECT * FROM buildings WHERE recordOwnerID IN ($accessto) ORDER BY buildingName";
$result = $con->query($sql);

echo '<h1>Select a Property...</h1>';

while ($row = $result->fetch_assoc()) {
    $buildingid = $row["idbuildings"];
    echo '<div class="panel">
    <div class="panel-title">'
        . $row["buildingName"] . 
    '</div>
    <div>'
        . $row["buildingAddress1"] .
    '</div>
    <div class="center">
        <a href="addinspectionpremises.php?bid=' . $buildingid . '" type="button" class="blue-button full_button">Select</a>
    </div>
    </div>';
}
?>
<!--
<script>
    // Register the service worker
    if ('serviceWorker' in navigator) {
        navigator.serviceWorker.register('/pwa/service-worker.js').then(() => {
            console.log('Service Worker registered.');
        });
    }
</script>
-->
</body>
</html>
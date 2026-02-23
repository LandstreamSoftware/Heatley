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
$Q = explode("/", $_SERVER['QUERY_STRING']);
parse_str($Q[0],$QueryParameters);
//$Query = $QueryParameters['premisesid'];
if(empty($QueryParameters['bid'])){
    $QPbid = "";
}else{
    $QPbid = $QueryParameters['bid'];
}
if(empty($QueryParameters['pid'])){
    $QPpid = "";
}else{
    $QPpid = $QueryParameters['pid'];
}
if(empty($QueryParameters['tid'])){
    $QPtid = "";
}else{
    $QPtid = $QueryParameters['tid'];
}
if(empty($QueryParameters['aid'])){
    $QPaid = "";
}else{
    $QPaid = $QueryParameters['aid'];
}

//Query for dropdowns
$sql = "SELECT * FROM inspectionconditions ORDER BY idinspectioncondition";
$result = $con->query($sql);

echo '<h1>Select the Inspection condition...</h1>';

while ($row = $result->fetch_assoc()) {
    $conditionid = $row["idinspectioncondition"];
    echo '<div class="panel">
    <div class="panel-title">'
        . $row["conditionName"] . 
    '</div>

    <div class="center">
        <a href="addinspection.php?bid=' . $QPbid . '&pid=' . $QPpid . '&tid=' . $QPtid . '&aid=' . $QPaid . '&cid=' . $conditionid . '" type="button" class="blue-button full_button">Select</a>
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
<?php
//This is a scheduled task run by cron jobs

//Access this page using an authorisation token.
$cron_token = '370e86358956424a1433a9d1812bc5e5dd210622879358880e301050da049eeb';

// Check if the request contains the correct token 
if (php_sapi_name() !== 'cli' && (!isset($_GET['token']) || $_GET['token'] !== $cron_token)) { 
    http_response_code(403); // Forbidden 
    die('Access denied.'); 
} else {
    //Run this code:

include 'main.php';

//Get the draft renewal records with startdate in next 60 days.
//Once the renewal record is set to active it wont be included in this notification.
$sql = "SELECT * FROM dashboard_upcoming_view WHERE 
    renewalstatusid = 1
    AND (startdate < (NOW() + INTERVAL 60 DAY))
    AND (startdate > NOW())
    ORDER BY startdate, tenantname";
$result = $con->query($sql);

if ($result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        $recipientemail = $row["propertymanageremail"];
        $renewalid = $row["renewalid"];
        $tenantname = $row["tenantname"];
        $renewaltype = $row["renewaltype"];
        $renewaldate = $row["startdate"];

        send_renewal_notification_email($recipientemail, $renewalid, $tenantname, $renewaltype, $renewaldate);
    }
}



}

return true;
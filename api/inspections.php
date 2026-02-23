<?php
// Access this page using an authorization token.

include_once '../config.php';

require_once '../vendor/autoload.php';

use Google\Cloud\Storage\StorageClient;

$valid_api_key = '21062287935eeb370e86358956428880e301050da049eeb370e86358956424a1433a9d1812bc5e5dd';
$headers = getallheaders();
$authHeader = isset($headers['Authorization']) ? $headers['Authorization'] : '';

if ($authHeader !== 'Bearer ' . $valid_api_key) {
  http_response_code(401);
  echo json_encode(['error' => 'Unauthorized']);
  exit;
} else {

  header('Content-Type: application/json');

  // Connect to the MySQL database using MySQLi
  $con = mysqli_connect(db_host, db_user, db_pass, db_name);
  // If there is an error with the MySQL connection, stop the script and output the error
  if (mysqli_connect_errno()) {
    exit('Failed to connect to MySQL: ' . mysqli_connect_error());
  }

  // Handle POST only
  if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $GoogleCredentials = Google_Application_Creadentials_file;
    putenv('GOOGLE_APPLICATION_CREDENTIALS=' . $GoogleCredentials);

    $bucketname = gcloud_bucket_inspection_media;

    $input = json_decode(file_get_contents('php://input'), true);
    $id = $input['id'];
    $typeid = $input['type'];
    $buildingid = $input['building'];
    $premisesid = $input['premises'];
    $areaid = $input['area'];
    $conditionid = $input['condition'];
    $notes = $input['notes'];
    $photos = $input['photos'];
    $inspector_id = $input['user'];
    $recordownerid = $input['recordowner'];
    $mediatype = "photo";

    //echo file_get_contents('php://input');

    // 1) Check for an inline Data URL upload
    if (!empty($input["photos"])) {
      $photos = $input["photos"];

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
      }
    }

    $timestamp = round($id / 1000, 0); //convert to seconds
    $date = new DateTime();
    $date->setTimestamp($timestamp);
    // Set timezone to New Zealand
    $nzTimezone = new DateTimeZone('Pacific/Auckland');
    $date->setTimezone($nzTimezone);

    $inspection_date = date_format($date, 'Y-m-d H:i:s');

    
    $inspectionStatusID = 3; // Completed
    $sql1 = "SELECT idinspection FROM inspections WHERE idinspection = $id";
    $result1 = $con->query($sql1);
    $sql2 = "SELECT idlease, tenantname, leasestatusid FROM leases_view where premisesid = $premisesid and leasestatusid = 2";
    $result2 = $con->query($sql2);
    if ($result2->num_rows > 0) {
      while ($row2 = $result2->fetch_assoc()) {
        $leaseid = $row2["idlease"];
      }
    } else {
      $leaseid = "";
    }

    if ($result1->num_rows == 0) {
      //Add a new inspection record
      $stmt = $con->prepare("INSERT INTO inspections (idinspection, premisesID, areaID, inspectorID, leaseID, inspectionTypeID, inspectionConditionID, inspectionDate, inspectionStatusID, notes, recordOwnerID) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
      $stmt->bind_param("iiiiiiisisi", $id, $premisesid, $areaid, $inspector_id, $leaseid, $typeid, $conditionid, $inspection_date, $inspectionStatusID, $notes, $recordownerid);
      if ($stmt->execute()) {
//        echo "Inspection record created";
        if (!empty($input["photos"])) {
          $photos = $input["photos"];

          // Ensure $photos is an array
          if (!is_array($photos)) {
              $photos = [$photos]; // Wrap single string in array
          }
          foreach ($photos as $index => $photoUrl) {
            
            //Derive object name, folders & extension
            $ext = explode('/', $mimeType, 2)[1] ?? 'bin';
            $objectName = sprintf('%s/%s/%s_%d.%s', $premisesid, $id, $id, $index, $ext);
            $gcsfileurl = $objectName;
            $stmtmedia = $con->prepare("INSERT INTO inspectionmedia (inspectionID, fileURL, mediaType) VALUES (?, ?, ?)");
            $stmtmedia->bind_param("iss", $id, $gcsfileurl, $mediatype);
            if ($stmtmedia->execute()) {
              //echo "Media record created";
            }
          }
        }
      }
      echo json_encode([
        'status' => 'success'
      ]);
    } else {
      //Update the existing inspection
      $sql3 = "UPDATE inspections SET 
        premisesID = '$premisesid',
        areaID = '$areaid',
        inspectorID = '$inspector_id',
        leaseID = '$leaseid',
        inspectionTypeID = '$typeid',
        inspectionConditionID = '$conditionid',
        inspectionDate = '$inspection_date',
        inspectionStatusID = '$inspectionStatusID',
        notes = '$notes'
        WHERE idinspection = $id";
      if ($con->query($sql3) === TRUE) {
        $sql3 = "SELECT * FROM inspectionmedia WHERE inspectionID = $id";
        $result3 = $con->query($sql3);
        if ($result3->num_rows == 0) { // No media records exist
          if (!empty($input["photos"])) {
            $photos = $input["photos"];

            // Ensure $photos is an array
            if (!is_array($photos)) {
                $photos = [$photos]; // Wrap single string in array
            }
            foreach ($photos as $index => $photoUrl) {
              //Derive object name, folders & extension
              $ext = explode('/', $mimeType, 2)[1] ?? 'bin';
              $objectName = sprintf('%s/%s/%s_%d.%s', $premisesid, $id, $id, $index, $ext);
              $gcsfileurl = "/" . $objectName;
              $stmtmedia = $con->prepare("INSERT INTO inspectionmedia (inspectionID, fileURL, mediaType) VALUES (?, ?, ?)");
              $stmtmedia->bind_param("iss", $id, $gcsfileurl, $mediatype);
              if ($stmtmedia->execute()) {
                echo json_encode([
                  'status' => 'success'
                ]);
              }
            }
          }
        } else { // There are existing media records against this inspection
          while ($row3 = $result3->fetch_assoc()) {
            $inspectionmediaid = $row3["idinspectionmedia"];
            //Derive object name, folders & extension
            $ext = explode('/', $mimeType, 2)[1] ?? 'bin';
            $objectName = sprintf('%s/%s/%s_%d.%s', $premisesid, $id, $id, $index, $ext);
            $gcsfileurl = "/" . $objectName;
            $sql4 = "UPDATE inspectionmedia SET
            inspectionID = '$id',
            fileURL = '$gcsfileurl',
            mediaType = '$mediatype'
            WHERE idinspectionmedia = $inspectionmediaid";
          }
          echo json_encode([
            'status' => 'success'
          ]);
        }
      }
    }
  }

  if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $inspection_id = $_GET['inspection_id'] ?? null;
    if (isset($_GET['inspection_id']) && $_GET['inspection_id'] !== "" && filter_var($_GET['inspection_id'], FILTER_VALIDATE_INT) !== false) {
      $sql = "SELECT * FROM inspections where idinspection = $inspection_id";
    } elseif (!isset($_GET['inspection_id'])) {
      $sql = "SELECT * FROM inspections";
    } else {
      $sql = "SELECT * FROM inspections where idinspection = 0";
    }
    $result = $con->query($sql);
    $rows = [];
    if ($result->num_rows > 0) {
      while ($row = $result->fetch_assoc()) {
        $rows[] = $row;
      }
    }
    echo json_encode($rows);
  }

  $con->close();
}
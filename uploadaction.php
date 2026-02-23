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

$sql9 = "SELECT * FROM accounts WHERE id = $accountid";
$result9 = $con->query($sql9);

while ($row9 = $result9->fetch_assoc()) {
    $recordownerid = $row9["companyID"];
}


template_header('File Upload');
?>

<div class="page-title">
	<div class="icon">
		<svg width="20" height="20" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 576 512"><!--!Font Awesome Free 6.5.1 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license/free Copyright 2024 Fonticons, Inc.--><path d="M575.8 255.5c0 18-15 32.1-32 32.1h-32l.7 160.2c0 2.7-.2 5.4-.5 8.1V472c0 22.1-17.9 40-40 40H456c-1.1 0-2.2 0-3.3-.1c-1.4 .1-2.8 .1-4.2 .1H416 392c-22.1 0-40-17.9-40-40V448 384c0-17.7-14.3-32-32-32H256c-17.7 0-32 14.3-32 32v64 24c0 22.1-17.9 40-40 40H160 128.1c-1.5 0-3-.1-4.5-.2c-1.2 .1-2.4 .2-3.6 .2H104c-22.1 0-40-17.9-40-40V360c0-.9 0-1.9 .1-2.8V287.6H32c-18 0-32-14-32-32.1c0-9 3-17 10-24L266.4 8c7-7 15-8 22-8s15 2 21 7L564.8 231.5c8 7 12 15 11 24z"/></svg>
	</div>	
	<div class="wrap">
		<h2>File Upload</h2>
	</div>
</div>

<?php
if ($_SERVER["REQUEST_METHOD"] == "POST") {

  if (empty($_POST["tablename"])) {
    $tablenameErr = "No table name";
  } else {
    $tablename = test_input($_POST["tablename"]);
    if (!preg_match("/^[a-zA-Z-0-9,()' ]*$/", $tablename)) {
        $tablenameErr = "Only letters and numbers allowed";
    }
  }

  if (empty($_POST["recordid"])) {
    $recordidErr = "No record ID";
    } else {
    $recordid = test_input($_POST["recordid"]);
    //check if the field only contains letters dash or white space
    if (!preg_match("/^[0-9' ]*$/", $recordid)) {
        $recordidErr = "Only numbers allowed";
    }
  }
  
  if (!empty($_POST["redirect"])) {
    $redirect = $_POST["redirect"];
  }

  $file = $_FILES["fileToUpload"];
  $original_name = basename($file["name"]);
  $file_ext = pathinfo($original_name, PATHINFO_EXTENSION);
  //echo $file["type"] . "<br>";
  $file_type = $file["type"];
  $file_size = $file["size"] / 1000;
  // Allowed file types and size limit (5MB)
  $allowed_types = ["application/pdf"];
  $max_size = 5 * 1024 * 1024;

  if (!in_array($file_type, $allowed_types)) {
      die("Invalid file type. Only PDF's are allowed.");
  }

  if ($file_size > $max_size) {
    die("File to large. Max size: 5MB.");
  }

  $timestamp = date("Ymd_His");
  $safe_filename = preg_replace("/[^a-zA-Z0-9_-]/", "_", pathinfo($original_name, PATHINFO_FILENAME));
  $new_filename = "{$tablename}_{$recordid}_{$safe_filename}_{$timestamp}.{$file_ext}";



  //$upload_dir = "uploads/{$tablename}/{$recordid}/";
  //if (!is_dir($upload_dir)) {
  //  mkdir($upload_dir, 0777, true);
  //}

  $file_path = $upload_dir . $new_filename;
  if (!move_uploaded_file($file["tmp_name"], $file_path)) {
    die("Error moving uploaded file.");
  }

  // Store metadata in the database
  $relative_path = "uploads/{$tablename}/{$recordid}/{$new_filename}";
  $stmt = $con->prepare("INSERT INTO files (tableName, recordID, originalName, fileName, filePath, fileType, fileSize) VALUES(?, ?, ?, ?, ?, ?, ?)");
  $stmt->bind_param("sissssi", $tablename, $recordid, $original_name, $new_filename, $relative_path, $file_type, $file_size);

  if ($stmt->execute()) {
    echo "<div class=\"row\">
      <div class=\"col-sm-12\">File uploaded successfully: <a href='$relative_path'>$original_name</a></div>
    </div>";
    if (!empty($_POST["redirect"])) {
    	echo "<div class=\"row\">
    		<div class=\"col-sm-2\"><a href='$redirect' class=\"btn btn-primary\" style=\"width:100px; margin:20px 0 0 0;\">Back</a></div>
    	</div>";
  	}
    
  } else {
    echo "Database error: " . $stmt->error;
  }
  $stmt->close();
} else {
  echo "No file uploaded.";
}

$con->close();


  function test_input($data)
  {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
  }
?>
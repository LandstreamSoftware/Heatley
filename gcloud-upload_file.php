<?php
// Include the main.php file
include 'main.php';
// Check if the user is logged in, if not then redirect to login page
check_loggedin($con);
// Template code below

require 'vendor/autoload.php';

use Google\Cloud\Storage\StorageClient;

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

    if (!empty($_POST["bucketname"])) {
        $bucketname = test_input($_POST["bucketname"]);
        //check if the field only contains letters dash or white space
        if (!preg_match("/^[a-zA-Z-0-9āēīōūĀĒĪŌŪ' ]*$/", $bucketname)) {
            $bucketnameErr = "Bucket name does not comply";
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

    $target_dir = "{$recordid}/";
    $target_file = $target_dir . basename($_FILES["fileToUpload"]["name"]);
    $source_file = $_FILES["fileToUpload"]["tmp_name"];
    $file_size = $_FILES['fileToUpload']['size'] / 1000;
    $file_ext = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));

    //echo "Bucket name: " . $bucketname . "<br>";
    //echo "Record ID: " . $recordid . "<br>";
    //echo "Target File: " . $target_file . "<br>";
    echo "Name: " . $_FILES["fileToUpload"]["name"] . "<br>";
    echo "File Size: " . $file_size . "<br>";
    //echo "File Extension: " . $file_ext . "<br>";
    //echo "tmp_name: " . $_FILES["fileToUpload"]["tmp_name"] . "<br>";
    //echo "Error: " . $_FILES["fileToUpload"]["error"] . "<br>";

    $uploadOK = 1;





    $file = $_FILES["fileToUpload"]["tmp_name"];
    $original_name = basename($_FILES["fileToUpload"]["name"]);
    
    

    $file_type = $_FILES["fileToUpload"]["type"];
    
    // Allowed file types and size limit (7MB)
    $allowed_types = ["application/pdf", "pdf"];
    $max_size = (7 * 1024 * 1024) / 1000;

    $handle = fopen($file, "r") or die("Unable to read file!");
    $header = fread($handle, 5);

    if ($header === '%PDF-') {

        if (!in_array($file_ext, $allowed_types)) {
                die("Invalid file type (" . $file_type . "). Only PDF files are allowed.");
        }

        if ($file_size > $max_size) {
            die("File to large. Max size: 7MB.");
        }

        $timestamp = date("Ymd_His");
        $safe_filename = preg_replace("/[^a-zA-Z0-9_-]/", "_", pathinfo($original_name, PATHINFO_FILENAME));
        $new_filename = "{$safe_filename}_{$timestamp}.{$file_ext}";
        $file_path = "{$recordid}/{$new_filename}";

        //echo "File Path: " . $file_path . "<br>";

        $GoogleCredentials = Google_Application_Creadentials_file;

        putenv('GOOGLE_APPLICATION_CREDENTIALS='.$GoogleCredentials);

        $uploadOK = 0;
    
        function uploadFileToGCS($bucketName, $objectName, $source, &$uploadOK) 
        {
            $storage = new StorageClient();
            $bucket = $storage->bucket($bucketName);
            $file = fopen($source, 'r');
            $object = $bucket->upload($file, [
                'name' => $objectName
            ]);

            $uploadOK = 1;
            return $uploadOK;
        }

        uploadFileToGCS($bucketname, $file_path, $source_file, $uploadOK);

        if ($uploadOK === 1) {
            $stmt = $con->prepare("INSERT INTO files (bucketName, recordID, originalName, fileName, filePath, fileType, fileSize) VALUES(?, ?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("sissssi", $bucketname, $recordid, $original_name, $new_filename, $file_path, $file_type, $file_size);  // Bind parameters
            $result = $stmt->execute(); // Execute inside function

            if ($result) {
                echo "<div class=\"row\">
                    <div class=\"col-sm-12\">Success!</div>
                </div>
                <div class=\"row\">
                    <div class=\"col-sm-12\">File uploaded successfully to: gs://$bucketname/$file_path\n</div>
                </div>";
            } else {
                echo "<div class=\"row\">
                <div class=\"col-sm-12\">Something went wrong with either the file upload or the File record creation.</div>
            </div>"; 
            }
            fclose($handle);
        } else {
            echo "Nope!" . $uploadOK;
        }
    }

    if (!empty($_POST["redirect"])) {
        echo "<div class=\"row\">
            <div class=\"col-sm-2\"><a href='$redirect' class=\"btn btn-primary\" style=\"width:100px; margin:20px 0 0 0;\">Back</a></div>
        </div>";
    }
}

function test_input($data)
    {
        $data = trim($data);
        $data = stripslashes($data);
        $data = htmlspecialchars($data);
        return $data;
    }
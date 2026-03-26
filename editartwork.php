<?php
// Include the main.php file
include 'main.php';
// Check if the user is logged in, if not then redirect to login page
check_loggedin($con);
// Template code below

$accountid = $_SESSION['account_id'] ?? null;
if (!is_int($accountid) && !ctype_digit($accountid)) {
    exit('Invalid account ID');
}
$accountid = (int)$accountid;

$stmt = $con->prepare("SELECT * FROM accesscontrol WHERE accountID = ?");
$stmt->bind_param("i", $accountid); // "i" = integer
$stmt->execute();
$resultAccess = $stmt->get_result();

$accessto = -1;

if ($resultAccess->num_rows > 0) {
    while($rowAccess = $resultAccess->fetch_assoc()) {
       $accessto .= "," . $rowAccess["companyID"]; 
    }
}

?>

<?=template_header('Edit Artwork')?>

<div class="page-title">
	<div class="icon">
		<svg width="20" height="20" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 576 512"><!--!Font Awesome Free 6.5.1 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license/free Copyright 2024 Fonticons, Inc.--><path d="M575.8 255.5c0 18-15 32.1-32 32.1h-32l.7 160.2c0 2.7-.2 5.4-.5 8.1V472c0 22.1-17.9 40-40 40H456c-1.1 0-2.2 0-3.3-.1c-1.4 .1-2.8 .1-4.2 .1H416 392c-22.1 0-40-17.9-40-40V448 384c0-17.7-14.3-32-32-32H256c-17.7 0-32 14.3-32 32v64 24c0 22.1-17.9 40-40 40H160 128.1c-1.5 0-3-.1-4.5-.2c-1.2 .1-2.4 .2-3.6 .2H104c-22.1 0-40-17.9-40-40V360c0-.9 0-1.9 .1-2.8V287.6H32c-18 0-32-14-32-32.1c0-9 3-17 10-24L266.4 8c7-7 15-8 22-8s15 2 21 7L564.8 231.5c8 7 12 15 11 24z"/></svg>
	</div>	
	<div class="wrap">
		<h2>Edit Artwork</h2>
	</div>
</div>

<div class="block">

<?php
// define variables and set to empty values

$Q = explode("/", $_SERVER['QUERY_STRING']);
parse_str($Q[0],$QueryParameters);
$QPartworkid = $QueryParameters['id'];

$sql = "SELECT * 
        FROM artworks 
        WHERE id = ?";
$stmt = $con->prepare($sql);
if (!$stmt) {
    die("Prepare failed: " . $con->error);
}
$stmt->bind_param("i", $QPartworkid);
$stmt->execute();
$result = $stmt->get_result();

$sql1 = "SELECT * from locations ORDER BY location";
$result1 = $con->query($sql1);

$sql2 = "SELECT * from artists ORDER BY firstname";
$result2 = $con->query($sql2);

$sql3 = "SELECT * from currencies";
$result3 = $con->query($sql3);



if ($result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        $id = $row["id"];
        $artistid = $row["artist_id"];
        $title = $row['title'];
        $medium = $row["medium"];
        $gallery = $row["gallery"];
        $locationid = $row["location_id"];
        $price = $row["hammer_price"];
        $currencyid = $row["currency_id"];
        $summary = $row["summary"];
        $imageURL = $row["Image_URL"];
        $provenanceURL = $row["Provenance_URL"];
    } 
} else {
    $id = $artistid = $title = $medium = $gallery = $locationid = $price = $currencyid = $summary = $imageURL = $provenanceURL = "";
}
    $idErr = $artistidErr = $titleErr = $mediumErr = $galleryErr = $locationidErr = $priceErr = $currencyidErr = $summaryErr = $imageURLErr = $provenanceURLErr = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    if (empty($_POST["artistid"])) {
        $artistidErr = "Artist is required";
    } else {
        $artistid = test_input($_POST["artistid"]);
        if (!preg_match("/^[0-9' ]*$/", $artistid)) {
            $artistidErr = "Only numbers allowed";
        }
    }

    $title = test_input($_POST["title"]);
    $titleErr = validate_title($title);

    $medium = test_input($_POST["medium"]);
    //check if the field only contains letters dash or white space
    if (!preg_match("/&|^[a-zA-Z-0-9āēīōūĀĒĪŌŪ()' \/]*$/", $medium)) {
        $mediumErr = "Some characters are not allowed";
    }

    $gallery = test_input($_POST["gallery"]);
    //check if the field only contains letters dash or white space
    if (!preg_match("/&|^[a-zA-Z-0-9āēīōūĀĒĪŌŪ()' \/]*$/", $gallery)) {
        $galleryErr = "Some characters are not allowed";
    }

    if (empty($_POST["locationid"])) {
        $locationidErr = "Location is required";
    } else {
        $locationid = test_input($_POST["locationid"]);
        if (!preg_match("/^[0-9' ]*$/", $locationid)) {
            $locationidErr = "Only numbers allowed";
        }
    }

    $price = test_input($_POST["price"]);
    //check if the field only contains numbers
    if (!preg_match("/^[0-9'. ]*$/", $price)) {
        $priceErr = "Only numbers allowed";
    }

    if (!empty($_POST["price"]) and empty($_POST["currencyid"])) {
        $currencyidErr = "Currency is required";
    } else {
        $currencyid = test_input($_POST["currencyid"]);
        if (!preg_match("/^[0-9' ]*$/", $currencyid)) {
            $currencyidErr = "Only numbers allowed";
        }
    }

    $summary = $_POST['summary'];

}

function test_input($data) {
  $data = trim($data);
  $data = stripslashes($data);
//  $data = htmlspecialchars($data);
  return $data;
}

function validate_title(string $title): ?string {
    // Length check first
    if ($title === '') {
        return "Title is required";
    }

    if (mb_strlen($title, 'UTF-8') > 255) {
        return "Title is too long";
    }

    // Allow letters, numbers, spaces, common punctuation, macrons
    if (!preg_match('/^[\p{L}\p{N}\s\(\)\'"\/,.\-:&…]+$/u', $title)) {
        return "Some characters are not allowed";
    }

    return null;
}

if ($_SERVER["REQUEST_METHOD"] == "POST" and $artistidErr == NULL and $titleErr == NULL and $mediumErr == NULL and $galleryErr == NULL and $locationidErr == NULL and $priceErr == NULL and $currencyidErr == NULL and $summaryErr == NULL and $imageURLErr == NULL and $provenanceURLErr == NULL) {

//prepare and bind
$stmt = $con->prepare("UPDATE artworks SET 
    artist_id = ?,
    title = ?,
    medium = ?,
    gallery = ?,
    location_id = ?,
    hammer_price = ?,
    currency_id = ?,
    summary = ?,
    Image_URL = ?,
    Provenance_URL = ?
    WHERE id = ?");
$stmt->bind_param("isssidisssi", $artistid, $title, $medium, $gallery, $locationid, $price, $currencyid, $summary, $imageURL, $provenanceURL, $QPartworkid);
$stmt->execute();

    if ($stmt->execute()) {
        echo '<table class="table table-hover">
        <tbody>
            <tr class="success">
                <td>Success!</td>
            </tr>
        </tbody>
        </table>';

        echo "<div class=\"row\">
           <div class=\"col-sm-2\"><a href=\"listartworks.php\" class=\"btn btn-primary\">Back to Artworks</a></div>
        </div>
        <div class=\"row\">";
    } else {
        echo 'Error updating record: ' . $con->error;
    }
} else {
    ?>




    <form id="EditArtworkForm" class="form" method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"].'?id='.$QPartworkid);?>">
    <div class="form-group">
        <div class="col-sm-2"></div>
        <div class="col-sm-6"><img src="<?php echo $imageURL;?>" height="300"></div>
    </div>
    <div class="form-group">
        <label class="form-label col-sm-2" for="title">Title: <span class="text-danger">*</span></label>
        <div class="col-sm-6"><input class="form-control" id="title" type="text" name="title" value="<?php echo htmlspecialchars($title, ENT_QUOTES, 'UTF-8') ?>"></div>
        <div class="col-sm-2"><span class="error"><span class="text-danger"><?php echo $titleErr;?></span></div>
    </div>
    <div class="form-group">
        <label class="form-label col-sm-2" for="artistid">Artist: <span class="text-danger">*</span></label>
        <div class="col-sm-6">
            <select class="form-control" id="artistid" name="artistid">
            <?php
            while($row2 = $result2->fetch_assoc()) {
                if($row2["id"] === $artistid){
                    echo "<option value=\"" . $row2["id"] . "\" selected>" . $row2["firstname"] . " " . $row2["lastname"] . "</option>";
                } else {
                    echo "<option value=\"" . $row2["id"] . "\">" . $row2["firstname"] . " " . $row2["lastname"] . "</option>";
                }
                
            }
            ?>
            </select>
        </div>
        <div class="col-sm-2"><span class="error"><span class="text-danger"><?php echo $locationidErr;?></span></div>
    </div>
    <div class="form-group">
        <label class="form-label col-sm-2" for="locationid">Location: <span class="text-danger">*</span></label>
        <div class="col-sm-6">
            <select class="form-control" id="locationid" name="locationid">
            <?php
            while($row1 = $result1->fetch_assoc()) {
                if($row1["id"] === $locationid){
                    echo "<option value=\"" . $row1["id"] . "\" selected>" . $row1["location"] . "</option>";
                } else {
                    echo "<option value=\"" . $row1["id"] . "\">" . $row1["location"] . "</option>";
                }
                
            }
            ?>
            </select>
        </div>
        <div class="col-sm-2"><span class="error"><span class="text-danger"><?php echo $locationidErr;?></span></div>
    </div>
    <div  class="form-group">
        <label class="form-label col-sm-2" for="medium">Medium:</label>
        <div class="col-sm-6"><input class="form-control" id="medium" type="text" name="medium" value="<?php echo $medium;?>"></div>
        <div class="col-sm-2"><span class="error"><span class="text-danger"><?php echo $mediumErr;?></span></div>
    </div>
    <div  class="form-group">
        <label class="form-label col-sm-2" for="summary">Summary: <span class="text-danger">*</span></label>
        <div class="col-sm-6"><textarea class="form-control" id="summary" name="summary" rows="12"><?php echo $summary;?></textarea></div>
        <div class="col-sm-2"><span class="error"><span class="text-danger"><?php echo $summaryErr;?></span></div>
    </div>
    <div  class="form-group">
        <label class="form-label col-sm-2" for="gallery">Gallery: <span class="text-danger">*</span></label>
        <div class="col-sm-6"><input class="form-control" id="gallery" type="text" name="gallery" value="<?php echo $gallery;?>"></div>
        <div class="col-sm-2"><span class="error"><span class="text-danger"><?php echo $galleryErr;?></span></div>
    </div>
    <div  class="form-group">
        <label class="form-label col-sm-2" for="price">Price: <span class="text-danger">*</span></label>
        <div class="col-sm-6"><input class="form-control" id="price" type="text" name="price" value="<?php echo $price;?>"></div>
        <div class="col-sm-2"><span class="error"><span class="text-danger"><?php echo $priceErr;?></span></div>
    </div>
    <div class="form-group">
        <label class="form-label col-sm-2" for="currencyid">Currency: <span class="text-danger">*</span></label>
        <div class="col-sm-6">
            <select class="form-control" id="currencyid" name="currencyid">
            <?php
            while($row3 = $result3->fetch_assoc()) {
                if($row3["id"] === $currencyid){
                    echo "<option value=\"" . $row3["id"] . "\" selected>" . $row3["currency"] . "</option>";
                } else {
                    echo "<option value=\"" . $row3["id"] . "\">" . $row3["currency"] . "</option>";
                }
                
            }
            ?>
            </select>
        </div>
        <div class="col-sm-2"><span class="error"><span class="text-danger"><?php echo $locationidErr;?></span></div>
    </div>
    
    <div class="form-group">
        <div class="col-sm-1" style="padding-top:40px;"><input type="submit" value="Submit" class="btn btn-primary" style="width:100px"></div>
    </div>
    </form>

<script>
//If the Company Type = Tenant, then there must be a Primary Contact
document.addEventListener("DOMContentLoaded", function() {
    document.getElementById("EditCompanyForm").onsubmit = function(event) {
        const companyTypeId = document.getElementById('companytypeid').value;
        const primaryContactId = document.getElementById('primarycontactid').value;
        console.log("Company Type ID:", companyTypeId);
        console.log("Primary Contact ID:", primaryContactId);
        if (companyTypeId === "1" && primaryContactId === "0")
            { alert("A Primary Contact is required for Tenant companies.\n\nIt is needed for certain functionality like generating a Right of Renewal document.");
            event.preventDefault(); // Prevent form submission
            return false;
        }
        return true;
    };
});
</script>

<div class="row">
<?php
}

$con->close();
?>

</div>

<?=template_footer()?>
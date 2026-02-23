<?php

function uuid4() {
    /* 32 random HEX + space for 4 hyphens */
    $out = bin2hex(random_bytes(18));

    $out[8]  = "-";
    $out[13] = "-";
    $out[18] = "-";
    $out[23] = "-";

    /* UUID v4 */
    $out[14] = "4";
    
    /* variant 1 - 10xx */
    $out[19] = ["8", "9", "a", "b"][random_int(0, 3)];

    return $out;
}

$count = 0;

include_once 'config.php';
// Connect to the MySQL database using MySQLi
$con = mysqli_connect(db_host, db_user, db_pass, db_name);

if (!$con) {
    die("Connection failed: " . mysqli_connect_error());
}

$sql1 = "SELECT * FROM transactions_view WHERE transactiontypeid = 1";
$result1 = $con->query($sql1);

if ($result1->num_rows > 0) {
    while($row1 = $result1->fetch_assoc()) {
        $idtransaction = $row1["idtransaction"];
        $sql2 = "SELECT * FROM public_invoice_links WHERE transactionID = $idtransaction";
        $result2 = $con->query($sql2);
        if ($result2->num_rows == 0) {
            $token = uuid4();
    //        $idtransaction = $row1["idtransaction"];
            $stmt = $con->prepare("INSERT INTO public_invoice_links (transactionID, token) VALUES (?, ?)");
            $stmt->bind_param("is", $idtransaction, $token);
            if ($stmt->execute()) {
                echo $idtransaction . " - " . $token . "<br>";
            }
            $count += 1;
        }
    }
}

$total_query = "SELECT COUNT(*) AS total FROM transactions_view WHERE transactiontypeid = 1 and recordOwnerID = 28";
$resultCount = $con->query($total_query);
$rowCount = $resultCount->fetch_assoc();
$total_results = $rowCount['total'];

echo "Total:" . $count . "/" . $total_results . "<br>";
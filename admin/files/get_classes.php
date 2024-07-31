<?php
function log_message($message) {
    $log_file = 'app.log';
    $current_time = date('Y-m-d H:i:s');
    file_put_contents($log_file, "$current_time - $message\n", FILE_APPEND);
}

$info = [];
include "../../database/config.php";

// Check connection
if ($conn->connect_error) {
    log_message("Connection failed: " . $conn->connect_error);
    die("Connection failed: " . $conn->connect_error);
}

$stmt = $conn->prepare("SELECT name FROM classes");
$stmt->execute();
$result = $stmt->get_result();

if (!$result) {
    log_message("Query failed: " . mysqli_error($conn));
    die("Query failed: " . mysqli_error($conn));
}

log_message("Query successful");

if (mysqli_num_rows($result) > 0) {
    // output data of each row
    while($row = mysqli_fetch_assoc($result)) {
        $info[] = $row['name'];
    }
    echo json_encode($info);
} else {
    echo "0 results";
}

$stmt->close();
mysqli_close($conn);
?>

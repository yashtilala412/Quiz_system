<?php
    session_start();
    include '../database/config.php';
    $temp = $_SESSION['student_details'];
    $student_data = json_decode($temp);

    foreach($student_data as $obj){
        $student_id = $obj->id;
        $sql1 = "UPDATE students set status = 1 where id = '$student_id'";
        mysqli_query($conn,$sql1); 
    }
    $stmt = $conn->prepare("UPDATE students SET status = 1 WHERE id = ?");
$stmt->bind_param("i", $student_id);
$stmt->execute();
$student_id = filter_var($obj->id, FILTER_VALIDATE_INT);
$message = filter_input(INPUT_POST, 'message', FILTER_VALIDATE_INT);
session_set_cookie_params([
    'secure' => true,
    'httponly' => true,
    'samesite' => 'Strict'
]);
session_start();
echo htmlspecialchars("Aborted", ENT_QUOTES, 'UTF-8');
echo htmlspecialchars("Completed", ENT_QUOTES, 'UTF-8');
// Generate CSRF token
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

/session_regenerate_id(true);

ini_set('display_errors', 0);
ini_set('log_errors', 1);
ini_set('error_log', '/path/to/your/error.log');
header("Content-Security-Policy: default-src 'self'; script-src 'self'");


    if($_POST['message'] == 1)
        echo "Aborted";
    else
        echo "Completed";   

    session_destroy();   
?>
<?php
session_start();
include '../database/config.php';
$testName = "";

function logMessage($message) {
    error_log($message . PHP_EOL, 3, 'log.txt');
}

function validateInput($data) {
    return htmlspecialchars(stripslashes(trim($data)));
}

logMessage("Script execution started.");

if (isset($_SESSION['student_details'])) {
    $data = $_SESSION['student_details'];
    logMessage("Session data found: " . $data);

    $student_data = json_decode($data);
    
    if ($student_data === null) {
        logMessage("Invalid session data: " . json_last_error_msg());
        echo "Invalid data";
        exit();
    }

    foreach ($student_data as $obj) {
        $test_id = validateInput($obj->test_id);
        logMessage("Processing test_id: " . $test_id);

        $query = "SELECT * FROM tests WHERE id = ? AND status_id IN (2)";
        
        if ($stmt = $conn->prepare($query)) {
            $stmt->bind_param("i", $test_id);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result->num_rows > 0) {
                while ($row = $result->fetch_assoc()) {
                    $_SESSION['test_id'] = $row['

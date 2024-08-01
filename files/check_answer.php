<?php
session_start();

if (!isset($_SESSION['student_details'])) {
    die(json_encode(array("status" => "error", "message" => "Session expired. Please log in again.")));
}

if (!isset($_SERVER['HTTPS']) || $_SERVER['HTTPS'] !== 'on') {
    die(json_encode(array("status" => "error", "message" => "Secure connection required.")));
}

include '../database/config.php';

define("TABLE_QUESTIONS", "Questions");
define("TABLE_SCORE", "score");
define("TABLE_STUDENTS", "students");

function validate_input($input) {
    return htmlspecialchars(stripslashes(trim($input)));
}

function rate_limit_check($student_id) {
    $time_window = 60; // time window in seconds
    $max_requests = 5; // max number of requests allowed in the time window

    $current_time = time();
    $time_limit = $current_time - $time_window;

    if (!isset($_SESSION['rate_limit'][$student_id])) {
        $_SESSION['rate_limit'][$student_id] = array();
    }

    $_SESSION['rate_limit'][$student_id] = array_filter($_SESSION['rate_limit'][$student_id], function($timestamp) use ($time_limit) {
        return $timestamp > $time_limit;
    });

    if (count($_SESSION['rate_limit'][$student_id]) >= $max_requests) {
        return false;
    }

    $_SESSION['rate_limit'][$student_id][] = $current_time;
    return true;
}

$selected_option = validate_input($_POST['selected_option']);
$question_id = validate_input($_POST['question_id']);
$score_earned = validate_input($_POST['score']);
$student_details = json_decode($_SESSION['student_details']);
$student_id;

foreach($student_details as $obj){
    $student_id = $obj->id;
    $test_id = $obj->test_id;
}

if (!rate_limit_check($student_id)) {
    error_log("Rate limit exceeded for student ID: $student_id");
    die(json_encode(array("status" => "error", "message" => "Rate limit exceeded. Please try again later.")));
}

if (!$conn) {
    error_log("Connection failed: " . mysqli_connect_error());
    die(json_encode(array("status" => "error", "message" => "Database connection failed.")));
} else {
    error_log("Connection established successfully.");

    $stmt = $conn->prepare("SELECT id FROM " . TABLE_QUESTIONS . " WHERE id = ? AND correctAns = ? LIMIT 1");
    $stmt->bind_param("ss", $question_id, $selected_option);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        // Increase question correct count
       
        $stmt = $conn->prepare("UPDATE " . TABLE_STUDENTS . " SET score = score + ? WHERE id = ?");
        $stmt->bind_param("is", $score_earned, $student_id);
        if ($stmt->execute()) {
            echo json_encode(array("status" => "success", "message" => "Score updated successfully."));
            error_log("Score updated successfully for student ID: $student_id");
        } else {
            echo json_encode(array("status" => "error", "message" => "Failed to update score."));
            error_log("Error updating score: " . $stmt->error);
        }
    } else {
        // Increase question wrong count
        $stmt = $conn->prepare("UPDATE " . TABLE_SCORE . " SET wrong_count = wrong_count + 1 WHERE question_id = ?");
        $stmt->bind_param("s", $question_id);
        if ($stmt->execute()) {
            error_log("Wrong count updated for question ID: $question_id");
        } else {
            error_log("Error updating wrong count: " . $stmt->error);
        }
        echo json_encode(array("status" => "error", "message" => "Wrong answer."));
    }
    
    $stmt->close();
}

mysqli_close($conn);
error_log("Connection closed.");
?>

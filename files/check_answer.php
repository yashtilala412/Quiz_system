<?php
session_start();

include '../database/config.php';

function validate_input($input) {
    return htmlspecialchars(stripslashes(trim($input)));
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

if (!$conn) {
    error_log("Connection failed: " . mysqli_connect_error());
    die("Connection failed: " . mysqli_connect_error());
} else {
    error_log("Connection established successfully.");

    $stmt = $conn->prepare("SELECT id FROM Questions WHERE id = ? AND correctAns = ? LIMIT 1");
    $stmt->bind_param("ss", $question_id, $selected_option);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        // Increase question correct count
        $stmt = $conn->prepare("UPDATE score SET correct_count = correct_count + 1 WHERE question_id = ?");
        $stmt->bind_param("s", $question_id);
        if ($stmt->execute()) {
            error_log("Correct count updated for question ID: $question_id");
        } else {
            error_log("Error updating correct count: " . $stmt->error);
        }

        $stmt = $conn->prepare("UPDATE students SET score = score + ? WHERE id = ?");
        $stmt->bind_param("is", $score_earned, $student_id);
        if ($stmt->execute()) {
            echo "SCORE_UPDATED_SUCCESSFULLY";
            error_log("Score updated successfully for student ID: $student_id");
        } else {
            echo "SCORE_UPDATE_FAILURE";
            error_log("Error updating score: " . $stmt->error);
        }
    } else {
        // Increase question wrong count
        $stmt = $conn->prepare("UPDATE score SET wrong_count = wrong_count + 1 WHERE question_id = ?");
        $stmt->bind_param("s", $question_id);
        if ($stmt->execute()) {
            error_log("Wrong count updated for question ID: $question_id");
        } else {
            error_log("Error updating wrong count: " . $stmt->error);
        }
        echo "WRONG_ANSWER";
    }
    
    $stmt->close();
}

mysqli_close($conn);
error_log("Connection closed.");
?>

<?php
session_start();

include '../database/config.php';
$selected_option = $_POST['selected_option'];
$question_id = $_POST['question_id'];
$score_earned = $_POST['score'];
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
    
    $result = mysqli_query($conn, "SELECT id FROM Questions WHERE id = '".$question_id."' AND correctAns = '".$selected_option."' LIMIT 1 ");        
    if (mysqli_num_rows($result) > 0){
        // Increase question correct count
        $sql = "UPDATE score set correct_count = correct_count + 1 where question_id = '$question_id'";
        if (mysqli_query($conn, $sql)) {
            error_log("Correct count updated for question ID: $question_id");
        } else {
            error_log("Error updating correct count: " . mysqli_error($conn));
        }

        if (mysqli_query($conn, "UPDATE students set score = score + '".$score_earned."' where id = '".$student_id."' ")){
            echo "SCORE_UPDATED_SUCCESSFULLY";
            error_log("Score updated successfully for student ID: $student_id");
        } else {
            echo "SCORE_UPDATE_FAILURE";
            error_log("Error updating score: " . mysqli_error($conn));
        }  
    } else {
        // Increase question wrong count
        $sql = "UPDATE score set wrong_count = wrong_count + 1 where question_id = '$question_id'";
        if (mysqli_query($conn, $sql)) {
            error_log("Wrong count updated for question ID: $question_id");
        } else {
            error_log("Error updating wrong count: " . mysqli_error($conn));
        }
        echo "WRONG_ANSWER";
    }
}

mysqli_close($conn);
error_log("Connection closed.");
?>

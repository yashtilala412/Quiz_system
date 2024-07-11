<?php
include "../../database/config.php";

$action = $_POST['action'];
$question_id = $_POST['question_id'];
$test_id = $_POST['test_id'];

if ($action === 'delete') {
    // Delete query
    $sql = "DELETE FROM question_test_mapping WHERE question_id = ? AND test_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $question_id, $test_id);

    if ($stmt->execute()) {
        echo "Record deleted successfully";
    } else {
        echo "Error deleting record: " . $conn->error;
    }

    $stmt->close();
} elseif ($action === 'edit') {
    // Example fields to update (assuming you want to update these fields)
    $new_test_id = $_POST['new_test_id'];
    $new_question_id = $_POST['new_question_id'];

    // Update query
    $sql = "UPDATE question_test_mapping SET question_id = ?, test_id = ? WHERE question_id = ? AND test_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("iiii", $new_question_id, $new_test_id, $question_id, $test_id);

    if ($stmt->execute()) {
        echo "Record updated successfully";
    } else {
        echo "Error updating record: " . $conn->error;
    }

    $stmt->close();
} else {
    echo "Invalid action";
}

$conn->close();
?>

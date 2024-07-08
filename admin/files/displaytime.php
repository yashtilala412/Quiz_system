// in test_view.php or equivalent file
<?php
// Fetch test details
$test_id = $_GET['test_id'];
$sql = "SELECT * FROM tests WHERE id = '$test_id'";
$result = mysqli_query($conn, $sql);
$test = mysqli_fetch_assoc($result);

// Display test details
echo "<h1>{$test['name']}</h1>";
echo "<p>Date: {$test['date']}</p>";
echo "<p>Subject: {$test['subject']}</p>";
echo "<p>Total Questions: {$test['total_questions']}</p>";
echo "<p>Time Limit: {$test['time_limit']} minutes</p>";
?>

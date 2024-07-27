$sql_question_bank_count = "SELECT COUNT(*) AS total FROM question_bank";
$result_question_bank_count = mysqli_query($conn, $sql_question_bank_count);
$question_bank_count = mysqli_fetch_assoc($result_question_bank_count);
echo "<p>Total Questions in Bank: {$question_bank_count['total']}</p>";

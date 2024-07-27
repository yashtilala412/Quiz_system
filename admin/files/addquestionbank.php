$limit = 10; // Number of questions per page
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $limit;
$sql_questions .= " LIMIT $limit OFFSET $offset";

// Fetch questions
$result_questions = mysqli_query($conn, $sql_questions);

// Display pagination controls
$total_questions = $question_bank_count['total'];
$total_pages = ceil($total_questions / $limit);
echo "<div class='pagination'>";
for ($i = 1; $i <= $total_pages; $i++) {
    echo "<a href='test_view.php?page=$i'>$i</a> ";
}
echo "</div>";
echo "<script>
function showDetails(questionId) {
    // Fetch and display question details in a modal or tooltip
}
</script>";
echo "<a href='edit_question.php?question_id={$question['id']}' class='btn btn-warning'>Edit</a>";
echo "<a href='delete_question.php?question_id={$question['id']}' class='btn btn-danger' onclick='return confirm(\"Are you sure?\")'>Delete</a>";

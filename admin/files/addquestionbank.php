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

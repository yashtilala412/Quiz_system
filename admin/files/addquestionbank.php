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
echo "<h3>Add New Question:</h3>";
echo "<form action='add_question.php' method='post'>
    <label for='question_text'>Question:</label>
    <input type='text' name='question_text' id='question_text'>
    <label for='category'>Category:</label>
    <input type='text' name='category' id='category'>
    <label for='difficulty'>Difficulty:</label>
    <select name='difficulty' id='difficulty'>
        <option value='Easy'>Easy</option>
        <option value='Medium'>Medium</option>
        <option value='Hard'>Hard</option>
    </select>
    <input type='submit' value='Add Question'>
</form>";
echo "<h3>Import Questions:</h3>";
echo "<form action='import_questions.php' method='post' enctype='multipart/form-data'>
    <input type='file' name='csv_file'>
    <input type='submit' value='Import'>
</form>";
echo "<a href='export_questions.php' class='btn btn-secondary'>Export to CSV</a>";
$sql_stats = "SELECT category, difficulty, COUNT(*) AS count FROM question_bank GROUP BY category, difficulty";
$result_stats = mysqli_query($conn, $sql_stats);

echo "<h3>Question Statistics:</h3><ul>";
while ($stat = mysqli_fetch_assoc($result_stats)) {
    echo "<li>{$stat['category']} - {$stat['difficulty']}: {$stat['count']} questions</li>";
}
echo "</ul>";$sql_questions = "SELECT * FROM question_bank ORDER BY RAND()";
$sql_popular = "SELECT * FROM question_bank WHERE is_popular = 1";
$result_popular = mysqli_query($conn, $sql_popular);

echo "<h3>Popular Questions:</h3><ul>";
while ($question = mysqli_fetch_assoc($result_popular)) {
    echo "<li>{$question['question_text']}</li>";
}
echo "</ul>";


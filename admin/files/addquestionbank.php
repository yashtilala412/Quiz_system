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

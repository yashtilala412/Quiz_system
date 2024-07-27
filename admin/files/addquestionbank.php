<?php
// Fetch test details
$test_id = $_GET['test_id'];
$sql = "SELECT * FROM tests WHERE id = '$test_id'";
$result = mysqli_query($conn, $sql);
$test = mysqli_fetch_assoc($result);

// Fetch test creator details
$creator_id = $test['creator_id'];
$sql_creator = "SELECT * FROM users WHERE id = '$creator_id'";
$result_creator = mysqli_query($conn, $sql_creator);
$creator = mysqli_fetch_assoc($result_creator);

// Display test details
echo "<h1>{$test['name']}</h1>";
echo "<p>Date: {$test['date']}</p>";
echo "<p>Subject: {$test['subject']}</p>";
echo "<p>Total Questions: {$test['total_questions']}</p>";
echo "<p>Time Limit: {$test['time_limit']} minutes</p>";
echo "<p>Description: {$test['description']}</p>";
echo "<p>Creator: {$creator['name']}</p>";
echo "<p><a href='{$test['resource_link']}' target='_blank'>Related Resources</a></p>";

if ($test['time_limit'] > 0) {
    echo "<div id='countdown'></div>";
    echo "<script>
    var timeLimit = {$test['time_limit'] * 60}; // Convert minutes to seconds
    var countdown = document.getElementById('countdown');
    function updateTimer() {
        var minutes = Math.floor(timeLimit / 60);
        var seconds = timeLimit % 60;
        countdown.textContent = minutes + 'm ' + seconds + 's';
        if (timeLimit > 0) {
            timeLimit--;
            setTimeout(updateTimer, 1000);
        }
    }
    updateTimer();
    </script>";
}

echo "<a href='download_test.php?test_id={$test_id}' class='btn btn-primary'>Download Test</a>";
echo "<a href='start_test.php?test_id={$test_id}' class='btn btn-success'>Start Test</a>";

$sql_questions = "SELECT * FROM questions WHERE test_id = '$test_id'";
$result_questions = mysqli_query($conn, $sql_questions);

echo "<h2>Questions:</h2><ul>";
while ($question = mysqli_fetch_assoc($result_questions)) {
    echo "<li>{$question['question_text']}</li>";
}
echo "</ul>";

echo "<a href='view_results.php?test_id={$test_id}' class='btn btn-info'>View Results</a>";

echo "<h3>Feedback:</h3>";
echo "<form action='submit_feedback.php' method='post'>
    <input type='hidden' name='test_id' value='{$test_id}'>
    <textarea name='feedback' rows='4' cols='50' placeholder='Your feedback...'></textarea>
    <br>
    <input type='submit' value='Submit Feedback'>
</form>";

// Question Bank Features
echo "<h3>Question Bank:</h3>";

// Total Number of Questions
$sql_question_bank_count = "SELECT COUNT(*) AS total FROM question_bank";
$result_question_bank_count = mysqli_query($conn, $sql_question_bank_count);
$question_bank_count = mysqli_fetch_assoc($result_question_bank_count);
echo "<p>Total Questions in Bank: {$question_bank_count['total']}</p>";

// Filter Questions by Category
$categories = ['Math', 'Science', 'History']; // Example categories
echo "<form method='GET' action='test_view.php'>
    <label for='category'>Filter by Category:</label>
    <select name='category' id='category'>
        <option value=''>All</option>";
foreach ($categories as $category) {
    echo "<option value='{$category}'>{$category}</option>";
}
echo "</select>
    <input type='submit' value='Filter'>
</form>";

$category_filter = isset($_GET['category']) ? $_GET['category'] : '';
$sql_questions = "SELECT * FROM question_bank" . ($category_filter ? " WHERE category = '$category_filter'" : "");

// Search Questions by Keyword
echo "<form method='GET' action='test_view.php'>
    <label for='keyword'>Search Questions:</label>
    <input type='text' name='keyword' id='keyword'>
    <input type='submit' value='Search'>
</form>";

$keyword_filter = isset($_GET['keyword']) ? $_GET['keyword'] : '';
$sql_questions .= ($keyword_filter ? " WHERE question_text LIKE '%$keyword_filter%'" : "");

// Sort Questions by Difficulty
$difficulties = ['Easy', 'Medium', 'Hard'];
echo "<form method='GET' action='test_view.php'>
    <label for='difficulty'>Sort by Difficulty:</label>
    <select name='difficulty' id='difficulty'>
        <option value=''>All</option>";
foreach ($difficulties as $difficulty) {
    echo "<option value='{$difficulty}'>{$difficulty}</option>";
}
echo "</select>
    <input type='submit' value='Sort'>
</form>";

$difficulty_filter = isset($_GET['difficulty']) ? $_GET['difficulty'] : '';
$sql_questions .= ($difficulty_filter ? " AND difficulty = '$difficulty_filter'" : "");

// Pagination
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

// Display Questions
while ($question = mysqli_fetch_assoc($result_questions)) {
    echo "<div class='question'>";
    echo "<p>{$question['question_text']}</p>";
    echo "<a href='edit_question.php?question_id={$question['id']}' class='btn btn-warning'>Edit</a>";
    echo "<a href='delete_question.php?question_id={$question['id']}' class='btn btn-danger' onclick='return confirm(\"Are you sure?\")'>Delete</a>";
    echo "</div>";
}

// Add New Question
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

// Import Questions from CSV
echo "<h3>Import Questions:</h3>";
echo "<form action='import_questions.php' method='post' enctype='multipart/form-data'>
    <input type='file' name='csv_file'>
    <input type='submit' value='Import'>
</form>";

// Export Questions to CSV
echo "<a href='export_questions.php' class='btn btn-secondary'>Export to CSV</a>";

// View Question Statistics
$sql_stats = "SELECT category, difficulty, COUNT(*) AS count FROM question_bank GROUP BY category, difficulty";
$result_stats = mysqli_query($conn, $sql_stats);

echo "<h3>Question Statistics:</h3><ul>";
while ($stat = mysqli_fetch_assoc($result_stats)) {
    echo "<li>{$stat['category']} - {$stat['difficulty']}: {$stat['count']} questions</li>";
}
echo "</ul>";

// Randomize Question Order
$sql_questions = "SELECT * FROM question_bank ORDER BY RAND()";

// Highlight Popular Questions
$sql_popular = "SELECT * FROM question_bank WHERE is_popular = 1";
$result_popular = mysqli_query($conn, $sql_popular);

echo "<h3>Popular Questions:</h3><ul>";
while ($question = mysqli_fetch_assoc($result_popular)) {
    echo "<li>{$question['question_text']}</li>";
}
echo "</ul>";

// Add Tags to Questions
$tags = ['Math', 'Algebra', 'Geometry', 'Physics', 'Biology']; // Example tags
echo "<form method='GET' action='test_view.php'>
    <label for='tag'>Filter by Tag:</label>
    <select name='tag' id='tag'>
        <option value=''>All</option>";
foreach ($tags as $tag) {
    echo "<option value='{$tag}'>{$tag}</option>";
}
echo "</select>
    <input type='submit' value='Filter'>
</form>";

$tag_filter = isset($_GET['tag']) ? $_GET['tag'] : '';
$sql_questions .= ($tag_filter ? " AND tags LIKE '%$tag_filter%'" : "");

?>

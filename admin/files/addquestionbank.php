echo "<form method='GET' action='test_view.php'>
    <label for='keyword'>Search Questions:</label>
    <input type='text' name='keyword' id='keyword'>
    <input type='submit' value='Search'>
</form>";

$keyword_filter = isset($_GET['keyword']) ? $_GET['keyword'] : '';
$sql_questions = "SELECT * FROM question_bank" . ($keyword_filter ? " WHERE question_text LIKE '%$keyword_filter%'" : "");

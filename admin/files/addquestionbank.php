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

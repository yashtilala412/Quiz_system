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
$sql_questions = "SELECT * FROM question_bank" . ($difficulty_filter ? " WHERE difficulty = '$difficulty_filter'" : "");

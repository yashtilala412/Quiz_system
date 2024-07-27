// Fetch test creator details
$creator_id = $test['creator_id'];
$sql_creator = "SELECT * FROM users WHERE id = '$creator_id'";
$result_creator = mysqli_query($conn, $sql_creator);
$creator = mysqli_fetch_assoc($result_creator);

// Display creator details
echo "<p>Creator: {$creator['name']}</p>";
echo "<p>Description: {$test['description']}</p>";
echo "<p><a href='{$test['resource_link']}' target='_blank'>Related Resources</a></p>";
echo "<a href='download_test.php?test_id={$test_id}' class='btn btn-primary'>Download Test</a>";
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
echo "<p>Instructions: {$test['instructions']}</p>";
echo "<a href='start_test.php?test_id={$test_id}' class='btn btn-success'>Start Test</a>";
$sql_questions = "SELECT * FROM questions WHERE test_id = '$test_id'";
$result_questions = mysqli_query($conn, $sql_questions);

echo "<h2>Questions:</h2><ul>";
while ($question = mysqli_fetch_assoc($result_questions)) {
    echo "<li>{$question['question_text']}</li>";
}
echo "</ul>";

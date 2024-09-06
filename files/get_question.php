<?php
session_start();
include '../database/config.php';

if(!isset($_SESSION['user_id'])){
    header("Location: login.php");
}

if(!isset($_SESSION['test_id'])){
    header("Location: ../index.php");
}

$test_id = $_SESSION['test_id'];

// Check connection
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
} else {
    if (!isset($_SESSION['question_IDS_fetched'])) {
        $result = mysqli_query($conn, "SELECT question_id FROM question_test_mapping WHERE test_id = '" . $test_id . "' ");
        if (mysqli_num_rows($result) > 0) {
            while ($row = mysqli_fetch_assoc($result)) {
                $question_ids[] = $row;
            }
            shuffle($question_ids); // Shuffle the questions
            $_SESSION['question_IDS_fetched'] = $question_ids;
            $_SESSION['question_counter'] = 0;
            getQuestion($conn, true);
        }
    } else {
        if ($_SESSION['question_counter'] >= sizeof($_SESSION['question_IDS_fetched'])) {
            echo 'QUESTION_SET_FINISHED';
            exit();
        } else {
            getQuestion($conn, false);
        }
    }
}

function getQuestion($conn, $isFirst)
{
    if ($isFirst == true) {
        $question = mysqli_query($conn, "SELECT id, title, optionA, optionB, optionC, optionD, score FROM Questions WHERE id = '" . $_SESSION['question_IDS_fetched'][0]['question_id'] . "' ");
        $_SESSION['question_counter']++;
        fetchAndReturnQuestion($question);
    } else {
        $question = mysqli_query($conn, "SELECT id, title, optionA, optionB, optionC, optionD, score FROM Questions WHERE id = '" . $_SESSION['question_IDS_fetched'][$_SESSION['question_counter']]['question_id'] . "' ");
        $_SESSION['question_counter']++;
        fetchAndReturnQuestion($question);
    }
}

function fetchAndReturnQuestion($question, $limit = 1)
{
    $log_file = 'question_log.txt'; // Log file path
    if (mysqli_num_rows($question) > 0) {
        $count = 0;
        $fetched_questions = [];
        while ($row = mysqli_fetch_assoc($question) && $count < $limit) {
            $fetched_question = array_map('htmlspecialchars', $row); // Sanitize data
            $fetched_question['timestamp'] = date('Y-m-d H:i:s'); // Add timestamp
            $fetched_questions[] = $fetched_question;
            // Log the fetched question
            file_put_contents($log_file, json_encode($fetched_question) . PHP_EOL, FILE_APPEND);
            $count++;
        }
        echo json_encode($fetched_questions);
    } else {
        echo json_encode(["error" => "No questions found"]);
    }
}



mysqli_close($conn);
?>

<script>
var timer = setTimeout(function(){ 
    alert("Time up!"); 
    window.location.href = "results.php"; 
}, 60000); // 60 seconds for example

function saveAnswer() {
    // Save answer logic
}

function reviewAnswers() {
    // Review answers logic
}

function skipQuestion() {
    // Skip question logic
}
</script>

<progress id="progressBar" value="<?php echo $_SESSION['question_counter']; ?>" max="<?php echo sizeof($_SESSION['question_IDS_fetched']); ?>"></progress>
<button onclick="saveAnswer()">Save Answer</button>
<button onclick="reviewAnswers()">Review Answers</button>
<button onclick="skipQuestion()">Skip Question</button>
<div id="feedback"></div>
<div id="score"></div>
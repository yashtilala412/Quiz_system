<?php
http_response_code(200);

function log_message($message) {
    $log_file = 'app.log';
    $current_time = date('Y-m-d H:i:s');
    file_put_contents($log_file, "$current_time - $message\n", FILE_APPEND);
}

$info = [];
include "../../database/config.php";

$cache_file = 'cache.json';
$cache_time = 3600; // 1 hour

if (file_exists($cache_file) && (time() - filemtime($cache_file) < $cache_time) && !isset($_GET['search'])) {
    // Cache file is less than one hour old and no search query. Serve it up and exit.
    $info = json_decode(file_get_contents($cache_file), true);
} else {
    // Check connection
    if ($conn->connect_error) {
        log_message("Connection failed: " . $conn->connect_error);
        http_response_code(500);
        die("Connection failed: " . $conn->connect_error);
    }

    $limit = 10; // Number of entries to show in a page.
    $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
    $start = ($page - 1) * $limit;
    $search = isset($_GET['search']) ? "%" . $_GET['search'] . "%" : "%";

    $stmt = $conn->prepare("SELECT name FROM classes WHERE name LIKE ? LIMIT ?, ?");
    $stmt->bind_param("sii", $search, $start, $limit);
    $stmt->execute();
    $result = $stmt->get_result();

    if (!$result) {
        log_message("Query failed: " . mysqli_error($conn));
        http_response_code(500);
        die("Query failed: " . mysqli_error($conn));
    }

    log_message("Query successful");

    if (mysqli_num_rows($result) > 0) {
        // output data of each row
        while($row = mysqli_fetch_assoc($result)) {
            $info[] = $row['name'];
        }
        if (!isset($_GET['search'])) {
            file_put_contents($cache_file, json_encode($info));
        }
    } else {
        $info[] = "0 results";
    }

    $stmt->close();
    mysqli_close($conn);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Class Names</title>
</head>
<body>
    <h1>Class Names</h1>
    <form method="GET">
        <input type="text" name="search" placeholder="Search classes">
        <input type="submit" value="Search">
    </form>
    <ul>
        <?php foreach($info as $class): ?>
            <li><?php echo htmlspecialchars($class); ?></li>
        <?php endforeach; ?>
    </ul>
    <div>
        <?php if ($page > 1): ?>
            <a href="?page=<?php echo $page - 1; ?>">Previous</a>
        <?php endif; ?>
        <a href="?page=<?php echo $page + 1; ?>">Next</a>
    </div>
</body>
</html>

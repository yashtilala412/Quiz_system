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

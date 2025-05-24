<?php

$host = 'localhost';
$username = 'root';
$password = '';
$database = 'quiz_master';

// Try to connect to MySQL
$conn = mysqli_connect($host, $username, $password);

// Check connection
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

echo "<h1>QuizMaster Installation</h1>";

// Create database if it doesn't exist
$sql = "CREATE DATABASE IF NOT EXISTS $database";
if (mysqli_query($conn, $sql)) {
    echo "<p>Database created successfully or already exists.</p>";
} else {
    die("<p>Error creating database: " . mysqli_error($conn) . "</p>");
}

// Select the database
mysqli_select_db($conn, $database);

// Set charset to utf8mb4
mysqli_set_charset($conn, "utf8mb4");

// Read the SQL file
$sql_file = file_get_contents('db/setup.sql');

// Split SQL file into individual statements
$queries = explode(';', $sql_file);

// Execute each query
$success = true;
foreach ($queries as $query) {
    $query = trim($query);
    if (empty($query)) continue;
    
    if (!mysqli_query($conn, $query)) {
        echo "<p>Error executing query: " . mysqli_error($conn) . "</p>";
        $success = false;
    }
}

if ($success) {
    echo "<p>Database setup completed successfully.</p>";
    echo "<p>Admin account created:</p>";
    echo "<ul>";
    echo "<li>Email: admin@quizmaster.com</li>";
    echo "<li>Password: admin123</li>";
    echo "</ul>";
    echo "<p><a href='index.php'>Go to QuizMaster</a></p>";
} else {
    echo "<p>Some errors occurred during database setup.</p>";
}

// Close connection
mysqli_close($conn);
?>
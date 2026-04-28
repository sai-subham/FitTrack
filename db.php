<?php
session_start();

// Use Environment Variables for Vercel deployment, fallback to local/InfinityFree if not set
$host = getenv('DB_HOST') ?: "sql201.infinityfree.com";
$user = getenv('DB_USER') ?: "if0_41733956";
$pass = getenv('DB_PASS') ?: "7LQsxJ5fbFwzy1T";
$db   = getenv('DB_NAME') ?: "if0_41733956_fitness_tracker";

$conn = mysqli_connect($host, $user, $pass, $db);

if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}
?>
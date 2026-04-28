<?php
session_start();

$host = "sql201.infinityfree.com";
$user = "if0_41733956";
$pass = "7LQsxJ5fbFwzy1T";
$db   = "if0_41733956_fitness_tracker";

$conn = mysqli_connect($host, $user, $pass, $db);

if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}
?>
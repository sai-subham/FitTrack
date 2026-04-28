<?php
session_start();

$host = "sql113.infinityfree.com";
$user = "if0_38802498";
$pass = "saisubham1234";
$db   = "if0_38802498_fitness_tracker";

$conn = mysqli_connect($host, $user, $pass, $db);

if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}
?>
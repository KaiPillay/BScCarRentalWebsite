<?php
$host = 'localhost';
$db = 'car_rental';
$port = 3306;
$user = 'root';
$pass = 'P455word';

$conn = new mysqli($host, $user, $pass, $db);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>
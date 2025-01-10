<?php
$host = 'localhost';
$db = 'car_rental';
$user = 'admin';
$pass = 'P455word';
$conn = new mysqli($host, $user, $pass, $db);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
    echo "Database failed to connect!";
} else {
    echo "Database connected successfully!";
}

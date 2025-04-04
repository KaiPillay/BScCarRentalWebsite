<?php
session_start();

// Redirect if user is not logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: booking_login.php");
    exit();
}

require 'db_connect.php';

// Capture form data
$user_id = $_SESSION['user_id'];
$vehicle_id = $_POST['vehicle_id'];
$pickup_date = $_POST['pickup_date'];
$return_date = $_POST['return_date'];
$pickup_location = $_POST['pickup_location'];
$return_location = $_POST['return_location'];

// Fetch the price per day for the selected vehicle
$stmt = $conn->prepare("SELECT price_per_day FROM vehicles WHERE vehicle_id = ?");
$stmt->bind_param("i", $vehicle_id);
$stmt->execute();
$result = $stmt->get_result();
$vehicle = $result->fetch_assoc();
$price_per_day = $vehicle['price_per_day'];
$stmt->close();

// Calculate the total price based on the number of days
$start_date_timestamp = strtotime($pickup_date);
$end_date_timestamp = strtotime($return_date);
$days_diff = ($end_date_timestamp - $start_date_timestamp) / (60 * 60 * 24); // Number of days
$total_price = $days_diff * $price_per_day;

// Insert the booking into the database
$stmt = $conn->prepare("INSERT INTO bookings (user_id, vehicle_id, pickup_date, return_date, pickup_location, return_location, total_price) VALUES (?, ?, ?, ?, ?, ?, ?)");
$stmt->bind_param("iissssd", $user_id, $vehicle_id, $pickup_date, $return_date, $pickup_location, $return_location, $total_price);

if ($stmt->execute()) {
    echo "Booking successful!";
} else {
    echo "Error: " . $stmt->error;
}

$stmt->close();
$conn->close();
?>
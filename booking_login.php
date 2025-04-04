<?php
require 'db_connect.php';

session_start();

// Capture URL parameters for booking (location, start, stop, and car_id)
$location = isset($_GET['location']) ? $_GET['location'] : '';
$start = isset($_GET['start']) ? $_GET['start'] : '';
$end = isset($_GET['stop']) ? $_GET['stop'] : '';
$car_id = isset($_GET['car_id']) ? $_GET['car_id'] : '';

// Check if user is already logged in, if so, redirect to booking page with query parameters
if (isset($_SESSION['user_id'])) {
    header("Location: book.php?location=" . urlencode($location) . "&start=" . urlencode($start) . "&stop=" . urlencode($end) . "&car_id=" . urlencode($car_id));
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $conn->real_escape_string($_POST['username']);
    $password = $_POST['password'];

    $stmt = $conn->prepare("SELECT user_id, password_hash, role FROM users WHERE username = ? OR email = ?");
    $stmt->bind_param("ss", $username, $username);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();

        // Use the correct column name in password verification
        if (password_verify($password, $user['password_hash'])) {
            $_SESSION['user_id'] = $user['user_id']; // Correct session key
            $_SESSION['role'] = $user['role'];

        // Redirect to booking page after successful login with query parameters
        header("Location: book.php?location=" . urlencode($location) . "&start=" . urlencode($start) . "&stop=" . urlencode($end) . "&vehicle_id=" . urlencode($car_id));
        exit();
        } else {
            $error = "Invalid password.";
        }
    } else {
        $error = "User not found.";
    }
    $stmt->close();
    $conn->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Booking Login</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>

<div class="container">
    <h1>Login to Book a Car</h1>

    <!-- Display error message if login fails -->
    <?php if (isset($error)): ?>
        <div class="alert"><?php echo $error; ?></div>
    <?php endif; ?>

    <!-- Login Form -->
    <form method="POST" action="booking_login.php">
        <label for="username">Username or Email</label>
        <input type="text" name="username" required>

        <label for="password">Password</label>
        <input type="password" name="password" required>

        <input type="submit" value="Login">
    </form>

    <p>Don't have an account? <a href="register.php">Register here</a></p>
</div>

</body>
</html>
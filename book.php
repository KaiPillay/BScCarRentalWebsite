<?php
session_start();

// Debug: Print all query parameters
echo "<pre>";
print_r($_GET);
echo "</pre>";

// Redirect if user is not logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: booking_login.php");
    exit();
}

// Capture query parameters
$location = isset($_GET['location']) ? $_GET['location'] : '';
$start = isset($_GET['start']) ? $_GET['start'] : '';
$end = isset($_GET['stop']) ? $_GET['stop'] : '';
$vehicle_id = isset($_GET['car_id']) ? $_GET['car_id'] : '';

// Debug: Print captured parameters
echo "Location: $location<br>";
echo "Start: $start<br>";
echo "End: $end<br>";
echo "Vehicle ID: $vehicle_id<br>";

// Fetch user details from the database
require 'db_connect.php';
$user_id = $_SESSION['user_id'];
$stmt = $conn->prepare("SELECT username, email FROM users WHERE user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
$stmt->close();

// Fetch available vehicles
$vehicles = [];
try {
    $stmt = $conn->prepare("SELECT vehicle_id, make, model FROM vehicles WHERE status = 'available'");
    $stmt->execute();
    $result = $stmt->get_result();
    $vehicles = $result->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
} catch (mysqli_sql_exception $e) {
    echo "<div class='alert'>Error fetching vehicles: " . $e->getMessage() . "</div>";
}
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Book a Car</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>

<div class="container">
    <h1>Book Your Car</h1>

    <!-- Booking Form -->
    <form method="POST" action="submit_booking.php">
        <!-- Autofill user information -->
        <label for="username">Username</label>
        <input type="text" name="username" value="<?php echo htmlspecialchars($user['username']); ?>" readonly>

        <label for="email">Email</label>
        <input type="email" name="email" value="<?php echo htmlspecialchars($user['email']); ?>" readonly>

        <!-- Autofill vehicle selection -->
        <label for="vehicle_id">Vehicle</label>
        <select name="vehicle_id" required>
            <?php if (empty($vehicles)): ?>
                <option value="">No vehicles available</option>
            <?php else: ?>
                <?php foreach ($vehicles as $vehicle): ?>
                    <option value="<?php echo $vehicle['vehicle_id']; ?>" <?php echo ($vehicle['vehicle_id'] == $vehicle_id) ? 'selected' : ''; ?>>
                        <?php echo $vehicle['make'] . ' ' . $vehicle['model']; ?>
                    </option>
                <?php endforeach; ?>
            <?php endif; ?>
        </select>

        <!-- Autofill location and dates -->
        <label for="pickup_date">Pickup Date</label>
        <input type="date" name="pickup_date" value="<?php echo htmlspecialchars($start); ?>" required>

        <label for="return_date">Return Date</label>
        <input type="date" name="return_date" value="<?php echo htmlspecialchars($end); ?>" required>

        <label for="pickup_location">Pickup Location</label>
        <input type="text" name="pickup_location" value="<?php echo htmlspecialchars($location); ?>" required>

        <label for="return_location">Return Location</label>
        <input type="text" name="return_location" required>

        <input type="submit" value="Submit Booking">
    </form>
</div>

</body>
</html>
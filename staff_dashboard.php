<?php
session_start();

// Check if user is logged in and has staff role
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'staff') {
    header("Location: login.php");
    exit();
}

// Connection to the database
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "car_rental";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Fetch users from the database
$users_query = "SELECT user_id, username FROM users";
$users_result = $conn->query($users_query);

// Fetch vehicles from the database
$vehicles_query = "SELECT vehicle_id, make, model, year, price_per_day, status, created_at FROM vehicles";
$vehicles_result = $conn->query($vehicles_query);

if (!$vehicles_result) {
    die("Error fetching vehicles: " . $conn->error);
}

// Handle manual booking creation
if (isset($_POST['add_booking'])) {
    $user_id = $_POST['user_id'];
    $vehicle_id = $_POST['vehicle_id'];
    $start_date = $_POST['start_date'];
    $end_date = $_POST['end_date'];
    $status = $_POST['status'];

    // Fetch the price per day for the selected vehicle
    $vehicle_query = "SELECT price_per_day FROM vehicles WHERE vehicle_id = '$vehicle_id'";
    $vehicle_result = $conn->query($vehicle_query);
    $vehicle_data = $vehicle_result->fetch_assoc();
    $price_per_day = $vehicle_data['price_per_day'];

    // Calculate the total price based on the number of days
    $start_date_timestamp = strtotime($start_date);
    $end_date_timestamp = strtotime($end_date);
    $days_diff = ($end_date_timestamp - $start_date_timestamp) / (60 * 60 * 24); // Number of days
    $total_price = $days_diff * $price_per_day;

    // Insert the booking into the database
    $add_booking_query = "INSERT INTO bookings (user_id, vehicle_id, start_date, end_date, status, total_price)
                          VALUES ('$user_id', '$vehicle_id', '$start_date', '$end_date', '$status', '$total_price')";
    if ($conn->query($add_booking_query) === TRUE) {
        echo "<script>alert('Booking added successfully!');</script>";
    } else {
        echo "Error: " . $conn->error;
    }
}

// Fetch bookings from the database
$bookings_query = "SELECT b.booking_id, b.start_date, b.end_date, b.status, v.make, v.model, u.username
                   FROM bookings b
                   JOIN vehicles v ON b.vehicle_id = v.vehicle_id
                   JOIN users u ON b.user_id = u.user_id";
$bookings_result = $conn->query($bookings_query);

// Handle booking details view
if (isset($_GET['booking_id'])) {
    $booking_id = $_GET['booking_id'];
    $booking_details_query = "SELECT b.booking_id, b.start_date, b.end_date, b.status, v.make, v.model, u.username, u.email
                              FROM bookings b
                              JOIN vehicles v ON b.vehicle_id = v.vehicle_id
                              JOIN users u ON b.user_id = u.user_id
                              WHERE b.booking_id = $booking_id";
    $booking_details_result = $conn->query($booking_details_query);
    $booking_details = $booking_details_result->fetch_assoc();
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Staff Dashboard</title>
    <link rel="stylesheet" href="styles.css"> <!-- Add your CSS file here -->
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            margin: 0;
            padding: 0;
        }
        .navbar {
            background-color: #333;
            color: white;
            padding: 15px;
            text-align: center;
        }
        .container {
            max-width: 900px;
            margin: 20px auto;
            background: white;
            padding: 20px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            text-align: center;
        }
        .btn {
            display: inline-block;
            padding: 10px 15px;
            color: white;
            background: #007bff;
            border: none;
            text-decoration: none;
            margin: 10px;
            cursor: pointer;
        }
        .btn-danger {
            background: #dc3545;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        table, th, td {
            border: 1px solid #ddd;
        }
        th, td {
            padding: 8px 12px;
            text-align: left;
        }
        th {
            background-color: #f2f2f2;
        }
        select, input[type="date"], input[type="text"], input[type="number"], input[type="submit"] {
            padding: 10px;
            margin: 10px;
            width: 100%;
            box-sizing: border-box;
        }
    </style>
</head>
<body>

    <div class="navbar">
        <h1>Staff Dashboard</h1>
    </div>

    <div class="container">
        <h2>Welcome, Staff!</h2>
        <p>You are logged in as: <strong><?php echo $_SESSION['role']; ?></strong></p>
        
        <a href="logout.php" class="btn btn-danger">Logout</a>

        <h3>Manually Add Booking</h3>
        <form method="POST" action="staff_dashboard.php">
            <label for="user_id">Select User:</label>
            <select name="user_id" required>
                <option value="">Select User</option>
                <?php while ($user = $users_result->fetch_assoc()): ?>
                    <option value="<?php echo $user['user_id']; ?>"><?php echo $user['username']; ?></option>
                <?php endwhile; ?>
            </select>

            <label for="vehicle_id">Select Vehicle:</label>
            <select name="vehicle_id" required>
                <option value="">Select Vehicle</option>
                <?php while ($vehicle = $vehicles_result->fetch_assoc()): ?>
                    <option value="<?php echo $vehicle['vehicle_id']; ?>">
                        <?php echo $vehicle['make'] . ' ' . $vehicle['model']; ?> - £<?php echo $vehicle['price_per_day']; ?> per day
                    </option>
                <?php endwhile; ?>
            </select>

            <label for="start_date">Start Date:</label>
            <input type="date" name="start_date" required>

            <label for="end_date">End Date:</label>
            <input type="date" name="end_date" required>

            <label for="status">Status:</label>
            <input type="text" name="status" required>

            <input type="submit" name="add_booking" value="Add Booking">
        </form>

        <h3>Vehicles</h3>
        <table>
            <thead>
                <tr>
                    <th>Vehicle ID</th>
                    <th>Make</th>
                    <th>Model</th>
                    <th>Year</th>
                    <th>Price per Day</th>
                    <th>Status</th>
                    <th>Created At</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($vehicle = $vehicles_result->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo $vehicle['vehicle_id']; ?></td>
                        <td><?php echo $vehicle['make']; ?></td>
                        <td><?php echo $vehicle['model']; ?></td>
                        <td><?php echo $vehicle['year']; ?></td>
                        <td><?php echo $vehicle['price_per_day']; ?></td>
                        <td><?php echo $vehicle['status']; ?></td>
                        <td><?php echo $vehicle['created_at']; ?></td>
                        <td>
                            <a href="edit_vehicle.php?id=<?php echo $vehicle['vehicle_id']; ?>" class="btn">Edit</a>
                            <a href="delete_vehicle.php?id=<?php echo $vehicle['vehicle_id']; ?>" class="btn btn-danger">Delete</a>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>

        <h3>Bookings</h3>
        <table>
            <thead>
                <tr>
                    <th>Booking ID</th>
                    <th>Vehicle</th>
                    <th>Start Date</th>
                    <th>End Date</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($booking = $bookings_result->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo $booking['booking_id']; ?></td>
                        <td><?php echo $booking['make'] . ' ' . $booking['model']; ?></td>
                        <td><?php echo $booking['start_date']; ?></td>
                        <td><?php echo $booking['end_date']; ?></td>
                        <td><?php echo $booking['status']; ?></td>
                        <td>
                            <a href="?booking_id=<?php echo $booking['booking_id']; ?>" class="btn">View Details</a>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>

    <?php if (isset($booking_details)): ?>
        <div class="container">
            <h3>Booking Details (ID: <?php echo $booking_details['booking_id']; ?>)</h3>
            <p><strong>Vehicle:</strong> <?php echo $booking_details['make'] . ' ' . $booking_details['model']; ?></p>
            <p><strong>Start Date:</strong> <?php echo $booking_details['start_date']; ?></p>
            <p><strong>End Date:</strong> <?php echo $booking_details['end_date']; ?></p>
            <p><strong>Status:</strong> <?php echo $booking_details['status']; ?></p>
            <p><strong>User:</strong> <?php echo $booking_details['username']; ?></p>
            <p><strong>Email:</strong> <?php echo $booking_details['email']; ?></p>
            <a href="staff_dashboard.php" class="btn">Back to Dashboard</a>
        </div>
    <?php endif; ?>

</body>
</html>

<?php
$conn->close();
?>

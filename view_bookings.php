booking_login.php<?php
session_start();


// Connection to the database
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "car_rental";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
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
        <h1>Edit Bookings</h1>
    </div>
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
                            <a href="?booking_id=<?php echo $booking['booking_id']; ?>" class="btn">View All Details</a>
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
            <a href="admin_dashboard.php" class="btn">Back to Dashboard</a>
        </div>
    <?php endif; ?>

</body>
</html>
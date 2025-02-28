<?php
session_start();

// Check if user is logged in and is a customer
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'customer') {
    header("Location: login.php");
    exit();
}

// Include database connection file
include 'db_connect.php'; // Adjust this according to your file structure

// Get the logged-in user's ID
$user_id = $_SESSION['user_id'];

// Handle booking cancellation if the cancel button is pressed
if (isset($_GET['cancel_id'])) {
    $cancel_id = $_GET['cancel_id'];

    // Prepare and execute the cancel query (update the booking status to "Cancelled")
    $cancel_query = "UPDATE bookings SET status = 'Cancelled' WHERE booking_id = ? AND user_id = ?";
    $cancel_stmt = $conn->prepare($cancel_query);
    $cancel_stmt->bind_param("ii", $cancel_id, $user_id);

    if ($cancel_stmt->execute()) {
        echo "<script>alert('Booking cancelled successfully.');</script>";
    } else {
        echo "<script>alert('Error cancelling booking.');</script>";
    }

    $cancel_stmt->close();
}

// Handle date update if the form is submitted
if (isset($_POST['update_dates'])) {
    $booking_id = $_POST['booking_id'];
    $new_start_date = $_POST['start_date'];
    $new_end_date = $_POST['end_date'];

    // Fetch the price per day for the selected booking vehicle
    $vehicle_query = "SELECT v.price_per_day FROM bookings b JOIN vehicles v ON b.vehicle_id = v.vehicle_id WHERE b.booking_id = ?";
    $vehicle_stmt = $conn->prepare($vehicle_query);
    $vehicle_stmt->bind_param("i", $booking_id);
    $vehicle_stmt->execute();
    $vehicle_stmt->bind_result($price_per_day);
    $vehicle_stmt->fetch();
    $vehicle_stmt->close();

    // Calculate the total price based on the number of days
    $start_date_timestamp = strtotime($new_start_date);
    $end_date_timestamp = strtotime($new_end_date);
    $days_diff = ($end_date_timestamp - $start_date_timestamp) / (60 * 60 * 24); // Number of days
    $total_price = $days_diff * $price_per_day;

    // Update the booking dates and total price
    $update_query = "UPDATE bookings SET start_date = ?, end_date = ?, total_price = ? WHERE booking_id = ? AND user_id = ?";
    $update_stmt = $conn->prepare($update_query);
    $update_stmt->bind_param("ssiii", $new_start_date, $new_end_date, $total_price, $booking_id, $user_id);

    if ($update_stmt->execute()) {
        echo "<script>alert('Booking dates updated successfully.');</script>";
    } else {
        echo "<script>alert('Error updating booking dates.');</script>";
    }

    $update_stmt->close();
}

// Fetch bookings for the logged-in user, joining with vehicles table to get the vehicle name
$query = "SELECT b.booking_id, CONCAT(v.make, ' ', v.model) AS vehicle_name, b.start_date, b.end_date, b.total_price, b.status 
          FROM bookings b 
          JOIN vehicles v ON b.vehicle_id = v.vehicle_id 
          WHERE b.user_id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Customer Dashboard</title>
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
            max-width: 800px;
            margin: 20px auto;
            background: white;
            padding: 20px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            text-align: center;
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
            padding: 10px;
            text-align: left;
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
        .btn-cancel {
            background: #dc3545;
            padding: 5px 10px;
            text-decoration: none;
            color: white;
            border-radius: 5px;
        }
        .btn-edit {
            background: #28a745;
            padding: 5px 10px;
            text-decoration: none;
            color: white;
            border-radius: 5px;
        }
        .form-container {
            margin-top: 20px;
        }
    </style>
</head>
<body>

    <div class="navbar">
        <h1>Customer Dashboard</h1>
    </div>

    <div class="container">
        <h2>Welcome, Customer!</h2>
        <p>You are logged in as: <strong><?php echo $_SESSION['role']; ?></strong></p>

        <h3>Your Bookings</h3>
        <?php if ($result->num_rows > 0): ?>
            <table>
                <thead>
                    <tr>
                        <th>Booking ID</th>
                        <th>Vehicle</th>
                        <th>Start Date</th>
                        <th>End Date</th>
                        <th>Total Price</th>
                        <th>Status</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($row = $result->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo $row['booking_id']; ?></td>
                            <td><?php echo $row['vehicle_name']; ?></td>
                            <td><?php echo $row['start_date']; ?></td>
                            <td><?php echo $row['end_date']; ?></td>
                            <td><?php echo '£' . number_format($row['total_price'], 2); ?></td>
                            <td><?php echo $row['status']; ?></td>
                            <td>
                                <?php if ($row['status'] !== 'Cancelled'): ?>
                                    <a href="?cancel_id=<?php echo $row['booking_id']; ?>" class="btn-cancel" onclick="return confirm('Are you sure you want to cancel this booking?')">Cancel</a>
                                    <button class="btn-edit" onclick="showEditForm(<?php echo $row['booking_id']; ?>, '<?php echo $row['start_date']; ?>', '<?php echo $row['end_date']; ?>')">Edit Dates</button>
                                <?php else: ?>
                                    <span class="btn-cancel" style="background-color: grey;">Cancelled</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>

            </table>
        <?php else: ?>
            <p>You have no bookings.</p>
        <?php endif; ?>

        <!-- Hidden form for editing dates -->
        <div class="form-container" id="editFormContainer" style="display:none;">
            <h4>Edit Booking Dates</h4>
            <form method="POST">
                <input type="hidden" name="booking_id" id="booking_id">
                <label for="start_date">Start Date:</label>
                <input type="date" name="start_date" id="start_date" required>
                <br>
                <label for="end_date">End Date:</label>
                <input type="date" name="end_date" id="end_date" required>
                <br>
                <input type="submit" name="update_dates" value="Update Dates" class="btn">
            </form>
        </div>

        <a href="logout.php" class="btn btn-danger">Logout</a>
    </div>

    <script>
    function showEditForm(bookingId, startDate, endDate) {
        // Show the form and populate the values
        document.getElementById('editFormContainer').style.display = 'block';
        document.getElementById('booking_id').value = bookingId;
        document.getElementById('start_date').value = startDate;
        document.getElementById('end_date').value = endDate;

        // Set the min attribute for start date and end date to prevent selecting past dates
        const today = new Date().toISOString().split('T')[0]; // Get today's date in YYYY-MM-DD format
        document.getElementById('start_date').setAttribute('min', today);
        document.getElementById('end_date').setAttribute('min', today);
    }
</script>

</body>
</html>

<?php
$stmt->close();
$conn->close();
?>

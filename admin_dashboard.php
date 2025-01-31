<?php
session_start();

// Check if user is logged in and has admin role
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
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
    </style>
</head>
<body>

    <div class="navbar">
        <h1>Admin Dashboard</h1>
    </div>

    <div class="container">
        <h2>Welcome, Admin!</h2>
        <p>You are logged in as: <strong><?php echo $_SESSION['role']; ?></strong></p>
        
        <a href="manage_users.php" class="btn">Manage Users</a>
        <a href="view_bookings.php" class="btn">View Bookings</a>
        <a href="logout.php" class="btn btn-danger">Logout</a>
    </div>

</body>
</html>

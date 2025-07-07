<?php
session_start(); // Start the session

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    // Redirect to the login page if the user is not logged in
    header("Location: /health_record/user_login.php");
    exit();
}

// Include the database connection
include 'sqlconnection.php';
include 'user_navbar.php'; // Include the user navbar

// Fetch user details, has_record status, and LRN from the database
$user_id = $_SESSION['user_id'];
$sql = "SELECT u.first_name, u.last_name, u.lrn, COALESCE(u.has_record, 0) as has_record FROM users u WHERE u.id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows == 1) {
    $user = $result->fetch_assoc();
    $first_name = $user['first_name'];
    $last_name = $user['last_name'];
    $full_name = $first_name . ' ' . $last_name;
    $has_record = (bool)$user['has_record']; // Cast to boolean
    $lrn = $user['lrn'];
} else {
    // Handle error if user data is not found
    $full_name = "User";
    $has_record = false; // Use boolean false instead of 0
    $lrn = null;
}

$stmt->close();
$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Welcome</title>
    <link rel="stylesheet" href="css/landing.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        /* Responsive adjustments for mobile */
        @media (max-width: 600px) {
            .container {
                padding: 0 8px;
            }
            .welcome-card {
                padding: 18px 8px;
                border-radius: 12px;
                max-width: 98vw;
            }
            .welcome-card h1 {
                font-size: 1.15rem;
                margin-bottom: 10px;
            }
            .welcome-card .greeting {
                font-size: 1rem;
            }
            .welcome-card .logo {
                width: 54px;
                height: 54px;
                margin-bottom: 10px;
            }
            .actions .btn {
                font-size: 0.98rem;
                padding: 8px 16px;
                margin: 6px 0;
                width: 100%;
                box-sizing: border-box;
            }
        }
        @media (max-width: 400px) {
            .welcome-card h1 {
                font-size: 0.98rem;
            }
            .welcome-card .logo {
                width: 40px;
                height: 40px;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="welcome-card">
            <img src="img/aski_logo.jpg" class="logo" alt="logo">
            <h1>Welcome to the ASKI-SKI Student Health Record MS!</h1>
            <p class="greeting">Hello, <span class="user-name"><?php echo htmlspecialchars($full_name); ?></span></p>
            <div class="actions">
                <?php if ($has_record): ?>
                    <!-- Show View Record and Edit Record buttons if the user has a record -->
                    <a href="user_view_record.php?lrn=<?php echo htmlspecialchars($lrn); ?>" class="btn">View Record</a>
                    <a href="user_edit_rec.php?lrn=<?php echo htmlspecialchars($lrn); ?>" class="btn">Edit Record</a>
                <?php else: ?>
                    <!-- Show Add New Record button if the user has no record -->
                    <a href="add_record.php" class="btn">Add New Record</a>
                <?php endif; ?>
            </div>
        </div>
    </div>
</body>
</html>
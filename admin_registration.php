<?php
include 'sqlconnection.php'; // Database connection

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);
    
    // Basic validation
    if (empty($name) || empty($email) || empty($password)) {
        echo "<script>alert('All fields are required!');</script>";
    } else {
        // Check if email already exists
        $stmt = $conn->prepare("SELECT * FROM admin_users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            echo "<script>alert('Email already exists!');</script>";
        } else {
            // Hash the password
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            
            // Prepare the insert statement
            $insert_stmt = $conn->prepare("INSERT INTO admin_users (name, email, password) VALUES (?, ?, ?)");
            $insert_stmt->bind_param("sss", $name, $email, $hashed_password);
            
            if ($insert_stmt->execute()) {
                echo "<script>alert('Registration successful!'); window.location='admin_login.php';</script>";
            } else {
                echo "<script>alert('Registration failed!');</script>";
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Registration</title>
    <link rel="stylesheet" href="css/admin_registration.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
</head>
<body>
    <div class="auth-container">
        <div class="auth-box">
            <h2>Registration</h2>
            <form method="POST">
                <div class="auth-input-group">
                    <label for="name">Name</label>
                    <input type="text" id="name" name="name" required>
                </div>
                <div class="auth-input-group">
                    <label for="email">Email</label>
                    <input type="email" id="email" name="email" required>
                </div>
                <div class="auth-input-group">
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password" required>
                </div>
                <button type="submit" class="auth-btn">Register</button>
            </form>

            <!-- Link to login if already have an account -->
            <p style="text-align: center; margin-top: 15px;">
                Already have an account? <a href="admin_login.php" style="color: #1e3a8a; text-decoration: none;">Login here</a>
            </p>
        </div>
    </div>
</body>
</html>
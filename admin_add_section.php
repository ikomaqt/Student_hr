<?php
include 'admin_navbar.php';
include 'sqlconnection.php'; // Database connection

// Handle form submission
$message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['section_name'])) {
    $section_name = trim($_POST['section_name']);
    if ($section_name !== '') {
        // Check if section already exists
        $stmt = $conn->prepare('SELECT section_id FROM section WHERE section_name = ?');
        $stmt->bind_param('s', $section_name);
        $stmt->execute();
        $stmt->store_result();
        if ($stmt->num_rows > 0) {
            $message = '<div style="color: red;">Section already exists!</div>';
            $stmt->close();
        } else {
            $stmt->close(); // Close the select statement before preparing insert
            $insert_stmt = $conn->prepare('INSERT INTO section (section_name) VALUES (?)');
            $insert_stmt->bind_param('s', $section_name);
            if ($insert_stmt->execute()) {
                $message = '<div style="color: green;">Section added successfully!</div>';
            } else {
                $message = '<div style="color: red;">Error adding section.</div>';
            }
            $insert_stmt->close();
        }
    } else {
        $message = '<div style="color: red;">Section name cannot be empty.</div>';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Add New Section</title>
    <link rel="stylesheet" href="css/admin.css">
    <style>
        body {
            background: #f3f4f6;
        }
        .section-card {
            background: #fff;
            border-radius: 12px;
            box-shadow: 0 2px 12px rgba(30,58,138,0.08);
            padding: 32px 32px 24px 32px;
            margin: 40px auto;
            max-width: 500px;
        }
        h2 {
            color: #1e3a8a;
            margin-bottom: 24px;
            font-weight: 700;
        }
        form {
            margin-bottom: 18px;
        }
        #section_name {
            width: 100%;
            padding: 10px 12px;
            border: 1px solid #cbd5e1;
            border-radius: 6px;
            font-size: 16px;
            margin-bottom: 8px;
            margin-top: 8px;
            background: #f8fafc;
            transition: border 0.2s;
        }
        #section_name:focus {
            border-color: #1e3a8a;
            outline: none;
        }
        .info-text {
            color: #555;
            font-size: 14px;
            margin-bottom: 16px;
            display: block;
        }
        .add-btn {
            padding: 9px 20px;
            background: #1e3a8a;
            color: white;
            border: none;
            border-radius: 4px;
            font-size: 16px;
            font-weight: 500;
            cursor: pointer;
            transition: background 0.2s;
        }
        .add-btn:hover {
            background: #2563eb;
        }
        .nav-links {
            margin-top: 28px;
        }
        .nav-links a {
            color: #1e3a8a;
            text-decoration: none;
            margin-right: 24px;
            font-weight: 500;
            transition: color 0.2s;
        }
        .nav-links a:hover {
            color: #2563eb;
        }
    </style>
</head>
<body>
    <div class="section-card">
        <h2>Add New Section</h2>
        <?php if ($message) echo $message; ?>
        <form method="POST" action="">
            <input type="text" id="section_name" name="section_name" required placeholder="e.g. 7 - Einstein">
            <span class="info-text">Format: [number] - [Section Name] (e.g. 7 - Einstein). Please follow this format when adding a new section.</span>
            <button type="submit" class="add-btn">Add Section</button>
        </form>
        <div class="nav-links">
            <a href="admin.php">&larr; Back to Dashboard</a>
            <a href="admin_manage_sections.php">Manage Sections</a>
        </div>
    </div>
</body>
</html>

<?php
include 'admin_navbar.php';
include 'sqlconnection.php';

// Pagination setup
$limit = 10;
$page = isset($_GET['page']) && is_numeric($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $limit;

// Handle delete request
$delete_error = '';
if (isset($_POST['delete_section_id'])) {
    $delete_id = intval($_POST['delete_section_id']);
    // Check if section is used by any user
    $check_stmt = $conn->prepare('SELECT COUNT(*) FROM users WHERE section_id = ?');
    $check_stmt->bind_param('i', $delete_id);
    $check_stmt->execute();
    $check_stmt->bind_result($user_count);
    $check_stmt->fetch();
    $check_stmt->close();
    if ($user_count > 0) {
        $delete_error = 'Cannot delete: Section is assigned to one or more users.';
    } else {
        $stmt = $conn->prepare('DELETE FROM section WHERE section_id = ?');
        $stmt->bind_param('i', $delete_id);
        $stmt->execute();
        $stmt->close();
    }
}

// Get total count for pagination
$total_result = $conn->query('SELECT COUNT(*) as total FROM section');
$total_row = $total_result->fetch_assoc();
$total_sections = $total_row['total'];
$total_pages = ceil($total_sections / $limit);

// Fetch sections for current page
$sections = [];
$result = $conn->query("SELECT section_id, section_name FROM section ORDER BY section_id ASC LIMIT $limit OFFSET $offset");
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $sections[] = $row;
    }
    $result->free();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Manage Sections</title>
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
            max-width: 700px;
        }
        h2 {
            color: #1e3a8a;
            margin-bottom: 24px;
            font-weight: 700;
        }
        table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0;
            background: #fff;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 1px 4px rgba(0,0,0,0.04);
        }
        th, td {
            padding: 14px 12px;
            text-align: left;
        }
        th {
            background: #e0e7ff;
            color: #1e3a8a;
            font-weight: 600;
        }
        tr:nth-child(even) {
            background: #f8fafc;
        }
        tr:nth-child(odd) {
            background: #fff;
        }
        .delete-btn {
            background: #dc2626;
            color: #fff;
            border: none;
            padding: 7px 18px;
            border-radius: 6px;
            font-size: 15px;
            cursor: pointer;
            transition: background 0.2s;
        }
        .delete-btn:hover {
            background: #b91c1c;
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
        .pagination {
            margin: 24px 0 0 0;
            text-align: center;
        }
        .pagination a, .pagination span {
            display: inline-block;
            margin: 0 4px;
            padding: 7px 14px;
            border-radius: 4px;
            background: #e0e7ff;
            color: #1e3a8a;
            text-decoration: none;
            font-weight: 500;
            transition: background 0.2s, color 0.2s;
        }
        .pagination a:hover {
            background: #1e3a8a;
            color: #fff;
        }
        .pagination .active {
            background: #1e3a8a;
            color: #fff;
            pointer-events: none;
        }
    </style>
</head>
<body>
    <div class="section-card">
        <h2>Manage Sections</h2>
        <?php if (!empty($delete_error)): ?>
            <div style="color: #dc2626; background: #fee2e2; border: 1px solid #fecaca; padding: 12px 18px; border-radius: 6px; margin-bottom: 18px; font-weight: 500;">
                <?php echo $delete_error; ?>
            </div>
        <?php endif; ?>
        <table>
            <tr>
                <th>ID</th>
                <th>Section Name</th>
                <th>Action</th>
            </tr>
            <?php foreach ($sections as $section): ?>
            <tr>
                <td><?php echo $section['section_id']; ?></td>
                <td><?php echo htmlspecialchars($section['section_name']); ?></td>
                <td>
                    <form method="POST" action="" onsubmit="return confirm('Are you sure you want to delete this section?');" style="display:inline;">
                        <input type="hidden" name="delete_section_id" value="<?php echo $section['section_id']; ?>">
                        <button type="submit" class="delete-btn">Delete</button>
                    </form>
                </td>
            </tr>
            <?php endforeach; ?>
        </table>
        <div class="pagination">
            <?php if ($page > 1): ?>
                <a href="?page=<?php echo $page - 1; ?>">&laquo; Prev</a>
            <?php endif; ?>
            <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                <?php if ($i == $page): ?>
                    <span class="active"><?php echo $i; ?></span>
                <?php else: ?>
                    <a href="?page=<?php echo $i; ?>"><?php echo $i; ?></a>
                <?php endif; ?>
            <?php endfor; ?>
            <?php if ($page < $total_pages): ?>
                <a href="?page=<?php echo $page + 1; ?>">Next &raquo;</a>
            <?php endif; ?>
        </div>
        <div class="nav-links">
            <a href="admin_add_section.php">&larr; Back to Add Section</a>
            <a href="admin.php">Back to Dashboard</a>
        </div>
    </div>
</body>
</html>

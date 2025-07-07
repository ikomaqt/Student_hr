<!DOCTYPE html>
<html lang="en" dir="ltr">
<head>
    <meta charset="UTF-8">
    <title>Responsive Sidebar Menu | Aski</title>
    <link rel="stylesheet" href="css/admin_navbar.css">
    <!-- Boxicons CDN Link -->
    <link href='https://unpkg.com/boxicons@2.0.7/css/boxicons.min.css' rel='stylesheet'>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
</head>
<body>
    <div class="sidebar">
        <div class="logo-details">
            <img src="img/aski_logo.jpg" alt="ASKI Logo" class="logo-image">
            <div class="logo_name">ASKI</div>
            <i class='bx bx-menu' id="btn"></i>
        </div>
        <ul class="nav-list">
            <li>
                <a href="/health_record/admin.php">
                    <i class='bx bx-grid-alt'></i>
                    <span class="links_name">Dashboard</span>
                </a>
            </li>
            <li>
                <a href="admin_add_section.php">
                    <i class='bx bx-plus-circle'></i>
                    <span class="links_name">Add New Section</span>
                </a>
            </li>
            <li>
                <a href="/health_record/admin_login.php" onclick="confirmLogout()">
                    <i class='bx bx-log-out'></i>
                    <span class="links_name">Logout</span>
                </a>
            </li>
        </ul>
    </div>

    <script>
    let sidebar = document.querySelector(".sidebar");
    let closeBtn = document.querySelector("#btn");

    closeBtn.addEventListener("click", ()=> {
        sidebar.classList.toggle("open");
        menuBtnChange(); // calling the function (optional)
    });

    // Function to change sidebar button (optional)
    function menuBtnChange() {
        if (sidebar.classList.contains("open")) {
            closeBtn.classList.replace("bx-menu", "bx-menu-alt-right"); // replacing the icons class
        } else {
            closeBtn.classList.replace("bx-menu-alt-right", "bx-menu"); // replacing the icons class
        }
    }

    // Logout confirmation function
    function confirmLogout() {
        if (confirm("Are you sure you want to logout?")) {
            window.location.href = "admin_login.php"; //
        }
    }
    </script>
</body>
</html>
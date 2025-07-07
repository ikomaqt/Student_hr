<?php
include 'admin_navbar.php';
include 'sqlconnection.php'; // Include your database connection file

// Add section filter
$selectedSection = isset($_GET['section']) ? $_GET['section'] : 'all';
$sortField = isset($_GET['sortField']) ? $_GET['sortField'] : 'name';
$sort = isset($_GET['sort']) ? $_GET['sort'] : 'asc';

// Validate sort field to prevent SQL injection
$allowedSortFields = ['name', 'section_name', 'grade_section'];
if (!in_array($sortField, $allowedSortFields)) {
    $sortField = 'name';
}

// Pagination settings
$recordsPerPage = 5; // Number of records per page
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1; // Current page
$offset = ($page - 1) * $recordsPerPage; // Offset for SQL query

// Fetch total number of students
$totalQuery = "SELECT COUNT(*) as total FROM student_info";
$totalResult = $conn->query($totalQuery);
$totalRows = $totalResult->fetch_assoc()['total'];
$totalPages = ceil($totalRows / $recordsPerPage); // Calculate total pages

// Modified query to handle detailed section sorting
$orderBy = "";
switch ($sortField) {
    case 'name':
        $orderBy = "si.name " . $sort;
        break;
    case 'grade_section':
        $orderBy = "CAST(SUBSTRING_INDEX(s.section_name, ' -', 1) AS UNSIGNED) " . $sort . ", " .
                  "CASE 
                      WHEN s.section_name LIKE '7%' THEN 1
                      WHEN s.section_name LIKE '8%' THEN 2
                      WHEN s.section_name LIKE '9%' THEN 3
                      WHEN s.section_name LIKE '10%' THEN 4
                      WHEN s.section_name LIKE '11%' THEN 5
                      WHEN s.section_name LIKE '12%' THEN 6
                   END " . $sort . ", " .
                  "s.section_name " . $sort;
        break;
    case 'section_name':
        $orderBy = "s.section_name " . $sort;
        break;
}

// Modify the query to include section filtering
$whereClause = $selectedSection !== 'all' ? "WHERE s.section_id = ?" : "";
$query = "SELECT si.name, si.lrn, s.section_name as section 
          FROM student_info si 
          LEFT JOIN section s ON si.section_id = s.section_id 
          $whereClause
          ORDER BY " . $orderBy . " 
          LIMIT ?, ?";

// Prepare and execute the query
$stmt = $conn->prepare($query);
if ($selectedSection !== 'all') {
    $stmt->bind_param("iii", $selectedSection, $offset, $recordsPerPage);
} else {
    $stmt->bind_param("ii", $offset, $recordsPerPage);
}
$stmt->execute();
$result = $stmt->get_result();

// Fetch sections for filter dropdown
$sectionsQuery = "SELECT section_id, section_name 
                 FROM section 
                 ORDER BY CAST(SUBSTRING_INDEX(section_name, ' -', 1) AS UNSIGNED), 
                          section_name";
$sections = $conn->query($sectionsQuery);

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    <link rel="stylesheet" href="css/admin.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body>
    <div id="webcrumbs">
        <div class="w-[1200px] bg-white shadow-lg rounded-lg p-6">
            <div class="flex flex-col">
                <div class="flex items-center justify-between mb-6">
                    <h1 class="text-2xl font-bold text-[#1e3a8a]">Student Health Records</h1>
                    <div class="flex items-center gap-4">
                        <!-- Search Bar -->
                        <div class="relative">
                            <span class="material-symbols-outlined absolute left-3 top-2.5 text-gray-400">search</span>
                            <input type="text" id="search" placeholder="Search records..." class="pl-10 pr-4 py-2 border border-gray-200 rounded-lg focus:outline-none focus:border-[#1e3a8a] transition-colors"/>
                        </div>
                        <!-- Section Filter Dropdown -->
                        <select onChange="window.location.href='?section='+this.value" class="px-4 py-2 border border-gray-200 rounded-lg focus:outline-none focus:border-[#1e3a8a]">
                            <option value="all" <?php echo $selectedSection === 'all' ? 'selected' : ''; ?>>All Sections</option>
                            <?php while ($section = $sections->fetch_assoc()): ?>
                                <option value="<?php echo $section['section_id']; ?>" 
                                        <?php echo $selectedSection == $section['section_id'] ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($section['section_name']); ?>
                                </option>
                            <?php endwhile; ?>
                        </select>
                        <!-- Sort Name Only -->
                        <a href="?section=<?php echo $selectedSection; ?>&sort=<?php echo $sort === 'asc' ? 'desc' : 'asc'; ?>" 
                           class="px-4 py-2 bg-gray-50 rounded-lg hover:bg-gray-100 transition-colors flex items-center gap-2">
                            <span class="material-symbols-outlined">sort</span>
                            Name (<?php echo $sort === 'asc' ? 'A-Z' : 'Z-A'; ?>)
                        </a>
                    </div>
                </div>
                <div class="grid grid-cols-1 gap-4" id="student-list">
                    <?php while ($row = $result->fetch_assoc()): ?>
                        <details class="border border-gray-200 rounded-lg student-record">
                            <summary class="list-none cursor-pointer p-4 bg-[#1e3a8a]/5 hover:bg-[#1e3a8a]/10 transition-colors rounded-lg flex items-center justify-between">
                                <div class="flex items-center gap-4">
                                    <div>
                                        <h3 class="font-semibold text-[#1e3a8a] student-name"><?php echo htmlspecialchars($row['name']); ?></h3>
                                        <p class="text-sm text-gray-500 student-lrn">LRN: <?php echo htmlspecialchars($row['lrn']); ?></p>
                                        <p class="text-sm text-gray-500 student-section">Section: <?php echo htmlspecialchars($row['section']); ?></p>
                                    </div>
                                </div>
                                <a href="admin_view_record.php?lrn=<?php echo $row['lrn']; ?>" class="px-4 py-2 bg-[#1e3a8a] text-white rounded-lg hover:bg-[#1e3a8a]/90 transition-colors flex items-center gap-2 hover:translate-x-1">
                                    View Record
                                </a>
                            </summary>
                        </details>
                    <?php endwhile; ?>
                </div>

               <!-- Pagination -->
                <div class="pagination">
                    <?php if ($page > 1): ?>
                        <a href="?section=<?php echo $selectedSection; ?>&sortField=<?php echo $sortField; ?>&sort=<?php echo $sort; ?>&page=<?php echo $page - 1; ?>">&laquo; Prev</a>
                    <?php endif; ?>
                    <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                        <?php if ($i == $page): ?>
                            <span class="active"><?php echo $i; ?></span>
                        <?php else: ?>
                            <a href="?section=<?php echo $selectedSection; ?>&sortField=<?php echo $sortField; ?>&sort=<?php echo $sort; ?>&page=<?php echo $i; ?>"><?php echo $i; ?></a>
                        <?php endif; ?>
                    <?php endfor; ?>
                    <?php if ($page < $totalPages): ?>
                        <a href="?section=<?php echo $selectedSection; ?>&sortField=<?php echo $sortField; ?>&sort=<?php echo $sort; ?>&page=<?php echo $page + 1; ?>">Next &raquo;</a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- JavaScript for Search Functionality -->
    <script>
        document.getElementById('search').addEventListener('input', function () {
            const searchTerm = this.value.toLowerCase();
            const studentRecords = document.querySelectorAll('.student-record');

            studentRecords.forEach(record => {
                const name = record.querySelector('.student-name').textContent.toLowerCase();
                const lrn = record.querySelector('.student-lrn').textContent.toLowerCase();

                if (name.includes(searchTerm) || lrn.includes(searchTerm)) {
                    record.style.display = '';
                } else {
                    record.style.display = 'none';
                }
            });
        });
    </script>
</body>
</html>

<style>
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

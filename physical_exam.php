<?php
include 'sqlconnection.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validate required fields
    $required_fields = ['student_id', 'height', 'weight', 'bmi', 'blood_pressure', 
                       'pulse_rate', 'respiratory_rate', 'examined_by', 'exam_date'];
    $errors = [];
    
    foreach ($required_fields as $field) {
        if (empty($_POST[$field])) {
            $errors[] = ucfirst(str_replace('_', ' ', $field)) . ' is required.';
        }
    }

    if (empty($errors)) {
        $student_id = $_POST['student_id'];
        $height = filter_var($_POST['height'], FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
        $weight = filter_var($_POST['weight'], FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
        $bmi = filter_var($_POST['bmi'], FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
        $blood_pressure = $_POST['blood_pressure'];
        $pulse_rate = filter_var($_POST['pulse_rate'], FILTER_SANITIZE_NUMBER_INT);
        $respiratory_rate = filter_var($_POST['respiratory_rate'], FILTER_SANITIZE_NUMBER_INT);
        $remarks = !empty($_POST['remarks']) ? trim($_POST['remarks']) : null; // Changed to null when empty
        $examined_by = $_POST['examined_by'];
        $exam_date = $_POST['exam_date'];
        $lrn = $_POST['lrn'];

        // Check if a record already exists for this student
        $checkQuery = "SELECT * FROM physical_examination WHERE student_id = ?";
        $stmt = $conn->prepare($checkQuery);
        $stmt->bind_param("i", $student_id);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            // UPDATE existing record
            $updateQuery = "UPDATE physical_examination SET 
                            height = ?, weight = ?, bmi = ?, blood_pressure = ?, 
                            pulse_rate = ?, respiratory_rate = ?, remarks = ?, 
                            examined_by = ?, exam_date = ? 
                            WHERE student_id = ?";
            $stmt = $conn->prepare($updateQuery);
            $stmt->bind_param("dddsiisssi", 
                $height, $weight, $bmi, 
                $blood_pressure, $pulse_rate, 
                $respiratory_rate, $remarks,
                $examined_by, $exam_date,
                $student_id
            );
        } else {
            // INSERT new record
            $insertQuery = "INSERT INTO physical_examination 
                            (student_id, height, weight, bmi, blood_pressure, 
                             pulse_rate, respiratory_rate, remarks, examined_by, exam_date) 
                            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
            $stmt = $conn->prepare($insertQuery);
            $stmt->bind_param("idddsiisss", 
                $student_id, $height, $weight, $bmi, 
                $blood_pressure, $pulse_rate, 
                $respiratory_rate, $remarks,
                $examined_by, $exam_date
            );
        }

        if ($stmt->execute()) {
            $message = "Physical examination data saved successfully.";
            $alertType = "success";
        } else {
            $message = "Error saving physical examination data: " . $stmt->error;
            $alertType = "error";
        }

        $stmt->close();
        $conn->close();
    } else {
        $message = "Please correct the following errors: " . implode(", ", $errors);
        $alertType = "error";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Physical Examination</title>
    <link rel="stylesheet" href="css/view_records.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body>
    <?php if (isset($message)): ?>
        <script>
            Swal.fire({
                icon: '<?php echo $alertType; ?>',
                title: '<?php echo $message; ?>',
                text: '<?php echo $alertType === "error" ? "Please check the form and try again." : ""; ?>'
            }).then(function() {
                window.location.href = 'admin_view_record.php?lrn=<?php echo isset($lrn) ? $lrn : ""; ?>';
            });
        </script>
    <?php endif; ?>
</body>
</html>
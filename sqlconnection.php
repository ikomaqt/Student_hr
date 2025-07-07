<?php
$conn = mysqli_connect('localhost', 'root', '', 'health_record');

// Check connection
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}
?>
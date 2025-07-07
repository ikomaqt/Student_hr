<?php
include 'user_navbar.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contact Us | ASKI</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="css/contact.css">
</head>
<body>
    <div class="container">
        <div class="page-header">
            <h1>Get In Touch With Us</h1>
            <p>We'd love to hear from you! Whether you have a question about our services, need assistance, or just want to say hello, our team is ready to help.</p>
        </div>
        
        <div class="contact-row">
            <div class="contact-card">
                <i class="fas fa-envelope"></i>
                <h2>Email Us</h2>
                <p>Have questions? Send us an email and we'll get back to you as soon as possible.</p>
                <a href="mailto:askiassessmentcenter@gmail.com">askiassessmentcenter@gmail.com</a>
            </div>
            
            <div class="contact-card">
                <i class="fas fa-map-marker-alt"></i>
                <h2>Visit Us</h2>
                <p>Come see us at our office location:</p>
                <p>Purok 1, Barangay Sampaloc, Talavera, Nueva Ecija</p>
            </div>
            
            <div class="contact-card">
                <i class="fas fa-phone-alt"></i>
                <h2>Call Us</h2>
                <p>Speak directly with our team during business hours.</p>
                <p>Mobile: <a href="tel:09976986046">(+63) 997 698 6046</a></p>
                <p>Telephone: <a href="tel:09449401800">(+63) 944 940 1800</a></p>
            </div>
        </div>
        
        <div class="business-hours">
            <h2><i class="far fa-clock"></i> Business Hours</h2>
            <p>Monday to Friday: 8:00 AM to 5:00 PM</p>
            <p>Saturday and Sunday: Closed</p>
        </div>
        
        <div class="map-container">
            <iframe src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d12345.6789!2d120.987654!3d15.123456!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x0%3A0x0!2zMTXCsDA3JzI0LjQiTiAxMjDCsDU5JzE1LjYiRQ!5e0!3m2!1sen!2sph!4v1234567890123!5m2!1sen!2sph" allowfullscreen="" loading="lazy"></iframe>
        </div>
    </div>

    <script>
        function handleSocialClick(platform) {
            alert(`Our ${platform} page would open in a real implementation.`);
        }
    </script>
</body>
</html>
<?php

define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'job_candidates');

// Email configuration
define('EMAIL_FROM', 'hr@yourcompany.com');
define('EMAIL_FROM_NAME', 'HR Department');

// database connection
$conn = mysqli_connect(DB_HOST, DB_USER, DB_PASS, DB_NAME);

if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}
?>
<?php
/**
 * ===============================
 * Database Configuration
 * ===============================
 */

// Database connection parameters
$db_host = "localhost:3306";
$db_user = "root";
$db_pass = "";
$db_name = "my_page";

// Admin authentication credentials
$login = "some_email@example.com";
$password = "some_password";
$recipient = "recipient@example.com";

// Email configuration
ini_set('SMTP', 'localhost');
ini_set('smtp_port', 25);

// Establish database connection
$link = mysqli_connect($db_host, $db_user, $db_pass, $db_name);
if (!$link) {
    die("Could not connect: " . mysqli_connect_error());
}

// Set character encoding
mysqli_set_charset($link, "utf8mb4");

<?php
if (!isset($_SESSION)) {
    session_start();
}
require_once('cfg.php');
require_once('includes/contact_functions.php');

$message = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit'])) {
    $result = SendMail(
        filter_var($_POST['sender'], FILTER_SANITIZE_EMAIL),
        $_POST['subject'],
        $_POST['body']
    );
    
    $message = '<div class="alert alert-' . $result['status'] . '">' . $result['message'] . '</div>';
}
?>
<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kontakt</title>
    <link rel="stylesheet" href="css/admin.css">
    <style>
        .contact-container {
            max-width: 600px;
            margin: 40px auto;
            padding: 0 20px;
        }
        
        .contact-form {
            background: var(--surface-color);
            padding: 30px;
            border-radius: var(--border-radius);
            box-shadow: var(--shadow-sm);
        }
        
        .contact-form h2 {
            margin: 0 0 20px;
            color: var(--text-primary);
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 500;
            color: var(--text-primary);
        }
        
        .form-group input,
        .form-group textarea {
            width: 100%;
            padding: 8px 12px;
            border: 1px solid var(--border-color);
            border-radius: var(--border-radius);
            font-size: 14px;
        }
        
        .form-group textarea {
            resize: vertical;
            min-height: 120px;
        }
        
        .form-buttons {
            display: flex;
            gap: 10px;
            justify-content: flex-end;
        }
        
        .alert {
            padding: 12px 20px;
            border-radius: var(--border-radius);
            margin-bottom: 20px;
        }
        
        .alert-success {
            background-color: #e6f4ea;
            color: #1e7e34;
        }
        
        .alert-error {
            background-color: #feeced;
            color: #dc3545;
        }
    </style>
</head>
<body>
    <div class="contact-container">
        <?php 
        echo $message;
        echo showContactForm(); 
        ?>
    </div>
</body>
</html> 

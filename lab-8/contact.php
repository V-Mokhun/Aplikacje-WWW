<?php
require_once('./cfg.php');

function ShowContact()
{
?>
  <!DOCTYPE html>
  <html lang="en">

  <head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contact Us</title>
    <style>
      .contact-form {
        max-width: 600px;
        margin: 0 auto;
        padding: 20px;
      }

      .form-group {
        margin-bottom: 15px;
      }

      .form-group label {
        display: block;
        margin-bottom: 5px;
      }

      .form-group input,
      .form-group textarea {
        width: 100%;
        padding: 8px;
        border: 1px solid #ddd;
        border-radius: 4px;
      }

      button {
        background-color: #4CAF50;
        color: white;
        padding: 10px 20px;
        border: none;
        border-radius: 4px;
        cursor: pointer;
      }

      button:hover {
        background-color: #45a049;
      }

      .error {
        color: red;
        margin: 10px 0;
      }

      .success {
        color: green;
        margin: 10px 0;
      }
    </style>
  </head>

  <body>
    <div class="contact-form">
      <h2>Contact Us</h2>
      <form method="POST" action="">
        <div class="form-group">
          <label for="sender">Your Email:</label>
          <input type="email" name="sender" id="sender" required>
        </div>

        <div class="form-group">
          <label for="subject">Subject:</label>
          <input type="text" name="subject" id="subject" required>
        </div>

        <div class="form-group">
          <label for="body">Message:</label>
          <textarea name="body" id="body" rows="5" required></textarea>
        </div>

        <button type="submit" name="submit">Send Message</button>
      </form>
    </div>
  </body>

  </html>
<?php

  if (isset($_POST['submit'])) {
    SendMail($_POST['sender'], $_POST['subject'], $_POST['body']);
  }
}

function SendMail($sender, $subject, $body)
{
  global $recipient;

  if (!filter_var($sender, FILTER_VALIDATE_EMAIL)) {
    echo '<p class="error">Invalid sender email address!</p>';
    return false;
  }

  if (empty($subject) || empty($body)) {
    echo '<p class="error">All fields are required!</p>';
    return false;
  }

  $headers = "From: " . $sender . "\r\n";
  $headers .= "Reply-To: " . $sender . "\r\n";
  $headers .= "MIME-Version: 1.0\r\n";
  $headers .= "Content-Type: text/html; charset=UTF-8\r\n";
  $headers .= "X-Mailer: PHP/" . phpversion();

  $htmlBody = "<html><body>";
  $htmlBody .= nl2br(htmlspecialchars($body));
  $htmlBody .= "</body></html>";

  if (mail($recipient, $subject, $htmlBody, $headers)) {
    echo '<p class="success">Message sent successfully!</p>';
    return true;
  } else {
    echo '<p class="error">Failed to send message. Please try again later.</p>';
    return false;
  }
}

function RemindPassword($sender)
{
  global $recipient, $password;

  if (!filter_var($sender, FILTER_VALIDATE_EMAIL)) {
    echo '<p class="error">Invalid sender email address!</p>';
    return false;
  }

  $subject = "Password reminder";

  $headers = "From: " . $sender . "\r\n";
  $headers .= "Reply-To: " . $sender . "\r\n";
  $headers .= "MIME-Version: 1.0\r\n";
  $headers .= "Content-Type: text/html; charset=UTF-8\r\n";
  $headers .= "X-Mailer: PHP/" . phpversion();

  $htmlBody = "<html><body>";
  $htmlBody .= "Password for admin panel is: " . $password;
  $htmlBody .= "</body></html>";

  if (mail($recipient, $subject, $htmlBody, $headers)) {
    echo '<p class="success">Message sent successfully!</p>';
    return true;
  } else {
    echo '<p class="error">Failed to send message. Please try again later.</p>';
    return false;
  }
}
?>

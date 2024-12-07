<?php
require_once('./cfg.php');

/**
 * ===============================
 * Contact Form Functions
 * ===============================
 */

/**
 * Displays the contact form and handles form submission
 */
function ShowContact()
{
  // Display contact form HTML
?>
  <!DOCTYPE html>
  <html lang="en">

  <head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contact Us</title>
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
  // Handle form submission
  if (isset($_POST['submit'])) {
    SendMail(
      filter_var($_POST['sender'], FILTER_SANITIZE_EMAIL),
      $_POST['subject'],
      $_POST['body'],
    );
  }
}

/**
 * Sends an email message
 * 
 * @param string $sender Sender's email address
 * @param string $subject Email subject
 * @param string $body Email body content
 * @return bool True if email sent successfully, false otherwise
 */
function SendMail($sender, $subject, $body)
{
  global $recipient;

  // Validate email address
  if (!filter_var($sender, FILTER_VALIDATE_EMAIL)) {
    echo '<p class="error">Invalid sender email address!</p>';
    return false;
  }

  // Validate required fields
  if (empty($subject) || empty($body)) {
    echo '<p class="error">All fields are required!</p>';
    return false;
  }

  // Prepare email headers
  $headers = "From: " . $sender . "\r\n";
  $headers .= "Reply-To: " . $sender . "\r\n";
  $headers .= "MIME-Version: 1.0\r\n";
  $headers .= "Content-Type: text/html; charset=UTF-8\r\n";
  $headers .= "X-Mailer: PHP/" . phpversion();

  // Prepare HTML body
  $htmlBody = "<html><body>";
  $htmlBody .= nl2br(htmlspecialchars($body));
  $htmlBody .= "</body></html>";

  // Send email
  if (mail($recipient, $subject, $htmlBody, $headers)) {
    echo '<p class="success">Message sent successfully!</p>';
    return true;
  } else {
    echo '<p class="error">Failed to send message. Please try again later.</p>';
    return false;
  }
}

/**
 * Sends a password reminder email
 * 
 * @param string $sender Sender's email address
 * @return bool True if reminder sent successfully, false otherwise
 */
function RemindPassword($sender)
{
  global $recipient, $password;

  // Validate email address
  if (!filter_var($sender, FILTER_VALIDATE_EMAIL)) {
    echo '<p class="error">Invalid sender email address!</p>';
    return false;
  }

  $subject = "Password reminder";

  // Prepare email headers
  $headers = "From: " . $sender . "\r\n";
  $headers .= "Reply-To: " . $sender . "\r\n";
  $headers .= "MIME-Version: 1.0\r\n";
  $headers .= "Content-Type: text/html; charset=UTF-8\r\n";
  $headers .= "X-Mailer: PHP/" . phpversion();

  // Prepare HTML body
  $htmlBody = "<html><body>";
  $htmlBody .= "Password for admin panel is: " . htmlspecialchars($password);
  $htmlBody .= "</body></html>";

  // Send email
  if (mail($recipient, $subject, $htmlBody, $headers)) {
    echo '<p class="success">Password reminder sent successfully!</p>';
    return true;
  } else {
    echo '<p class="error">Failed to send password reminder. Please try again later.</p>';
    return false;
  }
}
?>

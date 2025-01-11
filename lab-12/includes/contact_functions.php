<?php
if (!isset($_SESSION)) {
    session_start();
}

/**
 * Displays the contact form
 */
function showContactForm() {
    $output = '<div class="contact-form card">
        <h2>Kontakt</h2>
        <form method="POST" action="">
            <div class="form-group">
                <label for="sender">Twój Email:</label>
                <input type="email" name="sender" id="sender" required>
            </div>

            <div class="form-group">
                <label for="subject">Temat:</label>
                <input type="text" name="subject" id="subject" required>
            </div>

            <div class="form-group">
                <label for="body">Wiadomość:</label>
                <textarea name="body" id="body" rows="5" required></textarea>
            </div>

            <div class="form-buttons">
                <button type="submit" name="submit" class="btn btn-primary">Wyślij wiadomość</button>
                <a href="index.php" class="btn btn-secondary">Powrót</a>
            </div>
        </form>
    </div>';

    return $output;
}

/**
 * Sends an email message
 */
function SendMail($sender, $subject, $body) {
    global $recipient;

    // Validate email address
    if (!filter_var($sender, FILTER_VALIDATE_EMAIL)) {
        return ['status' => 'error', 'message' => 'Nieprawidłowy adres email!'];
    }

    // Validate required fields
    if (empty($subject) || empty($body)) {
        return ['status' => 'error', 'message' => 'Wszystkie pola są wymagane!'];
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
        return ['status' => 'success', 'message' => 'Wiadomość została wysłana pomyślnie!'];
    } else {
        return ['status' => 'error', 'message' => 'Nie udało się wysłać wiadomości. Spróbuj ponownie później.'];
    }
}
?>

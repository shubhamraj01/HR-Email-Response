<?php
session_start();
require_once 'config.php';

// Redirect if no candidate data in session
if (!isset($_SESSION['candidate_data'])) {
    header("Location: index.php");
    exit();
}

$candidate = $_SESSION['candidate_data'];
$success_msg = $error_msg = '';

// Email templates
function getSelectionTemplate($name, $position) {
    return "
    <html>
    <body>
        <h2>Congratulations!</h2>
        <p>Dear $name,</p>
        <p>We are pleased to inform you that you have been selected for the position of <strong>$position</strong> at our company.</p>
        <p>Your skills and experience impressed our hiring team, and we believe you will be a valuable addition to our organization.</p>
        <p>Our HR representative will contact you shortly to discuss the next steps, including the offer details and joining date.</p>
        <p>We look forward to welcoming you to our team!</p>
        <br>
        <p>Best regards,<br>HR Department</p>
    </body>
    </html>
    ";
}

function getRejectionTemplate($name, $position) {
    return "
    <html>
    <body>
        <h2>Application Update</h2>
        <p>Dear $name,</p>
        <p>Thank you for your interest in the <strong>$position</strong> position at our company and for taking the time to interview with us.</p>
        <p>We appreciate the opportunity to learn about your skills and accomplishments. Unfortunately, after careful consideration, we have decided to move forward with another candidate whose qualifications more closely match our current needs.</p>
        <p>We were impressed with your background and encourage you to apply for future openings that align with your experience.</p>
        <p>We wish you the best in your job search and future career endeavors.</p>
        <br>
        <p>Best regards,<br>HR Department</p>
    </body>
    </html>
    ";
}

// Get email content based on status
if ($candidate['status'] == 'Selected') {
    $email_subject = "Congratulations! Job Offer for " . $candidate['position'];
    $email_content = getSelectionTemplate($candidate['name'], $candidate['position']);
} else {
    $email_subject = "Update on Your Application for " . $candidate['position'];
    $email_content = getRejectionTemplate($candidate['name'], $candidate['position']);
}

// Process send email request
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['send_email'])) {
    // Save candidate to database
    $sql = "INSERT INTO candidates (name, email, position, status, email_sent) VALUES (?, ?, ?, ?, 1)";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "ssss", $candidate['name'], $candidate['email'], $candidate['position'], $candidate['status']);
    
    if (mysqli_stmt_execute($stmt)) {
        $candidate_id = mysqli_insert_id($conn);
        
        // Send email (simulated - in production, use PHPMailer or similar)
        $to = $candidate['email'];
        $headers = "From: " . EMAIL_FROM_NAME . " <" . EMAIL_FROM . ">\r\n";
        $headers .= "Reply-To: " . EMAIL_FROM . "\r\n";
        $headers .= "Content-Type: text/html; charset=UTF-8\r\n";
        
        if (mail($to, $email_subject, $email_content, $headers)) {
            $success_msg = "Email sent successfully to " . $candidate['email'];
            
            // Clear session data
            unset($_SESSION['candidate_data']);
            
            // Redirect after 2 seconds
            header("refresh:2;url=index.php");
        } else {
            $error_msg = "Failed to send email. Please try again.";
            
            // Update database to mark email as not sent
            $update_sql = "UPDATE candidates SET email_sent = 0 WHERE id = $candidate_id";
            mysqli_query($conn, $update_sql);
        }
    } else {
        $error_msg = "Error saving candidate data: " . mysqli_error($conn);
    }
    
    mysqli_stmt_close($stmt);
}

// Go back to form
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['go_back'])) {
    header("Location: index.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Preview Email</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="container">
        <header>
            <h1>Preview Email</h1>
            <p>Review the email before sending</p>
        </header>
        
        <div class="preview-content">
            <?php
            // Display success/error messages
            if (!empty($success_msg)) {
                echo '<div class="alert alert-success">' . $success_msg . '</div>';
            }
            if (!empty($error_msg)) {
                echo '<div class="alert alert-error">' . $error_msg . '</div>';
            }
            
            if (empty($success_msg)):
            ?>
            <div class="candidate-info">
                <h2>Candidate Details</h2>
                <div class="info-grid">
                    <div class="info-item">
                        <strong>Name:</strong> <?php echo htmlspecialchars($candidate['name']); ?>
                    </div>
                    <div class="info-item">
                        <strong>Email:</strong> <?php echo htmlspecialchars($candidate['email']); ?>
                    </div>
                    <div class="info-item">
                        <strong>Position:</strong> <?php echo htmlspecialchars($candidate['position']); ?>
                    </div>
                    <div class="info-item">
                        <strong>Status:</strong> <span class="status-badge status-<?php echo strtolower($candidate['status']); ?>"><?php echo $candidate['status']; ?></span>
                    </div>
                </div>
            </div>
            
            <div class="email-preview">
                <h2>Email Preview</h2>
                <div class="email-header">
                    <p><strong>To:</strong> <?php echo htmlspecialchars($candidate['email']); ?></p>
                    <p><strong>Subject:</strong> <?php echo $email_subject; ?></p>
                </div>
                <div class="email-content">
                    <?php echo $email_content; ?>
                </div>
            </div>
            
            <div class="action-buttons">
                <form method="post" class="inline-form">
                    <button type="submit" name="go_back" class="btn btn-secondary">Edit Details</button>
                </form>
                <form method="post" class="inline-form">
                    <button type="submit" name="send_email" class="btn btn-primary">Send Email</button>
                </form>
            </div>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>
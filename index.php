<?php
session_start();
require_once 'config.php';

// Initialize variables
$name = $email = $position = $status = '';
$name_err = $email_err = $position_err = $status_err = '';
$success_msg = $error_msg = '';

// Process form data when submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Validate name
    if (empty(trim($_POST["name"]))) {
        $name_err = "Please enter candidate name.";
    } else {
        $name = trim($_POST["name"]);
    }
    
    // Validate email
    if (empty(trim($_POST["email"]))) {
        $email_err = "Please enter candidate email.";
    } elseif (!filter_var(trim($_POST["email"]), FILTER_VALIDATE_EMAIL)) {
        $email_err = "Please enter a valid email address.";
    } else {
        $email = trim($_POST["email"]);
    }
    
    // Validate position
    if (empty(trim($_POST["position"]))) {
        $position_err = "Please enter position applied for.";
    } else {
        $position = trim($_POST["position"]);
    }
    
    // Validate status
    if (empty($_POST["status"])) {
        $status_err = "Please select candidate status.";
    } else {
        $status = $_POST["status"];
    }
    
    // Check input errors before processing
    if (empty($name_err) && empty($email_err) && empty($position_err) && empty($status_err)) {
        // Store data in session for preview
        $_SESSION['candidate_data'] = [
            'name' => $name,
            'email' => $email,
            'position' => $position,
            'status' => $status
        ];
        
        // Redirect to preview page
        header("Location: preview.php");
        exit();
    }
}

// Get all candidates from database
$candidates = [];
$sql = "SELECT * FROM candidates ORDER BY created_at DESC";
$result = mysqli_query($conn, $sql);
if ($result) {
    $candidates = mysqli_fetch_all($result, MYSQLI_ASSOC);
    mysqli_free_result($result);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Candidate Response System</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="container">
        <header>
            <h1>Candidate Response System</h1>
            <p>Send email responses to job candidates</p>
        </header>
        
        <div class="main-content">
            <div class="form-section">
                <h2>Candidate Information</h2>
                
                <?php
                // Display success/error messages
                if (!empty($success_msg)) {
                    echo '<div class="alert alert-success">' . $success_msg . '</div>';
                }
                if (!empty($error_msg)) {
                    echo '<div class="alert alert-error">' . $error_msg . '</div>';
                }
                ?>
                
                <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
                    <div class="form-group">
                        <label for="name">Candidate Name *</label>
                        <input type="text" id="name" name="name" value="<?php echo $name; ?>" class="<?php echo (!empty($name_err)) ? 'is-invalid' : ''; ?>">
                        <span class="error"><?php echo $name_err; ?></span>
                    </div>
                    
                    <div class="form-group">
                        <label for="email">Candidate Email *</label>
                        <input type="email" id="email" name="email" value="<?php echo $email; ?>" class="<?php echo (!empty($email_err)) ? 'is-invalid' : ''; ?>">
                        <span class="error"><?php echo $email_err; ?></span>
                    </div>
                    
                    <div class="form-group">
                        <label for="position">Position Applied *</label>
                        <input type="text" id="position" name="position" value="<?php echo $position; ?>" class="<?php echo (!empty($position_err)) ? 'is-invalid' : ''; ?>">
                        <span class="error"><?php echo $position_err; ?></span>
                    </div>
                    
                    <div class="form-group">
                        <label>Status *</label>
                        <div class="radio-group">
                            <label class="radio-label">
                                <input type="radio" name="status" value="Selected" <?php echo ($status == 'Selected') ? 'checked' : ''; ?>>
                                <span class="radio-custom"></span>
                                Selected
                            </label>
                            <label class="radio-label">
                                <input type="radio" name="status" value="Rejected" <?php echo ($status == 'Rejected') ? 'checked' : ''; ?>>
                                <span class="radio-custom"></span>
                                Rejected
                            </label>
                        </div>
                        <span class="error"><?php echo $status_err; ?></span>
                    </div>
                    
                    <div class="form-group">
                        <button type="submit" class="btn btn-primary">Preview Email</button>
                    </div>
                </form>
            </div>
            
            <div class="candidates-section">
                <h2>Recent Candidates</h2>
                <?php if (empty($candidates)): ?>
                    <p>No candidates found.</p>
                <?php else: ?>
                    <div class="candidates-table">
                        <table>
                            <thead>
                                <tr>
                                    <th>Name</th>
                                    <th>Email</th>
                                    <th>Position</th>
                                    <th>Status</th>
                                    <th>Email Sent</th>
                                    <th>Date</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($candidates as $candidate): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($candidate['name']); ?></td>
                                        <td><?php echo htmlspecialchars($candidate['email']); ?></td>
                                        <td><?php echo htmlspecialchars($candidate['position']); ?></td>
                                        <td><span class="status-badge status-<?php echo strtolower($candidate['status']); ?>"><?php echo $candidate['status']; ?></span></td>
                                        <td><?php echo $candidate['email_sent'] ? 'Yes' : 'No'; ?></td>
                                        <td><?php echo date('M j, Y', strtotime($candidate['created_at'])); ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</body>
</html>
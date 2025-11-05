<?php
// Start session securely
if (session_status() === PHP_SESSION_NONE) {
    session_start([
        'cookie_httponly' => true,
        'cookie_secure' => isset($_SERVER['HTTPS']),
        'cookie_samesite' => 'Strict',
    ]);
}

// Include database connection
require_once __DIR__ . '/../db.php';

// Check if user is already logged in
if (isset($_SESSION['client_id']) || isset($_SESSION['admin_id']) || isset($_SESSION['vet_id'])) {
    header('Location: ../index.php');
    exit;
}

// CSRF token validation
if (!isset($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
    die('CSRF token validation failed.');
}

// Check if form is submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['register'])) {
    // Sanitize and validate inputs
    $fullname = trim($_POST['fullname']);
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $address = trim($_POST['address'] ?? ''); // Address not in form, set empty
    $contact = trim($_POST['contact']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];

    // Validation
    $errors = [];

    if (empty($fullname) || strlen($fullname) < 2) {
        $errors[] = "Full name must be at least 2 characters.";
    }

    if (empty($username) || strlen($username) < 3) {
        $errors[] = "Username must be at least 3 characters.";
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Invalid email format.";
    }

    if (!preg_match('/^09\d{9}$/', $contact)) {
        $errors[] = "Contact number must start with 09 and be 11 digits.";
    }

    if (strlen($password) < 8) {
        $errors[] = "Password must be at least 8 characters.";
    }

    if ($password !== $confirm_password) {
        $errors[] = "Passwords do not match.";
    }

    // Check if username or email already exists
    if (empty($errors)) {
        $stmt = $pdo->prepare("SELECT client_id FROM client WHERE client_username = ? OR client_email = ?");
        $stmt->execute([$username, $email]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($result) {
            $errors[] = "Username or email already exists.";
        }
    }

    if (empty($errors)) {
        // Hash password
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);

        // Insert new client
        $stmt = $pdo->prepare("INSERT INTO client (client_name, client_username, client_email, client_address, client_contact_number, client_password, updated_at) VALUES (?, ?, ?, ?, ?, ?, NOW())");
        if ($stmt->execute([$fullname, $username, $email, $address, $contact, $hashed_password])) {
            // Get the new client ID
            $client_id = $pdo->lastInsertId();

            // Auto-login the new client
            $_SESSION['client_id'] = $client_id;
            $_SESSION['username'] = $username;
            $_SESSION['client_name'] = $fullname;
            $_SESSION['client_contact'] = $contact;
            $_SESSION['role'] = 'client';

            // Log the registration/login
            require_once 'logs.php';
            logAction($pdo, $client_id, 'Registration', $fullname . ' registered and logged in', 'Client');

            // Redirect to index.php (now logged in, profile dropdown will show)
            header('Location: ../index.php?message=Registration successful. You are now logged in.');
            exit;
        } else {
            $errors[] = "Registration failed. Please try again.";
        }
    }

    // If there are errors, redirect back with errors
    if (!empty($errors)) {
        $error_message = implode('<br>', $errors);
        header('Location: ../index.php?error=' . urlencode($error_message));
        exit;
    }
} else {
    // Invalid request
    header('Location: ../index.php');
    exit;
}

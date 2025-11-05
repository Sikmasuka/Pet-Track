<?php
// functions/authentication.php

require_once __DIR__ . '/../db.php';
require_once 'logs.php';

// Initialize variables
$message = '';
$login_success = false;
$redirect_url = '';

// If already logged in, redirect
if (isset($_SESSION['admin_id'])) {
    header('Location: admin/admin-dashboard.php');
    exit;
} elseif (isset($_SESSION['vet_id'])) {
    header('Location: dashboard.php');
    exit;
}
// Clients can stay on index.php when logged in

// Process login form
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['login'])) {

    // ✅ Step 1: Validate CSRF token
    if (!isset($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
        $message = 'Invalid request. Please refresh and try again.';
        return;
    }

    // ✅ Step 2: Get inputs safely
    $username = trim($_POST['username']);
    $password = $_POST['password'];

    if ($username === '' || $password === '') {
        $message = 'Username and password are required.';
        return;
    }

    // ✅ Step 5: Check Admin, Veterinarian, and Client tables
    $stmt = $pdo->prepare("
        SELECT 'admin' AS role, admin_id AS id, admin_username AS username, admin_password AS password
        FROM Admin WHERE admin_username = :username
        UNION
        SELECT 'veterinarian' AS role, vet_id AS id, vet_username AS username, vet_password AS password
        FROM Veterinarian WHERE vet_username = :username
        UNION
        SELECT 'client' AS role, client_id AS id, client_username AS username, client_password AS password
        FROM Client WHERE client_username = :username
    ");
    $stmt->execute(['username' => $username]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user) {
        $message = 'Invalid username or password';
        return;
    }

    // ✅ Check password with password_verify() for both roles
    if (password_verify($password, $user['password'])) {

        // ✅ Remember Me (username only)
        if (!empty($_POST['remember'])) {
            // Set a secure cookie that remembers the username for 30 days
            setcookie(
                'remember_username',
                $username,
                [
                    'expires' => time() + (86400 * 30), // 30 days
                    'path' => '/',
                    'secure' => isset($_SERVER['HTTPS']), // send only over HTTPS
                    'httponly' => true, // prevent JS access
                    'samesite' => 'Strict' // prevent cross-site attacks
                ]
            );
        } else {
            // Clear cookie if "Remember Me" is unchecked
            setcookie(
                'remember_username',
                '',
                [
                    'expires' => time() - 3600,
                    'path' => '/',
                    'secure' => isset($_SERVER['HTTPS']),
                    'httponly' => true,
                    'samesite' => 'Strict'
                ]
            );
        }


        // Regenerate session ID for security
        session_regenerate_id(true);

        if ($user['role'] === 'admin') {
            // Admin login
            $_SESSION['admin_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['role'] = 'admin';

            $login_success = true;
            $redirect_url = './admin/admin-dashboard.php';

            logAction($pdo, $user['id'], 'Login', $_SESSION['username'] . ' logged in', 'Admin');
        } elseif ($user['role'] === 'veterinarian') {
            // Veterinarian login
            // Fetch vet_name for session
            $stmt2 = $pdo->prepare("SELECT vet_name FROM Veterinarian WHERE vet_id = :id");
            $stmt2->execute(['id' => $user['id']]);
            $vet = $stmt2->fetch(PDO::FETCH_ASSOC);

            $_SESSION['vet_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['vet_name'] = $vet['vet_name'] ?? '';
            $_SESSION['role'] = 'veterinarian';

            $login_success = true;
            $redirect_url = 'dashboard.php';

            logAction($pdo, $user['id'], 'Login', $_SESSION['vet_name'] . ' logged in', 'Veterinarian');
        } else {
            // Client login
            // Fetch client_name and contact for session
            $stmt2 = $pdo->prepare("SELECT client_name, client_contact_number FROM Client WHERE client_id = :id");
            $stmt2->execute(['id' => $user['id']]);
            $client = $stmt2->fetch(PDO::FETCH_ASSOC);

            $_SESSION['client_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['client_name'] = $client['client_name'] ?? '';
            $_SESSION['client_contact'] = $client['client_contact_number'] ?? '';
            $_SESSION['role'] = 'client';

            $login_success = true;
            $redirect_url = './index.php';

            logAction($pdo, $user['id'], 'Login', $_SESSION['client_name'] . ' logged in', 'Client');
        }
    } else {
        // Invalid password
        $message = 'Invalid username or password';
    }
}

// 🔴 ADD THIS PART BELOW
if ($login_success && $user['role'] !== 'client') {
    // Instead of header() redirect, show loader with smooth transition for admin and vet
    echo '
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <title>Redirecting...</title>
        <script src="https://cdn.tailwindcss.com"></script>
    </head>
    <body class="flex flex-col items-center justify-center h-screen bg-white">
        <!-- Your blinking icon -->
        <img src="image/MainIcon.png" alt="Loading Icon" class="w-20 h-20 animate-pulse">
        <p class="mt-4 text-teal-700 font-semibold text-lg">Logging you in...</p>

        <script>
            setTimeout(function() {
                window.location.href = "' . $redirect_url . '";
            }, 1500); // 1.5 sec loader
        </script>
    </body>
    </html>
    ';
    exit;
}

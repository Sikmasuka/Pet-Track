<?php
// Check if the user is already logged in
if (isset($_SESSION['admin_id'])) {
    header('Location: admin/admin-dashboard.php');
    exit;
} elseif (isset($_SESSION['vet_id'])) {
    header('Location: dashboard.php');
    exit;
}

// Start session securely (same as in authentication.php)
if (session_status() === PHP_SESSION_NONE) {
    session_start([
        'cookie_httponly' => true,
        'cookie_secure' => isset($_SERVER['HTTPS']),
        'cookie_samesite' => 'Strict',
    ]);
}

// Generate CSRF token if missing
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$_SESSION['last_activity'] = time();
$_SESSION['expire_time'] = 3600; // 1 huor


// Include authentication script
require_once 'functions/authentication.php';
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" href="image/MainIcon.png" type="image/x-icon">
    <title>Pet Track | Login</title>

    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="Assets/Extension.js"></script>
</head>

<body class="bg-gradient-to-r from-emerald-400 via-teal-500 to-emerald-700">

    <!-- Main Section -->
    <main class="py-5 min-h-screen flex items-center justify-center px-4 sm:px-6">
        <!-- Login Box -->
        <div class="flex flex-col md:flex-row w-full max-w-3xl bg-white rounded-2xl shadow-lg overflow-hidden">

            <!-- Left: Logo + Description (visible on mobile & desktop) -->
            <div class="flex flex-col justify-center items-center w-full md:w-1/2 p-6 text-center bg-gray-50 md:bg-transparent">
                <img src="image/MainIcon.png" alt="Logo" class="w-14 sm:w-16 mb-3">
                <h1 class="text-2xl sm:text-3xl font-bold text-green-700">PET TRACK</h1>
                <p class="text-sm sm:text-lg text-gray-600 mt-2">
                    Balingasag Dog and Cat clinic management system
                </p>
            </div>

            <!-- Divider (only on desktop) -->
            <div class="hidden md:block w-px bg-gray-200"></div>

            <!-- Right: Login Form -->
            <div class="w-full md:w-1/2 p-6 sm:p-8 flex flex-col justify-center">
                <h2 class="text-base sm:text-lg font-bold text-gray-800 text-center mb-1">Welcome Back!</h2>
                <p class="text-xs sm:text-sm text-gray-500 text-center mb-5">Login to continue</p>

                <!-- Error message -->
                <?php if (isset($message) && $message): ?>
                    <p id="errorMessage" class="rounded-sm w-full bg-red-100 p-2 text-red-600 text-xs text-center mb-4">
                        <?php echo htmlspecialchars($message); ?>
                    </p>
                <?php endif; ?>

                <form action="index.php" method="POST" class="space-y-4">
                    <!-- Username -->
                    <div>
                        <label for="username" class="block text-xs font-semibold text-gray-600 mb-1">Username</label>
                        <div class="relative">
                            <input type="text" id="username" name="username"
                                value="<?= htmlspecialchars($_COOKIE['remember_username'] ?? '') ?>"
                                class="w-full px-3 py-2 border rounded-md focus:outline-none focus:ring-2 focus:ring-green-500 text-sm"
                                placeholder="Enter your username" required>

                            <span class="absolute right-3 top-1/2 transform -translate-y-1/2 text-gray-400">
                                <i class="fa fa-user"></i>
                            </span>
                        </div>
                    </div>

                    <!-- Password -->
                    <div>
                        <label for="password" class="block text-xs font-semibold text-gray-600 mb-1">Password</label>
                        <div class="relative">
                            <input type="password" id="password" name="password"
                                class="w-full px-3 py-2 border rounded-md focus:outline-none focus:ring-2 focus:ring-green-500 pr-10 text-sm"
                                placeholder="Enter your password" required>
                            <button type="button" id="togglePassword"
                                class="absolute right-3 top-1/2 transform -translate-y-1/2 text-gray-400 hover:text-gray-600">
                                <i class="fas fa-eye"></i>
                            </button>
                        </div>
                        <p id="passwordError" class="text-red-500 text-xs mt-1"></p>
                    </div>

                    <!-- Remember + Forgot -->
                    <div class="flex flex-col sm:flex-row items-center justify-between gap-2 sm:gap-0 text-xs">
                        <label class="flex items-center text-gray-600">
                            <input type="checkbox" name="remember" class="mr-2"
                                <?php if (isset($_COOKIE['remember_username'])) echo 'checked'; ?>> Remember Me
                    </div>

                    <!-- 🔒 CSRF protection -->
                    <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">

                    <!-- Login Button -->
                    <button type="submit" name="login"
                        class="w-full bg-green-600 hover:bg-green-700 text-white font-bold py-2 rounded-md text-sm transition duration-200">
                        Login
                    </button>
                </form>
            </div>
        </div>
    </main>

    <script src="./js/auth.js"></script>
    <script>
        // SweetAlert2 success message
        if (typeof Swal !== 'undefined') {
            <?php if ($login_success): ?>
                Swal.fire({
                    icon: 'success',
                    title: 'Login Successful!',
                    text: 'Redirecting to your dashboard...',
                    confirmButtonColor: '#3085d6',
                    timer: 1500,
                    showConfirmButton: false,
                    timerProgressBar: true
                }).then(() => {
                    window.location.href = '<?php echo $redirect_url; ?>';
                });
            <?php endif; ?>
        }

        document.addEventListener('DOMContentLoaded', function() {
            const errorMsg = document.getElementById("errorMessage");
            if (errorMsg) {
                setTimeout(() => {
                    errorMsg.style.transition = "opacity 0.5s ease";
                    errorMsg.style.opacity = "0";
                    setTimeout(() => errorMsg.remove(), 500);
                }, 3000);
            }
        });
    </script>
</body>

</html>
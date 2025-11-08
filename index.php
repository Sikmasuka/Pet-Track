<?php
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
$_SESSION['expire_time'] = 3600; // 1 hour
// Include authentication script
require_once 'functions/authentication.php';

// Check if the user is already logged in or just logged in
if (isset($_SESSION['admin_id'])) {
    header('Location: admin/admin-dashboard.php');
    exit;
} elseif (isset($_SESSION['vet_id'])) {
    header('Location: dashboard.php');
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <link rel="icon" href="image/MainIcon.png" type="image/x-icon" />
    <title>PetTrack - Balingasag Dog and Cat Clinic</title>
    <script src="Assets/chart.js"></script>
    <link rel="stylesheet" href="Assets/FontAwsome/css/all.min.css" />
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="./css/landing-page.css" />
</head>
<style>
    /* Custom styles for calendar day indicators */
    .day-cell {
        position: relative;
        transition: background-color 0.2s;
    }

    .day-cell.has-appointments {
        background-color: rgba(40, 167, 69, 0.1);
        /* Light green */
    }

    .day-cell.full-day {
        background-color: rgba(220, 53, 69, 0.1);
        /* Light red */
    }

    .day-cell.has-appointments .day-number {
        font-weight: bold;
        color: #166534;
        /* Darker green */
    }

    .day-cell.full-day .day-number {
        font-weight: bold;
        color: #b91c1c;
        /* Darker red */
    }

    .appointment-indicator {
        position: absolute;
        top: 4px;
        right: 4px;
        width: 8px;
        height: 8px;
        border-radius: 50%;
        z-index: 2;
    }

    /* Legend styles */
    .calendar-legend {
        display: flex;
        gap: 1rem;
        margin-bottom: 0.75rem;
        flex-wrap: wrap;
        justify-content: center;
    }

    .legend-item {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        font-size: 0.75rem;
    }

    .legend-circle {
        width: 10px;
        height: 10px;
        border-radius: 50%;
    }

    .legend-has-appointments {
        background-color: #28a745;
        /* Green */
    }

    .legend-full {
        background-color: #dc3545;
        /* Red */
    }
</style>

<body>
    <!-- Header -->
    <header class="fixed top-0 left-0 w-full h-16 z-50 bg-white shadow-sm">
        <div class="flex items-center justify-between h-full px-4 md:px-8 max-w-7xl mx-auto">
            <!-- Logo -->
            <div class="flex items-center">
                <img src="./image/logo.png" alt="PetTrack Logo" class="w-16 h-14 object-contain">
                <p class="text-[#169976] font-bold text-2xl md:text-3xl tracking-tight">PetTrack</p>
            </div>
            <!-- Navigation Links -->
            <nav class="hidden md:flex items-center gap-8">
                <a class="text-gray-700 font-medium hover:text-[#169976] transition-colors" href="#">Home</a>
                <a class="text-gray-700 font-medium hover:text-[#169976] transition-colors" href="#about">About</a>
                <a class="text-gray-700 font-medium hover:text-[#169976] transition-colors" href="#services">Services</a>
                <a class="text-gray-700 font-medium hover:text-[#169976] transition-colors" href="#contacts">Contact</a>
            </nav>
            <!-- Right Side Buttons -->
            <div class="flex items-center gap-3">
                <?php if (isset($_SESSION['client_id'])): ?>
                    <!-- 👤 Profile Dropdown -->
                    <div class="relative inline-block text-left">
                        <button id="profileButton"
                            class="flex items-center justify-center w-10 h-10 bg-gray-100 border border-gray-200 rounded-full hover:bg-gray-200 text-gray-800 text-lg transition-colors">
                            <i class="fas fa-user"></i>
                        </button>
                        <div id="dropdownMenu"
                            class="origin-top-right absolute right-0 mt-2 w-72 rounded-lg shadow-lg bg-white ring-1 ring-black ring-opacity-5 opacity-0 scale-95 pointer-events-none transition-all duration-200 ease-out z-50 border border-slate-200">
                            <!-- Header -->
                            <div class="px-4 py-3 border-b border-slate-200">
                                <div class="flex items-center gap-3">
                                    <div
                                        class="flex items-center justify-center w-12 h-12 rounded-full border-2 border-[#169976] bg-gray-100 text-[#169976] text-xl">
                                        <i class="fas fa-user"></i>
                                    </div>
                                    <div>
                                        <p class="text-sm font-semibold text-gray-800">
                                            <?= htmlspecialchars($_SESSION['username'] ?? 'Client') ?>
                                        </p>
                                        <p class="text-xs text-gray-500">Client</p>
                                    </div>
                                </div>
                            </div>
                            <!-- Links -->
                            <div class="py-1">
                                <a href="#" id="editProfileLink" class="flex items-center gap-3 px-4 py-3 text-sm text-gray-600 hover:bg-gray-100 hover:text-gray-800 transition-colors duration-150">
                                    <i class="fas fa-edit text-indigo-400"></i>
                                    <div>
                                        <div class="font-medium">Edit Profile</div>
                                        <div class="text-xs text-gray-500">Update your information</div>
                                    </div>
                                </a>
                                <hr class="my-1 border-slate-200">
                                <a href="#" onclick="confirmLogout(event)"
                                    class="flex items-center gap-3 px-4 py-3 text-sm text-red-500 hover:bg-gray-100 transition-colors duration-150">
                                    <i class="fas fa-sign-out-alt text-red-500"></i>
                                    <div>
                                        <div class="font-medium">Logout</div>
                                        <div class="text-xs text-red-600">Sign out of your account</div>
                                    </div>
                                </a>
                            </div>
                        </div>
                    </div>
                <?php else: ?>
                    <!-- 🔑 Not logged in — show buttons -->
                    <button onclick="openModal('loginModal')"
                        class="border border-[#169976] text-[#169976] hover:bg-[#169976] hover:text-white transition-colors px-3 py-1.5 md:px-5 md:py-2 rounded-md text-sm md:text-base font-medium">
                        Login
                    </button>
                    <button onclick="openModal('registerModal')"
                        class="bg-[#169976] hover:bg-[#128565] text-white font-medium text-sm md:text-base px-3 py-1.5 md:px-5 md:py-2 rounded-md transition-colors">
                        Register
                    </button>
                <?php endif; ?>
                <!-- Mobile Menu Button -->
                <button id="mobile-menu-button" class="md:hidden text-gray-700 focus:outline-none">
                    <i class="fas fa-bars text-2xl"></i>
                </button>
            </div>
        </div>
        <!-- Mobile Menu -->
        <div id="mobileMenu" class="hidden flex-col md:hidden bg-white shadow-md py-4 px-6 space-y-4 text-center">
            <a class="block text-gray-700 font-medium hover:text-[#169976] transition-colors" href="#">Home</a>
            <a class="block text-gray-700 font-medium hover:text-[#169976] transition-colors" href="#about">About</a>
            <a class="block text-gray-700 font-medium hover:text-[#169976] transition-colors" href="#services">Services</a>
            <a class="block text-gray-700 font-medium hover:text-[#169976] transition-colors" href="#contacts">Contact</a>
            <button onclick="openModal('<?php echo isset($_SESSION['client_id']) ? 'appointmentModal' : 'loginModal'; ?>')"
                class="w-full bg-gray-100 text-gray-800 border border-gray-300 px-4 py-2 rounded-md font-medium hover:bg-gray-200 transition-colors">
                Schedule Appointment
            </button>
            <?php if (isset($_SESSION['client_id'])): ?>
                <a href="#" onclick="confirmLogout(event)"
                    class="w-full bg-red-500 text-white font-medium px-4 py-2 rounded-md transition-colors">
                    Logout
                </a>
            <?php else: ?>
                <button onclick="openModal('loginModal')"
                    class="w-full border border-[#169976] text-[#169976] hover:bg-[#169976] hover:text-white transition-colors px-4 py-2 rounded-md font-medium">
                    Login
                </button>
                <button onclick="openModal('registerModal')"
                    class="w-full bg-[#169976] hover:bg-[#128565] text-white font-medium px-4 py-2 rounded-md transition-colors">
                    Register
                </button>
            <?php endif; ?>
        </div>
    </header>
    <!-- Hero Section -->
    <section
        class="hero-section flex items-center pt-24 px-4 md:px-8 bg-cover bg-center bg-no-repeat relative"
        style="background-image: url('./image/HeroBanner.png');">
        <!-- Overlay -->
        <div class="absolute inset-0 bg-black/50"></div>
        <div class="max-w-7xl mx-auto w-full relative z-10">
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-12 items-center">
                <div class="hero-content text-white">
                    <div class="inline-block bg-white/10 backdrop-blur-sm px-4 py-2 rounded-full mb-6">
                        <p class="text-sm font-medium"><img src="./image/MainIcon.png" alt="Award" class="w-4 h-4 inline mr-1"> Efficient Pet Record Management</p>
                    </div>
                    <h1 class="text-4xl md:text-6xl font-extrabold leading-tight mb-6">
                        Streamline Pet Care at Balingasag Dog and Cat Clinic
                    </h1>
                    <p class="text-lg md:text-xl opacity-90 mb-8 leading-relaxed">
                        PetTrack addresses challenges in managing pet medical records by centralizing data, reducing errors, and ensuring timely access for better pet care.
                    </p>
                    <div class="flex flex-col sm:flex-row gap-4">
                        <button onclick="<?php echo isset($_SESSION['client_id']) ? 'appointmentModal()' : 'openModal(\'loginModal\')'; ?>" class="btn-secondary">
                            Schedule Appointment
                        </button>
                        <a href="#services" class="btn-primary text-center">
                            Explore Features
                        </a>
                    </div>
                </div>
                <div class="hero-image-container hidden lg:block">
                    <img src="./image/dog-cat.png" alt="Happy Pets" class="w-full max-w-2xl mx-auto drop-shadow-2xl" />
                </div>
            </div>
        </div>
    </section>
    <!-- Stats Section -->
    <section class="stats-section py-16 md:py-24">
        <div class="max-w-7xl mx-auto px-4">
            <div class="grid grid-cols-2 md:grid-cols-4 gap-8">
                <div class="stat-card">
                    <span class="stat-number">Efficient</span>
                    <p class="text-white/90 font-medium">Record Management</p>
                </div>
                <div class="stat-card">
                    <span class="stat-number">Secure</span>
                    <p class="text-white/90 font-medium">Data Storage</p>
                </div>
                <div class="stat-card">
                    <span class="stat-number">Easy</span>
                    <p class="text-white/90 font-medium">Access & Updates</p>
                </div>
                <div class="stat-card">
                    <span class="stat-number">Local</span>
                    <p class="text-white/90 font-medium">Clinic Focus</p>
                </div>
            </div>
        </div>
    </section>
    <!-- Services Section -->
    <section id="services" class="py-20 md:py-32 bg-white">
        <div class="max-w-7xl mx-auto px-4">
            <div class="text-center mb-16">
                <h2 class="section-title">System Features</h2>
                <p class="section-subtitle">
                    Key capabilities designed to improve veterinary clinic operations and pet care
                </p>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-8">
                <div class="service-card">
                    <div class="service-icon">
                        <i class="fas fa-file-medical text-white text-2xl"></i>
                    </div>
                    <h3 class="text-xl font-bold text-gray-800 mb-3 text-center">Medical Records</h3>
                    <p class="text-gray-600 text-center leading-relaxed">
                        Centralized storage and management of pet medical histories and treatments.
                    </p>
                </div>
                <div class="service-card">
                    <div class="service-icon">
                        <i class="fas fa-calendar-check text-white text-2xl"></i>
                    </div>
                    <h3 class="text-xl font-bold text-gray-800 mb-3 text-center">Appointments</h3>
                    <p class="text-gray-600 text-center leading-relaxed">
                        Easy booking and management of client appointments.
                    </p>
                </div>
                <div class="service-card">
                    <div class="service-icon">
                        <i class="fas fa-users-cog text-white text-2xl"></i>
                    </div>
                    <h3 class="text-xl font-bold text-gray-800 mb-3 text-center">User Management</h3>
                    <p class="text-gray-600 text-center leading-relaxed">
                        Admin control over user accounts for vets and staff.
                    </p>
                </div>
                <div class="service-card">
                    <div class="service-icon">
                        <i class="fas fa-chart-bar text-white text-2xl"></i>
                    </div>
                    <h3 class="text-xl font-bold text-gray-800 mb-3 text-center">Basic Reporting</h3>
                    <p class="text-gray-600 text-center leading-relaxed">
                        Generate reports on records and transactions.
                    </p>
                </div>
            </div>
        </div>
    </section>
    <!-- About Section -->
    <section id="about" class="about-section py-20 md:py-32">
        <div class="max-w-7xl mx-auto px-4">
            <div class="text-center mb-16">
                <h2 class="section-title">About PetTrack</h2>
                <p class="section-subtitle">
                    Web-based management system for Balingasag Dog and Cat Clinic, addressing record-keeping challenges.
                </p>
            </div>
            <!-- Why Choose Us -->
            <div class="mb-16">
                <h3 class="text-3xl font-bold text-gray-800 mb-12 text-center">Significance of PetTrack</h3>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                    <div class="feature-card">
                        <div class="feature-icon">
                            <i class="fas fa-shield-alt text-[#169976] text-2xl"></i>
                        </div>
                        <h4 class="text-xl font-semibold text-gray-800 mb-3">For Clinic Staff</h4>
                        <p class="text-gray-600 leading-relaxed">
                            Easier updates to pet records, less paperwork, more time for care.
                        </p>
                    </div>
                    <div class="feature-card">
                        <div class="feature-icon">
                            <i class="fas fa-clock text-[#169976] text-2xl"></i>
                        </div>
                        <h4 class="text-xl font-semibold text-gray-800 mb-3">For Pet Owners</h4>
                        <p class="text-gray-600 leading-relaxed">
                            Convenient online booking and improved communication for better pet health.
                        </p>
                    </div>
                    <div class="feature-card">
                        <div class="feature-icon">
                            <i class="fas fa-heart text-[#169976] text-2xl"></i>
                        </div>
                        <h4 class="text-xl font-semibold text-gray-800 mb-3">For Communities</h4>
                        <p class="text-gray-600 leading-relaxed">
                            Enhances overall pet health, supporting public health in local areas.
                        </p>
                    </div>
                </div>
            </div>
            <!-- Team -->
            <div>
                <h3 class="text-3xl font-bold text-gray-800 mb-12 text-center">Project Team</h3>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                    <div class="team-card">
                        <div class="team-image flex items-center justify-center">
                            <i class="fas fa-user-circle text-white text-8xl opacity-50"></i>
                        </div>
                        <div class="p-6 text-center">
                            <h4 class="text-xl font-bold text-gray-800 mb-2">Michaela G. Abecia</h4>
                            <p class="text-[#169976] font-semibold mb-3">Project Manager</p>
                            <p class="text-gray-600 text-sm">
                                Information Technology Student at St. Rita’s College
                            </p>
                        </div>
                    </div>
                    <div class="team-card">
                        <div class="team-image flex items-center justify-center">
                            <i class="fas fa-user-circle text-white text-8xl opacity-50"></i>
                        </div>
                        <div class="p-6 text-center">
                            <h4 class="text-xl font-bold text-gray-800 mb-2">Jan Paul Michael Dela Cera</h4>
                            <p class="text-[#169976] font-semibold mb-3">Project Developer</p>
                            <p class="text-gray-600 text-sm">
                                Information Technology Student at St. Rita’s College
                            </p>
                        </div>
                    </div>
                    <div class="team-card">
                        <div class="team-image flex items-center justify-center">
                            <i class="fas fa-user-circle text-white text-8xl opacity-50"></i>
                        </div>
                        <div class="p-6 text-center">
                            <h4 class="text-xl font-bold text-gray-800 mb-2">Sheryl Mae Lozano</h4>
                            <p class="text-[#169976] font-semibold mb-3">Project Designer</p>
                            <p class="text-gray-600 text-sm">
                                Information Technology Student at St. Rita’s College
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
    <!-- CTA Section -->
    <section class="cta-section py-20 md:py-32 text-white relative">
        <div class="max-w-4xl mx-auto px-4 text-center relative z-10">
            <h2 class="text-3xl md:text-5xl font-extrabold mb-6">
                Ready to Streamline Your Pet Care?
            </h2>
            <p class="text-xl mb-10 opacity-90 leading-relaxed">
                Join Balingasag Dog and Cat Clinic in using PetTrack for efficient management and superior pet health services.
            </p>
            <button onclick="openModal('loginModal')" class="btn-secondary text-lg px-8 py-4">
                Book Your Appointment Today
            </button>
        </div>
    </section>
    <!-- Footer -->
    <footer id="contacts" class="py-16 text-white">
        <div class="max-w-7xl mx-auto px-4">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-12 mb-8">
                <div>
                    <div class="flex items-center gap-3 mb-4">
                        <div class="w-10 h-10 bg-white/10 rounded-xl flex items-center justify-center">
                            <img src="./image/MainIcon.png" alt="Paw" class="w-6 h-6 inline">
                        </div>
                        <p class="text-white font-bold text-2xl">PetTrack</p>
                    </div>
                    <p class="mb-6 text-white/80 leading-relaxed">Management system for Balingasag Dog and Cat Clinic</p>
                    <div class="space-y-3">
                        <div class="flex items-center gap-3 footer-link">
                            <i class="fas fa-phone-alt"></i>
                            <span>(123) 456-7890</span>
                        </div>
                        <div class="flex items-center gap-3 footer-link">
                            <i class="fas fa-envelope"></i>
                            <span>@pettrack.com</span>
                        </div>
                        <div class="flex items-center gap-3 footer-link">
                            <a href="https://vetphilippines.com/misamis-oriental/balingasag/balingasag-dog-and-cat-clinic-2/" target="_blank" class="flex items-center gap-2 text-white-600 hover:text-white-800">
                                <i class="fas fa-info-circle"></i>
                                For more info visit vetphilippines.com
                            </a>
                        </div>
                    </div>
                </div>
                <div>
                    <h3 class="text-xl font-bold mb-4">Our Location</h3>
                    <div class="flex items-start gap-3 mb-6">
                        <i class="fas fa-map-marker-alt mt-1"></i>
                        <p class="text-white/80 leading-relaxed">
                            PQXJ+Q9J, Butuan - Cagayan de Oro - Iligan Rd, Balingasag, Misamis Oriental
                        </p>
                    </div>
                    <div class="flex gap-4">
                        <a href="https://www.facebook.com/JP.delacera.78/" class="w-10 h-10 bg-white/10 rounded-lg flex items-center justify-center hover:bg-white/20 transition-all">
                            <i class="fab fa-facebook-f"></i>
                        </a>
                        <a href="https://www.instagram.com/explore/locations/104208107917496/balingasag-dog-and-cat-clinic/" class="w-10 h-10 bg-white/10 rounded-lg flex items-center justify-center hover:bg-white/20 transition-all">
                            <i class="fab fa-instagram"></i>
                        </a>
                        <a href="#" class="w-10 h-10 bg-white/10 rounded-lg flex items-center justify-center hover:bg-white/20 transition-all">
                            <i class="fab fa-twitter"></i>
                        </a>
                    </div>
                </div>
                <div>
                    <h3 class="text-xl font-bold mb-4">Find Us</h3>
                    <div class="rounded-xl overflow-hidden h-48 shadow-lg">
                        <iframe src="https://www.google.com/maps/embed?pb=!1m14!1m8!1m3!1d1262.921952432691!2d124.78045648758747!3d8.74964320210998!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x32ffe1fbecdd99ad%3A0x73cf6beb3b523f24!2sBalingasag%20Dog%20And%20Cat%20Clinic!5e1!3m2!1sen!2sph!4v1755610325757!5m2!1sen!2sph" width="100%" height="100%" style="border:0;" allowfullscreen="" loading="lazy" referrerpolicy="no-referrer-when-downgrade"></iframe>
                    </div>
                </div>
            </div>
            <div class="border-t border-white/10 pt-8 text-center">
                <p class="text-white/60">© 2025 PetTrack. All rights reserved. Developed by St. Rita’s College of Balingasag, Inc.</p>
            </div>
        </div>
    </footer>
    <!-- 🔒 LOGIN MODAL -->
    <div id="loginModal" class="hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
        <div class="bg-white rounded-2xl shadow-xl w-full max-w-md p-6 relative">
            <button onclick="closeModal('loginModal')" class="absolute top-3 right-3 text-gray-400 hover:text-gray-600">
                <i class="fa fa-times text-lg"></i>
            </button>
            <div class="text-center mb-4">
                <img src="image/logo.png" alt="PetTrack Logo" class="w-14 mx-auto mb-2">
                <h2 class="text-2xl font-bold text-[#169976]">Welcome Back!</h2>
                <p class="text-sm text-gray-500">Login to continue</p>
            </div>
            <!-- Error message -->
            <?php if (isset($message) && $message): ?>
                <p id="errorMessage" class="rounded-sm w-full bg-red-100 p-2 text-red-600 text-xs text-center mb-4">
                    <?php echo htmlspecialchars($message); ?>
                </p>
            <?php endif; ?>
            <!-- Success message for registration -->
            <?php if (isset($_GET['message'])): ?>
                <p class="rounded-sm w-full bg-green-100 p-2 text-green-600 text-xs text-center mb-4">
                    <?php echo htmlspecialchars($_GET['message']); ?>
                </p>
            <?php endif; ?>
            <!-- Error message for registration -->
            <?php if (isset($_GET['error'])): ?>
                <p class="rounded-sm w-full bg-red-100 p-2 text-red-600 text-xs text-center mb-4">
                    <?php echo htmlspecialchars($_GET['error']); ?>
                </p>
            <?php endif; ?>
            <form action="index.php" method="POST" class="space-y-4">
                <div>
                    <label for="username" class="block text-xs font-semibold text-gray-600 mb-1">Username</label>
                    <div class="relative">
                        <input type="text" id="username" name="username"
                            value="<?= htmlspecialchars($_COOKIE['remember_username'] ?? '') ?>"
                            class="w-full px-3 py-2 border rounded-md focus:outline-none focus:ring-2 focus:ring-[#169976] text-sm"
                            placeholder="Enter your username" required>
                        <span class="absolute right-3 top-1/2 transform -translate-y-1/2 text-gray-400">
                            <i class="fa fa-user"></i>
                        </span>
                    </div>
                </div>
                <div>
                    <label for="password" class="block text-xs font-semibold text-gray-600 mb-1">Password</label>
                    <div class="relative">
                        <input type="password" id="password" name="password"
                            class="w-full px-3 py-2 border rounded-md focus:outline-none focus:ring-2 focus:ring-[#169976] pr-10 text-sm"
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
                    </label>
                </div>
                <!-- 🔒 CSRF token -->
                <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
                <button type="submit" name="login"
                    class="w-full bg-[#169976] hover:bg-[#128565] text-white font-bold py-2 rounded-md text-sm transition duration-200">
                    Login
                </button>
            </form>
            <p class="text-sm text-center text-gray-600 mt-3">
                Don't have an account?
                <a href="#" onclick="switchModal('loginModal', 'registerModal')" class="text-[#169976] hover:underline">
                    Register here
                </a>
            </p>
        </div>
    </div>
    <!-- 🐾 REGISTER MODAL -->
    <div id="registerModal" class="hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
        <div class="bg-white rounded-2xl shadow-xl w-full max-w-2xl h-[85vh] overflow-y-auto p-6 relative">
            <!-- ❌ Close Button -->
            <button onclick="closeModal('registerModal')" class="absolute top-3 right-3 text-gray-400 hover:text-gray-600">
                <i class="fa fa-times text-lg"></i>
            </button>
            <!-- 🐶 Header -->
            <div class="text-center mb-4 mt-2">
                <img src="image/logo.png" alt="PetTrack Logo" class="w-14 mx-auto mb-2">
                <h2 class="text-2xl font-bold text-[#169976]">Create an Account</h2>
                <p class="text-sm text-gray-500">Join PetTrack and start managing your pet’s care</p>
            </div>
            <!-- 📝 Register Form -->
            <form action="./functions/register.php" method="POST" class="grid grid-cols-1 md:grid-cols-2 gap-4 pb-4">
                <!-- Full Name -->
                <div class="col-span-1">
                    <label for="fullname" class="block text-xs font-semibold text-gray-600 mb-1">Full Name</label>
                    <div class="relative">
                        <input type="text" id="fullname" name="fullname"
                            class="w-full px-3 py-2 border rounded-md focus:outline-none focus:ring-2 focus:ring-[#169976] text-sm"
                            placeholder="Enter your full name" required>
                        <span class="absolute right-3 top-1/2 transform -translate-y-1/2 text-gray-400">
                            <i class="fa fa-user"></i>
                        </span>
                    </div>
                </div>
                <!-- Username -->
                <div class="col-span-1">
                    <label for="username" class="block text-xs font-semibold text-gray-600 mb-1">Username</label>
                    <div class="relative">
                        <input type="text" id="username" name="username"
                            class="w-full px-3 py-2 border rounded-md focus:outline-none focus:ring-2 focus:ring-[#169976] text-sm"
                            placeholder="Choose a username" required>
                        <span class="absolute right-3 top-1/2 transform -translate-y-1/2 text-gray-400">
                            <i class="fa fa-id-badge"></i>
                        </span>
                    </div>
                </div>
                <!-- Email -->
                <div class="col-span-1">
                    <label for="email" class="block text-xs font-semibold text-gray-600 mb-1">Email</label>
                    <div class="relative">
                        <input type="email" id="email" name="email"
                            class="w-full px-3 py-2 border rounded-md focus:outline-none focus:ring-2 focus:ring-[#169976] text-sm"
                            placeholder="Enter your email address" required>
                        <span class="absolute right-3 top-1/2 transform -translate-y-1/2 text-gray-400">
                            <i class="fa fa-envelope"></i>
                        </span>
                    </div>
                </div>
                <!-- Contact Number -->
                <div class="col-span-1">
                    <label for="contact" class="block text-xs font-semibold text-gray-600 mb-1">Contact Number</label>
                    <div class="relative">
                        <input type="text" id="contact" name="contact"
                            class="w-full px-3 py-2 border rounded-md focus:outline-none focus:ring-2 focus:ring-[#169976] text-sm"
                            placeholder="Enter your contact number" required>
                        <span class="absolute right-3 top-1/2 transform -translate-y-1/2 text-gray-400">
                            <i class="fa fa-phone"></i>
                        </span>
                    </div>
                </div>
                <!-- Password -->
                <div class="col-span-1">
                    <label for="password" class="block text-xs font-semibold text-gray-600 mb-1">Password</label>
                    <div class="relative">
                        <input type="password" id="registerPassword" name="password"
                            class="w-full px-3 py-2 border rounded-md focus:outline-none focus:ring-2 focus:ring-[#169976] pr-10 text-sm"
                            placeholder="Create a password" required>
                        <button type="button" id="toggleRegisterPassword"
                            class="absolute right-3 top-1/2 transform -translate-y-1/2 text-gray-400 hover:text-gray-600">
                            <i class="fas fa-eye"></i>
                        </button>
                    </div>
                    <!-- Password strength bar -->
                    <div class="w-full h-2 bg-gray-200 rounded mt-2 overflow-hidden">
                        <div id="registerPasswordStrengthBar" class="h-2 rounded transition-all duration-300"></div>
                    </div>
                    <p id="registerPasswordStrengthText" class="text-xs mt-1"></p>
                    <p id="registerPasswordError" class="text-red-500 text-xs mt-1"></p>
                </div>
                <!-- Confirm Password -->
                <div class="col-span-1">
                    <label for="confirm_password" class="block text-xs font-semibold text-gray-600 mb-1">Confirm Password</label>
                    <div class="relative">
                        <input type="password" id="confirm_password" name="confirm_password"
                            class="w-full px-3 py-2 border rounded-md focus:outline-none focus:ring-2 focus:ring-[#169976] pr-10 text-sm"
                            placeholder="Re-enter your password" required>
                        <button type="button" id="toggleConfirmPassword"
                            class="absolute right-3 top-1/2 transform -translate-y-1/2 text-gray-400 hover:text-gray-600">
                            <i class="fas fa-eye"></i>
                        </button>
                    </div>
                    <p id="confirmPasswordError" class="text-red-500 text-xs mt-1"></p>
                </div>
                <!-- 🛡️ CSRF Token -->
                <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
                <!-- Register Button (Full Width) -->
                <div class="col-span-2 mt-2">
                    <button type="submit" name="register"
                        class="w-full bg-[#169976] hover:bg-[#128565] text-white font-bold py-2 rounded-md text-sm transition duration-200">
                        Register
                    </button>
                </div>
            </form>
            <!-- 🔄 Switch to Login -->
            <p class="text-sm text-center text-gray-600 mt-2 mb-2">
                Already have an account?
                <a href="#" onclick="switchModal('registerModal', 'loginModal')" class="text-[#169976] hover:underline">
                    Login here
                </a>
            </p>
        </div>
    </div>
    <!-- Appointment Modal - UPDATED WITH PREFILLED NAME, NEW INPUTS, AND TIME SLOTS -->
    <div id="appointmentModal" class="fixed inset-0 bg-black bg-opacity-50 z-50 hidden flex items-center justify-center" role="dialog" aria-labelledby="modalTitle" aria-modal="true">
        <div class="bg-white w-full max-w-2xl mx-4 h-[95vh] rounded-xl shadow-xl flex flex-col" tabindex="-1">
            <!-- Header -->
            <div class="bg-[#169976] px-6 py-4 rounded-t-xl flex justify-between items-center sticky top-0 z-10">
                <h2 id="modalTitle" class="text-xl font-semibold text-white text-center w-full">Book an Appointment</h2>
            </div>
            <!-- Form -->
            <form id="appointmentForm" method="POST" action="./functions/appointment-handler.php" class="flex flex-col justify-between flex-1 p-6 overflow-y-auto">
                <!-- Content area: Calendar + Inputs -->
                <div class="grid grid-cols-1 gap-6">
                    <!-- Right: Form fields -->
                    <div class="flex flex-col space-y-6">
                        <!-- Owner Information -->
                        <div class="space-y-4">
                            <h4 class="text-base font-semibold text-gray-800">Owner Information</h4>
                            <div>
                                <label for="owner" class="block text-sm font-medium text-gray-700">Owner Name</label>
                                <input type="text" id="owner" name="owner_name" value="<?= htmlspecialchars($_SESSION['username'] ?? '') ?>" placeholder="Enter Full Name" required
                                    class="mt-1 p-2 text-sm block w-full rounded-md border border-gray-300 shadow-sm focus:ring-[#169976] focus:border-[#169976]" />
                            </div>
                            <div>
                                <label for="address" class="block text-sm font-medium text-gray-700">Address</label>
                                <input type="text" id="address" name="address" placeholder="Enter Address" required
                                    class="mt-1 p-2 text-sm block w-full rounded-md border border-gray-300 shadow-sm focus:ring-[#169976] focus:border-[#169976]" />
                            </div>
                            <div>
                                <label for="contact" class="block text-sm font-medium text-gray-700">Contact Number</label>
                                <input type="text" id="contact" name="contact_number" required
                                    pattern="^09\d{9}$"
                                    maxlength="11"
                                    placeholder="e.g. 09171234567"
                                    value="<?= htmlspecialchars($_SESSION['client_contact'] ?? '') ?>"
                                    title="Enter a valid Philippine number starting with 09 and 11 digits long"
                                    class="mt-1 p-2 text-sm block w-full rounded-md border border-gray-300 shadow-sm focus:ring-[#169976] focus:border-[#169976]" />
                            </div>
                        </div>
                        <!-- Pet Information -->
                        <div class="space-y-4">
                            <h4 class="text-base font-semibold text-gray-800">Pet Information</h4>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label for="pet_name" class="block text-sm font-medium text-gray-700">Pet Name</label>
                                    <input type="text" id="pet_name" name="pet_name" placeholder="Enter Pet Name" required
                                        class="mt-1 p-2 text-sm block w-full rounded-md border border-gray-300 shadow-sm focus:ring-[#169976] focus:border-[#169976]" />
                                </div>
                                <div>
                                    <label for="pet_species" class="block text-sm font-medium text-gray-700">Species</label>
                                    <select name="pet_species" id="pet_species" required class="mt-1 p-2 text-sm block w-full rounded-md border border-gray-300 shadow-sm focus:ring-[#169976] focus:border-[#169976]">
                                        <option value="">Select Species</option>
                                        <option value="Dog">Dog</option>
                                        <option value="Cat">Cat</option>
                                    </select>
                                </div>
                                <div>
                                    <label for="pet_sex" class="block text-sm font-medium text-gray-700">Sex</label>
                                    <select name="pet_sex" id="pet_sex" required class="mt-1 p-2 text-sm block w-full rounded-md border border-gray-300 shadow-sm focus:ring-[#169976] focus:border-[#169976]">
                                        <option value="">Select Sex</option>
                                        <option value="Male">Male</option>
                                        <option value="Female">Female</option>
                                    </select>
                                </div>
                                <div>
                                    <label for="pet_breed" class="block text-sm font-medium text-gray-700">Breed</label>
                                    <input type="text" id="pet_breed" name="pet_breed" placeholder="Enter Pet Breed" required class="mt-1 p-2 text-sm block w-full rounded-md border border-gray-300 shadow-sm focus:ring-[#169976] focus:border-[#169976]" />
                                </div>
                                <div>
                                    <label for="pet_weight" class="block text-sm font-medium text-gray-700">Weight (kg)</label>
                                    <input type="number" step="0.01" min="0" id="pet_weight" name="pet_weight" placeholder="e.g., 5.2" required
                                        class="mt-1 p-2 text-sm block w-full rounded-md border border-gray-300 shadow-sm focus:ring-[#169976] focus:border-[#169976]" />
                                </div>
                                <div>
                                    <label for="pet_birth_date" class="block text-sm font-medium text-gray-700">Birth Date</label>
                                    <input type="date" id="pet_birth_date" name="pet_birth_date" required
                                        class="mt-1 p-2 text-sm block w-full rounded-md border border-gray-300 shadow-sm focus:ring-[#169976] focus:border-[#169976]" />
                                </div>
                            </div>
                        </div>
                        <div class="space-y-4">
                            <!-- Left: Calendar -->
                            <div class="flex flex-col justify-center">
                                <label class="block text-sm font-medium text-gray-700 mb-2">Date</label>
                                <!-- Calendar Legend -->
                                <div class="calendar-legend">
                                    <div class="legend-item">
                                        <div class="legend-circle legend-has-appointments"></div>
                                        <span>Has Appointments</span>
                                    </div>
                                    <div class="legend-item">
                                        <div class="legend-circle legend-full"></div>
                                        <span>Fully Booked</span>
                                    </div>
                                </div>

                                <div id="calendarContainer" class="p-4 bg-gray-50 rounded-md border border-gray-200 shadow-sm">
                                    <div class="flex justify-between items-center mb-3">
                                        <button type="button" id="prevMonth" class="px-3 py-1 bg-[#169976] text-white rounded hover:bg-[#137a60]">&lt;</button>
                                        <span id="monthYear" class="text-base font-semibold"></span>
                                        <button type="button" id="nextMonth" class="px-3 py-1 bg-[#169976] text-white rounded hover:bg-[#137a60]">&gt;</button>
                                    </div>
                                    <div id="calendarDays" class="grid grid-cols-7 gap-2 text-center"></div>
                                    <input type="hidden" id="selectedDate" name="appointment_date" required>
                                </div>
                                <p class="text-sm text-gray-500 mt-2">Please select your preferred date.</p>
                            </div>
                            <!-- Time Slots -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Available Time Slots</label>
                                <div id="timeSlotsContainer" class="grid grid-cols-2 gap-2"></div>
                                <input type="hidden" id="selectedTime" name="appointment_time" required>
                                <p id="dayFullMessage" class="text-sm text-red-500 mt-2 hidden">This day is fully booked (6 appointments limit reached).</p>
                                <p class="text-xs text-gray-500 mt-2">Clinic hours: 8:00 AM - 6:00 PM. Each slot is 90 minutes. Max 6 appointments per day.</p>
                            </div>
                            <!-- Reason -->
                            <div>
                                <label for="reason" class="block text-sm font-medium text-gray-700">Reason for Visit</label>
                                <select id="reason" name="reason" required onchange="toggleOtherReason(this)"
                                    class="mt-1 p-2 text-sm block w-full rounded-md border border-gray-300 shadow-sm focus:ring-[#169976] focus:border-[#169976]">
                                    <option value="">-- Select Reason --</option>
                                    <option value="Checkup">Check-up</option>
                                    <option value="Vaccination">Vaccination</option>
                                    <option value="Grooming">Grooming</option>
                                    <option value="Surgery">Surgery</option>
                                    <option value="Emergency">Emergency</option>
                                    <option value="Other">Other</option>
                                </select>
                                <input type="text" id="other_reason" name="other_reason" placeholder="Please specify"
                                    style="display:none; margin-top:5px;"
                                    class="mt-1 p-2 text-sm block w-full rounded-md border border-gray-300 shadow-sm focus:ring-[#169976] focus:border-[#169976]">
                            </div>
                        </div>
                    </div>
                    <!-- Buttons -->
                    <div class="flex justify-end space-x-3 pt-4 border-t mt-4">
                        <button type="button" onclick="closeAppointmentModal()" class="px-5 py-2 text-sm bg-gray-200 text-gray-700 rounded hover:bg-gray-300 transition">Cancel</button>
                        <button type="submit" id="submitButton" class="px-5 py-2 text-sm bg-[#169976] text-white rounded hover:bg-[#18b98e] transition">Submit</button>
                    </div>
            </form>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            // Header scroll effect
            window.addEventListener('scroll', () => {
                const header = document.querySelector('header');
                if (window.scrollY > 50) {
                    header.classList.add('scrolled');
                } else {
                    header.classList.remove('scrolled');
                }
            });
            setTimeout(() => {
                const error = document.getElementById('errorMessage');
                if (error) {
                    error.style.display = 'none';
                }
            }, 3000);
            // Mobile menu toggle (fixed selector)
            const mobileMenuButton = document.getElementById('mobile-menu-button');
            const mobileMenu = document.getElementById('mobileMenu');
            if (mobileMenuButton && mobileMenu) {
                mobileMenuButton.addEventListener('click', () => {
                    mobileMenu.classList.toggle('hidden');
                });
            }
            // Modal functions
            window.openModal = function(modalId) {
                document.getElementById(modalId).classList.remove('hidden');
            };
            window.closeModal = function(modalId) {
                document.getElementById(modalId).classList.add('hidden');
            };
            window.switchModal = function(fromId, toId) {
                closeModal(fromId);
                openModal(toId);
            };
            // Profile dropdown toggle (if logged in)
            const profileButton = document.getElementById('profileButton');
            const dropdownMenu = document.getElementById('dropdownMenu');
            if (profileButton && dropdownMenu) {
                profileButton.addEventListener('click', () => {
                    dropdownMenu.classList.toggle('opacity-0');
                    dropdownMenu.classList.toggle('scale-95');
                    dropdownMenu.classList.toggle('pointer-events-none');
                });
                // Close dropdown on outside click
                document.addEventListener('click', (event) => {
                    if (!profileButton.contains(event.target) && !dropdownMenu.contains(event.target)) {
                        dropdownMenu.classList.add('opacity-0', 'scale-95', 'pointer-events-none');
                    }
                });
            }
            // Logout confirmation
            window.confirmLogout = function(event) {
                event.preventDefault();
                Swal.fire({
                    title: 'Are you sure?',
                    text: 'You will be logged out!',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#169976',
                    cancelButtonColor: '#d33',
                    confirmButtonText: 'Yes, logout!'
                }).then((result) => {
                    if (result.isConfirmed) {
                        // Replace with your actual logout URL or form submission
                        window.location.href = 'logout.php'; // Adjust as needed
                    }
                });
            };
            // Password toggle for login
            const togglePassword = document.getElementById('togglePassword');
            const password = document.getElementById('password');
            if (togglePassword && password) {
                togglePassword.addEventListener('click', () => {
                    const type = password.getAttribute('type') === 'password' ? 'text' : 'password';
                    password.setAttribute('type', type);
                    togglePassword.querySelector('i').classList.toggle('fa-eye');
                    togglePassword.querySelector('i').classList.toggle('fa-eye-slash');
                });
            }
            // Password toggles for register
            const toggleRegisterPassword = document.getElementById('toggleRegisterPassword');
            const registerPassword = document.getElementById('registerPassword');
            if (toggleRegisterPassword && registerPassword) {
                toggleRegisterPassword.addEventListener('click', () => {
                    const type = registerPassword.getAttribute('type') === 'password' ? 'text' : 'password';
                    registerPassword.setAttribute('type', type);
                    toggleRegisterPassword.querySelector('i').classList.toggle('fa-eye');
                    toggleRegisterPassword.querySelector('i').classList.toggle('fa-eye-slash');
                });
            }
            const toggleConfirmPassword = document.getElementById('toggleConfirmPassword');
            const confirmPassword = document.getElementById('confirm_password');
            if (toggleConfirmPassword && confirmPassword) {
                toggleConfirmPassword.addEventListener('click', () => {
                    const type = confirmPassword.getAttribute('type') === 'password' ? 'text' : 'password';
                    confirmPassword.setAttribute('type', type);
                    toggleConfirmPassword.querySelector('i').classList.toggle('fa-eye');
                    toggleConfirmPassword.querySelector('i').classList.toggle('fa-eye-slash');
                });
            }
            // Password strength for register
            const strengthBar = document.getElementById('registerPasswordStrengthBar');
            const strengthText = document.getElementById('registerPasswordStrengthText');
            if (registerPassword && strengthBar && strengthText) {
                registerPassword.addEventListener('input', () => {
                    const val = registerPassword.value;
                    let strength = 0;
                    if (val.length > 7) strength++;
                    if (val.match(/([a-z].*[A-Z])|([A-Z].*[a-z])/)) strength++;
                    if (val.match(/([0-9])/)) strength++;
                    if (val.match(/([!,@,#,$,%,^,&,*,(,),_,+])/)) strength++;
                    let color = 'bg-red-500';
                    let text = 'Weak';
                    let width = '0%';
                    if (strength === 1) {
                        width = '25%';
                    } else if (strength === 2) {
                        color = 'bg-yellow-500';
                        text = 'Medium';
                        width = '50%';
                    } else if (strength === 3) {
                        color = 'bg-green-500';
                        text = 'Strong';
                        width = '75%';
                    } else if (strength === 4) {
                        color = 'bg-green-700';
                        text = 'Very Strong';
                        width = '100%';
                    }
                    strengthBar.className = `h-2 rounded transition-all duration-300 ${color}`;
                    strengthBar.style.width = width;
                    strengthText.textContent = text;
                });
            }
            // Confirm password match validation
            const confirmPasswordError = document.getElementById('confirmPasswordError');
            if (confirmPassword && confirmPasswordError) {
                confirmPassword.addEventListener('input', () => {
                    if (confirmPassword.value !== registerPassword.value) {
                        confirmPasswordError.textContent = 'Passwords do not match';
                    } else {
                        confirmPasswordError.textContent = '';
                    }
                });
            }
            // Appointment modal opener (opens and initializes calendar)
            window.appointmentModal = function() {
                document.getElementById("appointmentModal").classList.remove("hidden");
                initializeCalendar();
            };
            window.closeAppointmentModal = function() {
                document.getElementById("appointmentModal").classList.add("hidden");
                document.getElementById("dayFullMessage").classList.add("hidden");
                document.getElementById("timeSlotsContainer").innerHTML = "";
                const submitButton = document.getElementById("submitButton");
                submitButton.disabled = false;
                submitButton.classList.remove("bg-gray-400", "cursor-not-allowed");
                submitButton.classList.add("bg-[#169976]", "hover:bg-[#18b98e]");
            };
            window.toggleOtherReason = function(select) {
                const otherReasonInput = document.getElementById("other_reason");
                if (select.value === "Other") {
                    otherReasonInput.style.display = "block";
                    otherReasonInput.required = true;
                } else {
                    otherReasonInput.style.display = "none";
                    otherReasonInput.value = "";
                    otherReasonInput.required = false;
                }
            };
            // Calendar Initialization
            let currentDate = new Date();

            function initializeCalendar() {
                const monthYear = document.getElementById("monthYear");
                const calendarDays = document.getElementById("calendarDays");
                const prevMonth = document.getElementById("prevMonth");
                const nextMonth = document.getElementById("nextMonth");
                const submitButton = document.getElementById("submitButton");
                const selectedDate = document.getElementById("selectedDate");

                function renderCalendar() {
                    calendarDays.innerHTML = "";
                    const year = currentDate.getFullYear();
                    const month = currentDate.getMonth();
                    monthYear.textContent = currentDate.toLocaleString("default", {
                        month: "long",
                        year: "numeric"
                    });
                    const firstDay = new Date(year, month, 1);
                    const lastDay = new Date(year, month + 1, 0);
                    const daysInMonth = lastDay.getDate();
                    const startingDay = firstDay.getDay();

                    // Fetch appointment counts for the month
                    const startDate = `${year}-${String(month + 1).padStart(2, '0')}-01`;
                    const endDate = `${year}-${String(month + 1).padStart(2, '0')}-${daysInMonth}`;

                    fetch(`./functions/get-appointments.php?start=${startDate}&end=${endDate}`)
                        .then(response => response.json())
                        .then(updateCalendarUI);
                    // Add empty days for the first week
                    for (let i = 0; i < startingDay; i++) {
                        const emptyDiv = document.createElement("div");
                        calendarDays.appendChild(emptyDiv);
                    }
                    // Add days of the month
                    for (let day = 1; day <= daysInMonth; day++) {
                        const dayDiv = document.createElement("div");
                        dayDiv.classList.add("day-cell", "cursor-pointer", "hover:bg-gray-200", "rounded-full", "p-1", "text-center");
                        const dayNumber = document.createElement("span");
                        dayNumber.textContent = day;
                        dayNumber.classList.add("day-number");
                        dayDiv.appendChild(dayNumber);

                        const current = new Date(year, month, day);
                        if (current < new Date().setHours(0, 0, 0, 0)) {
                            dayDiv.classList.add("text-gray-400", "cursor-not-allowed");
                        } else { // Only add click listener for valid days
                            dayDiv.addEventListener("click", () => {
                                selectedDate.value = `${year}-${String(month + 1).padStart(2, '0')}-${String(day).padStart(2, '0')}`;
                                const days = document.querySelectorAll("#calendarDays div");
                                days.forEach(d => d.classList.remove("bg-[#169976]", "text-white"));
                                dayDiv.classList.add("bg-[#169976]", "text-white");
                                loadTimeSlots();
                            });
                        }
                        calendarDays.appendChild(dayDiv);
                    }
                }

                function updateCalendarUI(events) {
                    const dayCounts = {};
                    events.forEach(event => {
                        const date = event.start.split('T')[0];
                        dayCounts[date] = (dayCounts[date] || 0) + 1;
                    });

                    const dayElements = calendarDays.querySelectorAll('.day-cell');
                    dayElements.forEach(dayEl => {
                        const day = dayEl.textContent;
                        if (!day) return;

                        const dateStr = `${currentDate.getFullYear()}-${String(currentDate.getMonth() + 1).padStart(2, '0')}-${String(day).padStart(2, '0')}`;
                        const count = dayCounts[dateStr] || 0;

                        // Clear previous indicators
                        const existingIndicator = dayEl.querySelector('.appointment-indicator');
                        if (existingIndicator) existingIndicator.remove();

                        if (count > 0) {
                            const indicator = document.createElement('div');
                            indicator.className = 'appointment-indicator';
                            if (count >= 6) {
                                dayEl.classList.add('full-day');
                                indicator.style.backgroundColor = '#dc3545'; // Red
                            } else {
                                dayEl.classList.add('has-appointments');
                                indicator.style.backgroundColor = '#28a745'; // Green
                            }
                            dayEl.appendChild(indicator);
                        }
                    });
                }
                prevMonth.addEventListener("click", () => {
                    currentDate.setMonth(currentDate.getMonth() - 1);
                    if (currentDate < new Date().setDate(1)) {
                        currentDate = new Date();
                    }
                    renderCalendar();
                });
                nextMonth.addEventListener("click", () => {
                    currentDate.setMonth(currentDate.getMonth() + 1);
                    renderCalendar();
                });
                // Initial render
                renderCalendar();
                // Form submit validation
                document.getElementById("appointmentForm").addEventListener("submit", function(e) {
                    if (!selectedDate.value) {
                        e.preventDefault();
                        alert("Please select a date.");
                        return;
                    }
                    if (!document.getElementById("selectedTime").value) {
                        e.preventDefault();
                        alert("Please select a time slot.");
                    }
                });
            }
            // Load time slots for selected date
            function loadTimeSlots() {
                const selectedDate = document.getElementById("selectedDate").value;
                const timeSlotsContainer = document.getElementById("timeSlotsContainer");
                const dayFullMessage = document.getElementById("dayFullMessage");
                const selectedTime = document.getElementById("selectedTime");
                const submitButton = document.getElementById("submitButton");
                timeSlotsContainer.innerHTML = "";
                dayFullMessage.classList.add("hidden");
                selectedTime.value = "";
                submitButton.disabled = true;
                submitButton.classList.add("bg-gray-400", "cursor-not-allowed");
                submitButton.classList.remove("bg-[#169976]", "hover:bg-[#18b98e]");
                if (!selectedDate) return;
                // Define fixed 90-min slots (6 max, non-overlapping, within 8 AM - 6 PM)
                const possibleSlots = [
                    "08:00", "09:30", "11:00", "12:30", "14:00", "15:30"
                ];
                // Fetch existing appointments
                fetch(`./functions/get-appointments.php?start=${selectedDate}&end=${selectedDate}`)
                    .then(response => {
                        if (!response.ok) throw new Error(`HTTP error! status: ${response.status}`);
                        return response.json();
                    })
                    .then(events => {
                        const takenSlots = new Set();
                        events.forEach(event => {
                            const eventStart = new Date(event.start);
                            const startTime = eventStart.toLocaleTimeString([], {
                                hour: '2-digit',
                                minute: '2-digit',
                                hour12: false
                            });
                            takenSlots.add(startTime);
                        });
                        if (takenSlots.size >= 6) {
                            dayFullMessage.classList.remove("hidden");
                            return;
                        }
                        // Generate buttons for available slots
                        possibleSlots.forEach(slot => {
                            if (!takenSlots.has(slot)) {
                                const button = document.createElement("button");
                                button.type = "button";
                                button.textContent = `${slot} (${formatAMPM(slot)})`;
                                button.classList.add("p-2", "text-sm", "bg-gray-200", "rounded", "hover:bg-gray-300", "transition");
                                button.addEventListener("click", () => {
                                    selectedTime.value = slot;
                                    const buttons = timeSlotsContainer.querySelectorAll("button");
                                    buttons.forEach(btn => btn.classList.remove("bg-[#169976]", "text-white"));
                                    button.classList.add("bg-[#169976]", "text-white");
                                    submitButton.disabled = false;
                                    submitButton.classList.remove("bg-gray-400", "cursor-not-allowed");
                                    submitButton.classList.add("bg-[#169976]", "hover:bg-[#18b98e]");
                                });
                                timeSlotsContainer.appendChild(button);
                            }
                        });
                    })
                    .catch(error => {
                        console.error("Error loading time slots:", error);
                        timeSlotsContainer.innerHTML = "<p class='text-red-500 text-sm'>Error loading slots. Please try again.</p>";
                    });
            }
            // Helper to format time as AM/PM
            function formatAMPM(time) {
                const [hours, minutes] = time.split(":").map(Number);
                const ampm = hours >= 12 ? "PM" : "AM";
                const formattedHours = hours % 12 || 12;
                return `${formattedHours}:${minutes.toString().padStart(2, '0')} ${ampm}`;
            }
        });
    </script>
    <script src="./js/landing-page.js"></script>
</body>

</html>
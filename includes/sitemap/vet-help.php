<?php
// Start session and include database connection
session_start();
require_once __DIR__ . "/../../db.php";

// Check if user is logged in by verifying vet_id session
if (!isset($_SESSION['vet_id'])) {
    header('Location: index.php');
    exit;
}

// Fetch vet name for greeting
$stmt = $pdo->prepare("SELECT vet_name FROM Veterinarian WHERE vet_id=?");
$stmt->execute([$_SESSION['vet_id']]);
$user = $stmt->fetch();
$vetName = $user ? htmlspecialchars($user['vet_name']) : "Veterinarian not found";

// Fetch vet data for modal
$stmt = $pdo->prepare("SELECT * FROM Veterinarian WHERE vet_id = ?");
$stmt->execute([$_SESSION['vet_id']]);
$vet = $stmt->fetch(PDO::FETCH_ASSOC);

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Help - Balingasag Dog and Cat Clinic</title>
    <script src="/Assets/Extension.js"></script>
    <link rel="stylesheet" href="../../Assets/FontAwsome/css/all.min.css">
    <link rel="icon" href="../../image/logo.png" type="image/x-icon">
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        .mobile-menu-hidden {
            transform: translateX(-100%);
        }

        .mobile-menu-visible {
            transform: translateX(0);
        }

        /* Custom dark theme scrollbar */
        ::-webkit-scrollbar {
            width: 8px;
            height: 8px;
        }

        ::-webkit-scrollbar-track {
            background: #1e293b;
        }

        ::-webkit-scrollbar-thumb {
            background: #334155;
            border-radius: 4px;
        }

        ::-webkit-scrollbar-thumb:hover {
            background: #475569;
        }
    </style>
</head>

<body class="bg-slate-100 min-h-screen text-gray-800">
    <?php include('../edit-profile.php'); ?>

    <!-- Mobile Menu Button -->
    <button id="mobileMenuBtn" class="lg:hidden fixed top-4 left-4 z-50 bg-teal-700 text-white p-3 rounded-md shadow-lg hover:bg-teal-600 transition-colors">
        <i class="fas fa-bars"></i>
    </button>

    <!-- Sidebar -->
    <aside id="sidebar" class="fixed inset-y-0 left-0 w-[200px] bg-gradient-to-b from-emerald-600 via-teal-700 to-emerald-800 text-white p-5 transform -translate-x-full lg:translate-x-0 transition-transform duration-300 ease-in-out z-40 flex flex-col border-r border-teal-800">
        <!-- logo -->
        <div class="flex flex-col items-center">
            <img src="../../image/LogoWhite.png" alt="Balingasag Dog and Cat Clinic Logo" class="h-16 w-auto object-contain drop-shadow-lg">
            <div class="text-center leading-tight">
                <h2 class="text-xl font-extrabold tracking-wide text-white">
                    Balingasag
                </h2>
                <p class="text-base font-medium text-gray-200">
                    Dog & Cat Clinic
                </p>
            </div>
        </div>

        <nav class="flex-grow mt-8 lg:mt-12 space-y-0.5">
            <a href="../../dashboard.php" class="block text-sm text-white px-4 py-2 rounded-md hover:bg-teal-900 transition-colors">
                <i class="fas fa-tachometer-alt mr-2"></i> Dashboard
            </a>
            <a href="../../clients.php" class="block text-sm text-white hover:bg-teal-800 px-4 py-2 rounded-md transition-colors">
                <i class="fas fa-user mr-2"></i> Clients
            </a>
            <a href="../../pets.php" class="block text-sm text-white hover:bg-teal-800 px-4 py-2 rounded-md transition-colors">
                <i class="fas fa-paw mr-2"></i> Pets
            </a>
            <a href="../../medical_records.php" class="block text-sm text-white hover:bg-teal-800 px-4 py-2 rounded-md transition-colors">
                <i class="fas fa-file-medical mr-2"></i> Medical Records
            </a>
            <a href="../../payment_methods.php" class="block text-sm text-white hover:bg-teal-800 px-4 py-2 rounded-md transition-colors">
                <i class="fas fa-credit-card mr-2"></i> Payments
            </a>
            <a href="../../appointments.php" class="block text-sm text-white hover:bg-teal-800 px-4 py-2 rounded-md transition-colors">
                <i class="fas fa-calendar-days mr-2"></i> Appointments
            </a>
            <a href="../../archive.php" class="block text-sm text-white hover:bg-teal-800 px-4 py-2 rounded-md transition-colors">
                <i class="fa-solid fa-box-archive mr-2"></i> Archive
            </a>
            <a href="vet-help.php" class="block text-sm text-white bg-teal-800 hover:bg-teal-800 px-4 py-2 rounded-md transition-colors">
                <i class="fas fa-question-circle mr-2"></i> Help/Support
            </a>
        </nav>
        <div class="pt-4">
            <a href="#" onclick="confirmLogout(event)" class="block text-md text-white hover:bg-red-600 px-4 py-2 rounded-md transition-colors">
                <i class="fas fa-sign-out-alt mr-2"></i> Logout
            </a>
        </div>
    </aside>
    <div id="overlay" class="lg:hidden fixed inset-0 bg-black bg-opacity-50 z-30 hidden"></div>

    <!-- Main Content -->
    <div class="relative ml-0 lg:ml-52 p-4 pt-16 lg:pt-4 min-h-screen">

        <div id="loadingScreen" class="absolute inset-0 flex flex-col items-center justify-center bg-white bg-opacity-75 z-50 hidden">
            <img src="image/MainIcon.png" alt="Loading Icon" class="w-20 h-20 animate-pulse">
            <p class="mt-4 text-teal-700 font-semibold text-lg">Loading...</p>
        </div>

        <!-- Header -->
        <header class="bg-white shadow-lg rounded-lg text-gray-800 py-4 mb-6 lg:mb-8 p-4 lg:p-6 border border-slate-200">
            <div class="flex justify-between items-center">
                <h1 class="text-xl lg:text-2xl font-bold">Help & Guide</h1>

                <!-- Right Side (Notifications + Profile) -->
                <div class="flex items-center gap-2">
                    <!-- Notification Bell -->
                    <div class="relative inline-block text-left">
                        <button id="notificationButton" class="flex items-center justify-center w-10 h-10 bg-gray-100 border border-gray-200 rounded-full hover:bg-gray-200 text-gray-800 text-lg transition-colors relative">
                            <i class="fas fa-bell"></i>
                            <span id="notificationCount" class="absolute top-0 right-0 bg-red-500 text-white text-xs rounded-full px-1.5 py-0.5 hidden">0</span>
                        </button>
                        <div id="notificationDropdown" class="origin-top-right absolute right-0 mt-2 w-80 rounded-lg shadow-lg bg-white ring-1 ring-black ring-opacity-5 opacity-0 scale-95 pointer-events-none transition-all duration-200 ease-out z-50 border border-slate-200">
                            <div class="px-4 py-3 border-b border-slate-200">
                                <p class="text-sm font-semibold text-gray-800">Notifications</p>
                            </div>
                            <div id="notificationList" class="py-1 max-h-96 overflow-y-auto">
                                <!-- Notifications will be appended here -->
                            </div>
                            <div class="py-2 border-t border-slate-200">
                                <a href="#" onclick="markAllAsRead(event)" class="block text-center text-sm text-indigo-500 hover:text-indigo-600">Mark all as read</a>
                            </div>
                        </div>
                    </div>

                    <!-- Profile Dropdown -->
                    <div class="relative inline-block text-left">
                        <button id="profileButton" class="flex items-center justify-center w-10 h-10 bg-gray-100 border border-gray-200 rounded-full hover:bg-gray-200 text-gray-800 text-lg transition-colors">
                            <i class="fas fa-user"></i>
                        </button>
                        <div id="dropdownMenu" class="origin-top-right absolute right-0 mt-2 w-72 rounded-lg shadow-lg bg-white ring-1 ring-black ring-opacity-5 opacity-0 scale-95 pointer-events-none transition-all duration-200 ease-out z-50 border border-slate-200">
                            <div class="px-4 py-3 border-b border-slate-200">
                                <div class="flex items-center gap-3">
                                    <div class="flex items-center justify-center w-12 h-12 rounded-full border-2 border-indigo-500 bg-gray-100 text-indigo-400 text-xl">
                                        <i class="fas fa-user"></i>
                                    </div>
                                    <div>
                                        <p class="text-sm font-semibold text-gray-800"><?php echo $vetName; ?></p>
                                        <p class="text-xs text-gray-500">Veterinarian</p>
                                    </div>
                                </div>
                            </div>
                            <div class="py-1">
                                <a href="#" id="editProfileLink" class="flex items-center gap-3 px-4 py-3 text-sm text-gray-600 hover:bg-gray-100 hover:text-gray-800 transition-colors duration-150">
                                    <i class="fas fa-edit text-indigo-400"></i>
                                    <div>
                                        <div class="font-medium">Edit Profile</div>
                                        <div class="text-xs text-gray-500">Update your information</div>
                                    </div>
                                </a>
                                <hr class="my-1 border-slate-200">
                                <a href="#" onclick="confirmLogout(event)" class="flex items-center gap-3 px-4 py-3 text-sm text-red-500 hover:bg-gray-100 transition-colors duration-150">
                                    <i class="fas fa-sign-out-alt text-red-500"></i>
                                    <div>
                                        <div class="font-medium">Logout</div>
                                        <div class="text-xs text-red-600">Sign out of your account</div>
                                    </div>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </header>

        <main class="bg-white p-4 lg:p-6 rounded-lg shadow-lg border border-slate-200">
            <!-- Page Title -->
            <h1 class="text-2xl font-bold text-gray-800 flex items-center gap-2 mb-6">
                <i class="fas fa-sitemap text-teal-600"></i>
                Staff / Vet — Help & Sitemap
            </h1>

            <p class="text-gray-600 mb-6">
                Follow the instructions below to properly use each part of the system.
                Some pages are <b>view-only</b>, while others allow adding, editing, or deleting records.
            </p>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-2 gap-6">

                <!-- Dashboard -->
                <section class="bg-gray-50 p-5 rounded-lg border border-gray-200 shadow-sm">
                    <h2 class="text-lg font-semibold text-gray-800 flex items-center gap-2 mb-3">
                        <i class="fas fa-tachometer-alt text-teal-600"></i> Dashboard
                    </h2>
                    <ol class="list-decimal pl-6 space-y-1 text-gray-700 text-sm">
                        <li>When you log in, you will be redirected to the dashboard.</li>
                        <li>Here you can see today’s appointments, reminders, and notifications.</li>
                        <li>Use this page to quickly check important updates.</li>
                    </ol>
                </section>

                <!-- Clients -->
                <section class="bg-gray-50 p-5 rounded-lg border border-gray-200 shadow-sm">
                    <h2 class="text-lg font-semibold text-gray-800 flex items-center gap-2 mb-3">
                        <i class="fas fa-users text-teal-600"></i> Clients
                    </h2>
                    <ol class="list-decimal pl-6 space-y-1 text-gray-700 text-sm">
                        <li>Go to the <b>Clients</b> section from the sidebar.</li>
                        <li>Click <b>Add New Client</b> to register a new client.</li>
                        <li>Fill in the client’s <b>Name</b>, <b>Address</b>, and <b>Contact details</b>.</li>
                        <li>Click <b>Save</b> to add the client.</li>
                        <li>To update a client, click <b>Edit</b> beside their record.</li>
                        <li>To remove a client, click <b>Delete</b> beside their record.</li>
                    </ol>
                </section>

                <!-- Pets (View Only) -->
                <section class="bg-gray-50 p-5 rounded-lg border border-gray-200 shadow-sm">
                    <h2 class="text-lg font-semibold text-gray-800 flex items-center gap-2 mb-3">
                        <i class="fas fa-paw text-teal-600"></i> Pets (View Only)
                    </h2>
                    <ol class="list-decimal pl-6 space-y-1 text-gray-700 text-sm">
                        <li>Go to the <b>Pets</b> section from the sidebar.</li>
                        <li>Select a client to view their registered pets.</li>
                        <li>Click on a pet to open its profile and details.</li>
                        <li><b>Note:</b> You can only <b>view</b> pet records here, editing and deleting are not allowed.</li>
                    </ol>
                </section>

                <!-- Medical Records (View Only) -->
                <section class="bg-gray-50 p-5 rounded-lg border border-gray-200 shadow-sm">
                    <h2 class="text-lg font-semibold text-gray-800 flex items-center gap-2 mb-3">
                        <i class="fas fa-file-medical text-teal-600"></i> Medical Records (View Only)
                    </h2>
                    <ol class="list-decimal pl-6 space-y-1 text-gray-700 text-sm">
                        <li>Go to the <b>Medical Records</b> section from the sidebar.</li>
                        <li>Select a pet to open its medical history.</li>
                        <li>View past treatments, vaccinations, and doctor’s notes.</li>
                        <li><b>Note:</b> You cannot edit or delete medical records directly. They are view-only.</li>
                    </ol>
                </section>

                <!-- Appointments -->
                <section class="bg-gray-50 p-5 rounded-lg border border-gray-200 shadow-sm">
                    <h2 class="text-lg font-semibold text-gray-800 flex items-center gap-2 mb-3">
                        <i class="fas fa-calendar-check text-teal-600"></i> Appointments
                    </h2>
                    <ol class="list-decimal pl-6 space-y-1 text-gray-700 text-sm">
                        <li>Go to the <b>Appointments</b> section from the sidebar.</li>
                        <li>Review the list of pending appointment requests.</li>
                        <li>For each request, choose whether to <b>Approve</b>, <b>Reschedule</b>, or <b>Cancel</b>.</li>
                        <li>Once approved, the appointment will appear on the client’s schedule.</li>
                    </ol>
                </section>

                <!-- Payments -->
                <section class="bg-gray-50 p-5 rounded-lg border border-gray-200 shadow-sm">
                    <h2 class="text-lg font-semibold text-gray-800 flex items-center gap-2 mb-3">
                        <i class="fas fa-credit-card text-teal-600"></i> Payments
                    </h2>
                    <ol class="list-decimal pl-6 space-y-1 text-gray-700 text-sm">
                        <li>Go to the <b>Payments</b> section from the sidebar.</li>
                        <li>Check the list of available payment methods.</li>
                        <li>View past payment transactions for clients.</li>
                        <li>Staff can track and review payments, but cannot change payment methods.</li>
                    </ol>
                </section>

                <!-- Archive -->
                <section class="bg-gray-50 p-5 rounded-lg border border-gray-200 shadow-sm">
                    <h2 class="text-lg font-semibold text-gray-800 flex items-center gap-2 mb-3">
                        <i class="fa-solid fa-box-archive text-teal-600"></i> Archive
                    </h2>
                    <ol class="list-decimal pl-6 space-y-1 text-gray-700 text-sm">
                        <li>Go to the <b>Archive</b> section from the sidebar.</li>
                        <li>Browse archived records of clients, pets, and appointments.</li>
                        <li>Archived data is read-only and cannot be modified.</li>
                    </ol>
                </section>
            </div>
        </main>
    </div>

    <script src="../../js/dashboard.js"></script>
    <script src="../../js/sidebarHandler.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="../../js/confirmLogout.js"></script>
    <script src="../../js/edit-profile.js"></script>
    <script src="../../js/notification-bell.js"></script>
    <script src="./js/customize-loader.js"></script>
</body>

</html>
<?php
require_once __DIR__ . "/../../functions/auth.php";
require_once __DIR__ . "/../../db.php";
requireAdmin();

// Fetch admin data
if (!isset($currentAdmin)) {
    $stmt = $pdo->prepare("SELECT * FROM admin WHERE admin_id = ?");
    $stmt->execute([$_SESSION['admin_id']]);
    $currentAdmin = $stmt->fetch(PDO::FETCH_ASSOC);
}

$adminName = htmlspecialchars($currentAdmin['admin_name'] ?? 'Admin');
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Help</title>
    <script src="/Assets/Extension.js"></script>
    <link rel="stylesheet" href="../../Assets/FontAwsome/css/all.min.css">
    <link rel="icon" href="../../image/MainIcon.png" type="image/x-icon">
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        .mobile-menu-hidden {
            transform: translateX(-100%);
        }

        .mobile-menu-visible {
            transform: translateX(0);
        }

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
    <?php include('../../includes/edit-profile.php'); ?>

    <!-- Mobile Menu Button -->
    <button id="mobileMenuBtn" class="lg:hidden fixed top-4 left-4 z-50 bg-teal-700 text-white p-3 rounded-md shadow-lg hover:bg-teal-600 transition-colors">
        <i class="fas fa-bars"></i>
    </button>

    <!-- Sidebar -->
    <aside id="sidebar"
        class="fixed inset-y-0 left-0 w-[200px] bg-gradient-to-b from-emerald-600 via-teal-700 to-emerald-800 text-white p-5 transform -translate-x-full lg:translate-x-0 transition-transform duration-300 ease-in-out z-40 flex flex-col border-r border-emerald-900">

        <!-- Header -->
        <div class="flex items-center justify-between">
            <h2 class="text-xl lg:text-2xl font-semibold flex items-center gap-2">
                <img src="../../image/MainIconWhite.png" alt="Dashboard" class="w-6 lg:w-8">
                <span class="md:inline">Dashboard</span>
            </h2>
            <button id="closeSidebarBtn"
                class="lg:hidden text-gray-300 hover:text-white duration-200">
                <i class="fas fa-times text-xl"></i>
            </button>
        </div>

        <!-- Navigation -->
        <nav class="flex-grow mt-8 lg:mt-12 space-y-0.5">
            <a href="../../admin/admin-dashboard.php"
                class="block text-sm text-white hover:bg-emerald-700 px-4 py-2 rounded-md transition-colors">
                <i class="fas fa-tachometer-alt mr-2"></i> Dashboard
            </a>
            <a href="../../admin/admin.php"
                class="block text-sm text-white hover:bg-emerald-700 px-4 py-2 rounded-md transition-colors">
                <i class="fas fa-user-md mr-2"></i> Veterinarians
            </a>

            <!-- Records Dropdown -->
            <div class="space-y-0.5">
                <button id="recordsBtn"
                    class="w-full flex items-center justify-start gap-2 text-sm text-white px-4 py-2 rounded-md hover:bg-emerald-700 transition-colors">
                    <i class="fa-solid fa-file-lines"></i>
                    <span>Records</span>
                    <svg id="recordsArrow"
                        class="w-4 h-4 ml-1 transition-transform duration-200"
                        fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                    </svg>
                </button>

                <!-- Submenu -->
                <div id="recordsMenu"
                    class="max-h-0 overflow-hidden opacity-0 transition-all duration-200 ease-in-out pl-8 space-y-1">

                    <a href="../../admin/records/pet-records.php"
                        class="flex items-center text-sm text-gray-200 hover:bg-emerald-600 px-3 py-2 rounded-md hover:text-white transition-colors">
                        <i class="fas fa-paw mr-2"></i> Pets
                    </a>

                    <a href="../../admin/records/client-records.php"
                        class="flex items-center text-sm text-gray-200 hover:bg-emerald-600 px-3 py-2 rounded-md hover:text-white transition-colors">
                        <i class="fas fa-user mr-2"></i> Clients
                    </a>

                    <a href="../../admin/records/medical-records.php"
                        class="flex items-center text-sm text-gray-200 hover:bg-emerald-600 px-3 py-2 rounded-md hover:text-white transition-colors">
                        <i class="fas fa-file-medical mr-2"></i>
                        <span class="whitespace-normal leading-snug">Medical Records</span>
                    </a>

                    <a href="../../admin/records/admin-payments.php"
                        class="flex items-center text-sm text-gray-200 hover:bg-emerald-600 px-3 py-2 rounded-md hover:text-white transition-colors">
                        <i class="fas fa-credit-card mr-2"></i> Payments Records
                    </a>
                </div>
            </div>


            <!-- Active Link Example -->
            <a href="../../admin/admin-appointments.php"
                class="block text-sm text-white hover:bg-emerald-700 px-4 py-2 rounded-md">
                <i class="fas fa-calendar-days mr-2"></i> Appointments
            </a>

            <a href="../sitemap/admin-help.php"
                class="block text-sm text-white bg-emerald-800 px-4 py-2 rounded-md transition-colors">
                <i class="fas fa-question-circle mr-2"></i> Help/Support
            </a>
        </nav>

        <!-- Logout -->
        <div class="pt-4">
            <a href="../index.php" onclick="confirmLogout(event)"
                class="block text-sm text-gray-200 hover:bg-red-600 px-4 py-2 rounded-md transition-colors">
                <i class="fas fa-sign-out-alt mr-2"></i> Logout
            </a>
        </div>
    </aside>

    <div id="overlay" class="lg:hidden fixed inset-0 bg-black bg-opacity-50 z-30 hidden"></div>

    <!-- Main Content -->
    <div class="relative ml-0 lg:ml-52 p-4 pt-16 lg:pt-4 min-h-screen">

        <!-- Header -->
        <header class="bg-white shadow-lg rounded-lg text-gray-800 py-4 mb-6 lg:mb-8 p-4 lg:p-6 border border-slate-200">

            <!-- Top Section with Dropdown -->
            <div class="flex justify-between items-center mb-6">
                <h1 class="text-xl lg:text-2xl font-bold">Appointments</h1>

                <!-- Right Side (Notifications + Profile) -->
                <div class="flex items-center gap-2">
                    <!-- Notification Bell -->
                    <div class="relative inline-block text-left">
                        <button id="notificationButton"
                            class="flex items-center justify-center w-10 h-10 bg-gray-100 border border-gray-200 rounded-full hover:bg-gray-200 text-gray-800 text-lg transition-colors relative">
                            <i class="fas fa-bell"></i>
                            <span id="notificationCount"
                                class="absolute top-0 right-0 bg-red-500 text-white text-xs rounded-full px-1.5 py-0.5 hidden">0</span>
                        </button>
                        <div id="notificationDropdown"
                            class="origin-top-right absolute right-0 mt-2 w-80 rounded-lg shadow-lg bg-white ring-1 ring-black ring-opacity-5 opacity-0 scale-95 pointer-events-none transition-all duration-200 ease-out z-50 border border-slate-200">
                            <div class="bg-blue-500 px-4 py-3 border-b border-slate-200">
                                <p class="text-sm font-semibold text-white">Notifications</p>
                            </div>
                            <div id="notificationList" class="py-1 max-h-96 overflow-y-auto">
                                <!-- Notifications will be appended here -->
                            </div>
                            <div class="py-2 border-t border-slate-200">
                                <a href="#" onclick="markAllAsRead(event)"
                                    class="block text-center text-sm text-indigo-500 hover:text-indigo-600">Mark all as
                                    read</a>
                            </div>
                        </div>
                    </div>

                    <!-- Profile Dropdown -->
                    <div class="relative inline-block text-left">
                        <button id="profileButton"
                            class="flex items-center justify-center w-10 h-10 bg-gray-100 border border-gray-200 rounded-full hover:bg-gray-200 text-gray-800 text-lg transition-colors">
                            <i class="fas fa-user"></i>
                        </button>
                        <div id="dropdownMenu"
                            class="origin-top-right absolute right-0 mt-2 w-72 rounded-lg shadow-lg bg-white ring-1 ring-black ring-opacity-5 opacity-0 scale-95 pointer-events-none transition-all duration-200 ease-out z-50 border border-slate-200">
                            <div class="px-4 py-3 border-b border-slate-200">
                                <div class="flex items-center gap-3">
                                    <div
                                        class="flex items-center justify-center w-12 h-12 rounded-full border-2 border-indigo-500 bg-gray-100 text-indigo-400 text-xl">
                                        <i class="fas fa-user"></i>
                                    </div>
                                    <div>
                                        <p class="text-sm font-semibold text-gray-800">
                                            <?php echo isset($vetName) ? $vetName : $adminName; ?>
                                        </p>
                                        <p class="text-xs text-gray-500">
                                            <?php echo isset($vetName) ? 'Veterinarian' : 'Admin'; ?>
                                        </p>
                                    </div>
                                </div>
                            </div>
                            <div class="py-1">
                                <a href="#" id="editProfileLink"
                                    class="flex items-center gap-3 px-4 py-3 text-sm text-gray-600 hover:bg-gray-100 hover:text-gray-800 transition-colors duration-150">
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
                </div>
            </div>
        </header>

        <main class="bg-white p-4 lg:p-6 rounded-lg shadow-lg border border-slate-200">
            <h1 class="text-2xl font-bold text-gray-800 flex items-center gap-2 mb-6">
                <i class="fas fa-sitemap text-teal-600"></i>
                Admin — Help & Sitemap
            </h1>

            <p class="text-gray-600 mb-6">
                Follow the instructions below to properly use each part of the system.
                Some pages allow <b>add, edit, and delete</b>, while others are <b>view-only</b>.
            </p>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-2 gap-6">
                <!-- Accounts -->
                <section class="bg-gray-50 p-5 rounded-lg border border-gray-200 shadow-sm cursor-pointer hover:bg-gray-100 transition-colors" onclick="window.location.href='../../admin/admin.php'">
                    <h2 class="text-lg font-semibold text-gray-800 flex items-center gap-2 mb-3">
                        <i class="fas fa-users-cog text-teal-600"></i> Admin Accounts
                    </h2>
                    <ol class="list-decimal pl-6 space-y-1 text-gray-700 text-sm">
                        <li>Log in with your <b>Admin</b> account.</li>
                        <li>Go to the <b>Users</b> section from the sidebar.</li>
                        <li>Click <b>Add User</b> to register new staff or vets.</li>
                        <li>Fill in all required details (Name, Username, Password, Role).</li>
                        <li>Click <b>Save</b> to confirm.</li>
                        <li>Edit or delete accounts using the action buttons beside records.</li>
                    </ol>
                </section>

                <!-- Clients -->
                <section class="bg-gray-50 p-5 rounded-lg border border-gray-200 shadow-sm cursor-pointer hover:bg-gray-100 transition-colors" onclick="window.location.href='../../admin/records/client-records.php'">
                    <h2 class="text-lg font-semibold text-gray-800 flex items-center gap-2 mb-3">
                        <i class="fas fa-users text-teal-600"></i> Clients
                    </h2>
                    <ol class="list-decimal pl-6 space-y-1 text-gray-700 text-sm">
                        <li>Go to the <b>Clients</b> page.</li>
                        <li>Add, edit, or delete client records.</li>
                        <li>Manage client details and assign pets.</li>
                    </ol>
                </section>

                <!-- Pets -->
                <section class="bg-gray-50 p-5 rounded-lg border border-gray-200 shadow-sm cursor-pointer hover:bg-gray-100 transition-colors" onclick="window.location.href='../../admin/records/pet-records.php'">
                    <h2 class="text-lg font-semibold text-gray-800 flex items-center gap-2 mb-3">
                        <i class="fas fa-paw text-teal-600"></i> Pets (View Only)
                    </h2>
                    <ol class="list-decimal pl-6 space-y-1 text-gray-700 text-sm">
                        <li>Access the <b>Pets</b> page.</li>
                        <li>View pets linked to clients.</li>
                        <li><b>Note:</b> Pets are <b>view-only</b>. You cannot edit or delete them here.</li>
                    </ol>
                </section>

                <!-- Medical Records -->
                <section class="bg-gray-50 p-5 rounded-lg border border-gray-200 shadow-sm cursor-pointer hover:bg-gray-100 transition-colors" onclick="window.location.href='../../admin/records/medical-records.php'">
                    <h2 class="text-lg font-semibold text-gray-800 flex items-center gap-2 mb-3">
                        <i class="fas fa-file-medical text-teal-600"></i> Medical Records (View Only)
                    </h2>
                    <ol class="list-decimal pl-6 space-y-1 text-gray-700 text-sm">
                        <li>Go to the <b>Medical Records</b> page.</li>
                        <li>View medical history of pets including treatments and notes.</li>
                        <li><b>Note:</b> Medical records are <b>view-only</b> and cannot be modified.</li>
                    </ol>
                </section>

                <!-- Appointments -->
                <section class="bg-gray-50 p-5 rounded-lg border border-gray-200 shadow-sm cursor-pointer hover:bg-gray-100 transition-colors" onclick="window.location.href='../../admin/admin-appointments.php'">
                    <h2 class="text-lg font-semibold text-gray-800 flex items-center gap-2 mb-3">
                        <i class="fas fa-calendar-alt text-teal-600"></i> Appointments
                    </h2>
                    <ol class="list-decimal pl-6 space-y-1 text-gray-700 text-sm">
                        <li>Open the <b>Appointments</b> page.</li>
                        <li>Review and manage all scheduled appointments.</li>
                        <li>Track approvals, reschedules, or cancellations made by staff/vets.</li>
                    </ol>
                </section>

                <!-- Payments -->
                <section class="bg-gray-50 p-5 rounded-lg border border-gray-200 shadow-sm cursor-pointer hover:bg-gray-100 transition-colors" onclick="window.location.href='../../admin/records/admin-payments.php'">
                    <h2 class="text-lg font-semibold text-gray-800 flex items-center gap-2 mb-3">
                        <i class="fas fa-credit-card text-teal-600"></i> Payments
                    </h2>
                    <ol class="list-decimal pl-6 space-y-1 text-gray-700 text-sm">
                        <li>Go to the <b>Payments</b> section.</li>
                        <li>Manage available payment methods (Cash, GCash, etc.).</li>
                        <li>View and review all client transactions.</li>
                        <li>Generate receipts if needed.</li>
                    </ol>
                </section>

                <!-- Reports -->
                <section class="bg-gray-50 p-5 rounded-lg border border-gray-200 shadow-sm">
                    <h2 class="text-lg font-semibold text-gray-800 flex items-center gap-2 mb-3">
                        <i class="fas fa-chart-pie text-teal-600"></i> Reports & Analytics
                    </h2>
                    <ol class="list-decimal pl-6 space-y-1 text-gray-700 text-sm">
                        <li>Access <b>Reports</b> from the sidebar.</li>
                        <li>Check monthly income graphs and pet condition statistics.</li>
                        <li>Export/download reports for offline records.</li>
                    </ol>
                </section>

                <!-- Archive -->
                <section class="bg-gray-50 p-5 rounded-lg border border-gray-200 shadow-sm">
                    <h2 class="text-lg font-semibold text-gray-800 flex items-center gap-2 mb-3">
                        <i class="fa-solid fa-box-archive text-teal-600"></i> Archive
                    </h2>
                    <ol class="list-decimal pl-6 space-y-1 text-gray-700 text-sm">
                        <li>Go to the <b>Archive</b> section.</li>
                        <li>View archived clients, pets, and appointments.</li>
                        <li>All archive records are read-only.</li>
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
    <script src="../../js/admin-notification-bell.js"></script>.
</body>

</html>
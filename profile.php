<?php
require_once 'functions/profile-handler.php';
include "includes/sitemap/Help/support.php";

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profile - Balingasag Dog and Cat Clinic</title>
    <script src="Assets/Extension.js"></script>
    <link rel="stylesheet" href="Assets/FontAwsome/css/all.min.css">
    <link rel="icon" href="image/logo.png" type="image/x-icon">
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
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

<body class="bg-slate-900 min-h-screen text-gray-100">

    <!-- Mobile Menu Button -->
    <button id="mobileMenuBtn" class="lg:hidden fixed top-4 left-4 z-50 bg-slate-700 text-white p-3 rounded-md shadow-lg hover:bg-slate-600 transition-colors">
        <i class="fas fa-bars"></i>
    </button>

    <!-- Sidebar -->
    <aside id="sidebar" class="fixed inset-y-0 left-0 w-[200px] bg-slate-800 text-white p-5 transform -translate-x-full lg:translate-x-0 transition-transform duration-300 ease-in-out z-40 flex flex-col border-r border-slate-700">
        <!-- Sidebar Header -->
        <div class="flex items-center justify-between mb-6">
            <h2 class="text-xl lg:text-2xl font-semibold flex items-center gap-2 w-full">
                <img src="image/logo.png" alt="Balingasag Dog and Cat Clinic Logo" class="w-full h-auto object-contain">
            </h2>
            <!-- Close button (mobile only) -->
            <button id="closeSidebarBtn" class="lg:hidden absolute top-4 right-4 text-gray-300 hover:text-white duration-200">
                <i class="fas fa-times text-xl"></i>
            </button>
        </div>

        <!-- Sidebar Navigation -->
        <nav class="flex-grow mt-8 lg:mt-12 space-y-0.5">
            <a href="dashboard.php" class="block text-sm text-gray-300 hover:bg-slate-700 px-4 py-2 rounded-md hover:text-white transition-colors">
                <i class="fas fa-tachometer-alt mr-2"></i> Dashboard
            </a>
            <a href="clients.php" class="block text-sm text-gray-300 hover:bg-slate-700 px-4 py-2 rounded-md hover:text-white transition-colors">
                <i class="fas fa-user mr-2"></i> Clients
            </a>
            <a href="pets.php" class="block text-sm text-gray-300 hover:bg-slate-700 px-4 py-2 rounded-md hover:text-white transition-colors">
                <i class="fas fa-paw mr-2"></i> Pets
            </a>
            <a href="medical_records.php" class="block text-sm text-gray-300 hover:bg-slate-700 px-4 py-2 rounded-md hover:text-white transition-colors">
                <i class="fas fa-file-medical mr-2"></i> Medical Records
            </a>
            <a href="profile.php" class="block text-sm text-white bg-slate-700 px-4 py-2 rounded-md">
                <i class="fas fa-id-badge mr-2"></i> Profile
            </a>
            <a href="payment_methods.php" class="block text-sm text-gray-300 hover:bg-slate-700 px-4 py-2 rounded-md hover:text-white transition-colors">
                <i class="fas fa-credit-card mr-2"></i> Payments
            </a>
            <a href="appointments.php" class="block text-sm text-gray-300 hover:bg-slate-700 px-4 py-2 rounded-md hover:text-white transition-colors">
                <i class="fas fa-calendar-days mr-2"></i> Appointments
            </a>
            <a href="archive.php" class="block text-sm text-gray-300 hover:bg-slate-700 px-4 py-2 rounded-md hover:text-white transition-colors">
                <i class="fa-solid fa-box-archive mr-2"></i> Archive
            </a>
            <a href="#" class="block text-sm text-gray-300 hover:bg-slate-700 px-4 py-2 rounded-md hover:text-white transition-colors" onclick="toggleModal('vetHelpModal')">
                <i class="fas fa-question-circle mr-2"></i> Help/Support
            </a>
        </nav>

        <!-- Logout -->
        <div class="pt-4">
            <a href="#" onclick="confirmLogout(event)" class="block text-md text-gray-300 hover:text-red-400 px-4 py-2 rounded-md transition-colors">
                <i class="fas fa-sign-out-alt mr-2"></i> Logout
            </a>
        </div>
    </aside>

    <!-- Overlay for mobile menu -->
    <div id="overlay" class="lg:hidden fixed inset-0 bg-black bg-opacity-50 z-30 hidden"></div>

    <!-- Main Content -->
    <div class="ml-0 lg:ml-52 p-4 pt-12 lg:pt-4">
        <!-- Header -->
        <header class="bg-slate-800 rounded-none lg:rounded-lg p-4 lg:py-6 mx-4 shadow-sm mb-4 lg:mb-8 px-4 lg:px-8 border border-slate-700 ">
            <h1 class="text-xl sm:text-2xl lg:text-3xl font-semibold flex items-center gap-2">
                <i class="fas fa-user-circle text-indigo-400"></i>
                <span>My Profile</span>
            </h1>
        </header>

        <!-- Main Content Area - Compact Form -->
        <main class="w-full max-w-full sm:max-w-2xl mx-auto bg-slate-800 rounded-lg shadow-sm border border-slate-700" style="max-height: 70vh; overflow-y: auto;">
            <!-- Profile Header -->
            <div class="bg-gradient-to-r from-slate-700 to-slate-800 text-white p-6 rounded-t-lg border-b border-slate-700">
                <div class="flex items-center space-x-4">
                    <div class="bg-indigo-500 p-3 rounded-full flex-shrink-0">
                        <i class="fas fa-user-md text-xl"></i>
                    </div>
                    <div class="min-w-0 flex-1">
                        <h2 class="text-lg sm:text-xl font-bold">Profile</h2>
                        <p class="text-slate-300 text-sm">Manage your account information</p>
                    </div>
                </div>
            </div>

            <!-- Compact Form Content -->
            <div class="p-6">
                <form method="POST" class="space-y-4">
                    <!-- Name Field -->
                    <div>
                        <label class="block text-sm font-medium text-slate-300 mb-1">
                            <i class="fas fa-user mr-2 text-indigo-400"></i>
                            Full Name
                        </label>
                        <input type="text"
                            name="vet_name"
                            value="<?= htmlspecialchars($vet['vet_name']) ?>"
                            class="w-full px-3 py-3 text-sm border border-slate-700 rounded-md bg-slate-700 text-white focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-all"
                            required
                            placeholder="Enter your full name">
                    </div>

                    <!-- Contact Number Field -->
                    <div>
                        <label class="block text-sm font-medium text-slate-300 mb-1">
                            <i class="fas fa-phone mr-2 text-indigo-400"></i>
                            Contact Number
                        </label>
                        <input type="tel"
                            name="vet_contact_number"
                            value="<?= htmlspecialchars($vet['vet_contact_number']) ?>"
                            class="w-full px-3 py-3 text-sm border border-slate-700 rounded-md bg-slate-700 text-white focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-all"
                            required
                            placeholder="Enter your contact number">
                    </div>

                    <!-- Username Field -->
                    <div>
                        <label class="block text-sm font-medium text-slate-300 mb-1">
                            <i class="fas fa-at mr-2 text-indigo-400"></i>
                            Username
                        </label>
                        <input type="text"
                            name="vet_username"
                            value="<?= htmlspecialchars($vet['vet_username']) ?>"
                            class="w-full px-3 py-3 text-sm border border-slate-700 rounded-md bg-slate-700 text-white focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-all"
                            required
                            placeholder="Enter your username">
                    </div>

                    <!-- Password Field -->
                    <div>
                        <label class="block text-sm font-medium text-slate-300 mb-1">
                            <i class="fas fa-lock mr-2 text-indigo-400"></i>
                            Password
                        </label>
                        <div class="relative">
                            <input type="password"
                                name="vet_password"
                                id="password"
                                class="w-full px-3 py-3 text-sm border border-slate-700 rounded-md bg-slate-700 text-white focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-all pr-10"
                                placeholder="Leave blank to keep current password">
                            <button type="button"
                                onclick="togglePassword()"
                                class="absolute right-3 top-1/2 transform -translate-y-1/2 text-slate-400 hover:text-white p-1">
                                <i class="fas fa-eye text-sm" id="passwordToggle"></i>
                            </button>
                        </div>
                        <p class="text-xs text-slate-500 mt-1">Only fill this if you want to change your password</p>
                    </div>

                    <!-- Action Buttons -->
                    <div class="flex flex-col sm:flex-row gap-3 pt-4 border-t border-slate-700">
                        <button type="submit"
                            class="flex-1 bg-indigo-600 text-white px-4 py-3 rounded-md hover:bg-indigo-700 transition-colors text-sm font-medium">
                            <i class="fas fa-save mr-2"></i>
                            Save Changes
                        </button>
                        <button type="button"
                            onclick="resetForm()"
                            class="flex-1 bg-slate-700 text-white px-4 py-3 rounded-md hover:bg-slate-600 transition-colors text-sm font-medium">
                            <i class="fas fa-undo mr-2"></i>
                            Reset
                        </button>
                    </div>
                </form>
            </div>
        </main>
    </div>

    <!-- Scripts -->
    <script src="./js/sidebarHandler.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="./js/confirmLogout.js"></script>

    <script>
        // Password toggle functionality
        function togglePassword() {
            const passwordInput = document.getElementById('password');
            const passwordToggle = document.getElementById('passwordToggle');

            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                passwordToggle.classList.remove('fa-eye');
                passwordToggle.classList.add('fa-eye-slash');
            } else {
                passwordInput.type = 'password';
                passwordToggle.classList.remove('fa-eye-slash');
                passwordToggle.classList.add('fa-eye');
            }
        }

        // Reset form functionality
        function resetForm() {
            if (confirm('Are you sure you want to reset all changes?')) {
                document.querySelector('form').reset();
                // Reset password field specifically
                document.getElementById('password').value = '';
            }
        }

        // Enhanced mobile menu handling
        document.addEventListener('DOMContentLoaded', function() {
            const mobileMenuBtn = document.getElementById('mobileMenuBtn');
            const sidebar = document.getElementById('sidebar');
            const overlay = document.getElementById('overlay');
            const closeSidebarBtn = document.getElementById('closeSidebarBtn');

            // Open sidebar
            mobileMenuBtn?.addEventListener('click', function() {
                sidebar.classList.remove('-translate-x-full');
                overlay.classList.remove('hidden');
                document.body.classList.add('overflow-hidden');
            });

            // Close sidebar
            function closeSidebar() {
                sidebar.classList.add('-translate-x-full');
                overlay.classList.add('hidden');
                document.body.classList.remove('overflow-hidden');
            }

            closeSidebarBtn?.addEventListener('click', closeSidebar);
            overlay?.addEventListener('click', closeSidebar);

            // Close sidebar on escape key
            document.addEventListener('keydown', function(e) {
                if (e.key === 'Escape') {
                    closeSidebar();
                }
            });
        });

        function toggleModal(modalId) {
            console.log("Toggling modal:", modalId); // Debug log
            const modal = document.getElementById(modalId);
            if (modal) {
                modal.classList.toggle('hidden');
            } else {
                console.error("Modal not found:", modalId);
            }
        }
    </script>
</body>

</html>
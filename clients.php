<?php
require_once __DIR__ . '/functions/clients-handler.php';
// Fetch vet data for modal
$stmt = $pdo->prepare("SELECT * FROM veterinarian WHERE vet_id = ?");
$stmt->execute([$_SESSION['vet_id']]);
$vet = $stmt->fetch(PDO::FETCH_ASSOC);
// Fetch unique medical conditions for autocomplete suggestions
$stmtConditions = $pdo->prepare("SELECT medical_condition FROM Medical_Records WHERE medical_condition IS NOT NULL AND medical_condition != ''");
$stmtConditions->execute();
$allConditions = $stmtConditions->fetchAll(PDO::FETCH_COLUMN);
$uniqueConditions = [];
foreach ($allConditions as $cond) {
    $split = array_map('trim', explode(',', $cond));
    foreach ($split as $s) {
        if ($s) $uniqueConditions[strtolower($s)] = true;
    }
}
$uniqueConditions = array_keys($uniqueConditions);
sort($uniqueConditions);
// Optional: Add defaults if DB is empty
if (empty($uniqueConditions)) {
    $uniqueConditions = ['runny nose', 'sneezing', 'coughing', 'ear infection']; // Add more as needed
}
$sort = isset($_GET['sort']) ? strtolower(trim($_GET['sort'])) : 'asc';
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
// Always fetch ALL active clients (no filtering here - done client-side)
$query = "
    SELECT c.*, 
           (SELECT COUNT(*) 
            FROM appointments a
            WHERE a.client_id = c.client_id
              AND a.status = 'Scheduled'
              AND a.created_at >= NOW() - INTERVAL 1 HOUR) > 0 AS has_new_appointment
    FROM Client c
    WHERE c.status = 1
    ORDER BY c.client_name " . ($sort === 'desc' ? 'DESC' : 'ASC');

$stmt = $pdo->prepare($query);
$stmt->execute();
$clients = $stmt->fetchAll();

ob_end_flush();
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Clients - Balingasag Dog and Cat Clinic</title>
    <script src="Assets/Extension.js"></script>
    <link rel="stylesheet" href="Assets/FontAwsome/css/all.min.css">
    <link rel="icon" href="image/logo.png" type="image/x-icon">
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        /* Beautiful Custom Scrollbar inside SweetAlert */
        .custom-scrollbar::-webkit-scrollbar {
            width: 6px;
        }

        .custom-scrollbar::-webkit-scrollbar-track {
            background: #f1f5f9;
            border-radius: 10px;
        }

        .custom-scrollbar::-webkit-scrollbar-thumb {
            background: #94a3b8;
            border-radius: 10px;
        }

        .custom-scrollbar::-webkit-scrollbar-thumb:hover {
            background: #64748b;
        }

        .swal-wide {
            padding: 20px;
        }

        .swal-wide .swal2-html-container {
            padding: 0 !important;
        }

        .az-btn {
            @apply inline-block border border-gray-300 px-3 py-1 rounded text-sm text-gray-700 bg-gray-100 hover:bg-red-500 hover:text-white transition;
        }

        .az-btn.active {
            @apply bg-red-500 text-white;
        }

        .condition-tag {
            display: inline-flex;
            align-items: center;
            background-color: #d1fae5;
            color: #065f46;
            padding: 0.25rem 0.5rem;
            border-radius: 0.25rem;
            margin-right: 0.25rem;
            margin-bottom: 0.25rem;
            font-size: 0.75rem;
            line-height: 1.25rem;
        }

        .condition-tag span {
            margin-left: 0.25rem;
            cursor: pointer;
            font-weight: bold;
        }

        .condition-tag span:hover {
            color: #064e3b;
        }

        .mobile-menu-hidden {
            transform: translateX(-100%);
        }

        .mobile-menu-visible {
            transform: translateX(0);
        }

        .table-container {
            overflow-x: auto;
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

        .custom-scrollbar::-webkit-scrollbar {
            width: 8px;
        }

        .custom-scrollbar::-webkit-scrollbar-track {
            background: #edf2f7;
            border-radius: 4px;
        }

        .custom-scrollbar::-webkit-scrollbar-thumb {
            background: #cbd5e0;
            border-radius: 4px;
        }

        .custom-scrollbar::-webkit-scrollbar-thumb:hover {
            background: #a0aec0;
        }

        .compact-modal {
            background: white;
            border-radius: 12px;
            box-shadow: 0 8px 30px rgba(0, 0, 0, 0.12);
            border: 1px solid #e2e8f0;
            max-width: 500px;
            width: 90%;
        }

        .compact-header {
            background: linear-gradient(135deg, #059669 0%, #0d9488 100%);
            padding: 1.25rem;
            border-radius: 12px 12px 0 0;
            border-bottom: 1px solid #10b981;
        }

        .compact-content {
            padding: 1.25rem;
            max-height: 60vh;
            overflow-y: auto;
        }

        .compact-section {
            background: #f8fafc;
            border-radius: 8px;
            padding: 1rem;
            margin-bottom: 1rem;
            border-left: 3px solid #0ea5e9;
        }

        .compact-section.pet {
            border-left-color: #10b981;
        }

        .compact-section.medical {
            border-left-color: #8b5cf6;
        }

        .section-title {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            font-size: 0.9rem;
            font-weight: 600;
            color: #374151;
            margin-bottom: 0.75rem;
            padding-bottom: 0.5rem;
            border-bottom: 1px solid #e5e7eb;
        }

        .section-title i {
            color: #0ea5e9;
            width: 16px;
        }

        .section-title.pet i {
            color: #10b981;
        }

        .section-title.medical i {
            color: #8b5cf6;
        }

        .info-row {
            display: flex;
            justify-content: between;
            margin-bottom: 0.5rem;
            padding: 0.25rem 0;
        }

        .info-label {
            font-size: 0.75rem;
            font-weight: 600;
            color: #64748b;
            width: 120px;
            flex-shrink: 0;
        }

        .info-value {
            font-size: 0.8rem;
            color: #1e293b;
            font-weight: 500;
            flex: 1;
        }

        .empty-state {
            text-align: center;
            padding: 1rem;
            color: #94a3b8;
            font-size: 0.8rem;
        }

        .empty-state i {
            font-size: 1.5rem;
            margin-bottom: 0.5rem;
            opacity: 0.5;
        }

        .compact-footer {
            padding: 1rem 1.25rem;
            border-top: 1px solid #e5e7eb;
            background: #f8fafc;
            border-radius: 0 0 12px 12px;
        }

        .close-btn-compact {
            background: #6366f1;
            color: white;
            border: none;
            padding: 0.6rem 1.5rem;
            border-radius: 6px;
            font-size: 0.8rem;
            font-weight: 500;
            transition: all 0.2s ease;
        }

        .close-btn-compact:hover {
            background: #4f46e5;
            transform: translateY(-1px);
        }

        /* Smooth scrollbar */
        .compact-content::-webkit-scrollbar {
            width: 4px;
        }

        .compact-content::-webkit-scrollbar-track {
            background: #f1f5f9;
        }

        .compact-content::-webkit-scrollbar-thumb {
            background: #cbd5e1;
            border-radius: 2px;
        }

        /* Prevent body scroll when modal is open */
        body.modal-open {
            overflow: hidden;
        }
    </style>
</head>

<body class="bg-slate-100 min-h-screen text-gray-800">
    <?php include('./includes/edit-profile.php'); ?>
    <!-- Mobile Menu Button -->
    <button id="sidebar-toggle" class="lg:hidden fixed top-4 left-4 z-50 bg-teal-700 text-white p-3 rounded-md shadow-lg hover:bg-teal-600 transition-colors">
        <i class="fas fa-bars"></i>
    </button>

    <!-- Sidebar -->
    <aside id="sidebar" class="fixed inset-y-0 left-0 w-[200px] bg-gradient-to-b from-emerald-600 via-teal-700 to-emerald-800 text-white p-5 transform -translate-x-full lg:translate-x-0 transition-transform duration-300 ease-in-out z-40 flex flex-col border-r border-teal-800">
        <div class="flex flex-col items-center">
            <img src="image/logoWhite.png" alt="Balingasag Dog and Cat Clinic Logo" class="h-16 w-auto object-contain drop-shadow-lg">
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
            <a href="dashboard.php" class="block text-sm text-white px-4 py-2 rounded-md hover:bg-teal-900 transition-colors">
                <i class="fas fa-tachometer-alt mr-2"></i> Dashboard
            </a>
            <a href="clients.php" class="block text-sm text-white hover:bg-teal-800 bg-teal-800 px-4 py-2 rounded-md transition-colors">
                <i class="fas fa-user mr-2"></i> Clients
            </a>
            <a href="pets.php" class="block text-sm text-white hover:bg-teal-800 px-4 py-2 rounded-md transition-colors">
                <i class="fas fa-paw mr-2"></i> Pets
            </a>
            <a href="medical_records.php" class="block text-sm text-white hover:bg-teal-800 px-4 py-2 rounded-md transition-colors">
                <i class="fas fa-file-medical mr-2"></i> Medical Records
            </a>
            <a href="payment_methods.php" class="block text-sm text-white hover:bg-teal-800 px-4 py-2 rounded-md transition-colors">
                <i class="fas fa-credit-card mr-2"></i> Payments
            </a>
            <a href="appointments.php" class="block text-sm text-white hover:bg-teal-800 px-4 py-2 rounded-md transition-colors">
                <i class="fas fa-calendar-days mr-2"></i> Appointments
            </a>
            <a href="archive.php" class="block text-sm text-white hover:bg-teal-800 px-4 py-2 rounded-md transition-colors">
                <i class="fa-solid fa-box-archive mr-2"></i> Archive
            </a>
            <a href="./includes/sitemap/vet-help.php" class="block text-sm text-white hover:bg-teal-800 px-4 py-2 rounded-md transition-colors">
                <i class="fas fa-question-circle mr-2"></i> Help/Support
            </a>
        </nav>
        <div class="pt-4">
            <a href="#" onclick="confirmLogout(event)" class="block text-md text-white hover:bg-red-600 px-4 py-2 rounded-md transition-colors">
                <i class="fas fa-sign-out-alt mr-2"></i> Logout
            </a>
        </div>
    </aside>

    <!-- Overlay for mobile menu -->
    <div id="overlay" class="lg:hidden fixed inset-0 bg-black bg-opacity-50 z-30 hidden"></div>
    <!-- Main Content -->
    <div class="relative ml-0 lg:ml-52 p-4 pt-16 lg:pt-4 min-h-screen">
        <div id="loadingScreen" class="absolute inset-0 flex flex-col items-center justify-center bg-white bg-opacity-75 z-50 hidden">
            <img src="image/MainIcon.png" alt="Loading Icon" class="w-20 h-20 animate-pulse">
            <p class="mt-4 text-teal-700 font-semibold text-lg">Loading...</p>
        </div>
        <header class="bg-white shadow-lg rounded-lg text-gray-800 py-4 mb-6 lg:mb-8 p-4 lg:p-6 border border-slate-200">
            <!-- Top Section with Dropdown -->
            <div class="flex justify-between items-center">
                <!-- Page Title -->
                <div>
                    <h1 class="text-xl lg:text-2xl font-bold">Manage Clients</h1>
                    <p class="text-sm text-gray-600 mt-1">Manage and View All Clients Records in the System</p>
                </div>
                <!-- Right Side (Notifications + Profile) -->
                <div class="flex items-center gap-2">
                    <!-- Notification Bell -->
                    <div class="relative inline-block text-left">
                        <button id="notificationButton" class="flex items-center justify-center w-10 h-10 bg-gray-100 border border-gray-200 rounded-full hover:bg-gray-200 text-gray-800 text-lg transition-colors relative">
                            <i class="fas fa-bell"></i>
                            <span id="notificationCount" class="absolute top-0 right-0 bg-red-500 text-white text-xs rounded-full px-1.5 py-0.5 hidden">0</span>
                        </button>
                        <div id="notificationDropdown" class="origin-top-right absolute right-0 mt-2 w-80 rounded-lg shadow-lg bg-white ring-1 ring-black ring-opacity-5 opacity-0 scale-95 pointer-events-none transition-all duration-200 ease-out z-50 border border-slate-200">
                            <div class="bg-blue-500 px-4 py-3 border-b border-blue-200">
                                <p class="text-sm font-semibold text-white">Notifications</p>
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
            <?php if (isset($error) || isset($_GET['error'])): ?>
                <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-4" role="alert">
                    <p><?= htmlspecialchars($error ?? $_GET['error']) ?></p>
                </div>
            <?php endif; ?>
            <?php if (isset($_GET['message'])): ?>
                <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-4" role="alert">
                    <p><?= htmlspecialchars($_GET['message']) ?></p>
                </div>
            <?php endif; ?>
            <div class="flex flex-row justify-between items-center mb-4">
                <h2 class="text-lg sm:text-xl lg:text-xl font-semibold text-gray-800">List of Clients</h2>
                <button onclick="showClientModal('add')" class="bg-indigo-500 text-white px-4 py-2 font-semibold rounded-md hover:bg-indigo-600 transition-colors text-sm sm:text-base">
                    <i class="fas fa-plus mr-2"></i>Add New Client
                </button>
            </div>
            <div>
                <!-- Combined Sort and Search Row -->
                <div class="flex flex-col sm:flex-row items-start sm:items-center gap-4 mb-4">
                    <div class="flex items-center gap-2">
                        <label class="text-sm font-medium text-gray-700 whitespace-nowrap">Sort by Name:</label>
                        <button id="sortButton" class="border border-gray-300 rounded-lg px-3 py-1 text-sm focus:ring-2 focus:ring-indigo-400 focus:outline-none hover:bg-gray-50 transition-colors">
                            <i id="sortIcon" class="fas <?= ($sort === 'asc') ? 'fa-sort-alpha-up' : 'fa-sort-alpha-down' ?>"></i>
                        </button>
                    </div>
                    <div class="flex items-center gap-2 flex-1 w-full sm:w-auto">
                        <label for="search" class="text-sm font-medium text-gray-700 whitespace-nowrap">Search Clients:</label>
                        <input
                            type="text"
                            name="search"
                            id="search"
                            value="<?= htmlspecialchars($_GET['search'] ?? '') ?>"
                            class="w-full max-w-xs border border-gray-300 rounded-lg px-4 py-1 text-sm focus:ring-2 focus:ring-indigo-400 focus:outline-none"
                            placeholder="Search by name, address, or contact...">
                    </div>
                </div>
            </div>
            <?php if (count($clients) > 0): ?>
                <div class="table-container">
                    <table class="min-w-full divide-y divide-slate-200">
                        <thead class="bg-gray-300 sticky top-0 z-2">
                            <tr class="border-b border-slate-200">
                                <th class="px-2 py-3 text-left text-xs sm:text-sm font-medium text-gray-600 uppercase tracking-wider min-w-[120px] whitespace-nowrap">Name</th>
                                <th class="px-2 py-3 text-left text-xs sm:text-sm font-medium text-gray-600 uppercase tracking-wider min-w-[120px] whitespace-nowrap">Address</th>
                                <th class="px-2 py-3 text-left text-xs sm:text-sm font-medium text-gray-600 uppercase tracking-wider min-w-[120px] whitespace-nowrap">Contact Number</th>
                                <th class="px-2 py-3 text-left text-xs sm:text-sm font-medium text-gray-600 uppercase tracking-wider min-w-[120px] whitespace-nowrap">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-slate-200">
                            <?php foreach ($clients as $client): ?>
                                <tr class="hover:bg-gray-50 transition-colors" data-name="<?= htmlspecialchars(strtolower($client['client_name'])) ?>" data-address="<?= htmlspecialchars(strtolower($client['client_address'])) ?>" data-contact="<?= htmlspecialchars(strtolower($client['client_contact_number'])) ?>">
                                    <td class="px-4 py-2 text-sm text-gray-700 whitespace-nowrap">
                                        <?= htmlspecialchars($client['client_name']) ?>
                                        <?php if (isset($client['created_at']) && strtotime($client['created_at']) > strtotime('-24 hours')): ?>
                                            <span class="ml-2 bg-green-100 text-green-800 text-xs font-medium px-2.5 py-0.5 rounded-full">New</span>
                                        <?php endif; ?>
                                        <?php if ($client['has_new_appointment']): ?>
                                            <span class="ml-2 bg-blue-100 text-blue-800 text-xs font-medium px-2.5 py-0.5 rounded-full">New Appointment</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="px-4 py-2 text-sm text-gray-600"><?= htmlspecialchars($client['client_address']) ?></td>
                                    <td class="px-4 py-2 text-sm text-gray-600"><?= htmlspecialchars($client['client_contact_number']) ?></td>
                                    <td class="px-4 py-2 text-sm">
                                        <button onclick="showViewModal(<?= (int)$client['client_id'] ?>)" class="text-green-500 hover:text-green-400 hover:underline">
                                            <i class="fas fa-eye"></i>
                                        </button> |
                                        <a href="?edit_client_id=<?= (int)$client['client_id'] ?>" class="text-indigo-500 hover:text-indigo-400 hover:underline">
                                            <i class="fas fa-edit"></i>
                                        </a> |
                                        <a href="#" onclick="confirmDelete(<?= (int)$client['client_id'] ?>)" class="text-red-500 hover:text-red-400 hover:underline">
                                            <i class="fa-solid fa-box-archive"></i>
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <p id="noResults" class="text-center text-gray-500 text-sm sm:text-base" style="display: none;">No clients found matching your search.</p>
            <?php else: ?>
                <p class="text-center text-gray-500 text-sm sm:text-base">No clients found.</p>
            <?php endif; ?>
        </main>
    </div>
    <!-- Add/Edit Client, Pet & Medical Record Modal -->
    <div id="clientModal" class="fixed inset-0 bg-black bg-opacity-50 hidden flex justify-center items-center z-50">
        <div class="bg-white rounded-lg shadow-lg w-11/12 max-w-2xl max-h-[85vh] overflow-hidden flex flex-col border border-teal-800">
            <div class="w-full bg-gradient-to-r from-emerald-600 to-teal-700 rounded-t-lg text-gray-800 border-b border-teal-800">
                <h3 id="modalTitle" class="text-lg font-bold text-center py-2 text-white">Add New Client, Pet & Medical Record</h3>
            </div>
            <form id="clientForm" method="POST" class="p-4 overflow-y-auto custom-scrollbar space-y-6">
                <input type="hidden" name="client_id" id="client_id">
                <input type="hidden" name="pet_id" id="pet_id">
                <input type="hidden" name="record_id" id="record_id">
                <!-- Client Information -->
                <div class="bg-white p-5 rounded-lg shadow-sm border border-slate-200">
                    <h3 class="text-base font-semibold text-emerald-600 mb-4 flex items-center gap-2">
                        <i class="fas fa-user-edit text-emerald-600"></i>
                        Client Information
                    </h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs text-gray-500 mb-1">Client Name</label>
                            <input type="text" name="client_name" id="clientName" class="w-full p-2 border border-slate-300 rounded-md text-sm bg-gray-50 focus:ring-2 focus:ring-emerald-500 focus:border-transparent" required>
                        </div>
                        <div>
                            <label class="block text-xs text-gray-500 mb-1">Contact Number</label>
                            <input type="tel"
                                name="client_contact_number"
                                id="clientContactNumber"
                                class="w-full p-2 border border-slate-300 rounded-md text-sm bg-gray-50 focus:ring-2 focus:ring-emerald-500 focus:border-transparent"
                                placeholder="09171234567"
                                minlength="11"
                                maxlength="11"
                                title="Enter 11 digits starting with 09 (e.g. 09171234567)"
                                required>
                        </div>
                        <div class="md:col-span-2">
                            <label class="block text-xs text-gray-500 mb-1">Address</label>
                            <input type="text" name="client_address" id="clientAddress" class="w-full p-2 border border-slate-300 rounded-md text-sm bg-gray-50 focus:ring-2 focus:ring-emerald-500 focus:border-transparent" required>
                        </div>
                    </div>
                </div>
                <!-- Pet Information -->
                <div class="bg-white p-5 rounded-lg shadow-sm border border-slate-200">
                    <h3 class="text-base font-semibold text-emerald-600 mb-4 flex items-center gap-2">
                        <i class="fas fa-paw text-emerald-600"></i>
                        Pet Information
                    </h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs text-gray-500 mb-1">Pet Name</label>
                            <input type="text" name="pet_name" id="petName" class="w-full p-2 border border-slate-300 rounded-md text-sm bg-gray-50 focus:ring-2 focus:ring-emerald-500 focus:border-transparent">
                        </div>
                        <div>
                            <label class="block text-xs text-gray-500 mb-1">Species
                                <span id="speciesTooltip" class="text-xs text-gray-400 hidden">(Cannot be changed)</span>
                            </label>
                            <select name="pet_species" id="petSpecies" class="w-full p-2 border border-slate-300 rounded-md text-sm bg-gray-50 focus:ring-2 focus:ring-emerald-500 focus:border-transparent">
                                <option value="">Select</option>
                                <option value="Dog">Dog</option>
                                <option value="Cat">Cat</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs text-gray-500 mb-1">Pet Sex
                                <span id="sexTooltip" class="text-xs text-gray-400 hidden">(Cannot be changed)</span>
                            </label>
                            <select name="pet_sex" id="petSex" class="w-full p-2 border border-slate-300 rounded-md text-sm bg-gray-50 focus:ring-2 focus:ring-emerald-500 focus:border-transparent">
                                <option value="">Select</option>
                                <option value="Male">Male</option>
                                <option value="Female">Female</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs text-gray-500 mb-1">Pet Breed</label>
                            <input type="text" name="pet_breed" id="petBreed" class="w-full p-2 border border-slate-300 rounded-md text-sm bg-gray-50 focus:ring-2 focus:ring-emerald-500 focus:border-transparent">
                        </div>
                        <div>
                            <label class="block text-xs text-gray-500 mb-1">Pet Weight (kg)</label>
                            <input type="number" step="0.01" min="0" name="pet_weight" id="petWeight" class="w-full p-2 border border-slate-300 rounded-md text-sm bg-gray-50 focus:ring-2 focus:ring-emerald-500 focus:border-transparent">
                        </div>
                        <div>
                            <label class="block text-xs text-gray-500 mb-1">Birth Date</label>
                            <input type="date" name="pet_birth_date" id="petBirthDate" class="w-full p-2 border border-slate-300 rounded-md text-sm bg-gray-50 focus:ring-2 focus:ring-emerald-500 focus:border-transparent">
                        </div>
                    </div>
                </div>
                <!-- Medical History Information -->
                <div class="bg-white p-5 rounded-lg shadow-sm border border-slate-200">
                    <h3 class="text-base font-semibold text-emerald-600 mb-4 flex items-center gap-2">
                        <i class="fas fa-file-medical text-emerald-600"></i>
                        Medical History Information
                    </h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                        <!-- Conditions -->
                        <div>
                            <label class="block text-sm font-medium text-gray-600 mb-1">Conditions</label>
                            <div id="conditionsContainer" class="flex flex-wrap items-center w-full p-2 border border-slate-300 rounded-md bg-gray-50 focus-within:ring-2 focus-within:ring-emerald-500 focus-within:border-transparent min-h-[42px] gap-2">
                                <!-- Tags will be dynamically added here -->
                                <input type="text" id="conditionInput" list="conditionSuggestions"
                                    class="flex-1 outline-none bg-transparent min-w-[150px] text-sm"
                                    placeholder="Type a condition...">
                            </div>
                            <datalist id="conditionSuggestions">
                                <?php foreach ($uniqueConditions as $cond): ?>
                                    <option value="<?= htmlspecialchars(ucwords($cond)) ?>">
                                    <?php endforeach; ?>
                            </datalist>
                            <input type="hidden" name="medical_condition" id="medicalConditionHidden" required>
                            <p class="text-xs text-gray-400 mt-1">Separate multiple conditions with commas.</p>
                        </div>
                        <!-- Diagnosis -->
                        <div>
                            <label class="block text-sm font-medium text-gray-600 mb-1">Diagnosis</label>
                            <textarea name="medical_diagnosis" id="medicalDiagnosis"
                                class="w-full h-[90px] p-2 border border-slate-300 rounded-md bg-gray-50 focus:ring-2 focus:ring-emerald-500 focus:border-transparent text-sm resize-none"
                                placeholder="Enter diagnosis..." required></textarea>
                        </div>
                        <!-- Symptoms -->
                        <div>
                            <label class="block text-sm font-medium text-gray-600 mb-1">Symptoms</label>
                            <textarea name="medical_symptoms" id="medicalSymptoms"
                                class="w-full h-[90px] p-2 border border-slate-300 rounded-md bg-gray-50 focus:ring-2 focus:ring-emerald-500 focus:border-transparent text-sm resize-none"
                                placeholder="Describe symptoms..." required></textarea>
                        </div>
                        <!-- Treatment -->
                        <div>
                            <label class="block text-sm font-medium text-gray-600 mb-1">Treatment</label>
                            <textarea name="medical_treatment" id="medicalTreatment"
                                class="w-full h-[90px] p-2 border border-slate-300 rounded-md bg-gray-50 focus:ring-2 focus:ring-emerald-500 focus:border-transparent text-sm resize-none"
                                placeholder="Describe treatment plan..." required></textarea>
                        </div>
                    </div>
                </div>
                <!-- Action Buttons -->
                <div class="flex justify-between mt-4 pt-2 border-t border-slate-200">
                    <button type="submit" class="bg-indigo-500 text-white px-4 py-2 rounded-md hover:bg-indigo-600 transition-colors text-sm">Save</button>
                    <button type="button" onclick="hideModal()" class="text-gray-500 hover:text-gray-700 text-sm">Cancel</button>
                </div>
                <input type="hidden" name="add_client" id="formAction" value="1">
            </form>
        </div>
    </div>

    <!-- Alternative Neat Card-Style Client Modal -->
    <div id="clientViewModal" class="fixed inset-0 bg-black bg-opacity-50 hidden flex justify-center items-center z-50 p-4">
        <div class="bg-white w-full max-w-xl rounded-lg shadow-xl overflow-hidden flex flex-col">
            <!-- Header -->
            <div class="bg-emerald-600 px-5 py-4 flex justify-between items-center">
                <h3 class="text-lg font-semibold text-white">Client Profile</h3>
                <button onclick="hideViewModal()" class="text-white hover:text-gray-200 transition">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <!-- Content -->
            <div class="p-6 space-y-5 overflow-y-auto max-h-[70vh]">
                <!-- Client Information Card -->
                <div class="border rounded-lg p-4 shadow-sm bg-gray-50">
                    <h4 class="flex items-center text-sm font-semibold text-emerald-700 mb-3">
                        <i class="fas fa-user mr-2"></i> Client Information
                    </h4>
                    <div class="grid grid-cols-2 gap-3 text-sm">
                        <div>
                            <span class="block text-gray-500">Name</span>
                            <span id="viewClientName" class="font-medium text-gray-800">-</span>
                        </div>
                        <div>
                            <span class="block text-gray-500">Contact</span>
                            <span id="viewClientContact" class="font-medium text-gray-800">-</span>
                        </div>
                        <div class="col-span-2">
                            <span class="block text-gray-500">Address</span>
                            <span id="viewClientAddress" class="font-medium text-gray-800">-</span>
                        </div>
                    </div>
                </div>
                <!-- Pet Information Card -->
                <div class="border rounded-lg p-4 shadow-sm bg-gray-50">
                    <h4 class="flex items-center text-sm font-semibold text-emerald-700 mb-3">
                        <i class="fas fa-paw mr-2"></i> Pet Information
                    </h4>
                    <div id="petInfoContainer">
                        <div id="noPetInfo" class="text-center text-gray-400 text-sm py-3 border rounded bg-white">
                            <i class="fas fa-paw mr-1"></i> No pets registered
                        </div>
                        <div id="petInfoList" class="space-y-3"></div>
                    </div>
                </div>
                <!-- Medical Information Card (e.g., Symptoms, Diagnosis, Treatment) -->
                <div class="border rounded-lg p-4 shadow-sm bg-gray-50">
                    <h4 class="flex items-center text-sm font-semibold text-emerald-700 mb-3">
                        <i class="fas fa-file-medical mr-2"></i> Medical Information
                    </h4>
                    <div id="medicalInfoContainer">
                        <div id="noMedicalInfo" class="text-center text-gray-400 text-sm py-3 border rounded bg-white">
                            <i class="fas fa-file-medical mr-1"></i> No medical information
                        </div>
                        <div id="medicalInfoList" class="space-y-3"></div>
                    </div>
                </div>
                <!-- Medical History Card (e.g., Conditions, Historical Records, Consultations) -->
                <div class="border rounded-lg p-4 shadow-sm bg-gray-50">
                    <h4 class="flex items-center text-sm font-semibold text-emerald-700 mb-3">
                        <i class="fas fa-history mr-2"></i> Medical History
                    </h4>
                    <div id="medicalHistoryContainer">
                        <div id="noMedicalHistory" class="text-center text-gray-400 text-sm py-3 border rounded bg-white">
                            <i class="fas fa-history mr-1"></i> No medical history
                        </div>
                        <div id="medicalHistoryList" class="space-y-3"></div>
                    </div>
                </div>
            </div>
            <!-- Footer -->
            <div class="bg-gray-100 px-5 py-3 flex justify-end border-t">
                <button onclick="hideViewModal()" class="px-4 py-2 text-sm bg-emerald-600 text-white rounded-md hover:bg-emerald-700 transition">
                    <i class="fas fa-times mr-1"></i> Close
                </button>
            </div>
        </div>
    </div>

    <!-- Full Medical Record Modal – 100% Matches Add/Edit Modal Style -->
    <div id="fullRecordModal" class="fixed inset-0 bg-black bg-opacity-50 hidden flex justify-center items-center z-50 p-4 overflow-auto">
        <div class="bg-white rounded-lg shadow-lg w-11/12 max-w-5xl max-h-none overflow-visible flex flex-col border border-teal-800">

            <!-- Header – IDENTICAL to Add/Edit Modal -->
            <div class="w-full bg-gradient-to-r from-emerald-600 to-teal-700 rounded-t-lg text-white border-b border-teal-800">
                <div class="flex justify-between items-center px-6 py-4">
                    <div class="flex items-center gap-3">
                        <i class="fas fa-file-medical text-2xl"></i>
                        <div>
                            <h3 class="text-lg font-bold" id="recordModalTitle">Medical Record</h3>
                            <p class="text-sm opacity-90" id="recordModalSubtitle">Loading...</p>
                        </div>
                    </div>
                    <button onclick="closeFullRecordModal()" class="text-white hover:bg-white hover:bg-opacity-20 rounded-full p-2 transition">
                        <i class="fas fa-times text-xl"></i>
                    </button>
                </div>
            </div>

            <!-- Body – full height, no scrolling -->
            <div class="p-6 overflow-visible space-y-6" id="fullRecordContent">
                <div class="text-center py-20">
                    <i class="fas fa-spinner fa-spin text-6xl text-emerald-600"></i>
                    <p class="mt-4 text-lg text-gray-600">Loading medical record...</p>
                </div>
            </div>

            <!-- Footer – Same as Add/Edit -->
            <div class="flex justify-end p-4 bg-gray-50 border-t border-slate-200">
                <button onclick="closeFullRecordModal()"
                    class="bg-emerald-600 text-white px-6 py-2.5 rounded-md hover:bg-emerald-700 transition font-medium">
                    Close
                </button>
            </div>
        </div>
    </div>


    <script>
        function showClientModal(action) {
            const modal = document.getElementById('clientModal');
            const form = document.getElementById('clientForm');
            const modalTitle = document.getElementById('modalTitle');
            const formAction = document.getElementById('formAction');
            const speciesTooltip = document.getElementById('speciesTooltip');
            const sexTooltip = document.getElementById('sexTooltip');
            // Check if required elements exist
            if (!modal || !form || !modalTitle || !formAction) {
                console.error('Required modal elements not found');
                return;
            }
            // Reset form but preserve existing values for edit mode
            const updateInput = form.querySelector('input[name="update_client"]');
            if (updateInput) updateInput.remove();
            // Enable dropdowns and hide tooltips by default
            const petSpecies = document.getElementById('petSpecies');
            const petSex = document.getElementById('petSex');
            if (petSpecies) petSpecies.disabled = false;
            if (petSex) petSex.disabled = false;
            if (speciesTooltip) speciesTooltip.style.display = 'none';
            if (sexTooltip) sexTooltip.style.display = 'none';
            // List of fields to clear required attributes
            const fields = [
                'petName', 'petSpecies', 'petSex', 'petBreed',
                'petWeight', 'petBirthDate', 'medicalCondition',
                'medicalDiagnosis', 'medicalSymptoms', 'medicalTreatment'
            ];
            // Clear required attributes for all fields initially
            fields.forEach(id => {
                const element = document.getElementById(id);
                if (element) element.removeAttribute('required');
            });
            if (action === 'add') {
                form.reset(); // Clear form for adding new client
                formAction.name = 'add_client';
                modalTitle.textContent = 'Add New Client (with optional Pet & Medical Record)';
                // Set required attributes for add mode
                fields.forEach(id => {
                    const element = document.getElementById(id);
                    if (element) element.setAttribute('required', '');
                });
            } else if (action === 'edit') {
                modalTitle.textContent = 'Edit Client, Pet, and Medical Record';
                formAction.name = 'update_client'; // This will be handled by the PHP
            } else if (action === 'view') {
                modalTitle.textContent = 'View Client, Pet, and Medical Record';
                formAction.name = ''; // No form action for view
                // Make all fields read-only and disable them
                const allInputs = form.querySelectorAll('input, select, textarea');
                allInputs.forEach(input => {
                    input.readOnly = true;
                    input.disabled = true;
                });
                // Hide save button
                const saveButton = form.querySelector('button[type="submit"]');
                if (saveButton) saveButton.style.display = 'none';
            }
            modal.classList.remove('hidden');
            // Clean URL after showing modal
            const url = new URL(window.location.href);
            url.searchParams.delete('edit_client_id');
            url.searchParams.delete('view_client_id');
            window.history.replaceState({}, document.title, url);
        }

        function hideModal() {
            document.getElementById('clientModal').classList.add('hidden');
            // Clean URL when closing modal
            const url = new URL(window.location.href);
            url.searchParams.delete('edit_client_id');
            url.searchParams.delete('view_client_id');
            window.history.replaceState({}, document.title, url);
        }
        // Function to show view modal
        function showViewModal(clientId) {
            console.log('showViewModal called with ID:', clientId);
            // Clean URL immediately to prevent other handlers from triggering
            const url = new URL(window.location.href);
            url.searchParams.delete('view_client_id');
            url.searchParams.delete('edit_client_id');
            window.history.replaceState({}, document.title, url);
            const modal = document.getElementById('clientViewModal');
            // Show modal immediately
            modal.classList.remove('hidden');
            document.body.classList.add('modal-open');
            // Set loading state
            document.getElementById('viewClientName').textContent = 'Loading...';
            document.getElementById('viewClientContact').textContent = 'Loading...';
            document.getElementById('viewClientAddress').textContent = 'Loading...';
            // Clear previous content
            const petInfoList = document.getElementById('petInfoList');
            const medicalInfoList = document.getElementById('medicalInfoList');
            const medicalHistoryList = document.getElementById('medicalHistoryList');
            const noPetInfo = document.getElementById('noPetInfo');
            const noMedicalInfo = document.getElementById('noMedicalInfo');
            const noMedicalHistory = document.getElementById('noMedicalHistory');
            if (petInfoList) petInfoList.innerHTML = '';
            if (medicalInfoList) medicalInfoList.innerHTML = '';
            if (medicalHistoryList) medicalHistoryList.innerHTML = '';
            if (noPetInfo) noPetInfo.style.display = 'block';
            if (noMedicalInfo) noMedicalInfo.style.display = 'block';
            if (noMedicalHistory) noMedicalHistory.style.display = 'block';
            // Fetch client data
            fetch(`?get_client_details=${clientId}`)
                .then(response => {
                    if (!response.ok) throw new Error(`HTTP error! Status: ${response.status}`);
                    return response.json();
                })
                .then(data => {
                    if (data.error) throw new Error(data.error);
                    if (!data.client) throw new Error('Client data not found');
                    populateViewModal(data);
                })
                .catch(error => {
                    console.error('Error fetching client details:', error);
                    showViewModalError(error.message);
                });
        }
        // Function to populate view modal with data
        function populateViewModal(data) {
            try {
                // Client Information
                document.getElementById('viewClientName').textContent = data.client.client_name || '-';
                document.getElementById('viewClientContact').textContent = data.client.client_contact_number || 'Not provided';
                document.getElementById('viewClientAddress').textContent = data.client.client_address || 'Not provided';
                // Pet Information (only basic info)
                const petInfoList = document.getElementById('petInfoList');
                const noPetInfo = document.getElementById('noPetInfo');
                if (data.pets && data.pets.length > 0) {
                    if (noPetInfo) noPetInfo.style.display = 'none';
                    petInfoList.innerHTML = data.pets.map(pet => `
                    <!-- Pet Profile Card -->
                    <div class="bg-white rounded-lg border border-gray-200 shadow-sm overflow-hidden mb-4">
                        <!-- Basic Information -->
                        <div class="p-4">
                            <h5 class="text-sm font-semibold text-gray-500 mb-3">Basic Information</h5>
                            <!-- Pet Details -->
                            <div class="grid grid-cols-2 gap-x-4 gap-y-2 text-sm">
                                <div><strong class="font-medium text-gray-500 block">Name:</strong> <span class="text-gray-800">${escapeHtml(pet.pet_name || '-')}</span></div>
                                <div><strong class="font-medium text-gray-500 block">Age:</strong> <span class="text-gray-800">${calculateAge(pet.pet_birth_date) || '-'}</span></div>
                                <div><strong class="font-medium text-gray-500 block">Breed:</strong> <span class="text-gray-800">${escapeHtml(pet.pet_breed || '-')}</span></div>
                                <div><strong class="font-medium text-gray-500 block">Sex:</strong> <span class="text-gray-800">${escapeHtml(pet.pet_sex || '-')}</span></div>
                                <div><strong class="font-medium text-gray-500 block">Weight:</strong> <span class="text-gray-800">${pet.pet_weight ? pet.pet_weight + ' kg' : '-'}</span></div>
                                <div><strong class="font-medium text-gray-500 block">Species:</strong> <span class="text-gray-800">${escapeHtml(pet.pet_species || '-')}</span></div>
                            </div>
                        </div>
                    </div>
                `).join('');
                } else {
                    if (noPetInfo) noPetInfo.style.display = 'block';
                    petInfoList.innerHTML = ''; // Clear any previous content
                }
                // Collect all medical information and history across all pets
                let allMedicalInfo = [];
                let allMedicalHistory = [];
                data.pets.forEach(pet => {
                    const medicalRecords = (data.medicalRecords || []).filter(record => record.pet_id === pet.pet_id);
                    medicalRecords.forEach(record => {
                        // Separate into medical info (symptoms, diagnosis, treatment) and history (conditions, dates, consultations)
                        allMedicalInfo.push({
                            pet_name: pet.pet_name,
                            symptoms: record.medical_symptoms,
                            diagnosis: record.medical_diagnosis,
                            treatment: record.medical_treatment,
                            date: record.record_date || record.date
                        });
                        allMedicalHistory.push({
                            pet_name: pet.pet_name,
                            condition: record.medical_condition,
                            type: 'medical',
                            date: record.record_date || record.date,
                            record_id: record.record_id // THIS WAS MISSING!
                        });
                    });
                    const consultations = (pet.consultations || []);
                    consultations.forEach(consult => {
                        allMedicalHistory.push({
                            pet_name: pet.pet_name,
                            ...consult,
                            type: 'consultation',
                            date: consult.consultation_date
                        });
                    });
                });
                // Sort by date, most recent first
                allMedicalInfo.sort((a, b) => new Date(b.date) - new Date(a.date));
                allMedicalHistory.sort((a, b) => new Date(b.date) - new Date(a.date));
                // Populate Medical Information
                const medicalInfoList = document.getElementById('medicalInfoList');
                const noMedicalInfo = document.getElementById('noMedicalInfo');
                if (allMedicalInfo.length > 0) {
                    if (noMedicalInfo) noMedicalInfo.style.display = 'none';
                    medicalInfoList.innerHTML = allMedicalInfo.map(item => `
                        <div class="bg-white rounded-lg border border-gray-200 shadow-sm overflow-hidden mb-4 p-4 text-sm">
                            <div class="flex justify-between items-center mb-4">
                                <strong class="font-semibold text-emerald-700"><i class="fas fa-file-medical mr-1"></i> Medical Info for ${escapeHtml(item.pet_name)}</strong>
                                <span class="text-gray-400">${escapeHtml(new Date(item.date).toLocaleDateString())}</span>
                            </div>
                            <p><strong class="font-medium text-gray-500">Symptoms:</strong> ${escapeHtml(item.symptoms || '-')}</p>
                            <p><strong class="font-medium text-gray-500">Diagnosis:</strong> ${escapeHtml(item.diagnosis || '-')}</p>
                            <p><strong class="font-medium text-gray-500">Treatment:</strong> ${escapeHtml(item.treatment || '-')}</p>
                        </div>
                    `).join('');
                } else {
                    if (noMedicalInfo) noMedicalInfo.style.display = 'block';
                }
                // Populate Medical History
                const medicalHistoryList = document.getElementById('medicalHistoryList');
                const noMedicalHistory = document.getElementById('noMedicalHistory');
                if (allMedicalHistory.length > 0) {
                    if (noMedicalHistory) noMedicalHistory.style.display = 'none';
                    medicalHistoryList.innerHTML = allMedicalHistory.map(item => {
                        if (item.type === 'medical') {
                            return `
                                <div class="bg-white rounded-md border p-3 text-xs">
                                    <div class="flex justify-between items-center mb-2">
                                        <strong class="font-semibold text-emerald-700"><i class="fas fa-file-medical mr-1"></i> Medical Record for ${escapeHtml(item.pet_name)}</strong>
                                        <span class="text-gray-400 text-xs">${escapeHtml(new Date(item.date).toLocaleDateString())}</span>
                                    </div>
                                    <div class="flex justify-between items-center">
                                        <p class="flex-1"><strong class="font-medium text-gray-500">Condition:</strong> ${escapeHtml(item.condition || '-')}</p>
                                        <button onclick="showFullRecord(${item.record_id || ''})" class="text-green-500 hover:text-green-700 ml-2 p-1 rounded-full focus:outline-none focus:ring-2 focus:ring-green-500">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                    </div>
                                </div>
                            `;
                        } else if (item.type === 'consultation') {
                            return `
                                <div class="bg-white rounded-md border p-3 text-xs">
                                    <div class="flex justify-between items-center mb-2">
                                        <strong class="font-semibold text-indigo-700"><i class="fas fa-stethoscope mr-1"></i> Consultation for ${escapeHtml(item.pet_name)}</strong>
                                        <span class="text-gray-400">${escapeHtml(new Date(item.date).toLocaleDateString())}</span>
                                    </div>
                                    <p><strong class="font-medium text-gray-500">Vet:</strong> ${escapeHtml(item.vet_name || 'N/A')}</p>
                                    <div class="flex justify-between items-center">
                                        <p class="flex-1"><strong class="font-medium text-gray-500">Notes:</strong> ${escapeHtml(item.notes || '-')}</p>
                                    </div>
                                </div>
                            `;
                        }
                        return '';
                    }).join('');
                } else {
                    if (noMedicalHistory) noMedicalHistory.style.display = 'block';
                }
            } catch (error) {
                console.error('Error populating view modal:', error);
                showViewModalError('Error displaying client details');
            }
        }
        // Function to show error in view modal
        function showViewModalError(errorMessage) {
            document.getElementById('viewClientName').textContent = 'Error loading data';
            document.getElementById('viewClientContact').textContent = '-';
            document.getElementById('viewClientAddress').textContent = '-';
            const petInfoList = document.getElementById('petInfoList');
            const medicalInfoList = document.getElementById('medicalInfoList');
            const medicalHistoryList = document.getElementById('medicalHistoryList');
            const noPetInfo = document.getElementById('noPetInfo');
            const noMedicalInfo = document.getElementById('noMedicalInfo');
            const noMedicalHistory = document.getElementById('noMedicalHistory');
            if (noPetInfo) noPetInfo.style.display = 'none';
            if (noMedicalInfo) noMedicalInfo.style.display = 'none';
            if (noMedicalHistory) noMedicalHistory.style.display = 'none';
            if (petInfoList) {
                petInfoList.innerHTML = `<div class="text-center text-red-500 text-sm py-2">
                <i class="fas fa-exclamation-triangle mr-1"></i>Error loading pets: ${escapeHtml(errorMessage)}
            </div>`;
            }
            if (medicalInfoList) {
                medicalInfoList.innerHTML = `<div class="text-center text-red-500 text-sm py-2">
                <i class="fas fa-exclamation-triangle mr-1"></i>Error loading medical info: ${escapeHtml(errorMessage)}
            </div>`;
            }
            if (medicalHistoryList) {
                medicalHistoryList.innerHTML = `<div class="text-center text-red-500 text-sm py-2">
                <i class="fas fa-exclamation-triangle mr-1"></i>Error loading medical history: ${escapeHtml(errorMessage)}
            </div>`;
            }
        }
        // Function to hide view modal
        function hideViewModal() {
            console.log('Hiding view modal');
            const modal = document.getElementById('clientViewModal');
            modal.classList.add('hidden');
            document.body.classList.remove('modal-open');
            // Reset content
            document.getElementById('viewClientName').textContent = '-';
            document.getElementById('viewClientContact').textContent = '-';
            document.getElementById('viewClientAddress').textContent = '-';
            const noPetInfo = document.getElementById('noPetInfo');
            const noMedicalInfo = document.getElementById('noMedicalInfo');
            const noMedicalHistory = document.getElementById('noMedicalHistory');
            const petInfoList = document.getElementById('petInfoList');
            const medicalInfoList = document.getElementById('medicalInfoList');
            const medicalHistoryList = document.getElementById('medicalHistoryList');
            if (noPetInfo) noPetInfo.style.display = 'block';
            if (noMedicalInfo) noMedicalInfo.style.display = 'block';
            if (noMedicalHistory) noMedicalHistory.style.display = 'block';
            if (petInfoList) petInfoList.innerHTML = '';
            if (medicalInfoList) medicalInfoList.innerHTML = '';
            if (medicalHistoryList) medicalHistoryList.innerHTML = '';
        }
        // Helper function to escape HTML
        function escapeHtml(unsafe) {
            if (unsafe === null || unsafe === undefined) return '';
            return unsafe
                .toString()
                .replace(/&/g, "&amp;")
                .replace(/</g, "&lt;")
                .replace(/>/g, "&gt;")
                .replace(/"/g, "&quot;")
                .replace(/'/g, "&#039;");
        }
        // Helper function to calculate age from birth date
        function calculateAge(birthDate) {
            if (!birthDate) return '-';
            try {
                const birth = new Date(birthDate);
                const now = new Date();
                let years = now.getFullYear() - birth.getFullYear();
                let months = now.getMonth() - birth.getMonth();
                if (months < 0) {
                    years--;
                    months += 12;
                }
                if (years === 0) {
                    return `${months} month${months !== 1 ? 's' : ''}`;
                } else {
                    return `${years} year${years !== 1 ? 's' : ''} ${months} month${months !== 1 ? 's' : ''}`;
                }
            } catch (error) {
                console.error('Error calculating age:', error);
                return '-';
            }
        }

        function showFullRecord(recordId) {
            const modal = document.getElementById('fullRecordModal');
            const content = document.getElementById('fullRecordContent');
            const title = document.getElementById('recordModalTitle');
            const subtitle = document.getElementById('recordModalSubtitle');

            // Show loading state
            title.textContent = "Medical Record";
            subtitle.textContent = "Loading...";
            content.innerHTML = `<div class="text-center py-20"><i class="fas fa-spinner fa-spin text-6xl text-emerald-600"></i></div>`;
            modal.classList.remove('hidden');

            fetch(`?get_medical_record_details=${recordId}`, {
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                })
                .then(r => r.json())
                .then(data => {
                    if (data.error) throw new Error(data.error);

                    const r = data.record;
                    const p = data.pet;
                    const c = data.client;
                    const vet = data.vet_name ? `Dr. ${escapeHtml(data.vet_name)}` : 'Veterinarian';

                    // Update header
                    title.textContent = `${escapeHtml(p.pet_name)}'s Medical Record`;
                    subtitle.textContent = r.record_date ?
                        new Date(r.record_date).toLocaleDateString('en-PH', {
                            dateStyle: 'long'
                        }) :
                        'Date not recorded';

                    // Fill content – same layout
                    content.innerHTML = `
                        <!-- Client & Pet Summary -->
                        <div class="bg-slate-50 p-4 rounded-lg border border-slate-200 text-sm">
                            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                                <div>
                                    <span class="block text-xs text-gray-500">Owner</span>
                                    <span class="font-semibold text-gray-800">${escapeHtml(c.client_name)}</span>
                                </div>
                                <div>
                                    <span class="block text-xs text-gray-500">Contact</span>
                                    <span class="text-gray-700">${escapeHtml(c.client_contact_number || '—')}</span>
                                </div>
                                <div>
                                    <span class="block text-xs text-gray-500">Pet</span>
                                    <span class="font-semibold text-emerald-700">${escapeHtml(p.pet_name)}</span>
                                    <span class="text-gray-600 text-xs"> (${escapeHtml(p.pet_species)} • ${escapeHtml(p.pet_breed || 'N/A')})</span>
                                </div>
                            </div>
                        </div>

                        <!-- Medical Details in 2 Columns -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-5 mt-5">
                            <!-- Left Column -->
                            <div class="space-y-4">
                                <div class="bg-white p-4 rounded-lg shadow-sm border border-slate-200">
                                    <h4 class="text-sm font-semibold text-emerald-600 mb-2 flex items-center gap-2"><i class="fas fa-stethoscope"></i> Conditions</h4>
                                    ${r.medical_condition ? `
                                        <div class="flex flex-wrap gap-1">
                                            ${r.medical_condition.split(',').map(cond => `<span class="condition-tag text-xs">${escapeHtml(cond.trim())}</span>`).join('')}
                                        </div>
                                    ` : '<p class="text-gray-400 italic text-xs">No conditions recorded</p>'}
                                </div>
                                <div class="bg-white p-4 rounded-lg shadow-sm border border-red-200 border-l-4 border-l-red-500">
                                    <h4 class="font-semibold text-red-700 mb-2 flex items-center gap-2 text-sm"><i class="fas fa-thermometer-half"></i> Symptoms</h4>
                                    <p class="text-gray-700 text-xs leading-relaxed whitespace-pre-wrap">${escapeHtml(r.medical_symptoms || 'Not recorded')}</p>
                                </div>
                            </div>

                            <!-- Right Column -->
                            <div class="space-y-4">
                                <div class="bg-white p-4 rounded-lg shadow-sm border border-blue-200 border-l-4 border-l-blue-500">
                                    <h4 class="font-semibold text-blue-700 mb-2 flex items-center gap-2 text-sm"><i class="fas fa-diagnoses"></i> Diagnosis</h4>
                                    <p class="text-gray-700 text-xs leading-relaxed whitespace-pre-wrap">${escapeHtml(r.medical_diagnosis || 'Not recorded')}</p>
                                </div>
                                <div class="bg-white p-4 rounded-lg shadow-sm border border-teal-200 border-l-4 border-l-teal-500">
                                    <h4 class="font-semibold text-teal-700 mb-2 flex items-center gap-2 text-sm"><i class="fas fa-prescription-bottle-alt"></i> Treatment Plan</h4>
                                    <p class="text-gray-700 text-xs leading-relaxed whitespace-pre-wrap">${escapeHtml(r.medical_treatment || 'Not recorded')}</p>
                                </div>
                            </div>
                        </div>

                        <!-- Footer Info -->
                        <div class="text-center pt-4 mt-4 border-t border-slate-200 text-xs text-gray-500">
                            <p><strong>Veterinarian:</strong> ${vet}</p>
                            <p class="text-emerald-600 font-medium mt-1">Balingasag Dog & Cat Clinic • Record ID: ${recordId}</p>
                        </div>
                    `;
                })
                .catch(err => {
                    content.innerHTML = `
            <div class="text-center py-16 text-red-600">
                <i class="fas fa-exclamation-triangle text-6xl"></i>
                <p class="mt-4 text-lg font-semibold">Failed to Load Record</p>
                <p class="text-sm text-gray-600">${escapeHtml(err.message)}</p>
            </div>
        `;
                });
        }

        function closeFullRecordModal() {
            document.getElementById('fullRecordModal').classList.add('hidden');
        }


        // Handle URL parameters on page load
        document.addEventListener('DOMContentLoaded', function() {
            const urlParams = new URLSearchParams(window.location.search);
            const viewClientId = urlParams.get('view_client_id');
            if (viewClientId) {
                console.log('Found view_client_id in URL on page load:', viewClientId);
                // Small timeout to ensure DOM is fully ready
                setTimeout(() => {
                    showViewModal(viewClientId);
                }, 100);
            }
        });

        function confirmDelete(clientId) {
            if (typeof Swal === 'undefined') {
                // Fallback if SweetAlert2 fails to load
                if (confirm('Are you sure you want to delete this client, their associated pets, and medical records?')) {
                    window.location.href = `?delete_client_id=${clientId}`;
                }
                return false;
            }
            Swal.fire({
                title: 'Are you sure?',
                text: 'This will also delete all associated pets and medical records. You won\'t be able to revert this!',
                icon: 'warning',
                background: '#1e293b',
                color: '#e2e8f0',
                showCancelButton: true,
                confirmButtonColor: '#dc2626',
                cancelButtonColor: '#6b7280',
                confirmButtonText: 'Yes, delete client, pets, and records!'
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location.href = `?delete_client_id=${clientId}`;
                }
            });
            return false;
        }
        // Show SweetAlert2 for success messages on page load
        <?php if (isset($_GET['message'])): ?>
            document.addEventListener('DOMContentLoaded', function() {
                if (typeof Swal !== 'undefined') {
                    Swal.fire({
                        title: 'Success',
                        text: <?= json_encode($_GET['message']) ?>,
                        icon: 'success',
                        background: '#1e293b',
                        color: '#e2e8f0',
                        confirmButtonColor: '#6366f1',
                        confirmButtonText: 'OK'
                    }).then(() => {
                        // Clean URL after showing the success message
                        const url = new URL(window.location.href);
                        url.searchParams.delete('message');
                        window.history.replaceState({}, document.title, url);
                    });
                } else {
                    // Fallback to alert if SweetAlert2 is not loaded
                    alert(<?= json_encode($_GET['message']) ?>);
                    // Clean URL
                    const url = new URL(window.location.href);
                    url.searchParams.delete('message');
                    window.history.replaceState({}, document.title, url);
                }
            });
        <?php endif; ?>
        // Populate fields for edit mode
        <?php if ($clientToEdit): ?>
            document.addEventListener('DOMContentLoaded', function() {
                console.log('Client to edit:', <?= json_encode($clientToEdit) ?>); // Debug client data
                console.log('Pet to edit:', <?= json_encode($petToEdit) ?>); // Debug pet data
                console.log('Medical record to edit:', <?= json_encode($medicalRecordToEdit) ?>); // Debug medical record data
                showClientModal('edit');
                // Set client values
                document.getElementById('client_id').value = <?= json_encode($clientToEdit['client_id'] ?? '') ?>;
                document.getElementById('clientName').value = <?= json_encode($clientToEdit['client_name'] ?? '') ?>;
                document.getElementById('clientAddress').value = <?= json_encode($clientToEdit['client_address'] ?? '') ?>;
                // Convert 639... → 09... when editing
                let contactNumber = <?= json_encode($clientToEdit['client_contact_number'] ?? '') ?>;
                if (contactNumber && contactNumber.startsWith('63') && contactNumber.length === 12) {
                    contactNumber = '0' + contactNumber.substring(2);
                }
                document.getElementById('clientContactNumber').value = contactNumber;
                // Set pet values if exists
                <?php if ($petToEdit): ?>
                    document.getElementById('pet_id').value = <?= json_encode($petToEdit['pet_id'] ?? '') ?>;
                    document.getElementById('petName').value = <?= json_encode($petToEdit['pet_name'] ?? '') ?>;
                    document.getElementById('petSex').value = <?= json_encode($petToEdit['pet_sex'] ?? '') ?>;
                    document.getElementById('petBreed').value = <?= json_encode($petToEdit['pet_breed'] ?? '') ?>; // Corrected ID
                    document.getElementById('petWeight').value = <?= json_encode($petToEdit['pet_weight'] ?? '') ?>;
                    document.getElementById('petBirthDate').value = <?= json_encode($petToEdit['pet_birth_date'] ?? '') ?>;
                    document.getElementById('petSpecies').value = <?= json_encode($petToEdit['pet_species'] ?? '') ?>;
                    document.getElementById('petSpecies').disabled = false;
                    document.getElementById('petSex').disabled = false;
                    document.getElementById('speciesTooltip').style.display = 'none';
                    document.getElementById('sexTooltip').style.display = 'none';
                    // Set required attributes for pet fields when pet exists
                    document.getElementById('petName').setAttribute('required', '');
                    document.getElementById('petSpecies').setAttribute('required', '');
                    document.getElementById('petSex').setAttribute('required', '');
                    document.getElementById('petBreed').setAttribute('required', '');
                    document.getElementById('petWeight').setAttribute('required', '');
                    document.getElementById('petBirthDate').setAttribute('required', '');
                <?php else: ?>
                    // Clear pet fields if no pet exists
                    document.getElementById('pet_id').value = '';
                    document.getElementById('petName').value = '';
                    document.getElementById('petSex').value = '';
                    document.getElementById('petBreed').value = '';
                    document.getElementById('petWeight').value = '';
                    document.getElementById('petBirthDate').value = '';
                    document.getElementById('petSpecies').value = '';
                    document.getElementById('petSpecies').disabled = false;
                    document.getElementById('petSex').disabled = false;
                    document.getElementById('speciesTooltip').style.display = 'none';
                    document.getElementById('sexTooltip').style.display = 'none';
                <?php endif; ?>
                // Set medical record values if exists
                <?php if ($medicalRecordToEdit): ?>
                    document.getElementById('record_id').value = <?= json_encode($medicalRecordToEdit['record_id'] ?? '') ?>;
                    document.getElementById('medicalConditionHidden').value = <?= json_encode($medicalRecordToEdit['medical_condition'] ?? '') ?>;
                    document.getElementById('medicalDiagnosis').value = <?= json_encode($medicalRecordToEdit['medical_diagnosis'] ?? '') ?>;
                    document.getElementById('medicalSymptoms').value = <?= json_encode($medicalRecordToEdit['medical_symptoms'] ?? '') ?>;
                    document.getElementById('medicalTreatment').value = <?= json_encode($medicalRecordToEdit['medical_treatment'] ?? '') ?>;
                    // Set required attributes for medical record fields when record exists (if pet_id is also present)
                    document.getElementById('medicalCondition').setAttribute('required', '');
                    document.getElementById('medicalDiagnosis').setAttribute('required', '');
                    document.getElementById('medicalSymptoms').setAttribute('required', '');
                    document.getElementById('medicalTreatment').setAttribute('required', '');
                <?php else: ?>
                    // Clear medical record fields if no record exists
                    document.getElementById('record_id').value = '';
                    document.getElementById('medicalConditionHidden').value = '';
                    document.getElementById('medicalDiagnosis').value = '';
                    document.getElementById('medicalSymptoms').value = '';
                    document.getElementById('medicalTreatment').value = '';
                <?php endif; ?>
            });
        <?php endif; ?>

        function toggleModal(modalId) {
            console.log("Toggling modal:", modalId); // Debug log
            const modal = document.getElementById(modalId);
            if (modal) {
                modal.classList.toggle('hidden');
            } else {
                console.error("Modal not found:", modalId);
            }
        }
        document.getElementById('clientForm').addEventListener('submit', function(event) {
            const currentModalTitle = document.getElementById('modalTitle').textContent;
            if (currentModalTitle.includes('View')) {
                event.preventDefault(); // Prevent submit in view mode
                return;
            }
            const medicalFields = ['medicalConditionHidden', 'medicalDiagnosis', 'medicalSymptoms', 'medicalTreatment'];
            const petFields = ['petName', 'petSpecies', 'petSex', 'petBreed', 'petWeight', 'petBirthDate'];
            const petId = document.getElementById('pet_id').value.trim();
            const hasPetData = petFields.some(id => document.getElementById(id) && document.getElementById(id).value.trim());
            // Debugging: Log form data
            console.log('Submitting form with pet_id:', petId);
            console.log('Pet fields:', petFields.map(id => ({
                [id]: document.getElementById(id).value.trim()
            })));
            // If any pet field is filled, all pet fields must be filled
            if (hasPetData) {
                for (const id of petFields) { // Ensure all pet fields are filled if any are
                    if (!document.getElementById(id).value.trim()) {
                        event.preventDefault();
                        Swal.fire({
                            title: 'Error',
                            text: 'All pet fields are required if any pet field is filled.',
                            icon: 'error',
                            background: '#1e293b',
                            color: '#e2e8f0',
                            confirmButtonColor: '#6366f1'
                        });
                        return;
                    }
                }
            }
            // If any medical field is filled, all medical fields must be filled
            const hasMedicalData = medicalFields.some(id => document.getElementById(id) && document.getElementById(id).value.trim());
            if (hasMedicalData) {
                for (const id of medicalFields) {
                    if (!document.getElementById(id).value.trim()) {
                        event.preventDefault();
                        Swal.fire({
                            title: 'Error',
                            text: 'All medical record fields are required if any medical field is filled.',
                            icon: 'error',
                            background: '#1e293b',
                            color: '#e2e8f0',
                            confirmButtonColor: '#6366f1'
                        });
                        return;
                    }
                }
            }
        });
        // Handle multiple conditions with tag-like input
        document.addEventListener('DOMContentLoaded', function() {
            const conditionsContainer = document.getElementById('conditionsContainer');
            const conditionInput = document.getElementById('conditionInput');
            const medicalConditionHidden = document.getElementById('medicalConditionHidden');
            const conditionSuggestions = document.getElementById('conditionSuggestions');
            let conditions = [];
            // Function to update hidden input with concatenated conditions
            function updateHiddenInput() {
                medicalConditionHidden.value = conditions.join(', ');
            }
            // Function to render tags
            function renderTags() {
                // Clear existing tags (except input)
                conditionsContainer.querySelectorAll('.condition-tag').forEach(tag => tag.remove());
                // Add tags before the input
                conditions.forEach((condition, index) => {
                    const tag = document.createElement('span');
                    tag.className = 'condition-tag bg-emerald-100 text-emerald-800 text-xs font-medium px-2 py-1 rounded mr-1 mb-1 flex items-center';
                    tag.innerHTML = `
            ${escapeHtml(condition)}
            <span class="ml-1 cursor-pointer text-emerald-600 hover:text-emerald-800" data-index="${index}">&times;</span>
        `;
                    conditionsContainer.insertBefore(tag, conditionInput);
                });
                updateHiddenInput();
            }
            // Handle input events
            conditionInput.addEventListener('input', function(e) {
                const value = this.value.trim();
                // If a comma is entered, add the condition and reset input
                if (value.includes(',')) {
                    const newConditions = value.split(',').map(c => c.trim()).filter(c => c);
                    newConditions.forEach(condition => {
                        if (condition && !conditions.includes(condition)) {
                            conditions.push(condition);
                        }
                    });
                    this.value = '';
                    renderTags();
                }
            });
            // Handle Enter key to add condition
            conditionInput.addEventListener('keydown', function(e) {
                if (e.key === 'Enter' || e.key === ',') {
                    e.preventDefault();
                    const value = this.value.trim();
                    if (value && !conditions.includes(value)) {
                        conditions.push(value);
                        this.value = '';
                        renderTags();
                    }
                }
            });
            // Handle tag removal
            conditionsContainer.addEventListener('click', function(e) {
                if (e.target.classList.contains('cursor-pointer')) {
                    const index = parseInt(e.target.dataset.index);
                    conditions.splice(index, 1);
                    renderTags();
                }
            });
            // Handle datalist selection
            conditionInput.addEventListener('change', function() {
                const value = this.value.trim();
                if (value && !conditions.includes(value)) {
                    // Check if the selected value is in the datalist
                    const options = Array.from(conditionSuggestions.options).map(opt => opt.value);
                    if (options.includes(value)) {
                        conditions.push(value);
                        this.value = '';
                        renderTags();
                    }
                }
            });
            // Populate conditions in edit mode
            <?php if ($medicalRecordToEdit && !empty($medicalRecordToEdit['medical_condition'])): ?>
                conditions = <?= json_encode(array_map('trim', explode(',', $medicalRecordToEdit['medical_condition']))) ?>;
                renderTags();
            <?php endif; ?>
            // Helper function to escape HTML (already defined in your code)
            function escapeHtml(unsafe) {
                if (unsafe === null || unsafe === undefined) return '';
                return unsafe
                    .toString()
                    .replace(/&/g, "&amp;")
                    .replace(/</g, "&lt;")
                    .replace(/>/g, "&gt;")
                    .replace(/"/g, "&quot;")
                    .replace(/'/g, "&#039;");
            }
        });
        document.addEventListener('DOMContentLoaded', function() {
            const urlParams = new URLSearchParams(window.location.search);
            let currentSort = urlParams.get('sort') ? urlParams.get('sort').toLowerCase() : 'asc';
            const searchInput = document.getElementById('search');
            const sortButton = document.getElementById('sortButton');
            const sortIcon = document.getElementById('sortIcon');
            const tbody = document.querySelector('tbody');
            const noResults = document.getElementById('noResults');
            // Set initial icon based on current sort
            if (sortIcon) {
                sortIcon.className = `fas ${currentSort === 'asc' ? 'fa-sort-alpha-up' : 'fa-sort-alpha-down'}`;
            }
            // Handle sort button click
            if (sortButton) {
                sortButton.addEventListener('click', function() {
                    currentSort = urlParams.get('sort') === 'desc' ? 'asc' : 'desc';
                    if (sortIcon) {
                        sortIcon.className = currentSort === 'asc' ? 'fas fa-sort-alpha-up' : 'fas fa-sort-alpha-down';
                    }
                    const url = new URL(window.location.href);
                    if (currentSort === 'asc') {
                        url.searchParams.delete('sort');
                    } else {
                        url.searchParams.set('sort', currentSort);
                    }
                    window.location.href = url.toString();
                });
            }
            // Handle search input (live filtering)
            if (searchInput) {
                searchInput.addEventListener('input', function() {
                    // Update URL
                    const url = new URL(window.location.href);
                    const searchValue = this.value.trim();
                    if (searchValue) {
                        url.searchParams.set('search', searchValue);
                    } else {
                        url.searchParams.delete('search');
                    }
                    window.history.replaceState({}, document.title, url);
                    applySortAndFilter();
                });
            }
            // Function to sort rows
            function sortRows(direction) {
                const rows = Array.from(tbody.querySelectorAll('tr'));
                rows.sort((a, b) => {
                    const nameA = a.dataset.name.toLowerCase();
                    const nameB = b.dataset.name.toLowerCase();
                    if (nameA < nameB) return direction === 'asc' ? -1 : 1;
                    if (nameA > nameB) return direction === 'asc' ? 1 : -1;
                    return 0;
                });
                rows.forEach(row => tbody.appendChild(row));
            }
            // Filter function
            function filterClients() {
                const searchTerm = searchInput ? searchInput.value.toLowerCase().trim() : '';
                let visibleCount = 0;
                const rows = tbody.querySelectorAll('tr');
                rows.forEach(row => {
                    const name = row.dataset.name;
                    const address = row.dataset.address;
                    const contact = row.dataset.contact;
                    const matchesSearch = !searchTerm ||
                        name.includes(searchTerm) ||
                        address.includes(searchTerm) ||
                        contact.includes(searchTerm);
                    row.style.display = matchesSearch ? '' : 'none';
                    if (matchesSearch) visibleCount++;
                });
                if (noResults) {
                    noResults.style.display = (visibleCount === 0) ? 'block' : 'none';
                }
            }
            // Apply initial sort and filter on page load
            applySortAndFilter();
        });
    </script>
    <!-- scripts -->
    <script src="./js/dashboard.js"></script>
    <script src="./js/sidebarHandler.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="./js/confirmLogout.js"></script>
    <script src="./js/edit-profile.js"></script>
    <script src="./js/notification-bell.js"></script>
    <script src="./js/customize-loader.js"></script>
</body>

</html>
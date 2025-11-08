<?php
require_once '../functions/auth.php';
require_once '../functions/admin-dashboard-handler.php';
requireAdmin();

// Fetch admin data
if (!isset($currentAdmin)) {
    $stmt = $pdo->prepare("SELECT * FROM admin WHERE admin_id = ?");
    $stmt->execute([$_SESSION['admin_id']]);
    $currentAdmin = $stmt->fetch(PDO::FETCH_ASSOC);
}

// Define $adminName
$adminName = htmlspecialchars($currentAdmin['admin_name'] ?? 'Admin');

// Debug session and admin data
error_log("Debug: \$_SESSION in admin-dashboard.php: " . json_encode($_SESSION));
error_log("Debug: \$currentAdmin in admin-dashboard.php: " . json_encode($currentAdmin));

if (
    isset($_SESSION['last_activity']) &&
    (time() - $_SESSION['last_activity']) > $_SESSION['expire_time']
) {
    session_unset();
    session_destroy();
    header("Location: index.php?expired=1");
    exit;
}
$_SESSION['last_activity'] = time();

// Include edit-profile.php after defining $currentAdmin
ob_end_flush();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    <link rel="icon" href="../image/MainIcon.png" type="image/x-icon">
    <script src="../Assets/chart.js"></script>
    <link rel="stylesheet" href="../Assets/FontAwsome/css/all.min.css">
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        .chart-container {
            position: relative;
            height: 300px;
            width: 100%;
            padding: 10px;
            border-radius: 8px;
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
        }

        @media (min-width: 768px) {
            .chart-container {
                height: 400px;
            }
        }

        .table-container {
            overflow-x: auto;
        }

        table {
            width: 100%;
            min-width: 400px;
        }

        #dropdownMenu {
            width: 80vw;
            max-width: 260px;
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
    <?php include '../includes/edit-profile.php'; ?>

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
                <img src="../image/MainIconWhite.png" alt="Dashboard" class="w-6 lg:w-8">
                <span class="md:inline">Dashboard</span>
            </h2>
            <button id="closeSidebarBtn"
                class="lg:hidden text-gray-300 hover:text-white duration-200">
                <i class="fas fa-times text-xl"></i>
            </button>
        </div>

        <!-- Navigation -->
        <nav class="flex-grow mt-8 lg:mt-12 space-y-0.5">
            <a href="admin-dashboard.php"
                class="block text-sm text-white bg-teal-800 hover:bg-emerald-700 px-4 py-2 rounded-md transition-colors">
                <i class="fas fa-tachometer-alt mr-2"></i> Dashboard
            </a>
            <a href="admin.php"
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

                    <a href="./records/pet-records.php"
                        class="flex items-center text-sm text-gray-200 hover:bg-emerald-600 px-3 py-2 rounded-md hover:text-white transition-colors">
                        <i class="fas fa-paw mr-2"></i> Pets
                    </a>

                    <a href="./records/client-records.php"
                        class="flex items-center text-sm text-gray-200 hover:bg-emerald-600 px-3 py-2 rounded-md hover:text-white transition-colors">
                        <i class="fas fa-user mr-2"></i> Clients
                    </a>

                    <a href="./records/medical-records.php"
                        class="flex items-center text-sm text-gray-200 hover:bg-emerald-600 px-3 py-2 rounded-md hover:text-white transition-colors">
                        <i class="fas fa-file-medical mr-2"></i>
                        <span class="whitespace-normal leading-snug">Medical Records</span>
                    </a>

                    <a href="./records/admin-payments.php"
                        class="flex items-center text-sm text-gray-200 hover:bg-emerald-600 px-3 py-2 rounded-md hover:text-white transition-colors">
                        <i class="fas fa-credit-card mr-2"></i> Payments Records
                    </a>
                </div>
            </div>


            <!-- Active Link Example -->
            <a href="./admin-appointments.php"
                class="block text-sm text-white hover:bg-emerald-700 px-4 py-2 rounded-md">
                <i class="fas fa-calendar-days mr-2"></i> Appointments
            </a>

            <a href="../includes/sitemap/admin-help.php"
                class="block text-sm text-gray-200 hover:bg-emerald-600 px-4 py-2 rounded-md hover:text-white transition-colors">
                <i class="fas fa-question-circle mr-2"></i> Help/Support
            </a>
        </nav>

        <!-- Logout -->
        <div class="pt-4">
            <a href="#" onclick="confirmLogout(event)"
                class="block text-sm text-gray-200 hover:bg-red-600 px-4 py-2 rounded-md transition-colors">
                <i class="fas fa-sign-out-alt mr-2"></i> Logout
            </a>
        </div>
    </aside>

    <!-- Overlay for mobile menu -->
    <div id="overlay" class="lg:hidden fixed inset-0 bg-black bg-opacity-50 z-30 hidden"></div>


    <!-- Main Content -->
    <div class="relative ml-0 lg:ml-52 p-4 pt-16 lg:pt-4 min-h-screen">

        <div id="loadingScreen" class="absolute inset-0 flex flex-col items-center justify-center bg-white bg-opacity-75 z-50 hidden">
            <img src="../image/MainIcon.png" alt="Loading Icon" class="w-20 h-20 animate-pulse">
            <p class="mt-4 text-teal-700 font-semibold text-lg">Loading...</p>
        </div>

        <!-- Header with Welcome and Metrics -->
        <header class="bg-white shadow-lg rounded-lg text-gray-800 py-4 mb-8 p-4 lg:p-6 border border-slate-200">

            <!-- Top Section with Dropdown -->
            <div class="flex justify-between items-center mb-6">
                <h1 class="text-xl lg:text-2xl font-bold">Dashboard</h1>

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
                                            <?php echo $adminName; ?>
                                        </p>
                                        <p class="text-xs text-gray-500">
                                            Admin
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

            <!-- Metrics Grid -->
            <div class="grid grid-cols-1 sm:grid-cols-3 lg:grid-cols-6 gap-3 mt-4">
                <div
                    class="bg-white p-3 rounded-md h-full relative shadow-md border border-slate-200 hover:border-indigo-400 transition-colors">
                    <a href="./records/client-records.php"
                        class="absolute top-1 right-2 text-gray-500 hover:text-indigo-400 transition-colors text-sm">
                        <i class="fa-solid fa-arrow-up-right-from-square"></i>
                    </a>
                    <div class="text-center mt-2">
                        <h3 class="font-semibold text-lg mb-1"><i
                                class="fas fa-user mr-1 text-lg text-indigo-500"></i> Clients</h3>
                        <p class="text-base"><?= $clientCount ?></p>
                    </div>
                </div>

                <div
                    class="bg-white p-3 rounded-md h-full relative shadow-md border border-slate-200 hover:border-indigo-400 transition-colors">
                    <a href="./admin.php"
                        class="absolute top-1 right-2 text-gray-500 hover:text-indigo-400 transition-colors text-sm">
                        <i class="fa-solid fa-arrow-up-right-from-square"></i>
                    </a>
                    <div class="text-center mt-2">
                        <h3 class="font-semibold text-base mb-1"><i
                                class="fas fa-user-md mr-1 text-base text-indigo-500"></i> Vets</h3>
                        <p class="text-sm"><?= $vetCount ?></p>
                    </div>
                </div>

                <div
                    class="bg-white p-3 rounded-md relative shadow-md border border-slate-200 hover:border-indigo-400 transition-colors">
                    <a href="./records/pet-records.php"
                        class="absolute top-1 right-2 text-gray-500 hover:text-indigo-400 transition-colors text-sm">
                        <i class="fa-solid fa-arrow-up-right-from-square"></i>
                    </a>
                    <div class="text-center mt-2">
                        <h3 class="font-semibold text-lg mb-1"><i
                                class="fas fa-paw mr-1 text-lg text-teal-500"></i> Pets</h3>
                        <p class="text-base"><?= $petCount ?></p>
                    </div>
                </div>

                <div
                    class="bg-white p-3 rounded-md relative shadow-md border border-slate-200 hover:border-indigo-400 transition-colors">
                    <a href="./records/medical-records.php"
                        class="absolute top-1 right-2 text-gray-500 hover:text-indigo-400 transition-colors text-sm">
                        <i class="fa-solid fa-arrow-up-right-from-square"></i>
                    </a>
                    <div class="text-center mt-2">
                        <h3 class="font-semibold text-lg mb-1"><i
                                class="fas fa-file-medical mr-1 text-lg text-blue-500"></i> Records</h3>
                        <p class="text-base"><?= $recordCount ?></p>
                    </div>
                </div>

                <div
                    class="bg-white p-3 rounded-md relative shadow-md border border-slate-200 hover:border-indigo-400 transition-colors">
                    <a href="./records/admin-payments.php"
                        class="absolute top-1 right-2 text-gray-500 hover:text-indigo-400 transition-colors text-sm">
                        <i class="fa-solid fa-arrow-up-right-from-square"></i>
                    </a>
                    <div class="text-center mt-2">
                        <h3 class="font-semibold text-lg mb-1"><i class="fa-solid fa-money-bill-wave mr-1 text-lg text-indigo-500"></i>Payments</h3>
                        <p class="text-base">₱<?= number_format($totalPayment, 2) ?></p>
                    </div>
                </div>

                <!-- Appointments Today -->
                <div
                    class="bg-white p-3 rounded-md relative shadow-md border border-slate-200 hover:border-indigo-400 transition-colors">
                    <a href="../admin/admin-appointments.php"
                        class="absolute top-1 right-2 text-gray-500 hover:text-indigo-400 transition-colors text-sm">
                        <i class="fa-solid fa-arrow-up-right-from-square"></i>
                    </a>
                    <div class="text-center mt-2">
                        <h3 class="font-semibold text-lg mb-1"><i
                                class="fa-solid fa-calendar-check mr-1 text-lg text-green-500"></i> Today</h3>
                        <p class="text-base"><?php echo isset($appointmentsToday) ? $appointmentsToday : 0; ?></p>
                    </div>
                </div>
            </div>
        </header>

        <!-- Graph Section -->
        <main class="bg-white p-4 lg:p-6 rounded-lg shadow-lg border border-slate-200">
            <h2 class="text-lg sm:text-xl lg:text-2xl font-semibold text-gray-800 mb-6">Analytics Overview</h2>
            <div class="flex flex-col lg:flex-row gap-8">
                <div class="flex-1 bg-white border border-slate-200 rounded-lg p-4 shadow-lg hover:border-indigo-400 transition-colors">
                    <h3 class="text-base lg:text-lg font-semibold text-gray-800 mb-4">Monthly Income</h3>
                    <div class="chart-container">
                        <canvas id="incomeChart"></canvas>
                    </div>
                </div>
                <div class="flex-0.5 bg-white border border-slate-200 rounded-lg p-4 shadow-lg hover:border-indigo-400 transition-colors">
                    <h3 class="text-base lg:text-lg font-semibold text-gray-800 mb-4">Most Common Medical Conditions</h3>
                    <div class="chart-container">
                        <canvas id="conditionChart"></canvas>
                    </div>
                </div>
            </div>
        </main>

        <div class="mt-8 bg-white border border-slate-200 rounded-lg p-4 shadow-lg hover:border-indigo-400 transition-colors">
            <h3 class="text-base lg:text-lg font-semibold text-gray-800 mb-4">Recent Activities</h3>
            <div class="table-container overflow-x-scroll lg:overflow-x-hidden">
                <table class="min-w-full divide-y divide-slate-200">
                    <thead class="bg-gray-300">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs text-gray-700 uppercase tracking-wider">#</th>
                            <th class="px-4 py-3 text-left text-xs text-gray-700 uppercase tracking-wider">Name</th>
                            <th class="px-4 py-3 text-left text-xs text-gray-700 uppercase tracking-wider">Description</th>
                            <th class="px-4 py-3 text-left text-xs text-gray-700 uppercase tracking-wider">Date</th>
                        </tr>
                    </thead>
                    <tbody id="activities-body" class="bg-white divide-y divide-slate-200">
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Chart.js Scripts -->
    <script>
        // Monthly Income Bar Chart
        const monthlyLabels = <?= json_encode($monthlyLabels) ?>;
        const monthlyTotals = <?= json_encode($monthlyTotals) ?>;

        const incomeCtx = document.getElementById('incomeChart').getContext('2d');
        const incomeChart = new Chart(incomeCtx, {
            type: 'bar',
            data: {
                labels: monthlyLabels,
                datasets: [{
                    label: 'Total Income (₱)',
                    data: monthlyTotals,
                    backgroundColor: ['#3b82f6', '#6366f1', '#2dd4bf'],
                    borderColor: ['#2563eb', '#4f46e5', '#14b8a6'],
                    borderWidth: 1,
                    borderRadius: 4
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: true,
                        labels: {
                            font: {
                                size: 11
                            }
                        }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        title: {
                            display: true,
                            text: 'Amount (₱)',
                            font: {
                                size: 11
                            }
                        },
                        ticks: {
                            font: {
                                size: 9
                            }
                        }
                    },
                    x: {
                        grid: {
                            display: false
                        },
                        ticks: {
                            font: {
                                size: 9
                            }
                        }
                    }
                }
            }
        });

        // Most Common Medical Conditions Pie Chart
        const conditionLabels = <?= json_encode($conditionLabels) ?>;
        const conditionCounts = <?= json_encode($conditionCounts) ?>;

        const conditionCtx = document.getElementById('conditionChart').getContext('2d');
        const conditionChart = new Chart(conditionCtx, {
            type: 'pie',
            data: {
                labels: conditionLabels,
                datasets: [{
                    data: conditionCounts,
                    backgroundColor: ['#3b82f6', '#2dd4bf', '#6366f1', '#a855f7'],
                    borderColor: '#fff',
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: {
                            font: {
                                size: 9
                            }
                        }
                    }
                }
            }
        });

        function toggleModal(modalId) {
            const modal = document.getElementById(modalId);
            if (modal) {
                modal.classList.toggle('hidden');
            } else {
                console.error("Modal not found:", modalId);
            }
        }

        // AJAX polling for Recent Activities
        let currentPage = 1;
        let isFetching = false;

        async function fetchRecentActivities(page = 1) {
            if (isFetching) return;
            isFetching = true;

            const tbody = document.getElementById("activities-body");
            if (!tbody) {
                console.error("Activities table body not found");
                isFetching = false;
                return;
            }

            const newTbody = document.createElement("tbody");
            newTbody.classList.add("bg-white", "divide-y", "divide-slate-200");
            newTbody.innerHTML = '<tr><td colspan="4" class="px-4 py-2 text-sm text-center text-gray-500">Loading...</td></tr>';

            try {
                const url = `./admin-get-recent-activities.php?page=${page}`;
                console.log("Attempting to fetch:", url);
                const response = await fetch(url);
                console.log("Response status:", response.status, response.statusText);
                if (!response.ok) {
                    const errorText = await response.text();
                    console.log("Error response text:", errorText);
                    throw new Error(`HTTP error: ${response.status} ${response.statusText}`);
                }
                const text = await response.text();
                console.log("Raw response:", text);
                if (!text) throw new Error("Empty response from server");
                const data = JSON.parse(text);
                console.log("Parsed data:", data);

                newTbody.innerHTML = '';
                if (!data.activities || data.activities.length === 0) {
                    newTbody.innerHTML = '<tr><td colspan="4" class="px-4 py-2 text-sm text-center text-gray-500">No recent activities</td></tr>';
                } else {
                    data.activities.forEach((activity, index) => {
                        const serial = data.offset + index + 1;
                        const timestamp = new Date(activity.Timestamp);
                        const formattedDate = isNaN(timestamp.getTime()) ? 'Unknown' : timestamp.toLocaleString();
                        const row = `
                    <tr class="hover:bg-gray-50 opacity-0 transition-opacity duration-300">
                        <td class="px-4 py-2 text-sm whitespace-nowrap">${serial}</td>
                        <td class="px-4 py-2 text-sm whitespace-nowrap">${activity.name || 'Admin'}</td>
                        <td class="px-4 py-2 text-sm whitespace-nowrap">${activity.Description || 'No description'}</td>
                        <td class="px-4 py-2 text-sm whitespace-nowrap">${formattedDate}</td>
                    </tr>
                `;
                        newTbody.insertAdjacentHTML("beforeend", row);
                    });
                }

                const parentTable = tbody.parentElement;
                tbody.classList.add("opacity-0");
                setTimeout(() => {
                    parentTable.replaceChild(newTbody, tbody);
                    newTbody.id = "activities-body";
                    newTbody.classList.remove("opacity-0");
                    newTbody.querySelectorAll("tr").forEach((row, index) => {
                        setTimeout(() => row.classList.remove("opacity-0"), index * 50);
                    });
                }, 300);

                const pagination = document.getElementById("pagination");
                if (pagination) {
                    pagination.innerHTML = '';
                    if (data.currentPage > 1) {
                        pagination.innerHTML += `<button onclick="fetchRecentActivities(${data.currentPage - 1})" class="px-3 py-1 bg-gray-100 text-gray-800 rounded hover:bg-gray-200">« Prev</button>`;
                    }
                    for (let i = 1; i <= data.totalPages; i++) {
                        pagination.innerHTML += `<button onclick="fetchRecentActivities(${i})" class="px-3 py-1 ${i === data.currentPage ? 'bg-indigo-500 text-white' : 'bg-gray-100 text-gray-700'} rounded hover:bg-indigo-500 hover:text-white">${i}</button>`;
                    }
                    if (data.currentPage < data.totalPages) {
                        pagination.innerHTML += `<button onclick="fetchRecentActivities(${data.currentPage + 1})" class="px-3 py-1 bg-gray-100 text-gray-800 rounded hover:bg-gray-200">Next »</button>`;
                    }
                }

                currentPage = data.currentPage;
            } catch (error) {
                console.error("Fetch error:", error.message);
                newTbody.innerHTML = `<tr><td colspan="4" class="px-4 py-2 text-sm text-center text-red-500">Failed to load activities: ${error.message}</td></tr>`;
                const parentTable = tbody.parentElement;
                parentTable.replaceChild(newTbody, tbody);
                newTbody.id = "activities-body";
                newTbody.classList.remove("opacity-0");
            } finally {
                isFetching = false;
            }
        }

        // Poll every 10 seconds on page 1
        setInterval(() => {
            if (currentPage === 1 && !isFetching) {
                fetchRecentActivities(1);
            }
        }, 10000);

        // Initial fetch
        document.addEventListener('DOMContentLoaded', () => {
            fetchRecentActivities(1);
        });

        const recordsBtn = document.getElementById('recordsBtn');
        const recordsMenu = document.getElementById('recordsMenu');
        const recordsArrow = document.getElementById('recordsArrow');

        recordsBtn.addEventListener('click', () => {
            if (recordsMenu.classList.contains('max-h-0')) {
                recordsMenu.classList.remove('max-h-0', 'opacity-0');
                recordsMenu.classList.add('max-h-96', 'opacity-100');
            } else {
                recordsMenu.classList.remove('max-h-96', 'opacity-100');
                recordsMenu.classList.add('max-h-0', 'opacity-0');
            }
            recordsArrow.classList.toggle('rotate-180');
        });

        // Prevent submenu links from toggling the dropdown
        const submenuLinks = document.querySelectorAll('#recordsMenu a');
        submenuLinks.forEach(link => {
            link.addEventListener('click', (event) => {
                event.stopPropagation(); // Prevent click from bubbling up to recordsBtn
            });
        });

        // Open records dropdown if on records page
        if (window.location.pathname.includes('/records/')) {
            recordsMenu.classList.remove('max-h-0', 'opacity-0');
            recordsMenu.classList.add('max-h-96', 'opacity-100');
            recordsArrow.classList.add('rotate-180');
        }
    </script>

    <script src="../js/dashboard.js"></script>
    <script src="../js/sidebarHandler.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="../js/edit-profile.js"></script>
    <script src="../js/profile-dropdown.js"></script>
    <script src="../js/confirmLogout.js"></script>
    <script src="../js/admin-notification-bell.js"></script>
    <script src="../js/customize-loader.js"></script>
</body>

</html>
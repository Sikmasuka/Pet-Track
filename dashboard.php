<?php
ob_start(); // Start output buffering
require_once __DIR__ . "/functions/auth.php";
require_once __DIR__ . "/functions/dashboard-handler.php";
require_once __DIR__ . "/functions/logs.php";

requireVet();

// Fetch vet data for modal
$stmt = $pdo->prepare("SELECT * FROM veterinarian WHERE vet_id = ?");
$stmt->execute([$_SESSION['vet_id']]);
$vet = $stmt->fetch(PDO::FETCH_ASSOC);

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

ob_end_flush();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Balingasag Dog and Cat Clinic</title>
    <script src="Assets/chart.js"></script>
    <link rel="icon" href="image/logo.png" type="image/x-icon">
    <link rel="stylesheet" href="Assets/FontAwsome/css/all.min.css">
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

        /* Custom scrollbar for Recent Activities table */
        .activities-container::-webkit-scrollbar {
            width: 8px;
            height: 8px;
        }

        .activities-container::-webkit-scrollbar-track {
            background: #f1f1f1;
            /* Light grey track */
        }

        .activities-container::-webkit-scrollbar-thumb {
            background: #0d9488;
            /* Teal thumb */
            border-radius: 4px;
        }
    </style>
</head>

<body class="bg-slate-100 min-h-screen text-gray-800">
    <?php include('./includes/edit-profile.php') ?>

    <!-- Mobile Menu Button -->
    <button id="mobileMenuBtn" class="lg:hidden fixed top-4 left-4 z-50 bg-teal-700 text-white p-3 rounded-md shadow-lg hover:bg-teal-600 transition-colors">
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
            <a href="dashboard.php" class="block text-sm text-white bg-teal-800 px-4 py-2 rounded-md hover:bg-teal-900 transition-colors">
                <i class="fas fa-tachometer-alt mr-2"></i> Dashboard
            </a>
            <a href="clients.php" class="block text-sm text-white hover:bg-teal-800 px-4 py-2 rounded-md transition-colors">
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


    <div id="overlay" class="lg:hidden fixed inset-0 bg-black bg-opacity-50 z-30 hidden"></div>

    <!-- Main Content -->
    <div class="relative ml-0 lg:ml-52 p-4 pt-16 lg:pt-4 min-h-screen">

        <div id="loadingScreen" class="absolute inset-0 flex flex-col items-center justify-center bg-white bg-opacity-75 z-50 hidden">
            <img src="image/MainIcon.png" alt="Loading Icon" class="w-20 h-20 animate-pulse">
            <p class="mt-4 text-teal-700 font-semibold text-lg">Loading...</p>
        </div>

        <!-- Header -->
        <header class="bg-white shadow-lg rounded-lg text-gray-800 py-4 mb-8 p-4 lg:p-6 border border-slate-200">

            <div class="flex justify-between items-center mb-6">
                <h1 class="text-xl lg:text-2xl font-bold">Welcome To Dashboard!</h1>
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

            <!-- Metrics Grid -->
            <div class="grid grid-cols-1 sm:grid-cols-3 lg:grid-cols-5 gap-3 mt-4">
                <div class="bg-white p-3 rounded-md h-full relative shadow-md border border-slate-200 hover:border-indigo-400 transition-colors">
                    <a href="clients.php" class="absolute top-1 right-2 text-gray-500 hover:text-indigo-400 transition-colors text-sm">
                        <i class="fa-solid fa-arrow-up-right-from-square"></i>
                    </a>
                    <div class="text-center mt-2">
                        <h3 class="font-semibold text-lg mb-1"><i class="fas fa-user mr-1 text-lg text-indigo-500"></i> Clients</h3>
                        <p class="text-base"><?php echo isset($clientCount) ? $clientCount : 0; ?></p>
                    </div>
                </div>

                <div class="bg-white p-3 rounded-md relative shadow-md border border-slate-200 hover:border-indigo-400 transition-colors">
                    <a href="pets.php" class="absolute top-1 right-2 text-gray-500 hover:text-indigo-400 transition-colors text-sm">
                        <i class="fa-solid fa-arrow-up-right-from-square"></i>
                    </a>
                    <div class="text-center mt-2">
                        <h3 class="font-semibold text-lg mb-1"><i class="fas fa-paw mr-1 text-lg text-teal-500"></i> Pets</h3>
                        <p class="text-base"><?php echo isset($petCount) ? $petCount : 0; ?></p>
                    </div>
                </div>

                <div class="bg-white p-3 rounded-md relative shadow-md border border-slate-200 hover:border-indigo-400 transition-colors">
                    <a href="medical_records.php" class="absolute top-1 right-2 text-gray-500 hover:text-indigo-400 transition-colors text-sm">
                        <i class="fa-solid fa-arrow-up-right-from-square"></i>
                    </a>
                    <div class="text-center mt-2">
                        <h3 class="font-semibold text-lg mb-1"><i class="fas fa-file-medical mr-1 text-lg text-blue-500"></i> Records</h3>
                        <p class="text-base"><?php echo isset($recordCount) ? $recordCount : 0; ?></p>
                    </div>
                </div>

                <div class="bg-white p-3 rounded-md relative shadow-md border border-slate-200 hover:border-indigo-400 transition-colors">
                    <a href="payment_methods.php" class="absolute top-1 right-2 text-gray-500 hover:text-indigo-400 transition-colors text-sm">
                        <i class="fa-solid fa-arrow-up-right-from-square"></i>
                    </a>
                    <div class="text-center mt-2">
                        <h3 class="font-semibold text-lg mb-1"><i class="fa-solid fa-money-bill-wave mr-1 text-lg text-indigo-500"></i> Payments</h3>
                        <p class="text-base">₱<?php echo isset($totalPayment) ? number_format($totalPayment, 2) : '0.00'; ?></p>
                    </div>
                </div>

                <!-- Appointments Today -->
                <div class="bg-white p-3 rounded-md relative shadow-md border border-slate-200 hover:border-indigo-400 transition-colors">
                    <a href="appointments.php" class="absolute top-1 right-2 text-gray-500 hover:text-indigo-400 transition-colors text-sm">
                        <i class="fa-solid fa-arrow-up-right-from-square"></i>
                    </a>
                    <div class="text-center mt-2">
                        <h3 class="font-semibold text-lg mb-1"><i class="fa-solid fa-calendar-check mr-1 text-lg text-green-500"></i> Today</h3>
                        <p class="text-base"><?php echo isset($appointmentsToday) ? $appointmentsToday : 0; ?></p>
                    </div>
                </div>
            </div>
        </header>

        <!-- Main Content -->
        <main class="bg-white p-4 lg:p-6 rounded-lg shadow-lg border border-slate-200">
            <!-- Loader Overlay -->
            <div id="loader" class="fixed inset-0 flex flex-col items-center justify-center bg-white z-[9999] hidden">
                <img src="image/MainIcon.png" alt="Loading Icon" class="w-20 h-20 animate-pulse">
                <p class="mt-4 text-teal-700 font-semibold text-lg">Loading...</p>
            </div>

            <h2 class="text-lg sm:text-xl lg:text-2xl font-semibold text-gray-800 mb-6">Analytics Overview</h2>
            <div class="flex flex-col lg:flex-row gap-8">
                <!-- === RECENT ACTIVITIES === -->
                <div class="w-full lg:w-3/5 bg-white border border-slate-200 rounded-lg p-4 shadow-lg hover:border-indigo-400 transition-colors">

                    <h3 class="text-base lg:text-lg font-semibold text-gray-800 mb-4">
                        Recent Activities
                    </h3>

                    <!--  Scrollable container – now fully responsive  -->
                    <div class="w-full overflow-x-auto rounded-lg border border-slate-200">
                        <div class="max-h-96 overflow-y-auto activities-container">
                            <table class="min-w-full divide-y divide-slate-200" style="table-layout: fixed;">
                                <thead class="bg-gray-300 sticky top-0 z-10">
                                    <tr>
                                        <th class="px-3 py-2 text-left text-xs font-medium text-gray-700 uppercase tracking-wider" style="width: 10%;">#</th>
                                        <th class="px-3 py-2 text-left text-xs font-medium text-gray-700 uppercase tracking-wider" style="width: 30%;">Name</th>
                                        <th class="px-3 py-2 text-left text-xs font-medium text-gray-700 uppercase tracking-wider" style="width: 40%;">Description</th>
                                        <th class="px-3 py-2 text-left text-xs font-medium text-gray-700 uppercase tracking-wider" style="width: 20%;">Date</th>
                                    </tr>
                                </thead>

                                <tbody id="activities-body" class="bg-white divide-y divide-slate-200">
                                    <!-- rows injected by JS -->
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- Pagination -->
                    <div id="pagination" class="mt-3 flex flex-wrap justify-center gap-1"></div>
                </div>

                <!-- Most common medical condition -->
                <div class="lg:w-1/3 bg-white border border-slate-200 rounded-lg p-4 shadow-lg hover:border-indigo-400 transition-colors">
                    <h3 class="text-base lg:text-lg font-semibold text-gray-800 mb-4">
                        Most Common Medical Conditions
                    </h3>

                    <!-- Fixed-size chart container -->
                    <div class="chart-container flex justify-center items-center">
                        <canvas id="conditionChart" width="250" height="250"></canvas>
                    </div>
                </div>

            </div>

            <div class="mt-8 bg-white border border-slate-200 rounded-lg p-4 shadow-lg hover:border-indigo-400 transition-colors">
                <h3 class="text-base lg:text-lg font-semibold text-gray-800 mb-4">Monthly Income</h3>
                <div class="chart-container">
                    <canvas id="incomeChart"></canvas>
                </div>
            </div>
        </main>

    </div>

    <script>
        // Monthly Income Bar Chart
        const monthlyLabels = <?php echo json_encode($monthlyLabels ?? []); ?>;
        const monthlyTotals = <?php echo json_encode($monthlyTotals ?? []); ?>;

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
                scales: {
                    y: {
                        beginAtZero: true,
                        title: {
                            display: true,
                            text: 'Amount (₱)'
                        }
                    },
                    x: {
                        grid: {
                            display: false
                        }
                    }
                }
            }
        });

        // Most Common Medical Conditions Pie Chart
        const conditionLabels = <?php echo json_encode($conditionLabels ?? []); ?>;
        const conditionCounts = <?php echo json_encode($conditionCounts ?? []); ?>;

        const conditionCtx = document.getElementById('conditionChart').getContext('2d');
        const conditionChart = new Chart(conditionCtx, {
            type: 'pie',
            data: {
                labels: conditionLabels,
                datasets: [{
                    data: conditionCounts,
                    backgroundColor: ['#3b82f6', '#2dd4bf', '#6366f1', '#a855f7'],
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom'
                    }
                }
            }
        });

        /* -------------------------------------------------------------
   Recent Activities – AJAX + smooth replace
   ------------------------------------------------------------- */
        let currentPage = 1;
        let isFetching = false;

        async function fetchRecentActivities(page = 1) {
            if (isFetching) return;
            isFetching = true;

            const tbody = document.getElementById('activities-body');
            if (!tbody) {
                isFetching = false;
                return;
            }

            // ---- show a tiny loader inside the tbody (no DOM swap) ----
            tbody.innerHTML = `<tr><td colspan="4" class="px-4 py-2 text-center text-gray-500">Loading...</td></tr>`;

            try {
                const res = await fetch(`./functions/get-recent-activities.php?page=${page}`);
                if (!res.ok) throw new Error(`HTTP ${res.status}`);

                const data = await res.json(); // ← make sure PHP returns JSON
                const rows = [];

                if (!data.activities || data.activities.length === 0) {
                    rows.push(`<tr><td colspan="4" class="px-4 py-2 text-center text-gray-500">No recent activities</td></tr>`);
                } else {
                    data.activities.forEach((act, i) => {
                        const nr = data.offset + i + 1;
                        const ts = new Date(act.Timestamp);
                        const date = isNaN(ts) ? 'Unknown' : ts.toLocaleString();

                        // truncate long description (optional)
                        const desc = (act.Description || 'No description');
                        const shortDesc = desc.length > 60 ? desc.substring(0, 57) + '...' : desc;

                        rows.push(`
                    <tr class="hover:bg-gray-50 opacity-0 transition-opacity duration-300">
                        <td class="px-4 py-2 text-sm whitespace-nowrap">${nr}</td>
                        <td class="px-4 py-2 text-sm whitespace-nowrap max-w-xs truncate" title="${act.name || 'Veterinarian'}">
                            ${act.name || 'Veterinarian'}
                        </td>
                        <td class="px-4 py-2 text-sm max-w-xs truncate" title="${desc}">${shortDesc}</td>
                        <td class="px-4 py-2 text-sm whitespace-nowrap">${date}</td>
                    </tr>`);
                    });
                }

                // ---- replace content & animate rows ----
                tbody.innerHTML = rows.join('');

                // stagger fade-in
                tbody.querySelectorAll('tr').forEach((r, idx) => {
                    setTimeout(() => r.classList.remove('opacity-0'), idx * 50);
                });

                // ---- pagination ----
                const pag = document.getElementById('pagination');
                pag.innerHTML = '';

                if (data.totalPages > 1) {
                    if (data.currentPage > 1) {
                        pag.innerHTML += `<button onclick="fetchRecentActivities(${data.currentPage-1})"
                                         class="px-3 py-1 bg-gray-100 text-gray-800 rounded hover:bg-gray-200">Prev</button>`;
                    }

                    for (let i = 1; i <= data.totalPages; i++) {
                        pag.innerHTML += `<button onclick="fetchRecentActivities(${i})"
                                         class="px-3 py-1 rounded ${i===data.currentPage?'bg-indigo-500 text-white':'bg-gray-100 text-gray-700'} hover:bg-indigo-500 hover:text-white">${i}</button>`;
                    }

                    if (data.currentPage < data.totalPages) {
                        pag.innerHTML += `<button onclick="fetchRecentActivities(${data.currentPage+1})"
                                         class="px-3 py-1 bg-gray-100 text-gray-800 rounded hover:bg-gray-200">Next</button>`;
                    }
                }

                currentPage = data.currentPage;
            } catch (err) {
                console.error(err);
                tbody.innerHTML = `<tr><td colspan="4" class="px-4 py-2 text-center text-red-500">
            Failed to load activities
        </td></tr>`;
            } finally {
                isFetching = false;
            }
        }

        /* poll page 1 every 10 s */
        setInterval(() => {
            if (currentPage === 1 && !isFetching) fetchRecentActivities(1);
        }, 10_000);

        /* initial load */
        document.addEventListener('DOMContentLoaded', () => fetchRecentActivities(1));
    </script>

    <script src="./js/dashboard.js"></script>
    <script src="./js/sidebarHandler.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="./js/confirmLogout.js"></script>
    <script src="./js/edit-profile.js"></script>
    <script src="./js/customize-loader.js"></script>
    <script src="./js/notification-bell.js"></script>
</body>

</html>
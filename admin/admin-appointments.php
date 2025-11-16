<?php
session_start();
require_once __DIR__ . "/../db.php"; // Adjust path to your PDO connection file
require_once __DIR__ . "/../functions/logs.php"; // Include the logs.php file

// Fetch admin data
if (!isset($currentAdmin)) {
    $stmt = $pdo->prepare("SELECT * FROM admin WHERE admin_id = ?");
    $stmt->execute([$_SESSION['admin_id']]);
    $currentAdmin = $stmt->fetch(PDO::FETCH_ASSOC);
}

// Define $adminName
$adminName = htmlspecialchars($currentAdmin['admin_name'] ?? 'Admin');

// Function to get the current user's role
function getUserRole()
{
    return isset($_SESSION['role']) ? $_SESSION['role'] : 'Veterinarian';
}

// Define $start_date and $end_date based on initial load or default
$cur_year = date('Y'); // 2025
$cur_month = date('m'); // 08 (August)
if (isset($_GET['month']) && isset($_GET['year'])) {
    $cur_month = str_pad((int)$_GET['month'], 2, '0', STR_PAD_LEFT);
    $cur_year = (int)$_GET['year'];
    if ($cur_month < 1) {
        $cur_month = 12;
        $cur_year--;
    }
    if ($cur_month > 12) {
        $cur_month = 1;
        $cur_year++;
    }
}
$start_date = "$cur_year-$cur_month-01";
$end_date = date("Y-m-t", strtotime($start_date));

// Fetch logs related to appointments for the current month
$log_stmt = $pdo->prepare("
    SELECT User_ID AS name, Description, Timestamp, Role 
    FROM Logs 
    WHERE Timestamp BETWEEN :start_date AND :end_date
    ORDER BY Timestamp DESC
");
$log_stmt->execute(['start_date' => $start_date, 'end_date' => $end_date]);
$logs = $log_stmt->fetchAll(PDO::FETCH_ASSOC);


// Fetch ALL appointments for this month (without LIMIT for combining with logs)
$stmt = $pdo->prepare("
    SELECT id, owner_name, contact_number, appointment_date, appointment_time, reason, status, duration
    FROM appointments
    WHERE appointment_date BETWEEN :start_date AND :end_date
    AND status = 'Scheduled'
    ORDER BY appointment_date DESC, appointment_time DESC
");
$stmt->execute(['start_date' => $start_date, 'end_date' => $end_date]);
$appoint_list = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Set up pagination for appointments only
$items_per_page = 10;
$page_num = isset($_GET['page']) && is_numeric($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$total_items = count($appoint_list);
$total_pages = ceil($total_items / $items_per_page);
$start_point = ($page_num - 1) * $items_per_page;
$paginated_data = array_slice($appoint_list, $start_point, $items_per_page);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Appointments - PetTrack</title>
    <link rel="stylesheet" href="../Assets/FontAwsome/css/all.min.css">
    <link rel="icon" href="../image/logoWhite.png" type="image/x-icon">
    <script src="https://cdn.tailwindcss.com"></script>
    <link href='https://cdn.jsdelivr.net/npm/fullcalendar@5.11.0/main.min.css' rel='stylesheet' />
    <script src='https://cdn.jsdelivr.net/npm/fullcalendar@5.11.0/main.min.js'></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        /* Custom Scrollbar */
        ::-webkit-scrollbar {
            width: 8px;
            height: 8px;
        }

        ::-webkit-scrollbar-track {
            background: #e2e8f0;
            /* Lighter track */
        }

        ::-webkit-scrollbar-thumb {
            background: #10b981;
            /* Emerald green */
            border-radius: 4px;
        }

        ::-webkit-scrollbar-thumb:hover {
            background: #059669;
            /* Darker emerald on hover */
        }

        .hidden {
            display: none !important;
        }

        .chart-container {
            height: 300px;
            width: 100%;
        }

        @media (min-width: 768px) {
            .chart-container {
                height: 400px;
            }
        }

        #calendar {
            width: 100%;
            margin: 0 auto;
            height: 400px;
        }

        /* Custom styles for calendar day indicators */
        .fc-daygrid-day-number {
            cursor: pointer;
            position: relative;
            z-index: 1;
        }

        .fc-daygrid-day.fc-day-other {
            display: none;
        }

        .fc-daygrid-day {
            transition: background-color 0.3s;
            position: relative;
        }

        /* Appointment indicator styles */
        .appointment-indicator {
            position: absolute;
            top: 5px;
            right: 5px;
            width: 12px;
            height: 12px;
            border-radius: 50%;
            z-index: 2;
        }

        .appointment-indicator.has-appointments {
            background-color: #28a745;
        }

        .appointment-indicator.full-day {
            position: absolute;
            top: 2px;
            right: 2px;
            width: 8px;
            height: 8px;
            border-radius: 50%;
            background-color: #dc3545;
        }

        /* Day with appointments styling */
        .fc-daygrid-day.has-appointments {
            background-color: rgba(40, 167, 69, 0.1) !important;
        }

        .fc-daygrid-day.full-day {
            background-color: rgba(220, 53, 69, 0.1) !important;
        }

        .fc-daygrid-day.full-day .fc-daygrid-day-number {
            color: #dc3545 !important;
            font-weight: bold;
        }

        .fc-daygrid-day.has-appointments .fc-daygrid-day-number {
            color: #28a745 !important;
            font-weight: bold;
        }

        #appointmentModal .appointment-details {
            max-height: 60vh;
            overflow-y: auto;
        }

        /* Legend styles */
        .calendar-legend {
            display: flex;
            gap: 20px;
            margin-bottom: 15px;
            flex-wrap: wrap;
        }

        .legend-item {
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 14px;
        }

        .legend-circle {
            width: 12px;
            height: 12px;
            border-radius: 50%;
        }

        .legend-available {
            background-color: #e5e7eb;
        }

        .legend-has-appointments {
            background-color: #28a745;
        }

        .legend-full {
            background-color: #dc3545;
        }
    </style>
</head>

<body class="bg-slate-100 min-h-screen text-gray-800">

    <!-- Mobile Menu Button -->
    <button id="mobileMenuBtn" class="lg:hidden fixed top-4 left-4 z-50 bg-teal-700 text-white p-3 rounded-md shadow-lg hover:bg-teal-600 transition-colors">
        <i class="fas fa-bars"></i>
    </button>

    <!-- Sidebar -->
    <aside id="sidebar" style="height: 100vh;"
        class="fixed inset-y-0 left-0 w-[200px] bg-gradient-to-b from-emerald-600 via-teal-700 to-emerald-800 text-white p-5 transform -translate-x-full lg:translate-x-0 transition-transform duration-300 ease-in-out z-40 flex flex-col border-r border-emerald-900">

        <!-- Header -->
        <div class="flex flex-col items-center">
            <img src="../image/logoWhite.png" alt="Balingasag Dog and Cat Clinic Logo"
                class="h-16 w-auto object-contain drop-shadow-lg">
            <div class="text-center leading-tight mt-2">
                <h2 class="text-xl font-extrabold tracking-wide text-white">
                    Balingasag
                </h2>
                <p class="text-base font-medium text-gray-200">
                    Dog & Cat Clinic
                </p>
            </div>
        </div>

        <!-- Navigation -->
        <nav class="flex-grow mt-8 lg:mt-12 space-y-0.5 overflow-y-auto" style="height: calc(100% - 150px);">
            <a href="admin-dashboard.php"
                class="block text-sm text-white hover:bg-emerald-700 px-4 py-2 rounded-md transition-colors">
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
                class="block text-sm text-white hover:bg-emerald-700 bg-teal-800 px-4 py-2 rounded-md">
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

    <!-- Loading Screen -->
    <div id="loadingScreen" class="fixed inset-0 lg:left-52 flex flex-col items-center justify-center bg-white bg-opacity-75 z-50 hidden">
        <img src="../image/MainIcon.png" alt="Loading Icon" class="w-20 h-20 animate-pulse">
        <p class="mt-4 text-teal-700 font-semibold text-lg">Loading...</p>
    </div>

    <!-- Main content -->
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

        <!-- FullCalendar -->
        <div class="bg-white rounded-lg shadow-lg p-6 mb-6">
            <h2 class="text-lg font-semibold mb-4">Appointment Days</h2>

            <!-- Calendar Legend -->
            <div class="calendar-legend">
                <div class="legend-item">
                    <div class="legend-circle legend-available"></div>
                    <span>Available</span>
                </div>
                <div class="legend-item">
                    <div class="legend-circle legend-has-appointments"></div>
                    <span>Has Appointments</span>
                </div>
                <div class="legend-item">
                    <div class="legend-circle legend-full"></div>
                    <span>Full (6/6)</span>
                </div>
            </div>

            <div id='calendar'></div>
        </div>

        <!-- Appointment Table -->
        <div class="bg-white rounded-lg shadow-lg p-6">
            <h2 class="text-lg font-semibold text-gray-800 mb-4">Appointment List</h2>
            <div class="overflow-x-auto">
                <table class="min-w-full bg-white border border-gray-200 rounded-md overflow-hidden">
                    <thead class="bg-gray-300 sticky top-0 z-2">
                        <tr class="bg-gray-100">
                            <th class="py-2 px-4 border-b text-left text-sm font-semibold text-gray-600">#</th>
                            <th class="py-2 px-4 border-b text-left text-sm font-semibold text-gray-600">Owner Name</th>
                            <th class="py-2 px-4 border-b text-left text-sm font-semibold text-gray-600">Phone</th>
                            <th class="py-2 px-4 border-b text-left text-sm font-semibold text-gray-600">Reason</th>
                            <th class="py-2 px-4 border-b text-left text-sm font-semibold text-gray-600">Duration</th>
                            <th class="py-2 px-4 border-b text-left text-sm font-semibold text-gray-600">Date</th>
                            <th class="py-2 px-4 border-b text-left text-sm font-semibold text-gray-600">Time</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($paginated_data)): ?>
                            <tr>
                                <td colspan="7" class="py-2 px-4 border-b text-center text-sm text-gray-500">No appointments this month.</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($paginated_data as $index => $appointment): ?>
                                <?php
                                $serial = $start_point + $index + 1;
                                $name = htmlspecialchars($appointment['owner_name']);
                                $phone = htmlspecialchars($appointment['contact_number']);
                                $reason = htmlspecialchars($appointment['reason']);
                                $status = htmlspecialchars($appointment['status']);
                                $duration = htmlspecialchars($appointment['duration']);
                                $date = htmlspecialchars($appointment['appointment_date']);
                                // Convert time to 12-hour format (e.g., 2:30 PM)
                                $time = date('h:i A', strtotime($appointment['appointment_time']));
                                ?>
                                <tr class="hover:bg-gray-50">
                                    <td class="py-2 px-4 border-b text-sm"><?= $serial ?></td>
                                    <td class="py-2 px-4 border-b text-sm"><?= $name ?></td>
                                    <td class="py-2 px-4 border-b text-sm"><?= $phone ?></td>
                                    <td class="py-2 px-4 border-b text-sm"><?= $reason ?></td>
                                    <td class="py-2 px-4 border-b text-sm"><?= $duration ?> min</td>
                                    <td class="py-2 px-4 border-b text-sm"><?= $date ?></td>
                                    <td class="py-2 px-4 border-b text-sm"><?= $time ?></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <!-- Pagination Links -->
            <div class="mt-4 flex justify-center space-x-2">
                <?php if ($page_num > 1): ?>
                    <a href="?month=<?= $cur_month ?>&year=<?= $cur_year ?>&page=<?= $page_num - 1 ?>"
                        class="px-3 py-1 bg-gray-200 text-gray-800 rounded hover:bg-gray-300">« Prev</a>
                <?php else: ?>
                    <span class="px-3 py-1 bg-gray-200 text-gray-800 rounded opacity-50 cursor-not-allowed">« Prev</span>
                <?php endif; ?>

                <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                    <a href="?month=<?= $cur_month ?>&year=<?= $cur_year ?>&page=<?= $i ?>"
                        class="px-3 py-1 <?= $i === $page_num ? 'bg-gray-300 text-gray-800' : 'bg-gray-100 text-gray-800' ?> rounded hover:bg-gray-300 hover:text-gray-800"><?= $i ?></a>
                <?php endfor; ?>

                <?php if ($page_num < $total_pages): ?>
                    <a href="?month=<?= $cur_month ?>&year=<?= $cur_year ?>&page=<?= $page_num + 1 ?>"
                        class="px-3 py-1 bg-gray-200 text-gray-800 rounded hover:bg-gray-300">Next »</a>
                <?php else: ?>
                    <span class="px-3 py-1 bg-gray-200 text-gray-800 rounded opacity-50 cursor-not-allowed">Next »</span>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Appointment Details Modal -->
    <div id="appointmentModal" class="fixed inset-0 bg-black bg-opacity-50 z-50 hidden flex items-center justify-center" role="dialog" aria-labelledby="modalTitle" aria-modal="true">
        <div class="bg-white w-full max-w-md mx-4 h-[90vh] rounded-xl shadow-lg flex flex-col" tabindex="-1">
            <div class="bg-indigo-500 px-6 py-4 rounded-t-xl sticky top-0 z-10 flex justify-between items-center">
                <h2 id="modalTitle" class="text-xl font-semibold text-white text-center">Appointment Details</h2>
                <button type="button" onclick="closeModal('appointmentModal')" class="text-white hover:text-gray-200 focus:outline-none" aria-label="Close modal">
                    <i class="fas fa-times text-xl"></i>
                </button>
            </div>
            <div class="p-6 space-y-4 overflow-y-auto flex-1 appointment-details">
                <p id="modalDate" class="text-lg font-semibold"></p>
                <p id="appointmentCount" class="text-sm text-gray-600"></p>
                <div id="appointmentDetails" class="space-y-4"></div>
            </div>
        </div>
    </div>


    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Ensure all modals are hidden on page load
            const modals = ['adminHelpModal', 'appointmentModal'];
            modals.forEach(modalId => {
                const modal = document.getElementById(modalId);
                if (modal) {
                    modal.classList.add('hidden');
                }
            });
        });

        function toggleModal(modalId) {
            const modal = document.getElementById(modalId);
            if (!modal) return;

            if (modal.classList.contains('hidden')) {
                modal.classList.remove('hidden');
                document.body.style.overflow = 'hidden';
            } else {
                modal.classList.add('hidden');
                document.body.style.overflow = 'auto';
            }
        }

        let calendar;
        let appointmentCounts = {};
        let allEvents = {};

        document.addEventListener("DOMContentLoaded", function() {
            var calendarEl = document.getElementById("calendar");
            calendar = new FullCalendar.Calendar(calendarEl, {
                initialView: "dayGridMonth",
                initialDate: "<?= $start_date ?>", // Use PHP-generated start date
                events: function(fetchInfo, successCallback, failureCallback) {
                    const start = fetchInfo.startStr.split('T')[0]; // Extract date part
                    const end = fetchInfo.endStr.split('T')[0]; // Extract date part
                    console.log('Fetching events for:', {
                        start,
                        end
                    });
                    fetch(`/Pet-Track/functions/get-appointments.php?start=${start}&end=${end}`)
                        .then((response) => {
                            console.log('Fetch response status:', response.status);
                            if (!response.ok) throw new Error(`HTTP error! status: ${response.status}`);
                            return response.text();
                        })
                        .then((text) => {
                            console.log('Raw response from get-appointments.php:', text);
                            let events;
                            try {
                                events = JSON.parse(text);
                                console.log('Parsed events:', events);
                                if (events.error) {
                                    throw new Error(events.error);
                                }
                            } catch (e) {
                                console.error("JSON parse error:", e, "Response:", text);
                                Swal.fire({
                                    icon: 'error',
                                    title: 'Error',
                                    text: 'Failed to load appointments: Invalid data format.',
                                    confirmButtonColor: '#dc3545'
                                });
                                events = [];
                            }
                            processEvents(events, successCallback);
                        })
                        .catch((error) => {
                            console.error("Fetch error:", error);
                            Swal.fire({
                                icon: 'error',
                                title: 'Error',
                                text: `Failed to load appointments: ${error.message}`,
                                confirmButtonColor: '#dc3545'
                            });
                            processEvents([], successCallback);
                        });
                },
                dateClick: function(info) {
                    handleDateClick(info);
                },
                eventDidMount: function(info) {
                    info.el.style.display = "none";
                },
                eventsSet: function(events) {
                    console.log('Events set, updating calendar appearance');
                    updateCalendarAppearance();
                },
                dayMaxEvents: false,
                showNonCurrentDates: false,
                timeZone: 'Asia/Manila'
            });
            calendar.render();

            // Force refresh to ensure latest data
            calendar.refetchEvents();

            // Check for session messages
            <?php if (isset($_SESSION['success'])): ?>
                Swal.fire({
                    icon: 'success',
                    title: 'Success',
                    text: '<?php echo $_SESSION['success']; ?>',
                    confirmButtonColor: '#28a745'
                });
                <?php unset($_SESSION['success']); ?>
            <?php elseif (isset($_SESSION['error'])): ?>
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: '<?php echo $_SESSION['error']; ?>',
                    confirmButtonColor: '#dc3545'
                });
                <?php unset($_SESSION['error']); ?>
            <?php endif; ?>
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

    <?php include '../includes/edit-profile.php'; ?>
    <script src="../js/appointment-handler.js"></script>
    <script src="../js/dashboard.js"></script>
    <script src="../js/sidebarHandler.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="../js/confirmLogout.js"></script>
    <script src="../js/edit-profile.js"></script>
    <script src="../js/admin-notification-bell.js"></script>
    <script src="../js/customize-loader.js"></script>
</body>

</html>
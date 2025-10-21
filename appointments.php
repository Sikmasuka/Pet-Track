<?php
session_start();
require_once __DIR__ . "/db.php"; // Adjust path to your PDO connection file
require_once __DIR__ . "/functions/logs.php"; // Include the logs.php file

// Check if user is logged in
if (!isset($_SESSION['vet_id'])) {
    header('Location: index.php');
    exit;
}

// Define $vetName
$vetName = htmlspecialchars($currentVet['vet_name'] ?? 'Unknown');

// Fetch veterinarian data for modal (if not already set)
if (!isset($currentVet)) {
    $stmt = $pdo->prepare("SELECT * FROM veterinarian WHERE vet_id = ?");
    $stmt->execute([$_SESSION['vet_id']]);
    $currentVet = $stmt->fetch(PDO::FETCH_ASSOC);
}

// Fetch vet data for modal
$stmt = $pdo->prepare("SELECT * FROM veterinarian WHERE vet_id = ?");
$stmt->execute([$_SESSION['vet_id']]);
$vet = $stmt->fetch(PDO::FETCH_ASSOC);

// Fetch vet name and vet_username for logging
$stmt = $pdo->prepare("SELECT vet_name, vet_username FROM Veterinarian WHERE vet_id = ?");
$stmt->execute([$_SESSION['vet_id']]);
$user = $stmt->fetch();
$vetName = $user ? htmlspecialchars($user['vet_name']) : "Veterinarian not found";
$username = $user ? htmlspecialchars($user['vet_username']) : "Unknown";

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

// Get vet name from the database
$stmt = $pdo->prepare("SELECT vet_name FROM veterinarian WHERE vet_id = ?");
$stmt->execute([$_SESSION['vet_id']]);
$my_name_data = $stmt->fetch(PDO::FETCH_ASSOC);
$my_name = $my_name_data ? $my_name_data['vet_name'] : "Unknown Vet";

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
    <link rel="stylesheet" href="Assets/FontAwsome/css/all.min.css">
    <link rel="icon" href="image/MainIcon.png" type="image/x-icon">
    <script src="https://cdn.tailwindcss.com"></script>
    <link href='https://cdn.jsdelivr.net/npm/fullcalendar@5.11.0/main.min.css' rel='stylesheet' />
    <script src='https://cdn.jsdelivr.net/npm/fullcalendar@5.11.0/main.min.js'></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
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
            height: 300px;
            /* Default for mobile */
        }

        @media (min-width: 768px) {
            #calendar {
                height: 400px;
                /* Larger for desktop */
            }
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

        @media (max-width: 767px) {
            .fc .fc-daygrid-day-number {
                font-size: 0.75rem;
                /* Smaller font for day numbers on mobile */
            }

            .appointment-indicator {
                top: 3px;
                right: 3px;
                width: 8px;
                height: 8px;
            }

            .appointment-indicator.full-day {
                top: 1px;
                right: 1px;
                width: 6px;
                height: 6px;
            }

            .legend-item {
                font-size: 12px;
                /* Smaller legend text on mobile */
            }

            .legend-circle {
                width: 10px;
                height: 10px;
            }

            .calendar-legend {
                gap: 10px;
                /* Smaller gap for legend on mobile */
                justify-content: center;
            }

            .fc .fc-toolbar {
                flex-direction: column;
                align-items: center;
            }

            .fc .fc-toolbar-chunk {
                margin: 2px 0;
            }

            .fc .fc-toolbar-title {
                font-size: 1rem;
            }

            .fc .fc-button {
                padding: 0.25rem 0.5rem;
                font-size: 0.75rem;
            }
        }
    </style>
</head>

<body class="bg-slate-100 min-h-screen text-gray-800">
    <?php include('./includes/edit-profile.php'); ?>

    <!-- Mobile Menu Button -->
    <button id="mobileMenuBtn" class="lg:hidden fixed top-4 left-4 z-50 bg-teal-700 text-white p-3 rounded-md shadow-lg hover:bg-teal-600 transition-colors">
        <i class="fas fa-bars"></i>
    </button>

    <!-- Sidebar -->
    <aside id="sidebar" class="fixed inset-y-0 left-0 w-[200px] bg-gradient-to-b from-emerald-600 via-teal-700 to-emerald-800 text-white p-5 transform -translate-x-full lg:translate-x-0 transition-transform duration-300 ease-in-out z-40 flex flex-col border-r border-teal-800">
        <div class="flex items-center justify-between mb-6">
            <h2 class="text-xl lg:text-2xl font-semibold flex items-center gap-2">
                <img src="image/MainIconWhite.png" alt="Dashboard" class="w-6 lg:w-8">
                <span class="md:inline">Dashboard</span>
            </h2>
            <button id="closeSidebarBtn" class="lg:hidden absolute top-4 right-4 text-white hover:text-gray-200 duration-200">
                <i class="fas fa-times text-xl"></i>
            </button>
        </div>
        <nav class="flex-grow mt-8 lg:mt-12 space-y-0.5">
            <a href="dashboard.php" class="block text-sm text-white px-4 py-2 rounded-md hover:bg-teal-900 transition-colors">
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
            <a href="appointments.php" class="block text-sm text-white bg-teal-800 hover:bg-teal-800 px-4 py-2 rounded-md transition-colors">
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

    <!-- Main content -->
    <div class="relative ml-0 lg:ml-52 p-4 pt-16 lg:pt-4 min-h-screen">

        <div id="loadingScreen" class="absolute inset-0 flex flex-col items-center justify-center bg-white bg-opacity-75 z-50 hidden">
            <img src="image/MainIcon.png" alt="Loading Icon" class="w-20 h-20 animate-pulse">
            <p class="mt-4 text-teal-700 font-semibold text-lg">Loading...</p>
        </div>

        <!-- Header -->
        <header class="bg-white shadow-lg rounded-lg text-gray-800 py-4 mb-6 lg:mb-8 p-4 lg:p-6 border border-slate-200">
            <!-- Top Section with Dropdown -->
            <div class="flex justify-between items-center">
                <!-- Dashboard Title -->
                <h1 class="text-xl lg:text-2xl font-bold">Manage Appointments</h1>

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
        let calendar;
        let appointmentCounts = {};
        let allEvents = {};

        document.addEventListener("DOMContentLoaded", function() {
            var calendarEl = document.getElementById("calendar");
            calendar = new FullCalendar.Calendar(calendarEl, {
                initialView: "dayGridMonth",
                initialDate: "<?= $start_date ?>", // Use PHP-generated start date
                headerToolbar: {
                    left: 'prev,next today',
                    center: 'title',
                    right: ''
                },
                events: function(fetchInfo, successCallback, failureCallback) {
                    const start = fetchInfo.startStr.split('T')[0]; // Extract date part
                    const end = fetchInfo.endStr.split('T')[0]; // Extract date part
                    console.log('Fetching events for:', {
                        start,
                        end
                    });
                    fetch(`./functions/get-appointments.php?start=${start}&end=${end}`)
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
    </script>

    <script src="./js/appointment-handler.js"></script>
    <script src="./js/dashboard.js"></script>
    <script src="./js/sidebarHandler.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="./js/confirmLogout.js"></script>
    <script src="./js/edit-profile.js"></script>
    <script src="./js/notification-bell.js"></script>
    <script src="./js/customize-loader.js"></script>
</body>

</html>
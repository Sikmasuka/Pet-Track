<?php
require_once __DIR__ . "/../../functions/auth.php";
require_once '../record-handler.php';
requireAdmin();


// Check if admin is logged in
if (!isset($_SESSION['admin_id'])) {
    header('Location: ../index.php');
    exit;
}

// Fetch admin data
try {
    $stmt = $pdo->prepare("SELECT admin_name FROM admin WHERE admin_id = ?");
    $stmt->execute([$_SESSION['admin_id']]);
    $currentAdmin = $stmt->fetch(PDO::FETCH_ASSOC);
    $adminName = $currentAdmin ? htmlspecialchars($currentAdmin['admin_name']) : 'Admin';
} catch (PDOException $e) {
    $adminName = 'Admin';
}

// Initialize error message variable
$error_message = '';

// Fetch clinic details
try {
    $stmt = $pdo->query("SELECT name, address, phone FROM Clinic_Details WHERE id = 1");
    $clinic = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$clinic) {
        $clinic = ['name' => 'Vet Clinic', 'address' => '123 Clinic Street, Vet City', 'phone' => '(123) 456-7890'];
    }
} catch (PDOException $e) {
    $clinic = ['name' => 'Vet Clinic', 'address' => '123 Clinic Street, Vet City', 'phone' => '(123) 456-7890'];
}

// Fetch payments with applied filters
$query = "SELECT p.*, m.method_name, YEAR(p.date) AS payment_year, MONTH(p.date) AS payment_month 
          FROM Payments p 
          JOIN Payment_Methods m ON p.method_id = m.method_id";
$conditions = [];
$params = [];
if (isset($_GET['year']) && $_GET['year'] !== 'All' && is_numeric($_GET['year'])) {
    $conditions[] = "YEAR(p.date) = ?";
    $params[] = $_GET['year'];
}
if (isset($_GET['month']) && $_GET['month'] !== 'All' && is_numeric($_GET['month']) && $_GET['month'] >= 1 && $_GET['month'] <= 12) {
    $conditions[] = "MONTH(p.date) = ?";
    $params[] = $_GET['month'];
}
if (!empty($conditions)) {
    $query .= " WHERE " . implode(" AND ", $conditions);
}
$query .= " ORDER BY p.date DESC";
try {
    $stmt = $pdo->prepare($query);
    $stmt->execute($params);
    $payments = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $error_message = "Error fetching payments: " . $e->getMessage();
    $payments = [];
}

// Calculate total payments
$total_amount = array_sum(array_column($payments, 'amount'));

// Fetch all payments grouped by month and year for print report
$query_all = "SELECT p.*, m.method_name, YEAR(p.date) AS payment_year, MONTH(p.date) AS payment_month 
              FROM Payments p 
              JOIN Payment_Methods m ON p.method_id = m.method_id 
              ORDER BY p.date DESC";
try {
    $stmt_all = $pdo->prepare($query_all);
    $stmt_all->execute();
    $all_payments = $stmt_all->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $all_payments = [];
}

// Group payments by year and month
$monthly_payments = [];
foreach ($all_payments as $pay) {
    $year = $pay['payment_year'];
    $month = $pay['payment_month'];
    if (!isset($monthly_payments[$year])) {
        $monthly_payments[$year] = [];
    }
    if (!isset($monthly_payments[$year][$month])) {
        $monthly_payments[$year][$month] = [
            'payments' => [],
            'total' => 0,
            'count' => 0
        ];
    }
    $monthly_payments[$year][$month]['payments'][] = $pay;
    $monthly_payments[$year][$month]['total'] += $pay['amount'];
    $monthly_payments[$year][$month]['count']++;
}

// Calculate total for all payments
$total_amount_all = array_sum(array_column($all_payments, 'amount'));

// Define months for filter
$months = [
    1 => 'January',
    2 => 'February',
    3 => 'March',
    4 => 'April',
    5 => 'May',
    6 => 'June',
    7 => 'July',
    8 => 'August',
    9 => 'September',
    10 => 'October',
    11 => 'November',
    12 => 'December'
];

// Fetch available years for filter
try {
    $stmt = $pdo->query("SELECT DISTINCT YEAR(date) AS year FROM Payments ORDER BY year DESC");
    $years = $stmt->fetchAll(PDO::FETCH_COLUMN);
} catch (PDOException $e) {
    $years = [date('Y')];
}

// Determine period for print report
$period = 'All Time';
if (isset($_GET['year']) && $_GET['year'] !== 'All' && is_numeric($_GET['year'])) {
    $period = $_GET['year'];
    if (isset($_GET['month']) && $_GET['month'] !== 'All' && is_numeric($_GET['month']) && $_GET['month'] >= 1 && $_GET['month'] <= 12) {
        $period = $months[$_GET['month']] . ' ' . $_GET['year'];
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Payments</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="../../Assets/Extension.js"></script>
    <link rel="icon" href="../../image/MainIconWhite.png" type="image/x-icon">
    <style>
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

        .table-container {
            overflow-x: auto;
            width: 100%;
            max-width: 100%;
            -webkit-overflow-scrolling: touch;
        }

        table {
            min-width: 800px;
            width: 100%;
            border-collapse: separate;
            border-spacing: 0;
        }

        th,
        td {
            text-align: left;
            padding: 12px 16px;
            border-bottom: 1px solid #e2e8f0;
        }

        th {
            background-color: #f1f5f9;
            font-weight: 600;
            text-transform: uppercase;
            font-size: 0.75rem;
            letter-spacing: 0.05em;
            color: #1e293b;
        }

        td {
            font-size: 0.875rem;
            color: #1e293b;
        }

        .col-client {
            width: 20%;
        }

        .col-method {
            width: 15%;
        }

        .col-amount {
            width: 15%;
        }

        .col-description {
            width: 30%;
        }

        .col-date {
            width: 15%;
        }

        .col-actions {
            width: 10%;
        }

        .truncate-cell {
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .mobile-menu-btn {
            z-index: 1000;
        }

        body {
            overflow-x: hidden;
        }

        /* Print-specific styles for report */
        .print-report {
            display: none;
            font-family: 'Helvetica', Arial, sans-serif;
            max-width: 900px;
            margin: 20px auto;
            color: #333;
            line-height: 1.6;
        }

        @media print {
            body {
                margin: 0;
                font-size: 12pt;
            }

            body * {
                visibility: hidden;
            }

            .print-report,
            .print-report * {
                visibility: visible;
            }

            .print-report {
                display: block !important;
                position: absolute;
                top: 0;
                left: 0;
                width: 100%;
            }

            .sidebar,
            .header,
            .filter-form,
            .table-container,
            button {
                display: none !important;
            }

            .print-report .header {
                text-align: center;
                margin-bottom: 30px;
            }

            .print-report .clinic-name {
                font-size: 28pt;
                font-weight: bold;
                color: #2ecc71;
                margin-bottom: 10px;
            }

            .print-report .clinic-details {
                font-size: 14pt;
                color: #555;
            }

            .print-report .report-title {
                font-size: 20pt;
                font-weight: bold;
                margin: 20px 0;
                text-transform: uppercase;
            }

            .print-report .summary {
                background-color: #f8fafc;
                padding: 20px;
                border-radius: 8px;
                margin-bottom: 30px;
                font-size: 14pt;
                border: 1px solid #e2e8f0;
                page-break-inside: avoid;
            }

            .print-report table {
                width: 100%;
                border-collapse: collapse;
                margin-bottom: 30px;
                font-size: 10pt;
            }

            .print-report th,
            .print-report td {
                border: 1px solid #ddd;
                padding: 12px;
                text-align: left;
            }

            .print-report th {
                background-color: #e2e8f0;
                font-weight: bold;
                text-transform: uppercase;
                font-size: 10pt;
            }

            .print-report .amount {
                text-align: right;
            }

            .print-report .total-row td {
                font-weight: bold;
                background-color: #f1f5f9;
            }

            .print-report .no-data {
                text-align: center;
                padding: 20px;
                background-color: #fef2f2;
                border: 1px solid #fecaca;
                border-radius: 8px;
                color: #b91c1c;
                page-break-inside: avoid;
                font-size: 14pt;
            }

            .print-report .month-section {
                margin-bottom: 40px;
                page-break-inside: avoid;
            }

            .print-report .month-title {
                font-size: 16pt;
                font-weight: bold;
                margin: 20px 0 10px;
                color: #2b6cb0;
            }

            .print-report .footer {
                text-align: center;
                font-style: italic;
                margin-top: 30px;
                font-size: 12pt;
                color: #666;
                page-break-inside: avoid;
            }

            .print-report .signature-section {
                margin-top: 40px;
                padding-top: 20px;
                border-top: 1px solid #ccc;
                page-break-inside: avoid;
            }

            .print-report .signature-section p {
                font-size: 12pt;
                margin: 10px 0;
                font-weight: bold;
            }

            .print-report .signature-line {
                border-bottom: 1px solid #000;
                width: 250px;
                margin: 20px 0;
                display: inline-block;
            }

            .print-report hr {
                border: 0;
                border-top: 1px dashed #ccc;
                margin: 30px 0;
            }

            @page {
                margin: 1in;
            }
        }

        /* Print-specific styles for receipt */
        .receipt-print {
            display: none;
        }

        @media print {
            .receipt-print {
                display: block !important;
                visibility: visible;
            }
        }
    </style>
</head>

<body class="bg-slate-100 min-h-screen text-gray-800">
    <?php include '../../includes/edit-profile.php' ?>

    <!-- Mobile Menu Button -->
    <button id="mobileMenuBtn" class="lg:hidden fixed top-4 left-4 z-50 bg-teal-700 text-white p-3 rounded-md shadow-lg hover:bg-teal-600 transition-colors mobile-menu-btn">
        <i class="fas fa-bars"></i>
    </button>

    <!-- Sidebar -->
    <aside id="sidebar" style="height: 100vh;"
        class="fixed inset-y-0 left-0 w-[200px] bg-gradient-to-b from-emerald-600 via-teal-700 to-emerald-800 text-white p-5 transform -translate-x-full lg:translate-x-0 transition-transform duration-300 ease-in-out z-40 flex flex-col border-r border-emerald-900">

        <!-- Header -->
        <div class="flex flex-col items-center">
            <img src="../../image/logoWhite.png" alt="Balingasag Dog and Cat Clinic Logo"
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
            <a href="../admin-dashboard.php"
                class="block text-sm text-white hover:bg-emerald-700 px-4 py-2 rounded-md transition-colors">
                <i class="fas fa-tachometer-alt mr-2"></i> Dashboard
            </a>
            <a href="../admin.php"
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

                    <a href="../records/pet-records.php"
                        class="flex items-center text-sm text-gray-200 hover:bg-emerald-600 px-3 py-2 rounded-md hover:text-white transition-colors">
                        <i class="fas fa-paw mr-2"></i> Pets
                    </a>

                    <a href="../records/client-records.php"
                        class="flex items-center text-sm text-gray-200 hover:bg-emerald-600 px-3 py-2 rounded-md hover:text-white transition-colors">
                        <i class="fas fa-user mr-2"></i> Clients
                    </a>

                    <a href="../records/medical-records.php"
                        class="flex items-center text-sm text-gray-200 hover:bg-emerald-600 px-3 py-2 rounded-md hover:text-white transition-colors">
                        <i class="fas fa-file-medical mr-2"></i>
                        <span class="whitespace-normal leading-snug">Medical Records</span>
                    </a>

                    <a href="../records/admin-payments.php"
                        class="flex items-center text-sm text-gray-200 bg-teal-800 hover:bg-emerald-600 px-3 py-2 rounded-md hover:text-white transition-colors">
                        <i class="fas fa-credit-card mr-2"></i> Payments Records
                    </a>
                </div>
            </div>


            <!-- Active Link Example -->
            <a href="../admin-appointments.php"
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

    <!-- Loading Screen -->
    <div id="loadingScreen" class="fixed inset-0 lg:left-52 flex flex-col items-center justify-center bg-white bg-opacity-75 z-50 hidden">
        <img src="../../image/MainIcon.png" alt="Loading Icon" class="w-20 h-20 animate-pulse">
        <p class="mt-4 text-teal-700 font-semibold text-lg">Loading...</p>
    </div>

    <!-- Main content -->
    <div class="relative ml-0 lg:ml-52 p-4 pt-16 lg:pt-4 min-h-screen">

        <!-- Headre -->
        <header class="bg-white shadow-lg rounded-lg text-gray-800 py-4 mb-6 lg:mb-8 p-4 lg:p-6 border border-slate-200">

            <!-- Top Section with Dropdown -->
            <div class="flex justify-between items-center mb-6">
                <h1 class="text-xl lg:text-2xl font-bold">Manage Payments</h1>

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

        <!-- Main Content Section -->
        <div class="bg-white shadow-lg rounded-lg text-gray-800 py-4 mb-6 lg:mb-8 p-4 lg:p-6 border border-slate-200">
            <!-- Display error message if any -->
            <?php if (!empty($error_message)): ?>
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4" role="alert">
                    <span class="block sm:inline"><?php echo htmlspecialchars($error_message); ?></span>
                </div>
            <?php endif; ?>

            <!-- Payment History -->
            <div class="flex justify-between items-center mb-4">
                <h2 class="text-lg sm:text-xl lg:text-2xl font-semibold mb-4">Payment History</h2>
                <button onclick="printReport()" class="bg-emerald-600 text-white px-3 py-2 rounded-md hover:bg-emerald-700 text-sm sm:text-base transition-colors duration-200">
                    <i class="fas fa-print mr-2"></i>Print Report
                </button>
            </div>
            <!-- Filter Dropdowns -->
            <form method="GET" class="flex flex-row gap-6 mb-4">
                <div>
                    <label for="yearFilter" class="text-sm font-medium text-gray-700 mr-2">Filter by Year:</label>
                    <select name="year" id="yearFilter" class="border border-gray-300 rounded-lg px-4 cursor-pointer py-1 text-sm focus:ring-2 focus:ring-indigo-400 focus:outline-none" onchange="this.form.submit()">
                        <option value="All" <?php echo (!isset($_GET['year']) || $_GET['year'] === 'All') ? 'selected' : ''; ?>>All</option>
                        <?php foreach ($years as $year): ?>
                            <option value="<?php echo $year; ?>" <?php echo (isset($_GET['year']) && $_GET['year'] == $year) ? 'selected' : ''; ?>>
                                <?php echo $year; ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div>
                    <label for="monthFilter" class="text-sm font-medium text-gray-700 mr-2">Filter by Month:</label>
                    <select name="month" id="monthFilter" class="border border-gray-300 rounded-lg px-4 cursor-pointer py-1 text-sm focus:ring-2 focus:ring-indigo-400 focus:outline-none" onchange="this.form.submit()">
                        <option value="All" <?php echo (!isset($_GET['month']) || $_GET['month'] === 'All') ? 'selected' : ''; ?>>All</option>
                        <?php foreach ($months as $num => $name): ?>
                            <option value="<?php echo $num; ?>" <?php echo (isset($_GET['month']) && $_GET['month'] == $num) ? 'selected' : ''; ?>>
                                <?php echo $name; ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </form>

            <?php if (count($payments) > 0): ?>
                <div class="table-container">
                    <table>
                        <thead>
                            <tr>
                                <th class="col-client">Client</th>
                                <th class="col-method">Method</th>
                                <th class="col-amount">Amount</th>
                                <th class="col-description">Description</th>
                                <th class="col-date">Date</th>
                                <th class="col-actions">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($payments as $pay): ?>
                                <tr class="hover:bg-gray-50 transition-colors">
                                    <td class="col-client truncate-cell"><?php echo htmlspecialchars($pay['client_name']); ?></td>
                                    <td class="col-method truncate-cell"><?php echo htmlspecialchars($pay['method_name']); ?></td>
                                    <td class="col-amount">₱<?php echo number_format($pay['amount'], 2); ?></td>
                                    <td class="col-description truncate-cell" title="<?php echo htmlspecialchars($pay['description']); ?>">
                                        <?php echo htmlspecialchars($pay['description']); ?>
                                    </td>
                                    <td class="col-date"><?php echo date('M j, Y', strtotime($pay['date'])); ?></td>
                                    <td class="col-actions">
                                        <button onclick="printReceipt('<?php echo htmlspecialchars($pay['client_name']); ?>', '<?php echo htmlspecialchars($pay['method_name']); ?>', '<?php echo $pay['amount']; ?>', '<?php echo htmlspecialchars($pay['description']); ?>', '<?php echo $pay['date']; ?>')"
                                            class="text-indigo-400 hover:text-indigo-300 hover:underline text-sm">
                                            Print
                                        </button>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <?php
                $display_message = "No payments recorded yet.";
                if ((isset($_GET['year']) && $_GET['year'] !== 'All') || (isset($_GET['month']) && $_GET['month'] !== 'All')) {
                    $selected_month = isset($_GET['month']) && $_GET['month'] !== 'All' ? $months[$_GET['month']] : '';
                    $display_message = "No payments or transactions " . ($selected_month ? "this $selected_month" : "") . (isset($_GET['year']) && $_GET['year'] !== 'All' ? " in " . $_GET['year'] : "") . ".";
                }
                ?>
                <div class="text-center py-8 rounded-lg mb-4 bg-emerald-100">
                    <i class="fas fa-receipt text-slate-600 text-4xl mb-4"></i>
                    <p class="text-lg"><?php echo $display_message; ?></p>
                </div>
            <?php endif; ?>
        </div>

        <!-- Add Veterinarian Modal -->
        <div id="addModal" class="fixed inset-0 z-50 hidden bg-black bg-opacity-50 flex items-center justify-center">
            <div id="modalContent" class="bg-white rounded-lg shadow-lg w-full max-w-md overflow-hidden border border-slate-200">
                <div class="bg-emerald-600 px-6 py-4">
                    <h3 id="petModalTitle" class="text-lg font-bold text-center text-white">
                        Add Veterinarian
                    </h3>
                </div>
                <div class="p-6">
                    <form method="POST" class="grid grid-cols-1 gap-4">
                        <div>
                            <label for="vet_name" class="font-medium text-sm text-gray-800">Name</label>
                            <input type="text" name="vet_name" id="vet_name" placeholder="Name" required class="w-full p-2 border border-slate-200 rounded-md text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500">
                        </div>
                        <div>
                            <label for="vet_contact_number" class="font-medium text-sm text-gray-800">Contact Number</label>
                            <input type="text" name="vet_contact_number" id="vet_contact_number" placeholder="Contact Number" required class="w-full p-2 border border-slate-200 rounded-md text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500">
                        </div>
                        <div>
                            <label for="vet_username" class="font-medium text-sm text-gray-800">Username</label>
                            <input type="text" name="vet_username" id="vet_username" placeholder="Username" required class="w-full p-2 border border-slate-200 rounded-md text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500">
                        </div>
                        <div>
                            <label for="vet_password" class="font-medium text-sm text-gray-800">Password</label>
                            <input type="password" name="vet_password" id="vet_password" placeholder="Password" required class="w-full p-2 border border-slate-200 rounded-md text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500">
                        </div>
                        <div class="flex justify-between items-center mt-4 gap-3">
                            <button type="submit" name="add_vet" class="bg-emerald-600 text-white px-4 py-2 rounded-md hover:bg-emerald-700 text-sm transition-colors">
                                Add Veterinarian
                            </button>
                            <button type="button" id="closeAddModal" class="bg-red-500 text-white px-4 py-2 rounded-md hover:bg-red-600 text-sm transition-colors">
                                Close
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Print Report Section -->
        <div id="print-report" class="print-report">
            <div class="header">
                <div class="clinic-name"><?php echo htmlspecialchars($clinic['name']); ?></div>
                <div class="clinic-details"><?php echo htmlspecialchars($clinic['address']); ?></div>
                <div class="clinic-details"><?php echo htmlspecialchars($clinic['phone']); ?></div>
                <hr>
                <div class="report-title">Payment Report</div>
                <div class="clinic-details">Generated on <?php echo date('F j, Y'); ?></div>
            </div>
            <div class="summary">
                <p><strong>Summary for <?php echo htmlspecialchars($period); ?></strong></p>
                <p>Total Payments: ₱<?php echo number_format($total_amount_all, 2); ?></p>
                <p>Total Records: <?php echo count($all_payments); ?></p>
            </div>
            <?php if (empty($all_payments)): ?>
                <div class="no-data">No payments found for the selected period.</div>
            <?php else: ?>
                <?php foreach ($monthly_payments as $year => $months_data): ?>
                    <?php foreach ($months_data as $month => $data): ?>
                        <?php if (isset($_GET['year']) && $_GET['year'] !== 'All' && $year != $_GET['year']) continue; ?>
                        <?php if (isset($_GET['month']) && $_GET['month'] !== 'All' && $month != $_GET['month']) continue; ?>
                        <div class="month-section">
                            <div class="month-title"><?php echo $months[$month] . ' ' . $year; ?></div>
                            <div class="summary">
                                <p>Total Payments: ₱<?php echo number_format($data['total'], 2); ?></p>
                                <p>Total Records: <?php echo $data['count']; ?></p>
                            </div>
                            <table>
                                <thead>
                                    <tr>
                                        <th>Client</th>
                                        <th>Payment Method</th>
                                        <th class="amount">Amount (₱)</th>
                                        <th>Description</th>
                                        <th>Date</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($data['payments'] as $pay): ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($pay['client_name']); ?></td>
                                            <td><?php echo htmlspecialchars($pay['method_name']); ?></td>
                                            <td class="amount"><?php echo number_format($pay['amount'], 2); ?></td>
                                            <td><?php echo htmlspecialchars($pay['description'] ?: 'N/A'); ?></td>
                                            <td><?php echo date('M j, Y', strtotime($pay['date'])); ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                    <tr class="total-row">
                                        <td colspan="2">Total for <?php echo $months[$month]; ?></td>
                                        <td class="amount"><?php echo number_format($data['total'], 2); ?></td>
                                        <td colspan="2"></td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    <?php endforeach; ?>
                <?php endforeach; ?>
                <div class="summary">
                    <p><strong>Grand Total</strong></p>
                    <p>Total Payments: ₱<?php echo number_format($total_amount_all, 2); ?></p>
                </div>
            <?php endif; ?>
            <hr>
            <div class="signature-section">
                <p>Prepared by:</p>
                <div class="signature-line"></div>
                <p>Name: _____________________________</p>
                <p>Signature: _________________________</p>
            </div>
            <div class="footer">
                Generated by <?php echo htmlspecialchars($clinic['name']); ?>. Thank you for your continued trust.
            </div>
        </div>

        <!-- Print Receipt Section -->
        <iframe id="receiptFrame" class="hidden receipt-print"></iframe>

        <script>
            function printReport() {
                const images = document.querySelectorAll('img');
                let loadedImages = 0;
                const totalImages = images.length;

                if (totalImages === 0) {
                    triggerPrint();
                } else {
                    images.forEach(img => {
                        if (img.complete) {
                            loadedImages++;
                            if (loadedImages === totalImages) {
                                triggerPrint();
                            }
                        } else {
                            img.onload = () => {
                                loadedImages++;
                                if (loadedImages === totalImages) {
                                    triggerPrint();
                                }
                            };
                            img.onerror = () => {
                                loadedImages++;
                                if (loadedImages === totalImages) {
                                    triggerPrint();
                                }
                            };
                        }
                    });
                }
            }

            function triggerPrint() {
                const printSection = document.getElementById('print-report');
                printSection.style.display = 'block';
                void printSection.offsetHeight;
                setTimeout(() => {
                    window.print();
                    printSection.style.display = 'none';
                }, 100);
            }

            function printReceipt(clientName, methodName, amount, description, date) {
                const formattedDate = new Date(date).toLocaleDateString('en-US', {
                    year: 'numeric',
                    month: 'long',
                    day: 'numeric',
                    hour: '2-digit',
                    minute: '2-digit'
                });

                const receiptHTML = `
                    <html>
                    <head>
                        <title>Payment Receipt</title>
                        <style>
                            body { 
                                font-family: Arial, sans-serif; 
                                padding: 20px; 
                                max-width: 400px; 
                                margin: 0 auto; 
                                text-align: center;
                            }
                            .header { margin-bottom: 20px; }
                            .clinic-name { 
                                font-size: 24px; 
                                font-weight: bold; 
                                margin-bottom: 5px; 
                                color: #2ecc71;
                            }
                            .receipt-title { 
                                font-size: 18px; 
                                margin: 20px 0; 
                                font-weight: bold;
                            }
                            .details { 
                                margin-bottom: 20px; 
                                text-align: left; 
                                display: inline-block; 
                            }
                            .detail-row { display: flex; margin-bottom: 8px; }
                            .detail-label { font-weight: bold; width: 120px; }
                            .detail-value { flex: 1; }
                            .thank-you { 
                                text-align: center; 
                                margin-top: 30px; 
                                font-style: italic; 
                            }
                            .footer { 
                                text-align: center; 
                                margin-top: 40px; 
                                font-size: 12px; 
                                color: #666; 
                            }
                            hr { 
                                border: 0; 
                                border-top: 1px dashed #ccc; 
                                margin: 20px 0; 
                            }
                        </style>
                    </head>
                    <body>
                        <div class="header">
                            <div class="clinic-name"><?php echo htmlspecialchars($clinic['name']); ?></div>
                            <div><?php echo htmlspecialchars($clinic['address']); ?></div>
                            <div>Phone: <?php echo htmlspecialchars($clinic['phone']); ?></div>
                            <hr>
                            <div class="receipt-title">PAYMENT RECEIPT</div>
                        </div>
                        <div class="details">
                            <div class="detail-row">
                                <div class="detail-label">Date:</div>
                                <div class="detail-value">${formattedDate}</div>
                            </div>
                            <div class="detail-row">
                                <div class="detail-label">Client Name:</div>
                                <div class="detail-value">${clientName}</div>
                            </div>
                            <div class="detail-row">
                                <div class="detail-label">Payment Method:</div>
                                <div class="detail-value">${methodName}</div>
                            </div>
                            <div class="detail-row">
                                <div class="detail-label">Amount:</div>
                                <div class="detail-value">₱${parseFloat(amount).toFixed(2)}</div>
                            </div>
                            <div class="detail-row">
                                <div class="detail-label">Description:</div>
                                <div class="detail-value">${description || 'N/A'}</div>
                            </div>
                        </div>
                        <hr>
                        <div class="thank-you">
                            Thank you for your payment!
                        </div>
                        <div class="footer">
                            This is an official receipt from <?php echo htmlspecialchars($clinic['name']); ?>
                        </div>
                    </body>
                    </html>
                `;

                const frame = document.getElementById('receiptFrame');
                frame.contentDocument.open();
                frame.contentDocument.write(receiptHTML);
                frame.contentDocument.close();
                setTimeout(() => {
                    frame.contentWindow.focus();
                    frame.contentWindow.print();
                }, 500);
            }

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

            // Open records dropdown if on records page
            if (window.location.pathname.includes('/records/')) {
                recordsMenu.classList.remove('max-h-0', 'opacity-0');
                recordsMenu.classList.add('max-h-96', 'opacity-100');
                recordsArrow.classList.add('rotate-180');
            }

            const submenuLinks = document.querySelectorAll('#recordsMenu a');
            submenuLinks.forEach(link => {
                link.addEventListener('click', (event) => {
                    event.stopPropagation();
                });
            });

            window.addEventListener('afterprint', () => {
                document.getElementById('print-report').style.display = 'none';
                document.getElementById('receiptFrame').classList.add('hidden');
            });
        </script>
        <script src="../../js/sidebarHandler.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
        <script src="../../js/edit-profile.js"></script>
        <script src="../../js/profile-dropdown.js"></script>
        <script src="../../js/confirmLogout.js"></script>
        <script src="../../js/admin-notification-bell.js"></script>
        <script src="../../js/customize-loader.js"></script>
</body>

</html>
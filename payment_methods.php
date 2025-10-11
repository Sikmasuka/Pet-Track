<?php
// Start session and include database connection
session_start();
require_once 'db.php';

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
// Initialize error message variable
$error_message = '';

/**
 * Handle adding, updating payment methods, and recording payments via POST requests
 */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['record_payment'])) {
        try {
            $stmt = $pdo->prepare("INSERT INTO Payments (client_name, method_id, amount, description, date) VALUES (?, ?, ?, ?, NOW())");
            $stmt->execute([
                $_POST['client_name'],
                $_POST['method_id'],
                $_POST['amount'],
                $_POST['description']
            ]);
            header('Location: ' . $_SERVER['PHP_SELF']);
            exit;
        } catch (PDOException $e) {
            $error_message = "Error recording payment: " . $e->getMessage();
        }
    }
}

/**
 * Fetch all clients for dropdown
 */
$stmt = $pdo->query("SELECT client_name FROM Client ORDER BY client_name ASC");
$clients = $stmt->fetchAll();

/**
 * Fetch available years for filter
 */
$stmt = $pdo->query("SELECT DISTINCT YEAR(date) AS year FROM Payments ORDER BY year DESC");
$years = $stmt->fetchAll(PDO::FETCH_COLUMN);

/**
 * Define months for filter
 */
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

/**
 * Fetch payments with applied filters
 */
$query = "SELECT p.*, m.method_name FROM Payments p JOIN Payment_Methods m ON p.method_id = m.method_id";
$conditions = [];
$params = [];
if (isset($_GET['year']) && $_GET['year'] !== 'All' && is_numeric($_GET['year'])) {
    $conditions[] = "YEAR(p.date) = ?";
    $params[] = $_GET['year'];
}
if (isset($_GET['month']) && $_GET['month'] !== 'All' && is_numeric($_GET['month']) && $_GET['month'] >= 1 && $_GET['month'] <= 12) {
    $conditions[] = "MONTH(p.date) = ?";
    $params[] = $_GET['month'];   // <-- Missing before
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
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Vet Clinic Payments</title>
    <script src="Assets/Extension.js"></script>
    <link rel="stylesheet" href="Assets/FontAwsome/css/all.min.css">
    <link href="https://cdn.jsdelivr.net/npm/tom-select/dist/css/tom-select.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/tom-select/dist/js/tom-select.complete.min.js"></script>
    <link rel="icon" href="image/MainIcon.png" type="image/x-icon">
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

        .table-container {
            overflow-x: auto;
            width: 100%;
            max-width: 100%;
            -webkit-overflow-scrolling: touch;
        }

        .table-container table {
            min-width: 600px;
            width: 100%;
        }

        .mobile-menu-btn {
            z-index: 1000;
        }

        body {
            overflow-x: hidden;
        }

        .truncate-cell {
            max-width: 200px;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
    </style>
</head>

<body class="bg-slate-100 min-h-screen text-gray-800">
    <?php include('./includes/edit-profile.php'); ?>

    <!-- Display error message if any -->
    <?php if (!empty($error_message)): ?>
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4" role="alert">
            <span class="block sm:inline"><?php echo htmlspecialchars($error_message); ?></span>
        </div>
    <?php endif; ?>

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
            <a href="payment_methods.php" class="block text-sm bg-teal-800 text-white hover:bg-teal-800 px-4 py-2 rounded-md transition-colors">
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
        <header class="bg-white shadow-lg rounded-lg text-gray-800 py-4 mb-6 lg:mb-8 p-4 lg:p-6 border border-slate-200">
            <div class="flex justify-between items-center">
                <h1 class="text-xl lg:text-2xl font-bold">Manage Payments</h1>

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

        <div class="bg-white shadow-lg rounded-lg text-gray-800 py-4 mb-6 lg:mb-8 p-4 lg:p-6 border border-slate-200">
            <!-- Payment History Table -->
            <div class="flex justify-between items-center mb-4">
                <h2 class="text-lg sm:text-xl lg:text-2xl font-semibold mb-4">Payment History</h2>
                <button onclick="showPaymentModal()" class="bg-indigo-600 text-white px-3 py-2 rounded-md hover:bg-indigo-700 text-sm sm:text-base transition-colors duration-200">
                    <i class="fas fa-plus mr-2"></i>Record Payment
                </button>
            </div>
            <!-- Filter Dropdowns in Same Form (Always Visible) -->
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
                    <table class="min-w-full divide-y divide-slate-200">
                        <thead class="bg-gray-300 sticky top-0 z-2">
                            <tr class="border-b border-slate-200">
                                <th class="px-2 py-3 text-left text-xs sm:text-sm font-medium text-gray-600 uppercase tracking-wider min-w-[120px] whitespace-nowrap overflow-hidden truncate">Client</th>
                                <th class="px-2 py-3 text-left text-xs sm:text-sm font-medium text-gray-600 uppercase tracking-wider min-w-[120px] whitespace-nowrap overflow-hidden truncate">Method</th>
                                <th class="px-2 py-3 text-left text-xs sm:text-sm font-medium text-gray-600 uppercase tracking-wider min-w-[120px] whitespace-nowrap overflow-hidden truncate">Amount</th>
                                <th class="px-2 py-3 text-left text-xs sm:text-sm font-medium text-gray-600 uppercase tracking-wider min-w-[120px] whitespace-nowrap overflow-hidden truncate">Description</th>
                                <th class="px-2 py-3 text-left text-xs sm:text-sm font-medium text-gray-600 uppercase tracking-wider min-w-[120px] whitespace-nowrap overflow-hidden truncate">Date</th>
                                <th class="px-2 py-3 text-left text-xs sm:text-sm font-medium text-gray-600 uppercase tracking-wider min-w-[120px] whitespace-nowrap overflow-hidden truncate">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-slate-200">
                            <?php foreach ($payments as $pay): ?>
                                <tr class="hover:bg-gray-50 transition-colors">
                                    <td class="px-4 py-2 text-gray-700"><?php echo htmlspecialchars($pay['client_name']); ?></td>
                                    <td class="px-4 py-2 text-gray-700"><?php echo htmlspecialchars($pay['method_name']); ?></td>
                                    <td class="px-4 py-2 text-gray-700 font-medium">₱<?php echo number_format($pay['amount'], 2); ?></td>
                                    <td class="px-4 py-2 text-gray-700 truncate-cell" title="<?php echo htmlspecialchars($pay['description']); ?>">
                                        <?php echo htmlspecialchars($pay['description']); ?>
                                    </td>
                                    <td class="px-4 py-2 text-gray-700"><?php echo date('M j, Y', strtotime($pay['date'])); ?></td>
                                    <td class="px-3 py-3 text-sm">
                                        <button onclick="printReceipt('<?php echo htmlspecialchars($pay['client_name']); ?>', '<?php echo htmlspecialchars($pay['method_name']); ?>', '<?php echo $pay['amount']; ?>', '<?php echo htmlspecialchars($pay['description']); ?>', '<?php echo $pay['date']; ?>')"
                                            class="text-indigo-400 hover:text-indigo-300 hover:underline">
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
                    <p class="text-sm mt-2">Click "Record Payment" to add your first payment.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Payment Modal -->
    <div id="paymentModal" class="fixed inset-0 bg-black bg-opacity-50 hidden flex justify-center items-center z-50 p-4">
        <div class="bg-white rounded-lg shadow-xl w-full max-w-md max-h-[90vh] flex flex-col border border-gray-200">
            <div class="bg-emerald-600 rounded-t-lg text-gray-800 px-4 py-3 border-b border-gray-300">
                <h3 class="text-lg text-white font-bold">Record Payment</h3>
            </div>
            <form method="POST" class="p-4 overflow-y-auto">
                <div class="mb-3">
                    <label class="block text-xs font-medium text-gray-700 mb-1">Client</label>
                    <select id="clientSelect" name="client_name" required
                        class="w-full p-2 text-sm border rounded-md bg-white text-gray-800 focus:outline-none focus:ring-1 focus:ring-indigo-500">
                        <option value="">Select Client</option>
                        <?php foreach ($clients as $client): ?>
                            <option value="<?php echo htmlspecialchars($client['client_name']); ?>">
                                <?php echo htmlspecialchars($client['client_name']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="mb-3">
                    <label class="block text-xs font-medium text-gray-700 mb-1">Payment Method</label>
                    <select name="method_id" required
                        class="w-full p-2 text-sm border border-gray-300 rounded-md bg-white text-gray-800 focus:outline-none focus:ring-1 focus:ring-indigo-500">
                        <option value="">Select Payment Method</option>
                        <?php
                        $stmt = $pdo->query("SELECT * FROM Payment_Methods ORDER BY method_name ASC");
                        $methods = $stmt->fetchAll();
                        foreach ($methods as $method): ?>
                            <option value="<?php echo (int)$method['method_id']; ?>"><?php echo htmlspecialchars($method['method_name']); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="mb-3">
                    <label class="block text-xs font-medium text-gray-700 mb-1">Amount (₱)</label>
                    <input type="number" name="amount" min="0" step="0.01" required
                        class="w-full p-2 text-sm border border-gray-300 rounded-md bg-white text-gray-800 focus:outline-none focus:ring-1 focus:ring-indigo-500">
                </div>
                <div class="mb-4">
                    <label class="block text-xs font-medium text-gray-700 mb-1">Description</label>
                    <textarea name="description"
                        class="w-full p-2 text-sm border border-gray-300 rounded-md bg-white text-gray-800 focus:outline-none focus:ring-1 focus:ring-indigo-500"
                        rows="2" placeholder="Enter payment description..."></textarea>
                </div>
                <div class="flex justify-end gap-3 pt-3 border-t border-gray-300">
                    <button type="button" onclick="hidePaymentModal()"
                        class="px-3 py-1 text-sm text-gray-600 hover:text-gray-800">Cancel</button>
                    <button type="submit" name="record_payment"
                        class="bg-indigo-600 text-white px-4 py-1 text-sm rounded-md hover:bg-indigo-700 focus:outline-none focus:ring-1 focus:ring-indigo-500">
                        Save Payment
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Print Logic -->
    <iframe id="receiptFrame" class="hidden"></iframe>

    <script>
        function showPaymentModal() {
            document.getElementById('paymentModal').classList.remove('hidden');
        }

        function hidePaymentModal() {
            document.getElementById('paymentModal').classList.add('hidden');
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
                    color: #2ecc71; /* emerald green */
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
                <div class="clinic-name">Vet Clinic</div>
                <div>123 Clinic Street, Vet City</div>
                <div>Phone: (123) 456-7890</div>
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
                This is an official receipt from Vet Clinic
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

        function toggleModal(modalId) {
            console.log("Toggling modal:", modalId);
            const modal = document.getElementById(modalId);
            if (modal) {
                modal.classList.toggle('hidden');
            } else {
                console.error("Modal not found:", modalId);
            }
        }

        document.addEventListener("DOMContentLoaded", function() {
            new TomSelect("#clientSelect", {
                create: false,
                sortField: {
                    field: "text",
                    direction: "asc"
                },
                placeholder: "Search or select client..."
            });
        });
    </script>

    <script src="./js/dashboard.js"></script>
    <script src="./js/sidebarHandler.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="./js/confirmLogout.js"></script>
    <script src="./js/edit-profile.js"></script>
    <script src="./js/notification-bell.js"></script>
    <script src="./js/customize-loader.js"></script>
</body>

</html>
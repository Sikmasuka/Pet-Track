<?php
require_once '../db.php';
require_once '../functions/logs.php';
require_once '../functions/auth.php'; // Adjust path to auth.php (since this is in the admin folder)

// Protect admin pages
requireAdmin();
// Check login
if (!isset($_SESSION['admin_id'])) {
    header('Location: index.php');
    exit;
}

// Fetch vet name for greeting
$stmt = $pdo->prepare("SELECT * FROM admin WHERE admin_id=?");
$stmt->execute([$_SESSION['admin_id']]);
$user = $stmt->fetch();
$vetName = $user ? htmlspecialchars($user['admin_name']) : "Admin not found";

// Fetch the counts
$stmtClients = $pdo->prepare("SELECT COUNT(*) FROM Client");
$stmtClients->execute();
$clientCount = $stmtClients->fetchColumn();

$stmtPets = $pdo->prepare("SELECT COUNT(*) FROM Pet");
$stmtPets->execute();
$petCount = $stmtPets->fetchColumn();

$stmtRecords = $pdo->prepare("SELECT COUNT(*) FROM Medical_Records");
$stmtRecords->execute();
$recordCount = $stmtRecords->fetchColumn();

$stmtVet = $pdo->prepare("SELECT COUNT(*) FROM Veterinarian");
$stmtVet->execute();
$vetCount = $stmtVet->fetchColumn();

$today = date('Y-m-d');

$stmtAppointments = $pdo->prepare("
    SELECT COUNT(*) 
    FROM Appointments 
    WHERE DATE(created_at) = :today
");
$stmtAppointments->execute(['today' => $today]);
$appointmentsToday = $stmtAppointments->fetchColumn();

// Fetch most common medical conditions: Retrieve and split all medical conditions
$stmtConditions = $pdo->prepare("
    SELECT medical_condition
    FROM Medical_Records 
    WHERE medical_condition IS NOT NULL AND medical_condition != ''
");
$stmtConditions->execute();
$allConditions = $stmtConditions->fetchAll(PDO::FETCH_COLUMN);

// Split and count individual conditions
$conditionCounts = [];
foreach ($allConditions as $conditionStr) {
    if (!empty($conditionStr)) {
        // Split by comma and trim each condition
        $individualConditions = array_map('trim', explode(',', $conditionStr));
        foreach ($individualConditions as $condition) {
            if (!empty($condition)) {
                $condition = strtolower($condition);
                if (!isset($conditionCounts[$condition])) {
                    $conditionCounts[$condition] = 0;
                }
                $conditionCounts[$condition]++;
            }
        }
    }
}

// Sort by count descending and get top conditions
arsort($conditionCounts);
$topConditions = array_slice($conditionCounts, 0, 5); // Get top 5 conditions

$conditionLabels = [];
$conditionCounts = [];

foreach ($topConditions as $condition => $count) {
    // Format back to presentable
    $conditionLabels[] = ucwords($condition);
    $conditionCounts[] = $count;
}

// If no conditions found, set defaults
if (empty($conditionLabels)) {
    $conditionLabels = ['No conditions recorded'];
    $conditionCounts = [1];
}

// Fetch total payment amount
$stmtPayment = $pdo->prepare("SELECT SUM(amount) FROM Payments");
$stmtPayment->execute();
$totalPayment = $stmtPayment->fetchColumn();
$totalPayment = $totalPayment ? number_format((float) $totalPayment, 2, '.', '') : "0.00";


// Fetch monthly income (grouped by month)s
$stmtMonthly = $pdo->prepare("
    SELECT DATE_FORMAT(date, '%b') AS month,
           MONTH(date) AS month_num,
           SUM(amount) AS total
    FROM Payments
    GROUP BY month_num
    ORDER BY month_num
");
$stmtMonthly->execute();
$monthlyData = $stmtMonthly->fetchAll();

// Initialize all 12 months to 0
$allMonths = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
$monthlyTotals = array_fill(0, 12, 0);

// Fill in actual totals from DB
$monthlyLabels = $allMonths;
foreach ($monthlyData as $data) {
    $index = (int)$data['month_num'] - 1;
    $monthlyTotals[$index] = round($data['total'], 2);
}

// Pagination settings
$itemsPerPage = 10; // Change this number as needed
$currentPage = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$offset = ($currentPage - 1) * $itemsPerPage;

// Count total logs
$totalStmt = $pdo->query("SELECT COUNT(*) FROM Logs");
$totalLogs = $totalStmt->fetchColumn();
$totalPages = ceil($totalLogs / $itemsPerPage);

// Fetch paginated logs
$logQuery = "
    SELECT 
        l.Description,
        l.Timestamp,
        CASE
            WHEN l.Table_Affected = 'Admin' THEN a.admin_name
            WHEN l.Table_Affected = 'Veterinarian' THEN v.vet_name
            WHEN l.Table_Affected = 'Guest' THEN ap.owner_name
            ELSE 'Unknown'
        END AS name
    FROM Logs l
    LEFT JOIN Admin a ON l.Table_Affected = 'Admin' AND l.User_ID = a.admin_id
    LEFT JOIN Veterinarian v ON l.Table_Affected = 'Veterinarian' AND l.User_ID = v.vet_id
    LEFT JOIN Appointments ap ON l.Table_Affected = 'Guest' AND l.User_ID = 0 AND ap.id = (SELECT MAX(id) FROM appointments WHERE owner_name LIKE CONCAT('%', SUBSTRING_INDEX(l.Description, ' ', 2), '%'))
    ORDER BY l.Timestamp DESC
    LIMIT :limit OFFSET :offset
";

$stmt = $pdo->prepare($logQuery);
$stmt->bindValue(':limit', $itemsPerPage, PDO::PARAM_INT);
$stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
$stmt->execute();
$logs = $stmt->fetchAll(PDO::FETCH_ASSOC);

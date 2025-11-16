<?php
// functions/get-recent-activities.php
ob_start();
require_once __DIR__ . '/../db.php';
require_once __DIR__ . '/auth.php';
requireVet();

$itemsPerPage = 10;
$currentPage  = max(1, (int)($_GET['page'] ?? 1));
$offset       = ($currentPage - 1) * $itemsPerPage;

$totalStmt = $pdo->prepare("
    SELECT COUNT(*) 
    FROM Logs 
    WHERE Table_Affected IN ('Admin', 'Veterinarian', 'Guest')
");
$totalStmt->execute();
$totalLogs  = $totalStmt->fetchColumn();
$totalPages = max(1, ceil($totalLogs / $itemsPerPage));

$query = "
    SELECT 
        l.Description,
        l.Timestamp,
        l.Table_Affected,
        l.User_ID,
        COALESCE(v.vet_name, a.admin_name) AS staff_name,
        COALESCE(
            (SELECT owner_name FROM appointments 
             WHERE owner_name = SUBSTRING_INDEX(l.Description, ' booked an appointment', 1)
             ORDER BY id DESC LIMIT 1),
            'Client'
        ) AS guest_name
    FROM Logs l
    LEFT JOIN veterinarian v ON l.User_ID = v.vet_id AND l.Table_Affected = 'Veterinarian'
    LEFT JOIN admin a ON l.User_ID = a.admin_id AND l.Table_Affected = 'Admin'
    WHERE l.Table_Affected IN ('Admin', 'Veterinarian', 'Guest')
    ORDER BY l.Timestamp DESC
    LIMIT :limit OFFSET :offset
";

$stmt = $pdo->prepare($query);
$stmt->bindValue(':limit', $itemsPerPage, PDO::PARAM_INT);
$stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
$stmt->execute();
$logs = $stmt->fetchAll(PDO::FETCH_ASSOC);

$activities = array_map(function ($log) {
    $name = 'Unknown';

    if ($log['Table_Affected'] === 'Veterinarian' && $log['staff_name']) {
        $name = $log['staff_name'];
    } elseif ($log['Table_Affected'] === 'Admin' && $log['staff_name']) {
        $name = $log['staff_name'];
    } elseif ($log['Table_Affected'] === 'Guest' && $log['guest_name']) {
        $name = $log['guest_name'];
    }

    return [
        'name'        => $name,
        'Description' => $log['Description'],
        'Timestamp'   => $log['Timestamp']
    ];
}, $logs);

header('Content-Type: application/json');
echo json_encode([
    'activities'   => $activities,
    'totalPages'   => $totalPages,
    'currentPage'  => $currentPage,
    'offset'       => $offset
]);

ob_end_flush();
exit;

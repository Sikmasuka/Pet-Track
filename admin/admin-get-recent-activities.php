<?php
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/error.log');
ob_start();
header('Content-Type: application/json');
try {
    require_once __DIR__ . "/../db.php"; // Correct: resolves to /project-root/db.php
    require_once __DIR__ . "/../functions/auth.php"; // Correct: resolves to /project-root/functions/auth.php
    if (!isset($pdo)) {
        throw new Exception("Database connection not established");
    }
    requireAdmin();
    $itemsPerPage = 10;
    $currentPage = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
    $offset = ($currentPage - 1) * $itemsPerPage;
    $totalStmt = $pdo->query("SELECT COUNT(*) FROM Logs WHERE Table_Affected IN ('Veterinarian', 'Guest', 'Admin')");
    $totalLogs = $totalStmt->fetchColumn();
    $totalPages = ceil($totalLogs / $itemsPerPage);
    $logQuery = "
        SELECT
            Description,
            Timestamp,
            'Admin' AS name
        FROM Logs
        WHERE Table_Affected IN ('Veterinarian', 'Guest', 'Admin')
        ORDER BY Timestamp DESC
        LIMIT :limit OFFSET :offset
    ";
    $stmt = $pdo->prepare($logQuery);
    $stmt->bindValue(':limit', $itemsPerPage, PDO::PARAM_INT);
    $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
    $stmt->execute();
    $recentActivities = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode([
        'activities' => $recentActivities,
        'totalPages' => $totalPages,
        'currentPage' => $currentPage,
        'offset' => $offset
    ]);
} catch (Exception $e) {
    error_log("Error in admin-get-recent-activities.php: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
ob_end_flush();
exit;

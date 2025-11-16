<?php
// functions/get-appointments.php
require_once __DIR__ . '/../db.php';
date_default_timezone_set('Asia/Manila');

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
header('Pragma: no-cache');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

/* ------------------------------------------------------------------
   Parameters – start & end dates (YYYY-MM-DD)
------------------------------------------------------------------ */
$start = $_GET['start'] ?? date('Y-m-01');
$end   = $_GET['end']   ?? date('Y-m-t');

$start = substr($start, 0, 10);
$end   = substr($end,   0, 10);

if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $start) || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $end)) {
    echo json_encode([]);
    exit;
}

try {
    $stmt = $pdo->prepare("
        SELECT appointment_date, appointment_time
        FROM appointments
        WHERE appointment_date BETWEEN ? AND ?
          AND status = 'Scheduled'
    ");
    $stmt->execute([$start, $end]);
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $events = [];
    foreach ($rows as $row) {
        $time = substr($row['appointment_time'], 0, 5);
        $start_dt = $row['appointment_date'] . 'T' . $time . ':00';
        $end_dt   = date('Y-m-d\TH:i:s', strtotime($start_dt . ' +90 minutes'));

        $events[] = [
            'start' => $start_dt,
            'end'   => $end_dt,
            'title' => 'Booked'
        ];
    }

    echo json_encode($events);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Server error']);
    error_log('get-appointments error: ' . $e->getMessage());
}

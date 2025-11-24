<?php
session_start();
require_once __DIR__ . "/../db.php"; // Adjust path if needed

header('Content-Type: application/json');

$start = $_GET['start'] ?? date('Y-m-d');
$end = $_GET['end'] ?? $start;

try {
    $stmt = $pdo->prepare("
        SELECT 
            a.appointment_date,
            a.appointment_time,
            a.owner_name,
            a.contact_number,
            a.reason,
            a.pet_id,
            a.client_id
        FROM appointments a
        WHERE a.appointment_date BETWEEN ? AND ?
          AND a.status = 'Scheduled'
        ORDER BY a.appointment_date, a.appointment_time
    ");
    $stmt->execute([$start, $end]);
    $appointments = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Group by date
    $events = [];
    foreach ($appointments as $appt) {
        $date = $appt['appointment_date'];
        if (!isset($events[$date])) {
            $events[$date] = [
                'start' => $date,
                'title' => '',
                'extendedProps' => [
                    'count' => 0,
                    'appointments' => []
                ]
            ];
        }
        $events[$date]['extendedProps']['count']++;
        $events[$date]['extendedProps']['appointments'][] = [
            'owner_name' => $appt['owner_name'],
            'contact_number' => $appt['contact_number'],
            'appointment_time' => $appt['appointment_time'],
            'reason' => $appt['reason'] ?? 'Check-up',
            'pet_id' => $appt['pet_id'],
            'client_id' => $appt['client_id']
        ];
    }

    // Convert to indexed array for FullCalendar
    $result = array_values($events);

    // Debug: Remove this line after testing
    // error_log("get-appointments.php called for $start to $end → " . count($result) . " dates with appointments");

    echo json_encode($result);
} catch (Exception $e) {
    error_log("get-appointments.php error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([]);
}

<?php
// Get Appointments - NO HTML COMMENTS ABOVE THIS LINE!
require_once __DIR__ . "/../db.php"; // Adjust path to your PDO connection file
date_default_timezone_set('Asia/Manila');

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');

// Handle preflight requests
if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    exit(0);
}

$start_date = isset($_GET['start']) ? substr($_GET['start'], 0, 10) : date('Y-m-01'); // Extract Y-m-d from ISO 8601
$end_date = isset($_GET['end']) ? substr($_GET['end'], 0, 10) : date('Y-m-t', strtotime($start_date));

// Validate date format
if (!DateTime::createFromFormat('Y-m-d', $start_date) || !DateTime::createFromFormat('Y-m-d', $end_date)) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid date format']);
    exit;
}

// Debug: Log the received dates and environment
file_put_contents(__DIR__ . '/debug.log', "Time: " . date('Y-m-d H:i:s') . " - Start: $start_date, End: $end_date, Server: " . $_SERVER['REQUEST_URI'] . "\n", FILE_APPEND);
try {
    // Test database connection
    $pdo->query("SELECT 1"); // Simple ping to check connection

    // Fetch appointment counts per day
    $stmt = $pdo->prepare("
        SELECT appointment_date, COUNT(*) as count
        FROM appointments
        WHERE appointment_date BETWEEN :start_date AND :end_date
        AND status = 'Scheduled'
        GROUP BY appointment_date
    ");
    $stmt->execute(['start_date' => $start_date, 'end_date' => $end_date]);
    $day_counts = [];
    foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
        $day_counts[$row['appointment_date']] = $row['count'];
        file_put_contents(__DIR__ . '/debug.log', "Count for " . $row['appointment_date'] . ": " . $row['count'] . "\n", FILE_APPEND);
    }

    // Fetch appointment details
    $stmt = $pdo->prepare("
        SELECT id, owner_name, contact_number, appointment_date, appointment_time, reason, status, duration
        FROM appointments
        WHERE appointment_date BETWEEN :start_date AND :end_date
        AND status = 'Scheduled'
        ORDER BY appointment_date, appointment_time
    ");
    $stmt->execute(['start_date' => $start_date, 'end_date' => $end_date]);
    $appointments = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $events = [];
    foreach ($appointments as $appt) {
        $count = $day_counts[$appt['appointment_date']] ?? 0;
        $startDateTime = new DateTime("{$appt['appointment_date']} {$appt['appointment_time']}", new DateTimeZone('Asia/Manila'));
        $endDateTime = clone $startDateTime;
        $endDateTime->modify("+{$appt['duration']} minutes");

        $events[] = [
            'id' => $appt['id'],
            'title' => $appt['owner_name'] . ' - ' . $appt['reason'],
            'start' => $startDateTime->format('Y-m-d\TH:i:s'),
            'end' => $endDateTime->format('Y-m-d\TH:i:s'),
            'extendedProps' => [
                'contact' => $appt['contact_number'],
                'owner' => $appt['owner_name'],
                'reason' => $appt['reason'],
                'status' => $appt['status'],
                'duration' => $appt['duration'],
                'count' => $count,
                'isFull' => $count >= 6
            ],
            'backgroundColor' => $count >= 6 ? '#dc3545' : '#28a745', // Red if full, green if not
            'borderColor' => $count >= 6 ? '#dc3545' : '#28a745'
        ];
        file_put_contents(__DIR__ . '/debug.log', "Event: " . $appt['owner_name'] . " on " . $appt['appointment_date'] . " at " . $appt['appointment_time'] . " (count: $count, duration: {$appt['duration']})\n", FILE_APPEND);
    }

    // Debug: Log the number of events
    file_put_contents(__DIR__ . '/debug.log', "Events found: " . count($events) . "\n", FILE_APPEND);

    echo json_encode($events);
} catch (PDOException $e) {
    http_response_code(500);
    $error = ['error' => 'Database error: ' . $e->getMessage()];
    file_put_contents(__DIR__ . '/debug.log', "Error: " . $e->getMessage() . "\n", FILE_APPEND);
    echo json_encode($error);
    exit;
}

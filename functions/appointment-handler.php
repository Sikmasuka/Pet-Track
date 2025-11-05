<?php
// Ensure session starts
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . "/../db.php"; // Include your database connection
require_once __DIR__ . "/../functions/logs.php"; // Include logging functions
date_default_timezone_set('Asia/Manila'); // Set to Philippine Standard Time (UTC+08:00)

// Debug: Log script execution
file_put_contents('debug.log', "Script executed at " . date('Y-m-d H:i:s') . "\n", FILE_APPEND);

// New function to log appointment bookings
function logAppointment($pdo, $owner_name, $appointment_date, $appointment_time)
{
    try {
        $dateObj = DateTime::createFromFormat('Y-m-d', $appointment_date, new DateTimeZone('Asia/Manila'));
        $timeObj = DateTime::createFromFormat('H:i:s', $appointment_time, new DateTimeZone('Asia/Manila'));
        $formattedDate = $dateObj->format('F j, Y');
        $formattedTime = $timeObj->format('g:i A');
        $description = "Guest $owner_name booked an appointment on $formattedDate at $formattedTime";
        logAction($pdo, 0, 'Appointment', $description, 'Guest');
        file_put_contents('debug.log', "Appointment logged: $description\n", FILE_APPEND);
    } catch (PDOException $e) {
        file_put_contents('debug.log', "Log error in logAppointment: " . $e->getMessage() . "\n", FILE_APPEND);
    }
}

// Test database connection
try {
    $pdo->query("SELECT 1");
    file_put_contents('debug.log', "Database connection successful.\n", FILE_APPEND);
} catch (PDOException $e) {
    file_put_contents('debug.log', "Connection error: " . $e->getMessage() . "\n", FILE_APPEND);
    $_SESSION['error'] = "Database connection failed: " . $e->getMessage();
    header("Location: ../index.php");
    exit();
}

// Get and log form data
$owner_name = trim($_POST['owner_name'] ?? '');
$contact_number = trim($_POST['contact_number'] ?? '');
$appointment_date = trim($_POST['appointment_date'] ?? '');
$appointment_time = trim($_POST['appointment_time'] ?? '') . ':00'; // Ensure seconds
$reason = trim($_POST['reason'] ?? '');

file_put_contents('debug.log', "Form data: " . print_r(['owner_name' => $owner_name, 'contact_number' => $contact_number, 'appointment_date' => $appointment_date, 'appointment_time' => $appointment_time, 'reason' => $reason], true) . "\n", FILE_APPEND);

try {
    // Basic validation
    if (empty($owner_name) || empty($contact_number) || empty($appointment_date) || empty($appointment_time) || empty($reason)) {
        $_SESSION['error'] = "Please fill in all required fields.";
        header("Location: ../landing-page.php");
        exit();
    }

    // Validate and sanitize date
    $dateObj = DateTime::createFromFormat('Y-m-d', $appointment_date);
    if ($dateObj === false || $appointment_date !== $dateObj->format('Y-m-d')) {
        $_SESSION['error'] = "Invalid appointment date. Use YYYY-MM-DD format.";
        header("Location: ../landing-page.php");
        exit();
    }

    // Ensure date is not in the past
    $today = new DateTime('now', new DateTimeZone('Asia/Manila'));
    if (new DateTime($appointment_date) < $today->setTime(0, 0, 0)) {
        $_SESSION['error'] = "Cannot book appointments for past dates.";
        header("Location: ../landing-page.php");
        exit();
    }

    // Validate time is between 8:00 AM and 6:00 PM
    $timeObj = DateTime::createFromFormat('H:i:s', $appointment_time, new DateTimeZone('Asia/Manila'));
    if ($timeObj === false) {
        $_SESSION['error'] = "Invalid appointment time.";
        header("Location: ../landing-page.php");
        exit();
    }
    $hours = (int)$timeObj->format('H');
    $minutes = (int)$timeObj->format('i');
    if ($hours < 8 || $hours > 18 || ($hours === 18 && $minutes > 0)) {
        $_SESSION['error'] = "Appointments can only be booked between 8:00 AM and 6:00 PM.";
        header("Location: ../landing-page.php");
        exit();
    }

    // Check total appointments for the date (max 6)
    $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM appointments WHERE appointment_date = :appointment_date");
    $stmt->execute(['appointment_date' => $appointment_date]);
    $count = $stmt->fetchColumn();
    if ($count >= 6) {
        $_SESSION['error'] = "This day is fully booked (6/6 appointments). Please choose another date.";
        header("Location: ../landing-page.php");
        exit();
    }

    // Check for time overlaps (90-minute slots)
    $duration = 90; // Duration in minutes (1 hour 30 minutes)
    $startTime = new DateTime("$appointment_date $appointment_time", new DateTimeZone('Asia/Manila'));
    $endTime = clone $startTime;
    $endTime->modify("+$duration minutes");

    $stmt = $pdo->prepare("
        SELECT appointment_time, duration 
        FROM appointments 
        WHERE appointment_date = :appointment_date 
        AND status IN ('Scheduled')
    ");
    $stmt->execute(['appointment_date' => $appointment_date]);
    $existingAppointments = $stmt->fetchAll(PDO::FETCH_ASSOC);

    foreach ($existingAppointments as $existing) {
        $existingStart = new DateTime("$appointment_date $existing[appointment_time]", new DateTimeZone('Asia/Manila'));
        $existingEnd = clone $existingStart;
        $existingEnd->modify("+$existing[duration] minutes");

        if ($startTime < $existingEnd && $endTime > $existingStart) {
            $_SESSION['error'] = "This time slot overlaps with an existing appointment. Please choose another time.";
            header("Location: ../landing-page.php");
            exit();
        }
    }

    // Insert into database
    $stmt = $pdo->prepare("
        INSERT INTO appointments (owner_name, contact_number, appointment_date, appointment_time, reason, status, duration)
        VALUES (:owner_name, :contact_number, :appointment_date, :appointment_time, :reason, 'Scheduled', :duration)
    ");
    $params = [
        'owner_name' => $owner_name,
        'contact_number' => $contact_number,
        'appointment_date' => $appointment_date,
        'appointment_time' => $appointment_time,
        'reason' => $reason,
        'duration' => $duration
    ];
    $stmt->execute($params);

    // Verify insertion
    $lastId = $pdo->lastInsertId();
    if ($lastId > 0) {
        logAppointment($pdo, $owner_name, $appointment_date, $appointment_time);
        $_SESSION['success'] = "Appointment booked successfully! (ID: $lastId)";
    } else {
        $_SESSION['error'] = "Appointment was not saved. Please try again.";
    }
    header("Location: ../landing-page.php");
    exit();
} catch (PDOException $e) {
    file_put_contents('debug.log', "Insert error: " . $e->getMessage() . "\n", FILE_APPEND);
    $_SESSION['error'] = "Failed to book appointment: " . $e->getMessage();
    header("Location: ../landing-page.php");
    exit();
} catch (Exception $e) {
    file_put_contents('debug.log', "General error: " . $e->getMessage() . "\n", FILE_APPEND);
    $_SESSION['error'] = "An unexpected error occurred: " . $e->getMessage();
    header("Location: ../landing-page.php");
    exit();
}

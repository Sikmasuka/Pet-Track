<?php
// functions/appointment-handler.php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/* --------------------------------------------------------------
   DEBUG – KEEP THESE LINES ONLY WHILE DEBUGGING
   They will write the real error to the browser console.
-------------------------------------------------------------- */
ini_set('display_errors', 0);               // do NOT show raw PHP errors on screen
error_reporting(E_ALL);

/* ----------------------------------------------------------- */

require_once __DIR__ . '/../db.php';
require_once __DIR__ . '/../functions/logs.php';
date_default_timezone_set('Asia/Manila');

$ALLOWED_SLOTS = ['08:00', '09:30', '11:00', '12:30', '14:00', '15:30'];
$MAX_PER_DAY   = 6;

/* ----------------------- CSRF ----------------------- */
if (!isset($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
    sendError('Invalid CSRF token.');
}

/* ----------------------- INPUTS ----------------------- */
$owner_name       = trim($_POST['owner_name'] ?? '');
$address          = trim($_POST['address'] ?? '');
$contact_number   = trim($_POST['contact_number'] ?? '');
$appointment_date = trim($_POST['appointment_date'] ?? '');
$appointment_time = trim($_POST['appointment_time'] ?? '');
$reason           = trim($_POST['reason'] ?? '');
$other_reason     = trim($_POST['other_reason'] ?? '');
$pet_name         = trim($_POST['pet_name'] ?? '');
$pet_species      = trim($_POST['pet_species'] ?? '');
$pet_sex          = trim($_POST['pet_sex'] ?? '');
$pet_breed        = trim($_POST['pet_breed'] ?? '');
$pet_weight       = trim($_POST['pet_weight'] ?? '');
$pet_birth_date   = trim($_POST['pet_birth_date'] ?? '');

/* ------------------- LOGGED‑IN CLIENT ------------------- */
$client_id = $_SESSION['client_id'] ?? null;
if ($client_id) {
    $owner_name     = $_SESSION['client_name']     ?? $owner_name;
    $address        = $_SESSION['client_address']  ?? $address;
    $contact_number = $_SESSION['client_contact']  ?? $contact_number;   // <-- from registration
}

/* ------------------- CONTACT VALIDATION ------------------- */
if (empty($contact_number) || !preg_match('/^09\d{9}$/', $contact_number)) {
    sendError('Valid Philippine mobile number (09xxxxxxxxx) is required.');
}

/* ------------------- OTHER REQUIRED FIELDS ------------------- */
if (
    empty($owner_name) || empty($appointment_date) || empty($appointment_time) ||
    empty($reason) || empty($pet_name) || empty($pet_species) ||
    empty($pet_sex) || empty($pet_breed)
) {
    sendError('Please fill in all required fields.');
}

/* ------------------- DATE ------------------- */
$dateObj = DateTime::createFromFormat('Y-m-d', $appointment_date);
if (!$dateObj || $dateObj->format('Y-m-d') !== $appointment_date) {
    sendError('Invalid date format.');
}
$today = new DateTime('today', new DateTimeZone('Asia/Manila'));
if ($dateObj < $today) {
    sendError('Cannot book past dates.');
}

/* ------------------- TIME ------------------- */
if (!in_array($appointment_time, $ALLOWED_SLOTS)) {
    sendError('Invalid time slot selected.');
}
$appointment_time_full = $appointment_time . ':00';

/* ------------------- PET WEIGHT ------------------- */
if (!is_numeric($pet_weight) || floatval($pet_weight) <= 0) {
    sendError('Pet weight must be a positive number.');
}
$pet_weight = (float)$pet_weight;

/* ------------------- PET BIRTH DATE ------------------- */
if (!empty($pet_birth_date)) {
    $birthObj = DateTime::createFromFormat('Y-m-d', $pet_birth_date);
    if (!$birthObj || $birthObj->format('Y-m-d') !== $pet_birth_date) {
        sendError('Invalid pet birth date.');
    }
}

/* ------------------- REASON ------------------- */
if ($reason === 'Other') {
    if (empty($other_reason)) {
        sendError('Please specify the reason when selecting “Other”.');
    }
    $reason = $other_reason;
}

/* --------------------------------------------------------------
   DATABASE TRANSACTION
-------------------------------------------------------------- */
try {
    $pdo->beginTransaction();

    /* ----- DAILY LIMIT ----- */
    $stmt = $pdo->prepare(
        "SELECT COUNT(*) FROM appointments WHERE appointment_date = ? AND status = 'Scheduled'"
    );
    $stmt->execute([$appointment_date]);
    if ($stmt->fetchColumn() >= $MAX_PER_DAY) {
        $pdo->rollBack();
        sendError('This day is fully booked (6/6).');
    }

    /* ----- TIME CONFLICT ----- */
    $stmt = $pdo->prepare(
        "SELECT id FROM appointments WHERE appointment_date = ? AND appointment_time = ? AND status = 'Scheduled'"
    );
    $stmt->execute([$appointment_date, $appointment_time_full]);
    if ($stmt->fetch()) {
        $pdo->rollBack();
        sendError('This time slot was just taken. Please choose another.');
    }

    /* ----- CLIENT (find or create) ----- */
    if (!$client_id) {
        $stmt = $pdo->prepare(
            "SELECT client_id FROM Client WHERE client_name = ? AND client_contact_number = ?"
        );
        $stmt->execute([$owner_name, $contact_number]);
        $row = $stmt->fetch();

        if ($row) {
            $client_id = $row['client_id'];
        } else {
            $stmt = $pdo->prepare(
                "INSERT INTO Client (client_name, client_address, client_contact_number, status, created_at)
                 VALUES (?, ?, ?, ?, NOW())"
            );
            $stmt->execute([$owner_name, $address, $contact_number]);
            $client_id = $pdo->lastInsertId();
            if (!$client_id) {
                $pdo->rollBack();
                sendError('Failed to create client record.');
            }
        }
    }

    /* ----- PET (find or create) ----- */
    $stmt = $pdo->prepare(
        "SELECT pet_id FROM Pet WHERE client_id = ? AND pet_name = ?"
    );
    $stmt->execute([$client_id, $pet_name]);
    $row = $stmt->fetch();

    if ($row) {
        $pet_id = $row['pet_id'];
    } else {
        $stmt = $pdo->prepare("
        INSERT INTO Pet 
        (client_id, pet_name, pet_species, pet_sex, pet_breed, pet_weight, pet_birth_date, status)
        VALUES (?, ?, ?, ?, ?, ?, ?, 1)
    ");
        $stmt->execute([
            $client_id,
            $pet_name,
            $pet_species,
            $pet_sex,
            $pet_breed,
            $pet_weight,
            $pet_birth_date ?: null,
        ]);
        $pet_id = $pdo->lastInsertId();

        if (!$pet_id) {
            $pdo->rollBack();
            sendError('Failed to create pet record.');
        }
    }

    /* ----- INSERT APPOINTMENT ----- */
    $duration = 90;
    $stmt = $pdo->prepare(
        "INSERT INTO appointments
         (client_id, pet_id, owner_name, contact_number, appointment_date,
          appointment_time, reason, status, duration)
         VALUES (?, ?, ?, ?, ?, ?, ?, 'Scheduled', ?)"
    );
    $stmt->execute([
        $client_id,
        $pet_id,
        $owner_name,
        $contact_number,
        $appointment_date,
        $appointment_time_full,
        $reason,
        $duration
    ]);
    $appt_id = $pdo->lastInsertId();

    /* ----- NOTIFICATION & LOG ----- */
    logAppointment($pdo, $owner_name, $appointment_date, $appointment_time_full);

    $pdo->commit();

    $_SESSION['success'] = "Appointment booked successfully! ID: $appt_id";
    header('Location: ../index.php');
    exit;
} catch (Throwable $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }

    // ------------------- CONSOLE LOGGING -------------------
    $errorMsg = $e->getMessage();
    $trace    = $e->getTraceAsString();

    // Send JSON + JS to the browser so it prints to console
    header('Content-Type: application/json');
    echo json_encode([
        'error'   => true,
        'message' => $errorMsg,
        'trace'   => $trace,
        'js'      => "console.error('APPOINTMENT ERROR:', " . json_encode($errorMsg) . ");\n" .
            "console.error('Stack trace:', " . json_encode($trace) . ");\n" .
            "alert('Booking failed – check console (F12) for details.');"
    ]);
    exit;
}

/* --------------------------------------------------------------
   Helper – send error + console log + redirect
-------------------------------------------------------------- */
function sendError(string $msg)
{
    $_SESSION['error'] = $msg;

    // Log to console even on validation errors
    header('Content-Type: application/json');
    echo json_encode([
        'error'   => true,
        'message' => $msg,
        'js'      => "console.warn('Validation Error:', " . json_encode($msg) . ");\n" .
            "alert(" . json_encode($msg) . ");"
    ]);
    exit;
}

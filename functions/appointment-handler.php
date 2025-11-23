<?php
// functions/appointment-handler.php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

ini_set('display_errors', 0);
error_reporting(E_ALL);

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
$contact_number   = trim($_POST['contact_number'] ?? ''); // ← raw input
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

/* ------------------- LOGGED-IN CLIENT ------------------- */
$client_id = $_SESSION['client_id'] ?? null;
if ($client_id) {
    $owner_name = $_SESSION['client_name']    ?? $owner_name;
    $address    = $_SESSION['client_address'] ?? $address;
    // DO NOT overwrite $contact_number from session!
    // We want the value from the form (09...) so we can convert it properly
}

/* ------------------- CONVERT 09... → 63... (THIS MUST RUN FIRST!) ------------------- */
function to_international_ph($number)
{
    $digits = preg_replace('/\D/', '', $number); // Remove all non-digits

    // Case 1: 09171234567 → 11 digits → 639171234567
    if (strlen($digits) === 11 && str_starts_with($digits, '09')) {
        return '63' . substr($digits, 1);
    }

    // Case 2: 9171234567 → 10 digits starting with 9 → 639171234567
    if (strlen($digits) === 10 && str_starts_with($digits, '9')) {
        return '63' . $digits;
    }

    // Case 3: Already 639171234567 → 12 digits
    if (strlen($digits) === 12 && str_starts_with($digits, '63')) {
        return $digits;
    }

    // Case 4: +639171234567 → remove +
    if (strlen($digits) === 13 && str_starts_with($digits, '639')) {
        return $digits;
    }

    return false; // Invalid
}

$contact_number = to_international_ph($contact_number);
if ($contact_number === false || empty($contact_number)) {
    sendError('Please enter a valid Philippine mobile number (e.g., 09171234567 or 9171234567).');
}

/* ------------------- SINGLE, CORRECT CONTACT VALIDATION ------------------- */
if (empty($contact_number) || !preg_match('/^639\d{9}$/', $contact_number)) {
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
        sendError('Please specify the reason when selecting "Other".');
    }
    $reason = $other_reason;
}

/* --------------------------------------------------------------
   DATABASE TRANSACTION
-------------------------------------------------------------- */
try {
    $pdo->beginTransaction();

    /* ----- DAILY LIMIT ----- */
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM appointments WHERE appointment_date = ? AND status = 'Scheduled'");
    $stmt->execute([$appointment_date]);
    if ($stmt->fetchColumn() >= $MAX_PER_DAY) {
        $pdo->rollBack();
        sendError('This day is fully booked (6/6).');
    }

    /* ----- TIME CONFLICT ----- */
    $stmt = $pdo->prepare("SELECT id FROM appointments WHERE appointment_date = ? AND appointment_time = ? AND status = 'Scheduled'");
    $stmt->execute([$appointment_date, $appointment_time_full]);
    if ($stmt->fetch()) {
        $pdo->rollBack();
        sendError('This time slot was just taken. Please choose another.');
    }

    /* ----- CLIENT (find or create) ----- */
    if (!$client_id) {
        $stmt = $pdo->prepare("SELECT client_id FROM Client WHERE client_name = ? AND client_contact_number = ?");
        $stmt->execute([$owner_name, $contact_number]);
        $row = $stmt->fetch();

        if ($row) {
            $client_id = $row['client_id'];
        } else {
            $stmt = $pdo->prepare("INSERT INTO Client (client_name, client_address, client_contact_number, status, created_at)
                                   VALUES (?, ?, ?, 1, NOW())");
            $stmt->execute([$owner_name, $address, $contact_number]);
            $client_id = $pdo->lastInsertId();
            if (!$client_id) {
                $pdo->rollBack();
                sendError('Failed to create client record.');
            }
        }
    }

    /* ----- PET (find or create) ----- */
    $stmt = $pdo->prepare("SELECT pet_id FROM Pet WHERE client_id = ? AND pet_name = ?");
    $stmt->execute([$client_id, $pet_name]);
    $row = $stmt->fetch();

    if ($row) {
        $pet_id = $row['pet_id'];
    } else {
        $stmt = $pdo->prepare("INSERT INTO Pet 
            (client_id, pet_name, pet_species, pet_sex, pet_breed, pet_weight, pet_birth_date, status)
            VALUES (?, ?, ?, ?, ?, ?, ?, 1)");
        $stmt->execute([
            $client_id,
            $pet_name,
            $pet_species,
            $pet_sex,
            $pet_breed,
            $pet_weight,
            $pet_birth_date ?: null
        ]);
        $pet_id = $pdo->lastInsertId();
        if (!$pet_id) {
            $pdo->rollBack();
            sendError('Failed to create pet record.');
        }
    }

    /* ----- INSERT APPOINTMENT ----- */
    $stmt = $pdo->prepare("INSERT INTO appointments
        (client_id, pet_id, owner_name, contact_number, appointment_date,
         appointment_time, reason, status, duration)
         VALUES (?, ?, ?, ?, ?, ?, ?, 'Scheduled', 90)");
    $stmt->execute([
        $client_id,
        $pet_id,
        $owner_name,
        $contact_number,
        $appointment_date,
        $appointment_time_full,
        $reason
    ]);
    $appt_id = $pdo->lastInsertId();

    logAppointment($pdo, $owner_name, $appointment_date, $appointment_time_full);

    $pdo->commit();

    $_SESSION['success'] = "Appointment booked successfully! ID: $appt_id";
    header('Location: ../index.php');
    exit;
} catch (Throwable $e) {
    if ($pdo->inTransaction()) $pdo->rollBack();

    header('Content-Type: application/json');
    echo json_encode([
        'error'   => true,
        'message' => $e->getMessage(),
        'js'      => "console.error('APPOINTMENT ERROR:', " . json_encode($e->getMessage()) . "); alert('Booking failed');"
    ]);
    exit;
}

function sendError(string $msg)
{
    $_SESSION['error'] = $msg;
    header('Content-Type: application/json');
    echo json_encode([
        'error'   => true,
        'message' => $msg,
        'js'      => "console.warn('Validation Error:', " . json_encode($msg) . "); alert(" . json_encode($msg) . ");"
    ]);
    exit;
}

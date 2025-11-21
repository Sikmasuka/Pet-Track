<?php
session_start();
require_once 'db.php';
require_once 'functions/archive-handler.php';
require_once './functions/logs.php';

if (!isset($_SESSION['vet_id'])) {
    header('Location: index.php');
    exit;
}

$stmt = $pdo->prepare("SELECT vet_name FROM Veterinarian WHERE vet_id = ?");
$stmt->execute([$_SESSION['vet_id']]);
$vetName = $stmt->fetchColumn() ? htmlspecialchars($stmt->fetchColumn()) : "Veterinarian";

function validateInput($data)
{
    return htmlspecialchars(trim($data), ENT_QUOTES, 'UTF-8');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // === CLIENT DATA ===
    $client_name        = validateInput($_POST['client_name'] ?? '');
    $client_address     = validateInput($_POST['client_address'] ?? '');
    $client_contact_raw = trim($_POST['client_contact_number'] ?? '');

    // === NORMALIZE CONTACT NUMBER (09... → 639...) ===
    $digits = preg_replace('/\D/', '', $client_contact_raw);

    if (strlen($digits) === 11 && str_starts_with($digits, '09')) {
        $client_contact = '63' . substr($digits, 1);
    } elseif (strlen($digits) === 12 && str_starts_with($digits, '63')) {
        $client_contact = $digits;
    } elseif (strlen($digits) === 13 && str_starts_with($digits, '+63')) {
        $client_contact = substr($digits, 1);
    } else {
        $_SESSION['error'] = "Invalid contact number. Please enter 11 digits starting with 09 (e.g. 09171234567)";
        header('Location: clients.php');
        exit;
    }

    if (empty($client_name) || empty($client_address)) {
        $_SESSION['error'] = "Client name and address are required.";
        header('Location: clients.php');
        exit;
    }

    // === PET & MEDICAL DATA ===
    $pet_name        = validateInput($_POST['pet_name'] ?? '');
    $pet_sex         = $_POST['pet_sex'] ?? '';
    $pet_weight      = $_POST['pet_weight'] ?? '';
    $pet_breed       = validateInput($_POST['pet_breed'] ?? '');
    $pet_birth_date  = $_POST['pet_birth_date'] ?? '';
    $pet_species     = $_POST['pet_species'] ?? '';

    $medical_condition = validateInput($_POST['medical_condition'] ?? '');
    $medical_diagnosis = validateInput($_POST['medical_diagnosis'] ?? '');
    $medical_symptoms  = validateInput($_POST['medical_symptoms'] ?? '');
    $medical_treatment = validateInput($_POST['medical_treatment'] ?? '');

    try {
        $pdo->beginTransaction();

        // ==================== ADD NEW CLIENT ====================
        if (isset($_POST['add_client'])) {

            $stmt = $pdo->prepare("INSERT INTO Client (client_name, client_address, client_contact_number, status, created_at) VALUES (?, ?, ?, 1, NOW())");
            $stmt->execute([$client_name, $client_address, $client_contact]);
            $client_id = $pdo->lastInsertId();

            $logDesc = "added new client '$client_name'";

            // Add pet if all required fields are filled
            if (!empty($pet_name) && !empty($pet_sex) && !empty($pet_species)) {
                $stmt = $pdo->prepare("INSERT INTO Pet (client_id, pet_name, pet_species, pet_sex, pet_breed, pet_weight, pet_birth_date) VALUES (?, ?, ?, ?, ?, ?, ?)");
                $stmt->execute([$client_id, $pet_name, $pet_species, $pet_sex, $pet_breed, $pet_weight ?: null, $pet_birth_date ?: null]);
                $pet_id = $pdo->lastInsertId();
                $logDesc .= ", pet '$pet_name'";

                // Add medical record if provided
                if (!empty($medical_condition) && !empty($medical_diagnosis)) {
                    $stmt = $pdo->prepare("INSERT INTO Medical_Records (pet_id, medical_condition, medical_diagnosis, medical_symptoms, medical_treatment, date, record_date, status) VALUES (?, ?, ?, ?, ?, CURDATE(), CURDATE(), 1)");
                    $stmt->execute([$pet_id, $medical_condition, $medical_diagnosis, $medical_symptoms, $medical_treatment]);
                    $logDesc .= " and medical record";
                }
            }

            logAction($pdo, $_SESSION['vet_id'], 'add', $logDesc, 'Admin');
            $pdo->commit();
            $_SESSION['message'] = "Client added successfully!";
            header('Location: clients.php');
            exit;
        }

        // ==================== UPDATE CLIENT ====================
        elseif (isset($_POST['update_client'])) {
            $client_id = (int)$_POST['client_id'];
            $pet_id    = !empty($_POST['pet_id']) ? (int)$_POST['pet_id'] : null;
            $record_id = !empty($_POST['record_id']) ? (int)$_POST['record_id'] : null;

            // Update client info
            $stmt = $pdo->prepare("UPDATE Client SET client_name = ?, client_address = ?, client_contact_number = ? WHERE client_id = ?");
            $stmt->execute([$client_name, $client_address, $client_contact, $client_id]);

            $logDesc = "updated client '$client_name' (ID: $client_id)";
            $changeMade = true;

            // Update or add pet
            if (!empty($pet_name) && !empty($pet_sex) && !empty($pet_species)) {

                if ($pet_id) {
                    // Check if this pet belongs to this client
                    $checkStmt = $pdo->prepare("SELECT 1 FROM Pet WHERE pet_id = ? AND client_id = ? AND status = 1");
                    $checkStmt->execute([$pet_id, $client_id]);
                    $petExists = $checkStmt->fetchColumn(); // returns 1 or false
                } else {
                    $petExists = false;
                }

                if ($petExists) {
                    // UPDATE existing pet
                    $stmt = $pdo->prepare("UPDATE Pet 
                               SET pet_name = ?, pet_sex = ?, pet_species = ?, 
                                   pet_breed = ?, pet_weight = ?, pet_birth_date = ? 
                               WHERE pet_id = ?");
                    $stmt->execute([$pet_name, $pet_sex, $pet_species, $pet_breed, $pet_weight ?: null, $pet_birth_date ?: null, $pet_id]);
                } else {
                    // INSERT new pet
                    $stmt = $pdo->prepare("INSERT INTO Pet 
                               (client_id, pet_name, pet_species, pet_sex, pet_breed, pet_weight, pet_birth_date) 
                               VALUES (?, ?, ?, ?, ?, ?, ?)");
                    $stmt->execute([$client_id, $pet_name, $pet_species, $pet_sex, $pet_breed, $pet_weight ?: null, $pet_birth_date ?: null]);
                    $pet_id = $pdo->lastInsertId();
                }

                $logDesc .= ", pet '$pet_name'";
            }

            // Update or add medical record
            if ($pet_id && !empty($medical_condition) && !empty($medical_diagnosis)) {
                if ($record_id) {
                    $stmt = $pdo->prepare("UPDATE Medical_Records SET medical_condition=?, medical_diagnosis=?, medical_symptoms=?, medical_treatment=?, date=CURDATE() WHERE record_id=? AND pet_id=?");
                    $stmt->execute([$medical_condition, $medical_diagnosis, $medical_symptoms, $medical_treatment, $record_id, $pet_id]);
                } else {
                    $stmt = $pdo->prepare("INSERT INTO Medical_Records (pet_id, medical_condition, medical_diagnosis, medical_symptoms, medical_treatment, date, record_date, status) VALUES (?, ?, ?, ?, ?, CURDATE(), CURDATE(), 1)");
                    $stmt->execute([$pet_id, $medical_condition, $medical_diagnosis, $medical_symptoms, $medical_treatment]);
                }
                $logDesc .= " and medical record";
            }

            logAction($pdo, $_SESSION['vet_id'], 'update', $logDesc, 'Admin');
            $pdo->commit();
            $_SESSION['message'] = "Client updated successfully!";
            header('Location: clients.php');
            exit;
        }
    } catch (Exception $e) {
        $pdo->rollBack();
        error_log("Client handler error: " . $e->getMessage());
        $_SESSION['error'] = "Operation failed. Please try again.";
        header('Location: clients.php');
        exit;
    }
}

/**
 * Handle archiving a client and their pets via GET request
 */
if (isset($_GET['delete_client_id']) && is_numeric($_GET['delete_client_id'])) {
    try {
        $client_id = (int)$_GET['delete_client_id'];

        // Fetch client name before update
        $stmt = $pdo->prepare("SELECT client_name FROM Client WHERE client_id = ?");
        $stmt->execute([$client_id]);
        $client = $stmt->fetch(PDO::FETCH_ASSOC);
        $client_name = $client['client_name'] ?? 'Unknown';

        // Begin transaction
        $pdo->beginTransaction();

        // Archive all pets for this client
        $stmt = $pdo->prepare("UPDATE Pet SET status = 0 WHERE client_id = ?");
        $stmt->execute([$client_id]);

        // Archive the client
        $stmt = $pdo->prepare("UPDATE Client SET status = 0 WHERE client_id = ?");
        $stmt->execute([$client_id]);

        // Archive associated medical records
        $stmt = $pdo->prepare("UPDATE Medical_Records SET status = 0 WHERE pet_id IN (SELECT pet_id FROM Pet WHERE client_id = ?)");
        $stmt->execute([$client_id]);

        // Log the action
        $actionType = 'delete';
        $description = $_SESSION['username'] . " archived client '$client_name'";
        logAction($pdo, $_SESSION['vet_id'], $actionType, $description, 'Admin');

        // Commit transaction
        $pdo->commit();

        header('Location: clients.php?message=Client, associated pets, and medical records archived successfully');
        exit;
    } catch (PDOException $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        $error = "Database error: Cannot archive client, pets, and medical records. " . $e->getMessage();
        error_log("Database error: " . $e->getMessage());
        header('Location: clients.php?error=' . urlencode($error));
        exit;
    }
}

/**
 * Fetch client data for editing
 */
function getDataToEdit($pdo)
{
    $clientToEdit = null;
    $petToEdit = null;
    $medicalRecordToEdit = null;
    $error = null;

    if (isset($_GET['edit_client_id']) && is_numeric($_GET['edit_client_id'])) {
        try {
            // Get client info
            $stmt = $pdo->prepare("SELECT * FROM Client WHERE client_id = ? AND status = 1");
            $stmt->execute([(int)$_GET['edit_client_id']]);
            $clientToEdit = $stmt->fetch(PDO::FETCH_ASSOC);

            // Get the first pet for this client
            if ($clientToEdit) {
                $stmt = $pdo->prepare("SELECT * FROM Pet WHERE client_id = ? AND status = 1 LIMIT 1");
                $stmt->execute([(int)$_GET['edit_client_id']]);
                $petToEdit = $stmt->fetch(PDO::FETCH_ASSOC);
                error_log("Pet to edit for client_id {$_GET['edit_client_id']}: " . json_encode($petToEdit));

                // Get the first medical record for this pet (if exists)
                if ($petToEdit) {
                    $stmt = $pdo->prepare("SELECT * FROM Medical_Records WHERE pet_id = ? AND status = 1 LIMIT 1");
                    $stmt->execute([$petToEdit['pet_id']]);
                    $medicalRecordToEdit = $stmt->fetch(PDO::FETCH_ASSOC);
                    error_log("Medical record to edit for pet_id {$petToEdit['pet_id']}: " . json_encode($medicalRecordToEdit));
                }
            }
        } catch (PDOException $e) {
            $error = "Database error: " . $e->getMessage();
            error_log("Database error in getDataToEdit: " . $e->getMessage());
        }
    }

    return [
        'client' => $clientToEdit,
        'pet' => $petToEdit,
        'medical_record' => $medicalRecordToEdit,
        'error' => $error
    ];
}

// Get data for editing
$editData = getDataToEdit($pdo);
$clientToEdit = $editData['client'];
$petToEdit = $editData['pet'];
$medicalRecordToEdit = $editData['medical_record'];
$error = $error ?? $editData['error'];


// Handle Viewing the clients details
function getDataToView($pdo)
{
    $clientToView = null;
    $petToView = null;
    $medicalToView = null;

    if (isset($_GET['view_client_id']) && is_numeric($_GET['view_client_id'])) {
        try {
            //Get Client info
            $stmt = $pdo->prepare("SELECT * FROM Client WHERE client_id = ? AND status = 1");
            $stmt->execute([(int)$_GET['view_client_id']]);
            $clientToView = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($clientToView) {
                $stmt = $pdo->prepare("SELECT * FROM Pet WHERE client_id = ? AND status = 1");
                $stmt->execute([(int)$_GET['view_client_id']]);
                $petToView = $stmt->fetch(PDO::FETCH_ASSOC);
                error_log("Pet to view for client_id {$_GET['view_client_id']}: " . json_encode($petToView));

                if ($petToView) {
                    $stmt = $pdo->prepare("SELECT * FROM Medical_Records WHERE pet_id = ? AND status = 1");
                    $stmt->execute([$petToView['pet_id']]);
                    $medicalToView = $stmt->fetch(PDO::FETCH_ASSOC);
                    error_log("Medical record to view for pet_id {$petToView['pet_id']}: " . json_encode($medicalToView));
                }
            }
        } catch (PDOException $e) {
            $error = "Database error: " . $e->getMessage();
            error_log("Database error in getDataToView: " . $e->getMessage());
        }
    }

    return ([
        'client' => $clientToView,
        'pet' => $petToView,
        'medical_record' => $medicalToView,
        'error' => $error ?? null
    ]);
}

// Get data for viewing
$viewData = getDataToView($pdo);
$clientToView = $viewData['client'];
$petToView = $viewData['pet'];
$medicalToView = $viewData['medical_record'];
$error = $error ?? $viewData['error'];


// Add this code to your existing clients-handler.php file

// Handle AJAX request for client details
if (isset($_GET['get_client_details'])) {
    $clientId = (int)$_GET['get_client_details'];

    try {
        // Log request
        error_log("Processing get_client_details for client_id: $clientId");

        // Fetch client data
        $stmt = $pdo->prepare("SELECT * FROM Client WHERE client_id = ? AND status = 1");
        $stmt->execute([$clientId]);
        $client = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$client) {
            error_log("Client not found for client_id: $clientId");
            header('Content-Type: application/json');
            http_response_code(404);
            echo json_encode(['error' => 'Client not found']);
            exit;
        }

        // Fetch pets for this client
        $stmt = $pdo->prepare("SELECT * FROM Pet WHERE client_id = ? AND status = 1");
        $stmt->execute([$clientId]);
        $pets = $stmt->fetchAll(PDO::FETCH_ASSOC);
        error_log("Fetched " . count($pets) . " pets for client_id: $clientId");

        // Fetch medical records for this client's pets
        $medicalRecords = [];
        if ($pets && count($pets) > 0) {
            $petIds = array_column($pets, 'pet_id');
            $placeholders = str_repeat('?,', count($petIds) - 1) . '?';

            $stmt = $pdo->prepare("SELECT mr.*, p.pet_name 
                                  FROM Medical_Records mr 
                                  JOIN Pet p ON mr.pet_id = p.pet_id 
                                  WHERE mr.pet_id IN ($placeholders) AND mr.status = 1
                                  ORDER BY mr.record_date DESC");
            $stmt->execute($petIds);
            $medicalRecords = $stmt->fetchAll(PDO::FETCH_ASSOC);
            error_log("Fetched " . count($medicalRecords) . " medical records for client_id: $clientId");
        }

        header('Content-Type: application/json');
        echo json_encode([
            'client' => $client,
            'pets' => $pets,
            'medicalRecords' => $medicalRecords
        ], JSON_NUMERIC_CHECK);
        exit;
    } catch (PDOException $e) {
        error_log("Database error for client_id $clientId: " . $e->getMessage());
        header('Content-Type: application/json');
        http_response_code(500);
        echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
        exit;
    }
}

// Handle fetching full medical record details for printing
if (isset($_GET['get_medical_record_details']) && is_numeric($_GET['get_medical_record_details'])) {
    header('Content-Type: application/json');
    // Suppress PHP notices and warnings to ensure a clean JSON output
    error_reporting(0);
    ini_set('display_errors', 0);

    $record_id = (int)$_GET['get_medical_record_details'];

    try {
        // Fetch the medical record
        $stmt = $pdo->prepare("SELECT * FROM Medical_Records WHERE record_id = ?");
        $stmt->execute([$record_id]);
        $record = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$record) {
            echo json_encode(['error' => 'Medical record not found.']);
            exit;
        }

        // Fetch the associated pet
        $stmt = $pdo->prepare("SELECT * FROM Pet WHERE pet_id = ?");
        $stmt->execute([$record['pet_id'] ?? null]);
        $pet = $stmt->fetch(PDO::FETCH_ASSOC) ?: ['pet_name' => 'Unknown Pet', 'client_id' => null];

        $client = ['client_name' => 'Unknown Owner'];
        if (!empty($pet['client_id'])) {
            // Fetch the client (owner)
            $stmt = $pdo->prepare("SELECT * FROM Client WHERE client_id = ?");
            $stmt->execute([$pet['client_id']]);
            $client = $stmt->fetch(PDO::FETCH_ASSOC) ?: $client;
        }

        // Fetch the veterinarian who created the record
        $vet_name = 'Unknown Vet';
        if (!empty($record['vet_id'])) {
            $stmt = $pdo->prepare("SELECT vet_name FROM veterinarian WHERE vet_id = ?");
            $stmt->execute([$record['vet_id']]);
            $vet = $stmt->fetch(PDO::FETCH_ASSOC);
            $vet_name = $vet['vet_name'] ?? $vet_name;
        }

        // No clinic table? Just send clinic name directly
        $clinicName = "Balingasag Dog & Cat Clinic";
        $clinicPhone = "(088) 123-4567"; // or fetch from config later

        echo json_encode([
            'record' => $record,
            'pet' => $pet,
            'client' => $client,
            'vet_name' => $vet_name,
            'clinic' => [
                'name' => $clinicName,
                'phone' => $clinicPhone
            ]
        ]);
    } catch (PDOException $e) {
        echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
    }
    exit;
}
/**
 * Fetch all clients
 */
try {
    $stmt = $pdo->prepare("SELECT * FROM Client WHERE status = 1 ORDER BY client_name ASC");
    $stmt->execute();
    $clients = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $error = "Database error: " . $e->getMessage();
    error_log("Database error in fetching clients: " . $e->getMessage());
    $clients = [];
}

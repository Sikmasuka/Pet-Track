<?php
// Start session and include database connection
session_start();
require_once 'db.php';
require_once 'functions/archive-handler.php';
require_once './functions/logs.php';

// Check if user is logged in
if (!isset($_SESSION['vet_id'])) {
    header('Location: index.php');
    exit;
}

// Fetch vet name for greeting
$stmt = $pdo->prepare("SELECT vet_name FROM Veterinarian WHERE vet_id=?");
$stmt->execute([$_SESSION['vet_id']]);
$user = $stmt->fetch();
$vetName = $user ? htmlspecialchars($user['vet_name']) : "Veterinarian not found";

/**
 * Input validation function
 */
function validateInput($data)
{
    return htmlspecialchars(trim($data), ENT_QUOTES, 'UTF-8');
}

/**
 * Handle adding and updating clients via POST requests
 */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Clients
    $client_name = validateInput($_POST['client_name'] ?? '');
    $client_address = validateInput($_POST['client_address'] ?? '');
    $client_contact = validateInput($_POST['client_contact_number'] ?? '');

    // Pets
    $pet_name = validateInput($_POST['pet_name'] ?? '');
    $pet_sex = $_POST['pet_sex'] ?? '';
    $pet_weight = $_POST['pet_weight'] ?? '';
    $pet_breed = validateInput($_POST['pet_breed'] ?? '');
    $pet_birth_date = $_POST['pet_birth_date'] ?? '';
    $pet_species = $_POST['pet_species'] ?? '';

    // Medical Records
    $medical_condition = validateInput($_POST['medical_condition'] ?? '');
    $medical_diagnosis = validateInput($_POST['medical_diagnosis'] ?? '');
    $medical_symptoms = validateInput($_POST['medical_symptoms'] ?? '');
    $medical_treatment = validateInput($_POST['medical_treatment'] ?? '');

    // Basic validation for client fields
    if (empty($client_name) || empty($client_address) || empty($client_contact)) {
        $error = "All client fields are required";
    } elseif (!preg_match('/^[0-9]{11}$/', $client_contact)) {
        $error = "Contact number must be exactly 11 digits";
    } else {
        try {
            if (isset($_POST['add_client'])) {
                $hasPetData = !empty($pet_name) || !empty($pet_sex) || !empty($pet_weight) || !empty($pet_breed) || !empty($pet_birth_date) || !empty($pet_species);
                $hasMedicalData = !empty($medical_condition) || !empty($medical_diagnosis) || !empty($medical_symptoms) || !empty($medical_treatment);

                if ($hasPetData && (empty($pet_name) || empty($pet_sex) || empty($pet_weight) || empty($pet_breed) || empty($pet_birth_date) || empty($pet_species))) {
                    $error = "All pet fields are required if any pet field is provided.";
                } elseif ($hasMedicalData && (empty($medical_condition) || empty($medical_diagnosis) || empty($medical_symptoms) || empty($medical_treatment))) {
                    // Medical fields are only required if pet data is also present and medical data is provided
                    if (!$hasPetData) {
                        $error = "Cannot add medical record without pet information.";
                    }
                    $error = "All medical record fields are required for adding a client";
                } else {
                    $pdo->beginTransaction();
                    // Insert Client
                    $stmt = $pdo->prepare("INSERT INTO Client (client_name, client_address, client_contact_number, status, created_at) VALUES (?, ?, ?, 1, NOW())");
                    $stmt->execute([$client_name, $client_address, $client_contact]);
                    $client_id = $pdo->lastInsertId();

                    $description = $_SESSION['username'] . " added a new client '$client_name'";

                    if ($hasPetData) {
                        // Insert Pet
                        $stmt = $pdo->prepare("INSERT INTO Pet (pet_name, pet_sex, pet_weight, pet_breed, pet_birth_date, pet_species, client_id) VALUES (?, ?, ?, ?, ?, ?, ?)");
                        $stmt->execute([$pet_name, $pet_sex, $pet_weight, $pet_breed, $pet_birth_date, $pet_species, $client_id]);
                        $pet_id = $pdo->lastInsertId();
                        $description .= ", pet '$pet_name'";
                        if ($hasMedicalData) {
                            // Insert Medical Record
                            $stmt = $pdo->prepare("INSERT INTO Medical_Records (pet_id, date, medical_condition, medical_diagnosis, medical_symptoms, medical_treatment, status, record_date) VALUES (?, CURDATE(), ?, ?, ?, ?, ?, CURDATE())");
                            $stmt->execute([$pet_id, $medical_condition, $medical_diagnosis, $medical_symptoms, $medical_treatment, 1]);
                            $description .= ", and medical record";
                        }
                    }

                    // Log action
                    logAction($pdo, $_SESSION['vet_id'], 'add', $description, 'Admin');
                    $pdo->commit();
                    header('Location: clients.php?message=Client, pet, and medical record added successfully');
                    exit;
                }
            } elseif (isset($_POST['update_client'])) {
                $pdo->beginTransaction();
                $client_id = (int)$_POST['client_id'];
                $pet_id = !empty($_POST['pet_id']) ? (int)$_POST['pet_id'] : null;
                $record_id = !empty($_POST['record_id']) ? (int)$_POST['record_id'] : null;

                // Debugging: Log received data
                error_log("Updating client_id: $client_id, pet_id: $pet_id, record_id: $record_id");
                error_log("Pet fields: " . json_encode([
                    'pet_name' => $pet_name,
                    'pet_sex' => $pet_sex,
                    'pet_weight' => $pet_weight,
                    'pet_breed' => $pet_breed,
                    'pet_birth_date' => $pet_birth_date,
                    'pet_species' => $pet_species
                ]));

                // Update client
                $stmt = $pdo->prepare("UPDATE Client SET client_name=?, client_address=?, client_contact_number=? WHERE client_id=?");
                $stmt->execute([$client_name, $client_address, $client_contact, $client_id]);

                // Initialize log description
                $description = $_SESSION['username'] . " updated client '$client_name' (ID: $client_id)";
                $changeMade = false;

                // Check if pet fields are provided
                $pet_fields_provided = !empty($pet_name) || !empty($pet_sex) || !empty($pet_weight) || !empty($pet_breed) || !empty($pet_birth_date) || !empty($pet_species);
                if ($pet_fields_provided) {
                    // Validate pet fields only if all are provided
                    $all_pet_fields_filled = !empty($pet_name) && !empty($pet_sex) && !empty($pet_weight) && !empty($pet_breed) && !empty($pet_birth_date) && !empty($pet_species);
                    if ($all_pet_fields_filled) {
                        // Validate pet_sex and pet_species
                        $valid_species = ['Dog', 'Cat'];
                        $valid_sex = ['Male', 'Female'];
                        if (!in_array($pet_species, $valid_species)) {
                            $pdo->rollBack();
                            $error = "Invalid pet species selected.";
                            header('Location: clients.php?error=' . urlencode($error));
                            exit;
                        }
                        if (!in_array($pet_sex, $valid_sex)) {
                            $pdo->rollBack();
                            $error = "Invalid pet sex selected.";
                            header('Location: clients.php?error=' . urlencode($error));
                            exit;
                        }

                        // Check if pet exists
                        $stmt = $pdo->prepare("SELECT pet_id FROM Pet WHERE pet_id = ? AND client_id = ? AND status = 1");
                        $stmt->execute([$pet_id, $client_id]);
                        $existing_pet = $stmt->fetch(PDO::FETCH_ASSOC);
                        error_log("Pet exists check: " . json_encode($existing_pet));

                        if ($existing_pet) {
                            // Update existing pet
                            $stmt = $pdo->prepare("UPDATE Pet SET pet_name=?, pet_sex=?, pet_weight=?, pet_breed=?, pet_birth_date=?, pet_species=? WHERE pet_id=? AND client_id=?");
                            $stmt->execute([$pet_name, $pet_sex, $pet_weight, $pet_breed, $pet_birth_date, $pet_species, $pet_id, $client_id]);
                            $changeMade = true;
                            error_log("Updated pet with pet_id: $pet_id");
                        } else {
                            // Insert new pet
                            $stmt = $pdo->prepare("INSERT INTO Pet (pet_name, pet_sex, pet_weight, pet_breed, pet_birth_date, pet_species, client_id) VALUES (?, ?, ?, ?, ?, ?, ?)");
                            $stmt->execute([$pet_name, $pet_sex, $pet_weight, $pet_breed, $pet_birth_date, $pet_species, $client_id]);
                            $pet_id = $pdo->lastInsertId();
                            error_log("Inserted new pet with pet_id: $pet_id");
                            $changeMade = true;
                        }
                        // Only append to description if a pet was actually added/updated
                        $description .= " and pet '$pet_name'";
                    } else {
                        $pdo->rollBack();
                        $error = "All pet fields are required if any pet field is provided.";
                        header('Location: clients.php?error=' . urlencode($error));
                        exit;
                    }
                }

                // Check if medical fields are provided
                $medical_fields_provided = !empty($medical_condition) || !empty($medical_diagnosis) || !empty($medical_symptoms) || !empty($medical_treatment);
                if ($pet_id && $medical_fields_provided) {
                    $all_medical_fields_filled = !empty($medical_condition) && !empty($medical_diagnosis) && !empty($medical_symptoms) && !empty($medical_treatment);
                    if (!$all_medical_fields_filled) {
                        $pdo->rollBack();
                        $error = "All medical record fields are required if any medical field is provided.";
                        header('Location: clients.php?error=' . urlencode($error));
                        exit;
                    }
                    if ($record_id) {
                        // Update existing medical record
                        $stmt = $pdo->prepare("UPDATE Medical_Records SET date=CURDATE(), medical_condition=?, medical_diagnosis=?, medical_symptoms=?, medical_treatment=?, status=1, updated_at=NOW() WHERE record_id=? AND pet_id=?");
                        $stmt->execute([$medical_condition, $medical_diagnosis, $medical_symptoms, $medical_treatment, $record_id, $pet_id]);
                        $changeMade = true;
                        error_log("Updated medical record with record_id: $record_id");
                    } else {
                        // Insert new medical record
                        $stmt = $pdo->prepare("INSERT INTO Medical_Records (pet_id, date, medical_condition, medical_diagnosis, medical_symptoms, medical_treatment, status, record_date) VALUES (?, CURDATE(), ?, ?, ?, ?, ?, CURDATE())");
                        $stmt->execute([$pet_id, $medical_condition, $medical_diagnosis, $medical_symptoms, $medical_treatment, 1]);
                        $changeMade = true;
                        error_log("Inserted new medical record for pet_id: $pet_id");
                    }
                    $description .= " and updated/added a medical record";
                } elseif ($medical_fields_provided && !$pet_id) {
                    $pdo->rollBack();
                    $error = "Cannot add or update medical record without a valid pet";
                    header('Location: clients.php?error=' . urlencode($error));
                    exit;
                }

                if (!isset($error) && $changeMade) {
                    // Log action
                    // Only log if an actual change was made beyond just client details
                    // (client details update is implicitly logged by the client update query)
                    logAction($pdo, $_SESSION['vet_id'], 'update', $description, 'Admin');
                    $pdo->commit();
                    header('Location: clients.php?message=Client' . ($pet_fields_provided ? " and pet" : "") . ($medical_fields_provided ? " and medical record" : "") . ' updated successfully');
                    exit;
                } else {
                    $pdo->rollBack();
                    header('Location: clients.php?error=' . urlencode($error));
                    exit;
                }
            }
        } catch (PDOException $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            $error = "Database error: " . $e->getMessage();
            error_log("Database error: " . $e->getMessage());
            header('Location: clients.php?error=' . urlencode($error));
            exit;
        }
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

        // Fetch clinic details
        $stmt = $pdo->query("SELECT name, address, phone FROM Clinic_Details WHERE id = 1");
        $clinic = $stmt->fetch(PDO::FETCH_ASSOC);

        echo json_encode([
            'record' => $record,
            'pet' => $pet,
            'client' => $client,
            'vet_name' => $vet_name,
            'clinic' => $clinic ?: ['name' => 'PetTrack Clinic', 'address' => '123 Clinic Street, Animal City', 'phone' => '(123) 456-7890']
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

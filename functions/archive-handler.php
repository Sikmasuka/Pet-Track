<?php
require_once __DIR__ . "/../db.php";
require_once __DIR__ . "/logs.php"; // Include logs.php for logging functionality

// Remove session check since it's handled in the main file

function archiveRecord($pdo, $table, $id, $idColumn)
{
    try {
        // Update status to 0 (archived)
        $pdo->prepare("UPDATE $table SET status = 0, updated_at = NOW() WHERE $idColumn = ?")->execute([$id]);
        // Archive pets and medical records if client
        if ($table === 'client') {
            $pdo->prepare("UPDATE pet SET status = 0, updated_at = NOW() WHERE client_id = ?")->execute([$id]);
            $pdo->prepare("UPDATE Medical_Records SET status = 0, updated_at = NOW() WHERE pet_id IN (SELECT pet_id FROM Pet WHERE client_id = ?)")
                ->execute([$id]);
        }
        return true;
    } catch (Exception $e) {
        throw new Exception("Archive failed: " . $e->getMessage());
    }
}

function restoreRecord($pdo, $id, $table)
{
    try {
        if ($table === 'client') {
            // Fetch client name for logging
            $stmt = $pdo->prepare("SELECT client_name FROM client WHERE client_id = ?");
            $stmt->execute([$id]);
            $client = $stmt->fetch(PDO::FETCH_ASSOC);
            $client_name = $client ? htmlspecialchars($client['client_name']) : 'Unknown';

            // Fetch username for logging
            $username = 'Unknown';
            if (isset($_SESSION['vet_id'])) {
                $stmt = $pdo->prepare("SELECT vet_username FROM veterinarian WHERE vet_id = ?");
                $stmt->execute([$_SESSION['vet_id']]);
                $vet = $stmt->fetch(PDO::FETCH_ASSOC);
                $username = $vet ? htmlspecialchars($vet['vet_username']) : 'Unknown';
            }


            // Begin transaction to ensure atomicity
            $pdo->beginTransaction();

            // Restore client
            $pdo->prepare("UPDATE client SET status = 1, updated_at = NOW() WHERE client_id = ?")->execute([$id]);

            // Restore associated pets
            $pdo->prepare("UPDATE pet SET status = 1, updated_at = NOW() WHERE client_id = ?")->execute([$id]);

            // Restore associated medical records
            $pdo->prepare("UPDATE Medical_Records SET status = 1, updated_at = NOW() WHERE pet_id IN (SELECT pet_id FROM Pet WHERE client_id = ?)")
                ->execute([$id]);

            // Log the restore action
            $description = "$username restored client '$client_name' and associated pets and medical records";
            logAction($pdo, $_SESSION['vet_id'], 'restore', $description, 'Admin');

            $pdo->commit();
        } elseif ($table === 'medical_records') {
            // Fetch record and pet details for logging
            $stmt = $pdo->prepare("SELECT mr.record_id, p.pet_name 
                                  FROM Medical_Records mr 
                                  JOIN Pet p ON mr.pet_id = p.pet_id 
                                  WHERE mr.record_id = ?");
            $stmt->execute([$id]);
            $record = $stmt->fetch(PDO::FETCH_ASSOC);
            $pet_name = $record ? htmlspecialchars($record['pet_name']) : 'Unknown';

            // Fetch username for logging
            $username = 'Unknown';
            if (isset($_SESSION['vet_id'])) {
                $stmt = $pdo->prepare("SELECT vet_username FROM veterinarian WHERE vet_id = ?");
                $stmt->execute([$_SESSION['vet_id']]);
                $vet = $stmt->fetch(PDO::FETCH_ASSOC);
                $username = $vet ? htmlspecialchars($vet['vet_username']) : 'Unknown';
            }


            // Restore medical record
            $pdo->prepare("UPDATE Medical_Records SET status = 1, updated_at = NOW() WHERE record_id = ?")->execute([$id]);

            // Log the restore action
            $description = "$username restored medical record ID $id for pet '$pet_name'";
            logAction($pdo, $_SESSION['vet_id'], 'restore', $description, 'Admin');
        } else {
            throw new Exception("Invalid table: $table");
        }
        return true;
    } catch (Exception $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        throw new Exception("Restore failed: " . $e->getMessage());
    }
}

function deleteFromArchive($pdo, $id, $table)
{
    try {
        if ($table === 'client') {

            // Fetch client name for logging
            $stmt = $pdo->prepare("SELECT client_name FROM client WHERE client_id = ?");
            $stmt->execute([$id]);
            $client = $stmt->fetch(PDO::FETCH_ASSOC);
            $client_name = $client ? htmlspecialchars($client['client_name']) : 'Unknown';

            // Fetch username for logging
            $username = 'Unknown';
            if (isset($_SESSION['vet_id'])) {
                $stmt = $pdo->prepare("SELECT vet_username FROM veterinarian WHERE vet_id = ?");
                $stmt->execute([$_SESSION['vet_id']]);
                $vet = $stmt->fetch(PDO::FETCH_ASSOC);
                $username = $vet ? htmlspecialchars($vet['vet_username']) : 'Unknown';
            }

            // Begin transaction
            $pdo->beginTransaction();

            // Delete medical records associated with client's pets
            $pdo->prepare("DELETE FROM Medical_Records WHERE pet_id IN (SELECT pet_id FROM Pet WHERE client_id = ?)")
                ->execute([$id]);

            // Delete client's pets
            $pdo->prepare("DELETE FROM Pet WHERE client_id = ?")->execute([$id]);

            // Anonymize the client record by nullifying personal data but preserving the record.
            // Set status to 2 (or another value for 'anonymized') to hide it from all lists.
            $stmt = $pdo->prepare(
                "UPDATE client 
                 SET client_name = 'Deleted Client', 
                     client_address = 'Deleted', 
                     client_contact_number = '00000000000',
                     status = 2
                 WHERE client_id = ?"
            );
            $stmt->execute([$id]);

            // Log the delete action
            $description = "$username permanently deleted data for client '$client_name' (ID: $id) and associated pets/records. Account preserved.";
            logAction($pdo, $_SESSION['vet_id'], 'delete', $description, 'Admin');

            $pdo->commit();
        } elseif ($table === 'medical_records') {
            // Fetch record and pet details for logging
            $stmt = $pdo->prepare("SELECT mr.record_id, p.pet_name 
                                  FROM Medical_Records mr 
                                  JOIN Pet p ON mr.pet_id = p.pet_id 
                                  WHERE mr.record_id = ?");
            $stmt->execute([$id]);
            $record = $stmt->fetch(PDO::FETCH_ASSOC);
            $pet_name = $record ? htmlspecialchars($record['pet_name']) : 'Unknown';

            // Fetch vet username for logging
            $stmt = $pdo->prepare("SELECT vet_username FROM veterinarian WHERE vet_id = ?");
            $stmt->execute([$_SESSION['vet_id']]);
            $vet = $stmt->fetch(PDO::FETCH_ASSOC);
            $username = $vet ? htmlspecialchars($vet['vet_username']) : 'Unknown';

            // Delete medical record
            $pdo->prepare("DELETE FROM Medical_Records WHERE record_id = ?")->execute([$id]);

            // Log the delete action
            $description = "$username permanently deleted medical record ID $id for pet '$pet_name'";
            logAction($pdo, $_SESSION['vet_id'], 'delete', $description, 'Admin');
        } else {
            throw new Exception("Invalid table: $table");
        }
        return true;
    } catch (Exception $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        throw new Exception("Delete failed: " . $e->getMessage());
    }
}

function getVetName($pdo, $vet_id)
{
    try {
        $stmt = $pdo->prepare("SELECT vet_name FROM veterinarian WHERE vet_id = ?");
        $stmt->execute([$vet_id]);
        $user = $stmt->fetch();
        return $user ? htmlspecialchars($user['vet_name']) : "Veterinarian not found";
    } catch (Exception $e) {
        return "User";
    }
}


$showRestoreAlert = false;
$showDeleteAlert = false;
$alertTable = '';
$clients = []; // Initialize $clients to avoid undefined variable error
$medical_records = []; // Initialize $medical_records for consistency

try {
    // Handle actions FIRST - before fetching data
    if (isset($_GET['action'], $_GET['id'], $_GET['table'])) {
        $id = (int)$_GET['id'];
        $table = $_GET['table'];

        if ($_GET['action'] == 'restore') {
            if (restoreRecord($pdo, $id, $table)) {
                // Redirect with success parameter
                header("Location: archive.php?success=restore&table=" . urlencode($table));
                exit;
            }
        } elseif ($_GET['action'] == 'delete') {
            if (deleteFromArchive($pdo, $id, $table)) {
                // Redirect with success parameter
                header("Location: archive.php?success=delete&table=" . urlencode($table));
                exit;
            }
        }
    }

    // Check for success parameters from redirect
    if (isset($_GET['success']) && isset($_GET['table'])) {
        if ($_GET['success'] === 'restore') {
            $showRestoreAlert = true;
            $alertTable = $_GET['table'];
        } elseif ($_GET['success'] === 'delete') {
            $showDeleteAlert = true;
            $alertTable = $_GET['table'];
        }
    }

    // NOW fetch the data after handling any actions
    // Fetch archived clients and pets (status = 0)
    $stmt = $pdo->query("
        SELECT c.client_id, c.client_name, c.client_address, c.client_contact_number, c.updated_at, 
               p.pet_id, p.pet_name, p.pet_species, p.pet_weight, p.pet_breed
        FROM client c
        LEFT JOIN pet p ON p.client_id = c.client_id
        WHERE c.status = 0
    ");
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $clients[$row['client_id']]['client_name'] = $row['client_name'];
        $clients[$row['client_id']]['client_address'] = $row['client_address'];
        $clients[$row['client_id']]['client_contact_number'] = $row['client_contact_number'];
        $clients[$row['client_id']]['updated_at'] = $row['updated_at'];
        if ($row['pet_id']) {
            $clients[$row['client_id']]['pets'][] = [
                'pet_name' => $row['pet_name'],
                'pet_species' => $row['pet_species'],
                'pet_weight' => $row['pet_weight'],
                'pet_breed' => $row['pet_breed']
            ];
        }
    }

    // Fetch archived medical records
    $stmt = $pdo->query("
        SELECT record_id, pet_id, medical_diagnosis, medical_condition, medical_treatment, date, updated_at as deleted_at
        FROM medical_records
        WHERE status = 0
    ");
    $medical_records = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    $error = $e->getMessage();
}

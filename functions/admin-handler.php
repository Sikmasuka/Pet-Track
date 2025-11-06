<?php

// Start session and include database connection
require_once '../db.php';


// Check if user is logged in by verifying session role
if (!isset($_SESSION['admin_id'])) {
    header('Location: index.php');
    exit;
}


/**
 * Fetch all veterinarians from the database
 */
$stmt = $pdo->query("SELECT vet_id, vet_name, vet_contact_number, vet_username FROM Veterinarian");
$vets = $stmt->fetchAll();

/**
 * Determine if the page is in edit mode by checking for 'edit' parameter
 */
$edit_mode = false;
$edit_vet = null;
if (isset($_GET['edit'])) {
    $stmt = $pdo->prepare("SELECT * FROM Veterinarian WHERE vet_id = ?");
    $stmt->execute([$_GET['edit']]);
    $edit_vet = $stmt->fetch();
    if ($edit_vet) {
        $edit_mode = true;
    }
}

/**
 * Handle adding a new veterinarian
 */
if (isset($_POST['add_vet'])) {
    $stmt = $pdo->prepare("INSERT INTO Veterinarian (vet_name, vet_contact_number, vet_username, vet_password) VALUES (?, ?, ?, ?)");
    $stmt->execute([
        $_POST['vet_name'],
        $_POST['vet_contact_number'],
        $_POST['vet_username'],
        password_hash($_POST['vet_password'], PASSWORD_BCRYPT)
    ]);
    header("Location: admin.php");
    exit;
}

/**
 * Handle updating an existing veterinarian
 */
if (isset($_POST['update_vet'])) {
    // This logic will need to be updated to handle changes in the Users table as well.
    // For now, focusing on the core separation.
    if (!empty($_POST['vet_password'])) {
        $stmt = $pdo->prepare("UPDATE Veterinarian SET vet_name=?, vet_contact_number=?, vet_username=?, vet_password=? WHERE vet_id=?");
        $stmt->execute([
            $_POST['vet_name'],
            $_POST['vet_contact_number'],
            $_POST['vet_username'],
            password_hash($_POST['vet_password'], PASSWORD_BCRYPT),
            $_POST['vet_id']
        ]);
    } else {
        $stmt = $pdo->prepare("UPDATE Veterinarian SET vet_name=?, vet_contact_number=?, vet_username=? WHERE vet_id=?");
        $stmt->execute([$_POST['vet_name'], $_POST['vet_contact_number'], $_POST['vet_username'], $_POST['vet_id']]);
    }
    header("Location: admin.php");
    exit;
}

/**
 * Handle deleting a veterinarian
 */
if (isset($_GET['delete'])) {
    $stmt = $pdo->prepare("DELETE FROM Veterinarian WHERE vet_id = ?");
    $stmt->execute([$_GET['delete']]);
    header("Location: admin.php");
    exit;
}

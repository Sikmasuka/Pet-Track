<?php
require_once __DIR__ . "/../db.php";

function logAction($pdo, $userId, $actionType, $description, $userRole)
{
    $stmt = $pdo->prepare("INSERT INTO Logs (User_ID, Action_Type, Description, Table_Affected) VALUES (?, ?, ?, ?)");
    $stmt->execute([$userId, $actionType, $description, $userRole]);
}

function logAppointment($pdo, $owner_name, $appointment_date, $appointment_time)
{
    // Format the description string for the log entry
    $description = htmlspecialchars($owner_name) . " booked an appointment on " . $appointment_date . " at " . date("g:i A", strtotime($appointment_time));

    // Call the generic logAction function with details for a guest appointment
    // User_ID is 0 because it's a guest, not a logged-in user.
    // Table_Affected is 'Guest' to identify the type of log.
    logAction($pdo, 0, 'Booking', $description, 'Guest');
}

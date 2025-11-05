<?php
require_once 'functions/db.php';

try {
    // Check pets for client 58
    $stmt = $pdo->prepare('SELECT * FROM Pet WHERE client_id = 58 AND status = 1');
    $stmt->execute();
    $pets = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo 'Pets for client 58: ' . count($pets) . PHP_EOL;
    foreach ($pets as $pet) {
        echo 'Pet ID: ' . $pet['pet_id'] . ', Name: ' . $pet['pet_name'] . PHP_EOL;

        // Check medical records for each pet
        $stmt2 = $pdo->prepare('SELECT * FROM Medical_Records WHERE pet_id = ? AND status = 1');
        $stmt2->execute([$pet['pet_id']]);
        $records = $stmt2->fetchAll(PDO::FETCH_ASSOC);
        echo '  Medical records: ' . count($records) . PHP_EOL;
        foreach ($records as $record) {
            echo '    Record ID: ' . $record['record_id'] . ', Date: ' . $record['record_date'] . ', Condition: ' . $record['medical_condition'] . PHP_EOL;
        }
    }
} catch (Exception $e) {
    echo 'Error: ' . $e->getMessage() . PHP_EOL;
}

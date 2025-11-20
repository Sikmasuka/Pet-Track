<?php
ob_start();
session_start();
require_once 'db.php';
require_once 'functions/logs.php';

// Check if user is logged in
if (!isset($_SESSION['vet_id'])) {
    header('Location: index.php');
    exit;
}

// Define $vetName
$vetName = htmlspecialchars($currentVet['vet_name'] ?? 'Unknown');

// Fetch veterinarian data for modal (if not already set)
if (!isset($currentVet)) {
    $stmt = $pdo->prepare("SELECT * FROM veterinarian WHERE vet_id = ?");
    $stmt->execute([$_SESSION['vet_id']]);
    $currentVet = $stmt->fetch(PDO::FETCH_ASSOC);
}

// Fetch vet data for modal
$stmt = $pdo->prepare("SELECT * FROM veterinarian WHERE vet_id = ?");
$stmt->execute([$_SESSION['vet_id']]);
$vet = $stmt->fetch(PDO::FETCH_ASSOC);

// Fetch vet name and vet_username for logging
$stmt = $pdo->prepare("SELECT vet_name, vet_username FROM Veterinarian WHERE vet_id = ?");
$stmt->execute([$_SESSION['vet_id']]);
$user = $stmt->fetch();
$vetName = $user ? htmlspecialchars($user['vet_name']) : "Veterinarian not found";
$username = $user ? htmlspecialchars($user['vet_username']) : "Unknown";

// Fetch clinic details
try {
    $stmt = $pdo->query("SELECT name, address, phone FROM Clinic_Details WHERE id = 1");
    $clinic = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$clinic) {
        $clinic = ['name' => 'PetTrack Clinic', 'address' => '123 Clinic Street, Animal City', 'phone' => '(123) 456-7890'];
    }
} catch (PDOException $e) {
    $clinic = ['name' => 'PetTrack Clinic', 'address' => '123 Clinic Street, Animal City', 'phone' => '(123) 456-7890'];
}

// Handle POST requests for adding/updating medical records
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Validate required fields
        $pet_id = $_POST['pet_id'] ?? '';
        $medical_condition = trim($_POST['medical_condition'] ?? '');
        $medical_diagnosis = trim($_POST['medical_diagnosis'] ?? '');
        $medical_symptoms = trim($_POST['medical_symptoms'] ?? '');
        $medical_treatment = trim($_POST['medical_treatment'] ?? '');

        if (empty($pet_id) || empty($medical_condition) || empty($medical_diagnosis) || empty($medical_symptoms) || empty($medical_treatment)) {
            throw new Exception("All medical record fields are required.");
        }

        // Validate pet_id exists and is active
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM Pet WHERE pet_id = ? AND status = 1");
        $stmt->execute([$pet_id]);
        if ($stmt->fetchColumn() == 0) {
            throw new Exception("Invalid or inactive pet selected.");
        }

        if (isset($_POST['add_record'])) {
            $stmt = $pdo->prepare("INSERT INTO Medical_Records (pet_id, date, medical_condition, medical_diagnosis, medical_symptoms, medical_treatment, status, record_date) VALUES (?, CURDATE(), ?, ?, ?, ?, 1, CURDATE())");
            $stmt->execute([$pet_id, $medical_condition, $medical_diagnosis, $medical_symptoms, $medical_treatment]);
            $record_id = $pdo->lastInsertId();
            $stmt = $pdo->prepare("SELECT pet_name FROM Pet WHERE pet_id = ?");
            $stmt->execute([$pet_id]);
            $pet = $stmt->fetch();
            $pet_name = $pet ? htmlspecialchars($pet['pet_name']) : 'Unknown';
            $description = "$username added a medical record for pet ID $pet_id ('$pet_name')";
            logAction($pdo, $_SESSION['vet_id'], 'add', $description, 'Admin');
            header('Location: medical_records.php?message=Medical record added successfully');
            exit;
        } elseif (isset($_POST['update_record'])) {
            $record_id = $_POST['record_id'] ?? '';
            if (empty($record_id)) {
                throw new Exception("Record ID is required for updating.");
            }
            $stmt = $pdo->prepare("UPDATE Medical_Records SET pet_id = ?, date = CURDATE(), medical_condition = ?, medical_diagnosis = ?, medical_symptoms = ?, medical_treatment = ?, status = 1, updated_at = NOW() WHERE record_id = ?");
            $stmt->execute([$pet_id, $medical_condition, $medical_diagnosis, $medical_symptoms, $medical_treatment, $record_id]);
            $stmt = $pdo->prepare("SELECT pet_name FROM Pet WHERE pet_id = ?");
            $stmt->execute([$pet_id]);
            $pet = $stmt->fetch();
            $pet_name = $pet ? htmlspecialchars($pet['pet_name']) : 'Unknown';
            $description = "$username updated medical record ID $record_id for pet ID $pet_id ('$pet_name')";
            logAction($pdo, $_SESSION['vet_id'], 'update', $description, 'Admin');
            header('Location: medical_records.php?message=Medical record updated successfully');
            exit;
        }
    } catch (PDOException $e) {
        echo "Database Error: Cannot process medical record. " . $e->getMessage();
        exit;
    } catch (Exception $e) {
        echo "Error: " . $e->getMessage();
        exit;
    }
}

// Handle archiving of medical records
if (isset($_GET['archive_record_id']) && is_numeric($_GET['archive_record_id'])) {
    try {
        $record_id = (int)$_GET['archive_record_id'];
        $stmt = $pdo->prepare("SELECT Pet.pet_name FROM Medical_Records JOIN Pet ON Medical_Records.pet_id = Pet.pet_id WHERE Medical_Records.record_id = ?");
        $stmt->execute([$record_id]);
        $pet = $stmt->fetch(PDO::FETCH_ASSOC);
        $pet_name = $pet ? htmlspecialchars($pet['pet_name']) : 'Unknown';
        $stmt = $pdo->prepare("SELECT vet_username FROM Veterinarian WHERE vet_id = ?");
        $stmt->execute([$_SESSION['vet_id']]);
        $vet = $stmt->fetch(PDO::FETCH_ASSOC);
        $username = $vet ? htmlspecialchars($vet['vet_username']) : 'Unknown';
        $stmt = $pdo->prepare("UPDATE Medical_Records SET status = 0, updated_at = NOW() WHERE record_id = ?");
        $stmt->execute([$record_id]);
        $description = "$username archived medical record ID $record_id for pet '$pet_name'";
        logAction($pdo, $_SESSION['vet_id'], 'archive', $description, 'Admin');
        header('Location: medical_records.php?message=Medical record archived successfully');
        exit;
    } catch (PDOException $e) {
        echo "Database Error: Cannot archive medical record. " . $e->getMessage();
        exit;
    }
}

// Fetch medical record for editing
$recordToEdit = null;
if (isset($_GET['edit_record_id']) && is_numeric($_GET['edit_record_id'])) {
    $stmt = $pdo->prepare("SELECT * FROM Medical_Records WHERE record_id = ? AND status = 1");
    $stmt->execute([$_GET['edit_record_id']]);
    $recordToEdit = $stmt->fetch(PDO::FETCH_ASSOC);
}

// Handle sort parameter
$sort = isset($_GET['sort']) ? strtolower(trim($_GET['sort'])) : 'date_desc';

$orderBy = 'Medical_Records.date DESC';
if ($sort === 'name_asc') {
    $orderBy = 'Pet.pet_name ASC';
} elseif ($sort === 'name_desc') {
    $orderBy = 'Pet.pet_name DESC';
}

// Fetch active medical records with pet names
$query = "SELECT Medical_Records.record_id, Pet.pet_name, Medical_Records.date, Medical_Records.medical_condition, Medical_Records.medical_diagnosis, Medical_Records.medical_symptoms, Medical_Records.medical_treatment 
          FROM Medical_Records 
          JOIN Pet ON Medical_Records.pet_id = Pet.pet_id 
          WHERE Medical_Records.status = 1 
          ORDER BY $orderBy";
$stmt = $pdo->prepare($query);
$stmt->execute();
$records = $stmt->fetchAll(PDO::FETCH_ASSOC);

ob_end_flush();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Medical Records - Balingasag Dog and Cat Clinic</title>
    <script src="Assets/Extension.js"></script>
    <link rel="stylesheet" href="Assets/FontAwsome/css/all.min.css">
    <link rel="icon" href="image/logo.png" type="image/x-icon">
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        .custom-scrollbar::-webkit-scrollbar {
            width: 8px;
        }

        .custom-scrollbar::-webkit-scrollbar-track {
            background: #edf2f7;
            border-radius: 4px;
        }

        .custom-scrollbar::-webkit-scrollbar-thumb {
            background: #cbd5e0;
            border-radius: 4px;
        }

        .custom-scrollbar::-webkit-scrollbar-thumb:hover {
            background: #a0aec0;
        }

        .table-container {
            overflow-x: auto;
        }

        .mobile-menu-hidden {
            transform: translateX(-100%);
        }

        .mobile-menu-visible {
            transform: translateX(0);
        }

        ::-webkit-scrollbar {
            width: 8px;
            height: 8px;
        }

        ::-webkit-scrollbar-track {
            background: #1e293b;
        }

        ::-webkit-scrollbar-thumb {
            background: #334155;
            border-radius: 4px;
        }

        ::-webkit-scrollbar-thumb:hover {
            background: #475569;
        }
    </style>
</head>

<body class="bg-slate-100 min-h-screen text-gray-800">
    <?php include('./includes/edit-profile.php') ?>

    <!-- Mobile Menu Button -->
    <button id="sidebar-toggle" class="lg:hidden fixed top-4 left-4 z-50 bg-teal-700 text-white p-3 rounded-md shadow-lg hover:bg-teal-600 transition-colors">
        <i class="fas fa-bars"></i>
    </button>

    <!-- Sidebar -->
    <aside id="sidebar" class="fixed inset-y-0 left-0 w-[200px] bg-gradient-to-b from-emerald-600 via-teal-700 to-emerald-800 text-white p-5 transform -translate-x-full lg:translate-x-0 transition-transform duration-300 ease-in-out z-40 flex flex-col border-r border-teal-800">
        <div class="flex flex-col items-center">
            <img src="image/logoWhite.png" alt="Balingasag Dog and Cat Clinic Logo" class="h-16 w-auto object-contain drop-shadow-lg">
            <div class="text-center leading-tight">
                <h2 class="text-xl font-extrabold tracking-wide text-white">
                    Balingasag
                </h2>
                <p class="text-base font-medium text-gray-200">
                    Dog & Cat Clinic
                </p>
            </div>
        </div>

        <nav class="flex-grow mt-8 lg:mt-12 space-y-0.5">
            <a href="dashboard.php" class="block text-sm text-white px-4 py-2 rounded-md hover:bg-teal-900 transition-colors">
                <i class="fas fa-tachometer-alt mr-2"></i> Dashboard
            </a>
            <a href="clients.php" class="block text-sm text-white hover:bg-teal-800 px-4 py-2 rounded-md transition-colors">
                <i class="fas fa-user mr-2"></i> Clients
            </a>
            <a href="pets.php" class="block text-sm text-white hover:bg-teal-800 px-4 py-2 rounded-md transition-colors">
                <i class="fas fa-paw mr-2"></i> Pets
            </a>
            <a href="medical_records.php" class="block text-sm text-white hover:bg-teal-800 bg-teal-800 px-4 py-2 rounded-md transition-colors">
                <i class="fas fa-file-medical mr-2"></i> Medical Records
            </a>
            <a href="payment_methods.php" class="block text-sm text-white hover:bg-teal-800 px-4 py-2 rounded-md transition-colors">
                <i class="fas fa-credit-card mr-2"></i> Payments
            </a>
            <a href="appointments.php" class="block text-sm text-white hover:bg-teal-800 px-4 py-2 rounded-md transition-colors">
                <i class="fas fa-calendar-days mr-2"></i> Appointments
            </a>
            <a href="archive.php" class="block text-sm text-white hover:bg-teal-800 px-4 py-2 rounded-md transition-colors">
                <i class="fa-solid fa-box-archive mr-2"></i> Archive
            </a>
            <a href="./includes/sitemap/vet-help.php" class="block text-sm text-white hover:bg-teal-800 px-4 py-2 rounded-md transition-colors">
                <i class="fas fa-question-circle mr-2"></i> Help/Support
            </a>
        </nav>
        <div class="pt-4">
            <a href="#" onclick="confirmLogout(event)" class="block text-md text-white hover:bg-red-600 px-4 py-2 rounded-md transition-colors">
                <i class="fas fa-sign-out-alt mr-2"></i> Logout
            </a>
        </div>
    </aside>

    <div id="overlay" class="lg:hidden fixed inset-0 bg-black bg-opacity-50 z-30 hidden"></div>

    <!-- Main Content -->
    <div class="relative ml-0 lg:ml-52 p-4 pt-16 lg:pt-4 min-h-screen">

        <div id="loadingScreen" class="absolute inset-0 flex flex-col items-center justify-center bg-white bg-opacity-75 z-50 hidden">
            <img src="image/MainIcon.png" alt="Loading Icon" class="w-20 h-20 animate-pulse">
            <p class="mt-4 text-teal-700 font-semibold text-lg">Loading...</p>
        </div>

        <header class="bg-white shadow-lg rounded-lg text-gray-800 py-4 mb-6 lg:mb-8 p-4 lg:p-6 border border-slate-200">
            <div class="flex justify-between items-center">
                <h1 class="text-xl lg:text-2xl font-bold">Medical Records</h1>

                <!-- Right Side (Notifications + Profile) -->
                <div class="flex items-center gap-2">
                    <!-- Notification Bell -->
                    <div class="relative inline-block text-left">
                        <button id="notificationButton" class="flex items-center justify-center w-10 h-10 bg-gray-100 border border-gray-200 rounded-full hover:bg-gray-200 text-gray-800 text-lg transition-colors relative">
                            <i class="fas fa-bell"></i>
                            <span id="notificationCount" class="absolute top-0 right-0 bg-red-500 text-white text-xs rounded-full px-1.5 py-0.5 hidden">0</span>
                        </button>
                        <div id="notificationDropdown" class="origin-top-right absolute right-0 mt-2 w-80 rounded-lg shadow-lg bg-white ring-1 ring-black ring-opacity-5 opacity-0 scale-95 pointer-events-none transition-all duration-200 ease-out z-50 border border-slate-200">
                            <div class="px-4 py-3 border-b border-slate-200">
                                <p class="text-sm font-semibold text-gray-800">Notifications</p>
                            </div>
                            <div id="notificationList" class="py-1 max-h-96 overflow-y-auto">
                                <!-- Notifications will be appended here -->
                            </div>
                            <div class="py-2 border-t border-slate-200">
                                <a href="#" onclick="markAllAsRead(event)" class="block text-center text-sm text-indigo-500 hover:text-indigo-600">Mark all as read</a>
                            </div>
                        </div>
                    </div>

                    <!-- Profile Dropdown -->
                    <div class="relative inline-block text-left">
                        <button id="profileButton" class="flex items-center justify-center w-10 h-10 bg-gray-100 border border-gray-200 rounded-full hover:bg-gray-200 text-gray-800 text-lg transition-colors">
                            <i class="fas fa-user"></i>
                        </button>
                        <div id="dropdownMenu" class="origin-top-right absolute right-0 mt-2 w-72 rounded-lg shadow-lg bg-white ring-1 ring-black ring-opacity-5 opacity-0 scale-95 pointer-events-none transition-all duration-200 ease-out z-50 border border-slate-200">
                            <div class="px-4 py-3 border-b border-slate-200">
                                <div class="flex items-center gap-3">
                                    <div class="flex items-center justify-center w-12 h-12 rounded-full border-2 border-indigo-500 bg-gray-100 text-indigo-400 text-xl">
                                        <i class="fas fa-user"></i>
                                    </div>
                                    <div>
                                        <p class="text-sm font-semibold text-gray-800"><?php echo $vetName; ?></p>
                                        <p class="text-xs text-gray-500">Veterinarian</p>
                                    </div>
                                </div>
                            </div>
                            <div class="py-1">
                                <a href="#" id="editProfileLink" class="flex items-center gap-3 px-4 py-3 text-sm text-gray-600 hover:bg-gray-100 hover:text-gray-800 transition-colors duration-150">
                                    <i class="fas fa-edit text-indigo-400"></i>
                                    <div>
                                        <div class="font-medium">Edit Profile</div>
                                        <div class="text-xs text-gray-500">Update your information</div>
                                    </div>
                                </a>
                                <hr class="my-1 border-slate-200">
                                <a href="#" onclick="confirmLogout(event)" class="flex items-center gap-3 px-4 py-3 text-sm text-red-500 hover:bg-gray-100 transition-colors duration-150">
                                    <i class="fas fa-sign-out-alt text-red-500"></i>
                                    <div>
                                        <div class="font-medium">Logout</div>
                                        <div class="text-xs text-red-600">Sign out of your account</div>
                                    </div>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </header>

        <main class="bg-white p-4 lg:p-6 rounded-lg shadow-lg border border-slate-200">
            <h2 class="text-lg sm:text-xl lg:text-xl font-semibold text-gray-800 mb-4">List of Medical Records</h2>
            <?php if (isset($_GET['message'])): ?>
                <div class="mb-4 p-3 bg-green-100 text-green-700 rounded-md text-sm">
                    <?= htmlspecialchars($_GET['message']) ?>
                </div>
            <?php endif; ?>

            <!-- Search Bar and Sort -->
            <div class="mb-4 flex flex-col lg:flex-row gap-3">
                <div class="flex items-center gap-2 w-full lg:w-auto">
                    <label for="search" class="text-sm font-medium text-gray-700 whitespace-nowrap">Search Medical Records:</label>
                    <input type="text" id="search" class="border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-400 focus:outline-none w-full lg:w-64" placeholder="Search by pet name, condition, diagnosis, symptoms, or treatment...">
                </div>

                <div class="flex items-center gap-2">
                    <label class="text-sm font-medium text-gray-700 whitespace-nowrap">Sort by Name:</label>
                    <button id="sortButton" class="border border-gray-300 rounded-lg px-3 py-1 text-sm focus:ring-2 focus:ring-indigo-400 focus:outline-none hover:bg-gray-50 transition-colors">
                        <i id="sortIcon" class="fas <?= ($sort === 'name_asc') ? 'fa-sort-alpha-up' : 'fa-sort-alpha-down' ?>"></i>
                    </button>
                </div>
            </div>

            <?php if (count($records) > 0): ?>
                <div class="table-container">
                    <div class="max-h-96 overflow-y-auto">
                        <table class="min-w-full divide-y divide-slate-200">
                            <thead class="bg-gray-100 sticky top-0 z-2">
                                <tr class="border-b border-slate-200">
                                    <th class="px-2 py-3 text-left text-xs sm:text-sm font-medium text-gray-600 uppercase tracking-wider min-w-[120px]">Pet Name</th>
                                    <th class="px-2 py-3 text-left text-xs sm:text-sm font-medium text-gray-600 uppercase tracking-wider min-w-[120px]">Date</th>
                                    <th class="px-2 py-3 text-left text-xs sm:text-sm font-medium text-gray-600 uppercase tracking-wider min-w-[120px]">Condition</th>
                                    <th class="px-2 py-3 text-left text-xs sm:text-sm font-medium text-gray-600 uppercase tracking-wider min-w-[120px]">Diagnosis</th>
                                    <th class="px-2 py-3 text-left text-xs sm:text-sm font-medium text-gray-600 uppercase tracking-wider min-w-[120px]">Symptoms</th>
                                    <th class="px-2 py-3 text-left text-xs sm:text-sm font-medium text-gray-600 uppercase tracking-wider min-w-[120px]">Treatment</th>
                                    <th class="px-2 py-3 text-left text-xs sm:text-sm font-medium text-gray-600 uppercase tracking-wider min-w-[80px]">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-slate-200">
                                <?php foreach ($records as $record): ?>
                                    <tr class="hover:bg-gray-50 transition-colors">
                                        <td class="px-4 py-2 text-gray-700"><?= htmlspecialchars($record['pet_name']) ?></td>
                                        <td class="px-4 py-2 text-sm text-gray-600"><?= htmlspecialchars($record['date']) ?></td>
                                        <td class="px-4 py-2 text-sm text-gray-600"><?= htmlspecialchars($record['medical_condition']) ?></td>
                                        <td class="px-4 py-2 text-sm text-gray-600"><?= htmlspecialchars($record['medical_diagnosis']) ?></td>
                                        <td class="px-4 py-2 text-sm text-gray-600"><?= htmlspecialchars($record['medical_symptoms']) ?></td>
                                        <td class="px-4 py-2 text-sm text-gray-600"><?= htmlspecialchars($record['medical_treatment']) ?></td>
                                        <td class="px-4 py-2 text-sm text-gray-600">
                                            <?php
                                            $printData = htmlspecialchars(json_encode([
                                                'record' => $record,
                                                'clinic' => $clinic,
                                                'vet_name' => $vetName
                                            ]), ENT_QUOTES, 'UTF-8');
                                            ?>
                                            <button onclick="printMedicalRecord(this)" data-print='<?= $printData ?>' class="text-indigo-500 hover:text-indigo-700 hover:underline" title="Print Medical Record">
                                                <i class="fas fa-print"></i>
                                            </button>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            <?php else: ?>
                <p class="text-sm sm:text-base text-gray-500 text-center">No medical records added yet.</p>
            <?php endif; ?>
        </main>
    </div>

    <!-- Hidden Iframe for Printing -->
    <iframe id="printFrame" class="hidden"></iframe>



    <script>
        function confirmArchive(recordId) {
            if (typeof Swal === 'undefined') {
                if (confirm('Are you sure you want to archive this medical record?')) {
                    window.location.href = `?archive_record_id=${recordId}`;
                }
                return false;
            }
            Swal.fire({
                title: 'Are you sure?',
                text: 'This will archive the medical record.',
                icon: 'warning',
                background: '#1e293b',
                color: '#e2e8f0',
                showCancelButton: true,
                confirmButtonColor: '#dc2626',
                cancelButtonColor: '#6b7280',
                confirmButtonText: 'Yes, archive it!'
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location.href = `?archive_record_id=${recordId}`;
                }
            });
            return false;
        }

        function showRecordModal(action) {
            const modal = document.getElementById('recordModal');
            const form = document.getElementById('recordForm');
            form.reset();
            form.innerHTML = form.innerHTML.replace(/<input type="hidden" name="(add_record|update_record)" value="1">/, '');
            if (action === 'add') {
                document.getElementById('recordModalTitle').textContent = 'Add New Medical Record';
                form.innerHTML += '<input type="hidden" name="add_record" value="1">';
            } else if (action === 'edit') {
                document.getElementById('recordModalTitle').textContent = 'Edit Medical Record';
                form.innerHTML += '<input type="hidden" name="update_record" value="1">';
            }
            modal.classList.remove('hidden');
        }

        function hideRecordModal() {
            document.getElementById('recordModal').classList.add('hidden');
        }

        <?php if ($recordToEdit): ?>
            document.addEventListener('DOMContentLoaded', function() {
                showRecordModal('edit');
                document.getElementById('record_id').value = '<?= (int)$recordToEdit['record_id'] ?>';
                document.getElementById('petId').value = '<?= (int)$recordToEdit['pet_id'] ?>';
                document.getElementById('medicalCondition').value = <?= json_encode($recordToEdit['medical_condition']) ?>;
                document.getElementById('medicalDiagnosis').value = <?= json_encode($recordToEdit['medical_diagnosis']) ?>;
                document.getElementById('medicalSymptoms').value = <?= json_encode($recordToEdit['medical_symptoms']) ?>;
                document.getElementById('medicalTreatment').value = <?= json_encode($recordToEdit['medical_treatment']) ?>;
            });
        <?php endif; ?>

        <?php if (isset($_GET['message'])): ?>
            document.addEventListener('DOMContentLoaded', function() {
                if (typeof Swal !== 'undefined') {
                    Swal.fire({
                        title: 'Success',
                        text: <?= json_encode($_GET['message']) ?>,
                        icon: 'success',
                        background: '#1e293b',
                        color: '#e2e8f0',
                        confirmButtonColor: '#6366f1',
                        confirmButtonText: 'OK'
                    }).then(() => {
                        const url = new URL(window.location.href);
                        url.searchParams.delete('message');
                        window.history.replaceState({}, document.title, url);
                    });
                } else {
                    alert(<?= json_encode($_GET['message']) ?>);
                    const url = new URL(window.location.href);
                    url.searchParams.delete('message');
                    window.history.replaceState({}, document.title, url);
                }
            });
        <?php endif; ?>

        function toggleModal(modalId) {
            console.log("Toggling modal:", modalId);
            const modal = document.getElementById(modalId);
            if (modal) {
                modal.classList.toggle('hidden');
            } else {
                console.error("Modal not found:", modalId);
            }
        }

        // Client-side filtering for search input
        document.addEventListener('DOMContentLoaded', function() {
            const searchInput = document.getElementById('search');
            if (searchInput) {
                searchInput.addEventListener('input', function() {
                    const searchTerm = this.value.toLowerCase().trim();
                    const rows = document.querySelectorAll('tbody tr');
                    let visibleCount = 0;

                    rows.forEach(row => {
                        const petName = row.cells[0].textContent.toLowerCase();
                        const date = row.cells[1].textContent.toLowerCase();
                        const condition = row.cells[2].textContent.toLowerCase();
                        const diagnosis = row.cells[3].textContent.toLowerCase();
                        const symptoms = row.cells[4].textContent.toLowerCase();
                        const treatment = row.cells[5].textContent.toLowerCase();

                        if (petName.includes(searchTerm) || date.includes(searchTerm) || condition.includes(searchTerm) || diagnosis.includes(searchTerm) || symptoms.includes(searchTerm) || treatment.includes(searchTerm)) {
                            row.style.display = '';
                            visibleCount++;
                        } else {
                            row.style.display = 'none';
                        }
                    });

                    // Optional: Show "No results" message if no rows visible
                    let noResults = document.getElementById('noResults');
                    if (!noResults) {
                        noResults = document.createElement('p');
                        noResults.id = 'noResults';
                        noResults.className = 'text-center text-gray-500 text-sm';
                        noResults.textContent = 'No medical records found matching your search.';
                        noResults.style.display = 'none';
                        // Insert after the table
                        const tableContainer = document.querySelector('.table-container');
                        if (tableContainer) {
                            tableContainer.appendChild(noResults);
                        }
                    }
                    noResults.style.display = (visibleCount === 0 && searchTerm) ? 'block' : 'none';
                });
            }

            // Sort button functionality
            const sortButton = document.getElementById('sortButton');
            const sortIcon = document.getElementById('sortIcon');
            if (sortButton) {
                sortButton.addEventListener('click', function() {
                    const currentSort = '<?= $sort ?>';
                    let newSort = 'name_asc';
                    if (currentSort === 'name_asc') {
                        newSort = 'name_desc';
                    } else if (currentSort === 'name_desc') {
                        newSort = 'date_desc';
                    }
                    // Update URL with new sort parameter
                    const url = new URL(window.location.href);
                    url.searchParams.set('sort', newSort);
                    window.location.href = url.toString();
                });
            }
        });

        function printMedicalRecord(buttonElement) {
            try {
                const printDataString = buttonElement.getAttribute('data-print');
                const {
                    record,
                    clinic,
                    vet_name
                } = JSON.parse(printDataString);
                const formattedDate = new Date(record.date).toLocaleDateString('en-US', {
                    year: 'numeric',
                    month: 'long',
                    day: 'numeric'
                });

                const printHTML = `
                <html>
                <head>
                    <title>Medical Record for ${record.pet_name}</title>
                    <style>
                        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; margin: 25px; color: #333; }
                        .header { text-align: center; border-bottom: 2px solid #4CAF50; padding-bottom: 15px; margin-bottom: 25px; }
                        .clinic-name { font-size: 24px; font-weight: bold; color: #2E8B57; }
                        .clinic-details { font-size: 12px; color: #555; }
                        .section { margin-bottom: 20px; }
                        .section-title { font-size: 16px; font-weight: bold; color: #333; border-bottom: 1px solid #eee; padding-bottom: 5px; margin-bottom: 10px; }
                        .details-table { width: 100%; border-collapse: collapse; }
                        .details-table td { padding: 8px; border-bottom: 1px solid #f0f0f0; }
                        .details-table td:first-child { font-weight: 600; color: #555; width: 120px; }
                        .footer { text-align: center; margin-top: 40px; font-size: 12px; color: #777; }
                        .signature-line { border-top: 1px solid #555; width: 250px; margin: 40px auto 5px auto; }
                    </style>
                </head>
                <body>
                    <div class="header">
                        <div class="clinic-name">${clinic.name || 'PetTrack Clinic'}</div>
                        <div class="clinic-details">${clinic.address || ''} | ${clinic.phone || ''}</div>
                    </div>
                    <h2 style="text-align: center; margin-bottom: 20px;">Official Medical Record</h2>

                    <div class="section">
                        <div class="section-title">Record Details</div>
                        <table class="details-table">
                            <tr><td>Pet Name:</td><td>${record.pet_name || '-'}</td></tr>
                            <tr><td>Date:</td><td>${formattedDate}</td></tr>
                            <tr><td>Condition(s):</td><td>${record.medical_condition || '-'}</td></tr>
                            <tr><td>Symptoms:</td><td>${record.medical_symptoms || '-'}</td></tr>
                            <tr><td>Diagnosis:</td><td>${record.medical_diagnosis || '-'}</td></tr>
                            <tr><td>Treatment:</td><td>${record.medical_treatment || '-'}</td></tr>
                        </table>
                    </div>

                    <div class="footer">
                        <div class="signature-line"></div>
                        <div>${vet_name || 'Veterinarian Signature'}</div>
                        <p>This document is a true and accurate record of the pet's medical history at our clinic.</p>
                    </div>
                </body>
                </html>
            `;

                const frame = document.getElementById('printFrame');
                frame.contentDocument.open();
                frame.contentDocument.write(printHTML);
                frame.contentDocument.close();
                setTimeout(() => {
                    frame.contentWindow.focus();
                    frame.contentWindow.print();
                }, 250);

            } catch (error) {
                console.error('Error preparing print data:', error);
                Swal.fire({
                    icon: 'error',
                    title: 'Printing Error',
                    text: 'Could not prepare the medical record for printing. Please try again.',
                });
            }
        }
    </script>

    <script src="./js/dashboard.js"></script>
    <script src="./js/sidebarHandler.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="./js/confirmLogout.js"></script>
    <script src="./js/edit-profile.js"></script>
    <script src="./js/notification-bell.js"></script>
    <script src="./js/customize-loader.js"></script>
</body>

</html>
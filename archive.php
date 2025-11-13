<?php
require_once __DIR__ . "/functions/archive-handler.php";
require_once __DIR__ . "/functions/dashboard-handler.php";
require_once __DIR__ . "/functions/auth.php";
requireVet();


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
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Archive - Balingasag Dog and Cat Clinic</title>
    <link rel="stylesheet" href="Assets/FontAwsome/css/all.min.css">
    <script src="https://cdn.tailwindcss.com"></script>
</head>

<body class="bg-slate-100 min-h-screen text-gray-800">
    <?php include('./includes/edit-profile.php'); ?>

    <!-- Mobile Menu Button -->
    <button id="mobileMenuBtn" class="lg:hidden fixed top-4 left-4 z-50 bg-teal-700 text-white p-3 rounded-md shadow-lg hover:bg-teal-600 transition-colors">
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
            <a href="medical_records.php" class="block text-sm text-white hover:bg-teal-800 px-4 py-2 rounded-md transition-colors">
                <i class="fas fa-file-medical mr-2"></i> Medical Records
            </a>
            <a href="payment_methods.php" class="block text-sm text-white hover:bg-teal-800 px-4 py-2 rounded-md transition-colors">
                <i class="fas fa-credit-card mr-2"></i> Payments
            </a>
            <a href="appointments.php" class="block text-sm text-white hover:bg-teal-800 px-4 py-2 rounded-md transition-colors">
                <i class="fas fa-calendar-days mr-2"></i> Appointments
            </a>
            <a href="archive.php" class="block text-sm text-white hover:bg-teal-800 bg-teal-800 px-4 py-2 rounded-md transition-colors">
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

    <!-- Overlay for mobile menu -->
    <div id="overlay" class="lg:hidden fixed inset-0 bg-black bg-opacity-50 z-30 hidden"></div>

    <!-- Main Container -->
    <div class="relative ml-0 lg:ml-52 p-4 pt-16 lg:pt-4 min-h-screen">

        <div id="loadingScreen" class="absolute inset-0 flex flex-col items-center justify-center bg-white bg-opacity-75 z-50 hidden">
            <img src="image/MainIcon.png" alt="Loading Icon" class="w-20 h-20 animate-pulse">
            <p class="mt-4 text-teal-700 font-semibold text-lg">Loading...</p>
        </div>

        <header class="bg-white shadow-lg rounded-lg text-gray-800 py-4 mb-6 lg:mb-8 p-4 lg:p-6 border border-slate-200">
            <!-- Top Section with Dropdown -->
            <div class="flex justify-between items-center">
                <!-- Dashboard Title -->
                <h1 class="text-xl lg:text-2xl font-bold">Archives</h1>

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

        <!-- Main Content -->
        <main>
            <?php if (isset($message)): ?>
                <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-4"><?= htmlspecialchars($message) ?></div>
            <?php endif; ?>
            <?php if (isset($error)): ?>
                <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-4"><?= htmlspecialchars($error) ?></div>
            <?php endif; ?>

            <div class="mb-8 bg-white p-4 lg:p-6 rounded-lg shadow-lg border border-slate-200">
                <h2 class="text-lg font-semibold text-gray-800 mb-4">Archived Pets and Clients</h2>
                <?php if (count($clients) > 0): ?>
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-100">
                                <tr class="border-b bg-gray-200">
                                    <th class="px-2 py-3 text-left text-xs font-medium text-gray-600 uppercase">Client</th>
                                    <th class="px-2 py-3 text-left text-xs font-medium text-gray-600 uppercase">Pet Name</th>
                                    <th class="px-2 py-3 text-left text-xs font-medium text-gray-600 uppercase">Species</th>
                                    <th class="px-2 py-3 text-left text-xs font-medium text-gray-600 uppercase">Weight</th>
                                    <th class="px-2 py-3 text-left text-xs font-medium text-gray-600 uppercase">Breed</th>
                                    <th class="px-2 py-3 text-left text-xs font-medium text-gray-600 uppercase">Address</th>
                                    <th class="px-2 py-3 text-left text-xs font-medium text-gray-600 uppercase">Contact</th>
                                    <th class="px-2 py-3 text-left text-xs font-medium text-gray-600 uppercase">Archived At</th>
                                    <th class="px-2 py-3 text-left text-xs font-medium text-gray-600 uppercase">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                <?php foreach ($clients as $client_id => $client): ?>
                                    <?php
                                    $rowspan = !empty($client['pets']) ? count($client['pets']) : 1;
                                    $first = true;
                                    ?>
                                    <?php if (!empty($client['pets'])): ?>
                                        <?php foreach ($client['pets'] as $pet): ?>
                                            <tr class="hover:bg-gray-50">
                                                <?php if ($first): ?>
                                                    <td class="px-4 py-2 text-sm" rowspan="<?= $rowspan ?>"><?= htmlspecialchars($client['client_name'] ?? 'N/A') ?></td>
                                                <?php endif; ?>
                                                <td class="px-4 py-2 text-sm"><?= htmlspecialchars($pet['pet_name'] ?? 'N/A') ?></td>
                                                <td class="px-4 py-2 text-sm"><?= htmlspecialchars($pet['pet_species'] ?? 'N/A') ?></td>
                                                <td class="px-4 py-2 text-sm"><?= htmlspecialchars($pet['pet_weight'] ?? 'N/A') ?></td>
                                                <td class="px-4 py-2 text-sm"><?= htmlspecialchars($pet['pet_breed'] ?? 'N/A') ?></td>
                                                <?php if ($first): ?>
                                                    <td class="px-4 py-2 text-sm" rowspan="<?= $rowspan ?>"><?= htmlspecialchars($client['client_address'] ?? 'N/A') ?></td>
                                                    <td class="px-4 py-2 text-sm" rowspan="<?= $rowspan ?>"><?= htmlspecialchars($client['client_contact_number'] ?? 'N/A') ?></td>
                                                    <td class="px-4 py-2 text-sm" rowspan="<?= $rowspan ?>"><?= htmlspecialchars($client['updated_at'] ?? 'N/A') ?></td>
                                                    <td class="px-4 py-2 text-sm" rowspan="<?= $rowspan ?>">
                                                        <a href="?action=restore&id=<?= $client_id ?>&table=client" class="text-indigo-500 hover:underline" onclick="return confirmRestore(<?= $client_id ?>, 'client')">Restore</a> |
                                                        <a href="?action=delete&id=<?= $client_id ?>&table=client" class="text-red-500 hover:underline" onclick="return confirmDelete(<?= $client_id ?>, 'client')">Delete</a>
                                                    </td>
                                                <?php endif; ?>
                                            </tr>
                                            <?php $first = false; ?>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <tr class="hover:bg-gray-50">
                                            <td class="px-4 py-2 text-sm"><?= htmlspecialchars($client['client_name'] ?? 'N/A') ?></td>
                                            <td class="px-4 py-2 text-sm">N/A</td>
                                            <td class="px-4 py-2 text-sm">N/A</td>
                                            <td class="px-4 py-2 text-sm">N/A</td>
                                            <td class="px-4 py-2 text-sm">N/A</td>
                                            <td class="px-4 py-2 text-sm"><?= htmlspecialchars($client['client_address'] ?? 'N/A') ?></td>
                                            <td class="px-4 py-2 text-sm"><?= htmlspecialchars($client['client_contact_number'] ?? 'N/A') ?></td>
                                            <td class="px-4 py-2 text-sm"><?= htmlspecialchars($client['updated_at'] ?? 'N/A') ?></td>
                                            <td class="px-4 py-2 text-sm">
                                                <a href="?action=restore&id=<?= $client_id ?>&table=client" class="text-indigo-500 hover:underline" onclick="return confirmRestore(<?= $client_id ?>, 'client')">Restore</a> |
                                                <a href="?action=delete&id=<?= $client_id ?>&table=client" class="text-red-500 hover:underline" onclick="return confirmDelete(<?= $client_id ?>, 'client')">Delete</a>
                                            </td>
                                        </tr>
                                    <?php endif; ?>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <p class="text-center text-gray-500 text-sm mb-4">No archived clients found.</p>
                <?php endif; ?>
            </div>

            <div class="mb-8 bg-white p-4 lg:p-6 rounded-lg shadow-lg border border-slate-200">
                <h2 class="text-lg font-semibold text-gray-800 mb-4">Archived Medical Records</h2>
                <?php if (count($medical_records) > 0): ?>
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-100">
                                <tr class="border-b bg-gray-200">
                                    <th class="px-2 py-3 text-left text-xs font-medium text-gray-600 uppercase">Diagnosis</th>
                                    <th class="px-2 py-3 text-left text-xs font-medium text-gray-600 uppercase">Treatment</th>
                                    <th class="px-2 py-3 text-left text-xs font-medium text-gray-600 uppercase">Condition</th>
                                    <th class="px-2 py-3 text-left text-xs font-medium text-gray-600 uppercase">Date</th>
                                    <th class="px-2 py-3 text-left text-xs font-medium text-gray-600 uppercase">Archived At</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                <?php foreach ($medical_records as $record): ?>
                                    <tr class="hover:bg-gray-50">
                                        <td class="px-4 py-2 text-sm"><?= htmlspecialchars($record['medical_diagnosis'] ?? 'N/A') ?></td>
                                        <td class="px-4 py-2 text-sm"><?= htmlspecialchars($record['medical_treatment'] ?? 'N/A') ?></td>
                                        <td class="px-4 py-2 text-sm"><?= htmlspecialchars($record['medical_condition'] ?? 'N/A') ?></td>
                                        <td class="px-4 py-2 text-sm"><?= htmlspecialchars($record['date'] ?? 'N/A') ?></td>
                                        <td class="px-4 py-2 text-sm"><?= htmlspecialchars($record['deleted_at'] ?? 'N/A') ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <p class="text-center text-gray-500 text-sm">No archived medical records</p>
                <?php endif; ?>
            </div>
        </main>
    </div>

    <!-- Sweetalert Confirmation -->
    <script>
        // Check for restore or delete success
        <?php if ($showRestoreAlert): ?>
            Swal.fire({
                title: 'Success!',
                text: '<?php echo htmlspecialchars($alertTable); ?> restored successfully.',
                icon: 'success',
                confirmButtonColor: '#3085d6',
                confirmButtonText: 'OK'
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location.href = 'archive.php';
                }
            });
        <?php endif; ?>

        <?php if ($showDeleteAlert): ?>
            Swal.fire({
                title: 'Success!',
                text: '<?php echo htmlspecialchars($alertTable); ?> deleted permanently.',
                icon: 'success',
                confirmButtonColor: '#3085d6',
                confirmButtonText: 'OK'
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location.href = 'archive.php';
                }
            });
        <?php endif; ?>

        function confirmRestore(id, table) {
            Swal.fire({
                title: 'Are you sure?',
                text: `Restore archived ${table}?`,
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Yes'
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location.href = `?action=restore&id=${id}&table=${table}`;
                }
            });
            return false;
        }

        function confirmDelete(id, table) {
            Swal.fire({
                title: 'Are you sure?',
                text: `Delete archived ${table} permanently?`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Yes'
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location.href = `?action=delete&id=${id}&table=${table}`;
                }
            });
            return false;
        }

        function toggleModal(modalId) {
            console.log("Toggling modal:", modalId); // Debug log
            const modal = document.getElementById(modalId);
            if (modal) {
                modal.classList.toggle('hidden');
            } else {
                console.error("Modal not found:", modalId);
            }
        }
    </script>

    <!-- scrpits -->
    <script src="./js/dashboard.js"></script>
    <script src="./js/sidebarHandler.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="./js/confirmLogout.js"></script>
    <script src="./js/edit-profile.js"></script>
    <script src="./js/notification-bell.js"></script>
    <script src="./js/customize-loader.js"></script>
</body>

</html>
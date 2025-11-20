<?php
// Start session and include database connection
session_start();
require_once __DIR__ . "/db.php";

// Check if user is logged in by verifying vet_id session
if (!isset($_SESSION['vet_id'])) {
    header('Location: index.php');
    exit;
}

// Fetch vet name for greeting
$stmt = $pdo->prepare("SELECT vet_name FROM Veterinarian WHERE vet_id=?");
$stmt->execute([$_SESSION['vet_id']]);
$user = $stmt->fetch();
$vetName = $user ? htmlspecialchars($user['vet_name']) : "Veterinarian not found";

// Fetch vet data for modal
$stmt = $pdo->prepare("SELECT * FROM Veterinarian WHERE vet_id = ?");
$stmt->execute([$_SESSION['vet_id']]);
$vet = $stmt->fetch(PDO::FETCH_ASSOC);

/**
 * Handle adding and updating pets via POST requests
 */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['update_pet'])) {
        // Update existing pet
        $stmt = $pdo->prepare("UPDATE Pet SET pet_name=?, pet_sex=?, pet_weight=?, pet_breed=?, pet_birth_date=?, pet_species=?, client_id=? WHERE pet_id=?");
        $stmt->execute([$_POST['pet_name'], $_POST['pet_sex'], $_POST['pet_weight'], $_POST['pet_breed'], $_POST['pet_birth_date'], $_POST['pet_species'], $_POST['client_id'], $_POST['pet_id']]);
        header('Location: pets.php');
        exit;
    }
}

/**
 * Handle deleting a pet via GET request
 */
if (isset($_GET['delete_pet_id'])) {
    $stmt = $pdo->prepare("DELETE FROM Pet WHERE pet_id=?");
    $stmt->execute([$_GET['delete_pet_id']]);
    header('Location: pets.php');
    exit;
}

/**
 * Fetch pet data for editing if edit_pet_id is set
 */
$petToEdit = null;
$clientName = null;
if (isset($_GET['edit_pet_id'])) {
    $stmt = $pdo->prepare("
        SELECT Pet.*, Client.client_name 
        FROM Pet 
        JOIN Client ON Pet.client_id = Client.client_id 
        WHERE Pet.pet_id = ?
    ");
    $stmt->execute([$_GET['edit_pet_id']]);
    $petToEdit = $stmt->fetch();
    $clientName = $petToEdit ? $petToEdit['client_name'] : null;
}

/**
 * Fetch all pets joined with client names, ordered by pet name
 */
$sort = isset($_GET['sort']) ? strtolower(trim($_GET['sort'])) : 'asc';

// Always fetch ALL active pets (no filtering here - done client-side)
$query = "SELECT Pet.pet_id, Pet.pet_name, Pet.pet_sex, Pet.pet_weight, Pet.pet_breed, Pet.pet_birth_date, Pet.pet_species, Client.client_name
    FROM Pet
    JOIN Client ON Pet.client_id = Client.client_id
    WHERE Client.status = 1 ORDER BY Pet.pet_name " . ($sort === 'desc' ? 'DESC' : 'ASC');

$stmt = $pdo->prepare($query);
$stmt->execute();
$pets = $stmt->fetchAll();
ob_end_flush();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pets - Balingasag Dog and Cat Clinic</title>
    <script src="Assets/Extension.js"></script>
    <link rel="stylesheet" href="Assets/FontAwsome/css/all.min.css">
    <link rel="icon" href="image/logo.png" type="image/x-icon">
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        .mobile-menu-hidden {
            transform: translateX(-100%);
        }

        .mobile-menu-visible {
            transform: translateX(0);
        }

        /* Custom dark theme scrollbar */
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
    <?php include('./includes/edit-profile.php'); ?>

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
            <a href="pets.php" class="block text-sm text-white hover:bg-teal-800 bg-teal-800 px-4 py-2 rounded-md transition-colors">
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

        <header class="bg-white shadow-lg rounded-lg text-gray-800 py-4 mb-6 lg:mb-8 p-4 lg:p-6 border border-slate-200">
            <!-- Top Section with Dropdown -->
            <div class="flex justify-between items-center">
                <!-- Page Title -->
                <div>
                    <h1 class="text-xl lg:text-2xl font-bold">Manage Pets</h1>
                    <p class="text-sm text-gray-600 mt-1">View All of the Pets Records in the System</p>
                </div>

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
            <!-- Pets Section Title -->
            <div class="mb-4">
                <h2 class="text-lg lg:text-xl font-semibold text-gray-800">List of Pets</h2>
            </div>

            <!-- Filters Section Above Table -->
            <div class="mb-4 flex flex-col lg:flex-row gap-3">
                <!-- Search Bar -->
                <div class="flex items-center gap-2 w-full lg:w-auto">
                    <label for="search" class="text-sm font-medium text-gray-700 whitespace-nowrap">Search Pets:</label>
                    <input type="text" name="search" id="search"
                        value="<?= htmlspecialchars($_GET['search'] ?? '') ?>"
                        class="border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-400 focus:outline-none w-full lg:w-64"
                        placeholder="Search by name, breed, or client...">
                </div>

                <!-- Filters -->
                <div class="flex flex-row gap-3 w-full lg:w-auto">
                    <div class="flex items-center gap-2 flex-1">
                        <label for="speciesFilter" class="text-sm font-medium text-gray-700">Species:</label>
                        <select name="species" id="speciesFilter"
                            class="border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-400 focus:outline-none cursor-pointer flex-1">
                            <option value="All">All</option>
                            <option value="Dog">Dog</option>
                            <option value="Cat">Cat</option>
                        </select>
                    </div>

                    <div class="flex items-center gap-2 flex-1">
                        <label for="sexFilter" class="text-sm font-medium text-gray-700">Sex:</label>
                        <select name="sex" id="sexFilter"
                            class="border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-400 focus:outline-none cursor-pointer flex-1">
                            <option value="All">All</option>
                            <option value="Male">Male</option>
                            <option value="Female">Female</option>
                        </select>
                    </div>

                    <div class="flex items-center gap-2">
                        <label class="text-sm font-medium text-gray-700 whitespace-nowrap">Sort by Name:</label>
                        <button id="sortButton" class="border border-gray-300 rounded-lg px-3 py-1 text-sm focus:ring-2 focus:ring-indigo-400 focus:outline-none hover:bg-gray-50 transition-colors">
                            <i id="sortIcon" class="fas <?= ($sort === 'asc') ? 'fa-sort-alpha-up' : 'fa-sort-alpha-down' ?>"></i>
                        </button>
                    </div>
                </div>
            </div>

            <!-- Pets Table -->
            <?php if (count($pets) > 0): ?>
                <div class="overflow-y-auto max-h-96">
                    <table class="min-w-full divide-y divide-slate-200">
                        <thead class="bg-gray-300 sticky top-0 z-2">
                            <tr class="border-b border-slate-200">
                                <th class="px-2 py-3 text-left text-xs sm:text-sm font-medium text-gray-600 uppercase tracking-wider">Pet Name</th>
                                <th class="px-2 py-3 text-left text-xs sm:text-sm font-medium text-gray-600 uppercase tracking-wider">Species</th>
                                <th class="px-2 py-3 text-left text-xs sm:text-sm font-medium text-gray-600 uppercase tracking-wider">Pet Sex</th>
                                <th class="px-2 py-3 text-left text-xs sm:text-sm font-medium text-gray-600 uppercase tracking-wider">Breed</th>
                                <th class="px-2 py-3 text-left text-xs sm:text-sm font-medium text-gray-600 uppercase tracking-wider">Weight</th>
                                <th class="px-2 py-3 text-left text-xs sm:text-sm font-medium text-gray-600 uppercase tracking-wider">Birth Date</th>
                                <th class="px-2 py-3 text-left text-xs sm:text-sm font-medium text-gray-600 uppercase tracking-wider">Client</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-slate-200">
                            <?php foreach ($pets as $pet): ?>
                                <tr class="hover:bg-gray-50 transition-colors" data-name="<?= htmlspecialchars(strtolower($pet['pet_name'])) ?>" data-species="<?= htmlspecialchars(strtolower($pet['pet_species'])) ?>" data-sex="<?= htmlspecialchars(strtolower($pet['pet_sex'])) ?>" data-breed="<?= htmlspecialchars(strtolower($pet['pet_breed'])) ?>" data-client="<?= htmlspecialchars(strtolower($pet['client_name'])) ?>">
                                    <td class="px-4 py-2 text-sm text-gray-700"><?= htmlspecialchars($pet['pet_name']) ?></td>
                                    <td class="px-4 py-2 text-sm text-gray-600"><?= htmlspecialchars($pet['pet_species']) ?></td>
                                    <td class="px-4 py-2 text-sm text-gray-600"><?= htmlspecialchars($pet['pet_sex']) ?></td>
                                    <td class="px-4 py-2 text-sm text-gray-600"><?= htmlspecialchars($pet['pet_breed']) ?></td>
                                    <td class="px-4 py-2 text-sm text-gray-600"><?= htmlspecialchars($pet['pet_weight']) ?> kg</td>
                                    <td class="px-4 py-2 text-sm text-gray-600"><?= htmlspecialchars($pet['pet_birth_date']) ?></td>
                                    <td class="px-4 py-2 text-sm text-gray-600"><?= htmlspecialchars($pet['client_name']) ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <p id="noResults" class="text-center text-gray-500 text-sm sm:text-base" style="display: none;">No pets found matching your search.</p>
            <?php else: ?>
                <p class="text-center text-gray-500 text-sm sm:text-base">No pets added yet.</p>
            <?php endif; ?>
        </main>


        <!-- Add/Edit Pet Modal -->
        <div id="petModal" class="fixed inset-0 bg-black bg-opacity-50 hidden flex justify-center items-center z-50">
            <div class="bg-white rounded-lg shadow-lg w-full max-w-md border border-slate-200">
                <div class="w-full bg-gray-50 rounded-t-lg text-gray-800 border-b border-slate-200">
                    <h3 id="petModalTitle" class="text-lg sm:text-xl lg:text-2xl font-bold text-center text-gray-800 m-0 py-3">
                        Edit Pet
                    </h3>
                </div>
                <form id="petForm" method="POST" class="p-4 sm:p-6">
                    <input type="hidden" name="pet_id" id="pet_id" value="<?= isset($petToEdit) ? htmlspecialchars($petToEdit['pet_id']) : '' ?>">
                    <input type="hidden" name="update_pet" value="1">
                    <input type="hidden" name="client_id" id="clientId" value="<?= isset($petToEdit) ? htmlspecialchars($petToEdit['client_id']) : '' ?>">
                    <div class="mb-4">
                        <label class="block text-md font-semibold text-gray-700">Pet Name</label>
                        <input type="text" name="pet_name" id="petName" class="w-full p-2 border border-slate-200 rounded-md bg-gray-50 text-gray-800 focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
                            value="<?= isset($petToEdit) ? htmlspecialchars($petToEdit['pet_name']) : '' ?>" required>
                    </div>
                    <div class="mb-4">
                        <label class="block text-md font-semibold text-gray-700">Pet Sex</label>
                        <select name="pet_sex" id="petSex" class="w-full p-2 border border-slate-200 rounded-md bg-gray-50 text-gray-800 focus:ring-2 focus:ring-indigo-500 focus:border-transparent" required>
                            <option value="Male" <?= isset($petToEdit) && $petToEdit['pet_sex'] == 'Male' ? 'selected' : '' ?>>Male</option>
                            <option value="Female" <?= isset($petToEdit) && $petToEdit['pet_sex'] == 'Female' ? 'selected' : '' ?>>Female</option>
                        </select>
                    </div>
                    <div class="mb-4">
                        <label class="block text-md font-semibold text-gray-700">Pet Breed</label>
                        <input type="text" name="pet_breed" id="petBreed" class="w-full p-2 border border-slate-200 rounded-md bg-gray-50 text-gray-800 focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
                            value="<?= isset($petToEdit) ? htmlspecialchars($petToEdit['pet_breed']) : '' ?>" required>
                    </div>
                    <div class="mb-4">
                        <label class="block text-md font-semibold text-gray-700">Pet Weight (kg)</label>
                        <input type="number" name="pet_weight" id="petWeight" class="w-full p-2 border border-slate-200 rounded-md bg-gray-50 text-gray-800 focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
                            value="<?= isset($petToEdit) ? htmlspecialchars($petToEdit['pet_weight']) : '' ?>" required>
                    </div>
                    <div class="mb-4">
                        <label class="block text-md font-semibold text-gray-700">Birth Date</label>
                        <input type="date" name="pet_birth_date" id="petBirthDate" class="w-full p-2 border border-slate-200 rounded-md bg-gray-50 text-gray-800 focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
                            value="<?= isset($petToEdit) ? htmlspecialchars($petToEdit['pet_birth_date']) : '' ?>" required>
                    </div>
                    <div class="mb-4">
                        <label class="block text-md font-semibold text-gray-700">Owner</label>
                        <input type="text" id="clientName" class="w-full p-2 border border-slate-200 rounded-md bg-gray-50/50 text-gray-600"
                            value="<?= isset($clientName) ? htmlspecialchars($clientName) : '' ?>" readonly>
                    </div>
                    <div class="flex justify-between pt-4 border-t border-slate-200">
                        <button type="submit" class="bg-indigo-500 text-white px-4 py-2 rounded-md hover:bg-indigo-600 transition-colors">Save</button>
                        <button type="button" onclick="hidePetModal()" class="text-gray-500 hover:text-gray-700">Cancel</button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Modal Control Script -->
        <script>
            // Show pet modal
            function showPetModal() {
                document.getElementById('petModal').classList.remove('hidden');
            }

            // Hide pet modal
            function hidePetModal() {
                document.getElementById('petModal').classList.add('hidden');
                // Clear URL parameter
                const url = new URL(window.location.href);
                url.searchParams.delete('edit_pet_id');
                window.history.replaceState({}, document.title, url.pathname);
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

        <script>
            // Sort button functionality
            document.addEventListener('DOMContentLoaded', function() {
                const sortButton = document.getElementById('sortButton');
                const sortIcon = document.getElementById('sortIcon');

                if (sortButton) {
                    sortButton.addEventListener('click', function() {
                        const url = new URL(window.location.href);
                        const currentSort = url.searchParams.get('sort') || 'asc';
                        const newSort = currentSort === 'asc' ? 'desc' : 'asc';
                        url.searchParams.set('sort', newSort);
                        window.location.href = url.toString();
                    });
                }
            });

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
                            const breed = row.cells[3].textContent.toLowerCase();
                            const client = row.cells[6].textContent.toLowerCase();

                            if (petName.includes(searchTerm) || breed.includes(searchTerm) || client.includes(searchTerm)) {
                                row.style.display = '';
                                visibleCount++;
                            } else {
                                row.style.display = 'none';
                            }
                        });

                        const noResults = document.getElementById('noResults');
                        if (noResults) {
                            noResults.style.display = (visibleCount === 0) ? 'block' : 'none';
                        }
                    });

                    // Trigger filtering on page load if search value is present (from URL)
                    if (searchInput.value) {
                        searchInput.dispatchEvent(new Event('input'));
                    }
                }
            });
        </script>

        <script>
            // Client-side filtering for species and sex
            document.addEventListener('DOMContentLoaded', function() {
                const speciesFilter = document.getElementById('speciesFilter');
                const sexFilter = document.getElementById('sexFilter');

                function applyFilters() {
                    const selectedSpecies = speciesFilter.value.toLowerCase();
                    const selectedSex = sexFilter.value.toLowerCase();
                    const rows = document.querySelectorAll('tbody tr');
                    let visibleCount = 0;

                    rows.forEach(row => {
                        const species = row.getAttribute('data-species') || '';
                        const sex = row.getAttribute('data-sex') || '';

                        const speciesMatch = selectedSpecies === 'all' || species === selectedSpecies;
                        const sexMatch = selectedSex === 'all' || sex === selectedSex;

                        if (speciesMatch && sexMatch) {
                            row.style.display = '';
                            visibleCount++;
                        } else {
                            row.style.display = 'none';
                        }
                    });

                    const noResults = document.getElementById('noResults');
                    if (noResults) {
                        noResults.style.display = (visibleCount === 0) ? 'block' : 'none';
                    }
                }

                if (speciesFilter) {
                    speciesFilter.addEventListener('change', applyFilters);
                }

                if (sexFilter) {
                    sexFilter.addEventListener('change', applyFilters);
                }

                // Apply filters on page load
                applyFilters();
            });
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
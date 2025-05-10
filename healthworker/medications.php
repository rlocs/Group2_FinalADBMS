<?php
require_once '../dbConnection.php';
session_start();

// Check if user is logged in and is a Healthworker
if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'Healthworker') {
    header("Location: ../login.php");
    exit;
}

$username = $_SESSION['username'];

// Logout functionality
if (isset($_POST['logout'])) {
    session_destroy();
    header("Location: ../login.php");
    exit;
}

// Initialize database connection
$database = new Database();
$conn = $database->getConnection();

// Ensure we're using the renew_healthcenter database
$conn->exec("USE renew_healthcenter");

// Check for session messages
if (isset($_SESSION['message'])) {
    $message = $_SESSION['message'];
    $message_type = $_SESSION['message_type'] ?? 'info';
    // Clear the message after displaying it
    unset($_SESSION['message']);
    unset($_SESSION['message_type']);
}

// Process AJAX requests
if (isset($_GET['action'])) {
    header('Content-Type: application/json');
    
    if ($_GET['action'] == 'get_inventory_batches') {
        $medication_id = $_GET['medication_id'] ?? 0;
        
        try {
            // Get available inventory batches (quantity > 0)
            $stmt = $conn->prepare("SELECT * FROM medication_inventory WHERE medication_id = :id AND quantity > 0 ORDER BY expiry_date ASC");
            $stmt->bindParam(':id', $medication_id, PDO::PARAM_INT);
            $stmt->execute();
            $batches = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            echo json_encode($batches);
        } catch (PDOException $e) {
            echo json_encode(['error' => $e->getMessage()]);
        }
        exit;
    }
    
    // Add handler for get_medication_details
    if ($_GET['action'] == 'get_medication_details') {
        $medication_id = $_GET['medication_id'] ?? 0;
        
        try {
            // Get medication details
            $stmt = $conn->prepare("SELECT * FROM medications WHERE medication_id = :id");
            $stmt->bindParam(':id', $medication_id, PDO::PARAM_INT);
            $stmt->execute();
            $medication = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$medication) {
                echo json_encode(['error' => 'Medication not found']);
                exit;
            }
            
            // Get all inventory batches for this medication
            $batchStmt = $conn->prepare("SELECT * FROM medication_inventory WHERE medication_id = :id ORDER BY expiry_date ASC");
            $batchStmt->bindParam(':id', $medication_id, PDO::PARAM_INT);
            $batchStmt->execute();
            $batches = $batchStmt->fetchAll(PDO::FETCH_ASSOC);
            
            echo json_encode([
                'medication' => $medication,
                'batches' => $batches
            ]);
        } catch (PDOException $e) {
            echo json_encode(['error' => $e->getMessage()]);
        }
        exit;
    }
}

// Process form submissions
$message = '';

// Add new medication
if (isset($_POST['add_medication'])) {
    $name = $_POST['name'];
    $generic_name = $_POST['generic_name'];
    $category = $_POST['category'];
    $unit = $_POST['unit'];
    $description = $_POST['description'];
    
    $stmt = $conn->prepare("CALL add_medication(:name, :generic_name, :category, :unit, :description)");
    $stmt->bindParam(':name', $name);
    $stmt->bindParam(':generic_name', $generic_name);
    $stmt->bindParam(':category', $category);
    $stmt->bindParam(':unit', $unit);
    $stmt->bindParam(':description', $description);
    
    if ($stmt->execute()) {
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        $_SESSION['message'] = "Medication added successfully with ID: " . $result['medication_id'];
        $_SESSION['message_type'] = "success";
    } else {
        $_SESSION['message'] = "Error adding medication: " . implode(", ", $stmt->errorInfo());
        $_SESSION['message_type'] = "danger";
    }
    $stmt->closeCursor();
    
    header("Location: medications.php");
    exit;
}

// Add inventory
if (isset($_POST['add_inventory'])) {
    $medication_id = $_POST['medication_id'];
    $batch_number = $_POST['batch_number'];
    $quantity = $_POST['quantity'];
    $expiry_date = $_POST['expiry_date'];
    $supplier = $_POST['supplier'];
    $date_received = $_POST['date_received'];
    
    $stmt = $conn->prepare("CALL add_inventory(:medication_id, :batch_number, :quantity, :expiry_date, :supplier, :date_received)");
    $stmt->bindParam(':medication_id', $medication_id, PDO::PARAM_INT);
    $stmt->bindParam(':batch_number', $batch_number);
    $stmt->bindParam(':quantity', $quantity, PDO::PARAM_INT);
    $stmt->bindParam(':expiry_date', $expiry_date);
    $stmt->bindParam(':supplier', $supplier);
    $stmt->bindParam(':date_received', $date_received);
    
    if ($stmt->execute()) {
        $_SESSION['message'] = "Inventory added successfully";
        $_SESSION['message_type'] = "success";
    } else {
        $_SESSION['message'] = "Error adding inventory: " . implode(", ", $stmt->errorInfo());
        $_SESSION['message_type'] = "danger";
    }
    $stmt->closeCursor();
    
    header("Location: medications.php");
    exit;
}

// Dispense medication
if (isset($_POST['dispense_medication'])) {
    $inventory_id = $_POST['dispense_inventory_id'];
    $quantity = $_POST['dispense_quantity'];
    $patient_id = !empty($_POST['dispense_patient_id']) ? $_POST['dispense_patient_id'] : null;
    $notes = $_POST['dispense_notes'];
    $created_by = $username;
    
    // Server-side validation - check if quantity is valid
    $checkStmt = $conn->prepare("SELECT quantity FROM medication_inventory WHERE inventory_id = :id");
    $checkStmt->bindParam(':id', $inventory_id, PDO::PARAM_INT);
    $checkStmt->execute();
    $availableQuantity = $checkStmt->fetchColumn();
    
    if ($quantity <= 0 || $quantity > $availableQuantity) {
        $_SESSION['message'] = "Invalid quantity. You cannot dispense more than the available stock ($availableQuantity).";
        $_SESSION['message_type'] = "danger";
        header("Location: medications.php");
        exit;
    }
    
    // Continue with dispensing if validation passes
    $stmt = $conn->prepare("CALL dispense_medication(:inventory_id, :quantity, :patient_id, :notes, :created_by)");
    $stmt->bindParam(':inventory_id', $inventory_id, PDO::PARAM_INT);
    $stmt->bindParam(':quantity', $quantity, PDO::PARAM_INT);
    $stmt->bindParam(':patient_id', $patient_id, PDO::PARAM_INT);
    $stmt->bindParam(':notes', $notes);
    $stmt->bindParam(':created_by', $created_by);
    
    if ($stmt->execute()) {
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($result['result'] == 'Success') {
            $_SESSION['message'] = "Medication dispensed successfully";
            $_SESSION['message_type'] = "success";
        } else {
            $_SESSION['message'] = $result['result']; // Should be "Insufficient stock"
            $_SESSION['message_type'] = "danger";
        }
    } else {
        $_SESSION['message'] = "Error dispensing medication: " . implode(", ", $stmt->errorInfo());
        $_SESSION['message_type'] = "danger";
    }
    $stmt->closeCursor();
    
    // Safe redirect
    header("Location: medications.php");
    exit;
}

// Get medication list for dropdown
$medications = [];
$stmt = $conn->query("SELECT medication_id, name, generic_name FROM medications ORDER BY name");
if ($stmt) {
    $medications = $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Get medication inventory
$inventory = [];
try {
    $stmt = $conn->prepare("CALL get_medication_inventory()");
    $stmt->execute();
    $inventory = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $stmt->closeCursor();
} catch (PDOException $e) {
    // Handle error
}

// Get expiring medications (within 15 days)
$expiring = [];
try {
    $stmt = $conn->prepare("CALL get_expiring_medications(15)");
    $stmt->execute();
    $expiring = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $stmt->closeCursor();
} catch (PDOException $e) {
    // Handle error
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Medication Inventory</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="stylesheet" href="../css/appointment.css">
</head>
<body>

<!-- Navbar -->
<nav class="navbar navbar-expand-lg navbar-custom">
    <div class="container-fluid">
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse justify-content-between" id="navbarNav">
            <!-- Left side nav -->
            <ul class="navbar-nav">
                <li class="nav-item">
                    <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'index.php' ? 'active' : ''; ?>" href="index.php">Dashboard</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'appointment.php' ? 'active' : ''; ?>" href="appointment.php">Appointments</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'patient.php' ? 'active' : ''; ?>" href="patient.php">Patients</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'hhprofile.php' ? 'active' : ''; ?>" href="hhprofile.php">Household Profiles</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'medications.php' ? 'active' : ''; ?>" href="medications.php">Medications</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'barangay.php' ? 'active' : ''; ?>" href="barangay.php">Brgy. Map</a>
                </li>
                <!-- More dropdown -->
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle 
                        <?php echo in_array(basename($_SERVER['PHP_SELF']), ['faqs.php', 'aboutus.php']) ? 'active' : ''; ?>" 
                        href="#" id="navbarDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                        More
                    </a>
                    <ul class="dropdown-menu" aria-labelledby="navbarDropdown">
                        <li><a class="dropdown-item" href="faqs.php">FAQs</a></li>
                        <li><a class="dropdown-item" href="aboutus.php">About Us</a></li>
                    </ul>
                </li>
            </ul>
            
            <!-- USER INFO WRAPPER -->
            <div class="user-info">
                <!-- DATE -->
                <div class="user-date">
                    <p class="label">Today's Date</p>
                    <p class="value">
                        <?php 
                            date_default_timezone_set('Asia/Manila');
                            echo date('Y-m-d');
                        ?>
                    </p>
                </div>

                <!-- USERNAME -->
                <div class="user-name">
                    <a href="profile.php">
                        <strong><?php echo htmlspecialchars($username); ?></strong>
                    </a>
                </div>

                <!-- LOGOUT -->
                <form method="POST" action="">
                    <button type="submit" name="logout" class="logout-btn">Logout</button>
                </form>
            </div>
        </div>
    </div>
</nav>

<!-- Content Section -->
<div class="container mt-4">
    <?php if (!empty($message)): ?>
    <div class="alert alert-<?php echo $message_type ?? 'info'; ?> alert-dismissible fade show">
        <?php echo $message; ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    <?php endif; ?>

    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>Medication Inventory Management</h2>
        <div>
            <button type="button" class="btn btn-custom-add me-2" data-bs-toggle="modal" data-bs-target="#addMedicationModal">
                <i class="bi bi-plus-circle"></i> Add Medication
            </button>
            <button type="button" class="btn btn-custom-add" data-bs-toggle="modal" data-bs-target="#addInventoryModal">
                <i class="bi bi-plus-circle"></i> Add Inventory
            </button>
        </div>
    </div>
    
    <!-- Expiring Medications Alert -->
    <?php if (count($expiring) > 0): ?>
    <div class="alert alert-warning">
        <h5><i class="bi bi-exclamation-triangle"></i> Expiring Medications (15 days or less)</h5>
        <p>The following medications will expire soon:</p>
        <ul>
            <?php 
            // Use an array to track unique combinations to avoid duplicates
            $displayedItems = [];
            foreach($expiring as $med): 
                // Create a unique key for this medication + batch
                $key = $med['name'] . '-' . $med['batch_number'] . '-' . $med['expiry_date'];
                
                // Only display if we haven't seen this combination before
                if (!in_array($key, $displayedItems)):
                    $displayedItems[] = $key;
                    
                    // Only show if days remaining is 15 or fewer
                    if ($med['days_remaining'] <= 15):
            ?>
            <li>
                <strong><?php echo htmlspecialchars($med['name']); ?></strong> 
                (<?php echo htmlspecialchars($med['generic_name']); ?>) - 
                Batch: <?php echo htmlspecialchars($med['batch_number']); ?>, 
                Expires: <?php echo htmlspecialchars($med['expiry_date']); ?> 
                (<?php echo htmlspecialchars($med['days_remaining']); ?> days remaining)
            </li>
            <?php 
                    endif;
                endif;
            endforeach; 
            ?>
        </ul>
    </div>
    <?php endif; ?>
    
    <!-- Medications Table -->
    <div class="table-responsive mt-4">
        <table class="table table-bordered table-hover">
            <thead class="table-light">
                <tr>
                    <th>ID</th>
                    <th>Name</th>
                    <th>Generic Name</th>
                    <th>Category</th>
                    <th>Unit</th>
                    <th>Total Stock</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php
                // Fetch medications with the latest stock count
                $stmt = $conn->prepare("SELECT m.* FROM medications m ORDER BY m.medication_id ASC");
                $stmt->execute();
                $medications = $stmt->fetchAll(PDO::FETCH_ASSOC);
                
                foreach($medications as $item):
                    // Get the current total stock for this medication
                    $stockStmt = $conn->prepare("SELECT COALESCE(SUM(quantity), 0) as total_stock 
                                                FROM medication_inventory 
                                                WHERE medication_id = :id");
                    $stockStmt->bindParam(':id', $item['medication_id'], PDO::PARAM_INT);
                    $stockStmt->execute();
                    $stockResult = $stockStmt->fetch(PDO::FETCH_ASSOC);
                    $total_stock = $stockResult['total_stock'];
                ?>
                <tr>
                    <td><?php echo $item['medication_id']; ?></td>
                    <td><?php echo htmlspecialchars($item['name']); ?></td>
                    <td><?php echo htmlspecialchars($item['generic_name']); ?></td>
                    <td><?php echo htmlspecialchars($item['category']); ?></td>
                    <td><?php echo htmlspecialchars($item['unit']); ?></td>
                    <td><?php echo $total_stock; ?></td>
                    <td>
                        <!-- View Button -->
                        <button type="button" class="btn btn-primary btn-sm view-medication" 
                                data-bs-toggle="modal" data-bs-target="#viewMedicationModal" 
                                data-id="<?php echo $item['medication_id']; ?>">
                            <i class="bi bi-eye"></i> View
                        </button>
                        
                        <!-- Dispense Button -->
                        <button type="button" class="btn btn-success btn-sm dispense-medication" 
                                data-bs-toggle="modal" data-bs-target="#dispenseModal" 
                                data-id="<?php echo $item['medication_id']; ?>">
                            <i class="bi bi-box-arrow-right"></i> Dispense
                        </button>
                        
                        <!-- Delete Button -->
                        <button type="button" class="btn btn-danger btn-sm delete-medication"
                                data-bs-toggle="modal" data-bs-target="#deleteModal"
                                data-id="<?php echo $item['medication_id']; ?>"
                                data-name="<?php echo htmlspecialchars($item['name']); ?>">
                            <i class="bi bi-trash"></i> Delete
                        </button>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Add Medication Modal -->
<div class="modal fade" id="addMedicationModal" tabindex="-1" aria-labelledby="addMedicationModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="addMedicationModalLabel">Add New Medication</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="addMedicationForm" method="POST" action="">
                    <div class="mb-3">
                        <label for="name" class="form-label">Medication Name</label>
                        <input type="text" class="form-control" id="name" name="name" required>
                    </div>
                    <div class="mb-3">
                        <label for="generic_name" class="form-label">Generic Name</label>
                        <input type="text" class="form-control" id="generic_name" name="generic_name" required>
                    </div>
                    <div class="mb-3">
                        <label for="category" class="form-label">Category</label>
                        <select class="form-select" id="category" name="category">
                            <option value="Antibiotic">Antibiotic</option>
                            <option value="Analgesic">Analgesic</option>
                            <option value="Antipyretic">Antipyretic</option>
                            <option value="Antihypertensive">Antihypertensive</option>
                            <option value="Vitamin">Vitamin</option>
                            <option value="Other">Other</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="unit" class="form-label">Unit</label>
                        <select class="form-select" id="unit" name="unit">
                            <option value="Tablet">Tablet</option>
                            <option value="Capsule">Capsule</option>
                            <option value="Bottle">Bottle</option>
                            <option value="Vial">Vial</option>
                            <option value="Ampule">Ampule</option>
                            <option value="Sachet">Sachet</option>
                            <option value="Tube">Tube</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="description" class="form-label">Description</label>
                        <textarea class="form-control" id="description" name="description" rows="3"></textarea>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" name="add_medication" class="btn btn-primary">Add Medication</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Add Inventory Modal -->
<div class="modal fade" id="addInventoryModal" tabindex="-1" aria-labelledby="addInventoryModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="addInventoryModalLabel">Add Inventory</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="addInventoryForm" method="POST" action="">
                    <div class="mb-3">
                        <label for="medication_id" class="form-label">Select Medication</label>
                        <select class="form-select" id="medication_id" name="medication_id" required>
                            <option value="">-- Select Medication --</option>
                            <?php foreach($medications as $med): ?>
                            <option value="<?php echo $med['medication_id']; ?>">
                                <?php echo htmlspecialchars($med['name']); ?> (<?php echo htmlspecialchars($med['generic_name']); ?>)
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="batch_number" class="form-label">Batch Number</label>
                        <input type="text" class="form-control" id="batch_number" name="batch_number" required>
                    </div>
                    <div class="mb-3">
                        <label for="quantity" class="form-label">Quantity</label>
                        <input type="number" class="form-control" id="quantity" name="quantity" min="1" required>
                    </div>
                    <div class="mb-3">
                        <label for="expiry_date" class="form-label">Expiry Date</label>
                        <input type="date" class="form-control" id="expiry_date" name="expiry_date" required>
                    </div>
                    <div class="mb-3">
                        <label for="supplier" class="form-label">Supplier</label>
                        <input type="text" class="form-control" id="supplier" name="supplier">
                    </div>
                    <div class="mb-3">
                        <label for="date_received" class="form-label">Date Received</label>
                        <input type="date" class="form-control" id="date_received" name="date_received" value="<?php echo date('Y-m-d'); ?>" required>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" name="add_inventory" class="btn btn-primary">Add Inventory</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- View Medication Modal -->
<div class="modal fade" id="viewMedicationModal" tabindex="-1" aria-labelledby="viewMedicationModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="viewMedicationModalLabel">Medication Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" id="viewMedicationBody">
                <p class="text-center">Loading...</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<!-- Dispense Medication Modal -->
<div class="modal fade" id="dispenseModal" tabindex="-1" aria-labelledby="dispenseModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="dispenseModalLabel">Dispense Medication</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form method="post" action="" id="dispenseForm">
                <div class="modal-body">
                    <input type="hidden" id="dispense_medication_id" name="dispense_medication_id">
                    
                    <div class="mb-3">
                        <label for="dispense_inventory_id" class="form-label">Select Batch</label>
                        <select class="form-select" id="dispense_inventory_id" name="dispense_inventory_id" required>
                            <option value="">-- Select a batch --</option>
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <label for="dispense_quantity" class="form-label">Quantity</label>
                        <input type="number" class="form-control" id="dispense_quantity" name="dispense_quantity" min="1" required>
                        <div class="invalid-feedback" id="quantity-feedback">
                            Quantity cannot exceed available stock.
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="dispense_patient_id" class="form-label">Patient ID (optional)</label>
                        <input type="number" class="form-control" id="dispense_patient_id" name="dispense_patient_id">
                    </div>
                    
                    <div class="mb-3">
                        <label for="dispense_notes" class="form-label">Notes</label>
                        <textarea class="form-control" id="dispense_notes" name="dispense_notes" rows="3"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" name="dispense_medication" class="btn btn-primary" id="dispenseButton">Dispense</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Delete Confirmation Modal -->
<div class="modal fade" id="deleteModal" tabindex="-1" aria-labelledby="deleteModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="deleteModalLabel">Delete Medication</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to delete <span id="delete-medication-name"></span>?</p>
                <div class="alert alert-warning">
                    <p><strong>Warning:</strong> If this medication has inventory records or transaction history, 
                    you must choose "Force Delete" to remove all related records as well.</p>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                
                <!-- Regular delete - will fail if there are related records -->
                <form method="post" action="" class="d-inline">
                    <input type="hidden" name="medication_id" id="delete-medication-id">
                    <button type="submit" name="delete_medication" class="btn btn-danger">Delete</button>
                </form>
                
                <!-- Force delete - will delete all related records -->
                <form method="post" action="" class="d-inline">
                    <input type="hidden" name="medication_id" id="force-delete-medication-id">
                    <button type="submit" name="force_delete_medication" class="btn btn-danger">
                        Force Delete (All Records)
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Bootstrap and jQuery Scripts -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Search functionality
    const searchInput = document.getElementById('medicationSearch');
    if (searchInput) {
        searchInput.addEventListener('input', function() {
            const searchTerm = this.value.toLowerCase();
            const tableRows = document.querySelectorAll('tbody tr');
            
            tableRows.forEach(row => {
                const name = row.cells[1].textContent.toLowerCase();
                const genericName = row.cells[2].textContent.toLowerCase();
                const category = row.cells[3].textContent.toLowerCase();
                
                if (name.includes(searchTerm) || genericName.includes(searchTerm) || category.includes(searchTerm)) {
                    row.style.display = '';
                } else {
                    row.style.display = 'none';
                }
            });
        });
    }
    
    // View medication details
    const viewButtons = document.querySelectorAll('.view-btn');
    viewButtons.forEach(button => {
        button.addEventListener('click', function() {
            const medicationId = this.getAttribute('data-id');
            
            // Fetch medication details via AJAX
            fetch(`medications.php?action=get_medication_details&medication_id=${medicationId}`)
                .then(response => response.json())
                .then(data => {
                    const medication = data.medication;
                    const batches = data.batches;
                    
                    let batchesHtml = '';
                    if (batches.length > 0) {
                        batchesHtml = `
                            <h5 class="mt-4">Inventory Batches</h5>
                            <div class="table-responsive">
                                <table class="table table-striped">
                                    <thead>
                                        <tr>
                                            <th>Batch #</th>
                                            <th>Quantity</th>
                                            <th>Expiry Date</th>
                                            <th>Supplier</th>
                                            <th>Date Received</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                        `;
                        
                        batches.forEach(batch => {
                            batchesHtml += `
                                <tr>
                                    <td>${batch.batch_number || 'N/A'}</td>
                                    <td>${batch.quantity}</td>
                                    <td>${batch.expiry_date}</td>
                                    <td>${batch.supplier || 'N/A'}</td>
                                    <td>${batch.date_received || 'N/A'}</td>
                                </tr>
                            `;
                        });
                        
                        batchesHtml += `
                                    </tbody>
                                </table>
                            </div>
                        `;
                    } else {
                        batchesHtml = '<p>No inventory batches available for this medication.</p>';
                    }
                    
                    document.getElementById('viewMedicationBody').innerHTML = `
                        <div class="row">
                            <div class="col-md-6">
                                <p><strong>Name:</strong> ${medication.name}</p>
                                <p><strong>Generic Name:</strong> ${medication.generic_name}</p>
                                <p><strong>Category:</strong> ${medication.category}</p>
                            </div>
                            <div class="col-md-6">
                                <p><strong>Unit:</strong> ${medication.unit}</p>
                                <p><strong>Description:</strong> ${medication.description || 'N/A'}</p>
                            </div>
                        </div>
                        ${batchesHtml}
                    `;
                })
                .catch(error => {
                    console.error('Error fetching medication details:', error);
                    document.getElementById('viewMedicationBody').innerHTML = `
                        <div class="alert alert-danger">
                            Error loading medication details. Please try again.
                        </div>
                    `;
                });
        });
    });
    
    // Dispense medication
    const dispenseButtons = document.querySelectorAll('.dispense-btn');
    dispenseButtons.forEach(button => {
        button.addEventListener('click', function() {
            const medicationId = this.getAttribute('data-id');
            document.getElementById('dispense_medication_id').value = medicationId;
            
            // Fetch inventory batches via AJAX
            fetch(`medications.php?action=get_inventory_batches&medication_id=${medicationId}`)
                .then(response => response.json())
                .then(batches => {
                    const batchSelect = document.getElementById('dispense_inventory_id');
                    batchSelect.innerHTML = '<option value="">-- Select a batch --</option>';
                    
                    if (batches.length === 0) {
                        batchSelect.innerHTML += '<option disabled>No available batches</option>';
                    } else {
                        batches.forEach(batch => {
                            batchSelect.innerHTML += `
                                <option value="${batch.inventory_id}">
                                    Batch: ${batch.batch_number || 'N/A'} | 
                                    Qty: ${batch.quantity} | 
                                    Expires: ${batch.expiry_date}
                                </option>
                            `;
                        });
                    }
                })
                .catch(error => {
                    console.error('Error fetching inventory batches:', error);
                    document.getElementById('dispense_inventory_id').innerHTML = 
                        '<option value="">Error loading batches</option>';
                });
        });
    });
});
</script>
<script src="medication.js"></script>
</body>
</html>















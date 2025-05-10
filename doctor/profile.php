<?php
require_once '../dbConnection.php'; // Make sure this contains the Database class
session_start();
if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'Doctor') {
    header("Location: ../login.php");
    exit;
}

if (!isset($_SESSION['user_id']) || empty($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit;
}

$user_id = $_SESSION['user_id'];
$username = $_SESSION['username'];

// Establish database connection
$database = new Database();
$conn = $database->getConnection();

// Fetch user data from database
$sql = "SELECT * FROM users WHERE user_id = :user_id";
$stmt = $conn->prepare($sql);
$stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
$stmt->execute();
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user) {
    // User not found, redirect to login or show error
    header("Location: ../login.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="UTF-8">
      <title>Healthworker Profile</title>
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="description" content="View and update your healthworker profile. Create accounts for HealthCenter team members.">
      <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">   
      <link rel="stylesheet" href="../css/profile.css">
      <!-- SweetAlert2 CSS -->
      <link href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css" rel="stylesheet">
  </head>
<body>

  <a href="index.php" class="btn back-button">&larr; Back</a>

<div class="container">
    <div class="profile-title">Profile</div>

          <!-- Welcome Section -->
          <div class="welcome-container mb-4">
                  <h3>Welcome!</h3>
                  <h1><?php echo htmlspecialchars($username); ?>.</h1>
                  <p>Thanks for joining us. We are always striving to provide you with the best service.<br>
                    You can view your daily schedule and manage your patient appointments!</p>
              </div>

              <!-- Staff Info -->
              <div class="info-section mt-4">
              <h4 class="mb-3">Your Information</h4>
              <div class="table-responsive">
              <table class="table table-bordered table-striped table-hover align-middle shadow-sm">
                  <tbody>
                      <tr>
                          <th class="w-25">Name</th>
                          <td><?= htmlspecialchars($user['name']); ?></td>
                      </tr>
                      <tr>
                          <th>NIC Number</th>
                          <td><?= htmlspecialchars($user['nic']); ?></td>
                      </tr>
                      <tr>
                          <th>Email</th>
                          <td><?= htmlspecialchars($user['email']); ?></td>
                      </tr>
                      <tr>
                          <th>Gender</th>
                          <td><?= htmlspecialchars($user['gender']); ?></td>
                      </tr>
                      <tr>
                          <th>Date of Birth</th>
                          <td><?= htmlspecialchars($user['dob']); ?></td>
                      </tr>
                      <tr>
                          <th>Address</th>
                          <td><?= htmlspecialchars($user['address']); ?></td>
                      </tr>
                  </tbody>
              </table>
              </div>
          </div>

        <!-- Edit Button to open Modal -->
        <div class="edit-container">
            <button class="btn btn-sm btn-warning" data-bs-toggle="modal" data-bs-target="#editProfileModal">Edit Profile</button>
        </div>
        
        

<!-- Modal for Editing Profile -->
<div class="modal fade" id="editProfileModal" tabindex="-1" aria-labelledby="editProfileModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <form method="post" action="profile_crud.php" class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="editProfileModalLabel">Edit Profile</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <div class="mb-3">
          <label for="modalName" class="form-label">Name</label>
          <input type="text" id="modalName" name="name" class="form-control" value="<?= htmlspecialchars($user['name']); ?>" required>
        </div>
        <div class="mb-3">
          <label for="modalNic" class="form-label">NIC Number</label>
          <input type="text" id="modalNic" name="nic" class="form-control" value="<?= htmlspecialchars($user['nic']); ?>" required>
        </div>
        <div class="mb-3">
          <label for="modalEmail" class="form-label">Email</label>
          <input type="email" id="modalEmail" name="email" class="form-control" value="<?= htmlspecialchars($user['email']); ?>" required>
        </div>
        <div class="mb-3">
          <label for="modalGender" class="form-label">Gender</label>
          <select id="modalGender" name="gender" class="form-select" required>
            <option value="Male" <?= $user['gender'] === 'Male' ? 'selected' : '' ?>>Male</option>
            <option value="Female" <?= $user['gender'] === 'Female' ? 'selected' : '' ?>>Female</option>
            <option value="Other" <?= $user['gender'] === 'Other' ? 'selected' : '' ?>>Other</option>
          </select>
        </div>
        <div class="mb-3">
          <label for="modalDob" class="form-label">Date of Birth</label>
          <input type="date" id="modalDob" name="dob" class="form-control" value="<?= htmlspecialchars($user['dob']); ?>" required>
        </div>
        <div class="mb-3">
          <label for="modalAddress" class="form-label">Address</label>
          <textarea id="modalAddress" name="address" class="form-control" rows="2" required><?= htmlspecialchars($user['address']); ?></textarea>
        </div>
      </div>
      <div class="modal-footer">
      <button type="submit" name="edit_profile" class="btn btn-success">Save Changes</button>
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
      </div>
    </form>
  </div>
</div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
  <!-- SweetAlert2 JS -->
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
  <script>
    document.addEventListener('DOMContentLoaded', function () {
      <?php if (isset($_SESSION['success'])): ?>
        Swal.fire({
          icon: 'success',
          title: 'Success',
          text: '<?= addslashes($_SESSION['success']); ?>',
          confirmButtonText: 'OK'
        });
        <?php unset($_SESSION['success']); ?>
      <?php endif; ?>

      <?php if (isset($_SESSION['error'])): ?>
        Swal.fire({
          icon: 'error',
          title: 'Error',
          text: '<?= addslashes($_SESSION['error']); ?>',
          confirmButtonText: 'OK'
        });
        <?php unset($_SESSION['error']); ?>
      <?php endif; ?>
    });
  </script>
</body>
</html>
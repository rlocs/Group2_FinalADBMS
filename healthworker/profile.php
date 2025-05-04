<?php
session_start();
if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'Healthworker') {
    header("Location: ../login.php");
    exit;
}

// Retrieve the username and other details from the session
$username = $_SESSION['username'];
$email = $username . "@gmail.com"; // Dummy email
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
                              <td><?= htmlspecialchars($username); ?></td>
                          </tr>
                          <tr>
                              <th>NIC Number</th>
                              <td>123456789</td>
                          </tr>
                          <tr>
                              <th>Email</th>
                              <td><?= htmlspecialchars($email); ?></td>
                          </tr>
                          <tr>
                              <th>Gender</th>
                              <td>Male</td>
                          </tr>
                          <tr>
                              <th>Date of Birth</th>
                              <td>1980-01-01</td>
                          </tr>
                          <tr>
                              <th>Address</th>
                              <td>123 Main St, City, Country</td>
                          </tr>
                      </tbody>
                  </table>
              </div>
          </div>



        <!-- Edit Button to open Modal -->
        <div class="edit-container">
            <button class="btn btn-sm btn-warning" data-bs-toggle="modal" data-bs-target="#editProfileModal">Edit Profile</button>
        </div>
        
        <div class="create-section mt-4">
            <h3>Create Account For The HealthCenter Team!</h3>
        </div>

        <div class="btn-container">
            <button class="btn btn-success action-button" data-bs-toggle="modal" data-bs-target="#createAccountModal">Create</button>
        </div>
        
        </div>
    </div>
</div>

<!-- Modal for Creating New Staff Account -->
<div class="modal fade" id="createAccountModal" tabindex="-1" aria-labelledby="createAccountModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <form method="post" class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Create New Staff Account</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <div class="mb-3"><label class="form-label">Full Name</label><input type="text" name="name" class="form-control" required></div>
        <div class="mb-3"><label class="form-label">NIC Number</label><input type="text" name="nic" class="form-control" required></div>
        <div class="mb-3"><label class="form-label">Gmail</label><input type="email" name="email" class="form-control" required></div>
        <div class="mb-3">
          <label class="form-label">Gender</label>
          <select name="gender" class="form-control" required>
            <option value="" disabled selected>Select gender</option>
            <option>Male</option>
            <option>Female</option>
            <option>Other</option>
          </select>
        </div>
        <div class="mb-3"><label class="form-label">Date of Birth</label><input type="date" name="dob" class="form-control" required></div>
        <div class="mb-3"><label class="form-label">Address</label><textarea name="address" class="form-control" rows="2" required></textarea></div>
        <div class="mb-3">
          <label class="form-label">User Type</label>
          <select name="usertype" class="form-control" required>
            <option value="" disabled selected>Choose user type</option>
            <option value="Healthworker">Healthworker</option>
            <option value="Nurse">Nurse</option>
            <option value="Doctor">Doctor</option>
          </select>
        </div>
        <div class="mb-3"><label class="form-label">Username</label><input type="text" name="username" class="form-control" required></div>
        <div class="mb-3"><label class="form-label">Password</label><input type="password" name="password" class="form-control" required></div>
      </div>
      <div class="modal-footer">
        <button type="submit" name="create_account" class="btn btn-primary">Create Account</button>
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
      </div>
    </form>
  </div>
</div>


  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

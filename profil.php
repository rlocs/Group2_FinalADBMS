<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Profile</title>
  <style>
    body {
      font-family: Arial, sans-serif;
      margin: 0;
      padding: 40px;
      background-color: #fff;
    }

    .nav-buttons {
      display: flex;
      justify-content: space-between;
    }

    .btn-back {
      background-color: #e8edff;
      border: none;
      padding: 6px 12px;
      font-size: 12px;
      cursor: pointer;
      color: #333;
    }

    .profile-header {
      position: relative;
      background: url('profile.jpg') no-repeat center center;
      background-size: cover;
      display: flex;
      justify-content: space-between;
      align-items: center;
      padding: 30px;
      margin-top: 20px;
      color: white;
    }

    .profile-header::before {
      content: '';
      position: absolute;
      inset: 0;
      background-color: rgba(0, 0, 0, 0.4);
      z-index: 0;
    }

    .profile-pic {
      width: 150px;
      height: 150px;
      background-color: #fff;
      border-radius: 50%;
      z-index: 1;
    }

    .profile-text {
      position: relative;
      text-align: right;
      z-index: 1;
    }

    .profile-text h2 {
      margin: 0;
      font-size: 24px;
      font-weight: bold;
    }

    .profile-text h3 {
      margin: 10px 0 0;
      font-size: 18px;
      font-weight: bold;
    }

    .profile-text p {
      margin: 5px 0 0;
      font-size: 14px;
    }

    .profile-form {
      display: flex;
      gap: 40px;
      margin-top: 40px;
      flex-wrap: wrap;
      position: relative;
    }

    .form-column {
      display: flex;
      flex-direction: column;
      gap: 20px;
      flex: 1;
      min-width: 250px;
    }

    input[type="text"],
    input[type="email"] {
      background-color: #eaeaea;
      border: none;
      padding: 10px;
      font-size: 14px;
      width: 100%;
    }

    input:disabled {
      background-color: #ddd;
      color: #999;
    }

    .create-section {
      margin-top: 20px;
      text-align: center;
      display: flex;
      flex-direction: column;
      align-items: center;
      gap: 10px;
    }

    .create-link {
      display: block;
      color: #0044cc;
      font-size: 18px;
      text-decoration: none;
      cursor: pointer;
    }

    .btn-create,
    .btn-edit {
      background-color: #5689f6;
      border: none;
      padding: 8px 16px;
      font-size: 12px;
      cursor: pointer;
      color: white;
    }

    /* Modal Styles */
    .modal {
      display: none;
      position: fixed;
      z-index: 1000;
      left: 0;
      top: 0;
      width: 100%;
      height: 100%;
      background-color: rgba(0,0,0,0.4);
    }

    .modal-content {
      background-color: #fff;
      margin: 80px auto;
      padding: 20px;
      border-radius: 10px;
      width: 90%;
      max-width: 500px;
      position: relative;
    }

    .modal-header {
      display: flex;
      justify-content: space-between;
      align-items: center;
    }

    .modal-header h2 {
      font-size: 20px;
      font-weight: bold;
    }

    .modal-header button {
      background: none;
      border: none;
      font-size: 22px;
      cursor: pointer;
    }

    .modal-content label {
      display: block;
      margin-top: 12px;
      font-weight: bold;
      font-size: 14px;
    }

    .modal-content input,
    .modal-content select,
    .modal-content textarea {
      width: 100%;
      padding: 8px;
      margin-top: 4px;
      margin-bottom: 10px;
      border: 1px solid #ccc;
      border-radius: 4px;
    }

    .modal-footer {
      text-align: right;
      margin-top: 20px;
    }

    .modal-footer button {
      padding: 6px 12px;
      margin-left: 10px;
    }

  </style>
</head>
<body>

  <div class="nav-buttons">
    <button class="btn-back">Back</button>
  </div>

  <div class="profile-header">
    <div class="profile-pic"></div>
    <div class="profile-text">
      <h2>Profile</h2>
      <h3>Hello there Healthworker!</h3>
      <p>All systems are running smoothly. Let's get started.</p>
    </div>
  </div>

  <form class="profile-form" action="save_profile.php" method="POST">
    <div class="form-column">
      <input type="text" name="name" placeholder="Name" disabled>
      <input type="text" name="nic" placeholder="NIC Number" disabled>
      <input type="email" name="email" placeholder="Gmail acc" disabled>
      <input type="text" name="gender" placeholder="Gender" disabled>
    </div>
    <div class="form-column">
      <input type="text" name="dob" placeholder="Date of Birth" disabled>
      <input type="text" name="address" placeholder="Address" disabled>

      <!-- Edit Profile button -->
      <div style="text-align: right;">
        <button type="button" class="btn-edit" onclick="openModal()">Edit Profile</button>
      </div>

      <!-- Create section -->
      <div class="create-section">
        <a href="#" class="create-link">Create Acc for the Staff</a>
        <button type="submit" class="btn-create">Create</button>
      </div>
    </div>
  </form>

  <!-- Modal for editing profile -->
  <div class="modal" id="editModal">
    <div class="modal-content">
      <div class="modal-header">
        <h2>Edit Profile</h2>
        <button onclick="closeModal()">&times;</button>
      </div>
      <form id="editProfileForm">
        <label for="modalName">Name</label>
        <input type="text" id="modalName" placeholder="Enter your name">

        <label for="modalNic">NIC Number</label>
        <input type="text" id="modalNic" placeholder="Enter NIC number">

        <label for="modalEmail">Email</label>
        <input type="email" id="modalEmail" placeholder="Enter your email">

        <label for="modalGender">Gender</label>
        <select id="modalGender">
          <option value="Male">Male</option>
          <option value="Female">Female</option>
        </select>

        <label for="modalDob">Date of Birth</label>
        <input type="date" id="modalDob">

        <label for="modalAddress">Address</label>
        <textarea id="modalAddress" rows="2" placeholder="Enter address"></textarea>

        <div class="modal-footer">
          <button type="button" onclick="closeModal()">Cancel</button>
          <button type="button" onclick="saveChanges()">Save Changes</button>
        </div>
      </form>
    </div>
  </div>

  <script>
    // Open the modal for editing profile
    function openModal() {
      document.getElementById("editModal").style.display = "block";
    }

    // Close the modal without saving changes
    function closeModal() {
      document.getElementById("editModal").style.display = "none";
    }

    // Function to save the changes (just an alert for now)
    function saveChanges() {
      // Get the values from the modal input fields
      const name = document.getElementById("modalName").value;
      const nic = document.getElementById("modalNic").value;
      const email = document.getElementById("modalEmail").value;
      const gender = document.getElementById("modalGender").value;
      const dob = document.getElementById("modalDob").value;
      const address = document.getElementById("modalAddress").value;

      // You can update the form or send data to the server here
      alert(`Changes saved:\nName: ${name}\nNIC: ${nic}\nEmail: ${email}\nGender: ${gender}\nDOB: ${dob}\nAddress: ${address}`);

      // Close the modal after saving
      closeModal();
    }


    window.onclick = function(event) {
      const modal = document.getElementById("editModal");
      if (event.target === modal) {
        closeModal();
      }
    };
  </script>

</body>
</html>

<?php
session_start();
if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'Doctor') {
    header("Location: ../login.php");
    exit;
}
$username = $_SESSION['username'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <title>About Us - Health Center</title>
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <meta name="description" content="About Us - Health Center" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet" />
    <link rel="stylesheet" href="../css/index.css" />
    <link rel="stylesheet" href="../css/aboutus.css" />
</head>
<body>
<!-- Navbar -->
<nav class="navbar navbar-expand-lg navbar-custom">
    <div class="container-fluid">
        <div class="collapse navbar-collapse justify-content-between" id="navbarNav">
            <ul class="navbar-nav">
                <li class="nav-item">
                    <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'index.php' ? 'active' : ''; ?>" href="index.php">Dashboard</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'patient.php' ? 'active' : ''; ?>" href="patient.php">Patients</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'barangay.php' ? 'active' : ''; ?>" href="barangay.php">Brgy. Map</a>
                </li>
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle 
                        <?php echo in_array(basename($_SERVER['PHP_SELF']), ['faqs.php', 'aboutus.php']) ? 'active' : ''; ?>" 
                        href="#" id="navbarDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                        More
                    </a>
                    <ul class="dropdown-menu" aria-labelledby="navbarDropdown">
                        <li><a class="dropdown-item" href="faqs.php">FAQs</a></li>
                        <li><a class="dropdown-item active" href="aboutus.php">About Us</a></li>
                    </ul>
                </li>
            </ul>
            <div class="user-info">
                <div class="user-date">
                    <p class="label">Today's Date</p>
                    <p class="value">
                        <?php 
                            date_default_timezone_set('Asia/Manila');
                            echo date('Y-m-d');
                        ?>
                    </p>
                </div>
                <div class="user-name">
                    <a href="profile.php">
                        <strong><?php echo htmlspecialchars($username); ?></strong>
                    </a>
                </div>
                <form method="POST" action="">
                    <button type="submit" name="logout" class="logout-btn">Logout</button>
                </form>
            </div>
        </div>
    </div>
</nav>

<section class="about-section container mt-4">
    <h1>About Us</h1>
    <h2>Our Mission</h2>
    <p>Our mission is to provide quality healthcare services to the community with compassion and dedication.</p>
    <h2>Our Vision</h2>
    <p>To be the leading healthcare provider known for excellence and innovation.</p>
    <h2>Our Team</h2>
    <p>We have a team of dedicated health workers, doctors, and support staff committed to your well-being.</p>
</section>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

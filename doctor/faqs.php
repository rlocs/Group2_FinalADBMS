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
    <title>FAQs - Health Center</title>
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <meta name="description" content="Frequently Asked Questions about the Health Center System" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet" />
    <link rel="stylesheet" href="../css/index.css" />
    <link rel="stylesheet" href="../css/faqs_custom.css" />
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
                        <li><a class="dropdown-item active" href="faqs.php">FAQs</a></li>
                        <li><a class="dropdown-item" href="aboutus.php">About Us</a></li>
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

<div class="faq_3" unique-script-id="w-w-dm-id">
  <div class="responsive-container-block container">
    <div class="responsive-container-block faqheading-bg">
      <div class="heading-content">
        <p class="text-blk faq-heading">
          How can we help you?
        </p>
        <p class="text-blk faq-subhead">
          Welcome to the Health Center FAQ section. Here you will find answers to common questions about scheduling appointments, managing patient and household profiles, using the Barangay Map feature, and navigating the system efficiently.
        </p>
        <form class="form-box" id="i91f">
          <div class="input-box">
            <input class="input" placeholder="Type keywords here...">
            <span class="search-btn">
              <img class="search-svg" src="https://workik-widget-assets.s3.amazonaws.com/widget-assets/images/Search.svg" alt="search icon">
            </span>
          </div>
        </form>
      </div>
    </div>
    <div class="responsive-container-block dropdown-container-wrapper">
      <div class="responsive-container-block dropdown-container">
        <p class="text-blk faq-head2">
          Frequently Asked Questions
        </p>
        <div class="container-block">
          <div class="faq">
            <span class="faq-question-container">
              <p class="text-blk faq-questions">
                How do I schedule an appointment for a patient?
              </p>
              <img class="openimg image-block" src="https://workik-widget-assets.s3.amazonaws.com/widget-assets/images/arrow.svg" alt="toggle arrow">
            </span>
            <div class="answer-box">
              <p class="text-blk faq-answer">
                To schedule an appointment, navigate to the "Appointments" page, click the "Add Appointment" button, and fill out the required patient and appointment details. Submit the form to save the appointment.
              </p>
            </div>
          </div>
          <div class="faq">
            <span class="faq-question-container">
              <p class="text-blk faq-questions">
                How can I add or update patient information?
              </p>
              <img class="openimg" src="https://workik-widget-assets.s3.amazonaws.com/widget-assets/images/arrow.svg" alt="toggle arrow">
            </span>
            <div class="answer-box">
              <p class="text-blk faq-answer">
                Patient information can be managed on the "Patients" page. Use the "Add Patient" button to add new patients or the "Edit" button next to each patient to update their details.
              </p>
            </div>
          </div>
          <div class="faq">
            <span class="faq-question-container">
              <p class="text-blk faq-questions">
                What is a Household Profile and how do I manage it?
              </p>
              <img class="openimg" src="https://workik-widget-assets.s3.amazonaws.com/widget-assets/images/arrow.svg" alt="toggle arrow">
            </span>
            <div class="answer-box">
              <p class="text-blk faq-answer">
                A Household Profile contains information about a household, including members, medical conditions, and emergency contacts. You can manage household profiles on the "Household Profiles" page by adding, editing, or deleting profiles.
              </p>
            </div>
          </div>
          <div class="faq">
            <span class="faq-question-container">
              <p class="text-blk faq-questions">
                How do I use the Barangay Map feature?
              </p>
              <img class="openimg" src="https://workik-widget-assets.s3.amazonaws.com/widget-assets/images/arrow.svg" alt="toggle arrow">
            </span>
            <div class="answer-box">
              <p class="text-blk faq-answer">
                The Barangay Map feature allows you to view the geographic distribution of households and health resources. Access it via the "Brgy. Map" link in the navigation bar.
              </p>
            </div>
          </div>
          <div class="faq">
            <span class="faq-question-container">
              <p class="text-blk faq-questions">
                How do I log out of the system?
              </p>
              <img class="openimg" src="https://workik-widget-assets.s3.amazonaws.com/widget-assets/images/arrow.svg" alt="toggle arrow">
            </span>
            <div class="answer-box">
              <p class="text-blk faq-answer">
                Use the "Logout" button located in the top right corner of the navigation bar to securely log out of your account.
              </p>
            </div>
          </div>
          <div class="faq">
            <span class="faq-question-container">
              <p class="text-blk faq-questions">
                What should I do if I forget my password?
              </p>
              <img class="openimg" src="https://workik-widget-assets.s3.amazonaws.com/widget-assets/images/arrow.svg" alt="toggle arrow">
            </span>
            <div class="answer-box">
              <p class="text-blk faq-answer">
                If you forget your password, use the "Forgot Password" link on the login page to reset it. Follow the instructions sent to your registered email address to create a new password.
              </p>
            </div>
          </div>
          <div class="faq">
            <span class="faq-question-container">
              <p class="text-blk faq-questions">
                How can I update my profile information?
              </p>
              <img class="openimg" src="https://workik-widget-assets.s3.amazonaws.com/widget-assets/images/arrow.svg" alt="toggle arrow">
            </span>
            <div class="answer-box">
              <p class="text-blk faq-answer">
                You can update your profile information by navigating to the "Profile" page and editing your details. Remember to save changes before leaving the page.
              </p>
            </div>
          </div>
          <div class="faq">
            <span class="faq-question-container">
              <p class="text-blk faq-questions">
                Can I access the system from my mobile device?
              </p>
              <img class="openimg" src="https://workik-widget-assets.s3.amazonaws.com/widget-assets/images/arrow.svg" alt="toggle arrow">
            </span>
            <div class="answer-box">
              <p class="text-blk faq-answer">
                Yes, the Health Center system is responsive and can be accessed from mobile devices and tablets for your convenience.
              </p>
            </div>
          </div>
          <div class="faq">
            <span class="faq-question-container">
              <p class="text-blk faq-questions">
                Who do I contact for technical support?
              </p>
              <img class="openimg" src="https://workik-widget-assets.s3.amazonaws.com/widget-assets/images/arrow.svg" alt="toggle arrow">
            </span>
            <div class="answer-box">
              <p class="text-blk faq-answer">
                For technical support, please contact the Health Center IT department at support@healthcenter.local or call (123) 456-7899 during business hours.
              </p>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
  const faqs = document.querySelectorAll('.faq_3 .faq');
  faqs.forEach(faq => {
    faq.querySelector('.faq-question-container').addEventListener('click', () => {
      faq.classList.toggle('active');
    });
  });

  // Search functionality
  const searchInput = document.querySelector('.faq_3 .input');
  searchInput.addEventListener('input', () => {
    const filter = searchInput.value.toLowerCase();
    faqs.forEach(faq => {
      const question = faq.querySelector('.faq-questions').textContent.toLowerCase();
      if (question.includes(filter)) {
        faq.style.display = '';
      } else {
        faq.style.display = 'none';
      }
    });
  });
});
</script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
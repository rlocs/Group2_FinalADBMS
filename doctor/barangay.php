<?php
require_once '../dbConnection.php';
session_start();

// Check if user is logged in and is a Doctor
if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'Doctor') {
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
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Appointment</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="Manage your appointments and schedule with ease. Add, edit, or delete appointments for patients and doctors in the health center.">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="stylesheet" href="../css/appointment.css">
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<body>

<!-- Navbar -->
<nav class="navbar navbar-expand-lg navbar-custom">
    <div class="container-fluid">
        <div class="collapse navbar-collapse justify-content-between" id="navbarNav">
            <!-- Left side nav -->
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
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

<div class="container mt-4">
    <h2 class="text-center mb-4">Barangay San Pedro Map - Sto. Tomas, Batangas</h2>
    
    <div id="map" style="width: 100%; height: 600px;"></div>
    
    <div class="mt-3">
        <h4>Health Centers in Barangay San Pedro:</h4>
        <ul id="health-centers-list" class="list-group">
            <!-- Will be populated dynamically -->
        </ul>
    </div>
</div>

<script>
    // Health centers data with updated coordinates
    const healthCenters = [
        {
            name: "San Pedro Rural Health Unit",
            position: { lat: 14.0850, lng: 121.1782 },
            address: "123 San Pedro Main Road, Sto. Tomas, Batangas",
            contact: "(043) 784-5612",
            services: ["General Consultation", "Immunization", "Maternal Care", "Child Health Services"]
        },
        {
            name: "San Pedro Community Health Center",
            position: { lat: 14.0860, lng: 121.1790 },
            address: "45 East Road, San Pedro, Sto. Tomas, Batangas",
            contact: "(043) 778-3421",
            services: ["First Aid", "Basic Consultation", "Family Planning", "Nutrition Programs"]
        },
        {
            name: "Barangay Hall San Pedro",
            position: { lat: 14.0875, lng: 121.1775 },
            address: "San Pedro Barangay Hall, Sto. Tomas, Batangas",
            contact: "(043) 404-8765",
            services: [
                "Administrative Services", 
                "Community Programs", 
                "Healthcare Programs", 
                "Maternal and Child Health", 
                "Immunization Campaigns", 
                "Health Education Sessions"
            ]
        },
        {
            name: "Emelina's Pharmacy",
            position: { lat: 14.0870, lng: 121.1774 },
            address: "San Pedro, Sto. Tomas, Batangas",
            contact: "(043) 404-9876",
            services: ["Prescription Medications", "Over-the-counter Medicines", "Medical Supplies", "Health Consultations"]
        },
        {
            name: "San Pedro Pediatric Child Neurology Clinic",
            position: { lat: 14.0834, lng: 121.1770 },
            address: "78 Eastern Boulevard, San Pedro, Sto. Tomas, Batangas",
            contact: "(043) 789-5432",
            services: [
                "Pediatric Neurology Consultations",
                "Developmental Assessments",
                "Neurological Disorders Treatment",
                "EEG Testing",
                "Behavioral Therapy",
                "Child Development Programs"
            ]
        }
    ];

    document.addEventListener('DOMContentLoaded', function() {
        // Center map on updated Barangay Hall San Pedro location
        const barangayHall = { lat: 14.0875, lng: 121.1775 };
        
        const map = L.map('map').setView([barangayHall.lat, barangayHall.lng], 15);
        
        // Add OpenStreetMap tiles
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
        }).addTo(map);
        
        const barangayBoundary = [
            [14.0820, 121.1740], // Southwest corner 
            [14.0890, 121.1740], // Northwest corner
            [14.0890, 121.1825], // Northeast corner 
            [14.0820, 121.1825]  // Southeast corner 
        ];
        
        const boundary = L.polygon(barangayBoundary, {
            color: '#FF0000',
            weight: 2,
            opacity: 0.8,
            fillColor: '#FF0000',
            fillOpacity: 0.1
        }).addTo(map);
        
        // Add markers for health centers
        const healthCentersList = document.getElementById("health-centers-list");
        
        healthCenters.forEach((center, i) => {
            let markerIcon;
            if (center.name.includes("Barangay Hall")) {
                markerIcon = L.icon({
                    iconUrl: 'https://raw.githubusercontent.com/pointhi/leaflet-color-markers/master/img/marker-icon-2x-gold.png',
                    shadowUrl: 'https://cdnjs.cloudflare.com/ajax/libs/leaflet/0.7.7/images/marker-shadow.png',
                    iconSize: [25, 41],
                    iconAnchor: [12, 41],
                    popupAnchor: [1, -34],
                    shadowSize: [41, 41]
                });
            } else if (center.name.includes("Pharmacy")) {
                markerIcon = L.icon({
                    iconUrl: 'https://raw.githubusercontent.com/pointhi/leaflet-color-markers/master/img/marker-icon-2x-blue.png',
                    shadowUrl: 'https://cdnjs.cloudflare.com/ajax/libs/leaflet/0.7.7/images/marker-shadow.png',
                    iconSize: [25, 41],
                    iconAnchor: [12, 41],
                    popupAnchor: [1, -34],
                    shadowSize: [41, 41]
                });
            } else {
                markerIcon = L.icon({
                    iconUrl: 'https://raw.githubusercontent.com/pointhi/leaflet-color-markers/master/img/marker-icon-2x-red.png',
                    shadowUrl: 'https://cdnjs.cloudflare.com/ajax/libs/leaflet/0.7.7/images/marker-shadow.png',
                    iconSize: [25, 41],
                    iconAnchor: [12, 41],
                    popupAnchor: [1, -34],
                    shadowSize: [41, 41]
                });
            }
            
            const marker = L.marker([center.position.lat, center.position.lng], {icon: markerIcon})
                .addTo(map)
                .bindPopup(`
                    <div class="info-window">
                        <h5>${center.name}</h5>
                        <p><strong>Address:</strong> ${center.address}</p>
                        <p><strong>Contact:</strong> ${center.contact}</p>
                        <p><strong>Services:</strong></p>
                        <ul>
                            ${center.services.map(service => `<li>${service}</li>`).join('')}
                        </ul>
                    </div>
                `);
            
            // Add to list below map
            const listItem = document.createElement("li");
            listItem.className = "list-group-item";
            listItem.innerHTML = `
                <h5>${center.name}</h5>
                <p><strong>Address:</strong> ${center.address}</p>
                <p><strong>Contact:</strong> ${center.contact}</p>
                <button class="btn btn-sm btn-primary center-map-btn" data-index="${i}">Show on Map</button>
            `;
            healthCentersList.appendChild(listItem);
        });
        
        // Add click listeners to "Show on Map" buttons
        document.querySelectorAll(".center-map-btn").forEach(button => {
            button.addEventListener("click", function() {
                const index = parseInt(this.getAttribute("data-index"));
                map.setView([healthCenters[index].position.lat, healthCenters[index].position.lng], 17);
                
                let markerIcon;
                if (healthCenters[index].name.includes("Barangay Hall")) {
                    markerIcon = L.icon({
                        iconUrl: 'https://raw.githubusercontent.com/pointhi/leaflet-color-markers/master/img/marker-icon-2x-gold.png',
                        shadowUrl: 'https://cdnjs.cloudflare.com/ajax/libs/leaflet/0.7.7/images/marker-shadow.png',
                        iconSize: [25, 41],
                        iconAnchor: [12, 41],
                        popupAnchor: [1, -34],
                        shadowSize: [41, 41]
                    });
                } else if (healthCenters[index].name.includes("Pharmacy")) {
                    markerIcon = L.icon({
                        iconUrl: 'https://raw.githubusercontent.com/pointhi/leaflet-color-markers/master/img/marker-icon-2x-blue.png',
                        shadowUrl: 'https://cdnjs.cloudflare.com/ajax/libs/leaflet/0.7.7/images/marker-shadow.png',
                        iconSize: [25, 41],
                        iconAnchor: [12, 41],
                        popupAnchor: [1, -34],
                        shadowSize: [41, 41]
                    });
                } else {
                    markerIcon = L.icon({
                        iconUrl: 'https://raw.githubusercontent.com/pointhi/leaflet-color-markers/master/img/marker-icon-2x-red.png',
                        shadowUrl: 'https://cdnjs.cloudflare.com/ajax/libs/leaflet/0.7.7/images/marker-shadow.png',
                        iconSize: [25, 41],
                        iconAnchor: [12, 41],
                        popupAnchor: [1, -34],
                        shadowSize: [41, 41]
                    });
                }
                
                L.marker([healthCenters[index].position.lat, healthCenters[index].position.lng], {icon: markerIcon})
                    .addTo(map)
                    .bindPopup(`
                        <div class="info-window">
                            <h5>${healthCenters[index].name}</h5>
                            <p><strong>Address:</strong> ${healthCenters[index].address}</p>
                            <p><strong>Contact:</strong> ${healthCenters[index].contact}</p>
                            <p><strong>Services:</strong></p>
                            <ul>
                                ${healthCenters[index].services.map(service => `<li>${service}</li>`).join('')}
                            </ul>
                        </div>
                    `)
                    .openPopup();
            });
        });
    });
</script>

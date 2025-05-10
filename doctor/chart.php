<?php
if (!class_exists('Database')) {
    require_once '../dbConnection.php';
}

try {
    if (!isset($conn)) {
        $database = new Database();
        $conn = $database->getConnection();
    }

    // Patient age distribution data
    $stmt = $conn->prepare("CALL get_patient_age_distribution_sp()");
    $stmt->execute();
    $ageData = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $stmt->closeCursor();

    $allAgeRanges = ['0-9', '10-19', '20-29', '30-39', '40-49', '50-59', '60+', 'Unknown'];
    $ageCountsMap = array_fill_keys($allAgeRanges, 0);

    foreach ($ageData as $row) {
        if (in_array($row['age_range'], $allAgeRanges)) {
            $ageCountsMap[$row['age_range']] = (int)$row['patient_count'];
        }
    }

    $ageLabels = $allAgeRanges;
    $ageCounts = array_values($ageCountsMap);

    // Appointment counts data
    $stmt2 = $conn->prepare("CALL get_appointment_counts_by_date()");
    $stmt2->execute();
    $apptData = $stmt2->fetchAll(PDO::FETCH_ASSOC);
    $stmt2->closeCursor();

    $apptLabels = [];
    $apptCounts = [];
    foreach ($apptData as $row) {
        $apptLabels[] = $row['date'];
        $apptCounts[] = (int)$row['appointment_count'];
    }

    // Medical conditions prevalence data
    $stmtMed = $conn->prepare("CALL get_medical_conditions_prevalence_sp()");
    $stmtMed->execute();
    $medData = $stmtMed->fetchAll(PDO::FETCH_ASSOC);
    $stmtMed->closeCursor();

    $medLabels = [];
    $medCounts = [];
    foreach ($medData as $row) {
        $medLabels[] = $row['condition_name'];
        $medCounts[] = (int)$row['condition_count'];
    }

} catch (PDOException $e) {
    $ageLabels = [];
    $ageCounts = [];
    $apptLabels = [];
    $apptCounts = [];
    $medLabels = [];
    $medCounts = [];
}
?>

<!-- Chart containers -->
<div class="container mt-4">
    <div class="row">
        <div class="col-md-6">
            <canvas id="patientAgeChart" width="400" height="300"></canvas>
        </div>
        <div class="col-md-6">
            <canvas id="appointmentCountChart" width="400" height="300"></canvas>
        </div>
    </div>
</div>

<!-- Include Chart.js library -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Patient Age Distribution Chart (Line Chart)
    const ageCtx = document.getElementById('patientAgeChart').getContext('2d');
    const ageData = {
        labels: <?php echo json_encode($ageLabels); ?>,
        datasets: [{
            label: 'Number of Patients',
            data: <?php echo json_encode($ageCounts); ?>,
            borderColor: 'rgba(255, 99, 132, 1)',
            backgroundColor: 'rgba(255, 99, 132, 0.2)',
            fill: true,
            tension: 0.4
        }]
    };
    const ageConfig = {
        type: 'line',
        data: ageData,
        options: {
            responsive: true,
            plugins: {
                legend: { position: 'top' },
                title: { display: true, text: 'Patient Age Distribution' }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    max: 20,
                    ticks: {
                        stepSize: 1,
                        callback: function(value) {
                            return Number.isInteger(value) ? value : null;
                        }
                    }
                }
            }
        }
    };
    new Chart(ageCtx, ageConfig);

    // Appointment Counts Chart (Combo Chart: Bar + Line)
    const apptCtx = document.getElementById('appointmentCountChart').getContext('2d');
    const apptData = {
        labels: <?php echo json_encode($apptLabels); ?>,
        datasets: [
            {
                type: 'bar',
                label: 'Appointments',
                backgroundColor: 'rgba(54, 162, 235, 0.5)',
                borderColor: 'rgba(54, 162, 235, 1)',
                data: <?php echo json_encode($apptCounts); ?>,
                borderWidth: 1
            },
            {
                type: 'line',
                label: 'Trend',
                borderColor: 'rgba(75, 192, 192, 1)',
                backgroundColor: 'rgba(75, 192, 192, 0.2)',
                data: <?php echo json_encode($apptCounts); ?>,
                fill: false,
                tension: 0.4
            }
        ]
    };
    const apptConfig = {
        type: 'bar',
        data: apptData,
        options: {
            responsive: true,
            plugins: {
                title: { display: true, text: 'Appointments Over Last 7 Days' },
                legend: { position: 'top' }
            },
            scales: {
                x: { type: 'category' },
                y: { 
                    beginAtZero: true,
                    max: 20,
                    ticks: {
                        stepSize: 1,
                        callback: function(value) {
                            return Number.isInteger(value) ? value : null;
                        }
                    }
                }
            }
        }
    };
    new Chart(apptCtx, apptConfig);
});
</script>

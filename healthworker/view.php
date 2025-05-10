<?php
require '../dbConnection.php';

$intervention = null;

if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $id = (int) $_GET['id'];

    try {
        $database = new Database();
        $conn = $database->getConnection();

        $stmt = $conn->prepare("SELECT * FROM view_patient_intervention WHERE id = :id");
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();

        $intervention = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$intervention) {
            echo "<p style='color: red;'>No intervention found with ID: $id</p>";
            exit;
        }
    } catch (PDOException $e) {
        echo "<p style='color: red;'>Error fetching intervention: " . $e->getMessage() . "</p>";
        exit;
    }
} else {
    echo "<p style='color: red;'>Invalid or missing ID.</p>";
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Intervention Details</title>
    <link rel="stylesheet" href="../css/pp.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<div class="container mt-5">
    <h2>Intervention Details</h2>
    <div class="card p-4">
        <p><strong>Intervention ID:</strong> <?= htmlspecialchars($intervention['id']) ?></p>
        <p><strong>Patient Name:</strong> <?= htmlspecialchars($intervention['patient_name']) ?></p>
        <p><strong>Doctor:</strong> <?= htmlspecialchars($intervention['doctor']) ?></p>
        <p><strong>Reason:</strong> <?= htmlspecialchars($intervention['reason']) ?></p>
        <p><strong>Intervention:</strong> <?= htmlspecialchars($intervention['intervention']) ?></p>
    </div>
    <a href="patient.php" class="btn btn-secondary mt-3">Back</a>
</div>
</body>
</html>

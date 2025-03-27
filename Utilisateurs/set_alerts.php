<?php
session_start();
include '../BDD-Gestion/functions.php';

// Vérifier si l'utilisateur a le niveau d'accès "expert"
$userId = $_SESSION['user_id'];
$level = getUserLevel($userId);

if ($level != 'expert') {
    header("Location: ../Principale/index.php");
    exit();  // Si l'utilisateur n'a pas accès, rediriger vers la page principale
}

// Traitement du formulaire pour configurer une alerte
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $alertType = $_POST['alert_type'];
    $alertThreshold = $_POST['alert_threshold'];

    // Validation de l'alerte
    if (is_numeric($alertThreshold) && $alertThreshold > 0) {
        // Enregistrer l'alerte dans la base de données
        $stmt = $conn->prepare("INSERT INTO alerts (alert_type, threshold, user_id) VALUES (?, ?, ?)");
        $stmt->bind_param("sii", $alertType, $alertThreshold, $userId);

        if ($stmt->execute()) {
            $successMessage = "Alerte configurée avec succès.";
        } else {
            $errorMessage = "Erreur lors de la configuration de l'alerte. Veuillez réessayer.";
        }
    } else {
        $errorMessage = "Le seuil de l'alerte doit être un nombre valide et positif.";
    }
}
?>

<?php include '../Principale/header.php'; ?>

<div class="container mt-5">
    <h2 class="mb-4 text-center">Configuration des Alertes Globales</h2>

    <!-- Affichage des messages de succès ou d'erreur -->
    <?php if (isset($successMessage)): ?>
        <div class="alert alert-success"><?= htmlspecialchars($successMessage) ?></div>
    <?php endif; ?>
    <?php if (isset($errorMessage)): ?>
        <div class="alert alert-danger"><?= htmlspecialchars($errorMessage) ?></div>
    <?php endif; ?>

    <!-- Formulaire de configuration des alertes -->
    <div class="card mb-4">
        <div class="card-header bg-warning text-white">
            Configurer une alerte
        </div>
        <div class="card-body">
            <form action="set_alerts.php" method="POST">
                <div class="form-group">
                    <label for="alert_type">Type d'alerte :</label>
                    <select class="form-control" name="alert_type" id="alert_type">
                        <option value="energy_consumption">Surconsommation d'énergie</option>
                        <option value="device_maintenance">Maintenance des appareils</option>
                    </select>
                </div>
                <div class="form-group mt-2">
                    <label for="alert_threshold">Seuil d'alerte :</label>
                    <input type="number" class="form-control" name="alert_threshold" id="alert_threshold" required>
                    <small class="form-text text-muted">Entrez le seuil au-delà duquel l'alerte sera déclenchée.</small>
                </div>
                <button type="submit" class="btn btn-danger mt-3">Configurer l'alerte</button>
            </form>
        </div>
    </div>
</div>

<?php include '../Principale/footer.php'; ?>

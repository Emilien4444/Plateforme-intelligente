<?php
session_start();  // Démarre la session 

include '../BDD-Gestion/functions.php'; 

$userId = $_SESSION['user_id']; // Récupère l'ID de l'utilisateur actuellement connecté
$level = getUserLevel($userId); // Récupère le niveau d'accès de l'utilisateur 

// Si l'utilisateur n'a pas le niveau "expert" -> il est redirigé vers la page d'accueil
if ($level != 'expert') {
    header("Location: ../Principale/index.php");  // Redirige vers la page principale
    exit();
}

// Vérifie si le formulaire a été soumis via la méthode POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Récupère le type d'alerte et le seuil d'alerte depuis le formulaire
    $alertType = $_POST['alert_type'];
    $alertThreshold = $_POST['alert_threshold'];

    // Validation du seuil d'alerte -> doit être un nombre valide et positif
    if (is_numeric($alertThreshold) && $alertThreshold > 0) {
        // Si la validation réussit -> enregistre l'alerte dans la BDD
        $stmt = $conn->prepare("INSERT INTO alerts (alert_type, threshold, user_id) VALUES (?, ?, ?)");  
        $stmt->bind_param("sii", $alertType, $alertThreshold, $userId);  // Lie les paramètres à la requête

        if ($stmt->execute()) {
            $successMessage = "Alerte configurée avec succès.";  // Message de succès en cas d'insertion réussie
        } else {
            $errorMessage = "Erreur lors de la configuration de l'alerte. Veuillez réessayer.";  // Message d'erreur si l'insertion échoue
        }
    } else {
        $errorMessage = "Le seuil de l'alerte doit être un nombre valide et positif.";  // Message d'erreur si le seuil n'est pas valide
    }
}
?>

<?php include '../Principale/header.php'; ?> 

<div class="container mt-5">
    <h2 class="mb-4 text-center">Configuration des Alertes Globales</h2>

    <!-- Affichage des messages de succès ou d'erreur -->
    <?php if (isset($successMessage)): ?>
        <div class="alert alert-success"><?= htmlspecialchars($successMessage) ?></div>  <!-- Affiche le message de succès -->
    <?php endif; ?>
    <?php if (isset($errorMessage)): ?>
        <div class="alert alert-danger"><?= htmlspecialchars($errorMessage) ?></div>  <!-- Affiche le message d'erreur -->
    <?php endif; ?>

    <!-- Formulaire de configuration des alertes -->
    <div class="card mb-4">
        <div class="card-header bg-warning text-white">
            Configurer une alerte 
        </div>
        <div class="card-body">
            <form action="set_alerts.php" method="POST">
                <!-- Choix du type d'alerte -->
                <div class="form-group">
                    <label for="alert_type">Type d'alerte :</label>
                    <select class="form-control" name="alert_type" id="alert_type">
                        <option value="energy_consumption">Surconsommation d'énergie</option>
                        <option value="device_maintenance">Maintenance des appareils</option>
                    </select>
                </div>

                <!-- Seuil d'alerte -->
                <div class="form-group mt-2">
                    <label for="alert_threshold">Seuil d'alerte :</label>
                    <input type="number" class="form-control" name="alert_threshold" id="alert_threshold" required>  <!-- Champ pour entrer un seuil numérique -->
                    <small class="form-text text-muted">Entrez le seuil au-delà duquel l'alerte sera déclenchée.</small>  <!-- Explication sous le champ -->
                </div>

                <button type="submit" class="btn btn-danger mt-3">Configurer l'alerte</button>
            </form>
        </div>
    </div>
</div>

<?php include '../Principale/footer.php'; ?>  

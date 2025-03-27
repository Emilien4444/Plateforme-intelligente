<?php
session_start();
include '../BDD-Gestion/functions.php';

// Vérification de la session utilisateur
if (!isset($_SESSION['user_id'])) {
    header("Location: ../Utilisateurs/login.php");
    exit();
}

// Récupérer l'ID de l'utilisateur connecté
$userId = $_SESSION['user_id'];
$deviceId = intval($_GET['id']); // ID de l'objet connecté à modifier

// Vérifier si l'objet existe
$device = getDeviceById($deviceId);
if (!$device) {
    die("Objet introuvable.");
}

// Vérification du niveau de l'utilisateur : seulement les utilisateurs "expert" peuvent accéder à cette page
$userlevel = getUserLevel($userId);
if ($userlevel != 'expert') {
    die("Accès refusé.");
}

// Récupérer le message pour les erreurs ou succès
$message = "";

// Traitement du formulaire de mise à jour de l'objet
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['edit_device'])) {
        // Récupérer les données du formulaire
        $name = trim($_POST['name']);
        $type = trim($_POST['type']);
        $brand = trim($_POST['brand']);
        $connectivity = trim($_POST['connectivity']);
        $battery_status = intval($_POST['battery_status']);
        $current_temperature = floatval($_POST['current_temperature']);
        $target_temperature = floatval($_POST['target_temperature']);
        $mode = trim($_POST['mode']);
        $status = trim($_POST['status']);
        $location = trim($_POST['location']);

        // Mise à jour de l'objet dans la base de données
        $sql = "UPDATE devices SET name = ?, type = ?, brand = ?, connectivity = ?, battery_status = ?, current_temperature = ?, target_temperature = ?, mode = ?, status = ?, location = ? WHERE id = ? AND user_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sssssssssssi", $name, $type, $brand, $connectivity, $battery_status, $current_temperature, $target_temperature, $mode, $status, $location, $deviceId, $userId);

        if ($stmt->execute()) {
            $message = '<div class="alert alert-success text-center">✅ Objet connecté mis à jour avec succès.</div>';
        } else {
            $message = '<div class="alert alert-danger text-center">❌ Erreur lors de la mise à jour de l\'objet connecté.</div>';
        }
    }

    // Gestion de l'état ON/OFF
    if (isset($device['toggle_status'])) {
        // Envoi de la requête AJAX pour mettre à jour le statut
        $newStatus = ($_POST['status'] == 'active') ? 'inactive' : 'active';

        // Appel au fichier toggle_device_statusV.php pour changer le statut de l'objet
        $url = 'toggle_device_status.php';
        $data = [
            'device_id' => $deviceId,
            'status' => $newStatus
        ];

        // Initialiser la requête cURL pour envoyer les données
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));

        // Exécuter et obtenir la réponse
        $response = curl_exec($ch);
        curl_close($ch);

        $response = json_decode($response, true);
        if ($response['success']) {
            $message = '<div class="alert alert-success text-center">✅ Statut mis à jour avec succès.</div>';
        } else {
            $message = '<div class="alert alert-danger text-center">❌ Erreur lors de la mise à jour du statut.</div>';
        }
    }
}
?>

<?php include '../Principale/header.php'; ?>

<div class="form-container">
    <h2>Modifier l'état de l'objet</h2>
    <p><strong>Objet :</strong> <?= htmlspecialchars($device['name']) ?></p>

    <!-- Affichage du message -->
    <?php if ($message) echo $message; ?>

    <div class="card shadow p-3 mb-4">
    <!-- Formulaire de modification de l'objet -->
    <form method="POST">
        <input type="hidden" name="device_id" value="<?= $device['id'] ?>">

        <!-- Bouton ON/OFF -->
        <button type="submit" name="toggle_status" class="btn btn-<?= ($device['status'] == 'actif') ? 'success' : 'danger' ?>" 
            data-id="<?= $deviceId ?>" data-status="<?= $device['status'] ?>">
            <?= ($device['status'] == 'actif') ? 'Allumer' : 'Éteindre' ?>
        </button>

        <p id="status-message"></p>
        
        <!-- Champs pour modifier l'objet -->
        <div class="mb-3">
            <label for="name" class="form-label">Nom de l'objet</label>
            <input type="text" id="name" name="name" class="form-control" value="<?= htmlspecialchars($device['name']) ?>" required>
        </div>

        <div class="mb-3">
            <label for="type" class="form-label">Type</label>
            <input type="text" id="type" name="type" class="form-control" value="<?= htmlspecialchars($device['type']) ?>" required>
        </div>

        <div class="mb-3">
            <label for="brand" class="form-label">Marque</label>
            <input type="text" id="brand" name="brand" class="form-control" value="<?= htmlspecialchars($device['brand']) ?>" required>
        </div>

        <div class="mb-3">
            <label for="connectivity" class="form-label">Connectivité</label>
            <input type="text" id="connectivity" name="connectivity" class="form-control" value="<?= htmlspecialchars($device['connectivity']) ?>" required>
        </div>

        <div class="mb-3">
            <label for="battery_status" class="form-label">Niveau de batterie</label>
            <input type="number" id="battery_status" name="battery_status" class="form-control" value="<?= htmlspecialchars($device['battery_status']) ?>" required min="0" max="100">
        </div>

        <div class="mb-3">
            <label for="current_temperature" class="form-label">Température actuelle</label>
            <input type="number" id="current_temperature" name="current_temperature" class="form-control" value="<?= htmlspecialchars($device['current_temperature']) ?>" step="0.01" required>
        </div>

        <div class="mb-3">
            <label for="target_temperature" class="form-label">Température cible</label>
            <input type="number" id="target_temperature" name="target_temperature" class="form-control" value="<?= htmlspecialchars($device['target_temperature']) ?>" step="0.01" required>
        </div>

        <div class="mb-3">
            <label for="mode" class="form-label">Mode</label>
            <input type="text" id="mode" name="mode" class="form-control" value="<?= htmlspecialchars($device['mode']) ?>" required>
        </div>

        <div class="mb-3">
            <label for="status" class="form-label">Statut</label>
            <select name="status" id="status" class="form-select">
                <option value="actif" <?= ($device['status'] == 'actif') ? 'selected' : '' ?>>Actif</option>
                <option value="inactif" <?= ($device['status'] == 'inactif') ? 'selected' : '' ?>>Inactif</option>
                <option value="connected" <?= ($device['status'] == 'connected') ? 'selected' : '' ?>>Connecté</option>
                <option value="disconnected" <?= ($device['status'] == 'disconnected') ? 'selected' : '' ?>>Déconnecté</option>
            </select>
        </div>

        <div class="mb-3">
            <label for="location" class="form-label">Emplacement</label>
            <input type="text" id="location" name="location" class="form-control" value="<?= htmlspecialchars($device['location']) ?>" required>
        </div>

        <button type="submit" name="edit_device" class="btn btn-primary">Mettre à jour l'objet</button>
    </form>
    </div>
</div>

<?php include '../Principale/footer.php'; ?>

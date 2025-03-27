<?php
session_start();
include '../BDD-Gestion/functions.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: ../Utilisateurs/login.php");
    exit();
}

$userId = $_SESSION['user_id'];
$message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['add_device'])) {
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
    
    // Insertion de l'objet dans la table devices
    $sql = "INSERT INTO devices (user_id, name, type, brand, connectivity, battery_status, current_temperature, target_temperature, mode, status, location) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("isssssddsss", $userId, $name, $type, $brand, $connectivity, $battery_status, $current_temperature, $target_temperature, $mode, $status, $location);

    if ($stmt->execute()) {
        $message = '<div class="alert alert-success text-center">✅ Objet connecté ajouté avec succès.</div>';
    } else {
        $message = '<div class="alert alert-danger text-center">❌ Erreur lors de l\'ajout de l\'objet connecté.</div>';
    }
}
?>

<?php include '../Principale/header.php'; ?>

<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header bg-primary text-white text-center">
                    <h3>➕ Ajouter un Objet Connecté</h3>
                </div>
                <div class="card-body">
                    <?php if ($message): ?>
                        <div class="alert alert-danger"><?= $message ?></div>
                    <?php endif; ?>
                    <form method="POST">
                        <div class="mb-3">
                            <label for="name" class="form-label">Nom de l'objet :</label>
                            <input type="text" id="name" name="name" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label for="type" class="form-label">Type :</label>
                            <input type="text" id="type" name="type" class="form-control" required placeholder="Ex: Thermostat, Caméra">
                        </div>
                        <div class="mb-3">
                            <label for="brand" class="form-label">Marque :</label>
                            <input type="text" id="brand" name="brand" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label for="connectivity" class="form-label">Connectivité :</label>
                            <input type="text" id="connectivity" name="connectivity" class="form-control" required placeholder="Ex: Wi-Fi, Bluetooth">
                        </div>
                        <div class="mb-3">
                            <label for="battery_status" class="form-label">Niveau de batterie (%) :</label>
                            <input type="number" id="battery_status" name="battery_status" class="form-control" required min="0" max="100">
                        </div>
                        <div class="mb-3">
                            <label for="current_temperature" class="form-label">Température actuelle (°C) :</label>
                            <input type="number" id="current_temperature" name="current_temperature" class="form-control" required step="0.01">
                        </div>
                        <div class="mb-3">
                            <label for="target_temperature" class="form-label">Température cible (°C) :</label>
                            <input type="number" id="target_temperature" name="target_temperature" class="form-control" required step="0.01">
                        </div>
                        <div class="mb-3">
                            <label for="mode" class="form-label">Mode :</label>
                            <input type="text" id="mode" name="mode" class="form-control" required placeholder="Ex: Automatique, Manuel">
                        </div>
                        <div class="mb-3">
                            <label for="status" class="form-label">Statut :</label>
                            <select id="status" name="status" class="form-select" required>
                                <option value="active">Actif</option>
                                <option value="inactive">Inactif</option>
                                <option value="connected">Connecté</option>
                                <option value="disconnected">Déconnecté</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="location" class="form-label">Lieu :</label>
                            <input type="text" id="location" name="location" class="form-control" required placeholder="Ex: Salon, Chambre">
                        </div>
                        <button type="submit" name="add_device" class="btn btn-success w-100">Ajouter</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include '../Principale/footer.php'; ?>

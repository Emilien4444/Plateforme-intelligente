<?php
session_start();
include '../BDD-Gestion/functions.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$userId = $_SESSION['user_id'];
$level = getUserLevel($userId);

// Récupérer les paramètres de recherche et de filtre
$search = $_GET['search'] ?? '';
$type = $_GET['type'] ?? '';
$status = $_GET['status'] ?? '';
$connectivity = $_GET['connectivity'] ?? '';

// Construire la requête de filtrage
$sql = "SELECT * FROM devices WHERE user_id = ? AND (name LIKE ? OR type LIKE ?)";
if ($type) {
    $sql .= " AND type LIKE ?";
}
if ($status) {
    $sql .= " AND status LIKE ?";
}
if ($connectivity) {
    $sql .= " AND connectivity LIKE ?";
}

$stmt = $conn->prepare($sql);
$searchQuery = "%" . $search . "%";
$filterParams = [$userId, $searchQuery, $searchQuery];

if ($type) {
    $filterParams[] = "%" . $type . "%";
}
if ($status) {
    $filterParams[] = "%" . $status . "%";
}
if ($connectivity) {
    $filterParams[] = "%" . $connectivity . "%";
}

$stmt->bind_param(str_repeat('s', count($filterParams)), ...$filterParams);
$stmt->execute();
$devices = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

// Récupérer les statistiques de consommation pour le graphique
$sqlStats = "SELECT device_id, SUM(consumption) as total_consumption FROM consumption_stats GROUP BY device_id";
$stmtStats = $conn->prepare($sqlStats);
$stmtStats->execute();
$consumptionStats = $stmtStats->get_result()->fetch_all(MYSQLI_ASSOC);

// Ajouter des points à chaque connexion
$pointsForConnection = 0.25;  // Points pour la connexion
$sql = "UPDATE users SET points = points + ? WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("di", $pointsForConnection, $userId);
$stmt->execute();

// Mettre à jour le niveau en fonction des points accumulés
updateUserLevel($userId);
?>

<?php include '../Principale/header.php'; ?>

<div class="container py-5">
    <h2 class="text-center mb-4">Tableau de Bord des Objets Connectés</h2>

    <?php if ($level == 'advanced' || $level == 'expert'): ?>
        <a href="add_device.php" class="btn btn-primary mb-3">➕ Ajouter un objet connecté</a>
        <a href="gestion.php" class="btn btn-primary mb-3">Détails de vos objets connectés</a>
    <?php endif; ?>

    <!-- Formulaire de recherche avec filtres -->
    <form method="GET" class="mb-3">
        <div class="row">
            <div class="col-md-3">
                <input type="text" name="search" class="form-control" placeholder="Rechercher un objet" value="<?= htmlspecialchars($search) ?>">
            </div>
            <div class="col-md-2">
                <select name="type" class="form-select">
                    <option value="">Type</option>
                    <option value="thermostat" <?= $type == 'thermostat' ? 'selected' : '' ?>>Thermostat</option>
                    <option value="capteur" <?= $type == 'capteur' ? 'selected' : '' ?>>Capteur</option>
                    <option value="camera" <?= $type == 'camera' ? 'selected' : '' ?>>Caméra</option>
                    <option value="montre" <?= $type == 'montre' ? 'selected' : '' ?>>Montre Connectée</option>
                </select>
            </div>
            <div class="col-md-2">
                <select name="status" class="form-select">
                    <option value="">État</option>
                    <option value="actif" <?= $status == 'actif' ? 'selected' : '' ?>>Actif</option>
                    <option value="inactif" <?= $status == 'inactif' ? 'selected' : '' ?>>Inactif</option>
                    <option value="connecté" <?= $status == 'connecté' ? 'selected' : '' ?>>Connecté</option>
                    <option value="déconnecté" <?= $status == 'déconnecté' ? 'selected' : '' ?>>Déconnecté</option>
                </select>
            </div>
            <div class="col-md-2">
                <select name="connectivity" class="form-select">
                    <option value="">Connectivité</option>
                    <option value="Wi-Fi" <?= $connectivity == 'Wi-Fi' ? 'selected' : '' ?>>Wi-Fi</option>
                    <option value="Bluetooth" <?= $connectivity == 'Bluetooth' ? 'selected' : '' ?>>Bluetooth</option>
                    <option value="Zigbee" <?= $connectivity == 'Zigbee' ? 'selected' : '' ?>>Zigbee</option>
                </select>
            </div>
            <div class="col-md-2">
                <button type="submit" class="btn btn-dark w-100">Rechercher</button>
            </div>
        </div>
    </form>

    <!-- Liste des objets connectés -->
    <ul class="list-group">
        <?php foreach ($devices as $device): ?>
            <li class="list-group-item d-flex justify-content-between align-items-center">
                <div>
                    <strong><?= htmlspecialchars($device['name']) ?></strong> - <?= htmlspecialchars($device['type']) ?>
                </div>
                <div class="d-flex">
                    <!-- Button ON/OFF -->
                    <?php if ($level == 'advanced' || $level == 'expert'): ?>
                        <button class="btn toggle-btn btn-sm <?= ($device['status'] == 'active') ? 'btn-success' : 'btn-danger' ?>" 
                            data-id="<?= $device['id'] ?>" data-status="<?= $device['status'] ?>">
                            <?= ($device['status'] == 'active') ? 'Allumer' : 'Éteindre' ?>
                        </button>
                    <?php endif; ?>

                    <!-- Button Détails - Afficher dans un modal -->
                    <button class="btn btn-info btn-sm ms-2" data-bs-toggle="modal" data-bs-target="#deviceModal"
                        data-id="<?= $device['id'] ?>"
                        data-name="<?= $device['name'] ?>"
                        data-type="<?= $device['type'] ?>"
                        data-status="<?= $device['status'] ?>"
                        data-connectivity="<?= $device['connectivity'] ?>"
                        data-battery-status="<?= $device['battery_status'] ?>"
                        data-mode="<?= $device['mode'] ?>"
                        data-location="<?= $device['location'] ?>">
                        Détails
                    </button>

                    <!-- Formulaire de suppression -->
                    <?php if ($level == 'advanced' || $level == 'expert'): ?>
                        <a href="delete_device.php?id=<?= $device['id'] ?>" class="btn btn-danger btn-sm delete-btn">Supprimer</a>
                    <?php endif; ?>

                </div>
            </li>
        <?php endforeach; ?>
    </ul>
    
    <!-- Options supplémentaires -->
    <div class="mt-4">
        <a href="logout.php" class="btn btn-danger">Déconnexion</a>

        <?php if ($level == 'expert'): ?>
            <a href="manage_family.php" class="btn btn-info">👨‍👩‍👧 Gérer ma famille</a>
        <?php endif; ?>
    </div>
</div>

<!-- Modal pour afficher les détails de l'objet -->
<div class="modal fade" id="deviceModal" tabindex="-1" aria-labelledby="deviceModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="deviceModalLabel">Détails de l'objet connecté</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <p><strong>Nom:</strong> <span id="modal-name"></span></p>
        <p><strong>Type:</strong> <span id="modal-type"></span></p>
        <p><strong>État:</strong> <span id="modal-status"></span></p>
        <p><strong>Connectivité:</strong> <span id="modal-connectivity"></span></p>
        <p><strong>Batterie:</strong> <span id="modal-battery-status"></span></p>
        <p><strong>Mode:</strong> <span id="modal-mode"></span></p>
        <p><strong>Lieu:</strong> <span id="modal-location"></span></p>
      </div>
    </div>
  </div>
</div>

<script>
// Remplir le modal avec les données de l'objet
document.addEventListener('DOMContentLoaded', function() {
    const deviceButtons = document.querySelectorAll('[data-bs-toggle="modal"]');
    deviceButtons.forEach(button => {
        button.addEventListener('click', function() {
            document.getElementById('modal-name').textContent = this.getAttribute('data-name');
            document.getElementById('modal-type').textContent = this.getAttribute('data-type');
            document.getElementById('modal-status').textContent = this.getAttribute('data-status');
            document.getElementById('modal-connectivity').textContent = this.getAttribute('data-connectivity');
            document.getElementById('modal-battery-status').textContent = this.getAttribute('data-battery-status');
            document.getElementById('modal-mode').textContent = this.getAttribute('data-mode');
            document.getElementById('modal-location').textContent = this.getAttribute('data-location');
        });
    });
});

// Gérer le clic sur le bouton ON/OFF
document.addEventListener('DOMContentLoaded', function() {
    const toggleButtons = document.querySelectorAll('.toggle-btn');
    toggleButtons.forEach(button => {
        button.addEventListener('click', function() {
            const deviceId = this.getAttribute('data-id');
            const currentStatus = this.getAttribute('data-status');
            
            // Changer l'état (toggle ON/OFF)
            const newStatus = currentStatus === 'active' ? 'inactive' : 'active';

            // Envoyer une requête AJAX pour mettre à jour l'état dans la base de données
            fetch('toggle_device_status.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({ device_id: deviceId, status: newStatus })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Mettre à jour l'interface utilisateur
                    this.innerText = newStatus === 'active' ? 'Éteindre' : 'Allumer';
                    this.classList.toggle('btn-success');
                    this.classList.toggle('btn-danger');
                    this.setAttribute('data-status', newStatus);  // Mettre à jour de l'attribut de statut
                } else {
                    alert('Erreur lors de la mise à jour de l\'état.');
                }
            })
            .catch(error => console.error('Erreur:', error));
        });
    });
});
</script>

<?php include '../Principale/footer.php'; ?>

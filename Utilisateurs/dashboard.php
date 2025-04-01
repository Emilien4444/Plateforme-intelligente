<?php
session_start(); // Démarre la session 
include '../BDD-Gestion/functions.php'; 

// Vérification si l'utilisateur est connecté
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php"); // Si l'utilisateur n'est pas connecté, redirige vers la page de login
    exit();
}

$userId = $_SESSION['user_id']; // Récupère l'ID de l'utilisateur connecté
$level = getUserLevel($userId); // Récupère le niveau de l'utilisateur (par exemple, 'expert')

// Récupérer les paramètres de recherche et de filtre depuis l'URL
$search = $_GET['search'] ?? ''; // Si un terme de recherche est passé dans l'URL, on le récupère
$type = $_GET['type'] ?? ''; // Récupère le type de l'objet 
$status = $_GET['status'] ?? ''; // Récupère l'état de l'objet 
$connectivity = $_GET['connectivity'] ?? ''; // Récupère la connectivité de l'objet 

// Construire la requête SQL en fonction des filtres appliqués
$sql = "SELECT * FROM devices WHERE user_id = ? AND (name LIKE ? OR type LIKE ?)";
if ($type) {
    $sql .= " AND type LIKE ?"; // Ajoute un filtre pour le type de l'objet si spécifié
}
if ($status) {
    $sql .= " AND status LIKE ?"; // Ajoute un filtre pour l'état de l'objet si spécifié
}
if ($connectivity) {
    $sql .= " AND connectivity LIKE ?"; // Ajoute un filtre pour la connectivité si spécifié
}

// Préparer et exécuter la requête SQL
$stmt = $conn->prepare($sql);
$searchQuery = "%" . $search . "%"; // Ajoute les caractères "%" pour rechercher par motif
$filterParams = [$userId, $searchQuery, $searchQuery]; // Paramètres de la requête

if ($type) {
    $filterParams[] = "%" . $type . "%";
}
if ($status) {
    $filterParams[] = "%" . $status . "%";
}
if ($connectivity) {
    $filterParams[] = "%" . $connectivity . "%";
}

$stmt->bind_param(str_repeat('s', count($filterParams)), ...$filterParams); // Lier les paramètres à la requête
$stmt->execute(); // Exécuter la requête
$devices = $stmt->get_result()->fetch_all(MYSQLI_ASSOC); // Récupérer les résultats de la requête

// Récupérer les statistiques de consommation pour chaque appareil
$sqlStats = "SELECT device_id, SUM(consumption) as total_consumption FROM consumption_stats GROUP BY device_id";
$stmtStats = $conn->prepare($sqlStats);
$stmtStats->execute();
$consumptionStats = $stmtStats->get_result()->fetch_all(MYSQLI_ASSOC); // Récupère les statistiques de consommation

// Ajouter des points à l'utilisateur pour chaque connexion
$pointsForConnection = 0.25;  // Points donnés pour chaque connexion
$sql = "UPDATE users SET points = points + ? WHERE id = ?"; // Mise à jour des points dans la base de données
$stmt = $conn->prepare($sql);
$stmt->bind_param("di", $pointsForConnection, $userId);
$stmt->execute(); // Exécuter la mise à jour des points

// Maj le niveau de l'utilisateur en fonction des points accumulés
updateUserLevel($userId); // Appelle une fonction pour maj le niveau de l'utilisateur
?>


<?php include '../Principale/header.php'; ?> 

<div class="container py-5">
    <h2 class="text-center mb-4">Tableau de Bord des Objets Connectés</h2> 

    <?php if ($level == 'advanced' || $level == 'expert'): ?> <!-- Affiche les boutons uniquement si l'utilisateur est 'advanced' ou 'expert' -->
        <a href="add_device.php" class="btn btn-primary mb-3">➕ Ajouter un objet connecté</a>
        <a href="gestion.php" class="btn btn-primary mb-3">Détails de vos objets connectés</a>
    <?php endif; ?>

    <!-- Formulaire de recherche avec filtres pour les appareils -->
    <form method="GET" class="mb-3">
        <div class="row">
            <div class="col-md-3">
                <input type="text" name="search" class="form-control" placeholder="Rechercher un objet" value="<?= htmlspecialchars($search) ?>"> <!-- Champ de recherche -->
            </div>
            <div class="col-md-2">
                <select name="type" class="form-select"> <!-- Sélecteur pour le type d'objet -->
                    <option value="">Type</option>
                    <option value="thermostat" <?= $type == 'thermostat' ? 'selected' : '' ?>>Thermostat</option>
                    <option value="capteur" <?= $type == 'capteur' ? 'selected' : '' ?>>Capteur</option>
                    <option value="camera" <?= $type == 'camera' ? 'selected' : '' ?>>Caméra</option>
                    <option value="montre" <?= $type == 'montre' ? 'selected' : '' ?>>Montre Connectée</option>
                </select>
            </div>
            <div class="col-md-2">
                <select name="status" class="form-select"> <!-- Sélecteur pour l'état des appareils -->
                    <option value="">État</option>
                    <option value="active" <?= $status == 'active' ? 'selected' : '' ?>>Actif</option>
                    <option value="inactive" <?= $status == 'inactive' ? 'selected' : '' ?>>Inactif</option>
                </select>
            </div>
            <div class="col-md-2">
                <select name="connectivity" class="form-select"> <!-- Sélecteur pour la connectivité des appareils -->
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

    <!-- Liste des appareils connectés -->
    <ul class="list-group">
        <?php foreach ($devices as $device): ?>
            <li class="list-group-item d-flex justify-content-between align-items-center">
                <div>
                    <strong><?= htmlspecialchars($device['name']) ?></strong> - <?= htmlspecialchars($device['type']) ?> <!-- Affiche le nom et le type de l'appareil -->
                </div>
                <div class="d-flex">
                    <!-- Bouton ON/OFF (affiché si l'utilisateur est 'advanced' ou 'expert') -->
                    <?php if ($level == 'advanced' || $level == 'expert'): ?>
                        <button class="btn toggle-btn btn-sm <?= ($device['status'] == 'active') ? 'btn-success' : 'btn-danger' ?>" 
                            data-id="<?= $device['id'] ?>" data-status="<?= $device['status'] ?>">
                            <?= ($device['status'] == 'active') ? 'Allumer' : 'Éteindre' ?>
                        </button>
                    <?php endif; ?>

                    <!-- Bouton Détails - Ouvre un modal pour afficher les informations de l'appareil -->
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

    <!-- Boutons supplémentaires -->
    <div class="mt-4">
        <a href="logout.php" class="btn btn-danger">Déconnexion</a> 

        <?php if ($level == 'expert'): ?>
            <a href="manage_family.php" class="btn btn-info">👨‍👩‍👧 Gérer ma famille</a> <!-- Lien vers la gestion de la famille si l'utilisateur est un expert -->
        <?php endif; ?>
    </div>
</div>

<!-- Modal pour afficher les détails d'un appareil -->
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

            // Envoyer une requête AJAX pour maj l'état dans la base de données
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
                    this.setAttribute('data-status', newStatus);  // Maj de l'attribut de statut
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

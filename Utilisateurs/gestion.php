<?php
session_start();
include '../BDD-Gestion/functions.php';

$userId = $_SESSION['user_id'];
$userLevel = getUserLevel($userId);

// Vérifier si l'utilisateur a le niveau approprié (avancé ou expert)
if ($userLevel != 'advanced' && $userLevel != 'expert') {
    header("Location: ../Principale/index.php");
    exit();  // Si l'utilisateur n'a pas accès, rediriger vers la page principale
}

// Récupérer les objets connectés
$sql = "SELECT * FROM devices WHERE user_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $userId);
$stmt->execute();
$devices = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

// Récupérer les rapports d'utilisation des objets
$sqlStats = "SELECT device_id, SUM(consumption) AS total_consumption, AVG(current_temperature) AS avg_temperature FROM consumption_stats GROUP BY device_id";
$stmtStats = $conn->prepare($sqlStats);
$stmtStats->execute();
$consumptionStats = $stmtStats->get_result()->fetch_all(MYSQLI_ASSOC);

?>

<?php include '../Principale/header.php'; ?>

<div class="container mt-5">
    <h2 class="text-center mb-4">⚙️ Gestion des Objets Connectés</h2>

    <!-- Ajouter un objet connecté -->
    <div class="card mb-4">
        <div class="card-header bg-primary text-white">
            Ajouter un nouvel objet connecté
        </div>
        <div class="card-body">
            <a href="add_device.php" class="btn btn-success">Ajouter un Objet Connecté</a>
            <a href="rapport.php" class="btn btn-success">Rapport détaillés</a>
        </div>
    </div>

    <!-- Liste des objets connectés -->
    <div class="card mb-4">
        <div class="card-header bg-success text-white">
            Objets Connectés
        </div>
        <div class="card-body">
            <table class="table table-bordered">
                <thead class="table-dark">
                    <tr>
                        <th>ID</th>
                        <th>Nom</th>
                        <th>Type</th>
                        <th>Statut</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($devices as $device): ?>
                        <tr>
                            <td><?= htmlspecialchars($device['id']) ?></td>
                            <td><?= htmlspecialchars($device['name']) ?></td>
                            <td><?= htmlspecialchars($device['type']) ?></td>
                            <td>
                                <span class="badge <?= $device['status'] == 'active' ? 'bg-success' : 'bg-danger' ?>">
                                    <?= htmlspecialchars($device['status']) ?>
                                </span>
                            </td>
                            <td>
                                <a href="edit_device.php?id=<?= $device['id'] ?>" class="btn btn-warning btn-sm">Modifier</a>
                                <a href="delete_device.php?id=<?= $device['id'] ?>" class="btn btn-danger btn-sm">Supprimer</a>
                                <button class="btn toggle-btn btn-sm <?= ($device['status'] == 'active') ? 'btn-success' : 'btn-danger' ?>" 
                                    data-id="<?= $device['id'] ?>" data-status="<?= $device['status'] ?>">
                                    <?= ($device['status'] == 'active') ? 'Allumer' : 'Éteindre' ?>
                                </button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Rapports de consommation -->
    <div class="row">
        <div class="col-md-6">
            <h3>Consommation Totale</h3>
            <canvas id="totalConsumptionChart"></canvas>
        </div>
        <div class="col-md-6">
            <h3>Température Moyenne</h3>
            <canvas id="temperatureChart"></canvas>
        </div>
    </div>


</div>

<?php include '../Principale/footer.php'; ?>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Graphique Consommation Totale
    const consumptionCtx = document.getElementById('totalConsumptionChart').getContext('2d');
    const consumptionData = {
        labels: [<?php foreach ($consumptionStats as $stat) { echo "'" . $stat['device_id'] . "',"; } ?>],
        datasets: [{
            label: 'Consommation Totale (kWh)',
            data: [<?php foreach ($consumptionStats as $stat) { echo $stat['total_consumption'] . ","; } ?>],
            backgroundColor: "rgba(75, 192, 192, 0.6)"
        }]
    };

    new Chart(consumptionCtx, {
        type: 'bar',
        data: consumptionData,
        options: {
            responsive: true,
            scales: {
                y: { beginAtZero: true }
            }
        }
    });

    // Graphique de Température Moyenne
    const temperatureCtx = document.getElementById('temperatureChart').getContext('2d');
    const temperatureData = {
        labels: [<?php foreach ($consumptionStats as $stat) { echo "'" . $stat['device_id'] . "',"; } ?>],
        datasets: [{
            label: 'Température Moyenne (°C)',
            data: [<?php foreach ($consumptionStats as $stat) { echo $stat['avg_temperature'] . ","; } ?>],
            backgroundColor: "rgba(153, 102, 255, 0.6)"
        }]
    };

    new Chart(temperatureCtx, {
        type: 'line',
        data: temperatureData,
        options: {
            responsive: true,
            scales: {
                y: { beginAtZero: true }
            }
        }
    });
});

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


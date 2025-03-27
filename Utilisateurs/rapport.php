<?php
session_start();
include '../BDD-Gestion/functions.php';

// Vérifier si l'utilisateur est connecté
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Récupérer l'ID de l'utilisateur
$userId = $_SESSION['user_id'];
$userLevel = getUserLevel($userId);

// Vérifier si l'utilisateur a le niveau approprié (avancé ou expert)
if ($userLevel != 'advanced' && $userLevel != 'expert') {
    header("Location: ../Principale/index.php");
    exit();  // Si l'utilisateur n'a pas accès, rediriger vers la page principale
}

// Récupérer les rapports de consommation quotidienne ou hebdomadaire
$sql = "SELECT cs.device_id, d.name, 
                SUM(cs.consumption) as total_consumption, 
                AVG(cs.current_temperature) as avg_temperature, 
                COUNT(*) as usage_count,
                d.target_temperature
        FROM consumption_stats cs
        JOIN devices d ON cs.device_id = d.id
        WHERE cs.user_id = ? 
        AND cs.date BETWEEN ? AND ?
        GROUP BY cs.device_id";

// Calculer les dates de début et de fin pour la période souhaitée
$startDate = date('Y-m-d', strtotime('-7 days')); // 7 derniers jours
$endDate = date('Y-m-d', strtotime('today')); // Aujourd'hui

$stmt = $conn->prepare($sql);
$stmt->bind_param("iss", $userId, $startDate, $endDate); // Assurez-vous que les dates sont bien passées en format 'YYYY-MM-DD'
$stmt->execute();
$consumptionStats = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

// Vérification des données récupérées
if (!$consumptionStats) {
    die("Aucune donnée de consommation trouvée.");
}

// Récupérer les objets inefficaces (par exemple, avec une consommation élevée)
$sqlInefficient = "SELECT device_id, SUM(consumption) as total_consumption
                   FROM consumption_stats 
                   GROUP BY device_id
                   HAVING total_consumption > ?";

$stmtInefficient = $conn->prepare($sqlInefficient);
$threshold = 100;  // Seuil de consommation élevé
$stmtInefficient->bind_param("i", $threshold);
$stmtInefficient->execute();
$inefficientDevices = $stmtInefficient->get_result()->fetch_all(MYSQLI_ASSOC);

// Récupérer l'historique des objets connectés
$sqlHistory = "SELECT cs.device_id, d.name, cs.usage_time, cs.consumption, d.target_temperature, cs.current_temperature
               FROM consumption_stats cs
               JOIN devices d ON cs.device_id = d.id
               WHERE cs.user_id = ?
               ORDER BY cs.usage_time DESC";
$stmtHistory = $conn->prepare($sqlHistory);
$stmtHistory->bind_param("i", $userId);
$stmtHistory->execute();
$historyData = $stmtHistory->get_result()->fetch_all(MYSQLI_ASSOC);

// Récupérer les données sur le niveau de batterie des objets
$sqlBattery = "SELECT d.name, d.battery_status
               FROM devices d
               WHERE d.user_id = ?";
$stmtBattery = $conn->prepare($sqlBattery);
$stmtBattery->bind_param("i", $userId);
$stmtBattery->execute();
$batteryStats = $stmtBattery->get_result()->fetch_all(MYSQLI_ASSOC);

?>

<?php include '../Principale/header.php'; ?>

<div class="container mt-5">
    <h2>Rapports d'utilisation des objets connectés</h2>

    <!-- Rapport de Consommation -->
    <div class="card mb-4">
        <div class="card-header">Rapport de Consommation</div>
        <div class="card-body">
            <canvas id="consumptionChart" style="max-width: 500px; max-height: 500px;"></canvas>
        </div>
    </div>

    <!-- Température Moyenne -->
    <div class="card mb-4">
        <div class="card-header">Température Moyenne</div>
        <div class="card-body">
            <canvas id="temperatureChart" style="max-width: 500px; max-height: 500px;"></canvas>
        </div>
    </div>
    
    <!-- Rapport de Niveau de Batterie -->
    <div class="card mb-4">
        <div class="card-header">État de la Batterie</div>
        <div class="card-body">
            <canvas id="batteryChart" style="max-width: 500px; max-height: 500px;"></canvas>
        </div>
    </div>

    <!-- Historique des Données -->
    <div class="card mb-4">
        <div class="card-header">Historique des Données</div>
        <div class="card-body">
            <table class="table">
                <thead>
                    <tr>
                        <th>Nom de l'objet</th>
                        <th>Temps d'utilisation</th>
                        <th>Consommation (kWh)</th>
                        <th>Message Température</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($historyData as $data): ?>
                        <tr>
                            <td><?= htmlspecialchars($data['name']) ?></td>
                            <td><?= htmlspecialchars($data['usage_time']) ?></td>
                            <td><?= htmlspecialchars($data['consumption']) ?> kWh</td>
                            <td>
                                <?php 
                                    // Comparaison de la température actuelle avec la température cible
                                    if ($data['current_temperature'] < $data['target_temperature'] - 5) {
                                        echo "Température trop basse ! (Plus de 5°C en dessous de la cible)";
                                    } elseif ($data['current_temperature'] > $data['target_temperature'] + 5) {
                                        echo "Température trop élevée ! (Plus de 5°C au-dessus de la cible)";
                                    } elseif ($data['current_temperature'] < $data['target_temperature']) {
                                        echo "Température légèrement trop basse.";
                                    } elseif ($data['current_temperature'] > $data['target_temperature']) {
                                        echo "Température légèrement trop élevée.";
                                    } else {
                                         echo "Température idéale.";
                                    }
                                ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Graphique de consommation totale (Camembert)
    const consumptionCtx = document.getElementById('consumptionChart').getContext('2d');
    const consumptionData = {
        labels: [<?php foreach ($consumptionStats as $data) { echo "'" . $data['name'] . "',"; } ?>],
        datasets: [{
            label: 'Consommation Totale (kWh)',
            data: [<?php foreach ($consumptionStats as $data) { echo $data['total_consumption'] . ","; } ?>],
            backgroundColor: [
                "rgba(75, 192, 192, 0.6)",
                "rgba(153, 102, 255, 0.6)",
                "rgba(255, 159, 64, 0.6)"
            ]
        }]
    };
    new Chart(consumptionCtx, {
        type: 'pie',  // Changement ici pour un graphique camembert
        data: consumptionData,
        options: {
            responsive: true
        }
    });

    // Graphique de température moyenne (Camembert)
    const temperatureCtx = document.getElementById('temperatureChart').getContext('2d');
    const temperatureData = {
        labels: [<?php foreach ($consumptionStats as $data) { echo "'" . $data['name'] . "',"; } ?>],
        datasets: [{
            label: 'Température Moyenne (°C)',
            data: [<?php foreach ($consumptionStats as $data) { echo $data['avg_temperature'] . ","; } ?>],
            backgroundColor: [
                "rgba(153, 102, 255, 0.6)",
                "rgba(75, 192, 192, 0.6)",
                "rgba(255, 159, 64, 0.6)"
            ]
        }]
    };
    new Chart(temperatureCtx, {
        type: 'pie',  // Changement ici pour un graphique camembert
        data: temperatureData,
        options: {
            responsive: true
        }
    });

    // Graphique de niveau de batterie (Camembert)
    const batteryCtx = document.getElementById('batteryChart').getContext('2d');
    const batteryData = {
        labels: [<?php foreach ($batteryStats as $data) { echo "'" . $data['name'] . "',"; } ?>],
        datasets: [{
            label: 'Niveau de Batterie (%)',
            data: [<?php foreach ($batteryStats as $data) { echo $data['battery_status'] . ","; } ?>],
            backgroundColor: [
                "rgba(255, 99, 132, 0.6)",
                "rgba(54, 162, 235, 0.6)",
                "rgba(255, 206, 86, 0.6)"
            ]
        }]
    };
    new Chart(batteryCtx, {
        type: 'pie',  // Changement ici pour un graphique camembert
        data: batteryData,
        options: {
            responsive: true
        }
    });
});
</script>

<?php include '../Principale/footer.php'; ?>

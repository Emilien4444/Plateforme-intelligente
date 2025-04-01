<?php
session_start();
include '../BDD-Gestion/functions.php'; 

// Vérification si l'utilisateur est authentifié
$userId = $_SESSION['user_id']; // Récupère l'ID de l'utilisateur connecté
$userLevel = getUserLevel($userId); // Récupère le niveau de l'utilisateur 

// Verifie son niveau
if ($userLevel != 'advanced' && $userLevel != 'expert') {
    header("Location: ../Principale/index.php");
    exit();  // Redirection vers la page principale si l'utilisateur n'a pas les droits d'accès
}

// Récupérer tous les appareils associés à l'utilisateur
$sql = "SELECT * FROM devices WHERE user_id = ?"; // Requête SQL 
$stmt = $conn->prepare($sql); 
$stmt->bind_param("i", $userId); // Lie l'ID utilisateur à la requête
$stmt->execute(); 
$devices = $stmt->get_result()->fetch_all(MYSQLI_ASSOC); // Récupère tous les appareils sous forme de tableau associatif

// Récupérer les statistiques de consommation des appareils
$sqlStats = "SELECT device_id, SUM(consumption) AS total_consumption, AVG(current_temperature) AS avg_temperature FROM consumption_stats GROUP BY device_id";
$stmtStats = $conn->prepare($sqlStats); 
$stmtStats->execute(); 
$consumptionStats = $stmtStats->get_result()->fetch_all(MYSQLI_ASSOC); // Récupère les statistiques sous forme de tableau associatif
?>

<?php include '../Principale/header.php'; ?>


<div class="container mt-5">
    <h2 class="text-center mb-4">⚙️ Gestion des Objets Connectés</h2>

    <!-- Ajouter un nouvel objet connecté -->
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
            <div id="app">
                <table class="table table-bordered">
                    <thead class="table-dark">
                        <tr>
                            <th>ID</th>
                            <th>Nom</th>
                            <th>Type</th>
                            <th>Statut</th>
                            <th>Batterie (%)</th>
                            <th>Actions</th>
                            <th>Modifier</th> 
                        </tr>
                    </thead>
                    <tbody>
                        <!-- Utilisation de Vue.js pour afficher dynamiquement les appareils -->
                        <tr v-for="device in devices" :key="device.id">
                            <td>{{ device.id }}</td>
                            <td>{{ device.name }}</td>
                            <td>{{ device.type }}</td>
                            <td>
                                <!-- Affichage du statut (actif ou inactif) avec une badge colorée -->
                                <span class="badge" :class="device.status === 'active' ? 'bg-success' : 'bg-danger'">
                                    {{ device.status }}
                                </span>
                            </td>
                            <td>
                                <!-- Affichage du niveau de batterie -->
                                <span :class="device.status === 'active' ? 'text-success' : 'text-danger'">
                                    {{ device.battery_status }}%
                                </span>
                            </td>
                            <td>
                                <!-- Bouton pour basculer l'état de l'appareil (actif/inactif) -->
                                <button class="btn toggle-btn btn-sm" :class="device.status === 'active' ? 'btn-success' : 'btn-danger'" 
                                        @click="toggleDeviceStatus(device)">
                                    {{ device.status === 'active' ? 'Éteindre' : 'Allumer' }}
                                </button>
                            </td>
                            <td>
                                <!-- Bouton pour modifier l'appareil -->
                                <a :href="'edit_device.php?id=' + device.id" class="btn btn-warning btn-sm ms-2">Modifier</a> 
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Graphiques des rapports de consommation -->
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

<script src="https://cdn.jsdelivr.net/npm/vue@2.6.14/dist/vue.js"></script> <!-- Inclusion de Vue.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script> <!-- Inclusion de Chart.js pour les graphiques -->

<script>
// Passer les données des appareils PHP à Vue.js
const devices = <?php echo json_encode($devices); ?>;

new Vue({
    el: '#app',
    data: {
        devices: devices, // Stocke les données des appareils
        intervals: {} // Intervalles pour gérer la batterie, la température et la consommation
    },
    methods: {
        // Méthode pour basculer l'état de l'appareil entre 'active' et 'inactive'
        toggleDeviceStatus(device) {
            // Conserver le statut original pour revenir en arrière si nécessaire
            const originalStatus = device.status;
            const newStatus = originalStatus === 'active' ? 'inactive' : 'active';

            // Mise à jour optimiste de l'UI
            device.status = newStatus;

            // Envoi de la requête pour mettre à jour le statut dans la BDD
            fetch('update_device_status.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    device_id: device.id,
                    status: newStatus
                })
            })
            .then(response => response.json())
            .then(data => {
                if (!data.success) {
                    alert('Échec de la mise à jour du statut.');
                    device.status = originalStatus; // Revert UI en cas d'erreur
                } else {
                    console.log('Statut mis à jour avec succès');
                }
            })
            .catch(error => {
                console.error('Erreur:', error);
                device.status = originalStatus; // Revert UI en cas d'échec
                alert('Erreur de mise à jour : ' + error.message);
            });

            // Lancer ou arrêter les intervalles pour gérer la batterie, la température, et la consommation
            if (newStatus === 'active') {
                this.startBatteryDrain(device); // Décharger la batterie
                this.startTemperatureIncrease(device); // Augmenter la température
                this.startConsumptionIncrease(device); // Augmenter la consommation
            } else {
                this.startBatteryCharge(device); // Charger la batterie
                this.startTemperatureRecovery(device); // Remonter la température
                this.startConsumptionDecrease(device); // Diminuer la consommation
            }
        },

        // Méthode pour décharger la batterie
        startBatteryDrain(device) {
            this.intervals[device.id] = setInterval(() => {
                if (device.battery_status > 0) {
                    device.battery_status -= 1; // Réduire le niveau de batterie de 1
                    this.updateDeviceBatteryStatus(device); // Mettre à jour la batterie dans la base de données
                } else {
                    clearInterval(this.intervals[device.id]); // Arrêter si la batterie est vide
                }
            }, 1000); // Décharge la batterie toutes les secondes
        },

        // Méthode pour charger la batterie
        startBatteryCharge(device) {
            this.intervals[device.id] = setInterval(() => {
                if (device.battery_status < 100) {
                    device.battery_status += 1; // Augmenter le niveau de batterie
                    this.updateDeviceBatteryStatus(device); // Mettre à jour la batterie dans la base de données
                } else {
                    clearInterval(this.intervals[device.id]); // Arrêter si la batterie est pleine
                }
            }, 1000); // Charge la batterie toutes les secondes
        },

        // Méthode pour mettre à jour la batterie dans la base de données
        updateDeviceBatteryStatus(device) {
            fetch('update_device_battery.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    device_id: device.id,
                    battery_status: device.battery_status
                })
            })
            .then(response => response.json())
            .then(data => {
                if (!data.success) {
                    alert('Erreur de mise à jour du niveau de batterie.');
                }
            })
            .catch(error => console.error('Erreur:', error));
        },

    }
});

    // Code pour générer les graphiques avec Chart.js
document.addEventListener('DOMContentLoaded', function() {
    // Graphique de la consommation totale des appareils
    const consumptionCtx = document.getElementById('totalConsumptionChart').getContext('2d');
    const consumptionData = {
        labels: [<?php foreach ($consumptionStats as $data) { echo "'" . $data['device_id'] . "',"; } ?>],
        datasets: [{
            label: 'Consommation Totale (kWh)',
            data: [<?php foreach ($consumptionStats as $data) { echo $data['total_consumption'] . ","; } ?>],
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

    // Graphique de la température moyenne
    const temperatureCtx = document.getElementById('temperatureChart').getContext('2d');
    const temperatureData = {
        labels: [<?php foreach ($consumptionStats as $data) { echo "'" . $data['device_id'] . "',"; } ?>],
        datasets: [{
            label: 'Température Moyenne (°C)',
            data: [<?php foreach ($consumptionStats as $data) { echo $data['avg_temperature'] . ","; } ?>],
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
</script>
</body>
</html>

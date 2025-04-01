<?php
session_start();
include '../BDD-Gestion/functions.php'; // Inclut les fonctions nécessaires pour interagir avec la BDD

// Vérifie si l'utilisateur est connecté
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php"); // Si l'utilisateur n'est pas connecté -> redirige vers la page de connexion
    exit();
}

$userId = $_SESSION['user_id']; // Récupère l'ID de l'utilisateur connecté
$userLevel = getUserLevel($userId); // Récupère le niveau d'accès de l'utilisateur (ex: "advanced" ou "expert")

// Vérifie si l'utilisateur a le niveau d'accès approprié pour voir le rapport
if ($userLevel != 'advanced' && $userLevel != 'expert') {
    header("Location: ../Principale/index.php"); // Si l'utilisateur n'a pas accès -> redirigé vers la page principale
    exit();
}

// Récupérer les rapports de consommation sans filtre de date pour tjr afficher quelque chose 
$sql = "SELECT cs.device_id, d.name, 
                SUM(cs.consumption) as total_consumption, 
                AVG(cs.current_temperature) as avg_temperature, 
                COUNT(*) as usage_count,
                d.target_temperature
        FROM consumption_stats cs
        JOIN devices d ON cs.device_id = d.id
        WHERE cs.user_id = ? 
        GROUP BY cs.device_id";  // Sélectionne les statistiques de consommation et les informations sur les appareils de l'utilisateur
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $userId); // Lier l'ID de l'utilisateur à la requête
$stmt->execute();  
$consumptionStats = $stmt->get_result()->fetch_all(MYSQLI_ASSOC); // Récupère les résultats sous forme de tableau associatif

// Vérification des données récupérées
if (!$consumptionStats) {
    die("Aucune donnée de consommation trouvée."); // Si aucune donnée n'est trouvée -> message d'erreur est affiché
}

// Récupérer l'historique des objets connectés
$sqlHistory = "SELECT cs.device_id, d.name, cs.usage_time, cs.consumption, d.target_temperature, cs.current_temperature
               FROM consumption_stats cs
               JOIN devices d ON cs.device_id = d.id
               WHERE cs.user_id = ?
               ORDER BY cs.usage_time DESC";  // Récupère l'historique des appareils avec leur consommation et température
$stmtHistory = $conn->prepare($sqlHistory);
$stmtHistory->bind_param("i", $userId); // Lier l'ID de l'utilisateur à la requête
$stmtHistory->execute();  // Exécute la requête SQL
$historyData = $stmtHistory->get_result()->fetch_all(MYSQLI_ASSOC); // Récupère les résultats sous forme de tableau associatif

// Récupérer les données sur le niveau de batterie des appareils
$sqlBattery = "SELECT d.name, d.battery_status
               FROM devices d
               WHERE d.user_id = ?";  // Récupère les niveaux de batterie des appareils
$stmtBattery = $conn->prepare($sqlBattery);
$stmtBattery->bind_param("i", $userId); // Lier l'ID de l'utilisateur à la requête
$stmtBattery->execute();  // Exécute la requête SQL
$batteryStats = $stmtBattery->get_result()->fetch_all(MYSQLI_ASSOC); // Récupère les résultats sous forme de tableau associatif
?>

<?php include '../Principale/header.php'; ?>  <!-- Inclut l'en-tête de la page -->

<div class="container mt-5">
    <h2>Rapports d'utilisation des objets connectés</h2>

    <!-- Rapport de Consommation -->
    <div class="card mb-4">
        <div class="card-header">Rapport de Consommation</div>
        <div class="card-body">
            <canvas id="consumptionChart" style="max-width: 500px; max-height: 500px;"></canvas> <!-- Graphique pour la consommation totale -->
        </div>
    </div>

    <!-- Température Moyenne -->
    <div class="card mb-4">
        <div class="card-header">Température Moyenne</div>
        <div class="card-body">
            <canvas id="temperatureChart" style="max-width: 500px; max-height: 500px;"></canvas> <!-- Graphique pour la température moyenne -->
        </div>
    </div>
    
    <!-- Rapport de Niveau de Batterie -->
    <div class="card mb-4">
        <div class="card-header">État de la Batterie</div>
        <div class="card-body">
            <canvas id="batteryChart" style="max-width: 500px; max-height: 500px;"></canvas> <!-- Graphique pour le niveau de batterie -->
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
// Après le chargement du document, générer les graphiques
document.addEventListener('DOMContentLoaded', function() {
    // Fonction pour générer une couleur aléatoire en format RGBA avec transparence
    function randomColor() {
        const r = Math.floor(Math.random() * 256); // Génère une valeur aléatoire pour le rouge
        const g = Math.floor(Math.random() * 256); // Génère une valeur aléatoire pour le vert
        const b = Math.floor(Math.random() * 256); // Génère une valeur aléatoire pour le bleu
        return `rgba(${r}, ${g}, ${b}, 0.6)`; // Retourne la couleur en format rgba avec une transparence de 60%
    }

    // Graphique de consommation totale (Camembert) : Affiche la consommation totale de chaque appareil
    const consumptionCtx = document.getElementById('consumptionChart').getContext('2d'); // Sélectionne le canevas pour afficher le graphique
    const consumptionData = {
        labels: [<?php foreach ($consumptionStats as $data) { echo "'" . $data['name'] . "',"; } ?>], // Récupère les noms des appareils à afficher comme labels
        datasets: [{
            label: 'Consommation Totale (kWh)', // Titre du graphique
            data: [<?php foreach ($consumptionStats as $data) { echo $data['total_consumption'] . ","; } ?>], // Récupère les données de consommation totale
            backgroundColor: [] // Tableau vide pour stocker les couleurs dynamiques pour chaque segment
        }]
    };

    // Générer une couleur différente pour chaque segment (chaque appareil)
    consumptionData.datasets[0].backgroundColor = consumptionData.labels.map(() => randomColor()); // Applique une couleur aléatoire pour chaque segment du graphique

    // Création du graphique de consommation totale
    new Chart(consumptionCtx, {
        type: 'pie',  // Type de graphique ->  Camembert
        data: consumptionData, // Données à afficher dans le graphique
        options: {
            responsive: true // Rendre le graphique responsive pour s'adapter à la taille de l'écran
        }
    });

    // Graphique de température moyenne (Camembert) : Affiche la température moyenne de chaque appareil
    const temperatureCtx = document.getElementById('temperatureChart').getContext('2d'); // Sélectionne le canevas pour afficher le graphique
    const temperatureData = {
        labels: [<?php foreach ($consumptionStats as $data) { echo "'" . $data['name'] . "',"; } ?>], // Récupère les noms des appareils
        datasets: [{
            label: 'Température Moyenne (°C)', // Titre du graphique
            data: [<?php foreach ($consumptionStats as $data) { echo $data['avg_temperature'] . ","; } ?>], // Récupère les températures moy des appareils
            backgroundColor: [] // Tableau vide pour stocker les couleurs dynamiques
        }]
    };

    // Générer une couleur différente pour chaque segment (chaque appareil)
    temperatureData.datasets[0].backgroundColor = temperatureData.labels.map(() => randomColor()); // Applique une couleur aléatoire pour chaque segment du graphique

    // Création du graphique de température moy
    new Chart(temperatureCtx, {
        type: 'pie',  // Type de graphique ->  Camembert
        data: temperatureData, // Données à afficher dans le graphique
        options: {
            responsive: true
        }
    });

    // Graphique de niveau de batterie (Camembert) : Affiche le niveau de batterie de chaque appareil
    const batteryCtx = document.getElementById('batteryChart').getContext('2d'); // Sélectionne le canevas pour afficher le graphique
    const batteryData = {
        labels: [<?php foreach ($batteryStats as $data) { echo "'" . $data['name'] . "',"; } ?>], // Récupère les noms des appareils
        datasets: [{
            label: 'Niveau de Batterie (%)', // Libellé du graphique
            data: [<?php foreach ($batteryStats as $data) { echo $data['battery_status'] . ","; } ?>], // Récupère les niveaux de batterie des appareils
            backgroundColor: [] // Tableau vide pour stocker les couleurs dynamiques
        }]
    };

    // Générer une couleur différente pour chaque segment (chaque appareil)
    batteryData.datasets[0].backgroundColor = batteryData.labels.map(() => randomColor()); // Applique une couleur aléatoire pour chaque segment du graphique

    // Création du graphique de niveau de batterie
    new Chart(batteryCtx, {
        type: 'pie',  // Type de graphique -> Camembert
        data: batteryData, // Données à afficher dans le graphique
        options: {
            responsive: true
        }
    });
});
</script>

<?php include '../Principale/footer.php'; ?>

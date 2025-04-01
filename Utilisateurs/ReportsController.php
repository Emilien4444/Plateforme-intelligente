<?php
session_start(); // Démarre la session

// Vérification de la session utilisateur
if (!isset($_SESSION['user_id'])) { // Si l'utilisateur n'est pas connecté -> redirige vers la page de connexion
    header("Location: login.php"); // Redirige vers la page de connexion
    exit(); 
}

$userId = $_SESSION['user_id']; // Récupère l'ID de l'utilisateur connecté
$level = getUserLevel($userId); // Récupère le niveau d'accès de l'utilisateur (ex: "advanced" ou "expert")

// Si l'utilisateur n'a pas le niveau "expert" -> on le redirige
if ($level != 'expert') { // Vérifie si l'utilisateur n'a pas le niveau approprié
    header("Location: index.php"); // Si l'utilisateur n'a pas accès -> redirige vers la page principale
    exit(); 
}


// Requête pour obtenir les statistiques de consommation par objet connecté
$sqlStats = "
    SELECT 
        d.name, 
        d.type, 
        SUM(cs.consumption) AS total_consumption, 
        COUNT(cs.device_id) AS usage_count, 
        AVG(cs.current_temperature) AS avg_temperature
    FROM consumption_stats cs
    JOIN devices d ON cs.device_id = d.id
    WHERE d.user_id = ?
    GROUP BY d.id
";
$stmt = $conn->prepare($sqlStats); 
$stmt->bind_param("i", $userId); // Lier l'ID de l'utilisateur à la requête SQL
$stmt->execute();  
$usageStats = $stmt->get_result()->fetch_all(MYSQLI_ASSOC); // Récupère les résultats sous forme de tableau associatif


// Historique des données des objets connectés
$sqlHistory = "
    SELECT 
        d.name, 
        cs.usage_time, 
        cs.consumption 
    FROM consumption_stats cs
    JOIN devices d ON cs.device_id = d.id
    WHERE d.user_id = ?
    ORDER BY cs.usage_time DESC
";
$stmtHistory = $conn->prepare($sqlHistory);
$stmtHistory->bind_param("i", $userId); // Lier l'ID de l'utilisateur à la requête
$stmtHistory->execute();  
$historyData = $stmtHistory->get_result()->fetch_all(MYSQLI_ASSOC); // Récupère les résultats sous forme de tableau associatif


// Génération d'un rapport CSV
if (isset($_POST['export_csv'])) {
    $csvFile = fopen('php://output', 'w'); // Ouvre un flux pour écrire le fichier CSV
    header('Content-Type: text/csv'); // Définit l'en-tête pour le type de contenu CSV
    header('Content-Disposition: attachment;filename="rapport_statistiques.csv"'); // Définit le nom du fichier CSV

    fputcsv($csvFile, ['Nom', 'Type', 'Consommation Totale', 'Nombre d\'Utilisation', 'Température Moyenne']); // Écrit les en-têtes du CSV

    foreach ($usageStats as $stat) {
        fputcsv($csvFile, $stat); // Écrit les données des statistiques dans le fichier CSV
    }
    fclose($csvFile); // Ferme le fichier CSV
    exit(); // Interrompt l'exécution après le téléchargement du fichier
}
?>

<?php include '../Principale/header.php'; ?>

<div class="container py-5">
    <h2 class="text-center mb-4">Rapports de Consommation et d'Utilisation des Objets Connectés</h2>

    <!-- Statistiques d'utilisation -->
    <div class="card mb-4">
        <div class="card-header bg-primary text-white">
            Statistiques des Objets Connectés
        </div>
        <div class="card-body">
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>Nom de l'objet</th>
                        <th>Type</th>
                        <th>Consommation Totale (kWh)</th>
                        <th>Nombre d'Utilisation</th>
                        <th>Température Moyenne</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($usageStats as $stat): ?>
                        <tr>
                            <td><?= htmlspecialchars($stat['name']) ?></td>
                            <td><?= htmlspecialchars($stat['type']) ?></td>
                            <td><?= htmlspecialchars($stat['total_consumption']) ?> kWh</td>
                            <td><?= htmlspecialchars($stat['usage_count']) ?></td>
                            <td><?= htmlspecialchars($stat['avg_temperature']) ?> °C</td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>

            <!-- Bouton pour exporter le rapport au format CSV -->
            <form method="POST">
                <button type="submit" name="export_csv" class="btn btn-success">Exporter en CSV</button>
            </form>
        </div>
    </div>

    <!-- Historique des Données -->
    <div class="card">
        <div class="card-header bg-success text-white">
            Historique des Données des Objets Connectés
        </div>
        <div class="card-body">
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>Nom de l'objet</th>
                        <th>Temps d'Utilisation</th>
                        <th>Consommation (kWh)</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($historyData as $data): ?>
                        <tr>
                            <td><?= htmlspecialchars($data['name']) ?></td>
                            <td><?= htmlspecialchars($data['usage_time']) ?></td>
                            <td><?= htmlspecialchars($data['consumption']) ?> kWh</td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php include '../Principale/footer.php'; ?>

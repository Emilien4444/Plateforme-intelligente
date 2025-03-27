<?php
session_start();
include '../BDD-Gestion/functions.php';

// Vérification de la session utilisateur
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$userId = $_SESSION['user_id'];
$level = getUserLevel($userId);

// Si l'utilisateur n'a pas le niveau "expert", on le redirige
if ($level != 'expert') {
    header("Location: index.php");
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
$stmt->bind_param("i", $userId);
$stmt->execute();
$usageStats = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

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
$stmtHistory->bind_param("i", $userId);
$stmtHistory->execute();
$historyData = $stmtHistory->get_result()->fetch_all(MYSQLI_ASSOC);

// Génération d'un rapport CSV
if (isset($_POST['export_csv'])) {
    $csvFile = fopen('php://output', 'w');
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment;filename="rapport_statistiques.csv"');

    fputcsv($csvFile, ['Nom', 'Type', 'Consommation Totale', 'Nombre d\'Utilisation', 'Température Moyenne']);

    foreach ($usageStats as $stat) {
        fputcsv($csvFile, $stat);
    }
    fclose($csvFile);
    exit();
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

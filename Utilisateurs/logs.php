<?php
session_start(); // Démarre la session
include '../BDD-Gestion/functions.php'; // Inclut les fonctions nécessaires

// Vérifier si l'utilisateur est connecté et s'il a le niveau d'accès "expert"
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php"); // Si non connecté, redirection vers la page de connexion
    exit();
}

$userId = $_SESSION['user_id']; // Récupère l'ID de l'utilisateur
$level = getUserLevel($userId); // Récupère le niveau de l'utilisateur

// Vérifier que l'utilisateur a le niveau d'accès "expert"
if ($level != 'expert') {
    header("Location: ../Principale/index.php"); // Si l'utilisateur n'a pas accès, redirection
    exit();
}

// Récupérer toutes les entrées de la table 'logs' pour l'utilisateur
$stmt = $conn->prepare("SELECT * FROM logs");
$stmt->execute();
$logs = $stmt->get_result()->fetch_all(MYSQLI_ASSOC); // Récupère toutes les entrées des logs sous forme de tableau associatif
?>

<?php include '../Principale/header.php'; ?> <!-- Inclut l'en-tête de votre site -->

<div class="container mt-5">
    <h2 class="text-center mb-4">📝 Logs des Connexions</h2>

    <!-- Affichage des logs -->
    <div class="card">
        <div class="card-header bg-primary text-white">
            Logs des Connexions
        </div>
        <div class="card-body">
            <table class="table table-bordered">
                <thead class="table-dark">
                    <tr>
                        <th>ID</th>
                        <th>Utilisateur ID</th>
                        <th>Action</th>
                        <th>Date</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($logs as $log): ?> <!-- Parcours de chaque log -->
                        <tr>
                            <td><?= htmlspecialchars($log['id']) ?></td> <!-- Affiche l'ID du log -->
                            <td><?= htmlspecialchars($log['user_id']) ?></td> <!-- Affiche l'ID de l'utilisateur -->
                            <td><?= htmlspecialchars($log['action']) ?></td> <!-- Affiche l'action effectuée -->
                            <td><?= htmlspecialchars($log['timestamp']) ?></td> <!-- Affiche la date/heure -->
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php include '../Principale/footer.php'; ?> <!-- Inclut le pied de page -->

</body>
</html>

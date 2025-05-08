<?php
session_start(); // Démarre une session 
include '../BDD-Gestion/functions.php'; 

// Vérification si l'utilisateur est authentifié
if (!isset($_SESSION['user_id'])) { 
    header("Location: login.php"); // Redirige vers la page de connexion si l'utilisateur n'est pas authentifié
    exit(); 
}

$userId = $_SESSION['user_id']; // Récupère l'ID de l'utilisateur connecté
$userLevel = getUserLevel($userId); // Vérifie le niveau de l'utilisateur 

// Si l'utilisateur n'a pas le niveau "expert" -> rediriger vers la page principale
if ($userLevel != 'expert') {
    header("Location: index.php"); // Redirige vers la page principale
    exit();
}

// Récupérer la liste des utilisateurs
$sql = "SELECT id, username, email, level, points FROM users"; // Requête SQL pour récupérer la liste des utilisateurs
$stmt = $conn->prepare($sql); 
$stmt->execute(); 
$users = $stmt->get_result()->fetch_all(MYSQLI_ASSOC); // Récupère les résultats sous forme de tableau associatif

?>

<?php include '../Principale/header.php'; ?>

<div class="container mt-5">
    <h2>Gestion des Utilisateurs</h2>

    <a href="add_user.php" class="btn btn-success mb-3">Ajouter un utilisateur</a>

    <table class="table">
        <thead>
            <tr>
                <th>ID</th>
                <th>Nom d'utilisateur</th>
                <th>Email</th>
                <th>Niveau</th>
                <th>Points</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($users as $user): ?>
                <tr>
                    <td><?= htmlspecialchars($user['id']) ?></td>
                    <td><?= htmlspecialchars($user['username']) ?></td>
                    <td><?= htmlspecialchars($user['email']) ?></td>
                    <td><?= htmlspecialchars($user['level']) ?></td>
                    <td><?= htmlspecialchars($user['points']) ?></td>
                    <td>
                        <a href="edit_user.php?id=<?= $user['id'] ?>" class="btn btn-warning btn-sm">Modifier</a>
                        <a href="delete_user.php?id=<?= $user['id'] ?>" class="btn btn-danger btn-sm">Supprimer</a>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<?php include '../Principale/footer.php'; ?>
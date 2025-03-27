<?php
session_start();
include '../BDD-Gestion/functions.php';

// Vérification si l'utilisateur est administrateur
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$userId = $_SESSION['user_id'];
$userLevel = getUserLevel($userId);

if ($userLevel != 'expert') {
    header("Location: index.php");
    exit();
}

// Récupérer la liste des utilisateurs
$sql = "SELECT id, username, email, level, points FROM users";
$stmt = $conn->prepare($sql);
$stmt->execute();
$users = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

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

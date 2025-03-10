<?php
session_start();
include '../BDD-Gestion/functions.php';

if ($_SESSION['user_role'] != 'admin') {
    die("Accès refusé.");
}

$users = getAllUsers(); // Fonction à ajouter dans functions.php
?>

<?php include '../Principale/header.php'; ?>
<div class="container">
    <h2>Panneau Administrateur</h2>
    <h3>Gestion des utilisateurs</h3>
    <table>
        <tr>
            <th>ID</th>
            <th>Email</th>
            <th>Rôle</th>
            <th>Action</th>
        </tr>
        <?php foreach ($users as $user): ?>
            <tr>
                <td><?= $user['id'] ?></td>
                <td><?= $user['email'] ?></td>
                <td><?= $user['role'] ?></td>
                <td>
                    <a href="edit_user.php?id=<?= $user['id'] ?>">Modifier</a>
                    <a href="delete_user.php?id=<?= $user['id'] ?>" style="color:red;">Supprimer</a>
                </td>
            </tr>
        <?php endforeach; ?>
    </table>
</div>
<?php include '../Principale/footer.php'; ?>

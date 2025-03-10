<?php
session_start();
if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] != 'admin') {
    header("Location: ../Principale/index.php");
    exit();
}
include '../BDD-Gestion/functions.php';

$users = $conn->query("SELECT * FROM users")->fetch_all(MYSQLI_ASSOC);
$devices = $conn->query("SELECT * FROM devices")->fetch_all(MYSQLI_ASSOC);
?>

<?php include '../Principale/header.php'; ?>
<div class="container">
    <h2>Panel Administrateur</h2>
    <h3>Utilisateurs :</h3>
    <ul>
        <?php foreach ($users as $user): ?>
            <li><?= htmlspecialchars($user['username']) ?> (<?= htmlspecialchars($user['role']) ?>)</li>
        <?php endforeach; ?>
    </ul>

    <h3>Objets Connectés :</h3>
    <ul>
        <?php foreach ($devices as $device): ?>
            <li><?= htmlspecialchars($device['name']) ?> - <?= htmlspecialchars($device['type']) ?></li>
        <?php endforeach; ?>
    </ul>
</div>
<?php include '../Principale/footer.php'; ?>

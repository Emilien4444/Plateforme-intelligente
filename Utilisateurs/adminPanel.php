<?php
session_start();
include '../BDD-Gestion/functions.php';

// Vérifier que l'utilisateur a le niveau d'accès "expert"
$userId = $_SESSION['user_id'];
$level = getUserLevel($userId);

if ($level != 'expert') {
    header("Location: ../Principale/index.php");
    exit();  // Si l'utilisateur n'a pas accès, rediriger vers la page principale
}

// Récupérer la liste des utilisateurs
$stmt = $conn->prepare("SELECT id, username, email, level FROM users");
$stmt->execute();
$users = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

// Récupérer la liste des objets connectés
$stmt = $conn->prepare("SELECT id, name, type, status FROM devices");
$stmt->execute();
$devices = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

if (isset($_GET['id'])) {
    $userIdToActivate = $_GET['id'];

    // Mettre à jour le champ `is_active` à 1 pour activer l'utilisateur
    $stmt = $conn->prepare("UPDATE users SET is_active = 1 WHERE id = ?");
    $stmt->bind_param("i", $userIdToActivate);
    $stmt->execute();

    echo "<div class='alert alert-success'>L'utilisateur a été activé avec succès !</div>";
}

// Récupérer la liste des utilisateurs inactifs
$stmt = $conn->prepare("SELECT id, username, email FROM users WHERE is_active = 0");
$stmt->execute();
$inactiveUsers = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
?>

<?php include '../Principale/header.php'; ?>

</body>
    </html>
        <div class="container mt-5">
            <h2 class="mb-4 text-center">⚙️ Panel Administrateur</h2>

            <!-- Gestion des utilisateurs -->
            <div class="card mb-4">
                <div class="card-header bg-primary text-white">
                    Utilisateurs enregistrés
                </div>
                <div class="card-body">
                    <table class="table table-bordered">
                    <thead class="table-dark">
                        <tr>
                            <th>ID</th>
                            <th>Nom d'utilisateur</th>
                            <th>Email</th>
                            <th>Rôle</th>
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
                                <td>
                                    <a href="edit_user.php?id=<?= $user['id'] ?>" class="btn btn-warning btn-sm">Modifier</a>
                                    <a href="delete_user.php?id=<?= $user['id'] ?>" class="btn btn-danger btn-sm">Supprimer</a>
                                    <!-- Ajouter un bouton pour changer le rôle -->
                                    <a href="change_user_role.php?id=<?= $user['id'] ?>" class="btn btn-info btn-sm">Changer Rôle</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                    </table>
                <a href="add_user.php" class="btn btn-success">Ajouter un Utilisateur</a>
                </div>
            </div>
        
            <div class="container mt-5">
                <h2>Gestion des Utilisateurs Inactifs</h2>
                <table class="table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Nom d'utilisateur</th>
                        <th>Email</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($inactiveUsers as $user): ?>
                        <tr>
                            <td><?= htmlspecialchars($user['id']) ?></td>
                            <td><?= htmlspecialchars($user['username']) ?></td>
                            <td><?= htmlspecialchars($user['email']) ?></td>
                            <td>
                                <a href="admin_activation.php?id=<?= $user['id'] ?>" class="btn btn-success btn-sm">Activer</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
                </table>
            </div>

            <!-- Gestion des objets connectés -->
            <div class="card">
                <div class="card-header bg-success text-white">
                    Objets Connectés
                </div>
                <div class="card-body">
                    <table class="table table-bordered">
                        <thead class="table-dark">
                            <tr>
                                <th>ID</th>
                                <th>Nom</th>
                                <th>Type</th>
                                <th>Statut</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($devices as $device): ?>
                            <tr>
                                <td><?= htmlspecialchars($device['id']) ?></td>
                                <td><?= htmlspecialchars($device['name']) ?></td>
                                <td><?= htmlspecialchars($device['type']) ?></td>
                                <td>
                                    <span class="badge <?= $device['status'] == 'actif' ? 'bg-success' : 'bg-danger' ?>">
                                        <?= htmlspecialchars($device['status']) ?>
                                    </span>
                                </td>
                                <td>
                                    <a href="edit_device.php?id=<?= $device['id'] ?>" class="btn btn-warning btn-sm">Modifier</a>
                                    <a href="delete_device.php?id=<?= $device['id'] ?>" class="btn btn-danger btn-sm">Supprimer</a>
                                    <!-- Ajout d'un bouton pour changer le statut -->
                                    <a href="toggle_device_status.php?id=<?= $device['id'] ?>" class="btn btn-secondary btn-sm">
                                        <?= $device['status'] == 'actif' ? 'Désactiver' : 'Activer' ?>
                                    </a>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                    <a href="add_device.php" class="btn btn-success">Ajouter un Objet Connecté</a>
                </div>
            </div>

            <!-- Configuration des alertes -->
            <div class="card mt-4">
                <div class="card-header bg-warning text-white">
                    Configurer les alertes globales
                </div>
                <div class="card-body">
                    <form action="set_alerts.php" method="POST">
                        <div class="form-group">
                            <label for="alert_type">Type d'alerte :</label>
                                <select class="form-control" name="alert_type" id="alert_type">
                                    <option value="energy_consumption">Surconsommation d'énergie</option>
                                    <option value="device_maintenance">Maintenance des appareils</option>
                                </select>
                        </div>
                        <div class="form-group mt-2">
                            <label for="alert_threshold">Seuil d'alerte :</label>
                            <input type="number" class="form-control" name="alert_threshold" id="alert_threshold" required>
                        </div>
                        <button type="submit" class="btn btn-danger mt-3">Configurer l'alerte</button>
                    </form>
                </div>
            </div>
    
            <!-- Bouton pour accéder à la page de maintenance -->
            <div class="card mb-4">
                <div class="card-header bg-dark text-white">
                    Maintenance et sécurité
                </div>
                <div class="card-body text-center">
                    <p class="lead">Accédez à la section de maintenance pour effectuer des sauvegardes, vérifier l'intégrité des données et vérifier l'utilisation du site.</p>
                    <a href="maintenance.php" class="btn btn-danger btn-lg">Accéder à la Maintenance</a>
                    <a href="ReportsController.php" class="btn btn-danger btn-lg">Rapport d'utilisation</a>
                </div>
            </div>

        </div>

        <?php include '../Principale/footer.php'; ?>

    </body>
</html>

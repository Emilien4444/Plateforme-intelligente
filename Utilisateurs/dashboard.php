<?php
session_start();
include '../BDD-Gestion/functions.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: ../Utilisateurs/login.php");
    exit();
}

// Récupérer le rôle de l'utilisateur
$userRole = getUserRole($_SESSION['user_id']);

// Récupérer les objets connectés associés à l'utilisateur
$userDevices = getDevices($_SESSION['user_id']); 

// Récupérer les statistiques de consommation énergétique et d'utilisation
$deviceStats = getDeviceUsageStats($_SESSION['user_id']);

?>

<?php include '../Principale/header.php'; ?>

<div class="container">
    <h2>Bienvenue dans votre tableau de bord</h2>
    <p>Accédez à vos objets connectés et gérez vos services ici.</p>

    <?php if ($userRole == 'complexe' || $userRole == 'admin'): ?>
        <a href="add_device.php" class="btn">Ajouter un objet connecté</a>
    <?php endif; ?>
    <br><br><br>
    <h3>Statistiques de consommation</h3>

    <canvas id="consommationChart"></canvas>
    
    <br>
    
    <h3>Détails des consommations :</h3>
    <table border="1">
        <tr>
            <th>Objet</th>
            <th>Type</th>
            <th>État</th>
            <th>Consommation (kWh)</th>
            <th>Temps d'utilisation (heures)</th>
        </tr>
        <?php foreach ($deviceStats as $stat): ?>
            <tr>
                <td><?= htmlspecialchars($stat['name']) ?></td>
                <td><?= htmlspecialchars($stat['type']) ?></td>
                <td><?= htmlspecialchars($stat['status']) ?></td>
                <td><?= htmlspecialchars($stat['consommation']) ?> kWh</td>
                <td><?= htmlspecialchars($stat['temps_utilisation']) ?> h</td>
            </tr>
        <?php endforeach; ?>
    </table>
    
    <br><br><br>
    
    <form method="GET">
        <input type="text" name="search" placeholder="Rechercher un objet...">
        <select name="status">
            <option value="">Tous</option>
            <option value="actif">Actif</option>
            <option value="inactif">Inactif</option>
        </select>
        <button type="submit">Rechercher</button>
    </form>

    <h3>Vos objets connectés :</h3>
        <ul style="list-style: none; padding: 0;">
        <?php if (!empty($userDevices)): ?>
            <?php foreach ($userDevices as $device): ?>
                <li class="device-item">
                    <div class="device-info">
                        <strong><?= htmlspecialchars($device['name']) ?></strong> - <?= htmlspecialchars($device['type']) ?>
                    </div>

                    <div class="device-actions">
                        <button class="toggle-btn" data-id="<?= $device['id'] ?>" data-status="<?= $device['status'] ?>">
                            <?= ($device['status'] == 'actif') ? 'ON' : 'OFF' ?>
                        </button>
                    </div>
                </li>
            <?php endforeach; ?>
        <?php else: ?>
            <li>Aucun objet connecté trouvé.</li>
        <?php endif; ?>
        </ul>



    <?php if ($userRole == 'simple'): ?>
        <a href="payment.php" class="btn">E platform + (5€)</a>
    <?php endif; ?>

    <a href="logout.php" class="btn">Déconnexion</a>
    
    <?php if ($_SESSION['user_role'] == 'complexe'): ?>
        <a href="manage_family.php" class="btn">Gérer ma famille</a>
    <?php endif; ?>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    document.addEventListener("DOMContentLoaded", function() {
        let ctx = document.getElementById("consommationChart").getContext("2d");

        let chartData = {
            labels: [<?php foreach ($deviceStats as $stat) { echo "'" . $stat['name'] . "',"; } ?>],
            datasets: [{
                label: "Consommation (kWh)",
                data: [<?php foreach ($deviceStats as $stat) { echo $stat['consommation'] . ","; } ?>],
                backgroundColor: "rgba(75, 192, 192, 0.6)",
            }]
        };

        new Chart(ctx, {
            type: "bar",
            data: chartData,
            options: {
                responsive: true,
                scales: {
                    y: { beginAtZero: true }
                }
            }
        });
    });
    
    
    document.querySelectorAll('.toggle-btn').forEach(button => {
        button.addEventListener('click', function () {
            let deviceId = this.getAttribute('data-id');
            let currentStatus = this.getAttribute('data-status');
            let newStatus = (currentStatus === 'actif') ? 'inactif' : 'actif';

            fetch('toggle_device_status.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: 'id=' + deviceId + '&status=' + newStatus
            })
            .then(response => response.text())
            .then(data => {
                if (data === 'success') {
                    this.textContent = (newStatus === 'actif') ? 'ON' : 'OFF';
                    this.setAttribute('data-status', newStatus);
                } else {
                    alert('Erreur lors du changement d\'état');
                }
            });
        });
    });
</script>

<?php include '../Principale/footer.php'; ?>

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

?>

<?php include '../Principale/header.php'; ?>

<div class="container mt-5">
    <h2 class="mb-4 text-center">Maintenance et Sécurité</h2>

    <!-- Formulaire de sauvegarde -->
    <div class="card mb-4">
        <div class="card-header bg-info text-white">
            Sauvegarde de la base de données
        </div>
        <div class="card-body">
            <p>Effectuer une sauvegarde manuelle de la base de données.</p>
            <form action="backup_database.php" method="POST">
                <button type="submit" class="btn btn-danger">Sauvegarder maintenant</button>
            </form>
        </div>
    </div>

    <!-- Vérification de l'intégrité des données -->
    <div class="card mb-4">
        <div class="card-header bg-warning text-white">
            Vérification de l'intégrité des données
        </div>
        <div class="card-body">
            <p>Vérifier l'intégrité des données dans la plateforme.</p>
            <form action="check_integrity.php" method="POST">
                <button type="submit" class="btn btn-secondary">Vérifier l'intégrité</button>
            </form>
        </div>
    </div>

</div>

<?php include '../Principale/footer.php'; ?>

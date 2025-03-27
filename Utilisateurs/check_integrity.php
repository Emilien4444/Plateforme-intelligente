<?php
session_start();
include '../BDD-Gestion/functions.php';  // S'assurer que functions.php est inclus

// Vérifier si l'utilisateur a le niveau d'accès "expert"
$userId = $_SESSION['user_id'];
$level = getUserLevel($userId);

if ($level != 'expert') {
    header("Location: ../Principale/index.php");
    exit();  // Si l'utilisateur n'a pas accès, rediriger vers la page principale
}

// Vérification de l'intégrité des données en appelant la fonction du fichier functions.php
$issues = checkDatabaseIntegrity();  // Appel de la fonction déjà définie dans functions.php
?>

<?php include '../Principale/header.php'; ?>

<div class="container mt-5">
    <h2 class="text-center">Vérification de l'Intégrité de la Base de Données</h2>

    <?php if (empty($issues)): ?>
        <div class="alert alert-success">Aucun problème trouvé. La base de données est intacte.</div>
    <?php else: ?>
        <div class="alert alert-danger">
            <h4>Problèmes trouvés :</h4>
            <ul>
                <?php foreach ($issues as $issue): ?>
                    <li><?= htmlspecialchars($issue) ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <a href="adminPanel.php" class="btn btn-primary">Retour à l'administration</a>
</div>

<?php include '../Principale/footer.php'; ?>

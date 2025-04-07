<?php
session_start(); // Démarre la session 
include '../BDD-Gestion/functions.php';  

$userId = $_SESSION['user_id']; // Récupère l'ID de l'utilisateur connecté
$level = getUserLevel($userId); // Récupère le niveau de l'utilisateur

// Vérification du niveau d'accès de l'utilisateur
if ($level != 'expert') {
    header("Location: ../Principale/index.php"); // Redirige vers la page principale si l'utilisateur n'a pas les droits d'accès
    exit();  
}

$issues = checkDatabaseIntegrity();  // Appelle la fonction checkDatabaseIntegrity() qui retourne les problèmes détectés (si il y en a)
?>


<?php include '../Principale/header.php'; ?> 

<div class="container mt-5">
    <h2 class="text-center">Vérification de l'Intégrité de la Base de Données</h2>

    <!-- Si aucun problème n'a été trouvé -->
    <?php if (empty($issues)): ?>
        <div class="alert alert-success">Aucun problème trouvé. La base de données est intacte.</div>
    <!-- Si des problèmes ont été trouvés, afficher une liste des problèmes -->
    <?php else: ?>
        <div class="alert alert-danger">
            <h4>Problèmes trouvés :</h4>
            <ul>
                <?php foreach ($issues as $issue): ?>  <!-- Affiche chaque problème trouvé dans la liste -->
                    <li><?= htmlspecialchars($issue) ?></li> <!-- Utilisation de htmlspecialchars pour éviter les attaques XSS -->
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <a href="adminPanel.php" class="btn btn-primary">Retour à l'administration</a>
</div>

<?php include '../Principale/footer.php'; ?> 


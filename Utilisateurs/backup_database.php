<?php
session_start();
include '../BDD-Gestion/functions.php';

// Vérifier si l'utilisateur a le niveau d'accès "expert"
$userId = $_SESSION['user_id'];
$level = getUserLevel($userId);

if ($level != 'expert') {
    header("Location: ../Principale/index.php");
    exit();  // Si l'utilisateur n'a pas accès, rediriger vers la page principale
}

$databaseName = 'plateforme intelligente';  // Nom de la base de données
$backupDirectory = 'C:/backups/';  // Répertoire où les sauvegardes seront stockées
$backupFile = $backupDirectory . 'backup_' . date('Y-m-d_H-i-s') . '.sql';

// Créer le répertoire de sauvegarde s'il n'existe pas
if (!file_exists($backupDirectory)) {
    mkdir($backupDirectory, 0777, true);
}

// Connexion à la base de données
$conn = new mysqli('localhost', 'root', '', $databaseName);

// Vérifier la connexion
if ($conn->connect_error) {
    die("La connexion a échoué: " . $conn->connect_error);
}

// Récupérer toutes les tables de la base de données
$tables = [];
$result = $conn->query("SHOW TABLES");
while ($row = $result->fetch_row()) {
    $tables[] = $row[0];
}

// Ouvrir un fichier pour écrire la sauvegarde
$handle = fopen($backupFile, 'w');

// Sauvegarder chaque table
foreach ($tables as $table) {
    // Récupérer la structure de la table
    $createTableResult = $conn->query("SHOW CREATE TABLE `$table`");
    $createTableRow = $createTableResult->fetch_row();
    fwrite($handle, $createTableRow[1] . ";\n\n");

    // Récupérer les données de la table
    $dataResult = $conn->query("SELECT * FROM `$table`");
    while ($row = $dataResult->fetch_assoc()) {
        $rowValues = array_map(function ($value) {
        // Si la valeur est NULL, on la remplace par une chaîne vide
        return "'" . addslashes($value !== NULL ? $value : '') . "'";
    }, array_values($row));
    
    fwrite($handle, "INSERT INTO `$table` (" . implode(',', array_keys($row)) . ") VALUES (" . implode(',', $rowValues) . ");\n");
    }
}


// Fermer le fichier de sauvegarde
fclose($handle);

// Fermer la connexion à la base de données
$conn->close();

// Afficher un message de succès
echo "<div class='alert alert-success'>La sauvegarde de la base de données a été effectuée avec succès !</div>";
echo "<p>Le fichier de sauvegarde est disponible à l'adresse suivante : <a href='$backupFile' target='_blank'>$backupFile</a></p>";
?>

<?php include '../Principale/header.php'; ?>

<div class="container mt-5">
    <h2 class="text-center">Sauvegarde de la Base de Données</h2>
    <p class="lead text-center">Cliquez ci-dessous pour effectuer une sauvegarde complète de la base de données.</p>
    <a href="backup_database.php" class="btn btn-primary btn-lg d-block mx-auto">Effectuer la sauvegarde</a>
</div>

<?php include '../Principale/footer.php'; ?>

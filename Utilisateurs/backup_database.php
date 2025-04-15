<?php
session_start(); // Démarre la session
include '../BDD-Gestion/functions.php'; 


$userId = $_SESSION['user_id']; // Récupère l'ID de l'utilisateur connecté
$level = getUserLevel($userId); // Récupère le niveau de l'utilisateur

// Si l'utilisateur n'a pas le niveau "expert", rediriger vers la page principale
if ($level != 'expert') {
    header("Location: ../Principale/index.php"); 
    exit(); 
}

$databaseName = 'plateforme intelligente';  // Nom de la BDD à sauvegarder
$backupDirectory = 'C:/backups/';  // Répertoire où les fichiers de sauvegarde seront enregistrés
$backupFile = $backupDirectory . 'backup_' . date('Y-m-d_H-i-s') . '.sql';  // Nom du fichier de sauvegarde avec la date et l'heure actuelles

// Vérifie si le répertoire de sauvegarde existe, sinon le crée
if (!file_exists($backupDirectory)) {
    mkdir($backupDirectory, 0777, true); // Crée le répertoire avec des permissions appropriées
}

// Connexion à la BDD
$conn = new mysqli('localhost', 'root', '', $databaseName); // Crée une connexion MySQL avec les paramètres de la base de données

// Vérification de la connexion
if ($conn->connect_error) {
    die("La connexion a échoué: " . $conn->connect_error); // Si la connexion échoue, arrête le script et affiche un message d'erreur
}

// Récupérer toutes les tables de la base de données
$tables = [];  // Tableau pour stocker les noms des tables
$result = $conn->query("SHOW TABLES");  // Exécute une requête SQL pour obtenir les tables de la base de données
while ($row = $result->fetch_row()) {
    $tables[] = $row[0];  // Ajoute chaque table au tableau $tables
}

$handle = fopen($backupFile, 'w');  // Ouvre le fichier de sauvegarde en mode écriture

// Sauvegarder chaque table de la BDD
foreach ($tables as $table) {
    // Récupérer la structure de la table
    $createTableResult = $conn->query("SHOW CREATE TABLE `$table`");  // Récupère la requête de création de la table
    $createTableRow = $createTableResult->fetch_row();
    fwrite($handle, $createTableRow[1] . ";\n\n");  // Écrit la requête de création dans le fichier de sauvegarde

    // Récupérer les données de la table
    $dataResult = $conn->query("SELECT * FROM `$table`");  // Récupère toutes les lignes de la table
    while ($row = $dataResult->fetch_assoc()) {
        // Préparer les valeurs des colonnes pr les insérer dans le fichier de sauvegarde
        $rowValues = array_map(function ($value) {
            // Remplacer les valeurs NULL par une chaîne vide et échapper les caractères spéciaux
            return "'" . addslashes($value !== NULL ? $value : '') . "'"; 
        }, array_values($row));
        
        // Écrire la requête d'insertion des données dans le fichier de sauvegarde
        fwrite($handle, "INSERT INTO `$table` (" . implode(',', array_keys($row)) . ") VALUES (" . implode(',', $rowValues) . ");\n");
    }
}

fclose($handle);  // Ferme le fichier après avoir écrit toutes les données


$conn->close();  // Ferme la connexion MySQL

// Afficher un message de succès
echo "<div class='alert alert-success'>La sauvegarde de la base de données a été effectuée avec succès !</div>";
echo "<p>Le fichier de sauvegarde est disponible à l'adresse suivante : <a href='$backupFile' target='_blank'>$backupFile</a></p>";  // Affiche le lien vers le fichier de sauvegarde
?>

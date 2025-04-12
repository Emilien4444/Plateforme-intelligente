<?php
$host = "localhost";
$user = "root";
$password = "";
$database = "plateforme intelligente";

// Connexion à la base de données
$conn = new mysqli($host, $user, $password, $database);

// Vérifier la connexion
if ($conn->connect_error) {
    die("Échec de la connexion : " . $conn->connect_error);
}
?>

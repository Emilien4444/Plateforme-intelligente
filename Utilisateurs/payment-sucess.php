<?php
require 'vendor/autoload.php';
include '../BDD-Gestion/functions.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    addLog($_SESSION['user_id'], "Paiement effectué - Passage en compte complexe");
    header("Location: ../Utilisateurs/login.php");
    exit();
}

upgradeToComplexe($_SESSION['user_id']);
header("Location: dashboard.php?message=Paiement+réussi,+vous+êtes+maintenant+utilisateur+Complexe!");
?>
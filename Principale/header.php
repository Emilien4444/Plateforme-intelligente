<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Plateforme Intelligente</title>

    <!-- CSS Bootstrap -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- JS Bootstrap -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <link rel="stylesheet" href="../Principale/styles.css">
    <script src="https://cdn.jsdelivr.net/npm/vue@2.6.14/dist/vue.js"></script>
</head>

<body>
//Encadre la section de l'en-tête de la page
<header>
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
        <div class="container">
            <a class="navbar-brand" href="../Principale/index.php">E Plateform</a>

            <!-- Bouton pour mobile -->
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>

            <!-- Menu -->
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item"><a class="nav-link" href="../Principale/index.php">Accueil</a></li>
                    <li class="nav-item"><a class="nav-link" href="../Services/services.php">Services</a></li>
                    <li class="nav-item"><a class="nav-link" href="../Services/contact.php">Contact</a></li>
                    <li class="nav-item"><a class="nav-link" href="../Utilisateurs/dashboard.php">Tableau de Bord</a></li>
                    <li class="nav-item"><a class="nav-link" href="../Utilisateurs/adminPanel.php">Admin</a></li>
                    <button id="theme-toggle" class="btn btn-dark">
                        Mode Nuit
                    </button>
                </ul>

                <!-- Boutons connexion et profil -->
                <div class="d-flex">
                    <?php if (isset($_SESSION['user_id'])): ?>
                        <a href="../Utilisateurs/monProfil.php" class="btn btn-outline-light me-2">Mon Profil</a>
                        <a href="../Utilisateurs/logout.php" class="btn btn-light text-primary">Déconnexion</a>
                    <?php else: ?>
                        <a href="../Utilisateurs/login.php" class="btn btn-light text-primary me-2">Connexion</a>
                        <a href="../Utilisateurs/register.php" class="btn btn-outline-light">Inscription</a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </nav>
</header>

</body>


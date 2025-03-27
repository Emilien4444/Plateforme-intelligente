<?php
session_start(); // Démarre la session pour accéder aux variables de session
include '../Principale/header.php';

// Connexion à la base de données
include '../BDD-Gestion/functions.php'; 

// Récupérer les filtres s'ils existent
$categorie = isset($_POST['categorie']) ? $_POST['categorie'] : '';
$marque = isset($_POST['marque']) ? $_POST['marque'] : '';

// Requête SQL de recherche
$query = "SELECT * FROM objets_connectes WHERE 1";
if ($categorie) {
    $query .= " AND categorie LIKE '%$categorie%'";
}
if ($marque) {
    $query .= " AND marque LIKE '%$marque%'";
}

$result = mysqli_query($conn, $query);
?>

<html>
<body>
<!-- HERO SECTION -->
<section class="hero bg-primary text-white text-center py-5">
    <div class="container">
        <h1 class="fw-bold">Bienvenue sur notre plateforme intelligente</h1>
        <p class="lead">Connectez-vous et explorez nos services pour une ville ou un bâtiment intelligent.</p>
        
        <?php if (!isset($_SESSION['user_logged_in'])): ?>
            <!-- Affiche le bouton "S'inscrire" seulement si l'utilisateur n'est pas connecté -->
            <a href="../Utilisateurs/register.php" class="btn btn-light btn-lg mt-3">S'inscrire</a>
        <?php endif; ?>
    </div>
</section>


<!-- Formulaire de recherche -->
<div class="container my-5">
    <h2 class="text-center mb-4">Rechercher des objets connectés</h2>
    <form method="POST" action="index.php">
        <div class="row">
            <div class="col-md-6">
                <div class="mb-3">
                    <label for="categorie" class="form-label">Catégorie</label>
                    <select name="categorie" id="categorie" class="form-select">
                        <option value="">Sélectionner une catégorie</option>
                        <option value="Maison" <?= ($categorie == 'Maison') ? 'selected' : '' ?>>Maison</option>
                        <option value="Santé" <?= ($categorie == 'Santé') ? 'selected' : '' ?>>Santé</option>
                        <option value="Sécurité" <?= ($categorie == 'Sécurité') ? 'selected' : '' ?>>Sécurité</option>
                    </select>
                </div>
            </div>
            <div class="col-md-6">
                <div class="mb-3">
                    <label for="marque" class="form-label">Marque</label>
                    <select name="marque" id="marque" class="form-select">
                        <option value="">Sélectionner une marque</option>
                        <option value="Nest" <?= ($marque == 'Nest') ? 'selected' : '' ?>>Nest</option>
                        <option value="Philips" <?= ($marque == 'Philips') ? 'selected' : '' ?>>Philips</option>
                        <option value="Xiaomi" <?= ($marque == 'Xiaomi') ? 'selected' : '' ?>>Xiaomi</option>
                        <option value="Samsung" <?= ($marque == 'Samsung') ? 'selected' : '' ?>>Samsung</option>
                        <option value="Fitbit" <?= ($marque == 'Fitbit') ? 'selected' : '' ?>>Fitbit</option>
                        <option value="Amazon" <?= ($marque == 'Amazon') ? 'selected' : '' ?>>Amazon</option>
                    </select>
                </div>
            </div>
        </div>
        <button type="submit" class="btn btn-primary w-100">Rechercher</button>
    </form>
</div>


<!-- Affichage des infos de la BDD -->
<div class="container">
    <div class="row">
        <?php while ($row = mysqli_fetch_assoc($result)) { ?>
            <div class="col-md-4 mb-4">
                <div class="card">
                    <img src="<?= $row['url_image'] ?>" class="card-img-top" alt="<?= $row['nom'] ?>">
                    <div class="card-body">
                        <h5 class="card-title"><?= $row['nom'] ?></h5>
                        <p class="card-text"><?= $row['description'] ?></p>
                        <p class="card-text"><strong>Prix :</strong> <?= $row['prix'] ?> €</p>
                        <p class="card-text"><strong>Marque :</strong> <?= $row['marque'] ?></p>
                    </div>
                </div>
            </div>
        <?php } ?>
    </div>
</div>


<!-- SERVICES SECTION -->
<section class="services py-5">
    <div class="container">
        <h2 class="text-center mb-4">Nos Services</h2>
        <div class="row">
            <div class="col-md-4">
                <div class="card text-white bg-primary shadow-sm">
                    <div class="card-body text-center">
                        <h3 class="card-title">Gestion des Objets Connectés</h3>
                        <p class="card-text">Surveillez et contrôlez vos objets connectés en temps réel.</p>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card text-white bg-primary shadow-sm">
                    <div class="card-body text-center">
                        <h3 class="card-title">Optimisation Énergétique</h3>
                        <p class="card-text">Analysez et réduisez votre consommation d'énergie.</p>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card text-white bg-primary shadow-sm">
                    <div class="card-body text-center">
                        <h3 class="card-title">Accès Sécurisé</h3>
                        <p class="card-text">Gérez les accès et permissions des utilisateurs.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<?php include '../Principale/footer.php'; ?>
</body>
</html>

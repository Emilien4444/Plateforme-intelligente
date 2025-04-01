<?php
session_start(); // Démarre la session 

// Vérifier si l'utilisateur est déjà connecté
if (isset($_SESSION['user_logged_in']) && $_SESSION['user_logged_in'] === true) {
    // Si l'utilisateur est déjà connecté -> redirige vers la page d'accueil 
    header("Location: ../index.php");
    exit(); // Arrêter l'exécution du script après la redirection
}

include '../BDD-Gestion/functions.php'; 
$error = ""; // Variable pour stocker les messages d'erreur

// Vérifier si le formulaire a été soumis
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Récupérer les données du formulaire et les valider
    $username = trim($_POST['username']); 
    $email = trim($_POST['email']); 
    $password = $_POST['password']; 
    $first_name = trim($_POST['first_name']); 
    $last_name = trim($_POST['last_name']); 
    $birthdate = $_POST['birthdate']; 
    $member_type = trim($_POST['member_type']);
    
    // Vérifier que tous les champs sont remplis
    if (empty($username) || empty($email) || empty($password) || empty($first_name) || empty($last_name) || empty($birthdate) || empty($member_type)) {
        // Si l'un des champs est vide -> affiche un message d'erreur
        $error = "Tous les champs doivent être remplis.";
    } else {
        // Si tous les champs sont remplis -> tenter d'enregistrer l'utilisateur dans la BDD
        $result = registerUser($username, $email, $password, $first_name, $last_name, $birthdate, $member_type);

        if ($result === true) {
            // Si l'inscription réussit -> redirige l'utilisateur vers la page de connexion
            header("Location: login.php");
            exit(); 
        } else {
            // Si l'inscription échoue -> affiche le message d'erreur retourné par la fonction registerUser
            $error = $result;
        }
    }
}
?>

<?php include '../Principale/header.php'; ?> 

<div class="container d-flex justify-content-center align-items-center" style="height: auto; margin-top: 50px;">
    <div class="card shadow-lg p-4" style="max-width: 500px; width: 100%;"> <!-- Crée un cadre pour le formulaire d'inscription -->
        <h2 class="text-center text-primary">📝 Inscription</h2> 

        <!-- Affichage des erreurs, s'il y en a -->
        <?php if (!empty($error)): ?>
            <div class="alert alert-danger text-center"><?= $error ?></div> <!-- Message d'erreur si un problème survient -->
        <?php endif; ?>

        <form method="POST">
            <!-- Formulaire d'inscription -->
            <div class="mb-3">
                <label for="first_name" class="form-label">Prénom</label>
                <input type="text" id="first_name" name="first_name" class="form-control" required> <!-- Champ pour le prénom -->
            </div>

            <div class="mb-3">
                <label for="last_name" class="form-label">Nom</label>
                <input type="text" id="last_name" name="last_name" class="form-control" required> <!-- Champ pour le nom -->
            </div>

            <div class="mb-3">
                <label for="birthdate" class="form-label">Date de naissance</label>
                <input type="date" id="birthdate" name="birthdate" class="form-control" required> <!-- Champ pour la date de naissance -->
            </div>

            <div class="mb-3">
                <label for="member_type" class="form-label">Type de membre</label>
                <select id="member_type" name="member_type" class="form-select" required> <!-- Sélecteur pour le type de membre -->
                    <option value="">-- Sélectionnez --</option> <!-- Option vide par défaut -->
                    <option value="Père">Père</option> 
                    <option value="Mère">Mère</option> 
                    <option value="Enfant">Enfant</option> 
                    <option value="Habitant">Habitant</option> 
                </select>
            </div>

            <div class="mb-3">
                <label for="username" class="form-label">Nom d'utilisateur</label>
                <input type="text" id="username" name="username" class="form-control" required> <!-- Champ pour le nom d'utilisateur -->
            </div>

            <div class="mb-3">
                <label for="email" class="form-label">Email</label>
                <input type="email" id="email" name="email" class="form-control" required> <!-- Champ pour l'email -->
            </div>

            <div class="mb-3">
                <label for="password" class="form-label">Mot de passe</label>
                <input type="password" id="password" name="password" class="form-control" required> <!-- Champ pour le mot de passe -->
            </div>

            <button type="submit" class="btn btn-primary w-100">S'inscrire</button> <!-- Bouton pour soumettre le formulaire -->
        </form>

        <!-- Lien vers la page de connexion si l'utilisateur a déjà un compte -->
        <div class="text-center mt-3">
            <a href="login.php" class="btn btn-outline-dark w-100">Déjà un compte ? Connectez-vous</a>
        </div>
    </div>
</div>

<?php include '../Principale/footer.php'; ?>

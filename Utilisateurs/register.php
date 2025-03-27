<?php
session_start(); // Démarre la session pour accéder aux variables de session

// Vérifier si l'utilisateur est déjà connecté
if (isset($_SESSION['user_logged_in']) && $_SESSION['user_logged_in'] === true) {
    header("Location: ../index.php"); // Redirige l'utilisateur vers la page d'accueil ou autre page
    exit();
}

include '../BDD-Gestion/functions.php';
$error = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Récupération et validation des champs
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $first_name = trim($_POST['first_name']);
    $last_name = trim($_POST['last_name']);
    $birthdate = $_POST['birthdate'];
    $member_type = trim($_POST['member_type']);

    // Vérifier que tous les champs sont remplis
    if (empty($username) || empty($email) || empty($password) || empty($first_name) || empty($last_name) || empty($birthdate) || empty($member_type)) {
        $error = "❌ Tous les champs doivent être remplis.";
    } else {
        // Tente d'inscrire l'utilisateur
        $result = registerUser($username, $email, $password, $first_name, $last_name, $birthdate, $member_type);

        if ($result === true) {
            header("Location: login.php");
            exit();
        } else {
            $error = $result;
        }
    }
}
?>

<?php include '../Principale/header.php'; ?>
<div class="container d-flex justify-content-center align-items-center" style="height: auto; margin-top: 50px;">
    <div class="card shadow-lg p-4" style="max-width: 500px; width: 100%;">
        <h2 class="text-center text-primary">📝 Inscription</h2>

        <!-- Affichage des erreurs -->
        <?php if (!empty($error)): ?>
            <div class="alert alert-danger text-center"><?= $error ?></div>
        <?php endif; ?>

        <form method="POST">
            <div class="mb-3">
                <label for="first_name" class="form-label">👤 Prénom</label>
                <input type="text" id="first_name" name="first_name" class="form-control" required>
            </div>

            <div class="mb-3">
                <label for="last_name" class="form-label">👤 Nom</label>
                <input type="text" id="last_name" name="last_name" class="form-control" required>
            </div>

            <div class="mb-3">
                <label for="birthdate" class="form-label">🎂 Date de naissance</label>
                <input type="date" id="birthdate" name="birthdate" class="form-control" required>
            </div>

            <div class="mb-3">
                <label for="member_type" class="form-label">🏷️ Type de membre</label>
                <select id="member_type" name="member_type" class="form-select" required>
                    <option value="">-- Sélectionnez --</option>
                    <option value="Père">Père</option>
                    <option value="Mère">Mère</option>
                    <option value="Enfant">Enfant</option>
                    <option value="Habitant">Habitant</option>
                </select>
            </div>

            <div class="mb-3">
                <label for="username" class="form-label">📛 Nom d'utilisateur</label>
                <input type="text" id="username" name="username" class="form-control" required>
            </div>

            <div class="mb-3">
                <label for="email" class="form-label">📧 Email</label>
                <input type="email" id="email" name="email" class="form-control" required>
            </div>

            <div class="mb-3">
                <label for="password" class="form-label">🔑 Mot de passe</label>
                <input type="password" id="password" name="password" class="form-control" required>
            </div>

            <button type="submit" class="btn btn-primary w-100">S'inscrire</button>
        </form>

        <div class="text-center mt-3">
            <a href="login.php" class="btn btn-outline-dark w-100">Déjà un compte ? Connectez-vous</a>
        </div>
    </div>
</div>

<?php include '../Principale/footer.php'; ?>

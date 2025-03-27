<?php
session_start();
include '../BDD-Gestion/functions.php';

$message = ""; // Stocke les erreurs ou succès

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = trim($_POST['email']);
    $password = $_POST['password'];

    $user = loginUser($email, $password);

    if ($user) {
        $_SESSION['user_id'] = $user['id']; // Stocke uniquement l'ID
        $_SESSION['user_role'] = $user['role']; // Stocke le rôle récupéré

        addLog($_SESSION['user_id'], "Connexion réussie");

        header("Location: dashboard.php");
        exit();
    } else {
        $message = '<div class="alert alert-danger text-center"> Email ou mot de passe incorrect.</div>';
    }
}


?>

<?php include '../Principale/header.php'; ?>

<body>
    <html>
        
        <div class="container d-flex justify-content-center align-items-center" style="height: 80vh;">
            <div class="card shadow-lg p-4" style="max-width: 400px; width: 100%;">
                <h2 class="text-center text-primary mb-3">Connexion</h2>

                <!-- Affichage des erreurs -->
                <?php if (!empty($message)) echo $message; ?>

                <form method="POST">
                <div class="mb-3">
                    <label for="email" class="form-label">📧 Email</label>
                    <input type="email" id="email" name="email" class="form-control" placeholder="Votre email" required>
                </div>

                <div class="mb-3">
                    <label for="password" class="form-label">🔑 Mot de passe</label>
                    <input type="password" id="password" name="password" class="form-control" placeholder="Votre mot de passe" required>
                </div>

                <button type="submit" class="btn btn-primary w-100">Se connecter</button>
                </form>

                <div class="text-center mt-3">
                    <a href="forgotPassword.php" class="text-decoration-none">Mot de passe oublié ?</a>
                </div>

                <div class="text-center mt-2">
                    <a href="register.php" class="btn btn-outline-success w-100">Créer un compte</a>
                </div>
            </div>
        </div>

        <?php include '../Principale/footer.php'; ?>
    </body>
</html>

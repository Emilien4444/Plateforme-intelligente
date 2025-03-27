<?php
session_start();
include '../BDD-Gestion/functions.php';

// Vérification si l'utilisateur est administrateur
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$userId = $_SESSION['user_id'];
$userLevel = getUserLevel($userId);

if ($userLevel != 'expert') {
    header("Location: index.php");
    exit();
}

// Ajouter un utilisateur
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'];
    $email = $_POST['email'];
    $password = password_hash($_POST['password'], PASSWORD_BCRYPT);
    $level = $_POST['level'];

    // Préparer et exécuter la requête d'insertion
    $sql = "INSERT INTO users (username, email, password, level) VALUES (?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssss", $username, $email, $password, $level);
    $stmt->execute();

    header("Location: manage_user.php");
    exit();
}

?>

<?php include '../Principale/header.php'; ?>

<div class="container mt-5">
    <h2>Ajouter un Utilisateur</h2>

    <form method="POST" action="">
        <div class="form-group">
            <label for="username">Nom d'utilisateur</label>
            <input type="text" name="username" class="form-control" required>
        </div>
        <div class="form-group">
            <label for="email">Email</label>
            <input type="email" name="email" class="form-control" required>
        </div>
        <div class="form-group">
            <label for="password">Mot de passe</label>
            <input type="password" name="password" class="form-control" required>
        </div>
        <div class="form-group">
            <label for="level">Niveau</label>
            <select name="level" class="form-control" required>
                <option value="basic">De base</option>
                <option value="advanced">Avancé</option>
                <option value="expert">Expert</option>
            </select>
        </div>
        <button type="submit" class="btn btn-primary mt-3">Ajouter l'utilisateur</button>
    </form>
</div>

<?php include '../Principale/footer.php'; ?>

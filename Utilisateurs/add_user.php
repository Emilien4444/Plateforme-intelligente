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
    $first_name = $_POST['first_name']; 
    $last_name = $_POST['last_name'];   
    $birthdate = $_POST['birthdate'];   
    $gender = $_POST['gender'];         

    // Préparer et exécuter la requête d'insertion
    $sql = "INSERT INTO users (username, email, password, first_name, last_name, birthdate, gender, level) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sssssiss", $username, $email, $password, $first_name, $last_name, $birthdate, $gender, $level);
    $stmt->execute();

    header("Location: manage_user.php");
    exit();
}
?>

<?php include '../Principale/header.php'; ?>

<div class="container mt-5">
    <h2>Ajouter un Utilisateur</h2>

    <form method="POST" action="">
        <div class="mb-3">
                <label for="first_name" class="form-label">Prénom</label>
                <input type="text" id="first_name" name="first_name" class="form-control" required>
        </div>
        <div class="mb-3">
                <label for="last_name" class="form-label">Nom</label>
                <input type="text" id="last_name" name="last_name" class="form-control" required>
        </div>
        <div class="mb-3">
                <label for="birthdate" class="form-label">Date de naissance</label>
                <input type="date" id="birthdate" name="birthdate" class="form-control" required>
        </div>
        <div class="mb-3">
                <label for="username" class="form-label">Nom d'utilisateur</label>
                <input type="text" id="username" name="username" class="form-control" required>
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
        <div class="form-group">
            <label for="gender">Genre</label>
            <input type="text" name="gender" class="form-control" required>
        </div>
        <button type="submit" class="btn btn-primary mt-3">Ajouter l'utilisateur</button>
    </form>
</div>

<?php include '../Principale/footer.php'; ?>

<?php
session_start(); // Démarre la session
include '../BDD-Gestion/functions.php'; 

// Vérification si l'utilisateur est connecté
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php"); // Si l'utilisateur n'est pas connecté -> redirige vers la page de connexion
    exit();
}

$userId = $_SESSION['user_id']; // Récupère l'ID de l'utilisateur connecté
$userLevel = getUserLevel($userId); // Récupère le niveau de l'utilisateur

// Vérification que l'utilisateur a le niveau d'accès "expert"
if ($userLevel != 'expert') {
    header("Location: index.php"); // Si l'utilisateur n'a pas le niveau requis -> redirige vers la page principale
    exit();
}

// Vérification si l'ID de l'utilisateur à modifier est passé en paramètre dans l'URL
if (isset($_GET['id'])) {
    $userIdToEdit = $_GET['id']; // Récupère l'ID de l'utilisateur à modifier

    // Récupérer les info de l'utilisateur à modifier
    $sql = "SELECT id, username, email, level, points FROM users WHERE id = ?"; // Requête pour récupérer les données de l'utilisateur
    $stmt = $conn->prepare($sql); 
    $stmt->bind_param("i", $userIdToEdit); // Lier l'ID de l'utilisateur à la requête
    $stmt->execute(); 
    $user = $stmt->get_result()->fetch_assoc(); // Récupérer le résultat sous forme de tableau associatif

    // Vérification si l'utilisateur existe dans la BDD
    if (!$user) {
        die("Utilisateur non trouvé."); // Si l'utilisateur n'existe pas -> arrête le script + affiche un message d'erreur
    }

    // Traitement du formulaire de maj de l'utilisateur
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        // Récupérer les données envoyées par le formulaire
        $username = $_POST['username'];
        $email = $_POST['email'];
        $level = $_POST['level'];
        $points = $_POST['points'];

        // Préparer et exécuter la requête de maj
        $sqlUpdate = "UPDATE users SET username = ?, email = ?, level = ?, points = ? WHERE id = ?"; // Requête pour maj les infos
        $stmtUpdate = $conn->prepare($sqlUpdate); 
        $stmtUpdate->bind_param("ssssi", $username, $email, $level, $points, $userIdToEdit); // Lier les paramètres à la requête
        $stmtUpdate->execute(); 

        // Redirection vers la page de gestion des utilisateurs après la maj
        header("Location: manage_user.php");
        exit(); 
    }
} else {
    die("ID utilisateur non spécifié."); // Si l'ID de l'utilisateur à modifier n'est pas spécifié -> affiche un message d'erreur
}
?>


<?php include '../Principale/header.php'; ?>

<div class="container mt-5">
    <h2>Modifier l'utilisateur</h2> 

    <!-- Formulaire de maj des infos de l'utilisateur -->
    <form method="POST" action="">
        <div class="form-group">
            <label for="username">Nom d'utilisateur</label>
            <input type="text" name="username" class="form-control" value="<?= htmlspecialchars($user['username']) ?>" required>
        </div>

        <div class="form-group">
            <label for="email">Email</label>
            <input type="email" name="email" class="form-control" value="<?= htmlspecialchars($user['email']) ?>" required>
        </div>

        <div class="form-group">
            <label for="level">Niveau</label>
            <select name="level" class="form-control" required> <!-- Sélecteur pour le niveau de l'utilisateur -->
                <option value="basic" <?= $user['level'] == 'beginner' ? 'selected' : '' ?>>beginner</option>
                <option value="intermediate" <?= $user['level'] == 'intermediate' ? 'selected' : '' ?>>intermediate</option>
                <option value="advanced" <?= $user['level'] == 'advanced' ? 'selected' : '' ?>>Avancé</option>
                <option value="expert" <?= $user['level'] == 'expert' ? 'selected' : '' ?>>Expert</option>
            </select>
        </div>

        <div class="form-group">
            <label for="points">Points</label>
            <input type="number" name="points" class="form-control" value="<?= htmlspecialchars($user['points']) ?>" required>
        </div>

        <button type="submit" class="btn btn-primary mt-3">Mettre à jour</button>
    </form>
</div>

<?php include '../Principale/footer.php'; ?>


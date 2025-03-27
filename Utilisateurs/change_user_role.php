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

// Vérification si l'ID de l'utilisateur est passé en paramètre
if (isset($_GET['id'])) {
    $userIdToChange = $_GET['id'];

    // Récupérer les informations actuelles de l'utilisateur
    $sql = "SELECT id, username, email, level FROM users WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $userIdToChange);
    $stmt->execute();
    $user = $stmt->get_result()->fetch_assoc();

    // Si l'utilisateur n'existe pas
    if (!$user) {
        die("Utilisateur non trouvé.");
    }

    // Mise à jour du rôle si le formulaire est soumis
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $level = $_POST['level'];

        // Mise à jour du rôle de l'utilisateur
        $sqlUpdate = "UPDATE users SET level = ? WHERE id = ?";
        $stmtUpdate = $conn->prepare($sqlUpdate);
        $stmtUpdate->bind_param("si", $level, $userIdToChange);
        $stmtUpdate->execute();

        header("Location: manage_user.php");
        exit();
    }
} else {
    die("ID utilisateur non spécifié.");
}
?>

<?php include '../Principale/header.php'; ?>

<div class="container mt-5">
    <h2>Modifier le rôle de l'utilisateur</h2>

    <form method="POST" action="">
        <div class="form-group">
            <label for="username">Nom d'utilisateur</label>
            <input type="text" name="username" class="form-control" value="<?= htmlspecialchars($user['username']) ?>" disabled>
        </div>
        <div class="form-group">
            <label for="level">Niveau</label>
            <select name="level" class="form-control" required>
                <option value="basic" <?= $user['level'] == 'basic' ? 'selected' : '' ?>>Simple</option>
                <option value="advanced" <?= $user['level'] == 'advanced' ? 'selected' : '' ?>>Avancé</option>
                <option value="expert" <?= $user['level'] == 'expert' ? 'selected' : '' ?>>Expert</option>
            </select>
        </div>
        <button type="submit" class="btn btn-primary mt-3">Changer le rôle</button>
    </form>
</div>

<?php include '../Principale/footer.php'; ?>

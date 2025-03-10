<?php
include 'config.php';
require_once __DIR__ . '/../vendor/autoload.php';
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

function userExists($email) {
    global $conn;
    $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $stmt->store_result();
    return $stmt->num_rows > 0;
}


function registerUser($username, $email, $password) {
    global $conn;

    // Vérifier si l'utilisateur existe déjà
    if (userExists($email)) {
        return "Cet email est déjà utilisé. <a href='forgotPassword.php'>Mot de passe oublié ?</a>";
    }

    $hashedPassword = password_hash($password, PASSWORD_BCRYPT);
    $stmt = $conn->prepare("INSERT INTO users (username, email, password) VALUES (?, ?, ?)");
    $stmt->bind_param("sss", $username, $email, $hashedPassword);

    return $stmt->execute() ? true : "Erreur lors de l'inscription.";
}
function loginUser($email, $password) {
    global $conn;
    
    $stmt = $conn->prepare("SELECT id, role, password FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($user = $result->fetch_assoc()) {
        if (password_verify($password, $user['password'])) {
            return ['id' => $user['id'], 'role' => $user['role']];
        }
    }
    
    return false;
}
function getUserById($userId) {
    global $conn;
    $stmt = $conn->prepare("SELECT username, email FROM users WHERE id = ?");
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    return $stmt->get_result()->fetch_assoc();
}

function updateUserProfile($userId, $username, $email) {
    global $conn;
    $stmt = $conn->prepare("UPDATE users SET username = ?, email = ? WHERE id = ?");
    $stmt->bind_param("ssi", $username, $email, $userId);
    return $stmt->execute();
}

function changeUserPassword($userId, $oldPassword, $newPassword) {
    global $conn;
    
    // Vérifier l'ancien mot de passe
    $stmt = $conn->prepare("SELECT password FROM users WHERE id = ?");
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();

    if (!$user || !password_verify($oldPassword, $user['password'])) {
        return false;
    }

    // Mettre à jour le nouveau mot de passe
    $hashedPassword = password_hash($newPassword, PASSWORD_BCRYPT);
    $stmt = $conn->prepare("UPDATE users SET password = ? WHERE id = ?");
    $stmt->bind_param("si", $hashedPassword, $userId);
    return $stmt->execute();
}

function addLog($userId, $action) {
    global $conn;
    $stmt = $conn->prepare("INSERT INTO logs (user_id, action) VALUES (?, ?)");
    $stmt->bind_param("is", $userId, $action);
    return $stmt->execute();
}





function addDevice($userId, $name, $type) {
    global $conn;
    $stmt = $conn->prepare("INSERT INTO devices (user_id, name, type) VALUES (?, ?, ?)");
    $stmt->bind_param("iss", $userId, $name, $type);
    return $stmt->execute();
}
function getDevices($userId, $search = "", $status = "") {
    global $conn;
    $query = "SELECT * FROM devices WHERE user_id = ?";
    
    if (!empty($search)) {
        $query .= " AND name LIKE ?";
        $search = "%$search%";
    }
    if (!empty($status)) {
        $query .= " AND status = ?";
    }

    $stmt = $conn->prepare($query);
    if (!empty($search) && !empty($status)) {
        $stmt->bind_param("iss", $userId, $search, $status);
    } elseif (!empty($search)) {
        $stmt->bind_param("is", $userId, $search);
    } elseif (!empty($status)) {
        $stmt->bind_param("is", $userId, $status);
    } else {
        $stmt->bind_param("i", $userId);
    }
    
    $stmt->execute();
    return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
}
function getDeviceById($deviceId) {
    global $conn;
    $stmt = $conn->prepare("SELECT * FROM devices WHERE id = ?");
    $stmt->bind_param("i", $deviceId);
    $stmt->execute();
    return $stmt->get_result()->fetch_assoc();
}
function updateDeviceStatus($deviceId, $status) {
    global $conn;
    $stmt = $conn->prepare("UPDATE devices SET status = ? WHERE id = ?");
    $stmt->bind_param("si", $status, $deviceId);
    return $stmt->execute();
}
function deleteDevice($deviceId) {
    global $conn;
    $stmt = $conn->prepare("DELETE FROM devices WHERE id = ?");
    $stmt->bind_param("i", $deviceId);
    return $stmt->execute();
}
function addDeviceData($deviceId, $userId, $dataType, $value) {
    global $conn;
    $stmt = $conn->prepare("INSERT INTO device_data (device_id, user_id, data_type, value) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("iiss", $deviceId, $userId, $dataType, $value);
    return $stmt->execute();
}





function getUserRole($userId) {
    global $conn;

    // Vérifier que $userId est bien un entier
    if (!is_numeric($userId)) {
        die("Erreur : userId invalide dans getUserRole(). Valeur reçue : " . print_r($userId, true));
    }

    $stmt = $conn->prepare("SELECT role FROM users WHERE id = ?");
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();

    return ($user && isset($user['role'])) ? $user['role'] : 'simple';
}
function upgradeToComplexe($userId) {
    global $conn;
    $stmt = $conn->prepare("UPDATE users SET role = 'complexe', payment_status = 'paye' WHERE id = ?");
    $stmt->bind_param("i", $userId);
    return $stmt->execute();
}





function createFamily($userId) {
    global $conn;
    $familyId = $userId; // L'ID du chef de famille devient l'ID de la famille
    $stmt = $conn->prepare("UPDATE users SET family_id = ? WHERE id = ?");
    $stmt->bind_param("ii", $familyId, $userId);
    return $stmt->execute();
}

function generateInvitation($complexeId, $email) {
    global $conn;

    // Vérifier si l'utilisateur invité existe
    $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($user = $result->fetch_assoc()) {
        $userId = $user['id'];
        $token = bin2hex(random_bytes(50));

        // Vérifier si le complexe a déjà une famille
        $stmt = $conn->prepare("SELECT family_id FROM users WHERE id = ?");
        $stmt->bind_param("i", $complexeId);
        $stmt->execute();
        $result = $stmt->get_result();
        $complexeData = $result->fetch_assoc();

        if (!$complexeData['family_id']) {
            // Si le complexe n'a pas encore de `family_id`, on lui donne son propre ID
            $stmt = $conn->prepare("UPDATE users SET family_id = ? WHERE id = ?");
            $stmt->bind_param("ii", $complexeId, $complexeId);
            $stmt->execute();
        }

        // Stocker le token d'invitation
        $stmt = $conn->prepare("UPDATE users SET invitation_token = ? WHERE id = ?");
        $stmt->bind_param("si", $token, $userId);
        $stmt->execute();

        return $token;
    }
    return false;
}
function sendInvitationEmail($email, $complexeId) {
    global $conn;

    $token = generateInvitation($complexeId, $email);
    if (!$token) return false;

    $mail = new PHPMailer(true);
    try {
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';
        $mail->SMTPAuth = true;
        $mail->Username = 'emilienbouffart@gmail.com';
        $mail->Password = 'yaremtiqdoyiviiv';
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = 587;

        $mail->setFrom('emilienbouffart@gmail.com', 'Plateforme Intelligente');
        $mail->addAddress($email);
        $mail->Subject = 'Invitation à rejoindre une famille';
        
        $baseUrl = "http://localhost/Plateforme_Intelligente/Utilisateurs/";
        $invitationLink = $baseUrl . "accept_invite.php?token=$token";
        $mail->Body = "Vous avez été invité à rejoindre une famille. Cliquez ici pour accepter : $invitationLink";

        $mail->send();
        return true;
    } catch (Exception $e) {
        return false;
    }
}





function removeUserFromFamily($complexeId, $userId) {
    global $conn;
    
    // Vérifier si l'utilisateur appartient bien à la famille
    $stmt = $conn->prepare("SELECT family_id FROM users WHERE id = ?");
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $result = $stmt->get_result();
    $family = $result->fetch_assoc();

    if (!$family) {
        echo "L'utilisateur n'existe pas !";
        return false;
    }

    if ($family['family_id'] == $complexeId) {
        echo "L'utilisateur appartient bien à cette famille.";
    }

    // Exécuter la mise à jour
    $stmt = $conn->prepare("UPDATE users SET family_id = NULL WHERE id = ?");
    $stmt->bind_param("i", $userId);
        
    if ($stmt->execute()) {
        echo "Suppression réussie !";
        return true;
    } else {
        echo "Erreur SQL  : " . $stmt->error;
        return false;
    }
}

function getDeviceUsageStats($userId) {
    global $conn;

    $query = "
        SELECT 
            d.name, d.type, d.status, 
            COALESCE(SUM(dd.value), 0) AS consommation, 
            COALESCE(COUNT(dd.id), 0) AS temps_utilisation
        FROM devices d
        LEFT JOIN device_data dd ON d.id = dd.device_id
        WHERE d.user_id = ?
        GROUP BY d.id
    ";

    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
}


        
?>
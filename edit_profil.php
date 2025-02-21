<?php
session_start();
require_once "config/database.php";

// Vérifier si l'utilisateur est connecté
if (!isset($_SESSION['id'])) {
    die("Erreur : Vous devez être connecté pour modifier votre profil.");
}

// Connexion à la base de données
$database = new Database();
$db = $database->getConnection();

// Récupérer les informations actuelles de l'utilisateur
$query = "SELECT username, solde FROM user WHERE id = :id";
$stmt = $db->prepare($query);
$stmt->execute([':id' => $_SESSION['id']]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user) {
    die("Erreur : Utilisateur introuvable.");
}

// Initialiser les variables pour le formulaire
$error_message = "";
$success_message = "";

// Traitement du formulaire
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $new_username = trim($_POST['username']);
    $new_solde = trim($_POST['solde']);

    // Vérifier si le nouveau username est déjà pris
    $query = "SELECT id FROM user WHERE username = :username AND id != :id";
    $stmt = $db->prepare($query);
    $stmt->execute([':username' => $new_username, ':id' => $_SESSION['id']]);
    
    if ($stmt->rowCount() > 0) {
        $error_message = "Ce nom d'utilisateur est déjà pris.";
    } elseif (!is_numeric($new_solde) || $new_solde < 0) {
        $error_message = "Le solde doit être un nombre positif.";
    } else {
        // Mettre à jour l'utilisateur
        $query = "UPDATE user SET username = :username, solde = :solde WHERE id = :id";
        $stmt = $db->prepare($query);
        $stmt->execute([
            ':username' => $new_username,
            ':solde' => $new_solde,
            ':id' => $_SESSION['id']
        ]);

        // Mettre à jour la session avec le nouveau username
        $_SESSION['username'] = $new_username;

        // Rediriger vers profil.php après mise à jour
        header("Location: profil.php?username=" . urlencode($new_username));
        exit();

    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Modifier mon profil</title>
    <link rel="stylesheet" href="src/css/style.css">
</head>
<body>
    <h1>Modifier mon profil</h1>

    <?php if (!empty($error_message)) : ?>
        <p class="error"><?= htmlspecialchars($error_message) ?></p>
    <?php endif; ?>

    <?php if (!empty($success_message)) : ?>
        <p class="success"><?= htmlspecialchars($success_message) ?></p>
    <?php endif; ?>

    <form method="POST">
        <label for="username">Nouveau username :</label>
        <input type="text" id="username" name="username" value="<?= htmlspecialchars($user['username']) ?>" required>

        <label for="solde">Solde (€) :</label>
        <input type="number" id="solde" name="solde" value="<?= htmlspecialchars($user['solde']) ?>" required min="0">

        <a href = 'profil.php'><button type="submit">Mettre à jour</button></a>
    </form>

    <a href="profil.php?username=<?= urlencode($_SESSION['username']) ?>">Retour au profil</a>
</body>
</html>
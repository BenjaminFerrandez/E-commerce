<?php
session_start();
require_once "config/database.php";

// Vérifier si l'utilisateur est admin
if (!isset($_SESSION['id']) && $_SESSION['role'] != 'admin') {
    header("Location: ../index.php");
    exit();
}

$database = new Database();
$db = $database->getConnection();

// Récupérer tous les utilisateurs
$userQuery = $db->prepare("SELECT * FROM user");
$userQuery->execute();
$users = $userQuery->fetchAll(PDO::FETCH_ASSOC);

// Récupérer tous les articles avec leur stock
$articleQuery = $db->prepare("SELECT article.*, stock.quantite FROM article LEFT JOIN stock ON article.id = stock.article_id");
$articleQuery->execute();
$articles = $articleQuery->fetchAll(PDO::FETCH_ASSOC);


if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['changeQuantityArticle'])) {
    $stmt = $db->prepare("INSERT INTO commandes (user_id, montant_total, adresse, ville, code_postal) VALUES (?, ?, ?, ?, ?)");
    $stmt->execute([$userId, $total, $_POST['billing_address'], $_POST['billing_city'], $_POST['billing_cp']]);
    $commandeId = $db->lastInsertId();  
}


?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - Gestion des utilisateurs et articles</title>
    <link rel="stylesheet" href="../src/css/style.css">
</head>
<body>
    <h1>Panneau d'administration</h1>
    
    <h2>Utilisateurs</h2>
    <table>
        <tr>
            <th>ID</th>
            <th>Nom</th>
            <th>Rôle</th>
            <th>Actions</th>
        </tr>
        <?php foreach ($users as $user): ?>
            <tr>
                <td><?= htmlspecialchars($user['id']) ?></td>
                <td><?= htmlspecialchars($user['username']) ?></td>
                <td>
                    <form method="post" action="update_user.php">
                        <input type="hidden" name="user_id" value="<?= $user['id'] ?>">
                        <select name="role">
                            <option value="user" <?= $user['role'] === 'user' ? 'selected' : '' ?>>User</option>
                            <option value="admin" <?= $user['role'] === 'admin' ? 'selected' : '' ?>>Admin</option>
                        </select>
                        <button type="submit">Modifier</button>
                    </form>
                </td>
                <td>
                    <form method="post" action="delete_user.php">
                        <input type="hidden" name="user_id" value="<?= $user['id'] ?>">
                        <button type="submit">Supprimer</button>
                    </form>
                </td>
            </tr>
        <?php endforeach; ?>
    </table>
    
    <h2>Articles</h2>
    <table>
        <tr>
            <th>ID</th>
            <th>Nom</th>
            <th>Prix</th>
            <th>Quantité</th>
            <th>Actions</th>
        </tr>
        <?php foreach ($articles as $article): ?>
            <tr>
                <td><?= htmlspecialchars($article['id']) ?></td>
                <td><?= htmlspecialchars($article['nom']) ?></td>
                <td><?= htmlspecialchars($article['prix']) ?> €</td>
                <td>
                    <form method="post">
                        <input type="hidden" name="article_id" value="<?= $article['id'] ?>">
                        <input type="number" name="quantite" value="<?= $article['quantite'] ?? 0 ?>" min="0">
                        <button type="submit" name="changeQuantityArticle">Modifier</button>
                    </form>
                </td>
                <td>
                    <form method="post">
                        <input type="hidden" name="article_id" value="<?= $article['id'] ?>">
                        <button type="submit" name="deleteArticle">Supprimer</button>
                    </form>
                </td>
            </tr>
        <?php endforeach; ?>
    </table>
</body>
</html>
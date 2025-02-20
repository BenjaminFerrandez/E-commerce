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

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['changeNomArticle'])) {
    $stmt = $db->prepare("UPDATE article SET nom = ? WHERE id = ?");
    $stmt->execute([$_POST['nom'], $_POST['article_id']]);

    $slug = str_replace(" ", "_", strtolower($_POST['nom']));

    $stmt = $db->prepare("UPDATE article SET slug = ? WHERE id = ?");
    $stmt->execute([$slug, $_POST['article_id']]);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['changeDescriptionArticle'])) {
    $stmt = $db->prepare("UPDATE article SET description = ? WHERE id = ?");
    $stmt->execute([$_POST['description'], $_POST['article_id']]);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['changeImageArticle'])) {
    $stmt = $db->prepare("UPDATE article SET image_url = ? WHERE id = ?");
    $stmt->execute([$_POST['image'], $_POST['article_id']]);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['changePrixArticle'])) {
    $stmt = $db->prepare("UPDATE article SET prix = ? WHERE id = ?");
    $stmt->execute([$_POST['prix'], $_POST['article_id']]);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['changeQuantityArticle'])) {
    if ((int)$_POST['quantite'] > 0) {
        $stmt = $db->prepare("UPDATE article SET deleted = 0 WHERE id = ?");
        $stmt->execute([$_POST['article_id']]);
    }
    $stmt = $db->prepare("UPDATE stock SET quantite = ? WHERE article_id = ?");
    $stmt->execute([$_POST['quantite'], $_POST['article_id']]);
    $commandeId = $db->lastInsertId();  
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['deleteArticle'])) {
    $stmt = $db->prepare("UPDATE stock SET quantite = 0 WHERE article_id = ?");
    $stmt->execute([$_POST['article_id']]); 

    $stmt = $db->prepare("UPDATE article SET deleted = 1 WHERE id = ?");
    $stmt->execute([$_POST['article_id']]); 
}

// Récupérer tous les utilisateurs
$userQuery = $db->prepare("SELECT * FROM user");
$userQuery->execute();
$users = $userQuery->fetchAll(PDO::FETCH_ASSOC);

// Récupérer tous les articles avec leur stock
$articleQuery = $db->prepare("SELECT article.*, stock.quantite FROM article LEFT JOIN stock ON article.id = stock.article_id");
$articleQuery->execute();
$articles = $articleQuery->fetchAll(PDO::FETCH_ASSOC);





?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - Gestion des utilisateurs et articles</title>
    <link rel="stylesheet" href="src/css/style.css">
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
            <th>Description</th>
            <th>Image URL</th>
            <th>Prix</th>
            <th>Quantité</th>
            <th>Supprimé</th>
            <th>Actions</th>
        </tr>
        <?php foreach ($articles as $article): ?>
            <tr>
                <td><?= htmlspecialchars($article['id']) ?></td>
                <td>
                    <form method="post">
                        <input type="hidden" name="article_id" value="<?= $article['id'] ?>">
                        <input type="text" name="nom" value="<?= $article['nom']?>" min="0">
                        <button type="submit" name="changeNomArticle">Modifier</button>
                    </form>
                </td>
                <td>
                    <form method="post">
                        <input type="hidden" name="article_id" value="<?= $article['id'] ?>">
                        <input type="text" name="description" value="<?= $article['description']?>" min="0">
                        <button type="submit" name="changeDescriptionArticle">Modifier</button>
                    </form>
                </td>
                <td>
                    <form method="post">
                        <input type="hidden" name="article_id" value="<?= $article['id'] ?>">
                        <input type="text" name="image" value="<?= $article['image_url']?>" min="0">
                        <button type="submit" name="changeImageArticle">Modifier</button>
                    </form>
                </td>
                <td>
                    <form method="post">
                        <input type="hidden" name="article_id" value="<?= $article['id'] ?>">
                        <input type="number" name="prix" value="<?= $article['prix']?>" min="0">
                        <button type="submit" name="changePrixArticle">Modifier</button>
                    </form>
                </td>
                <td>
                    <form method="post">
                        <input type="hidden" name="article_id" value="<?= $article['id'] ?>">
                        <input type="number" name="quantite" value="<?= $article['quantite']?>" min="0">
                        <button type="submit" name="changeQuantityArticle">Modifier</button>
                    </form>
                </td>
                <td>
                    <?php
                        if ($article['deleted'] == 0) {
                            echo "<p>Disponible</p>";
                        } else {
                            echo "<p>Supprimé</p>";
                        }?>
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
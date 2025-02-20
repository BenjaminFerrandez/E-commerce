<?php
session_start();

// Inclure la connexion à la base de données
require_once "config/database.php";

// Vérifier si un username est passé en paramètre GET
if (!isset($_GET['username']) || empty($_GET['username'])) {
    die("Erreur : Aucun username sélectionné.");
}

// Récupérer l'username depuis l'URL
$user_username = $_GET['username']; // Sécurisation contre les injections SQL

// Connexion à la base de données
$database = new Database();
$db = $database->getConnection();

// Requête pour récupérer les infos du user + articles publiés
$query = "SELECT * FROM user INNER JOIN article ON user.id = article.user_id WHERE user.username = :username";
$stmt = $db->prepare($query);
$stmt->execute([':username' => $user_username]);

// Vérifier si l'utilisateur existe
if ($stmt->rowCount() == 0) {
    die("Erreur : Utilisateur introuvable.");
}

// Récupérer toutes les données de l'utilisateur
$user_data = [];
$articles = [];

while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    if (empty($user_data)) {
        $user_data = [
            'id' => $row['id'],
            'username' => $row['username'],
            //'email' => $row['email'],
            'solde' => $row['solde']
        ];
    }

    // Si l'utilisateur a publié des articles, on les ajoute à la liste
    if (empty($row['article_id'])) {
        $articles[] = [
            //'id' => $row['article_id'],
            'nom' => $row['nom'],
            'description' => $row['description'],
            'date_publication' => $row['date_publication'],
            'lien_image' => $row['image_url']
        ];
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profil de <?= htmlspecialchars($user_data['username']) ?></title>
    <link rel="stylesheet" href="src/css/style.css">
</head>
<body>

    <h1>Profil de <?= htmlspecialchars($user_data['username']) ?></h1>
    
    <p><strong>Solde :</strong> <?= htmlspecialchars($user_data['solde']) ?> €</p>

    <h2>Articles publiés :</h2>
    <?php if (empty($articles)) : ?>
        <p>Aucun article publié.</p>
    <?php else : ?>
        <div class="articles">
            <?php foreach ($articles as $article) : ?>
                <div class="article">
                    <h3><?= htmlspecialchars($article['nom']) ?></h3>
                    <img src="<?= htmlspecialchars($article['lien_image']) ?>" alt="<?= htmlspecialchars($article['nom']) ?>" width="200">
                    <p><?= htmlspecialchars($article['description']) ?></p>
                    <p><strong>Publié le :</strong> <?= htmlspecialchars($article['date_publication']) ?></p>
                    <?php 
                        if ($stmt->rowCount() > 0) {
                            while ($article = $stmt->fetch(PDO::FETCH_ASSOC)) {
                                echo "<a href='product.php?slug=" . $article['slug'] . "'>Voir l'article</a>";
                            }
                        } else {
                            echo "<p>Aucun article en vente pour le moment.</p>";
                        }  
                        ?>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
    <a href="index.php">Retour à l'accueil</a>
</body>
</html>
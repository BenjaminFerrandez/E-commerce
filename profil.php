<?php
session_start();

// Inclure la connexion à la base de données
require_once "config/database.php";

// Vérifier si un username est passé en paramètre GET
if (!isset($_GET['username']) || empty($_GET['username'])) {
    die("Erreur : Aucun username sélectionné.");
}

// Récupérer l'username depuis l'URL
$user_username = $_GET['username'];

// Connexion à la base de données
$database = new Database();
$db = $database->getConnection();

// Requête SQL corrigée avec LEFT JOIN
$query = "SELECT user.id, user.username, user.solde,
           article.id AS article_id, article.nom AS article_nom, article.slug, 
           article.description, article.date_publication, article.image_url
    FROM user
    LEFT JOIN article ON user.id = article.user_id
    WHERE user.username = :username
";

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
            'solde' => $row['solde']
        ];
    }

    // Ajouter les articles s'ils existent
    if (!empty($row['article_id'])) {
        $articles[] = [
            'id' => $row['article_id'],
            'nom' => $row['article_nom'],
            'slug' => $row['slug'],
            'image_url' => $row['image_url'],
            'description' => $row['description'],
            'date_publication' => $row['date_publication']
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
    
    <?php if ($_SESSION['username'] == $user_data['username']) : ?>
    <p><strong>Solde :</strong> <?= htmlspecialchars($user_data['solde']) ?> €</p>
    <?php endif; ?>

    <h2>Articles publiés :</h2>
    <?php if (empty($articles)) : ?>
        <p>Aucun article publié.</p>
    <?php else : ?>
        <div class="articles">
            <?php foreach ($articles as $article) : ?>
                <div class="article">
                    <h3><?= htmlspecialchars($article['nom']) ?></h3>
                    <?php echo "<img src='" . htmlspecialchars($article['image_url']) . "' alt='" . htmlspecialchars($article['nom']) . "' width='200'>"?>
                    <p><?= htmlspecialchars($article['description']) ?></p>
                    <p><strong>Publié le :</strong> <?= htmlspecialchars($article['date_publication']) ?></p>
                    <a href="product.php?slug=<?= htmlspecialchars($article['slug']) ?>">Voir l'article</a>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <a href="index.php">Retour à l'accueil</a>
</body>
</html>
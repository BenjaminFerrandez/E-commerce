<?php
// Inclure la connexion à la base de données
require_once "config/database.php";

// Vérifier si un slug est passé en paramètre GET
if (!isset($_GET['slug']) || empty($_GET['slug'])) {
    die("Erreur : Aucun article sélectionné.");
}

// Récupérer le slug de l'article depuis l'URL
$article_slug = ($_GET['slug']); // Sécurisation contre les injections SQL

// Connexion à la base de données
$database = new Database();
$db = $database->getConnection();

// Préparer et exécuter la requête pour récupérer les détails de l'article + le nom de l'utilisateur
$query = "SELECT article.*, user.username FROM article INNER JOIN user ON article.user_id = user.id WHERE article.slug = :slug";
$stmt = $db->prepare($query);
$stmt->bindParam(':slug', $article_slug, PDO::PARAM_STR);
$stmt->execute();

// Vérifier si l'article existe
if ($stmt->rowCount() == 0) {
    die("Erreur : Article introuvable.");
}

// Récupérer les données de l'article
$article = $stmt->fetch(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($article['nom']) ?></title>
    <link rel="stylesheet" href="src/css/style.css">
</head>
<body>

    <h1><?= htmlspecialchars($article['nom']) ?></h1>
    <img src="<?= htmlspecialchars($article['image_url']) ?>" alt="<?= htmlspecialchars($article['nom']) ?>" width="300">
    <p><strong>Description :</strong> <?= htmlspecialchars($article['description']) ?></p>
    <p><strong>Publié le :</strong> <?= htmlspecialchars($article['date_publication']) ?></p>
    <p><strong>Par :</strong> <?= htmlspecialchars($article['username']) ?></p>
    <p><strong>Prix :</strong> <?= htmlspecialchars($article['prix'])?> €</p>
    <a href="index.php">Retour à l'accueil</a>
</body>
</html>
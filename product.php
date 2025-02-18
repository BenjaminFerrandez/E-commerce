<?php
// Inclure la connexion à la base de données
require_once "config/database.php";

// Vérifier si un ID d'article est passé en paramètre GET
if (!isset($_GET['id']) || empty($_GET['id'])) {
    die("Erreur : Aucun article sélectionné.");
}

// Récupérer l'ID de l'article depuis l'URL
$article_id = intval($_GET['id']); // Sécurisation contre les injections SQL

// Connexion à la base de données
$database = new Database();
$db = $database->getConnection();

// Préparer et exécuter la requête pour récupérer les détails de l'article
$query = "SELECT * FROM article WHERE id = :id";
$stmt = $db->prepare($query);
$stmt->bindParam(':id', $article_id, PDO::PARAM_INT);
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
    <p><strong>Prix :</strong> <?= htmlspecialchars($article['prix'])?> €</p>

    <a href="index.php">Retour à l'accueil</a>

</body>
</html>
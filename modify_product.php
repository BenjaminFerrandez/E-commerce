<?php
require_once "config/database.php";
require_once 'functions.php';

ini_set('display_errors', 1);
error_reporting(E_ALL);

$database = new Database();
$db = $database->getConnection();

if (!$db) {
    die("La connexion à la base de données a échoué.");
}

// Vérifier si un ID est passé dans l'URL
if (!isset($_GET['id']) || empty($_GET['id'])) {
    die("Aucun article spécifié.");
}

$article_id = intval($_GET['id']);

// Récupérer les infos de l'article
$stmt = $db->prepare("SELECT * FROM article WHERE id = ?");
$stmt->execute([$article_id]);
$article = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$article) {
    die("Article introuvable.");
}

// Récupérer la quantité depuis la table stock
$stmt = $db->prepare("SELECT quantite FROM stock WHERE article_id = ?");
$stmt->execute([$article_id]);
$stock = $stmt->fetch(PDO::FETCH_ASSOC);
$quantite = $stock ? $stock['quantite'] : 0;

// Traitement du formulaire de modification
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['edit_article'])) {
    $nom = htmlspecialchars($_POST['nom']);
    $slug = generateSlug($nom);
    $description = htmlspecialchars($_POST['description']);
    $image_url = htmlspecialchars($_POST['image_url']);
    $prix = floatval($_POST['prix']);
    $quantite = intval($_POST['quantite']);

    try {
        $db->beginTransaction();

        // Mise à jour de l'article
        $stmt = $db->prepare("UPDATE article SET nom = ?, slug = ?, description = ?, image_url = ?, prix = ? WHERE id = ?");
        $stmt->execute([$nom, $slug, $description, $image_url, $prix, $article_id]);

        // Mise à jour du stock
        $stmt = $db->prepare("UPDATE stock SET quantite = ? WHERE article_id = ?");
        $stmt->execute([$quantite, $article_id]);

        $db->commit();
        echo "Article mis à jour avec succès !";
        header("Location: create_product.php"); // Redirection après modification
        exit();
    } catch (Exception $e) {
        $db->rollBack();
        echo "Erreur : " . $e->getMessage();
    }
}

?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Modifier l'article</title>
</head>
<body>

<h1>Modifier l'article</h1>
<form method="POST">
    <label>Nom :</label>
    <input type="text" name="nom" value="<?php echo htmlspecialchars($article['nom']); ?>" required><br><br>

    <label>Description :</label>
    <textarea name="description" required><?php echo htmlspecialchars($article['description']); ?></textarea><br><br>

    <label>URL de l'image :</label>
    <input type="text" name="image_url" value="<?php echo htmlspecialchars($article['image_url']); ?>" required><br><br>

    <label>Prix :</label>
    <input type="number" name="prix" step="0.01" value="<?php echo htmlspecialchars($article['prix']); ?>" required><br><br>

    <label>Quantité :</label>
    <input type="number" name="quantite" value="<?php echo htmlspecialchars($quantite); ?>" required><br><br>

    <button type="submit" name="edit_article">Modifier</button>
</form>


<a href="create_product.php">Retour</a>

</body>
</html>

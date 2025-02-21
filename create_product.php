<?php 
require_once "config/database.php";
require_once 'functions.php';

ini_set('display_errors', 1);
error_reporting(E_ALL);

// Vérifier si l'utilisateur est connecté
if (!isset($_SESSION['id'])) {
    header('Location: /E-commerce/register.php');
    exit();
}

$error = "";
$success = "";

$database = new Database();
$db = $database->getConnection();

if (!$db) {
    die("La connexion à la base de données a échoué.");
}

// Vérifier si "username" est défini dans l'URL
$username = isset($_GET["username"]) ? $_GET["username"] : $_SESSION['username'];

// Créer un nouvel article
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['create_article'])) {
    $nom = htmlspecialchars($_POST['nom']);
    $slug = generateSlug($nom);
    $description = htmlspecialchars($_POST['description']);
    $image_url = htmlspecialchars($_POST['image_url']);
    $prix = floatval($_POST['prix']);
    $quantite = intval($_POST['quantite']);
    $user_id = $_SESSION['id'];

    // Vérifier si l'article existe déjà
    $stmt = $db->prepare("SELECT id FROM article WHERE slug = ?");
    $stmt->execute([$slug]);

    if ($stmt->rowCount() > 0) {
        $error = "Un article avec ce nom existe déjà.";
    } else {
        try {
            $db->beginTransaction();

            // Insérer l'article avec l'ID de l'utilisateur
            $stmt = $db->prepare("INSERT INTO article (nom, slug, description, image_url, prix, user_id) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->execute([$nom, $slug, $description, $image_url, $prix, $user_id]);

            $article_id = $db->lastInsertId();

            // Ajouter le stock
            $stmt = $db->prepare("INSERT INTO stock (article_id, quantite) VALUES (?, ?)");
            $stmt->execute([$article_id, $quantite]);

            $db->commit();
            $success = "Article créé avec succès !";

            // Rafraîchir la page pour afficher l'article immédiatement
            header("Location: create_product.php?username=" . urlencode($username));
            exit();
        } catch (Exception $e) {
            $db->rollBack();
            $error = "Erreur lors de la création de l'article : " . $e->getMessage();
        }
    }
}

// Supprimer un article
if (isset($_GET['delete_id'])) {
    $delete_id = intval($_GET['delete_id']);
    try {
        $db->beginTransaction();

        // Supprimer d'abord les entrées liées dans `cart`
        $stmt = $db->prepare("DELETE FROM cart WHERE article_id = ?");
        $stmt->execute([$delete_id]);

        // Supprimer les stocks liés à l'article
        $stmt = $db->prepare("DELETE FROM stock WHERE article_id = ?");
        $stmt->execute([$delete_id]);

        // Supprimer l'article
        $stmt = $db->prepare("DELETE FROM article WHERE id = ?");
        $stmt->execute([$delete_id]);

        $db->commit();
        $success = "Article supprimé avec succès !";
        header("Location: create_product.php?username=" . urlencode($username));
        exit();
    } catch (Exception $e) {
        $db->rollBack();
        $error = "Erreur lors de la suppression de l'article : " . $e->getMessage();
    }
}


// Modifier un article
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['edit_article'])) {
    $edit_id = intval($_POST['edit_id']);
    $nom = htmlspecialchars($_POST['nom']);
    $slug = generateSlug($nom);
    $description = htmlspecialchars($_POST['description']);
    $image_url = htmlspecialchars($_POST['image_url']);
    $prix = floatval($_POST['prix']);
    $quantite = intval($_POST['quantite']);

    try {
        $db->beginTransaction();

        $stmt = $db->prepare("UPDATE article SET nom = ?, slug = ?, description = ?, image_url = ?, prix = ? WHERE id = ?");
        $stmt->execute([$nom, $slug, $description, $image_url, $prix, $edit_id]);

        $stmt = $db->prepare("UPDATE stock SET quantite = ? WHERE article_id = ?");
        $stmt->execute([$quantite, $edit_id]);

        $db->commit();
        $success = "Article mis à jour avec succès !";

        // Rafraîchir la page après modification
        header("Location: create_product.php?username=" . urlencode($username));
        exit();
    } catch (Exception $e) {
        $db->rollBack();
        $error = "Erreur lors de la mise à jour de l'article : " . $e->getMessage();
    }
}

// Récupérer les articles de l'utilisateur connecté
$stmt = $db->prepare("SELECT * FROM article WHERE user_id = ?");
$stmt->execute([$_SESSION["id"]]);
$articles = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Créer un Article</title>
</head>
<body>

    <!-- Affichage des messages -->
    <?php if ($error): ?>
        <p style='color:red;'><?= $error; ?></p>
    <?php endif; ?>
    <?php if ($success): ?>
        <p style='color:green;'><?= $success; ?></p>
    <?php endif; ?>

    <h1>Créer un Article</h1>
    <form method="POST">
        <label>Nom de l'article :</label>
        <input type="text" name="nom" required><br><br>

        <label>Description :</label>
        <textarea name="description" required></textarea><br><br>

        <label>URL de l'image :</label>
        <input type="text" name="image_url" required><br><br>

        <label>Prix :</label>
        <input type="number" name="prix" step="0.01" required><br><br>

        <label>Quantité :</label>
        <input type="number" name="quantite" required><br><br>

        <button type="submit" name="create_article">Créer l'article</button>
    </form>

    <h1>Vos articles</h1>
    <table border="1">
        <thead>
            <tr>
                <th>Nom</th>
                <th>Description</th>
                <th>Prix</th>
                <th>Quantité</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($articles as $article): ?>
                <tr>
                    <td><?= htmlspecialchars($article['nom']); ?></td>
                    <td><?= htmlspecialchars($article['description']); ?></td>
                    <td><?= number_format($article['prix'], 2, ',', ' '); ?> €</td>
                    <td>
                        <?php
                            $stmt = $db->prepare("SELECT quantite FROM stock WHERE article_id = ?");
                            $stmt->execute([$article['id']]);
                            $stock = $stmt->fetch();
                            echo $stock ? $stock['quantite'] : 0;
                        ?>
                    </td>
                    <td>
                    <a href="modify_product.php?id=<?php echo $article['id']; ?>">Modifier</a>

                     <a href="?delete_id=<?= $article['id']; ?>" onclick="return confirm('Êtes-vous sûr ?')">Supprimer</a>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <a href="index.php"><button type="button">Retour à l'Accueil</button></a>

</body>
</html>

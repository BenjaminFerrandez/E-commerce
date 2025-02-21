<?php 
require_once "config/database.php";
require_once 'functions.php';

// Afficher les erreurs PHP pour faciliter le débogage
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

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['changeNomArticle'])) {
    echo "aaaa";
    $stmt = $db->prepare("UPDATE article SET nom = ? WHERE id = ?");
    echo "bbb";
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


    // Créer un nouvel article
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        if (isset($_POST['create_article'])) {
            // Récupérer les valeurs du formulaire et les sécuriser
            $nom = htmlspecialchars($_POST['nom']);
            $slug = generateSlug($nom);
            $description = htmlspecialchars($_POST['description']);
            $image_url = htmlspecialchars($_POST['image_url']);
            $prix = floatval($_POST['prix']);
            $quantite = intval($_POST['quantite']);

            // Vérifier si l'article existe déjà
            $stmt = $db->prepare("SELECT id FROM article WHERE slug = ?");
            $stmt->execute([$slug]);

            if ($stmt->rowCount() > 0) {
                $error = "Un article avec ce nom existe déjà.";
            } else {
                try {
                    // Démarrer la transaction
                    $db->beginTransaction();

                    // Insérer l'article
                    $stmt = $db->prepare("INSERT INTO article (user_id, nom, slug, description, image_url, prix) VALUES (?, ?, ?, ?, ?, ?)");
                    $stmt->execute([$_SESSION["id"], $nom, $slug, $description, $image_url, $prix]);

                    // Récupérer l'ID de l'article inséré
                    $article_id = $db->lastInsertId();

                    // Ajouter le stock
                    $stmt = $db->prepare("INSERT INTO stock (article_id, quantite) VALUES (?, ?)");
                    $stmt->execute([$article_id, $quantite]);

                    // Commit de la transaction
                    $db->commit();
                    $success = "Article créé avec succès !";
                } catch (Exception $e) {
                    // En cas d'erreur, annuler la transaction
                    $db->rollBack();
                    $error = "Erreur lors de la création de l'article : " . $e->getMessage();
                }
            }
        }

        // Supprimer un article
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['deleteArticle'])) {
            $stmt = $db->prepare("UPDATE stock SET quantite = 0 WHERE article_id = ?");
            $stmt->execute([$_POST['article_id']]); 
        
            $stmt = $db->prepare("UPDATE article SET deleted = 1 WHERE id = ?");
            $stmt->execute([$_POST['article_id']]); 
        }

        // Modifier un article
        if (isset($_POST['edit_article'])) {
            $edit_id = intval($_POST['edit_id']);
            $nom = htmlspecialchars($_POST['nom']);
            $slug = generateSlug($nom);
            $description = htmlspecialchars($_POST['description']);
            $image_url = htmlspecialchars($_POST['image_url']);
            $prix = floatval($_POST['prix']);
            $quantite = intval($_POST['quantite']);

            try {
                $db->beginTransaction();

                // Mettre à jour l'article
                $stmt = $db->prepare("UPDATE article SET nom = ?, slug = ?, description = ?, image_url = ?, prix = ? WHERE id = ?");
                $stmt->execute([$nom, $slug, $description, $image_url, $prix, $edit_id]);

                // Mettre à jour le stock
                $stmt = $db->prepare("UPDATE stock SET quantite = ? WHERE article_id = ?");
                $stmt->execute([$quantite, $edit_id]);

                // Commit de la transaction
                $db->commit();
                $success = "Article mis à jour avec succès !";
            } catch (Exception $e) {
                // En cas d'erreur, annuler la transaction
                $db->rollBack();
                $error = "Erreur lors de la mise à jour de l'article : " . $e->getMessage();
            }
        }
    }

// Récupérer les articles existants

$stmt = $db->prepare("SELECT * FROM article INNER JOIN user ON article.user_id = user.id INNER JOIN stock ON article.id = stock.article_id WHERE user.username = ?");
$stmt->execute([$_SESSION["username"]]);
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

    <!-- Affichage des messages d'erreur et de succès -->
    <?php
        if ($error) {
            echo "<p style='color:red;'>$error</p>";
        }
        if ($success) {
            echo "<p style='color:green;'>$success</p>";
        }
    ?>

    <h1>Créer un Article</h1>
    <form method="POST">
        <label for="nom">Nom de l'article :</label>
        <input type="text" id="nom" name="nom" required><br><br>

        <label for="description">Description :</label>
        <textarea id="description" name="description" required></textarea><br><br>

        <label for="image_url">URL de l'image :</label>
        <input type="text" id="image_url" name="image_url" required><br><br>

        <label for="prix">Prix :</label>
        <input type="number" id="prix" name="prix" step="0.01" required><br><br>

        <label for="quantite">Quantité :</label>
        <input type="number" id="quantite" name="quantite" required><br><br>

        <button type="submit" name="create_article">Créer l'article</button>
    </form>

        <h1>Vos articles</h1>
        <table border="1">
            <thead>
                <tr>
                    <th>Id</th>
                    <th>Nom</th>
                    <th>Descrition</th>
                    <th>Image</th>
                    <th>Prix</th>
                    <th>Quantité</th>
                </tr>
            </thead>
            <tbody>
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
            </tbody>
        </table>

    
    <a href="index.php">
        <button type="button">Retour à l'Accueil</button>
    </a>

</body>
</html>

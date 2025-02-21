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
                    $stmt = $db->prepare("INSERT INTO article (nom, slug, description, image_url, prix) VALUES (?, ?, ?, ?, ?)");
                    $stmt->execute([$nom, $slug, $description, $image_url, $prix]);

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
        if (isset($_GET['delete_id'])) {
            $delete_id = intval($_GET['delete_id']);
            try {
                $db->beginTransaction();

                // Supprimer l'article du stock
                $stmt = $db->prepare("DELETE FROM stock WHERE article_id = ?");
                $stmt->execute([$delete_id]);

                // Supprimer l'article
                $stmt = $db->prepare("DELETE FROM article WHERE id = ?");
                $stmt->execute([$delete_id]);

                // Commit de la transaction
                $db->commit();
                $success = "Article supprimé avec succès !";
            } catch (Exception $e) {
                // En cas d'erreur, annuler la transaction
                $db->rollBack();
                $error = "Erreur lors de la suppression de l'article : " . $e->getMessage();
            }
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
$stmt = $db->prepare("SELECT * FROM article INNER JOIN user ON article.user_id = user.id WHERE user.username = ?");
$stmt->execute([$_GET["username"]]);
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
                        <td><?php echo htmlspecialchars($article['nom']); ?></td>
                        <td><?php echo htmlspecialchars($article['description']); ?></td>
                        <td><?php echo number_format($article['prix'], 2, ',', ' '); ?> €</td>
                        <td>
                            <?php
                                // Récupérer la quantité depuis la table stock
                                $stmt = $db->prepare("SELECT quantite FROM stock WHERE article_id = ?");
                                $stmt->execute([$article['id']]);
                                $stock = $stmt->fetch();
                                echo $stock ? $stock['quantite'] : 0;
                            ?>
                        </td>
                        <td>
                            <!-- Modifier -->
                            <a href="modify_product.php"<?php echo $article['id']; ?>">Modifier</a> |
                            <!-- Supprimer -->
                            <a href="?delete_id=<?php echo $article['id']; ?>" onclick="return confirm('Êtes-vous sûr de vouloir supprimer cet article ?')">Supprimer</a>
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

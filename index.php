<?php
// Inclure la connexion à la base de données
require_once "config/database.php";
require_once 'functions.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['addToCart'])) {
    if (isset($_POST['article'])) {
        $articleId = $_POST['article'];
        addToCart($articleId);
    }
}

// Connexion à la base de données
$database = new Database();
$db = $database->getConnection();

// Requête pour récupérer les articles
$query = "SELECT * FROM article ORDER BY date_publication DESC";
$stmt = $db->prepare($query);
$stmt->execute();
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Accueil - Articles en vente</title>
    <link rel="stylesheet" href="src/css/style.css">
</head>
<body>

    <nav>
        <div class="title">
            <h1>PHONE</h1>
        </div>
        <div class="searchBar">
            <input type="search" name="search" id="search" placeholder="Search for article">
        </div>

        <div class="CTANav">

            <ul>
                <li><a href="">Creer un article</a></li>
                <li><a href="">Wishlist</a></li>
                <li><a href="">Panier</a></li>
            </ul>

            <?php
                if (isset($_SESSION['id'])) {
                    echo "<div class='userLog'>";
                    echo "<div class='profilPic'></div>";
                    echo "<p>" . $_SESSION['username'] . "</p>";
                    echo "</div>";
                } else {
                    echo "<div class='user'>";
                    echo "<p>LOGIN</p>";
                    echo "</div>";
                }
            ?>
        </div>

    </nav>

    <h1>Articles en vente</h1>

    <div class="articles">
        
        <?php
        // Vérifier s'il y a des articles
        if ($stmt->rowCount() > 0) {
            while ($article = $stmt->fetch(PDO::FETCH_ASSOC)) {
                echo "<div class='article'>";
                echo "<h2>" . htmlspecialchars($article['nom']) . "</h2>";
                //echo "<p>" . htmlspecialchars($article['description']) . "</p>";
                echo "<img src='" . htmlspecialchars($article['image_url']) . "' alt='" . htmlspecialchars($article['nom']) . "' width='200'>";
                echo "<p>Prix : " . htmlspecialchars($article['prix']) . " €</p>";
                //echo "<p>Publié le : " . htmlspecialchars($article['date_publication']) . "</p>";
                echo "<a href='product.php?id=" . $article['id'] . "'>Voir l'article</a>";
                echo "<form method='post'>";
                echo "<input type='submit' name='addToCart' value='AddToCart'/>";
                echo "<input type='hidden' name='article' value='" . $article["id"] . "'>";
                echo "</form>";
                echo "</div>";
            }
        } else {
            echo "<p>Aucun article en vente pour le moment.</p>";
        }
        ?>
        
    </div>

</body>
</html>

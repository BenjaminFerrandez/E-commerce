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

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['addItem'])) {
        addItem($_POST['article_id']);
        header('Location: /E-commerce/index.php');
        exit();
    } elseif (isset($_POST['removeItem'])) {
        removeItem($_POST['article_id']);
        header('Location: /E-commerce/index.php');
        exit();
    }
}

// Connexion à la base de données
$database = new Database();
$db = $database->getConnection();

// Afficher les articles
$query = "SELECT * FROM article WHERE deleted = 0 ORDER BY date_publication DESC";
$stmt = $db->prepare($query);
$stmt->execute();

$query = "SELECT * FROM user";
$request_user = $db->prepare($query);
$request_user->execute();
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
            <a href="index.php"><h1>PHONE</h1></a>
        </div>
        <div class="searchBar">
            <input type="search" name="search" id="search" placeholder="Search for article">
        </div>

        <div class="CTANav">

            <ul>
                <li><a href="create_product.php">Creer un article</a></li>
                <li><a href="">Wishlist</a></li>
                <li><a href="cart.php">Panier</a></li>
            </ul>

                <?php
                    $user = $request_user->fetch(PDO::FETCH_ASSOC);
                    echo "<a href='profil.php?username=" . $user['username'] . "'>";
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
                    echo "</a>";
                ?>
            <?php
                if (isset($_SESSION['id'])) {
                    echo "<div class='userLog'>";
                    echo "<div class='profilPic'></div>";
                    echo "<p>" . $_SESSION['username'] . "</p>";
                    echo "<div class='userMenuHitbox'>";
                    echo "<div class='userMenu'>";
                    echo "<ul>";
                    echo "<li><a href='profil.php?username=" . $user['username'] . "'>Mon profil</a></li>";
                    echo "<li><a href='commandes.php'>Mes Commandes</a></li>";
                    echo "<li><a href=''>Déconnexion</a></li>";
                    echo "</ul>";
                    echo "</div>";
                    echo "</div>";
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

        $query = "SELECT article_id, quantite FROM cart WHERE user_id = ?";
        $cartRequest = $db->prepare($query);
        $cartRequest->execute([$_SESSION['id']]);
        $cartItems = $cartRequest->fetchAll(PDO::FETCH_ASSOC);

        if ($stmt->rowCount() > 0) {
            while ($article = $stmt->fetch(PDO::FETCH_ASSOC)) {
                echo "<div class='article'>";
                echo "<div class='articleImage'>";
                echo "<img src='" . htmlspecialchars($article['image_url']) . "' alt='" . htmlspecialchars($article['nom']) . "' width='200'>";
                echo "</div>";
                echo "<div class='articleContent'>";
                echo "<h2>" . htmlspecialchars($article['nom']) . "</h2>";
                echo "<p>Prix : " . htmlspecialchars($article['prix']) . " €</p>";

                $cartIds = array_column($cartItems, 'article_id');

                if (in_array($article["id"], $cartIds)) {
                    $articleIndex = array_search($article["id"], $cartIds);
                    $articleQuantity = $cartItems[$articleIndex]['quantite'];

                    echo "<form method='post'>";
                    echo "<input type='hidden' name='article_id' value='" . $article["id"] . "'>";
                    echo "<input type='submit' name='removeItem' value='-'/>";
                    echo "<span>" . $articleQuantity . "</span>";
                    echo "<input type='submit' name='addItem' value='+'/>";
                    echo "</form>";

                } else {
                    echo "<form method='post'>";
                    echo "<input type='submit' name='addToCart' value='AddToCart'/>";
                    echo "<input type='hidden' name='article' value='" . $article["id"] . "'>";
                    echo "</form>";
                }
                
                echo "<a href='product.php?slug=" . $article['slug'] . "'>Voir l'article</a>";
                echo "</div>";
                echo "</div>";
            }
        } else {
            echo "<p>Aucun article en vente pour le moment.</p>";
        }
        ?>
        
    </div>

</body>
</html>
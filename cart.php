<?php

require_once 'config/database.php';
require_once 'functions.php';

if (!isset($_SESSION['id'])) {
    echo "aaa";
    header('Location: index.php');
    exit();
}

$database = new Database();
$db = $database->getConnection();

$username = $_SESSION['username'];
$userId = $_SESSION['id'];


// Join user et cart
$query = "SELECT * FROM user INNER JOIN cart ON user.id = cart.user_id WHERE cart.user_id = ?";
$userRequest = $db->prepare($query);
$userRequest->execute([$userId]);
$user = $userRequest->fetch(PDO::FETCH_ASSOC);



// Join article cart
$query = "SELECT * FROM article INNER JOIN cart ON article.id = cart.article_id WHERE cart.user_id = ?";
$articleRequest = $db->prepare($query);
$articleRequest->execute([$userId]);

// Join stock article
$query = "SELECT * FROM stock INNER JOIN article ON stock.article_id = article.id WHERE article.id = ?";
$stockRequest = $db->prepare($query);
$stockRequest->execute([$userId]);

// Solde user
$stmt = $db->prepare("SELECT solde FROM user WHERE username = ?");
$stmt->execute([$username]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);


$stmt = $db->prepare("SELECT * FROM article");
$stmt->execute();


if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['addItem'])) {
        if ($_POST['article_stock'] > $_POST["article_quantity"]) {
            addItem($_POST['article_id']);
            header('Location: /E-commerce/cart.php');
        } else {
            echo "pas assez de stocks";
        }
        
    } elseif (isset($_POST['removeItem'])) {
        removeItem($_POST['article_id']);
        header('Location: /E-commerce/cart.php');
    }
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
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
                <li><a href="">Creer un article</a></li>
                <li><a href="">Wishlist</a></li>
                <li><a href="cart.php">Panier</a></li>
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

    <h2>PANIER</h2>

    <div class="cartLanding">

        <div class="wrap">

            <?php


                $stmt = $db->prepare("SELECT solde FROM user WHERE username = ?");
                $stmt->execute([$username]);
                $user = $stmt->fetch(PDO::FETCH_ASSOC);


                $query = "SELECT article.*, stock.quantite 
                FROM cart
                INNER JOIN article ON cart.article_id = article.id
                LEFT JOIN stock ON stock.article_id = article.id
                WHERE cart.user_id = ?";
                $articleRequest = $db->prepare($query);
                $articleRequest->execute([$_SESSION["id"]]);
                $articles = $articleRequest->fetchAll(PDO::FETCH_ASSOC);

                $query = "SELECT article_id, quantite FROM cart WHERE user_id = ?";
                $cartRequest = $db->prepare($query);
                $cartRequest->execute([$_SESSION['id']]);
                $cartItems = $cartRequest->fetchAll(PDO::FETCH_ASSOC);

                $total = 0;

                if (count($articles) > 0) {
                    foreach ($articles as $article) {

                        $cartIds = array_column($cartItems, 'article_id');

                        if (in_array($article["id"], $cartIds)) {
                            $articleIndex = array_search($article["id"], $cartIds);
                            $articleQuantity = $cartItems[$articleIndex]['quantite'];
                        }

                        $sub_total = $articleQuantity * $article['prix'];
                        echo $sub_total;

                        echo "<div class='cartArticle'>";
                        echo "<div class='cartArticlePicture'>";
                        echo "<img src='" . htmlspecialchars($article['image_url']) . "' alt='" . htmlspecialchars($article['nom']) . "'width='120'>";
                        echo "</div>";
                        echo "<div class='cartArticleDesc'>";
                        echo "<h2>" . htmlspecialchars($article['nom']) . "</h2>";
                        echo "<p>Prix : " . htmlspecialchars($article['prix']) . " €</p>";
                        echo "<a href='product.php?slug=" . $article['slug'] . "'>Voir l'article</a>";
                        echo "<form method='post'>";
                        echo "<input type='hidden' name='article_id' value='" . $article["id"] . "'>";
                        echo "<input type='hidden' name='article_stock' value='" . $article["quantite"] . "'>";
                        echo "<input type='hidden' name='article_quantity' value='" . $articleQuantity . "'>";
                        echo "<input type='submit' name='removeItem' value='-'/>";
                        echo "<span>" . $articleQuantity . "</span>";
                        echo "<input type='submit' name='addItem' value='+'/>";
                        echo "</form>";
                        echo "<p>Stock : " . ($article["quantite"] ?? 'Indisponible') . "</p>";
                        echo "</div>";
                        echo "</div>";
                    }
                } else {
                    echo "<p>Votre panier est vide.</p>";
                }

            ?>

        </div>

    </div>

    <a href="cart/validate.php" style="display: flex"><p>valider le panier</p></a>
    
    
</body>
</html>
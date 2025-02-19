<?php

require_once 'config/database.php';
require_once 'functions.php';

if (!isset($_SESSION['id'])) {
    echo "aaa";
    //header('Location: index.php');
    exit();
}

$database = new Database();
$db = $database->getConnection();

$username = $_SESSION['username'];
echo $username;
$userId = $_SESSION['id'];
echo $userId;


// Join user et cart

$query = "SELECT * FROM user INNER JOIN cart ON user.id = cart.user_id WHERE cart.user_id = ?";
$userRequest = $db->prepare($query);
$userRequest->execute([$userId]);
$user = $userRequest->fetch(PDO::FETCH_ASSOC);

var_dump($user);


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

echo("aaaa");
echo($username);
echo($user["solde"]);

$stmt = $db->prepare("SELECT * FROM article");
$stmt->execute();

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

    <h2>PANIER</h2>

    <div class="wrap">

        <?php


            $stmt = $db->prepare("SELECT solde FROM user WHERE username = ?");
            $stmt->execute([$username]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

        

            if ($articleRequest->rowCount() > 0) {
                while ($article = $articleRequest->fetch(PDO::FETCH_ASSOC)) {
                    $query = "SELECT * FROM stock INNER JOIN article ON stock.article_id = article.id WHERE article.id = ?";
                    $stockRequest = $db->prepare($query);
                    $stockRequest->execute([$article['id']]);
                    $stock = $stockRequest->fetch(PDO::FETCH_ASSOC);
                    echo "<div class='article'>";
                    echo "<h2>" . htmlspecialchars($article['nom']) . "</h2>";
                    //echo "<p>" . htmlspecialchars($article['description']) . "</p>";
                    echo "<img src='" . htmlspecialchars($article['image_url']) . "' alt='" . htmlspecialchars($article['nom']) . "'>";
                    echo "<p>Prix : " . htmlspecialchars($article['prix']) . " €</p>";
                    //echo "<p>Publié le : " . htmlspecialchars($article['date_publication']) . "</p>";
                    echo "<a href='product.php?slug=" . $article['slug'] . "'>Voir l'article</a>";
                    echo "<p>Stock : " . $stock["quantite"] . "</p>";
                    echo "</div>";
                }
            } else {
                echo "<p>Aucun article en vente pour le moment.</p>";
            }
        ?>

    </div>

    
    
</body>
</html>
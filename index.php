<?php

// Inclure la connexion à la base de données
require_once "config/database.php";
require_once "functions.php";

// Vérifier si le bouton "Déconnexion" a été cliqué
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['Deconnexion'])) {
    session_destroy(); // Supprime la session
    header("Location: index.php"); // Recharge la page pour refléter la déconnexion
    exit();
}

// Connexion à la base de données
$database = new Database();
$db = $database->getConnection();

// Vérifier si l'utilisateur est connecté
$userLoggedIn = isset($_SESSION['id']);

// Ajouter un article au panier
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['addToCart']) && $userLoggedIn) {
    $articleId = $_POST['article'];
    addToCart($articleId);
}

// Ajouter/Supprimer un article du panier
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['addItem'])) {
        if ($_POST['article_stock'] > $_POST["article_quantity"]) {
            addItem($_POST['article_id']);
            header('Location: index.php');
        } else {
            echo "pas assez de stocks";
        } 
    } elseif (isset($_POST['removeItem'])) {
        removeItem($_POST['article_id']);
        header('Location: index.php');
        exit();
    }
}

// Afficher les articles
$query = "SELECT * FROM article WHERE deleted = 0 ORDER BY date_publication DESC";
$stmt = $db->prepare($query);
$stmt->execute();

// Récupérer les articles du panier si l'utilisateur est connecté
$cartItems = [];
if ($userLoggedIn) {
    $query = "SELECT article_id, quantite FROM cart WHERE user_id = ?";
    $cartRequest = $db->prepare($query);
    $cartRequest->execute([$_SESSION['id']]);
    $cartItems = $cartRequest->fetchAll(PDO::FETCH_ASSOC);
}
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
                <?php if ($userLoggedIn) : ?>
                    <li><a href="create_product.php">Créer un article</a></li>
                <?php endif; ?>
                <li><a href="#">Wishlist</a></li>
                <li><a href="cart.php">Panier</a></li>
            </ul>
            <?php if ($userLoggedIn) : ?>
                <div class='userLog'>
                    <div class='profilPic'></div>
                    <p><?= htmlspecialchars($_SESSION['username']) ?></p>
                    <div class='userMenuHitbox'>
                        <div class='userMenu'>
                            <ul>
                                <li><a href="profil.php?username=<?= urlencode($_SESSION['username']) ?>">Mon profil</a></li>
                                <li><a href="commandes.php">Mes Commandes</a></li>
                                <li>
                                    <form method="POST" style="display:inline;">
                                        <button type="submit" name="Deconnexion" style="border: none; background: none; color: red; cursor: pointer;">
                                            Déconnexion
                                        </button>
                                    </form>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
            <?php else : ?>
                <div class='user'>
                    <p><a href="register.php">LOGIN</a></p>
                </div>
            <?php endif; ?>
        </div>
    </nav>

    <h1>Articles en vente</h1>

    <div class="landing">
        <div class="articles">
            <?php if ($stmt->rowCount() > 0) : ?>
                <?php while ($article = $stmt->fetch(PDO::FETCH_ASSOC)) : ?>
                    <div class='article'>
                        <div class='articleImage'>
                            <img src='<?= htmlspecialchars($article['image_url']) ?>' alt='<?= htmlspecialchars($article['nom']) ?>' width='200'>
                        </div>
                        <div class='articleContent'>
                            <h2><?= htmlspecialchars($article['nom']) ?></h2>
                            <p>Prix : <?= htmlspecialchars($article['prix']) ?> €</p>

                            <?php 
                            $cartIds = array_column($cartItems, 'article_id');
                            if (in_array($article["id"], $cartIds)) :
                                $articleIndex = array_search($article["id"], $cartIds);
                                $articleQuantity = $cartItems[$articleIndex]['quantite'];
                            ?>
                                <form method="post">
                                    <input type='hidden' name='article_id' value='<?= $article["id"] ?>'>
                                    <input type='submit' name='removeItem' value='-'>
                                    <span><?= $articleQuantity ?></span>
                                    <input type='submit' name='addItem' value='+'>
                                </form>
                            <?php elseif ($userLoggedIn) : ?>
                                <form method="post">
                                    <input type="submit" name="addToCart" value="Add To Cart">
                                    <input type="hidden" name="article" value="<?= $article["id"] ?>">
                                </form>
                            <?php else : ?>
                                <p style="color:gray;">Connectez-vous pour ajouter au panier</p>
                            <?php endif; ?>
                            
                            <a href='product.php?slug=<?= urlencode($article["slug"]) ?>'>Voir l'article</a>
                        </div>
                    </div>
                <?php endwhile; ?>
            <?php else : ?>
                <p>Aucun article en vente pour le moment.</p>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>
<?php
require_once "../config/database.php";
require_once "../functions.php";

// Vérifier si l'utilisateur est connecté
if (!isset($_SESSION['id'])) {
    header('Location: /E-commerce/login.php');
    exit();
}

$database = new Database();
$db = $database->getConnection();
$userId = $_SESSION['id'];

// Récupérer le solde de l'utilisateur
$stmt = $db->prepare("SELECT solde FROM user WHERE id = ?");
$stmt->execute([$userId]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);
$solde = $user['solde'];

// Récupérer les articles du panier
$stmt = $db->prepare("SELECT c.article_id, c.quantite, a.nom, a.prix FROM cart c INNER JOIN article a ON c.article_id = a.id WHERE c.user_id = ?");
$stmt->execute([$userId]);
$cartItems = $stmt->fetchAll(PDO::FETCH_ASSOC);

$total = 0;
foreach ($cartItems as $item) {
    $total += $item['prix'] * $item['quantite'];
}

// Traitement de la validation du panier
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['validate'])) {
    if ($solde >= $total) {
        // Déduire le montant du solde
        $stmt = $db->prepare("UPDATE user SET solde = solde - ? WHERE id = ?");
        $stmt->execute([$total, $userId]);
        
        // Enregistrer la commande
        $stmt = $db->prepare("INSERT INTO orders (user_id, total, date) VALUES (?, ?, NOW())");
        $stmt->execute([$userId, $total]);
        $orderId = $db->lastInsertId();
        
        // Enregistrer les articles de la commande
        foreach ($cartItems as $item) {
            $stmt = $db->prepare("INSERT INTO order_items (order_id, article_id, quantite, prix) VALUES (?, ?, ?, ?)");
            $stmt->execute([$orderId, $item['article_id'], $item['quantite'], $item['prix']]);
        }
        
        // Vider le panier
        $stmt = $db->prepare("DELETE FROM cart WHERE user_id = ?");
        $stmt->execute([$userId]);
        
        // Redirection vers la page de confirmation
        header('Location: /E-commerce/cart/success.php?order_id=' . $orderId);
        exit();
    } else {
        $error = "Solde insuffisant pour valider la commande.";
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Validation du Panier</title>
    <link rel="stylesheet" href="../src/css/style.css">
</head>
<body>

<nav>
        <div class="title">
            <a href="/E-commerce/index.php"><h1>PHONE</h1></a>
        </div>
        <div class="searchBar">
            <input type="search" name="search" id="search" placeholder="Search for article">
        </div>

        <div class="CTANav">

            <ul>
                <li><a href="">Creer un article</a></li>
                <li><a href="">Wishlist</a></li>
                <li><a href="/E-commerce/cart.php">Panier</a></li>
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

    <h1>Validation du Panier</h1>
    
    <h2>Informations de Facturation</h2>
    <form method="post">
        <label>Nom :</label>
        <input type="text" name="billing_name" required>
        <br>
        <label>Adresse :</label>
        <input type="text" name="billing_address" required>
        <br>
        <label>Ville :</label>
        <input type="text" name="billing_city" required>
        <br>
        <label>Code Postal :</label>
        <input type="text" name="billing_cp" required>
        <br>
        <h2>Récapitulatif</h2>
        <ul>
            <?php foreach ($cartItems as $item) : ?>
                <li><?= htmlspecialchars($item['nom']) ?> - Quantité: <?= $item['quantite'] ?> - Prix: <?= $item['prix'] ?> €</li>
            <?php endforeach; ?>
        </ul>
        <p>Total : <?= $total ?> €</p>
        <p>Votre solde : <?= $solde ?> €</p>
        <?php if (isset($error)) echo "<p style='color: red;'>$error</p>"; ?>
        <button type="submit" name="validate">Valider la commande</button>
    </form>
</body>
</html>

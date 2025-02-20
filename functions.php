<?php
session_start();

function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

function isAdmin() {
    return isset($_SESSION['role']) && $_SESSION['role'] === 'admin';
}

function generateSlug($string) {
    return strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $string)));
}

function redirectIfNotLoggedIn() {
    if (!isLoggedIn()) {
        header('Location: /login.php');
        exit();
    }
}

function addToCart($articleId) {
    // Vérifier si l'utilisateur est connecté
    if (!isset($_SESSION['id'])) {
        echo "aaa";
        //header('Location: index.php');
        exit();
    }
    $database = new Database();
    $db = $database->getConnection();

    $stmt = $db->prepare("INSERT INTO cart (user_id, article_id) VALUES (?, ?)");
    $stmt->execute([$_SESSION["id"], $articleId]);

    header('Location: /E-commerce/index.php');
    exit();
    echo "Article ajouté au panier !";
}

function addItem($articleId) {
    if (!isset($_SESSION['id'])) {
        header('Location: index.php');
        exit();
    }

    $database = new Database();
    $db = $database->getConnection();

    // Vérifier si l'article est déjà dans le panier
    $stmt = $db->prepare("SELECT quantite FROM cart WHERE user_id = ? AND article_id = ?");
    $stmt->execute([$_SESSION['id'], $articleId]);
    $article = $stmt->fetch(PDO::FETCH_ASSOC);


    if ($article) {
        $stmt = $db->prepare("UPDATE cart SET quantite = quantite + 1 WHERE user_id = ? AND article_id = ?");
        $stmt->execute([$_SESSION['id'], $articleId]);
    } else {
        $stmt = $db->prepare("INSERT INTO cart (user_id, article_id, quantite) VALUES (?, ?, 1)");
        $stmt->execute([$_SESSION['id'], $articleId]);
    }
}

function removeItem($articleId) {
    if (!isset($_SESSION['id'])) {
        header('Location: index.php');
        exit();
    }

    $database = new Database();
    $db = $database->getConnection();

    // Vérifier la quantité actuelle de l'article
    $stmt = $db->prepare("SELECT quantite FROM cart WHERE user_id = ? AND article_id = ?");
    $stmt->execute([$_SESSION['id'], $articleId]);
    $article = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($article) {
        if ($article['quantite'] > 1) {
            // Si plus d'une unité, on décrémente
            $stmt = $db->prepare("UPDATE cart SET quantite = quantite - 1 WHERE user_id = ? AND article_id = ?");
            $stmt->execute([$_SESSION['id'], $articleId]);
        } else {
            // Sinon, on supprime l'article du panier
            $stmt = $db->prepare("DELETE FROM cart WHERE user_id = ? AND article_id = ?");
            $stmt->execute([$_SESSION['id'], $articleId]);
        }
    }
}
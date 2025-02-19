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

    header('Location: /E-commerce/cart.php');
    exit();
    echo "Article ajouté au panier !";
}
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
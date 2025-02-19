<?php 
session_start();
require_once '../config/database.php';
require_once '../functions.php';

if (!isAdmin()) {
    header('Location: /index.php');
    exit();
}

$error = "";
$success = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $database = new Database();
    $db = $database->getConnection();

    $nom = htmlspecialchars($_POST['nom']);
    $slug = generateSlug($nom);
    $description = htmlspecialchars($_POST['description']);
    $image_url = htmlspecialchars($_POST['image_url']);
    $prix = floatval($_POST['prix']);
    $quantite = intval($_POST['quantite']);

    // Vérifier si l'article existe déjà
    $stmt = $db->prepare("SELECT id FROM articles WHERE slug = ?");
    $stmt->execute([$slug]);

    if ($stmt->rowCount() > 0) {
        $error = "Un article avec ce nom existe déjà.";
    } else {
        try {
            $db->beginTransaction();

            // Insérer l'article
            $stmt = $db->prepare("INSERT INTO articles (nom, slug, description, image_url, prix) VALUES (?, ?, ?, ?, ?)");
            $stmt->execute([$nom, $slug, $description, $image_url, $prix]);

            // Récupérer l'ID de l'article inséré
            $article_id = $db->lastInsertId();

            // Ajouter le stock
            $stmt = $db->prepare("INSERT INTO stocks (article_id, quantite) VALUES (?, ?)");
            $stmt->execute([$article_id, $quantite]);

            $db->commit();
            $success = "Article créé avec succès !";
        } catch (Exception $e) {
            $db->rollBack();
            $error = "Erreur lors de la création de l'article : " . $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="fr">

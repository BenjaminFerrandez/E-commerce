<?php
require_once 'config/database.php';
require_once 'functions.php';

$database = new Database();
$db = $database->getConnection();

$commandQuery = $db->prepare("SELECT * FROM commandes INNER JOIN commande_articles ON commandes.id = commande_articles.commande_id INNER JOIN article ON commande_articles.article_id = article.id WHERE commandes.user_id = ?");
$commandQuery->execute([$_SESSION['id']]);
$commands = $commandQuery->fetchAll(PDO::FETCH_ASSOC);

var_dump($commands);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
</head>
<body>
    commandes
    <?php 
    if (count($commands) > 0) {
        foreach ($commands as $command) {
            echo "<p>Commande</p>";
            echo "<p>" . htmlspecialchars($command['nom']) . "</p>";
        }
    }
    ?>
</body>
</html>
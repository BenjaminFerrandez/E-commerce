Projet E-commerce

Ce projet est une plateforme e-commerce permettant aux utilisateurs d'acheter et de vendre des articles, de gérer un panier et de passer des commandes. Une interface administrateur permet la gestion des utilisateurs et des articles.

Prérequis

Avant d'installer le projet, assure-toi d'avoir les outils suivants :

XAMPP ou WAMP pour exécuter un serveur Apache et MySQL/MariaDB

PHP 8.0 ou supérieur


Installation

1. Cloner le projet

git clone https://github.com/BenjaminFerrandez/E-commerce.git
cd ton-repo

2. Configurer la base de données

Démarre MySQL via XAMPP/WAMP

Ouvre phpMyAdmin et crée une base de données : php_exam_telephone

Importe le fichier SQL situé dans config/database.sql

3. Démarrer le serveur local

Si tu utilises XAMPP/WAMP, place le projet dans htdocs et accède à http://localhost/ton-projet

Fonctionnalités

🔹 Gestion des utilisateurs (inscription, connexion, rôle admin)

🛒 Gestion du panier (ajout, suppression, validation)

📦 Commandes

⚙️ Interface admin pour gérer les articles et utilisateurs
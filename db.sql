CREATE TABLE user (
    id INT PRIMARY KEY AUTO_INCREMENT,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,  -- Pour le hash bcrypt
    solde DECIMAL(10,2) DEFAULT 0.00,
    avatar VARCHAR(255),
    role ENUM('user', 'admin') DEFAULT 'user',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Table des articles
CREATE TABLE article (
    id INT PRIMARY KEY AUTO_INCREMENT,
    created_by INT NOT NULL,
    nom VARCHAR(100) NOT NULL,
    slug VARCHAR(100) NOT NULL UNIQUE,
    description TEXT,
    image_url VARCHAR(255),
    date_publication TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    date_modification TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    prix DECIMAL(10,2) NOT NULL,
    FOREIGN KEY (created_by) REFERENCES user(id)
);

-- Table des stocks
CREATE TABLE stock (
    id INT PRIMARY KEY AUTO_INCREMENT,
    article_id INT NOT NULL,
    quantite INT NOT NULL DEFAULT 0,
    FOREIGN KEY (article_id) REFERENCES article(id)
);

-- Table du panier
CREATE TABLE cart (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    article_id INT NOT NULL,
    quantite INT NOT NULL DEFAULT 1,
    date_ajout TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES user(id),
    FOREIGN KEY (article_id) REFERENCES article(id)
);

-- Table des commandes
CREATE TABLE commandes (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    date_transaction TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    montant_total DECIMAL(10,2) NOT NULL,
    adresse VARCHAR(255) NOT NULL,
    ville VARCHAR(100) NOT NULL,
    code_postal VARCHAR(10) NOT NULL,
    FOREIGN KEY (user_id) REFERENCES user(id)
);

-- Table de liaison entre commandes et articles
CREATE TABLE commande_articles (
    id INT PRIMARY KEY AUTO_INCREMENT,
    commande_id INT NOT NULL,
    article_id INT NOT NULL,
    quantite INT NOT NULL,
    prix_unitaire DECIMAL(10,2) NOT NULL,
    FOREIGN KEY (commande_id) REFERENCES commandes(id),
    FOREIGN KEY (article_id) REFERENCES article(id)
);
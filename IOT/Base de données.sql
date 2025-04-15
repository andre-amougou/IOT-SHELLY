-- Structure de base de données pour projet IOT

CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nom_utilisateur VARCHAR(100) NOT NULL,
    mot_de_passe TEXT NOT NULL,
    role ENUM('user','admin') DEFAULT 'user'
);

CREATE TABLE equipement (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nom_appareil VARCHAR(100),
    ip VARCHAR(100),
    user_id INT,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

CREATE TABLE aenergy_log (
    id INT AUTO_INCREMENT PRIMARY KEY,
    equipement_id INT NOT NULL,
    energie_totale FLOAT NOT NULL,
    date_heure DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (equipement_id) REFERENCES equipement(id) ON DELETE CASCADE
);

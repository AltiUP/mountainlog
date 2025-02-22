CREATE TABLE courses (
    id INT AUTO_INCREMENT PRIMARY KEY,
    sommet VARCHAR(255) NOT NULL,
    altitude INT NOT NULL,
    denivele INT NOT NULL,
    duree TIME NOT NULL,
    participants TEXT,
    itineraire TEXT,
    type_activite VARCHAR(255) NOT NULL,
    difficulte VARCHAR(100) NOT NULL,
    date DATE NOT NULL,
    conditions TEXT,
    remarques TEXT,
    position_cordee ENUM('Leader', 'Second', 'Reversible') NOT NULL,
    photos TEXT,
    date_creation TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

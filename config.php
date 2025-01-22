<?php
// Configuration de la base de données
define('DB_SERVER', 'localhost'); // Adresse du serveur
define('DB_USERNAME', 'AltiUP_mountainlog');    // Nom d'utilisateur MySQL
define('DB_PASSWORD', 'O%F8xANXZikt6iKX');        // Mot de passe MySQL
define('DB_NAME', 'AltiUP_mountainlog'); // Nom de la base de données

// Connexion à la base de données
$conn = new mysqli(DB_SERVER, DB_USERNAME, DB_PASSWORD, DB_NAME);

// Vérification de la connexion
if ($conn->connect_error) {
    die("Erreur de connexion : " . $conn->connect_error);
}
?>


# Mountain Log - Gestion des courses en montagne

## Description

Mountain Log est une application web conçue pour enregistrer et gérer les courses en montagne. Elle permet de suivre les détails des courses, y compris les itinéraires, les participants, les conditions météo, et les photos associées.

## Fonctionnalités principales

- Ajouter, modifier et supprimer des courses.
- Consulter les détails d'une course individuelle.
- Télécharger et afficher des photos associées à chaque course.
- Tri et recherche des courses dans la liste principale.

## Prérequis

- **PHP** 7.4 ou supérieur
- **MySQL** ou **MariaDB**
- Serveur local comme **XAMPP**, **MAMP**, ou **WAMP**

## Installation

### 1. Télécharger et extraire

Téléchargez ce projet et extrayez les fichiers dans le répertoire racine de votre serveur local (exemple : `htdocs` pour XAMPP).

### 2. Configurer la base de données

1. Connectez-vous à **phpMyAdmin** ou utilisez un outil de gestion de base de données.
2. Créez une base de données nommée `mountainlog`.
3. Importez le fichier `database.sql` pour créer les tables nécessaires.

### 3. Configurer le fichier `config.php`

Mettez à jour le fichier `config.php` avec vos informations de connexion MySQL :

```php
<?php
$host = 'localhost'; // Hôte MySQL
$dbname = 'mountainlog'; // Nom de la base de données
$username = 'root'; // Nom d'utilisateur MySQL
$password = ''; // Mot de passe MySQL (laissez vide pour localhost)

$conn = new mysqli($host, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection échouée: " . $conn->connect_error);
}
?>
```

### 4. Configurer l'accès restreint (optionnel)

Le fichier `.htpasswd` contient des informations pour protéger certaines zones du site. Configurez `.htaccess` si vous souhaitez restreindre l'accès.

## Utilisation

1. **Ajout de courses** : Accédez à `ajouter_course.php` pour ajouter une nouvelle course.
2. **Modification et suppression** : Modifiez ou supprimez une course via les actions disponibles dans `index.php`.
3. **Visualisation des détails** : Consultez les informations détaillées et les photos d'une course dans `details_course.php`.

## Structure des fichiers

```
/mountainlog
    ajouter_course.php         # Page pour ajouter une course
    details_course.php         # Page pour voir les détails d'une course
    edit_course.php            # Page pour modifier une course existante
    index.php                  # Page principale avec la liste des courses
    config.php                 # Configuration de la base de données
    database.sql               # Script SQL pour créer la base de données
    .htaccess                  # Configuration pour restreindre l'accès
    .htpasswd                  # Fichier pour protéger l'accès
    /assets                    # Dossier pour les fichiers CSS
        add_course_styles.css  # Styles pour la page d'ajout
        edit_course_styles.css # Styles pour la page d'édition
        details_course_styles.css # Styles pour la page de détails
        messages_styles.css    # Styles pour les messages d'alerte
        styles.css             # Styles globaux
```

## Contribution

Les contributions sont les bienvenues. Veuillez soumettre vos suggestions ou corrections via une issue ou une pull request.

## Licence

Ce projet est sous licence [MIT License](LICENSE).

---

Par [CirrusLab/AltiUP].

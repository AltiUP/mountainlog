<?php
// Inclusion du fichier de configuration
include 'config.php';

// Fonction pour redimensionner les images
function redimensionner_image($source, $destination, $largeur_max = 1920, $hauteur_max = 1080) {
    list($largeur_orig, $hauteur_orig, $type) = getimagesize($source);

    // Calcul des nouvelles dimensions
    $ratio_orig = $largeur_orig / $hauteur_orig;
    if ($largeur_max / $hauteur_max > $ratio_orig) {
        $largeur_max = $hauteur_max * $ratio_orig;
    } else {
        $hauteur_max = $largeur_max / $ratio_orig;
    }

    // Création de l'image redimensionnée
    $image_p = imagecreatetruecolor($largeur_max, $hauteur_max);

    switch ($type) {
        case IMAGETYPE_JPEG:
            $image = imagecreatefromjpeg($source);
            break;
        case IMAGETYPE_PNG:
            $image = imagecreatefrompng($source);
            break;
        case IMAGETYPE_GIF:
            $image = imagecreatefromgif($source);
            break;
        default:
            return false;
    }

    imagecopyresampled($image_p, $image, 0, 0, 0, 0, $largeur_max, $hauteur_max, $largeur_orig, $hauteur_orig);

    // Sauvegarde de l'image redimensionnée
    switch ($type) {
        case IMAGETYPE_JPEG:
            imagejpeg($image_p, $destination, 85); // 85 = qualité JPEG
            break;
        case IMAGETYPE_PNG:
            imagepng($image_p, $destination);
            break;
        case IMAGETYPE_GIF:
            imagegif($image_p, $destination);
            break;
    }

    return true;
}

$message = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    // Récupération des données du formulaire
    $sommet = $_POST['sommet'];
    $altitude = $_POST['altitude'];
    $denivele = $_POST['denivele'];
    $duree = $_POST['duree'];
    $participants = $_POST['participants'];
    $itineraire = $_POST['itineraire'];
    $type_activite = $_POST['type_activite'];
    if ($type_activite === "Autre" && !empty($_POST['autre_activite'])) {
        $type_activite = $_POST['autre_activite'];
    }
    $difficulte = $_POST['difficulte'];
    $date = $_POST['date'];
    $conditions = $_POST['conditions'];
    $remarques = $_POST['remarques'];
    $position_cordee = $_POST['position_cordee'];

    // Gestion des photos
    $photos = [];
    if (isset($_FILES['photos']) && count($_FILES['photos']['name']) > 0) {
        $upload_dir = 'uploads/' . preg_replace('/[^a-zA-Z0-9]/', '_', $sommet) . '_' . preg_replace('/[^a-zA-Z0-9]/', '_', $itineraire) . '_' . $date;
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }

        foreach ($_FILES['photos']['name'] as $key => $name) {
            if ($_FILES['photos']['error'][$key] === 0) {
                $tmp_name = $_FILES['photos']['tmp_name'][$key];
                $extension = pathinfo($name, PATHINFO_EXTENSION);
                $increment = 1;
                do {
                    $photo_name = sprintf("photo_%03d.%s", $increment, $extension);
                    $destination = $upload_dir . '/' . $photo_name;
                    $increment++;
                } while (file_exists($destination));

                if (redimensionner_image($tmp_name, $destination)) {
                    $photos[] = $destination;
                }
            }
        }
    }
    $photos_str = implode(",", $photos);

    // Insertion dans la base de données
    $sql = "INSERT INTO courses (sommet, altitude, denivele, duree, participants, itineraire, type_activite, difficulte, date, conditions, remarques, position_cordee, photos)
            VALUES ('$sommet', $altitude, $denivele, '$duree', '$participants', '$itineraire', '$type_activite', '$difficulte', '$date', '$conditions', '$remarques', '$position_cordee', '$photos_str')";

    if ($conn->query($sql) === TRUE) {
        $message = "<div class='message success'>Course ajoutée avec succès ! <button class='close' onclick='this.parentElement.style.display=\"none\";'>&times;</button></div>";
    } else {
        $message = "<div class='message error'>Erreur : " . htmlspecialchars($conn->error) . " <button class='close' onclick='this.parentElement.style.display=\"none\";'>&times;</button></div>";
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ajouter une Course</title>
    <link rel="stylesheet" href="assets/styles.css">
    <link rel="stylesheet" href="assets/add_course_styles.css">
    <link rel="stylesheet" href="assets/messages_styles.css">
</head>
<body>
    <div class="container">
        <h1>Ajouter une Course</h1>
        <?= $message; ?>
        <form action="ajouter_course.php" method="POST" enctype="multipart/form-data">
            <label for="sommet">Sommet :</label>
            <input type="text" id="sommet" name="sommet" required>

            <label for="altitude">Altitude (m) :</label>
            <input type="number" id="altitude" name="altitude" required>

            <label for="denivele">Dénivelé (m) :</label>
            <input type="number" id="denivele" name="denivele" required>

            <label for="duree">Durée (HH:MM) :</label>
            <input type="time" id="duree" name="duree" required>

            <label for="participants">Participants :</label>
            <input type="text" id="participants" name="participants">

            <label for="itineraire">Itinéraire :</label>
            <textarea id="itineraire" name="itineraire"></textarea>

            <label for="type_activite">Type d'activité :</label>
            <select id="type_activite" name="type_activite" required>
                <option value="Alpinisme">Alpinisme</option>
                <option value="Ski de randonnée">Ski de randonnée</option>
                <option value="Randonnée">Randonnée</option>
                <option value="Escalade">Escalade</option>
                <option value="Cascade de glace">Cascade de glace</option>
                <option value="Autre">Autre</option>
            </select>

            <!-- Champ texte caché pour "Autre" -->
            <div id="autre_activite_div" style="display: none;">
                <label for="autre_activite">Précisez l'activité :</label>
                <input type="text" id="autre_activite" name="autre_activite">
            </div>

            <label for="difficulte">Difficulté :</label>
            <input type="text" id="difficulte" name="difficulte">

            <label for="date">Date :</label>
            <input type="date" id="date" name="date" required>

            <label for="conditions">Conditions météo :</label>
            <textarea id="conditions" name="conditions"></textarea>

            <label for="remarques">Remarques :</label>
            <textarea id="remarques" name="remarques"></textarea>

            <label for="position_cordee">Position dans la cordée :</label>
            <select id="position_cordee" name="position_cordee" required>
                <option value="Leader">Leader</option>
                <option value="Second">Second</option>
                <option value="Reversible">Reversible</option>
            </select>

            <label for="photos">Photos :</label>
            <input type="file" id="photos" name="photos[]" multiple>

            <button type="submit">Ajouter la Course</button>
        </form>
        <a href="index.php">Retour à l'accueil</a>
    </div>
<script>
document.addEventListener("DOMContentLoaded", function () {
    let selectActivite = document.getElementById("type_activite");
    let autreActiviteDiv = document.getElementById("autre_activite_div");
    let autreActiviteInput = document.getElementById("autre_activite");

    selectActivite.addEventListener("change", function () {
        if (this.value === "Autre") {
            autreActiviteDiv.style.display = "block";
            autreActiviteInput.setAttribute("required", "required");
        } else {
            autreActiviteDiv.style.display = "none";
            autreActiviteInput.removeAttribute("required");
        }
    });
});
</script>
</body>
</html>

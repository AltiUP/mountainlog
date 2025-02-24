<?php
// Inclusion du fichier de configuration
include 'config.php';

// Vérification de l'ID fourni
if (!isset($_GET['id'])) {
    die("ID de course non fourni.");
}

$id = $_GET['id'];

// Requête pour récupérer les détails de la course
$sql = "SELECT * FROM courses WHERE id = $id";
$result = $conn->query($sql);

if ($result->num_rows === 0) {
    die("Course non trouvée.");
}

$course = $result->fetch_assoc();

// Mise à jour des données si le formulaire est soumis
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $sommet = $_POST['sommet'];
    $altitude = $_POST['altitude'];
    $denivele = $_POST['denivele'];
    $duree = $_POST['duree'];
    $participants = $_POST['participants'];
    $itineraire = $_POST['itineraire'];
    $type_activite = $_POST['type_activite'];
    if ($type_activite === "Autre" && !empty($_POST['autre_activite'])) {
      $type_activite = trim($_POST['autre_activite']);
    }
    $difficulte = $_POST['difficulte'];
    $date = $_POST['date'];
    $conditions = $_POST['conditions'];
    $remarques = $_POST['remarques'];
    $position_cordee = $_POST['position_cordee'];

// Construire l'ancien et le nouveau répertoire des photos
$old_dir = 'uploads/' . preg_replace('/[^a-zA-Z0-9]/', '_', $course['sommet']) . '_' . preg_replace('/[^a-zA-Z0-9]/', '_', $course['itineraire']) . '_' . $course['date'];
$new_dir = 'uploads/' . preg_replace('/[^a-zA-Z0-9]/', '_', $sommet) . '_' . preg_replace('/[^a-zA-Z0-9]/', '_', $itineraire) . '_' . $date;

// Vérifier si le répertoire doit être renommé
if ($old_dir !== $new_dir && is_dir($old_dir)) {
    rename($old_dir, $new_dir);
}

// Mise à jour des photos (gestion du changement de dossier)
$photos_updated = $course['photos'] ? str_replace($old_dir, $new_dir, $course['photos']) : "";

// Vérifier si le dossier existe, sinon le créer
if (!is_dir($new_dir)) {
    mkdir($new_dir, 0777, true);
}

// Gestion des nouvelles photos
$new_photos = [];
$existing_photos = glob($new_dir . '/photo_*.{jpg,jpeg,png,gif}', GLOB_BRACE);
$increment = count($existing_photos) + 1;

if (isset($_FILES['new_photos']) && count($_FILES['new_photos']['name']) > 0) {
    foreach ($_FILES['new_photos']['name'] as $key => $name) {
        if ($_FILES['new_photos']['error'][$key] === 0) {
            $tmp_name = $_FILES['new_photos']['tmp_name'][$key];
            $extension = pathinfo($name, PATHINFO_EXTENSION);
            do {
                $photo_name = sprintf("photo_%03d.%s", $increment, $extension);
                $destination = $new_dir . '/' . $photo_name;
                $increment++;
            } while (file_exists($destination));

            if (move_uploaded_file($tmp_name, $destination)) {
                $new_photos[] = $destination;
            }
        }
    }
}

// Fusion des anciennes et nouvelles photos
$all_photos = $photos_updated ? explode(",", $photos_updated) : [];
$all_photos = array_merge($all_photos, $new_photos);
$photos_str = implode(",", $all_photos);

// Mise à jour de la base de données
$update_sql = "UPDATE courses
               SET sommet = '$sommet', altitude = $altitude, denivele = $denivele, duree = '$duree',
                   participants = '$participants', itineraire = '$itineraire', type_activite = '$type_activite',
                   difficulte = '$difficulte', date = '$date', conditions = '$conditions', remarques = '$remarques',
                   position_cordee = '$position_cordee', photos = '$photos_str'
               WHERE id = $id";

if ($conn->query($update_sql) === TRUE) {
    header("Location: index.php?message=success");
    exit();
} else {
    echo "<div class='message error'>Erreur lors de la modification : " . htmlspecialchars($conn->error) . " <button class='close' onclick='this.parentElement.style.display=\"none\";'>&times;</button></div>";
}
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Modifier une Course</title>
    <link rel="stylesheet" href="assets/styles.css">
    <link rel="stylesheet" href="assets/edit_course_styles.css">
    <link rel="stylesheet" href="assets/messages_styles.css">
</head>
<body>
    <div class="container">
        <h1>Modifier une Course</h1>
        <form action="" method="POST" enctype="multipart/form-data">
            <label for="sommet">Sommet :</label>
            <input type="text" id="sommet" name="sommet" value="<?= htmlspecialchars($course['sommet']) ?>" required>

            <label for="altitude">Altitude (m) :</label>
            <input type="number" id="altitude" name="altitude" value="<?= htmlspecialchars($course['altitude']) ?>" required>

            <label for="denivele">Dénivelé (m) :</label>
            <input type="number" id="denivele" name="denivele" value="<?= htmlspecialchars($course['denivele']) ?>" required>

            <label for="duree">Durée (HH:MM) :</label>
            <input type="time" id="duree" name="duree" value="<?= htmlspecialchars($course['duree']) ?>" required>

            <label for="participants">Participants :</label>
            <input type="text" id="participants" name="participants" value="<?= htmlspecialchars($course['participants']) ?>">

            <label for="itineraire">Itinéraire :</label>
            <textarea id="itineraire" name="itineraire"><?= htmlspecialchars($course['itineraire']) ?></textarea>

            <label for="type_activite">Type d'activité :</label>
<select id="type_activite" name="type_activite" required>
    <option value="Alpinisme" <?= $course['type_activite'] === 'Alpinisme' ? 'selected' : '' ?>>Alpinisme</option>
    <option value="Ski de randonnée" <?= $course['type_activite'] === 'Ski de randonnée' ? 'selected' : '' ?>>Ski de randonnée</option>
    <option value="Randonnée" <?= $course['type_activite'] === 'Randonnée' ? 'selected' : '' ?>>Randonnée</option>
    <option value="Escalade" <?= $course['type_activite'] === 'Escalade' ? 'selected' : '' ?>>Escalade</option>
    <option value="Cascade de glace" <?= $course['type_activite'] === 'Cascade de glace' ? 'selected' : '' ?>>Cascade de glace</option>
    <option value="Autre" <?= !in_array($course['type_activite'], ['Alpinisme', 'Ski de randonnée', 'Randonnée', 'Escalade', 'Cascade de glace']) ? 'selected' : '' ?>>Autre</option>
</select>

<!-- Champ texte caché pour l'activité personnalisée -->
<div id="autre_activite_div" style="display: <?= !in_array($course['type_activite'], ['Alpinisme', 'Ski de randonnée', 'Randonnée', 'Escalade', 'Cascade de glace']) ? 'block' : 'none' ?>;">
    <label for="autre_activite">Précisez l'activité :</label>
    <input type="text" id="autre_activite" name="autre_activite" value="<?= htmlspecialchars(!in_array($course['type_activite'], ['Alpinisme', 'Ski de randonnée', 'Randonnée', 'Escalade', 'Cascade de glace']) ? $course['type_activite'] : '') ?>">
</div>

            <label for="difficulte">Difficulté :</label>
            <input type="text" id="difficulte" name="difficulte" value="<?= htmlspecialchars($course['difficulte']) ?>">

            <label for="date">Date :</label>
            <input type="date" id="date" name="date" value="<?= htmlspecialchars($course['date']) ?>" required>

            <label for="conditions">Conditions météo :</label>
            <textarea id="conditions" name="conditions"><?= htmlspecialchars($course['conditions']) ?></textarea>

            <label for="remarques">Remarques :</label>
            <textarea id="remarques" name="remarques"><?= htmlspecialchars($course['remarques']) ?></textarea>

            <label for="position_cordee">Position dans la cordée :</label>
            <select id="position_cordee" name="position_cordee" required>
                <option value="Leader" <?= $course['position_cordee'] === 'Leader' ? 'selected' : '' ?>>Leader</option>
                <option value="Second" <?= $course['position_cordee'] === 'Second' ? 'selected' : '' ?>>Second</option>
                <option value="Reversible" <?= $course['position_cordee'] === 'Reversible' ? 'selected' : '' ?>>Reversible</option>
            </select>

            <label for="new_photos">Ajouter des photos supplémentaires :</label>
            <input type="file" id="new_photos" name="new_photos[]" multiple accept=".jpg,.jpeg,.png,.gif">
            <p id="new-error-message" style="color: red;"></p>

            <button type="submit">Enregistrer les modifications</button>
        </form>
        <a href="index.php">Retour à l'accueil</a>
    </div>
<script>
document.getElementById('new_photos').addEventListener('change', function() {
    const allowedExtensions = ['jpg', 'jpeg', 'png', 'gif'];
    const maxSize = 5 * 1024 * 1024; // 5 Mo en octets
    const files = this.files;
    let errorMessage = '';

    for (let file of files) {
        const fileExtension = file.name.split('.').pop().toLowerCase();
        if (!allowedExtensions.includes(fileExtension)) {
            errorMessage = "Seuls les fichiers JPG, JPEG, PNG et GIF sont autorisés.";
            this.value = ''; // Réinitialise le champ
            break;
        }
        if (file.size > maxSize) {
            errorMessage = "Chaque fichier doit être inférieur à 5 Mo.";
            this.value = ''; // Réinitialise le champ
            break;
        }
    }

    document.getElementById('new-error-message').textContent = errorMessage;
});
</script>
<script>
document.addEventListener("DOMContentLoaded", function () {
    let selectActivite = document.getElementById("type_activite");
    let autreActiviteDiv = document.getElementById("autre_activite_div");
    let autreActiviteInput = document.getElementById("autre_activite");

    function toggleAutreActivite() {
        if (selectActivite.value === "Autre") {
            autreActiviteDiv.style.display = "block";
            autreActiviteInput.setAttribute("required", "required");
        } else {
            autreActiviteDiv.style.display = "none";
            autreActiviteInput.removeAttribute("required");
            autreActiviteInput.value = "";
        }
    }

    selectActivite.addEventListener("change", toggleAutreActivite);
});
</script>
</body>
</html>

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

// Suppression d'une photo
if (isset($_GET['delete_photo'])) {
    $photo_to_delete = $_GET['delete_photo'];
    $upload_dir = 'uploads/' . preg_replace('/[^a-zA-Z0-9]/', '_', $course['sommet']) . '_' . preg_replace('/[^a-zA-Z0-9]/', '_', $course['itineraire']) . '_' . $course['date'];

    // Supprimer la photo du répertoire
    if (file_exists($upload_dir . '/' . $photo_to_delete)) {
        unlink($upload_dir . '/' . $photo_to_delete);
    }

    // Supprimer la photo de la base de données
    $photos = explode(",", $course['photos']);
    $photos = array_diff($photos, [$upload_dir . '/' . $photo_to_delete]);
    $new_photos = implode(",", $photos);

    // Mise à jour des photos dans la base de données
    $update_sql = "UPDATE courses SET photos = '$new_photos' WHERE id = $id";
    $conn->query($update_sql);

    // Redirection pour éviter le repostage du formulaire
    header("Location: details_course?id=" . $id);
    exit();
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MountainLog | Détails de la Course</title>
    <link rel="stylesheet" href="assets/styles.css">
    <link rel="stylesheet" href="assets/details_course_styles.css">
</head>
<body>
    <div class="container">
        <h1>Détails de la Course : <?= htmlspecialchars($course['sommet']) ?></h1>

        <h2>Informations de la course</h2>
        <table>
            <tr>
                <th>Sommet</th>
                <td><?= htmlspecialchars($course['sommet']) ?></td>
            </tr>
            <tr>
                <th>Altitude</th>
                <td><?= htmlspecialchars($course['altitude']) ?> m</td>
            </tr>
            <tr>
                <th>Dénivelé</th>
                <td><?= htmlspecialchars($course['denivele']) ?> m</td>
            </tr>
            <tr>
                <th>Durée</th>
                <td><?= htmlspecialchars($course['duree']) ?></td>
            </tr>
            <tr>
                <th>Participants</th>
                <td><?= htmlspecialchars($course['participants']) ?></td>
            </tr>
            <tr>
                <th>Itinéraire</th>
                <td><?= nl2br(htmlspecialchars($course['itineraire'])) ?></td>
            </tr>
            <tr>
                <th>Type d'activité</th>
                <td><?= htmlspecialchars($course['type_activite']) ?></td>
            </tr>
            <tr>
                <th>Difficulté</th>
                <td><?= htmlspecialchars($course['difficulte']) ?></td>
            </tr>
            <tr>
                <th>Date</th>
                <td><?= htmlspecialchars($course['date']) ?></td>
            </tr>
            <tr>
                <th>Conditions météo</th>
                <td><?= nl2br(htmlspecialchars($course['conditions'])) ?></td>
            </tr>
            <tr>
                <th>Remarques</th>
                <td><?= nl2br(htmlspecialchars($course['remarques'])) ?></td>
            </tr>
            <tr>
                <th>Position dans la cordée</th>
                <td><?= htmlspecialchars($course['position_cordee']) ?></td>
            </tr>
        </table>

        <h2>Photos</h2>
        <?php
        $upload_dir = 'uploads/' . preg_replace('/[^a-zA-Z0-9]/', '_', $course['sommet']) . '_' . preg_replace('/[^a-zA-Z0-9]/', '_', $course['itineraire']) . '_' . $course['date'];
        if (is_dir($upload_dir) && $course['photos']) {
            $photos = explode(",", $course['photos']);
            echo '<div class="photo-gallery">';
            foreach ($photos as $photo) {
                $photo_name = basename($photo);
                echo '<div>';
                echo '<img src="' . $photo . '" alt="Photo de la course" class="course-photo">';
                echo '<a href="details_course?id=' . $id . '&delete_photo=' . urlencode($photo_name) . '" class="delete-photo" onclick="return confirm(\'Êtes-vous sûr de vouloir supprimer cette photo ?\')">Supprimer</a>';
                echo '</div>';
            }
            echo '</div>';
        } else {
            echo '<p>Aucune photo disponible.</p>';
        }
        ?>

        <a href="/mountainlog">Retour à l'accueil</a>
    </div>
</body>
</html>

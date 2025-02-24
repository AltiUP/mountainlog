<?php
// Inclusion du fichier de configuration
include 'config.php';

// Vérification de la présence du message de succès
$message = "";
if (isset($_GET['message']) && $_GET['message'] === 'success') {
    $message = "<div class='message success'>Course modifiée avec succès ! <button class='close' onclick='this.parentElement.style.display=\"none\";'>&times;</button></div>";
}

if (isset($_GET['delete_id'])) {
    $delete_id = intval($_GET['delete_id']); // Sécuriser l'ID

    // Requête préparée pour éviter l'injection SQL
    $stmt = $conn->prepare("SELECT photos, sommet, itineraire, date FROM courses WHERE id = ?");
    $stmt->bind_param("i", $delete_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($row = $result->fetch_assoc()) {
        $photos = explode(",", $row['photos']);
        $upload_dir = 'uploads/' . preg_replace('/[^a-zA-Z0-9]/', '_', $row['sommet']) . '_' . preg_replace('/[^a-zA-Z0-9]/', '_', $row['itineraire']) . '_' . $row['date'];

        // Supprimer chaque photo dans le répertoire
        foreach ($photos as $photo) {
            $photo_path = $upload_dir . '/' . basename($photo);
            if (file_exists($photo_path) && is_file($photo_path)) {
                unlink($photo_path);
            }
        }

        // Fonction pour supprimer un répertoire et son contenu
        function deleteDir($dir) {
            if (!is_dir($dir)) return;
            $files = array_diff(scandir($dir), array('.', '..'));
            foreach ($files as $file) {
                $filePath = $dir . DIRECTORY_SEPARATOR . $file;
                is_dir($filePath) ? deleteDir($filePath) : unlink($filePath);
            }
            rmdir($dir);
        }

        // Supprimer le répertoire après suppression des fichiers
        if (is_dir($upload_dir)) {
            deleteDir($upload_dir);
        }
    }

    $stmt->close();

    // Supprimer la course de la base de données
    $stmt = $conn->prepare("DELETE FROM courses WHERE id = ?");
    $stmt->bind_param("i", $delete_id);
    
    if ($stmt->execute()) {
        echo "<div class='message success'>Course supprimée avec succès ! 
                <button class='close' onclick='this.parentElement.style.display=\"none\";'>&times;</button>
              </div>";
    } else {
        echo "<div class='message error'>Erreur lors de la suppression : " . htmlspecialchars($stmt->error) . " 
                <button class='close' onclick='this.parentElement.style.display=\"none\";'>&times;</button>
              </div>";
    }

    $stmt->close();
}

// Gestion de la recherche
$search = isset($_GET['search']) ? $_GET['search'] : '';

// Gestion du tri des colonnes
$sortable_columns = ['sommet', 'itineraire', 'altitude', 'denivele', 'duree', 'type_activite', 'date'];
$order_column = isset($_GET['sort_by']) && in_array($_GET['sort_by'], $sortable_columns) ? $_GET['sort_by'] : 'date';
$order_direction = isset($_GET['order']) && $_GET['order'] === 'asc' ? 'ASC' : 'DESC';

// Construction de la requête SQL
$sql = "SELECT * FROM courses WHERE sommet LIKE ? OR itineraire LIKE ? ORDER BY $order_column $order_direction";
$stmt = $conn->prepare($sql);
$search_term = '%' . $search . '%';
$stmt->bind_param('ss', $search_term, $search_term);
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mes Courses</title>
    <link rel="stylesheet" href="assets/styles.css">
    <link rel="stylesheet" href="assets/messages_styles.css">
</head>
<body>
    <div class="container">
        <h1>Liste des Courses</h1>

        <!-- Affichage des messages éventuels -->
        <?php if (!empty($message)): ?>
            <div class="message">
                <?= $message ?>
            </div>
        <?php endif; ?>

        <!-- Conteneur regroupant la barre de recherche et le bouton "Ajouter une nouvelle course" -->
        <div class="search-container">
            <form method="GET" action="" class="search-form">
                <input type="text" name="search" value="<?= htmlspecialchars($search ?? '') ?>" placeholder="Rechercher un sommet ou un itinéraire" aria-label="Rechercher une course">
                <button type="submit" title="Rechercher">Rechercher</button>
            </form>
            <a href="ajouter_course.php" class="btn-add" title="Ajouter une nouvelle course">+ Ajouter</a>
        </div>

        <div class="table-wrapper">
            <table>
                <thead>
                    <tr>
                        <th><a href="?sort_by=sommet&order=<?= ($order_column === 'sommet' && $order_direction === 'ASC') ? 'desc' : 'asc' ?>">Sommet</a></th>
                        <th><a href="?sort_by=itineraire&order=<?= ($order_column === 'itineraire' && $order_direction === 'ASC') ? 'desc' : 'asc' ?>">Itinéraire</a></th>
                        <th><a href="?sort_by=altitude&order=<?= ($order_column === 'altitude' && $order_direction === 'ASC') ? 'desc' : 'asc' ?>">Altitude (m)</a></th>
                        <th><a href="?sort_by=denivele&order=<?= ($order_column === 'denivele' && $order_direction === 'ASC') ? 'desc' : 'asc' ?>">Dénivelé (m)</a></th>
                        <th><a href="?sort_by=duree&order=<?= ($order_column === 'duree' && $order_direction === 'ASC') ? 'desc' : 'asc' ?>">Durée</a></th>
                        <th><a href="?sort_by=type_activite&order=<?= ($order_column === 'type_activite' && $order_direction === 'ASC') ? 'desc' : 'asc' ?>">Type d'activité</a></th>
                        <th><a href="?sort_by=date&order=<?= ($order_column === 'date' && $order_direction === 'ASC') ? 'desc' : 'asc' ?>">Date</a></th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($result->num_rows > 0): ?>
                        <?php while ($row = $result->fetch_assoc()): ?>
                            <tr>
                                <td data-label="Sommet"><?= htmlspecialchars($row['sommet']) ?></td>
                                <td data-label="Itinéraire"><?= htmlspecialchars($row['itineraire']) ?></td>
                                <td data-label="Altitude"><?= htmlspecialchars($row['altitude']) ?> m</td>
                                <td data-label="Dénivelé"><?= htmlspecialchars($row['denivele']) ?> m</td>
                                <td data-label="Durée"><?= htmlspecialchars($row['duree']) ?></td>
                                <td data-label="Type d'activité"><?= htmlspecialchars($row['type_activite']) ?></td>
                                <td data-label="Date"><?= htmlspecialchars($row['date']) ?></td>
                                <td data-label="Actions">
                                    <a href="details_course.php?id=<?= $row['id'] ?>" class="btn-details" title="Voir les détails">🔍 Détails</a>
                                    <a href="edit_course.php?id=<?= $row['id'] ?>" class="btn-edit" title="Modifier la course">✏️ Modifier</a>
                                    <a href="?delete_id=<?= $row['id'] ?>" class="btn-delete" onclick="return confirm('Êtes-vous sûr de vouloir supprimer cette course ?')" title="Supprimer la course">🗑️ Supprimer</a>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="8" class="no-data">Aucune course enregistrée</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</body>
</html>

<?php
$conn->close();
?>

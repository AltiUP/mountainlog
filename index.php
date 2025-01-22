<?php
// Inclusion du fichier de configuration
include 'config.php';

// Suppression d'une course si l'action est demandée
if (isset($_GET['delete_id'])) {
    $delete_id = intval($_GET['delete_id']); // S'assurer que l'ID est un entier

    // Récupérer les informations de la course pour supprimer les fichiers et le répertoire
    $query = "SELECT photos, sommet, itineraire, date FROM courses WHERE id = $delete_id";
    $result = $conn->query($query);

    if ($result && $row = $result->fetch_assoc()) {
        $photos = explode(",", $row['photos']);
        $upload_dir = 'uploads/' . preg_replace('/[^a-zA-Z0-9]/', '_', $row['sommet']) . '_' . preg_replace('/[^a-zA-Z0-9]/', '_', $row['itineraire']) . '_' . $row['date'];

        // Supprimer chaque photo dans le répertoire
        foreach ($photos as $photo) {
            $photo_path = $upload_dir . '/' . basename($photo); // Construire le chemin complet
            if (file_exists($photo_path)) {
                unlink($photo_path); // Supprimer le fichier
            }
        }

        // Supprimer le répertoire s'il est vide
        if (is_dir($upload_dir)) {
            rmdir($upload_dir);
        }
    }

    // Supprimer la course de la base de données
    $delete_query = "DELETE FROM courses WHERE id = $delete_id";
    if ($conn->query($delete_query) === TRUE) {
        echo "<div class='message success'>Course supprimée avec succès ! <button class='close' onclick='this.parentElement.style.display=\"none\";'>&times;</button></div>";
    } else {
        echo "<div class='message error'>Erreur lors de la suppression : " . htmlspecialchars($conn->error) . " <button class='close' onclick='this.parentElement.style.display=\"none\";'>&times;</button></div>";
    }
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

        <!-- Formulaire de recherche -->
        <form method="GET" action="" class="search-form">
            <input type="text" name="search" value="<?= htmlspecialchars($search) ?>" placeholder="Rechercher un sommet ou un itinéraire">
            <button type="submit">Rechercher</button>
        </form>

        <table>
            <thead>
                <tr>
                    <th><a href="?sort_by=sommet&order=<?= $order_column === 'sommet' && $order_direction === 'ASC' ? 'desc' : 'asc' ?>">Sommet</a></th>
                    <th><a href="?sort_by=itineraire&order=<?= $order_column === 'itineraire' && $order_direction === 'ASC' ? 'desc' : 'asc' ?>">Itinéraire</a></th>
                    <th><a href="?sort_by=altitude&order=<?= $order_column === 'altitude' && $order_direction === 'ASC' ? 'desc' : 'asc' ?>">Altitude (m)</a></th>
                    <th><a href="?sort_by=denivele&order=<?= $order_column === 'denivele' && $order_direction === 'ASC' ? 'desc' : 'asc' ?>">Dénivelé (m)</a></th>
                    <th><a href="?sort_by=duree&order=<?= $order_column === 'duree' && $order_direction === 'ASC' ? 'desc' : 'asc' ?>">Durée</a></th>
                    <th><a href="?sort_by=type_activite&order=<?= $order_column === 'type_activite' && $order_direction === 'ASC' ? 'desc' : 'asc' ?>">Type d'activité</a></th>
                    <th><a href="?sort_by=date&order=<?= $order_column === 'date' && $order_direction === 'ASC' ? 'desc' : 'asc' ?>">Date</a></th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php
                if ($result->num_rows > 0) {
                    while ($row = $result->fetch_assoc()) {
                        echo "<tr>";
                        echo "<td>" . htmlspecialchars($row['sommet']) . "</td>";
                        echo "<td>" . htmlspecialchars($row['itineraire']) . "</td>";
                        echo "<td>" . htmlspecialchars($row['altitude']) . "</td>";
                        echo "<td>" . htmlspecialchars($row['denivele']) . "</td>";
                        echo "<td>" . htmlspecialchars($row['duree']) . "</td>";
                        echo "<td>" . htmlspecialchars($row['type_activite']) . "</td>";
                        echo "<td>" . htmlspecialchars($row['date']) . "</td>";
                        echo "<td>
                                <a href='details_course.php?id=" . $row['id'] . "' class='btn-details'>Détails</a> |
                                <a href='edit_course.php?id=" . $row['id'] . "' class='btn-edit'>Modifier</a> |
                                <a href='?delete_id=" . $row['id'] . "' class='btn-delete' onclick=\"return confirm('Êtes-vous sûr de vouloir supprimer cette course ?')\">Supprimer</a>
                              </td>";
                        echo "</tr>";
                    }
                } else {
                    echo "<tr><td colspan='8'>Aucune course enregistrée</td></tr>";
                }
                ?>
            </tbody>
        </table>
        <a href="ajouter_course.php" class="btn-add">Ajouter une nouvelle course</a>
    </div>
</body>
</html>

<?php
$conn->close();
?>

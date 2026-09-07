<?php
// add_item.php
require_once 'config.php';

// Zabezpieczenie - tylko dla zalogowanych
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$error = '';
$success = '';

// Pobranie kategorii do listy rozwijanej
$stmt = $pdo->query("SELECT * FROM categories");
$categories = $stmt->fetchAll();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $title = trim($_POST['title']);
    $category_id = $_POST['category_id'];
    $description = trim($_POST['description']);
    $price = $_POST['price'];
    $image_path = null;

    // Obsługa wgrywania zdjęcia
    if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
        $allowed = ['jpg', 'jpeg', 'png', 'webp'];
        $filename = $_FILES['image']['name'];
        $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));

        if (in_array($ext, $allowed)) {
            // Generujemy unikalną nazwę, aby pliki się nie nadpisywały
            $new_filename = uniqid() . '.' . $ext;
            $destination = 'uploads/' . $new_filename;
            
            if (move_uploaded_file($_FILES['image']['tmp_name'], $destination)) {
                $image_path = $destination;
            } else {
                $error = "Błąd podczas zapisywania pliku na serwerze.";
            }
        } else {
            $error = "Niedozwolony format pliku. Użyj JPG, PNG lub WEBP.";
        }
    }

    if (empty($title) || empty($description) || $price === '') {
        $error = "Wypełnij wymagane pola (tytuł, opis, cena).";
    } elseif (empty($error)) {
        try {
            $stmt = $pdo->prepare("INSERT INTO items (user_id, category_id, title, description, price, image_path) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->execute([$_SESSION['user_id'], $category_id, $title, $description, $price, $image_path]);
            $success = "Ogłoszenie zostało dodane pomyślnie!";
        } catch (PDOException $e) {
            $error = "Błąd bazy danych: " . $e->getMessage();
        }
    }
}

require_once 'header.php';
?>

<h2>Dodaj nowe ogłoszenie</h2>

<?php if($error): ?>
    <p style="color: red; font-weight: bold;"><?= $error ?></p>
<?php endif; ?>
<?php if($success): ?>
    <p style="color: green; font-weight: bold;"><?= $success ?></p>
<?php endif; ?>

<form method="POST" action="add_item.php" enctype="multipart/form-data" style="max-width: 500px;">
    <label>Tytuł ogłoszenia:</label>
    <input type="text" name="title" required>

    <label>Kategoria:</label>
    <select name="category_id" required>
        <?php foreach($categories as $cat): ?>
            <option value="<?= $cat['id'] ?>"><?= htmlspecialchars($cat['name']) ?></option>
        <?php endforeach; ?>
    </select>

    <label>Opis przedmiotu:</label>
    <textarea name="description" rows="5" required></textarea>

    <label>Cena (zł):</label>
    <input type="number" step="0.01" name="price" value="0.00" required>

    <label>Zdjęcie przedmiotu (opcjonalnie):</label>
    <input type="file" name="image" accept="image/png, image/jpeg, image/webp">

    <button type="submit" class="btn" style="margin-top: 15px;">Wystaw przedmiot</button>
</form>

<?php require_once 'footer.php'; ?>
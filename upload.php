<?php
include 'db.php';
if (!isset($_SESSION['user_id'])) header("Location: login.php");
$userId = $_SESSION['user_id'];
$currentFolderId = isset($_GET['folder_id']) ? intval($_GET['folder_id']) : NULL;
$message = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['filename'];
    $folderId = !empty($_POST['folder_id']) ? intval($_POST['folder_id']) : NULL;
    $file = $_FILES['file'];

    // Validate unique file name
    $query = $folderId
        ? "SELECT id FROM files WHERE user_id = ? AND filename = ? AND folder_id = ?"
        : "SELECT id FROM files WHERE user_id = ? AND filename = ? AND folder_id IS NULL";
    $stmt = $conn->prepare($query);
    if ($folderId) {
        $stmt->bind_param("isi", $userId, $name, $folderId);
    } else {
        $stmt->bind_param("is", $userId, $name);
    }
    $stmt->execute();
    if ($stmt->get_result()->num_rows > 0) {
        $message = "❌ File name already exists in this folder!";
    } else {
        if ($file['error'] === 0) {
            $target = "uploads/" . basename($file['name']);
            move_uploaded_file($file['tmp_name'], $target);
            $query = $folderId
                ? "INSERT INTO files (user_id, folder_id, filename, path) VALUES (?, ?, ?, ?)"
                : "INSERT INTO files (user_id, filename, path) VALUES (?, ?, ?)";
            $stmt = $conn->prepare($query);
            if ($folderId) {
                $stmt->bind_param("iiss", $userId, $folderId, $name, $target);
            } else {
                $stmt->bind_param("iss", $userId, $name, $target);
            }
            $stmt->execute();
            $message = "✅ File uploaded successfully!";
        } else {
            $message = "❌ File upload failed!";
        }
    }
    $stmt->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Upload File</title>
  <link rel="stylesheet" href="upload.css">
</head>
<body>
<h2 style="color: white; text-align: center">Upload a New File</h2>

<?php if ($message): ?>
  <div class="message"><?= $message ?></div>
<?php endif; ?>

<form method="POST" enctype="multipart/form-data" style="background: white; padding: 20px; border-radius: 6px; box-shadow: 0 0 0px rgba(0,0,0,0.1); text-align: center; justify-content: center; max-width: 400px; margin: auto;">
  <label for="filename">File Name:</label>
  <input type="text" name="filename" id="filename" required><br>
  
  <label for="folder_id">Select Folder:</label>
  <select name="folder_id" id="folder_id">
    <option value="">Root</option>
    <?php
    $stmt = $conn->prepare("SELECT id, folder_name FROM folders WHERE user_id = ? AND parent_folder_id IS NULL");
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $folders = $stmt->get_result();
    while ($folder = $folders->fetch_assoc()) {
        $selected = ($folder['id'] == $currentFolderId) ? 'selected' : '';
        echo "<option value='{$folder['id']}' $selected>" . htmlspecialchars($folder['folder_name']) . "</option>";
    }
    $stmt->close();
    ?>
  </select><br>
  
  <label for="file">Choose File:</label>
  <input type="file" name="file" id="file" required><br>
  
  <button type="submit">Upload</button>
</form>

<div class="dashboard-btn-container">
  <a href="dashboard.php<?php echo $currentFolderId ? '?folder_id=' . $currentFolderId : ''; ?>">
    <button class="dashboard-btn">← Go to Dashboard</button>
  </a>
</div>
</body>
</html>

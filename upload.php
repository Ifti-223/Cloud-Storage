<?php
include 'db.php';
if (!isset($_SESSION['user_id'])) header("Location: login.php");

$message = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $name = $_POST['filename'];
  $userId = $_SESSION['user_id'];
  $file = $_FILES['file'];

  if ($file['error'] === 0) {
    $target = "uploads/" . basename($file['name']);
    move_uploaded_file($file['tmp_name'], $target);
    $conn->query("INSERT INTO files (user_id, filename, path) VALUES ('$userId', '$name', '$target')");
    $message = "✅ File uploaded successfully!";
  } else {
    $message = "❌ File upload failed!";
  }
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

<h2 style= "color: white; text-align: center">Upload a New File</h2>

<?php if ($message): ?>
  <div class="message"><?= $message ?></div>
<?php endif; ?>

<form  method="POST" enctype="multipart/form-data" style="background: white; padding: 20px; border-radius: 6px; box-shadow: 0 0 0px rgba(0,0,0,0.1); text-align: center; justify-content: center; max-width: 400px; margin: auto;">
  <label for="filename">File Name:</label>
  <input type="text" name="filename" id="filename" required><br>
  
  <label for="file">Choose File:</label>
  <input type="file" name="file" id="file" required><br>
  
  <button type="submit">Upload</button>
</form>

<div class="dashboard-btn-container">
  <a href="dashboard.php">
    <button class="dashboard-btn">← Go to Dashboard</button>
  </a>
</div>


</body>
</html>

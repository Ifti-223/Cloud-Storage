<?php
include 'db.php';
if (!isset($_SESSION['user_id'])) header("Location: login.php");
$userId = $_SESSION['user_id'];

// Handle folder navigation
$currentFolderId = isset($_GET['folder_id']) ? intval($_GET['folder_id']) : NULL;
$breadcrumb = [];
if ($currentFolderId) {
    $stmt = $conn->prepare("SELECT id, folder_name, parent_folder_id FROM folders WHERE id = ? AND user_id = ?");
    $stmt->bind_param("ii", $currentFolderId, $userId);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($row = $result->fetch_assoc()) {
        $breadcrumb[] = ['id' => $row['id'], 'name' => $row['folder_name']];
        $parentId = $row['parent_folder_id'];
        while ($parentId) {
            $stmt->bind_param("ii", $parentId, $userId);
            $stmt->execute();
            $result = $stmt->get_result();
            if ($parentRow = $result->fetch_assoc()) {
                array_unshift($breadcrumb, ['id' => $parentRow['id'], 'name' => $parentRow['folder_name']]);
                $parentId = $parentRow['parent_folder_id'];
            } else {
                break;
            }
        }
    }
    $stmt->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Your Files</title>
  <link rel="stylesheet" href="dashboard.css">
</head>
<body>
<div class="header">
  <img src="logo2.png" alt="Logo" class="logo" style="width: 200px; height: auto;">
  <div>
    <a href='upload.php?folder_id=<?php echo $currentFolderId ? $currentFolderId : ""; ?>' class="button">Upload File</a>
    <a href='logout.php' class="button">Logout</a>
  </div>
</div>

<div class="breadcrumb">
  <a href="dashboard.php">Root</a>
  <?php foreach ($breadcrumb as $crumb): ?>
    / <a href="dashboard.php?folder_id=<?php echo $crumb['id']; ?>"><?php echo htmlspecialchars($crumb['name']); ?></a>
  <?php endforeach; ?>
</div>

<h2>Your Files and Folders</h2>

<div class="create-folder">
  <form method="POST" action="create_folder.php">
    <input type="hidden" name="parent_folder_id" value="<?php echo $currentFolderId ? $currentFolderId : ''; ?>">
    <input type="text" name="folder_name" placeholder="New Folder Name" required>
    <button type="submit">Create Folder</button>
  </form>
</div>

<div class="grid-container">
  <?php
  // Fetch folders
  $folderQuery = $currentFolderId
      ? "SELECT * FROM folders WHERE user_id = ? AND parent_folder_id = ?"
      : "SELECT * FROM folders WHERE user_id = ? AND parent_folder_id IS NULL";
  $stmt = $conn->prepare($folderQuery);
  if ($currentFolderId) {
      $stmt->bind_param("ii", $userId, $currentFolderId);
  } else {
      $stmt->bind_param("i", $userId);
  }
  $stmt->execute();
  $folders = $stmt->get_result();
  while ($folder = $folders->fetch_assoc()) {
      $folderId = $folder['id'];
      $folderName = htmlspecialchars($folder['folder_name']);
      echo "
        <div class='folder-card'>
          <div class='folder-icon'>📁</div>
          <span class='folder-name'><a href='dashboard.php?folder_id=$folderId'>$folderName</a></span>
          <button class='dropdown-btn' onclick='toggleDropdown(this)'>Options</button>
          <ul class='dropdown-menu'>
            <li onclick=\"renameItem('folder', $folderId, '$folderName')\">Rename</li>
            <li onclick=\"deleteFolder($folderId)\">Delete</li>
          </ul>
        </div>
      ";
  }
  $stmt->close();

  // Fetch files
  $fileQuery = $currentFolderId
      ? "SELECT * FROM files WHERE user_id = ? AND folder_id = ?"
      : "SELECT * FROM files WHERE user_id = ? AND folder_id IS NULL";
  $stmt = $conn->prepare($fileQuery);
  if ($currentFolderId) {
      $stmt->bind_param("ii", $userId, $currentFolderId);
  } else {
      $stmt->bind_param("i", $userId);
  }
  $stmt->execute();
  $files = $stmt->get_result();
  while ($row = $files->fetch_assoc()) {
      $fileId = $row['id'];
      $filePath = $row['path'];
      $fileName = htmlspecialchars($row['filename']);
      echo "
        <div class='file-card'>
          <div class='file-icon'>📄</div>
          <span class='filename'>$fileName</span>
          <button class='dropdown-btn' onclick='toggleDropdown(this)'>Options</button>
          <ul class='dropdown-menu'>
            <li onclick=\"window.open('$filePath', '_blank')\">Open</li>
            <li onclick=\"renameItem('file', $fileId, '$fileName')\">Rename</li>
            <li onclick=\"deleteFile($fileId)\">Delete</li>
          </ul>
        </div>
      ";
  }
  $stmt->close();
  ?>
</div>

<!-- Hidden forms -->
<form method="POST" action="delete.php" id="deleteFileForm" style="display:none;">
  <input type="hidden" name="file_id" id="fileToDelete">
</form>
<form method="POST" action="delete.php" id="deleteFolderForm" style="display:none;">
  <input type="hidden" name="folder_id" id="folderToDelete">
</form>
<form method="POST" action="rename.php" id="renameForm" style="display:none;">
  <input type="hidden" name="type" id="renameType">
  <input type="hidden" name="id" id="renameId">
  <input type="hidden" name="folder_id" value="<?php echo $currentFolderId ? $currentFolderId : ''; ?>">
</form>

<script>
  function toggleDropdown(btn) {
    document.querySelectorAll('.dropdown-menu').forEach(menu => {
      if (menu !== btn.nextElementSibling) menu.style.display = 'none';
    });
    const menu = btn.nextElementSibling;
    menu.style.display = (menu.style.display === 'block') ? 'none' : 'block';
  }

  function deleteFile(id) {
    if (confirm("Are you sure you want to delete this file?")) {
      document.getElementById('fileToDelete').value = id;
      document.getElementById('deleteFileForm').submit();
    }
  }

  function deleteFolder(id) {
    if (confirm("Are you sure you want to delete this folder and all its contents?")) {
      document.getElementById('folderToDelete').value = id;
      document.getElementById('deleteFolderForm').submit();
    }
  }

  function renameItem(type, id, currentName) {
    const newName = prompt(`Enter new name for ${type}:`, currentName);
    if (newName && newName !== currentName) {
      document.getElementById('renameType').value = type;
      document.getElementById('renameId').value = id;
      const form = document.getElementById('renameForm');
      const input = document.createElement('input');
      input.type = 'hidden';
      input.name = 'new_name';
      input.value = newName;
      form.appendChild(input);
      form.submit();
    }
  }

  document.addEventListener('click', function(e) {
    if (!e.target.classList.contains('dropdown-btn')) {
      document.querySelectorAll('.dropdown-menu').forEach(menu => {
        menu.style.display = 'none';
      });
    }
  });
</script>
</body>
</html>

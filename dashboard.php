<?php
include 'db.php';
if (!isset($_SESSION['user_id'])) header("Location: login.php");
$userId = $_SESSION['user_id'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Your Files</title>
  <style>
    body {
      font-family: Arial, sans-serif;
      margin: 0;
      padding: 20px;
      background: url('bg.jpg') no-repeat center center fixed;
      background-size: cover;
    }

    .header {
      display: flex;
      align-items: center;
      justify-content: space-between;
      background-color: transparent;
      padding: 10px 20px;
      border-radius: 8px;
      color: white;
      margin-bottom: 20px;
    }

    .header2 {
        display: flex;
        justify-content: flex-end; /* Align all child elements to the right */
        align-items: center;       /* Vertically center them */
        background-color: transparent;
        padding: 10px 20px;
        border-radius: 8px;
        color: white;
        margin-bottom: 20px;
    }

    .header img.logo {
      height: 60px;
    }
    

    h2 {
      color: white;
      text-align: center;
      background-color: black;
    }

    .grid-container {
      display: flex;
      flex-wrap: wrap;
      gap: 20px;
      
    }

    .file-card {
      background: white;
      width: 200px;
      padding: 15px;
      border: 0px solid;
      border-radius: 6px;
      position: relative;
      word-break: break-word;
      text-align: center;
    }

    .file-icon {
      font-size: 40px;
      margin-bottom: 10px;
    }

    .filename {
      font-weight: bold;
      display: block;
      margin-bottom: 10px;
    }

    .dropdown-btn {
      background-color: #007bff;
      color: white;
      border: none;
      padding: 6px 10px;
      border-radius: 4px;
      cursor: pointer;
      font-size: 14px;
    }

    .dropdown-menu {
      display: none;
      position: absolute;
      top: 120px;
      left: 50%;
      transform: translateX(-50%);
      background-color: #ffffff;
      border: 1px solid #ddd;
      border-radius: 4px;
      min-width: 120px;
      z-index: 1000;
      box-shadow: 0 4px 6px rgba(0,0,0,0.1);
    }

    .dropdown-menu li {
      padding: 8px 12px;
      cursor: pointer;
      border-bottom: 1px solid #eee;
    }

    .dropdown-menu li:last-child {
      border-bottom: 1px solid;
    }

    .dropdown-menu li:hover {
      background-color: #f0f0f0;
    }

    a.button {
      background-color: yellow;
      color: black;
      padding: 6px 12px;
      text-decoration: none;
      border-radius: 4px;
      margin-left: 10px;
    }

    a.button:hover {
      background-color: green;
    }
  </style>
</head>
<body>
<div class="header">
  <img src="insaff.PNG" alt="Logo" class="logo">
  <div>
    <a href='upload.php' class="button">Upload File</a>
    <a href='logout.php' class="button">Logout</a>
  </div>
</div>


<h2>Your Files</h2>
<div class="grid-container">
<?php
$res = $conn->query("SELECT * FROM files WHERE user_id = $userId");
while ($row = $res->fetch_assoc()) {
  $fileId = $row['id'];
  $filePath = $row['path'];
  $fileName = $row['filename'];
  echo "
    <div class='file-card'>
      <div class='file-icon'>📄</div>
      <span class='filename'>$fileName</span>
      <button class='dropdown-btn' onclick='toggleDropdown(this)'>Options</button>
      <ul class='dropdown-menu'>
        <li onclick=\"window.open('$filePath', '_blank')\">Open</li>
        <li onclick=\"deleteFile($fileId)\">Delete</li>
      </ul>
    </div>
  ";
}
?>
</div>

<!-- Hidden delete form -->
<form method="POST" action="delete.php" id="deleteForm" style="display:none;">
  <input type="hidden" name="file_id" id="fileToDelete">
</form>

<script>
  // Toggle dropdown menu
  function toggleDropdown(btn) {
    document.querySelectorAll('.dropdown-menu').forEach(menu => {
      if (menu !== btn.nextElementSibling) menu.style.display = 'none';
    });

    const menu = btn.nextElementSibling;
    menu.style.display = (menu.style.display === 'block') ? 'none' : 'block';
  }

  // Delete file via hidden form
  function deleteFile(id) {
    if (confirm("Are you sure you want to delete this file?")) {
      document.getElementById('fileToDelete').value = id;
      document.getElementById('deleteForm').submit();
    }
  }

  // Close dropdown if clicked outside
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

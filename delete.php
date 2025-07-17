<?php
include 'db.php';
if (!isset($_SESSION['user_id'])) header("Location: login.php");
$userId = $_SESSION['user_id'];

function deleteFolder($conn, $folderId, $userId) {
    // Delete files in folder
    $stmt = $conn->prepare("SELECT id, path FROM files WHERE folder_id = ? AND user_id = ?");
    $stmt->bind_param("ii", $folderId, $userId);
    $stmt->execute();
    $result = $stmt->get_result();
    while ($file = $result->fetch_assoc()) {
        if (file_exists($file['path'])) {
            unlink($file['path']);
        }
        $stmt2 = $conn->prepare("DELETE FROM files WHERE id = ? AND user_id = ?");
        $stmt2->bind_param("ii", $file['id'], $userId);
        $stmt2->execute();
        $stmt2->close();
    }
    $stmt->close();

    // Recursively delete subfolders
    $stmt = $conn->prepare("SELECT id FROM folders WHERE parent_folder_id = ? AND user_id = ?");
    $stmt->bind_param("ii", $folderId, $userId);
    $stmt->execute();
    $result = $stmt->get_result();
    while ($subfolder = $result->fetch_assoc()) {
        deleteFolder($conn, $subfolder['id'], $userId);
    }
    $stmt->close();

    // Delete the folder itself
    $stmt = $conn->prepare("DELETE FROM folders WHERE id = ? AND user_id = ?");
    $stmt->bind_param("ii", $folderId, $userId);
    $stmt->execute();
    $stmt->close();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['file_id'])) {
        $fileId = intval($_POST['file_id']);
        $stmt = $conn->prepare("SELECT path FROM files WHERE id = ? AND user_id = ?");
        $stmt->bind_param("ii", $fileId, $userId);
        $stmt->execute();
        $stmt->bind_result($filePath);
        if ($stmt->fetch()) {
            $stmt->close();
            if (file_exists($filePath)) {
                unlink($filePath);
            }
            $stmt = $conn->prepare("DELETE FROM files WHERE id = ? AND user_id = ?");
            $stmt->bind_param("ii", $fileId, $userId);
            $stmt->execute();
            $stmt->close();
        }
    } elseif (isset($_POST['folder_id'])) {
        $folderId = intval($_POST['folder_id']);
        deleteFolder($conn, $folderId, $userId);
    }
}

header("Location: dashboard.php");
exit;
?>

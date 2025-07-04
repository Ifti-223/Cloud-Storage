<?php
include 'db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['file_id'])) {
    $fileId = intval($_POST['file_id']);

    // Get file path before deleting
    $stmt = $conn->prepare("SELECT path FROM files WHERE id = ? AND user_id = ?");
    $stmt->bind_param("ii", $fileId, $_SESSION['user_id']);
    $stmt->execute();
    $stmt->bind_result($filePath);
    
    if ($stmt->fetch()) {
        $stmt->close();
        if (file_exists($filePath)) {
            unlink($filePath);
        }

        $stmt = $conn->prepare("DELETE FROM files WHERE id = ? AND user_id = ?");
        $stmt->bind_param("ii", $fileId, $_SESSION['user_id']);
        $stmt->execute();
    }
}

header("Location: dashboard.php");
exit;
?>
<?php
session_start();
require_once '../includes/db.php';

if (!isset($_SESSION['user_id'])) {
    die("Access denied.");
}

$user_id = $_SESSION['user_id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['project_title'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $course_id = intval($_POST['course_id'] ?? 0);
    $highlight = isset($_POST['highlight']) ? 1 : 0;

    if (empty($title) || empty($_FILES['project_files']['name'][0]) || $course_id === 0) {
        die("Please provide all required fields.");
    }

    function getUploadSubfolder($extension) {
        $ext = strtolower($extension);
        if (in_array($ext, ['jpg', 'jpeg', 'png', 'gif'])) return 'images';
        if (in_array($ext, ['mp4', 'mov', 'webm'])) return 'videos';
        if (in_array($ext, ['mp3', 'wav', 'ogg'])) return 'audios';
        return 'misc';
    }

    $baseFolder = '../uploads/projects/';
    $files = $_FILES['project_files'];

    for ($i = 0; $i < count($files['name']); $i++) {
        if ($files['error'][$i] !== UPLOAD_ERR_OK) continue;

        $original_name = basename($files['name'][$i]);
        $ext = strtolower(pathinfo($original_name, PATHINFO_EXTENSION));
        $subfolder = getUploadSubfolder($ext);
        $uploadDir = $baseFolder . $subfolder . '/';

        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }

        $filename = uniqid('proj_') . '_' . $original_name;
        $fullPath = $uploadDir . $filename;
        $relativePath = "projects/$subfolder/$filename";

        if (move_uploaded_file($files['tmp_name'][$i], $fullPath)) {
            $highlightPath = null;

            if (!empty($_FILES['highlight_reel']['name']) && $i === 0) {
                $highlightFile = $_FILES['highlight_reel'];
                $highlightExt = pathinfo($highlightFile['name'], PATHINFO_EXTENSION);
                $highlightSubfolder = getUploadSubfolder($highlightExt);
                $highlightDir = $baseFolder . $highlightSubfolder . '/';

                if (!is_dir($highlightDir)) {
                    mkdir($highlightDir, 0755, true);
                }

                $highlightName = uniqid('highlight_') . '_' . basename($highlightFile['name']);
                $highlightFullPath = $highlightDir . $highlightName;
                $highlightRelative = "projects/$highlightSubfolder/$highlightName";

                if (move_uploaded_file($highlightFile['tmp_name'], $highlightFullPath)) {
                    $highlightPath = $highlightRelative;
                }
            }

            $stmt = $pdo->prepare("INSERT INTO projects 
                (user_id, title, file_path, course_id, description, is_highlight, approved) 
                VALUES (?, ?, ?, ?, ?, ?, 0)");
            $stmt->execute([
                $user_id,
                $title,
                $relativePath,
                $course_id,
                $description,
                $highlight
            ]);
        }
    }

    header("Location: ../Public/profile.php?uploaded=1");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Upload Project</title>
</head>
<body>
    <h2>Upload Your Project</h2>

    <form method="POST" action="upload_project.php" enctype="multipart/form-data">
        <label for="project_title">Project Title:</label><br>
        <input type="text" name="project_title" id="project_title" required><br><br>

        <label for="description">Project Description:</label><br>
        <textarea name="description" id="description" rows="4" cols="50"></textarea><br><br>

        <label for="course_id">Select Course:</label><br>
        <select name="course_id" id="course_id" required>
            <option value="">-- Select Course --</option>
            <option value="1">Music Production</option>
            <option value="2">Sound Engineering</option>
            <option value="3">Graphic Design</option>
            <option value="4">Video Game Design</option>
            <option value="5">2D & 3D Animation</option>
            <option value="6">Film & TV Production</option>
            <option value="7">Video Production</option>
        </select><br><br>

        <label for="project_files">Upload Project Files (multiple allowed):</label><br>
        <input type="file" name="project_files[]" id="project_files" accept=".mp4,.mp3,.zip,.pdf,.jpg,.png,.mov" multiple required><br><br>

        <label for="highlight_reel">Upload Optional Highlight Reel:</label><br>
        <input type="file" name="highlight_reel" id="highlight_reel" accept=".mp4,.mov"><br><br>

        <button type="submit">Upload Project</button>
    </form>
</body>
</html>
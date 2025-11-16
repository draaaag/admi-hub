<?php
session_start();
require_once '../includes/db.php';

if (!isset($_SESSION['user_id'])) {
  header("Location: ../Public/login.php");
  exit();
}

$user_id = $_SESSION['user_id'];
$on_profile_page = true;
// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $form_type = $_POST['form_type'] ?? '';

  // ---- PROFILE UPDATE ----
  if ($form_type === 'profile') {
    $username    = trim($_POST['username']);
    $first_name  = trim($_POST['first_name'] ?? '');
    $last_name   = trim($_POST['last_name'] ?? '');
    $about_me    = trim($_POST['about_me']);
    $email       = trim($_POST['email']);

    $stmt = $pdo->prepare("UPDATE users SET username = ?, first_name = ?, last_name = ?, about_me = ?, email = ? WHERE id = ?");
    $stmt->execute([$username, $first_name, $last_name, $about_me, $email, $user_id]);

    if (isset($_FILES['profile_pic']) && $_FILES['profile_pic']['error'] === UPLOAD_ERR_OK) {
      $file = $_FILES['profile_pic'];
      $filename = basename($file['name']);
      $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
      $allowed = ['jpg', 'jpeg', 'png'];

      if (in_array($ext, $allowed)) {
        $upload_dir = "../uploads/profile_pics/";
        if (!is_dir($upload_dir)) {
          mkdir($upload_dir, 0755, true);
        }

        $new_name = uniqid() . "_" . $filename;
        $target_path = $upload_dir . $new_name;

        if (move_uploaded_file($file['tmp_name'], $target_path)) {
          $stmt = $pdo->prepare("UPDATE users SET profile_pic = ? WHERE id = ?");
          $stmt->execute([$new_name, $user_id]);
        } else {
          error_log("Failed to move uploaded file.");
        }
      } else {
        error_log("Invalid file type uploaded: $ext");
      }
    } elseif (isset($_FILES['profile_pic'])) {
      error_log("Upload error code: " . $_FILES['profile_pic']['error']);
    }

    header("Location: profile.php?updated=1");
    exit();
  }

}

// Load user profile info
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch();

if (!$user) {
  session_destroy();
  header("Location: ../Public/login.php");
  exit();
}

// Load user's projects
$stmt = $pdo->prepare("SELECT projects.*, courses.name AS course_name 
                       FROM projects 
                       JOIN courses ON projects.course_id = courses.id 
                       WHERE projects.user_id = ?");
$stmt->execute([$user_id]);
$projects = $stmt->fetchAll();

// Load courses for dropdown
$courses = $pdo->query("SELECT id, name FROM courses")->fetchAll();

// Set profile pic path
$default_pic_url = '/admihub/Public/assets/images/default-user.png';
$stored_filename = $user['profile_pic'] ?? '';
$pic_server_path = "../uploads/profile_pics/" . $stored_filename;
$pic_url_path = "/admihub/Public/uploads/profile_pics/" . $stored_filename;

$profile_pic_path = (!empty($stored_filename) && file_exists($pic_server_path)) 
    ? $pic_url_path 
    : $default_pic_url;
?>



<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Profile - ADMI Hub</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <style>
  body {
    font-family: 'Segoe UI', sans-serif;
    background-color: #f9f9f9;
    margin: 0;
  }

  header {
      background-color: #222; 
      color: #fff; 
      padding: 15px 20px;
      display: flex; 
      justify-content: space-between; 
      align-items: center;
    }

    header h1 { 
      font-family: Times, serif; 
      margin: 0; 
      font-size: 1.6rem; 
    }

    header a {
      color: #fff; 
      text-decoration: none; 
      margin-left: 20px; 
      font-family: Arial, sans-serif;
    }

  main {
    max-width: 700px;
    margin: 40px auto;
    text-align: center;
  }

  form {
    margin-top: 20px;
    text-align: left;
    background: #fff;
    padding: 40px;
    border-radius: 12px;
    box-shadow: 0 4px 12px rgba(0,0,0,0.05);
  }

  label {
    font-weight: bold;
    display: block;
    margin-bottom: 5px;
    color: #333;
  }

  input[type="text"],
  input[type="file"],
  textarea,
  select {
    width: 100%;
    padding: 12px;
    margin-bottom: 15px;
    border: 1px solid #ccc;
    border-radius: 8px;
    font-size: 1rem;
    font-family: Arial;
    transition: border-color 0.2s, box-shadow 0.2s;
  }

  input:focus,
  textarea:focus,
  select:focus {
    border-color: #28a745;
    box-shadow: 0 0 5px rgba(40, 167, 69, 0.2);
    outline: none;
  }

  button {
    background:  #28a745;
    color: white;
    border: none;
    padding: 10px 20px;
    border-radius: 6px;
    font-size: 1rem;
    cursor: pointer;
    margin-right: 10px;
  }

  button:hover {
    background-color:#2fbd50;
  }

  ul {
    list-style: none;
    padding: 0;
  }

  li {
    background: #fff;
    margin-bottom: 10px;
    padding: 10px;
    border-radius: 8px;
    box-shadow: 0 2px 5px rgba(0,0,0,0.05);
  }

  .about-tags span {
    background:#eee;
    padding:4px 10px;
    margin:5px;
    border-radius:20px;
    display:inline-block;
  }

  footer {
      margin-top: 60px;
      text-align: center;
      color: #777;
      padding: 20px;
    }
</style>

</head>
<body>

<header style="background-color: #222; padding: 10px 20px; color: white; display: flex; justify-content: space-between; align-items: center;">
    <div style="display: flex; align-items: center;">
       <a href="/admihub/Public/index.php">
         <img src="assets/images/admi-logo.png" alt="ADMI Logo" style="height: 60px; margin-right: 10px;">
       </a>
        <h1 style="margin-right:0px; font-size: 1.5rem; color: white;">ADMI Hub</h1>
    </div>
    <nav>
  <a href="/admihub/Public/index.php">Home</a> 
  <a href="/admihub/Public/highlight.php">Highlights</a>
  <a href="/admihub/Public/profile.php">Profile</a>

  <?php if (isset($_SESSION['user_id'])): ?>
    <?php if (isset($_SESSION['role']) && in_array($_SESSION['role'], ['admin', 'moderator'])): ?>
      <a href="/admihub/admin/dashboard.php">Dashboard</a>
    <?php endif; ?>
    <a href="/admihub/Public/logout.php">Logout</a>
  <?php else: ?>
    <a href="/admihub/Public/login.php">Login</a>
  <?php endif; ?>
</nav>

  </header>

  <main>
    <?php if (isset($_GET['updated'])): ?>
        <p style="color: green;">Profile updated successfully!</p>
    <?php endif; ?>

<!-- Profile Picture -->
<div style="text-align:center; margin-bottom: 10px;">
    <img src="<?= (!empty($stored_filename) && file_exists('../uploads/profile_pics/' . $stored_filename)) 
                  ? '../uploads/profile_pics/' . htmlspecialchars($stored_filename) 
                  : $default_pic_url ?>" 
         alt="Profile Picture" 
         style="width: 140px; height: 140px; object-fit: cover; border-radius: 50%; border: 2px solid #ccc;">
</div>

<!-- Name & Username -->
<div style="display: flex; flex-direction: column; align-items: center;">
    <h1 style="font-size: 2rem; margin: 10px 0;">
        <?= htmlspecialchars($user['first_name'] . ' ' . $user['last_name']) ?>
    </h1>
    <p style="color: gray;">@<?= htmlspecialchars($user['username']) ?></p>

    <?php if (!empty($user['about_me'])): ?>
        <div class="about-tags">
            <?php foreach (explode(',', $user['about_me']) as $tag): ?>
                <span><?= htmlspecialchars(trim($tag)) ?></span>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<!-- Upload/Delete Profile Picture Form -->
<form action="upload_profile_pic.php" method="POST" enctype="multipart/form-data" style="margin-bottom: 15px;">
    <h3>Edit Profile Picture</h3>

    <!-- File Upload -->
    <label>Profile Picture:</label><br>
    <input type="file" name="profile_pic" accept="image/*" style="margin-bottom: 10px;">

    <!-- Buttons -->
    <div style="display: flex; gap: 10px;">
        <button type="submit" name="action" value="upload" 
            style="background-color: #007bff; color: white; border: none; padding: 5px 10px; border-radius: 5px; cursor: pointer;">
            Upload Picture
        </button>

        <?php if (!empty($stored_filename) && $stored_filename !== basename($default_pic_url)): ?>
            <button type="submit" name="action" value="delete" 
                style="background-color: red; color: white; border: none; padding: 5px 10px; border-radius: 5px; cursor: pointer;">
                Delete Picture
            </button>
        <?php endif; ?>
    </div>
</form>

<!-- Profile Details Form -->
<form action="profile.php" method="POST">
    <input type="hidden" name="form_type" value="profile">

    <label>Username:</label>
    <input type="text" name="username" value="<?= htmlspecialchars($user['username']) ?>" required>

    <label>First name:</label>
    <input type="text" name="first_name" value="<?= htmlspecialchars($user['first_name']) ?>" required>

    <label>Last name:</label>
    <input type="text" name="last_name" value="<?= htmlspecialchars($user['last_name']) ?>" required>

    <label>Email:</label>
    <input type="text" name="email" value="<?= htmlspecialchars($user['email'] ?? '') ?>" required>

    <label>About Me (comma-separated interests):</label>
    <input type="text" name="about_me" value="<?= htmlspecialchars($user['about_me'] ?? '') ?>" required>

    <button type="submit">Save Changes</button>
</form>

<form action="upload_project.php" method="post" enctype="multipart/form-data" style="margin-top: 40px;">
  <input type="hidden" name="form_type" value="project">

  <h2>Upload a Project</h2>

  <label for="project_title">Project Title:</label>
  <input type="text" name="project_title" id="project_title" required>

  <label for="course_id">Courses:</label>
  <select name="course_id" required>
    <option value="">Select Course</option>
    <?php foreach ($courses as $course): ?>
      <option value="<?= $course['id'] ?>"><?= htmlspecialchars($course['name']) ?></option>
    <?php endforeach; ?>
  </select>

  <label for="project_file">Select Project File:</label>
  <input type="file" name="project_files[]" id="project_file" multiple required>


  <label for="description">Description:</label>
  <textarea name="description" id="description" rows="4" required></textarea>

  <label>
  <input type="checkbox" name="highlight">
  Mark as Highlight (Show on Highlights Page)
</label>


  <button type="submit">Upload Project</button>
</form>


  <a href="/admihub/Public/myprojects.php"><h3>My Projects</h3></a>
  <?php if (count($projects) > 0): ?>
    <ul>
  <?php foreach ($projects as $proj): ?>
    <li>
      <strong><?= htmlspecialchars($proj['title']) ?></strong> - <?= htmlspecialchars($proj['course_name'])  ?>

      <?= ($proj['is_highlight'] ?? 0) ? ' - Highlighted' : '' ?>

      <?php if ($proj['approved'] == 1): ?>
        <span style="color: green; font-weight: bold;"> - Uploaded</span>
      <?php else: ?>
        <span style="color: orange; font-weight: bold;"> - Submitted (Pending Approval)</span>
      <?php endif; ?>

      
    </li>
  <?php endforeach; ?>
</ul>

  <?php else: ?>
    <p>No projects uploaded yet.</p>
  <?php endif; ?>
</main>

<footer>
  &copy; <?= date('Y') ?> ADMI Hub. All rights reserved.
</footer>

</body>
</html>

<?php
session_start();
require_once '../includes/db.php';

$user_id = $_GET['id'] ?? null;

if (!$user_id || !is_numeric($user_id)) {
  echo "Invalid user ID.";
  exit;
}

// Get user data
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch();

if (!$user) {
  echo "User not found.";
  exit;
}

// Get user projects with course names
$stmt = $pdo->prepare("SELECT p.*, c.name AS course_name
                       FROM projects p
                       JOIN courses c ON p.course_id = c.id
                       WHERE p.user_id = ? AND p.approved = 1");
$stmt->execute([$user_id]);
$projects = $stmt->fetchAll();

// Profile picture logic
$default_pic = '/admihub/Public/assets/images/default-user.png';
$pic_path = (!empty($user['profile_pic']) && file_exists(__DIR__ . '/../uploads/profile_pics/' . $user['profile_pic']))
    ? '/admihub/uploads/profile_pics/' . htmlspecialchars($user['profile_pic'])
    : $default_pic;
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title><?= htmlspecialchars($user['first_name'] . ' ' . $user['last_name']) ?>'s Profile | ADMI Hub</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <style>
    body {
      font-family: 'Arial', sans-serif;
      background-color: #f9f9f9;
      margin: 0;
    }
    header {
      background-color: #222;
      color: white;
      padding: 10px 20px;
      display: flex;
      justify-content: space-between;
      align-items: center;
    }

    header h1 {
      font-family: Times, serif;
      margin: 0;
      font-size: 1.6rem;
    }

    header nav a {
      color: white;
      margin-left: 15px;
      text-decoration: none;
    }

    /* Profile */
  
    .profile-container {
      max-width: 800px;
      margin: 40px auto;
      text-align: center;
    }

    .profile-container img {
      width: 140px;
      height: 140px;
      object-fit: cover;
      border-radius: 50%;
      box-shadow: 0 4px 10px rgba(0,0,0,0.1);
    }

    .tag {
      background: #eee;
      padding: 6px 12px;
      margin: 5px;
      border-radius: 20px;
      display: inline-block;
      font-size: 0.9rem;
    }

    /* Projects */
    .project-list {
      display: grid;
      grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
      gap: 24px;
      margin-top: 40px;
      padding: 0 20px;
    }

    .project-card {
      background: #fff;
      padding: 20px;
      margin-bottom: 20px;
      border-radius: 12px;
      box-shadow: 0 4px 12px rgba(0,0,0,0.05);
      text-align: left;
    }
    .project-card h4 {
      margin-top: 0;
    }
    
    .project-card a {
      display: inline-block; 
      margin-top: 6px;
      color: #1987fc; 
      text-decoration: none;
    }

    .project-card a:hover { 
      text-decoration: underline; 
    }

    .media-preview img, .media-preview video {
      width: 100%;
      max-height: 200px;
      object-fit: cover;
      cursor: pointer;
      border-radius: 10px;
      transition: transform 0.3s ease;
    }

    .media-preview img:hover, .media-preview video:hover {
      transform: scale(1.02);
    }

    /* Modal */
    .media-modal {
      display: none;
      position: fixed;
      z-index: 999;
      left: 0; top: 0;
      width: 100%; height: 100%;
      background-color: rgba(0,0,0,0.9);
      justify-content: center;
      align-items: center;
    }

    .media-modal.active {
      display: flex;
    }

    .media-modal-content {
      max-width: 90%;
      max-height: 90%;
      border-radius: 0px;
      overflow: hidden;
    }

    .media-modal video, .media-modal img {
      width: 100%;
      height: auto;
      max-height: 90vh;
    }

    .media-modal-close {
      position: absolute;
      top: 20px; right: 30px;
      font-size: 2.5rem;
      color: white;
      cursor: pointer;
      z-index: 1001;
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

<div class="profile-container">
<?php
$default_pic = '/admihub/Public/assets/images/default-user.png';
$profile_pic_path = __DIR__ . '/../uploads/profile_pics/' . $user['profile_pic'];

$profile_pic_url = (!empty($user['profile_pic']) && file_exists($profile_pic_path))
    ? '/admihub/uploads/profile_pics/' . htmlspecialchars($user['profile_pic'])
    : $default_pic;
?>
<img src="<?= $profile_pic_url ?>" alt="Profile Picture">


  <h1><?= htmlspecialchars($user['first_name'] . ' ' . $user['last_name']) ?></h1>
  <p style="color: gray;">@<?= htmlspecialchars($user['username']) ?></p>

  <?php if (!empty($user['about_me'])): ?>
    <div>
      <?php foreach (explode(',', $user['about_me']) as $tag): ?>
        <span class="tag"><?= htmlspecialchars(trim($tag)) ?></span>
      <?php endforeach; ?>
    </div>
  <?php endif; ?>

  <h3 style="margin-top: 40px;">Projects</h3>

  <?php if (count($projects) > 0): ?>
    <div class="project-list">
      <?php foreach ($projects as $proj): ?>
        <div class="project-card">
        <p><strong>Title:</strong><?= htmlspecialchars($proj['title']) ?></p>
          <p><strong>Course:</strong> <?= htmlspecialchars($proj['course_name']) ?></p>
          <?php if (!empty($proj['description'])): ?>
          <p><strong>Description:</strong> <?= htmlspecialchars($proj['description']) ?></p>
        <?php endif; ?>

          <!-- Project File -->
          <?php
            $file_path = $proj['file_path'] ?? '';
            if (!empty($file_path)) {
              $ext = strtolower(pathinfo($file_path, PATHINFO_EXTENSION));
              $url = "/admihub/uploads/" . htmlspecialchars($file_path);

              if (in_array($ext, ['jpg', 'jpeg', 'png', 'gif', 'webp'])) {
                echo "<div class='media-preview' onclick=\"openModalImage('$url')\">
                        <img src='$url' alt='Project Image'>
                      </div>
                      <a href='$url' download>Download Image</a>";
              } elseif (in_array($ext, ['mp4', 'webm'])) {
                echo "<div class='media-preview' onclick=\"openModalVideo('$url')\">
                        <video src='$url' muted></video>
                      </div>
                      <a href='$url' download> Download Video</a>";
              } else {
                echo "<p><a href='$url' target='_blank'>📁 Download File</a></p>";
              }
            }

            if (!empty($proj['highlight_reel_path'])) {
              $highlight_url = "/admihub/uploads/" . htmlspecialchars($proj['highlight_reel_path']);
              echo "<div class='media-preview' onclick=\"openModalVideo('$highlight_url')\">
                      <video src='$highlight_url' muted></video>
                    </div>
                    <a href='$highlight_url' download>🎬 Download Highlight</a>";
            }
          ?>
        </div>
      <?php endforeach; ?>
    </div>
  <?php else: ?>
    <p>No projects uploaded yet.</p>
  <?php endif; ?>
</div>

<!-- Modal View -->
<div class="media-modal" id="mediaModal">
  <span class="media-modal-close" onclick="closeModal()">&times;</span>
  <div class="media-modal-content" id="modalContent"></div>
</div>

<script>
  const modal = document.getElementById("mediaModal");
  const modalContent = document.getElementById("modalContent");

  function openModal(media) {
    modalContent.innerHTML = "";
    const clone = media.cloneNode(true);
    clone.style.width = "95%";
    clone.style.height = "auto";
    clone.removeAttribute("controls");
    if (media.tagName === 'VIDEO') clone.setAttribute("controls", true);
    modalContent.appendChild(clone);
    modal.classList.add("active");
  }

  function closeModal() {
    modal.classList.remove("active");
    modalContent.innerHTML = "";
  }

  document.querySelectorAll('.media-preview img, .media-preview video').forEach(el => {
    el.addEventListener('click', () => openModal(el));
  });

  window.addEventListener('click', e => {
    if (e.target === modal) closeModal();
  });
</script>


<footer>
  &copy; <?= date('Y') ?> ADMI Hub. All rights reserved.
</footer>

</body>
</html>


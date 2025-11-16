<?php
session_start();
require_once '../../includes/db.php';

$user_id = $_SESSION['user_id'] ?? null;
$profile_pic = null;

if ($user_id) {
  $stmt = $pdo->prepare("SELECT profile_pic FROM users WHERE id = ?");
  $stmt->execute([$user_id]);
  $profile_pic = $stmt->fetchColumn();
}

$course_name = "Graphic Design";
$search = $_GET['search'] ?? '';

$query = "SELECT p.*, u.username, u.first_name, u.last_name, c.name AS course_name 
          FROM projects p 
          JOIN users u ON p.user_id = u.id 
          JOIN courses c ON p.course_id = c.id 
          WHERE c.name = ? AND p.approved = 1";



$params = [$course_name];

if (!empty($search)) {
  $query .= " AND (u.username LIKE ? OR p.title LIKE ?)";
  $searchTerm = '%' . $search . '%';
  $params[] = $searchTerm;
  $params[] = $searchTerm;
}

$stmt = $pdo->prepare($query);
$stmt->execute($params);
$projects = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title><?= htmlspecialchars($course_name) ?> Projects - ADMI Hub</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <style>
    body {
      font-family: Arial, sans-serif;
      margin: 0;
      padding: 0;
      background-color: #f9f9f9;
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

    .hero {
      position: relative;
      width: 100%;
      height: 400px;
    }

    .hero img {
     width:100%;
     height: auto;
     max-height: 400px; /* Adjust as needed */
     object-fit: cover;  
     filter: brightness(60%);
     border-radius: 0px; 
    }

    .hero-content {
      position: absolute;
      top: 50%;
      left: 50%;
      transform: translate(-50%, -50%);
      text-align: center;
      color: white;
    }

    .hero-content h1 {
      font-size: 2.5rem;
      margin-bottom: 20px;
    }

    .hero-search {
      display: flex;
      justify-content: center;
      max-width: 600px;
      margin: auto;
    }

    .hero-search input {
      padding: 12px 15px;
      border-radius: 30px 0 0 30px;
      border: none;
      width: 70%;
      font-size: 1rem;
    }

    .hero-search button {
      padding: 12px 25px;
      border-radius: 0 30px 30px 0;
      border: none;
      background-color: rgb(41, 155, 67);
      color: white;
      font-size: 1rem;
    }

    h2 {
      padding: 30px 20px 10px;
    }

    .project-grid {
      display: grid;
      grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
      gap: 20px;
      padding: 20px;
    }

    .project-card {
      background: white;
      padding: 1rem;
      border-radius: 10px;
      box-shadow: 0 0 10px rgba(0,0,0,0.1);
    }

    .project-card h4 {
      margin: 0;
      font-size: 1.1rem;
    }

    .project-card p {
      margin: 5px 0;
      font-size: 0.9rem;
    }

    .project-card a {
      color: #1987fc;
      text-decoration: none;
      font-size: 0.95rem;
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
       <img src="../assets/images/admi-logo.png" alt="ADMI Logo" style="height: 60px; margin-right: 10px;">
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

<div class="hero">
  <img src="../assets/images/graphic_design.jpg" alt="Hero Image">
  <div class="hero-content">
    <h1>Graphic Design</h1>
    <form method="GET" class="hero-search">
      <input type="text" name="search" value="<?= htmlspecialchars($search) ?>" placeholder="Search student or project...">
      <button type="submit">Search</button>
    </form>
  </div>
</div>

<h2><?= htmlspecialchars($course_name) ?> Projects</h2>

<?php if (count($projects) > 0): ?>
  <div class="project-grid">
    <?php foreach ($projects as $proj): ?>
      <div class="project-card">
        <h4><?= htmlspecialchars($proj['title']) ?></h4>
        <p><strong>By:</strong> <a href="/admihub/Public/view_profile.php?id=<?= $proj['user_id'] ?>">
            <?= htmlspecialchars($proj['first_name'] . ' ' . $proj['last_name']) ?>
        </a></p>

        <div class="media-preview">
          <?php if ($proj['file_path'] && preg_match('/\.(jpg|jpeg|png|gif)$/i', $proj['file_path'])): ?>
            <img src="../../uploads/<?= htmlspecialchars($proj['file_path']) ?>" alt="Project Image">
          <?php elseif ($proj['file_path'] && preg_match('/\.(mp4|webm|ogg)$/i', $proj['file_path'])): ?>
            <video src="../../uploads/<?= htmlspecialchars($proj['file_path']) ?>"></video>
          <?php endif; ?>
        </div>

        <?php if (!empty($proj['description'])): ?>
          <p><strong>Description:</strong> <?= htmlspecialchars($proj['description']) ?></p>
        <?php endif; ?>

        <?php if (!empty($proj['file_path'])): ?>
          <a href="../../uploads/<?= htmlspecialchars($proj['file_path']) ?>" download>Download Project</a><br>
        <?php endif; ?>

        <?php if (!empty($proj['highlight_reel_path'])): ?>
          <a href="/admihub/Public/uploads/<?= urlencode($proj['highlight_reel_path']) ?>" target="_blank">Watch Highlight</a>
        <?php endif; ?>
      </div>
    <?php endforeach; ?>
  </div>
<?php else: ?>
  <p style="padding: 20px;">No projects have been posted for this course yet.</p>
<?php endif; ?>

<!-- Modal View -->
<div class="media-modal" id="mediaModal">
  <span class="media-modal-close" onclick="closeModal()">&times;</span>
  <div class="media-modal-content" id="modalContent"></div>
</div>

<footer>
  &copy; <?= date('Y') ?> ADMI Hub. All rights reserved.
</footer>

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
</body>
</html>
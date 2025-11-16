<?php
session_start();
require_once '../includes/db.php';

if (!in_array($_SESSION['role'], ['admin', 'moderator'])) {
    die("Access denied.");
}

// --- Handle actions (Approve, Reject, Delete) ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['project_id'], $_POST['action'])) {
    $project_id = intval($_POST['project_id']);
    $action = $_POST['action'];

    // Get details for logging
    $stmt = $pdo->prepare("
        SELECT p.id, p.title, p.user_id, p.uploaded_at, c.name AS course_name,
               CONCAT(u.first_name, ' ', u.last_name) AS submitter_name
        FROM projects p
        JOIN users u ON p.user_id = u.id
        LEFT JOIN courses c ON p.course_id = c.id
        WHERE p.id = ?
    ");
    $stmt->execute([$project_id]);
    $project = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$project) {
        die("Project not found.");
    }

    // Current moderator/admin
    $stmt = $pdo->prepare("SELECT CONCAT(first_name, ' ', last_name) FROM users WHERE id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $performed_by_name = $stmt->fetchColumn();
    $performed_by_id   = $_SESSION['user_id']; // for foreign key

    if ($action === 'approve') {
        $pdo->prepare("UPDATE projects SET approved = 1 WHERE id = ?")->execute([$project_id]);

        $pdo->prepare("
            INSERT INTO project_logs 
                (project_id, submitted_at, project_title, course_name, user_id, submitter_name, 
                 approved_rejected_by, action, performed_by) 
            VALUES (?, ?, ?, ?, ?, ?, ?, 'approved', ?)
        ")->execute([
            $project['id'],
            $project['uploaded_at'],
            $project['title'],
            $project['course_name'],
            $project['user_id'],         // student (FK)
            $project['submitter_name'],
            $performed_by_name,
            $performed_by_id             // admin/mod (FK)
        ]);

    } elseif ($action === 'reject') {
        $pdo->prepare("DELETE FROM projects WHERE id = ?")->execute([$project_id]);

        $pdo->prepare("
            INSERT INTO project_logs 
                (project_id, submitted_at, project_title, course_name, user_id, submitter_name, 
                 approved_rejected_by, action, performed_by) 
            VALUES (?, ?, ?, ?, ?, ?, ?, 'rejected', ?)
        ")->execute([
            $project['id'],
            $project['uploaded_at'],
            $project['title'],
            $project['course_name'],
            $project['user_id'],         // student (FK)
            $project['submitter_name'],
            $performed_by_name,
            $performed_by_id             // admin/mod (FK)
        ]);

    } elseif ($action === 'delete') {
        $pdo->prepare("
            UPDATE project_logs 
            SET action = 'deleted', deleted_by = ?, deleted_at = NOW(), performed_by = ?
            WHERE project_id = ? AND action IN ('approved', 'rejected')
        ")->execute([$performed_by_name, $performed_by_id, $project_id]);

        $pdo->prepare("DELETE FROM projects WHERE id = ?")->execute([$project_id]);
    }

    header("Location: approve_projects.php");
    exit;
}


// --- Helpers ---
function formatDateTime($date) {
    return date('d/m/y h:i A', strtotime($date));
}
function renderMedia($filePath) {
    $ext = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));
    if (in_array($ext, ['jpg', 'jpeg', 'png', 'gif'])) {
        return "<img src='../uploads/$filePath' alt='' />";
    } elseif (in_array($ext, ['mp4', 'webm'])) {
        return "<video controls><source src='../uploads/$filePath'></video>";
    }
    return "<a href='../uploads/$filePath' download>Download File</a>";
}

// --- Load filter values ---
$search = $_GET['search'] ?? '';
$courseFilter = $_GET['course'] ?? '';
$statusFilter = $_GET['status'] ?? 'all';

// --- Load courses for filter dropdown ---
$courses = $pdo->query("SELECT id, name FROM courses ORDER BY name")->fetchAll(PDO::FETCH_ASSOC);

// --- Load projects ---
$sqlBase = "
    SELECT p.*, c.name AS course_name, u.first_name, u.last_name
    FROM projects p
    JOIN users u ON p.user_id = u.id
    LEFT JOIN courses c ON p.course_id = c.id
    WHERE 1
";
$params = [];

// Apply filters
if ($search) {
    $sqlBase .= " AND (p.title LIKE ? OR u.first_name LIKE ? OR u.last_name LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
    $params[] = "%$search%";
}
if ($courseFilter) {
    $sqlBase .= " AND p.course_id = ?";
    $params[] = $courseFilter;
}

$sqlPending = $sqlBase . " AND (p.approved IS NULL OR p.approved = 0) ORDER BY p.uploaded_at DESC";
$sqlApproved = $sqlBase . " AND p.approved = 1 ORDER BY p.uploaded_at DESC";

$stmt = $pdo->prepare($sqlPending);
$stmt->execute($params);
$pendingProjects = $stmt->fetchAll(PDO::FETCH_ASSOC);

$stmt = $pdo->prepare($sqlApproved);
$stmt->execute($params);
$approvedProjects = $stmt->fetchAll(PDO::FETCH_ASSOC);

$projectCount = $pdo->query("SELECT COUNT(*) FROM projects WHERE approved = 1")->fetchColumn();
$pendingCount = $pdo->query("SELECT COUNT(*) FROM projects WHERE approved = 0")->fetchColumn();
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Approve Projects</title>
<link rel="stylesheet" href="styles.css">
</head>
<style>

    body {
  font-family: Arial, sans-serif;
  margin: 0; padding: 0;
  background: #f4f4f4;
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
    }

    .search-bar { 
      display: flex; 
      justify-content: center; 
      gap: 10px; 
      margin-bottom: 30px; 
      flex-wrap: wrap; 
    }

    .search-bar input, .search-bar select { 
      padding: 10px; 
      border-radius: 6px; 
      border: 1px solid #ccc; 
    }

    .search-bar button { 
      padding: 10px 18px; 
      background: #0d6efd; 
      color: white; 
      border: none; 
      border-radius: 6px; 
      cursor: pointer; 
    }

    .project-card { 
      background: white; 
      padding: 20px; 
      border-radius: 10px; 
      margin-bottom: 25px; 
      box-shadow: 0 2px 8px rgba(0,0,0,0.05); 
    }

    .media-preview img, .media-preview video { 
      width: 100%; 
      max-width: 320px; 
      max-height: 200px; 
      border-radius: 10px; 
      cursor: pointer; 
      transition: transform 0.2s ease; 
    }

    .media-preview img:hover, .media-preview video:hover { 
      transform: scale(1.03); 
    }

    /* Base button style for all actions */
.btn {
  padding: 5px 10px;
  border: none;
  border-radius: 6px;
  font-size: 1rem;
  cursor: pointer;
  margin-right: 10px;
  transition: transform 0.2s ease, opacity 0.2s ease;
}

/* Button colors for actions */
.btn-approve {
  background-color: #28a745; 
  color: white;
}

.btn-reject {
  background-color: #dc3545; 
  color: white;
}

.btn-delete {
  background-color: #dc3545;
  color: white;
}

/* Hover effect for all buttons */
.btn:hover {
  transform: scale(1.05);
  opacity: 0.9;
}


    .actions button { 
      padding: 10px 20px; 
      border: none; 
      border-radius: 6px; 
      font-size: 1rem; 
      cursor: pointer; 
      margin-right: 10px;
     }

</style>
</head>
<body>
<header>
  <div style="display: flex; align-items: center;">
    <a href="/admihub/Public/index.php">
      <img src="../Public/assets/images/admi-logo.png" style="height: 60px; margin-right: 10px;">
    </a>
    <h1>ADMI Hub</h1>
  </div>
  <nav>
    <a href="/admihub/Public/index.php">Home</a>
    <a href="/admihub/Public/highlight.php">Highlights</a>
    <a href="/admihub/Public/profile.php">Profile</a>
    <a href="/admihub/admin/dashboard.php">Dashboard</a>
    <a href="/admihub/Public/logout.php">Logout</a>
  </nav>
</header>

<form method="GET" class="search-bar">
  <input type="text" name="search" placeholder="Search by name or title" value="<?= htmlspecialchars($search) ?>">
  <select name="course">
    <option value="">All Courses</option>
    <?php foreach ($courses as $course): ?>
      <option value="<?= $course['id'] ?>" <?= $courseFilter == $course['id'] ? 'selected' : '' ?>>
        <?= htmlspecialchars($course['name']) ?>
      </option>
    <?php endforeach; ?>
  </select>
  <select name="status" id="statusFilter">
    <option value="all" <?= $statusFilter === 'all' ? 'selected' : '' ?>>All</option>
    <option value="pending" <?= $statusFilter === 'pending' ? 'selected' : '' ?>>Pending Only</option>
    <option value="approved" <?= $statusFilter === 'approved' ? 'selected' : '' ?>>Approved Only</option>
  </select>
  <button type="submit">Filter</button>
</form>

<div id="pendingSection">
<h2>
  Pending Projects 
  <span style="font-size: 1rem; color: #555; margin-left: 10px;">
    <?= $pendingCount ?> pending
  </span>
</h2>

<?php if (empty($pendingProjects)): ?>
  <p>No pending projects.</p>
<?php else: ?>
  <?php foreach ($pendingProjects as $project): ?>
    <div class="project-card">
      <strong>Title:</strong> <?= htmlspecialchars($project['title']) ?><br>
      <strong>Submitted by:</strong> <?= htmlspecialchars($project['first_name'] . ' ' . $project['last_name']) ?><br>
      <strong>Submitted on:</strong> <?= formatDateTime($project['uploaded_at']) ?><br>
      <strong>Course:</strong> <?= htmlspecialchars($project['course_name']) ?><br>
      <strong>Description:</strong> <?= nl2br(htmlspecialchars($project['description'])) ?><br>
      <div class="media-preview"><?= renderMedia($project['file_path']) ?></div>
      <form method="post" style="display:inline;">
         <input type="hidden" name="project_id" value="<?= $project['id'] ?>">
         <button name="action" value="approve" class="btn btn-approve">Approve</button>
         <button name="action" value="reject" class="btn btn-reject" onclick="return confirm('Reject this project?');">Reject</button>
      </form>
    </div>
  <?php endforeach; ?>
<?php endif; ?>
</div>

<div id="approvedSection">
<h2>
  Approved Projects 
  <span style="font-size: 1rem; color: #555; margin-left: 10px;">
    <?= $projectCount ?> approved
  </span>
</h2>

<?php if (empty($approvedProjects)): ?>
  <p>No approved projects.</p>
<?php else: ?>
  <?php foreach ($approvedProjects as $project): ?>
    <div class="project-card">
      <strong>Title:</strong> <?= htmlspecialchars($project['title']) ?><br>
      <strong>Submitted by:</strong> <?= htmlspecialchars($project['first_name'] . ' ' . $project['last_name']) ?><br>
      <strong>Submitted on:</strong> <?= formatDateTime($project['uploaded_at']) ?><br>
      <strong>Course:</strong> <?= htmlspecialchars($project['course_name']) ?><br>
      <strong>Description:</strong> <?= nl2br(htmlspecialchars($project['description'])) ?><br>
      <div class="media-preview"><?= renderMedia($project['file_path']) ?></div>
      <form method="post" style="display:inline;">
         <input type="hidden" name="project_id" value="<?= $project['id'] ?>">
         <button name="action" value="delete" class="btn btn-delete" onclick="return confirm('Are you sure you want to Delete this project?');">Delete</button>
      </form>
    </div>
  <?php endforeach; ?>
<?php endif; ?>
</div>

<script>
function updateSections(val) {
  document.getElementById('pendingSection').style.display = (val === 'approved') ? 'none' : '';
  document.getElementById('approvedSection').style.display = (val === 'pending') ? 'none' : '';
}
updateSections("<?= $statusFilter ?>");
document.getElementById('statusFilter').addEventListener('change', function() {
  updateSections(this.value);
});
</script>
</body>
</html>

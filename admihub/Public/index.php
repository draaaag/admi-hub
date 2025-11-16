<?php session_start();
require_once '../includes/db.php';?>
<?php $user_id = $_SESSION['user_id'] ?? null; ?>


<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>ADMI Hub</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">

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
      font-family: Arial, sans-serif;
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

   footer h1{ 
    font-family: Times, sans-serif;
      margin: 0;
      font-size: 0.9rem;
    }

    .course-grid {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
      gap: 1rem;
      margin: 2rem;
    }

    .course-card {
     background: #fff;
     border-radius: 12px;
     box-shadow: 0 2px 12px rgba(0,0,0,0.1);
     transition: transform 0.2s ease, box-shadow 0.3s ease, opacity 0.3s ease;
     opacity: 1;
     pointer-events: auto;
     }

    .course-card.hide {
     opacity: 0;
     transform: scale(0.98);
     pointer-events: none;
     height: 0;
     margin: 0;
     padding: 0;
     overflow: hidden;
     }

    .course-card a {
     display: block;
     padding: 20px;
     text-decoration: none;
    color: #333;
     }

    .course-card h3 {
     margin: 10px 0 5px;
     font-size: 1.2rem;
     color: #222;
     }

    .course-card p {
     font-size: 0.95rem;
     color: #666;
     }

    .course-card:hover {
     transform: translateY(-5px);
     box-shadow: 0 6px 16px rgba(0,0,0,0.15);
    background-color: #f0f0f0;
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

 <!-- Hero Banner with Live Search -->
 <div style="position: relative; width: 100%; height: 350px; overflow: hidden;">
  <img src="assets/images/hero-banner.jpg" alt="Hero Image" style="width: 100%; height: 100%; object-fit: cover; filter: brightness(60%);">

   <div style="position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); text-align: center; color: white;">
    <h1 style="font-size: 2.5rem; margin-bottom: 20px;">Featured Courses</h1>
    
    <div style="display: flex; justify-content: center; max-width: 600px; margin: auto;">
      <input type="text" id="courseSearch" placeholder="Search courses..."
        style="padding: 12px 15px; border-radius: 30px 0 0 30px; border: none; width: 70%; font-size: 1rem;">
      <button disabled
        style="padding: 12px 25px; border-radius: 0 30px 30px 0; border: none; background-color: rgb(41, 155, 67); color: white; font-size: 1rem;">
        Search
      </button>
    </div>
   </div>
 </div>


<!--Student Projects by course-->
<main>
  <h2 style="text-align: center; margin-top: 30px;">Student Projects by Course</h2>

  <div class="course-grid">

  <div class="course-card">
  <a href="courses/music_production.php">
    <img src="assets/images/music_production.jpg"style="display: block; margin: 0 auto; width: 100%; height: 150px; border-radius: 8px;">
    <h3 style="text-align: center;">Music Production</h3>
    <p style="text-align: center;">Create, record & produce original music projects using industry tools.</p>
  </a>
  </div>


<div class="course-card">
  <a href="courses/sound_engineering.php">
    <img src="assets/images/sound_engineering.png"style="display: block; margin: 0 auto; width: 100%; height: 150px; border-radius: 8px;">
         <h3>Sound Engineering</h3>
        <p>Master audio mixing, studio setup & live sound techniques.</p>
      </a>
    </div>

    <div class="course-card">
  <a href="courses/graphic_design.php">
    <img src="assets/images/graphic_design.jpg" style="display: block; margin: 0 auto; width: 100%; height: 150px; border-radius: 8px;">
         <h3>Graphic Design</h3>
        <p>Design powerful visuals with creativity and professional tools.</p>
      </a>
    </div>

    <div class="course-card">
  <a href="courses/video_game_design.php">
    <img src="assets/images/video_game_design.jpg" style="display: block; margin: 0 auto; width: 100%; height: 150px; border-radius: 8px;">
         <h3>Video Game Design</h3>
        <p>Build games, characters, and game worlds from scratch.</p>
      </a>
    </div>

    <div class="course-card">
  <a href="courses/animation.php">
    <img src="assets/images/animation.jpg" style="display: block; margin: 0 auto; width: 100%; height: 150px; border-radius: 8px;">
         <h3>2D & 3D Animation</h3>
        <p>Animate characters and stories using modern animation techniques.</p>
      </a>
    </div>

    <div class="course-card">
  <a href="courses/film.php">
    <img src="assets/images/film.jpg" style="display: block; margin: 0 auto; width: 100%; height: 150px; border-radius: 8px;">
         <h3>Film & TV Production</h3>
        <p>Script, direct and produce cinematic short films and features.</p>
      </a>
    </div>

    <div class="course-card">
  <a href="courses/video_production.php">
    <img src="assets/images/video_production.png" style="display: block; margin: 0 auto; width: 100%; height: 150px; border-radius: 8px;">
         <h3>Video Production</h3>
        <p>Capture, edit, and produce creative videos for any platform.</p>
      </a>
    </div>
  </div>
</main>

 <div style="padding: 20px; background-color: #222; color: #fff; font-family: Arial;">
  <div style="display: flex; justify-content: space-between; flex-wrap: wrap;">
    
    <!-- Left Side -->
    <div style="flex: 1; min-width: 250px;">
      <h3 style="font-size: 1.3rem; color: green;">Get in Touch</h3>
      <p>Caxton House, 3rd Floor</p>
      <p>25, Kenyatta Avenue.</p>
      <p>P. O. Box 35447 - 00100</p>
      <p>Nairobi, Kenya.</p>
    </div>

    <!-- Right Side -->
    <div style="flex: 1; min-width: 250px; text-align: right;">
      <h3 style="font-size: 1.3rem; color: green;">Contact Info</h3>
      <p>Email: info@admi.ac.ke</p>
      <p>Phone: (+254) 706 349 696, (+254) 711 486 581</p>
      <p>WhatsApp: (+254) 711 486 581</p>
      <p>Hours: Mon-Fri 8:00am - 5:00pm / Sat: 8:00am to 2:00pm</p>
    </div>

  </div>

  <!-- Horizontal line -->
  <hr style="border: 0.4px solid white; margin-top: 20px;">
 </div>


<script>
  const searchInput = document.getElementById('courseSearch');
  const cards = document.querySelectorAll('.course-card');

  searchInput.addEventListener('input', function () {
    const query = this.value.toLowerCase();

    cards.forEach(card => {
      const text = card.textContent.toLowerCase();
      if (text.includes(query)) {
        card.classList.remove('hide');
      } else {
        card.classList.add('hide');
      }
    });
  });
</script>



  <footer style="text-align: center; padding: 10px; background-color: #222; color: #fff;">
     <h1> &copy; <?= date('Y') ?> <br> ADMI Hub. All rights reserved.</h1>
  </footer>

</body>
</html>

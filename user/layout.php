<?php
if(session_status() === PHP_SESSION_NONE){
    session_start();
}

$firm_name = isset($_SESSION['firm_name']) ? $_SESSION['firm_name'] : "User";
?>

<!-- HEADER + SIDEBAR CSS -->
<!-- <link rel="stylesheet" href="user/css/header.css"> -->
<link rel="stylesheet" href="/exp/user/css/header.css" />

<!-- FONT AWESOME -->
<link
  rel="stylesheet"
  href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css"
/>

<!-- HEADER -->

<div class="header">
  <div class="left-header">
    <button id="hamburger" class="hamburger">☰</button>
    <h1>EXPIROCHAIN</h1>
  </div>

  <div class="right-header">
    <span class="firm-name"> <?php echo htmlspecialchars($firm_name); ?> </span>

    <a href="/exp/logout.php" class="logout-btn"> Logout </a>
  </div>
</div>

<script>
  document.addEventListener("DOMContentLoaded", function () {
    const sidebar = document.getElementById("sidebar");
    const overlay = document.getElementById("overlay");
    const hamburger = document.getElementById("hamburger");

    if (hamburger) {
      hamburger.onclick = () => {
        sidebar.classList.add("active");
        overlay.classList.add("active");
      };
    }
  });
</script>

<?php $current_page = basename(parse_url($_SERVER['REQUEST_URI'],
PHP_URL_PATH)); ?>

<!-- SIDEBAR -->

<div id="sidebar" class="sidebar">
  <div class="sidebar-header">
    <h3>Menu</h3>
    <button id="closeSidebar" class="close-btn">✖</button>
  </div>

  <a
    href="/exp/home.php"
    class="<?php if($current_page=='home.php') echo 'active'; ?>"
  >
    Home
  </a>

  <a
    href="/exp/user/add_product.php"
    class="<?php if($current_page=='add_product.php') echo 'active'; ?>"
  >
    Add Product
  </a>

  <a
    href="/exp/user/products.php"
    class="<?php if($current_page=='products.php') echo 'active'; ?>"
  >
    Products
  </a>

  <a
    href="/exp/user/add_stock.php"
    class="<?php if($current_page=='add_stock.php') echo 'active'; ?>"
  >
    Add Stock
  </a>

  <a
    href="/exp/user/stock.php"
    class="<?php if($current_page=='stock.php') echo 'active'; ?>"
  >
    Stock
  </a>

  <a
    href="/exp/user/expiry_tracker.php"
    class="<?php if($current_page=='expiry_tracker.php') echo 'active'; ?>"
  >
    Expiry Tracker
  </a>

  <a
    href="/exp/user/marketplace.php"
    class="<?php if($current_page=='marketplace.php') echo 'active'; ?>"
  >
    Marketplace
  </a>

  <a
    href="/exp/user/received_requests.php"
    class="<?php if($current_page=='received_requests.php') echo 'active'; ?>"
  >
    Requests
  </a>

  <a
    href="/exp/user/account.php"
    class="<?php if($current_page=='account.php') echo 'active'; ?>"
  >
    Account
  </a>
</div>

<!-- OVERLAY -->

<div id="overlay" class="overlay"></div>

<script>
  const sidebar = document.getElementById("sidebar");
  const overlay = document.getElementById("overlay");
  const hamburger = document.getElementById("hamburger");
  const closeSidebar = document.getElementById("closeSidebar");

  /* OPEN SIDEBAR */

  if (hamburger) {
    hamburger.addEventListener("click", function () {
      sidebar.classList.add("active");
      overlay.classList.add("active");
    });
  }

  /* CLOSE BUTTON */

  if (closeSidebar) {
    closeSidebar.addEventListener("click", function () {
      sidebar.classList.remove("active");
      overlay.classList.remove("active");
    });
  }

  /* CLICK OUTSIDE SIDEBAR */

  if (overlay) {
    overlay.addEventListener("click", function () {
      sidebar.classList.remove("active");
      overlay.classList.remove("active");
    });
  }

  /* ESC KEY */

  document.addEventListener("keydown", function (e) {
    if (e.key === "Escape") {
      sidebar.classList.remove("active");
      overlay.classList.remove("active");
    }
  });
</script>

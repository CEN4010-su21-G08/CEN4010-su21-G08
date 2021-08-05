<?php
// define("app_page", true);
global $is_logged_in;
global $center_page;
?>
<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo(isset($page_title) ? $page_title . " - " : ""); ?>Burrow</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC" crossorigin="anonymous">
    <!-- <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Material+Icons"> -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.5.0/font/bootstrap-icons.css">
    <link href="./static/main.css" rel="stylesheet" />
    <script src="https://code.jquery.com/jquery-3.6.0.min.js" integrity="sha256-/xUj+3OJU5yExlq6GSYGSHk7tPXikynS7ogEvDej/m4=" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js" integrity="sha384-MrcW6ZMFYlzcLA8Nl+NtUVF0sA7MsXsP1UyJoMp4YLEuNSfAP+JcXn/tWtIaxVXM" crossorigin="anonymous"></script>
  </head>
  <body>
  <header>
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark burrow-nav">
      <div class="container-fluid">
      <a href="<?php echo($is_logged_in ? "courses.php" : "index.php"); ?>" class="navbar-brand">Burrow</a>
        <ul class="navbar-nav">
          <?php if ($is_logged_in) { ?>
          <div class="collapse navbar-collapse" id="mainNavbar">
              <ul class="navbar-nav">
                <li class="nav-item dropdown">
                  <a class="nav-link dropdown-toggle" href="#" id="mainNavbarMyAccount" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                  My Account
                  </a>
                  <ul class="dropdown-menu dropdown-menu-dark dropdown-menu-end" aria-labelledby="mainNavbarMyAccount">
                    <li><span class="dropdown-item" href="#"><?php echo htmlspecialchars($_SESSION['email']); ?></a></li>
                    <li><a class="dropdown-item" href="account.php">Account Settings</a></li>
                    <li><a class="dropdown-item" href="signout.php">Sign out</a></li>
                  </ul>
                </li>
              </ul>
            </div>
            <?php } else { ?>
            <li class="nav-item"><a href="signin.php" class="nav-link">Sign In</a></li>
              <?php } ?>
        </ul>
      </div>
    </nav>
  </header>
  <main>
    <?php if (!isset($include_sidebar)) {
      ?> <div class="main-content<?php if (isset($center_page)) { ?> main-content-center<?php }?>"><?php
    } ?>
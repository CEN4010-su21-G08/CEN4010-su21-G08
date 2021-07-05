<?php
$is_logged_in = true;
?>
<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo(isset($page_title) ? $page_title . " - " : ""); ?>Burrow</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC" crossorigin="anonymous">
    <link href="./static/main.css" rel="stylesheet" />
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
                    <li><span class="dropdown-item" href="#">example@fau.edu</a></li>
                    <li><a class="dropdown-item" href="#">Account Settings</a></li>
                    <li><a class="dropdown-item" href="#">Sign out</a></li>
                  </ul>
                </li>
              </ul>
            </div>
            <?php } ?>
        </ul>
      </div>
    </nav>
  </header>
<!doctype html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <link rel="shortcut icon" href="./favicon.ico" />
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="Cute Cloths by An">
    <meta name="author" content="Mees Lindeman">
    <title>Cute Cloths by An • <?php echo $title?></title>
    <!-- Used for icons, see https://fontawesome.com/icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.1/css/all.min.css" integrity="sha512-+4zCK9k+qNFUR5X+cKL9EIR+ZOhtIloNl9GIKS57V1MyNsYpYcUrUeQc9vNfzsWfV28IaLL3i96P9sdNyeRssA==" crossorigin="anonymous" />
    <link rel="preconnect" href="https://fonts.gstatic.com">
    <link href="https://fonts.googleapis.com/css2?family=Kalam:wght@400;700&display=swap" rel="stylesheet">  
    <link href="https://fonts.googleapis.com/css2?family=Raleway:wght@300;400;600&display=swap" rel="stylesheet"> 
    <link href="styles.css" rel="stylesheet">
  </head>
  <body>
    <div class="container">
      <header>
        <h1>Cute Cloths by An</h1>
        <nav>
          <ul>
            <li><a href="?" <?php if ($page=='home') echo 'class="active"'?>>Home</a></li>
            <li><a href="?page=producten" <?php if ($page=='producten') echo 'class="active"'?>>Producten</a></li>
            <li><a href="?page=contact" <?php if ($page=='contact') echo 'class="active"'?>>Contact</a></li>
          </ul>
          <div id="user">
          <?php if ($user): ?>
            <a class="button" href="?page=profiel"><i class="far fa-id-card"></i> Profiel</a>
            <a class="button" href="?page=logout"><i class="fas fa-sign-out-alt"></i> Afmelden</a>
          <?php else: ?>
            <a class="button" href="?page=inloggen"><i class="fas fa-sign-in-alt"></i> Aanmelden</a>
          <?php endif?>
        </div>
        </nav>
      </header>
      <main>
        <?php if ($page == 'producten'): ?>
          <div id="categories">
          <?php foreach($app->getCategories() as $category): 
              $primaryClass = (@$_REQUEST['category'] == $category['id']) ? "button-primary" : "";
          ?>
              <a class="button <?php echo $primaryClass?> " href="?page=producten&amp;category=<?php echo $category['id']?>"><?php echo $category['name']?></a>
          <?php endforeach?>
          </div>
        <?php endif?>
        <h2><?php echo $title?></h2>

        <?php include 'messages.php'?>
        <?php if ($lead) :?><p class="lead"><?php echo $lead?></p><?php endif?>
        <?php include "./pages/{$page}.php" ?>
      </main>
      <footer>
          <nav>
            <ul class="list-inline">
              <li><a href="?page=team">Team</a></li>
              <li><a href="?page=privacy">Privacy</a></li>
              <li><a href="?page=voorwaarden">Voorwaarden</a></li>
              <li><a href="?page=contact">Contact</a></li>
            </ul>
        </nav>
      </footer>
    </div>

  </body>
</html>

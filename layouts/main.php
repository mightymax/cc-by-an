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
    <link href="styles.css?<?php echo time()?>" rel="stylesheet">
  </head>
  <body>
    <!-- see bug https://bugzilla.mozilla.org/show_bug.cgi?id=1404468 -->
    <script>0</script>
    <div class="container">
      <header>
        <h1>Cute Cloths by An</h1>
        <nav>
          <ul>
            <li><a href="?" <?php if ($page=='home') echo 'class="active"'?>>Home</a></li>
            <li><a href="?page=producten" <?php if ($page=='producten') echo 'class="active"'?>>Producten</a></li>
            <li><a href="?page=contact" <?php if ($page=='contact') echo 'class="active"'?>>Contact</a></li>
            <?php if ($app->getAppUser()):?>
            <li><a href="?page=admin">Product toevoegen</a></li>
            <?php endif ?>
          </ul>
          <div id="user">
          <?php if ($app->getAppUser()): ?>
            <a class="button" href="?page=profiel"><i class="far fa-id-card"></i><span>Profiel</span></a>
            <a class="button" href="?page=logout"><i class="fas fa-sign-out-alt"></i> <span>Afmelden</span></a>
          <?php else: ?>
            <a class="button" href="?page=inloggen"><i class="fas fa-sign-in-alt"></i> <span>Aanmelden</span></a>
          <?php endif?>
          <?php if ($countShoppingCart = $app->countShoppingCart()) :?>
            <a class="button" href="?page=winkelwagen"><i class="fas fa-shopping-cart"></i> <span>Winkelwagen </span> (<?php echo $countShoppingCart?>)</a>
            <?php endif?>
        </div>
        </nav>
      </header>
      <main class="page-<?php echo $page ?>">
        <?php if ($page == 'producten'): ?>
          <div id="categories">
          <?php foreach($app->getCategories() as $category): 
              $primaryClass = (@$_REQUEST['category'] == $category['id']) ? "button-primary" : "";
          ?>
              <a class="button <?php echo $primaryClass?> " href="?page=producten&amp;category=<?php echo $category['id']?>"><?php echo $category['name']?></a>
          <?php endforeach?>
          </div>
          <h2><?php echo $title?></h2>
        <?php elseif ($page == 'product'): ?>
          <?php $id = intval($_REQUEST['product']);
                $product = $app->getProduct($id);?>
          <h2><?php echo $product['name']?></h2>
        <?php else: ?>
        <h2><?php echo $title?></h2>
        <?php endif ?>
        <?php 
          // include the messages and the main page. Pay attention to the path, __DIR__ contains the current
          // Php path of this script
          include __DIR__ . '/messages.php';
          include __DIR__ . "/../pages/{$page}.php";
        ?>
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
        <nav>
            <ul class="list-inline">
              <li><a href="https://www.instagram.com/cute_cloths_by_an/" title="Instagram"><i class="fab fa-instagram-square"></i></a></li>
            </ul>
        </nav>
      </footer>
    </div>

  </body>
</html>

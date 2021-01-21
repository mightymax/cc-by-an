<!doctype html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <link rel="shortcut icon" href="./favicon.ico" />
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="Cute Cloths by An">
    <meta name="author" content="Mees Lindeman">
    <title>Cute Cloths by An • <?php echo ucfirst($page)?></title>
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
        <a href="?"><h1>Cute Cloths by An</h1></a>
        <nav>
          <ul>
            <?php foreach (['home', 'producten', 'contact'] as $_page) :?>
              <li><a href="?page=<?php echo $_page?>" <?php if ($page==$_page) echo 'class="active"'?>><?php echo ucfirst($_page)?></a></li>
            <?php endforeach?>
          </ul>
          <?php include __DIR__ . '/usermenu.php' ?>
        </nav>
      </header>
      <main class="page-<?php echo $page ?>">
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

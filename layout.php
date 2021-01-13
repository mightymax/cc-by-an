<!doctype html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <link rel="icon" type="image/png" href="data:image/png;base64,iVBORw0KGgo=">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="Haakwerk van de moeder van Mees">
    <meta name="author" content="Mees Lindeman">
    <title>Haakwerk Webshop • <?php echo $title?></title>
    <!-- Used for icons, see https://fontawesome.com/icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.1/css/all.min.css" integrity="sha512-+4zCK9k+qNFUR5X+cKL9EIR+ZOhtIloNl9GIKS57V1MyNsYpYcUrUeQc9vNfzsWfV28IaLL3i96P9sdNyeRssA==" crossorigin="anonymous" />
    <link href='//fonts.googleapis.com/css?family=Raleway:400,300,600' rel='stylesheet' type='text/css'>
    <link href="styles.css" rel="stylesheet">
  </head>
  <body>
    <div class="container">
      <header>
        <h1>Haakwerk van de Moeder van Mees</h1>
        <nav>
          <ul>
            <li><a href="?">Home</a></li>
            <li><a href="?page=producten">Producten</a></li>
            <li><a href="?page=contact">Contact</a></li>
          </ul>
          <div id="user">
          <?php if ($user): ?>
            <a class="button" href="?page=profiel"><i class="far fa-id-card"></i> Profiel</a>
          <?php else: ?>
            <a class="button" href="?page=inloggen"><i class="fas fa-sign-in-alt"></i> Inloggen</a>
          <?php endif?>
        </div>
        </nav>
      </header>
      <main>
        <h2><?php echo $title?></h2>
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

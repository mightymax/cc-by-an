<!doctype html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="Haakwerk van de moeder van Mees">
    <meta name="author" content="Mees Lindeman">
    <title>Haakwerk Webshop • <?php echo $title?></title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0-beta1/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-giJF6kkoqNQ00vy+HMDP7azOuL0xtbfIcaT9wjKHr8RbDVddVHyTfAAsrekwKmP1" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.3.0/font/bootstrap-icons.css">
    <link href="styles.css" rel="stylesheet">
  </head>
  <body>
    
<header class="d-flex flex-column flex-md-row align-items-center p-3 px-md-4 mb-3 bg-white border-bottom shadow-sm">
  <p class="h5 my-0 me-md-auto fw-normal">Haakwerk van de Moeder van Mees</p>
  <nav class="my-2 my-md-0 me-md-3">
    <a class="p-2 text-dark" href="/">Home</a>
    <a class="p-2 text-dark" href="?page=producten">Producten</a>
    <a class="p-2 text-dark" href="?page=contact">Contact</a>
  </nav>
  <?php if ($user): ?>
  <a class="btn btn-outline-primary" href="?page=profiel">Profiel</a>
  <?php else: ?>
  <a class="btn btn-outline-primary" href="?page=inloggen">Inloggen</a>
  <?php endif?>
</header>

<main class="container">
  <div class="pricing-header px-3 py-3 pt-md-5 pb-md-4 mx-auto text-center">
    <h1 class="display-4"><?php echo $title?></h1>
    <?php if ($lead) :?><p class="lead"><?php echo $lead?></p><?php endif?>
  </div>

  <div class="<?php echo $page?>">
  <?php include "./pages/{$page}.php" ?>
  </div>

  <footer class="pt-4 my-md-5 pt-md-5 border-top">
    <div class="row">
      <div class="col-12 col-md">
        <small class="d-block mb-3 text-muted">&copy; 2021</small>
      </div>
      <div class="col-6 col-md">
      <h5>Over</h5>
        <ul class="list-unstyled text-small">
          <li><a class="link-secondary" href="?page=team">Team</a></li>
          <li><a class="link-secondary" href="?page=privacy">Privacy</a></li>
          <li><a class="link-secondary" href="?page=voorwaarden">Voorwaarden</a></li>
        </ul>
      </div>
      <div class="col-6 col-md">
        <!-- <h5>Resources</h5> -->
      </div>
      <div class="col-6 col-md">
        <!-- <h5>Features</h5> -->
      </div>
    </div>
  </footer>
</main>


    
  </body>
</html>

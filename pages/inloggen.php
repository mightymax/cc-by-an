<?php
if ($app->getAppUser()) {
  // User is already logged in, redirect to profile
  $app->redirect('profiel');
}
// Only if the (hidden) POST var 'login' is set, we need to try to log this user in
// By checking this, we are sure that the user pushed the login button
if (isset($_POST['login'])) {
  if (!isset($_POST['email']) || !isset($_POST['password'])) {
      $this->setMessage('Vul uw e-mailadres en wachtwoord in om in te loggen.', 'error');
      $this->redirect('inloggen');
  }
  $page = (true == $app->login($_POST['email'], $_POST['password'])) ? 'home' : 'inloggen';
  $app->redirect($page);
}
?>
<form action="?page=inloggen" method="POST">
  <div class="row">
      <div class="three columns">
          <?php echo $app->getCrfsToken() ?>
          <input type="hidden" name="login" value="1">
          <label for="email">Emailadres</label>
          <input type="email" name="email" id="email" required>
      </div>
      <div class="three columns">
          <label for="password">Wachtwoord</label>
          <input type="password" name="password" id="password">
      </div>
      <div class="three columns">
        <label>&nbsp;</label>
        <button type="submit" class="button-primary"><i class="fas fa-sign-in-alt"></i>  Inloggen</button>
      </div>
  </div>
  <div class="row" style="margin-top:20px;">
    <div class="six columns">
    <h4>Nog geen account?</h4>
    <p>
      <a href="?page=profiel" class="button">
          <i class="fas fa-user-circle"></i>
          Maak een nieuw account aan
      </a>
    </p>
    </div>
  </div>
</form>

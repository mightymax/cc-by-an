<?php
//Logout:
if (isset($_REQUEST['logout'])) {
  $app->logout();
}

if ($app->getAppUser()) {
  // User is already logged in, redirect to profile
  $app->redirect('profiel');
}

if (isset($_REQUEST['forgotpassword'])) {
  $app->userForgotPassword(@$_REQUEST['email']);
  exit;
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
<h2>Inloggen op onze webshop</h2>
<form action="?page=inloggen" method="POST">
  <div class="row">
      <div class="four columns">
          <?php echo $app->getCrfsToken() ?>
          <input type="hidden" name="login" value="1">
          <label for="email">Emailadres</label>
          <input type="email" name="email" id="email" required placeholder="Typ een geldig e-mailadres" value="<?php echo @$_GET['email']?>">
      </div>
      <div class="four columns">
          <label for="password">Wachtwoord</label>
          <input type="password" name="password" id="password" required placeholder="Typ uw wachtwoord">
      </div>
      <div class="three columns">
        <label>&nbsp;</label>
        <button type="submit" class="button-primary"><i class="fas fa-sign-in-alt"></i>  Inloggen</button>
      </div>
  </div>
</form>
<form action="?" method="GET">
  <div class="row" style="margin: 20px 0 0 0;">
    <div class="six columns"><h4>Nog geen account?</h4></div>
  </div>
  <div class="row" style="margin-bottom: 20px;">
    <div class="six columns">
      <button type="submit" class="button" name="page" value="profiel"><i class="fas fa-user"></i>  Maak nu een nieuw account</button>
    </div>
  </div>
</form>
<form action="?" method="GET">
  <div class="row" style="margin-bottom: 0px;">
    <div class="six columns"><h4>Wachtwoord vergeten?</h4></div>
  </div>
  <div class="row">
    <div class="four columns">
        <input type="email" name="email" required placeholder="E-mail waarmee uw account is aangemaakt">
    </div>
    <div class="four columns">
      <input type="hidden" name="forgotpassword">
      <button type="submit" class="button" name="page" value="inloggen"><i class="fas fa-unlock"></i>  Aanvragen</button>
    </div>
  </div>
</form>

<form action="?inloggen" method="POST">
  <div class="row">
      <div class="three columns">
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
</form>

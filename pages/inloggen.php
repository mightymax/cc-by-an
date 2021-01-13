<form action="?inloggen" method="POST">
    <p>
      <input type="hidden" name="login" value="1">
      <label for="email">Emailadres</label>
      <input type="email" name="email" class="form-control" id="email" required>
    </p>
    <p>
      <label for="password">Wachtwoord</label>
      <input type="password" name="password" class="form-control" id="password">
    </p>
    <p>
      <button type="submit" class="button-primary"><i class="fas fa-sign-in-alt"></i>  Inloggen</button>
    </p>
</form>

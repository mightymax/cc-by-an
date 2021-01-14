<?php
$app->gatekeeper($user);

if (isset($_POST['profiel'])) {
    $app->saveUser($user, $_POST);
}
?>
<form class="profiel" action="?page=profiel" method="POST">
    <div class="row">
        <div class="four columns">
            <p>
                <input type="hidden" name="profiel" value="1">
              <label for="naam">Naam <i class="fas fa-asterisk"></i></label>
              <input type="text" name="name" class="form-control" id="name" required value="<?php echo $user['name']?>">
            </p>
        </div>
        <div class="four columns">
            <p>
              <label for="email">Emailadres <i class="fas fa-asterisk"></i></label>
              <input type="email" name="email" class="form-control" id="email" required value="<?php echo $user['email']?>">
            </p>
        </div>
    </div>
    <p></p>
    <div class="row">
        <div class="two columns">
            <p>
                <label for="postalcode">Postcode</label>
                <input type="text" name="postalcode" class="form-control" id="postalcode" value="<?php echo $user['postalcode']?>">
            </p>
        </div>
        <div class="two columns">
            <p>
                <label for="housenumber">Huisnummer</label>
                <input type="text" name="housenumber" class="form-control" id="housenumber" value="<?php echo $user['housenumber']?>">
            </p>
        </div>
        <div class="three columns">
            <p>
              <label for="phone">Telefoon </label>
              <input type="text" name="phone" class="form-control" id="phone" value="<?php echo $user['phone']?>">
            </p>
        </div>
    </div>
    <div class="row">
        <div class="three columns">
            <p>
                <label for="password">Wachtwoord</label>
                <input type="password" name="password" class="form-control" id="password">
            </p>
        </div>
        <div class="three columns">
            <p>
                <label for="password2"> (controle)</label>
                <input type="password" name="password2" class="form-control" id="password2">
            </p>
        </div>
    </div>

    <p>
      <button type="submit" class="button-primary"><i class="far fa-save"></i>  Opslaan</button>
    </p>
</form>

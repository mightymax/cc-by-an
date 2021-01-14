<?php
$app->gatekeeper($user);

if (isset($_POST['profiel'])) {
    $app->saveUser($user, $_POST);
}
?>
<form class="profiel" action="?page=profiel" method="POST">
    <div class="row">
        <div class="four columns">
            <input type="hidden" name="profiel" value="1">
            <label for="naam">Naam <i class="fas fa-asterisk"></i></label>
            <input type="text" name="name" id="name" required value="<?php echo $user['name']?>">
        </div>
        <div class="four columns">
            <label for="email">Emailadres <i class="fas fa-asterisk"></i></label>
            <input type="email" name="email" id="email" required value="<?php echo $user['email']?>">
        </div>
    </div>
    <p></p>
    <div class="row">
        <div class="two columns">
            <label for="postalcode">Postcode</label>
            <input type="text" name="postalcode" id="postalcode" value="<?php echo $user['postalcode']?>">
        </div>
        <div class="two columns">
            <label for="housenumber">Huisnummer</label>
            <input type="text" name="housenumber" id="housenumber" value="<?php echo $user['housenumber']?>">
        </div>
        <div class="three columns">
            <label for="phone">Telefoon </label>
            <input type="text" name="phone" id="phone" value="<?php echo $user['phone']?>">
        </div>
    </div>
    <div class="row">
        <div class="three columns">
            <label for="password">Wachtwoord</label>
            <input type="password" name="password" id="password">
        </div>
        <div class="three columns">
            <label for="password2"> (controle)</label>
            <input type="password" name="password2" id="password2">
        </div>
    </div>

    <div class="row">
        <div class="three columns">
            <label>&nbsp;</label>
            <button type="submit" class="button-primary"><i class="far fa-save"></i>  Opslaan</button>
        </div>
    </div>
</form>

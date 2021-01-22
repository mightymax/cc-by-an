<?php
//edit user from an administrator:
if ($app->formIsPosted() && isset($_REQUEST['user'])) {
    $app->gateKeeper(true);
    $user = $app->getUser($_REQUEST['user']);
    if (!$user) {
        $app->setMessage('Gebruiker `'.$_REQUEST['user'].'` niet gevonden', 'warning');
        $app->redirect('users');
    }
    $user['isNew'] = false;
} else {
    $user = $app->getAppUser();
    if ($user === false) {
        $user = $app->getEmptyUser();
    } else {
        $user['isNew'] = false;
    }
}

if ($app->formIsPosted() && isset($_POST['profiel'])) {
    if ($app->getAppUser()) {
        $app->saveProfile($_POST);
    } else {
        $app->saveProfile($_POST, true);
    }
}
?>
<h2>Pas hier uw profiel aan</h2>
<form class="form" action="?page=profiel" method="POST">
    <div class="row">
        <div class="four columns">
            <?php echo $app->getCrfsToken() ?>
            <input type="hidden" name="id" value="<?php echo @$_REQUEST['user']?>">
            <input type="hidden" name="profiel" value="1">
            <label for="naam">Naam <i class="fas fa-asterisk"></i></label>
            <input type="text" name="name" id="name" required value="<?php echo $user['name']?>">
        </div>
        <div class="four columns">
            <label for="email">Emailadres <i class="fas fa-asterisk"></i></label>
            <input type="email" name="email" id="email" required value="<?php echo $user['email']?>">
        </div>
    </div>
    <div class="row">
        <div class="two columns">
            <label for="postalcode">Postcode <i class="fas fa-asterisk"></i></label>
            <input type="text" name="postalcode" id="postalcode" value="<?php echo $user['postalcode']?>">
        </div>
        <div class="two columns">
            <label for="housenumber">Huisnummer <i class="fas fa-asterisk"></i></label>
            <input type="text" name="housenumber" id="housenumber" required value="<?php echo $user['housenumber']?>">
        </div>
        <div class="four columns">
            <label for="streetname">Straat <i class="fas fa-asterisk"></i></label>
            <input type="text" name="streetname" id="streetname" required value="<?php echo $user['streetname']?>">
        </div>
    </div>
    <div class="row">
        <div class="four columns">
            <label for="place">Plaatsnaam <i class="fas fa-asterisk"></i></label>
            <input type="text" name="place" id="place" required value="<?php echo $user['place']?>">
        </div>
        <div class="four columns">
            <label for="phone">Telefoon</label>
            <input type="text" name="phone" id="phone" value="<?php echo $user['phone']?>">
        </div>
    </div>
    <div class="row">
        <div class="two columns">
            <label for="password">Wachtwoord <?php if ($user['isNew']):?> <i class="fas fa-asterisk"></i><?php endif?></label>
            <input type="password" name="password" id="password" <?php if ($user['isNew']) echo 'required'; ?>>
        </div>
        <div class="two columns">
            <label for="password2"> (controle)</label>
            <input type="password" name="password2" id="password2" <?php if ($user['isNew']) echo 'required'; ?>>
        </div>
    </div>
    <div class="row">
        <div class="three columns">
            <button type="submit" class="button-primary"><i class="far fa-save"></i>  Opslaan</button>
            <label></label>
            <label><small><i class="fas fa-asterisk"></i> = verplicht veld</small></label>
        </div>
        <div class="three columns">
        </div>
    </div>
</form>

<?php
//edit user from an administrator:
if (isset($_REQUEST['user'])) {
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
            <input type="text" name="phone" id="phone" value="<?php echo $user['phone']?>" maxlength="10">
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
<script>
window.onload = function() {
    // see https://www.w3schools.com/xml/xml_http.asp

    //get reference to DOM form field objects postalcode and housenumber:
    var postalcode = document.getElementById('postalcode');
    var housenumber = document.getElementById('housenumber');

    // listen for blur events (=focus removed from field)
    // and call function loadAddress() every time this happens:
    postalcode.addEventListener('blur', loadAddress);
    housenumber.addEventListener('blur', loadAddress);

    // callback function for blur events:
    function loadAddress() {
        //some very old browser do not have XMLHttpRequest, so be it ...
        if (typeof XMLHttpRequest != 'function') {
            return;
        }
        //only cal the API if bot fields have values:
        if (postalcode.value && housenumber.value) {
            // display the CSS spinner to indicate the page is doing something:
            document.getElementById('spinner').style.display = 'inherit';

            // initiate new asynchronous request:
            var xhttp = new XMLHttpRequest();

            // when request is ready, parse JSON API result:
            xhttp.onreadystatechange = function() {
                if (this.readyState == 4) {
                    if (this.status == 200) {
                        var response = JSON.parse(this.responseText);
                        //set value of form objects based on response from API:
                        if (response.street) {
                            document.getElementById('streetname').value  = response.street;
                        }
                        if (response.city) {
                            document.getElementById('place').value  = response.city;
                        }
                    }
                    // hide the CSS spinner
                    document.getElementById('spinner').style.display = 'none';
                }
            };

            // open the actual request and send it to the server:
            xhttp.open("GET", `postcode.php?postcode=${postalcode.value}&huisnummer=${housenumber.value}`, true);
            xhttp.send();
        }
    }
}
</script>

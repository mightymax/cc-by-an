<?php
if (!isset($_REQUEST['token'])) $app->redirect();

$user = $app->getUser($_REQUEST['token'], 'token');
if (!$user) {
    $app->setMessage('Ongeldig token.', 'error');
    $app->redirect();
}
if ($app->formIsPosted()) {
    if (!@$_POST['password'] || @$_POST['password'] != @$_POST['password2']) {
        $app->setMessage('Geen wachtwoord, of wachtwoord en controle wachtwoord komen niet overeen', 'warning');
        $app->redirect('resetpassword', '&token=' . $_REQUEST['token']);
    }
    $password = password_hash($_POST['password'],  PASSWORD_DEFAULT);
    $stmt = $app->getDbConnection()->prepare("UPDATE client SET password=:password, token='' WHERE id=:id");
    $stmt->bindParam(':password', $password);
    $stmt->bindParam(':id', $user['id']);
    if ($stmt->execute()) {
        $app->setMessage('Uw wachtwoord is aangepast, u kunt het nieuwe wachtwoord direct gebruiken om in te loggen.', 'success');
    } else {
        $app->setMessage('Uw wachtwoord is NIET aangepast door een systeemfout, probeert u het later nogmaals.', 'error');
    }
    $app->redirect();
}
?>
<form class="form" action="?page=resetpassword" method="POST">
    <div class="row">
        <div class="three columns">
            <?php echo $app->getCrfsToken() ?>
            <input type="hidden" name="token" value="<?php echo $user['token']?>">
            <label for="password">Wachtwoord <i class="fas fa-asterisk"></i></label>
            <input type="password" name="password" id="password" required>
        </div>
        <div class="three columns">
            <label for="password2"> (controle)</label>
            <input type="password" name="password2" id="password2" required>
        </div>
    </div>
    <div class="row">
        <div class="three columns">
            <label>&nbsp;</label>
            <button type="submit" class="button-primary"><i class="far fa-save"></i>  Opslaan</button>
            <label></label>
            <label><small><i class="fas fa-asterisk"></i> = verplicht veld</small></label>
        </div>
        <div class="three columns">
        </div>
    </div>
</form>

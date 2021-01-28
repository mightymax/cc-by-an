<?php    
if ($app->formIsPosted()){
    $app->sendContactform($_POST);
}
$user = $app->getAppUser();
?>

<h2>Klantenservice en contact</h2>
<article>
    <p class="lead">Wij helpen u graag verder!</p>
    <p>Wilt u meer informatie, heeft u specifieke wensen, of heeft u een klacht? Vul dan dit contact formulier in:</p>
</article>
<form class="form" action="?page=contact" method="POST">
    <div class="row">
        <div class="four columns">
            <?php echo $app->getCrfsToken() ?>
            <label for="name">Naam</label>
            <input type="text" id="name" name="name" required <?php if ($user) echo "value=\"{$user['name']}\""; ?>>
        </div>
    </div>
    <div class="row">
        <div class="four columns">
            <label for="email">Emailadres</label>
            <input type="email" id="email" name="email" required <?php if ($user) echo "value=\"{$user['email']}\""; ?>>
        </div>
    </div>
    <div class="row">
        <div class="eight columns">
            <label for="subject">Uw vraag/opmerking</label>
            <textarea id="subject" name="subject" style="height:200px" required></textarea>
        </div>
    </div>
    <div class="row">
        <div class="three columns">
            <button class="button-primary" type="submit"><i class="fas fa-paper-plane"></i><span> Verstuur</span></button>
        </div>
    </div>
</form>

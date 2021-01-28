<?php
if (!isset($_REQUEST['token'])) $app->redirect();
if (0!== strpos($_REQUEST['token'], 'ccba-')) {
    $app->setMessage('Ongeldig bevestigingstoken.', 'error');
    $app->redirect();
}
$user = $app->getUser($_REQUEST['token'], 'token');
if (!$user) {
    $app->setMessage('Ongeldig token.', 'error');
    $app->redirect();
}

$stmt = $app->getDbConnection()->prepare("UPDATE client SET token='' WHERE id=:id");
$stmt->bindParam(':id', $user['id']);
try {
    $stmt->execute();
    $app->setMessage('Uw e-mailadres is nu bevestigd, u kunt nu inloggen.', 'success');
    $app->redirect('inloggen', '&email=' . $user['email']);
} catch (PDOException $e) {
    $app->setMessage('Uw e-mailadres is NIET aangepast door een systeemfout, probeert u het later nogmaals.', 'error');
    $app->redirect();
}

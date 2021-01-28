<?php
$app->gateKeeper(True);
if (isset($_REQUEST['toggleAdmin'])) {
    $app->toggleUserAdmin($_REQUEST['toggleAdmin']);
    $app->redirect('users', '#user-' . $_REQUEST['toggleAdmin']);
}

if (isset($_REQUEST['delete'])) {
  $app->deleteUser($_REQUEST['delete']);
  $app->redirect('users');
}

$users = $app->getUsers();

?>
<h2>Beheer gebruikers</h2>
<table class="u-full-width">
  <thead>
    <tr>
      <th>#</th>
      <th>Naam</th>
      <th><i class="fas fa-envelope"></i></th>
      <th>Postcode</th>
      <th>Huisnr</th>
      <th>Plaats</th>
      <th></th>
      <th></th>
    </tr>
  </thead>
  <tbody>
    <?php foreach ($app->getUsers() as $i => $user) : ?> 
    <tr id="user-<?php echo $user['id']?>">
        <th scope="row"><?php echo $i+1 ?></th>
        <td><a href="?page=profiel&user=<?php echo $user['id']?>" title="Bewerk deze gebruiker"><?php echo $user['name']?></a></td>
        <td><a href="mailto:<?php echo $user['email']?>"><i class="fas fa-envelope" title="<?php echo $user['email']?>"></i></a></td>
        <td><?php echo $user['postalcode']?></td>
        <td><?php echo $user['housenumber']?></td>
        <td><?php echo $user['place']?></td>
        <td>
            <a href="?page=users&amp;toggleAdmin=<?php echo $user['id']?>" title="Geef/Ontneem deze gebruiker beheerdersrechten">
                <i class="<?php if (0===strpos($user['token'], 'ccba-')) echo 'unconfirmed'?> fas fa-user<?php echo $user['isadmin'] == 'Y' ? '-cog':'';?>"></i>
            </a>
        </td>
        <td>
            <a href="?page=users&amp;delete=<?php echo $user['id']?>" onclick="return confirm('Weet je zeker dat je deze gebruiker definitief wilt verwijderen?')" title="Verwijder deze gebruiker">
                <i class="fas fa-trash"></i>
            </a>
        </td>
    </tr>
    <?php endforeach ?>
  </tbody>
</table>

<?php
$app->gatekeeper(true);
$orders = $app->getOrders();
?>
<h2>Beheer orders</h2>
<?php if (!$orders): ?>
    <p class="message info"><i class="fas fa-info-circle"></i> Geen orders gevonden</p>
<?php return; endif; ?>
<table class="u-full-width">
  <thead>
    <tr>
      <th></th>
      <th>Datum</th>
      <th>Klant</th>
      <th><i class="fas fa-envelope"></i></th>
      <th>Plaats</th>
      <th class="num">Items</th>
      <th class="num">Bedrag</th>
      <th></th>
    </tr>
  </thead>
  <tbody>
    <?php foreach ($orders as $i => $order) : ?> 
    <tr id="order-<?php echo $order['id']?>">
        <td><span class="order-status order-status-<?php echo $order['status'] ?>"><?php echo $order['status'] ?></span></td>
        <td><?php echo date('d-m \'y', strtotime($order['date']))?></td>
        <td><a href="?page=profiel&user=<?php echo $order['client']?>" title="Bewerk deze gebruiker"><?php echo $order['name']?></a></td>
        <td><a href="mailto:<?php echo $order['email']?>"><i class="fas fa-envelope" title="<?php echo $order['email']?>"></i></a></td>
        <td><?php echo $order['place']?></td>
        <td class="num"><?php echo $order['num_items']?></td>
        <td class="num">€ <?php echo number_format($order['ammount']/100, 2, ',', '.')?></td>
        <td>
            <a href="?page=order&amp;order=<?php echo $order['id']?>" title="Bekijk/Bewerk deze order">
            <i class="fas fa-edit"></i>
            </a>
        </td>
    </tr>
    <?php endforeach ?>
  </tbody>
</table>

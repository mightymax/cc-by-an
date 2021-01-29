<?php
$app->gatekeeper(true);

$order_id = intval(@$_REQUEST['order']);
if (!$order_id) {
    $app->setMessage('Geen order opgegeven', 'warning');
    $app->redirect('orders');
}
$orderDetails = $app->getOrderDetails($order_id);
if (!$orderDetails) {
    $app->setMessage('Order niet gevonden', 'warning');
    $app->redirect('orders');
}
list($order, $client, $order_lines) = array_values($orderDetails);

if ($app->formIsPosted()) {
    if (isset($_POST['delete'])) {
        $app->deleteOrder($order['id']);
    } else {
        $app->setOrderStatus($order, @$_POST['status']);
    }
}

?>
<h2>Details van order <?php echo $order_id?></h2>
<p>
    <a href="?page=orders#order-<?php echo $order['id']?>">
        <button>
            <i class="fas fa-backward"></i>
            Terug naar orders
        </button>
    </a>
</p>
<div class="row">
    <div class="four columns">
        <h5>Ordergegevens</h5>
        <div class="row">
            <div class="three columns">
                <strong>Datum:</strong><br>
                <strong>Tijd:</strong><br>
                <strong>Bedrag:</strong>
                <strong>Status:</strong>
            </div>
            <div class="six columns">
                <?php echo date('d-m \'y', strtotime($order['date']))?><br>
                <?php echo date('H:i', strtotime($order['date']))?><br>
                € <?php echo number_format($order['ammount']/100, 2, ',', '.')?><br>
                <span class="order-status order-status-<?php echo $order['status']?>"><?php echo $order['status']?></span>
            </div>
        </div>
    </div>
    <div class="four columns">
        <h5>Klantgegevens</h5>
        <?php echo $client['name'] ;?><br>
        <?php echo $client['streetname'] ;?> <?php echo $client['housenumber'] ;?> <br>
        <?php echo $client['postalcode'] ;?> <?php echo $client['place'] ;?><br>
        <i class="fas fa-envelope"></i> <a href="mailto:<?php echo $client['email']?>"><?php echo $client['email'];?></a>
    </div>
</div>
<div class="row">
    <div class="eight columns">
        <form method="post" action="?">
            <?php echo $app->getCrfsToken() ?>
            <input type="hidden" name="page" value="order">
            <input type="hidden" name="order" value="<?php echo $order['id']?>">
            <strong for="status">Wijzig status:</strong>&nbsp;&nbsp;
            <select name="status" id="status">
                <?php foreach ($app->getOrderStatussen() as $status):?>
                    <option <?php if ($status==$order['status']) echo 'selected="selected"'?>><?php echo $status?></option>
                <?php endforeach?>
            </select>&nbsp;&nbsp;
            <button type="submit" class="button-primary"><i class="far fa-save"></i>  Opslaan</button>
            <button onclick="return confirm('Weet je zeker dat je deze order definitief wilt verwijderen?')" name="delete"><i class="fas fa-trash"></i>  Verwijder</button>
        </form>
    </div>
</div>
<h5>Bestelde producten:</h5>
<table class="u-full-width">
  <thead>
    <tr>
      <th></th>
      <th>Product</th>
      <th class="num">Aantal</th>
      <th class="num">Prijs</th>
      <th class="num">Bedrag</th>
    </tr>
  </thead>
  <tbody>
    <?php foreach($order_lines as $order_line):?>
    <tr>
        <td class="img">
            <a href="?page=product&amp;product=<?php echo $order_line['product']?>"><img src="images/products/small/<?php echo $order_line['product']?>.jpg"></a>
        </td>
        <td><?php echo $order_line['name']?></td>
        <td class="num">
            <?php echo $order_line['ammount']?>
        </td>
        <td class="num">€ <?php echo number_format($order_line['price']/100, 2, ',', '.')?></td>
        <td class="num">€ <?php echo number_format($order_line['ammount'] * $order_line['price'], 2, ',', '.')?></td>
    </tr>
    <?php endforeach ?>
  </tbody>
</table>
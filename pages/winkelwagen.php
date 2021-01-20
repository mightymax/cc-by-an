<?php 
$checkout = isset($_REQUEST['checkout']) && $_REQUEST['checkout'] ? (int)$_REQUEST['checkout'] : false;
if (isset($_REQUEST['shoppingCartMin'])) {
    $app->shoppingCartMin(intval($_REQUEST['shoppingCartMin']));
} 

if (isset($_REQUEST['shoppingCartPlus'])) {
    $app->shoppingCartPlus(intval($_REQUEST['shoppingCartPlus']));
} 

if (isset($_REQUEST['shoppingCartDelete'])) {
    $app->shoppingCartDelete(intval($_REQUEST['shoppingCartDelete']));
}  

?>
<?php if (!$app->countShoppingCart()) :?>
    <p class="message info"><i class="fas fa-info"></i> Uw winkelwagen is momenteel leeg.</p>
<?php return; endif; 
$num_items_total = 0;
$total_sum = 0;
?>
<?php if ($checkout) : 
    $user = $app->getAppUser();
    if (!$user) {
        $app->setMessage('Om af te rekenen moet u eerst inloggen of een account aanmaken', 'info');
        $app->redirect('inloggen');
    }
    if ($checkout == 2) {
        $app->checkoutShoppingCart();
    }
?>
<p>Kijk uw uw winkelwagen hieronder nog een keer goed na. Nadat u onderstaande order heeft bevestigd is uw bestelling compleet.
U ontvangt van ons een factuur op uw emailadres <em><?php echo $user['email']?></em>, zodra u die betaalt heeft, sturen wij uw order naar het onderstaande adres:</p>
<p>
    <blockquote>
    <?php echo $user['name'] ;?><br>
    Hoogeweg 40-B <br>
    1851 PJ Heiloo
    </blockquote>
</p>
<p>Als bovenstaand adres of e-mailadres onjuist is, pas dan eerst <a href="?page=profiel">uw profiel</a> aan.</p>
<p>
    <a href="?page=winkelwagen&checkout=2">
        <button class="button-primary">
            <i class="fas fa-check-double"></i>
            Ik ga akkoord met uw voorwaarden en wil onderstaande bestelling definitief plaatsen
        </button>
    </a>
</p>
<?php endif?>
<table class="u-full-width">
  <thead>
    <tr>
      <th></th>
      <th>Product</th>
      <th class="num">Aantal</th>
      <th class="num">Prijs</th>
      <th class="num">Bedrag</th>
      <th></th>
    </tr>
  </thead>
  <tbody>
    <?php foreach ($app->getShoppingCart() as $product_id => $num_items) :
        $product = $app->getProduct($product_id);
        $num_items_total += $num_items;
        $sum = $num_items * $product['price']/100;
        $total_sum += $sum;
    ?> 
    <tr>
        <td class="img">
            <a href="?page=product&amp;product=<?php echo $product_id?>"><img src="images/products/small/<?php echo $product_id?>.jpg"></a>
        </td>
        <td><?php echo $product['name']?></td>
        <td class="num">
        <?php if (!$checkout) :?>
            <a href="?page=winkelwagen&shoppingCartMin=<?php echo $product_id?>" class="button">-</a>
            <a href="" class="button num_items" onclick="return false;"><?php echo $num_items?></a>
            <a href="?page=winkelwagen&shoppingCartPlus=<?php echo $product_id?>" class="button">+</a>
        <?php else: ?>
            <?php echo $num_items?>
        <?php endif?>
        </td>
        <td class="num">€ <?php echo number_format($product['price']/100, 2, ',', '.')?></td>
        <td class="num">€ <?php echo number_format($sum, 2, ',', '.')?></td>
        <td>
            <?php if (!$checkout) :?>
            <a href="?page=winkelwagen&shoppingCartDelete=<?php echo $product_id?>" class="button"><i class="fas fa-trash"></i></a>
            <?php endif?>
        </td>
    </tr>
    <?php endforeach ?>
  </tbody>
  <tfoot>
    <tr>
        <th></th>
        <th class="num">Totaal:</th>
        <th class="num"?><?php echo $num_items_total?></th>
        <th></th>
        <th class="num">€ <?php echo number_format($total_sum, 2, ',', '.')?></th>
        <td>
        <?php if (!$checkout) :?>
            <?php if ($user): ?>
                <a href="?page=inloggen" class="button button-primary"><i class="fas fa-cash-register"></i>Aanmelden om af te rekenen</a>
            <?php else: ?>
                <a href="?page=winkelwagen&amp;checkout=1" class="button button-primary"><i class="fas fa-cash-register"></i> Afrekenen</a>
            <?php endif?>
        <?php endif?> 
        </td>
    </tr>
  </tfoot>
</table>

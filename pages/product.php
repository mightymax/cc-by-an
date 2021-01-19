<style>
img {
  width: 270px;
  border: 1px solid black;
} 
</style>

<?php
if (!isset($_REQUEST['product'])) {
    // doe iets (want de URL is niet wat we verwachten), bv redirect naar categorie pagina
    // maar nu even iets eenvoudigs:
    echo "no product parameter in  Url";
    return;
}
$id = intval($_REQUEST['product']);
$product = $app->getProduct($id);
if ($product == false) {
    // doe iets (want het product dat via de URL wordt gevraagd bestaat niet), bv redirect naar categorie pagina
    // maar nu even iets eenvoudigs:
    echo "product {$id} not found";
    return;
}
?>
<div class="row">
    <div class="five columns"><img src="./images/products/large/<?php echo $product['id']?>.jpg" alt="<?php echo $product['name']?>" style="width:400px;height:400px;"></div>
    <div class="seven columns">
    <p><?php echo $product['description']?></p><br><br><br><br>
    <p class="price">
        <span class="currency">€</span>
        <span class="integers"><?php echo intval($product['price']/100)?></span>
        <span class="decimals">,<?php echo str_pad(fmod($product['price'], 100), 2, '0')?></span>
    </p>
    </div>
  </div>



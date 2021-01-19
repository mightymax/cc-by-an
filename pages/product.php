<style>
.product {
  width: 400px;
  height: 400px;
  overflow: hidden;
  margin: 0 auto;
  border: 1px solid black;
} 

.product img {
    width: 100%;
    transition: 0.5s all ease-in-out;
}

.product:hover img {
    transform: scale(1.5);
}

p {
    font-size: 20px;
}

.price_product .currency {
    font-size: 20px;
}

.price_product .integers {
    font-size: 30px;
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
    <div class="five columns">
        <div class="product">
        <img src="./images/products/large/<?php echo $product['id']?>.jpg" alt="<?php echo $product['name']?>" style="width:100%;">
        </div>
    </div>
    <div class="seven columns">
        <div class="p">
        <p><?php echo $product['description']?></p><br>
        </div>
    <p class="price_product">
        <span class="currency">€</span>
        <span class="integers"><?php echo intval($product['price']/100)?></span>
        <span class="currency">,<?php echo str_pad(fmod($product['price'], 100), 2, '0')?></span>
    </p>
    <button type="button"><i class="fas fa-cart-plus"></i> In winkelwagen</button>
    </div>
</div>
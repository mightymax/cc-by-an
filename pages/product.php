<?php 
if (isset($_REQUEST['inShoppingCart'])) {
    $app->inShoppingCart(intval($_REQUEST['inShoppingCart']));
} 
?>
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
<h2><?php echo $product['name']?></h2>
<article class="product">
<div class="row">
    <div class="five columns">
        <img src="./images/products/large/<?php echo $product['id']?>.jpg" alt="<?php echo $product['name']?>">
    </div>
    <div class="seven columns product-metadata">
        <div class="description"><br>
        <?php echo nl2br($product['description'])?>
        </div>
    <p class="price_product">
        <span class="currency">€</span>
        <span class="integers"><?php echo intval($product['price']/100)?></span>
        <span class="currency">,<?php echo str_pad(fmod($product['price'], 100), 2, '0')?></span>
    </p>
    <a href="?page=producten&amp;category=<?php echo @$_REQUEST['category']?>&inShoppingCart=<?php echo $product['id']?>#product-<?php echo $product['id']?>"><button type="button"><i class="fas fa-cart-plus"></i> <span>In winkelwagen</span></button></a>
        <?php if ($app->getAppUser() && $app->getAppUser()['isAdmin']):?>
            <a href="?page=addproduct&amp;product=<?php echo $product['id']?>"><button type="button"><i class="fas fa-edit"></i><span>Product bewerken</span></button></a>
        <?php endif ?>
    </div>
</div>
</article>
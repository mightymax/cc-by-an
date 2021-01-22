<?php 
if (isset($_REQUEST['inShoppingCart'])) {
    $pap->inShoppingCart(intval($_REQUEST['inShoppingCart']));
} 
?>
<div id="categories">
<?php foreach($app->getCategories() as $category): 
    $primaryClass = (@$_REQUEST['category'] == $category['id']) ? "button-primary" : "";
?>
    <a class="button <?php echo $primaryClass?> " href="?page=producten&amp;category=<?php echo $category['id']?>"><?php echo $category['name']?></a>
<?php endforeach?>
</div>
<h2>Producten</h2>

<?php foreach ($app->getProducts(@$_REQUEST['category']) as $i => $row) : ?>
    <?php if ($i % 3 == 0):?>
        <div class="row products">
    <?php endif?>
            <article class="four columns product" id="product-<?php echo $row['id']?>">
                <a href="?page=product&amp;category=<?php echo @$_REQUEST['category']?>&amp;product=<?php echo $row['id']?>">
                    <img src="./images/products/small/<?php echo $row['id']?>.jpg" alt="<?php echo $row['name']?>" />
                    <div class="body">
                        <h4><?php echo $row['name']?></h4>
                        <p class="price">
                            <span class="currency">€</span>
                            <span class="integers"><?php echo intval($row['price']/100)?></span>
                            <span class="decimals">,<?php echo str_pad(fmod($row['price'], 100), 2, '0')?></span>
                        </p>
                        <?php if ($app->getAppUser() || true): ?>
                        <a href="?page=producten&amp;category=<?php echo @$_REQUEST['category']?>&inShoppingCart=<?php echo $row['id']?>#product-<?php echo $row['id']?>"><button type="button"><i class="fas fa-cart-plus"></i> In winkelwagen</button></a>
                        <?php else: ?>
                        <a href="?page=inloggen"><button type="button"><i class="fas fa-sign-in-alt"></i> Log in om te bestellen</button></a>
                        <?php endif ?>
                    </div>
                </a>
            </article>
    <?php if ($i % 3 == 2):?></div><?php endif?>
<?php endforeach?>
<?php if ($i % 3 < (3-1)):?></div><?php endif?>

<?php if($category = $dbh->getCategory()): ?>
<div class="container text-center">
    <h3><small class="text-muted"><?php echo $category['name'];?></small></h3>
</div>
<?php endif?>

<?php foreach ($dbh->getProducts() as $i => $row) : ?>
    <?php if ($i % 3 == 0):?>
        <div class="row products">
    <?php endif?>
            <article class="four columns product">
                <h4><?php echo $row['name']?></h4>
                <p class="price">
                    <span class="integers">€ <?php echo intval($row['price']/100)?></span>
                    <span class="decimals">,<?php echo str_pad(fmod($row['price'], 100), 2, '0')?></span>
                </p>
                <p class="description"><?php echo $row['description']?></p>
                <button type="button"><i class="fas fa-cart-plus"></i> In winkelwagen</button>
            </article>
    <?php if ($i % 3 == 2):?></div><?php endif?>
<?php endforeach?>
<?php if ($i % 3 < (3-1)):?></div><?php endif?>

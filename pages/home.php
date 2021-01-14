<div class="row products categories">
<?php foreach ($app->getTeasers() as $i => $row) : ?>
        <article class="four columns product">
            <a href="?page=producten&amp;category=<?php echo $row['id']?>">
                <img src="./images/products/small/<?php echo $row['product']['id']?>.jpg" alt="<?php echo $row['product']['name']?>" />
                <h4><?php echo $row['name']?></h4>
            </a>
        </article>
<?php endforeach?>
</div>
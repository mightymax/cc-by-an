<h2>Welkom in onze webshop!</h2>
<p class="lead">U kunt hier terecht voor het unieke haakwerk van An.
    Klik op een categorie om onze producten te bekijken:</p>
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
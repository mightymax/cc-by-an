<div class="row products teasers">
<?php foreach ($app->getTeasers() as $i => $row) : ?>
    <article class="four columns product">
        <h4><a href="?page=producten&amp;category=<?php echo $row['id']?>"><?php echo $row['name']?></a></h4>
        <!-- <h5><?php echo $row['product']['name']?></h5> -->
        <a href="?page=producten&amp;category=<?php echo $row['id']?>">
            <img src="./images/<?php echo $row['product']['id']?>.jpg" alt="<?php echo $row['product']['name']?>" />
        </a>
    </article>
<?php endforeach?>
</div>
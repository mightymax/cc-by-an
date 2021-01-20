<?php 
$app->gateKeeper(True);
$categories = $app->getCategories();

if ($app->formIsPosted()){
    $app->saveProduct($_POST);
}

if (isset($_REQUEST['product'])) {
    $product = $app->getProduct($_REQUEST['product']);
}
?>

<form method="POST" action="?page=admin">
    <div class="row">
        <div class="four columns">
            <?php echo $app->getCrfsToken() ?>
            <input type="hidden" name="id" value="<?php echo @$product['id']?>">
            <label for="name">Product naam</label>
            <input type="text" name="name" id="name" value="<?php echo @$product['name']?>">
        </div>
        <div class="four columns">
            <label for="category">Categorie</label>
            <select name="category" id="category">
            <?php foreach($categories as $category):?>  
            <option value="<?php echo $category['id']?>" <?php if ($category['id']==@$product['category']) echo 'selected="selected"' ?>><?php echo $category['name']; ?></option>
            <?php endforeach ?>
            </select>
        </div>
    </div>
    <div class="row">
        <div class="four columns">
            <label for="description">Beschrijving</label>
            <textarea name="description" id="description"><?php echo @$product['description']?></textarea>
        </div>
        <div class="four columns">
            <label for="productPhoto">Upload foto</label>
            <input type="file" id="myFile" name="filename">
        </div>
    </div>
    <div class="row">
        <div class="four columns">
            <label for="price">Prijs (in centen)</label>
            <input type="number" name="price" id="price" value="<?php echo @$product['price']?>">
        </div>
    </div>
    <div class="row">
        <div class="three columns">  
            <label></label>
            <button class="button-primary" type="submit"><i class="far fa-save"></i> Opslaan</button> 
        </div>
    </div>
</form>
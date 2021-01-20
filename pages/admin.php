<?php 
$app->gateKeeper(True);
$categories = $app->getCategories();
?>

<?php
function saveProduct(Array $data, $app){

    /* Checks if input data is valid */

    if (isset($data['name']) && $data['name']) {
        $storeData['name'] = $data['name'];
    }
    if (isset($data['description']) && $data['description']) {
        $storeData['description'] = $data['description'];
    }
    if (isset($data['category']) && $data['category']) {
        $category = $app->getCategory($data['category']);
        if (!$category) {
            $app->setMessage('Dit is geen bestaande categorie', 'warning');
            $app->redirect('admin');
        }
        $storeData['category'] = $data['category'];
    }
    if (isset($data['price']) && intval($data['price'])){
        $storeData['price'] = $data['price'];
    } else {
        $app->setMessage('Voer een getal in bij prijs', 'warning');
        $app->redirect('admin');
    }
    
    /* Submits newly created product to database */

    if (isset($data['id']) && intval($data['id'])) {
        $product = $app->getProduct($data['id']);
        if (!$product) {
            $app->setMessage('Product niet gevonden', 'error');
            $app->redirect('admin');
        }
        $sql = "UPDATE product SET name=:name, price=:price, description=:description, category=:category WHERE id=:id";
    }   else {
        $product = False;
        $sql = "INSERT INTO product SET name=:name, price=:price, description=:description, category=:category";
    }
    $stmt=$app->getDbconnection()->prepare($sql);
    $stmt->bindParam(':name', $storeData['name']);
    $stmt->bindParam(':price', $storeData['price'],PDO::PARAM_INT);
    $stmt->bindParam(':description', $storeData['description']);
    $stmt->bindParam(':category', $storeData['category'],PDO::PARAM_INT);
    if ($product) {
        $stmt->bindParam(':id', $product['id'],PDO::PARAM_INT);
    }
    if ($stmt->execute()) {
        $app->setMessage('Product is met succes opgeslagen', 'success');
    } else {
        $app->setMessage('Systeem fout: product is niet opgeslagen', 'error');
    }
    $app->redirect('admin');   
}
?>

<?php
if ($app->formIsPosted()){
    saveProduct($_POST, $app);
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
        <div class="two columns">  
            <label></label>
            <input class="button-primary" type="submit" value="Submit">
        </div>
    </div>
</form>
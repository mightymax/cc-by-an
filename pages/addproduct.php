<?php 
$app->gateKeeper(True);
$categories = $app->getCategories();



if ($app->formIsPosted() && isset($_POST['deleteproduct'])):
    $app->deleteProduct($_POST);
elseif ($app->formIsPosted()):

    //make instance of our Image helper class:
    include_once __DIR__ . '/../Images.php';
    $imageTools = new WebshopAppImages($app, 'img');
    //try to save product. When this is a new product, check if an image is uploaded by providing an extra argument:
    $product = $app->editProduct($_POST, $imageTools->isUploadFile());
    if (!$product) {
        $app->setMessage('Het is niet gelukt om het gewijzigde product op te slaan');
        $app->redirect('addproduct');
    } else {

        if ($imageTools->isUploadFile()) {
            
            $imageTools->checkUploadDirectories();

            // did the upload go well?
            $imageTools->checkUploadedFile();

            // is this an image? 
            $imageTools->checkIfUploadedFileIsAnImage();

            // At this stage, we know we have a valid upload that is a JPEG. 
            // Now we will do some image manipulation using GD 
            $large_image = $imageTools->createCroppedAndResizedImage();

            //create thumbnail:
            $small_image = $imageTools->createThumbnailFromLargeImage($large_image);

            $imageTools->saveImage($large_image, $product, 'large');
            $imageTools->saveImage($small_image, $product, 'small');
            $app->setMessage('De gewijzigde productgegevens en afbeelding zijn opgeslagen', 'success');
        } else {
            $app->setMessage('De gewijzigde productgegevens zijn opgeslagen', 'success');
        }
        $app->redirect('addproduct', '&product=' . $product['id']);
    }

endif;

if (isset($_REQUEST['product'])) {
    $product = $app->getProduct($_REQUEST['product']);
} else {
    $product = false;
}

$MAX_FILE_SIZE = min($app->get_ini_size('post_max_size'), $app->get_ini_size('upload_max_filesize'));

?>

<form method="POST" action="?page=addproduct" enctype="multipart/form-data">
    <input type="hidden" name="MAX_FILE_SIZE" value="<?php echo $MAX_FILE_SIZE?>" />
    <div class="row">

        <div class="four columns">
            <?php echo $app->getCrfsToken() ?>
            <input type="hidden" name="id" value="<?php echo @$product['id']?>">
            <p>
                <label for="name">Product naam</label>
                <input type="text" name="name" id="name" required value="<?php echo $app->formValue('name', @$product['name']) ?>" placeholder="Naam van het product">
            </p>
            <p>
                <label for="price">Prijs (in centen)</label>
                <input type="number" name="price" id="price" required value="<?php echo $app->formValue('price', @$product['price']) ?>" placeholder="Prijs in centen: €10 => 1000">
            </p>
            <p>
                <label for="category">Categorie</label>
                <select name="category" id="category" required >
                    <option value=""> -- Kies een categorie --</option>
                <?php foreach($categories as $category):?>  
                    <option value="<?php echo $category['id']?>" <?php if ($category['id']==$app->formValue('category', @$product['category'])) echo 'selected="selected"' ?>><?php echo $category['name']; ?></option>
                <?php endforeach ?>
                </select>
            </p>

        </div>
        <div class="four columns">
            <label for="img"><?php echo $product ? 'Vervang': 'Upload'?> foto <small>(max. <?php echo ini_get('upload_max_filesize')?>)</small></label>
            <input type="file" id="img" name="img">
            <?php if ($product):?>
            <img width="150" style="padding-top:10px;" src="images/products/small/<?php echo $product['id']?>.jpg?<?php echo time()?>" alt="">
            <?php endif?>
        </div>
    </div>
    <div class="row">
        <div class="eight columns">
            <label for="description">Beschrijving</label>
            <textarea name="description" id="description" style="height:100px" placeholder="Voer een beschrijving van het artikel in ..." required ><?php echo $app->formValue('description', @$product['description']) ?></textarea>
        </div>
    </div>
    <div class="row">
        <div class="eight columns">  
            <label></label>
            <button class="button-primary" type="submit"><i class="far fa-save"></i><span> Opslaan</span></button>
            <?php if ($product):?>
                &nbsp;<button type="submit" onclick="return confirm('Weet je zeker dat je dit product en de afbeelding definitief wilt verwijderen?')" name="deleteproduct" value="deleteproduct"><i class="fas fa-trash-alt"></i><span> Verwijderen</span></button>
            <?php endif ?> 
        </div>
    </div>
</form>

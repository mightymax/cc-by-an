<?php 
$app->gateKeeper(True);
$categories = $app->getCategories();

if ($app->formIsPosted() && (isset($_POST['deleteproduct']))):
    $app->deleteProduct($_POST);
elseif ($app->formIsPosted()):
    $app->editProduct($_POST);
endif;

if (isset($_REQUEST['product'])) {
    $product = $app->getProduct($_REQUEST['product']);
}

function uploadImg(Array $data) {
    $target_dir = "webtech-webshop/images/products/small";
    $target_file = $target_dir . basename($_FILES["productimg"]['name']);
    $uploadOk = 1;
    $imageFileType = strtolower(pathinfo($target_file,PATHINFO_EXTENSION));

    // Check if image file is an actual image
    if (isset($_POST['submit'])) {
        $check = getimagesize($_FILES["productimg"]["tmp_name"]);
        if($check !== false) {
            echo "File is an image - " . $check["mime"] . ".";
            $uploadOk = 1;
          } else {
            echo "File is not an image.";
            $uploadOk = 0;
          }
    }
    //Check if file already exists
    if (file_exists($target_file)) {
        echo "Sorry, this file already exists.";
        $uploadOk = 0;
    }
    // Check file size
    if ($_FILES["productimg"]["size"] > 100) { 
        echo "Sorry, your file is too large.";
        $uploadOk = 0;
    }

    //File dimensions must by 300x300

    // Allow certain file formats
    if($imageFileType != "jpg" && $imageFileType != "jpeg") {
        echo "Sorry, only JPG & JPEG are allowed.";
        $uploadOk = 0;
    } 
    // Check if $uploadOk is set to 0 by an error
    if ($uploadOk == 0) {
        echo "Sorry, your file was not uploaded.";
    // if everything is ok, try to upload file
    } else {
    if (move_uploaded_file($_FILES["productimg"]["tmp_name"], $target_file)) {
      echo "The file ". htmlspecialchars( basename( $_FILES["fileToUpload"]["name"])). " has been uploaded.";
    } else {
      echo "Sorry, there was an error uploading your file.";
    }
  }  
}

?>


<br>
<form method="POST" action="?page=addproduct" enctype="multipart/form-data">
    <div class="row">
        <div class="four columns">
            <?php echo $app->getCrfsToken() ?>
            <input type="hidden" name="id" value="<?php echo @$product['id']?>">
            <label for="name">Product naam</label>
            <input type="text" name="name" id="name" value="<?php echo @$product['name']?>">
        </div>
        <div class="four columns">
            <label for="productImg">Upload foto</label>
            <input type="file" id="productimg" name="productimg">
        </div>
    </div>
    <div class="row">
        <div class="four columns">
            <label for="price">Prijs (in centen)</label>
            <input type="number" name="price" id="price" value="<?php echo @$product['price']?>">
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
        <div class="eight columns">
            <label for="description">Beschrijving</label>
            <textarea name="description" id="description" style="height:100px"><?php echo @$product['description']?></textarea>
        </div>
    </div>
    <div class="row">
        <div class="four columns">  
            <label></label>
            <button class="button-primary" type="submit"><i class="far fa-save"></i><span> Opslaan</span></button><?php if (isset($_REQUEST['product'])):?><button class="button-primary" type="submit" name="deleteproduct" value="deleteproduct"><i class="fas fa-trash-alt"></i><span> Verwijderen</span></button><?php endif ?> 
        </div>
    </div>
</form>
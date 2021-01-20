<!--    INSERT INTO product (name, price, description, category)
        VALUES (:name, :price,'','') -->

<?php 
function saveProduct():

?>

<form method="POST" action="?page=admin">
    <div class="row">
        <div class="four columns">
            <label for="name">Product naam</label>
            <input type="text" name="name" id="name">
        </div>
        <div class="four columns">
            <label for="category">Categorie</label>
            <select name="category" id="category">
            <option value="Option 1">Mode & accessoires</option>
            <option value="Option 2">Baby & kind</option>
            <option value="Option 3">Divers</option>
            </select>
        </div>
    </div>
    <div class="row">
        <div class="four columns">
            <label for="description">Beschrijving</label>
            <textarea name="description" id="description"></textarea>
        </div>
        <div class="four columns">
            <label for="productPhoto">Upload foto</label>
            <input type="file" id="myFile" name="filename">
        </div>
    </div>
    <div class="row">
        <div class="four columns">
            <label for="price">Prijs (in centen)</label>
            <input type="number" name="price" id="price">
        </div>
    </div>
    <div class="row">
        <div class="two columns">  
            <label></label>
            <input class="button-primary" type="submit" value="Submit">
        </div>
    </div>
</form>
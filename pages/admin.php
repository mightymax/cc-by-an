<!--    INSERT INTO product (name, price, description, category)
        VALUES ('','','','') -->

<form>
  <div class="row">
    <div class="six columns">
        <label for="productName">Product naam</label>
        <input class="u-full-width" type="text" id="productName"><br><br>
        <label for="productDescription">Beschrijving</label>
        <textarea class="u-full-width" id="productDescription"></textarea><br><br>
        <label for="productPrice">Prijs (in centen)</label>
        <input class="u-full-width" type="text" id="productPrice"><br><br>
        <input class="button-primary" type="submit" value="Submit">
    </div>
    <div class="six columns">
      <label for="productCategory">Categorie</label>
      <select class="u-full-width" id="productCategory">
        <option value="Option 1">Mode & accessoires</option>
        <option value="Option 2">Baby & kind</option>
        <option value="Option 3">Divers</option>
      </select><br><br>
      <label for="productPhoto">Upload foto</label>
      <input type="file" id="myFile" name="filename">
    </div>
  </div>
</form>
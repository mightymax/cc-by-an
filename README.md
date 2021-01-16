# WebTech Webshop

Building a Webshop for the course WebTech

## How to create the product page:
1. Write a SQL `SELECT … FROM … INNER JOIN … ON … WHERE id = …` statement. 
Test this SQL statement in PhpMyAdmin before [writing any code](https://www.w3schools.com/php/php_mysql_select.asp)
2. Write a [function](https://www.w3schools.com/php/php_functions.asp) with the signature `getProduct($id) {…}`
that uses the SQL query to return an [Associative Array](https://www.w3schools.com/php/php_arrays_associative.asp])of product properties from the MySQL table. 
Some tips:
    - create this function directly in this page, refactor later to include it in db.php
    - Make sure to read the "Select Data With PDO (+ Prepared Statements)" section of the [W3Schools tutorial](https://www.w3schools.com/php/php_mysql_select.asp) so you use a statement and not the raw SQL query.
    - to execute a query in your function you need access to the [PHP PDO MySQL object](https://www.w3schools.com/php/php_mysql_intro.asp) that handles the database requests. This object is encapsulated in the `App` class stored in `$app`. It can be retrieved by calling `$app->getDbConnection()`. So whenever in the W3Schools example they use the example variable `$conn` you sould use `$app->getDbConnection`.
    e.g. `$result = $conn->query($sql);` becomes `$result = $app->getDbConnection()->query($sql);`
    - Keep in mind that `$app` is not in scope of your function, so you should either pass this to your function, or you should make it a global variable. [Make sure you understand scoping](https://www.w3schools.com/php/php_variables_scope.asp), it is very important!
3. Whenever this page is loaded in the browser, test if the [superglobal](https://www.w3schools.com/php/php_superglobals.asp) `$REQUEST['product']` [is set](https://www.w3schools.com/php/func_var_isset.asp) and not empty. Make sure to [cast it to an INT](https://www.w3schools.com/php/func_var_intval.asp) ([never trust user input](https://dev.to/_garybell/never-trust-user-input-4ff1) !!) before passing this id to your database query function.
4. Call your function `getProduct($id) {}` with the value from the superglobal (casted to an int!) and store the result in a variable named `$product`. You can/must skip this step if no ID is set, or ID is empty, or 0.
5. If there is no ID, or the value of the `$product` var is `FALSE` (=no result from database), then redirect user to the `?page=producten` page. Make sure to set a message to inform the user that the requested product does not exists. 
**Tip:** use the `$app->setMessage($msg, $category = 'info') {…}` and `$app->redirect($page = 'home') {…}` methods, example usages can be found in the login page.
5. Use the values stored in the associative array `$product` to create a nice looking responsive product page.  You must include the photo of the product (tip: the `src` attribute of the `<img>` tag should point to `./images/products/large/<?php echo $product['id'] ?>.jpg`). Use the `<div class="row">` and `<div class="[number] columns">` HTML elements to create a [grid-like structure](http://getskeleton.com/#grid).
6. Refactor the `getProduct($id) {…}` so that it is part of the main App class `WebshopApp` in `db.php`. When refactoring, make sure you get rid of the global reference to the `$app` variable, use `$this` instead.
7. Make sure to test your product page with ligitimate URL's and all sorts of URL's that should be handled gracely without showing error pages on screen.
Some examples:
    - [?page=product&product=](?page=product&product=)
    - [?page=product&product=2](?page=product&product=2)
    - [?page=product&product=6666666666](?page=product&product=6666666666)
    - [?page=product&product=ABCDEF](?page=product&product=ABCDEF)
    - [?page=product&product=1 OR 1=1](?page=product&product=1%20OR%201=1)
    - [?page=product&product[]=1&product[]=2](?page=product&product[]=1&product[]=2)

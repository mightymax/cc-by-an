<h3>Todo for product page:</h3>
<ol>
    <li>Write a SQL <code>SELECT … FROM … JOIN … ON … WHERE id = …</code> statement. 
        Test this SQL statement in PhpMyAdmin before <a href="https://www.w3schools.com/php/php_mysql_select.asp">writing any code</a>.</li>
    <li>Write a <a href="https://www.w3schools.com/php/php_functions.asp">function</a> with the signature <code>getProduct($id) {…}</code> 
        that uses the SQL query to return an <a href="https://www.w3schools.com/php/php_arrays_associative.asp">Associative Array</a> 
        of product properties from the MySQL table. 
    <br>Some tips:
        <ul>
            <li>create this function directly in this page, refactor later to include it in db.php</li>
            <li>Make sure to read the "Select Data With PDO (+ Prepared Statements)" section of the 
                <a href="https://www.w3schools.com/php/php_mysql_select.asp">W3Schools tutorial</a> so you use a statement and not the raw SQL query.</li>
            <li>to execute a query in your function you need access to the 
                <a href="https://www.w3schools.com/php/php_mysql_intro.asp">PHP PDO MySQL object</a> that handles the database requests. 
                This object is stored in the variable <code>$app</code>. So whenever in the W3Schools example they use the example variable <code>$conn</code>
                you sould use <code>$app</code>.</li>
            <li>Keep in mind that <code>$app</code> is not in scope of your function, 
                so you should either pass this to your function, or you should make it a global variable.
                <a href="https://www.w3schools.com/php/php_variables_scope.asp">Make sure you understand scoping</a>, it is very important!
            </li>
        </ul>
    </li>
    <li>Whenever this page is loaded in the browser, test if the <a href="https://www.w3schools.com/php/php_superglobals.asp">superglobal</a> 
        <code>$REQUEST['product']</code> <a href="https://www.w3schools.com/php/func_var_isset.asp">is set</a> and not empty. 
        Make sure to <a href="https://www.w3schools.com/php/func_var_intval.asp">cast it to an INT</a> (<a href="https://dev.to/_garybell/never-trust-user-input-4ff1">never trust user input</a>!!) 
        before passing this id to your database query function.
    </li>
    <li>Call your function <code>getProduct($id) {}</code> with the value from the superglobal (casted to an int!) and store the result in a variable named <code>$product</code>.
        You can/must skip this step if no ID is set, or ID is empty, or 0.
    </li>
    <li>If there is no ID, or the value of the <code>$product</code> var is FALSE (=no result from database), 
    then redirect user to the <code>?page=producten</code> page. Make sure to set a message to inform the user that the requested product does not exists. 
    <strong>Tip:</strong> use the <code>$app->setMessage($msg, $category = 'info') {…}</code> and <code>$app->redirect($page = 'home') {…}</code> methods, 
        example usages can be found in the login page.</li>
    </li>
    <li>Use the values stored in the associative array <code>$product</code> to create a nice looking responsive product page. 
        You must include the photo of the product (tip: the <code>src</code> attribute of the <code>&lt;img&gt;</code> tag should point to <code>./images/products/large/&lt;?php echo $product['id'] ?&gt;.jpg</code>)
        Use the <code>&lt;div class="row"&gt;</code> and <code>&lt;div class="[number] columns"&gt;</code> HTML elements to create a <a href="http://getskeleton.com/#grid">grid-like structure</a>.
    </li>
    <li>Refactor the <code>getProduct($id) {…}</code> so that it is part of the main App class <code>WebshopDB</code> in <code>db.php</code>. 
        When refactoring, make sure you get rid of the global reference to the <code>$app</code> variable, use <code>$this</code> instead.
    </li>
    <li>Make sure to test your product page with ligitimate URL's and all sorts of URL's that should be handled gracely without showing error pages on screen.
        <br>Some examples:
        <ul>
            <li><a href="?page=product&amp;product=">?page=product&amp;product=</a></li>
            <li><a href="?page=product&amp;product=2">?page=product&amp;product=2</a></li>
            <li><a href="?page=product&amp;product=6666666666">?page=product&amp;product=6666666666</a></li>
            <li><a href="?page=product&amp;product=ABCDEF">?page=product&amp;product=ABCDEF</a></li>
            <li><a href="?page=product&amp;product=1 OR 1=1">?page=product&amp;product=1 OR 1=1</a></li>
            <li><a href="?page=product&amp;product[]=1&amp;product[]=2">?page=product&amp;product[]=1&amp;product[]=2</a></li>
        </ul>
    </li>
</ol>
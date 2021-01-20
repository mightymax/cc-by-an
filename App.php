<?php
// require_once './vendor/autoload.php';

/**
 * This is our main Class for the Webshop App
 * It holds the PHP PDO class for connecting to our MySQL DB
 * By adding functionality to this class, we keep our page/modules relatively clean
 * which makes it easier to maintain code
 * @see https://www.w3schools.com/php/php_oop_inheritance.asp
*/
class WebshopApp
{
    /**
     * @var $conn 
     */
    protected $conn;

    function __construct($dsn, $user, $password)
    {
        try {
            /**
             * We use PHP PDO to connect to database
             * @see https://www.w3schools.com/php/php_mysql_connect.asp
             * @see https://www.php.net/manual/en/book.pdo.php
             */
            $this->conn = new PDO($dsn, $user, $password);
        } catch (PDOException $e) {
            // if the DB connection fails, we can not do anything else, so we quit ...
            echo 'Connection failed: ' . $e->getMessage();
            exit;
        }
    }

    /**
     * Since the reference to our db connection is a private class var
     * we should use this getter to use it outside of this class
     * 
     * @return PDO
     */
    function getDbConnection()
    {
        return $this->conn;
    }

    /**
     * We use Sessions to enable users login to our app.
     * The session muse be started very early in the bootstrap process of our app (in index.php)     * 
     * @see https://www.w3schools.com/php/php_sessions.asp
     */
    function startSession() 
    {
        
        session_name("cc-by-an-session-id");
        session_set_cookie_params([
            'lifetime' => 0,
            'path' => '/',
            'domain' => $_SERVER['HTTP_HOST'],
            'secure' => $_SERVER['REQUEST_SCHEME'] == 'https',
            'httponly' => false,
            'samesite' => 'lax'
        ]);
        session_start();

        /**
         * prevent CSRF attacks
         * @see https://medium.com/@steveclifton_12558/php-csrf-prevention-ad0baa2d2902
         */
        if (isset($_POST['csrftoken']) && $_POST['csrftoken'] !== @$_SESSION['csrftoken']) {
            $this->setMessage("CSRF attack detected.", 'error');
            $this->redirect();

        }
        // generate a new token
        $_SESSION['csrftoken'] = md5(base64_encode(random_bytes(32)));
    }

    function getCrfsToken()
    {
        return '<input type="hidden" name="csrftoken" value="'. $_SESSION['csrftoken'] .'">';
    }

    function formIsPosted() 
    {
        return isset($_SESSION['csrftoken']) && isset($_POST['csrftoken']);
    }

    /**
     * Fetch a single Category record by it's ID from our database 
     * 
     * @see https://www.w3schools.com/php/php_mysql_select.asp
     * @see https://www.w3schools.com/php/php_arrays_associative.asp
     * @return Associative Array with the Category or False if no record matches the ID
     */
    function getCategory($id)
    {
        $stmt = $this->conn->prepare("SELECT id, name FROM category WHERE id=:id"); 
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute(); 
        $category = $stmt->fetch(PDO::FETCH_ASSOC);
        return $category;
    }
    
    
    /**
     * Fetch all Categories from our database, order by their names
     * The number of categories can be limited by providing offset and limit parameters. 
     * By default the limit is a very high number, which in practice means "all records"
     * 
     * @see https://www.w3schools.com/php/php_mysql_select.asp
     * @see https://www.w3schools.com/php/php_arrays_multidimensional.asp
     * @return multidimensional Associative Array with the Categories or False if no categories exists
     */
    function getCategories($limit = 100000, $offset = 0) 
    {
        $stmt = $this->conn->prepare("SELECT * FROM category ORDER BY name LIMIT :offset, :limit"); 
        $stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
        $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute(); 
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * On the home page, we want to display 3 categories, each with a random product photo
     * this method creates this result with two queries
     */
    function getTeasers()
    {
        //step 1: fetch 3 categories from DB using our previously defined method
        $categories = $this->getCategories(3);

        //step 2: fetch a random product for each category:
        $stmt = $this->conn->prepare("SELECT * FROM product WHERE category=:id ORDER BY RAND() LIMIT 1"); 
        foreach ($categories as &$category) {
            $stmt->bindParam(':id', $category['id'], PDO::PARAM_INT);
            $stmt->execute();
            $category['id'] = (int)$category['id'];
            $category['product'] = $stmt->fetch(PDO::FETCH_ASSOC);
        }
        return $categories;
    }

    /**
     * Fetches products from our database
     * Optionaly, when a category ID is provided, filters product on that category;
     * @var int $category_id an optional filter for products
     * @var int $limit an optional limit parameter for limiting the number of returned records
     * @var int $offset an optional offset parameter
     */
    function getProducts($category_id = null, $limit = 1000000, $offset = 0)
    {
        /**
         * Create a variable that stores an optional query part string when a category is requested
         */
        $categoryFilterQuery = '';
        $category = false;
        if ($category_id) {
            $category = $this->getCategory($category_id);
            if ($category) {
                $categoryFilterQuery = 'WHERE category=' . intval($category['id']);
            }
        }

        /**
         * @see https://www.w3schools.com/sql/sql_join.asp
         * @see https://www.w3schools.com/sql/sql_join_inner.asp
         * @see https://www.php.net/manual/en/language.types.string.php#language.types.string.syntax.heredoc
         */
        $sql = <<<SQL
            SELECT product.*, category.name AS category 
            FROM product 
            INNER JOIN category ON product.category=category.id
            {$categoryFilterQuery}
            ORDER BY product.name
            LIMIT :offset, :limit
        SQL;
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
        $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute(); 
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Gets product to show single product page 
     */
    function getProduct($id)
   {    
        $stmt=$this->conn->prepare("SELECT * FROM product WHERE product.id = :id");
        $stmt->bindParam(':id',$id,PDO::PARAM_INT);
        $stmt->execute();
        $product = $stmt->fetch(PDO::FETCH_ASSOC);
        return $product;
    }

    /**
     * Flash messages: store messages in session data until they are retrieved. 
     * 
     * The next two methods setMessage() and getMessage() are used to create a
     * "Flash" messaging system to provide usefull feedback to our webshop clients.
     * It works by storing a messages string into our session and fetching that message
     * on the next request. It is especially usefull for POST request, since we redirect users
     * after a POST to page with GET. 
     * 
     * Flash message are rendered in the browser with the "messages.php" file 
     * which is included in our bootsrtap file "index.php"
     * 
     * @see $this->redirect()
     * @see https://www.theserverside.com/news/1365146/Redirect-After-Post
     * @see https://www.w3schools.com/php/php_sessions.asp
     */

    /**
     * Set a Flash message
     */
    function setMessage($msg, $category = 'info')
    {
        $_SESSION["message-{$category}"] = $msg;
    }

    function clearMessage($category = 'info')
    {
        unset($_SESSION["message-{$category}"]);
    }

    /**
     * Fetch Flash message from Session and delete it
     */
    function getMessage($category = 'info')
    {
        if (isset($_SESSION["message-{$category}"])) {
            $msg = $_SESSION["message-{$category}"];
            unset($_SESSION["message-{$category}"]);
            return $msg;
        }
    }

    /**
     * Checks if a user is logged in or redirects
     * 
     * In every page that requires a valid user, add this method on top of that page.
     * If no user is present, a Flash message is set and the user is redirected to the home page.
     */
    function gateKeeper() 
    {
        $user = $this->getAppUser();
        if (!$user) {
            $this->setMessage("U heeft geen toegang tot deze pagina", 'warning');
            $this->redirect();
        }
    }
    
    /** 
     * Redirects the user to another/same page
     * 
     * @see https://code.tutsplus.com/tutorials/how-to-redirect-with-php--cms-34680
     */
    function redirect($page = 'home', $extra = '') 
    {
        header('Location: ?page=' . $page . $extra, true, 301);
        exit;
    }

    /**
     * Function used to get the user that is currently logged in. 
     * Since it can be called from different places, we want to make sure that the user is
     * fetched only once for eacht HTTP request. We do this by defining a static variable.
     * 
     */
    function getAppUser()
    {
        static $user;
        if (null != $user) {
            return $user;
        }
        if (!@(int)$_SESSION['user_id']) return false;
        $user = $this->getUser($_SESSION['user_id']);
        if (!$user) {
            //this is weird: there is a user in this session, but the user does not exists in our DB
            session_unset();
            $this->setMessage("System error: User not found", 'error');
            return false;
        }
        return $user;
    }

    /**
     * Fetch a single User record by it's ID from our database 
     * This can be any user, not just the user that is currently logged in
     * 
     * @see https://www.w3schools.com/php/php_mysql_select.asp
     * @see https://www.w3schools.com/php/php_arrays_associative.asp
     * @return Associative Array with the User or False if no record matches the ID
     */
    function getUser($id) 
    {
        $stmt = $this->conn->prepare("SELECT * FROM client WHERE id=:id"); 
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute(); 
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        $user['id'] = (int)$user['id'];
        return $user;
    }

    function getEmptyUser()
    {
        return [
            'id' => 0,
            'name' => '',
            'email' => '',
            'postalcode' => '',
            'housenumber' => '',
            'phone' => '',
            'isNew' => true
        ];
    
    }

    /**
     * Updates a user in our DB bases on the data that is provided via the profile page
     * Containes strict checks on data input, because we never trust user input
     * 
     * @see https://www.w3schools.com/php/php_mysql_update.asp
     * @see https://dev.to/_garybell/never-trust-user-input-4ff1
     */
    function saveProfile(Array $data, $createUserMode = false) {
        if ($createUserMode) {
            $user = $this->getEmptyUser();
        } else {
            $user = $this->getAppUser();
            if (!$user) {
                session_unset();
                $this->setMessage("Deze gebruiker komt niet voor in ons systeem.", 'error');
                $this->redirect();
            }
        }

        // container for the daata that the user wants us to update:
        $updateData = [];

        /** 
         * make sure the user provided a valid emailaddress
         * @see https://www.w3schools.com/php/php_filter.asp
         */
        if (!isset($data['email']) || !$data['email']  || filter_var($data['email'], FILTER_VALIDATE_EMAIL) == false) {
            $this->setMessage('Een geldig emailadres is verplicht', 'warning');
            $this->redirect('profiel');
        }

        /**
         * We want our emails to be unique, check if new emailadress already exists for other users.
         * "Fetch all users that have this e-mailaddress, but are not the user that wants to edit his data"
         */
        $stmt = $this->conn->prepare("SELECT id FROM client WHERE email=:email AND NOT(id=:id)"); 
        $stmt->bindParam(':id', $user['id'], PDO::PARAM_INT);
        $stmt->bindParam(':email', $data['email']);
        $stmt->execute(); 
        if ($stmt->fetchColumn()) {
            $this->setMessage('Er bestaat al een andere gebruiker met dit emailadres.', 'warning');
            $this->redirect('profiel');
        }

        /**
         * Only change if e-mail address differs from stored e-mail address
         */
        if ($data['email'] != $user['email']) {
            $updateData['email'] = $data['email'];
        }

        if (!isset($data['name']) || !trim($data['name'])) {
            $this->setMessage('Een geldig naam is verplicht', 'warning');
            $this->redirect('profiel');
        }

        /**
         * Only change if name differs from stored name
         */
        if ($data['name'] != $user['name']) {
            $updateData['name'] = $data['name'];
        }

        /**
         * Only change if phone differs from stored phone
         */
        if (isset($data['phone']) && $data['phone'] != $user['phone']) {
            $updateData['phone'] = $data['phone'];
        }

        /**
         * Only change if postalcode differs from stored postalcode
         */
        if (isset($data['postalcode']) && $data['postalcode'] != $user['postalcode']) {
            $updateData['postalcode'] = $data['postalcode'];
        }

        /**
         * Only change if housenumber differs from stored housenumber
         */
        if (isset($data['housenumber']) && $data['housenumber'] != $user['housenumber']) {
            $updateData['housenumber'] = $data['housenumber'];
        }

        /**
         * Password needs to be updated only if a user request this by filling in the password field
         * If it needs to be updated, we check against the second value to check if passwords match
         * to prevent user from accidentally locking themselves out of our webshop.
         * 
         * If all is well, we encrypt the password before storing it in our DB
         * 
         * @see https://www.php.net/manual/en/function.password-hash.php
         */
        if (isset($data['password']) && $data['password'] != '') {
            if (!isset($data['password2']) || $data['password'] != $data['password2']) {
                $this->setMessage('Wachtwoord en controle wachtwoord komen niet overeen', 'warning');
                $this->redirect('profiel');
            }
            $updateData['password'] = password_hash($data['password'],  PASSWORD_DEFAULT);
        }

        /**
         * A password is required when creating a new user:
         */
        if (!$updateData['password'] && $createUserMode) {
            $this->setMessage('U heeft geen geldig wachtwoord opgegeven', 'warning');
            $this->redirect('profiel');
        }

        /**
         * If no data needs to be updated, e.g. the user saves without changing anything,
         * there is no need to go to the DB and store data
         */
        if (!count($updateData)) {
            $this->setMessage("Uw gegevens zijn niet gewijzigd.", 'info');
            $this->redirect('profiel');
        }

        /**
         * Loop thru all fields that needs to be updated and construct an SQL statement, eg.
         * UPDATE CLIENT SET name=:name, phone=:phone WHERE id:id
         */
        $fields = array_keys($updateData);
        array_walk($fields, function($val, $i) use (&$fields) {
            $fields[$i] = "{$val}=:{$val}";
        });
        $updateData['id'] = $user['id'];
        if ($createUserMode) {
            unset($updateData['id']);
            $sql = 'INSERT INTO client SET ' . implode(', ', $fields);
        } else {
            $sql = 'UPDATE client SET ' .implode(', ', $fields).' WHERE id=:id';
        }
        /**
         * If the update or insert statements fails, we do not want to show the native DB message to our users
         * so we redirect with a flash message
         */
        try {
            $this->conn->prepare($sql)->execute($updateData);
        } catch (PDOException $e) {
            $this->setMessage("Uw gegevens zijn niet opgeslagen door een technisch probleem met onze website.", 'error');
            $this->redirect('profiel');
        }
        if ($createUserMode) {
            $this->login($updateData['email'], $data['password']);
            $this->clearMessage('success');
            $this->setMessage("Uw profiel is aangemaakt en u bent automatisch ingelogd.", 'success');
            $this->redirect();
        } else {
            $this->setMessage("Uw gewijzigde gegevens zijn opgeslagen.", 'success');
            $this->redirect('profiel');
        }
    }

    /**
     * Tries to login a user based on e-mail address and password
     * First we fecth a user from our DB based on the e-mail address
     * If such a user exists, we check the provided password against the hash that is stored in the DB
     * If all is well, we store the user's ID and name in the session, so the user stays logged is
     * 
     * @see https://www.php.net/manual/en/function.password-verify.php
     */
    function login($email, $password) {

        $stmt = $this->conn->prepare("SELECT id, name, password FROM client WHERE email=:email"); 
        $stmt->bindParam(':email', $email);
        $stmt->execute(); 
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$user) {
            $this->setMessage("We hebben uw emailadres niet gevonden. Controleer dit, of maak een nieuw account aan.", 'warning');
            return false;
        }

        if (!password_verify($password, $user['password'])) {
            $this->setMessage("De combinatie van het door u opgegeven emailadres en wachtwoord komt niet voor in ons systeem. Controleer dit, of maak een nieuw account aan.", 'warning');
            return false;
        }

        /**
         * Store some user data in the session:
         */
        $_SESSION['user_id'] = (int)$user['id'];
        $_SESSION['user_name'] = $user['name'];

        $this->setMessage("U bent met succes aangemeld", 'success');
        return true;
    }

    /**
     * Log out a user by simply unsetting the session var and redirect to home
     */
    function logout()
    {
        session_unset();
        $this->setMessage("U bent met succes afgemeld, tot ziens!", 'success');
        $this->redirect();
    }

    function inShoppingCart($product_id, $page = 'producten')
    {
        /*
        $user = $this->getAppUser();
        if (!$user) {
            $this->setMessage("U moet eerst <a href='?page=inloggen'>Aanmelden</a> voordat u bij ons kunt bestellen", 'info');
            $this->redirect($page, '#');
        }
        */
        $product = $this->getProduct($product_id);
        if (!$product) {
            $this->setMessage("Product niet gevonden", 'error');
            $this->redirect($page, '#');
        }
        if (!isset($_SESSION['shoppingcart'])) {
            $_SESSION['shoppingcart'] = [];
        }
        if (!isset($_SESSION['shoppingcart'][$product['id']])) {
            $_SESSION['shoppingcart'][$product['id']] = 0;
        }
        $_SESSION['shoppingcart'][$product['id']] ++;
        $this->setMessage("Het product `<strong>{$product['name']}</strong>` is met succes toegevoegd aan uw winkelwagen.", 'info');
        $this->redirect($page, '#');
    }

    function getShoppingCart() {
        return $_SESSION['shoppingcart'];
    }

    function shoppingCartDelete($product_id) {
        if (isset($_SESSION['shoppingcart'][$product_id])) {
            unset($_SESSION['shoppingcart'][$product_id]);
        }
        $this->redirect('winkelwagen');
    }

    function shoppingCartPlus($product_id) {
        if (isset($_SESSION['shoppingcart'][$product_id])) {
            $_SESSION['shoppingcart'][$product_id] ++;
        }
        $this->redirect('winkelwagen');
    }

    function shoppingCartMin($product_id, $delta_items = 1) {
        if (isset($_SESSION['shoppingcart'][$product_id])) {
            $_SESSION['shoppingcart'][$product_id] = $_SESSION['shoppingcart'][$product_id] - 1;
        }
        if ($_SESSION['shoppingcart'][$product_id] == 0) {
            unset($_SESSION['shoppingcart'][$product_id]);
        }
        $this->redirect('winkelwagen');
    }

    function checkoutShoppingCart()
    {
        $user = $this->getAppUser();
        if (!$user) {
            $this->setMessage("U moet eerst <a href='?page=inloggen'>Aanmelden</a> voordat u bij ons kunt bestellen", 'info');
            $this->redirect();
        }
        if (!$this->countShoppingCart()) {
            $this->setMessage("Uw winkelwagen is leeg", 'warning');
            $this->redirect();
        }
        $bestelnummer = session_id();
        $sum = 0;
        $bestelling = "";

        foreach ($_SESSION['shoppingcart'] as $id => $num_items) {
            $product = $this->getProduct($id);
            if ($product) {
                $sum += $num_items * $product['price'];
                $bestelling .= "{$num_items} x {$product['name']} à € " . number_format($product['price']/100, 2, ',', '.') . "\n";
            }
        }
        $sum_fmt = number_format($sum/100, 2, ',', '.');
        $message = "Beste {$user['name']},

Bedankt voor uw bestelling. 
Het ordernummer van uw bestelling is «{$bestelnummer}». 
Zodra wij uw betaling van € {$sum_fmt} hebben ontvangen sturen wij uw producten op naar:
Hoogeweg 40-B
1851 PJ Heiloo

Uw bestelling:
{$bestelling}

Nogmaals bedankt voor uw bestelling en graag tot ziens in onze webshop!

Het team van Cute Cloths By An.
        ";

        $mailresult = mail($user['email'], 'Uw bestelling van Cute Cloths By An', $message);

        if (false) {
            $this->mollie();
        }

        $this->setMessage("Bedankt voor uw bestelling. Wij sturen uw een e-mail met verdere instructies.", 'success');
        if (!$mailresult) {
            $this->setMessage("Bedankt voor uw bestelling. Het is helaas niet gelukt om een e-mail te sturen, wij nemen z.s.m. contact met u op.<p><code>*** DEBUG START E-MAIL ***</code></p><p><blockquote><pre>{$message}</pre></blockquote></p><p><code>*** END E-MAIL ***</code></p>", 'warning');
        } else {
            $this->setMessage("Bedankt voor uw bestelling. Wij sturen uw een e-mail met verdere instructies.<p><code>*** DEBUG START E-MAIL ***</code></p><p><blockquote><pre>{$message}</pre></blockquote></p><p><code>*** END E-MAIL ***</code></p>", 'success');
        }
        unset($_SESSION['shoppingcart']);
        $this->redirect();
    }

    function mollie()
    {
        $protocol = isset($_SERVER['HTTPS']) && strcasecmp('off', $_SERVER['HTTPS']) !== 0 ? "https" : "http";
        $hostname = $_SERVER['HTTP_HOST'];
        $path = dirname(isset($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : $_SERVER['PHP_SELF']);

        $protocol = 'https';
        $hostname = 'cc-by-an.lindeman.nu';
        $path = '';
        
        $orderlines = [];
        $bestelnummer = session_id();
        
        $user = $this->getAppUser();
        $mollie = new \Mollie\Api\MollieApiClient();
        $mollie->setApiKey("test_EjPWTwGKTc7TnJkpSB73HeUc4WBFEC");

        foreach ($_SESSION['shoppingcart'] as $id => $num_items) {
            $product = $this->getProduct($id);
            if ($product) {
                $sum += $num_items * $product['price'];
                $orderlines[] = [
                    'sku' => 'ccan' . sprintf('%05d', $id),
                    'name'  => $product['name'],
                    'productUrl' => "{$protocol}://{$hostname}{$path}/index.php?page=product&product={$id}",
                    "imageUrl" =>  "{$protocol}://{$hostname}{$path}/images/products/small/{$id}.jpg",
                    'quantity' => $num_items,
                    'vatRate' => 0,
                    'unitPrice' => [
                        'currency' => 'EUR',
                        'value' => sprintf('%0.2f', $product['price']/100)
                    ],
                    'totalAmount' => [
                        'currency' => 'EUR',
                        'value' => sprintf('%0.2f', $num_items * $product['price']/100)
                    ],
                    "vatAmount" => [
                        "currency" => "EUR",
                        "value" => "0.00",
                    ],
                ];
            }
        }
        $address = [
            "streetAndNumber" => "Hoogeweg 40-B",
            "postalCode" => "1851 PJ",
            "city" => "Heiloo",
            "country" => "nl",
            "givenName" => "Mees",
            "familyName" => "Lindeman",
            "email" => $user['email']
        ];
        try {
            $order = $mollie->orders->create([
                "amount" => [
                    "currency" => "EUR",
                    "value" => sprintf('%0.2f', $sum/100)
                ],
                'billingAddress' => $address,
                'shippingAddress' => $address,
                "metadata" => [
                    "order_id" => $bestelnummer,
                    "description" => "Cute Cloths By An Bestelling"
                ],
                "locale" => "nl_NL",
                "method" => "ideal",
                'lines' => $orderlines,
                "orderNumber" => strval($bestelnummer),
                "redirectUrl" => "{$protocol}://{$hostname}{$path}/index.php?page=home&confirm=1&order_id={$bestelnummer}",
                "webhookUrl"  => "{$protocol}://{$hostname}{$path}/webhook.php",
            ]);
        } catch (\Mollie\Api\Exceptions\ApiException $e) {
            echo "API call failed: " . htmlspecialchars($e->getMessage());
            exit;
        }
        header("Location: " . $order->getCheckoutUrl(), true, 303);
    }

    function countShoppingCart()
    {
        if (!isset($_SESSION['shoppingcart']) || count($_SESSION['shoppingcart']) == 0) {
            return 0;
        }
        return array_reduce($_SESSION['shoppingcart'], function ($count, $item) {
            $count += $item;
            return $count;
        }, 0);
    }

}


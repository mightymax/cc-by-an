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
         * Since we allow file uploads, catch errors when POST is too large at very early stage in boot proccess
         */
        if (@$_SERVER["CONTENT_LENGTH"] > $this->get_ini_size('post_max_size')) {
            $this->setMessage("Je probeerde iets te uploaden dat groter is dan wij kunnen toestaan (max. ".ini_get("post_max_size").")", "error");
            $this->redirect();
        }

        /**
         * prevent CSRF attacks
         * @see https://medium.com/@steveclifton_12558/php-csrf-prevention-ad0baa2d2902
         */
        if (isset($_POST['csrftoken']) && $_POST['csrftoken'] !== @$_SESSION['csrftoken']) {
            $this->setMessage("CSRF attack detected.", 'error');
            $this->redirect();
        }


        if (isset($_SESSION['redirect'])) {
            unset($_SESSION['redirect']);
        } elseif (isset($_SESSION['POST'])) {
            unset($_SESSION['POST']);
        }


        // generate a new token
        $_SESSION['csrftoken'] = md5(base64_encode(random_bytes(32)));
    }

    /**
     * convert shorthand size to something we can actually do math on
     * @see https://www.php.net/manual/en/faq.using.php#faq.using.shorthandbytes
     */
    function get_ini_size($ini_val_key) {
        $size = ini_get($ini_val_key);
        if (preg_match('/^([\d\.]+)([KMG])$/i', $size, $match)) {
            $pos = array_search($match[2], array("K", "M", "G"));
            if ($pos !== false) {
                $size = $match[1] * pow(1024, $pos + 1);
            }
        }
        return (int)$size;
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
     * Deletes product from database
     */
    function deleteProduct(Array $data)
    {
        if (isset($data['id']) && intval($data['id'])) {
            $product = $this->getProduct($data['id']);
            if (!$product) {
                $this->setMessage('Product niet gevonden', 'error');
                $this->redirect('producten');
            }
        }
        $stmt = $this->conn->prepare('DELETE FROM `product` WHERE id=:id');
        $stmt->bindParam(':id', $product['id'], PDO::PARAM_INT);
        try {
            $stmt->execute(); 
            $this->setMessage('Product is met succes verwijderd', 'success');
        } catch (Exception $e) {
            $this->setMessage('Systeem fout: product is niet opgeslagen', 'error');
        }
        //delete images:
        include_once __DIR__ . '/Images.php';
        $imageTools = new WebshopAppImages($this);
        $imageTools->deleteImage($product, 'small');
        $imageTools->deleteImage($product, 'large');
        $this->redirect('producten');
    }

    /**
     * Able to edit products within our database
     */
    function editProduct(Array $data, $hasFileUpload){

        /* Checks if input data is valid */
    
        if (isset($data['name']) && $data['name']) {
            $storeData['name'] = $data['name'];
        }
        if (isset($data['description']) && $data['description']) {
            $storeData['description'] = $data['description'];
        }
        if (isset($data['category']) && $data['category']) {
            $category = $this->getCategory($data['category']);
            if (!$category) {
                $this->setMessage('Dit is geen bestaande categorie', 'warning');
                $this->redirect('addproduct');
            }
            $storeData['category'] = $data['category'];
        } else {
            $this->setMessage('Kies een categorie', 'warning');
            $this->redirect('addproduct');
        }
        
        if (isset($data['price']) && intval($data['price'])){
            $storeData['price'] = $data['price'];
        } else {
            $this->setMessage('Voer een getal in bij prijs', 'warning');
            $this->redirect('addproduct');
        }
        
        /* Submits newly created product to database */
    
        if (isset($data['id']) && intval($data['id'])) {
            $product = $this->getProduct($data['id']);
            if (!$product) {
                $this->setMessage('Product niet gevonden', 'error');
                $this->redirect('addproduct');
            }
            $sql = "UPDATE product SET name=:name, price=:price, description=:description, category=:category WHERE id=:id";
        } else {
            if (!$hasFileUpload) {
                $this->setMessage('Als je een nieuw product wilt aanmaken is het uploaden van een afbeelding verplicht.', 'warning');
                $this->redirect('addproduct');
            }
            $product = False;
            $sql = "INSERT INTO product SET name=:name, price=:price, description=:description, category=:category";
        }
        $stmt=$this->conn->prepare($sql);
        $stmt->bindParam(':name', $storeData['name']);
        $stmt->bindParam(':price', $storeData['price'],PDO::PARAM_INT);
        $stmt->bindParam(':description', $storeData['description']);
        $stmt->bindParam(':category', $storeData['category'],PDO::PARAM_INT);
        if ($product) {
            $stmt->bindParam(':id', $product['id'],PDO::PARAM_INT);
        }
        if ($stmt->execute()) {
            $this->setMessage('Product is met succes opgeslagen', 'success');
            if ($product) {
                //reload product from our database and return it:
                return $this->getProduct($product['id']);
            } else {
                //new product created, get the id of that record:
                $product_id = (int)$this->conn->lastInsertId();
                return $this->getProduct($product_id);
            }
        } else {
            $this->setMessage('Systeem fout: product is niet opgeslagen', 'error');
            return false;
        }
        //no redirect so we can hand over to image upload class
        // $this->redirect('addproduct');   
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
    function gateKeeper($needAdmin = false) 
    {
        $user = $this->getAppUser();
        if (!$user) {
            $this->setMessage("U heeft geen toegang tot deze pagina", 'warning');
            $this->redirect();
        }

        if ($needAdmin && $user['isAdmin'] == false) {
            $this->setMessage("U heeft geen rechten om deze pagina te bekijken", 'warning');
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
        //save $_POST so we can regenerate form without the user having tot retype everything
        if ($this->formIsPosted()) {
            $_SESSION['POST'] = $_POST;
            if (isset($_SESSION['POST']['csrftoken'])) {
                unset($_SESSION['POST']['csrftoken']);
            }
        } else {
            if (isset($_SESSION['POST'])) {
                unset($_SESSION['POST']);
            }
        }
        //tell next request this is a redirect:
        $_SESSION['redirect'] = true;
        header('Location: ?page=' . $page . $extra, true, 301);
        exit;
    }

    function formValue($key, $default = '') {
        if ($default) return $default;
        else return @$_SESSION['POST'][$key];
    }

    /**
     * Retrieves users from database
     */
    function getUsers($limit = 1000000, $offset = 0)
    {
        $stmt = $this->conn->prepare('SELECT * FROM client ORDER BY `name` LIMIT :offset, :limit');
        $stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
        $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute(); 
        return $stmt->fetchAll(PDO::FETCH_ASSOC);        
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
    function getUser($value, $field = 'id') 
    {
        if ($field != 'id' && preg_match('/^[a-zA-Z_]+$/', $field)) {
            $stmt = $this->conn->prepare("SELECT * FROM client WHERE {$field}=:value");
            $stmt->bindParam(':value', $value);
        } else {
            $stmt = $this->conn->prepare("SELECT * FROM client WHERE id=:id"); 
            $stmt->bindParam(':id', $value, PDO::PARAM_INT);
        }
        $stmt->execute(); 
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$user) return;
        $user['id'] = (int)$user['id'];
        $user['isAdmin'] = $user['isadmin'] == 'Y';
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
            'streetname' => '',
            'place' => '',
            'isadmin' => 'N',
            'isAdmin' => false,
            'isNew' => true
        ];
    
    }

    /**
     * This enables our users to reset their password
     */
    function userForgotPassword ($email) {
        if (!$email  || filter_var($email, FILTER_VALIDATE_EMAIL) == false) {
            $this->setMessage('Een geldig emailadres is verplicht', 'warning');
            $this->redirect('profiel');
        }

        $user = $this->getUser($email, 'email');
        if ($user) {
            $token = bin2hex(openssl_random_pseudo_bytes(16));
            $stmt = $this->conn->prepare("UPDATE client SET token=:token WHERE id=:id"); 
            $stmt->bindParam(':id', $user['id'], PDO::PARAM_INT);
            $stmt->bindParam(':token', $token);
            $stmt->execute();
            $protocol = isset($_SERVER['HTTPS']) && strcasecmp('off', $_SERVER['HTTPS']) !== 0 ? "https" : "http";
            $hostname = $_SERVER['HTTP_HOST'];
            $path = dirname(isset($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : $_SERVER['PHP_SELF']);
    
            $resetUrl = "{$protocol}://{$hostname}{$path}/index.php?page=resetpassword&token={$token}";

            $message = "Beste {$user['name']},

We hebben een verzoek ontvangen voor een nieuw wachtwoord op uw e-mailadres. 
Als u dat niet zelf heeft gedaan, kunt u dit bericht negeren.

Heeft u inderdaad een nieuw wachtwoord aangevraagd, klik dan op deze link:
{$resetUrl}

Met vriendelijke groet,

Het team van Cute Cloths By An.
        ";
            $this->mail($user['email'], 'Nieuw wachtwoord aangevraagd', $message, 'From: cc-by-an@lindeman.nu');
        }

        $this->setMessage('Als uw e-mailadres in ons systeem staat, sturen wij u een e-mail met een link waarmee u een nieuw wachtwoord kunt instellen.', 'success');
        $this->redirect('inloggen');
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
            //is Admin user trying to update a user?
            if (isset($data['id']) && (int)$data['id']) {
                $this->gateKeeper(true);
                $user = $this->getUser($data['id']);
                if (!$user) {
                    $app->setMessage('Gebruiker niet gevonden', 'warning');
                    $app->redirect('users');
                }
                $redirectPage = 'users';
            } else {
                $user = $this->getAppUser();
                if (!$user) {
                    session_unset();
                    $this->setMessage("Deze gebruiker komt niet voor in ons systeem.", 'error');
                    $this->redirect();
                }
                $redirectPage = 'profiel';
            }
        }

        // container for the data that the user wants us to update:
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

        if (!isset($data['postalcode']) || !trim($data['postalcode'])) {
            $this->setMessage('Een geldige postcode is verplicht', 'warning');
            $this->redirect('profiel');
        }

        if (!isset($data['housenumber']) || !trim($data['housenumber'])) {
            $this->setMessage('Een geldig huisnummer is verplicht', 'warning');
            $this->redirect('profiel');
        }

        if (!isset($data['streetname']) || !trim($data['streetname'])) {
            $this->setMessage('Een geldige straatnaam is verplicht', 'warning');
            $this->redirect('profiel');
        }
        
        if (!isset($data['place']) || !trim($data['place'])) {
            $this->setMessage('Een geldige plaatsnaam is verplicht', 'warning');
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

        if (isset($data['streetname']) && $data['streetname'] != $user['streetname']) {
            $updateData['streetname'] = $data['streetname'];
        }

        if (isset($data['place']) && $data['place'] != $user['place']) {
            $updateData['place'] = $data['place'];
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
            if (strlen($data['password']) < 8 || !preg_match("#[0-9]+#", $data['password']) || !preg_match("#[a-zA-Z]+#", $data['password'])) {
                $this->setMessage('Uw wachtwoord moet ten minste 8 karakters bevatten waarvan minstens 1 cijfer en 1 letter', 'warning');
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
            $this->redirect($redirectPage);
        }

        if ($createUserMode) {
            // create token for user to confirm e-mailadress:
            $updateData['token'] = 'ccba-' . bin2hex(openssl_random_pseudo_bytes(16));
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
            $this->redirect($redirectPage);
        }
        if ($createUserMode) {
            // $this->login($updateData['email'], $data['password']);
            $this->clearMessage('success');
            // $this->setMessage("Uw profiel is aangemaakt en u bent automatisch ingelogd.", 'success');
            $this->setMessage("Uw profiel is aangemaakt. Voordat u kunt inloggen moet u uw e-mailadres aan ons bevestigen. Houd uw e-mail in de gaten voor verdere instructies.", 'success');
            $protocol = isset($_SERVER['HTTPS']) && strcasecmp('off', $_SERVER['HTTPS']) !== 0 ? "https" : "http";
            $hostname = $_SERVER['HTTP_HOST'];
            $path = dirname(isset($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : $_SERVER['PHP_SELF']);
    
            $resetUrl = "{$protocol}://{$hostname}{$path}/index.php?page=confirmemailadress&token={$updateData['token']}";

            $message = "Beste {$updateData['name']},

Iemand heeft met uw e-mailadres een profiel aangemaakt op onze webshop. 
Als u dat niet zelf heeft gedaan, kunt u dit bericht negeren.

Heeft u inderdaad een nieuw profiel aangevraagd, dan willen wij graag uw e-mailadres
bevestigen. Klik daarvoor op deze link:
{$resetUrl}

Met vriendelijke groet,

Het team van Cute Cloths By An.
        ";  
            $this->mail($updateData['email'], 'Bevestig uw e-mailadres', $message, 'From: cc-by-an@lindeman.nu');
            $this->redirect();
        } else {
            $this->setMessage("Uw gewijzigde gegevens zijn opgeslagen.", 'success');
            $this->redirect($redirectPage);
        }
    }

    function toggleUserAdmin($id) 
    {
        if ((int)$id == $this->getAppUser()['id']) {
            $this->setMessage('Het is niet verstandig om je eigen adminrechten weg te nemen &hellip; ', 'warning');
            $this->redirect('users', '#user-' . $id);
        }
        $stmt = $this->conn->prepare("UPDATE client SET isadmin = IF(isadmin='Y', 'N', 'Y') WHERE id=:id"); 

        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        try {
            $stmt->execute();
        } catch (Exception $e) {
            $this->setMessage('Systeemfout: niet gelukt om isadmin status te wijzigen', 'error');
        }
    }

    /**
     * This function enables admins to delete users
     */
    function deleteUser($id) 
    {
        if ((int)$id == $this->getAppUser()['id']) {
            $this->setMessage('Huh? Je wilt toch zeker niet jezelf verwijderen?', 'warning');
            $this->redirect('users', '#user-' . $id);
        }
        $stmt = $this->conn->prepare("DELETE FROM client WHERE id=:id"); 
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        try {
            $stmt->execute();
            $this->setMessage('De gebruiker is definitief verwijderd.', 'success');
        } catch (Exception $e) {
            $this->setMessage('Systeemfout: niet gelukt om de gebruiker te verwijderen', 'error');
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

        $stmt = $this->conn->prepare("SELECT id, name, password, token FROM client WHERE email=:email"); 
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

        //e-mailaddress is not yet confirmed:
        if (0 === strpos($user['token'], 'ccba-')) {
            $this->setMessage("Uw e-mailadres is nog niet bevestigd. Kijk uw e-mail na voor een link om dit te doen.", 'warning');
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

    /**
     * All functions below make for optional functioning of the shopping cart.
     * Names are logically given and functions are short so no more commentary is needed.
     */
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

        $orderlines = [];
        foreach ($_SESSION['shoppingcart'] as $id => $num_items) {
            $product = $this->getProduct($id);
            if ($product) {
                $sum += $num_items * $product['price'];
                $bestelling .= "{$num_items} x {$product['name']} à € " . number_format($product['price']/100, 2, ',', '.') . "\n";
                //store orderline for DB storage later on:
                $orderlines[] = ['product' => $product['id'], 'ammount' => $num_items, 'price' => $product['price']];
            }
        }
        $sum_fmt = number_format($sum/100, 2, ',', '.');

        //store order in database:
        $sql = 'INSERT INTO `order` SET client=:client, `date`=NOW(), ammount=:sum, status="nieuw"';
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':client', $user['id'], PDO::PARAM_INT);
        $stmt->bindParam(':sum', $sum, PDO::PARAM_INT);
        try {
            $stmt->execute();
        } catch (Exception $e) {
            $this->setMessage('Er is iets misgegegaan bij het opslaan van uw order. Probeer het later nogmaals.', 'error');
            $this->redirect();
        }
        $order_id = (int)$this->conn->lastInsertId();

        //create orderlines:
        $sql = 'INSERT INTO `order_line` (`order`, `product`, `ammount`, `price`) VALUES (:order, :product, :ammount, :price)';
        $stmt = $this->conn->prepare($sql);
        foreach ($orderlines as $orderline) {
            $stmt->bindParam(':order', $order_id, PDO::PARAM_INT);
            $stmt->bindParam(':product', $orderline['product'], PDO::PARAM_INT);
            $stmt->bindParam(':ammount', $orderline['ammount'], PDO::PARAM_INT);
            $stmt->bindParam(':price', $orderline['price'], PDO::PARAM_INT);
            try {
                $stmt->execute();
            } catch (Exception $e) {
                $this->conn->query('DELETE FROM `order` where id=' . $order_id);
                $this->setMessage('Er is iets misgegegaan bij het opslaan van uw order-regel. Probeer het later nogmaals.', 'error');
                $this->redirect();
            }
        }

        $message = "Beste {$user['name']},

Bedankt voor uw bestelling. 
Het ordernummer van uw bestelling is «{$bestelnummer}». 
Zodra wij uw betaling van € {$sum_fmt} hebben ontvangen sturen wij uw producten op naar:
{$user['streetname']} {$user['housenumber']}
{$user['postalcode']} {$user['place']}

Uw bestelling:
{$bestelling}

Nogmaals bedankt voor uw bestelling en graag tot ziens in onze webshop!

Het team van Cute Cloths By An.
        ";

        $mailresult = $this->mail($user['email'], 'Uw bestelling van Cute Cloths By An', $message, 'From: cc-by-an@lindeman.nu');

        if (false) {
            return $this->mollie();
        }

        $this->setMessage("Bedankt voor uw bestelling. Wij sturen u een e-mail met verdere instructies.", 'success');
        if (!$mailresult) {
            $this->setMessage("Bedankt voor uw bestelling. Het is helaas niet gelukt om een e-mail te sturen, wij nemen z.s.m. contact met u op.", 'warning');
        } else {
            $this->setMessage("Bedankt voor uw bestelling. Wij sturen u een e-mail met verdere instructies.", 'success');
        }
        unset($_SESSION['shoppingcart']);
        $this->redirect();
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

    /**
     * This following functions below enable admins to retrieve orders from the database.
     */
    function getOrders($status = null) {
        // de tabelnaam "order" is wat ongelukkig gekozen, dat is een reserved term in SQL
        // we moeten daarom overal in de query waar we de tabelnaam gebruiken `order` gebruiken (dus tussen "backticks")
        $sql = "
        SELECT 
            client.*, 
            `order`.*, 
            SUM(order_line.ammount) AS num_items 
        FROM `order` 
        INNER JOIN `client` ON client.id = `order`.client
        INNER JOIN `order_line` ON order_line.`order` = `order`.id
        ";
        if ($status) $sql .= " WHERE status=" . $this->conn->quote($status);
        $sql .= " GROUP BY `order`.id";
        $sql .= " ORDER BY `date` DESC";
        $stmt = $this->conn->prepare($sql);
        try {
            $stmt->execute();
        } catch (Exception $e) {
            $this->setMessage('Er is iets misgegegaan bij het ophalen van orders. Probeer het later nogmaals.', 'error');
            $this->redirect();
        }
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    function getOrder($id) 
    {
        $stmt = $this->conn->prepare("SELECT * FROM `order` WHERE id=:id"); 
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        try {
            $stmt->execute();
        } catch (Exception $e) {
            $this->setMessage('Er is iets misgegegaan bij het opphalen van de order. Probeer het later nogmaals.', 'error');
            $this->redirect();
        }
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    function getOrderDetails($order_id) 
    {
        $order = $this->getOrder($order_id);
        if (!$order) return;

        $client = $this->getUser($order['client']);
        if (!$client) return;

        $stmt = $this->conn->prepare('SELECT order_line.*, product.name, product.price AS current_price FROM order_line LEFT JOIN product ON product.id=order_line.product WHERE `order`=:order');
        $stmt->bindParam(':order', $order['id'], PDO::PARAM_INT);
        try {
            $stmt->execute();
        } catch (Exception $e) {
            $this->setMessage('Er is iets misgegegaan bij het ophalen van uw order. Probeer het later nogmaals.', 'error');
            $this->redirect();
        }
        $order_lines = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return [
            'order' => $order,
            'client' => $client,
            'order_lines' => $order_lines
        ];
    }

    /**
     * This function enables admins to set order status.
     */
    function setOrderStatus(Array $order, $newStatus) 
    {
        $order = $this->getOrder($order['id']);
        if (!$order) {
            $this->setMessage('Order niet gevonden', 'warning');
            $this->redirect('orders');
        }
        $statussen = $this->getOrderStatussen();
        if (!in_array($newStatus, $statussen)) {
            $this->setMessage('Ongeldige orderstatus', 'warning');
        } else {
            if ($newStatus != $order['status']) {
                $this->setMessage("Status gewijzigd van <span class=\"order-status order-status-{$order['status']}\">{$order['status']}</span> naar <span class=\"order-status order-status-{$newStatus}\">{$newStatus}</span>", 'success');
                $stmt = $this->conn->prepare('UPDATE `order` SET status=:status WHERE id=:id');
                $stmt->bindParam(':id', $order['id'], PDO::PARAM_INT);
                $stmt->bindParam(':status', $newStatus);
                try {
                    $stmt->execute();
                } catch (Exception $e) {
                    $this->setMessage('Er is iets misgegegaan bij het aanpassen van de orderstatus. Probeer het later nogmaals.', 'error');
                    $this->redirect();
                }
                $user = $this->getUser($order['client']);
                $message = "Beste {$user['name']},

We hebben de status van uw order met nummer {$order['id']} aangepast:
Oude status: {$order['status']}
Nieuw status: {$newStatus}

Met vriendelijke groet,

Het team van Cute Cloths By An.";
                $this->mail($user['email'], 'De status van uw order is gewijzigd', $message, 'From: cc-by-an@lindeman.nu');
            } else {
                $this->setMessage("De status van deze order is ongewijzigd.", 'info');
            }
        }
        $this->redirect('order', '&order=' . $order['id']);
    
    }

    /**
     * This function enables admins to delete orders.
     */
    function deleteOrder($id) 
    {
        $order = $this->getOrder($id);
        if (!$order) {
            $this->setMessage('Order niet gevonden', 'warning');
            $this->redirect('orders');
        }
        $stmt = $this->conn->prepare('DELETE FROM `order` WHERE id=:id');
        $stmt->bindParam(':id', $order['id'], PDO::PARAM_INT);
        try {
            $stmt->execute();
        } catch (Exception $e) {
            $this->setMessage('Er is iets misgegegaan bij het verwijderen van de order. Probeer het later nogmaals.', 'error');
            $this->redirect();
        }
        $this->setMessage("Order is definitief verwijderd.", 'success');
        $this->redirect('orders');
    }

    function getOrderStatussen()
    {
        return ['nieuw', 'betaald', 'verzonden', 'geannuleerd'];
    }

    function config($key = null)
    {
        static $config;
        if (null == $config) {
            if (file_exists(__DIR__ . '/.ENV')) {
                $config = parse_ini_file(__DIR__. '/.ENV');
            } else {
                $config = [];
            }
        }
        if ($key) {
            if (isset($config[$key])) return $config[$key];
        } else {
            return $config;
        }
    }

    function mail($to, $subject, $message, $additional_headers) 
    {
        $sendmail = $this->config('sendmail');
        if (null !== $sendmail && false === (bool)$sendmail ) {
            $this->setMessage("<strong>To:</strong> {$to}\n<strong>Subject:</strong> {$subject}\n\n<strong>Message:</strong>\n{$message}", 'debug');
            return true;
        }
        return mail($to, $subject, $message, $additional_headers);
    }

    /**
     * This function is used to send a contact form to the owner of the webshop.
     */
    function sendContactform(Array $data) {

        if (!isset($data['name']) || !$data['name']) {
            $this->setMessage('Voer uw naam in','warning');
            $this->redirect('contact');
        }
        
        if (!isset($data['email']) || !$data['email']  || filter_var($data['email'], FILTER_VALIDATE_EMAIL) == false) {
            $this->setMessage('Een geldig emailadres is verplicht', 'warning');
            $this->redirect('contact');
        }

        if (!isset($data['subject']) || !$data['subject']) {
            $this->setMessage('Voer een vraag/opmerking in','warning');
            $this->redirect('contact');
        }

        $ToEmail = 'mees@lindeman.nu'; 
        $EmailSubject = 'CC-by-An contactformulier'; 
        $mailheader = "From: ".$data['email']."\r\n"; 
        $mailheader .= "Reply-To: ".$data['email']."\r\n"; 
        $mailheader .= "Content-type: text/html; charset=utf-8\r\n"; 
        $MESSAGE_BODY = "Er is een contactformulier verzonden:
Naam: {$data['name']} 
Email adres: {$data['email']}
Vraag/ opmerking: {$data['subject']}"; 
        //we use a wrapper for php's mail function here, not all dev environments can send mail:
        $result = $this->mail($ToEmail, $EmailSubject, $MESSAGE_BODY, $mailheader);
        if (!$result) {
            $this->setMessage('Uw formulier is niet verzonden, excuus', 'error');
        } else {
            $this->setMessage('Uw formulier is verzonden', 'success');
        }
        $this->redirect('home');
    }
}

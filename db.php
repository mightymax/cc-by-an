<?php
/**
 * Credentials to connect to MySQL server
 * Created with:
    CREATE USER 'webshop'@'localhost' IDENTIFIED BY 'webshop';
    GRANT ALL PRIVILEGES ON webshop.* TO 'webshop'@'localhost';
*/
$dsn = 'mysql:dbname=webshop;host=localhost';
$user = 'webshop';
$password = 'webshop';

try {
    /**
     * We use PHP PDO to connect to database
     * @see https://www.w3schools.com/php/php_mysql_connect.asp
     * @see https://www.php.net/manual/en/book.pdo.php
     */
    $dbh = new WebshopDB($dsn, $user, $password);
} catch (PDOException $e) {
    // if the DB connection fails, we can not do anything else, so we quit ...
    echo 'Connection failed: ' . $e->getMessage();
    exit;
}

/**
 * This is our main Class for the Webshop App
 * It extends the PHP PDO class by adding some usefull methods
 * By adding functionality to this class, we keep our page/modules relatively clean
 * which makes it easier to maintain code
 * @see https://www.w3schools.com/php/php_oop_inheritance.asp
*/
class WebshopDB extends PDO
{
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
        $stmt = $this->prepare("SELECT id, name FROM category WHERE id=:id"); 
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
        $stmt = $this->prepare("SELECT * FROM category ORDER BY name LIMIT :offset, :limit"); 
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
        $stmt = $this->prepare("SELECT * FROM product WHERE category=:id ORDER BY RAND() LIMIT 1"); 
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
        $stmt = $this->prepare($sql);
        $stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
        $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute(); 
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
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
    function redirect($page = 'home') 
    {
        header('Location: ?page=' . $page, true, 301);
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
        $stmt = $this->prepare("SELECT * FROM client WHERE id=:id"); 
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute(); 
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        $user['id'] = (int)$user['id'];
        return $user;
    }

    /**
     * Updates a user in our DB bases on the data that is provided via the profile page
     * Containes strict checks on data input, because we never trust user input
     * 
     * @see https://www.w3schools.com/php/php_mysql_update.asp
     * @see https://dev.to/_garybell/never-trust-user-input-4ff1
     */
    function saveProfile(Array $data) {
        $user = $this->getAppUser();
        if (!$user) {
            session_unset();
            $this->setMessage("Deze gebruiker komt noet voor in ons systeem.", 'error');
            $this->redirect();
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
        $stmt = $this->prepare("SELECT id FROM client WHERE email=:email AND NOT(id=:id)"); 
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
            $updateData['password'] = password_hash($data['password']);
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
        $sql = 'UPDATE client SET ' .implode(', ', $fields).' WHERE id=:id';
        /**
         * If the update statements fails, we do not want to show the natove DB message to our users
         * so we redirect with a flash message
         */
        try {
            $this->prepare($sql)->execute($updateData);
        } catch (PDOException $e) {
            $this->setMessage("Uw gegevens zijn niet opgeslagen door een technisch probleem met onze website.", 'error');
            $this->redirect('profiel');
        }
        $this->setMessage("Uw gewijzigde gegevens zijn opgeslagen.", 'success');
        $this->redirect('profiel');
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

        $stmt = $this->prepare("SELECT id, name, password FROM client WHERE email=:email"); 
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

        $this->setMessage("U bent met succes aangemeldd", 'success');
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

}

// We do not want this sensitive data in the rest of our app
// see https://www.w3schools.com/php/php_variables_scope.asp
unset($user, $password, $dsn);
return $dbh;
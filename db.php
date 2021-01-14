<?php
$dsn = 'mysql:dbname=webshop;host=localhost';
$user = 'webshop';
$password = 'webshop';

try {
    $dbh = new WebshopDB($dsn, $user, $password);
} catch (PDOException $e) {
    echo 'Connection failed: ' . $e->getMessage();
    exit;
}

class WebshopDB extends PDO
{
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

    function getCategory($id)
    {
        $stmt = $this->prepare("SELECT id, name FROM category WHERE id=:id"); 
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute(); 
        $category = $stmt->fetch(PDO::FETCH_ASSOC);
        return $category;
    }

    function getCategories() 
    {
        $stmt = $this->prepare("SELECT id, name FROM category ORDER BY name"); 
        $stmt->execute(); 
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    function getTeasers()
    {

        //step 1: fetch 3 categories from DB:
        $stmt = $this->prepare('SELECT * FROM category ORDER BY category.name LIMIT 3');
        $stmt->execute();
        $categories = $stmt->fetchAll(PDO::FETCH_ASSOC);

        //step 2: fetch a random product for each category:
        foreach ($categories as &$category) {
            $stmt2 = $this->prepare("SELECT * FROM product WHERE category=:id ORDER BY RAND() LIMIT 1"); 
            $stmt2->bindParam(':id', $category['id'], PDO::PARAM_INT);
            $stmt2->execute();
            $category['id'] = (int)$category['id'];
            $category['product'] = $stmt2->fetch(PDO::FETCH_ASSOC);
        }
        return $categories;

    }

    function getProducts($category_id = null, $offset = 0, $limit = 1000000)
    {
        if ($category_id) {
            $category = $this->getCategory($category_id);
        } else {
            $category = false;
        }
        if ($category) {
            $categoryQuery = 'AND category=' . intval($category['id']);
        } else {
            $categoryQuery = '';
        }

        $sql = <<<SQL
            SELECT product.*, category.name AS category 
            FROM product 
            JOIN category ON product.category=category.id
            WHERE 1 {$categoryQuery}
            ORDER BY product.name
            LIMIT {$offset}, {$limit}
        SQL;
        return $this->query($sql);
    }

    #function getProduct()
    #{
    #    $sql = <<<SQL
    #        SELECT 
    #}

    function setMessage($msg, $category = 'info')
    {
        $_SESSION["message-{$category}"] = $msg;
    }

    function getMessage($category = 'info')
    {
        if (isset($_SESSION["message-{$category}"])) {
            $msg = $_SESSION["message-{$category}"];
            unset($_SESSION["message-{$category}"]);
            return $msg;
        }
    }

    function gateKeeper($user) 
    {
        if (!$user || $user['id'] !== @(int)$_SESSION['user_id']) {
            $_SESSION = [];
            $this->setMessage("U heeft geen toegang tot deze pagina", 'warning');
            $this->redirect();
        }
    }

    function redirect($page = 'home') 
    {
        header('Location: ?page=' . $page, true, 301);
        exit;
    }

    function getUser()
    {
        if (!@(int)$_SESSION['user_id']) return false;
        $stmt = $this->prepare("SELECT * FROM client WHERE id=:id"); 
        $stmt->bindParam(':id', $_SESSION['user_id'], PDO::PARAM_INT);
        $stmt->execute(); 
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$user) {
            //this is weird: there is a user in this session, but the user does not exists in our DB
            unset($_SESSION['user_id'], $_SESSION['user_name']);
            $this->setMessage("System error: User not found", 'error');
            return false;
        }
        $user['id'] = (int)$user['id'];
        unset($user['password']);
        return $user;
    }

    function saveUser($user, Array $data) {
        $updateData = [];
        if (!isset($_POST['email']) || !$_POST['email']  || filter_var($_POST['email'], FILTER_VALIDATE_EMAIL) == false) {
            $this->setMessage('Een geldig emailadres is verplicht', 'warning');
            $this->redirect('profiel');
        }

        //emails must be unique, check if new emailadress already exists for other users:
        $stmt = $this->prepare("SELECT id FROM client WHERE email=:email AND NOT(id=:id)"); 
        $stmt->bindParam(':id', $user['id'], PDO::PARAM_INT);
        $stmt->bindParam(':email', $_POST['email']);
        $stmt->execute(); 
        if ($stmt->fetchColumn()) {
            $this->setMessage('Er bestaat al een andere gebruiker met dit emailadres.', 'warning');
            $this->redirect('profiel');
        }

        if ($_POST['email'] != $user['email']) {
            $updateData['email'] = $_POST['email'];
        }

        if (!isset($_POST['name']) || !trim($_POST['name'])) {
            $this->setMessage('Een geldig naam is verplicht', 'warning');
            $this->redirect('profiel');
        }

        if ($_POST['name'] != $user['name']) {
            $updateData['name'] = $_POST['name'];
        }

        if (isset($_POST['phone']) && $_POST['phone'] != $user['phone']) {
            $updateData['phone'] = $_POST['phone'];
        }

        if (isset($_POST['postalcode']) && $_POST['postalcode'] != $user['postalcode']) {
            $updateData['postalcode'] = $_POST['postalcode'];
        }

        if (isset($_POST['housenumber']) && $_POST['housenumber'] != $user['housenumber']) {
            $updateData['housenumber'] = $_POST['housenumber'];
        }

        if (isset($_POST['password']) && $_POST['password'] != '') {
            if (!isset($_POST['password2']) || $_POST['password'] != $_POST['password2']) {
                $this->setMessage('Wachtwoord en controle wachtwoord komen niet overeen', 'warning');
                $this->redirect('profiel');
            }
            $updateData['password'] = password_hash($_POST['password']);
        }

        if (!count($updateData)) {
            $this->setMessage("Uw gegevens zijn niet gewijzigd.", 'info');
            $this->redirect('profiel');
        }

        $fields = array_keys($updateData);
        array_walk($fields, function($val, $i) use (&$fields) {
            $fields[$i] = "{$val}=:{$val}";
        });
        $updateData['id'] = $user['id'];
        $sql = 'UPDATE client SET ' .implode(', ', $fields).' WHERE id=:id';
        try {
            $this->prepare($sql)->execute($updateData);
        } catch (PDOException $e) {
            $this->setMessage("Uw gegevens zijn niet opgeslagen door een technisch probleem met onze website.", 'error');
            $this->redirect('profiel');
        }
        $this->setMessage("Uw gewijzigde gegevens zijn opgeslagen.", 'success');
        $this->redirect('profiel');
    }

    function login() {
        if (!isset($_POST['email']) || !isset($_POST['password'])) {
            $this->setMessage('Vul uw e-mailadres en wachtwoord in om in te loggen.', 'error');
            return false;
        }

        $stmt = $this->prepare("SELECT id, name, password FROM client WHERE email=:email"); 
        $stmt->bindParam(':email', $_POST['email']);
        $stmt->execute(); 
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$user) {
            $this->setMessage("We hebben uw emailadres niet gevonden. Controleer dit, of maak een nieuw account aan.", 'warning');
            return false;
        }
        // $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
        if (!password_verify($_POST['password'], $user['password'])) {
            $this->setMessage("De combinatie van het door u opgegeven emailadres en wachtwoord komt niet voor in ons systeem. Controleer dit, of maak een nieuw account aan.", 'warning');
            return false;
        }
        $_SESSION['user_id'] = (int)$user['id'];
        $_SESSION['user_name'] = $user['name'];

        $this->setMessage("U bent met succes aangemeldd", 'success');
        return true;
    }

    function logout()
    {
        $_SESSION = [];
        $this->setMessage("U bent met succes afgemeld, tot ziens!", 'success');
    }

}



unset($user, $password, $dsn);
return $dbh;
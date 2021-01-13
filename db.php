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
    function getCategory()
    {
        if (!isset($_REQUEST['category'])) {
            return;
        }
        $stmt = $this->prepare("SELECT id, name FROM property WHERE id=:id AND category='categorie' LIMIT 1"); 
        $stmt->bindParam(':id', $_REQUEST['category'], PDO::PARAM_INT);
        $stmt->execute(); 
        $category = $stmt->fetch(PDO::FETCH_ASSOC);
        return $category;
    }

    function getProducts($category = null, $offset = 0, $limit = 1000000)
    {
        $category = $this->getCategory();
        if ($category) {
            $categoryQuery = 'AND property.id=' . intval($category['id']);
        } else {
            $categoryQuery = '';
        }

        $sql = <<<SQL
            SELECT product.*, property.name AS category 
            FROM product 
            JOIN product_has_property ON product.id=product_has_property.product
            JOIN property ON property.id=product_has_property.property AND property.category='categorie'
            WHERE 1 {$categoryQuery}
            ORDER BY product.name
            LIMIT {$offset}, {$limit}
        SQL;
        return $this->query($sql);
    }

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

    function saveUser(Array $data) {
        $this->setMessage("@TODO save user", 'info');
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
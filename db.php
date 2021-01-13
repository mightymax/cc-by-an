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
        $category = $stmt->fetch();
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

}



unset($user, $password, $dsn);
return $dbh;
<?php
$dsn = 'mysql:dbname=webshop;host=localhost';
$user = 'root';
$password = 'root';

try {
    $dbh = new PDO($dsn, $user, $password);
} catch (PDOException $e) {
    echo 'Connection failed: ' . $e->getMessage();
    exit;
}

unset($user, $password, $dsn);
return $dbh;
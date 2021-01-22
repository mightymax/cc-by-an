<?php
/**
 * Bootstrap page for our webshop application
 */

/**
 * Most webservers prevent errors from showing up un the webpage, which is a good thing
 * But since we are developing we need all the info to make sure everything works as expected.
 */
ini_set('error_reporting', E_ALL);
ini_set('display_errors', 1);

/**
 * Credentials to connect to MySQL server
 * Created with:
    CREATE USER 'webshop'@'localhost' IDENTIFIED BY 'webshop';
    GRANT ALL PRIVILEGES ON webshop.* TO 'webshop'@'localhost';
*/
$dsn = 'mysql:dbname=webshop;host=localhost';
$user = 'webshop';
$password = 'webshop';

/**
 * Include our main App class and construct our App
 */
require 'App.php';
$app = new WebshopApp($dsn, $user, $password);

/**
 * Start our session as early as possible:
 */
$app->startSession();

// Use the request super global to get the page that the users wants to see
// @see https://www.w3schools.com/php/php_superglobals.asp

if (!isset($_REQUEST['page'])) $page = 'home';
else $page = $_REQUEST['page'];

// Check if $page only contains simple letters:
if (!preg_match('/^[a-z]+$/', $page)) {
    $page = 404;
}

//check if $page exists:
$pageFile = __DIR__ .'/pages/' . $page . '.php';
if (!file_exists($pageFile) || !is_file($pageFile) || !is_readable($pageFile)) {
    $page = 404;
    $pageFile = __DIR__ .'/pages/' . $page . '.php';
}

// Include our layout page a.k.a. our View
include 'layouts/main.php';

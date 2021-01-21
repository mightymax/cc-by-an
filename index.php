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
$page = @$_REQUEST['page'];
switch ($page) {
    case 'voorwaarden':
    case 'privacy':
    case 'team':
    case 'contact':
    case 'inloggen':
    case 'producten':
    case 'product':
    case 'admin':
    case 'addproduct':
    case 'profiel':
    case 'winkelwagen':
    case 'resetpassword':
    case 'home':
        break;
    case 'logout':
        $app->logout();
        header('Location: ?page=home', true, 301);
        exit;
    // If no page is requested, or someone tries to mess by asking a non-existing page, 
    // show an error page and send HTTP 404 error Not Found
    default:
        // https://en.wikipedia.org/wiki/HTTP_404
        http_response_code(404);
        $page = '404';
        break;
}

// Include our layout page a.k.a. our View
include 'layouts/main.php';

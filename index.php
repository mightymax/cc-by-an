<?php
/**
 * Bootstrap page for our webshop application
 */

/**
 * Most webservers prevent errors from showing up un the webpage, which is a good thing
 * But since we are developing we need all the info to make sure evrything works as expected.
 */
ini_set('error_reporting', E_ALL);
ini_set('display_errors', 1);

/**
 * Include our main App class and assign it to a variable $app
 */
$app = require 'db.php';

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
    case 'profiel':
        $lead = '';
        $title = ucfirst($page);
        break;
    case 'logout':
        $app->logout();
        header('Location: ?page=home', true, 301);
        exit;
    // If no page is requested, or someone tries to mess by asking a non-existing page, 
    // simply show the home page
    default:
        $title = 'Haakwerk';
        $lead = 'Welkom bij onze webshop! U kunt hier terecht voor het unieke haakwerk van An.';
        $page = 'home';
        break;
}

// Include our layout page a.k.a. our View
include 'layout.php';

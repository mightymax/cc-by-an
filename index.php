<?php
session_start();
ini_set('error_reporting', E_ALL);
ini_set('display_errors', 1);

$app = require 'db.php';

$lead = '';

$user = $app->getUser();

if (!$user && isset($_POST['login'])) {
    $page = (true == $app->login()) ? 'home' : 'inloggen';
    $app->redirect($page);
}

$page = @$_REQUEST['page'];
switch ($page) {
    case 'voorwaarden':
    case 'privacy':
    case 'team':
    case 'contact':
    case 'inloggen':
    case 'producten':
    case 'profiel':
        $title = ucfirst($page);
        break;
    case 'logout':
        $app->logout();
        header('Location: ?page=home', true, 301);
        exit;
    default:
        $title = 'Haakwerk';
        $lead = 'Welkom bij onze webshop! U kunt hier terecht voor het unieke haakwerk van An.';
        $page = 'home';
        break;
}

include 'layout.php';

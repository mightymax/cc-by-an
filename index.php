<?php
ini_set('error_reporting', E_ALL);
ini_set('display_errors', 1);

$db = require 'db.php';

$lead = '';
$user = '';


$page = @$_REQUEST['page'];
switch ($page) {
    case 'voorwaarden':
    case 'privacy':
    case 'team':
    case 'contact':
    case 'inloggen':
    case 'producten':
    case 'blah':
        $title = ucfirst($page);
        break;
    default:
        $title = 'Haakwerk';
        $lead = 'Welkom bij onze webshop! U kunt hier terecht voor het unieke haakwerk van &laquo;<em>de Moeder van Mees</em>&raquo;';
        $page = 'home';
        break;
}

include 'layout.php';

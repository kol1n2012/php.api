<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

spl_autoload_register(function ($class_name) {
    $class_name = str_replace('App\\', '', $class_name);
    require_once '../src/' . $class_name . '.php';
});

use App\Controller\DotEnvEnvironment;
use App\Server\Api;
use App\Server\Api\Route;

(new DotEnvEnvironment)->load(__DIR__ . '/../');

//Basic Auth
$login = $_SERVER['PHP_AUTH_USER'] ?? ''; //Username

$password = $_SERVER['PHP_AUTH_PW'] ?? ''; //Password

(new Api($login, $password))->routing([
    '/getUsers' => new Route(['GET', 'POST'], false, $_REQUEST),
    '/getUser/%id%' => new Route('GET', true),
    '/addUser' => new Route('POST', true, $_REQUEST),
    '/deleteUser/%id%' => new Route('DELETE', true),
]);
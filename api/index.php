<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

spl_autoload_register(function ($class_name) {
    $class_name = str_replace('App\\', '', $class_name);
    include '../src/' . $class_name . '.php';
});

use App\Controller\DotEnvEnvironment;
use App\Server\Api;

(new DotEnvEnvironment)->load(__DIR__ . '/../');

//Basic Auth
$login = $_SERVER['PHP_AUTH_USER'] ?? ''; //Username

$password = $_SERVER['PHP_AUTH_PW'] ?? ''; //Password

$api = new Api($login, $password);

$path = parse_url($_SERVER['REQUEST_URI'])['path'];
$path = explode('/', $path);
$path = array_filter($path);

$method = @array_values(array_filter($path, fn($v) => $v !== basename(__DIR__))) ?? [];

$api->setMethod($method);

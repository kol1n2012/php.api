<?php
spl_autoload_register(function ($class_name) {
    $class_name = str_replace('App\\', '', $class_name);
    require_once '../src/' . $class_name . '.php';
});

use App\Controller\DotEnvEnvironment;
use App\Server\Api;
use App\Server\Api\Route;
use App\HTTP\Request;

(new DotEnvEnvironment)->load(__DIR__ . '/../');

if (filter_var(@getenv('DEBUG'), FILTER_VALIDATE_BOOLEAN)) {
    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
    error_reporting(E_ALL);
}


$request = Request::getInstance();

(new Api())->routing([
    '/getUsers' => new Route(['GET', 'POST'], false, [
        'filter' => $request->filter,
        'sort' => $request->sort,
        'select' => $request->select,
    ]),
    '/getUser/%id%' => new Route('GET', true),
    '/addUser' => new Route('POST', true, [
        'name' => $request->name,
        'email' => $request->email,
    ]),
    '/deleteUser/%id%' => new Route('DELETE', true),
    '/getToken' => new Route('GET', false),
]);
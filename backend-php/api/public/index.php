<?php

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

require_once '../core/Router.php';
require_once '../resources/v1/LoginResource.php';
require_once '../resources/v1/UserResource.php';
require_once '../resources/v1/ProductResource.php';

$scriptName = dirname($_SERVER['SCRIPT_NAME']);
$basePath = $scriptName;

$router = new Router('v1', $basePath);
$loginResource = new LoginResource();
$userResource = new UserResource();
$productResource = new ProductResource();

// RUTAS PÚBLICAS
$router->addRoute('POST', '/login', [$loginResource, 'login']);
$router->addRoute('POST', '/logout', [$loginResource, 'logout']);

// RUTAS PROTEGIDAS - USUARIOS
$router->addProtectedRoute('GET', '/users', [$userResource, 'index']);
$router->addProtectedRoute('GET', '/users/{id}', [$userResource, 'show']);
$router->addProtectedRoute('POST', '/users', [$userResource, 'store']);
$router->addProtectedRoute('PUT', '/users/{id}', [$userResource, 'update']);
$router->addProtectedRoute('DELETE', '/users/{id}', [$userResource, 'destroy']);

// RUTAS PROTEGIDAS - PRODUCTOS
$router->addProtectedRoute('GET', '/productos', [$productResource, 'index']);
$router->addProtectedRoute('GET', '/productos/{id}', [$productResource, 'show']);
$router->addProtectedRoute('POST', '/productos', [$productResource, 'store']);
$router->addProtectedRoute('PUT', '/productos/{id}', [$productResource, 'update']);
$router->addProtectedRoute('DELETE', '/productos/{id}', [$productResource, 'destroy']);

$router->dispatch();
?>
<?php

require_once __DIR__ . '/../vendor/autoload.php';

use Symfony\Component\Dotenv\Dotenv;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\HttpKernel\KernelInterface;

(new Dotenv())->bootEnv(__DIR__ . '/../.env');

$kernel = new App\Kernel($_SERVER['APP_ENV'], (bool) $_SERVER['APP_DEBUG']);
$kernel->boot();

$request = Request::create('/opisto/parts', 'GET');

$response = $kernel->handle($request);

echo $response->getContent();

$kernel->terminate($request, $response);
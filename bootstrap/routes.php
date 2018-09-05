<?php

use Slim\Http\Request;
use Slim\Http\Response;

// Routes

$app->post('/organizations', App\Controllers\V1\OrganizationController::class.':store');
$app->get('/organizations/{organization}', App\Controllers\V1\OrganizationController::class.':index');

$app->get('/', function (Request $request, Response $response, array $args) {
    $this->logger->info("Slim-Skeleton '/' route");
    return json_encode(['Status'=>'Framework is up and running !']);
});


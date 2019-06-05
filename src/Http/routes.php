<?php

/**
 * @var Router $router
 */
use Illuminate\Routing\Router;

$router->resource(
    'task',
    'JobController',
    [
        'only' => [
            'index',
            'show',
            'store',
            'update',
        ],
    ]
);

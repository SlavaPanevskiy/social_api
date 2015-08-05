<?php

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It is a breeze. Simply tell Lumen the URIs it should respond to
| and give it the Closure to call when that URI is requested.
|
*/

$app->get('/', function () use ($app) {
    return $app->welcome();
});

$app->get('/users',             'UserController@listUsers');
$app->get('/users/{id:\d+}',    'UserController@getUser');
$app->post('/users',            'UserController@createUser');
$app->delete('/users/{id:\d+}', 'UserController@removeUser');
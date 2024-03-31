<?php

use DI\Container;
use Slim\Factory\AppFactory;
use Slim\Middleware\MethodOverrideMiddleware;
use App\Validator;

require __DIR__ . "/../vendor/autoload.php";

require __DIR__ . '/../src/Validator.php';

$container = new Container();
$container->set('renderer', function () {
    return new \Slim\Views\PhpRenderer(__DIR__ . "/../templates");
});

AppFactory::setContainer($container);
$app = AppFactory::create();
$app->add(MethodOverrideMiddleware::class);
$app->addErrorMiddleware(true, true, true);

$app->get("/users/new", function ($request, $response) {
    $params = [
        'user' => ['nickname' => '', 'email' => '', 'id' => ''],
        'errors' => []
    ];
    return $this->get('renderer')->render($response, 'users/new.phtml', $params);
})->setName('newUser');

$app->get('/users/{id}/edit', function ($request, $response, array $args) {
    $id = $args['id'];
    $string = file_get_contents("users.json");
    $user = json_decode($string, 8);
    $params = [
        'user' => $user,
        'errors' => []
    ];

    return $this->get('renderer')->render($response, 'users/edit.phtml', $params);
})->setName('editUser');

$router = $app->getRouteCollector()->getRouteParser();

$app->patch('/users/{id}', function ($request, $response, array $args) use ($router) {
    $string = file_get_contents("users.json");
    $user = json_decode($string, 8);
    $data = $request->getParsedBodyParam('user');

    $validator = new Validator();
    $errors = $validator->validate($data);

    if (count($errors) === 0) {
        $user['nickname'] = $data['nickname'];

        $jsonUser = json_encode($user);
        file_put_contents("users.json", $jsonUser);
        $url = $router->urlFor('editUser', ['id' => $user['id']]);
        return $response->withRedirect($url);
    }

    $params = [
        'user' => $user,
        'errors' => $errors
    ];

    return $this->get('renderer')->render($response, 'users/edit.phtml', $params);
});

$app->post("/users", function ($request, $response) {
    $user = $request->getParsedBodyParam('user');
    $validator = new Validator();
    $errors = $validator->validate($user);
    if (count($errors) === 0) {
        $jsonUser = json_encode($user);
        file_put_contents("users.json", $jsonUser);
        return $response->withRedirect('/users');
    }
    $params = [
        'user' => $user,
        'errors' => $errors
    ];
    
    return $this->get('renderer')->render($response, 'users/new.phtml', $params)->withStatus(422);
});

$app->run();
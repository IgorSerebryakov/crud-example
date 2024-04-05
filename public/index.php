<?php

use Slim\Factory\AppFactory;
use DI\Container;
use Slim\Middleware\MethodOverrideMiddleware;

require __DIR__ . '/../vendor/autoload.php';

session_start();

$container = new Container();
$container->set('renderer', function () {
    return new \Slim\Views\PhpRenderer(__DIR__ . '/../templates');
});
$container->set('flash', function () {
    return new \Slim\Flash\Messages();
});

AppFactory::setContainer($container);
$app = AppFactory::create();
$app->addErrorMiddleware(true, true, true);
$app->add(MethodOverrideMiddleware::class);

$users = [
    ['name' => 'admin', 'passwordDigest' => hash('sha256', 'secret')],
    ['name' => 'mike', 'passwordDigest' => hash('sha256', 'superpass')],
    ['name' => 'kate', 'passwordDigest' => hash('sha256', 'strongpass')]
];

// BEGIN (write your solution here)
$app->get('/', function ($request, $response) {
    $messages = $this->get('flash')->getMessages();
    if (isset($_SESSION['user'])) {
        $authenticatedUser = $_SESSION['user'];
        $params = [
            'authenticatedUser' => $authenticatedUser,
            'flash' => $messages
        ];

        return $this->get('renderer')->render($response, 'index.phtml', $params);
    }
    
    $params = [
        'flash' => $messages
    ];
    return $this->get('renderer')->render($response, 'index.phtml', $params);
});

$app->post('/session', function ($request, $response) use ($users) {
    $user = $request->getParsedBodyParam('user');
    $encryptedPass = hash('sha256', $user['password']);

    if (in_array(['name' => $user['name'], 'passwordDigest' => $encryptedPass], $users)) {
        $_SESSION['user'] = $user;
    } else {
        $this->get('flash')->addMessage('warning', 'Wrong password or name');
    }

    return $response->withRedirect('/');
});

$app->delete('/session', function ($request, $response) {
    $_SESSION = [];
    session_destroy();
    return $response->withRedirect('/');
});
// END
$app->run();

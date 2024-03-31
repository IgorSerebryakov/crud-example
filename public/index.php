<?php

require __DIR__ . '/../vendor/autoload.php';

use DI\Container;
use Slim\Factory\AppFactory;

$container = new Container();
$container->set('renderer', function () {
    return new \Slim\Views\PhpRenderer(__DIR__ . '/../templates');
});

AppFactory::setContainer($container);
$app = AppFactory::create();
$app->addErrorMiddleware(true, true, true);

$app->get('/schools', function ($request, $response) {
    $data = file_get_contents("schools.json");
    $schools = json_decode($data, true);
    $params = [
        'schools' => $schools
    ];

    return $this->get('renderer')->render($response, "schools/index.phtml", $params);
})->setName('schools');

$app->get('/schools/{id}', function ($request, $response, $args) {
    $id = $args['id'];
    $data = file_get_contents("schools.json");
    $schools = json_decode($data, true);
    foreach ($schools as $school) {
        if ($school['id'] === $id) {
            $params = [
                'school' => $school
            ];
            
            return $this->get('renderer')->render($response, 'schools/show.phtml', $params);
        }
    }
    
    return $response->write('Page not found')->withStatus(404);
})->setName('school');

$app->run();


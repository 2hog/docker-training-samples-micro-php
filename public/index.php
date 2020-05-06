<?php
use Carbon\Carbon;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Respect\Validation\Validator as v;
use Respect\Validation\Exceptions\NestedValidationException;


require __DIR__ . '/../vendor/autoload.php';

$container = new \DI\Container();
\Slim\Factory\AppFactory::setContainer($container);
$app = \Slim\Factory\AppFactory::create();
$app->addBodyParsingMiddleware();
$container = $app->getContainer();

$container->set(
    'log',
    function ($c) {
        $logger = new \Monolog\Logger('ms-php');
        $handler = new \Monolog\Handler\StreamHandler('php://stdout');
        $formatter = new \Monolog\Formatter\JsonFormatter();
        $handler->setFormatter($formatter);
        $logger->pushHandler($handler);
        return $logger;
    }
);

$app->post(
    '/v1/hey',
    function (Request $request, Response $response, array $args) {
        $body = $request->getParsedBody();

        $this->get('log')->info("Got: ${body['name']}");
        if ($body['name'] === 'fail') {
            return withError($response, ['message' => 'Name is fail']);
        }

        return withSuccess(
            $response,
            [
                'name' => $body['name']
            ]
        );
    }
);

/**
 * Responds with success, using our common structure
 */
function withSuccess($response, $data = null, $status = 200)
{
    $payload = json_encode(
        ['data' => $data, 'meta' => ['success' => true], 'errors' => null]
    );
    $response->getBody()->write($payload);
    return $response->withHeader(
        'Content-Type', 'application/json'
    )->withStatus($status);
}

/**
 * Responds with error, using our common structure
 */
function withError($response, $errors = null, $status = 500)
{
    $payload = json_encode(
        ['data' => null, 'meta' => ['success' => false], 'errors' => $errors]
    );
    $response->getBody()->write($payload);
    return $response->withHeader(
        'Content-Type', 'application/json'
    )->withStatus($status);
}


$app->run();

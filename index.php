<?php
chdir(dirname(__DIR__));

define("OK", 200);
define("BAD_REQUEST", 400);
define("UNAUTHORIZED", 401);
define("FORBIDDEN", 403);
define("NOT_FOUND", 404);
define("INTERNAL_SERVER_ERROR", 500);

require_once('vendor/autoload.php');
require 'database/dbHandler.php';

use Zend\Config\Factory;

/*Configuration values*/
$config = Factory::fromFile('Restaurant-API/config/config.php', true);

$app = new \Slim\App;

function handleError($message, $type, $code){
    return [
        'error' => [
            'message' => $message,
            'type' => $type,
            'code' => $code
        ],
    ];
}

require_once 'user.php';
require_once 'menu.php';
require_once 'status.php';
require_once 'take.php';
require_once 'give.php';
$app->run();

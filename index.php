<?php
chdir(dirname(__DIR__));

require_once('vendor/autoload.php');
require 'database/dbHandler.php';

use Zend\Config\Factory;

/*Configuration values*/
$config = Factory::fromFile('Restaurant-API/config/config.php', true);

$app = new \Slim\App;

require_once 'user.php';
require_once 'status.php';
require_once 'take.php';
require_once 'give.php';
$app->run();

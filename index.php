<?php
declare(strict_types=1);

require_once 'vendor/autoload.php';

use iutnc\deefy\dispatch\Dispatcher;

\iutnc\deefy\repository\DeefyRepository::setConfig('deefy.db.ini');

$action = (isset($_GET['action'])) ? $_GET['action'] : 'default';

$app = new Dispatcher($action);
$app->run();
<?php

use Symfony\Component\Dotenv\Dotenv;
use \Symfony\Bundle\FrameworkBundle\Console\Application;
use \Symfony\Component\Console\Input\ArrayInput;
use \Symfony\Component\Console\Output\NullOutput;
use \Symfony\Component\Console\Output\BufferedOutput;
use \App\Kernel;

require dirname(__DIR__).'/vendor/autoload.php';

if (file_exists(dirname(__DIR__).'/config/bootstrap.php')) {
    require dirname(__DIR__).'/config/bootstrap.php';
} elseif (method_exists(Dotenv::class, 'bootEnv')) {
    (new Dotenv())->bootEnv(dirname(__DIR__).'/.env');
}

$kernel = new Kernel('test', true);
$kernel->boot();
$app = new Application($kernel);
$app->setAutoExit(false);

$bo = new BufferedOutput();
$app->run(new ArrayInput([
    'command' => 'doctrine:database:drop',
    '--no-interaction' => true,
    '--if-exists' => true,
    '--env' => 'test',
    '--force' => true,
]), $bo);

$app->run(new ArrayInput([
    'command' => 'doctrine:database:create',
    '--no-interaction' => true,
    '--if-not-exists' => true,
    '--env' => 'test',
]), $bo);

$app->run(new ArrayInput([
    'command' => 'doctrine:schema:create',
    '--no-interaction' => true,
    '--env' => 'test',
]), $bo);

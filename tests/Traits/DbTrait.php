<?php

namespace App\Tests\Traits;

use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\BufferedOutput;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Process;

/**
 * Trait DbTrait
 * @package App\Tests\Traits
 * @this KernelTestCase
 */
trait DbTrait
{
    private static function dropSchema()
    {
        return self::runProcess([
            'doctrine:schema:drop',
            '--force',
        ]);
    }

    private static function createSchema()
    {
        return self::runProcess(['doctrine:schema:create']);
    }

    private static function loadFixtures(array $groups = [])
    {
        array_walk($groups, function (&$group) {
            $group = '--group=' . $group;
        });

        return self::runProcess(array_merge(['doctrine:fixtures:load'], $groups));
    }

    private static function migrate()
    {
        return self::runProcess(['doctrine:migrations:migrate']);
    }

    private static function runProcess(array $array)
    {
        $arguments = array_merge(
            [
                'php',
                'bin/console',
            ],
            $array,
            [
                '--no-interaction',
                '--env=test',
            ]
        );

        $process = new Process($arguments, null, ['APP_ENV' => 'test']);
        $process->run();

        // executes after the command finishes
        if (!$process->isSuccessful()) {
            throw new ProcessFailedException($process);
        }

        return $process->getOutput();
    }

    private static function runCommand(array $params)
    {
        $application = new Application(self::$kernel);
        $application->setAutoExit(false);

        $input = new ArrayInput($params);

        // You can use NullOutput() if you don't need the output
        $output = new BufferedOutput();
        $application->run($input, $output);

        return $output->fetch();
    }
}
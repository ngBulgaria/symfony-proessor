#!/usr/bin/env php
<?php

declare(strict_types=1);

require __DIR__ . '/../vendor/autoload.php';

use Symfony\Component\Console\Application;
use App\Bootstrap\Bootstrap;
use App\Command\ProcessFileCommand;

// Initialize the container using the Bootstrap class
$container = Bootstrap::initContainer();

$application = new Application();

// Retrieve the ProcessFileCommand from the container
$command = $container->get(ProcessFileCommand::class);
$application->add($command);

$application->setDefaultCommand($command->getName(), true);
$application->run();

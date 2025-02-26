<?php

declare(strict_types=1);

namespace App\Bootstrap;

use Symfony\Component\Dotenv\Dotenv;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use Symfony\Component\Config\FileLocator;

class Bootstrap
{
    public static function initContainer(): ContainerBuilder
    {
        // Load environment variables
        $dotenv = new Dotenv();
        $dotenv->usePutenv(true);

        $envFile = dirname(__DIR__) . '/.env';

        // Use `.env.test` if running PHPUnit
        if (isset($_SERVER['APP_ENV']) && $_SERVER['APP_ENV'] === 'test') {
            $envFile = dirname(__DIR__) . '/.env.test';
        }

        // Ensure the file exists before loading
        if (file_exists($envFile)) {
            $dotenv->load($envFile);
        }

        // Initialize the DI container
        $container = new ContainerBuilder();
        $container->setParameter('project.dir', dirname(__DIR__));
        $container->setParameter('exchange.rates.api.key', getenv('EXCHANGE_RATES_API_KEY') ?: '');

        $loader = new YamlFileLoader($container, new FileLocator(__DIR__ . '/../config'));
        $loader->load('services.yaml');

        $container->compile(); // Finalize the container

        return $container;
    }
}

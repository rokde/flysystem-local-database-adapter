<?php

namespace Rokde\Flysystem\Adapter\Supports\Laravel;

use Illuminate\Contracts\Foundation\Application;
use League\Flysystem\Filesystem;
use Rokde\Flysystem\Adapter\LocalDatabaseAdapter;
use Illuminate\Support\ServiceProvider;

/**
 * Class LocalDatabaseFilesystemAdapterServiceProvider
 *
 * Service provider for Laravel
 *
 * @package Rokde\Flysystem\Adapter\Supports\Laravel
 */
class LocalDatabaseFilesystemAdapterServiceProvider extends ServiceProvider
{
    /**
     * booting the storage adapter
     */
    public function boot()
    {
        $this->publishMigrations();

        /** @var \Illuminate\Filesystem\FilesystemManager $filesystemManager */
        $filesystemManager = $this->app->make('Illuminate\Contracts\Filesystem\Factory');

        $filesystemManager->extend('local-database', function (Application $app, array $config) {

            $modelClass = array_get($config, 'model', 'Rokde\Flysystem\Adapter\Model\FileModel');
            $fileModel = $app->make($modelClass);

            $adapter = new LocalDatabaseAdapter($fileModel);

            return new Filesystem($adapter);

        });
    }

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        // nothing to register
    }

    /**
     * publishes migrations
     */
    private function publishMigrations()
    {
        $this->publishes([
            realpath(__DIR__ . '/migrations') => $this->app->databasePath() . '/migrations',
        ]);
    }
}
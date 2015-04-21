<?php namespace Rokde\Flysystem\Adapter\Supports\Laravel;

use League\Flysystem\Filesystem;
use Rokde\Flysystem\Adapter\LocalDatabaseAdapter;
use Rokde\Flysystem\Adapter\Model\FileModel;
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
        /** @var \Illuminate\Filesystem\FilesystemManager $filesystemManager */
        $filesystemManager = $this->app->make('Illuminate\Contracts\Filesystem\Factory');

        $filesystemManager->extend('local-database', function ($app, array $config) {

            $fileModel = new FileModel(array_get($config, 'table', 'files'));
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
}
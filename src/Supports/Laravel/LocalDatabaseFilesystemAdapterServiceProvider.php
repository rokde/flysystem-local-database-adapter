<?php namespace Rokde\Flysystem\Adapter\Supports\Laravel;

use Illuminate\Contracts\Foundation\Application;
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

        $filesystemManager->extend('local-database', function (Application $app, array $config) {

            //  @TODO fix to instantiate an interface binding

            $modelClass = array_get($config, 'model', 'Rokde\Flysystem\Adapter\Model\FileModel');
            $fileModel = $app->make($modelClass);

            $fileModel = new FileModel($fileModel);
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
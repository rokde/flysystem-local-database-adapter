<?php

namespace Rokde\Flysystem\Adapter;

use League\Flysystem\AdapterInterface;
use League\Flysystem\Config;
use League\Flysystem\Util;
use Rokde\Flysystem\Adapter\Model\FileModel;

class LocalDatabaseAdapter implements AdapterInterface
{
    /**
     * internal model for communicating to the database
     *
     * @var FileModel
     */
    private $model;

    /**
     * constructing
     *
     * @param FileModel $model
     */
    public function __construct(FileModel $model)
    {
        $this->model = $model;
    }

    /**
     * @param string $path
     *
     * @return FileModel|null
     */
    protected function findByLocation($path)
    {
        return $this->model->where('location', '=', $path)->first();
    }

    /**
     * Write a new file.
     *
     * @param string $path
     * @param string $contents
     * @param Config $config Config object
     *
     * @return array|false false on failure file meta data on success
     */
    public function write($path, $contents, Config $config)
    {
        $model = $this->findByLocation($path);

        if (null === $model) {
            $model = $this->model->create(['location' => $path]);
        }

        $model->content = $contents;

        $size = mb_strlen($contents);
        $type = 'file';
        $result = compact('contents', 'type', 'size', 'path');

        if ($visibility = $config->get('visibility')) {
            $result['visibility'] = $visibility;
            $model->visibility = $visibility === true;
        }

        try {
            $model->save();
        } catch (\Exception $e) {
            return false;
        }

        return $result;
    }

    /**
     * Write a new file using a stream.
     *
     * @param string $path
     * @param resource $resource
     * @param Config $config Config object
     *
     * @return array|false false on failure file meta data on success
     */
    public function writeStream($path, $resource, Config $config)
    {
        $model = $this->findByLocation($path);
        if (null === $model) {
            $model = $this->model->create(['location' => $path]);
        }

        while ( ! feof($resource)) {
            $model->content .= fread($resource, 1024);
        }

        if ($visibility = $config->get('visibility')) {
            $model->visibility = $visibility === true;
        }

        try {
            $model->save();
        } catch (\Exception $e) {
            return false;
        }

        return compact('path', 'visibility');
    }

    /**
     * Update a file.
     *
     * @param string $path
     * @param string $contents
     * @param Config $config Config object
     *
     * @return array|false false on failure file meta data on success
     */
    public function update($path, $contents, Config $config)
    {
        $result = $this->write($path, $contents, $config);

        if (false === $result) {
            return false;
        }

        $result['mimetype'] = Util::guessMimeType($path, $contents);

        return $result;
    }

    /**
     * Update a file using a stream.
     *
     * @param string $path
     * @param resource $resource
     * @param Config $config Config object
     *
     * @return array|false false on failure file meta data on success
     */
    public function updateStream($path, $resource, Config $config)
    {
        return $this->writeStream($path, $resource, $config);
    }

    /**
     * Rename a file.
     *
     * @param string $path
     * @param string $newpath
     *
     * @return bool
     */
    public function rename($path, $newpath)
    {
        $model = $this->findByLocation($path);

        if (null === $model) {
            return false;
        }

        $model->location = $newpath;

        try {
            $model->save();
        } catch (\Exception $e) {
            return false;
        }

        return true;
    }

    /**
     * Copy a file.
     *
     * @param string $path
     * @param string $newpath
     *
     * @return bool
     */
    public function copy($path, $newpath)
    {
        $model = $this->findByLocation($path);

        if (null === $model) {
            return false;
        }

        $newModel = clone $model;
        $newModel->location = $newpath;

        try {
            $newModel->save();
        } catch (\Exception $e) {
            return false;
        }

        return true;
    }

    /**
     * Delete a file.
     *
     * @param string $path
     *
     * @return bool
     */
    public function delete($path)
    {
        $model = $this->findByLocation($path);

        if (null === $model) {
            return true;
        }

        return $model->delete();
    }

    /**
     * Delete a directory.
     *
     * @param string $dirname
     *
     * @return bool
     */
    public function deleteDir($dirname)
    {
        $entries = $this->model->where('location', 'LIKE', $dirname . '%')->get();

        if ($entries->count() === 0) {
            return true;
        }

        try {
            $entries->map(function ($file) {
                $file->delete();
            });
        } catch (\Exception $e) {
            return false;
        }

        return true;
    }

    /**
     * Create a directory.
     *
     * @param string $dirname directory name
     * @param Config $config
     *
     * @return array|false
     */
    public function createDir($dirname, Config $config)
    {
        return ['path' => $dirname, 'type' => 'dir'];
    }

    /**
     * Set the visibility for a file.
     *
     * @param string $path
     * @param string $visibility
     *
     * @return array|false file meta data
     */
    public function setVisibility($path, $visibility)
    {
        $model = $this->findByLocation($path);

        if (null === $model) {
            return false;
        }

        $model->visibility = $visibility === AdapterInterface::VISIBILITY_PUBLIC;

        try {
            $model->save();
        } catch (\Exception $e) {
            return false;
        }

        return compact('visibility');
    }

    /**
     * Check whether a file exists.
     *
     * @param string $path
     *
     * @return bool
     */
    public function has($path)
    {
        return $this->findByLocation($path) !== null;
    }

    /**
     * Read a file.
     *
     * @param string $path
     *
     * @return array|false
     */
    public function read($path)
    {
        $model = $this->findByLocation($path);

        if (null === $model) {
            return false;
        }

        $contents = $model->content;

        return compact('contents', 'path');
    }

    /**
     * Read a file as a stream.
     *
     * @param string $path
     *
     * @return array|false
     */
    public function readStream($path)
    {
        $result = $this->read($path);

        if (false === $result) {
            return false;
        }

        $stream = fopen('php://memory', 'r+');
        fwrite($stream, $result['contents']);
        rewind($stream);

        return compact('stream', 'path');
    }

    /**
     * List contents of a directory.
     *
     * @param string $directory
     * @param bool $recursive
     *
     * @return array
     */
    public function listContents($directory = '', $recursive = false)
    {
        if ($directory === '')
            $entries = $this->model->all();
        else
            $entries = $this->model->where('location', 'LIKE', $directory . '%')->get();

        if ($entries->count() === 0) {
            return [];
        }

        $result = [];
        $directories = [];

        foreach ($entries as $file) {

            if ( ! $this->checkRecursiveParam($directory, $file, $recursive)) {
                continue;
            }

            $directories = $this->addDirectories($file, $directories);

            $result[] = $this->getMetadataForFile($file);
        }

        foreach ($directories as $directory => $folder) {
            $result[] = $this->getMetadataForDirectory($folder['path'], $folder['timestamp']);
        }

        return $result;
    }

    /**
     * Get all the meta data of a file or directory.
     *
     * @param string $path
     *
     * @return array|false
     */
    public function getMetadata($path)
    {
        $model = $this->findByLocation($path);

        if (null === $model) {
            return false;
        }

        return $this->getMetadataForFile($model);
    }

    /**
     * returns metadata for file
     *
     * @param \Rokde\Flysystem\Adapter\Model\FileModel $file
     *
     * @return array
     */
    private function getMetadataForFile(FileModel $file)
    {
        return [
            'type' => 'file',
            'path' => $file->location,
            'timestamp' => $file->updated_at->timestamp,
            'size' => mb_strlen($file->content),
        ];
    }

    /**
     * returns metadata for directory
     *
     * @param string $path
     * @param int $timestamp
     *
     * @return array
     */
    private function getMetadataForDirectory($path, $timestamp)
    {
        return [
            'type' => 'dir',
            'path' => $path,
            'timestamp' => $timestamp,
        ];
    }

    /**
     * Get all the meta data of a file or directory.
     *
     * @param string $path
     *
     * @return array|false
     */
    public function getSize($path)
    {
        return $this->getMetadata($path);
    }

    /**
     * Get the mimetype of a file.
     *
     * @param string $path
     *
     * @return array|false
     */
    public function getMimetype($path)
    {
        $model = $this->findByLocation($path);

        if (null === $model) {
            return false;
        }

        $mimetype = Util::guessMimeType($model->location, $model->content);
        if (null === $mimetype) {
            return false;
        }

        return compact('mimetype');
    }

    /**
     * Get the timestamp of a file.
     *
     * @param string $path
     *
     * @return array|false
     */
    public function getTimestamp($path)
    {
        return $this->getMetadata($path);
    }

    /**
     * Get the visibility of a file.
     *
     * @param string $path
     *
     * @return array|false
     */
    public function getVisibility($path)
    {
        $model = $this->findByLocation($path);

        if (null === $model) {
            return false;
        }

        $visibility = $model->visible ? AdapterInterface::VISIBILITY_PUBLIC : AdapterInterface::VISIBILITY_PRIVATE;

        return compact('visibility');
    }

    /**
     * checking recursive param
     *
     * returns true when recursive flag is on or the number of occurences for the directory separators has to be the
     * same
     *
     * @TODO fix this quick-and-dirty solution
     *
     * @param string $directory
     * @param \Rokde\Flysystem\Adapter\Model\FileModel $file
     * @param bool $recursive
     *
     * @return bool
     */
    private function checkRecursiveParam($directory, FileModel $file, $recursive)
    {
        if ($recursive) {
            return true;
        }

        return substr_count($directory, '/') !== substr_count($file->location, '/');
    }

    /**
     * adds virtually directories from file location
     *
     * @TODO fix this quick-and-dirty solution
     *
     * @param \Rokde\Flysystem\Adapter\Model\FileModel $file
     * @param array $directories
     *
     * @return array
     */
    private function addDirectories(FileModel $file, array $directories)
    {
        $directory = dirname($file->location);

        if ($directory !== '.' && $directory !== '..') {

            $dir = explode('/', $directory);

            $prefix = '';
            foreach ($dir as $folder) {
                $path = $prefix . $folder;

                if (array_key_exists($path, $directories)) {
                    if ($directories[$path]['timestamp'] < $file->updated_at->timestamp) {
                        $directories[$path]['timestamp'] = $file->updated_at->timestamp;
                    }
                } else {
                    $directories[$path] = [
                        'path' => $path,
                        'timestamp' => $file->updated_at->timestamp,
                    ];
                }
                $prefix = $folder . '/';
            }

        }


        return $directories;
    }
}

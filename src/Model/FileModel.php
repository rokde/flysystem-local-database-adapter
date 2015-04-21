<?php

namespace Rokde\Flysystem\Adapter\Model;

use Illuminate\Database\Eloquent\Model;

/**
 * Class FileModel
 *
 * The FileModel represents the database model as Active Record pattern
 *
 * @package Rokde\Flysystem\Adapter\Model
 */
class FileModel extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'files';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'location',
        'content',
        'visible',
    ];

    /**
     * constructing FileModel with a given table name
     *
     * @param string $tableName
     * @param array $attributes
     */
    public function __construct($tableName, array $attributes = [])
    {
        $this->table = $tableName;

        parent::__construct($attributes);
    }
}
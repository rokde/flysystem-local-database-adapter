<?php

namespace Rokde\Flysystem\Adapter\Model;

use Illuminate\Database\Eloquent\Model;

/**
 * Class FileModel
 *
 * The FileModel represents the database model as Active Record pattern
 *
 * @property integer $id
 * @property string $location
 * @property bool $visibility
 * @property string $content
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 * @method static \Illuminate\Database\Query\Builder|\Rokde\Flysystem\Adapter\Model\FileModel whereId($value)
 * @method static \Illuminate\Database\Query\Builder|\Rokde\Flysystem\Adapter\Model\FileModel whereLocation($value)
 * @method static \Illuminate\Database\Query\Builder|\Rokde\Flysystem\Adapter\Model\FileModel whereVisibility($value)
 * @method static \Illuminate\Database\Query\Builder|\Rokde\Flysystem\Adapter\Model\FileModel whereContent($value)
 * @method static \Illuminate\Database\Query\Builder|\Rokde\Flysystem\Adapter\Model\FileModel whereCreatedAt($value)
 * @method static \Illuminate\Database\Query\Builder|\Rokde\Flysystem\Adapter\Model\FileModel whereUpdatedAt($value)
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
     * is the file visible
     *
     * @return bool
     */
    public function isVisible()
    {
        return boolval($this->visibility);
    }
}
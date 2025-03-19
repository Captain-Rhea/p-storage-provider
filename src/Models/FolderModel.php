<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

class FolderModel extends Model
{
    protected $table = 'folder';
    protected $primaryKey = 'id';
    public $timestamps = true;

    protected $fillable = [
        'name',
        'parent_id',
        'path',
        'created_by',
        'updated_by',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            $model->created_at = Carbon::now('Asia/Bangkok');
            $model->updated_at = Carbon::now('Asia/Bangkok');
        });

        static::updating(function ($model) {
            $model->updated_at = Carbon::now('Asia/Bangkok');
        });
    }

    public function parent()
    {
        return $this->belongsTo(FolderModel::class, 'parent_id');
    }

    public function children()
    {
        return $this->hasMany(FolderModel::class, 'parent_id');
    }

    public function files()
    {
        return $this->hasMany(FileModel::class, 'folder_id');
    }
}

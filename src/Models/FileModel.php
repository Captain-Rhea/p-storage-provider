<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

class FileModel extends Model
{
    protected $table = 'files';
    protected $primaryKey = 'id';
    public $timestamps = true;

    protected $fillable = [
        'folder_id',
        'file_name',
        'file_path',
        'file_size',
        'file_type',
        'uploaded_by',
        'updated_by',
    ];

    const CREATED_AT = 'uploaded_at';
    const UPDATED_AT = 'updated_at';

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            $model->uploaded_at = Carbon::now('Asia/Bangkok');
            $model->updated_at = Carbon::now('Asia/Bangkok');
        });

        static::updating(function ($model) {
            $model->updated_at = Carbon::now('Asia/Bangkok');
        });
    }

    public function folder()
    {
        return $this->belongsTo(FolderModel::class, 'folder_id');
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

class ImageModel extends Model
{
    protected $table = 'images';
    protected $primaryKey = 'id';
    public $timestamps = true;

    protected $fillable = [
        'folder_id',
        'image_name',
        'image_path',
        'image_url',
        'image_size',
        'image_type',
        'width',
        'height',
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

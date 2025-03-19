<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

class FileTypeConfigModel extends Model
{

    protected $table = 'tb_files_type_config';
    protected $primaryKey = 'id';
    public $timestamps = true;

    protected $fillable = [
        'file_type',
        'mime_type',
        'description'
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

    public static function getAll(): array
    {
        return self::where('is_active', 1)->get()->toArray();
    }
}

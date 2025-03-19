<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

class FileModel extends Model
{
    protected $table = 'tb_files';
    protected $primaryKey = 'id';
    public $timestamps = true;

    protected $fillable = [
        'group',
        'file_name',
        'file_description',
        'file_path',
        'file_url',
        'file_size',
        'file_type',
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
}

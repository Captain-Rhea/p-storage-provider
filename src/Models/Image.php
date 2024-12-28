<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Image extends Model
{
    protected $table = 'images';
    protected $primaryKey = 'image_id';
    public $timestamps = true;

    protected $fillable = [
        'name',
        'path',
        'base_url',
        'lazy_url',
        'base_size',
        'lazy_size',
        'uploaded_by',
    ];

    const CREATED_AT = 'uploaded_at';
    const UPDATED_AT = 'updated_at';
}

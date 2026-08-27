<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CustomFrame extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'category',
        'layout_type',
        'frame_image_path',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];
}
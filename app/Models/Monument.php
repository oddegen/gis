<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Monument extends Model
{
    /** @use HasFactory<\Database\Factories\MonumentFactory> */
    use HasFactory;

    protected $fillable = [
        'name',
        'image',
        'geom',
    ];
}

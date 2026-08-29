<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Shirt extends Model
{
    use HasFactory;

    protected $fillable = [
        'uuid', 'creator_name',
        'front_text', 'front_text_color', 'front_image_path', 'front_x', 'front_y', 'front_width', 'front_height',
        'back_text', 'back_text_color', 'back_image_path', 'back_x', 'back_y', 'back_width', 'back_height',
    ];

    protected static function booted(): void
    {
        static::creating(function (Shirt $shirt) {
            $shirt->uuid = $shirt->uuid ?: (string) Str::uuid();
        });
    }

    public function signatures()
    {
        return $this->hasMany(Signature::class);
    }

    public function getRouteKeyName(): string
    {
        return 'uuid';
    }
}
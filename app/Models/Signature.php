<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Signature extends Model
{
    use HasFactory;

    protected $fillable = [
        'shirt_id', 'side', 'mode', 'x', 'y', 'rotation',
        'color', 'typed_text', 'drawn_path', 'signer_name',
    ];

    public function shirt()
    {
        return $this->belongsTo(Shirt::class);
    }
}
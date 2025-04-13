<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    //
    use HasFactory;
    
    protected $guarded = [];

    protected $casts = [
        'image' => 'array',
    ];

    public function store() {
        return $this->belongsTo(Store::class);
    }

    public function productSizes() {
        return $this->hasMany(ProductSize::class);
    }
}

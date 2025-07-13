<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    //
    use HasFactory;

    protected $guarded = [];

    protected $casts =  [
        "quantity" => "integer",
        "store_id" => "integer",
    ];

    public function store()
    {
        return $this->belongsTo(Store::class);
    }

    public function productSizes()
    {
        return $this->hasMany(ProductSize::class);
    }

    public function getImageAttribute($value)
{
    if ($value && !preg_match('/^https?:\/\//', $value)) {
        return asset('storage/' . $value);
    }
    return $value;
}
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TransactionItem extends Model
{

    public $table = "transaction_item";
    public $primaryKey = "id";
    public $fillable = ["transaction_id", "product_id", "quantity", "price"];

    public function transaction()
    {
        return $this->belongsTo(Transaction::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}

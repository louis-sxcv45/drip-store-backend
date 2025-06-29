<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class TransactionItem extends Model
{
    use SoftDeletes;

    public $table = "transaction_item";
    public $primaryKey = "id";
    public $fillable = ["transaction_id", "product_id", "quantity", "price"];

    public function transaction()
    {
        return $this->belongsTo(Transaction::class);
    }
}

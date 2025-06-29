<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Transaction extends Model
{
    public $table = "transaction";
    public $primaryKey = "id";
    public $fillable = ["user_id", "store_id", "status", "total_amount"];

    public function transactionItems()
    {
        return $this->hasMany(TransactionItem::class);
    }
}

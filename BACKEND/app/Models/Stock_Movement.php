<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Stock_Movement extends Model
{
    protected $fillable = [
        'product_id',
        'movement_type',
        'quantity',
        'remarks'
    ];

    public function product() {
        $this->belongsTo(Product::class);
    }
}

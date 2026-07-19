<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Purchase_Item extends Model
{
    protected $fillable = [
        'product_id',
        'purchase_id',
        'quantity',
        'buying_price',
        'subtotal'
    ];

    public function product() {
        return $this->belongsTo(Product::class);
    }
}

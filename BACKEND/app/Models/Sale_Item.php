<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Sale_Item extends Model
{
    protected $fillable = [
        'sale_id',
        'product_id',
        'quantity',
        'selling_price',
        'subtotal'
    ];

    public function product() {
        return $this->belongsTo(Product::class);
    }
}

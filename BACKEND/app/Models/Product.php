<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    protected $fillable = [
        'category_id',
        'barcode',
        'name',
        'image',
        'SKU',
        'description',
        'buying_price',
        'selling_price',
        'unit',
        'minimum_stock',
        'status'
    ];

    public function category() {
        return $this->belongsTo(Category::class);
    }

    public function inventory() {
        return $this->belongsTo(Inventory::class);
    }

    public function saleItems() {
        return $this->hasMany(Sale_Item::class);
    }

    public function purchaseItems() {
        return $this->hasMany(Purchase_Item::class);
    }

    public function stockMovements() {
        return $this->hasMany(Stock_Movement::class);
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Purchase extends Model
{
    protected $fillable = [
        'supplier_id',
        'purchase_date',
        'invoice_number',
        'total_amount',
        'payment_status',
        'remarks'
    ];

    public function supplier() {
        return $this->belongsTo(Supplier::class);
    }

    public function purchaseItems() {
        return $this->hasMany(Purchase_Item::class);
    }
}

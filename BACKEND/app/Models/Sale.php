<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Sale extends Model
{
    protected $fillable = [
        'customer_id',
        'user_id',
        'sale_date',
        'invoice_number',
        'total_amount',
        'payment_method',
        'payment_status',
        'remarks'
    ];

    public function saleItems() {
        return $this->hasMany(Sale_Item::class);
    }

    public function user() {
        return $this->belongsTo(User::class);
    }

    public function customer() {
        return $this->belongsTo(Customer::class);
    }
}

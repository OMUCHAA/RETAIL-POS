<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Customer extends Model
{
    protected $fillable = [
        'customer_name',
        'phone_number',
        'email',
        'address',
        'status'
    ];

    public function sales() {
        return $this->hasMany(Sale::class);
    }
}

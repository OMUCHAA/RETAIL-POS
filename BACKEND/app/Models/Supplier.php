<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Supplier extends Model
{
    protected $fillable = [
        'supplier_name',
        'company_name',
        'contact_phone',
        'contact_email',
        'address',
        'status',
        'remarks'
    ];

    public function purchases() {
        return $this->hasMany(Purchase::class);
    }
}

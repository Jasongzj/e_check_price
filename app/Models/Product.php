<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    protected $fillable = [
        'name', 'barcode', 'price', 'brand',
        'supplier', 'standard'
    ];


    public function storeProducts()
    {
        return $this->hasMany(StoreProduct::class);
    }
}

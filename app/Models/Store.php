<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Store extends Model
{
    protected $fillable = ['name', 'img'];

    public function users()
    {
        return $this->hasMany(User::class);
    }

    public function storeProduct()
    {
        return $this->hasMany(StoreProduct::class);
    }
}

<?php

namespace App\Models;

use App\Services\QiniuService;
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

    public function getQrCodeAttribute($value)
    {
        if (!$value) {
            return $value;
        }
        $qiniuService = new QiniuService();
        return $qiniuService->getAntiTheftUrl($value);
    }

    public function getImgAttribute($value)
    {
        if (!$value) {
            return $value;
        }
        $qiniuService = new QiniuService();
        return $qiniuService->getAntiTheftUrl($value);
    }
}

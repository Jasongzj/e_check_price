<?php

namespace App\Models;

use App\Services\QiniuService;
use Illuminate\Database\Eloquent\Model;

class StoreProduct extends Model
{
    protected $fillable = [
        'barcode', 'cost_price', 'selling_price', 'img',
        'alias',
    ];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function store()
    {
        return $this->belongsTo(Store::class);
    }

    public function getImgAttribute($value)
    {
        if (!$value) {
            return $value;
        }
        $qiniuService = new QiniuService();
        return $qiniuService->getAntiTheftUrl($value);
    }

    public function getAliasAttribute($value) {
        if (is_null($value)) {
            return '';
        }
        return $value;
    }
}

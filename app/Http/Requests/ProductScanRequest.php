<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ProductScanRequest extends Request
{
    public function rules()
    {
        return [
            'barcode' => 'required',
        ];
    }

    public function messages()
    {
        return [
            'barcode.required' => '条形码不能为空',
        ];
    }
}

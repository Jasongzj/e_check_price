<?php

namespace App\Http\Requests;


class ProductStore extends Request
{

    public function rules()
    {
        return [
            'cost_price' => 'required|numeric',
            'selling_price' => 'required|numeric',
        ];
    }

    public function messages()
    {
        return [
            'cost_price.required' => '成本价不能为空',
            'selling_price.required' => '售价不能为空',
        ];
    }
}

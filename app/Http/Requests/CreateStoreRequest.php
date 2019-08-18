<?php

namespace App\Http\Requests;


class CreateStoreRequest extends Request
{
    public function rules()
    {
        return [
            'name' => 'required',
        ];
    }

    public function messages()
    {
        return [
            'name.required' => '店铺名称不能为空',
        ];
    }
}

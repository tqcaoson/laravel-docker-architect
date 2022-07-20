<?php


namespace LargeLaravel\Ship\Http\Requests\API;


use LargeLaravel\Ship\Abstracts\Requests\ApiRequest;
use LargeLaravel\Ship\Http\Requests\API\Interfaces\StoreRequestInterface;

class StoreBookRequest extends ApiRequest implements StoreRequestInterface
{
    public function rules()
    {
        return [
            'limit'  => 'integer|min:0|required_with:offset',
            'offset' => 'integer|min:0',
        ];
    }
}

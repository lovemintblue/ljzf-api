<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class ShopRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array|string>
     */
    public function rules(): array
    {
        return [
            'floor' => ['nullable', 'integer', 'min:0', 'max:999'],
            'room_number' => ['nullable', 'string', 'max:50'],
            'floor_height' => ['nullable', 'numeric', 'min:0', 'max:999'],
            'frontage' => ['nullable', 'numeric', 'min:0', 'max:999'],
            'depth' => ['nullable', 'numeric', 'min:0', 'max:999'],
            'description' => ['nullable', 'string', 'max:5000'],
            'suitable_businesses' => ['nullable', 'string'],
            'rental_type' => ['nullable', 'integer', 'in:0,1'],
        ];
    }
    
    /**
     * 自定义验证消息
     */
    public function messages(): array
    {
        return [
            'floor.integer' => '楼层必须是整数',
            'floor.min' => '楼层不能小于0',
            'floor.max' => '楼层不能大于999',
            'room_number.string' => '门牌号必须是文本',
            'room_number.max' => '门牌号不能超过50字符',
            'floor_height.numeric' => '层高必须是数字',
            'floor_height.min' => '层高不能小于0',
            'floor_height.max' => '层高不能大于999',
            'frontage.numeric' => '面宽必须是数字',
            'frontage.min' => '面宽不能小于0',
            'frontage.max' => '面宽不能大于999',
            'depth.numeric' => '进深必须是数字',
            'depth.min' => '进深不能小于0',
            'depth.max' => '进深不能大于999',
            'description.string' => '描述必须是文本',
            'description.max' => '描述不能超过5000字符',
        ];
    }
}

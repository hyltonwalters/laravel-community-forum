<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CreateChannelsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $channel = $this->route('channel');

        return [
            'title' => [
                'required',
                'string',
                'max:255',
                Rule::unique('channels', 'title')->ignore($channel),
            ],
        ];
    }
}

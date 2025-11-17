<?php

namespace App\Http\Requests;

use App\Exceptions\ValidationException;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;

abstract class BaseRequest extends FormRequest
{
    protected function failedValidation(Validator $validator)
    {
        $errors = collect($validator->errors()->messages())
            ->map(fn($messages) => match (count($messages)) {
                1 => $messages[0],
                default => $messages[0] . ' (+' . (count($messages) - 1) . ' more)'
            })
            ->toArray();

        throw new ValidationException('Validation faild', null, $errors);
    }
}

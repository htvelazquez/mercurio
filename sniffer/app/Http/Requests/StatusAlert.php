<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Validation\ValidationException;

class StatusAlert extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'status' => 'required|integer|min:0|max:1'
        ];
    }

    public function response(array $errors)
    {
        return response()->json($errors, 422);
    }

    protected function failedValidation(Validator $validator)
    {
        $response = new JsonResponse([
                'message' => 'The given data is invalid', 
                'errors' => $validator->errors()
            ], 422);

        throw new ValidationException($validator, $response);
    }

    public function messages()
    {
        $messages = [
            "status.required" => "The status field is required.",
        ];

        return $messages;
    }
    
}

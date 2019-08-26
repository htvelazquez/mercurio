<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Validation\ValidationException;
use Illuminate\Validation\Rule;
use App\Rules\UniqueSalesforceId;

class StoreSnapshot extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'lId' => 'required',
            'fullExperience' => 'required|array',
            'fullExperience.*.jobTitle' => 'required',
            'priority' => 'required',
            'salesforceId' => [new UniqueSalesforceId($this->input('lId'))]
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
            "lId.required" => "The lId field is required.",
            "priority"     => "The priority field is required.",
        ];

        if (!empty($this->input('fullExperience'))) {
            foreach ($this->input('fullExperience') as $key => $val) {
                $messages["fullExperience.$key.companyName.required"] = "The fullExperience.companyName field is required.";
                $messages["fullExperience.$key.jobTitle.required"] = "The fullExperience.jobTitle field is required.";
                $messages["fullExperience.$key.from.required"] = "The fullExperience.from field is required.";
            }
        }

        return $messages;
    }
}

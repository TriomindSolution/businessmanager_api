<?php

namespace App\Http\Requests;

use App\Traits\ResponseTrait;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Http\Exceptions\HttpResponseException;
// use Illuminate\Http\Response;

class SellerRequest extends FormRequest
{
    use ResponseTrait;
    public function authorize(): bool
    {
        return true;
    }


    public function rules(): array
    {
        return [
            'name' => 'required',
            'address' => 'nullable|string',
            'phone' => 'required|string',
            'email' => 'nullable|email',
            'description' => 'nullable|string',
            'status' => 'required|string',
        ];
    }

    protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(response()->json([
            // 'status_code' => Response::HTTP_UNPROCESSABLE_ENTITY,
            'status_code' => 403,
            'status' => false,
            'message' => $validator->errors()->first(),
        ], Response::HTTP_UNPROCESSABLE_ENTITY));
    }
}

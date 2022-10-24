<?php

/**
 * This file is for you put inside app/Http/Requests/PaginatedRequest.php
 */

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class PaginatedRequest extends FormRequest
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

    protected function prepareForValidation()
    {
        foreach ($this->defaults() as $key => $defaultValue) {
            if (! $this->has($key)) {
                $this->merge([$key => $defaultValue]);
            }
        }
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'page' => 'sometimes|integer',
            'per_page' => 'sometimes|integer|min:10|max:100',
        ];
    }

    protected function defaults()
    {
        return [
            'page' => 1,
            'per_page' => config('services.pagination.default', 10),
        ];
    }
}

<?php

declare(strict_types=1);

namespace AliSaleem\NovaDropzoneField\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * @property-read string $resource_name
 * @property-read string $resource_id
 * @property-read string $file
 * @property-read string $attribute
 */
class RemoveRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'resource_name' => ['required', 'string'],
            'resource_id'   => ['required', 'string'],
            'file'          => ['required', 'string'],
            'attribute'     => ['required', 'string'],
        ];
    }
}

<?php

declare(strict_types=1);

namespace AliSaleem\NovaDropzoneField\Http\Requests;

use AliSaleem\NovaDropzoneField\Support\RequestHelpers;
use Illuminate\Foundation\Http\FormRequest;

/**
 * @property-read string $key
 * @property-read string $attribute
 * @property-read string $name
 * @property-read ?string $full_path
 * @property-read ?string $temp_disk
 * @property-read ?string $temp_path
 */
class RemoveRequest extends FormRequest
{
    use RequestHelpers;

    public function rules(): array
    {
        return [
            'key'       => ['required', 'string', 'uuid'],
            'attribute' => ['required', 'string'],
            'name'      => ['required', 'string'],
            'full_path' => ['nullable', 'string'],
            'temp_disk' => ['nullable', 'string'],
            'temp_path' => ['nullable', 'string'],
        ];
    }
}

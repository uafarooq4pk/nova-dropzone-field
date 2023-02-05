<?php

declare(strict_types=1);

namespace AliSaleem\NovaDropzoneField\Http\Controllers;

use AliSaleem\NovaDropzoneField\Http\Requests\RemoveRequest;
use Illuminate\Http\Response;
use Laravel\Nova\Fields\Field;
use Laravel\Nova\Http\Requests\NovaRequest;
use Laravel\Nova\Nova;

class RemoveController
{
    public function __invoke(RemoveRequest $request, NovaRequest $novaRequest): Response
    {
        $model = Nova::modelInstanceForKey($request->resource_name)->findOrFail($request->resource_id);
        collect(Nova::resourceInstanceForKey($request->resource_name)->detailFields($novaRequest))
            ->first(fn (Field $field) => $field->attribute === $request->attribute)
            ->destroy($model, $request->file);

        return response()->noContent();
    }
}

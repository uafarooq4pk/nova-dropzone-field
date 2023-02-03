<?php

declare(strict_types=1);

namespace AliSaleem\NovaDropzoneField;

use Illuminate\Http\Request;
use Illuminate\Http\Response;

class UploadController
{
    public const ROUTE = '/nova-dropzone';
    public function __invoke(Request $request): Response
    {
        $data = validator($request->all(), [
            'key'       => ['required', 'string', 'uuid'],
            'attribute' => ['required', 'string'],
            'full_path' => ['nullable', 'string'],
        ])->safe();

        validator($request->all(), [
            $data->attribute => ['required', 'file'],
        ])->validate();

        $file = $request->file($data->attribute);

        cache()
            ->lock('dropzone-upload', 10)
            ->block(8, function () use ($data, $file): void {
                $key = "dropzone.{$data->key}";
                $path = $file->store("{$data->key}/{$file->getFilename()}", ['disk' => 'tmp']);
                $list = collect(cache()->get($key, []))
                    ->put($data->full_path ?: $file->getClientOriginalName(), [
                        'path'         => $path,
                        'originalName' => $file->getClientOriginalName(),
                        'mimeType'     => $file->getMimeType(),
                    ]);

                cache()->put($key, $list->toArray());
            });

        return response()->noContent();
    }
}

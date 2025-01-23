<?php

declare(strict_types=1);

namespace AliSaleem\NovaDropzoneField\Http\Controllers;

use AliSaleem\NovaDropzoneField\Http\Requests\UploadRequest;
use Illuminate\Http\Response;
use Illuminate\Support\Str;

class UploadController
{
    public function __invoke(UploadRequest $request): Response
    {
        validator(
            $request->only($request->attribute),
            [$request->attribute => ['required', 'file']]
        )->validate();

        cache()
            ->lock('dropzone-upload', 10)
            ->block(8, function () use ($request): void {
                $key = "dropzone.{$request->key}";
                $file = $request->file($request->attribute);
                $path = $file->storeAs(
                    $request->path(),
                    $file->getClientOriginalName(),
                    $request->temp_disk
                );

                $list = collect(cache()->get($key, []))
                    ->put($request->full_path ?: $file->getClientOriginalName(), [
                        'name' => str($path)->afterLast('/')->toString(),
                        'originalName' => $file->getClientOriginalName(),
                        'mimeType' => $file->getClientMimeType(),
                    ]);

                cache()->put($key, $list->toArray());
            });

        return response()->noContent();
    }
}

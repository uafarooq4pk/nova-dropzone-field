<?php

declare(strict_types=1);

namespace AliSaleem\NovaDropzoneField\Http\Controllers;

use AliSaleem\NovaDropzoneField\Http\Requests\RemoveTempRequest;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Storage;

class RemoveTempController
{
    public function __invoke(RemoveTempRequest $request): Response
    {
        cache()
            ->lock('dropzone-upload', 10)
            ->block(8, function () use ($request): void {
                $key = "dropzone.{$request->key}";
                $list = collect(cache()->get($key, []));
                if (!$file = $list->pull($request->full_path ?: $request->name)) {
                    return;
                }
                Storage::disk($request->temp_disk)->delete(
                    collect([
                        $request->path(),
                        $file['name']
                    ])->implode('/')
                );
                cache()->put($key, $list->toArray());
            });

        return response()->noContent();
    }
}

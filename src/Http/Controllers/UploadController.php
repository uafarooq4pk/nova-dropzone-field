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
                    $this->sanitizeFileName($file->getClientOriginalName()),
                    $request->temp_disk
                );

                $list = collect(cache()->get($key, []))
                    ->put($request->full_path ?: $this->sanitizeFileName($file->getClientOriginalName()), [
                        'name' => str($path)->afterLast('/')->toString(),
                        'originalName' => $this->sanitizeFileName($file->getClientOriginalName()),
                        'mimeType' => $file->getClientMimeType(),
                    ]);

                cache()->put($key, $list->toArray());
            });

        return response()->noContent();
    }
    private static function sanitizeFileName($filename)
    {
        // Split filename and extension
        $info = pathinfo($filename);
        $name = $info['filename'];
        $extension = isset($info['extension']) ? '.' . $info['extension'] : '';

        // Remove special characters and replace spaces with underscores
        $name = preg_replace('/[^a-zA-Z0-9-]/', '_', $name);
        
        // Replace multiple consecutive underscores with a single underscore
        $name = preg_replace('/_+/', '_', $name);
        
        // Remove leading/trailing underscores
        $name = trim($name, '_');

        // Combine name and extension
        return $name . $extension;
    }
}

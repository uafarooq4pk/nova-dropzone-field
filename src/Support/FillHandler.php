<?php

declare(strict_types=1);

namespace AliSaleem\NovaDropzoneField\Support;

use AliSaleem\NovaDropzoneField\Dropzone;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class FillHandler
{
    /** @var callable */
    protected $storeUsingCallback;

    public function __construct(
        callable $storeUsingCallback,
        protected Dropzone $field
    ) {
        $this->storeUsingCallback = $storeUsingCallback;
    }

    public function handle($request, $model, $attribute, $requestAttribute): void
    {
        $key = $request->get($requestAttribute);

        $this->ensureModelExists($model);

        $this->field->getUploadedFiles($key)
            ->each(fn (UploadedFile $file, string $target) => call_user_func(
                $this->storeUsingCallback,
                $file,
                $target,
                $model,
                $request,
                $attribute,
                $requestAttribute
            ));

        $this->cleanup($key);
    }

    protected function ensureModelExists(Model $model): void
    {
        if (!$model->exists) {
            $model->save();
        }
    }

    protected function cleanup(string $key): void
    {
        cache()->forget("dropzone.{$key}");
        Storage::disk($this->field->meta['tempDisk'])
            ->deleteDirectory(
                collect([trim($this->field->meta['tempPath'] ?: '', '/'), $key])->filter()->implode('/')
            );
    }
}

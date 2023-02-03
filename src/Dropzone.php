<?php

declare(strict_types=1);

namespace AliSaleem\NovaDropzoneField;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use IteratorIterator;
use Laravel\Nova\Fields\Field;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;

class Dropzone extends Field
{
    /**
     * The field's component.
     *
     * @var string
     */
    public $component = 'dropzone-field';

    public $meta = [
        'url'               => UploadController::ROUTE,
        'method'            => 'post',
        'withCredentials'   => true,
        'timeout'           => null,
        'parallelUploads'   => 1,
        'uploadMultiple'    => false,
        'maxFilesize'       => 256,
        'paramName'         => '',
        'thumbnailWidth'    => 120,
        'thumbnailHeight'   => 120,
        'filesizeBase'      => 1024,
        'maxFiles'          => null,
        'acceptedFiles'     => null,
        'acceptedMimeTypes' => null,
        'dz-key'            => null,
    ];

    protected $storeUsingCallback = null;

    public function __construct($name, $attribute = null, callable $resolveCallback = null)
    {
        parent::__construct($name, $attribute, $resolveCallback);

        $this->paramName($this->attribute);
        $this->withMeta(['dz-key' => Str::uuid()]);

        $this->displayUsing(function ($value, $resource, $attribute) {
            $path = storage_path("app/pages/popup_htmls/{$resource->getKey()}/");
            if (!file_exists($path)) {
                return [];
            }

            $regexIterator = new IteratorIterator(
                new RecursiveIteratorIterator(new RecursiveDirectoryIterator($path), RecursiveIteratorIterator::CHILD_FIRST),
            );

            return collect($regexIterator)
                ->keys()
                ->filter(fn ($file) => is_file($file))
                ->map(fn ($file) => str($file)->after($path)->toString())
                ->values()
                ->toArray();
        });

        $this->fillUsing(function ($request, $model, $attribute, $requestAttribute): void {
            if (!$model->exists) {
                $model->save();
            }
            collect(cache()->get('dropzone.'.$request->get($attribute), []))
                ->each(function ($tempFile, $target) use ($request, $model, $attribute, $requestAttribute): void {
                    if (is_callable($this->storeUsingCallback)) {
                        $tempFile['path'] = storage_path('app/tmp/'.$tempFile['path']);

                        $file = new UploadedFile(...$tempFile);
                        call_user_func($this->storeUsingCallback, $file, $target, $model, $request, $attribute, $requestAttribute);
                    }
                    unlink($tempFile['path']);
                });

            cache()->forget('dropzone.'.$request->get($attribute));
            Storage::disk('tmp')->deleteDirectory($request->get($attribute));
        });
    }

    public function paramName(string $paramName): static
    {
        return $this->withMeta(['paramName' => $paramName]);
    }

    public function storeUsing(callable $storeUsing): static
    {
        $this->storeUsingCallback = $storeUsing;

        return $this;
    }

    public function setValue($value): void
    {
        $this->withMeta(['dz-key' => $value]);

        $this->value = $value;
    }

    public function url(string $url): static
    {
        return $this->withMeta(['url' => $url]);
    }

    public function method(string $method): static
    {
        return $this->withMeta(['method' => $method]);
    }

    public function withCredentials(bool $withCredentials): static
    {
        return $this->withMeta(['withCredentials' => $withCredentials]);
    }

    public function timeout(?int $timeout): static
    {
        return $this->withMeta(['timeout' => $timeout]);
    }

    public function parallelUploads(bool $parallelUploads): static
    {
        return $this->withMeta(['parallelUploads' => $parallelUploads]);
    }

    public function uploadMultiple(bool $uploadMultiple): static
    {
        return $this->withMeta(['uploadMultiple' => $uploadMultiple]);
    }

    public function maxFilesize(?int $maxFilesize): static
    {
        return $this->withMeta(['maxFilesize' => $maxFilesize]);
    }

    public function thumbnailWidth(int $thumbnailWidth): static
    {
        return $this->withMeta(['thumbnailWidth' => $thumbnailWidth]);
    }

    public function thumbnailHeight(int $thumbnailHeight): static
    {
        return $this->withMeta(['thumbnailHeight' => $thumbnailHeight]);
    }

    public function filesizeBase(int $filesizeBase): static
    {
        return $this->withMeta(['filesizeBase' => $filesizeBase]);
    }

    public function maxFiles(?int $maxFiles): static
    {
        return $this->withMeta(['maxFiles' => $maxFiles]);
    }

    public function acceptedFiles(array $acceptedFiles): static
    {
        return $this->withMeta(['acceptedFiles' => $acceptedFiles]);
    }

    public function acceptedMimeTypes(array $acceptedMimeTypes): static
    {
        return $this->withMeta(['acceptedMimeTypes' => $acceptedMimeTypes]);
    }
}

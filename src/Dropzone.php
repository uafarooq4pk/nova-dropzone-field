<?php

declare(strict_types=1);

namespace AliSaleem\NovaDropzoneField;

use AliSaleem\NovaDropzoneField\Support\DropzoneRule;
use AliSaleem\NovaDropzoneField\Support\FillHandler;
use Illuminate\Contracts\Validation\InvokableRule;
use Illuminate\Contracts\Validation\Rule;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Laravel\Nova\Fields\Field;

class Dropzone extends Field
{
    /** @var string */
    public $component = 'dropzone-field';

    /** @var array */
    public $meta = [
        'options'  => [
            'url'                   => FieldServiceProvider::ROUTE,
            'method'                => 'post',
            'withCredentials'       => true,
            'timeout'               => null,
            'parallelUploads'       => 5,
            'uploadMultiple'        => false,
            'maxFilesize'           => 256,
            'paramName'             => null,
            'thumbnailWidth'        => 120,
            'thumbnailHeight'       => 120,
            'filesizeBase'          => 1024,
            'maxFiles'              => null,
            'acceptedFiles'         => null,
            'acceptedMimeTypes'     => null,
            'addRemoveLinks'        => true,
            'createImageThumbnails' => false,
        ],
        'key'      => null,
        'tempDisk' => null,
        'tempPath' => null,
    ];

    /**
     * @var array|callable|\Laravel\Nova\Fields\TValidationRules
     */
    public $dropzone_rules;

    /** @var callable */
    protected $destroyUsingCallback;

    public function __construct($name, $attribute = null, callable $resolveCallback = null)
    {
        parent::__construct($name, $attribute, $resolveCallback);

        $this
            ->paramName($this->attribute)
            ->withMeta([
                'key'      => Str::uuid(),
                'tempDisk' => config('filesystems.default'),
            ]);

        $this->rules = [new DropzoneRule($this)];
    }

    public function paramName(string $paramName): static
    {
        return $this->withOptions(['paramName' => $paramName]);
    }

    public function withOptions(array $options): static
    {
        $this->meta = array_merge($this->meta, [
            'options' => array_merge($this->meta['options'], $options)
        ]);

        return $this;
    }

    public function rules($rules): static
    {
        $parameters = func_get_args();

        $this->dropzone_rules = (
            $rules instanceof Rule ||
            $rules instanceof InvokableRule ||
            is_string($rules) ||
            count($parameters) > 1
        ) ? $parameters : $rules;

        return $this;
    }

    public function tempDisk(?string $disk): static
    {
        return $this->withMeta(['tempDisk' => $disk]);
    }

    public function tempPath(?string $path): static
    {
        return $this->withMeta(['tempPath' => $path]);
    }

    public function displayUsingList(callable $displayListCallback): static
    {
        $this->displayUsing(function ($value, $resource, $attribute) use ($displayListCallback) {
            $filesList = call_user_func(
                $displayListCallback,
                $resource,
                $attribute
            );

            return Collection::wrap($filesList)->values();
        });

        return $this;
    }

    public function storeUsing(callable $storeUsing): static
    {
        $this->fillUsing([
            new FillHandler(
                $storeUsing,
                $this
            ),
            'handle'
        ]);

        return $this;
    }

    public function destroyUsing(callable $destroyUsing): static
    {
        $this->destroyUsingCallback = $destroyUsing;

        return $this;
    }

    public function destroy(Model $model, string $file): static
    {
        call_user_func($this->destroyUsingCallback, $model, $file, $this->attribute);
        return $this;
    }

    public function setValue($value): void
    {
        $this->withMeta(['key' => $value]);
        $this->value = $value;
    }

    public function url(string $url): static
    {
        return $this->withOptions(['url' => $url]);
    }

    public function method(string $method): static
    {
        return $this->withOptions(['method' => $method]);
    }

    public function withCredentials(bool $withCredentials): static
    {
        return $this->withOptions(['withCredentials' => $withCredentials]);
    }

    public function timeout(?int $timeout): static
    {
        return $this->withOptions(['timeout' => $timeout]);
    }

    public function parallelUploads(bool $parallelUploads): static
    {
        return $this->withOptions(['parallelUploads' => $parallelUploads]);
    }

    public function uploadMultiple(bool $uploadMultiple): static
    {
        return $this->withOptions(['uploadMultiple' => $uploadMultiple]);
    }

    public function maxFilesize(?int $maxFilesize): static
    {
        return $this->withOptions(['maxFilesize' => $maxFilesize]);
    }

    public function thumbnailWidth(int $thumbnailWidth): static
    {
        return $this->withOptions(['thumbnailWidth' => $thumbnailWidth]);
    }

    public function thumbnailHeight(int $thumbnailHeight): static
    {
        return $this->withOptions(['thumbnailHeight' => $thumbnailHeight]);
    }

    public function filesizeBase(int $filesizeBase): static
    {
        return $this->withOptions(['filesizeBase' => $filesizeBase]);
    }

    public function maxFiles(?int $maxFiles): static
    {
        return $this->withOptions(['maxFiles' => $maxFiles]);
    }

    public function acceptedFiles(array $acceptedFiles): static
    {
        return $this->withOptions(['acceptedFiles' => $acceptedFiles]);
    }

    public function acceptedMimeTypes(array $acceptedMimeTypes): static
    {
        return $this->withOptions(['acceptedMimeTypes' => $acceptedMimeTypes]);
    }

    public function getUploadedFiles(string $key): Collection
    {
        if(config("filesystems.disks.{$this->meta['tempDisk']}.driver") == 's3'){
            return collect(cache()->get("dropzone.{$key}", []))
                    ->map(fn (array $data) => collect([
                        rtrim(config("filesystems.disks.{$this->meta['tempDisk']}.url")),
                        trim($this->meta['tempPath'] ?: '', '/'),
                        $key,
                        $data['name'],
                    ])
                    ->filter()
                    ->implode('/'));
        }else{
            return collect(cache()->get("dropzone.{$key}", []))
                ->map(fn (array $data) => new UploadedFile(
                    collect([
                        rtrim(config("filesystems.disks.{$this->meta['tempDisk']}.url"), '/'),
                        trim($this->meta['tempPath'] ?: '', '/'),
                        $key,
                        $data['name'],
                    ])
                        ->filter()
                        ->implode('/'),
                    $data['originalName'],
                    $data['mimeType'],
                    test: true
                ));
        }
    }

    
}

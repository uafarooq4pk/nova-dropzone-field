<?php

declare(strict_types=1);

namespace AliSaleem\NovaDropzoneField\Support;

use AliSaleem\NovaDropzoneField\Dropzone;
use Illuminate\Contracts\Support\MessageBag;
use Illuminate\Contracts\Validation\Rule;
use Illuminate\Http\UploadedFile;

class DropzoneRule implements Rule
{
    protected array $messages = [];

    public function __construct(protected Dropzone $field)
    {
    }

    public function passes($attribute, $value): bool
    {
        if(config("filesystems.default") == 's3'){
            return empty($this->messages);
        }else{
            $rules = $this->field->dropzone_rules;
    
            $this->messages = $this->field->getUploadedFiles($value)
                ->map(fn (UploadedFile $file) => validator(['dropzone' => $file], ['dropzone' => $rules])->errors())
                ->keyBy(fn ($_, string $file): string => str($file)->afterLast('/')->toString())
                ->map(fn (MessageBag $messages, string $file) => collect($messages->get('dropzone'))
                    ->map(fn ($message) => str($message)->replace('dropzone', "{$file} file")->toString())
                    ->toArray())
                ->flatten()
                ->toArray();
    
            return empty($this->messages);

        }
    }

    public function message(): array
    {
        return $this->messages;
    }
}

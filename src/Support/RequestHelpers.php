<?php

declare(strict_types=1);

namespace AliSaleem\NovaDropzoneField\Support;

/**
 * @property-read string $key
 * @property-read ?string $temp_path
 */
trait RequestHelpers
{
    public function path(): string
    {
        return collect([
            trim($this->temp_path ?: '', '/'),
            $this->key,
        ])
            ->filter()
            ->implode('/');
    }
}

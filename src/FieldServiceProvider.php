<?php

declare(strict_types=1);

namespace AliSaleem\NovaDropzoneField;

use AliSaleem\NovaDropzoneField\Http\Controllers\RemoveController;
use AliSaleem\NovaDropzoneField\Http\Controllers\RemoveTempController;
use AliSaleem\NovaDropzoneField\Http\Controllers\UploadController;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use Laravel\Nova\Events\ServingNova;
use Laravel\Nova\Nova;

class FieldServiceProvider extends ServiceProvider
{
    public const ROUTE = '/nova-vendor/nova-dropzone';

    public function boot(): void
    {
        Nova::serving(function (ServingNova $event): void {
            Nova::script('nova-dropzone-field', __DIR__.'/../dist/js/field.js');
        });
    }

    public function register(): void
    {
        Route::prefix(static::ROUTE)
            ->middleware(config('nova.middleware', []))
            ->group(function (): void {
                Route::post('/', UploadController::class);
                Route::put('/', RemoveTempController::class);
                Route::delete('/', RemoveController::class);
            });
    }
}

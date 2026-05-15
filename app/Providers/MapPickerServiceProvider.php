<?php

namespace App\Providers;

use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;

class MapPickerServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        // Load the vendor package's views under the 'filament-map-picker' namespace
        $this->loadViewsFrom(
            base_path('vendor/humaidem/filament-map-picker/resources/views'),
            'filament-map-picker'
        );

        // Serve the package's compiled JS/CSS assets
        Route::get('humaidem/map-picker/{file}', function (string $file) {
            $allowed = ['map-picker.js', 'map-picker.css', 'map-picker.js.map', 'map-picker.css.map'];

            if (!in_array($file, $allowed, true)) {
                abort(404);
            }

            $path = base_path("vendor/humaidem/filament-map-picker/dist/humaidem/map-picker/{$file}");

            if (!file_exists($path)) {
                abort(404);
            }

            $mimeTypes = [
                'js'  => 'application/javascript',
                'css' => 'text/css',
                'map' => 'application/json',
            ];

            $ext  = pathinfo($file, PATHINFO_EXTENSION);
            $mime = $mimeTypes[$ext] ?? 'application/octet-stream';

            return response()->file($path, ['Content-Type' => $mime]);
        })->name('map-picker.assets');
    }
}

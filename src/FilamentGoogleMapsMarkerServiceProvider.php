<?php

namespace AAbosham\FilamentGoogleMapsMarker;

use Spatie\LaravelPackageTools\Package;
use Filament\PluginServiceProvider;
use AAbosham\FilamentGoogleMapsMarker\Commands\FilamentGoogleMapsMarkerCommand;

class FilamentGoogleMapsMarkerServiceProvider extends PluginServiceProvider
{
    protected array $styles = [
        'filament-google-maps-marker-styles' => __DIR__ . '/../resources/dist/filament-google-maps-marker.css',
    ];

    protected array $scripts = [
        // 'filament-google-maps-marker-scripts' => __DIR__ . '/../resources/dist/filament-google-maps-marker.js',
    ];

    protected array $beforeCoreScripts = [
        'filament-google-maps-marker-scripts' => __DIR__ . '/../resources/dist/filament-google-maps-marker-core.min.js',
    ];

    public function configurePackage(Package $package): void
    {
        /*
         * This class is a Package Service Provider
         *
         * More info: https://github.com/spatie/laravel-package-tools
         */
        $package
            ->name('filament-google-maps-marker')
            ->hasConfigFile()
            ->hasViews()
            ->hasTranslations();
    }
}

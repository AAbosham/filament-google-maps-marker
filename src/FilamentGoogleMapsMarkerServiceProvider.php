<?php

namespace AAbosham\FilamentGoogleMapsMarker;

use Filament\Support\Assets\Js;
use Filament\Support\Assets\Css;
use Filament\Support\Assets\Asset;
use Filament\PluginServiceProvider;
use Spatie\LaravelPackageTools\Package;
use Filament\Support\Facades\FilamentAsset;
use Spatie\LaravelPackageTools\PackageServiceProvider;
use AAbosham\FilamentGoogleMapsMarker\Commands\FilamentGoogleMapsMarkerCommand;

class FilamentGoogleMapsMarkerServiceProvider extends PackageServiceProvider
{
    public static string $name = 'filament-google-maps-marker';

    public static string $viewNamespace = 'filament-google-maps-marker';


    public function configurePackage(Package $package): void
    {
        /*
         * This class is a Package Service Provider
         *
         * More info: https://github.com/spatie/laravel-package-tools
         */
        $package->name(static::$name);

        if (file_exists($package->basePath('/../config'))) {
            $package->hasConfigFile();
        }

        if (file_exists($package->basePath('/../resources/views'))) {
            $package->hasViews(static::$viewNamespace);
        }

        if (file_exists($package->basePath('/../resources/lang'))) {
            $package->hasTranslations();
        }
    }

    public function packageBooted(): void
    {
        // Asset Registration
        FilamentAsset::register(
            $this->getAssets(),
            $this->getAssetPackageName()
        );
    }

    protected function getAssetPackageName(): ?string
    {
        return 'filament-google-maps-marker';
    }

    /**
     * @return array<Asset>
     */
    protected function getAssets(): array
    {
        return [
            Css::make('filament-google-maps-marker-styles', __DIR__ . '/../resources/dist/filament-google-maps-marker.css'),
            Js::make('filament-google-maps-marker-scripts', __DIR__ . '/../resources/dist/filament-google-maps-marker-core.min.js'),
        ];
    }
}

<?php

namespace AAbosham\FilamentGoogleMapsMarker\Forms\Components;

use Closure;
use Filament\Forms\Components\Field;
use Filament\Forms\Components\Concerns\CanBeDisabled;
use Illuminate\Support\Str;
use InvalidArgumentException;

class GoogleMapsMarker extends Field
{
    use CanBeDisabled;

    protected string $view = 'filament-google-maps-marker::forms.components.google-maps-marker';

    /**
     * Google Maps controls variables
     * @var array
     */

    private array $controls = [
        'zoomControl' => false,
        'mapTypeControl' => false,
        'scaleControl' => false,
        'streetViewControl' => false,
        'rotateControl' => false,
        'fullScreenControl' => false,
        'searchBoxControl' => false,
        'geolocationControl' => false,
        'coordsBoxControl' => false,
    ];

    /**
     * Google Maps options variables
     * @var array
     */

    private array $options = [
        'zoom' => 1,
        'mapTypeId' => 'hybrid', // roadmap, satellite, hybrid or terrain
        'fixMarkerOnCenter' => false,
        'defaultToMyLocation' => false,
        'searchBoxPlaceholderText' => null,
        'locationButtonText' => null,
        'minHeight' => '50vh', // vh, px, %,
        'draggable' => true, // draggable markers,
        'multiple' => false,
        'maxMarkers' => 1,
        'minMarkers' => 0,
        'cast' => null, // latLngString , latLngArray
    ];

    /**
     * Setup function
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->dehydrateStateUsing(static function (GoogleMapsMarker $component, ?array $state) {
            if ($component->options['cast'] != null) {
                return $component->getCastLocations($state);
            }

            return $state;
        });

        $this->afterStateHydrated(function (GoogleMapsMarker $component, $state) {
            if ($component->options['cast'] != null) {
                $data = $component->setCastLocations($state);

                $component->state($data);
            }
        });

        $this->locationButtonText(__('filament-google-maps-marker::map.actions.current_location.label'));

        $this->searchBoxPlaceholderText(__('filament-google-maps-marker::map.fieldset.search_box.placeholder'));
    }

    public function getCastLocations($state): array
    {
        $castType = $this->options['cast'];

        $locations = [];

        switch ($castType) {
            case 'latLngString':
                return collect($state ?? [])
                    ->map(function ($location) {
                        return $location['lat'] . ',' . $location['lng'];
                    })
                    ->filter()
                    ->toArray();
                break;

            case 'latLngArray':
                return collect($state ?? [])
                    ->map(function ($location) {
                        return [
                            $location['lat'],
                            $location['lng']
                        ];
                    })
                    ->filter()
                    ->toArray();
                break;

            default:
                throw new InvalidArgumentException('Incorrect cast format.');
                break;
        }

        return $locations;
    }


    public function setCastLocations($state): array
    {
        if (!$state) {
            return [];
        }

        $castType = $this->options['cast'];

        $locations = [];

        switch ($castType) {
            case 'latLngString':
                foreach (collect($state) ?? [] as $location) {
                    $separator = Str::contains($location, ',') ? ',' : (Str::contains($location, ' ') ? ' ' : null);

                    if ($separator == null) {
                        throw new InvalidArgumentException('Location cast not string correct format check string lat lng separator.');
                    }

                    $location = explode($separator, $location);

                    if (count($location) != 2) {
                        throw new InvalidArgumentException('Incorrect location format.');
                    }

                    $locations[(string) Str::uuid()] = ['lat' => (float) $location[0], 'lng' => (float) $location[1]];
                };
                break;

            case 'latLngArray':
                foreach ($state ?? [] as $location) {

                    $location = collect($location)->toArray();

                    if (count($location) != 2) {
                        throw new InvalidArgumentException('Incorrect location format.');
                    }

                    $locations[(string) Str::uuid()] = ['lat' => (float) $location[0], 'lng' => (float) $location[1]];
                };
                break;

            default:
                throw new InvalidArgumentException('Incorrect cast format.');
                break;
        }

        return $locations;
    }

    public function castFromLatLngArray(): self
    {
        $this->cast('latLngString');

        return $this;
    }

    public function castFromLatLngString(): self
    {
        $this->cast('latLngString');

        return $this;
    }

    protected function cast(string $cast): self
    {
        $this->options['cast'] = $cast;

        return $this;
    }

    public function getCast(): string
    {
        return $this->evaluate($this->options['cast']);
    }

    public function zoom(int $zoom): self
    {
        $this->options['zoom'] = $zoom;

        return $this;
    }

    public function mapTypeId(string $mapTypeId): self
    {
        $this->options['mapTypeId'] = $mapTypeId;
        return $this;
    }

    public function fixMarkerOnCenter($status = true): self
    {
        $this->options['fixMarkerOnCenter'] = $status;
        return $this;
    }

    public function defaultToMyLocation($status = true): self
    {
        $this->options['defaultToMyLocation'] = $status;
        return $this;
    }

    public function getSearchBoxPlaceholderText()
    {
        return $this->options['searchBoxPlaceholderText'];
    }

    public function searchBoxPlaceholderText($text = 'Search Address'): self
    {
        $this->options['searchBoxPlaceholderText'] = $text;
        return $this;
    }

    public function getLocationButtonText()
    {
        return $this->options['locationButtonText'];
    }

    public function locationButtonText($text): self
    {
        $this->options['locationButtonText'] = $text ;

        return $this;
    }

    public function getMinHeight()
    {
        return $this->options['minHeight'];
    }

    public function minHeight($minHeight = '50vh'): self
    {
        $this->options['minHeight'] = $minHeight;
        return $this;
    }

    public function zoomControl($status = true): self
    {
        $this->controls['zoomControl'] = $status;
        return $this;
    }

    public function mapTypeControl($status = true): self
    {
        $this->controls['mapTypeControl'] = $status;
        return $this;
    }

    public function scaleControl($status = true): self
    {
        $this->controls['scaleControl'] = $status;
        return $this;
    }

    public function streetViewControl($status = true): self
    {
        $this->controls['streetViewControl'] = $status;
        return $this;
    }

    public function rotateControl($status = true): self
    {
        $this->controls['rotateControl'] = $status;
        return $this;
    }

    public function fullScreenControl($status = true): self
    {
        $this->controls['fullScreenControl'] = $status;
        return $this;
    }

    public function searchBoxControl($status = true): self
    {
        $this->controls['searchBoxControl'] = $status;
        return $this;
    }

    public function coordsBoxControl($status = true): self
    {
        $this->controls['coordsBoxControl'] = $status;
        return $this;
    }

    public function geolocationControl($status = true): self
    {
        $this->controls['geolocationControl'] = $status;
        return $this;
    }

    public function getMapControls()
    {
        return json_encode($this->controls);
    }

    public function getMapOptions()
    {
        return json_encode($this->options);
    }

    public function isSearchBoxControlEnabled()
    {
        return $this->controls['searchBoxControl'];
    }

    public function isCoordsBoxControlEnabled()
    {
        return $this->controls['coordsBoxControl'];
    }

    public function isGeolocationControlEnabled()
    {
        return $this->controls['geolocationControl'];
    }

    public function draggable(bool | Closure $condition = true): static
    {
        $this->options['draggable'] = $condition;

        return $this;
    }

    public function isDraggable(): bool
    {
        return $this->evaluate($this->options['draggable']) || $this->getContainer()->isDisabled();
    }

    public function maxMarkers(int | null | Closure $maxMarkers = null): static
    {
        $this->options['maxMarkers'] = $maxMarkers;

        return $this;
    }

    public function getMaxMarkers(): int | null
    {
        return $this->evaluate($this->options['maxMarkers']);
    }

    public function minMarkers(int | null |  Closure $minMarkers = null): static
    {
        $this->options['minMarkers'] = $minMarkers;

        return $this;
    }

    public function getMinMarkers(): int | null
    {
        return $this->evaluate($this->options['minMarkers']);
    }

    public function multiple(bool |   Closure $multiple = true): static
    {
        $this->options['multiple'] = $multiple;

        return $this;
    }

    public function isMultiple(): bool
    {
        return $this->evaluate($this->options['multiple']);
    }
}

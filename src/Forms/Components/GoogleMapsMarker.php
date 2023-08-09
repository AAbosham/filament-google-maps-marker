<?php

namespace AAbosham\FilamentGoogleMapsMarker\Forms\Components;

use App\Forms\Components\MapMultiMarker;
use Closure;
use Filament\Forms\Components\Actions;
use Filament\Forms\Components\Field;
use Filament\Forms\Components\Concerns\CanBeDisabled;
use Filament\Forms\Components\Concerns\HasActions;
use Filament\Forms;
use Illuminate\Support\Str;
use Filament\Forms\ComponentContainer;
use Filament\Forms\Components\Concerns\CanLimitItemsLength;
use Illuminate\Support\Facades\Log;
use InvalidArgumentException;

class GoogleMapsMarker extends Field
{
    use CanBeDisabled;
    use HasActions;
    use CanLimitItemsLength;

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

        $this->registerActions([
            Actions\Action::make('edit')
                ->button()
                ->color('primary')
                ->icon('heroicon-s-pencil')
                ->label(__('filament-google-maps-marker::map.actions.edit.label'))
                ->extraAttributes([
                    // 'class' => 'bg-white hover:bg-white',
                ])
                ->mountUsing(function (?ComponentContainer $form = null, array $data, $component, $livewire): void {
                    if (!$form) {
                        return;
                    }

                    $form->fill([
                        'locations' => $component->getState()
                    ]);
                })
                ->form([
                    Forms\Components\Tabs::make('')
                        ->tabs([
                            Forms\Components\Tabs\Tab::make('lat_lng_system')
                                ->label(__('filament-google-maps-marker::map.actions.edit.fieldset.lat_lng_system.label'))
                                ->schema([
                                    Forms\Components\Grid::make()
                                        ->columns(1)
                                        ->schema([
                                            Forms\Components\Repeater::make('locations')
                                                ->label(__('filament-google-maps-marker::map.actions.edit.fieldset.markers.label'))
                                                // ->createItemButtonLabel(__('filament-google-maps-marker::map.actions.edit.fieldset.markers.actions.create'))
                                                ->maxItems($this->getMaxItems() ?? 1)
                                                // ->minItems($this->getMinItems() ?? null)
                                                ->schema([
                                                    Forms\Components\TextInput::make('lat')
                                                        ->label(__('filament-google-maps-marker::map.actions.edit.fieldset.latitude.label'))
                                                        ->placeholder(__('filament-google-maps-marker::map.actions.edit.fieldset.latitude.placeholder'))
                                                        ->numeric()
                                                        ->maxValue(90.0000000)
                                                        ->minValue(-90.0000000)
                                                        ->required(),

                                                    Forms\Components\TextInput::make('lng')
                                                        ->label(__('filament-google-maps-marker::map.actions.edit.fieldset.longitude.label'))
                                                        ->placeholder(__('filament-google-maps-marker::map.actions.edit.fieldset.longitude.placeholder'))
                                                        ->numeric()
                                                        ->maxValue(180.0000000)
                                                        ->minValue(-180.0000000)
                                                        ->required(),
                                                ])
                                                ->columns(2),
                                        ]),
                                ]),
                        ]),
                ])
                ->action(function (array $data, $component, $livewire) {
                    // cast to number;
                    $locations = collect($data['locations'])->map(function ($location) {
                        $location['lat'] = (float) $location['lat'];
                        $location['lng'] = (float) $location['lng'];

                        return $location;
                    });

                    $component->state($locations);

                    $livewire->dispatch('update-markers-data', data: $locations);


                    // $component->emit('updateMarkersData', [
                    //     'data' => $locations,
                    // ]);
                })
        ]);
    }

    public function getCastLocations($state): array | null | string
    {
        $castType = $this->options['cast'];

        $locations = [];

        switch ($castType) {
            case 'latLngString':
                $locations = collect($state ?? [])
                    ->map(function ($location) {
                        return $location['lat'] . ',' . $location['lng'];
                    })
                    ->filter();

                if ($this->isMultiple()) {
                    return $locations->toArray();
                } else {
                    return $locations->first();
                }

                break;

            case 'latLngArray':
                $locations = collect($state ?? [])
                    ->map(function ($location) {
                        return [
                            $location['lat'],
                            $location['lng']
                        ];
                    })
                    ->filter();

                if ($this->isMultiple()) {
                    return $locations->toArray();
                } else {
                    return $locations->first();
                }

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
                foreach (collect($state)->filter()->toArray() ?? [] as $location) {
                    if (gettype($location) == 'array') {
                        if (array_key_exists('lat', $location) && array_key_exists('lng', $location)) {
                            $locations[(string) Str::uuid()] = ['lat' => (float) $location['lat'], 'lng' => (float) $location['lng']];
                            continue;
                        }
                    }

                    $separator = Str::contains($location, ',') ? ',' : (Str::contains($location, ' ') ? ' ' : null);

                    if ($separator == null) {
                        Log::error('Location cast not string correct format check string lat lng separator.');
                        //  throw new InvalidArgumentException('Location cast not string correct format check string lat lng separator.');
                    } else {
                        $location = explode($separator, $location);

                        if (count($location) == 2) {
                            $locations[(string) Str::uuid()] = ['lat' => (float) $location[0], 'lng' => (float) $location[1]];
                        } else {
                            Log::error('Incorrect location format.');
                            // throw new InvalidArgumentException('Incorrect location format.');
                        }
                    }
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
        $this->options['locationButtonText'] = $text;

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

    public function multiple(bool |   Closure $multiple = true): static
    {
        $this->options['multiple'] = $multiple;

        return $this;
    }

    public function isMultiple(): bool
    {
        return $this->evaluate($this->options['multiple']);
    }

    public function getEditAction(): Actions\Action
    {
        return $this->getAction('edit');
    }

    public function maxMarkers(int | Closure | null $count): static
    {
        $this->maxItems($count);

        if($count > 1){
            $this->multiple($count);
        }

        return $this;
    }

    public function minMarkers(int | Closure | null $count): static
    {
        $this->minItems($count);

        if($count > 1){
            $this->multiple($count);
        }

        return $this;
    }
}

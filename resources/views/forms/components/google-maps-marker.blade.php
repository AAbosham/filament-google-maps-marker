<script
    src="https://maps.googleapis.com/maps/api/js?key={{ config('filament-google-maps-marker.google_maps_key') }}&libraries=places&v=weekly&language={{ app()->getLocale() }}">
</script>

<x-dynamic-component :component="$getFieldWrapperView()" :field="$field" :id="$getId()" :label="$getLabel()" :label-sr-only="$isLabelHidden()"
    :helper-text="$getHelperText()" :hint="$getHint()" :hint-icon="$getHintIcon()" :required="$isRequired()" :state-path="$getStatePath()">

    <div wire:ignore x-data="googleMapMarker({
        value: $wire.entangle('{{ $getStatePath() }}'),
        controls: {{ $getMapControls() }},
        options: {{ $getMapOptions() }},
        statePath: '{{ $getStatePath() }}',
        maxItems: '{{ $getMaxItems() }}',
        minItems: '{{ $getMinItems() }}'
    })">
        <div x-ref="map" class="w-full" style="min-height: {{ $getMinHeight() }} ">
            @if ($isSearchBoxControlEnabled())
                <input x-ref="searchBox" type="text" placeholder="{{ $getSearchBoxPlaceholderText() }}"
                    onkeydown="if (event.keyCode == 13){  return false;}"
                    class="block w-2/6 mt-2 ml-2 transition duration-75 border-gray-300 rounded-lg shadow-sm focus:border-primary-600 focus:ring-1 focus:ring-inset focus:ring-primary-600 disabled:opacity-70" />
            @endif

            @if ($isGeolocationControlEnabled())
                <button x-ref="locationButton" onclick="return false;"
                    class="inline-flex items-center justify-center gap-1 px-4 mb-6 text-sm font-medium text-gray-800 transition-colors bg-white border border-gray-300 rounded-lg focus:outline-none focus:ring-offset-2 focus:ring-2 focus:ring-inset filament-button h-9 hover:bg-gray-50 focus:ring-primary-600 focus:text-primary-600 focus:bg-primary-50 focus:border-primary-600 filament-page-button-action">
                    <div class="w-5 h-5">
                        @svg('heroicon-o-map-pin')
                    </div <span>{{ $getLocationButtonText() }}</span>
                </button>
            @endif

            @if ($isCoordsBoxControlEnabled())
                <div x-ref="actionsBox" class="inline-flex items-center mx-1 mb-6 text-gray-800  h-9">
                    {{ $getEditAction() }}
                </div>
            @endif
        </div>
    </div>
</x-dynamic-component>

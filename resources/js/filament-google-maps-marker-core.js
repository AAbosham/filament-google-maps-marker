function googleMapMarker(config) {

    return {
        value: config.value,
        zoom: config.zoom,

        map: null,
        markers: [],
        poly: null,
        center: {
            lat: 0,
            lng: 0,
        },

        dispatchFormEvent: function (name, statePath, uuidToDelete) {
            this.$el.closest('form')?.dispatchEvent(
                new CustomEvent(name, {
                    statePath: statePath,
                    uuidToDelete: uuidToDelete,
                })
            )

            const event = new CustomEvent(name, {
                statePath: statePath,
                uuidToDelete: uuidToDelete,
            })

            this.$el.dispatchEvent(event)
        },

        drawPolyPath: function () {
            var path = [];

            for (polyPathIndex = 0; polyPathIndex < this.markers.length; polyPathIndex++) {
                var m = this.markers[polyPathIndex];
                if (m.map) {
                    path.push(m.getPosition());
                }
            }

            this.poly.setPath(path);
        },

        addMarker: function (position, key, label) {
            if (key == undefined || key == null) {
                key = 'key-' + Date.now();
            }

            let marker = new google.maps.Marker({
                map: this.map,
                position: position,
                draggable: config.options.draggable,
                label: label ?? null,
                extra: {
                    'markerId': key
                },
            });

            marker.addListener('dblclick',
                () => {
                    marker.setMap(null);

                    delete this.value[marker.extra.markerId];

                    for (dblclickRemoveMarkerIndex = 0; dblclickRemoveMarkerIndex < this.markers.length; dblclickRemoveMarkerIndex++) {
                        var m = this.markers[dblclickRemoveMarkerIndex];
                        if (m.map == null) {
                            this.markers.splice(dblclickRemoveMarkerIndex, 1);
                        }
                    }

                    this.drawPolyPath();

                });

            marker.addListener('position_changed',
                () => {
                    this.drawPolyPath();
                });

            marker.addListener('dragend',
                () => {
                    this.drawPolyPath();

                    this.value[marker.extra.markerId] = marker.getPosition().toJSON();
                });

            this.markers.push(marker);

            this.drawPolyPath()
        },

        getLastStateValue: function () {
            return this.value[this.getLastStateKey()];
        },

        getLastStateKey: function () {
            return Object.keys(this.value)[Object.keys(this.value).length - 1];
        },

        setMarkers: function () {
            if (this.value) {
                var markerLength = Object.keys(this.value).length;

                for (setMarkerIndex = 0; setMarkerIndex < markerLength; setMarkerIndex++) {

                    var key = Object.keys(this.value)[setMarkerIndex];

                    let position = this.value[Object.keys(this.value)[setMarkerIndex]];

                    var markerLabel = null;//((setMarkerIndex) + 1).toString();

                    this.addMarker(position, key, markerLabel)
                }
            }

        },

        removeMarkers: function () {
            this.markers.map(function (marker) {
                marker.setMap(null);
                marker.setVisible(false);
            });

            for (let i = 0; i < this.markers.length; i++) {
                this.markers[i].setMap(null);
                this.markers[i].setVisible(false);
            }

            this.markers = [];

            this.drawPolyPath();
        },

        init: function () {
            if (this.value) {
                this.center = this.value[Object.keys(this.value)[0]] || {
                    lat: 0,
                    lng: 0,
                }
            } else {
                this.center = {
                    lat: 0,
                    lng: 0,
                }
            };

            this.map = new google.maps.Map(this.$refs.map, {
                center: this.center,
                zoom: config.options.zoom,
                mapTypeId: config.options.mapTypeId,
                ...config.controls
            })

            this.poly = new google.maps.Polyline({
                strokeColor: '#FF0000',
                strokeOpacity: 1.0,
                strokeWeight: 3,
                geodesic: true,
                map: this.map,
            });

            this.setMarkers()

            Livewire.on('updateMarkersData', async ({
                data
            }) => {
                if (data) {
                    this.removeMarkers();

                    this.setMarkers();
                }
            })

            this.map.addListener('click', (event) => {
                if (!config.options.multiple && this.markers.length > 0) {
                    return;
                }

                if (config.maxItems > 0 && this.markers.length > 0) {
                    if (this.markers.length >= config.maxItems) {
                        return;
                    }
                }

                if (this.markers.length == 0) {
                    this.value = {};
                }

                var markerLabel = null;//(Object.keys(this.value).length + 1).toString();

                var key = 'key-' + Date.now();

                var position = event.latLng.toJSON();

                this.value[key] = position;

                this.addMarker(event.latLng.toJSON(), key, markerLabel)
            });

            if (config.options.fixMarkerOnCenter) {
                this.map.addListener('drag', (event) => {
                    markers[0].setPosition(this.map.getCenter());
                });

                this.map.addListener('dragend', (event) => {
                    markers[0].setPosition(this.map.getCenter());
                    this.value[0] = this.map.getCenter().toJSON();
                });
            }

            if (config.controls.geolocationControl) {
                const locationButton = this.$refs.locationButton;
                this.map.controls[google.maps.ControlPosition.BOTTOM_CENTER].push(locationButton);

                locationButton.addEventListener("click", () => {
                    if (navigator.geolocation) {
                        navigator.geolocation.getCurrentPosition(
                            (position) => {
                                const pos = {
                                    lat: position.coords.latitude,
                                    lng: position.coords.longitude,
                                };


                                this.addMarker(pos)

                                this.map.setCenter(pos);
                            },
                            () => {
                                console.log('Browser supports Geolocation but got error. Probably no permission granted.');
                            }
                        );
                    } else {
                        console.log('Browser doesn\'t support Geolocation');
                    }
                });
            }

            if (config.options.defaultToMyLocation && this.center.lat === 0 && this.center.lng === 0) {
                if (navigator.geolocation) {
                    navigator.geolocation.getCurrentPosition(
                        (position) => {
                            const pos = {
                                lat: position.coords.latitude,
                                lng: position.coords.longitude,
                            };

                            var key = 'key-' + Date.now();

                            let locations = {};

                            locations[key] = pos;

                            this.value = locations;

                            this.addMarker(pos)

                            this.map.setCenter(pos);
                        },
                        () => {
                            console.log('Browser supports Geolocation but got error. Probably no permission granted.');
                        }
                    );
                } else {
                    console.log('Browser doesn\'t support Geolocation');
                }
            }

            if (config.controls.searchBoxControl) {
                const input = this.$refs.searchBox;
                const searchBox = new google.maps.places.SearchBox(input);
                this.map.controls[google.maps.ControlPosition.TOP_CENTER].push(input);
                searchBox.addListener("places_changed", () => {
                    input.value = ''
                    var location = searchBox.getPlaces()[0].geometry.location

                    if (config.options.multiple || this.markers.length == 0) {

                        this.markers[0]

                        this.map.setCenter(location);
                    } else {
                        this.markers[0].setPosition(location);

                        this.map.setCenter(location);
                    }
                })
            }

            if (config.controls.coordsBoxControl) {
                actionsControl = this.$refs.actionsBox;
                this.map.controls[google.maps.ControlPosition.BOTTOM_CENTER].push(actionsControl);
            }

            this.$watch('value', () => {
                let position = this.getLastStateValue();

                if (position) {
                    this.map.setCenter(position);
                    this.map.panTo(position);
                }
            })

        },

    }
}

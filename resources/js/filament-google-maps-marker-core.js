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

            for (i = 0; i < this.markers.length; i++) {
                var m = this.markers[i];
                if (m.map) {
                    path.push(m.getPosition());
                }
            }

            this.poly.setPath(path);
        },

        addMarker: function (position, key, label) {
            if (key === undefined || key === null) {
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

                    for (i = 0; i < this.markers.length; i++) {
                        var m = this.markers[i];
                        if (m.map == null) {
                            this.markers.splice(i, 1);
                        }
                    }

                    this.drawPolyPath();

                });

            marker.addListener('position_changed',
                () => {
                    this.drawPolyPath();

                    this.value[marker.extra.markerId] = marker.getPosition().toJSON();
                });


            // if (changeCenter) {
            //     this.map.setCenter(point);
            // }

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
            for (i = 0; i < Object.keys(this.value).length; i++) {

                var key = Object.keys(this.value)[i];

                let position = this.value[Object.keys(this.value)[i]];

                var markerLabel = (i + 1).toString();

                this.addMarker(position, key, markerLabel)
            }
        },

        init: function () {
            this.center = this.value[Object.keys(this.value)[0]] || {
                lat: 0,
                lng: 0,
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


            // this.geodesicPoly.setPath(path);

            // if (markers.length > 0) {
            //     this.updateMapBinding(this.mapMarkers[0]);
            // }

            this.map.addListener('click', (event) => {
                var key = 'key-' + Date.now();
                var position = event.latLng.toJSON();
                this.value[key] = position;

                var markerLabel = (Object.keys(this.value).length + 1).toString();

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
                                // this.value[0] = pos;
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
                            // this.value[0] = pos;
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

                    this.addMarker(location)

                    this.map.setCenter(location);

                })
            }

            if (config.controls.coordsBoxControl) {
                coordsBoxControl = this.$refs.coordsBox;
                this.map.controls[google.maps.ControlPosition.LEFT_BOTTOM].push(coordsBoxControl);
            }

            this.$watch('value', () => {
                let position = this.getLastStateValue();
                // console.log('$watchvalue', this.value);
                // markers[0].setPosition(position);
                // map.panTo(position);


                if (config.controls.coordsBoxControl) {
                    coordsBoxControl.value = position.lat + ',' + position.lng;
                }
            })

        },

    }
}

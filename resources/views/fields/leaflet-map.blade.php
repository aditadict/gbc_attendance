@php
    $fieldWrapperView = $getFieldWrapperView();
    $statePath        = $getStatePath();
    $defaultLat       = $getDefaultLat();
    $defaultLng       = $getDefaultLng();
    $defaultZoom      = $getDefaultZoom();
@endphp

<x-dynamic-component :component="$fieldWrapperView" :field="$field">
    <div
        x-data="leafletMapField(@js($statePath), @js($defaultLat), @js($defaultLng), @js($defaultZoom))"
        wire:ignore
    >
        <div x-ref="map" class="w-full rounded-lg overflow-hidden" style="height:400px;z-index:1;"></div>
    </div>

    <script>
    window.leafletMapField = function (statePath, defaultLat, defaultLng, defaultZoom) {
        return {
            map:    null,
            marker: null,
            circle: null,

            async init() {
                await this._loadLeaflet();

                // Fix default marker icon paths when Leaflet is loaded from a custom path
                delete L.Icon.Default.prototype._getIconUrl;
                L.Icon.Default.mergeOptions({
                    iconUrl:       @js(asset('vendor/leaflet/images/marker-icon.png')),
                    iconRetinaUrl: @js(asset('vendor/leaflet/images/marker-icon-2x.png')),
                    shadowUrl:     @js(asset('vendor/leaflet/images/marker-shadow.png')),
                });

                const state = this.$wire.get(statePath);
                const lat   = state?.lat || defaultLat;
                const lng   = state?.lng || defaultLng;

                this.map = L.map(this.$refs.map, {
                    center:           [lat, lng],
                    zoom:             defaultZoom,
                    zoomControl:      false,
                    attributionControl: false,
                });

                L.tileLayer('https://tile.openstreetmap.org/{z}/{x}/{y}.png', {
                    maxZoom: 19,
                }).addTo(this.map);

                L.control.zoom({ position: 'topright' }).addTo(this.map);
                this._addLocateButton();

                // Place draggable marker at initial position
                this.marker = L.marker([lat, lng], { draggable: true }).addTo(this.map);
                this._redrawCircle(lat, lng);

                // Marker dragged to new position
                this.marker.on('dragend', () => {
                    const { lat, lng } = this.marker.getLatLng();
                    this._redrawCircle(lat, lng);
                    this.$wire.set(statePath, { lat, lng });
                });

                // Click anywhere on map to move marker there
                this.map.on('click', (e) => {
                    this.marker.setLatLng(e.latlng);
                    this._redrawCircle(e.latlng.lat, e.latlng.lng);
                    this.$wire.set(statePath, { lat: e.latlng.lat, lng: e.latlng.lng });
                });

                // Keep radius circle in sync when radius field changes
                try {
                    this.$wire.watch('data.radius', () => {
                        const { lat, lng } = this.marker.getLatLng();
                        this._redrawCircle(lat, lng);
                    });
                } catch {}
            },

            _getRadius() {
                try { return parseFloat(this.$wire.get('data.radius')) || 0; } catch { return 0; }
            },

            _redrawCircle(lat, lng) {
                const r = this._getRadius();
                if (!r) {
                    if (this.circle) { this.circle.remove(); this.circle = null; }
                    return;
                }
                if (this.circle) {
                    this.circle.setLatLng([lat, lng]).setRadius(r);
                } else {
                    this.circle = L.circle([lat, lng], {
                        radius:      r,
                        color:       '#3b82f6',
                        fillColor:   '#93c5fd',
                        fillOpacity: 0.15,
                        weight:      2,
                        dashArray:   '6 4',
                    }).addTo(this.map);
                }
            },

            _addLocateButton() {
                const LocateControl = L.Control.extend({
                    options: { position: 'topright' },
                    onAdd: (map) => {
                        const div = L.DomUtil.create('div', 'leaflet-bar leaflet-control');
                        const a   = L.DomUtil.create('a', '', div);
                        a.href  = '#';
                        a.title = 'Gunakan Lokasi Saya';
                        a.style.cssText = 'display:flex;align-items:center;justify-content:center;width:30px;height:30px;';
                        a.innerHTML = `<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16"
                            viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                            stroke-linecap="round" stroke-linejoin="round">
                            <circle cx="12" cy="12" r="3"/>
                            <line x1="12" y1="2"  x2="12" y2="6"/>
                            <line x1="12" y1="18" x2="12" y2="22"/>
                            <line x1="2"  y1="12" x2="6"  y2="12"/>
                            <line x1="18" y1="12" x2="22" y2="12"/>
                        </svg>`;
                        L.DomEvent.on(a, 'click', L.DomEvent.stop).on(a, 'click', () => {
                            if (!navigator.geolocation) return;
                            a.style.opacity = '0.4';
                            navigator.geolocation.getCurrentPosition(
                                pos => {
                                    const latlng = L.latLng(pos.coords.latitude, pos.coords.longitude);
                                    map.setView(latlng, 17);
                                    this.marker.setLatLng(latlng);
                                    this._redrawCircle(latlng.lat, latlng.lng);
                                    this.$wire.set(statePath, { lat: latlng.lat, lng: latlng.lng });
                                    a.style.opacity = '1';
                                },
                                err => { a.style.opacity = '1'; console.warn('Geolocation:', err.message); },
                                { enableHighAccuracy: false, timeout: 20000, maximumAge: 300000 }
                            );
                        });
                        return div;
                    },
                });
                new LocateControl().addTo(this.map);
            },

            async _loadLeaflet() {
                if (window.L) return;
                const cssHref = @js(asset('vendor/leaflet/leaflet.css'));
                const jsSrc   = @js(asset('vendor/leaflet/leaflet.js'));
                if (!document.getElementById('leaflet-css')) {
                    const l = document.createElement('link');
                    l.id = 'leaflet-css'; l.rel = 'stylesheet'; l.href = cssHref;
                    document.head.appendChild(l);
                }
                await new Promise(resolve => {
                    if (window.L) { resolve(); return; }
                    const existing = document.getElementById('leaflet-js');
                    if (existing) {
                        const poll = setInterval(() => {
                            if (window.L) { clearInterval(poll); resolve(); }
                        }, 30);
                    } else {
                        const s = document.createElement('script');
                        s.id = 'leaflet-js'; s.src = jsSrc; s.onload = resolve;
                        document.head.appendChild(s);
                    }
                });
            },
        };
    };
    </script>
</x-dynamic-component>

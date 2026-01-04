/**
 * DASHBOARD-MAP.JS
 * Handles map initialization, markers, and map-related interactions
 */

(function () {
    'use strict';

    // Wait for DOM to be ready
    document.addEventListener('DOMContentLoaded', function () {

        if (!window.APP_CONFIG) {
            console.error('APP_CONFIG not found');
            return;
        }

        const { parkingData, BASEURL } = window.APP_CONFIG;

        // Initialize Map
        // Check for saved position in session storage to enable smooth transitions across reloads
        const savedLat = sessionStorage.getItem('spark_map_lat');
        const savedLng = sessionStorage.getItem('spark_map_lng');
        const savedZoom = sessionStorage.getItem('spark_map_zoom');

        // Default to Yogyakarta if no saved state
        const initialLat = savedLat ? parseFloat(savedLat) : -7.7956;
        const initialLng = savedLng ? parseFloat(savedLng) : 110.3695;
        const initialZoom = savedZoom ? parseInt(savedZoom) : 13;

        const map = L.map('map', {
            zoomControl: true,
            scrollWheelZoom: true,
            zoomAnimation: true
        }).setView([initialLat, initialLng], initialZoom);

        // Save position on move end
        map.on('moveend', function () {
            const center = map.getCenter();
            sessionStorage.setItem('spark_map_lat', center.lat);
            sessionStorage.setItem('spark_map_lng', center.lng);
            sessionStorage.setItem('spark_map_zoom', map.getZoom());
        });

        L.tileLayer('https://{s}.basemaps.cartocdn.com/rastertiles/voyager/{z}/{x}/{y}{r}.png', {
            attribution: '© OpenStreetMap © CARTO',
            maxZoom: 19
        }).addTo(map);

        const markers = {};

        // Utility function
        function formatRupiah(amount) {
            return 'Rp ' + new Intl.NumberFormat('id-ID').format(amount);
        }

        // Render markers with SPARK branding
        parkingData.forEach(spot => {
            if (spot.latitude && spot.longitude) {
                const priceMarker = L.divIcon({
                    className: 'spark-price-marker',
                    html: `<div class="marker-pill">${formatRupiah(spot.harga_per_jam)}</div>`,
                    iconSize: [90, 34],
                    iconAnchor: [45, 17]
                });

                const marker = L.marker([spot.latitude, spot.longitude], { icon: priceMarker })
                    .addTo(map);

                marker.on('click', function () {
                    if (window.openBookingModal) {
                        window.openBookingModal(spot.id_tempat);
                    }
                });

                markers[spot.id_tempat] = marker;
            }
        });

        // Expose to global scope
        window.parkingMap = map;
        window.parkingMarkers = markers;
        window.formatRupiah = formatRupiah;

        // City Coordinates for Map Panning
        const cityCoordinates = {
            'Jakarta': { lat: -6.2088, lng: 106.8456, zoom: 12 },
            'Bandung': { lat: -6.9175, lng: 107.6191, zoom: 13 },
            'Surabaya': { lat: -7.2575, lng: 112.7521, zoom: 12 },
            'Yogyakarta': { lat: -7.7956, lng: 110.3695, zoom: 13 },
            'Semarang': { lat: -6.9667, lng: 110.4167, zoom: 13 },
            'Malang': { lat: -7.9666, lng: 112.6326, zoom: 13 },
            'Solo': { lat: -7.5755, lng: 110.8243, zoom: 14 },
            'Cirebon': { lat: -6.7320, lng: 108.5523, zoom: 13 }
        };

        // Check URL for city parameter and pan map
        const urlParams = new URLSearchParams(window.location.search);
        const cityParam = urlParams.get('city');

        if (cityParam && cityCoordinates[cityParam]) {
            const coords = cityCoordinates[cityParam];
            // Use a slight timeout to ensure map is ready and layout settled
            setTimeout(() => {
                map.flyTo([coords.lat, coords.lng], coords.zoom, {
                    animate: true,
                    duration: 1.5
                });
            }, 500);
        } else if (parkingData.length > 0) {
            // Fallback to fitBounds if no specific city selected or city not found
            const bounds = L.latLngBounds(
                parkingData
                    .filter(s => s.latitude && s.longitude)
                    .map(s => [s.latitude, s.longitude])
            );
            if (bounds.isValid()) {
                map.fitBounds(bounds, { padding: [50, 50] });
            }
        }
    });

})();
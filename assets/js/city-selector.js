/**
 * CITY SELECTOR & DATE FILTER
 * Handles city selection, map panning, and search functionality
 */

// Java Cities Coordinates
const javaCities = {
    'Jakarta': { lat: -6.2088, lng: 106.8456, zoom: 12 },
    'Bandung': { lat: -6.9175, lng: 107.6191, zoom: 13 },
    'Semarang': { lat: -6.9667, lng: 110.4167, zoom: 13 },
    'Surabaya': { lat: -7.2575, lng: 112.7521, zoom: 12 },
    'Yogyakarta': { lat: -7.7956, lng: 110.3695, zoom: 13 },
    'Malang': { lat: -7.9666, lng: 112.6326, zoom: 13 },
    'Solo': { lat: -7.5705, lng: 110.8285, zoom: 13 },
    'Cirebon': { lat: -6.7063, lng: 108.5571, zoom: 13 }
};

// Wait for DOM and map to be ready
document.addEventListener('DOMContentLoaded', function () {
    const citySelect = document.getElementById('citySelect');
    const dateSelect = document.getElementById('dateSelect');
    const searchBtn = document.getElementById('searchBtn');

    // Check if map exists (defined in dashboard-map.js)
    const checkMapReady = setInterval(function () {
        if (typeof map !== 'undefined') {
            clearInterval(checkMapReady);
            initializeCitySelector();
        }
    }, 100);

    function initializeCitySelector() {
        // Pan to city on page load if city filter is active
        const urlParams = new URLSearchParams(window.location.search);
        const selectedCity = urlParams.get('city');

        if (selectedCity && javaCities[selectedCity]) {
            const coords = javaCities[selectedCity];
            map.setView([coords.lat, coords.lng], coords.zoom);
        }

        // City select change handler - pan map immediately
        citySelect.addEventListener('change', function () {
            const city = this.value;
            if (city && javaCities[city]) {
                const coords = javaCities[city];
                map.flyTo([coords.lat, coords.lng], coords.zoom, {
                    duration: 1.5,
                    easeLinearity: 0.5
                });
            } else {
                // Reset to default Java view
                map.setView([-7.5, 110.0], 8);
            }
        });

        // Search button click handler
        searchBtn.addEventListener('click', function () {
            performSearch();
        });

        // Enter key on date input
        dateSelect.addEventListener('keypress', function (e) {
            if (e.key === 'Enter') {
                performSearch();
            }
        });
    }

    function performSearch() {
        const city = citySelect.value;
        const date = dateSelect.value;

        // Build URL with filters
        const params = new URLSearchParams(window.location.search);

        // Update or remove city parameter
        if (city) {
            params.set('city', city);
        } else {
            params.delete('city');
        }

        // Update or remove date parameter
        if (date) {
            params.set('date', date);
        } else {
            params.delete('date');
        }

        // Reload page with new filters
        const newUrl = `${window.location.pathname}?${params.toString()}`;
        window.location.href = newUrl;
    }
});

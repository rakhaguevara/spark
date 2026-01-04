/**
 * SEARCH POPUP - SIMPLE VERSION
 * Clean, minimal interactions
 */

document.addEventListener('DOMContentLoaded', function () {
    const searchInputBtn = document.getElementById('searchInputBtn');
    const searchPopup = document.getElementById('searchPopup');
    const searchInputText = document.getElementById('searchInputText');
    const cityChips = document.querySelectorAll('.city-chip');
    const citySearch = document.getElementById('citySearch');
    const popupDateInput = document.getElementById('popupDateInput');
    const btnApply = document.getElementById('btnApply');
    const btnReset = document.getElementById('btnReset');
    const selectedCityInput = document.getElementById('selectedCity');
    const selectedDateInput = document.getElementById('selectedDate');

    let tempSelectedCity = selectedCityInput.value;
    let tempSelectedDate = selectedDateInput.value;

    // Toggle popup
    searchInputBtn.addEventListener('click', function (e) {
        e.stopPropagation();
        searchPopup.classList.toggle('show');
        searchInputBtn.classList.toggle('active');
    });

    // Close on outside click
    document.addEventListener('click', function (e) {
        if (!searchInputBtn.contains(e.target) && !searchPopup.contains(e.target)) {
            searchPopup.classList.remove('show');
            searchInputBtn.classList.remove('active');
        }
    });

    // City chip selection
    cityChips.forEach(chip => {
        if (chip.dataset.city === tempSelectedCity) {
            chip.classList.add('active');
        }

        chip.addEventListener('click', function () {
            cityChips.forEach(c => c.classList.remove('active'));
            this.classList.add('active');
            tempSelectedCity = this.dataset.city;
        });
    });

    // City search filter
    citySearch.addEventListener('input', function () {
        const searchTerm = this.value.toLowerCase();
        cityChips.forEach(chip => {
            const cityName = chip.dataset.city.toLowerCase();
            chip.style.display = cityName.includes(searchTerm) ? 'block' : 'none';
        });
    });

    // Date selection
    popupDateInput.addEventListener('change', function () {
        tempSelectedDate = this.value;
    });

    // Apply button
    btnApply.addEventListener('click', function () {
        selectedCityInput.value = tempSelectedCity;
        selectedDateInput.value = tempSelectedDate;

        // Update display
        if (tempSelectedCity || tempSelectedDate) {
            const cityText = tempSelectedCity || 'City';
            const dateText = tempSelectedDate ? formatDate(tempSelectedDate) : 'Date';
            searchInputText.textContent = `${cityText} • ${dateText}`;
        } else {
            searchInputText.textContent = 'Select city and date';
        }

        // Close popup
        searchPopup.classList.remove('show');
        searchInputBtn.classList.remove('active');

        // Reload with filters
        const params = new URLSearchParams(window.location.search);
        if (tempSelectedCity) params.set('city', tempSelectedCity);
        else params.delete('city');
        if (tempSelectedDate) params.set('date', tempSelectedDate);
        else params.delete('date');

        window.location.href = `${window.location.pathname}?${params.toString()}`;
    });

    // Reset button
    btnReset.addEventListener('click', function () {
        tempSelectedCity = '';
        tempSelectedDate = '';
        cityChips.forEach(c => c.classList.remove('active'));
        popupDateInput.value = '';
        citySearch.value = '';
        cityChips.forEach(chip => chip.style.display = 'block');
        searchInputText.textContent = 'Select city and date';
    });

    // Format date
    function formatDate(dateString) {
        if (!dateString) return 'Date';
        const date = new Date(dateString);
        const months = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
        return `${date.getDate()} ${months[date.getMonth()]} ${date.getFullYear()}`;
    }

    // Map integration
    const checkMapReady = setInterval(function () {
        if (typeof map !== 'undefined' && typeof javaCities !== 'undefined') {
            clearInterval(checkMapReady);
            if (tempSelectedCity && javaCities[tempSelectedCity]) {
                const coords = javaCities[tempSelectedCity];
                map.setView([coords.lat, coords.lng], coords.zoom);
            }
        }
    }, 100);
});

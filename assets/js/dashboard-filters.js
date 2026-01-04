/**
 * DASHBOARD FILTERS CONTROLLER
 * Handles vehicle type filtering and UI state
 */

document.addEventListener('DOMContentLoaded', function () {
    // Elements
    const filterPills = document.querySelectorAll('.filter-pill');

    // Get current filter params
    const urlParams = new URLSearchParams(window.location.search);
    const vehicleType = urlParams.get('vehicle_type');
    const facilityType = urlParams.get('facility');

    // Initialize Active States
    if (vehicleType) {
        // If vehicle type is set (Motor/Mobil), highlight the Vehicle Type button
        const btn = document.querySelector('.filter-pill[data-filter="vehicle"]');
        if (btn) {
            btn.classList.add('active');
            // Update text to show selected type
            btn.innerHTML = `<i class="fas fa-car"></i> ${vehicleType}`;
        }
    } else if (facilityType) {
        // If facility is set
        const btn = document.querySelector(`.filter-pill[data-filter="${facilityType}"]`);
        if (btn) btn.classList.add('active');
    } else {
        // Default to All
        const btnAll = document.querySelector('.filter-pill[data-filter="all"]');
        if (btnAll) btnAll.classList.add('active');
    }

    // Add click listeners
    filterPills.forEach(pill => {
        pill.addEventListener('click', function (e) {
            e.stopPropagation(); // Prevent bubbling
            const filterKey = this.getAttribute('data-filter');

            if (filterKey === 'all') {
                resetFilters();
            } else if (filterKey === 'vehicle') {
                showVehicleDropdown(this);
            } else {
                toggleFilter(filterKey);
            }
        });
    });

    // Close dropdown on outside click
    document.addEventListener('click', function () {
        const dropdown = document.querySelector('.vehicle-dropdown-menu');
        if (dropdown) dropdown.remove();
    });

    /**
     * Show vehicle type selection dropdown
     */
    function showVehicleDropdown(button) {
        // Remove existing
        const existing = document.querySelector('.vehicle-dropdown-menu');
        if (existing) existing.remove();

        const menu = document.createElement('div');
        menu.className = 'vehicle-dropdown-menu';
        menu.innerHTML = `
            <div class="menu-item" onclick="applyVehicleFilter('Mobil')"><i class="fas fa-car"></i> Mobil</div>
            <div class="menu-item" onclick="applyVehicleFilter('Motor')"><i class="fas fa-motorcycle"></i> Motor</div>
        `;

        // Style
        menu.style.position = 'absolute';
        menu.style.top = (button.offsetTop + button.offsetHeight + 8) + 'px';
        menu.style.left = button.offsetLeft + 'px';
        menu.style.background = 'white';
        menu.style.border = '1px solid #e5e7eb';
        menu.style.borderRadius = '8px';
        menu.style.boxShadow = '0 4px 12px rgba(0,0,0,0.1)';
        menu.style.padding = '8px 0';
        menu.style.zIndex = '1000';
        menu.style.minWidth = '140px';

        // Add to parent
        button.parentNode.appendChild(menu);

        // Styling for items
        const items = menu.querySelectorAll('.menu-item');
        items.forEach(item => {
            item.style.padding = '8px 16px';
            item.style.cursor = 'pointer';
            item.style.fontSize = '14px';
            item.style.display = 'flex';
            item.style.alignItems = 'center';
            item.style.gap = '8px';
            item.style.color = '#333';
            item.style.transition = 'background 0.2s';

            item.onmouseover = () => item.style.background = '#f9fafb';
            item.onmouseout = () => item.style.background = 'white';
        });
    }

    /**
     * Apply standard single-select filter (Self Park / Garage)
     */
    function toggleFilter(filterKey) {
        const params = new URLSearchParams(window.location.search);

        // If clicking the same facility, toggle off? Or switch?
        // Let's assume single select for facility: either self-park OR garage.
        if (params.get('facility') === filterKey) {
            params.delete('facility');
        } else {
            params.set('facility', filterKey);
            // Remove vehicle filter if strict? No, allow combination.
        }
        params.delete('page');
        window.location.href = `${window.location.pathname}?${params.toString()}`;
    }

    /**
     * Reset all filters
     */
    function resetFilters() {
        // Keep search query? Maybe.
        const params = new URLSearchParams(window.location.search);
        params.delete('vehicle_type');
        params.delete('facility');
        params.delete('page');
        window.location.href = `${window.location.pathname}?${params.toString()}`;
    }
});

// Global function for dropdown onclick
window.applyVehicleFilter = function (type) {
    const params = new URLSearchParams(window.location.search);
    params.set('vehicle_type', type);
    params.delete('page');
    window.location.href = `${window.location.pathname}?${params.toString()}`;
};

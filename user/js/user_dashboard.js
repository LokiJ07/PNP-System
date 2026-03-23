// =====================================================
// FILE: user/js/user_dashboard.js
// PURPOSE: All JavaScript functions for user dashboard
// UPDATED: Added collapsible map feature with PNP color scheme
// =====================================================

// ==================== MOBILE MENU FUNCTIONS ====================
const sidebar = document.getElementById('sidebar');
const menuBtn = document.getElementById('mobileMenuBtn');
const closeBtn = document.getElementById('closeSidebar');
const overlay = document.getElementById('menuOverlay');

function openMobileMenu() {
    sidebar.classList.add('open');
    overlay.classList.remove('hidden');
    document.body.style.overflow = 'hidden';
}

function closeMobileMenu() {
    sidebar.classList.remove('open');
    overlay.classList.add('hidden');
    document.body.style.overflow = '';
}

if (menuBtn) menuBtn.addEventListener('click', openMobileMenu);
if (closeBtn) closeBtn.addEventListener('click', closeMobileMenu);
if (overlay) overlay.addEventListener('click', closeMobileMenu);
window.addEventListener('resize', function() { 
    if (window.innerWidth >= 768) closeMobileMenu(); 
});

// ==================== COLLAPSIBLE MAP FUNCTION ====================
let mapCollapsed = false;

function toggleMap() {
    const mapContainer = document.getElementById('mapContent');
    const mapElement = document.getElementById('map');
    const toggleIcon = document.getElementById('mapToggleIcon');
    const toggleText = document.getElementById('mapToggleText');
    const mapContainerDiv = document.querySelector('.map-container');
    
    if (!mapCollapsed) {
        // Collapse map with animation
        mapContainer.style.display = 'none';
        toggleIcon.className = 'fas fa-chevron-down';
        toggleText.textContent = 'Expand Map';
        mapCollapsed = true;
        
        // Add collapsed class for styling
        if (mapContainerDiv) {
            mapContainerDiv.classList.add('collapsed');
        }
    } else {
        // Expand map
        mapContainer.style.display = 'block';
        toggleIcon.className = 'fas fa-chevron-up';
        toggleText.textContent = 'Collapse Map';
        mapCollapsed = false;
        
        // Remove collapsed class
        if (mapContainerDiv) {
            mapContainerDiv.classList.remove('collapsed');
        }
        
        // Refresh map after expansion (important for proper rendering)
        setTimeout(() => {
            if (map) {
                map.invalidateSize();
                // Optional: recenter to current view
                const currentCenter = map.getCenter();
                map.setView([currentCenter.lat, currentCenter.lng], map.getZoom());
            }
        }, 300);
    }
}

// ==================== MAP VARIABLES ====================
let map;
let marker;
let userMarker;
let currentLat = 8.366379;
let currentLng = 124.864432;
let currentLayer = 'street';

// Map layer definitions with PNP color theme
const mapLayers = {
    street: L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors',
        maxZoom: 19
    }),
    satellite: L.tileLayer('https://server.arcgisonline.com/ArcGIS/rest/services/World_Imagery/MapServer/tile/{z}/{y}/{x}', {
        attribution: 'Tiles &copy; Esri &mdash; Source: Esri, i-cubed, USDA, USGS, AEX, GeoEye, Getmapping, Aerogrid, IGN, IGP, UPR-EGP, and the GIS User Community',
        maxZoom: 20,
        maxNativeZoom: 19
    }),
    terrain: L.tileLayer('https://{s}.tile.opentopomap.org/{z}/{x}/{y}.png', {
        attribution: 'Map data: &copy; <a href="https://www.opentopomap.org">OpenTopoMap</a> contributors',
        maxZoom: 17
    }),
    hybrid: L.layerGroup([
        L.tileLayer('https://server.arcgisonline.com/ArcGIS/rest/services/World_Imagery/MapServer/tile/{z}/{y}/{x}', {
            attribution: 'Tiles &copy; Esri',
            maxZoom: 20,
            maxNativeZoom: 19
        }),
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '&copy; OpenStreetMap',
            maxZoom: 19,
            opacity: 0.5
        })
    ])
};

// Barangay coordinates (will be populated from PHP)
const barangayCoords = {};

// ==================== MAP FUNCTIONS ====================
function initMap(barangaysData) {
    if (!document.getElementById('map')) return;
    
    Object.assign(barangayCoords, barangaysData);
    
    let zoomLevel = window.innerWidth < 540 ? 11 : 12;
    
    map = L.map('map', {
        layers: [mapLayers.street]
    }).setView([currentLat, currentLng], zoomLevel);

    // Add scale control
    L.control.scale({ imperial: false, metric: true }).addTo(map);
    
    // Add custom PNP styled attribution
    map.attributionControl.setPrefix('PNP Manolo Fortich');

    // Geocoder with custom styling
    L.Control.geocoder({
        defaultMarkGeocode: false,
        placeholder: 'Search location...',
        errorMessage: 'Location not found',
        showResultIcons: true,
        collapsed: false,
        position: 'topright'
    }).on('markgeocode', function(e) {
        const latlng = e.geocode.center;
        map.setView(latlng, 16);
        placeMarker(latlng.lat, latlng.lng);
        reverseGeocode(latlng.lat, latlng.lng);
        findNearestBarangay(latlng.lat, latlng.lng);
    }).addTo(map);

    map.on('click', function(e) {
        placeMarker(e.latlng.lat, e.latlng.lng);
        reverseGeocode(e.latlng.lat, e.latlng.lng);
        findNearestBarangay(e.latlng.lat, e.latlng.lng);
    });

    // Handle orientation changes
    window.addEventListener('orientationchange', function() {
        setTimeout(() => map.invalidateSize(), 200);
    });
    
    // Add layer control with PNP styling
    const layerControl = L.control.layers({
        '🗺️ Street Map': mapLayers.street,
        '🛰️ Satellite': mapLayers.satellite,
        '⛰️ Terrain': mapLayers.terrain,
        '🗺️ Hybrid': mapLayers.hybrid
    }, null, { position: 'topright' }).addTo(map);
}

function changeMapLayer(layerType) {
    if (!map) return;
    
    map.eachLayer(function(layer) {
        if (layer instanceof L.TileLayer || layer instanceof L.LayerGroup) {
            map.removeLayer(layer);
        }
    });
    
    if (layerType === 'street') {
        mapLayers.street.addTo(map);
        map.setMaxZoom(19);
    } else if (layerType === 'satellite') {
        mapLayers.satellite.addTo(map);
        map.setMaxZoom(20);
    } else if (layerType === 'terrain') {
        mapLayers.terrain.addTo(map);
        map.setMaxZoom(17);
    } else if (layerType === 'hybrid') {
        mapLayers.hybrid.addTo(map);
        map.setMaxZoom(20);
    }
    
    currentLayer = layerType;
}

function findNearestBarangay(lat, lng) {
    let nearestBarangay = null;
    let minDistance = Infinity;
    
    for (let id in barangayCoords) {
        const b = barangayCoords[id];
        const distance = calculateDistance(lat, lng, b.lat, b.lng);
        
        if (distance < minDistance) {
            minDistance = distance;
            nearestBarangay = { id: id, name: b.name };
        }
    }
    
    if (nearestBarangay && minDistance < 2) {
        document.getElementById('selectedBarangayId').value = nearestBarangay.id;
        const select = document.getElementById('barangaySelect');
        if (select) select.value = nearestBarangay.id;
    }
}

function calculateDistance(lat1, lon1, lat2, lon2) {
    const R = 6371;
    const dLat = (lat2 - lat1) * Math.PI / 180;
    const dLon = (lon2 - lon1) * Math.PI / 180;
    const a = 
        Math.sin(dLat/2) * Math.sin(dLat/2) +
        Math.cos(lat1 * Math.PI / 180) * Math.cos(lat2 * Math.PI / 180) * 
        Math.sin(dLon/2) * Math.sin(dLon/2);
    const c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1-a));
    return R * c;
}

function placeMarker(lat, lng) {
    if (marker) {
        marker.setLatLng([lat, lng]);
    } else {
        marker = L.marker([lat, lng], {
            icon: L.divIcon({
                className: 'location-marker',
                html: '<div class="location-marker"></div>',
                iconSize: [20, 20]
            })
        }).addTo(map).bindPopup('📍 Selected Location');
    }
    
    document.getElementById('selectedLat').value = lat.toFixed(6);
    document.getElementById('selectedLng').value = lng.toFixed(6);
    
    document.getElementById('locationInfo').classList.remove('hidden');
    document.getElementById('locationText').innerHTML = `<i class="fas fa-map-marker-alt text-[#FFD700] mr-1"></i> Selected: ${lat.toFixed(6)}, ${lng.toFixed(6)}`;
    document.getElementById('coordinatesText').innerHTML = `📌 Lat: ${lat.toFixed(6)}, Long: ${lng.toFixed(6)}`;
    
    getElevation(lat, lng);
}

function getElevation(lat, lng) {
    fetch(`https://api.open-elevation.com/api/v1/lookup?locations=${lat},${lng}`)
        .then(response => response.json())
        .then(data => {
            if (data.results && data.results[0]) {
                const elevation = data.results[0].elevation;
                document.getElementById('elevationText').innerHTML = `⛰️ Elevation: ${Math.round(elevation)}m`;
            }
        })
        .catch(() => {
            document.getElementById('elevationText').innerHTML = `⛰️ Elevation: Not available`;
        });
}

function reverseGeocode(lat, lng) {
    fetch(`https://nominatim.openstreetmap.org/reverse?format=json&lat=${lat}&lon=${lng}&zoom=18&addressdetails=1`)
        .then(response => response.json())
        .then(data => {
            let locationName = data.display_name || `${lat.toFixed(6)}, ${lng.toFixed(6)}`;
            document.getElementById('specificLocation').value = locationName.substring(0, 100);
            
            if (marker) {
                marker.bindPopup(`
                    <b>📍 Selected Location</b><br>
                    ${locationName.substring(0, 80)}
                `).openPopup();
            }
        })
        .catch(() => {
            document.getElementById('specificLocation').value = `${lat.toFixed(6)}, ${lng.toFixed(6)}`;
        });
}

function zoomToBarangay(select) {
    const barangayId = select.value;
    if (barangayId && barangayCoords[barangayId]) {
        const coords = barangayCoords[barangayId];
        map.setView([coords.lat, coords.lng], 16);
        placeMarker(coords.lat, coords.lng);
        document.getElementById('selectedBarangayId').value = barangayId;
        document.getElementById('specificLocation').value = coords.name + ', Manolo Fortich, Bukidnon';
        
        // Show success message
        const locationInfo = document.getElementById('locationInfo');
        if (locationInfo) {
            locationInfo.classList.remove('hidden');
            document.getElementById('locationText').innerHTML = `<i class="fas fa-check-circle text-green-500 mr-1"></i> Barangay: ${coords.name}`;
        }
    }
}

function getUserLocation() {
    if (navigator.geolocation) {
        document.getElementById('locationInfo').classList.remove('hidden');
        document.getElementById('locationText').innerHTML = '<i class="fas fa-spinner fa-spin mr-1"></i> Getting your exact location...';
        
        navigator.geolocation.getCurrentPosition(
            function(position) {
                const lat = position.coords.latitude;
                const lng = position.coords.longitude;
                const accuracy = position.coords.accuracy;
                
                map.setView([lat, lng], 18);
                
                if (userMarker) map.removeLayer(userMarker);
                
                userMarker = L.marker([lat, lng], {
                    icon: L.divIcon({
                        className: 'user-location-marker',
                        html: '<div class="user-location-marker"></div>',
                        iconSize: [20, 20]
                    })
                }).addTo(map).bindPopup(`
                    <b>👮 Your Location</b><br>
                    Accuracy: ${accuracy.toFixed(1)}m<br>
                    <span class="text-xs text-green-600">PNP On Duty</span>
                `).openPopup();
                
                placeMarker(lat, lng);
                reverseGeocode(lat, lng);
                findNearestBarangay(lat, lng);
                document.getElementById('gps_accuracy').value = accuracy;
                document.getElementById('locationText').innerHTML = `<i class="fas fa-location-dot text-green-500 mr-1"></i> Your location (accuracy: ${accuracy.toFixed(1)}m)`;
                setPhilippineDateTime();
            },
            function(error) {
                let msg = 'Location error: ';
                switch(error.code) {
                    case error.PERMISSION_DENIED: msg += 'Please allow location access for accurate reporting.'; break;
                    case error.POSITION_UNAVAILABLE: msg += 'Location unavailable. Please select manually on map.'; break;
                    case error.TIMEOUT: msg += 'Request timed out. Please try again.'; break;
                    default: msg += 'Unknown error. Please select location manually.';
                }
                alert(msg);
                document.getElementById('locationInfo').classList.add('hidden');
            },
            { enableHighAccuracy: true, timeout: 10000, maximumAge: 0 }
        );
    } else {
        alert('Geolocation is not supported by your browser. Please select location manually on the map.');
    }
}

function resetMapView() {
    map.setView([8.366379, 124.864432], 12);
    if (marker) map.removeLayer(marker);
    if (userMarker) map.removeLayer(userMarker);
    marker = null;
    userMarker = null;
    document.getElementById('specificLocation').value = '';
    document.getElementById('locationInfo').classList.add('hidden');
    document.getElementById('selectedLat').value = '';
    document.getElementById('selectedLng').value = '';
    document.getElementById('selectedBarangayId').value = '';
    document.getElementById('barangaySelect').value = '';
    document.getElementById('elevationText').innerHTML = '';
    setPhilippineDateTime();
    
    if (currentLayer !== 'street') {
        const layerSelect = document.getElementById('mapLayerSelect');
        if (layerSelect) layerSelect.value = 'street';
        changeMapLayer('street');
    }
}

// ==================== FORM FUNCTIONS ====================
function setPhilippineDateTime() {
    const now = new Date();
    const phTime = new Date(now.getTime() + (8 * 60 * 60 * 1000));
    
    const year = phTime.getUTCFullYear();
    const month = String(phTime.getUTCMonth() + 1).padStart(2, '0');
    const day = String(phTime.getUTCDate()).padStart(2, '0');
    const phDate = `${year}-${month}-${day}`;
    
    const hours = String(phTime.getUTCHours()).padStart(2, '0');
    const minutes = String(phTime.getUTCMinutes()).padStart(2, '0');
    const phTimeStr = `${hours}:${minutes}`;
    
    const dateField = document.getElementById('activity_date');
    const timeField = document.getElementById('activity_time');
    
    if (dateField) dateField.value = phDate;
    if (timeField) timeField.value = phTimeStr;
}

function validatePhotoUpload(input) {
    const files = input.files;
    const messageEl = document.getElementById('photoUploadMessage');
    let totalSize = 0;
    
    if (files.length > 5) {
        messageEl.innerHTML = '<span class="text-red-500"><i class="fas fa-exclamation-circle mr-1"></i> Maximum 5 photos allowed</span>';
        input.value = '';
        return;
    }
    
    for (let i = 0; i < files.length; i++) {
        totalSize += files[i].size;
    }
    
    const totalSizeMB = totalSize / (1024 * 1024);
    
    if (totalSizeMB > 15) {
        messageEl.innerHTML = '<span class="text-red-500"><i class="fas fa-exclamation-circle mr-1"></i> Total file size must be less than 15MB</span>';
        input.value = '';
    } else {
        messageEl.innerHTML = `<span class="text-green-500"><i class="fas fa-check-circle mr-1"></i> Selected ${files.length} file(s) (${totalSizeMB.toFixed(2)}MB)</span>`;
    }
}

function toggleActivityFields(activityType) {
    // Hide all fields first
    const fields = [
        'personnelField', 'vehicleField', 'checkpointFields', 
        'oplanBakalFields', 'oplanSitaFields', 'patrolViolationFields'
    ];
    fields.forEach(field => {
        const element = document.getElementById(field);
        if (element) element.classList.add('hidden');
    });
    
    // Show personnel field for all except checkpoint
    if (activityType && activityType !== 'checkpoint') {
        const personnelField = document.getElementById('personnelField');
        if (personnelField) personnelField.classList.remove('hidden');
    }
    
    // Show vehicle field for mobile patrols
    if (activityType === 'Mobile Patrol' || activityType === 'Motorcycle Patrol') {
        const vehicleField = document.getElementById('vehicleField');
        if (vehicleField) vehicleField.classList.remove('hidden');
    }
    
    // Show appropriate fields based on type
    if (activityType === 'checkpoint') {
        const checkpointFields = document.getElementById('checkpointFields');
        if (checkpointFields) checkpointFields.classList.remove('hidden');
    }
    
    if (activityType === 'Oplan Bakal') {
        const oplanBakalFields = document.getElementById('oplanBakalFields');
        if (oplanBakalFields) oplanBakalFields.classList.remove('hidden');
    }
    
    if (activityType === 'Oplan Sita') {
        const oplanSitaFields = document.getElementById('oplanSitaFields');
        if (oplanSitaFields) oplanSitaFields.classList.remove('hidden');
    }
    
    // Patrols can also have violations
    if (activityType && activityType.includes('Patrol')) {
        const patrolViolationFields = document.getElementById('patrolViolationFields');
        if (patrolViolationFields) patrolViolationFields.classList.remove('hidden');
    }
}

// ==================== CONTRABAND CALCULATION ====================
function calculateContrabandKg() {
    const quantity = parseFloat(document.getElementById('contraband_quantity')?.value) || 0;
    const unit = document.getElementById('contraband_unit')?.value || 'kg';
    let kgValue = 0;
    
    switch(unit) {
        case 'kg':
            kgValue = quantity;
            break;
        case 'g':
            kgValue = quantity / 1000;
            break;
        case 'mg':
            kgValue = quantity / 1000000;
            break;
        default:
            kgValue = 0;
    }
    
    const kgField = document.getElementById('contraband_kg');
    if (kgField) {
        kgField.value = kgValue.toFixed(6);
    }
}

// ==================== MODAL FUNCTIONS ====================
function openModal() {
    const form = document.getElementById('activityForm');
    if (!form.checkValidity()) {
        form.reportValidity();
        return false;
    }
    
    if (!document.getElementById('selectedLat').value || !document.getElementById('selectedLng').value) {
        alert('⚠️ Please select a location on the map before submitting.');
        return false;
    }
    
    if (!document.getElementById('selectedBarangayId').value) {
        alert('⚠️ Please select or verify the barangay location.');
        return false;
    }
    
    populateConfirmationModal();
    
    const modal = document.getElementById('confirmationModal');
    if (modal) {
        modal.classList.remove('hidden');
        document.body.style.overflow = 'hidden';
    }
    return false;
}

function closeModal() {
    const modal = document.getElementById('confirmationModal');
    if (modal) {
        modal.classList.add('hidden');
        document.body.style.overflow = '';
    }
}

function closeModalOnOutsideClick(event) {
    if (event.target === document.getElementById('confirmationModal')) {
        closeModal();
    }
}

function populateConfirmationModal() {
    // ===== BASIC INFO =====
    const activityTypeSelect = document.getElementById('activity_type');
    const activityType = activityTypeSelect.options[activityTypeSelect.selectedIndex]?.text || '-';
    const confirmActivityType = document.getElementById('confirmActivityType');
    if (confirmActivityType) confirmActivityType.textContent = activityType;
    
    const date = document.getElementById('activity_date').value;
    const time = document.getElementById('activity_time').value;
    const dateTime = new Date(date + 'T' + time);
    const formattedDateTime = dateTime.toLocaleString('en-PH', { 
        month: 'long', day: 'numeric', year: 'numeric',
        hour: 'numeric', minute: 'numeric', hour12: true 
    });
    const confirmDateTime = document.getElementById('confirmDateTime');
    if (confirmDateTime) confirmDateTime.textContent = formattedDateTime;
    
    const location = document.getElementById('specificLocation').value;
    const confirmLocation = document.getElementById('confirmLocation');
    if (confirmLocation) confirmLocation.textContent = location || 'Not specified';
    
    const lat = document.getElementById('selectedLat').value;
    const lng = document.getElementById('selectedLng').value;
    const confirmCoordinates = document.getElementById('confirmCoordinates');
    if (confirmCoordinates) confirmCoordinates.textContent = `Lat: ${lat}, Long: ${lng}`;
    
    // Barangay
    const barangaySelect = document.getElementById('barangaySelect');
    const barangayText = barangaySelect.options[barangaySelect.selectedIndex]?.text || 'Not selected';
    const confirmBarangay = document.getElementById('confirmBarangay');
    if (confirmBarangay) confirmBarangay.textContent = barangayText;
    
    // Personnel & Vehicle
    const personnel = document.querySelector('[name="personnel_count"]')?.value || '-';
    const confirmPersonnel = document.getElementById('confirmPersonnel');
    if (confirmPersonnel) confirmPersonnel.textContent = personnel;
    
    const vehicle = document.querySelector('[name="vehicle_number"]')?.value || 'None';
    const confirmVehicle = document.getElementById('confirmVehicle');
    if (confirmVehicle) confirmVehicle.textContent = vehicle;
    
    // ===== CHECKPOINT FIELDS =====
    const checkpointFields = ['border_control_ops', 'border_personnel', 'overlapping_ops', 
                              'mobile_checkpoint_ops', 'mobile_personnel', 'tct_ovr_accomplishment', 
                              'arrested_accomplishment'];
    const checkpointIds = ['confirmBorderOps', 'confirmBorderPersonnel', 'confirmOverlapping', 
                          'confirmMobileOps', 'confirmMobilePersonnel', 'confirmTct', 'confirmArrests'];
    
    checkpointFields.forEach((field, index) => {
        const element = document.getElementById(checkpointIds[index]);
        if (element) {
            element.textContent = document.querySelector(`[name="${field}"]`)?.value || '0';
        }
    });
    
    // ===== DISPOSITION FIELDS =====
    const dispositionFields = ['fixed_count', 'fined_count', 'warned_count', 'charged_count', 'community_service'];
    const dispositionIds = ['confirmFixed', 'confirmFined', 'confirmWarned', 'confirmCharged', 'confirmCommunityService'];
    
    dispositionFields.forEach((field, index) => {
        const element = document.getElementById(dispositionIds[index]);
        if (element) {
            element.textContent = document.querySelector(`[name="${field}"]`)?.value || '0';
        }
    });
    
    const dispositionOthers = document.querySelector('[name="disposition_others"]')?.value;
    const confirmDispositionOthers = document.getElementById('confirmDispositionOthers');
    if (confirmDispositionOthers) confirmDispositionOthers.textContent = dispositionOthers || 'None';
    
    // ===== ORDINANCE VIOLATIONS =====
    const violationFields = ['drinking_violations', 'smoking_violations', 'halfnaked_violations', 
                            'curfew_violations', 'vandalism_violations', 'other_violations'];
    const violationIds = ['confirmDrinking', 'confirmSmoking', 'confirmHalfNaked', 
                         'confirmCurfew', 'confirmVandalism', 'confirmOtherViolations'];
    
    let ordinanceTotal = 0;
    violationFields.forEach((field, index) => {
        const value = parseInt(document.querySelector(`[name="${field}"]`)?.value) || 0;
        ordinanceTotal += value;
        const element = document.getElementById(violationIds[index]);
        if (element) element.textContent = value;
    });
    
    const otherViolationsDesc = document.querySelector('[name="other_violations_desc"]')?.value;
    const confirmOtherViolationsDesc = document.getElementById('confirmOtherViolationsDesc');
    if (confirmOtherViolationsDesc) {
        confirmOtherViolationsDesc.textContent = otherViolationsDesc ? `(${otherViolationsDesc})` : '';
    }
    
    const confirmOrdinanceTotal = document.getElementById('confirmOrdinanceTotal');
    if (confirmOrdinanceTotal) confirmOrdinanceTotal.textContent = ordinanceTotal;
    
    // ===== OPLAN FIELDS =====
    const oplanFields = ['kontra_boga', 'anti_vaping', 'arrests_made', 'house_visitations'];
    const oplanIds = ['confirmKontraBoga', 'confirmAntiVaping', 'confirmOplanArrests', 'confirmHouseVisits'];
    
    oplanFields.forEach((field, index) => {
        const element = document.getElementById(oplanIds[index]);
        if (element) {
            element.textContent = document.querySelector(`[name="${field}"]`)?.value || '0';
        }
    });
    
    // Bakal specific
    const bakalFields = ['firearms_seized', 'firearms_crs', 'fas_deposit', 'renewed_fas'];
    const bakalIds = ['confirmFirearms', 'confirmFirearmsCRS', 'confirmFasDeposit', 'confirmRenewedFAS'];
    
    bakalFields.forEach((field, index) => {
        const element = document.getElementById(bakalIds[index]);
        if (element) {
            element.textContent = document.querySelector(`[name="${field}"]`)?.value || '0';
        }
    });
    
    // ===== CONTRABAND DETAILS =====
    const contrabandType = document.getElementById('contraband_type')?.value || '';
    const contrabandOther = document.querySelector('[name="contraband_other"]')?.value || '';
    const quantity = document.getElementById('contraband_quantity')?.value || '0';
    const unit = document.getElementById('contraband_unit')?.value || 'kg';
    const value = document.querySelector('[name="contraband_value"]')?.value || '0';
    
    let contrabandDisplay = 'None';
    if (contrabandType) {
        let typeText = contrabandType;
        if (contrabandType === 'Other' && contrabandOther) {
            typeText = contrabandOther;
        }
        contrabandDisplay = `${quantity} ${unit} of ${typeText}`;
        if (parseFloat(value) > 0) {
            contrabandDisplay += ` (₱${parseFloat(value).toLocaleString()} value)`;
        }
    }
    
    const confirmContrabandDetails = document.getElementById('confirmContrabandDetails');
    if (confirmContrabandDetails) {
        confirmContrabandDetails.textContent = contrabandDisplay;
    }
    
    // ===== DESCRIPTION =====
    const confirmDescription = document.getElementById('confirmDescription');
    if (confirmDescription) {
        confirmDescription.textContent = document.querySelector('[name="accomplishment_description"]')?.value || 'No description provided';
    }
    
    // ===== PHOTOS =====
    const photoInput = document.querySelector('[name="photos[]"]');
    let photoText = 'No photos uploaded';
    let photoList = '';
    
    if (photoInput && photoInput.files.length > 0) {
        photoText = `${photoInput.files.length} photo(s) selected:`;
        photoList = '<ul class="list-disc pl-4 mt-1 text-xs">';
        for (let i = 0; i < photoInput.files.length; i++) {
            const file = photoInput.files[i];
            const fileSize = (file.size / 1024).toFixed(1);
            photoList += `<li>📸 ${file.name} (${fileSize} KB)</li>`;
        }
        photoList += '</ul>';
    }
    
    const confirmPhotos = document.getElementById('confirmPhotos');
    if (confirmPhotos) {
        confirmPhotos.innerHTML = photoText + photoList;
    }
    
    // ===== GPS ACCURACY =====
    const gpsAccuracy = document.getElementById('gps_accuracy').value;
    const confirmGps = document.getElementById('confirmGps');
    if (confirmGps) {
        confirmGps.textContent = gpsAccuracy ? `📍 GPS Accuracy: ${gpsAccuracy} meters` : '';
    }
    
    // ===== SHOW/HIDE SECTIONS BASED ON ACTIVITY TYPE =====
    const type = document.getElementById('activity_type').value;
    
    // Hide all sections first
    const sections = ['confirmPersonnelField', 'confirmVehicleField', 'confirmCheckpointFields', 
                     'confirmOplanFields', 'confirmOrdinanceSection', 'confirmDispositionSection',
                     'confirmBakalSummary', 'confirmSitaSummary'];
    sections.forEach(section => {
        const element = document.getElementById(section);
        if (element) element.classList.add('hidden');
    });
    
    // Show personnel for all except checkpoint
    if (type && type !== 'checkpoint') {
        const confirmPersonnelField = document.getElementById('confirmPersonnelField');
        if (confirmPersonnelField) confirmPersonnelField.classList.remove('hidden');
    }
    
    // Show vehicle for mobile patrols
    if (type === 'Mobile Patrol' || type === 'Motorcycle Patrol') {
        const confirmVehicleField = document.getElementById('confirmVehicleField');
        if (confirmVehicleField) confirmVehicleField.classList.remove('hidden');
    }
    
    // Show checkpoint fields
    if (type === 'checkpoint') {
        const sectionsToShow = ['confirmCheckpointFields', 'confirmOrdinanceSection', 'confirmDispositionSection'];
        sectionsToShow.forEach(section => {
            const element = document.getElementById(section);
            if (element) element.classList.remove('hidden');
        });
    }
    
    // Show patrol violation fields
    if (type && type.includes('Patrol')) {
        const confirmOrdinanceSection = document.getElementById('confirmOrdinanceSection');
        if (confirmOrdinanceSection) confirmOrdinanceSection.classList.remove('hidden');
    }
    
    // Show Oplan Bakal fields
    if (type === 'Oplan Bakal') {
        const sectionsToShow = ['confirmOplanFields', 'confirmBakalSummary'];
        sectionsToShow.forEach(section => {
            const element = document.getElementById(section);
            if (element) element.classList.remove('hidden');
        });
    }
    
    // Show Oplan Sita fields
    if (type === 'Oplan Sita') {
        const sectionsToShow = ['confirmOplanFields', 'confirmOrdinanceSection', 'confirmDispositionSection', 'confirmSitaSummary'];
        sectionsToShow.forEach(section => {
            const element = document.getElementById(section);
            if (element) element.classList.remove('hidden');
        });
    }
}

function submitConfirmedReport() {
    closeModal();
    
    const form = document.getElementById('activityForm');
    const submitBtn = form.querySelector('button[type="submit"]');
    if (submitBtn) {
        submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i> Submitting...';
        submitBtn.disabled = true;
    }
    
    form.submit();
}

// ==================== INITIALIZATION ====================
document.addEventListener('DOMContentLoaded', function() {
    // Form submission handler
    const form = document.getElementById('activityForm');
    if (form) {
        form.onsubmit = function(e) {
            e.preventDefault();
            return openModal();
        };
    }

    // Contraband calculation listeners
    const quantityInput = document.getElementById('contraband_quantity');
    const unitSelect = document.getElementById('contraband_unit');
    
    if (quantityInput && unitSelect) {
        quantityInput.addEventListener('input', calculateContrabandKg);
        unitSelect.addEventListener('change', calculateContrabandKg);
    }
    
    // Initialize map with default collapsed state (optional - set to false if you want map expanded by default)
    // Uncomment below if you want map collapsed by default on page load
    // setTimeout(() => {
    //     if (!mapCollapsed) {
    //         toggleMap();
    //     }
    // }, 100);
});
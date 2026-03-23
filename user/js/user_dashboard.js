// =====================================================
// FILE: user/js/user_dashboard.js
// PURPOSE: All JavaScript functions for user dashboard
// FIXED: Removed duplicate functions and declarations
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

// ==================== MAP VARIABLES ====================
let map;
let marker;
let userMarker;
let currentLat = 8.366379;
let currentLng = 124.864432;
let currentLayer = 'street';

// Map layer definitions
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

    L.control.scale({ imperial: false, metric: true }).addTo(map);

    L.Control.geocoder({
        defaultMarkGeocode: false,
        placeholder: 'Search location...',
        errorMessage: 'Location not found',
        showResultIcons: true
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

    window.addEventListener('orientationchange', function() {
        setTimeout(() => map.invalidateSize(), 200);
    });
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
        }).addTo(map).bindPopup('Selected Location');
    }
    
    document.getElementById('selectedLat').value = lat.toFixed(6);
    document.getElementById('selectedLng').value = lng.toFixed(6);
    
    document.getElementById('locationInfo').classList.remove('hidden');
    document.getElementById('locationText').innerHTML = `Selected: ${lat.toFixed(6)}, ${lng.toFixed(6)}`;
    document.getElementById('coordinatesText').innerHTML = `Lat: ${lat.toFixed(6)}, Long: ${lng.toFixed(6)}`;
    
    getElevation(lat, lng);
}

function getElevation(lat, lng) {
    fetch(`https://api.open-elevation.com/api/v1/lookup?locations=${lat},${lng}`)
        .then(response => response.json())
        .then(data => {
            if (data.results && data.results[0]) {
                const elevation = data.results[0].elevation;
                document.getElementById('elevationText').innerHTML = `Elevation: ${Math.round(elevation)}m`;
            }
        })
        .catch(() => {});
}

function reverseGeocode(lat, lng) {
    fetch(`https://nominatim.openstreetmap.org/reverse?format=json&lat=${lat}&lon=${lng}&zoom=18&addressdetails=1`)
        .then(response => response.json())
        .then(data => {
            let locationName = data.display_name || `${lat.toFixed(6)}, ${lng.toFixed(6)}`;
            document.getElementById('specificLocation').value = locationName.substring(0, 100);
            
            if (marker) {
                marker.bindPopup(locationName.substring(0, 50)).openPopup();
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
        document.getElementById('specificLocation').value = coords.name + ', Manolo Fortich';
    }
}

function getUserLocation() {
    if (navigator.geolocation) {
        document.getElementById('locationInfo').classList.remove('hidden');
        document.getElementById('locationText').innerHTML = 'Getting your exact location...';
        
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
                }).addTo(map).bindPopup(`<b>Your Location</b><br>Accuracy: ${accuracy.toFixed(1)}m`).openPopup();
                
                placeMarker(lat, lng);
                reverseGeocode(lat, lng);
                findNearestBarangay(lat, lng);
                document.getElementById('gps_accuracy').value = accuracy;
                document.getElementById('locationText').innerHTML = `Your location (accuracy: ${accuracy.toFixed(1)}m)`;
                setPhilippineDateTime();
            },
            function(error) {
                let msg = 'Location error: ';
                switch(error.code) {
                    case error.PERMISSION_DENIED: msg += 'Please allow location access.'; break;
                    case error.POSITION_UNAVAILABLE: msg += 'Location unavailable.'; break;
                    case error.TIMEOUT: msg += 'Request timed out.'; break;
                    default: msg += 'Unknown error.';
                }
                alert(msg);
                document.getElementById('locationInfo').classList.add('hidden');
            },
            { enableHighAccuracy: true, timeout: 10000 }
        );
    } else {
        alert('Geolocation not supported');
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
        document.getElementById('mapLayerSelect').value = 'street';
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
    
    document.getElementById('activity_date').value = phDate;
    document.getElementById('activity_time').value = phTimeStr;
}

function validatePhotoUpload(input) {
    const files = input.files;
    const messageEl = document.getElementById('photoUploadMessage');
    let totalSize = 0;
    
    if (files.length > 5) {
        messageEl.innerHTML = '<span class="text-red-500">Maximum 5 photos allowed</span>';
        input.value = '';
        return;
    }
    
    for (let i = 0; i < files.length; i++) {
        totalSize += files[i].size;
    }
    
    const totalSizeMB = totalSize / (1024 * 1024);
    
    if (totalSizeMB > 15) {
        messageEl.innerHTML = '<span class="text-red-500">Total file size must be less than 15MB</span>';
        input.value = '';
    } else {
        messageEl.innerHTML = `<span class="text-green-500">Selected ${files.length} file(s) (${totalSizeMB.toFixed(2)}MB)</span>`;
    }
}

function toggleActivityFields(activityType) {
    // Hide all fields first
    document.getElementById('personnelField').classList.add('hidden');
    document.getElementById('vehicleField').classList.add('hidden');
    document.getElementById('checkpointFields').classList.add('hidden');
    document.getElementById('oplanBakalFields').classList.add('hidden');
    document.getElementById('oplanSitaFields').classList.add('hidden');
    document.getElementById('patrolViolationFields').classList.add('hidden');
    
    // Show personnel field for all except checkpoint
    if (activityType && activityType !== 'checkpoint') {
        document.getElementById('personnelField').classList.remove('hidden');
    }
    
    // Show vehicle field for mobile patrols
    if (activityType === 'Mobile Patrol' || activityType === 'Motorcycle Patrol') {
        document.getElementById('vehicleField').classList.remove('hidden');
    }
    
    // Show appropriate fields based on type
    if (activityType === 'checkpoint') {
        document.getElementById('checkpointFields').classList.remove('hidden');
    }
    
    if (activityType === 'Oplan Bakal') {
        document.getElementById('oplanBakalFields').classList.remove('hidden');
    }
    
    if (activityType === 'Oplan Sita') {
        document.getElementById('oplanSitaFields').classList.remove('hidden');
    }
    
    // Patrols can also have violations
    if (activityType && activityType.includes('Patrol')) {
        document.getElementById('patrolViolationFields').classList.remove('hidden');
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
        alert('Please select a location on the map');
        return false;
    }
    
    if (!document.getElementById('selectedBarangayId').value) {
        alert('Please select a barangay');
        return false;
    }
    
    populateConfirmationModal();
    
    document.getElementById('confirmationModal').classList.remove('hidden');
    document.body.style.overflow = 'hidden';
    return false;
}

function closeModal() {
    document.getElementById('confirmationModal').classList.add('hidden');
    document.body.style.overflow = '';
}

function closeModalOnOutsideClick(event) {
    if (event.target === document.getElementById('confirmationModal')) {
        closeModal();
    }
}

function populateConfirmationModal() {
    // ===== BASIC INFO =====
    const activityTypeSelect = document.getElementById('activity_type');
    const activityType = activityTypeSelect.options[activityTypeSelect.selectedIndex].text;
    document.getElementById('confirmActivityType').textContent = activityType;
    
    const date = document.getElementById('activity_date').value;
    const time = document.getElementById('activity_time').value;
    const dateTime = new Date(date + 'T' + time);
    const formattedDateTime = dateTime.toLocaleString('en-PH', { 
        month: 'long', day: 'numeric', year: 'numeric',
        hour: 'numeric', minute: 'numeric', hour12: true 
    });
    document.getElementById('confirmDateTime').textContent = formattedDateTime;
    
    const location = document.getElementById('specificLocation').value;
    document.getElementById('confirmLocation').textContent = location || 'Not specified';
    
    const lat = document.getElementById('selectedLat').value;
    const lng = document.getElementById('selectedLng').value;
    document.getElementById('confirmCoordinates').textContent = `Lat: ${lat}, Long: ${lng}`;
    
    // Barangay
    const barangaySelect = document.getElementById('barangaySelect');
    const barangayText = barangaySelect.options[barangaySelect.selectedIndex]?.text || 'Not selected';
    document.getElementById('confirmBarangay').textContent = barangayText;
    
    // Personnel & Vehicle
    const personnel = document.querySelector('[name="personnel_count"]')?.value || '-';
    document.getElementById('confirmPersonnel').textContent = personnel;
    
    const vehicle = document.querySelector('[name="vehicle_number"]')?.value || 'None';
    document.getElementById('confirmVehicle').textContent = vehicle;
    
    // ===== CHECKPOINT FIELDS =====
    document.getElementById('confirmBorderOps').textContent = document.querySelector('[name="border_control_ops"]')?.value || '0';
    document.getElementById('confirmBorderPersonnel').textContent = document.querySelector('[name="border_personnel"]')?.value || '0';
    document.getElementById('confirmOverlapping').textContent = document.querySelector('[name="overlapping_ops"]')?.value || '0';
    document.getElementById('confirmMobileOps').textContent = document.querySelector('[name="mobile_checkpoint_ops"]')?.value || '0';
    document.getElementById('confirmMobilePersonnel').textContent = document.querySelector('[name="mobile_personnel"]')?.value || '0';
    document.getElementById('confirmTct').textContent = document.querySelector('[name="tct_ovr_accomplishment"]')?.value || '0';
    document.getElementById('confirmArrests').textContent = document.querySelector('[name="arrested_accomplishment"]')?.value || '0';
    
    // ===== DISPOSITION FIELDS =====
    document.getElementById('confirmFixed').textContent = document.querySelector('[name="fixed_count"]')?.value || '0';
    document.getElementById('confirmFined').textContent = document.querySelector('[name="fined_count"]')?.value || '0';
    document.getElementById('confirmWarned').textContent = document.querySelector('[name="warned_count"]')?.value || '0';
    document.getElementById('confirmCharged').textContent = document.querySelector('[name="charged_count"]')?.value || '0';
    document.getElementById('confirmCommunityService').textContent = document.querySelector('[name="community_service"]')?.value || '0';
    
    const dispositionOthers = document.querySelector('[name="disposition_others"]')?.value;
    document.getElementById('confirmDispositionOthers').textContent = dispositionOthers || 'None';
    
    // ===== ORDINANCE VIOLATIONS =====
    const drinking = parseInt(document.querySelector('[name="drinking_violations"]')?.value) || 0;
    const smoking = parseInt(document.querySelector('[name="smoking_violations"]')?.value) || 0;
    const halfnaked = parseInt(document.querySelector('[name="halfnaked_violations"]')?.value) || 0;
    const curfew = parseInt(document.querySelector('[name="curfew_violations"]')?.value) || 0;
    const vandalism = parseInt(document.querySelector('[name="vandalism_violations"]')?.value) || 0;
    const other = parseInt(document.querySelector('[name="other_violations"]')?.value) || 0;
    const ordinanceTotal = drinking + smoking + halfnaked + curfew + vandalism + other;
    
    document.getElementById('confirmDrinking').textContent = drinking;
    document.getElementById('confirmSmoking').textContent = smoking;
    document.getElementById('confirmHalfNaked').textContent = halfnaked;
    document.getElementById('confirmCurfew').textContent = curfew;
    document.getElementById('confirmVandalism').textContent = vandalism;
    document.getElementById('confirmOtherViolations').textContent = other;
    
    const otherViolationsDesc = document.querySelector('[name="other_violations_desc"]')?.value;
    document.getElementById('confirmOtherViolationsDesc').textContent = otherViolationsDesc ? `(${otherViolationsDesc})` : '';
    
    document.getElementById('confirmOrdinanceTotal').textContent = ordinanceTotal;
    
    // ===== OPLAN FIELDS =====
    document.getElementById('confirmKontraBoga').textContent = document.querySelector('[name="kontra_boga"]')?.value || '0';
    document.getElementById('confirmAntiVaping').textContent = document.querySelector('[name="anti_vaping"]')?.value || '0';
    document.getElementById('confirmOplanArrests').textContent = document.querySelector('[name="arrests_made"]')?.value || '0';
    document.getElementById('confirmHouseVisits').textContent = document.querySelector('[name="house_visitations"]')?.value || '0';
    
    // Bakal specific
    document.getElementById('confirmFirearms').textContent = document.querySelector('[name="firearms_seized"]')?.value || '0';
    document.getElementById('confirmFirearmsCRS').textContent = document.querySelector('[name="firearms_crs"]')?.value || '0';
    document.getElementById('confirmFasDeposit').textContent = document.querySelector('[name="fas_deposit"]')?.value || '0';
    document.getElementById('confirmRenewedFAS').textContent = document.querySelector('[name="renewed_fas"]')?.value || '0';
    
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
    
    // Make sure the element exists before setting content
    const confirmContrabandDetails = document.getElementById('confirmContrabandDetails');
    if (confirmContrabandDetails) {
        confirmContrabandDetails.textContent = contrabandDisplay;
    }
    
    // ===== DESCRIPTION =====
    document.getElementById('confirmDescription').textContent = document.querySelector('[name="accomplishment_description"]')?.value || 'No description provided';
    
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
            photoList += `<li>${file.name} (${fileSize} KB)</li>`;
        }
        photoList += '</ul>';
    }
    
    document.getElementById('confirmPhotos').innerHTML = photoText + photoList;
    
    // ===== GPS ACCURACY =====
    const gpsAccuracy = document.getElementById('gps_accuracy').value;
    document.getElementById('confirmGps').textContent = gpsAccuracy ? `GPS Accuracy: ${gpsAccuracy} meters` : '';
    
    // ===== SHOW/HIDE SECTIONS BASED ON ACTIVITY TYPE =====
    const type = document.getElementById('activity_type').value;
    
    // Hide all sections first
    document.getElementById('confirmPersonnelField').classList.add('hidden');
    document.getElementById('confirmVehicleField').classList.add('hidden');
    document.getElementById('confirmCheckpointFields').classList.add('hidden');
    document.getElementById('confirmOplanFields').classList.add('hidden');
    document.getElementById('confirmOrdinanceSection').classList.add('hidden');
    document.getElementById('confirmDispositionSection').classList.add('hidden');
    document.getElementById('confirmBakalSummary').classList.add('hidden');
    document.getElementById('confirmSitaSummary').classList.add('hidden');
    
    // Show personnel for all except checkpoint
    if (type && type !== 'checkpoint') {
        document.getElementById('confirmPersonnelField').classList.remove('hidden');
    }
    
    // Show vehicle for mobile patrols
    if (type === 'Mobile Patrol' || type === 'Motorcycle Patrol') {
        document.getElementById('confirmVehicleField').classList.remove('hidden');
    }
    
    // Show checkpoint fields
    if (type === 'checkpoint') {
        document.getElementById('confirmCheckpointFields').classList.remove('hidden');
        document.getElementById('confirmOrdinanceSection').classList.remove('hidden');
        document.getElementById('confirmDispositionSection').classList.remove('hidden');
    }
    
    // Show patrol violation fields
    if (type && type.includes('Patrol')) {
        document.getElementById('confirmOrdinanceSection').classList.remove('hidden');
    }
    
    // Show Oplan Bakal fields
    if (type === 'Oplan Bakal') {
        document.getElementById('confirmOplanFields').classList.remove('hidden');
        document.getElementById('confirmBakalSummary').classList.remove('hidden');
    }
    
    // Show Oplan Sita fields
    if (type === 'Oplan Sita') {
        document.getElementById('confirmOplanFields').classList.remove('hidden');
        document.getElementById('confirmOrdinanceSection').classList.remove('hidden');
        document.getElementById('confirmDispositionSection').classList.remove('hidden');
        document.getElementById('confirmSitaSummary').classList.remove('hidden');
    }
}

function submitConfirmedReport() {
    closeModal();
    
    const form = document.getElementById('activityForm');
    const submitBtn = form.querySelector('button[type="submit"]');
    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i> Submitting...';
    submitBtn.disabled = true;
    
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
});
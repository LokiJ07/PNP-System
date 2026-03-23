// =====================================================
// FILE: admin/js/all_reports.js
// PURPOSE: All JavaScript functions for All Reports page
// =====================================================

// ==================== DROPDOWN FUNCTIONS ====================
function toggleDropdown(element) {
    const parent = element.closest('.dropdown');
    parent.classList.toggle('active');
    const arrow = element.querySelector('.fa-chevron-down');
    if (arrow) arrow.classList.toggle('rotate-180');
}

// Close dropdowns when clicking outside
document.addEventListener('DOMContentLoaded', function() {
    // Initialize dropdown toggles
    document.querySelectorAll('.dropdown > div').forEach(btn => {
        btn.addEventListener('click', function(e) {
            e.stopPropagation();
            const current = this.closest('.dropdown');
            document.querySelectorAll('.dropdown').forEach(drop => {
                if (drop !== current) {
                    drop.classList.remove('active');
                    const arrow = drop.querySelector('.fa-chevron-down');
                    if (arrow) arrow.classList.remove('rotate-180');
                }
            });
        });
    });
    
    // Close dropdowns when clicking outside
    document.addEventListener('click', function(event) {
        if (!event.target.closest('.dropdown')) {
            document.querySelectorAll('.dropdown').forEach(drop => {
                drop.classList.remove('active');
                const arrow = drop.querySelector('.fa-chevron-down');
                    if (arrow) arrow.classList.remove('rotate-180');
            });
        }
    });
});

// ==================== PRINT FUNCTION ====================
function printReport() {
    window.print();
}

// ==================== DATE NAVIGATION ====================
function changeDate(direction, currentDate, view) {
    let date = new Date(currentDate);
    
    if (view === 'daily') {
        date.setDate(date.getDate() + direction);
        const year = date.getFullYear();
        const month = String(date.getMonth() + 1).padStart(2, '0');
        const day = String(date.getDate()).padStart(2, '0');
        window.location.href = `?view=daily&date=${year}-${month}-${day}`;
    } else if (view === 'monthly') {
        date.setMonth(date.getMonth() + direction);
        const year = date.getFullYear();
        const month = String(date.getMonth() + 1).padStart(2, '0');
        window.location.href = `?view=monthly&month=${year}-${month}`;
    } else if (view === 'yearly') {
        date.setFullYear(date.getFullYear() + direction);
        window.location.href = `?view=yearly&year=${date.getFullYear()}`;
    }
}

// ==================== TABLE SORTING ====================
function sortTable(columnIndex, tableId) {
    const table = document.getElementById(tableId);
    const tbody = table.querySelector('tbody');
    const rows = Array.from(tbody.querySelectorAll('tr'));
    
    // Skip if no rows or only "no data" row
    if (rows.length === 0 || rows[0].querySelector('td[colspan]')) return;
    
    const isAscending = table.dataset.sortOrder !== 'asc';
    table.dataset.sortOrder = isAscending ? 'asc' : 'desc';
    
    rows.sort((a, b) => {
        const aVal = a.cells[columnIndex].innerText.trim();
        const bVal = b.cells[columnIndex].innerText.trim();
        
        // Check if numeric
        if (!isNaN(parseFloat(aVal)) && !isNaN(parseFloat(bVal))) {
            return isAscending ? parseFloat(aVal) - parseFloat(bVal) : parseFloat(bVal) - parseFloat(aVal);
        }
        
        // String comparison
        return isAscending ? aVal.localeCompare(bVal) : bVal.localeCompare(aVal);
    });
    
    // Clear and append sorted rows
    tbody.innerHTML = '';
    rows.forEach(row => tbody.appendChild(row));
}

// ==================== SEARCH FILTER ====================
function filterTable(inputId, tableId) {
    const input = document.getElementById(inputId);
    const filter = input.value.toLowerCase();
    const table = document.getElementById(tableId);
    const rows = table.querySelectorAll('tbody tr');
    
    rows.forEach(row => {
        // Skip if it's a "no data" row with colspan
        if (row.querySelector('td[colspan]')) return;
        
        let text = '';
        row.querySelectorAll('td').forEach(cell => {
            text += cell.innerText.toLowerCase() + ' ';
        });
        
        if (text.includes(filter)) {
            row.style.display = '';
        } else {
            row.style.display = 'none';
        }
    });
}

// ==================== EXPORT TO CSV ====================
function exportToCSV(tableId, filename) {
    const table = document.getElementById(tableId);
    const rows = table.querySelectorAll('tr');
    const csv = [];
    
    rows.forEach(row => {
        const cols = row.querySelectorAll('td, th');
        const rowData = [];
        cols.forEach(col => {
            // Skip action buttons column
            if (col.querySelector('a, button')) return;
            rowData.push('"' + col.innerText.replace(/"/g, '""') + '"');
        });
        csv.push(rowData.join(','));
    });
    
    const csvContent = csv.join('\n');
    const blob = new Blob([csvContent], { type: 'text/csv' });
    const url = window.URL.createObjectURL(blob);
    const a = document.createElement('a');
    a.href = url;
    a.download = filename;
    a.click();
    window.URL.revokeObjectURL(url);
}

// ==================== TOGGLE DETAILS ====================
function toggleDetails(rowId) {
    const detailsRow = document.getElementById('details-' + rowId);
    const icon = document.getElementById('icon-' + rowId);
    
    if (detailsRow.classList.contains('hidden')) {
        detailsRow.classList.remove('hidden');
        icon.classList.remove('fa-chevron-down');
        icon.classList.add('fa-chevron-up');
    } else {
        detailsRow.classList.add('hidden');
        icon.classList.remove('fa-chevron-up');
        icon.classList.add('fa-chevron-down');
    }
}

// ==================== HIGHLIGHT TODAY ====================
function highlightToday() {
    const today = new Date().toISOString().split('T')[0];
    const todayCells = document.querySelectorAll(`[data-date="${today}"]`);
    
    todayCells.forEach(cell => {
        cell.classList.add('bg-yellow-100', 'border-2', 'border-yellow-400');
    });
}

// Run highlight today when page loads
document.addEventListener('DOMContentLoaded', function() {
    highlightToday();
});

// ==================== TOOLTIP INIT ====================
function initTooltips() {
    const tooltips = document.querySelectorAll('[data-tooltip]');
    
    tooltips.forEach(element => {
        element.addEventListener('mouseenter', function(e) {
            const tooltip = document.createElement('div');
            tooltip.className = 'absolute bg-gray-800 text-white text-xs rounded px-2 py-1 z-50';
            tooltip.innerText = this.dataset.tooltip;
            tooltip.style.top = e.pageY + 10 + 'px';
            tooltip.style.left = e.pageX + 10 + 'px';
            tooltip.id = 'tooltip';
            document.body.appendChild(tooltip);
        });
        
        element.addEventListener('mouseleave', function() {
            document.getElementById('tooltip')?.remove();
        });
    });
}

// Initialize tooltips
document.addEventListener('DOMContentLoaded', function() {
    initTooltips();
});
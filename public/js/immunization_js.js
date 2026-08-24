/**
 * JS-Driven Immunizations CRUD Controller
 */

let immunizationModal;
let schedules = [];
let currentDate = new Date();

document.addEventListener('DOMContentLoaded', () => {
    // Initialize Bootstrap Modal
    const modalEl = document.getElementById('immunizationModal');
    if (modalEl) {
        immunizationModal = new bootstrap.Modal(modalEl);
        
        // Re-initialize Date pickers when modal shows up
        modalEl.addEventListener('shown.bs.modal', () => {
            if (typeof flatpickr !== 'undefined') {
                flatpickr('#modalDateGiven', {
                    dateFormat: 'Y-m-d',
                    allowInput: true,
                    maxDate: 'today'
                });
                flatpickr('#modalNextSchedule', {
                    dateFormat: 'Y-m-d',
                    allowInput: true,
                    minDate: 'today'
                });
            }
        });
    }

    // Load initial list
    loadImmunizations();

    // Calendar navigation listeners
    const prevBtn = document.getElementById('prev-month-btn');
    const nextBtn = document.getElementById('next-month-btn');
    if (prevBtn) {
        prevBtn.addEventListener('click', () => {
            currentDate.setMonth(currentDate.getMonth() - 1);
            renderCalendar();
                showFirstScheduledDay();
        });
    }
    if (nextBtn) {
        nextBtn.addEventListener('click', () => {
            currentDate.setMonth(currentDate.getMonth() + 1);
            renderCalendar();
            showFirstScheduledDay();
        });
    }

    // Click trigger on calendar days
    document.addEventListener('click', (e) => {
        const dayBox = e.target.closest('.calendar-day-box');
        if (dayBox) {
            const clickedDate = dayBox.getAttribute('data-date');
            showDaySchedules(clickedDate);
        }
    });

    // Toggle date inputs based on status selector change
    const statusSelect = document.getElementById('modalStatus');
    if (statusSelect) {
        statusSelect.addEventListener('change', toggleStatusInputs);
    }

    // Live display of vaccine inventory stocks
    const vacSelect = document.getElementById('modalVaccineId');
    if (vacSelect) {
        vacSelect.addEventListener('change', (e) => {
            const option = e.target.options[e.target.selectedIndex];
            const stock = option.getAttribute('data-stock');
            const hint = document.getElementById('modalStockHint');
            
            if (stock !== null && stock !== undefined && stock !== '') {
                hint.textContent = `Current stock in inventory: ${stock} pcs available.`;
                hint.classList.add('text-success');
                hint.classList.remove('text-muted');
            } else {
                hint.textContent = 'Select a vaccine to verify current center stock level.';
                hint.classList.remove('text-success');
                hint.classList.add('text-muted');
            }
        });
    }

    // Form submit listener
    const form = document.getElementById('immunizationForm');
    if (form) {
        form.addEventListener('submit', handleFormSubmit);
    }

    // Search filtering
    const searchInput = document.getElementById('immunizationSearch');
    if (searchInput) {
        searchInput.addEventListener('input', handleSearchAndFilter);
    }
    const vaccineFilter = document.getElementById('immunizationVaccineFilter');
    if (vaccineFilter) {
        vaccineFilter.addEventListener('change', handleSearchAndFilter);
    }
    const doseFilter = document.getElementById('immunizationDoseFilter');
    if (doseFilter) {
        doseFilter.addEventListener('change', handleSearchAndFilter);
    }
    const statusFilter = document.getElementById('immunizationStatusFilter');
    if (statusFilter) {
        statusFilter.addEventListener('change', handleSearchAndFilter);
    }
});

/**
 * Fetch and render all immunization records
 */
async function loadImmunizations() {
    try {
        const response = await fetch('?route=api/immunization/list');
        if (!response.ok) throw new Error('API server returned error (' + response.status + ')');

        const records = await response.json();
        const tbody = document.getElementById('immunizationTable');
        if (!tbody) return;

        tbody.innerHTML = '';

        if (records.length === 0) {
            tbody.innerHTML = `
                <tr>
                    <td colspan="8" class="text-center text-muted py-4">No immunization logs or schedules found.</td>
                </tr>
            `;
            return;
        }

        records.forEach(r => {
            let statusBadge = '';
            if (r.status === 'Completed') {
                statusBadge = '<span class="badge bg-success bg-opacity-10 text-success border border-success-subtle"><i class="bi bi-check-circle me-1"></i> Completed</span>';
            } else if (r.status === 'Upcoming') {
                statusBadge = '<span class="badge bg-primary bg-opacity-10 text-primary border border-primary-subtle"><i class="bi bi-clock-history me-1"></i> Upcoming</span>';
            } else {
                statusBadge = '<span class="badge bg-danger bg-opacity-10 text-danger border border-danger-subtle"><i class="bi bi-x-circle me-1"></i> Missed</span>';
            }

            const tr = document.createElement('tr');
            tr.innerHTML = `
                <td>
                    <div class="fw-bold">${escapeHtml(r.resident_name)}</div>
                    <small class="text-muted">${escapeHtml(r.resident_code)} • (${escapeHtml(r.resident_age)} yrs)</small>
                </td>
                <td class="fw-semibold text-dark">${escapeHtml(r.vaccine_name)}</td>
                <td class="font-monospace text-secondary">${escapeHtml(r.dose)}</td>
                <td>${escapeHtml(r.date_given || '--')}</td>
                <td>${escapeHtml(r.next_schedule || '--')}</td>
                <td class="small text-muted">${escapeHtml(r.worker_name)}</td>
                <td>${statusBadge}</td>
                <td class="text-center">
                    <div class="btn-group gap-1">
                        <button type="button" onclick="editImmunization(${r.id})" class="btn btn-outline-primary btn-sm rounded-2" title="Edit Log">
                            <i class="bi bi-pencil"></i>
                        </button>
                        <button type="button" onclick="deleteImmunization(${r.id}, '${escapeHtml(r.resident_name)}')" class="btn btn-outline-danger btn-sm rounded-2" title="Delete Log">
                            <i class="bi bi-trash"></i>
                        </button>
                    </div>
                </td>
            `;
            tbody.appendChild(tr);
        });

        // Update local schedules for calendar rendering
        schedules = records.map(r => {
            const scheduleDate = r.next_schedule || r.date_given;
            return {
                id: r.id,
                patient: r.resident_name,
                vaccine: r.vaccine_name,
                dose: r.dose,
                date: scheduleDate ? scheduleDate.substring(0, 10) : '',
                status: r.status
            };
        });
        renderCalendar();
        showFirstScheduledDay();
    } catch (error) {
        console.error(error);
        showToast('error', 'Failed to fetch immunization records: ' + error.message);
    }
}

/**
 * Configure modal for logging a new vaccine record
 */
function newImmunization() {
    const form = document.getElementById('immunizationForm');
    if (!form) return;

    form.reset();
    form.classList.remove('was-validated');
    $('#modalResidentId').val('').trigger('change');

    document.getElementById('immunizationId').value = '';
    document.getElementById('modalTitleText').textContent = 'Log Vaccination / Schedule';
    document.getElementById('modalStatus').value = 'Upcoming';
    document.getElementById('saveButton').textContent = 'Save Record';

    // Refresh display
    toggleStatusInputs();

    const hint = document.getElementById('modalStockHint');
    hint.textContent = 'Select a vaccine to verify current center stock level.';
    hint.classList.remove('text-success');
    hint.classList.add('text-muted');

    if (immunizationModal) immunizationModal.show();
}

/**
 * Fetch immunization details and populates form in Edit Modal
 */
async function editImmunization(id) {
    try {
        const response = await fetch('?route=api/immunization/detail&id=' + id);
        if (!response.ok) throw new Error('API server returned error (' + response.status + ')');

        const r = await response.json();
        if (r.status === 'error') throw new Error(r.message);

        const form = document.getElementById('immunizationForm');
        form.reset();
        form.classList.remove('was-validated');

        document.getElementById('immunizationId').value = r.id;
        document.getElementById('modalResidentId').value = r.resident_id;
        $('#modalResidentId').trigger('change');
        document.getElementById('modalVaccineId').value = r.vaccine_id;
        document.getElementById('modalDose').value = r.dose;
        document.getElementById('modalStatus').value = r.status;
        document.getElementById('modalDateGiven').value = r.date_given || '';
        document.getElementById('modalNextSchedule').value = r.next_schedule || '';

        // Trigger stock text display
        const vacSelect = document.getElementById('modalVaccineId');
        if (vacSelect) {
            const opt = vacSelect.options[vacSelect.selectedIndex];
            const stock = opt.getAttribute('data-stock');
            const hint = document.getElementById('modalStockHint');
            if (stock !== null) {
                hint.textContent = `Current stock in inventory: ${stock} pcs available.`;
                hint.classList.add('text-success');
                hint.classList.remove('text-muted');
            }
        }

        // Adjust input display based on loaded status
        toggleStatusInputs();

        document.getElementById('modalTitleText').textContent = 'Edit Immunization Details';
        document.getElementById('saveButton').textContent = 'Save Changes';

        if (immunizationModal) immunizationModal.show();
    } catch (error) {
        showToast('error', 'Error loading record: ' + error.message);
    }
}

/**
 * Handle form submit (prevent reload, validate state requirements, post JSON)
 */
async function handleFormSubmit(event) {
    event.preventDefault();
    const form = event.target;

    const id = document.getElementById('immunizationId').value;
    const resident_id = parseInt(document.getElementById('modalResidentId').value);
    const vaccine_id = parseInt(document.getElementById('modalVaccineId').value);
    const dose = document.getElementById('modalDose').value;
    const status = document.getElementById('modalStatus').value;
    const date_given = document.getElementById('modalDateGiven').value.trim();
    const next_schedule = document.getElementById('modalNextSchedule').value.trim();

    // Custom Validation logic matching standard controller constraints
    let hasCustomError = false;
    const dateGivenInput = document.getElementById('modalDateGiven');
    const nextScheduleInput = document.getElementById('modalNextSchedule');

    dateGivenInput.classList.remove('is-invalid');
    nextScheduleInput.classList.remove('is-invalid');

    if (status === 'Completed' && date_given === '') {
        hasCustomError = true;
        dateGivenInput.classList.add('is-invalid');
    }

    if (status === 'Upcoming' && next_schedule === '') {
        hasCustomError = true;
        nextScheduleInput.classList.add('is-invalid');
    }

    if (!form.checkValidity() || hasCustomError) {
        form.classList.add('was-validated');
        return;
    }

    const data = {
        resident_id,
        vaccine_id,
        dose,
        status,
        date_given: status === 'Completed' ? date_given : '',
        next_schedule: status === 'Upcoming' ? next_schedule : ''
    };

    let apiUrl = '?route=api/immunization/store';
    if (id !== '') {
        data.id = parseInt(id);
        apiUrl = '?route=api/immunization/update';
    }

    try {
        const response = await fetch(apiUrl, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify(data)
        });

        if (!response.ok) throw new Error('API server returned error (' + response.status + ')');

        const result = await response.json();
        if (result.status === 'success') {
            if (immunizationModal) immunizationModal.hide();
            showToast('success', result.message);
            loadImmunizations();
        } else {
            showToast('error', result.message);
        }
    } catch (error) {
        showToast('error', 'Request failed: ' + error.message);
    }
}

/**
 * Delete immunization log record
 */
function deleteImmunization(id, patientName) {
    const isDarkMode = document.documentElement.getAttribute('data-bs-theme') === 'dark';

    Swal.fire({
        title: 'Delete record?',
        text: `Are you sure you want to delete immunization record of ${patientName}?`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#dc3545',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Yes, delete it!',
        cancelButtonText: 'Cancel',
        background: isDarkMode ? '#212529' : '#fff',
        color: isDarkMode ? '#f8f9fa' : '#212529'
    }).then(async (result) => {
        if (result.isConfirmed) {
            try {
                const response = await fetch('?route=api/immunization/delete', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({ id: id })
                });

                if (!response.ok) throw new Error('API request failed.');

                const res = await response.json();
                if (res.status === 'success') {
                    showToast('success', res.message);
                    loadImmunizations();
                } else {
                    showToast('error', res.message);
                }
            } catch (error) {
                showToast('error', 'Failed to delete: ' + error.message);
            }
        }
    });
}

/**
 * Toggle date_given / next_schedule requirement states dynamically based on status selection
 */
function toggleStatusInputs() {
    const status = document.getElementById('modalStatus').value;
    const dateGivenContainer = document.getElementById('dateGivenContainer');
    const nextScheduleContainer = document.getElementById('nextScheduleContainer');
    
    const dateGivenInput = document.getElementById('modalDateGiven');
    const nextScheduleInput = document.getElementById('modalNextSchedule');

    if (status === 'Completed') {
        dateGivenContainer.style.display = 'block';
        nextScheduleContainer.style.display = 'block';
        
        dateGivenInput.setAttribute('required', 'required');
        nextScheduleInput.removeAttribute('required'); // Next schedule is optional when completed
    } else if (status === 'Upcoming') {
        dateGivenContainer.style.display = 'none';
        nextScheduleContainer.style.display = 'block';
        
        nextScheduleInput.setAttribute('required', 'required');
        dateGivenInput.removeAttribute('required');
        dateGivenInput.value = '';
    } else {
        // Missed status
        dateGivenContainer.style.display = 'none';
        nextScheduleContainer.style.display = 'none';
        
        dateGivenInput.removeAttribute('required');
        nextScheduleInput.removeAttribute('required');
        dateGivenInput.value = '';
        nextScheduleInput.value = '';
    }
}

/**
 * Client side filter search with multi-criteria support (Vaccine, Dose, Status)
 */
function handleSearchAndFilter() {
    const searchInput = document.getElementById('immunizationSearch');
    const vaccineSelect = document.getElementById('immunizationVaccineFilter');
    const doseSelect = document.getElementById('immunizationDoseFilter');
    const statusSelect = document.getElementById('immunizationStatusFilter');

    const keyword = searchInput ? searchInput.value.toLowerCase().trim() : '';
    const vaccineFilter = vaccineSelect ? vaccineSelect.value.toLowerCase() : '';
    const doseFilter = doseSelect ? doseSelect.value.toLowerCase() : '';
    const statusFilter = statusSelect ? statusSelect.value.toLowerCase() : '';

    const rows = document.querySelectorAll('#immunizationTable tr');

    rows.forEach(row => {
        if (row.cells.length === 1 && row.cells[0].colSpan > 1) return;
        
        const rowText = row.innerText.toLowerCase();
        
        const vaccineCellText = row.cells[1] ? row.cells[1].innerText.toLowerCase() : '';
        const doseCellText = row.cells[2] ? row.cells[2].innerText.toLowerCase() : '';
        const statusCellText = row.cells[6] ? row.cells[6].innerText.toLowerCase() : '';

        const matchesSearch = rowText.includes(keyword);
        const matchesVaccine = vaccineFilter === '' || vaccineCellText.includes(vaccineFilter);
        const matchesDose = doseFilter === '' || doseCellText.includes(doseFilter);
        const matchesStatus = statusFilter === '' || statusCellText.includes(statusFilter);

        row.style.display = (matchesSearch && matchesVaccine && matchesDose && matchesStatus) ? '' : 'none';
    });
}



/**
 * Render the Calendar scheduler UI dynamically
 */
function renderCalendar() {
    const monthYearEl = document.getElementById('calendar-month-year');
    if (!monthYearEl) return;

    const year = currentDate.getFullYear();
    const month = currentDate.getMonth();

    const monthNames = [
        "January", "February", "March", "April", "May", "June", 
        "July", "August", "September", "October", "November", "December"
    ];

    monthYearEl.textContent = `${monthNames[month]} ${year}`;

    const firstDayOfMonth = new Date(year, month, 1).getDay();
    const daysInMonth = new Date(year, month + 1, 0).getDate();
    const daysInPrevMonth = new Date(year, month, 0).getDate();

    let dayBoxes = '';

    // Prepend previous month days
    for (let i = firstDayOfMonth - 1; i >= 0; i--) {
        const dayNum = daysInPrevMonth - i;
        const prevMonthDate = new Date(year, month - 1, dayNum);
        dayBoxes += generateDayBox(prevMonthDate, true);
    }

    // Current month days
    for (let i = 1; i <= daysInMonth; i++) {
        const thisDate = new Date(year, month, i);
        dayBoxes += generateDayBox(thisDate, false);
    }

    // Append next month days to complete weekly columns
    const totalRendered = firstDayOfMonth + daysInMonth;
    const remaining = totalRendered % 7 === 0 ? 0 : 7 - (totalRendered % 7);
    for (let i = 1; i <= remaining; i++) {
        const nextMonthDate = new Date(year, month + 1, i);
        dayBoxes += generateDayBox(nextMonthDate, true);
    }

    const daysContainer = document.getElementById('calendar-days');
    if (daysContainer) {
        daysContainer.innerHTML = dayBoxes;
    }
}

/**
 * Display the first scheduled day in the currently visible month.
 */
function showFirstScheduledDay() {
    const year = currentDate.getFullYear();
    const month = currentDate.getMonth();
    const firstEvent = schedules
        .filter(event => {
            const eventDate = new Date(`${event.date}T00:00:00`);
            return eventDate.getFullYear() === year && eventDate.getMonth() === month;
        })
        .sort((first, second) => first.date.localeCompare(second.date))[0];

    const info = document.getElementById('calendar-day-info');
    if (firstEvent) {
        showDaySchedules(firstEvent.date);
    } else if (info) {
        info.textContent = 'Click a highlighted day in the calendar to see scheduled patient vaccinations.';
        document.getElementById('calendar-day-list').innerHTML = '';
    }
}

/**
 * Render schedules for a selected calendar date.
 */
function showDaySchedules(selectedDate) {
    const dayEvents = schedules.filter(event => event.date === selectedDate);
    const dateObj = new Date(`${selectedDate}T00:00:00`);
    const formatTitle = dateObj.toLocaleDateString('en-US', { month: 'long', day: 'numeric', year: 'numeric' });
    const info = document.getElementById('calendar-day-info');
    const list = document.getElementById('calendar-day-list');

    if (info) info.innerHTML = `Schedules for <strong>${formatTitle}</strong>`;
    if (!list) return;

    if (dayEvents.length === 0) {
        list.innerHTML = '<div class="text-muted text-center py-4 small">No immunizations scheduled on this day.</div>';
        return;
    }

    list.innerHTML = dayEvents.map(event => {
        const statusClass = event.status === 'Completed' ? 'success' : (event.status === 'Upcoming' ? 'warning' : 'danger');
        return `
            <div class="list-group-item border rounded-3 p-3">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <span class="fw-bold text-dark text-truncate" style="max-width: 70%;">${escapeHtml(event.patient)}</span>
                    <span class="badge bg-${statusClass} bg-opacity-10 text-${statusClass} border border-${statusClass}-subtle">${event.status}</span>
                </div>
                <div class="small text-muted"><i class="bi bi-shield-plus me-1 text-info"></i>Vaccine: ${escapeHtml(event.vaccine)}</div>
                <div class="small text-muted"><i class="bi bi-layers me-1"></i>Dose: ${escapeHtml(event.dose)}</div>
                <div class="d-flex justify-content-end mt-2">
                    <button type="button" onclick="editImmunization(${event.id})" class="btn btn-sm btn-light border py-1 px-2.5 small"><i class="bi bi-pencil-square me-1"></i> Update record</button>
                </div>
            </div>
        `;
    }).join('');
}

/**
 * Generate individual calendar day cells
 */
function generateDayBox(date, isOtherMonth) {
    // Format date as local YYYY-MM-DD taking timezone offsets into account
    const offset = date.getTimezoneOffset();
    const localDate = new Date(date.getTime() - (offset * 60 * 1000));
    const dateStr = localDate.toISOString().split('T')[0];
    const day = date.getDate();
    
    // Check if date is today
    const today = new Date();
    const todayOffset = today.getTimezoneOffset();
    const localToday = new Date(today.getTime() - (todayOffset * 60 * 1000));
    const todayStr = localToday.toISOString().split('T')[0];
    const isToday = (dateStr === todayStr);

    // Find matching events
    const dayEvents = schedules.filter(ev => ev.date === dateStr);
    let eventHTML = '';

    dayEvents.slice(0, 2).forEach(ev => {
        const statusClass = ev.status.toLowerCase();
        eventHTML += `
            <div class="calendar-event-indicator indicator-${statusClass}" title="${escapeHtml(ev.patient)}: ${escapeHtml(ev.vaccine)}">
                ${escapeHtml(ev.patient.split(' ')[0])} - ${escapeHtml(ev.vaccine)}
            </div>
        `;
    });

    if (dayEvents.length > 2) {
        eventHTML += `<div class="text-muted small mt-1 text-start" style="font-size: 0.65rem;">+ ${dayEvents.length - 2} more</div>`;
    }

    // Add styling for today's date
    const dayNumStyle = isToday ? 'bg-primary text-white rounded-circle d-inline-block text-center' : 'text-start';
    const dayNumWidth = isToday ? 'width: 24px; height: 24px; line-height: 24px;' : '';

    return `
        <div class="col-7-custom calendar-day-box ${isOtherMonth ? 'other-month' : ''}" data-date="${dateStr}">
            <div class="calendar-day-num ${dayNumStyle}" style="${dayNumWidth}">${day}</div>
            ${eventHTML}
        </div>
    `;
}

/**
 * Quick helper to set next schedule date by adding weeks
 */
function addWeeksToSchedule(weeks) {
    const baseDateInput = document.getElementById('modalDateGiven').value;
    const baseDate = baseDateInput ? new Date(baseDateInput) : new Date();
    baseDate.setDate(baseDate.getDate() + (weeks * 7));
    
    const nextDateStr = baseDate.toISOString().split('T')[0];
    const nextInput = document.getElementById('modalNextSchedule');
    if (nextInput) {
        nextInput.value = nextDateStr;
        if (nextInput._flatpickr) {
            nextInput._flatpickr.setDate(nextDateStr);
        }
    }
}

/**
 * Quick helper to set next schedule date by adding months
 */
function addMonthsToSchedule(months) {
    const baseDateInput = document.getElementById('modalDateGiven').value;
    const baseDate = baseDateInput ? new Date(baseDateInput) : new Date();
    baseDate.setMonth(baseDate.getMonth() + months);
    
    const nextDateStr = baseDate.toISOString().split('T')[0];
    const nextInput = document.getElementById('modalNextSchedule');
    if (nextInput) {
        nextInput.value = nextDateStr;
        if (nextInput._flatpickr) {
            nextInput._flatpickr.setDate(nextDateStr);
        }
    }
}


/**
 * Barangay Health Monitoring System - Main Application Script
 */

document.addEventListener('DOMContentLoaded', function () {
    
    // 1. Mobile Sidebar Toggle
    const sidebarToggleBtn = document.getElementById('sidebar-toggle-btn');
    const sidebar = document.getElementById('sidebar');

    if (sidebarToggleBtn && sidebar) {
        sidebarToggleBtn.addEventListener('click', function (e) {
            e.stopPropagation();
            sidebar.classList.toggle('show');
        });

        // Close sidebar when clicking outside of it on mobile
        document.addEventListener('click', function (e) {
            if (window.innerWidth < 992) {
                const isClickInsideSidebar = sidebar.contains(e.target);
                const isClickToggle = sidebarToggleBtn.contains(e.target);
                if (!isClickInsideSidebar && !isClickToggle && sidebar.classList.contains('show')) {
                    sidebar.classList.remove('show');
                }
            }
        });
    }

    // 2. Real-time Clock
    const timeElement = document.getElementById('nav-system-time');
    if (timeElement) {
        function updateClock() {
            const now = new Date();
            let hours = now.getHours();
            let minutes = now.getMinutes();
            let seconds = now.getSeconds();
            const ampm = hours >= 12 ? 'PM' : 'AM';
            
            hours = hours % 12;
            hours = hours ? hours : 12; // the hour '0' should be '12'
            minutes = minutes < 10 ? '0' + minutes : minutes;
            seconds = seconds < 10 ? '0' + seconds : seconds;
            
            timeElement.textContent = hours + ':' + minutes + ':' + seconds + ' ' + ampm;
        }
        updateClock();
        setInterval(updateClock, 1000);
    }

    // 3. Dark Mode Toggler
    const themeToggleBtn = document.getElementById('theme-toggle-btn');
    const themeIcon = document.getElementById('theme-icon');

    if (themeToggleBtn && themeIcon) {
        // Initial setup based on current theme state
        function syncThemeIcon() {
            const currentTheme = document.documentElement.getAttribute('data-bs-theme');
            if (currentTheme === 'dark') {
                themeIcon.className = 'bi bi-moon-fill';
            } else {
                themeIcon.className = 'bi bi-sun-fill';
            }
        }
        syncThemeIcon();

        themeToggleBtn.addEventListener('click', function () {
            const currentTheme = document.documentElement.getAttribute('data-bs-theme');
            const newTheme = currentTheme === 'dark' ? 'light' : 'dark';
            
            document.documentElement.setAttribute('data-bs-theme', newTheme);
            localStorage.setItem('theme', newTheme);
            syncThemeIcon();
        });
    }

    // 4. Flatpickr (DatePicker) Autoloader
    if (typeof flatpickr !== 'undefined') {
        flatpickr('.datepicker', {
            dateFormat: 'Y-m-d',
            allowInput: true,
            maxDate: 'today'
        });

        // For schedule/future dates (upcoming appointments/immunizations)
        flatpickr('.future-datepicker', {
            dateFormat: 'Y-m-d',
            allowInput: true,
            minDate: 'today'
        });

        // Birthdate DatePicker with Year Dropdown Autoloader
        App.initBirthdatePicker('.birthdate-picker');
    }

    // 5. SweetAlert2 Sign Out Confirmation
    const logoutBtn = document.getElementById('logout-btn');
    if (logoutBtn && typeof Swal !== 'undefined') {
        logoutBtn.addEventListener('click', function (e) {
            e.preventDefault();
            const logoutUrl = this.getAttribute('href');
            const isDarkMode = document.documentElement.getAttribute('data-bs-theme') === 'dark';

            Swal.fire({
                title: 'Are you sure?',
                text: 'You will be signed out of your session.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#dc3545',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Yes, sign out!',
                cancelButtonText: 'Cancel',
                background: isDarkMode ? '#212529' : '#fff',
                color: isDarkMode ? '#f8f9fa' : '#212529'
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location.href = logoutUrl;
                }
            });
        });
    }

    // 6. Custom Searchable Select Autoloader
    App.initSearchableSelect('.searchable-select');

    // 7. Auto add printing classes to body based on page route for reports
    window.addEventListener('beforeprint', function() {
        const urlParams = new URLSearchParams(window.location.search);
        const route = urlParams.get('route') || '';
        if (route.indexOf('reports') === 0 || route.indexOf('reports/') === 0) {
            document.body.classList.add('printing-report');
        }
    });

    window.addEventListener('afterprint', function() {
        document.body.classList.remove('printing-report');
    });
});

/**
 * Utility functions
 */
const App = {
    // Show loading spinner
    showLoader: function () {
        const loader = document.getElementById('app-spinner');
        if (loader) loader.classList.remove('d-none');
    },

    // Hide loading spinner
    hideLoader: function () {
        const loader = document.getElementById('app-spinner');
        if (loader) loader.classList.add('d-none');
    },

    // Initialize custom premium inline searchable select dropdown
    initSearchableSelect: function (selector) {
        $(selector).each(function() {
            const $select = $(this);
            if ($select.hasClass('searchable-select-initialized')) return;
            $select.addClass('searchable-select-initialized');

            const placeholder = $select.find('option:disabled:first').text() || 'Search and select...';
            const isRequired = $select.prop('required');
            const sizeClass = $select.hasClass('form-select-sm') ? 'form-control-sm' : '';
            
            const $wrapper = $('<div class="searchable-select-wrapper position-relative"></div>');
            const $input = $(`<input type="text" class="form-control searchable-select-input ${sizeClass}" placeholder="${placeholder}" autocomplete="off">`);
            const $clearBtn = $('<button type="button" class="btn position-absolute end-0 top-50 translate-middle-y border-0 bg-transparent text-muted searchable-select-clear" style="z-index: 5; display: none; padding: 0 10px;"><i class="bi bi-x-circle-fill"></i></button>');
            const $chevron = $('<span class="position-absolute end-0 top-50 translate-middle-y me-3 text-muted pointer-events-none searchable-select-chevron"><i class="bi bi-chevron-down"></i></span>');
            const $dropdown = $('<div class="dropdown-menu searchable-select-dropdown w-100 shadow" style="max-height: 250px; overflow-y: auto;"></div>');

            $select.hide();
            $select.after($wrapper);
            $wrapper.append($input).append($clearBtn).append($chevron).append($dropdown);

            function populateOptions(filterText = '') {
                $dropdown.empty();
                let matchCount = 0;
                const normalizedFilter = filterText.toLowerCase().trim();

                $select.find('option').each(function() {
                    const $opt = $(this);
                    if ($opt.is(':disabled') || !$opt.val()) return;

                    const text = $opt.text().trim();
                    if (normalizedFilter && !text.toLowerCase().includes(normalizedFilter)) {
                        return;
                    }

                    matchCount++;
                    const $item = $(`<a class="dropdown-item searchable-select-item text-truncate" href="#" data-value="${$opt.val()}"></a>`);
                    $item.text(text);
                    
                    if ($select.val() === $opt.val()) {
                        $item.addClass('active');
                        $input.val(text);
                        $clearBtn.show();
                        $chevron.hide();
                    }

                    $dropdown.append($item);
                });

                if (matchCount === 0) {
                    $dropdown.append('<div class="dropdown-item disabled text-muted">No results found</div>');
                }
            }

            populateOptions();

            $input.on('focus click', function(e) {
                e.stopPropagation();
                populateOptions($input.val());
                $('.searchable-select-dropdown').not($dropdown).removeClass('show');
                $dropdown.addClass('show');
            });

            $input.on('input', function() {
                populateOptions($input.val());
                $dropdown.addClass('show');
                if ($input.val().trim() !== '') {
                    $clearBtn.show();
                    $chevron.hide();
                } else {
                    $clearBtn.hide();
                    $chevron.show();
                    $select.val('').trigger('change');
                }
            });

            $clearBtn.on('click', function(e) {
                e.stopPropagation();
                $input.val('');
                $select.val('').trigger('change');
                $clearBtn.hide();
                $chevron.show();
                populateOptions();
                $dropdown.removeClass('show');
            });

            $dropdown.on('click', '.searchable-select-item', function(e) {
                e.preventDefault();
                e.stopPropagation();
                
                const val = $(this).data('value');
                const text = $(this).text();

                $select.val(val).trigger('change');
                $input.val(text);
                
                $clearBtn.show();
                $chevron.hide();
                $dropdown.removeClass('show');
                $input.removeClass('is-invalid');
            });

            $(document).on('click', function(e) {
                if (!$wrapper.is(e.target) && $wrapper.has(e.target).length === 0) {
                    $dropdown.removeClass('show');
                    const selectedOpt = $select.find(':selected');
                    if (selectedOpt.val()) {
                        $input.val(selectedOpt.text().trim());
                    } else {
                        $input.val('');
                        $clearBtn.hide();
                        $chevron.show();
                    }
                }
            });

            $select.on('change', function() {
                const selectedOpt = $select.find(':selected');
                if (selectedOpt.val()) {
                    $input.val(selectedOpt.text().trim());
                    $clearBtn.show();
                    $chevron.hide();
                } else {
                    $input.val('');
                    $clearBtn.hide();
                    $chevron.show();
                }
            });

            const form = $select.closest('form');
            if (form.length) {
                form.on('submit', function() {
                    if (isRequired && !$select.val()) {
                        $input.addClass('is-invalid');
                    }
                });
            }
        });
    },

    // Initialize Birthdate Picker with a Year Dropdown selector
    initBirthdatePicker: function (selector) {
        if (typeof flatpickr === 'undefined') return;
        
        const elements = document.querySelectorAll(selector);
        elements.forEach(function (el) {
            // Prevent duplicate initialization: if Flatpickr already exists, just sync date and return
            if (el._flatpickr) {
                el._flatpickr.setDate(el.value, false);
                return;
            }

            flatpickr(el, {
                dateFormat: 'Y-m-d',
                allowInput: true,
                maxDate: 'today',
                onChange: function (selectedDates, dateStr, instance) {
                    el.dispatchEvent(new Event('change'));
                },
                onReady: function (selectedDates, dateStr, instance) {
                    const container = instance.currentYearElement.parentNode;
                    if (container.querySelector('.flatpickr-custom-year-select')) return;

                    const yearSelect = document.createElement('select');
                    yearSelect.className = 'flatpickr-monthDropdown-months flatpickr-custom-year-select';
                    yearSelect.style.width = '75px';
                    yearSelect.style.marginLeft = '5px';
                    yearSelect.style.cursor = 'pointer';

                    const currentYear = new Date().getFullYear();
                    for (let y = currentYear; y >= currentYear - 100; y--) {
                        const option = document.createElement('option');
                        option.value = y;
                        option.textContent = y;
                        yearSelect.appendChild(option);
                    }

                    const currentYearInput = instance.currentYearElement;
                    const yearWrapper = currentYearInput.parentNode; // this is .numInputWrapper which contains the input and arrows
                    
                    // Hide the entire native year wrapper (removing arrows)
                    yearWrapper.style.display = 'none';
                    
                    // Insert select element right after the hidden native wrapper
                    yearWrapper.parentNode.insertBefore(yearSelect, yearWrapper.nextSibling);

                    // Set initial value
                    yearSelect.value = instance.currentYear;

                    yearSelect.addEventListener('change', function () {
                        instance.changeYear(parseInt(this.value));
                    });

                    // Add scroll wheel navigation (scroll up/down to change year)
                    yearSelect.addEventListener('wheel', function (e) {
                        e.preventDefault(); // Prevent scrolling the page
                        
                        let index = this.selectedIndex;
                        if (e.deltaY > 0) {
                            // Scrolling down: go to next option (older year, since options are descending)
                            if (index < this.options.length - 1) {
                                this.selectedIndex = index + 1;
                                this.dispatchEvent(new Event('change'));
                            }
                        } else if (e.deltaY < 0) {
                            // Scrolling up: go to previous option (newer year)
                            if (index > 0) {
                                this.selectedIndex = index - 1;
                                this.dispatchEvent(new Event('change'));
                            }
                        }
                    });

                    instance.config.onYearChange.push(function () {
                        yearSelect.value = instance.currentYear;
                    });
                }
            });
        });
    }
};

// Global Fetch Interceptor for automatic CSRF Token Header Injection
(function() {
    const originalFetch = window.fetch;
    window.fetch = async function(input, init) {
        init = init || {};
        init.headers = init.headers || {};
        
        // Retrieve the CSRF token from the meta tag
        const csrfMeta = document.querySelector('meta[name="csrf-token"]');
        if (csrfMeta) {
            const token = csrfMeta.getAttribute('content');
            
            // Inject header for non-GET requests
            const method = (init.method || 'GET').toUpperCase();
            if (method !== 'GET') {
                if (init.headers instanceof Headers) {
                    init.headers.set('X-CSRF-TOKEN', token);
                } else {
                    init.headers['X-CSRF-TOKEN'] = token;
                }
            }
        }
        
        return originalFetch(input, init);
    };
})();

// Global escapeHtml helper function
window.escapeHtml = function(string) {
    if (!string) return '';
    return String(string)
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;')
        .replace(/'/g, '&#039;');
};

// Global showToast helper function using SweetAlert2
window.showToast = function(icon, message) {
    if (typeof Swal === 'undefined') return;
    
    // Select semantic color themes and custom classes
    let bgColor = '#1e293b'; // Default Dark Slate
    let textColor = '#ffffff';
    let iconColor = '#ffffff';
    let customToastClass = '';

    if (icon === 'success') {
        bgColor = '#10b981'; // Emerald Green
        customToastClass = 'swal2-success-toast';
    } else if (icon === 'error') {
        bgColor = '#ef4444'; // Rose Red
        customToastClass = 'swal2-error-toast';
    } else if (icon === 'warning') {
        bgColor = '#f59e0b'; // Amber Yellow
        customToastClass = 'swal2-warning-toast';
    } else if (icon === 'info') {
        bgColor = '#3b82f6'; // Blue
        customToastClass = 'swal2-info-toast';
    }

    const Toast = Swal.mixin({
        toast: true,
        position: 'top-end',
        showConfirmButton: false,
        timer: 3000,
        timerProgressBar: true,
        background: bgColor,
        color: textColor,
        iconColor: iconColor,
        customClass: {
            popup: customToastClass
        },
        didOpen: (toast) => {
            toast.addEventListener('mouseenter', Swal.stopTimer);
            toast.addEventListener('mouseleave', Swal.resumeTimer);
        }
    });

    Toast.fire({
        icon: icon,
        title: message
    });
};

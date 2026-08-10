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
                themeIcon.className = 'bi bi-sun-fill text-warning';
            } else {
                themeIcon.className = 'bi bi-moon-fill';
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

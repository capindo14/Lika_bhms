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
    }
};

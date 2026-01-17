/**
 * Theme Manager - Handles light/dark/system theme switching
 * Cycles through: system -> light -> dark -> system
 * Persists choice to localStorage and responds to system theme changes
 */

window.Theme = {
    // Theme modes
    MODES: {
        SYSTEM: 'system',
        LIGHT: 'light',
        DARK: 'dark'
    },

    // Current mode
    currentMode: 'system',

    // Storage key
    STORAGE_KEY: 'dgc-theme-mode',

    // Media query for system dark mode preference
    mediaQuery: null,

    /**
     * Initialize the theme manager
     */
    init() {
        // Load saved preference
        this.currentMode = localStorage.getItem(this.STORAGE_KEY) || this.MODES.SYSTEM;

        // Set up system theme change listener
        this.mediaQuery = window.matchMedia('(prefers-color-scheme: dark)');
        this.mediaQuery.addEventListener('change', (e) => this.onSystemThemeChange(e));

        // Apply initial theme
        this.applyTheme();

        // Set up toggle button if present
        this.initToggleButton();

        return this;
    },

    /**
     * Initialize the theme toggle button
     */
    initToggleButton() {
        const toggleBtn = document.querySelector('.theme-toggle-btn');
        if (toggleBtn) {
            toggleBtn.addEventListener('click', () => this.toggle());
            this.updateToggleButton(toggleBtn);
        }
    },

    /**
     * Toggle to the next theme mode
     * Order: system -> light -> dark -> system
     */
    toggle() {
        switch (this.currentMode) {
            case this.MODES.SYSTEM:
                this.setMode(this.MODES.LIGHT, true);
                break;
            case this.MODES.LIGHT:
                this.setMode(this.MODES.DARK, true);
                break;
            case this.MODES.DARK:
                this.setMode(this.MODES.SYSTEM, true);
                break;
            default:
                this.setMode(this.MODES.SYSTEM, true);
        }
    },

    /**
     * Set the theme mode
     * @param {string} mode - 'system', 'light', or 'dark'
     * @param {boolean} animate - Whether to animate the icon change
     */
    setMode(mode, animate = false) {
        if (!Object.values(this.MODES).includes(mode)) {
            mode = this.MODES.SYSTEM;
        }

        this.currentMode = mode;
        localStorage.setItem(this.STORAGE_KEY, mode);
        this.applyTheme();

        // Update toggle button
        const toggleBtn = document.querySelector('.theme-toggle-btn');
        if (toggleBtn) {
            this.updateToggleButton(toggleBtn, animate);
        }
    },

    /**
     * Get the effective theme (light or dark) based on current mode
     * @returns {string} 'light' or 'dark'
     */
    getEffectiveTheme() {
        if (this.currentMode === this.MODES.SYSTEM) {
            return this.mediaQuery && this.mediaQuery.matches ? 'dark' : 'light';
        }
        return this.currentMode;
    },

    /**
     * Apply the current theme to the document
     */
    applyTheme() {
        const effectiveTheme = this.getEffectiveTheme();
        const root = document.documentElement;

        // Remove existing theme classes
        root.classList.remove('theme-light', 'theme-dark');

        // Add current theme class
        root.classList.add(`theme-${effectiveTheme}`);

        // Set data attribute for CSS selectors
        root.setAttribute('data-theme', effectiveTheme);

        // Set data attribute for current mode (for button state)
        root.setAttribute('data-theme-mode', this.currentMode);

        // Dispatch custom event for components that need to react
        window.dispatchEvent(new CustomEvent('themechange', {
            detail: {
                mode: this.currentMode,
                effectiveTheme: effectiveTheme
            }
        }));
    },

    /**
     * Handle system theme change
     * @param {MediaQueryListEvent} e - The media query change event
     */
    onSystemThemeChange(e) {
        // Only react if we're in system mode
        if (this.currentMode === this.MODES.SYSTEM) {
            this.applyTheme();
        }
    },

    /**
     * Update the toggle button icon and title
     * @param {HTMLElement} btn - The toggle button element
     * @param {boolean} animate - Whether to animate the icon change
     */
    updateToggleButton(btn, animate = false) {
        const icon = btn.querySelector('i');
        if (!icon) return;

        const updateIcon = () => {
            // Remove all theme icons
            icon.classList.remove('fa-desktop', 'fa-sun', 'fa-moon');

            // Set icon and title based on current mode
            switch (this.currentMode) {
                case this.MODES.SYSTEM:
                    icon.classList.add('fa-desktop');
                    btn.title = 'Theme: System (click for Light)';
                    break;
                case this.MODES.LIGHT:
                    icon.classList.add('fa-sun');
                    btn.title = 'Theme: Light (click for Dark)';
                    break;
                case this.MODES.DARK:
                    icon.classList.add('fa-moon');
                    btn.title = 'Theme: Dark (click for System)';
                    break;
            }

            // Update data attribute on button
            btn.setAttribute('data-mode', this.currentMode);
        };

        if (animate) {
            // Phase 1: Slide out old icon to the left
            btn.classList.remove('icon-slide-in');
            btn.classList.add('icon-slide-out');

            // Phase 2: After slide out, change icon and slide in from right
            setTimeout(() => {
                updateIcon();
                btn.classList.remove('icon-slide-out');
                btn.classList.add('icon-slide-in');

                // Clean up animation class
                setTimeout(() => {
                    btn.classList.remove('icon-slide-in');
                }, 150);
            }, 150);
        } else {
            updateIcon();
        }
    },

    /**
     * Check if current effective theme is dark
     * @returns {boolean}
     */
    isDark() {
        return this.getEffectiveTheme() === 'dark';
    },

    /**
     * Check if current effective theme is light
     * @returns {boolean}
     */
    isLight() {
        return this.getEffectiveTheme() === 'light';
    },

    /**
     * Get the current mode
     * @returns {string} 'system', 'light', or 'dark'
     */
    getMode() {
        return this.currentMode;
    }
};

// Auto-initialize when DOM is ready
document.addEventListener('DOMContentLoaded', () => {
    Theme.init();
});

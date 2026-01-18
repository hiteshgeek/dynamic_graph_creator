/**
 * Theme Manager - Handles light/dark/system theme switching
 * Cycles through: system -> light -> dark -> system
 * Persists choice to localStorage and responds to system theme changes
 */

window.Theme = {
    // Theme modes with sequence positions
    MODES: {
        LIGHT: 'light',   // position 0
        DARK: 'dark',     // position 1
        SYSTEM: 'system'  // position 2
    },

    // Mode sequence for determining animation direction
    MODE_SEQUENCE: ['light', 'dark', 'system'],

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
        // Load saved preference (default to light mode)
        this.currentMode = localStorage.getItem(this.STORAGE_KEY) || this.MODES.LIGHT;

        // Set up system theme change listener
        this.mediaQuery = window.matchMedia('(prefers-color-scheme: dark)');
        this.mediaQuery.addEventListener('change', (e) => this.onSystemThemeChange(e));

        // Apply initial theme
        this.applyTheme();

        // Set up toggle button if present
        this.initToggleButton();

        // Set up keyboard shortcuts (Alt+1/2/3)
        this.initKeyboardShortcuts();

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
     * Initialize keyboard shortcuts for theme switching
     * Alt+1: Light, Alt+2: Dark, Alt+3: System
     */
    initKeyboardShortcuts() {
        document.addEventListener('keydown', (e) => {
            if (!e.altKey) return;

            const prevMode = this.currentMode;
            let newMode = null;

            switch (e.key) {
                case '1':
                    newMode = this.MODES.LIGHT;
                    break;
                case '2':
                    newMode = this.MODES.DARK;
                    break;
                case '3':
                    newMode = this.MODES.SYSTEM;
                    break;
            }

            // Only change if it's a valid mode and different from current
            if (newMode && newMode !== prevMode) {
                e.preventDefault();
                this.setMode(newMode, true, prevMode);
            }
        });
    },

    /**
     * Toggle to the next theme mode
     * Order: light -> dark -> system -> light
     */
    toggle() {
        const prevMode = this.currentMode;
        let nextMode;

        switch (this.currentMode) {
            case this.MODES.LIGHT:
                nextMode = this.MODES.DARK;
                break;
            case this.MODES.DARK:
                nextMode = this.MODES.SYSTEM;
                break;
            case this.MODES.SYSTEM:
                nextMode = this.MODES.LIGHT;
                break;
            default:
                nextMode = this.MODES.LIGHT;
        }

        this.setMode(nextMode, true, prevMode);
    },

    /**
     * Set the theme mode
     * @param {string} mode - 'system', 'light', or 'dark'
     * @param {boolean} animate - Whether to animate the icon change
     * @param {string} prevMode - Previous mode for animation direction
     */
    setMode(mode, animate = false, prevMode = null) {
        if (!Object.values(this.MODES).includes(mode)) {
            mode = this.MODES.LIGHT;
        }

        const previousMode = prevMode || this.currentMode;
        this.currentMode = mode;
        localStorage.setItem(this.STORAGE_KEY, mode);
        this.applyTheme();

        // Update toggle button
        const toggleBtn = document.querySelector('.theme-toggle-btn');
        if (toggleBtn) {
            this.updateToggleButton(toggleBtn, animate, previousMode);
        }
    },

    /**
     * Get animation direction based on mode sequence
     * @param {string} fromMode - Previous mode
     * @param {string} toMode - New mode
     * @returns {string} 'left' or 'right'
     */
    getAnimationDirection(fromMode, toMode) {
        const fromIndex = this.MODE_SEQUENCE.indexOf(fromMode);
        const toIndex = this.MODE_SEQUENCE.indexOf(toMode);

        // Moving forward in sequence (light->dark, dark->system) = slide right
        // Moving backward/wrapping (system->light) = slide left
        if (toIndex > fromIndex) {
            return 'right';
        } else {
            return 'left';
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
     * Update the toggle button icon
     * @param {HTMLElement} btn - The toggle button element
     * @param {boolean} animate - Whether to animate the icon change
     * @param {string} prevMode - Previous mode for animation direction
     */
    updateToggleButton(btn, animate = false, prevMode = null) {
        const icon = btn.querySelector('i');
        if (!icon) return;

        const updateIcon = () => {
            // Remove all theme icons
            icon.classList.remove('fa-desktop', 'fa-sun', 'fa-moon');

            // Set icon based on current mode
            switch (this.currentMode) {
                case this.MODES.SYSTEM:
                    icon.classList.add('fa-desktop');
                    break;
                case this.MODES.LIGHT:
                    icon.classList.add('fa-sun');
                    break;
                case this.MODES.DARK:
                    icon.classList.add('fa-moon');
                    break;
            }

            // Update data attribute on button
            btn.setAttribute('data-mode', this.currentMode);
        };

        if (animate && prevMode) {
            const direction = this.getAnimationDirection(prevMode, this.currentMode);

            // Remove any existing animation classes
            btn.classList.remove('icon-slide-in-left', 'icon-slide-in-right', 'icon-slide-out-left', 'icon-slide-out-right');

            // Phase 1: Slide out in the appropriate direction
            if (direction === 'right') {
                btn.classList.add('icon-slide-out-left');
            } else {
                btn.classList.add('icon-slide-out-right');
            }

            // Phase 2: After slide out, change icon and slide in from opposite direction
            setTimeout(() => {
                updateIcon();
                btn.classList.remove('icon-slide-out-left', 'icon-slide-out-right');

                if (direction === 'right') {
                    btn.classList.add('icon-slide-in-right');
                } else {
                    btn.classList.add('icon-slide-in-left');
                }

                // Clean up animation class
                setTimeout(() => {
                    btn.classList.remove('icon-slide-in-left', 'icon-slide-in-right');
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

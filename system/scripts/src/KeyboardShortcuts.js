/**
 * KeyboardShortcuts - Global keyboard shortcut handler
 * Can be used across any module in the application
 */

export default class KeyboardShortcuts {
    constructor() {
        this.shortcuts = new Map();
        this.enabled = true;
        this.boundHandler = this.handleKeyDown.bind(this);
    }

    /**
     * Initialize the keyboard shortcuts handler
     */
    init() {
        document.addEventListener('keydown', this.boundHandler);
        return this;
    }

    /**
     * Destroy the handler and remove event listeners
     */
    destroy() {
        document.removeEventListener('keydown', this.boundHandler);
        this.shortcuts.clear();
    }

    /**
     * Enable/disable all shortcuts
     */
    setEnabled(enabled) {
        this.enabled = enabled;
    }

    /**
     * Register a keyboard shortcut
     * @param {string} key - The key combination (e.g., 'Escape', 'ctrl+s', 'ctrl+shift+z')
     * @param {Function} callback - The function to call when shortcut is triggered
     * @param {Object} options - Additional options
     * @param {string} options.description - Description of the shortcut
     * @param {boolean} options.preventDefault - Whether to prevent default action (default: true)
     * @param {boolean} options.stopPropagation - Whether to stop event propagation (default: false)
     * @param {string} options.scope - Scope identifier to group shortcuts
     */
    register(key, callback, options = {}) {
        const normalizedKey = this.normalizeKey(key);
        const shortcut = {
            key: normalizedKey,
            callback,
            description: options.description || '',
            preventDefault: options.preventDefault !== false,
            stopPropagation: options.stopPropagation || false,
            scope: options.scope || 'global',
            enabled: true
        };

        if (!this.shortcuts.has(normalizedKey)) {
            this.shortcuts.set(normalizedKey, []);
        }
        this.shortcuts.get(normalizedKey).push(shortcut);

        return this;
    }

    /**
     * Unregister a keyboard shortcut
     * @param {string} key - The key combination to unregister
     * @param {Function} callback - Optional specific callback to remove
     */
    unregister(key, callback = null) {
        const normalizedKey = this.normalizeKey(key);

        if (!this.shortcuts.has(normalizedKey)) return this;

        if (callback) {
            const shortcuts = this.shortcuts.get(normalizedKey);
            const filtered = shortcuts.filter(s => s.callback !== callback);
            if (filtered.length > 0) {
                this.shortcuts.set(normalizedKey, filtered);
            } else {
                this.shortcuts.delete(normalizedKey);
            }
        } else {
            this.shortcuts.delete(normalizedKey);
        }

        return this;
    }

    /**
     * Unregister all shortcuts for a specific scope
     * @param {string} scope - The scope to clear
     */
    unregisterScope(scope) {
        for (const [key, shortcuts] of this.shortcuts) {
            const filtered = shortcuts.filter(s => s.scope !== scope);
            if (filtered.length > 0) {
                this.shortcuts.set(key, filtered);
            } else {
                this.shortcuts.delete(key);
            }
        }
        return this;
    }

    /**
     * Enable/disable shortcuts for a specific scope
     * @param {string} scope - The scope to enable/disable
     * @param {boolean} enabled - Whether to enable or disable
     */
    setScopeEnabled(scope, enabled) {
        for (const shortcuts of this.shortcuts.values()) {
            shortcuts.forEach(s => {
                if (s.scope === scope) {
                    s.enabled = enabled;
                }
            });
        }
        return this;
    }

    /**
     * Normalize a key combination string
     * @param {string} key - The key combination
     * @returns {string} Normalized key string
     */
    normalizeKey(key) {
        const parts = key.toLowerCase().split('+').map(p => p.trim());
        const modifiers = [];
        let mainKey = '';

        parts.forEach(part => {
            if (['ctrl', 'control'].includes(part)) {
                modifiers.push('ctrl');
            } else if (['alt', 'option'].includes(part)) {
                modifiers.push('alt');
            } else if (['shift'].includes(part)) {
                modifiers.push('shift');
            } else if (['meta', 'cmd', 'command', 'win', 'windows'].includes(part)) {
                modifiers.push('meta');
            } else {
                mainKey = part;
            }
        });

        // Sort modifiers for consistent comparison
        modifiers.sort();

        return [...modifiers, mainKey].join('+');
    }

    /**
     * Get the key combination from a keyboard event
     * @param {KeyboardEvent} event - The keyboard event
     * @returns {string} The key combination string
     */
    getKeyFromEvent(event) {
        const modifiers = [];

        if (event.ctrlKey) modifiers.push('ctrl');
        if (event.altKey) modifiers.push('alt');
        if (event.shiftKey) modifiers.push('shift');
        if (event.metaKey) modifiers.push('meta');

        modifiers.sort();

        let key = event.key.toLowerCase();

        // Normalize common key names
        const keyMap = {
            'escape': 'escape',
            'esc': 'escape',
            'enter': 'enter',
            'return': 'enter',
            'backspace': 'backspace',
            'delete': 'delete',
            'tab': 'tab',
            'space': ' ',
            ' ': 'space',
            'arrowup': 'arrowup',
            'arrowdown': 'arrowdown',
            'arrowleft': 'arrowleft',
            'arrowright': 'arrowright'
        };

        key = keyMap[key] || key;

        return [...modifiers, key].join('+');
    }

    /**
     * Handle keydown events
     * @param {KeyboardEvent} event - The keyboard event
     */
    handleKeyDown(event) {
        if (!this.enabled) return;

        // Don't trigger shortcuts when typing in inputs (unless it's Escape)
        const target = event.target;
        const isInput = target.tagName === 'INPUT' ||
                       target.tagName === 'TEXTAREA' ||
                       target.tagName === 'SELECT' ||
                       target.isContentEditable;

        const keyCombo = this.getKeyFromEvent(event);
        const shortcuts = this.shortcuts.get(keyCombo);

        if (!shortcuts || shortcuts.length === 0) return;

        for (const shortcut of shortcuts) {
            if (!shortcut.enabled) continue;

            // Allow Escape key and shortcuts with Ctrl/Meta modifiers even in inputs
            const hasModifier = keyCombo.includes('ctrl+') || keyCombo.includes('meta+');
            if (isInput && keyCombo !== 'escape' && !hasModifier) continue;

            if (shortcut.preventDefault) {
                event.preventDefault();
            }
            if (shortcut.stopPropagation) {
                event.stopPropagation();
            }

            shortcut.callback(event);
        }
    }

    /**
     * Get all registered shortcuts
     * @param {string} scope - Optional scope to filter by
     * @returns {Array} Array of shortcut objects
     */
    getShortcuts(scope = null) {
        const result = [];

        for (const [key, shortcuts] of this.shortcuts) {
            shortcuts.forEach(s => {
                if (!scope || s.scope === scope) {
                    result.push({
                        key: key,
                        description: s.description,
                        scope: s.scope,
                        enabled: s.enabled
                    });
                }
            });
        }

        return result;
    }

    /**
     * Register common global shortcuts
     * Call this to set up application-wide shortcuts
     */
    registerGlobalShortcuts() {
        // Add global shortcuts here as needed
        return this;
    }
}

// Create singleton instance for global use
export const keyboardShortcuts = new KeyboardShortcuts();

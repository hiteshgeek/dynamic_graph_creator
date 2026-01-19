/**
 * Common utilities shared between graph and filter modules
 */

// Import Theme.js - bundled into common.js
import './Theme.js';

window.Toast = {
    container: null,
    init() {
        this.container = document.createElement('div');
        this.container.className = 'dgc-toast-container';
        document.body.appendChild(this.container);
    },
    show(message, type = 'success', duration = 3000) {
        if (!this.container) this.init();
        const toast = document.createElement('div');
        toast.className = `dgc-toast ${type}`;
        const icons = { success: 'fa-check-circle', error: 'fa-exclamation-circle', warning: 'fa-exclamation-triangle', info: 'fa-info-circle' };
        toast.innerHTML = `<span class="dgc-toast-indicator"></span><i class="fas ${icons[type] || icons.success}"></i><span class="dgc-toast-message">${message}</span><button class="dgc-toast-close"><i class="fas fa-times"></i></button>`;
        toast.querySelector('.dgc-toast-close').addEventListener('click', () => toast.remove());
        this.container.appendChild(toast);
        if (duration > 0) setTimeout(() => toast.remove(), duration);
    },
    success(message) { this.show(message, 'success'); },
    error(message) { this.show(message, 'error', 5000); },
    warning(message) { this.show(message, 'warning', 4000); },
    info(message) { this.show(message, 'info'); }
};

window.Loading = {
    overlay: null,
    show(message = 'Loading...') {
        if (!this.overlay) {
            this.overlay = document.createElement('div');
            this.overlay.className = 'loading-overlay';
            this.overlay.innerHTML = `<div class="loading-spinner"><div class="spinner"></div><span>${message}</span></div>`;
            document.body.appendChild(this.overlay);
        }
        this.overlay.querySelector('span').textContent = message;
        this.overlay.classList.add('active');
    },
    hide() { if (this.overlay) this.overlay.classList.remove('active'); }
};

window.Ajax = {
    getBaseUrl() {
        // Include query string to preserve routing (e.g., ?urlq=data-filter)
        return window.location.pathname + window.location.search;
    },
    async post(submit, data = {}) {
        const formData = new FormData();
        formData.append('submit', submit);
        for (const key in data) {
            formData.append(key, typeof data[key] === 'object' ? JSON.stringify(data[key]) : data[key]);
        }
        const response = await fetch(this.getBaseUrl(), { method: 'POST', body: formData, headers: { 'X-Requested-With': 'XMLHttpRequest' } });
        return response.json();
    }
};

window.ConfirmDialog = {
    modalElement: null,

    init() {
        if (this.modalElement) return;

        const modalHtml = `
            <div class="modal fade" id="confirmModal" tabindex="-1" aria-hidden="true">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title">Confirm</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <p class="confirm-message">Are you sure?</p>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-sm btn-secondary cancel-delete" data-bs-dismiss="modal">Cancel</button>
                            <button type="button" class="btn btn-sm btn-danger confirm-delete">Confirm</button>
                        </div>
                    </div>
                </div>
            </div>
        `;

        document.body.insertAdjacentHTML('beforeend', modalHtml);
        this.modalElement = document.getElementById('confirmModal');
    },

    show(options = {}) {
        this.init();

        const {
            message = 'Are you sure?',
            title = 'Confirm',
            confirmText = 'Confirm',
            cancelText = 'Cancel',
            confirmClass = 'btn-danger'
        } = options;

        // Update modal content
        this.modalElement.querySelector('.modal-title').textContent = title;
        this.modalElement.querySelector('.confirm-message').innerHTML = message;
        this.modalElement.querySelector('.confirm-delete').textContent = confirmText;
        this.modalElement.querySelector('.cancel-delete').textContent = cancelText;

        // Update button class
        const confirmBtn = this.modalElement.querySelector('.confirm-delete');
        confirmBtn.className = `btn btn-sm ${confirmClass} confirm-delete`;

        return new Promise((resolve) => {
            const modal = new bootstrap.Modal(this.modalElement);

            const confirmHandler = () => {
                cleanup();
                modal.hide();
                resolve(true);
            };

            const cancelHandler = () => {
                cleanup();
                modal.hide();
                resolve(false);
            };

            const cleanup = () => {
                this.modalElement.querySelector('.confirm-delete').removeEventListener('click', confirmHandler);
                this.modalElement.querySelector('.cancel-delete').removeEventListener('click', cancelHandler);
                this.modalElement.removeEventListener('hidden.bs.modal', cancelHandler);
            };

            this.modalElement.querySelector('.confirm-delete').addEventListener('click', confirmHandler);
            this.modalElement.querySelector('.cancel-delete').addEventListener('click', cancelHandler);
            this.modalElement.addEventListener('hidden.bs.modal', cancelHandler, { once: true });

            modal.show();
        });
    },

    confirm(message, title = 'Confirm') {
        return this.show({ message, title });
    },

    delete(message = 'Are you sure you want to delete this item?', title = 'Confirm Delete') {
        return this.show({
            message,
            title,
            confirmText: 'Delete',
            confirmClass: 'btn-danger'
        });
    }
};

/**
 * FormValidator - Reusable form validation with Bootstrap feedback classes
 *
 * Usage:
 * const validator = new FormValidator('#my-form', {
 *     rules: {
 *         'field-name': {
 *             required: true,
 *             minLength: 3,
 *             maxLength: 100,
 *             pattern: /^[a-z]+$/i,
 *             custom: (value) => value !== 'invalid' || 'Value cannot be "invalid"'
 *         }
 *     },
 *     messages: {
 *         'field-name': {
 *             required: 'Custom required message',
 *             minLength: 'Must be at least 3 characters'
 *         }
 *     },
 *     onSubmit: async (data) => { ... }
 * });
 */
window.FormValidator = class FormValidator {
    constructor(formSelector, options = {}) {
        this.form = typeof formSelector === 'string'
            ? document.querySelector(formSelector)
            : formSelector;

        if (!this.form) {
            console.error('FormValidator: Form not found');
            return;
        }

        this.options = {
            rules: {},
            messages: {},
            onSubmit: null,
            showToastOnError: true,
            toastMessage: 'Please correct the errors in the form',
            validateOnBlur: true,
            validateOnInput: true,
            ...options
        };

        this.init();
    }

    init() {
        // Disable browser validation
        this.form.setAttribute('novalidate', 'true');

        // Bind submit handler
        this.form.addEventListener('submit', (e) => this.handleSubmit(e));

        // Bind live validation events
        if (this.options.validateOnBlur || this.options.validateOnInput) {
            this.bindLiveValidation();
        }
    }

    bindLiveValidation() {
        const fields = this.form.querySelectorAll('input, select, textarea');

        fields.forEach(field => {
            const name = field.name || field.id;
            if (!name) return;

            if (this.options.validateOnBlur) {
                field.addEventListener('blur', () => this.validateField(field));
            }

            if (this.options.validateOnInput) {
                const eventType = field.tagName === 'SELECT' ? 'change' : 'input';
                field.addEventListener(eventType, () => {
                    // Only clear error on input if field was previously invalid
                    if (field.classList.contains('is-invalid')) {
                        this.validateField(field);
                    }
                });
            }
        });
    }

    getFieldRules(field) {
        const name = field.name || field.id;
        const rules = this.options.rules[name] || {};

        // Auto-detect rules from HTML attributes
        if (field.hasAttribute('required') && rules.required === undefined) {
            rules.required = true;
        }
        if (field.hasAttribute('minlength') && rules.minLength === undefined) {
            rules.minLength = parseInt(field.getAttribute('minlength'));
        }
        if (field.hasAttribute('maxlength') && rules.maxLength === undefined) {
            rules.maxLength = parseInt(field.getAttribute('maxlength'));
        }
        if (field.hasAttribute('pattern') && rules.pattern === undefined) {
            rules.pattern = new RegExp(field.getAttribute('pattern'));
        }
        if (field.type === 'email' && rules.email === undefined) {
            rules.email = true;
        }
        if (field.type === 'number') {
            if (field.hasAttribute('min') && rules.min === undefined) {
                rules.min = parseFloat(field.getAttribute('min'));
            }
            if (field.hasAttribute('max') && rules.max === undefined) {
                rules.max = parseFloat(field.getAttribute('max'));
            }
        }

        return rules;
    }

    getFieldLabel(field) {
        const name = field.name || field.id;
        // Try to find associated label
        const label = this.form.querySelector(`label[for="${field.id}"]`);
        if (label) {
            return label.textContent.replace(/\s*\*\s*$/, '').trim();
        }
        // Fallback to name
        return name.replace(/[-_]/g, ' ').replace(/\b\w/g, l => l.toUpperCase());
    }

    getMessage(field, rule) {
        const name = field.name || field.id;
        const customMessages = this.options.messages[name] || {};

        if (customMessages[rule]) {
            return customMessages[rule];
        }

        const label = this.getFieldLabel(field);
        const rules = this.getFieldRules(field);

        const defaultMessages = {
            required: `${label} is required`,
            minLength: `${label} must be at least ${rules.minLength} characters`,
            maxLength: `${label} must be no more than ${rules.maxLength} characters`,
            email: `Please enter a valid email address`,
            pattern: `${label} format is invalid`,
            min: `${label} must be at least ${rules.min}`,
            max: `${label} must be no more than ${rules.max}`,
            custom: `${label} is invalid`
        };

        return defaultMessages[rule] || `${label} is invalid`;
    }

    validateField(field) {
        const rules = this.getFieldRules(field);
        const value = field.value.trim();
        let error = null;

        // Required
        if (rules.required && !value) {
            error = this.getMessage(field, 'required');
        }
        // MinLength
        else if (rules.minLength && value && value.length < rules.minLength) {
            error = this.getMessage(field, 'minLength');
        }
        // MaxLength
        else if (rules.maxLength && value && value.length > rules.maxLength) {
            error = this.getMessage(field, 'maxLength');
        }
        // Email
        else if (rules.email && value && !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(value)) {
            error = this.getMessage(field, 'email');
        }
        // Pattern
        else if (rules.pattern && value && !rules.pattern.test(value)) {
            error = this.getMessage(field, 'pattern');
        }
        // Min (number)
        else if (rules.min !== undefined && value && parseFloat(value) < rules.min) {
            error = this.getMessage(field, 'min');
        }
        // Max (number)
        else if (rules.max !== undefined && value && parseFloat(value) > rules.max) {
            error = this.getMessage(field, 'max');
        }
        // Custom validation (runs regardless of value to support conditional required)
        if (!error && rules.custom) {
            const result = rules.custom(value, field, this.form);
            if (result !== true && result) {
                error = typeof result === 'string' ? result : this.getMessage(field, 'custom');
            }
        }

        this.setFieldError(field, error);
        return !error;
    }

    setFieldError(field, error) {
        // Find or create feedback element
        let feedback = field.parentElement.querySelector('.invalid-feedback');

        if (error) {
            field.classList.add('is-invalid');
            field.classList.remove('is-valid');

            if (!feedback) {
                feedback = document.createElement('div');
                feedback.className = 'invalid-feedback';
                field.parentElement.appendChild(feedback);
            }
            feedback.textContent = error;
        } else {
            field.classList.remove('is-invalid');
            // Don't add is-valid class - we only show errors
            if (feedback) {
                feedback.textContent = '';
            }
        }
    }

    validate() {
        const fields = this.form.querySelectorAll('input, select, textarea');
        let isValid = true;
        let firstInvalidField = null;

        fields.forEach(field => {
            const name = field.name || field.id;
            if (!name) return;

            const fieldValid = this.validateField(field);
            if (!fieldValid && isValid) {
                isValid = false;
                firstInvalidField = field;
            }
        });

        // Focus first invalid field
        if (firstInvalidField) {
            firstInvalidField.focus();
        }

        return isValid;
    }

    clearErrors() {
        const fields = this.form.querySelectorAll('.is-invalid');
        fields.forEach(field => {
            field.classList.remove('is-invalid');
            const feedback = field.parentElement.querySelector('.invalid-feedback');
            if (feedback) {
                feedback.textContent = '';
            }
        });
    }

    getFormData() {
        const formData = new FormData(this.form);
        const data = {};
        for (const [key, value] of formData.entries()) {
            data[key] = value;
        }
        return data;
    }

    async handleSubmit(e) {
        e.preventDefault();

        const isValid = this.validate();

        if (!isValid) {
            if (this.options.showToastOnError) {
                Toast.error(this.options.toastMessage);
            }
            return;
        }

        if (this.options.onSubmit) {
            await this.options.onSubmit(this.getFormData(), this.form);
        }
    }

    // Static method for quick validation without form binding
    static validateRequired(value, message = 'This field is required') {
        return value && value.trim() ? true : message;
    }
};

/**
 * IdGenerator - Centralized UUID-based ID generation
 * Matches PHP Utility::generateUUID() and Utility::generateShortId()
 */
window.IdGenerator = {
    /**
     * Generate a UUID v4
     * Format: xxxxxxxx-xxxx-4xxx-yxxx-xxxxxxxxxxxx
     */
    generateUUID() {
        return 'xxxxxxxx-xxxx-4xxx-yxxx-xxxxxxxxxxxx'.replace(/[xy]/g, function(c) {
            const r = Math.random() * 16 | 0;
            const v = c === 'x' ? r : (r & 0x3 | 0x8);
            return v.toString(16);
        });
    },

    /**
     * Generate a short unique ID (8 characters from UUID)
     * @param {string} prefix - Optional prefix for the ID
     * @returns {string} Short unique ID with optional prefix
     */
    generateShortId(prefix = '') {
        const uuid = this.generateUUID().replace(/-/g, '');
        const shortId = uuid.substring(0, 8);
        return prefix ? `${prefix}-${shortId}` : shortId;
    },

    /**
     * Generate unique section ID
     * Format: s-xxxxxxxx
     */
    sectionId() {
        return this.generateShortId('s');
    },

    /**
     * Generate unique area ID
     * Format: a-xxxxxxxx
     */
    areaId() {
        return this.generateShortId('a');
    },

    /**
     * Generate unique row ID
     * Format: r-xxxxxxxx
     */
    rowId() {
        return this.generateShortId('r');
    }
};

/**
 * KeyboardShortcuts - Global keyboard shortcut handler
 * Provides application-wide keyboard shortcuts
 */
window.KeyboardShortcuts = {
    shortcuts: new Map(),
    enabled: true,
    initialized: false,
    modalElement: null,

    init() {
        if (this.initialized) return this;
        this.initialized = true;
        document.addEventListener('keydown', (e) => this.handleKeyDown(e));
        this.registerGlobalShortcuts();
        return this;
    },

    register(key, callback, options = {}) {
        const normalizedKey = this.normalizeKey(key);
        const shortcut = {
            key: normalizedKey,
            callback,
            description: options.description || '',
            preventDefault: options.preventDefault !== false,
            scope: options.scope || 'global',
            enabled: true,
            available: options.available || null
        };

        if (!this.shortcuts.has(normalizedKey)) {
            this.shortcuts.set(normalizedKey, []);
        }
        this.shortcuts.get(normalizedKey).push(shortcut);
        return this;
    },

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
    },

    normalizeKey(key) {
        const parts = key.toLowerCase().split('+').map(p => p.trim());
        const modifiers = [];
        let mainKey = '';

        parts.forEach(part => {
            if (['ctrl', 'control'].includes(part)) modifiers.push('ctrl');
            else if (['alt', 'option'].includes(part)) modifiers.push('alt');
            else if (['shift'].includes(part)) modifiers.push('shift');
            else if (['meta', 'cmd', 'command'].includes(part)) modifiers.push('meta');
            else mainKey = part;
        });

        modifiers.sort();
        return [...modifiers, mainKey].join('+');
    },

    getKeyFromEvent(event) {
        const modifiers = [];
        if (event.ctrlKey) modifiers.push('ctrl');
        if (event.altKey) modifiers.push('alt');
        if (event.shiftKey) modifiers.push('shift');
        if (event.metaKey) modifiers.push('meta');
        modifiers.sort();

        let key = event.key.toLowerCase();
        const keyMap = {
            'escape': 'escape', 'esc': 'escape',
            'enter': 'enter', 'return': 'enter',
            ' ': 'space'
        };
        key = keyMap[key] || key;

        return [...modifiers, key].join('+');
    },

    handleKeyDown(event) {
        if (!this.enabled) return;

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

            // Allow Escape and shortcuts with Ctrl/Meta/Alt even in inputs
            const hasModifier = keyCombo.includes('ctrl+') || keyCombo.includes('meta+') || keyCombo.includes('alt+');
            if (isInput && keyCombo !== 'escape' && !hasModifier) continue;

            if (shortcut.preventDefault) {
                event.preventDefault();
            }
            shortcut.callback(event);
        }
    },

    /**
     * Navigate to previous or next item in sidebar navigation
     * @param {string} direction - 'prev' or 'next'
     */
    navigateSidebarItem(direction) {
        // Find all nav items (graph view or filter form)
        const items = document.querySelectorAll('.graph-nav-item, .filter-nav-item');
        if (items.length === 0) return;

        // Find current active item
        const activeItem = document.querySelector('.graph-nav-item.active, .filter-nav-item.active');
        if (!activeItem) return;

        const itemsArray = Array.from(items);
        const currentIndex = itemsArray.indexOf(activeItem);

        let targetIndex;
        if (direction === 'prev') {
            targetIndex = currentIndex > 0 ? currentIndex - 1 : itemsArray.length - 1;
        } else {
            targetIndex = currentIndex < itemsArray.length - 1 ? currentIndex + 1 : 0;
        }

        const targetItem = itemsArray[targetIndex];
        if (targetItem && targetItem.href) {
            window.location.href = targetItem.href;
        }
    },

    registerGlobalShortcuts() {
        // F1: Toggle keyboard shortcuts help
        this.register('f1', () => {
            this.toggleHelpModal();
        }, {
            description: 'Toggle keyboard shortcuts',
            scope: 'global'
        });

        // Escape: Close help modal if open
        this.register('escape', () => {
            this.hideHelpModal();
        }, {
            description: 'Close modal/dialog',
            scope: 'global',
            preventDefault: false
        });

        // Alt+M: Toggle theme (system -> light -> dark -> system)
        this.register('alt+m', () => {
            if (typeof Theme !== 'undefined' && Theme.toggle) {
                Theme.toggle();
            }
        }, {
            description: 'Cycle theme mode',
            scope: 'theme'
        });

        // Alt+1: Set light theme
        this.register('alt+1', () => {
            if (typeof Theme !== 'undefined' && Theme.setMode) {
                Theme.setMode('light', true, Theme.currentMode);
            }
        }, {
            description: 'Light theme',
            scope: 'theme'
        });

        // Alt+2: Set dark theme
        this.register('alt+2', () => {
            if (typeof Theme !== 'undefined' && Theme.setMode) {
                Theme.setMode('dark', true, Theme.currentMode);
            }
        }, {
            description: 'Dark theme',
            scope: 'theme'
        });

        // Alt+3: Set system theme
        this.register('alt+3', () => {
            if (typeof Theme !== 'undefined' && Theme.setMode) {
                Theme.setMode('system', true, Theme.currentMode);
            }
        }, {
            description: 'System theme',
            scope: 'theme'
        });

        // Alt+T: Toggle tweak switch (layout edit mode)
        this.register('alt+t', () => {
            const tweakSwitch = document.getElementById('toggle-layout-edit-switch');
            if (tweakSwitch) {
                tweakSwitch.checked = !tweakSwitch.checked;
                // Trigger change event for any listeners
                tweakSwitch.dispatchEvent(new Event('change', { bubbles: true }));
            }
        }, {
            description: 'Toggle tweak mode',
            scope: 'dashboard-builder',
            available: () => !!document.getElementById('toggle-layout-edit-switch')
        });

        // Alt+V: Toggle between View Mode and Design Mode
        this.register('alt+v', () => {
            // Look for View Mode button first (we're in design/edit mode)
            const viewModeBtn = document.querySelector('.btn-view-mode');
            if (viewModeBtn) {
                viewModeBtn.click();
                return;
            }

            // Look for Design Mode button (we're in view/preview mode)
            const designModeBtn = document.querySelector('.btn-design-mode');
            if (designModeBtn) {
                designModeBtn.click();
                return;
            }
        }, {
            description: 'Toggle View/Design mode',
            scope: 'dashboard-builder',
            available: () => !!(document.querySelector('.btn-view-mode') || document.querySelector('.btn-design-mode'))
        });

        // Ctrl+S: Save (triggers any save button with data-save-btn attribute)
        this.register('ctrl+s', () => {
            const saveBtn = document.querySelector('[data-save-btn]');
            if (saveBtn && !saveBtn.disabled) {
                saveBtn.click();
            }
        }, {
            description: 'Save',
            scope: 'global',
            available: () => !!document.querySelector('[data-save-btn]')
        });

        // Alt+B: Go back to list page
        this.register('alt+b', () => {
            const backBtn = document.querySelector('[data-back-to-list]');
            if (backBtn) {
                backBtn.click();
            }
        }, {
            description: 'Back to list',
            scope: 'global',
            available: () => !!document.querySelector('[data-back-to-list]')
        });

        // Alt+Up: Navigate to previous item in sidebar
        this.register('alt+arrowup', () => {
            this.navigateSidebarItem('prev');
        }, {
            description: 'Previous item',
            scope: 'global',
            available: () => !!document.querySelector('.graph-nav-item, .filter-nav-item')
        });

        // Alt+Down: Navigate to next item in sidebar
        this.register('alt+arrowdown', () => {
            this.navigateSidebarItem('next');
        }, {
            description: 'Next item',
            scope: 'global',
            available: () => !!document.querySelector('.graph-nav-item, .filter-nav-item')
        });

        // Graph Creator: Alt+O to toggle sidebar
        this.register('alt+o', () => {
            const sidebar = document.querySelector('.graph-creator .graph-sidebar-left');
            const card = sidebar?.querySelector('.sidebar-card');
            if (card) {
                card.classList.toggle('collapsed');
            }
            if (sidebar) {
                sidebar.classList.toggle('collapsed');
                localStorage.setItem('graphCreatorSidebarCollapsed', sidebar.classList.contains('collapsed') ? 'true' : 'false');
                // Trigger chart resize after animation
                setTimeout(() => {
                    if (window.graphCreator?.preview) {
                        window.graphCreator.preview.resize();
                    }
                }, 350);
            }
        }, {
            description: 'Toggle options panel',
            scope: 'graph-creator',
            available: () => !!document.querySelector('.graph-creator .graph-sidebar-left')
        });

        return this;
    },

    /**
     * Get all registered shortcuts with their availability status
     * @returns {Array} Array of shortcut info objects
     */
    getAllShortcuts() {
        const result = [];
        this.shortcuts.forEach((handlers, key) => {
            handlers.forEach(handler => {
                // Check if shortcut is available on current page
                let isAvailable = true;
                if (typeof handler.available === 'function') {
                    isAvailable = handler.available();
                }
                result.push({
                    key: key,
                    displayKey: this.formatKeyForDisplay(key),
                    description: handler.description,
                    scope: handler.scope,
                    available: isAvailable
                });
            });
        });
        return result;
    },

    /**
     * Format key combo for display (e.g., "alt+m" -> "Alt + M")
     * @param {string} key - Normalized key string
     * @returns {string} Formatted display string
     */
    formatKeyForDisplay(key) {
        const parts = key.split('+');
        return parts.map(part => {
            if (part === 'alt') return 'Alt';
            if (part === 'ctrl') return 'Ctrl';
            if (part === 'shift') return 'Shift';
            if (part === 'meta') return 'Cmd';
            if (part === 'escape') return 'Esc';
            if (part === 'f1') return 'F1';
            if (part === 'arrowup') return '↑';
            if (part === 'arrowdown') return '↓';
            if (part === 'arrowleft') return '←';
            if (part === 'arrowright') return '→';
            return part.toUpperCase();
        }).join(' + ');
    },

    /**
     * Create and show the keyboard shortcuts help modal
     */
    showHelpModal() {
        // Remove existing modal if any
        this.hideHelpModal();

        // Close navigation shortcuts modal if open
        const navModal = document.getElementById('nav-shortcuts-modal');
        if (navModal) {
            const navModalInstance = bootstrap.Modal.getInstance(navModal);
            if (navModalInstance) {
                navModalInstance.hide();
            }
        }

        const shortcuts = this.getAllShortcuts();

        // Group shortcuts by scope
        const grouped = {};
        shortcuts.forEach(shortcut => {
            const scope = shortcut.scope || 'global';
            if (!grouped[scope]) {
                grouped[scope] = [];
            }
            grouped[scope].push(shortcut);
        });

        // Build modal HTML
        let shortcutsHtml = '';
        const scopeLabels = {
            'global': 'Global',
            'theme': 'Theme',
            'dashboard-builder': 'Dashboard / Template / Graph',
            'graph-creator': 'Graph Creator',
            'filter': 'Filter Page'
        };

        // Sort scopes: global first, theme second, then alphabetically
        const scopeOrder = ['global', 'theme'];
        const sortedScopes = Object.keys(grouped).sort((a, b) => {
            const aIndex = scopeOrder.indexOf(a);
            const bIndex = scopeOrder.indexOf(b);
            if (aIndex !== -1 && bIndex !== -1) return aIndex - bIndex;
            if (aIndex !== -1) return -1;
            if (bIndex !== -1) return 1;
            return a.localeCompare(b);
        });

        sortedScopes.forEach(scope => {
            const scopeLabel = scopeLabels[scope] || scope;
            shortcutsHtml += `<div class="shortcuts-group">
                <div class="shortcuts-group-header" data-scope="${scope}">
                    <div class="shortcuts-group-title">${scopeLabel}</div>
                    <i class="fas fa-chevron-up shortcuts-group-toggle"></i>
                </div>
                <div class="shortcuts-list">`;

            grouped[scope].forEach(shortcut => {
                const disabledClass = shortcut.available ? '' : 'disabled';
                const keyParts = shortcut.displayKey.split(' + ');
                const keysHtml = keyParts.map(k => `<kbd>${k}</kbd>`).join('<span class="key-separator">+</span>');

                shortcutsHtml += `
                    <div class="shortcut-item ${disabledClass}">
                        <div class="shortcut-keys">${keysHtml}</div>
                        <div class="shortcut-description">${shortcut.description}</div>
                    </div>`;
            });

            shortcutsHtml += `</div></div>`;
        });

        const modalHtml = `
            <div class="modal fade" id="keyboardShortcutsModal" tabindex="-1" aria-labelledby="keyboardShortcutsModalLabel" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content keyboard-shortcuts-modal">
                        <div class="modal-header">
                            <h5 class="modal-title" id="keyboardShortcutsModalLabel">
                                <i class="fas fa-keyboard"></i> Keyboard Shortcuts
                            </h5>
                            <div class="shortcuts-expand-collapse">
                                <button type="button" class="btn btn-sm btn-link" id="shortcuts-toggle-all">Collapse All</button>
                            </div>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            ${shortcutsHtml}
                            <div class="shortcuts-hint">
                                <i class="fas fa-info-circle"></i>
                                Grayed out shortcuts are not available on this page
                            </div>
                        </div>
                    </div>
                </div>
            </div>`;

        document.body.insertAdjacentHTML('beforeend', modalHtml);
        this.modalElement = document.getElementById('keyboardShortcutsModal');

        // Add click handlers for section collapse/expand
        this.modalElement.querySelectorAll('.shortcuts-group-header').forEach(header => {
            header.addEventListener('click', () => {
                const group = header.closest('.shortcuts-group');
                group.classList.toggle('collapsed');
            });
        });

        // Add toggle all handler
        const toggleBtn = this.modalElement.querySelector('#shortcuts-toggle-all');
        toggleBtn.addEventListener('click', () => {
            const groups = this.modalElement.querySelectorAll('.shortcuts-group');
            const allCollapsed = Array.from(groups).every(g => g.classList.contains('collapsed'));

            groups.forEach(group => {
                if (allCollapsed) {
                    group.classList.remove('collapsed');
                } else {
                    group.classList.add('collapsed');
                }
            });

            toggleBtn.textContent = allCollapsed ? 'Collapse All' : 'Expand All';
        });

        // Show modal using Bootstrap
        const modal = new bootstrap.Modal(this.modalElement);
        modal.show();

        // Clean up modal element when hidden
        this.modalElement.addEventListener('hidden.bs.modal', () => {
            this.modalElement.remove();
            this.modalElement = null;
        }, { once: true });
    },

    /**
     * Hide the keyboard shortcuts help modal
     */
    hideHelpModal() {
        if (this.modalElement) {
            const modal = bootstrap.Modal.getInstance(this.modalElement);
            if (modal) {
                modal.hide();
            }
        }
    },

    /**
     * Toggle the keyboard shortcuts help modal
     */
    toggleHelpModal() {
        if (this.modalElement) {
            this.hideHelpModal();
        } else {
            this.showHelpModal();
        }
    }
};

/**
 * Tooltips - Bootstrap tooltip initialization helper
 * Use Tooltips.init() to initialize/reinitialize all tooltips on the page
 * Use Tooltips.disposeAll() before removing elements to clean up tooltips
 */
window.Tooltips = {
    init() {
        // Dispose existing tooltips to prevent duplicates
        this.disposeAll();

        // Initialize new tooltips with delay for slower appearance
        const tooltipTriggerList = document.querySelectorAll('[data-bs-toggle="tooltip"]');
        tooltipTriggerList.forEach(tooltipTriggerEl => {
            new bootstrap.Tooltip(tooltipTriggerEl, {
                delay: { show: 400, hide: 100 }
            });
        });
    },

    disposeAll() {
        // Dispose all existing tooltips and hide any visible ones
        const existingTooltips = document.querySelectorAll('[data-bs-toggle="tooltip"]');
        existingTooltips.forEach(el => {
            const tooltip = bootstrap.Tooltip.getInstance(el);
            if (tooltip) {
                tooltip.dispose();
            }
        });

        // Also remove any orphaned tooltip elements that might be left in the DOM
        document.querySelectorAll('.tooltip.bs-tooltip-auto, .tooltip.bs-tooltip-top, .tooltip.bs-tooltip-bottom, .tooltip.bs-tooltip-start, .tooltip.bs-tooltip-end').forEach(el => {
            el.remove();
        });
    }
};

document.addEventListener('DOMContentLoaded', () => {
    Toast.init();
    KeyboardShortcuts.init();
    Tooltips.init();
});

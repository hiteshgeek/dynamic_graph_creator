/**
 * Common utilities shared between graph and filter modules
 */

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
        const icons = { success: 'fa-check-circle', error: 'fa-exclamation-circle', warning: 'fa-exclamation-triangle' };
        toast.innerHTML = `<i class="fas ${icons[type] || icons.success}"></i><span class="dgc-toast-message">${message}</span><button class="dgc-toast-close"><i class="fas fa-times"></i></button>`;
        toast.querySelector('.dgc-toast-close').addEventListener('click', () => toast.remove());
        this.container.appendChild(toast);
        if (duration > 0) setTimeout(() => toast.remove(), duration);
    },
    success(message) { this.show(message, 'success'); },
    error(message) { this.show(message, 'error', 5000); },
    warning(message) { this.show(message, 'warning', 4000); }
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
        // Include query string to preserve routing (e.g., ?urlq=filters)
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
                            <button type="button" class="btn btn-secondary cancel-delete" data-bs-dismiss="modal">Cancel</button>
                            <button type="button" class="btn btn-danger confirm-delete">Confirm</button>
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
        this.modalElement.querySelector('.confirm-message').textContent = message;
        this.modalElement.querySelector('.confirm-delete').textContent = confirmText;
        this.modalElement.querySelector('.cancel-delete').textContent = cancelText;

        // Update button class
        const confirmBtn = this.modalElement.querySelector('.confirm-delete');
        confirmBtn.className = `btn ${confirmClass} confirm-delete`;

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

document.addEventListener('DOMContentLoaded', () => Toast.init());

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

document.addEventListener('DOMContentLoaded', () => Toast.init());

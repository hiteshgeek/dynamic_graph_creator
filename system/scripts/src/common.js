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

document.addEventListener('DOMContentLoaded', () => Toast.init());

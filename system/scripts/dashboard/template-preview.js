/**
 * Template Preview Page - Delete/Duplicate functionality and widget loading
 * Uses shared WidgetLoader for chart rendering
 */
(function() {
    'use strict';

    console.log('[template-preview.js] Script loaded');

    // Wait for dependencies to be available
    function waitForDependencies(callback, maxAttempts) {
        maxAttempts = maxAttempts || 100;
        var attempts = 0;

        function check() {
            attempts++;
            var ajaxReady = typeof window.Ajax !== 'undefined';
            var echartsReady = typeof window.echarts !== 'undefined';
            var graphPreviewReady = typeof window.GraphPreview !== 'undefined';
            var widgetLoaderReady = typeof window.WidgetLoader !== 'undefined';

            console.log('[template-preview.js] Check #' + attempts + ' - Ajax:', ajaxReady, 'ECharts:', echartsReady, 'GraphPreview:', graphPreviewReady, 'WidgetLoader:', widgetLoaderReady);

            if (ajaxReady && echartsReady && graphPreviewReady && widgetLoaderReady) {
                console.log('[template-preview.js] Dependencies ready');
                callback();
            } else if (attempts < maxAttempts) {
                setTimeout(check, 100);
            } else {
                console.error('[template-preview.js] Dependencies not available after ' + maxAttempts + ' attempts');
            }
        }

        check();
    }

    // Initialize when DOM is ready
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }

    function init() {
        console.log('[template-preview.js] DOM ready, initializing...');

        // Add page-specific body class for CSS targeting
        document.body.classList.add('template-preview-page');

        // Wait for dependencies then initialize widgets
        waitForDependencies(function() {
            console.log('[template-preview.js] Starting widget loading...');

            // Use shared WidgetLoader
            var previewContainer = document.getElementById('template-preview');
            if (previewContainer) {
                var loader = new window.WidgetLoader({
                    logPrefix: '[template-preview.js]'
                });
                loader.loadAll(previewContainer, {});
            }

            initDeleteButton();
            initDuplicateButton();
        });
    }

    /**
     * Initialize delete button handler
     */
    function initDeleteButton() {
        var deleteBtn = document.querySelector('.delete-template-btn');
        if (!deleteBtn) {
            console.log('[template-preview.js] No delete button found');
            return;
        }

        deleteBtn.addEventListener('click', function() {
            var btn = this;
            var templateId = btn.dataset.templateId;

            window.ConfirmDialog.delete('Are you sure you want to delete this template?', 'Confirm Delete')
                .then(function(confirmed) {
                    if (!confirmed) return;

                    btn.disabled = true;
                    btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';

                    return window.Ajax.post('delete_template', { id: templateId });
                })
                .then(function(result) {
                    if (!result) return;

                    if (result.success) {
                        window.Toast.success('Template deleted successfully');
                        setTimeout(function() {
                            window.location.href = '?urlq=dashboard/templates';
                        }, 500);
                    } else {
                        window.Toast.error(result.message || 'Failed to delete template');
                        btn.disabled = false;
                        btn.innerHTML = '<i class="fas fa-trash"></i>';
                    }
                })
                .catch(function(error) {
                    console.error('[template-preview.js] Delete error:', error);
                    window.Toast.error('Failed to delete template');
                    btn.disabled = false;
                    btn.innerHTML = '<i class="fas fa-trash"></i>';
                });
        });
    }

    /**
     * Initialize duplicate button handler
     */
    function initDuplicateButton() {
        var duplicateBtn = document.querySelector('.duplicate-template-btn');
        if (!duplicateBtn) {
            console.log('[template-preview.js] No duplicate button found');
            return;
        }

        duplicateBtn.addEventListener('click', function() {
            var btn = this;
            var templateId = btn.dataset.templateId;

            btn.disabled = true;
            var originalHtml = btn.innerHTML;
            btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';

            window.Ajax.post('duplicate_template', { id: templateId })
                .then(function(result) {
                    if (result.success && result.data && result.data.id) {
                        window.Toast.success('Template duplicated successfully');
                        setTimeout(function() {
                            window.location.href = '?urlq=dashboard/template/builder/' + result.data.id;
                        }, 500);
                    } else {
                        window.Toast.error(result.message || 'Failed to duplicate template');
                        btn.disabled = false;
                        btn.innerHTML = originalHtml;
                    }
                })
                .catch(function(error) {
                    console.error('[template-preview.js] Duplicate error:', error);
                    window.Toast.error('Failed to duplicate template');
                    btn.disabled = false;
                    btn.innerHTML = originalHtml;
                });
        });
    }

})();

/**
 * Table Creator Page
 * Handles initialization of table config when editing an existing table
 */
document.addEventListener('DOMContentLoaded', function() {
    const container = document.getElementById('table-creator');
    if (!container) return;

    const tableConfig = container.dataset.tableConfig;

    if (tableConfig) {
        // Set existing config if editing
        setTimeout(function() {
            if (window.tableCreator) {
                try {
                    const config = JSON.parse(tableConfig);
                    window.tableCreator.applyConfigToUI(config);
                } catch (e) {
                    console.error('Failed to parse table config:', e);
                }
            }
        }, 100);
    }
});

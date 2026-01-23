/**
 * Counter Creator Page
 * Handles initialization of counter config when editing an existing counter
 */
document.addEventListener('DOMContentLoaded', function() {
    const container = document.getElementById('counter-creator');
    if (!container) return;

    const counterConfig = container.dataset.counterConfig;

    if (counterConfig) {
        // Set existing config if editing
        setTimeout(function() {
            if (window.counterCreator) {
                try {
                    const config = JSON.parse(counterConfig);
                    window.counterCreator.applyConfigToUI(config);
                } catch (e) {
                    console.error('Failed to parse counter config:', e);
                }
            }
        }, 100);
    }
});

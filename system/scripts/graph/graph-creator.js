/**
 * Graph Creator Page
 * Handles initialization of graph config when editing an existing graph
 */
document.addEventListener('DOMContentLoaded', function() {
    const container = document.getElementById('graph-creator');
    if (!container) return;

    const graphConfig = container.dataset.graphConfig;

    if (graphConfig) {
        // Set existing config if editing
        setTimeout(function() {
            if (window.graphCreator && window.graphCreator.configPanel) {
                try {
                    const config = JSON.parse(graphConfig);
                    window.graphCreator.configPanel.setConfig(config);
                } catch (e) {
                    console.error('Failed to parse graph config:', e);
                }
            }
        }, 100);
    }
});

/**
 * Template Preview Page
 * Initializes TemplateManager for delete and duplicate buttons
 */
document.addEventListener('DOMContentLoaded', function() {
    if (window.TemplateManager) {
        TemplateManager.initTemplateList();
    }
});

/**
 * Template Preview Page
 * Initializes TemplateManager for delete and duplicate buttons
 */
document.addEventListener('DOMContentLoaded', function() {
    // Add page-specific body class for CSS targeting
    document.body.classList.add('template-preview-page');

    if (window.TemplateManager) {
        TemplateManager.initTemplateList();
    }
});

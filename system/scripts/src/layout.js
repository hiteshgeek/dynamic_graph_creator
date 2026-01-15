/**
 * Layout Module Entry Point
 * Simplified version for initial implementation
 */

import Sortable from 'sortablejs';

// Use globals from common.js (Toast, Loading, Ajax, ConfirmDialog)
const Toast = window.Toast;
const Loading = window.Loading;
const Ajax = window.Ajax;
const ConfirmDialog = window.ConfirmDialog;

/**
 * Layout Builder - Main orchestrator
 */
class LayoutBuilder {
    constructor(container, options = {}) {
        this.container = container;
        this.layoutId = options.layoutId || null;
        this.mode = options.mode || 'edit';
        this.currentLayout = null;
        this.isDirty = false;
        this.isSaving = false;
        this.sortableInstance = null;
        this.eventHandlersInitialized = false;
        this.autoSaveTimeout = null;
        this.templateSelectorMode = 'create-layout'; // or 'add-section'
    }

    init() {
        if (this.layoutId) {
            this.loadLayout();
        } else {
            this.showTemplateSelector();
        }
        this.initEventHandlers();
    }

    async loadLayout() {
        if (!this.layoutId) {
            console.error('No layout ID specified');
            return;
        }

        Loading.show('Loading layout...');

        try {
            const result = await Ajax.post('get_layout', { id: this.layoutId });

            if (result.success) {
                this.currentLayout = result.data;
                this.renderLayout();
            } else {
                Toast.error(result.message);
            }
        } catch (error) {
            Toast.error('Failed to load layout');
        } finally {
            Loading.hide();
        }
    }

    async showTemplateSelector() {
        const modal = document.getElementById('template-modal');
        if (!modal) return;

        modal.style.display = 'flex';

        try {
            const result = await Ajax.post('get_templates', {});

            if (result.success) {
                this.renderTemplates(result.data);
            } else {
                Toast.error('Failed to load templates');
            }
        } catch (error) {
            Toast.error('Failed to load templates');
        }
    }

    renderTemplatePreview(template) {
        try {
            const structure = JSON.parse(template.structure);
            let previewHtml = '<div class="template-preview-grid">';

            if (structure.sections && structure.sections.length > 0) {
                structure.sections.forEach(section => {
                    previewHtml += `<div class="preview-section" style="display: grid; grid-template-columns: ${section.gridTemplate}; gap: 2px;">`;

                    if (section.areas) {
                        section.areas.forEach(area => {
                            // Check if area has sub-rows
                            if (area.hasSubRows && area.subRows && area.subRows.length > 0) {
                                const rowHeights = area.subRows.map(row => row.height || '1fr').join(' ');
                                previewHtml += `<div class="preview-area-nested" style="display: grid; grid-template-rows: ${rowHeights}; gap: 2px;">`;

                                area.subRows.forEach(() => {
                                    previewHtml += '<div class="preview-sub-row"></div>';
                                });

                                previewHtml += '</div>';
                            } else {
                                // Regular area
                                previewHtml += '<div class="preview-area"></div>';
                            }
                        });
                    }

                    previewHtml += '</div>';
                });
            }

            previewHtml += '</div>';
            return previewHtml;
        } catch (e) {
            return '<div class="template-preview-fallback"><i class="fas fa-th-large"></i></div>';
        }
    }

    renderTemplates(templates) {
        const modal = document.getElementById('template-modal');
        const body = modal.querySelector('.modal-body');

        const categories = {
            'columns': 'Column Layouts',
            'rows': 'Row Layouts',
            'mixed': 'Mixed Layouts',
            'advanced': 'Advanced Layouts'
        };

        let html = '';

        for (const [category, label] of Object.entries(categories)) {
            if (templates[category] && templates[category].length > 0) {
                html += `<div class="template-category">
                    <h3>${label}</h3>
                    <div class="template-grid">`;

                templates[category].forEach(template => {
                    html += `<div class="template-card" data-template-id="${template.ltid}">
                        <div class="template-preview">
                            ${this.renderTemplatePreview(template)}
                        </div>
                        <div class="template-info">
                            <h4>${template.name}</h4>
                            <p>${template.description || ''}</p>
                        </div>
                    </div>`;
                });

                html += `</div></div>`;
            }
        }

        body.innerHTML = html;

        // Add click handlers
        body.querySelectorAll('.template-card').forEach(card => {
            card.addEventListener('click', () => {
                const templateId = parseInt(card.dataset.templateId);
                if (this.templateSelectorMode === 'add-section') {
                    this.addSectionFromTemplate(templateId);
                } else {
                    this.createFromTemplate(templateId);
                }
            });
        });
    }

    async createFromTemplate(templateId) {
        Loading.show('Creating layout...');

        try {
            const result = await Ajax.post('create_from_template', {
                template_id: templateId,
                name: 'New Dashboard Layout'
            });

            if (result.success) {
                this.layoutId = result.data.id;

                // Update URL to include layout ID (for refresh persistence)
                const newUrl = `?urlq=layout/builder/${this.layoutId}`;
                window.history.pushState({ layoutId: this.layoutId }, '', newUrl);

                // Update container data attribute
                this.container.dataset.layoutId = this.layoutId;

                // Update page title
                document.title = 'Edit Layout - Dynamic Graph Creator';

                // Update header title
                const headerTitle = document.querySelector('.page-header-left h1');
                if (headerTitle) {
                    headerTitle.textContent = 'Edit Layout';
                }

                // Show save indicator and View Layout button
                this.updateHeaderAfterCreation();

                // Update sidebar to show "Add Section" button instead of "Choose Template"
                this.updateSidebarAfterCreation();

                // Load the layout
                await this.loadLayout();

                // Close modal
                document.getElementById('template-modal').style.display = 'none';

                Toast.success('Layout created successfully');
            } else {
                Toast.error(result.message);
            }
        } catch (error) {
            Toast.error('Failed to create layout');
        } finally {
            Loading.hide();
        }
    }

    async addSectionFromTemplate(templateId) {
        Loading.show('Adding section...');

        try {
            const result = await Ajax.post('add_section_from_template', {
                layout_id: this.layoutId,
                template_id: templateId,
                position: this.pendingAddPosition
            });

            if (result.success) {
                await this.loadLayout();

                // Close modal
                document.getElementById('template-modal').style.display = 'none';

                // Reset mode
                this.templateSelectorMode = 'create-layout';

                Toast.success('Section added successfully');
            } else {
                Toast.error(result.message);
            }
        } catch (error) {
            Toast.error('Failed to add section');
        } finally {
            Loading.hide();
        }
    }

    updateHeaderAfterCreation() {
        // Show save indicator with proper styling
        const saveIndicator = document.querySelector('.save-indicator');
        if (saveIndicator) {
            saveIndicator.style.display = 'flex';
            saveIndicator.className = 'save-indicator saved';
            const icon = saveIndicator.querySelector('i');
            const text = saveIndicator.querySelector('span');
            if (icon) icon.className = 'fas fa-check-circle';
            if (text) text.textContent = 'Saved';
        }

        // Add View Layout button if it doesn't exist
        const headerRight = document.querySelector('.page-header-right');
        if (headerRight && this.layoutId) {
            const existingViewBtn = headerRight.querySelector('a[href*="layout/preview"]');
            if (!existingViewBtn) {
                const viewBtn = document.createElement('a');
                viewBtn.href = `?urlq=layout/preview/${this.layoutId}`;
                viewBtn.className = 'btn btn-primary';
                viewBtn.title = 'View Layout';
                viewBtn.innerHTML = '<i class="fas fa-eye"></i> View Layout';
                headerRight.appendChild(viewBtn);
            }
        }
    }

    updateSidebarAfterCreation() {
        // Replace the choose-template-card with the layout sections container
        const gridEditor = document.querySelector('.grid-editor');
        if (!gridEditor) return;

        gridEditor.innerHTML = `
            <div class="layout-sections">
                <div class="loading-message">
                    <i class="fas fa-spinner fa-spin"></i>
                    <p>Loading layout...</p>
                </div>
            </div>
        `;
    }

    renderLayout() {
        const sectionsContainer = this.container.querySelector('.layout-sections');
        if (!sectionsContainer) return;

        const structure = JSON.parse(this.currentLayout.structure);

        let html = '';

        if (structure.sections) {
            structure.sections.forEach((section, index) => {
                // Render the section with add button on top border
                html += this.renderSection(section, structure.sections.length, index);
            });
        }

        sectionsContainer.innerHTML = html;

        // Enable drag-drop
        this.initDragDrop();

        // Attach event listeners to border buttons
        this.initAddSectionBorderButtons();
    }

    initAddSectionBorderButtons() {
        const borderBtns = document.querySelectorAll('.add-section-border-btn');
        if (borderBtns.length > 0) {
            borderBtns.forEach(btn => {
                btn.addEventListener('click', async () => {
                    const position = parseInt(btn.dataset.position);
                    // Store the position for the add section handler
                    this.pendingAddPosition = position;
                    // Set mode to 'add-section' so template selection adds a section instead of creating layout
                    this.templateSelectorMode = 'add-section';
                    // Show template selector to choose section template
                    await this.showTemplateSelector();
                });
            });
        }
    }

    renderSection(section, totalSections = 1, index = 0) {
        let areasHtml = '';

        section.areas.forEach(area => {
            // Check if this area has sub-rows (nested layout)
            if (area.hasSubRows && area.subRows && area.subRows.length > 0) {
                areasHtml += this.renderAreaWithSubRows(area);
            } else {
                // Regular single area
                areasHtml += `<div class="layout-area" data-area-id="${area.aid}">
                    ${area.content && area.content.type === 'empty' ? this.renderEmptyState(area.emptyState) : this.renderContent(area.content)}
                </div>`;
            }
        });

        // Section control buttons on top border
        const dragHandleHtml = totalSections > 1 ? `
            <button class="section-control-btn drag-handle" title="Drag to reorder">
                <i class="fas fa-grip-vertical"></i>
            </button>
        ` : '';

        const topBorderControls = `
            <div class="section-top-border-controls">
                ${dragHandleHtml}
                <button class="section-control-btn remove-btn" data-section-id="${section.sid}" title="Remove section">
                    <i class="fas fa-trash"></i>
                </button>
            </div>
        `;

        // Add section button on top border
        const topBorderButton = `
            <button class="add-section-border-btn add-section-top-btn" data-position="${index}" title="Add section above">
                <i class="fas fa-plus"></i>
                <span>Add Section</span>
            </button>
        `;

        // Add section button on bottom border
        const bottomBorderButton = `
            <button class="add-section-border-btn add-section-bottom-btn" data-position="${index + 1}" title="Add section below">
                <i class="fas fa-plus"></i>
                <span>Add Section</span>
            </button>
        `;

        return `<div class="layout-section-wrapper">
            ${topBorderButton}
            ${topBorderControls}
            <div class="layout-section" data-section-id="${section.sid}" style="grid-template-columns: ${section.gridTemplate};">
                ${areasHtml}
            </div>
            ${bottomBorderButton}
        </div>`;
    }

    renderAreaWithSubRows(area) {
        // Build grid-template-rows from sub-row heights
        const rowHeights = area.subRows.map(row => row.height || '1fr').join(' ');

        let subRowsHtml = '';
        area.subRows.forEach(subRow => {
            subRowsHtml += `<div class="layout-sub-row" data-row-id="${subRow.rowId}">
                ${subRow.content && subRow.content.type === 'empty' ? this.renderEmptyState(subRow.emptyState) : this.renderContent(subRow.content)}
            </div>`;
        });

        return `<div class="layout-area layout-area-nested" data-area-id="${area.aid}" style="grid-template-rows: ${rowHeights};">
            ${subRowsHtml}
        </div>`;
    }

    renderEmptyState(emptyState) {
        return `<div class="empty-state">
            <div class="empty-state-icon">
                <i class="fas ${emptyState?.icon || 'fa-plus-circle'}"></i>
            </div>
            <div class="empty-state-message">
                ${emptyState?.message || 'Add content here'}
            </div>
        </div>`;
    }

    renderContent(content) {
        return `<div class="area-content">
            <p>Widget: ${content?.widgetType || 'Unknown'}</p>
        </div>`;
    }

    initDragDrop() {
        const sectionsContainer = this.container.querySelector('.layout-sections');

        if (this.sortableInstance) {
            this.sortableInstance.destroy();
        }

        this.sortableInstance = Sortable.create(sectionsContainer, {
            animation: 150,
            handle: '.drag-handle',
            draggable: '.layout-section-wrapper',
            ghostClass: 'section-ghost',
            onEnd: () => this.onSectionsReorder()
        });
    }

    async onSectionsReorder() {
        const sections = this.container.querySelectorAll('.layout-section');
        const order = Array.from(sections).map(section => section.dataset.sectionId);

        Loading.show('Reordering...');

        try {
            const result = await Ajax.post('reorder_sections', {
                layout_id: this.layoutId,
                order: order
            });

            if (result.success) {
                Toast.success('Sections reordered');
                this.markDirty();
            } else {
                Toast.error(result.message);
            }
        } catch (error) {
            Toast.error('Failed to reorder sections');
        } finally {
            Loading.hide();
        }
    }

    async saveLayout(showToast = false) {
        if (!this.currentLayout || this.isSaving) return;

        this.isSaving = true;
        this.updateSaveIndicator('saving');

        try {
            const result = await Ajax.post('save_layout', {
                layout_id: this.layoutId,
                name: this.currentLayout.name,
                structure: this.currentLayout.structure,
                config: this.currentLayout.config || '{}'
            });

            if (result.success) {
                this.isDirty = false;
                this.updateSaveIndicator('saved');
                if (showToast) {
                    Toast.success('Layout saved successfully');
                }
            } else {
                this.updateSaveIndicator('error');
                Toast.error(result.message);
            }
        } catch (error) {
            this.updateSaveIndicator('error');
            Toast.error('Failed to save layout');
        } finally {
            this.isSaving = false;
        }
    }

    markDirty() {
        this.isDirty = true;
        this.updateSaveIndicator('unsaved');

        // Auto-save after 2 seconds of inactivity
        if (this.autoSaveTimeout) {
            clearTimeout(this.autoSaveTimeout);
        }
        this.autoSaveTimeout = setTimeout(() => {
            this.saveLayout(false);
        }, 2000);
    }

    updateSaveIndicator(state) {
        const indicator = document.querySelector('.save-indicator');
        if (!indicator) return;

        const icon = indicator.querySelector('i');
        const text = indicator.querySelector('span');

        switch (state) {
            case 'saving':
                indicator.className = 'save-indicator saving';
                icon.className = 'fas fa-spinner fa-spin';
                text.textContent = 'Saving...';
                break;
            case 'saved':
                indicator.className = 'save-indicator saved';
                icon.className = 'fas fa-check-circle';
                text.textContent = 'Saved';
                break;
            case 'unsaved':
                indicator.className = 'save-indicator unsaved';
                icon.className = 'fas fa-circle';
                text.textContent = 'Unsaved changes';
                break;
            case 'error':
                indicator.className = 'save-indicator error';
                icon.className = 'fas fa-exclamation-circle';
                text.textContent = 'Save failed';
                break;
        }
    }

    initEventHandlers() {
        // Prevent duplicate event handler initialization
        if (this.eventHandlersInitialized) {
            return;
        }
        this.eventHandlersInitialized = true;

        // Warn before leaving page with unsaved changes
        window.addEventListener('beforeunload', (e) => {
            if (this.isDirty) {
                e.preventDefault();
                e.returnValue = ''; // Required for Chrome
                return '';
            }
        });

        // Choose template button (when no layout exists)
        const chooseTemplateBtn = document.querySelector('.choose-template-btn');
        if (chooseTemplateBtn) {
            chooseTemplateBtn.addEventListener('click', () => {
                this.showTemplateSelector();
            });
        }

        // Confirm add section
        const confirmBtn = document.getElementById('confirm-add-section');
        if (confirmBtn) {
            confirmBtn.addEventListener('click', () => this.handleAddSection());
        }

        // Template modal close
        const modalClose = document.querySelector('.layout-template-modal .modal-close');
        if (modalClose) {
            modalClose.addEventListener('click', () => {
                document.getElementById('template-modal').style.display = 'none';
            });
        }

        // Remove section handlers - use event delegation on container instead of document
        this.container.addEventListener('click', (e) => {
            if (e.target.closest('.remove-btn')) {
                const btn = e.target.closest('.remove-btn');
                const sectionId = btn.dataset.sectionId;
                this.removeSection(sectionId);
            }
        });
    }

    async handleAddSection() {
        const columns = document.getElementById('section-columns').value;
        // Use the stored position from the button click
        const position = this.pendingAddPosition !== undefined ? this.pendingAddPosition : 0;
        const modalElement = document.getElementById('add-section-modal');
        const modalInstance = bootstrap.Modal.getInstance(modalElement);

        // Close modal first
        if (modalInstance) {
            modalInstance.hide();

            // Wait for modal to fully close
            await new Promise(resolve => {
                modalElement.addEventListener('hidden.bs.modal', resolve, { once: true });
            });

            // Force cleanup of backdrop if it still exists
            const backdrop = document.querySelector('.modal-backdrop');
            if (backdrop) {
                backdrop.remove();
            }

            // Remove modal-open class from body
            document.body.classList.remove('modal-open');
            document.body.style.overflow = '';
            document.body.style.paddingRight = '';
        }

        Loading.show('Adding section...');

        try {
            const result = await Ajax.post('add_section', {
                layout_id: this.layoutId,
                columns: columns,
                position: position
            });

            if (result.success) {
                await this.loadLayout();
                Toast.success('Section added');
            } else {
                Toast.error(result.message);
            }
        } catch (error) {
            Toast.error('Failed to add section');
        } finally {
            Loading.hide();
        }
    }

    async removeSection(sectionId) {
        const confirmed = await ConfirmDialog.delete('Remove this section?', 'Confirm Delete');
        if (!confirmed) return;

        Loading.show('Removing section...');

        try {
            const result = await Ajax.post('remove_section', {
                layout_id: this.layoutId,
                section_id: sectionId
            });

            if (result.success) {
                await this.loadLayout();
                Toast.success('Section removed');
            } else {
                Toast.error(result.message);
            }
        } catch (error) {
            Toast.error('Failed to remove section');
        } finally {
            Loading.hide();
        }
    }
}

// Expose to window
window.LayoutBuilder = LayoutBuilder;

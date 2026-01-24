/**
 * Table module (use with common.js)
 */

import TableCreator from './TableCreator.js';
import TablePreview from './TablePreview.js';
import TableView from './TableView.js';
import QueryBuilder from './QueryBuilder.js';
import DataFilterManager from './DataFilterManager.js';
import DatePickerInit from './DatePickerInit.js';
import FilterRenderer from './FilterRenderer.js';

window.TableCreator = TableCreator;
window.TablePreview = TablePreview;
window.TableView = TableView;
window.QueryBuilder = QueryBuilder;
window.DataFilterManager = DataFilterManager;
window.DatePickerInit = DatePickerInit;
window.FilterRenderer = FilterRenderer;

document.addEventListener('DOMContentLoaded', () => {
    const creatorContainer = document.getElementById('table-creator');
    if (creatorContainer) {
        window.tableCreator = new TableCreator(creatorContainer, {
            tableId: creatorContainer.dataset.tableId || null
        });
        window.tableCreator.init();
    }

    const viewContainer = document.getElementById('table-view');
    if (viewContainer) {
        const config = viewContainer.dataset.config ? JSON.parse(viewContainer.dataset.config) : {};
        const hasFilters = viewContainer.dataset.hasFilters === '1';
        window.tableView = new TableView(viewContainer, {
            tableId: viewContainer.dataset.tableId,
            tableName: viewContainer.dataset.tableName || 'Table',
            config,
            hasFilters
        });
        window.tableView.init();
    }
});

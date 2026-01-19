/**
 * Data Filter module (use with common.js)
 */

import DataFilterManagerPage from './DataFilterManagerPage.js';
import DataFilterFormPage from './DataFilterFormPage.js';
import DataFilterListPage from './DataFilterListPage.js';
import DatePickerInit from './DatePickerInit.js';

window.DataFilterManagerPage = DataFilterManagerPage;
window.DataFilterFormPage = DataFilterFormPage;
window.DataFilterListPage = DataFilterListPage;
window.DatePickerInit = DatePickerInit;

document.addEventListener('DOMContentLoaded', () => {
    // Data filter manager page (embedded in graphs)
    const filterManagerContainer = document.querySelector('.data-filter-manager-page');
    if (filterManagerContainer) {
        window.dataFilterManagerPage = new DataFilterManagerPage(filterManagerContainer, { graphId: filterManagerContainer.dataset.graphId });
    }

    // Data filter list page
    const filterListContainer = document.querySelector('.data-filter-list-page');
    if (filterListContainer) {
        window.dataFilterListPage = new DataFilterListPage(filterListContainer);
    }

    // Data filter form page (add/edit)
    const filterFormContainer = document.querySelector('.data-filter-form-page');
    if (filterFormContainer) {
        window.dataFilterFormPage = new DataFilterFormPage(filterFormContainer);
    }
});

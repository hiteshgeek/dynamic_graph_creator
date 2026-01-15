/**
 * Filter module (use with common.js)
 */

import FilterManagerPage from './FilterManagerPage.js';
import FilterFormPage from './FilterFormPage.js';
import FilterListPage from './FilterListPage.js';

window.FilterManagerPage = FilterManagerPage;
window.FilterFormPage = FilterFormPage;
window.FilterListPage = FilterListPage;

document.addEventListener('DOMContentLoaded', () => {
    // Filter manager page (embedded in graphs)
    const filterManagerContainer = document.querySelector('.filter-manager-page');
    if (filterManagerContainer) {
        window.filterManagerPage = new FilterManagerPage(filterManagerContainer, { graphId: filterManagerContainer.dataset.graphId });
    }

    // Filter list page
    const filterListContainer = document.querySelector('.filter-list-page');
    if (filterListContainer) {
        window.filterListPage = new FilterListPage(filterListContainer);
    }

    // Filter form page (add/edit)
    const filterFormContainer = document.querySelector('.filter-form-page');
    if (filterFormContainer) {
        window.filterFormPage = new FilterFormPage(filterFormContainer);
    }
});

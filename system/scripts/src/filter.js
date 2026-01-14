/**
 * Filter module (use with common.js)
 */

import FilterManagerPage from './FilterManagerPage.js';

window.FilterManagerPage = FilterManagerPage;

document.addEventListener('DOMContentLoaded', () => {
    const filterManagerContainer = document.querySelector('.filter-manager-page');
    if (filterManagerContainer) {
        window.filterManagerPage = new FilterManagerPage(filterManagerContainer, { graphId: filterManagerContainer.dataset.graphId });
    }
});

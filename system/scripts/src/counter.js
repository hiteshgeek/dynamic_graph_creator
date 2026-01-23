/**
 * Counter module (use with common.js)
 */

import CounterCreator from './CounterCreator.js';
import CounterPreview from './CounterPreview.js';
import CounterView from './CounterView.js';
import QueryBuilder from './QueryBuilder.js';
import DataFilterManager from './DataFilterManager.js';
import DatePickerInit from './DatePickerInit.js';
import FilterRenderer from './FilterRenderer.js';

window.CounterCreator = CounterCreator;
window.CounterPreview = CounterPreview;
window.CounterView = CounterView;
window.QueryBuilder = QueryBuilder;
window.DataFilterManager = DataFilterManager;
window.DatePickerInit = DatePickerInit;
window.FilterRenderer = FilterRenderer;

document.addEventListener('DOMContentLoaded', () => {
    const creatorContainer = document.getElementById('counter-creator');
    if (creatorContainer) {
        window.counterCreator = new CounterCreator(creatorContainer, {
            counterId: creatorContainer.dataset.counterId || null
        });
        window.counterCreator.init();
    }

    const viewContainer = document.getElementById('counter-view');
    if (viewContainer) {
        const config = viewContainer.dataset.config ? JSON.parse(viewContainer.dataset.config) : {};
        const hasFilters = viewContainer.dataset.hasFilters === '1';
        window.counterView = new CounterView(viewContainer, {
            counterId: viewContainer.dataset.counterId,
            counterName: viewContainer.dataset.counterName || 'Counter',
            config,
            hasFilters
        });
        window.counterView.init();
    }
});

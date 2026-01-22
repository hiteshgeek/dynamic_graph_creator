/**
 * Graph module (use with common.js)
 */

import GraphCreator from './GraphCreator.js';
import GraphPreview from './GraphPreview.js';
import GraphExporter from './GraphExporter.js';
import QueryBuilder from './QueryBuilder.js';
import DataMapper from './DataMapper.js';
import DataFilterManager from './DataFilterManager.js';
import ConfigPanel from './ConfigPanel.js';
import GraphView from './GraphView.js';
import DatePickerInit from './DatePickerInit.js';
import FilterRenderer from './FilterRenderer.js';
import ChartSkeleton from './ChartSkeleton.js';

window.GraphCreator = GraphCreator;
window.GraphPreview = GraphPreview;
window.GraphExporter = GraphExporter;
window.QueryBuilder = QueryBuilder;
window.DataMapper = DataMapper;
window.DataFilterManager = DataFilterManager;
window.ConfigPanel = ConfigPanel;
window.GraphView = GraphView;
window.DatePickerInit = DatePickerInit;
window.FilterRenderer = FilterRenderer;
window.ChartSkeleton = ChartSkeleton;

document.addEventListener('DOMContentLoaded', () => {
    const creatorContainer = document.getElementById('graph-creator');
    if (creatorContainer) {
        window.graphCreator = new GraphCreator(creatorContainer, { graphId: creatorContainer.dataset.graphId || null });
        window.graphCreator.init();
    }

    const viewContainer = document.getElementById('graph-view');
    if (viewContainer) {
        const config = viewContainer.dataset.config ? JSON.parse(viewContainer.dataset.config) : {};
        const hasFilters = viewContainer.dataset.hasFilters === '1';
        window.graphView = new GraphView(viewContainer, {
            graphId: viewContainer.dataset.graphId,
            graphType: viewContainer.dataset.graphType || 'bar',
            graphName: viewContainer.dataset.graphName || 'Chart',
            config,
            hasFilters
        });
    }
});

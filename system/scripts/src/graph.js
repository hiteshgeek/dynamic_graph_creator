/**
 * Graph module (use with common.js)
 */

import GraphCreator from './GraphCreator.js';
import GraphPreview from './GraphPreview.js';
import GraphExporter from './GraphExporter.js';
import QueryBuilder from './QueryBuilder.js';
import DataMapper from './DataMapper.js';
import FilterManager from './FilterManager.js';
import ConfigPanel from './ConfigPanel.js';
import GraphView from './GraphView.js';

window.GraphCreator = GraphCreator;
window.GraphPreview = GraphPreview;
window.GraphExporter = GraphExporter;
window.QueryBuilder = QueryBuilder;
window.DataMapper = DataMapper;
window.FilterManager = FilterManager;
window.ConfigPanel = ConfigPanel;
window.GraphView = GraphView;

document.addEventListener('DOMContentLoaded', () => {
    const creatorContainer = document.getElementById('graph-creator');
    if (creatorContainer) {
        window.graphCreator = new GraphCreator(creatorContainer, { graphId: creatorContainer.dataset.graphId || null });
        window.graphCreator.init();
    }

    const viewContainer = document.getElementById('graph-view');
    if (viewContainer) {
        const config = viewContainer.dataset.config ? JSON.parse(viewContainer.dataset.config) : {};
        window.graphView = new GraphView(viewContainer, {
            graphId: viewContainer.dataset.graphId,
            graphType: viewContainer.dataset.graphType || 'bar',
            graphName: viewContainer.dataset.graphName || 'Chart',
            config
        });
    }
});

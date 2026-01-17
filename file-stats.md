# Project File Statistics

## Command
```bash
find /var/www/html/dynamic_graph_creator -type f \( -name "*.php" -o -name "*.js" -o -name "*.scss" -o -name "*.css" \) ! -path "*/node_modules/*" ! -path "*/vendor/*" ! -path "*/framework_sample_files/*" ! -path "*/dist/*" ! -name "*.min.*" -exec wc -l {} \; 2>/dev/null | sort -rn
```

## Results (73 files, ~22,600 lines)

| Lines | File |
|------:|------|
| 2,273 | system/scripts/src/dashboard.js |
| 1,683 | system/styles/src/_graph-creator.scss |
| 1,352 | system/styles/src/_theme.scss |
| 1,275 | system/scripts/src/GraphCreator.js |
| 1,197 | system/includes/dashboard/dashboard.inc.php |
| 964 | system/styles/src/_filters.scss |
| 964 | system/scripts/src/common.js |
| 834 | system/styles/src/dashboard/_dashboard-builder.scss |
| 784 | system/styles/src/_common.scss |
| 783 | system/styles/src/dashboard/_template-list.scss |
| 716 | system/scripts/src/dashboard/TemplateManager.js |
| 703 | system/scripts/src/FilterFormPage.js |
| 562 | system/includes/graph/graph.inc.php |
| 495 | system/scripts/src/FilterManager.js |
| 490 | system/classes/Filter.php |
| 465 | system/scripts/src/GraphPreview.js |
| 444 | system/scripts/src/CodeMirrorEditor.js |
| 430 | system/templates/dashboard/template-builder.php |
| 398 | system/templates/graph/graph-creator.php |
| 398 | system/classes/DashboardTemplate.php |
| 383 | system/classes/DashboardInstance.php |
| 379 | system/scripts/src/ConfigPanel.js |
| 378 | system/styles/src/_base.scss |
| 374 | system/scripts/src/QueryBuilder.js |
| 329 | system/classes/Utility.php |
| 312 | system/scripts/src/main.js |
| 305 | system/scripts/src/Theme.js |
| 302 | system/scripts/src/FilterManagerPage.js |
| 285 | system/styles/src/_code-editor.scss |
| 284 | system/templates/dashboard/dashboard-builder.php |
| 284 | system/classes/DashboardTemplateCategory.php |
| 277 | system/templates/dashboard/template-editor.php |
| 277 | system/styles/src/main.scss |
| 269 | system/styles/src/dashboard/_grid-editor.scss |
| 264 | system/scripts/src/KeyboardShortcuts.js |
| 262 | system/classes/DashboardBuilder.php |
| 261 | system/scripts/src/GraphView.js |
| 254 | system/classes/Graph.php |
| 253 | system/scripts/src/DataMapper.js |
| 235 | system/styles/src/_graph-list.scss |
| 234 | system/includes/filter/filter.inc.php |
| 224 | system/utilities/SystemConfig.php |
| 215 | system/styles/src/_config-panel.scss |
| 213 | system/templates/filter/filter-form.php |
| 205 | system/classes/FilterManager.php |
| 198 | system/templates/dashboard/template-list.php |
| 195 | system/scripts/src/PlaceholderSettings.js |
| 195 | system/classes/FilterSet.php |
| 192 | system/templates/graph/graph-view.php |
| 185 | system/styles/src/_query-builder.scss |
| 184 | system/templates/dashboard/dashboard-preview.php |
| 170 | system/classes/SQLiDatabase.php |
| 165 | system/templates/graph/graph-list.php |
| 159 | system/scripts/src/GraphExporter.js |
| 157 | build.js |
| 145 | system/templates/filter/filter-list.php |
| 144 | system/templates/dashboard/template-preview.php |
| 143 | system/styles/src/dashboard/_dashboard-list.scss |
| 141 | system/styles/src/dashboard/_template-preview-shared.scss |
| 131 | system/styles/src/dashboard/_template-selector.scss |
| 122 | system/styles/src/_variables.scss |
| 117 | system/templates/dashboard/dashboard-list.php |
| 93 | system/scripts/src/FilterUtils.js |
| 87 | system/scripts/src/FilterListPage.js |
| 75 | system/styles/src/_loader.scss |
| 70 | system/interfaces/DatabaseObject.php |
| 52 | system/config/BaseConfig.php |
| 46 | system/styles/src/dashboard/_responsive.scss |
| 46 | system/classes/Rapidkart.php |
| 44 | index.php |
| 42 | system/scripts/src/graph.js |
| 40 | system/includes/dashboard/template-preview-component.php |
| 37 | system/styles/src/dashboard/_empty-state.scss |
| 34 | system/utilities/SystemTables.php |
| 31 | system/styles/src/graph.scss |
| 31 | system/scripts/src/filter.js |
| 17 | system/styles/src/filter.scss |
| 10 | system/styles/src/dashboard.scss |
| 8 | system/styles/src/shared.scss |

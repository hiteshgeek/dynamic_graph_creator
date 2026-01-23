
<?php
    // Operations
    $rightContent = '<button type="submit" form="template-editor-form" class="btn btn-sm btn-outline-primary" id="submit-btn" data-save-btn>';
    $rightContent .= '<i class="fas fa-save"></i> ' . ($template ? 'Save' : 'Create Template') . '</button>';
    // Navigation links
    $rightContent .= '<a href="?urlq=home" class="btn btn-icon btn-outline-secondary" data-bs-toggle="tooltip" data-bs-placement="bottom" title="Home"><i class="fas fa-home"></i></a>';
    if (DGCHelper::hasAdminAccess()) {
        $rightContent .= '<a href="?urlq=dashboard/templates" class="btn btn-icon btn-outline-secondary" data-bs-toggle="tooltip" data-bs-placement="bottom" title="Templates"><i class="fas fa-clone"></i></a>';
        $rightContent .= '<a href="?urlq=graph" class="btn btn-icon btn-outline-secondary" data-bs-toggle="tooltip" data-bs-placement="bottom" title="Graphs"><i class="fas fa-chart-line"></i></a>';
        $rightContent .= '<a href="?urlq=data-filter" class="btn btn-icon btn-outline-secondary" data-bs-toggle="tooltip" data-bs-placement="bottom" title="Filters"><i class="fas fa-filter"></i></a>';
    }
    echo DGCHelper::renderPageHeader([
        'title' => $pageTitle,
        'backUrl' => '?urlq=dashboard/templates',
        'backLabel' => 'Templates',
        'rightContent' => $rightContent
    ]);
    ?>

    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-8 col-lg-6">
                <div class="card">
                    <div class="card-body p-0">
                        <form id="template-editor-form">
                            <div class="mb-3">
                                <label for="template-name" class="form-label">Template Name *</label>
                                <input type="text"
                                    class="form-control"
                                    id="template-name"
                                    name="name"
                                    placeholder="Enter template name"
                                    value="<?php echo $template ? htmlspecialchars($template->getName()) : ''; ?>"
                                    required>
                            </div>

                            <div class="mb-3">
                                <label for="template-description" class="form-label">Description</label>
                                <textarea class="form-control"
                                    id="template-description"
                                    name="description"
                                    rows="3"
                                    placeholder="Enter template description (optional)"><?php echo $template ? htmlspecialchars($template->getDescription()) : ''; ?></textarea>
                            </div>

                            <div class="mb-3">
                                <label for="template-category" class="form-label">Category</label>
                                <select class="form-select select-with-create" id="template-category" name="dtcid">
                                    <option value="">None (Uncategorized)</option>
                                    <?php foreach ($categories as $category): ?>
                                        <option value="<?php echo $category->getId(); ?>"
                                            <?php echo ($template && $template->getDtcid() == $category->getId()) ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($category->getName()); ?>
                                        </option>
                                    <?php endforeach; ?>
                                    <option value="__new__" class="option-create-new">+ Create New Category</option>
                                </select>
                            </div>

                            <!-- New Category Fields (hidden by default) -->
                            <div id="new-category-fields" class="new-category-fields" style="display: none;">
                                <div class="mb-3">
                                    <label for="new-category-name" class="form-label">Category Name *</label>
                                    <input type="text"
                                        class="form-control"
                                        id="new-category-name"
                                        name="new_category_name"
                                        placeholder="Enter category name">
                                </div>
                                <div class="mb-3">
                                    <label for="new-category-description" class="form-label">Category Description</label>
                                    <textarea class="form-control"
                                        id="new-category-description"
                                        name="new_category_description"
                                        rows="2"
                                        placeholder="Enter category description (optional)"></textarea>
                                </div>
                            </div>

                            <?php if ($template && $template->getId()): ?>
                                <input type="hidden" name="id" value="<?php echo $template->getId(); ?>">
                            <?php endif; ?>

                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>


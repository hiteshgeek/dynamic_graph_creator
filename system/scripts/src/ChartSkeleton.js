/**
 * ChartSkeleton - Centralized skeleton loader utility for charts
 * Reusable across GraphPreview, dashboard widgets, and other chart containers
 */

export default class ChartSkeleton {
    /**
     * Create skeleton HTML for a chart type
     * @param {string} type - Chart type: 'bar', 'line', or 'pie'
     * @returns {string} HTML string for skeleton
     */
    static getHTML(type = 'bar') {
        if (type === 'pie') {
            return `
                <div class="skeleton-chart-pie">
                    <div class="skeleton-pie"></div>
                </div>
            `;
        }

        if (type === 'line') {
            return `
                <div class="skeleton-chart-line">
                    <div class="skeleton-line-wave"></div>
                </div>
            `;
        }

        // Default: bar chart skeleton
        return `
            <div class="skeleton-chart-bar">
                <div class="skeleton-bar"></div>
                <div class="skeleton-bar"></div>
                <div class="skeleton-bar"></div>
                <div class="skeleton-bar"></div>
                <div class="skeleton-bar"></div>
                <div class="skeleton-bar"></div>
            </div>
        `;
    }

    /**
     * Create and return a skeleton element
     * @param {string} type - Chart type: 'bar', 'line', or 'pie'
     * @param {string} additionalClass - Additional CSS class to add
     * @returns {HTMLElement} Skeleton DOM element
     */
    static create(type = 'bar', additionalClass = '') {
        const skeleton = document.createElement('div');
        skeleton.className = `chart-skeleton${additionalClass ? ' ' + additionalClass : ''}`;
        skeleton.innerHTML = ChartSkeleton.getHTML(type);
        return skeleton;
    }

    /**
     * Show skeleton in a container
     * If skeleton already exists (e.g., rendered by PHP), keeps it instead of recreating
     * @param {HTMLElement} container - Container element
     * @param {string} type - Chart type: 'bar', 'line', or 'pie'
     * @param {string} additionalClass - Additional CSS class
     * @returns {HTMLElement} The skeleton element (existing or newly created)
     */
    static show(container, type = 'bar', additionalClass = '') {
        // If skeleton already exists (e.g., from PHP), keep it - it has the correct type
        const existing = container.querySelector('.chart-skeleton');
        if (existing) {
            return existing;
        }

        const skeleton = ChartSkeleton.create(type, additionalClass);
        container.appendChild(skeleton);
        return skeleton;
    }

    /**
     * Hide/remove skeleton from a container
     * @param {HTMLElement} container - Container element
     * @param {boolean} animate - Whether to animate the fade out
     */
    static hide(container, animate = false) {
        const skeleton = container.querySelector('.chart-skeleton');
        if (!skeleton) return;

        if (animate) {
            skeleton.classList.add('fade-out');
            skeleton.addEventListener('animationend', () => {
                skeleton.remove();
            }, { once: true });
        } else {
            skeleton.remove();
        }
    }

    /**
     * Check if container has a skeleton
     * @param {HTMLElement} container - Container element
     * @returns {boolean}
     */
    static has(container) {
        return container.querySelector('.chart-skeleton') !== null;
    }
}

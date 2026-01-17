/**
 * CodeMirrorEditor - Reusable CodeMirror wrapper component
 * Provides consistent SQL editor with copy, format, and test functionality
 *
 * Usage:
 *   const editor = new CodeMirrorEditor('#my-textarea', {
 *       copyBtn: true,
 *       formatBtn: true,
 *       testBtn: true,
 *       onTest: (query) => { ... },
 *       onChange: (query) => { ... }
 *   });
 */

export default class CodeMirrorEditor {
    /**
     * @param {string|HTMLElement} element - Textarea element or selector
     * @param {Object} options - Configuration options
     * @param {boolean} options.copyBtn - Show copy button (default: true)
     * @param {boolean} options.formatBtn - Show format button (default: false)
     * @param {boolean} options.testBtn - Show test button (default: false)
     * @param {boolean} options.readOnly - Make editor read-only (default: false)
     * @param {string} options.mode - CodeMirror mode (default: 'text/x-sql')
     * @param {string} options.theme - CodeMirror theme (default: 'default')
     * @param {boolean} options.lineNumbers - Show line numbers (default: true)
     * @param {boolean} options.lineWrapping - Enable line wrapping (default: true)
     * @param {number} options.minHeight - Minimum height in pixels (default: 100)
     * @param {Function} options.onTest - Callback when test button clicked
     * @param {Function} options.onChange - Callback when content changes
     * @param {Function} options.onFormat - Custom format function (optional)
     */
    constructor(element, options = {}) {
        this.textarea = typeof element === 'string'
            ? document.querySelector(element)
            : element;

        if (!this.textarea) {
            console.warn('CodeMirrorEditor: Element not found');
            return;
        }

        this.options = {
            copyBtn: true,
            formatBtn: false,
            testBtn: false,
            readOnly: false,
            mode: 'text/x-mysql',  // MySQL mode for better keyword highlighting (CASE, WHEN, etc.)
            theme: 'default',
            lineNumbers: true,
            lineWrapping: true,
            minHeight: 100,
            onTest: null,
            onChange: null,
            onFormat: null,
            ...options
        };

        this.editor = null;
        this.wrapper = null;
        this.init();
    }

    /**
     * Initialize the CodeMirror editor
     */
    init() {
        if (typeof CodeMirror === 'undefined') {
            console.warn('CodeMirrorEditor: CodeMirror library not loaded');
            return;
        }

        this.createWrapper();
        this.createEditor();
        this.createToolbar();
        this.bindEvents();
    }

    /**
     * Create wrapper element around textarea
     */
    createWrapper() {
        this.wrapper = document.createElement('div');
        this.wrapper.className = 'code-editor-wrapper';
        this.textarea.parentNode.insertBefore(this.wrapper, this.textarea);
        this.wrapper.appendChild(this.textarea);
    }

    /**
     * Create CodeMirror instance
     */
    createEditor() {
        this.editor = CodeMirror.fromTextArea(this.textarea, {
            mode: this.options.mode,
            theme: this.options.theme,
            lineNumbers: this.options.lineNumbers,
            lineWrapping: this.options.lineWrapping,
            readOnly: this.options.readOnly
        });

        // Set minimum height via CSS variable
        this.wrapper.style.setProperty('--editor-min-height', `${this.options.minHeight}px`);

        if (this.options.readOnly) {
            this.wrapper.classList.add('read-only');
        }
    }

    /**
     * Create toolbar with action buttons
     */
    createToolbar() {
        const hasButtons = this.options.copyBtn || this.options.formatBtn || this.options.testBtn;
        if (!hasButtons) return;

        // Create floating button container (top-right)
        const floatingBtns = document.createElement('div');
        floatingBtns.className = 'code-editor-floating-btns';

        if (this.options.copyBtn) {
            const copyBtn = document.createElement('button');
            copyBtn.type = 'button';
            copyBtn.className = 'btn btn-sm btn-outline-secondary code-editor-copy-btn';
            copyBtn.innerHTML = '<i class="fas fa-copy"></i>';
            // Bootstrap tooltip attributes
            copyBtn.setAttribute('data-bs-toggle', 'tooltip');
            copyBtn.setAttribute('data-bs-placement', 'left');
            copyBtn.setAttribute('data-bs-title', 'Copy to clipboard');
            copyBtn.addEventListener('click', () => this.copy());
            floatingBtns.appendChild(copyBtn);
            this.copyBtn = copyBtn;
        }

        this.wrapper.appendChild(floatingBtns);

        // Initialize tooltip after button is in DOM
        if (this.copyBtn && typeof bootstrap !== 'undefined' && bootstrap.Tooltip) {
            new bootstrap.Tooltip(this.copyBtn, {
                delay: { show: 400, hide: 100 }
            });
        }

        // Create bottom toolbar if format or test buttons needed
        if (this.options.formatBtn || this.options.testBtn) {
            const toolbar = document.createElement('div');
            toolbar.className = 'code-editor-toolbar';

            if (this.options.formatBtn) {
                const formatBtn = document.createElement('button');
                formatBtn.type = 'button';
                formatBtn.className = 'btn btn-sm btn-outline-secondary';
                formatBtn.innerHTML = '<i class="fas fa-align-left"></i> Format SQL';
                formatBtn.addEventListener('click', () => this.format());
                toolbar.appendChild(formatBtn);
            }

            if (this.options.testBtn) {
                const testBtn = document.createElement('button');
                testBtn.type = 'button';
                testBtn.className = 'btn btn-sm btn-primary';
                testBtn.innerHTML = '<i class="fas fa-play"></i> Test Query';
                testBtn.addEventListener('click', () => this.test());
                toolbar.appendChild(testBtn);
            }

            this.wrapper.appendChild(toolbar);
        }
    }

    /**
     * Bind editor events
     */
    bindEvents() {
        if (this.options.onChange) {
            this.editor.on('change', () => {
                this.options.onChange(this.getValue());
            });
        }

        // Auto-format on blur (if not read-only and format is enabled)
        if (!this.options.readOnly && this.options.formatBtn) {
            this.editor.on('blur', () => {
                const query = this.getValue();
                if (query.trim()) {
                    this.format();
                }
            });
        }
    }

    /**
     * Get editor value
     * @returns {string}
     */
    getValue() {
        return this.editor ? this.editor.getValue() : this.textarea.value;
    }

    /**
     * Set editor value
     * @param {string} value
     */
    setValue(value) {
        if (this.editor) {
            this.editor.setValue(value);
        } else {
            this.textarea.value = value;
        }
    }

    /**
     * Copy content to clipboard
     */
    copy() {
        const query = this.getValue();
        const copyBtn = this.wrapper.querySelector('.code-editor-copy-btn');

        if (!query.trim()) {
            this.showFeedback(copyBtn, 'Nothing to copy', false);
            return;
        }

        navigator.clipboard.writeText(query).then(() => {
            this.showFeedback(copyBtn, 'Copied!', true);
        }).catch(() => {
            this.showFeedback(copyBtn, 'Failed', false);
        });
    }

    /**
     * Format SQL query
     */
    format() {
        if (this.options.onFormat) {
            const formatted = this.options.onFormat(this.getValue());
            if (formatted) {
                this.setValue(formatted);
            }
            return;
        }

        // Default SQL formatting
        let query = this.getValue();
        if (!query.trim()) return;

        query = this.formatSQL(query);
        this.setValue(query);
    }

    /**
     * Default SQL formatter - phpMyAdmin style
     * Keywords on their own line, content indented below
     * @param {string} query
     * @returns {string}
     */
    formatSQL(query) {
        // Normalize whitespace
        query = query.replace(/\s+/g, ' ').trim();

        // Fix missing spaces before keywords (e.g., `column`DESC -> `column` DESC)
        query = query.replace(/(\S)(DESC|ASC)\b/gi, '$1 $2');

        // Main SQL clauses (top-level keywords that get their own line)
        const mainClauses = [
            'SELECT', 'FROM', 'WHERE', 'ORDER BY', 'GROUP BY', 'HAVING',
            'LIMIT', 'SET', 'VALUES', 'JOIN', 'LEFT JOIN', 'RIGHT JOIN',
            'INNER JOIN', 'OUTER JOIN', 'CROSS JOIN', 'INSERT INTO',
            'UPDATE', 'DELETE FROM', 'UNION ALL', 'UNION', 'DESC', 'ASC'
        ];

        // CASE statement keywords (get their own line with indentation)
        const caseKeywords = ['CASE', 'WHEN', 'THEN', 'ELSE', 'END'];

        // All keywords combined
        const allKeywords = [...mainClauses, ...caseKeywords];

        // Sort by length descending to match longer keywords first
        const sortedKeywords = [...allKeywords].sort((a, b) => b.length - a.length);

        // Add line break before each keyword (but not END AS which should stay together)
        sortedKeywords.forEach(keyword => {
            // Special handling: don't break before END when followed by AS
            if (keyword === 'END') {
                // Match END not followed by AS
                query = query.replace(/\s+(END)\s+(?!AS\b)/gi, '\n$1\n');
                // Match END AS - keep together
                query = query.replace(/\s+(END\s+AS)\s+/gi, '\n$1 ');
            } else {
                const regex = new RegExp('\\s+(' + keyword.replace(/ /g, '\\s+') + ')\\b', 'gi');
                query = query.replace(regex, '\n$1');
            }
        });

        // Handle leading keyword (SELECT, INSERT, UPDATE, DELETE, etc.)
        const leadingKeywords = ['SELECT', 'INSERT INTO', 'UPDATE', 'DELETE FROM'];
        leadingKeywords.forEach(keyword => {
            const regex = new RegExp('^(' + keyword.replace(/ /g, '\\s+') + ')\\s+', 'i');
            query = query.replace(regex, '$1\n');
        });

        // Separate keyword from its content (keyword on own line, content on next line)
        // Skip END AS which we handled specially above
        sortedKeywords.forEach(keyword => {
            if (keyword === 'END') return; // Already handled
            const regex = new RegExp('\\n(' + keyword.replace(/ /g, '\\s+') + ')\\s+', 'gi');
            query = query.replace(regex, '\n$1\n');
        });

        // Handle commas - each item on new line
        query = query.replace(/,\s*/g, ',\n');

        // Handle AND/OR - keep at end of line, next condition on new line
        query = query.replace(/\s+(AND)\s+/gi, ' $1 \n');
        query = query.replace(/\s+(OR)\s+/gi, ' $1 \n');

        // Clean up: trim lines and remove empty lines
        const lines = query.split('\n')
            .map(line => line.trim())
            .filter(line => line.length > 0);

        // Track nesting for proper indentation
        let caseDepth = 0;
        const formattedLines = [];

        for (let i = 0; i < lines.length; i++) {
            let line = lines[i];
            const upperLine = line.toUpperCase();

            // Check if line is a main clause keyword only
            const isMainKeyword = mainClauses.some(kw => {
                const regex = new RegExp('^' + kw.replace(/ /g, '\\s*') + '$', 'i');
                return regex.test(line);
            });

            // Check if line is a CASE keyword only (not END AS ...)
            const isCaseKeywordOnly = caseKeywords.some(kw => {
                const regex = new RegExp('^' + kw + '$', 'i');
                return regex.test(line);
            });

            // Check if line starts with END AS
            const isEndAs = /^END\s+AS\b/i.test(line);

            // Decrease depth before END
            if (upperLine === 'END' || isEndAs) {
                caseDepth = Math.max(0, caseDepth - 1);
            }

            if (isMainKeyword) {
                // Main keywords: no indent, uppercase
                formattedLines.push(line.toUpperCase());
            } else if (isCaseKeywordOnly) {
                // CASE keywords alone: indent based on depth, uppercase
                const indent = '    '.repeat(caseDepth + 1);
                formattedLines.push(indent + line.toUpperCase());
            } else if (isEndAs) {
                // END AS something: indent like CASE keyword, uppercase END AS
                const indent = '    '.repeat(caseDepth + 1);
                formattedLines.push(indent + line.replace(/^END\s+AS/i, 'END AS'));
            } else {
                // Content lines: indent based on context
                let indent = '    '; // Base indent for content under main keywords

                // Add extra indent if inside CASE
                if (caseDepth > 0) {
                    indent = '    '.repeat(caseDepth + 2);
                }

                formattedLines.push(indent + line);
            }

            // Increase depth after CASE
            if (upperLine === 'CASE') {
                caseDepth++;
            }
        }

        return formattedLines.join('\n');
    }

    /**
     * Test query (calls onTest callback)
     */
    test() {
        if (this.options.onTest) {
            this.options.onTest(this.getValue());
        }
    }

    /**
     * Show feedback message near button
     * @param {HTMLElement} btn
     * @param {string} message
     * @param {boolean} success
     */
    showFeedback(btn, message, success) {
        if (!btn) return;

        const existing = btn.querySelector('.copy-feedback');
        if (existing) existing.remove();

        const feedback = document.createElement('span');
        feedback.className = `copy-feedback ${success ? 'success' : 'error'}`;
        feedback.textContent = message;
        btn.style.position = 'relative';
        btn.appendChild(feedback);

        setTimeout(() => feedback.classList.add('show'), 10);
        setTimeout(() => {
            feedback.classList.remove('show');
            setTimeout(() => feedback.remove(), 200);
        }, 1500);
    }

    /**
     * Refresh the editor (useful after visibility changes)
     */
    refresh() {
        if (this.editor) {
            this.editor.refresh();
        }
    }

    /**
     * Focus the editor
     */
    focus() {
        if (this.editor) {
            this.editor.focus();
        }
    }

    /**
     * Destroy the editor and restore textarea
     */
    destroy() {
        if (this.editor) {
            this.editor.toTextArea();
        }
        if (this.wrapper && this.wrapper.parentNode) {
            this.wrapper.parentNode.insertBefore(this.textarea, this.wrapper);
            this.wrapper.remove();
        }
    }
}

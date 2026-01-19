/**
 * DatePickerInit - Initialize daterangepicker on filter inputs
 * Handles both single date and date range pickers with preset ranges
 */
class DatePickerInit {
    /**
     * Default preset ranges for date range picker
     */
    static getDefaultRanges() {
        return {
            'Today': [moment(), moment()],
            'Yesterday': [moment().subtract(1, 'days'), moment().subtract(1, 'days')],
            'Last 7 Days': [moment().subtract(6, 'days'), moment()],
            'Last 30 Days': [moment().subtract(29, 'days'), moment()],
            'This Month': [moment().startOf('month'), moment().endOf('month')],
            'Last Month': [moment().subtract(1, 'month').startOf('month'), moment().subtract(1, 'month').endOf('month')],
            'Year to Date': [moment().startOf('year'), moment()]
        };
    }

    /**
     * Initialize all date pickers in a container
     * @param {HTMLElement|Document} container - Container to search for pickers
     */
    static init(container = document) {
        // Check if jQuery and daterangepicker are available
        if (typeof $ === 'undefined' || typeof $.fn.daterangepicker === 'undefined') {
            console.warn('DatePickerInit: jQuery or daterangepicker not loaded');
            return;
        }

        const pickers = container.querySelectorAll('.dgc-datepicker');
        pickers.forEach(input => {
            // Skip if already initialized
            if (input.dataset.daterangepickerInit === 'true') {
                return;
            }
            DatePickerInit.initPicker(input);
        });
    }

    /**
     * Initialize a single date picker
     * @param {HTMLInputElement} input - Input element to initialize
     */
    static initPicker(input) {
        const pickerType = input.dataset.pickerType || 'single';
        const $input = $(input);

        if (pickerType === 'single') {
            DatePickerInit.initSinglePicker($input);
        } else {
            DatePickerInit.initRangePicker($input);
        }

        // Mark as initialized
        input.dataset.daterangepickerInit = 'true';
    }

    /**
     * Initialize a single date picker
     * @param {jQuery} $input - jQuery wrapped input
     */
    static initSinglePicker($input) {
        const existingValue = $input.val();
        const startDate = existingValue ? moment(existingValue, 'YYYY-MM-DD') : moment();

        $input.daterangepicker({
            singleDatePicker: true,
            showDropdowns: true,
            autoUpdateInput: true,
            startDate: startDate.isValid() ? startDate : moment(),
            locale: {
                format: 'YYYY-MM-DD',
                cancelLabel: 'Clear'
            }
        });

        // Set initial display value if exists
        if (existingValue && moment(existingValue, 'YYYY-MM-DD').isValid()) {
            $input.val(existingValue);
        } else {
            $input.val(''); // Clear if no initial value
        }

        // Handle apply event
        $input.on('apply.daterangepicker', function(ev, picker) {
            $(this).val(picker.startDate.format('YYYY-MM-DD'));
            // Trigger change event for other listeners
            this.dispatchEvent(new Event('change', { bubbles: true }));
        });

        // Handle cancel/clear event
        $input.on('cancel.daterangepicker', function() {
            $(this).val('');
            this.dispatchEvent(new Event('change', { bubbles: true }));
        });
    }

    /**
     * Initialize a date range picker with presets
     * @param {jQuery} $input - jQuery wrapped input
     */
    static initRangePicker($input) {
        $input.daterangepicker({
            autoUpdateInput: true,
            ranges: DatePickerInit.getDefaultRanges(),
            startDate: moment().subtract(6, 'days'),
            endDate: moment(),
            locale: {
                format: 'YYYY-MM-DD',
                separator: ' - ',
                cancelLabel: 'Clear'
            },
            alwaysShowCalendars: true,
            opens: 'left'
        });

        // Clear initial value (user should explicitly select)
        $input.val('');

        // Helper to update display and data attributes
        const updateValue = function(picker) {
            const startDate = picker.startDate.format('YYYY-MM-DD');
            const endDate = picker.endDate.format('YYYY-MM-DD');

            // Set display value
            $input.val(startDate + ' - ' + endDate);

            // Store individual values as data attributes for FilterUtils
            $input.data('from', startDate);
            $input.data('to', endDate);

            // Also store in dataset for vanilla JS access
            $input[0].dataset.from = startDate;
            $input[0].dataset.to = endDate;
        };

        // Update preview as user selects dates (live preview)
        $input.on('apply.daterangepicker', function(ev, picker) {
            updateValue(picker);
            // Trigger change event for other listeners
            this.dispatchEvent(new Event('change', { bubbles: true }));
        });

        // Handle cancel/clear event
        $input.on('cancel.daterangepicker', function() {
            $(this).val('');
            $(this).removeData('from');
            $(this).removeData('to');
            delete this.dataset.from;
            delete this.dataset.to;
            this.dispatchEvent(new Event('change', { bubbles: true }));
        });
    }

    /**
     * Get the date values from a picker input
     * @param {HTMLInputElement} input - The picker input element
     * @returns {Object|null} Object with date value(s) or null
     */
    static getValues(input) {
        const pickerType = input.dataset.pickerType || 'single';

        if (pickerType === 'single') {
            const value = input.value;
            return value ? { value: value } : null;
        } else {
            const from = input.dataset.from;
            const to = input.dataset.to;

            if (from || to) {
                return { from: from, to: to };
            }
            return null;
        }
    }

    /**
     * Destroy picker instance on an element
     * @param {HTMLInputElement} input - Input element
     */
    static destroy(input) {
        const $input = $(input);
        if ($input.data('daterangepicker')) {
            $input.data('daterangepicker').remove();
            input.dataset.daterangepickerInit = 'false';
        }
    }
}

// Export for module bundler
if (typeof module !== 'undefined' && module.exports) {
    module.exports = DatePickerInit;
}

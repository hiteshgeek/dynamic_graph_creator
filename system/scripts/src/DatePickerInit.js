/**
 * DatePickerInit - Initialize daterangepicker on filter inputs
 * Handles both single date and date range pickers with preset ranges
 */
class DatePickerInit {
    // Display format for user (DD-MM-YYYY)
    static DISPLAY_FORMAT = 'DD-MM-YYYY';
    // Data format for storage/queries (YYYY-MM-DD)
    static DATA_FORMAT = 'YYYY-MM-DD';
    /**
     * Get the current financial year (April to March for India)
     * @returns {Array} [startDate, endDate] as moment objects
     */
    static getFinancialYear() {
        const now = moment();
        const currentYear = now.year();
        const currentMonth = now.month(); // 0-indexed (0 = January)

        // Financial year starts in April (month index 3)
        if (currentMonth >= 3) {
            // April or later - FY starts this year
            return [moment([currentYear, 3, 1]), moment([currentYear + 1, 2, 31])];
        } else {
            // Before April - FY started last year
            return [moment([currentYear - 1, 3, 1]), moment([currentYear, 2, 31])];
        }
    }

    /**
     * Get last financial year
     * @returns {Array} [startDate, endDate] as moment objects
     */
    static getLastFinancialYear() {
        const [start, end] = DatePickerInit.getFinancialYear();
        return [moment(start).subtract(1, 'year'), moment(end).subtract(1, 'year')];
    }

    /**
     * Get company start date (default: 1 year ago if not set)
     * Can be overridden via window.companyStartDate (matches Rapidkart)
     * @returns {moment} Company start date
     */
    static getCompanyStartDate() {
        // Check if company start date is set globally (Rapidkart sets this)
        if (window.companyStartDate) {
            return moment(window.companyStartDate);
        }
        // Default: 1 year ago from today
        return moment().subtract(1, 'year').startOf('day');
    }

    /**
     * Default preset ranges for date range picker
     * Matches dashboard_old options
     */
    static getDefaultRanges() {
        return {
            'Today': [moment(), moment()],
            'Yesterday': [moment().subtract(1, 'days'), moment().subtract(1, 'days')],
            'Last 7 Days': [moment().subtract(6, 'days'), moment()],
            'Last 30 Days': [moment().subtract(29, 'days'), moment()],
            'This Month': [moment().startOf('month'), moment().endOf('month')],
            'Last Month': [moment().subtract(1, 'month').startOf('month'), moment().subtract(1, 'month').endOf('month')],
            'Year to Date': [moment().startOf('year'), moment()],
            'Company StartDate to Date': [DatePickerInit.getCompanyStartDate(), moment()],
            'This Financial Year': DatePickerInit.getFinancialYear(),
            'Last Financial Year': DatePickerInit.getLastFinancialYear()
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
            // Skip hidden elements - daterangepicker doesn't work well on hidden inputs
            // Check if input or any parent is hidden
            if (input.offsetParent === null || window.getComputedStyle(input).display === 'none') {
                return;
            }
            // Also check parent filter-input-item
            const filterItem = input.closest('.filter-input-item');
            if (filterItem && (filterItem.style.display === 'none' || window.getComputedStyle(filterItem).display === 'none')) {
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
        } else if (pickerType === 'main') {
            // Main datepicker with all preset ranges
            DatePickerInit.initMainPicker($input);
        } else {
            // Basic range picker without presets
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
        // Try parsing existing value in both formats
        let startDate = moment(existingValue, DatePickerInit.DATA_FORMAT, true);
        if (!startDate.isValid()) {
            startDate = moment(existingValue, DatePickerInit.DISPLAY_FORMAT, true);
        }
        if (!startDate.isValid()) {
            startDate = moment();
        }

        $input.daterangepicker({
            singleDatePicker: true,
            showDropdowns: true,
            autoUpdateInput: false,
            startDate: startDate,
            locale: {
                format: DatePickerInit.DISPLAY_FORMAT,
                cancelLabel: 'Clear'
            }
        });

        // Set initial display value if exists
        if (existingValue && startDate.isValid()) {
            $input.val(startDate.format(DatePickerInit.DISPLAY_FORMAT));
            // Store data format value
            $input[0].dataset.value = startDate.format(DatePickerInit.DATA_FORMAT);
        } else {
            $input.val(''); // Clear if no initial value
        }

        // Handle apply event
        $input.on('apply.daterangepicker', function(ev, picker) {
            // Display in DD-MM-YYYY format
            $(this).val(picker.startDate.format(DatePickerInit.DISPLAY_FORMAT));
            // Store data format (YYYY-MM-DD) for queries
            this.dataset.value = picker.startDate.format(DatePickerInit.DATA_FORMAT);
            // Trigger change event for other listeners
            this.dispatchEvent(new Event('change', { bubbles: true }));
        });

        // Handle cancel/clear event
        $input.on('cancel.daterangepicker', function() {
            $(this).val('');
            delete this.dataset.value;
            this.dispatchEvent(new Event('change', { bubbles: true }));
        });
    }

    /**
     * Initialize a basic date range picker (no presets)
     * @param {jQuery} $input - jQuery wrapped input
     */
    static initRangePicker($input) {
        $input.daterangepicker({
            autoUpdateInput: false,
            startDate: moment().subtract(6, 'days'),
            endDate: moment(),
            locale: {
                format: DatePickerInit.DISPLAY_FORMAT,
                separator: ' - ',
                cancelLabel: 'Clear'
            },
            alwaysShowCalendars: true,
            opens: 'left'
        });

        // Helper to update display and data attributes
        const updateValue = function($el, picker) {
            const startDateDisplay = picker.startDate.format(DatePickerInit.DISPLAY_FORMAT);
            const endDateDisplay = picker.endDate.format(DatePickerInit.DISPLAY_FORMAT);
            const startDateData = picker.startDate.format(DatePickerInit.DATA_FORMAT);
            const endDateData = picker.endDate.format(DatePickerInit.DATA_FORMAT);

            // Set display value in DD-MM-YYYY format
            $el.val(startDateDisplay + ' - ' + endDateDisplay);

            // Store data format values (YYYY-MM-DD) for queries
            $el.data('from', startDateData);
            $el.data('to', endDateData);

            // Also store in dataset for vanilla JS access
            $el[0].dataset.from = startDateData;
            $el[0].dataset.to = endDateData;
        };

        // Update preview as user selects dates (live preview)
        $input.on('apply.daterangepicker', function(ev, picker) {
            updateValue($(this), picker);
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
     * Initialize main date picker with all preset ranges
     * (Today, Yesterday, Last 7 Days, Last 30 Days, This Month, Last Month,
     * Year to Date, Company StartDate to Date, This Financial Year, Last Financial Year)
     * @param {jQuery} $input - jQuery wrapped input
     */
    static initMainPicker($input) {
        $input.daterangepicker({
            autoUpdateInput: false,
            ranges: DatePickerInit.getDefaultRanges(),
            startDate: moment().subtract(6, 'days'),
            endDate: moment(),
            locale: {
                format: DatePickerInit.DISPLAY_FORMAT,
                separator: ' - ',
                cancelLabel: 'Clear'
            },
            alwaysShowCalendars: true,
            opens: 'left'
        });

        // Helper to update display and data attributes
        const updateValue = function($el, picker) {
            const startDateDisplay = picker.startDate.format(DatePickerInit.DISPLAY_FORMAT);
            const endDateDisplay = picker.endDate.format(DatePickerInit.DISPLAY_FORMAT);
            const startDateData = picker.startDate.format(DatePickerInit.DATA_FORMAT);
            const endDateData = picker.endDate.format(DatePickerInit.DATA_FORMAT);

            // Set display value in DD-MM-YYYY format
            $el.val(startDateDisplay + ' - ' + endDateDisplay);

            // Store data format values (YYYY-MM-DD) for queries
            $el.data('from', startDateData);
            $el.data('to', endDateData);

            // Also store in dataset for vanilla JS access
            $el[0].dataset.from = startDateData;
            $el[0].dataset.to = endDateData;
        };

        // Update preview as user selects dates (live preview)
        $input.on('apply.daterangepicker', function(ev, picker) {
            updateValue($(this), picker);
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
     * Get the date values from a picker input (returns YYYY-MM-DD format for queries)
     * @param {HTMLInputElement} input - The picker input element
     * @returns {Object|null} Object with date value(s) or null
     */
    static getValues(input) {
        const pickerType = input.dataset.pickerType || 'single';

        if (pickerType === 'single') {
            // Return stored data format value (YYYY-MM-DD)
            const value = input.dataset.value;
            return value ? { value: value } : null;
        } else {
            // Return stored data format values (YYYY-MM-DD)
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

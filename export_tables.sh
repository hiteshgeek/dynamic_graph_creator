#!/bin/bash

# Export login-related tables from live databases
# Run this script to export schema and data for tables needed by DGC login system

# Get the directory where this script is located
SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"

# Load database credentials from export_tables/.developer_db.env
ENV_FILE="$SCRIPT_DIR/export_tables/.developer_db.env"
if [ -f "$ENV_FILE" ]; then
    source "$ENV_FILE"
else
    echo "Error: $ENV_FILE not found!"
    echo "Please create .developer_db.env with DB_HOST, DB_USER, and DB_PASS variables."
    exit 1
fi

# Output directory (always relative to script location)
OUTPUT_DIR="$SCRIPT_DIR/export_tables"
mkdir -p "$OUTPUT_DIR"

# Output files
OUTPUT_FILE_FACTORY="$OUTPUT_DIR/login_tables_factory.sql"
OUTPUT_FILE_STATIC="$OUTPUT_DIR/login_tables_static.sql"
OUTPUT_FILE_COMBINED="$OUTPUT_DIR/login_tables_export.sql"

# =========================================================================
# Tables from rapidkart_factory (main database)
# =========================================================================
FACTORY_TABLES=(
    # User tables
    "auser"
    "auser_session"
    "auser_company_mapping"
    "auser_role"
    "auser_otp_settings"
    "auser_permission_security"
    "auser_email_verification"
    "auser_mobile_verification"
    "auser_expense_claim_status"
    "auser_expense_claim_type"

    # Role & Permission tables
    "arole"
    "arole_permission"
    "permission_security"

    # Licence tables
    "licence"
    "licence_domain"
    "licence_companies"
    "licence_mask_config_mapping"
    "licence_system_preferences_mapping"
    "licence_measurement_mapping"
    "licence_invoice_config_mapping"
    "mask_config"
    "system_preferences"
    "system_preferences_group"
    "system_preferences_category"
    "system_preferences_module_mapping"
    "system_preferences_licence_history"

    # Outlet tables
    "outlet"
    "outlet_bank"
    "outlet_user_mapping"
    "outlet_header_images"
    "outlet_footer_images"
    "outlet_logo_images"
    "outlet_store_images"
    "outlet_payment_mapping"
    "outlet_extra_charges_mapping"
    "outlet_business_tax_profile_mapping"

    # Warehouse tables
    "warehouse"
    "warehouse_user_mapping"
    "warehouse_section"

    # Checkpoint tables
    "checkpoint_mapping"
    "checkpoint_type"
    "checkpoint_order"

    # Location tables
    "country"
    "state"
    "coverage"
    "coverage_locality"

    # Site variables
    "variable"
    "variable_category"
    "variable_update_log"
    "variable_company_mapping"
)

# =========================================================================
# Tables from rapidkart_factory_static (status/lookup tables)
# =========================================================================
STATIC_TABLES=(
    "auser_status"
    "auser_session_status"
    "auser_email_verification_status"
    "auser_mobile_verification_status"
    "apermission"
    "module"
)

# Convert arrays to space-separated strings
FACTORY_TABLE_LIST="${FACTORY_TABLES[*]}"
STATIC_TABLE_LIST="${STATIC_TABLES[*]}"

echo "==========================================================================="
echo "Exporting tables from rapidkart_factory..."
echo "Tables: $FACTORY_TABLE_LIST"
echo "==========================================================================="

mysqldump -h "$DB_HOST" -u "$DB_USER" -p"$DB_PASS" rapidkart_factory $FACTORY_TABLE_LIST \
    --single-transaction \
    --routines \
    --triggers \
    --add-drop-table \
    > "$OUTPUT_FILE_FACTORY"

echo ""
echo "==========================================================================="
echo "Exporting tables from rapidkart_factory_static..."
echo "Tables: $STATIC_TABLE_LIST"
echo "==========================================================================="

mysqldump -h "$DB_HOST" -u "$DB_USER" -p"$DB_PASS" rapidkart_factory_static $STATIC_TABLE_LIST \
    --single-transaction \
    --routines \
    --triggers \
    --add-drop-table \
    > "$OUTPUT_FILE_STATIC"

# echo ""
# echo "==========================================================================="
# echo "Combining exports..."
# echo "==========================================================================="

# Combine both exports
# cat "$OUTPUT_FILE_FACTORY" > "$OUTPUT_FILE_COMBINED"
# echo "" >> "$OUTPUT_FILE_COMBINED"
# echo "-- ==========================================================================" >> "$OUTPUT_FILE_COMBINED"
# echo "-- Tables from rapidkart_factory_static" >> "$OUTPUT_FILE_COMBINED"
# echo "-- ==========================================================================" >> "$OUTPUT_FILE_COMBINED"
# echo "" >> "$OUTPUT_FILE_COMBINED"
# cat "$OUTPUT_FILE_STATIC" >> "$OUTPUT_FILE_COMBINED"

echo ""
echo "Export complete!"
echo "  - Factory tables: $OUTPUT_FILE_FACTORY"
echo "  - Static tables:  $OUTPUT_FILE_STATIC"
# echo "  - Combined:       $OUTPUT_FILE_COMBINED"

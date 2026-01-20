<?php

/**
 * A class containing constants for the Database tables used by the system.
 *
 * A class is not exactly required for this situation, that is why it's in the includes folder.
 *
 * The class is used to provide proper scope to the constants and not let them go wild west in the global scope...
 *
 * @author Dynamic Graph Creator
 */
class SystemTables
{
      // =========================================================================
      // FROM LIVE PROJECT - Tables used by login/session classes
      // =========================================================================

      /**
       * User management tables
       */
      const DB_TBL_USER = "auser";
      const DB_TBL_USER_STATUS = "rapidkart_factory_static.auser_status";
      const DB_TBL_USER_ROLE = "auser_role";
      const DB_TBL_USER_LOG = "auser_log";
      const DB_TBL_USER_ACTION = "auser_log_action";
      const DB_TBL_USER_DETAILS = "auser_personal_details";
      const DB_TBL_USER_COMPANY_MAPPING = "auser_company_mapping";
      const DB_TBL_AUSER_OTP_SETTINGS = "auser_otp_settings";
      const DB_TBL_AUSER_EMAIL_VERIFICATION = "auser_email_verification";
      const DB_TBL_AUSER_EMAIL_VERIFICATION_STATUS = "auser_email_verification_status";
      const DB_TBL_AUSER_MOBILE_VERIFICATION = "auser_mobile_verification";
      const DB_TBL_AUSER_MOBILE_VERIFICATION_STATUS = "auser_mobile_verification_status";
      const DB_TBL_AUSER_EXPENSE_CLAIM_STATUS = "auser_expense_claim_status";
      const DB_TBL_AUSER_EXPENSE_CLAIM_TYPE = "auser_expense_claim_type";

      /**
       * Role & Permission tables
       */
      const DB_TBL_ROLE = "arole";
      const DB_TBL_ROLE_PERMISSION = "arole_permission";
      const DB_TBL_PERMISSION = "rapidkart_factory_static.apermission";
      const DB_TBL_PERMISSION_SECURITY = "permission_security";
      const DB_TBL_AUSER_PERMISSION_SECURITY = "auser_permission_security";

      /**
       * Session Management tables
       */
      const DB_TBL_USER_SESSION = "auser_session";
      const DB_TBL_USER_SESSION_STATUS = "rapidkart_factory_static.auser_session_status";

      /**
       * Licence tables
       */
      const DB_TBL_LICENCE = "licence";
      const DB_TBL_LICENCE_DOMAIN = "licence_domain";
      const DB_TBL_LICENCE_COMPANIES = "licence_companies";
      const DB_TBL_LICENCE_MASK_CONFIG_MAPPING = "licence_mask_config_mapping";
      const DB_TBL_LICENCE_INVOICE_CONFIG_MAPPING = "licence_invoice_config_mapping";
      const DB_TBL_LICENCE_SYSTEM_PREFERENCES_MAPPING = "licence_system_preferences_mapping";
      const DB_TBL_LICENCE_MEASUREMENT_MAPPING = "licence_measurement_mapping";
      const DB_TBL_MASK_CONFIG = "mask_config";
      const DB_TBL_SYSTEM_PREFERENCES = "system_preferences";
      const DB_TBL_SYSTEM_PREFERENCES_GROUP = "system_preferences_group";
      const DB_TBL_SYSTEM_PREFERENCES_CATEGORY = "system_preferences_category";
      const DB_TBL_SYSTEM_PREFERENCES_MODULE_MAPPING = "system_preferences_module_mapping";

      /**
       * Module tables
       */
      const DB_TBL_MODULE = "rapidkart_factory_static.module";

      /**
       * Outlet tables
       */
      const DB_TBL_OUTLET = "outlet";
      const DB_TBL_OUTLET_BANK = "outlet_bank";
      const DB_TBL_OUTLET_USER_MAPPING = "outlet_user_mapping";
      const DB_TBL_OUTLET_PAYMENT_MAPPING = "outlet_payment_mapping";
      const DB_TBL_OUTLET_BUSINESS_TAX_PROFILE_MAPPING = "outlet_business_tax_profile_mapping";
      const DB_TBL_OUTLET_HEADER_IMAGES = "outlet_header_images";
      const DB_TBL_OUTLET_FOOTER_IMAGES = "outlet_footer_images";
      const DB_TBL_OUTLET_STORE_IMAGES = "outlet_store_images";
      const DB_TBL_OUTLET_LOGO_IMAGES = "outlet_logo_images";
      const DB_TBL_OUTLET_EXTRA_CHARGES_MAPPING = "outlet_extra_charges_mapping";

      /**
       * Warehouse tables
       */
      const DB_TBL_WAREHOUSE = "warehouse";
      const DB_TBL_WAREHOUSE_USER_MAPPING = "warehouse_user_mapping";
      const DB_TBL_WAREHOUSE_SECTION = "warehouse_section";
      const DB_TBL_CHECKPOINT_MAPPING = "checkpoint_mapping";

      /**
       * Checkpoint tables
       */
      const DB_TBL_CHECKPOINT_TYPE = "checkpoint_type";
      const DB_TBL_CHECKPOINT_ORDER = "checkpoint_order";
      const DB_TBL_CHECKPOINT_ORDER_BOOKING = "checkpoint_order_booking";

      /**
       * Location tables
       */
      const DB_TBL_COUNTRY = "country";
      const DB_TBL_STATE = "state";
      const DB_TBL_COVERAGE = "coverage";
      const DB_TBL_COVERAGE_LOCALITY = "coverage_locality";

      /**
       * Customer tables
       */
      const DB_TBL_CUSTOMER = "customer";
      const DB_TBL_CUSTOMER_INVENTORY = "customer_inventory";
      const DB_TBL_CUSTOMER_PAYMENT_OPTION = "customer_payment_option";

      /**
       * Invoice & Config tables
       */
      const DB_TBL_INVOICE = "invoice";
      const DB_TBL_INVOICE_CONFIG = "invoice_config";
      const DB_TBL_INVOICE_CONFIG_VALUES = "invoice_config_values";

      /**
       * Item & Inventory tables
       */
      const DB_TBL_ITEM = "item_item";
      const DB_TBL_ITEM_PHOTO = "item_item_photo";
      const DB_TBL_ITEM_ITEM_ATTRIBUTE_VALUE = "item_item_attribute_value";
      const DB_TBL_INVENTORY_SET_ITEM = "inventory_set_item";
      const DB_TBL_INVENTORY_SET_VARIATIONS = "inventory_set_variations";
      const DB_TBL_INVENTORY_SET_VARIATIONS_ATTRIBUTE_ATTRIBUTE_VALUE = "inventory_set_variations_attribute_attribute_value";
      const DB_TBL_ORDER_ITEM = "item_order_item";

      /**
       * Attribute tables
       */
      const DB_TBL_ATTRIBUTE = "item_attribute";
      const DB_TBL_ATTRIBUTE_VALUE = "item_attribute_value";

      /**
       * Category tables
       */
      const DB_TBL_CATEGORY = "category";
      const DB_TBL_GI_CATEGORY = "item_gi_category";

      /**
       * Account tables
       */
      const DB_TBL_ACCOUNT_LEDGER = "account_ledger";
      const DB_TBL_ACKNOWLEDGEMENT_VOUCHER_INVOICES = "acknowledgement_voucher_invoices";
      const DB_TBL_ACKNOWLEDGEMENT_VOUCHER_DEBIT_NOTES = "acknowledgement_voucher_debit_notes";

      /**
       * Report tables
       */
      const DB_TBL_PHYSICAL_STOCK_VERIFICATION_REPORT_ATTRIBUTE_FILTER = "physical_stock_verification_report_attribute_filter";

      // =========================================================================
      // DGC SPECIFIC - Tables for Dynamic Graph Creator
      // =========================================================================

      // Graph table (status in gsid column: 1=active, 3=deleted)
      const DB_TBL_GRAPH = "graph";

      // Data filter table (status in dfsid column: 1=active, 3=deleted)
      const DB_TBL_DATA_FILTER = "data_filter";

      // Dashboard template category table (status in dtcsid column)
      const DB_TBL_DASHBOARD_TEMPLATE_CATEGORY = "dashboard_template_category";

      // Dashboard template table (status in dtsid column)
      const DB_TBL_DASHBOARD_TEMPLATE = "dashboard_template";

      // Dashboard instance table (status in disid column)
      const DB_TBL_DASHBOARD_INSTANCE = "dashboard_instance";
}

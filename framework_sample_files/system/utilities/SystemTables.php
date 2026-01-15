<?php

/**
 * A class containing constants for the Database tables used by the system.
 * 
 * A class is not exactly required for this situation, that is why it's in the includes folder.
 * 
 * The class is used to provide proper scope to the constants and not let them go wild west in the global scope...
 *
 * @author Sohil Gupta
 * @since 20140624
 */
class SystemTables
{

      /**
       * User management tables
       */
      const DB_TBL_USER = "auser";
      const DB_TBL_USER_STATUS = "rapidkart_factory_static.auser_status";
      const DB_TBL_USER_ROLE = "auser_role";
      const DB_TBL_USER_LOG = "auser_log";
      const DB_TBL_USER_ACTION = "auser_log_action";
      const DB_TBL_USER_DETAILS = "auser_personal_details";
      const DB_TBL_USER_CASH_COLLECTION = "auser_cash_collection";
      const DB_TBL_ROLE = "arole";
      const DB_TBL_AROLE_CATEGORY_MAPPING = "arole_category_mapping";
      const DB_TBL_ROLE_STATUS = "rapidkart_factory_static.arole_status";
      const DB_TBL_ROLE_PERMISSION = "arole_permission";
      const DB_TBL_PERMISSION = "rapidkart_factory_static.apermission";
      const DB_TBL_PERSONAL_DETAILS = "personal_details";
      const DB_TBL_USER_MESSAGES = "auser_messages";
      const DB_TBL_AUSER_NOTIFICATION = "auser_notification";
      const DB_TBL_USER_MESSAGES_STATUS = "auser_messages_status";
      const DB_TBL_PERMISSION_STATUS = "apermission_status";
      const DB_TBL_AUSER_OTP_SETTINGS = "auser_otp_settings";
      const DB_TBL_AUSER_OTP_SETTINGS_STATUS = "auser_otp_settings_status";
      const DB_TBL_AUSER_OTP_CODE = "auser_otp_code";
      const DB_TBL_AUSER_OTP_CODE_STATUS = "auser_otp_code_status";
      const DB_TBL_AUSER_EMAIL_VERIFICATION = "auser_email_verification";
      const DB_TBL_AUSER_EMAIL_VERIFICATION_STATUS = "auser_email_verification_status";
      const DB_TBL_AUSER_MOBILE_VERIFICATION = "auser_mobile_verification";
      const DB_TBL_AUSER_MOBILE_VERIFICATION_STATUS = "auser_mobile_verification_status";
      const DB_TBL_SYSTEM_PREFERENCES = "system_preferences";
      const DB_TBL_USER_COMPANY_MAPPING = "auser_company_mapping";
      const DB_TBL_PERMISSION_SECURITY = "permission_security";
      const DB_TBL_PERMISSION_SECURITY_IP = "permission_security_ip";
      const DB_TBL_AUSER_PERMISSION_SECURITY = "auser_permission_security";
      const DB_TBL_PORTAL_PERMISSION = "rapidkart_factory_static.portal_permission";

      /**
       * Salutation
       */
      const DB_TBL_SALUTATION = "salutation";

      /**
       * Customer Management Tables
       */
      const DB_TBL_CUSTOMER = "customer";
      const DB_TBL_CUSTOMER_MERGE_LOG = "customer_merge_log";
      const DB_TBL_CUSTOMER_STATUS = "customer_status";
      const DB_TBL_CUSTOMER_CARD_STATUS = "customer_card_status";
      const DB_TBL_CUSTOMER_SHIPPING_ADDR = "customer_shipping_address";
      const DB_TBL_CUSTOMER_ENQUIRY = "enquiry";
      const DB_TBL_CUSTOMER_ENQUIRY_TEMPLATE = "enquiry_template";
      const DB_TBL_CUSTOMER_ENQUIRY_STATUS = "enquiry_status";
      const DB_TBL_CUSTOMER_TRACKING_LOG = "customer_tracking_log";
      const DB_TBL_CUSTOMER_INVENTORY = "customer_inventory";
      const DB_TBL_AUSER_FORGOT_PASSWORD = "auser_forgot_password";
      const DB_TBL_CUSTOMER_RECORD = "customer_record";
      const DB_TBL_CUSTOMER_VARIATION_PRICE_MAPPING = "customer_variation_price_mapping";
      const DB_TBL_CUSTOMER_EMAIL_VERIFICATION = "customer_email_verification";
      const DB_TBL_CUSTOMER_MOBILE_VERIFICATION = "customer_mobile_verification";
      const DB_TBL_CUSTOMER_FORGOT_PASSWORD_VERIFICATION = "customer_forgot_password_verification";
      const DB_TBL_CUSTOMER_TYPE = "customer_type";
      const DB_TBL_CUSTOMER_SALES_TYPE = "customer_sales_type";
      const DB_TBL_CUSTOMER_TERRITORY = "customer_territory";
      const DB_TBL_CUSTOMER_TERRITORY_MAPPING = "customer_territory_mapping";
      const DB_TBL_CUSTOMER_TERRITORY_MERGE_LOG = "customer_territory_merge_log";
      const DB_TBL_CUSTOMER_USER_MAPPING = "customer_user_mapping";
      const DB_TBL_CUSTOMER_REPORT_FILTER = "customer_report_filter";
      const DB_TBL_CUSTOMER_BRAND_MARGIN = "customer_brand_margin";
      const DB_TBL_CUSTOMER_LOGIN = "customer_login";
      const DB_TBL_CUSTOMER_PERMISSION_MAPPING = "customer_permission_mapping";
      const DB_TBL_CUSTOMER_FILE = "customer_file";
      const DB_TBL_CUSTOMER_GROUP_WISE_CATEGORY_DISCOUNT_MAPPING = "customer_group_wise_category_discount_mapping";
      const DB_TBL_CUSTOMER_CATEGORY_MONTH_WISE_TARGET = "customer_category_month_wise_target";
      const DB_TBL_CUSTOMER_CATEGORY_WISE_TARGET = "customer_category_wise_target";
      const DB_TBL_CUSTOMER_COMMISSION_MASTER = "customer_commission_master";

      /**
       * Session Management tables
       * 
       */
      const DB_TBL_USER_SESSION = "auser_session";
      const DB_TBL_USER_SESSION_STATUS = "rapidkart_factory_static.auser_session_status";

      /**
       * RapidKart's Product management tables
       */
      const DB_TBL_GI_VIEW = "generic_item_view";
      const DB_TBL_GI = "item_gi";
      const DB_TBL_INVENTORY_SET_VARIATIONS_ACTIVITIES = "inventory_set_variations_activities";
      const DB_TBL_GI_STATUS = "item_gi_status";
      const DB_TBL_GI_TYPE = "item_gi_type";
      const DB_TBL_GI_CATEGORY = "item_gi_category";
      const DB_TBL_GI_ATTRIBUTE = "item_gi_attribute";
      const DB_TBL_ATTRIBUTE = "item_attribute";
      const DB_TBL_ATTRIBUTE_VALUE = "item_attribute_value";
      const DB_TBL_ATTRIBUTE_STATUS = "item_attribute_status";
      const DB_TBL_ATTRIBUTE_TYPE = "item_attribute_type";
      const DB_TBL_ITEM_BRAND = "item_brand";
      const DB_TBL_ITEM_BRAND_STATUS = "item_brand_status";
      const DB_TBL_ITEM_PHOTO = "item_item_photo";
      const DB_TBL_ITEM_COMPANY = "item_company";
      const DB_TBL_ITEM_COMPANY_STATUS = "item_company_status";
      const DB_TBL_ITEM = "item_item";
      const DB_TBL_ITEM_TYPE = "item_item_type";
      const DB_TBL_ITEM_VIEW = "item_view";
      const DB_TBL_ITEM_STATUS = "item_item_status";
      const DB_TBL_ITEM_ITEM_ATTRIBUTE_VALUE = "item_item_attribute_value";
      const DB_TBL_GI_PINCODE_SET = "item_gi_pincode_set";
      const DB_TBL_ITEM_GROUP_STATUS = "item_group_status";
      const DB_TBL_ITEM_GROUP = "item_group";
      const DB_TBL_ITEM_GROUP_ITEM = "item_group_item";
      const DB_TBL_ITEM_GROUP_PHOTOS = "item_group_photos";
      const DB_TBL_ATTRIBUTE_GROUP = "item_attribute_group";
      const DB_TBL_ATTRIBUTE_GROUP_STATUS = "item_attribute_group_status";
      const DB_TBL_ITEM_RAW_MATERIALS = "item_item_raw_materials";
      const DB_TBL_ITEM_MEASUREMENT = "item_item_measurement";
      const DB_TBL_ITEM_BUSINESS_TAX_PROFILE_MAPPING = "item_item_business_tax_profile_mapping";
      const DB_TBL_BRAND_MERGE_LOG = 'brand_merge_log';
      const DB_TBL_COMPANY_MERGE_LOG = 'company_merge_log';
      const DB_TBL_ATTRIBUTE_MERGE_LOG = 'attribute_merge_log';
      const DB_TBL_ITEM_BRAND_LENGTH = "item_brand_length";
      const DB_TBL_ITEM_BRAND_WIDTH = "item_brand_width";
      const DB_TBL_BRAND_DISCOUNT_COMMISSION_MAPPING = "brand_discount_commission_mapping";
      const DB_TBL_BRAND_DISCOUNT_INCENTIVE_MAPPING = "brand_discount_incentive_mapping";
      const DB_TBL_BRAND_WISE_USER_TARGET = "brand_wise_user_target";
      const DB_TBL_MONTH_WISE_TARGET = "month_wise_target";
      const DB_TBL_COMPANY_FILE = "company_file";

      /**
       * job cart template
       */
      const DB_TBL_JOB_CARD_TEMPLATE = "job_card_template";
      const DB_TBL_JOB_CARD_ATTRIBUTE_VALUE = "job_card_attribute_value";
      const DB_TBL_JOB_CARD_TASK = "job_card_task";
      const DB_TBL_JOB_CARD_CHECKPOINT_ORDER = "job_card_checkpoint_order";
      const DB_TBL_JOB_CARD_TEMPLATE_ATTRIBUTE = "job_card_template_attribute";
      const DB_TBL_JOB_CARD_TEMPLATE_ATTRIBUTE_TYPE = "job_card_template_attribute_type";
      const DB_TBL_JOB_CARD_TEMPLATE_TASK = "job_card_template_task";
      const DB_TBL_JOB_CARD_TEMPLATE_STATUS = "job_card_template_status";
      const DB_TBL_JOB_CARD = "job_card";
      const DB_TBL_JOB_CARD_TASK_STATUS = "job_card_task_status";
      const DB_TBL_JOB_CARD_STATUS = "job_card_status";
      const DB_TBL_JOB_CARD_PHOTO = "job_card_photo";
      const DB_TBL_JOB_CARD_STATUS_WORKFLOW = "job_card_status_workflow";
      const DB_TBL_JOB_CARD_EDIT_HISTORY = "job_card_edit_history";
      const DB_TBL_JOB_CARD_TEMPLATE_CHECKLIST = "job_card_template_checklist";
      const DB_TBL_JOB_CARD_CHECKLIST = "job_card_checklist";
      const DB_TBL_JOB_CARD_INTEND = "job_card_intend";
      const DB_TBL_JOB_CARD_INTEND_STATUS = "job_card_intend_status";
      const DB_TBL_JOB_CARD_ITEM = "job_card_item";
      const DB_TBL_JOB_CARD_ITEM_PROGRESS_LOG = "job_card_item_progress_log";
      const DB_TBL_JOB_CARD_ITEM_PROGRESS_LOG_FILE = "job_card_item_progress_log_file";

      /**
       * Quotaion managemetn table 
       */
      const DB_TBL_QUOTATION = "quotation";
      const DB_TBL_QUOTATION_PRODUCT = "quotation_product";
      const DB_TBL_QUOTATION_PRODUCT_PALLET = "quotation_product_pallet";
      const DB_TBL_QUOTATION_TEMPLATE_ATTRIBUTE_TYPE = "quotation_template_attribute_type";
      const DB_TBL_QUOTATION_TEMPLATE = "quotation_template";
      const DB_TBL_QUOTATION_TEMPLATE_STATUS = "quotation_template_status";
      const DB_TBL_QUOTATION_PRODUCT_GROUP = "quotation_product_group";
      const DB_TBL_QUOTATION_STATUS = "quotation_status";
      const DB_TBL_QUOTATION_TEMPLATE_ATTRIBUTE = "quotation_template_attribute";
      const DB_TBL_QUOTATION_TEMPLATE_PRODUCT_ATTRIBUTE = "quotation_template_product_attribute";
      const DB_TBL_QUOTATION_ATTRIBUTE_VALUE = "quotation_attribute_value";
      const DB_TBL_QUOTATION_PRODUCT_ATTRIBUTE_VALUE = "quotation_product_attribute_value";
      const DB_TBL_QUOTATION_FILE = "quotation_file";
      const DB_TBL_QUOTATION_EDIT_HISTORY = "quotation_edit_history";
      const DB_TBL_QUOTATION_TEMPLATE_BUSINESS_TAX_PROFILE = "quotation_template_business_tax_profile";
      const DB_TBL_QUOTATION_TEMPLATE_EXTRA_CHARGES = "quotation_template_extra_charges";
      const DB_TBL_QUOTATION_BUSINESS_TAX_PROFILE = "quotation_business_tax_profile";
      const DB_TBL_QUOTATION_EXTRA_CHARGES = "quotation_extra_charges";
      const DB_TBL_QUOTATION_BUSINESS_TAX_PROFILE_MAPPING = "quotation_business_tax_profile_mapping";
      const DB_TBL_QUOTATION_BRAND_MARKUP = "quotation_brand_markup";
      const DB_TBL_QUOTATION_EXTRA_DISCOUNT = "quotation_extra_discount";
      const DB_TBL_QUOTATION_CATEGORY_TYPE = "quotation_category_type";
      const DB_TBL_QUOTATION_PRODUCT_SDP_MAPPING = "quotation_product_sdp_mapping";
      const DB_TBL_QUOTATION_ATTENDEE_MAPPING = "quotation_attendee_mapping";

      /**
       * RapidKart's Category management tables
       */
      const DB_TBL_CATEGORY = "category";
      const DB_TBL_CATEGORY_STATUS = "category_status";
      const DB_TBL_CATEGORY_CHILD = "category_child";
      const DB_TBL_CATEGORY_COVERAGE = "category_coverage";
      const DB_TBL_CATEGORY_ITEM_ATTRIBUTE = "category_item_attribute";
      const DB_TBL_CATEGORY_MERGE_LOG = 'category_merge_log';
      const DB_TBL_CATEGORY_STAGE = 'category_stage';
      const DB_TBL_CATEGORY_CUSTOM_OPTION = 'category_custom_option';
      const DB_TBL_CATEGORY_SUGGESTION_LIST = "category_suggestion_list";

      /**
       * Rapidkart's inventory management tables
       */
      public static $inventory_set = "inventory_set";

      const DB_TBL_INVENTORY_SET_STATUS = "inventory_set_status";
      const DB_TBL_INVENTORY_SET_ITEM = "inventory_set_item";
      const DB_TBL_INVENTORY_SET_ITEM_OPEN_STOCK = "inventory_set_item_open_stock";
      const DB_TBL_INVENTORY_SET_ITEM_OPEN_STOCK_TYPE = "inventory_set_item_open_stock_type";
      const DB_TBL_INVENTORY_SET_TYPE = "inventory_set_type";
      const DB_TBL_INVENTORY_SET_ITEM_STATUS = "inventory_set_item_status";
      const DB_TBL_INVENTORY_SET_IMAGE = "inventory_set_image";
      const DB_TBL_INVENTORY_SET_ATTRIBUTE_ATTRIBUTE_VALUE = "inventory_set_attribute_attribute_value";
      const DB_TBL_INVENTORY_SET_ITEM_ATTRIBUTE_ATTRIBUTE_VALUE = "inventory_set_item_attribute_attribute_value";
      const DB_TBL_INVENTORY_SET_VARIATIONS = "inventory_set_variations";
      const DB_TBL_INVENTORY_SET_VARIATIONS_STATUS = "inventory_set_variations_status";
      const DB_TBL_INVENTORY_SET_VARIATIONS_VENDOR_MAPPING = "inventory_set_variations_vendor_mapping";
      const DB_TBL_INVENTORY_SET_VARIATION_ATTRIBUTE_ATTRIBUTE_VALUE = "inventory_set_variations_attribute_attribute_value";
      const DB_TBL_INVENTORY_SET_VARIATIONS_PHOTOS = "inventory_set_variations_photos";
      const DB_TBL_INVENTORY_SET_VARIATION_FILE = "inventory_set_variation_file";
      const DB_TBL_INVENTORY_SET_VARIATIONS_LIQUIDATION_PRICE = "inventory_set_variations_liquidation_price";
      const DB_TBL_INVENTORY_SET_VARIATIONS_CHANGE_PRICE_LOG = "inventory_set_variations_change_price_log";
      const DB_TBL_INVENTORY_SET_VARIATIONS_FUTURE_PRICE = "inventory_set_variations_future_price";
      const DB_TBL_INVENTORY_SET_VARIATIONS_EXTRA_CHARGES_MAPPING = "inventory_set_variations_extra_charges_mapping";
      const DB_TBL_INVENTORY_SET_VARIATIONS_EXTRA_FIELDS = "inventory_set_variations_extra_fields";
      const DB_TBL_INVENTORY_SET_VARIATIONS_INCENTIVE_COMMISSION = "inventory_set_variation_commission_incentive";
      /*
         *  Consignment
         */
      const DB_TBL_INVENTORY_SET_CONTAINER = "inventory_set_container";
      const DB_TBL_INVENTORY_SET_CONTAINER_ATTRIBUTE = "inventory_set_container_attribute";
      const DB_TBL_INVENTORY_SET_CONTAINER_PURCHASE_ITEM = " inventory_set_consignment_purchase_item";
      const DB_TBL_INVENTORY_SET_CONTAINER_ITEMS = " inventory_set_container_items";
      const DB_TBL_INVENTORY_SET_CONTAINER_PUTAWAY_REQUEST = " inventory_set_container_putaway_request";
      const DB_TBL_INVENTORY_SET_CONTAINER_PUTAWAY_REQUEST_LOG = " inventory_set_container_putaway_request_log";
      const DB_TBL_INVENTORY_SET_CONTAINER_PUTAWAY_REQUEST_ITEMS = " inventory_set_container_putaway_request_items";
      const DB_TBL_INVENTORY_SET_CONTAINER_ITEMS_QC_LOG = " inventory_set_container_items_qc_log";
      const DB_TBL_INVENTORY_SET_CONTAINER_ITEMS_LABEL_LOG = " inventory_set_container_items_label_log";
      const DB_TBL_INVENTORY_SET_CONTAINER_STATUS = "inventory_set_container_status";
      const DB_TBL_CONSIGNMENT_PAYMENT_FILE = "consignment_payment_file";
      const DB_TBL_CONSIGNMENT_PAYMENT = "consignment_payment ";
      const DB_TBL_PURCHASE_INVOICE_REPORT_ATTRIBUTE_FILTER = "purchase_invoice_report_attribute_filter";
      const DB_TBL_PURCHASE_REPORT_TRANSPORT_ATTRIBUTE = "purchase_report_transport_attribute";
      const DB_TBL_CONSIGNMENT_PAYMENT_BUSINESS_TAX_PROFILE_MAPPING = "consignment_payment_business_tax_profile_mapping";
      const DB_TBL_CONSIGNMENT_PAYMENT_EXTRA_CHARGES = "consignment_payment_extra_charges";

      public static $inventory_set_closed_stock_log = "inventory_set_closed_stock_log";

      const DB_TBL_CONSIGNMENT_PAYMENT_TRANSPORTATION_DETAILS = "consignment_payment_transportation_details";
      const DB_TBL_CONSIGNMENT_PAYMENT_TRANSPORTATION_EXTRA_CHARGES_DETAILS = "consignment_payment_transportation_extra_charges_details";
      const DB_TBL_CONSIGNMENT_PAYMENT_TRANSPORTATION_DETAILS_STATUS = "consignment_payment_transportation_details_status";
      const DB_TBL_CONSIGNMENT_PAYMENT_EXTRA_DISCOUNT = "consignment_payment_extra_discount";
      const DB_TBL_INVENTORY_SET_STOCK_TYPE = "inventory_set_stock_type";
      const DB_TBL_INVENTORY_SET_VARIATIONS_STOCK = "inventory_set_variations_stock";
      const DB_TBL_INVENTORY_SET_VARIATIONS_HSN_CODE = "inventory_set_variations_hsn_code";
      const DB_TBL_INVENTORY_SET_VARIATION_ECOMMERCE_MAPPING = "ecommerce_variation_mapping";
      const DB_TBL_INVENTORY_SET_DELINKING_LOG = "inventory_set_delinking_log";
      const DB_TBL_INVENTORY_SET_SECTION_LOG = "inventory_set_section_log";
      const DB_TBL_INVENTORY_REPORT_ATTRIBUTE_FILTER = "inventory_report_attribute_filter";
      const DB_TBL_INVENTORY_STOCK_REPORT_ATTRIBUTE_FILTER = "inventory_stock_report_attribute_filter";
      const DB_TBL_INVENTORY_BATCH_REPORT_ATTRIBUTE_FILTER = "inventory_batch_report_attribute_filter";
      const DB_TBL_INVENTORY_BARCODE_REPORT_ATTRIBUTE_FILTER = "inventory_barcode_report_attribute_filter";
      const DB_TBL_INVENTORY_BARCODE_TABLE = "inventory_barcode_table";

      /**
       * Rapidkart's stock management tables
       */
      const DB_TBL_STOCK_TRANSFER = "stock_transfer";
      const DB_TBL_STOCK_TRANSFER_STATUS = "stock_transfer_status";
      const DB_TBL_STOCK_TRANSFER_ITEM = "stock_transfer_item";
      const DB_TBL_STOCK_TRANSFER_ITEM_STATUS = "stock_transfer_item_status";
      const DB_TBL_STOCK_TRANSFER_FILE = "stock_transfer_file";

      /**
       * Rapidkart's order management tables
       */
      const DB_TBL_ORDER = "item_order";
      const DB_TBL_ORDER_ITEM = "item_order_item";
      const DB_TBL_ORDER_STATUS = "item_order_status";
      const DB_TBL_ORDER_ITEM_STATUS = "item_order_item_status";
      const DB_TBL_ORDER_EXECUTION = "item_order_item_execution";
      const DB_TBL_ORDER_SHIPPING = "order_shipping";
      const DB_TBL_ORDER_TRANSACTION = "item_order_transaction";

      /**
       * Order request
       */
      const DB_TBL_ORDER_REQUEST = "order_request";
      const DB_TBL_ORDER_REQUEST_ITEM = "order_request_item";
      const DB_TBL_ORDER_REQUEST_STATUS = "order_request_status";

      /**
       * Customer Queries
       */
      const DB_TBL_ISSUES = "customer_query_issue";
      const DB_TBL_QUERY = "customer_query";
      const DB_TBL_QUERY_VIEW = "customer_ticket_view";
      const DB_TBL_QUERY_STATUS = "customer_query_status";
      const DB_TBL_QUERY_COMMENT = "customer_query_comment";
      const DB_TBL_QUERY_ATTRIBUTE = "customer_query_issue_attribute";
      const DB_TBL_QUERY_ROLE = "customer_query_issue_role";
      const DB_TBL_QUERY_MESSAGE = "customer_query_message";
      const DB_TBL_QUERY_ATTRIBUTE_VALUE = "customer_query_issue_attribute_value";
      const DB_TBL_QUERY_PRIORITY = "customer_query_priority";
      const DB_TBL_QUERY_PRIORITY_STATUS = "customer_query_priority_status";
      const DB_TBL_QUERY_FILE = "customer_query_file";
      const DB_TBL_QUERY_ISSUE_STATUS_MAPPING = "customer_query_issue_status_mapping";
      const DB_TBL_CUSTOMER_QUERY_HISTORY = "customer_query_history";
      const DB_TBL_CUSTOMER_QUERY_ISSUE_STATUS_MAPPING_WORKFLOW = "customer_query_issue_status_mapping_workflow";
      const DB_TBL_CUSTOMER_REPORT_ATTRIBUTE_FILTER = "customer_attribute_filter";
      const DB_TBL_CUSTOMER_LEDGER_ANALYSIS_ATTRIBUTE_FILTER = "customer_ledger_analysis_attribute_filter";

      /**
       * Email templates
       */
      const DB_TBL_EMAIL_TEMPLATE = "email_template";
      const DB_TBL_EMAIL_TEMPLATE_STATUS = "email_template_status";
      const DB_TBL_EMAIL_TEMPLATE_PLACEHOLDER = "email_template_placeholder";
      const DB_TBL_EMAIL_TEMPLATE_TYPE = "email_template_type";
      const DB_TBL_EMAIL_TEMPLATE_TYPE_PLACEHOLDER_MAPPING = "email_template_type_placeholder_mapping";
      const DB_TBL_EMAIL_TEMPLATE_FILE = "email_template_file";

      /**
       * Frontend Components
       */
      const DB_TBL_COVER_IMAGES = "cover_image";
      const DB_TBL_COVER_IMAGES_STATUS = "cover_image_status";
      const DB_TBL_GRID_IMAGE = "grid_image";
      const DB_TBL_GRID_IMAGE_STATUS = "grid_image_status";
      const DB_TBL_FEATURED_PRODUCTS = "featured_item";

      /*
         * Product Offers And Discounts
         */
      const DB_TBL_OFFER = "offer";
      const DB_TBL_OFFER_STATUS = "offer_status";
      const DB_TBL_OFFER_TYPE = "offer_type";
      const DB_TBL_OFFER_ITEM = "offer_item";

      /**
       * Seller
       */
      const DB_TBL_SELLER = "seller";
      const DB_TBL_SELLER_STATUS = "seller_status";
      const DB_TBL_SELLER_STORE = "seller_store";
      const DB_TBL_SELLER_REQUEST = "seller_request";
      const DB_TBL_SELLER_REQUEST_STATUS = "seller_request_status";
      const DB_TBL_SELLER_CATEGORY = "seller_category";
      const DB_TBL_SELLER_ITEM_ITEM = "seller_item_item";
      const DB_TBL_SELLER_ITEM_ITEM_STORE = "seller_item_item_store";
      const DB_TBL_SELLER_COVERAGE = "seller_coverage";
      const DB_TBL_SELLER_STORE_CONTACT_DETAILS = "seller_store_contact_details";
      const DB_TBL_SELLER_STORE_CONTACT_DETAILS_TYPE = "seller_store_contact_details_type";

      /**
       * Static Pages
       */
      const DB_TBL_STATIC_PAGE = "static_page";
      const DB_TBL_STATIC_PAGE_STATUS = "static_page_status";
      const DB_TBL_STATIC_PAGE_CATEGORY_STATUS = "static_page_category_status";
      const DB_TBL_STATIC_PAGE_CATEGORY = "static_page_category";

      /**
       * Social widgets
       */
      const DB_TBL_SOCIAL_WIDGET_LIST = "social_widget_list";
      const DB_TBL_SOCIAL_WIDGET_DETAILS = "social_widget_details";
      const DB_TBL_SOCIAL_WIDGET_TYPE = "social_widget_type";
      const DB_TBL_SOCIAL_WIDGET_DETAILS_STATUS = "social_widget_details_status";

      /**
       * 
       */
      const DB_TBL_VARIABLE = "variable";
      const DB_TBL_VARIABLE_CATEGORY = "variable_category";
      const DB_TBL_VARIABLE_UPDATE_LOG = "variable_update_log";
      const DB_TBL_VARIABLE_COMPANY_MAPPING = "variable_company_mapping";

      /**
       * Extra
       */
      const DB_TBL_COUNTRY = "country";
      const DB_TBL_STATE = "state";

      /**
       * pincode
       */
      const DB_TBL_PINCODE_SET = "pincode_set";
      const DB_TBL_PINCODE_SET_PINCODE = "pincode_set_pincode";
      const DB_TBL_PINCODE_SET_STATUS = "pincode_set_status";
      const DB_TBL_ITEM_GI_PINCODE_SET = "item_gi_pincode_set";
      const DB_TBL_PINCODE = "pincode";

      /**
       * Customer Payment Options
       */
      const DB_TBL_CUSTOMER_PAYMENT_OPTION = "customer_payment_option";
      const DB_TBL_TRASACTION_CUSTOMER_PAYMENT_OPTION_ATTRIBUTE_VALUE = "transaction_customer_payment_option_attribute_value";
      const DB_TBL_CUSTOMER_PAYMENT_OPTION_ATTRIBUTE = "customer_payment_option_attribute";
      const DB_TBL_CUSTOMER_PAYMENT_OPTION_ATTRIBUTE_TYPE = "customer_payment_option_attribute_type";
      const DB_TBL_CUSTOMER_PAYMENT_OPTION_TYPE = "customer_payment_option_type";
      const DB_TBL_CUSTOMER_PAYMENT_OPTION_DATA = "customer_payment_option_data";
      const DB_TBL_CUSTOMER_PAYMENT_OPTION_STATUS = "customer_payment_option_status";
      const DB_TBL_CUSTOMER_PAYMENT_OPTION_MAPPING = "customer_payment_option_mapping";

      /**
       * Shipping
       */
      const DB_TBL_SHIPPING_COMPANY = "shipping_company";
      const DB_TBL_SHIPPING_COMPANY_STATUS = 'shipping_company_status';
      const DB_TBL_SHIPPING_COMPANY_PACKAGE_SHIPPING_TYPE = "shipping_company_package_shipping_type";

      /**
       * Transaction
       */
      const DB_TBL_TRANSACTION = "transaction";
      const DB_TBL_TRANSACTION_STATUS = "transaction_status";
      const DB_TBL_TRANSACTION_TYPE = "transaction_type";
      const DB_TBL_TRANSACTION_REFERENCE_SETTLEMENT = "transaction_reference_settlement";
      const DB_TBL_TRANSACTION_REFERENCE = "transaction_reference";

      /**
       * Hub
       */
      const DB_TBL_HUB = "hub";
      const DB_TBL_HUB_TYPE = "hub_type";
      const DB_TBL_HUB_STATUS = "hub_status";
      const DB_TBL_HUB_ORDER = "hub_item_order";
      const DB_TBL_HUB_ORDER_STATUS = "hub_item_order_status";
      const DB_TBL_HUB_ORDER_ITEM = "hub_item_order_item";
      const DB_TBL_HUB_ORDER_SHIPPING = "hub_item_order_internal_shipping";
      const DB_TBL_HUB_ORDER_PROCESSING = "hub_order_processing";
      const DB_TBL_HUB_TRANSACTION = "hub_transaction";
      const DB_TBL_HUB_CASH_SHIPPING = "hub_cash_shipping";
      const DB_TBL_HUB_SERVICE_TYPE = "hub_service_type";
      const DB_TBL_HUB_SERVICE_TYPE_STATUS = "hub_service_type_status";
      const DB_TBL_HUB_ITEM_ORDER_LOGS = "hub_item_order_logs";
      const DB_TBL_HUB_CASH_NOTES = "hub_cash_notes";
      const DB_TBL_HUB_CASH_SHIPPING_INVOICE = "hub_cash_shipping_invoice";
      const DB_TBL_HUB_CASH_SHIPPING_NOTES = "hub_cash_shipping_notes";
      const DB_TBL_HUB_CASH_SHIPPING_EXTRA = "hub_cash_shipping_extra";
      const DB_TBL_HUB_COVERAGE_LOCALITY = "hub_coverage_locality";
      const DB_TBL_HUB_ITEM_ORDER_REQUEST = "hub_item_order_request";
      const DB_TBL_HUB_ITEM_ORDER_ITEM_REQUEST = "hub_item_order_item_request";
      const DB_TBL_HUB_ITEM_ORDER_REQUEST_STATUS = "hub_item_order_request_status";

      /*
         *       Custom Design   
         */
      const DB_TBL_CUSTOM_BASE_FONT = "custom_base_font";
      const DB_TBL_CUSTOM_BASE_COLOR = "custom_base_color";
      const DB_TBL_CLIPART_CATEGORY = "custom_clipart_category";
      const DB_TBL_CLIPART_IMAGE = "custom_clipart_image";
      const DB_TBL_CUSTOM_DESIGN = "custom_base_design";
      const DB_TBL_CUSTOM_DESIGN_COLOR = "custom_base_design_color";
      const DB_TBL_CUSTOM_DESIGN_PRINT_COLOR = "custom_base_design_print_color";
      const DB_TBL_CUSTOM_DESIGN_VIEW = "custom_base_design_view";
      const DB_TBL_CUSTOM_BASE_DESIGN = "custom_base_design";
      const DB_TBL_CUSTOM_BASE_DESIGN_COLOR = "custom_base_design_color";

      /*
         *    Wallet       
         */
      const DB_TBL_WALLET = "wallet_transaction";
      const DB_TBL_WALLET_TRANSACTION_TYPE = "wallet_transaction_type";

      /**
       * DND Service
       */
      const DB_TBL_DND = "dnd_service";
      const DB_TBL_DND_EMAIL = "dnd_service_email";

      /**
       * Modules
       */
      const DB_TBL_MODULE = "rapidkart_factory_static.module";
      const DB_TBL_MODULE_STATUS = "rapidkart_factory_static.module_status";
      const DB_TBL_FANCY_IMAGES = "fancy_images";
      const DB_TBL_FANCY_IMAGES_RESOLUTION = "fancy_images_resolutions";
      const DB_TBL_FANCY_IMAGES_STATUS = "fancy_images_status";

      /**
       * Modules Task
       */
      const DB_TBL_MODULE_TASK = "rapidkart_factory_static.module_task";
      const DB_TBL_MODULE_TASK_VIEW = "module_task_view";
      const DB_TBL_MODULE_TASK_USER_MAPPING = "module_task_user_mapping";

      /**
       * Activity
       */
      const DB_TBL_AUSER_ACTIVITY = "auser_activity";
      const DB_TBL_AUSER_ACTIVITY_LISTENER = "auser_activity_listener";
      const DB_TBL_AUSER_ACTIVITY_NOTIFY = "auser_activity_notify";
      const DB_TBL_AUSER_ACTIVITY_VISIT = "auser_activity_visit";
      const DB_TBL_AUSER_ACTIVITY_COMMENT = "auser_activity_comment";

      /**
       * Expense claim
       */
      const DB_TBL_AUSER_EXPENSE_CLAIM = "auser_expense_claim";
      const DB_TBL_AUSER_EXPENSE_CLAIM_STATUS = "auser_expense_claim_status";
      const DB_TBL_AUSER_EXPENSE_CLAIM_TYPE = "auser_expense_claim_type";
      const DB_TBL_AUSER_CLAIM_EXPENSE_TRANSACTION_VIEW = "auser_claim_expense_transaction_view";
      const DB_TBL_AUSER_CLAIM_EXPENSE_VIEW = "auser_claim_expense_view";
      const DB_TBL_AUSER_CLAIM_EXPENSE_TYPE_EXTRA_CHARGE_MAPPING = "auser_expense_claim_type_extra_charge_mapping";
      const DB_TBL_AUSER_CLAIM_EXPENSE_TYPE_CUSTOM_ATTRIBUTE_MAPPING = "auser_expense_claim_type_custom_attribute_mapping";
      const DB_TBL_AUSER_CLAIM_EXPENSE_EXTRA_CHARGE_MAPPING = "auser_expense_claim_extra_charge_mapping";
      const DB_TBL_AUSER_CLAIM_FILE = "auser_expense_claim_file";
      const DB_TBL_AUSER_ITEMWISE_EXPENSE_CLAIM_FILE = "auser_itemwise_expense_claim_file";

      /**
       * Reporting Module
       */
      const DB_TBL_REPORT = "report";
      const DB_TBL_REPORT_SCHEDULE = "report_schedule";
      const DB_TBL_REPORT_LOG = "report_log";
      const DB_TBL_REPORT_FREQUENCY = "report_frequency";
      const DB_TBL_REPORT_ROLES = "report_roles";
      const DB_TBL_REPORT_STATUS = "report_status";
      const DB_TBL_REPORT_USER = "report_user";

      /**
       * Promo Codes
       */
      const DB_TBL_PROMO_CODE = "promo_code";
      const DB_TBL_PROMO_CODE_TYPE = "promo_code_type";
      const DB_TBL_PROMO_CODE_STATUS = "promo_code_status";
      const DB_TBL_PROMOCODE_DISCOUNT = "promo_code_discount_type";

      /*
         * Checkpoint
         */
      const DB_TBL_CHECKPOINT = "checkpoint";
      const DB_TBL_CHECKPOINT_STATUS = "checkpoint_status";
      const DB_TBL_CHECKPOINT_TYPE = "checkpoint_type";
      const DB_TBL_CHECKPOINT_NETWORK = "checkpoint_network";
      const DB_TBL_CHECKPOINT_FACTORY = "checkpoint_factory";
      const DB_TBL_CHECKPOINT_PHOTOS = "checkpoint_photos";
      const DB_TBL_CHECKPOINT_ORDER = "checkpoint_order";
      const DB_TBL_CHECKPOINT_ORDER_LOCK_LOG = "checkpoint_order_lock_log";
      const DB_TBL_CHECKPOINT_ORDER_ITEM = "checkpoint_order_item";
      const DB_TBL_CHECKPOINT_STAGE_TASK = "checkpoint_stage_task";
      const DB_TBL_CHECKPOINT_TASK = "checkpoint_task";
      const DB_TBL_CHECKPOINT_TASK_STATUS = "checkpoint_task_status";
      const DB_TBL_CHECKPOINT_STAGE = "checkpoint_stage";
      const DB_TBL_CHECKPOINT_STAGE_STATUS = "checkpoint_stage_status";
      const DB_TBL_CHECKPOINT_POINT_OF_SALE = "checkpoint_pos";
      const DB_TBL_CHECKPOINT_ORDER_ITEM_STAGE_TASK = "checkpoint_order_item_stage_task";
      const DB_TBL_CHECKPOINT_ORDER_TRANSACTION = "checkpoint_order_transaction";
      const DB_TBL_CHECKPOINT_ORDER_CREDIT_NOTE = "checkpoint_order_credit_note";
      const DB_TBL_CHECKPOINT_ORDER_CREDIT_NOTE_EDIT_HISTORY = "checkpoint_order_credit_note_edit_history";
      const DB_TBL_CHECKPOINT_ORDER_CREDIT_NOTE_EWAY = "checkpoint_order_credit_note_eway";
      const DB_TBL_REPAIRREQUEST = "repair_service";
      const DB_TBL_REPAIRREQUESTITEM = "repair_service_items";
      const DB_TBL_CHECKPOINT_ORDER_CREDIT_NOTE_ITEM = "checkpoint_order_credit_note_item";
      const DB_TBL_CHECKPOINT_ORDER_ITEM_CHECKPOINT = "checkpoint_order_item_checkpoint";
      const DB_TBL_CHECKPOINT_ORDER_ITEM_EXECUTION = "checkpoint_order_item_execution";
      const DB_TBL_CHECKPOINT_ORDER_STATUS = "checkpoint_order_status";
      const DB_TBL_CHECKPOINT_ORDER_BOOKING = "checkpoint_order_booking";
      const DB_TBL_CHECKPOINT_ORDER_BOOKING_STATUS = "checkpoint_order_booking_status";
      const DB_TBL_CHECKPOINT_TYPE_ATTRIBUTE = "checkpoint_type_attribute";
      const DB_TBL_CHECKPOINT_ATTRIBUTE_VALUE = "checkpoint_attribute_value";
      const DB_TBL_CHECKPOINT_USER = "checkpoint_user_mapping";
      const DB_TBL_CHECKPOINT_ORDER_ITEM_ASSIGNMENT = "checkpoint_order_item_assignment";
      const DB_TBL_CHECKPOINT_ORDER_EXTRA_CHARGES = "checkpoint_order_extra_charges";
      const DB_TBL_CHECKPOINT_ORDER_ITEM_EXTRA_CHARGES = "checkpoint_order_item_extra_charges";
      const DB_TBL_CHECKPOINT_ORDER_BUSINESS_TAX_PROFILE_MAPPING = "checkpoint_order_business_tax_profile_mapping";
      const DB_TBL_CHECKPOINT_ORDER_FILE = "checkpoint_order_file";
      const DB_TBL_CHECKPOINT_ORDER_CREDIT_NOTE_BUSINESS_TAX_PROFILE_MAPPING = "checkpoint_order_credit_note_business_tax_profile_mapping";
      const DB_TBL_CHECKPOINT_ORDER_CREDIT_NOTE_EXTRA_CHARGES = "checkpoint_order_credit_note_extra_charges";
      const DB_TBL_CHECKPOINT_ORDER_CREDIT_NOTE_ITEM_EXTRA_CHARGES = "checkpoint_order_credit_note_item_extra_charges";
      const DB_TBL_CHECKPOINT_ORDER_CREDIT_NOTE_ITEM_EXTRA_DISCOUNT = "checkpoint_order_credit_note_item_extra_discount";
      const DB_TBL_CHECKPOINT_ORDER_ITEM_INDENT_RELEASE_LOG = "checkpoint_order_item_indent_release_log";
      const DB_TBL_CHECKPOINT_ORDER_EXTRA_DISCOUNT = "checkpoint_order_extra_discount";
      const DB_TBL_CHECKPOINT_ORDER_ITEM_EXTRA_DISCOUNT = "checkpoint_order_item_extra_discount";
      const DB_TBL_CHECKPOINT_ORDER_CREDIT_NOTE_EXTRA_DISCOUNT = "checkpoint_order_credit_note_extra_discount";
      const DB_TBL_CHECKPOINT_ORDER_DELIVERY_SCHEDULE = "checkpoint_order_delivery_schedule";
      const DB_TBL_CHECKPOINT_ORDER_DELIVERY_SCHEDULE_DEPENDENCY = "checkpoint_order_delivery_schedule_dependency";
      const DB_TBL_CHECKPOINT_ORDER_EDIT_HISTORY_LOG = "checkpoint_order_edit_history_log";
      const DB_TBL_CHECKPOINT_ORDER_EDIT_LOG = "checkpoint_order_edit_log";
      const DB_TBL_CHECKPOINT_ORDER_CREDIT_NOTE_FILE = "checkpoint_order_credit_note_file";
      const DB_TBL_CHECKPOINT_ORDER_SERVICE_STATUS = "checkpoint_order_service_status";
      const DB_TBL_CHECKPOINT_ORDER_CATEGORY_TYPE = "checkpoint_order_category_type";
      const DB_TBL_CHECKPOINT_ORDER_MERGE_LOG = 'checkpoint_order_merge_log';
      const DB_TBL_CHECKPOINT_ORDER_CUSTOM_OPTION_MAPPING = 'checkpoint_order_custom_option_mapping';
      const DB_TBL_CHECKPOINT_ORDER_PICKLIST = "checkpoint_order_picklist";
      const DB_TBL_CHECKPOINT_ORDER_PICKLIST_ITEMS = "checkpoint_order_picklist_items";
      const DB_TBL_CHECKPOINT_ORDER_ATTENDEE_MAPPING = "checkpoint_order_attendee_mapping";
      const DB_TBL_CHECKPOINT_ORDER_ITEM_BRAND_APPROVE_MAPPING = "checkpoint_order_item_brand";
      const DB_TBL_CHECKPOINT_ORDER_ATTRIBUTE_VALUE = "checkpoint_order_attribute_value";

      /**
       * SMS Logs
       */
      const DB_TBL_SMS_LOG = "sms_log";

      /**
       * Newsletter
       */
      const DB_TBL_NEWSLETTER = "newsletter";
      const DB_TBL_NEWSLETTER_CONTENT = "newsletter_content";
      const DB_TBL_NEWSLETTER_LOG = "newsletter_log";
      const DB_TBL_NEWSLETTER_CONTENT_STATUS = "newsletter_content_status";

      /**
       * Coverage, locality and region
       */
      const DB_TBL_COVERAGE = "coverage";
      const DB_TBL_COVERAGE_LOCALITY = "coverage_locality";
      const DB_TBL_COVERAGE_LOCALITY_PINCODE_SET = "coverage_locality_pincode_set";
      const DB_TBL_COVERAGE_LOCALITY_SHIPPING_ADDRESS = "coverage_locality_shipping_address";
      const DB_TBL_COVERAGE_LOCALITY_STATUS = "coverage_locality_status";
      const DB_TBL_COVERAGE_STATUS = "coverage_status";
      const DB_TBL_REGION = "region";
      const DB_TBL_REGION_STATUS = "region_status";
      const DB_TBL_REGION_VIEW = "region_view";
      const DB_TBL_COVERAGE_LOCALITY_PRICE = 'coverage_locality_price';

      /**
       * testimonial
       */
      const DB_TBL_TESTIMONIAL = "testimonial";
      const DB_TBL_TESTIMONIAL_STATUS = "testimonial_status";
      const DB_TBL_TESTIMONIAL_PHOTO = "testimonial_photo";

      /* Raw Material */
      const DB_TBL_RAW_MATERIAL = "raw_material";
      const DB_TBL_RAW_MATERIAL_STATUS = "raw_material_status";
      const DB_TBL_RAW_MATERIAL_CATEGORY = "raw_material_category";

      /* Hub service type slot */
      const DB_TBL_HUB_SERVICE_TYPE_SLOT = "hub_service_type_slot";
      const DB_TBL_HUB_SERVICE_TYPE_SLOT_STATUS = "hub_service_type_slot_status";

      /* Showcase */
      const DB_TBL_SHOWCASE = "showcase";
      const DB_TBL_SHOWCASE_PHOTOS = "showcase_photos";
      const DB_TBL_SHOWCASE_STATUS = "showcase_status";
      const DB_TBL_SHOWCASE_CATEGORIES = "showcase_categories";
      const DB_TBL_RAW_MATERIALS_PHOTOS = "raw_material_photos";

      /**
       * vendor
       */
      const DB_TBL_VENDOR = "vendor";
      const DB_TBL_VENDOR_LOGIN = "vendor_login";
      const DB_TBL_VENDOR_MERGE_LOG = "vendor_merge_log";
      const DB_TBL_VENDOR_STATUS = "vendor_status";
      const DB_TBL_VENDOR_TRANSACTION = "vendor_transaction";
      const DB_TBL_VENDOR_TRANSPORTATION = "vendor_transportation";
      const DB_TBL_VENDOR_REJECTED_ITEMS_VIEW = "vendorwise_rejected_items_view";
      const DB_TBL_VENDOR_REPORT_FILTER = "vendor_report_filter";
      const DB_TBL_VENDOR_PERMISSION_MAPPING = "vendor_permission_mapping";
      const DB_TBL_VENDOR_FORGOT_PASSWORD = "vendor_forgot_password";
      const DB_TBL_VENDOR_EDIT_LOG = "vendor_edit_log";
      const DB_TBL_VENDOR_BRAND_MAPPING = "vendor_brand_mapping";
      const DB_TBL_VENDOR_CATEGORY_MAPPING = "vendor_category_mapping";
      const DB_TBL_VENDOR_CATEGORY_MARKUP = "vendor_category_markup";
      const DB_TBL_VENDOR_FILE = "vendor_file";
      const DB_TBL_VENDOR_PRICE_PROFILING = "vendor_price_profiling";
      const DB_TBL_VENDOR_SCHEME = "vendor_scheme";
      const DB_TBL_VENDOR_CATEGORY_DISCOUNT_LOG = "vendor_category_discount_log";

      /**
       * sms provider
       */
      const DB_TBL_SMS_PROVIDER = "sms_provider";
      const DB_TBL_SMS_PROVIDER_SENDER = "sms_provider_sender";
      const DB_TBL_SMS_PROVIDER_SENDER_STATUS = "sms_provider_sender_status";
      const DB_TBL_SMS_PROVIDER_STATUS = "sms_provider_status";
      const DB_TBL_SMS_PROVIDER_SENDER_TYPE = "sms_provider_sender_type";

      /**
       * call log
       */
      const DB_TBL_CALL_LOG = "call_log";

      /* order shipping address */
      const DB_TBL_ORDER_SHIPPING_ADDRESS = "item_order_shipping_address";

      /**
       * enquiry template
       */
      const DB_TBL_ENQUIRY_TEMPLATE = "enquiry_template";
      const DB_TBL_ENQUIRY_TEMPLATE_ATTRIBUTE = "enquiry_template_attribute";
      const DB_TBL_ENQUIRY_TEMPLATE_ATTRIBUTE_STATUS = "enquiry_template_attribute_status";
      const DB_TBL_ENQUIRY_TEMPLATE_STATUS = "enquiry_template_status";
      const DB_TBL_ENQUIRY_TEMPLATE_PRODUCT_ATTRIBUTE = "enquiry_template_product_attribute";
      const DB_TBL_ENQUIRY_TEMPLATE_PRODUCT_ATTRIBUTE_STATUS = "enquiry_template_product_attribute_status";
      const DB_TBL_ENQUIRY = "enquiry";
      const DB_TBL_ENQUIRY_ATTRIBUTE_VALUE = "enquiry_attribute_value";
      const DB_TBL_ENQUIRY_PRODUCT = "enquiry_product";
      const DB_TBL_ENQUIRY_PRODUCT_ATTRIBUTE_VALUE = "enquiry_product_attribute_value";
      const DB_TBL_ENQUIRY_STATUS = "enquiry_status";
      const DB_TBL_ENQUIRY_TEMPLATE_ATTRIBUTE_TYPE = "enquiry_template_attribute_type";
      const DB_TBL_ENQUIRY_FILE = "enquiry_file";
      const DB_TBL_ENQUIRY_TEMPLATE_ATTRIBUTE_VALUE = "enquiry_template_attribute_value";
      const DB_TBL_ENQUIRY_FEEDBACK_SCHEDULE = "enquiry_feedback_schedule";
      const DB_TBL_ENQUIRY_FEEDBACK_SCHEDULE_STATUS = "enquiry_feedback_schedule_status";
      const DB_TBL_ENQUIRY_FEEDBACK_SCHEDULE_TYPE = "enquiry_feedback_schedule_type";
      const DB_TBL_ENQUIRY_EDIT_HISTORY = "enquiry_edit_history";
      const DB_TBL_ENQUIRY_TEMPLATE_PRODUCT_ATTRIBUTE_VALUE = "enquiry_template_product_attribute_value";
      const DB_TBL_NOAH_ENQUIRY_STATUS = "noah_enquiry_status";
      const DB_TBL_ENQUIRY_PRODUCT_GROUP = "enquiry_product_group";
      const DB_TBL_ENQUIRY_CATEGORY_TYPE = "enquiry_category_type";

      /**
       * Lead related tables
       */
      const DB_TBL_LEAD = "lead";
      const DB_TBL_LEAD_COMMUNICATION_MODE = "lead_communication_mode";
      const DB_TBL_LEAD_STATUS = "lead_status";
      const DB_TBL_LEAD_TYPE = "lead_type";
      const DB_TBL_LEAD_ATTRIBUTES = "lead_type_attributes";
      const DB_TBL_LEAD_PHOTOS = 'lead_photos';
      const DB_TBL_LEAD_FOLLOWUP_CANCEL_REASON = 'lead_followup_cancel_reason';

      /**
       * Lead related views
       */
      const DB_TBL_LEAD_VIEW = "new_lead_view";

      /**
       * Industry type
       */
      const DB_TBL_INDUSTRY_TYPE = "industry_type";
      const DB_TBL_INDUSTRY_TYPE_STATUS = "industry_type_status";
      const DB_TBL_INDUSTRY_TYPE_VIEW = "industry_type_view";

      /**
       * Followup tables
       */
      const DB_TBL_CONTACT_DETAIL = "contact_detail";
      const DB_TBL_FOLLOWUP = "followup";
      const DB_TBL_FOLLOWUP_OUTCOME = "followup_outcome";
      const DB_TBL_FOLLOWUP_STATUS = "followup_status";
      const DB_TBL_FOLLOWUP_ACTIVITY_TYPE = "followup_activity_type";
      const DB_TBL_FOLLOWUP_ACTIVITY_TYPE_ATTRIBUTES = "followup_activity_type_attributes";
      const DB_TBL_FOLLOWUP_ATTACHMENTS = "followup_attachments";
      const DB_TBL_FOLLOWUP_TASK_TYPE = "followup_task_type";

      /**
       * Lead related views
       */
      const DB_TBL_FOLLOWUP_VIEW = "followup_view";
      const DB_TBL_DMR_VIEW = "dmr_view";

      /**
       * gender
       */
      const DB_TBL_GENDER = "gender";

      /**
       * purchase intend
       */
      const DB_TBL_PURCHASE_INTEND = "purchase_intend";
      const DB_TBL_PURCHASE_INTEND_STATUS = "purchase_intend_status";
      const DB_TBL_PURCHASE_INTEND_ORDER = "purchase_intend_order";
      const DB_TBL_PURCHASE_INTEND_LOG = "purchase_intend_log";

      /**
       * Bidding
       */
      const DB_TBL_BIDDING = "bidding";
      const DB_TBL_BIDDING_PRICE = "bidding_price";
      const DB_TBL_BIDDING_PURCHASE_INDENT_MAPPING = "bidding_purchase_intend_mapping";

      /**
       * purchase order
       */
      const DB_TBL_PURCHASE_ORDER = "purchase_order";
      const DB_TBL_PURCHASE_ORDER_SUB_STATUS = "purchase_order_sub_status";
      const DB_TBL_PURCHASE_ORDER_STATUS = "purchase_order_status";
      const DB_TBL_PURCHASE_ORDER_ITEM = "purchase_order_item";
      const DB_TBL_PURCHASE_ORDER_FILE = "purchase_order_file";
      const DB_TBL_PURCHASE_ORDER_DEBIT_NOTE = "purchase_order_debit_note";
      const DB_TBL_PURCHASE_ORDER_DEBIT_NOTE_ITEM = "purchase_order_debit_note_item";
      const DB_TBL_PURCHASE_ORDER_EXTRA_CHARGES = "purchase_order_extra_charges";
      const DB_TBL_PURCHASE_ORDER_BUSINESS_TAX_PROFILE_MAPPING = "purchase_order_business_tax_profile_mapping";
      const DB_TBL_PURCHASE_ORDER_EDIT_HISTORY = "purchase_order_edit_history";
      const DB_TBL_PURCHASE_ORDER_DEBIT_NOTE_BUSINESS_TAX_PROFILE_MAPPING = "purchase_order_debit_note_business_tax_profile_mapping";
      const DB_TBL_PURCHASE_ORDER_DEBIT_NOTE_EXTRA_CHARGES = "purchase_order_debit_note_extra_charges";
      const DB_TBL_PURCHASE_ORDER_DEBIT_NOTE_STATUS = "purchase_order_debit_note_status";
      const DB_TBL_PURCHASE_ORDER_DEBIT_NOTE_EWAY = "purchase_order_debit_note_eway";
      const DB_TBL_PURCHASE_ORDER_REPORT_ATTRIBUTE_FILTER = "purchase_order_report_attribute_filter";
      const DB_TBL_PURCHASE_ORDER_DEBIT_NOTE_EDIT_HISTORY = "purchase_order_debit_note_edit_history";
      //Purchase order transportation 
      const DB_TBL_PURCHASE_ORDER_TRANSPORTATION_DETAILS = "purchase_order_transportation_details";
      const DB_TBL_PURCHASE_ORDER_TRANSPORTATION_EXTRA_CHARGE_DETAILS = "purchase_order_transportation_extra_charges_details";
      const DB_TBL_PURCHASE_ORDER_REPORT_TABLE_VIEW = "purchase_order_report_table_view";
      const DB_TBL_PURCHASE_ORDER_EXTRA_DISCOUNT = "purchase_order_extra_discount";
      const DB_TBL_PURCHASE_ORDER_DEBIT_NOTE_EXTRA_DISCOUNT = "purchase_order_debit_note_extra_discount";
      const DB_TBL_PO_FILE = "po_file";
      const DB_TBL_PURCHASE_ORDER_DEBIT_NOTE_ITEM_INVENTORY = 'purchase_order_debit_note_item_inventory';
      //Purchase Order Forcasting
      const DB_TBL_PURCHASE_ORDER_FORECASTING_REPORT_FILTER = "purchase_forecasting_report_filter";

      /**
       * distributor
       */
      const DB_TBL_DISTRIBUTOR = "distributor";
      const DB_TBL_DISTRIBUTOR_STATUS = "distributor_status";
      const DB_TBL_DISTRIBUTOR_TRANSACTION = "distributor_transaction";
      const DB_TBL_DISTRIBUTOR_INVENTORY = "distributor_inventory";

      /**
       * import company
       */
      const DB_TBL_IMPORT_COMPANY = "import_company";
      const DB_TBL_IMPORT_COMPANY_STATUS = "import_company_status";

      /**
       * Package
       */
      const DB_TBL_PACKAGE = "package";
      const DB_TBL_PACKAGE_ATTRIBUTE = "package_attribute";
      const DB_TBL_PACKAGE_EXECUTION = "package_item_order_item_execution";
      const DB_TBL_PACKAGE_SHIPPING = "package_shipping";
      const DB_TBL_PACKAGE_SHIPPING_STATUS = "package_shipping_status";
      const DB_TBL_PACKAGE_SHIPPING_LOG = "package_shipping_log";
      const DB_TBL_PACKAGE_SHIPPING_TYPE = "package_shipping_type";
      const DB_TBL_PACKAGE_DELIVERY_GATEPASS = "package_delivery_gatepass";
      const DB_TBL_PACKAGE_DELIVERY_GATEPASS_STATUS = "package_delivery_gatepass_status";
      const DB_TBL_PACKAGE_DELIVERY_GATEPASS_PACKAGE = "package_delivery_gatepass_package";
      const DB_TBL_PACKAGE_DELIVERY_GATEPASS_PACKAGE_TRANSPORT = "package_delivery_gatepass_package_transport";
      const DB_TBL_PACKAGE_DELIVERY_GATEPASS_PACKAGE_STATUS = "package_delivery_gatepass_package_status";
      const DB_TBL_PACKAGE_CANCEL_LOG = "package_cancel_log";
      const DB_TBL_PACKAGE_FILES = "package_files";
      const DB_TBL_CREDIT_REQUEST_APPROVE_PACKAGE = "credit_request_approve_package";
      const DB_TBL_PACKAGE_EWAY = 'package_eway';

      /**
       * promotional_email
       * 
       */
      const DB_TBL_PROMOTIONAL_EMAIL = "promotional_email";
      const DB_TBL_PROMOTIONAL_EMAIL_CUSTOMER = "promotional_email_customer";

      /**
       * measurement
       * 
       */
      const DB_TBL_MEASUREMENT = "measurement";
      const DB_TBL_MEASUREMENT_STATUS = "measurement_status";
      const DB_TBL_MEASUREMENT_CONVERSION = "measurement_conversion";
      const DB_TBL_MEASUREMENT_TYPE = "measurement_type";
      const DB_TBL_MEASUREMENT_FORMULAE = "measurement_formulae";
      const DB_TBL_MEASUREMENT_FORMULAE_VARIABLE = "measurement_formulae_variable";

      /**
       * system Group
       * 
       */
      const DB_TBL_SYSTEM_GROUP = "system_group";
      const DB_TBL_SYSTEM_GROUP_STATUS = "system_group_status";
      const DB_TBL_ITEM_ITEM_SYSTEM_GROUP = "item_item_system_group";
      const DB_TBL_AUSER_SYSTEM_GROUP = "auser_system_group";

      /**
       * Email Log
       */
      const DB_TBL_EMAIL_LOG = "email_log";
      const DB_TBL_EMAIL_LOG_ATTACHMENT = "email_log_attachment";

      /**
       * Warehouse types
       */
      const DB_TBL_WAREHOUSE = "warehouse";
      const DB_TBL_WAREHOUSE_INVENTORY_CONSUMPTION = "warehouse_inventory_consumption";
      const DB_TBL_WAREHOUSE_INVENTORY_CONSUMPTION_ITEMS = "warehouse_inventory_consumption_items";
      const DB_TBL_WAREHOUSE_INVENTORY_CONSUMPTION_ITEM_WITHOUT_INVENTORY = "warehouse_inventory_consumption_item_without_inventory";
      const DB_TBL_WAREHOUSE_INVENTORY_RECEIVE = "warehouse_inventory_receive";
      const DB_TBL_WAREHOUSE_INVENTORY_RECEIVE_ITEMS = "warehouse_inventory_receive_items";
      const DB_TBL_WAREHOUSE_USER_MAPPING = "warehouse_user_mapping";
      const DB_TBL_CHECKPOINT_MAPPING = "checkpoint_mapping";
      const DB_TBL_WAREHOUSE_INVENTORY_STOCK_VIEW = "warehouse_inventory_stock_view";
      const DB_TBL_WAREHOUSE_INVENTORY_REQUEST_COUNT_VIEW = "warehouse_inventory_request_count_view";
      const DB_TBL_WAREHOUSE_INVENTORY_ALLOTMENT_COUNT_VIEW = "warehouse_inventory_allotment_count_view";
      const DB_TBL_WAREHOUSE_INVENTORY_SET_ORDER_DETAILS_VIEW = "inventory_set_variation_order_details_view";
      const DB_TBL_INVENTORY_SET_CONTAIN_TRANSPORTATION_DETAILS = "inventory_set_container_transportation_details";
      const DB_TBL_WAREHOUSE_MINIMUM_INVENTORY = "warehouse_minimum_inventory";
      const DB_TBL_WAREHOUSE_SECTION = "warehouse_section";
      const DB_TBL_WAREHOUSE_INVENTORY_DAMAGE = 'warehouse_inventory_damage';
      const DB_TBL_WAREHOUSE_INVENTORY_DAMAGE_ITEMS = 'warehouse_inventory_damage_items';
      const DB_TBL_WAREHOUSE_SECTION_USER_MAPPING = 'warehouse_section_user_mapping';

      /**
       * Stock issue and receive views
       */
      const DB_TBL_STOCK_CONSUMPTION_HISTORY = "stock_consumption_history";
      const DB_TBL_STOCK_RECEIVE_HISTORY = "stock_receive_history";
      const DB_TBL_AAD_STOCK_ATTRIBITE_FILTER = "inventory_add_stock_attribite_filter";
      const DB_TBL_WAREHOUSE_INVENTORY_CONSUMPTION_ATTACHMENTS = "warehouse_inventory_consumption_attachments";
      const DB_TBL_WAREHOUSE_INVENTORY_RECEIVE_ATTACHMENTS = "warehouse_inventory_receive_attachments";

      /**
       *  Extra Charges
       */
      const DB_TBL_EXTRA_CHARGES = "extra_charges";
      const DB_TBL_EXTRA_CHARGES_STATUS = "extra_charges_status";
      const DB_TBL_EXTRA_CHARGES_TYPE = "extra_charges_type";
      const DB_TBL_EXTRA_CHARGES_AMOUNT_TYPE = "extra_charges_amount_type";
      const DB_TBL_EXTRA_CHARGE_TAX_PROFILE = "extra_charge_tax_profiles";

      /**
       *  Business Tax Profile
       */
      const DB_TBL_BUSINESS_TAX_PROFILE = "business_tax_profile";
      const DB_TBL_BUSINESS_TAX_PROFILE_STATUS = "business_tax_profile_status";
      const DB_TBL_BUSINESS_TAX_PROFILE_TYPE = "business_tax_profile_type";
      const DB_TBL_BUSINESS_TAX_PROFILE_AMOUNT_TYPE = "business_tax_profile_amount_type";
      const DB_TBL_BUSINESS_TAX_PROFILE_APPLICATION_STATIC = 'business_tax_profile_application_static';
      const DB_TBL_GLOBAL_BUSINESS_TAX_PROFILE = "global_business_tax_profile";
      const DB_TBL_GLOBAL_BUSINESS_SUB_PROFILE = "global_business_sub_profile";
      const DB_TBL_GLOBAL_BUSINESS_SUB_PROFILE_DOCUMENT = "global_business_sub_profile_document";
      const DB_TBL_GLOBAL_BUSINESS_SUB_PROFILE_TYPE = "global_business_sub_profile_type";
      const DB_TBL_GLOBAL_TAX_PROFILE_PAYMENT = "global_tax_profile_payment";
      const DB_TBL_BUSINESS_TAX_PROFILE_ROUND = "business_tax_profile_round";
      const DB_TBL_GST_MAPPING = "gst_mapping";
      const DB_TBL_GLOBAL_BUSINESS_SUB_SUB_PROFILE = "global_business_sub_sub_profile";
      const DB_TBL_GLOBAL_BUSINESS_SUB_SUB_PROFILE_MAPPING = "global_business_sub_sub_profile_mapping";
      const DB_TBL_BUSINESS_TAX_SUB_PROFILE = "business_tax_sub_profile";
      const DB_TBL_GLOBAL_BUSINESS_TAX_PROFILE_COMPANY_MAPPING = "global_business_tax_profile_company_mapping";
      const DB_TBL_GLOBAL_BUSINESS_TAX_PROFILE_COMPANY_GSTIN_DETAILS = "global_business_tax_profile_company_gstin_details";
      const DB_TBL_BUSINESS_CESS_PROFILE = "business_cess_profile";

      /**
       * SMS Template
       */
      const DB_TBL_SMS_TEMPLATE = "sms_template";
      const DB_TBL_SMS_TEMPLATE_STATUS = "sms_template_status";
      const DB_TBL_SMS_TEMPLATE_PLACEHOLDER = "sms_template_placeholder";
      const DB_TBL_SMS_TEMPLATE_TYPE = "sms_template_type";
      const DB_TBL_SMS_TEMPLATE_TYPE_PLACEHOLDER_MAPPING = "sms_template_type_placeholder_mapping";

      /*
         * Factory Module
         */
      const DB_TBL_INVENTORY_SET_VARIATIONS_BOM = "inventory_set_variations_bom";
      const DB_TBL_INVENTORY_SET_VARIATIONS_BOM_VIEW = "inventory_set_variation_bom_view";
      const DB_TBL_INVENTORY_SET_VARIATIONS_BOM_ITEM = "inventory_set_variations_bom_item";
      const DB_TBL_INVENTORY_SET_VARIATIONS_BOM_ITEM_VIEW = "inventory_set_variations_bom_item_view";
      const DB_TBL_INVENTORY_SET_VARIATIONS_BOM_STATUS = "inventory_set_variations_bom_status";
      const DB_TBL_FACTORY = "factory";
      const DB_TBL_FACTORY_STAGE = "factory_stage";
      const DB_TBL_FACTORY_STAGE_ATTRIBUTE = "factory_stage_attribute";
      const DB_TBL_FACTORY_STAGE_INVENTORY_SET = "factory_stage_inventory_set";
      const DB_TBL_FACTORY_STAGE_INVENTORY_SET_VIEW = "factory_stage_inventory_set_view";
      const DB_TBL_FACTORY_STAGE_TASK = "factory_stage_task";
      const DB_TBL_FACTORY_STAGE_TASK_VIEW = "factory_stage_task_view";
      const DB_TBL_FACTORY_STAGE_QUALITY_CHECK = "factory_stage_quality_check";
      const DB_TBL_FACTORY_STAGE_QUALITY_CHECK_VIEW = "factory_stage_quality_check_view";
      const DB_TBL_FACTORY_STAGE_TASK_CATEGORY = "factory_stage_task_category";
      const DB_TBL_FACTORY_STAGE_QUALITY_CHECK_CATEGORY = "factory_stage_quality_check_category";
      const DB_TBL_FACTORY_STAGE_TASK_STATUS = "factory_stage_task_status";
      const DB_TBL_FACTORY_STAGE_QUALITY_CHECK_STATUS = "factory_stage_quality_check_status";
      const DB_TBL_FACTORY_STAGE_USER = "factory_stage_user";
      const DB_TBL_FACTORY_STAGE_QUALITY_CHECK_USER = "factory_stage_quality_check_user";
      const DB_TBL_FACTORY_STAGE_DELIVERY_USER = "factory_stage_delivery_user";
      const DB_TBL_FACTORY_STAGE_STATUS = "factory_stage_status";
      const DB_TBL_FACTORY_USER = "factory_user";
      const DB_TBL_FACTORY_CONSIGNMENT_DELIVERY = "factory_consignment_delivery";
      const DB_TBL_FACTORY_CONSIGNMENT_DELIVERY_VIEW = "factory_consignment_delivery_view";
      const DB_TBL_INVENTORY_SET_VARIATIONS_PRODUCTION_PROCESS = "inventory_set_variations_production_process";
      const DB_TBL_INVENTORY_SET_VARIATIONS_PRODUCTION_PROCESS_STATUS = "inventory_set_variations_production_process_status";
      const DB_TBL_INVENTORY_SET_VARIATIONS_PRODUCTION_PROCESS_STEP_VIEW = "inventory_set_variations_production_process_step_view";
      const DB_TBL_INVENTORY_SET_VARIATIONS_PRODUCTION_PROCESS_STEP = "inventory_set_variations_production_process_step";
      const DB_TBL_INVENTORY_SET_VARIATIONS_PRODUCTION_PROCESS_STEP_ITEM = "inventory_set_variations_production_process_step_item";
      const DB_TBL_INVENTORY_SET_VARIATIONS_PRODUCTION_PROCESS_STEP_ITEM_VIEW = "inventory_set_variation_production_process_step_item_view";
      const DB_TBL_INVENTORY_SET_VARIATIONS_BOM_PRODUCTION_PROCESS_RAW_ITEM_VIEW = "inventory_set_bom_process_raw_material_list";
      const DB_TBL_INVENTORY_SET_VARIATIONS_PRODUCTION_PROCESS_STEP_QUALITY_CHECK = "inventory_set_variations_production_process_step_quality_check";
      const DB_TBL_INVENTORY_SET_VARIATIONS_PRODUCTION_PROCESS_STEP_SEQUENCE = "inventory_set_variations_production_process_step_sequence";
      const DB_TBL_INVENTORY_SET_VARIATIONS_PRODUCTION_PROCESS_STEP_TASK = "inventory_set_variations_production_process_step_task";
      const DB_TBL_INVENTORY_SET_VARIATIONS_PRICE_PROFILING_LOG = "inventory_set_variations_price_profiling_log";

      /*
         * WorkOrder Module
         */
      const DB_TBL_WORKORDER = "workorder";
      const DB_TBL_WORKORDER_INTEND = "workorder_intend";
      const DB_TBL_WORKORDER_INTEND_LOG = "workorder_intend_log";
      const DB_TBL_WORKORDER_INTEND_STATUS = "workorder_intend_status";
      const DB_TBL_WORKORDER_INTEND_ISSUE = "workorder_intend_issue";
      const DB_TBL_WORKORDER_ITEM = "workorder_item";
      const DB_TBL_WORKORDER_ITEM_RAW_MATERIAL = "workorder_item_raw_material";
      const DB_TBL_WORKORDER_ITEM_RAW_MATERIAL_STAGE = "workorder_item_raw_material_stage";
      const DB_TBL_WORKORDER_CURRENT_STAGE = "workorder_current_stage";
      const DB_TBL_WORKORDER_FACTORY_SHIFT = "factory_shift";
      const DB_TBL_WORKORDER_STAGE = "workorder_stage";
      const DB_TBL_WORKORDER_STAGE_VIEW = "workorder_stage_view";
      const DB_TBL_WORKORDER_STAGE_DELIVERY_INPUT = "workorder_stage_delivery_input";
      const DB_TBL_WORKORDER_STAGE_ITEM = "workorder_stage_item";
      const DB_TBL_WORKORDER_STAGE_ITEM_ATTRIBUTE = "workorder_stage_item_attribute";
      const DB_TBL_WORKORDER_STAGE_ITEM_ASSET = "workorder_stage_item_assets";
      const DB_TBL_WORKORDER_STAGE_ITEM_INVENTORY_LEGAL = "workorder_stage_item_inventory_legal";
      const DB_TBL_WORKORDER_STAGE_ITEM_INVENTORY_USAGE = "workorder_stage_item_inventory_usage";
      const DB_TBL_WORKORDER_STAGE_ITEM_INVENTORY_REQUEST = "workorder_stage_item_inventory_request";
      const DB_TBL_WORKORDER_STAGE_ITEM_INVENTORY_REQUEST_PURCHASE_INTENDS = "workorder_stage_item_inventory_request_purchase_intend_mapping";
      const DB_TBL_WORKORDER_STAGE_ITEM_INVENTORY_REQUEST_VIEW = "workorder_stage_item_inventory_request_view";
      const DB_TBL_WORKORDER_STAGE_ITEM_INVENTORY_REQUEST_STATUS = "workorder_stage_item_inventory_request_status";
      const DB_TBL_WORKORDER_STAGE_ITEM_INVENTORY_ALLOTMENT = "workorder_stage_item_inventory_allotment";
      const DB_TBL_WORKORDER_STAGE_ITEM_INVENTORY_ALLOTMENT_VIEW = "workorder_stage_item_inventory_allotment_view";
      const DB_TBL_WORKORDER_STAGE_ITEM_INVENTORY_ALLOTMENT_STATUS = "workorder_stage_item_inventory_allotment_status";
      const DB_TBL_WORKORDER_STAGE_ITEM_VIEW = "workorder_stage_item_view";
      const DB_TBL_WORKORDER_STAGE_ITEM_QC_VIEW = "workorder_stage_item_qc_view";
      const DB_TBL_WORKORDER_STAGE_ITEM_DELIVERY = "workorder_stage_item_delivery";
      const DB_TBL_WORKORDER_STAGE_ITEM_MY_STAGE_DELIVERY_VIEW = "workorder_stage_item_my_stage_delivery_view";
      const DB_TBL_WORKORDER_STAGE_ITEM_MY_DELIVERY_VIEW = "workorder_stage_item_my_delivery_view";
      const DB_TBL_WORKORDER_STAGE_ITEM_DELIVERY_CHECKPOINT = "workorder_stage_item_delivery_checkpoint";
      const DB_TBL_WORKORDER_STAGE_ITEM_DELIVERY_CHECKPOINT_VIEW = "workorder_stage_item_delivery_checkpoint_view";
      const DB_TBL_WORKORDER_STAGE_ITEM_TASK = "workorder_stage_item_task";
      const DB_TBL_WORKORDER_STAGE_ITEM_QUALITY_CHECK = "workorder_stage_item_quality_check";
      const DB_TBL_WORKORDER_STAGE_ITEM_QUALITY_CHECK_LIST = "workorder_stage_item_quality_check_list";
      const DB_TBL_WORKORDER_STAGE_ITEM_DELIVERY_CHECKPOINT_CONSIGNMENT_MAPPING = "workorder_stage_item_delivery_checkpoint_consignment_mapping";
      const DB_TBL_WORKORDER_STAGE_SAMPLE_QC = "workorder_stage_sample_qc";
      const DB_TBL_WORKORDER_STAGE_SAMPLE_QC_LIST = "workorder_stage_sample_qc_list";

      /**
       *  Stock Request Tables.
       */
      const DB_TBL_STOCK_REQUEST = "stock_request";
      const DB_TBL_STOCK_REQUEST_VIEW = "stock_request_view";
      const DB_TBL_STOCK_REQUEST_PROCESSED = "stock_request_processed";
      const DB_TBL_STOCK_TRANSFER_LOGS = "stock_transfer_logs";
      const DB_TBL_STOCK_REQUEST_PROCESSED_ITEM = "stock_request_processed_item";
      const DB_TBL_STOCK_CONVERSION = "stock_conversion";
      const DB_TBL_STOCK_CONVERSION_TYPE = "stock_conversion_type";
      const DB_TBL_STOCK_CONVERSION_SOURCE_ITEM = "stock_conversion_source_item";
      const DB_TBL_STOCK_CONVERSION_TARGET_ITEM = "stock_conversion_target_item";
      const DB_TBL_STOCK_CONVERSION_HISTORY = "stock_conversion_history";
      const DB_TBL_STOCK_REQUEST_ITEMS = "stock_request_items";
      const DB_TBL_STOCK_REQUEST_STATUS = "stock_request_status";

      /**
       * Trysqure room
       */
      const DB_TBL_TRYSQUARE_ROOM = "trysquare_room";
      const DB_TBL_TRYSQUARE_SUB_ROOM = "trysquare_sub_room";

      /**
       *  Block Inventory
       */
      const DB_TBL_BLOCK_INVENTORY = "block_inventory";

      /**
       * Sidebar Menu
       */
      const DB_TBL_SIDEBAR_MENU = "sidebar_menu";

      /**
       * Moshi Screen and Video
       */
      const DB_TBL_MOSHI_SCREEN = "moshi_screen";
      const DB_TBL_MOSHI_SCREEN_STATUS = "moshi_screen_status";
      const DB_TBL_MOSHI_VIDEO = "moshi_video";
      const DB_TBL_MOSHI_VIDEO_STATUS = "moshi_video_status";
      const DB_TBL_MOSHI_SCHEDULE = "moshi_schedule";
      const DB_TBL_MOSHI_VIDEO_TYPE = "moshi_video_type";
      const DB_TBL_MOSHI_SCHEDULE_STATUS = "moshi_schedule_status";
      const DB_TBL_MOSHI_SCHEDULE_VIDEO_MAPPING = "moshi_schedule_video_mapping";
      const DB_TBL_MOSHI_SCREEN_SCHEDULE_MAPPING = "moshi_screen_schedule_mapping";
      const DB_TBL_MOSHI_SCHEDULE_VIDEO_MAPPING_TYPE = "moshi_schedule_video_mapping_type";
      const DB_TBL_MOSHI_SCREEN_LOG = 'moshi_screen_log';
      const DB_TBL_MOSHI_SCREEN_SCHEDULE_LOG = 'moshi_screen_schedule_log';
      const DB_TBL_SYSTEM_LOG = "system_log";
      const DB_TBL_MOSHI_VIDEO_IMAGE_MAPPING = "moshi_video_image_mapping";
      const DB_TBL_MOSHI_TWITTER_USER = "moshi_twitter_user";
      const DB_TBL_MOSHI_TWITTER_FEED = "moshi_twitter_feed";
      const DB_TBL_MOSHI_SCREEN_TWITTER_LOG = "moshi_screen_twitter_log";

      /**
       * leave tables
       */
      const DB_TBL_HR_LEAVE_TYPE = "hr_leave_type";
      const DB_TBL_HR_LEAVE_TYPE_STATUS = "hr_leave_type_status";
      const DB_TBL_HR_LEAVE_PERIOD = "hr_leave_period";
      const DB_TBL_HR_LEAVE_PERIOD_STATUS = "hr_leave_period_status";
      const DB_TBL_HR_LEAVE_PERIOD_TIME = "hr_leave_period_time";
      const DB_TBL_HR_LEAVE_PERIOD_TIME_STATUS = "hr_leave_period_time_status";
      const DB_TBL_HR_LEAVE_ENTITLEMENT = "hr_leave_entitlement";
      const DB_TBL_HR_VIEW_LEAVE_ENTITLEMENT = "hr_view_leave_entitlements";
      const DB_TBL_HR_LEAVE_ENTITLEMENT_STATUS = "hr_leave_entitlement_status";
      const DB_TBL_HR_LEAVE_REQUEST = "hr_leave_request";
      const DB_TBL_HR_LEAVE_REQUEST_STATUS = "hr_leave_request_status";
      const DB_TBL_HR_LEAVE_ADJUSTMENT = "hr_leave_adjustment";
      const DB_TBL_HR_HOLIDAY = "hr_holiday";
      const DB_TBL_HR_HOLIDAY_TYPE = "hr_holiday_type";
      const DB_TBL_HR_BIOMETRIC_DEVICE = "hr_biometric_device";
      const DB_TBL_HR_LETTER_TEMPLATE = "hr_letter_template";
      const DB_TBL_HR_SHORT_LEAVE = "short_leave";
      const DB_TBL_WEEKLY_OFF_PROFILE = "weekly_off_profile";
      const DB_TBL_HR_LEAVE_ALTERATION_LOG = "hr_leave_alteration_log";

      /**
       * Alltendance Record
       */
      const DB_TBL_HR_ATTENDANCE_RECORD = "hr_attendance_record";
      const DB_TBL_HR_BIOMETRIC_ATTENDANCE_REPORT = "hr_biometric_attendance_report";

      /**
       * Employee tables
       */
      const DB_TBL_HR_EMPLOYEE = "hr_employee";
      const DB_TBL_HR_EMPLOYEE_STATUS = "hr_employee_status";
      const DB_TBL_HR_EMPLOYEE_DEPARTMENT = "hr_employee_department";
      const DB_TBL_HR_EMPLOYEE_DESIGNATION = "hr_employee_designation";
      const DB_TBL_HR_EMPLOYEE_DESIGNATION_STATUS = "hr_employee_designation_status";
      const DB_TBL_HR_EMPLOYEE_DEPARTMENT_STATUS = "hr_employee_department_status";
      const DB_TBL_HR_EMPLOYEE_GROUP = "hr_employee_group";
      const DB_TBL_HR_EMPLOYEE_GROUP_STATUS = "hr_employee_group_status";
      const DB_TBL_HR_EMPLOYEE_EDUCATION = "hr_employee_education";
      const DB_TBL_HR_EMPLOYEE_EDUCATION_VIEW = "hr_employee_education_view";
      const DB_TBL_HR_EMPLOYEE_EDUCATION_ATTACHMENTS = "hr_employee_education_attachments";
      const DB_TBL_HR_EMPLOYEE_EDUCATION_STATUS = "hr_employee_education_status";
      const DB_TBL_HR_EMPLOYEE_EDUCATION_LEVEL = "hr_employee_education_level";
      const DB_TBL_HR_EMPLOYEE_TERMINATION = "hr_employee_termination";
      const DB_TBL_HR_EMPLOYEE_TERMINATION_VIEW = "hr_employee_termination_view";
      const DB_TBL_HR_EMPLOYEE_TERMINATION_REASON = "hr_employee_termination_reason";
      const DB_TBL_HR_EMPLOYEE_WORK_EXPERIENCE = "hr_employee_work_experience";
      const DB_TBL_HR_EMPLOYEE_WORK_EXPERIENCE_STATUS = "hr_employee_work_experience_status";
      const DB_TBL_HR_EMPLOYEE_WORK_EXPERIENCE_ATTACHMENTS = "hr_employee_work_experience_attachments";
      const DB_TBL_HR_COMPANY_TIMING = "hr_company_timing";
      const DB_TBL_HR_EMPLOYEE_SAVING = "hr_employee_saving";
      const DB_TBL_HR_SAVING_TYPE = "hr_saving_type";
      const DB_TBL_HR_EMPLOYEE_SAVING_MONTHLY = "hr_employee_saving_monthly";
      const DB_TBL_HR_ADVANCE_TYPE = "hr_advance_type";
      const DB_TBL_HR_EMPLOYEE_ADVANCE = "hr_employee_advance";
      const DB_TBL_HR_EMPLOYEE_ADVANCE_MONTHLY = "hr_employee_advance_monthly";
      const DB_TBL_HR_EMPLOYEE_OVERTIME = "hr_employee_overtime";
      const DB_TBL_HR_EMPLOYEE_LOGIN = "hr_employee_login";
      const DB_TBL_HR_EMPLOYEE_SESSION = "hr_employee_session";
      const DB_TBL_EMPLOYEE_ALLOWANCE_STATIC = "employee_allowance_static";
      const DB_TBL_HR_EMPLOYEE_OTHER_DOCUMENT_ATTACHMENTS = "hr_employee_other_document_attachments";
      const DB_TBL_HR_EMPLOYEE_USER_LINKAGE_CHANGE_LOG = "hr_employee_user_linkage_change_log";

      /**
       *  Ui Field Type Static
       */
      const DB_TBL_UI_FIELD_TYPE_STATIC = "ui_field_type_static";

      /**
       * Chat
       */
      const DB_TBL_CHAT_CHANNEL = "chat_channel";
      const DB_TBL_CHAT_CHANNEL_STATUS = "chat_channel_status";
      const DB_TBL_CHAT_CHANNEL_TYPE = "chat_channel_type";
      const DB_TBL_CHAT_CHANNEL_USER_MAPPING = "chat_channel_user_mapping";
      const DB_TBL_CHAT_LIST = "chat_list";
      const DB_TBL_CHAT_MESSAGES = "chat_messages";
      const DB_TBL_CHAT_MESSAGES_READ = "chat_messages_read";
      const DB_TBL_CHAT_LIST_TYPE_VIEW = "chat_list_type_view";
      /*
         * system settings
         */
      const DB_TBL_SYSTEM_SETTINGS = "system_settings";
      const DB_TBL_SYSTEM_SETTINGS_TYPE = "system_settings_type";
      const DB_TBL_SYSTEM_SETTINGS_STATUS = "system_settings_status";

      /**
       * Payroll
       */
      const DB_TBL_PAYROLL_EMPLOYEE_MANAGEMENT = "payroll_employee_management";
      const DB_TBL_PAYROLL_EMPLOYEE_MANAGEMENT_VIEW = "payroll_employee_management_view";
      const DB_TBL_PAYROLL_EMPLOYEE_MANAGEMENT_STATUS = "payroll_employee_management_status";
      const DB_TBL_PAYROLL_PROFILE = "payroll_profile";
      const DB_TBL_PAYROLL_GENERATE = "payroll_generate";
      const DB_TBL_PAYROLL_PROFILE_TYPE = "payroll_profile_type";
      const DB_TBL_PAYROLL_PROFILE_PROFILE = "payroll_profile_profile";
      const DB_TBL_PAYROLL_FINANCIALYEAR = "payroll_financialyear";
      const DB_TBL_PAYROLL_EMPLOYEE_PROFILE = "payroll_employee_profile";
      const DB_TBL_PAYROLL_EMPLOYEE_PAYSLIP_VIEW = "payroll_employee_payslip_view";
      const DB_TBL_PAYROLL_EMPLOYEE_PROFILE_INCLUDE_MAPPING = "payroll_employee_profile_include_mapping";
      const DB_TBL_PAYROLL_STATUTORY_COMPLIANCE = "payroll_statutory_compliance";
      const DB_TBL_PAYROLL_EMPLOYER_CONTRIBUTION = "payroll_employer_contribution";
      const DB_TBL_PAYROLL_PROFILE_SLAB = "payroll_profile_slab";

      /**
       * Testing Server
       */
      const DB_TBL_TESTING_PROJECTS = "testing_projects";

      /**
       * Commenting
       */
      const DB_TBL_COMMENT = "comment";
      const DB_TBL_COMMENT_LISTENERS = "comment_listeners";

      /**
       * Accounting
       */
      const DB_TBL_PURCHASE_ORDER_TAX_VIEW = "purchase_order_tax_view";

      /**
       *  Custom Attribute
       */
      const DB_TBL_CUSTOM_ATTRIBUTE = "custom_attribute";
      const DB_TBL_CUSTOM_ATTRIBUTE_STATUS = "custom_attribute_status";
      const DB_TBL_CUSTOM_ATTRIBUTE_TYPE = "custom_attribute_type";
      const DB_TBL_CUSTOM_ATTRIBUTE_VALUE = "custom_attribute_value";

      /**
       *  Admin User Wallet and payment
       */
      const DB_TBL_AUSER_WALLET_TRANSACTION = "auser_wallet_transaction";
      const DB_TBL_AUSER_WALLET_TRANSACTION_TYPE = "auser_wallet_transaction_type";
      const DB_TBL_AUSER_PAYMENT_DETAILS = "auser_payment_details";
      const DB_TBL_AUSER_PAYMENT_DETAILS_CLAIM_MAPPING = "auser_payment_details_claim_mapping";
      const DB_TBL_AUSER_PAYMENT_DETAILS_VIEW = "auser_payment_details_view";
      const DB_TBL_EMPLOYEE_TRANSACTION = "employee_transaction";

      /**
       * DMR
       */
      const DB_TBL_DMR = "dmr";
      const DB_TBL_DMR_FOLLOWUP_MAPPING = "dmr_followup_mapping";
      const DB_TBL_DMR_CLAIM_MAPPING = "dmr_auser_expense_claim_mapping";
      const DB_TBL_LEAD_CONTACT_VIEW = "lead_contact_view";

      /**
       * Order Template
       */
      const DB_TBL_ORDER_TEMPLATE = "order_template";

      /**
       * Invoice Print Template
       */
      const DB_TBL_INVOICE_PRINT_TEMPLATE = "invoice_print_template";

      /**
       * Purchase Invoice Template
       */
      const DB_TBL_PURCHASE_INVOICE_TEMPLATE = "purchase_invoice_template";

      /**
       * Credit Note Template
       */
      const DB_TBL_CREDIT_NOTE_TEMPLATE = "credit_note_template";
      const DB_TBL_CREDIT_NOTE_ITEM_WISE_REPORT = "credit_note_item_wise_report";

      /**
       * Freight Slip
       */
      const DB_TBL_FREIGHT_SLIP = "freight_slip";
      const DB_TBL_FREIGHT_SLIP_ITEM = "freight_slip_item";

      /**
       * Debit Note Template
       */
      const DB_TBL_DEBIT_NOTE_TEMPLATE = "debit_note_template";

      /**
       * Bank detail table
       */
      const DB_TBL_BANK_DETAILS = "bank_details";
      const DB_TBL_BANK_DETAIL_STATUS = "rapidkart_factory_static.bank_detail_status";
      const DB_TBL_BANK_DETAIL_TYPE = "rapidkart_factory_static.bank_detail_type";

      /**
       * Purchase Order Template
       */
      const DB_TBL_PURCHASE_ORDER_TEMPLATE = "purchase_order_template";

      /**
       * Project
       */
      const DB_TBL_MILESTONE = "milestone";
      const DB_TBL_MILESTONE_STATUS = "milestone_status";
      const DB_TBL_PROJECT = "project";
      const DB_TBL_PROJECT_STATUS = "project_status";
      const DB_TBL_CUSTOMER_PROJECT_MAPPING = "customer_project_mapping";
      const DB_TBL_CUSTOMER_PROJECT_MANAGEMENT = "project_management";
      const DB_TBL_CUSTOMER_PROJECT_MANAGEMENT_USER_MAPPING = "project_management_user_mapping";
      const DB_TBL_PROJECT_MANAGEMENT_WAREHOUSE_MAPPING = "project_management_warehouse_mapping";
      const DB_TBL_PROJECT_MILESTONE_MAPPING = "project_milestone_mapping";
      const DB_TBL_CHESTER_FACTORY_WORKORDER_ITEM = "chester_factory_workorder_item";
      const DB_TBL_CHESTER_FACTORY_WORKORDER_ITEM_STATUS = "chester_factory_workorder_item_status";
      const DB_TBL_CHESTER_FACTORY_WORKORDER_PROCESS_SEQUENCE = "chester_factory_workorder_process_sequence";
      const DB_TBL_CHESTER_FACTORY_WORKORDER_ITEM_DELIVERY = "chester_factory_workorder_item_delivery";
      const DB_TBL_CHESTER_FACTORY_WORKORDER_ITEM_TRANSPORT_LOGS = "chester_factory_workorder_item_transport_logs";
      const DB_TBL_PROJECT_MANAGEMENT_NEGOTIATED_ITEMS = "project_management_items";
      const DB_TBL_PROJECT_MANAGEMENT_FILE = "project_management_file";
      const DB_TBL_PROJECT_MANAGEMENT_REFERENCE = "project_management_reference";
      const DB_TBL_PROJECT_MANAGEMENT = "project_management";
      const DB_TBL_ECOM_STORE_PROJECTS = 'ecom_store_projects';

      /**
       * system reference
       * 
       */
      const DB_TBL_SYSTEM_REFERENCES = "system_references";
      const DB_TBL_SYSTEM_OTHER_REFERENCES = "system_other_references";
      const DB_TBL_SYSTEM_OTHER_REFERENCES_UPI = "system_other_references_upi";
      const DB_TBL_SYSTEM_OTHER_REFERENCES_FILES = "system_other_references_files";
      const DB_TBL_SYSTEM_PREFERENCES_GROUP = "system_preferences_group";
      const DB_TBL_SYSTEM_PREFERENCES_CATEGORY = "system_preferences_category";
      const DB_TBL_SYSTEM_PREFERENCES_MODULE_MAPPING = "system_preferences_module_mapping";

      /**
       * Outlet
       */
      const DB_TBL_OUTLET = "outlet";
      const DB_TBL_OUTLET_BANK = "outlet_bank";
      const DB_TBL_OUTLET_USER_MAPPING = "outlet_user_mapping";
      const DB_TBL_OUTLET_PAYMENT_MAPPING = "outlet_payment_mapping";
      const DB_TBL_SHIPPING_ADDRESS_MAPPING = "shipping_address_mapping";
      const DB_TBL_OUTLET_BUSINESS_TAX_PROFILE_MAPPING = "outlet_business_tax_profile_mapping";
      const DB_TBL_OUTLET_HEADER_IMAGES = "outlet_header_images";
      const DB_TBL_OUTLET_FOOTER_IMAGES = "outlet_footer_images";
      const DB_TBL_OUTLET_STORE_IMAGES = "outlet_store_images";
      const DB_TBL_OUTLET_LOGO_IMAGES = "outlet_logo_images";
      const DB_TBL_OUTLET_EXTRA_CHARGES_MAPPING = 'outlet_extra_charges_mapping';
      const DB_TBL_OUTLET_REGION = "outlet_region";

      /**
       * Invoice
       */
      const DB_TBL_INVOICE = "invoice";
      //        const DB_TBL_INVOICE_FILES = "invoice_file";
      const DB_TBL_INVOICE_STATUS = "invoice_status";
      const DB_TBL_INVOICE_ITEM = "invoice_item";
      const DB_TBL_INVOICE_ITEM_PALLET = "invoice_item_pallet";
      const DB_TBL_INVOICE_ITEM_STATUS = "invoice_item_status";
      const DB_TBL_INVOICE_BUSINESS_TAX_PROFILE = "invoice_business_tax_profile";
      const DB_TBL_INVOICE_BUSINESS_TAX_PROFILE_DOCUMENT = "invoice_business_tax_profile_document";
      const DB_TBL_INVOICE_BUSINESS_TAX_PROFILE_DOCUMENT_STATUS = "invoice_business_tax_profile_document_status";
      const DB_TBL_INVOICE_FILE = "invoice_file";
      const DB_TBL_INVOICE_EXTRA_CHARGES = "invoice_extra_charges";
      const DB_TBL_INVOICE_ITEM_EXTRA_CHARGES = "invoice_item_extra_charges";
      const DB_TBL_INVOICE_TRANSACTION = "invoice_transaction";
      const DB_TBL_INVOICE_TRANSACTION_STATUS = "invoice_transaction_status";
      const DB_TBL_INVOICE_VIEW = "invoice_view";
      const DB_TBL_JOBCARD_INVOICE_TRANSACTION_VIEW = "jobcard_invoice_transaction_view";
      const DB_TBL_CHECKPOINT_INVOICE_TRANSACTION_VIEW = "checkpoint_invoice_transaction_view";
      const DB_TBL_INVOICE_REFERENCE = "invoice_reference";
      const DB_TBL_INVOICE_EXTRA_DISCOUNT = "invoice_extra_discount";
      const DB_TBL_INVOICE_ITEM_EXTRA_DISCOUNT = "invoice_item_extra_discount";
      // const DB_TBL_INVOICE_FILE = "invoice_file";
      const DB_TBL_CARD = "card";
      const DB_TBL_CARD_ITEM = "card_item";
      const DB_TBL_INVOICE_TABLE_VIEW = "invoice_table_view";
      const DB_TBL_SALES_TRANSACTION_TYPE = "sales_transaction_type";
      const DB_TBL_INVOICE_EDIT_HISTORY = "invoice_edit_history";
      const DB_TBL_INVOICE_ITEM_INVENTORY = "invoice_item_inventory";
      const DB_TBL_INVOICE_SALES_ORDER_REPORT = "invoice_sales_order_report";
      const DB_TBL_INVOICE_CATEGORY_TYPE = "invoice_category_type";
      const DB_TBL_SALES_TRANSACTION_TYPE_VARIABLES_STATIC = "sales_transaction_type_variables_static";
      const DB_TBL_INVOICE_REPORT_ATTRIBUTE_FILTER = "invoice_report_attribute_filter";
      const DB_TBL_INVOICE_EDIT_CUSTOMER_LOG = "invoice_edit_customer_log";
      const DB_TBL_INVOICE_CONTAINER_WEIGHING = 'invoice_container_weighing';
      //   const DB_TBL_PURCHASE_ORDER_REPORT = "purchase_order_report_table_view_";
      const DB_TBL_INVOICE_ESTIMATION = "invoice_estimation";
      const DB_TBL_INVOICE_CASH_VOUCHER = "invoice_cash_voucher";
      const DB_TBL_INVOICE_ESTIMATION_ITEM = "invoice_estimation_items";
      const DB_TBL_INVOICE_EWAY = "invoice_eway";
      const DB_TBL_INVOICE_CUSTOM = "invoice_custom";
      const DB_TBL_INVOICE_CUSTOM_BRC = "invoice_custom_brc";
      const DB_TBL_INVOICE_CUSTOM_CONTAINER = "invoice_custom_container";
      const DB_TBL_INVOICE_CUSTOM_EXTRA_CHARGES = "invoice_custom_extra_charges";
      const DB_TBL_INVOICE_COMMERCIAL = "invoice_commercial";
      const DB_TBL_INVOICE_COMMERCIAL_CONTAINER = "invoice_commercial_container";
      const DB_TBL_INVOICE_COMMERCIAL_EXTRA_CHARGES = "invoice_commercial_extra_charges";
      const DB_TBL_INVOICE_LINK_MAPPING = "invoice_order_link_mapping";
      const DB_TBL_INVOICE_DELIVERY_TRACKING = "invoice_delivery_tracking";

      /**
       * Accounts
       */
      const DB_TBL_ACCOUNT_LEDGER = "account_ledger";
      const DB_TBL_ACCOUNT_LEDGER_USER_MAPPING = "account_ledger_user_mapping";
      const DB_TBL_ACCOUNT_LEDGER_TDS_CATEGORY_MAPPING = "account_ledger_tds_category_mapping";
      const DB_TBL_ACCOUNT_LEDGER_ATTACHMENT = "account_ledger_attachment";
      const DB_TBL_ACCOUNT_GROUP = "account_group";
      const DB_TBL_ACCOUNT_GROUP_STATUS = "account_group_status";
      const DB_TBL_ACCOUNT_LEDGER_STATUS = "account_ledger_status";
      const DB_TBL_ACCOUNT_TRANSACTION = "account_transaction";
      const DB_TBL_ACCOUNT_TRANSACTION_STATUS = "account_transaction_status";
      const DB_TBL_ACCOUNT_TRANSACTION_TYPE = "account_transaction_type";
      const DB_TBL_ACCOUNT_TRANSACTION_TYPE_STATUS = "account_transaction_type_status";
      const DB_TBL_ACCOUNT_VOUCHER = "account_voucher";
      const DB_TBL_ACCOUNT_VOUCHER_STATUS = "account_voucher_status";
      const DB_TBL_ACCOUNT_VOUCHER_ATTACHMENT = "account_voucher_attachment";
      const DB_TBL_ACCOUNT_VOUCHER_TYPE = "account_voucher_type";
      const DB_TBL_ACCOUNT_VOUCHER_SUB_TYPE = "account_voucher_sub_type";
      const DB_TBL_ACCOUNT_VOUCHER_TYPE_RESTRICTION = "account_voucher_type_restriction";
      const DB_TBL_ACCOUNT_VOUCHER_TYPE_STATUS = "account_voucher_type_status";
      const DB_TBL_ACCOUNT_TYPE = "account_type";
      const DB_TBL_ACCOUNT_TYPE_STATUS = "account_type_status";
      const DB_TBL_ACCOUNT_VOUCHER_MAPPING = "account_voucher_mapping";
      const DB_TBL_ACCOUNT_VOUCHER_STATUTORY_MAPPING = "account_voucher_statutory_mapping";
      const DB_TBL_ACCOUNT_LEDGER_POS_LEDGER_MAPPING = "account_ledger_pos_ledger_mapping";
      const DB_TBL_ACCOUNT_VOUCHER_EDIT_HISTORY = "account_voucher_edit_history";
      const DB_TBL_ACCOUNT_VOUCHER_ATTENDEE_MAPPING = "account_voucher_attendee_mapping";
      const DB_TBL_MONTHLY_SALES_ANALYSIS_REPORT_ATTRIBUTE_FILTER = "monthly_sales_analysis_report_attribute_filter";

      /**
       * Purchase Invoice
       */
      const DB_TBL_PURCHASE_INVOICE = "purchase_invoice";
      const DB_TBL_PURCHASE_INVOICE_STATUS = "purchase_invoice_status";
      const DB_TBL_PURCHASE_INVOICE_ITEM = "purchase_invoice_item";
      const DB_TBL_PURCHASE_INVOICE_ITEM_BARCODE = "purchase_invoice_item_barcode";
      const DB_TBL_PURCHASE_INVOICE_ITEM_STATUS = "purchase_invoice_item_status";
      const DB_TBL_PURCHASE_INVOICE_BUSINESS_TAX_PROFILE = "purchase_invoice_business_tax_profile";
      const DB_TBL_PURCHASE_INVOICE_BUSINESS_TAX_PROFILE_DOCUMENT = "purchase_invoice_business_tax_profile_document";
      const DB_TBL_PURCHASE_INVOICE_BUSINESS_TAX_PROFILE_DOCUMENT_STATUS = "purchase_invoice_business_tax_profile_document_status";
      const DB_TBL_PURCHASE_INVOICE_EXTRA_CHARGES = "purchase_invoice_extra_charges";
      const DB_TBL_PURCHASE_INVOICE_TRANSACTION = "purchase_invoice_transaction";
      const DB_TBL_PURCHASE_INVOICE_VIEW = "invoice_view";
      const DB_TBL_PURCHASE_INVOICE_REFERENCE = "purchase_invoice_reference";
      const DB_TBL_PURCHASE_TRANSACTION_TYPE = "purchase_transaction_type";
      const DB_TBL_PURCHASE_INVOICE_EXTRA_DISCOUNT = "purchase_invoice_extra_discount";
      const DB_TBL_PURCHASE_INVOICE_REPORT = "purchase_invoice_report";
      const DB_TBL_PURCHASE_INVOICE_EDIT_VENDOR_LOG = 'purchase_invoice_edit_vendor_log';
      const DB_TBL_PURCHASE_INVOICE_MARKUP_LOG = "purchase_invoice_markup_log";
      const DB_TBL_PURCHASE_INVOICE_EDIT_HISTORY = "purchase_invoice_edit_history";

      /**
       * Asset
       */
      const DB_TBL_ASSET = "asset";
      const DB_TBL_ASSET_DEPRECIATION = "asset_depreciation";
      const DB_TBL_ASSET_DEPRECIATION_STATUS = "asset_depreciation_status";
      const DB_TBL_ASSET_DEPRECIATION_ASSET = "asset_depreciation_asset";
      const DB_TBL_ASSET_DEPRECIATION_METHOD = "asset_depreciation_method";
      const DB_TBL_ASSET_DEPRECIATION_METHOD_STATUS = "asset_depreciation_method_status";
      const DB_TBL_ASSET_DEPRECIATION_PURPOSE = "asset_depreciation_purpose";
      const DB_TBL_ASSET_DEPRECIATION_PURPOSE_STATUS = "asset_depreciation_purpose_status";
      const DB_TBL_ASSET_DISPOSAL = "asset_disposal";
      const DB_TBL_ASSET_DISPOSAL_STATUS = "asset_disposal_status";
      const DB_TBL_ASSET_DISPOSAL_ASSET = "asset_disposal_asset";
      const DB_TBL_ASSET_INSURANCE = "asset_insurance";
      const DB_TBL_ASSET_INSURANCE_ASSET = "asset_insurance_asset";
      const DB_TBL_ASSET_INSURANCE_ASSET_STATUS = "asset_insurance_asset_status";
      const DB_TBL_ASSET_INSURANCE_STATUS = "asset_insurance_status";
      const DB_TBL_ASSET_INSURANCE_TYPE = "asset_insurance_type";
      const DB_TBL_ASSET_MANAGEMENT_COMPANY = "asset_management_company";
      const DB_TBL_ASSET_MANAGEMENT_COMPANY_STATUS = "asset_management_company_status";
      const DB_TBL_ASSET_MANAGEMENT_COMPANY_ASSET = "asset_management_company_asset";
      const DB_TBL_ASSET_MANAGEMENT_COMPANY_ASSET_STATUS = "asset_management_company_asset_status";
      const DB_TBL_ASSET_MANAGEMENT_COMPANY_TYPE = "asset_management_company_type";
      const DB_TBL_ASSET_PURCHASE = "asset_purchase";
      const DB_TBL_ASSET_PURCHASE_ASSET = "asset_purchase_asset";
      const DB_TBL_ASSET_PURCHASE_BUSINESS_TAX_PROFILE = "asset_purchase_business_tax_profile";
      const DB_TBL_ASSET_PURCHASE_EXTRA_CHARGES = "asset_purchase_extra_charges";
      const DB_TBL_ASSET_PURCHASE_STATUS = "asset_purchase_status";
      const DB_TBL_ASSET_PURCHASE_ASSET_STATUS = "asset_purchase_asset_status";
      const DB_TBL_ASSET_SALES = "asset_sales";
      const DB_TBL_ASSET_SALES_ASSET = "asset_sales_asset";
      const DB_TBL_ASSET_SALES_BUSINESS_TAX_PROFILE = "asset_sales_business_tax_profile";
      const DB_TBL_ASSET_SALES_EXTRA_CHARGES = "asset_sales_extra_charges";
      const DB_TBL_ASSET_SALES_STATUS = "asset_sales_status";
      const DB_TBL_ASSET_SALES_ASSET_STATUS = "asset_sales_asset_status";
      const DB_TBL_ASSET_STATUS = "asset_status";
      const DB_TBL_ASSET_TRANSFER = "asset_transfer";
      const DB_TBL_ASSET_TRANSFER_STATUS = "asset_transfer_status";
      const DB_TBL_ASSET_TRANSFER_ASSET = "asset_transfer_asset";
      const DB_TBL_FACTORY_STAGE_ASSET = "factory_stage_assets";
      const DB_TBL_FACTORY_STAGE_ATTRIBUTE_VALUES = "factory_stage_attribute_values";

      /**
       * Insurance
       */
      const DB_TBL_INSURANCE = "insurance";
      const DB_TBL_INSURANCE_STATUS = "insurance_status";
      const DB_TBL_INSURANCE_TYPE = "insurance_type";
      const DB_TBL_INSURANCE_TYPE_STATUS = "insurance_type_status";

      /**
       * Sales report
       */
      const DB_TBL_SALES_REPORT_VIEW = "sales_report_view";
      const DB_TBL_INVOICE_REPORT_VIEW = "invoice_report_view";
      const DB_TBL_PURCHASE_INVOICE_REPORT_VIEW = "purchase_invoice_report_view";
      const DB_TBL_INVOICE_ORDER_REPORT_TABLE_VIEW = "invoice_order_report_table_view";
      const DB_TBL_CHECK_POINT_MONTHLY_SALES_VIEW = "check_point_monthly_sales_view";
      const DB_TBL_INVOICE_REPORT_TABLE_VIEW = "invoice_report_table_view";
      const DB_TBL_SALES_INVOICE_REPORT = "sales_invoice_report";

      /**
       * Check Item Availability
       */
      const DB_TBL_CHECK_ITEM_AVAILABILITY = "check_item_availability";
      const DB_TBL_CHECK_ITEM_AVAILABILITY_STATUS = "check_item_availability_status";

      /**
       * Request
       */
      const DB_TBL_REQUEST = "request";
      const DB_TBL_REQUEST_TYPE_CUSTOMER_MAPPING = "request_type_customer_mapping";
      const DB_TBL_REQUEST_GROUP_CUSTOMER_MAPPING = "request_group_customer_mapping";
      const DB_TBL_REQUESTOR_REQUEST_GROUP_CUSTOMER_MAPPING = "requestor_request_group_customer_mapping";
      const DB_TBL_REQUEST_VENDOR_MAPPING = "request_vendor_mapping";
      const DB_TBL_REQUEST_CONTRACT_MAPPING = "request_contract_mapping";
      const DB_TBL_REQUEST_LOG = "request_log";
      const DB_TBL_ATTACHMENT_LOG = "attacthment_log";
      const DB_TBL_REQUEST_GROUP = "request_group";
      const DB_TBL_REQUEST_VENDOR = "request_vendor";
      const DB_TBL_REQUEST_CONTRACT = "request_contract";
      const DB_TBL_REQUEST_PRIORITY = "request_priority";
      const DB_TBL_REQUEST_TYPE = "request_type";
      const DB_TBL_REQUEST_STATUS = "request_status";
      const DB_TBL_REQUEST_ATTACHMENT = "request_attachment";
      const DB_TBL_REQUEST_CUSTOMER_VENDOR_MAPPING = "request_customer_vendor_mapping";

      /**
       * 
       */
      const DB_TBL_FABWOOD_PACKING_LIST = "fabwood_packing_list";
      const DB_TBL_FABWOOD_PACKING_LIST_ITEM = "fabwood_packing_list_item";

      /**
       * 
       */
      const DB_TBL_POS_ORDER = "pos_order";
      const DB_TBL_POS_ORDER_ITEMS = "pos_order_items";
      const DB_TBL_POS_ORDER_CUSTOMER_PAYMENT_OPTION_ATTRIBUTES = "pos_order_customer_payment_option_attributes";
      const DB_TBL_POS_ORDER_EXTRA_CHARGES = "pos_order_extra_chargs";
      const DB_TBL_POS_BUSINESS_TAX_PROFILE = "pos_order_business_tax_profile";

      /*
         * upload files
         */
      const DB_TBL_UPLOADING_TYPE = "uploading_type";
      const DB_TBL_UPLOADING_TYPE_COLUMN = "uploading_type_columns";

      /**
       * rcm
       */
      const DB_TBL_RCM = "rcm";
      const DB_TBL_RCM_DETAILS = "rcm_details";

      /**
       * Customization
       */
      const DB_TBL_REQUEST_CUSTOMIZATION_ITEM = 'request_customization_item';
      const DB_TBL_RECEIVED_CUSTOMIZATION_ITEM = 'received_customization_item';

      /**
       * commission
       */
      const DB_TBL_COMMISSION = "commission";
      const DB_TBL_COMMISSION_MAPPING = "commission_mapping";
      const DB_TBL_COMMISSION_MAPPING_PAYMENT = "commision_mapping_payment";
      const DB_TBL_ACCOUNT_VOUCHER_INVOICE_COMMISSION_MAPPING = "account_voucher_invoice_commission_mapping";

      /**
       * Stat Adjustment
       */
      const DB_TBL_STAT_ADJUSTMENT = "stat_adjustment";
      const DB_TBL_STAT_ADJUSTMENT_TYPE = "stat_adjustment_type";
      const DB_TBL_STAT_ADJUSTMENT_OTHER_TYPE = "stat_adjustment_other_type";
      const DB_TBL_ADVANCE_SALES_VIEW = "advance_sales_view";
      ////bank reconciliation 

      const DB_TBL_BANK_TRANSACTIONS = "bank_transactions";
      const DB_TBL_BANK_LEDGER_MAPPING = "bank_ledger_mapping";
      const DB_TBL_BANK_TYPE = "bank_type";
      const DB_TBL_ACCOUNT_LEDGER_VIEW_BANK = "account_ledger_view_bank";

      /**
       * purchase Invoice Import
       */
      const DB_TBL_PURCHASE_INVOICE_IMPORT = "purchase_invoice_import";
      const DB_TBL_PURCHASE_INVOICE_IMPORT_ITEM = "purchase_invoice_import_item";
      const DB_TBL_PURCHASE_INVOICE_IMPORT_EXTRA_CHARGES = "purchase_invoice_import_extra_charges";
      const DB_TBL_PURCHASE_INVOICE_IMPORT_BUSINESS_TAX_PROFILE = "purchase_invoice_import_business_tax_profile";
      const DB_TBL_SALES_PERSON_REPORT_VIEW_TODAY = "sales_person_report_view_today";
      const DB_TBL_ACCOUNT_LEDGER_CHEQUE_CONFIGURATION = "account_ledger_cheque_configuration";
      const DB_TBL_SMS_REPORT_LOG = "sms_report_log";
      const DB_TBL_SMS_REPORT = "sms_report";
      const DB_TBL_EMAIL_REPORT_LOG = "email_report_log";
      const DB_TBL_EMAIL_REPORT = "email_report";
      const DB_TBL_CRON_JOB_LOG = "cron_job_log";
      const DB_TBL_INVENTORY_SET_VARIATIONS_ATTRIBUTE_ATTRIBUTE_VALUE = "inventory_set_variations_attribute_attribute_value";

      /**
       * Extra Discount
       */
      const DB_TBL_EXTRA_DISCOUNT = "extra_discount";

      /**
       * Warehouse Production
       */
      const DB_TBL_WAREHOUSE_PRODUCTION = "warehouse_production";
      const DB_TBL_WAREHOUSE_PRODUCTION_SOURCE_ITEM = "warehouse_production_source_item";
      const DB_TBL_WAREHOUSE_PRODUCTION_TARGET_ITEM = "warehouse_production_target_item";
      const DB_TBL_QUALITY_CHECK_DETAIL = "quality_check_detail";
      //product group//
      const DB_TBL_PRODUCT_GROUP = "product_group";
      const DB_TBL_PRODUCT_GROUP_MAPPING = "product_group_mapping";
      const DB_TBL_PHYSICAL_STOCK_VERIFICATION_REPORT_ATTRIBUTE_FILTER = "physical_stock_verification_report_attribute_filter";

      /**
       * Licence
       */
      const DB_TBL_LICENCE = "licence";
      const DB_TBL_LICENCE_DOMAIN = "licence_domain";
      const DB_TBL_LICENCE_COMPANIES = "licence_companies";
      const DB_TBL_MASK_CONFIG = "mask_config";
      const DB_TBL_LICENCE_MASK_CONFIG_MAPPING = "licence_mask_config_mapping";
      const DB_TBL_INVOICE_CONFIG = "invoice_config";
      const DB_TBL_INVOICE_CONFIG_VALUES = "invoice_config_values";
      const DB_TBL_LICENCE_INVOICE_CONFIG_MAPPING = "licence_invoice_config_mapping";
      const DB_TBL_LICENCE_SYSTEM_PREFERENCES_MAPPING = "licence_system_preferences_mapping";
      const DB_TBL_LICENCE_CUSTOMER_PAYMENT_OPTION_MAPPING = "licence_customer_payment_option_mapping";
      const DB_TBL_LICENCE_MEASUREMENT_MAPPING = "licence_measurement_mapping";

      /**
       * Stock Verification
       */
      const DB_TBL_STOCK_VERIFICATION = "stock_verification";
      const DB_TBL_STOCK_VERIFICATION_ITEM = "stock_verification_item";
      const DB_TBL_STOCK_VERIFICATION_ITEM_FILES = "stock_verification_item_files";

      /**
       * Work Order Services
       */
      const DB_TBL_WORKORDER_SERVICES = "workorder_services";
      const DB_TBL_WORKORDER_SERVICES_ITEM = "workorder_services_item";
      const DB_TBL_WORKORDER_SERVICES_EXTRA_CHARGES = "workorder_services_extra_charges";
      const DB_TBL_WORKORDER_SERVICES_BUSINESS_TAX_PROFILE = "workorder_services_business_tax_profile";
      const DB_TBL_WORKORDER_SERVICES_EXTRA_DISCOUNT = "workorder_services_extra_discount";
      const DB_TBL_WORKORDER_INVENTORY_CONSUMPTION_SERVICE_ITEMS = "warehouse_inventory_consumption_service_items";
      const DB_TBL_WORKORDER_INVENTORY_RECEIVE_SERVICE_ITEMS = "warehouse_inventory_receive_service_items";
      const DB_TBL_WORKORDER_SERVICE_INTEND = "workorder_service_intend";
      const DB_TBL_WORKORDER_SERVICE_INTEND_STATUS = "workorder_service_intend_status";
      const DB_TBL_WORKORDER_SERVICES_ITEM_PROGRESS_LOG = "workorder_services_item_progress_log";
      const DB_TBL_WORKORDER_SERVICES_ITEM_PROGRESS_DOCS = "workorder_services_item_progress_docs";

      /**
       * 
       * project management
       */
      const DB_TBL_PROECTMANAGEMENT = "project_management";

      /**
       * Service Log
       */
      const DB_TBL_SERVICE_LOG = "service_log";

      /**
       * Customer Log
       */
      const DB_TBL_Customer_LOG = "customer_log";
      const DB_TBL_CUSTOMER_BRAND_CATEGORY_DISCOUNT_LOG = "customer_brand_category_discount_log";

      /**
       *  Schemes
       */
      const DB_TBL_SCHEMES = "schemes";
      //currecny//

      const DB_TBL_CURRENCIES = "currencies";

      /*
          HR Configuration
         */
      const DB_TBL_HR_CONFIGURATION = "hr_configuration";
      const DB_TBL_HR_CONFIGURATION_MAPPING = "hr_configuration_mapping";

      /**
       * TDS Category
       */
      const DB_TBL_TDS_CATEGORY = "tds_category";
      const DB_TBL_ATTENDANCE = "attendance";
      const DB_TBL_DEDUCTEE_TYPE = "deductee_type";
      const DB_TBL_TDS_DEDUCTION = "tds_deduction";
      const DB_TBL_TDS_DEDUCTION_PAYMENT = "tds_deduction_payment";
      const DB_TBL_TDS_DEDUCTION_REFERENCE = "tds_deduction_reference";
      const DB_TBL_PURCHASE_INVOICE_ALID_MAPPING = "purchase_invoice_alid_mapping";
      const DB_TBL_TDS_CATEGORY_DEDUCTEE_TYPE_MAPPING = "tds_category_deductee_type_mapping";
      const DB_TBL_VENDOR_TDS_CATEGORY_MAPPING = "vendor_tds_category_mapping";
      const DB_TBL_CUSTOMER_TDS_CATEGORY_MAPPING = "customer_tds_category_mapping";

      /**
       * Daily tracking
       */
      const DB_TBL_DAILY_MOVEMENT_TRACKER = "daily_movement_tracker";
      const DB_TBL_DAILY_MOVEMENT_TRACKER_MARKS = "daily_movement_tracker_marks";

      /*         * Variation Purchase Price
         */
      const DB_TBL_VARIATION_PURCHASE_PRICE = "variation_purchase_price";
      const DB_TBL_INVENTORY_SET_CONTAINER_PURCHASE_ORDER_MAPPING = "inventory_set_container_purchase_order_mapping";
      const DB_TBL_VARIATION_PRICE_UPDATE = "variation_price_update";
      const DB_TBL_VARIATION_PRICE_UPDATE_ITEM = "variation_price_update_item";

      /**
       * Low Inventory
       */
      const DB_TBL_LOW_INVENTORY_TABLE = "low_inventory_table";

      /**
       * Service Memo
       */
      const DB_TBL_SERVICE_MEMO = "service_memo";
      const DB_TBL_SAMPLEMODULE = "sample_module";
      const DB_TBL_MODULEDETAILS = "module_details";
      const DB_TBL_SERVICE_MEMO_STATUS = "service_memo_status";

      /*
         * MOM
         */
      const DB_TBL_MEETING_LIST = "accreteglobus.meeting_list";
      const DB_TBL_MEETING_LIST_MESSAGE = "accreteglobus.meeting_list_message";
      const DB_TBL_MEETING_ISSUE_FILE = "accreteglobus.meeting_issue_file";
      const DB_TBL_ISSUE_LIST = "accreteglobus.issue_list";
      const DB_TBL_ISSUE_LIST_MESSAGE = "accreteglobus.issue_list_message";
      const DB_TBL_REPORTING_TYPE = "accreteglobus.reporting_type";
      const DB_TBL_MEETING_AGENDA = "accreteglobus.meeting_agenda";
      const DB_TBL_MEETING_ACTIVITY = "accreteglobus.meeting_activity";
      const DB_TBL_MEETING_ACTIVITY_HISTORY = "accreteglobus.meeting_activity_history";
      const DB_TBL_MEETING_ACTIVITY_ETR_HISTORY = "accreteglobus.meeting_activity_etr_history";
      const DB_TBL_MEETING_ACTIVITY_STATUS = "accreteglobus.meeting_activity_status";
      const DB_TBL_MEETING_ACTIVITY_PARENT_STATUS = "accreteglobus.meeting_activity_parent_status";
      const DB_TBL_MEETING_ACTIVITY_PARENT_STATUS_MAPPING = "accreteglobus.meeting_activity_parent_status_mapping";
      const DB_TBL_MEETING_ACTIVITY_STATUS_WORKFLOW = "accreteglobus.meeting_activity_status_workflow";
      const DB_TBL_MOM_CATEGORY = "accreteglobus.mom_category";
      const DB_TBL_MOM_PRIORITY = "accreteglobus.mom_priority";
      const DB_TBL_MOM_APP_TYPE = "accreteglobus.mom_app_type";

      /**
       * Issue Reporting
       */
      const DB_TBL_ISSUE = "accreteglobus.issue";
      const DB_TBL_ISSUE_ATTACHMENT = "accreteglobus.issue_attachment";
      const DB_TBL_MEETING_ACTIVITY_ATTACHMENT = "accreteglobus.meeting_activity_attachment";
      const DB_TBL_ISSUE_STATUS = "accreteglobus.issue_status";

      /*
         * Secondary Consumer Tables
         */
      const DB_TBL_SECONDARY_CONSUMER = "secondary_consumer";
      const DB_TBL_SECONDARY_CONSUMER_TYPE = "secondary_consumer_type";
      const DB_TBL_SECONDARY_CONSUMER_TYPE_ATTRIBUTE = "secondary_consumer_type_attribute";

      /*
         * Ecommerce mapping Tables
         */
      const DB_TBL_ECOMMERCE_VARIATION_MAPPING = "ecommerce_variation_mapping";
      const DB_TBL_ECOMMERCE_WAREHOUSE_MAPPING = "ecommerce_warehouse_mapping";
      const DB_TBL_ECOMMERCE_SITES = "ecommerce_sites";
      const DB_TBL_ECOMMERCE_REQUEST_REPORT = "ecommerce_report_request";
      const DB_TBL_ECOMMERCE_REPORT_DETAILS = "ecommerce_report_details";
      const DB_TBL_ECOMMERCE_REPORT_PAYMENT_DETAILS = "ecommerce_payment_report_details";
      const DB_TBL_ECOMMERCE_RETURN_REPORT_DETAILS = "ecommerce_report_return_details";
      const DB_TBL_ECOMMERCE_INVENTORY_REPORT_DETAILS = "ecommerce_inventory_report_details";
      const DB_TBL_ECOMMERCE_LIST_ORDERS = "ecommerce_list_orders";
      const DB_TBL_ECOMMERCE_API_URL = "ecommerce_api_url";
      const DB_TBL_ECOMMERCE_LIST_ORDER_ITEMS = "ecommerce_list_order_items";
      const DB_TBL_ECOMMERCE_REIMBURSEMENT_REPORT_DETAILS = "ecommerce_reimbursement_report_details";
      const DB_TBL_ECOMMERCE_REPLACEMENT_REPORT_DETAILS = "ecommerce_report_replacement_details";

      /*
         * Sales Return Request Tables
         */
      const DB_TBL_SALES_RETURN_REQUEST = "sales_return_request";
      const DB_TBL_SALES_RETURN_REQUEST_STATUS = "sales_return_request_status";
      const DB_TBL_SALES_RETURN_REQUEST_FILE = "sales_return_request_file";
      const DB_TBL_SALES_RETURN_REQUEST_ITEM = "sales_return_request_item";
      const DB_TBL_SALES_RETURN_BUSINESS_TAX_PROFILE_MAPPING = "sales_return_request_business_tax_profile_mapping";
      const DB_TBL_SALES_RETURN_REQUEST_EXTRA_CHARGES = "sales_return_request_extra_charges";
      const DB_TBL_SALES_RETURN_EXTRA_DISCOUNT = "sales_return_request_extra_discount";
      const DB_TBL_SALES_RETURN_REQUEST_ITEM_PUT_AWAY_LOG = "sales_return_request_item_put_away_log";

      /**
       * Service Tables
       */
      const DB_TBL_SERVICE_ASSIGNMENT = "service_assignment";
      const DB_TBL_SERVICE_EXECUTION = "service_execution";

      /**
       * Cost Analysis
       */
      const DB_TBL_COST_ANALYSIS = "cost_analysis";
      const DB_TBL_COST_ANALYSIS_ASSIGNMENT = "cost_analysis_assignment";
      const DB_TBL_COST_ANALYSIS_ISSUE = "cost_analysis_issue";
      const DB_TBL_COST_ANALYSIS_ISSUE_MAIN = "cost_analysis_issue_main";
      const DB_TBL_COST_ANALYSIS_RECEIVE = "cost_analysis_receive";

      /**
       * Workorder service template
       */
      const DB_TBL_WORKORDER_SERVICE_TEMPLATE = "workorder_service_template";
      /*
         * Appointment Talbes
         */
      const DB_TBL_APPOINTMENT = "appointment";
      const DB_TBL_APPOINTMENT_SERVICES = "appointment_services";
      const DB_TBL_APPOINTMENT_STATUS = "appointment_status";
      const DB_TBL_APPOINTMENT_LOG = "appointment_log";

      /*
         * Service Package
         */
      const DB_TBL_SERVICE_PACKAGE = "service_package";
      const DB_TBL_SERVICE_PACKAGE_SERVICE = "service_package_service";
      const DB_TBL_SERVICE_PACKAGE_PURCHASE = "service_package_purchase";
      const DB_TBL_SERVICE_PACKAGE_PURCHASE_CONSUMPTION = "service_package_consumption";
      const DB_TBL_PACKAGE_EXTRA_CHARGES = "package_extra_charges";

      /**
       * DISPATCH ORDER
       */
      const DB_TBL_DISPATCH_ORDER = "dispatch_order";
      const DB_TBL_DISPATCH_ORDER_ITEM = 'dispatch_order_item';
      const DB_TBL_DISPATCH_ORDER_ITEM_PALLET = "dispatch_order_item_pallet";

      /*         * bLOCK sQUARING* */
      const DB_TBL_FACTORY_MANUFACTURE = "factory_manfacture";
      const DB_TBL_FACTORY_MANUFACTURE_SOURCE_ITEM = "factory_manfacture_source_item";
      const DB_TBL_FACTORY_MANUFACTURE_TARGET_ITEM = "factory_manfacture_target_item";

      /**
       * Cash discount profile table 
       */
      const DB_TBL_CASH_DISCOUNT_PROFILE = "cash_discount_profile";
      const DB_TBL_CUSTOMER_CASH_DISCOUNT_PROFILE_MAPPING = "customer_cash_discount_profile_mapping";
      const DB_TBL_VENDOR_CASH_DISCOUNT_PROFILE_MAPPING = "vendor_cash_discount_profile_mapping";

      /**
       * Location transfer log table
       */
      const DB_TBL_LOCATION_TRANSFER_LOG = "location_transfer_log";

      /**
       * FCM server key table
       */
      const DB_TBL_FCM_SERVER_KEY = "fcm_server_key";

      /*
         * Item Profiling tables
         */
      const DB_TBL_ITEM_PROFILING = "item_profiling";
      const DB_TBL_ITEM_PROFILING_ITEM = "item_profiling_item";
      const DB_TBL_ITEM_PROFILING_ATTRIBUTE_FILTER = "item_profiling_attribute_filter";
      const DB_TBL_ITEM_PROFILING_TEMPLATE = "item_profiling_template";

      /**
       * Item Profiling tables
       */
      const DB_TBL_CUSTOMER_ITEM_PROFILING = "customer_item_profiling";

      /**
       * Sales Order Table Filter
       */
      const DB_TBL_SALES_ORDER_TABLE_FILTER = "sales_order_table_filter";

      /**
       * Item Wise Sales Order Table Filter
       */
      const DB_TBL_ITEM_WISE_SALES_TABLE_FILTER = "item_wise_sales_table_filter";
      const DB_TBL_ITEM_WISE_SALES_TABLE = "item_wise_sales_table";

      /*
         * Cost Category
         */
      const DB_TBL_COST_CATEGORY = "cost_category";

      /**
       * Slurry Process tables
       */
      const DB_TBL_SLURRY_BALLMILLS = "ball_mill";

      /*
         * Cost Center
         */
      const DB_TBL_COST_CENTER = "cost_center";

      /**
       * Incentive Freeze
       */
      const DB_TBL_INCENTIVE_FREEZE = "incentive_freeze";
      const DB_TBL_INCENTIVE_FREEZE_INVOICES = "incentive_freeze_invoices";
      const DB_TBL_INCENTIVE_FREEZE_CREDIT_NOTES = "incentive_freeze_credit_notes";

      /**
       * Stock Statement Attribute Filter
       */
      const DB_TBL_STOCK_STATEMENT_ATTRIBUTE_FILTER = "stock_statement_attribute_filter";

      /**
       * Collection Ageing Filter
       */
      const DB_TBL_COLLECTION_AGEING_FILTER = "collection_ageing_filter";

      /*
         * Stock Ageing Filter
         */
      const DB_TBL_STOCK_AGEING_FILTER = "stock_ageing_filter";

      /*
         * Expiring Item Ageing Filter
         */
      const DB_TBL_EXPIRING_ITEM_AGEING_FILTER = "expiring_item_ageing_filter";

      /**
       * Payment Ageing Filter
       */
      const DB_TBL_PAYMENT_AGEING_FILTER = "payment_ageing_filter";

      /**
       * Item Wise Sales Order Table Filter
       */
      const DB_TBL_ITEM_WISE_PURCHASE_TABLE_FILTER = "item_wise_purchase_table_filter";
      const DB_TBL_ITEM_WISE_PURCHASE_TABLE = "item_wise_purchase_table";

      /**
       * Training
       */
      const DB_TBL_TRAINING_VIDEO = "training_video";
      const DB_TBL_TRAINING_VIDEO_TAG = "training_video_tag";
      const DB_TBL_TRAINING_CATEGORY = "training_category";
      const DB_TBL_TRAINING_CATEGORY_ROLE = "training_category_role";

      /**
       * Driver
       */
      const DB_TBL_DRIVER = "driver";

      /**
       * Transporter
       */
      const DB_TBL_TRANSPORTER = 'transporter';
      const DB_TBL_TRANSPORTER_COMPANY_STATIC = 'transporter_company_static';

      /**
       * Vehicle
       */
      const DB_TBL_VEHICLE = 'vehicle';
      const DB_TBL_VEHICLE_TYPES = 'vehicle_types';

      /**
       * Walk in
       */
      const DB_TBL_WALKIN = 'walkin';
      const DB_TBL_WALKIN_FEEDBACK = 'walkin_feedback';

      /*         * *
         * Application Type
         */
      const DB_TBL_APPLICATION_TYPE = 'application_type';

      /**
       * Pallet Type
       */
      const DB_TBL_PALLET_TYPE = 'pallet_type';
      const DB_TBL_PALLET_TYPE_ATTRIBUTE_VALUE_MAPPING = 'pallet_type_attribute_value_mapping';

      /**
       * Price Profiling
       */
      const DB_TBL_PRICE_PROIFILING = 'price_profiling';

      /**
       * Payment Planner Table
       */
      const DB_TBL_GROUP_PAYMENT_PLANNER = "group_payment_planner";

      /**
       * POS Receipt
       */
      const DB_TBL_POS_RECEIPT = 'pos_receipt';
      const DB_TBL_POS_RECEIPT_INVOICES = "pos_receipt_invoices";
      const DB_TBL_POS_RECEIPT_PAYMENT_MODE = "pos_receipt_payment_mode";
      const DB_TBL_POS_RECEIPT_VOUCHER = "pos_receipt_voucher";
      const DB_TBL_POS_RECEIPT_CASH_VOUCHER = "pos_receipt_cash_voucher";

      /**
       * E -Invoicing
       */
      const DB_TBL_EINVOICING_TOKEN = 'einvoicing_token';
      const DB_TBL_EINVOICING = 'einvoicing';

      /**
       * TCS          
       */
      const DB_TBL_TCS_CATEGORY = 'tcs_category';
      const DB_TBL_TCS_COLLECTION = 'tcs_collection';

      /**
       * Bank Linking
       */
      const DB_TBL_BANK = 'bank';
      const DB_TBL_VIRTUAL_ACCOUNT_CODE = 'virtual_account_code';

      /**
       * HDFC
       */
      const DB_TBL_ECOLLECTION_HDFC = 'ecollection_hdfc';
      const DB_TBL_HDFC_LOG = 'hdfc_log';

      /**
       * ICICI
       */
      const DB_TBL_ECOLLECTION_ICICI = 'ecollection_icici';
      const DB_TBL_ICICI_LOG = 'icici_log';

      /**
       * Kotak
       */
      const DB_TBL_ECOLLECTION_KOTAK = 'ecollection_kotak';
      const DB_TBL_KOTAK_LOG = 'kotak_log';

      /**
       * Barcode Log
       */
      const DB_TBL_BARCODE_LOG = 'barcode_log';

      /**
       * Campaign
       */
      const DB_TBL_CAMPAIGN = 'campaign';

      /**
       * JobWork
       */
      const DB_TBL_JOBWORK = 'jobwork';
      const DB_TBL_JOBWORK_STATUS = 'jobwork_status';
      const DB_TBL_JOBWORK_ACTIVITY = 'jobwork_activity';
      const DB_TBL_JOBWORK_ITEM = 'jobwork_item';
      const DB_TBL_JOBWORK_ITEM_ACTIVITY = 'jobwork_item_activity';
      const DB_TBL_JOBWORK_ITEM_ACTIVITY_SERVICE = 'jobwork_item_activity_service';
      const DB_TBL_JOBWORK_ITEM_ACTIVITY_RAW_MATERIAL = 'jobwork_item_activity_raw_material';
      const DB_TBL_JOBWORK_BUSINESS_TAX_PROFILE_MAPPING = 'jobwork_business_tax_profile_mapping';
      const DB_TBL_JOBWORK_EXTRA_CHARGES = "jobwork_extra_charges";
      const DB_TBL_JOBWORK_EXTRA_DISCOUNT = "jobwork_extra_discount";
      const DB_TBL_JOBWORK_PROGRESS_LOG = 'jobwork_progress_log';

      /**
       * Job Order
       */
      const DB_TBL_JOBORDER = "joborder";
      const DB_TBL_JOBORDER_ITEM = "joborder_item";
      const DB_TBL_JOBORDER_STATUS = "joborder_status";
      const DB_TBL_JOBORDER_RECEIVE_ITEM_LOG = "joborder_receive_item_log";
      const DB_TBL_JOBORDER_INDENT = "joborder_indent";
      const DB_TBL_JOBORDER_INDENT_STATUS = "joborder_indent_status";
      const DB_TBL_JOBORDER_ITEM_RAW_MATERIAL = "joborder_item_raw_material";
      const DB_TBL_JOBORDER_ITEM_BATCH = "job_order_item_batch";
      const DB_TBL_JOBORDER_ITEM_RAW_MATERIAL_FILE_UPLOAD = "joborder_item_raw_material_file_upload";

      /**
       * To Do
       */
      const DB_TBL_TODO_TASK = 'todo_task';
      const DB_TBL_TODO_TASK_ACTIVITY = 'todo_task_activity';
      const DB_TBL_TODO_TASK_ACTIVITY_MAPPING = 'todo_task_activity_mapping';
      const DB_TBL_TODO_TASK_CATEGORY = 'todo_task_category';
      const DB_TBL_TODO_TASK_CATEGORY_MAPPING = 'todo_task_category_mapping';

      /**
       * Collection FOllowup
       */
      const DB_TBL_COLLECTION_FOLLOWUP = 'collection_followup';
      const DB_TBL_COLLECTION_FOLLOWUP_ON_HOLD = 'collection_followup_on_hold';
      const DB_TBL_COLLECTION_FOLLOWUP_ON_HOLD_REASON = 'collection_followup_on_hold_reason';
      const DB_TBL_COLLECTION_FOLLOWUP_PROMISE_PAY = 'collection_followup_promise_pay';

      /**
       * TRuck Movement
       */
      const DB_TBL_TRUCK_MOVEMENT = 'truck_movement';

      /**
       * Customer Price
       */
      const DB_TBL_CUSTOMER_PRICE_CLASS = 'customer_price_class';
      const DB_TBL_CUSTOMER_PRICE_CLASS_MAPPING = 'customer_price_class_mapping';
      const DB_TBL_CUSTOMER_PRICE_GROUP_MAPPING = 'customer_price_group_mapping';
      const DB_TBL_CUSTOMER_PRICE_PROFILING = 'customer_price_profiling';

      /**
       * Variation Price Group
       */
      const DB_TBL_VARIATION_PRICE_GROUP = 'variation_price_group';
      const DB_TBL_VARIATION_PRICE_GROUP_PRICE_LIST = 'variation_price_group_price_list';
      const DB_TBL_VARIATION_PRICE_GROUP_PRICE_LIST_ITEMS = 'variation_price_group_price_list_items';
      const DB_TBL_VARIATION_PRICE_GROUP_MAPPING = 'variation_price_group_mapping';

      /**
       * Branch Voucher
       */
      const DB_TBL_BRANCH_VOUCHER = 'branch_voucher';
      const DB_TBL_BRANCH_VOUCHER_TRANSACTION = 'branch_voucher_transaction';

      /**
       * Print Template
       */
      const DB_TBL_PRINT_TEMPLATE = 'print_template';
      const DB_TBL_PRINT_TEMPLATE_GROUP = 'print_template_group';
      const DB_TBL_PRINT_TEMPLATE_VARIABLES = 'print_template_variables';
      const DB_TBL_PRINT_TEMPLATE_ATTRIBUTE_MAPPING = 'print_template_attribute_mapping';
      const DB_TBL_PRINT_TEMPLATE_TYPE_VARIABLE_MAPPING = 'print_template_type_variable_mapping';
      const DB_TBL_PRINT_TEMPLATE_OUTLET_MAPPING = 'print_template_outlet_mapping';
      const DB_TBL_PRINT_TEMPLATE_ROLE_MAPPING = 'print_template_role_mapping';

      /**
       * Bucket
       */
      const DB_TBL_BUCKET = 'bucket';

      /**
       * Pick List
       */
      const DB_TBL_PICKLIST = 'picklist';
      const DB_TBL_PICKLIST_ITEMS = 'picklist_items';
      const DB_TBL_PICKLIST_STATUS = 'picklist_status';

      /**
       * Customer Feedback Template
       */
      const DB_TBL_CUSTOMER_FEEDBACK_TEMPLATE = "customer_feedback_template";
      const DB_TBL_CUSTOMER_FEEDBACK_TEMPLATE_ATTRIBUTES = "customer_feedback_template_attributes";
      const DB_TBL_CUSTOMER_FEEDBACK = "customer_feedback";
      const DB_TBL_CUSTOMER_FEEDBACK_ATTRIBUTE_VALUE = "customer_feedback_attribute_value";

      /**
       * Terms Condition Static
       */
      const DB_TBL_TERMS_CONDITIONS_STATIC = "terms_conditions_static";
      const DB_TBL_TERMS_CONDITIONS_STATIC_VALUE = "terms_conditions_static_value";
      const DB_TBL_TERMS_CONDITIONS_STATIC_COMPANY_MAPPING = "terms_conditions_static_company_mapping";
      const DB_TBL_TERMS_CONDITIONS_STATIC_VALUE_MAPPING = "terms_conditions_static_value_mapping";

      /**
       * Item Wise Custom Field Static
       */
      const DB_TBL_CUSTOM_ITEM_FIELD_STATIC = "custom_item_field_static";
      const DB_TBL_CUSTOM_ITEM_FIELD_STATIC_VALUE = "custom_item_field_static_value";
      const DB_TBL_CUSTOM_ITEM_FIELD_STATIC_COMPANY_MAPPING = "custom_item_field_static_company_mapping";
      const DB_TBL_CUSTOM_ITEM_FIELD_STATIC_VALUE_MAPPING = "custom_item_field_static_value_mapping";

      /**
       * Employye
       */
      const DB_TBL_EMPLOYEE = "employee";
      const DB_TBL_EMPLOYEE_MOBILE = "employee_mobile";
      const DB_TBL_EMPLOYEE_FILES = "employee_files";

      /**
       * RequestCallBack
       */
      const DB_TBL_REQUEST_CALLBACK = "request_callback";

      /**
       * Temporary Text
       */
      const DB_TBL_TEMPORARY_TEST = "temporary_text";

      /**
       * Helpdesk Call Log
       */
      const DB_TBL_HELP_DESK_CALL_LOG = "help_desk_call_log";

      /**
       * ShipRocket
       */
      const DB_TBL_SHIPROCKET_CREDENTIALS = "shiprocket_credentials";
      const DB_TBL_SHIPROCKET_API_LOG = "shiprocket_api_log";

      /*
         * Indent - For forwarding purchase
         */
      const DB_TBL_INDENT = "indent";
      const DB_TBL_INDENT_ITEMS = "indent_items";
      const DB_TBL_INDENT_TERM = "indent_term";
      const DB_TBL_INDENT_TERM_VALUE = "indent_term_value";
      const DB_TBL_INDENT_TERM_VALUE_MAPPING = "indent_term_value_mapping";

      /**
       * FranchiseSalesInvoiceReport
       */
      const DB_TBL_FRANCHISE_SALES_INVOICE_REPORT = "franchise_sales_invoice_report";

      /**
       * EmailDomain
       */
      const DB_TBL_EMAIL_DOMAIN = "email_domain";

      /**
       * Franchise
       */
      const DB_TBL_FRANCHISE = "franchise";
      const DB_TBL_FRANCHISE_STORE_IMAGES = "franchise_store_images";

      /**
       * Ledger 
       */
      const DB_TBL_BANK_CVS_CONFIG = "bank_csv_config";

      /**
       * Exotel 
       */
      const DB_TBL_EXOTEL_CREDENTIALS = "exotel_credentials";

      /**
       * Biometric Service Provider 
       */
      const DB_TBL_BIOMETRIC_SERVICE_PROVIDER_TYPE_STATIC = "biometric_service_provider_type_static";
      const DB_TBL_BIOMETRIC_SERVICE_PROVIDER = "biometric_service_provider";

      /**
          CustomerDiscountMapping
       */
      const DB_TBL_VARIATION_PRICE_GROUP_PRICE_CUSTOMER_CATEGORY_MAPPING = "variation_price_group_price_customer_category_mapping";

      /**
       * CustomerTypeDiscountMapping
       */
      const DB_TBL_VARIATION_PRICE_GROUP_PRICE_CUSTOMER_TYPE_CATEGORY_MAPPING = "variation_price_group_price_customer_type_category_mapping";

      /**
       * Print Template Item Columns
       */
      const DB_TBL_PRINT_TEMPLATE_ITEM_COLUMNS = "print_template_item_columns";
      const DB_TBL_PRINT_TEMPLATE_ITEM_COLUMNS_MAPPING = "print_template_type_item_column_mapping";
      const DB_TBL_PRINT_TEMPLATE_RAW_ITEM_COLUMNS_MAPPING = "print_template_type_raw_item_column_mapping";
      const DB_TBL_PRINT_TEMPLATE_EXTRA_FIELDS_MAPPING = "print_template_type_extra_fields_mapping";
      const DB_TBL_PRINT_TEMPLATE_EXTRA_FIELDS = 'print_template_extra_fields';

      /**
       *  Intergration Leads Indiamart Credentials
       */
      const DB_TBL_INTERGRATION_LEADS_INDIAMART_CREDENTIALS = 'intergration_leads_indiamart_credentials';

      /**
       * ATTRIBUTE WISE PRODUCTION RATE
       */
      const DB_TBL_ATTRIBUTE_WISE_PRODUCTION_RATE = 'attribute_wise_production_rate';

      /**
       * credit request reason static
       */
      const DB_TBL_CREDIT_REQUEST_REASON_STATIC = 'credit_request_reason_static';

      /**
       * Sales Category Type

       */
      const DB_TBL_SALES_CATEGORY_TYPE = 'sales_category_type';

      /**
       * Purchase Category Type
       */
      const DB_TBL_PURCHASE_CATEGORY_TYPE = 'purchase_category_type';

      /**
       * Customer Custom Fields Values

       */
      const DB_TBL_CUSTOMER_CUSTOM_FIELDS_VALUES = 'customer_custom_fields_values';

      /**
       * Customer Custom Fields

       */
      const DB_TBL_CUSTOMER_CUSTOM_FIELDS = 'customer_custom_fields';

      /**
       * Customer Custom Fields Mapping

       */
      const DB_TBL_CUSTOMER_CUSTOM_FIELDS_MAPPING = 'customer_custom_fields_mapping';

      /**
       *  Automation Type Company Mapping
       */
      const DB_TBL_AUTOMATION_TYPE_COMPANY_MAPPING = 'automation_type_company_mapping';

      /**
       *  Automation Type
       */
      const DB_TBL_AUTOMATION_TYPE = 'automation_type';

      /**
       * Email Template triggers
       */
      const DB_TBL_EMAIL_TEMPLATE_TRIGGERS = 'email_template_triggers';
      const DB_TBL_SMS_TEMPLATE_TRIGGERS = 'sms_template_triggers';

      /**
       * Consignment Item File

       */
      const DB_TBL_CONSIGNMENT_ITEM_FILE = 'consignment_item_file';

      /**
       * Purchase Order Debit Note File
       */
      const DB_TBL_PURCHASE_ORDER_DEBIT_NOTE_FILE = 'purchase_order_debit_note_file';

      /**
       * KYC

       */
      const DB_TBL_KYC = 'kyc';

      /**
       * Customer KYC Details
       */
      const DB_TBL_CUSTOMER_KYC_DETAILS = 'customer_kyc_details';

      /**
       * Customer Limit Unlock Log
       */
      const DB_TBL_CUSTOMER_LIMIT_UNLOCK_LOG = 'customer_limit_unlock_log';

      /**
       * Promocode
       */
      const DB_TBL_PROMOCODE = 'promocode';

      /**
       * Promocode Sticker
       */
      const DB_TBL_PROMOCODE_STICKER = 'promocode_sticker';

      /**
       * Report Generator
       */
      const DB_TBL_REPORT_GENERATOR = 'report_generator';
      const DB_TBL_REPORT_GENERATOR_COLUMNS = 'report_generator_columns';

      /**
       * Document Series
       */
      const DB_TBL_DOCUMENT_SERIES = 'document_series';

      /**
       * Shopify Credentials
       */
      const DB_TBL_SHOPIFY_CREDENTIALS = 'shopify_credentials';

      /**
       * outcome
       */
      const DB_TBL_OUTCOME = 'followup_outcome';

      /**
       * Package Box Dimensions
       */
      const DB_TBL_PACKAGE_BOX_DIMENSIONS = 'package_box_dimensions';

      /**
       * purchase order item ready log
       */
      const DB_TBL_PURCHASE_ORDER_ITEM_READY_LOG = 'purchase_order_item_ready_log';

      /**
       * purchase order item Planned 
       */
      const DB_TBL_PURCHASE_ORDER_ITEM_PLANNED_LOG = 'purchase_order_item_planned_log';
      const DB_TBL_PURCHASE_ORDER_ITEM_PLANNED = 'purchase_order_item_planned';
      const DB_TBL_PURCHASE_ORDER_ITEM_PLANNED_EXTRA_CHARGES_DETAILS = 'purchase_order_item_planned_extra_charges_details';

      /**
       * Purchase Order Item Dispatched
       */
      const DB_TBL_PURCHASE_ORDER_ITEM_DISPATCHED_LOG = 'purchase_order_item_dispatched_log';
      const DB_TBL_PURCHASE_ORDER_ITEM_DISPATCHED_EXTRA_CHARGES_DETAILS = 'purchase_order_item_dispatched_extra_charges_details';
      const DB_TBL_PURCHASE_ORDER_ITEM_DISPATCHED = 'purchase_order_item_dispatched';
      const DB_TBL_PURCHASE_ORDER_ITEM_STATUS = 'purchase_order_item_status';

      /**
       * Acknowledgement Voucher
       */
      const DB_TBL_ACKNOWLEDGEMENT_VOUCHER = 'acknowledgement_voucher';
      const DB_TBL_ACKNOWLEDGEMENT_VOUCHER_INVOICES = 'acknowledgement_voucher_invoices';
      const DB_TBL_ACKNOWLEDGEMENT_VOUCHER_DEBIT_NOTES = 'acknowledgement_voucher_debit_notes';

      /**
       * CUSTOMER_BRAND_CATEGORY_DISCOUNT
       */
      const DB_TBL_CUSTOMER_BRAND_CATEGORY_DISCOUNT = 'customer_brand_category_discount';

      /**
       * Batch item code
       */
      const DB_TBL_BATCH_ITEM_CODE = "batch_item_code";
      const DB_TBL_BATCH_ITEM_CODE_ISVID_MAPPING = "batch_item_code_isvid_mapping";

      /**
       * Damaged Reason
       */
      const DB_TBL_DAMAGED_REASON = 'damaged_reason';

      /**
       * CUSTOMER_BRAND_CATEGORY_DISCOUNT
       */
      const DB_TBL_REJECTED_REASON = 'rejected_reason';
      const DB_TBL_LIQUIDATION_MOVEMENT_REPORT = 'liquidation_movement_report';
      const DB_TBL_ACCOUNT_LEDGER_USER_WISE_BUDGET = 'account_ledger_user_wise_budget';
      const DB_TBL_ACCOUNT_LEDGER_BUDGET_LIMIT = 'account_ledger_budget_limit';
      const DB_TBL_VENDOR_SUPPORT_RATE_TABLE = 'vendor_support_rate';

      /**
       * Vehicle Report
       */
      const DB_TBL_VEHICLE_REPORT = 'vehicle_report';
      const DB_TBL_INVENTORY_SET_VARIATIONS_RATE_VARIANCE = "inventory_set_variations_rate_variance";
      const DB_TBL_INVENTORY_SET_VARIATIONS_RATE_VARIANCE_LOG = "inventory_set_variations_rate_variance_log";
      const DB_TBL_INVOICE_HSN_DESCRIPTION = "invoice_hsn_description";
      const DB_TBL_INVOICE_CUSTOM_CONTAINER_ITEM = "invoice_custom_container_item";
      const DB_TBL_INVOICE_COMMERCIAL_CONTAINER_ITEM = "invoice_commercial_container_item";

      /**
       * Training Series
       */
      const DB_TBL_TRAINING_SERIES = 'training_series';

      /**
       * Assets Issue 
       */
      const DB_TBL_ASSETS_ISSUE = 'assets_issue';
      const DB_TBL_ASSETS_ISSUE_ITEM = 'assets_issue_item';
      const DB_TBL_ASSETS_RECEIVE = 'assets_receive';
      const DB_TBL_ASSETS_RECEIVE_ITEM = 'assets_receive_item';

      /**
       * Account Voucher Cash deposit slip
       */
      const DB_TBL_ACCOUNT_VOUCHER_CASH_DEPOSIT_SLIP = 'account_voucher_cash_deposit_slip';
      const DB_TBL_ACCOUNT_VOUCHER_CASH_DEPOSIT_SLIP_DETAILS = 'account_voucher_cash_deposit_slip_details';

      /**
       * Whatsapp
       */
      const DB_TBL_WHATSAPP_PROVIDER = "whatsapp_provider";
      const DB_TBL_WHATSAPP_TEMPLATE = "whatsapp_template";
      const DB_TBL_WHATSAPP_TEMPLATE_TYPE = "whatsapp_template_type";
      const DB_TBL_WHATSAPP_TEMPLATE_TYPE_PLACEHOLDER_MAPPING = "whatsapp_template_type_placeholder_mapping";
      const DB_TBL_WHATSAPP_TEMPLATE_PLACEHOLDER = "whatsapp_template_placeholder";

      /**
       * User Wise Target
       */
      const DB_TBL_AUSER_WISE_TARGET = "auser_wise_target";

      /**
       * Service Request
       */
      const DB_TBL_SERVICE_REQUEST = "service_request";
      const DB_TBL_SERVICE_REQUEST_ITEM = "service_request_item";
      const DB_TBL_SERVICE_REQUEST_TYPE = "service_request_type";
      const DB_TBL_SERVICE_REQUEST_STATUS = "service_request_status";
      const DB_TBL_SERVICE_REQUEST_ITEM_STATUS = "service_request_item_status";
      const DB_TBL_SERVICE_REQUEST_SCHEDULE_VISIT = "service_request_schedule_visit";
      const DB_TBL_SERVICE_REQUEST_SCHEDULE_PICKUP = "service_request_schedule_pickup";
      const DB_TBL_SERVICE_REQUEST_TIMMING_SLOT = "service_request_timming_slot";
      const DB_TBL_SERVICE_REQUEST_VISIT_RESOLVE = "service_request_visit_resolve";
      const DB_TBL_SERVICE_REQUEST_REPAIR_MATERIAL = "service_request_repair_material";
      const DB_TBL_SERVICE_REQUEST_REPAIR_SERVICE = "service_request_repair_service";
      const DB_TBL_SERVICE_REQUEST_ESTIMATION = "service_request_estimation";
      const DB_TBL_SERVICE_REQUEST_ESTIMATION_ITEM = "service_request_estimation_item";
      const DB_TBL_SERVICE_REQUEST_RESOLUTION_STATUS = "service_request_resolution_status";
      const DB_TBL_SERVICE_REQUEST_ITEM_LOG = "service_request_item_log";

      /**
       * Ice Load
       */
      const DB_TBL_ICE_LOAD = "ice_load";

      /**
       * Sales return consignment
       */
      const DB_TBL_SALES_RETURN_CONSIGNMENT = "sales_return_consignment";
      const DB_TBL_SALES_RETURN_CONSIGNMENT_ITEM = "sales_return_consignment_item";

      /**
       * Annual Maintance
       */
      const DB_TBL_ANNUAL_MAINTENANCE_CONTRACT = "annual_maintenance_contract";
      const DB_TBL_BATCH_WISE_STOCK_VERIFICATION = "batch_wise_stock_verification";
      const DB_TBL_BATCH_WISE_STOCK_VERIFICATION_ITEM = "batch_wise_stock_verification_item";
      const DB_TBL_BATCH_WISE_STOCK_VERIFICATION_ACTUAL_STOCK = "batch_wise_stock_verification_actual_stock";
      const DB_TBL_VENDOR_GSTIN = "vendor_gstin";
      const DB_TBL_TRAINING_VIDEO_QUESTIONS = "training_video_questions";
      const DB_TBL_TRAINING_VIDEO_QUESTION_OPTIONS = "training_video_question_options";
      const DB_TBL_CHECKPOINT_ORDER_DELIVERY_DATE = "checkpoint_order_item_delivery_date";

      /**
       * Progressive Discount
       */
      const DB_TBL_PROGRESSIVE_DISCOUNT = 'progressive_discount';
      const DB_TBL_CUSTOMER_CATEGORY_PROGRESSIVE_DISCOUNT = 'customer_category_progressive_discount';

      /**
       * Invoice Incentive Exclusion
       */
      const DB_TBL_INVOICE_INCENTIVE_EXCLUSION = 'invoice_incentive_exclusion';
      const DB_TBL_INVOICE_INCENTIVE_INCLUSION = 'invoice_incentive_inclusion';

      /**
       * Contract Pricing
       */
      const DB_TBL_CONTRACT_PRICING = 'contract_pricing';

      /**
       * Route
       */
      const DB_TBL_ROUTE = "routes";
      const DB_TBL_ROUTE_PINCODE_MAPPING = "route_pincode_mapping";

      /**
       * Warehouse Custom Valuation
       */
      const DB_TBL_WAREHOUSE_CUSTOM_VALUATION = "warehouse_custom_valuation";

      /**
       * Special Discount Approval
       */
      const DB_TBL_SPECIAL_DISCOUNT_APPROVAL = 'special_discount_approval';
      const DB_TBL_SPECIAL_DISCOUNT_APPROVAL_LOG = 'special_discount_approval_log';

      /**
       * Frames
       */
      const DB_TBL_FRAMES = 'frames';

      /**
       * Panel
       */
      const DB_TBL_PANEL_CODE = 'panel_code';

      /**
       * Invoice Referral Points
       */
      const DB_TBL_INVOICE_REFERRAL_POINTS = 'invoice_referral_points';

      /**
       * Inventory Set purchase profiling discount
       */
      const DB_TBL_INVENTORY_SET_VARIATIONS_PURCHASE_PROFILING_DISCOUNT = 'inventory_set_variations_purchase_profiling_discount';

      /**
       * Invoice Referral Points
       */
      const DB_TBL_ROOM_TYPE = 'room_type';
      const DB_TBL_ROOM_TYPE_CATEGORY = 'room_type_categories';

      /**
       * CHECKPOINT ORDER RATE DIFFRENCE ITEM LOG
       */
      const DB_TBL_CHECKPOINT_ORDER_RATE_DIFFRENCE_ITEM_LOG = 'checkpoint_order_rate_diffrence_item_log';
      const DB_TBL_INFLUENCER_FIRM = 'influencer_firm';

      /**
       * size wise extra quantity
       */
      const DB_TBL_SIZE_WISE_EXTRA_QUANTITY = "size_wise_extra_quantity";
      const DB_TBL_SIZE_WISE_EXTRA_QUANTITY_TYPE = "size_wise_extra_quantity_type";

      /*
         * Video table
         */
      const DB_TBL_VIDEO = "video";

      /*
         * inspiration_gallery
         */
      const DB_TBL_INSPIRATION_GALLERY = "inspiration_gallery";
      const DB_TBL_INSPIRATION_GALLERY_ITEMS = "inspiration_gallery_items";
      const DB_TBL_INSPIRATION_GALLERY_MEDIA = "inspiration_gallery_media";
      const DB_TBL_INSPIRATION_CATEGORY = "inspiration_category";

      /**
       * eCom Management
       */
      const DB_TBL_ECOM_OFFERS = 'ecom_offers';
      const DB_TBL_ECOM_OFFERS_ITEM = 'ecom_offers_item';
      const DB_TBL_DELIVERY_COVERAGE = 'delivery_coverage';
      const DB_TBL_DELIVERY_COVERAGE_PINCODE_LIST = 'delivery_coverage_pincode_list';
      const DB_TBL_DELIVERY_COVERAGE_STATE = 'delivery_coverage_state';
      const DB_TBL_KNOWLEDGE_CENTRE = 'knowledge_centre';
      const DB_TBL_KNOWLEDGE_CATEGOARY = 'knowledge_category';

      /**
       * insta reels
       */
      const DB_TBL_INSTAGRAM_REELS = "instagram_reels";

      /**
       * warehouse_issue_stock_item_purchase_invoice_log
       */
      const DB_TBL_WAREHOUSE_ISSUE_STOCK_ITEM_PURCHASE_INVOICE_LOG = "warehouse_issue_stock_item_purchase_invoice_log";

      /**
       * Payment gateway
       */
      const DB_TBL_PAYMENT_GATEWAY = "payment_gateway";
      const DB_TBL_PAYMENT_GATEWAY_TYPE = "payment_gateway_type";

      /**
       * ERP form
       */
      const DB_TBL_ERP_FORM = "erp_form";
      const DB_TBL_ERP_FORM_COLUMNS = "erp_form_column";
      const DB_TBL_ERP_FORM_COLUMNS_MAPPING = "erp_form_columns_mapping";

      /**
       * Prototype Management Module
       */
      const DB_TBL_PROTOTYPE_MANAGEMENT = "prototype_management";
      const DB_TBL_PROTOTYPE_MANAGEMENT_FILES = "prototype_management_files";

      /**
       * Inventory Set Variations Ecom
       */
      const DB_TBL_INVENTORY_SET_VARIATIONS_ECOM = "inventory_set_variations_ecom";

      /*
         * Bachup Files
         */
      const DB_TBL_BACKUP_FILES = "backup_files";

      /**
       * Variable Configurations
       */
      const DB_TBL_ECOM_VARIABLES = "ecom_variables";
      const DB_TBL_ECOM_VARIABLE_COMPANY_MAPPING = "ecom_variable_company_mapping";

      /**
       * Item Reviews
       */
      const DB_TBL_ECOM_ITEM_REVIEWS = "ecom_item_reviews";
      const DB_TBL_ECOM_ITEM_REVIEWS_ATTACHEMENTS = "ecom_item_reviews_attachments";

      /**
       * Application Area
       */
      const DB_TBL_APPLICATION_AREA = "application_area";
      const DB_TBL_APPLICATION_AREA_SUBTYPE = "application_area_sub_type";
      const DB_TBL_APPLICATION_AREA_MAPPING = "application_area_mapping";
      const DB_TBL_VARIATION_APPLICATION_MAPPING = "variation_application_mapping";

      /**
       * Item Architect Images Mapping
       */
      const DB_TBL_ITEM_ARCHITECT_IMAGE_MAPPING = "item_architect_image_mapping";
      /*
         * Module Report
         */
      const DB_TBL_REPORT_TABLE = "report_table";
      const DB_TBL_REPORT_TABLE_COLUMN = "report_table_column";

      /**
       * Influencer
       */
      const DB_TBL_INFLUENCER_DEPARTMENT = "influencer_department";
      const DB_TBL_INFLUENCER_DESIGNATION = "influencer_designation";
      const DB_TBL_TARGET_INCENTIVE_FORM = "target_incentive_form";

      /**
       * Franch Sales Purchase Report
       */
      const DB_TBL_FRANCHISE_SALES_PURCHASE = "franchise_sales_purchase";

      /**
       * NOTIFICATION CONFIGURATION
       */
      const DB_TBL_NOTIFICATION_CONFIGURATION = "notification_configuration";
      const DB_TBL_NOTIFICATION_CONFIGURATION_COMPANY_MAPPING = "notification_configuration_company_mapping";

      /**
       * Cart
       */
      const DB_TBL_CART = "cart";
      const DB_TBL_CART_ITEM = "cart_item";

      /**
       *  Automation Extra Fields
       */
      const DB_TBL_AUTOMATION_EXTRA_FIELDS = 'automation_extra_fields';

      /*
         * Sanity Test
         */
      const DB_TBL_SANITY_TEST = "sanity_test";
      const DB_TBL_SANITY_TEST_RESULT = "sanity_test_result";

      /**
       * Quick Commerce Table
       */
      const QUICK_COMMERCE_INVOICE_DATA = "quick_commerce_invoice_data";

      /**
       *  Dashboard Widget Static
       */
      const DB_TBL_DASHBOARD_WIDGET_STATIC = "dashboard_widget_static";
      const DB_TBL_DASHBOARD_WIDGET_TYPE_STATIC = "dashboard_widget_type_static";
      const DB_TBL_DASHBOARD_WIDGET_USER_MAPPING = "dashboard_widget_user_mapping";
      const DB_TBL_DASHBOARD_GRAPH = "dashboard_graph";
      const DB_TBL_DASHBOARD_GRAPH_TYPE = "dashboard_graph_type";

      /**
       * dashboard dashboard tables
       */
      const DB_TBL_DASHBOARD_LAYOUT = "dashboard_dashboard";
      const DB_TBL_DASHBOARD_ELEMENT = "dashboard_element";
      const DB_TBL_DASHBOARD_COUNTER_OPTIONS = "dashboard_counter_options";
      const DB_TBL_DASHBOARD_LINK_OPTIONS = "dashboard_link_options";
      const DB_TBL_DASHBOARD_PERMISSIONS = "dashboard_permissions";
      const DB_TBL_DASHBOARD_USER_LINKS = "dashboard_user_links";
      const DB_TBL_DASHBOARD_ELEMENT_TYPE = "dashboard_element_type";
      const DB_TBL_DASHBOARD_ELEMENT_CATEGORY = "dashboard_element_category";
      const DB_TBL_DASHBOARD_ELEMENT_COLOR_PROFILE = "dashboard_element_color_profile";

      /**
       * Brand Wise Discount
       */
      const DB_TBL_BRAND_WISE_DISCOUNT = "brand_wise_discount";
}

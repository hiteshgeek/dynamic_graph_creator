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
      const DB_TBL_USER = "auser";
      const DB_TBL_USER_COMPANY_MAPPING = "auser_company_mapping";
      const DB_TBL_LICENCE = "licence";
      const DB_TBL_LICENCE_DOMAIN = "licence_domain";
      const DB_TBL_LICENCE_COMPANIES = "licence_companies";
      const DB_TBL_MASK_CONFIG = "mask_config";
      const DB_TBL_LICENCE_MASK_CONFIG_MAPPING = "licence_mask_config_mapping";
      const DB_TBL_OUTLET = "outlet";
      const DB_TBL_OUTLET_BANK = "outlet_bank";
      const DB_TBL_OUTLET_USER_MAPPING = "outlet_user_mapping";
      const DB_TBL_USER_SESSION = "auser_session";

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

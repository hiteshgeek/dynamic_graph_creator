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

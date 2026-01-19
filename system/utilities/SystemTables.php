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

      /**
       * Graph Management Tables
       */
      const DB_TBL_GRAPH = "graph";
      const DB_TBL_GRAPH_STATUS = "graph_status";

      /**
       * DataFilter Management Tables
       */
      const DB_TBL_DATA_FILTER = "data_filter";
      const DB_TBL_DATA_FILTER_STATUS = "data_filter_status";

      /**
       * Dashboard Builder Tables
       */
      const DB_TBL_DASHBOARD_TEMPLATE_CATEGORY = "dashboard_template_category";
      const DB_TBL_DASHBOARD_TEMPLATE = "dashboard_template";
      const DB_TBL_DASHBOARD_INSTANCE = "dashboard_instance";

}

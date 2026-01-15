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
       * Filter Management Tables
       */
      const DB_TBL_FILTER = "filter";
      const DB_TBL_FILTER_STATUS = "filter_status";

      /**
       * Layout Builder Tables
       */
      const DB_TBL_LAYOUT_TEMPLATE = "layout_template";
      const DB_TBL_LAYOUT_INSTANCE = "layout_instance";

}

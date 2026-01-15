
<?php

/*
     * Click nbfs://nbhost/SystemFileSystem/Templates/Licenses/license-default.txt to change this license
     * Click nbfs://nbhost/SystemFileSystem/Templates/Scripting/EmptyPHP.php to edit this template
     *
     * Created by: Anish
     */

$url = Rapidkart::getInstance()->getURL();
$theme = Rapidkart::getInstance()->getThemeRegistry();

//key binding files
$theme->addScript(SystemConfig::scriptsUrl() . "key_binding/key_binding.js");
$theme->addCss(SystemConfig::stylesUrl() . "key_binding/key_binding.css");

//error handling files
$theme->addScript(SystemConfig::scriptsUrl() . "error_handling/debug_control.js");
$theme->addScript(SystemConfig::scriptsUrl() . "error_handling/error_handling.js");
$theme->addCss(SystemConfig::stylesUrl() . "error_handling/error_handling.css");

$theme->addCss(SystemConfig::stylesUrl() . "dashboard/dashboard_builder.css");

$url[1] = isset($url[1]) ? $url[1] : 'main_dashboard'; //sales
//    ini_set('display_errors', true);
if (isset($_POST['submit'])) {
      switch ($_POST['submit']) {
            case "edit_dashboard_name":
                  editNewDashboard($_POST);
                  break;
            case "create_new_dashboard":
                  createNewDashboard($_POST);
                  break;
            case "get_dashboard_user_link_ids":
                  $user_links = getDashboardUserLinkIds();

                  Utility::ajaxResponseTrue("", $user_links);
                  break;
            case "delete_dashboard":
                  deleteDashboard($_POST);
                  break;
            case "save_dashboard_links":
                  saveDashboardLinks($_POST);
                  break;
            case "save_dashboard_permissions":
                  saveDashboardPermissions($_POST);
                  break;
            case "save_dashboard":
                  saveDashboard($_POST);
                  break;
            case "dashboard-render":
                  dashboard_render($_POST);
                  break;
            case "sales-dashboard-render":
                  sales_dashboard_render($_POST);
                  break;
            case "item-dashboard-sales-settings":
                  item_dashboard_sales_setting($_POST);
                  break;
            case "dashboard-permission-data":
                  $dashboard_permission_data = DashboardPermissionManager::getAllPermissions();
                  $dashboards = DashboardManager::getAllDashboards();
                  $roles = RoleManager::loadRolesObjectArray();

                  $data = [
                        "permissions" => $dashboard_permission_data,
                        "roles" => $roles,
                        "dashboards" => $dashboards
                  ];

                  Utility::ajaxResponseTrue("", $data);
                  exit;
            case "dashboard-item-data":
                  $element_types = DashboardManager::getDashboardElementTypes();
                  $element_categories = DashboardManager::getDashboardElementCategories([3]);
                  $elements = DashboardManager::getDashboardElements([3]);

                  $sales_graph_data = [
                        'element_types' => $element_types,
                        'element_categories' => $element_categories,
                        'elements' => $elements,
                  ];

                  echo json_encode($sales_graph_data);
                  exit;
                  break;
            case "dashboard-graph-data":
                  $element_types = DashboardManager::getDashboardElementTypes();
                  $element_categories = DashboardManager::getDashboardElementCategories();
                  $elements = DashboardManager::getDashboardElements([1, 2]);

                  $sales_graph_data = [
                        'element_types' => $element_types,
                        'element_categories' => $element_categories,
                        'elements' => $elements,
                  ];

                  echo json_encode($sales_graph_data);
                  exit;
                  break;
            case "dashboard-sales-submit":
                  dashboard_sales_submit($_POST);
                  break;
            case "edit_report_info":
                  edit_report_info($_POST);
                  break;
            case "change_report_info_sequence":
                  change_report_info_sequence($_POST);
                  break;
            case "save_report_table":
                  save_report_table($_POST);
                  break;
      }
}

switch ($url[1]) {
      // case "move_counters":
      //     move_counters();
      //     break;
      // case "move_links":
      //     move_links();
      //     break;
      case "saved-graphs":
            $theme->setContent("full_main", saved_graphs());
      case "graph-creator":
            $theme->setContent("full_main", graph_creator());
            break;
      case "main_dashboard":
            $theme->setContent("full_main", main_dashboard());
            break;
      case "report_info_render":
            render_report_info_table();
            break;
      case "report_info":
            if (!isset($url[2])) {
                  ScreenMessage::setMessage("The url you entered is not valid", ScreenMessage::MESSAGE_TYPE_INFO);
                  System::redirectInternal("home");
            }
            get_report_info_form($url[2]);
            break;
      case "sales":
            $theme->setContent("full_main", sales_dashboard());
            break;
      case "purchase":
            $theme->setContent("full_main", purchase_dashboard());
            break;
      case "new":

            $query = "";

            $theme->setContent("full_main", new_dashboard());
            break;
      default:

            if (is_numeric($url[1])) {

                  if (count($url) > 2) {
                        switch ($url[2]) {
                              case "load":
                                    getDashboardDashboard($url[1]);
                                    break;
                        }
                  }

                  //dashboard builder files

                  $theme->setContent("full_main", new_dashboard());
                  break;
            }

            ScreenMessage::setMessage("The url you entered is not valid", ScreenMessage::MESSAGE_TYPE_INFO);
            System::redirectInternal("home");
            break;
}

function render_report_info_table()
{
      $table_id = "";
      // $table_id = $_GET['table_id'];
      $cond = [];
      $cond[] = '1';
      // $cond[] = "datatable_id = " . $table_id;
      // $cond[] = "(licid = '" . BaseConfig::$licence_id . "' OR licid IS NULL or licid <=0)";
      // if (isset($_GET['filter_preference_modules'])) {
      //     $module_ids = $_GET['filter_preference_modules'];
      //     $module_ids = is_array($module_ids) ? $module_ids : [$module_ids];
      //     $module_cond = [];
      //     foreach ($module_ids as $module_id) {
      //         $module_cond[] = "FIND_IN_SET($module_id, module_ids) > 0";
      //     }
      //     $cond[] = "(" . implode(" AND ", $module_cond) . ")";
      // }
      // if (isset($_GET['filter_spcid'])) {
      //     $cond[] = "spcid = " . $_GET['filter_spcid'];
      // }
      // if (isset($_GET['filter_is_verified'])) {
      //     $cond[] = "verified_flag = " . $_GET['filter_is_verified'];
      // }

      $cond = implode(" AND ", $cond);

      /*
          DataTable $table,
          $table_name,
          $primary_key,
          $cols = array(),
          $condition,
          $get,
          $group_by = null,
          $having_condition = null,
          $order_by = null,
          $api = FALSE,
          $report_db = false
         */

      DataTable::tableRender(get_report_info_table($table_id), "report_table_view", 'rtcid', $_GET['columns'], $cond, $_GET);
}

function save_report_table($data)
{
      $db = Rapidkart::getInstance()->getDB();

      $sql = "
        UPDATE " . SystemTables::DB_TBL_REPORT_TABLE . " SET
            title = '::title',
            description = '::description'
        WHERE
            rtid = '::rtid'
            ";
      $args = array(
            "::title" => $data['title'],
            "::description" => $data['description'],
            "::rtid" => $data['report_table_id']
      );

      $res = $db->query($sql, $args);
      if (!$res) {
            Utility::ajaxResponseFalse("Fail to update report table");
      }

      get_report_info_form($data['table_id'], "Report data updated");
}

function change_report_info_sequence($data)
{
      $db = Rapidkart::getInstance()->getDB();
      $db->autoCommit(false);
      if (isset($data['changed_sequences']) && is_array($data['changed_sequences']) && !empty($data['changed_sequences'])) {
            foreach ($data['changed_sequences'] as $key => $changed_sequence_data) {
                  $id = $changed_sequence_data['row_id'];
                  $new_sequence = $changed_sequence_data['new_sequence'];

                  $report_table_column = new ReportTableColumn($id);
                  $report_table_column->setSequence($new_sequence);

                  if (!$report_table_column->update()) {
                        $db->rollBack();
                        $db->autoCommit(true);
                        break;
                        Utility::ajaxResponseFalse("Fail to update report table");
                  }
            }
      }
      $db->commit();
      $db->autoCommit(true);

      get_report_info_form($data['table_id'], "Report data updated");
}

function edit_report_info($data)
{
      $db = Rapidkart::getInstance()->getDB();

      $sql = "
        UPDATE " . SystemTables::DB_TBL_REPORT_TABLE_COLUMN . " SET
            title = '::title',
            description = '::description'
        WHERE
            rtcid = '::rtcid'
            ";
      $args = array(
            "::title" => $data['title'],
            "::description" => $data['description'],
            "::rtcid" => $data['id']
      );

      $res = $db->query($sql, $args);
      if (!$res) {
            Utility::ajaxResponseFalse("Fail to update report table");
      }

      get_report_info_form($data['table_id'], "Report data updated");
}

function get_report_info_form($table_id, $message = "")
{
      ReportTableManager::getReportTableView();

      // $table = get_report_info_table($table_id);
      // $form = new GenericForm('report_info_table');

      $tpl = new Template(SystemConfig::templatesPath() . "report_table/report_table_view");

      $report_table_data = ReportTableManager::getReportTableData($table_id);

      $tpl->table_id = $table_id;

      $tpl->can_edit = in_array(intval(BaseConfig::$company_id), [232, 147, 241]);

      $tpl->report_table_ob = $report_table_data['report_table_ob'];
      $tpl->report_table_column_ob = $report_table_data['report_table_column_ob'];

      $panel = new Panel('', 'fa fa-info', 'bg-teal report_table_wrapper', 'Table Data Information', '', true);
      $panel->setCustomHtml($tpl->parse());
      Utility::ajaxResponseTrue($message, $panel->publish());
}

function get_report_info_table($table_id = "")
{
      $table = new DataTable();
      $table->setTableId("report_info_table");
      $columns = array();
      $columns[] = array('name' => 'datatable_column_id', 'title' => 'Column ID', 'description' => 'Column ID');
      $columns[] = array('name' => 'title', 'title' => 'Column Title', 'description' => 'Column Title');
      $columns[] = array('name' => 'description', 'title' => 'Column Description', 'description' => 'Column Description');
      $columns[] = array('name' => 'sequence', 'title' => 'Sequence', 'description' => 'Column Sequence');

      $table->setDefaultColumns($columns);
      $table->setColumns($columns);
      $table->setIfDetails(FALSE);
      $table->setIfTask(FALSE);
      $table->setIfSerial(TRUE);
      $table->setIfExportable(TRUE);
      $table->setIfHeader(TRUE);
      $table->setIfFooter(FALSE);
      $table->setRealtimeUrl(Jpath::fullUrl("dashboard/report_info_render"));

      $table->setRealtimeCallback("redrawReportInfoTable");
      $table->setIfRealtimeExtraProperty(TRUE);
      $table->setRealTimeExtraProperty(array());

      $table->setIfRealtime(TRUE);
      $table->setIfAction(TRUE);

      $action_buttons = array();

      $action_buttons[] = array(
            'title' => 'Edit Record',
            'icon' => 'fa fa-edit',
            'class' => 'get-report-info-form',
            'show' => True,
            'action_id' => array(
                  array('key' => 'datatable_column_id', 'value' => 'datatable_column_id'),
                  array('key' => 'title', 'value' => 'title'),
                  array('key' => 'description', 'value' => 'description'),
                  array('key' => 'sequence', 'value' => 'sequence'),
            )
      );

      $table->setActionButtons($action_buttons);
      $table->setExtra(array(
            'datatable_column_id' => 'datatable_column_id',
            'title' => 'title',
            'description' => 'description',
            'sequence' => 'sequence',
      ));

      return $table;
}

function saved_graphs()
{
      global $theme;

      require_once SystemConfig::librariesPath() . 'graph-creator/api/config/database.php';
      require_once SystemConfig::librariesPath() . 'graph-creator/classes/GraphConfig.php';
      require_once SystemConfig::librariesPath() . 'graph-creator/dashboard-graph.php';

      $theme->addScript(SystemConfig::scriptsUrl() . "graph-creator/echarts.min.js");

      $theme->addScript(SystemConfig::scriptsUrl() . "dashboard/dashboard-saved-graphs.js"); //IIFE

      $tpl = new Template(SystemConfig::templatesPath() . "dashboard/saved-graphs");
}

function graph_creator()
{
      global $theme;

      // $theme->addCss(SystemConfig::stylesUrl() . "graph-creator/github.min.css");
      // $theme->addCss(SystemConfig::stylesUrl() . "graph-creator/github-dark.min.css");

      $theme->addScript(SystemConfig::scriptsUrl() . "graph-creator/echarts.min.js");

      $theme->addScript(SystemConfig::scriptsUrl() . "graph-creator/highlight.min.js");
      $theme->addScript(SystemConfig::scriptsUrl() . "graph-creator/sql.min.js");

      $theme->addCss(SystemConfig::stylesUrl() . "graph-creator/graph-creator.css");

      $theme->addScript(SystemConfig::scriptsUrl() . "graph-creator/graph-creator.iife.js"); //IIFE
      $theme->addCss(SystemConfig::stylesUrl() . "graph-creator/graph-creator.css");
      $theme->addScript(SystemConfig::scriptsUrl() . "graph-creator/highlight.min.js");

      $theme->addScript(SystemConfig::scriptsUrl() . "dashboard/dashboard-graph-creator.iife.js"); //IIFE
      $theme->addCss(SystemConfig::stylesUrl() . "dashboard/dashboard-graph-creator.css");

      $tpl = new Template(SystemConfig::templatesPath() . "dashboard/graph-creator");

      return $tpl->parse();
}

function main_dashboard()
{
      global $theme;
      $theme->addScript(SystemConfig::scriptsUrl() . "dashboard/main_dashboard.js");
      $theme->addCss(SystemConfig::stylesUrl() . "dashboard/main_dashboard.css");

      $tpl = new Template(SystemConfig::templatesPath() . "dashboard/main-dashboard");

      $user = SystemConfig::getUser();
      $tpl->is_admin = false;

      // hprint($permitted_dashboards);

      if ($user->getIsAdmin()) {
            $dashboads_list = DashboardManager::getAllDashboards();
            $tpl->is_admin = true;
      } else {
            $roles = [];

            $user_roles = $user->getRoles();

            foreach ($user_roles as $role_id => $role_name) {
                  $roles[] = $role_id;
            }

            $dashboad_ids = DashboardPermissionManager::getUserPermittedDashbards($roles);

            $dashboads_list = DashboardManager::getUserDashboards($dashboad_ids);
      }

      $user_link_types = DashboardUserLinkManager::getUserDashboardLinks($user->getId());

      $tpl->user_link_types = $user_link_types;
      $tpl->dashboads_list = $dashboads_list;

      return $tpl->parse();
}

function sales_dashboard()
{
      global $theme;
      $theme->addScript(SystemConfig::scriptsUrl() . "dashboard/sales_dashboard.js");

      $tpl = new Template(SystemConfig::templatesPath() . "dashboard/sales-dashboard");
      $sales = DashboardManager::getUserDashboard(1);
      $tpl->sales = $sales;
      return $tpl->parse();
}

function new_dashboard()
{
      global $theme;

      $theme->addScript(SystemConfig::scriptsUrl() . "dashboard/dashboard.js");
      $theme->addScript(SystemConfig::scriptsUrl() . "dashboard/dashboard_permission.js");
      $theme->addCss(SystemConfig::stylesUrl() . "dashboard/dashboard_permission.css");
      $theme->addCss(SystemConfig::stylesUrl() . "dashboard/counter.css");

      $theme->addScript(SystemConfig::scriptsUrl() . "dashboard/dashboard_builder.js");

      $tpl = new Template(SystemConfig::templatesPath() . "dashboard/new-dashboard");

      return $tpl->parse();
}

function purchase_dashboard()
{
      $tpl = new Template(SystemConfig::templatesPath() . "dashboard/purchase-dashboard");
      return $tpl->parse();
}

function dashboard_render($data)
{
      DashboardManager::getDashboardData($data);
}

function sales_dashboard_render($data)
{
      DashboardManager::getSalesDashboardData($data);
}

function item_dashboard_sales_setting($data)
{
      $graphs = DashboardManager::getDashboardGraphs(1);
      $mappings = DashboardManager::getUserDashboardGraphMapping();
      $form = new GenericForm('sales-dashboard-form');
      $form->setSubmitCallback('salesDashboardFormSubmit');

      $form_combo = new FormComboTableV2('combo');
      $form_combo->addHeaderElem('#', 0, 0, 0);
      $form_combo->addHeaderElem('#', 0, 0, 0);
      $form_combo->addHeaderElem('#', 3, 3, 3);
      $form_combo->addHeaderElem('Graph', 3, 3, 3);
      $form_combo->addHeaderElem('Width', 3, 3, 3);
      $form_combo->setAction(false);

      if ($graphs) {
            foreach ($graphs as $graph) {
                  $selected = 0;
                  $width = '';
                  $selected_id = 0;
                  if (isset($mappings[$graph->dagrid])) {
                        $selected = 1;
                        $selected_id = $mappings[$graph->dagrid]->dagrumid;
                        $width = $mappings[$graph->dagrid]->width;
                  }
                  $input = new FormInputBox('graph_' . $graph->dagrid);
                  $input->setVal($graph->dagrid);
                  $input->setType('hidden');
                  $form_combo->addChild($input->publishXml());

                  $input = new FormInputBox('dagrumid_' . $graph->dagrid);
                  $input->setVal($selected_id);
                  $input->setType('hidden');
                  $form_combo->addChild($input->publishXml());

                  $checkbox = new FormCheckBoxGroup('graph_checkbox_' . $graph->dagrid, 'graph_checkbox_' . $graph->dagrid);
                  $checkbox->addItem(1, '&nbsp;', 1 == $selected);
                  $form_combo->addChild($checkbox->publishXml());

                  $p = new FormDisplayHtml('p');
                  $p->setValue($graph->name);
                  $form_combo->addChild($p->publishXml());

                  $input = new FormInputBox('graph_width_' . $graph->dagrid);
                  $input->setVal($width);
                  $form_combo->addChild($input->publishXml());
            }
      }
      $form->addChild($form_combo->publishXml());

      $btn = new FormBtn('submit', 'dashboard-sales-submit', 'Submit');
      $btn->setCntlClass('m-t-10');
      $form->addChild($btn->publishXml());

      $panel = new Panel('dashboard-sales-wrapper', 'fa fa-list', 'bg-teal', 'Sales Dashboard', '', true);
      $panel->setCustomHtml($form->publish());
      Utility::ajaxResponseTrue("", $panel->publish());
}

function dashboard_sales_submit($data)
{
      $user_mapping = DashboardManager::getUserDashboard(1);
      $db = Rapidkart::getInstance()->getDB();
      $db->autoCommit(false);
      if (isset($data['combo']) && is_array($data['combo']) && !empty($data['combo'])) {
            foreach ($data['combo'] as $combo) {
                  $dagrid = $combo['graph'];
                  if (isset($combo['graph_checkbox']) && is_array($combo['graph_checkbox']) && !empty($combo['graph_checkbox']) && $combo['graph_checkbox'][0] == 1) {
                        if (isset($combo['dagrumid']) && $combo['dagrumid'] > 0) {
                              $user_mapping_obj = new DashboardGraphUserMapping($combo['dagrumid']);
                              $user_mapping_obj->setWidth($combo['graph_width']);
                              if (!$user_mapping_obj->update()) {
                                    $db->rollBack();
                                    $db->autoCommit(true);
                                    Utility::ajaxResponseFalse("Fail to update user mapping");
                              }
                              if (isset($user_mapping[$dagrid])) {
                                    unset($user_mapping[$dagrid]);
                              }
                        } else {
                              $user_mapping_obj = new DashboardGraphUserMapping();
                              $user_mapping_obj->setDagrid($combo['graph']);
                              $user_mapping_obj->setUid(Session::loggedInUid());
                              $user_mapping_obj->setWidth($combo['graph_width']);
                              if (!$user_mapping_obj->insert()) {
                                    $db->rollBack();
                                    $db->autoCommit(true);
                                    Utility::ajaxResponseFalse("Fail to insert user mapping");
                              }
                        }
                  }
            }
      }
      if (!empty($user_mapping) && !empty($user_mapping)) {
            foreach ($user_mapping as $user_r) {
                  if (!DashboardGraphUserMapping::delete($user_r->dagrumid)) {
                        $db->rollBack();
                        $db->autoCommit(true);
                        Utility::ajaxResponseFalse("Fail to delete user mapping");
                  }
            }
      }
      $db->commit();
      Utility::ajaxResponseTrue("Dashboard Sales Settings Updated Successfully");
}

function getDashboardDashboard($id)
{
      $dashboard_dashboard = new DashboardDashboard($id);
      if (!$dashboard_dashboard->getId()) {
            Utility::ajaxResponseFalse("Dashboard not found");
      }

      Utility::ajaxResponseTrue("Dashboard loaded successfully", $dashboard_dashboard->toArray());
}

function editNewDashboard($data)
{
      $id = $data['id'];
      $user = SystemConfig::getUser();
      $dashboard_dashboard = new DashboardDashboard($id);

      if (!$dashboard_dashboard->getId()) {
            Utility::ajaxResponseFalse("Dashboard not found");
      }

      $dashboard_dashboard->setName($data['name']);

      if (!$dashboard_dashboard->update()) {
            Utility::ajaxResponseFalse("Failed to update new dashboard");
      } else {
            Utility::ajaxResponseTrue("Dashboard updated successfully", ["name" => $dashboard_dashboard->getName()]);
      }
}

function createNewDashboard($data)
{
      $default_dashboard = [
            [
                  "columnWrappers" => [
                        [
                              "dashboardCol" => 2,
                              "columns" => [[]]
                        ],
                        [
                              "dashboardCol" => 2,
                              "columns" => [[]]
                        ]
                  ],
                  "heightMultiplier" => 3
            ]
      ];

      $user = SystemConfig::getUser();
      $dashboard_dashboard = new DashboardDashboard();
      $dashboard_dashboard->setName($data['name']);
      $dashboard_dashboard->setDescription('');
      $dashboard_dashboard->setData(json_encode($default_dashboard));
      $dashboard_dashboard->setCompanyId($user->getCompanyId());

      if (!$dashboard_dashboard->insert()) {
            Utility::ajaxResponseFalse("Failed to create new dashboard");
      } else {
            Utility::ajaxResponseTrue("Dashboard created successfully", ["new_dashboard_id" => $dashboard_dashboard->getId()]);
      }
}

function getDashboardUserLinkIds()
{
      $user = SystemConfig::getUser();
      $company_id = $user->getCompanyId();
      $uid = $user->getId();

      return DashboardUserLinkManager::getUserDashboardLinkIds($uid, $company_id);
}

function deleteDashboard($data)
{
      $dlid = isset($data['id']) ? intval($data['id']) : 0;

      DashboardDashboard::delete($dlid);

      Utility::ajaxResponseTrue("Dashboard deleted successfully");
}

function saveDashboardLinks($data)
{
      $user = SystemConfig::getUser();
      $company_id = $user->getCompanyId();
      $uid = $user->getId();

      $passed = 0;
      $failed = 0;

      $ids = [];

      if (isset($data["links"]) && count($data["links"])) {
            foreach ($data["links"] as $daelid) {
                  if (DashboardUserLink::isAlreadyExists($daelid, $uid, $company_id)) {
                        $find_id = DashboardUserLink::getDashboardUserLinkId($daelid, $uid, $company_id);
                        if ($find_id) {
                              $ids[] = $find_id;
                              continue;
                        }
                        continue;
                  }

                  $dashboard_user_link = new DashboardUserLink();
                  $dashboard_user_link->setDaelid($daelid);
                  $dashboard_user_link->setUid($uid);
                  $dashboard_user_link->setCompanyId($company_id);
                  $dashboard_user_link->setCreatedUid($user->getId());

                  if (!$dashboard_user_link->insert()) {
                        $failed++;
                  } else {
                        $passed++;
                        $ids[] = $dashboard_user_link->getId();
                  }
            }
      }

      DashboardUserLink::deleteOldUserLinks($ids, $company_id, $uid);

      $main_dashboard = main_dashboard();

      if ($failed > 0) {
            Utility::ajaxResponseFalse("Some links could not be saved. Passed: $passed, Failed: $failed", $main_dashboard);
      } else {
            Utility::ajaxResponseTrue("Dashboard links updated", $main_dashboard);
      }
}

function saveDashboardPermissions($data)
{
      $user = SystemConfig::getUser();
      $company_id = $user->getCompanyId();

      $passed = 0;
      $failed = 0;

      $old_permissions = DashboardPermissionManager::getAllPermissions();

      $ids = [];

      foreach ($data["permissions"] as $permission) {
            $dashboard_id = $permission['dashboard_id'];
            $role_id = $permission['role_id'];

            if (DashboardPermission::isAlreadyExists($dashboard_id, $role_id, $company_id)) {
                  $find_id = DashboardPermission::getDashboardPermissionId($dashboard_id, $role_id, $company_id);
                  if ($find_id) {
                        $ids[] = $find_id;
                        continue;
                  }
                  continue;
            }

            $dashboard_permission = new DashboardPermission();
            $dashboard_permission->setDlid($dashboard_id);
            $dashboard_permission->setRid($role_id);
            $dashboard_permission->setCompanyId($company_id);
            $dashboard_permission->setCreatedUid($user->getId());

            if (!$dashboard_permission->insert()) {
                  $failed++;
            } else {
                  $passed++;
                  $ids[] = $dashboard_permission->getId();
            }
      }

      DashboardPermission::deleteOldPermissions($ids, $company_id);

      if ($failed > 0) {
            Utility::ajaxResponseFalse("Some permissions could not be saved. Passed: $passed, Failed: $failed");
      } else {
            Utility::ajaxResponseTrue("All permissions saved successfully. Total: $passed");
      }
}

function saveDashboard($data)
{
      $is_edit = false;
      if (isset($data['id']) && !empty($data['id'])) {
            $is_edit = true;
            $dashboard_dashboard = new DashboardDashboard($data['id']);
      } else {
            $dashboard_dashboard = new DashboardDashboard();
      }

      $dashboard_dashboard->setName($data['name']);
      $dashboard_dashboard->setDescription('');
      $dashboard_dashboard->setData($data['data']);
      $dashboard_dashboard->setCompanyId(BaseConfig::$company_id);

      if ($is_edit) {
            if (!$dashboard_dashboard->update()) {
                  Utility::ajaxResponseFalse("Failed to save dashboard");
            }
      } else {
            if (!$dashboard_dashboard->insert()) {
                  Utility::ajaxResponseFalse("Failed to save dashboard");
            }
      }

      Utility::ajaxResponseTrue("Dashboard saved successfully", ["id" => $dashboard_dashboard->getId()]);
}

// function move_links()
// {
//     $db = Rapidkart::getInstance()->getDB();

//     $query = "SELECT * FROM rapidkart_factory_static.module_task WHERE motasid = 1 AND url IS NOT NULL AND lower(url) !='null' AND url != '' AND name != ''";

//     $result = $db->query($query);

//     //print all records one by one 

//     if ($result && $db->resultNumRows($result) > 0) {
//         while ($row = $db->fetchObject($result)) {

//             $dashboard_element = new DashboardElement();
//             $dashboard_element->setDaeltypid(1); //static counter type
//             $dashboard_element->setName($row->name);
//             $dashboard_element->setDescription($row->description);
//             $dashboard_element->setDaelcatid(3); //counter category

//             $dashboard_element->insert();

//             $daelid = $db->lastInsertId();

//             $options = new DashboardLinkOptions();

//             $options->setDaelid($daelid);
//             $options->setLinkUrl($row->url);
//             $options->setIcon($row->icon);
//             $options->setTextColor($row->color);
//             $options->setIsDirectLink($row->motatid);
//             $options->insert();
//         }
//     }
// }

// function move_counters()
// {
//     $db = Rapidkart::getInstance()->getDB();

//     $query = "SELECT * FROM dashboard_widget_static WHERE dawitid = 2";

//     $result = $db->query($query);

//     //print all records one by one 

//     if ($result && $db->resultNumRows($result) > 0) {
//         while ($row = $db->fetchObject($result)) {
//             $dashboard_element = new DashboardElement();
//             $dashboard_element->setDaeltypid(1); //static counter type
//             $dashboard_element->setName($row->name);
//             $dashboard_element->setDescription($row->description);
//             $dashboard_element->setThumbnail("counter_1.png");
//             $dashboard_element->setCallback($row->callback);
//             $dashboard_element->setDaelcatid(2); //counter category

//             $dashboard_element->insert();
//         }
//     }
// }


// Get filters from query params
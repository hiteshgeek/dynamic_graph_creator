<?php

/**
 * This file is the controller for the entire system. 
 * It loads and runs all the necessary system objects and handles core system functionalities.
 * 
 * @author Sohil Gupta
 * @since 20140616
 */
$url = Rapidkart::getInstance()->getURL();
$theme = Rapidkart::getInstance()->getThemeRegistry();

header('Access-Control-Allow-Origin: *');
header("Access-Control-Allow-Methods: GET, POST, OPTIONS, PUT, DELETE");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, X-Requested-With");
// setup
$domain_name = (isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : NULL);

setlocale(LC_MONETARY, 'en_IN');
date_default_timezone_set('Asia/Kolkata');
ini_set("display_errors", false);
if (BaseConfig::DB_SERVER == 'developerdb.sixorbit.com') {
      ini_set('display_errors', true);
}

$licence = LicenceManager::checkDomainExists($domain_name);
if (!$licence) {
      header('Location: https://www.sixorbit.com/?page_id=56');
      exit;
}

if (trim($url[0]) != 'service' && trim($url[0]) != 'biometric' && trim($url[0]) != 'invoice_service' && trim($url[0]) != 'invoice_service_new') {
      if ($licence->getSSLStatus() == 1 && $_SERVER['REQUEST_SCHEME'] == 'http') {
            $redirect = "https://" . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
            header("Location:$redirect");
      } else if ($licence->getSSLStatus() != 1 && $_SERVER['REQUEST_SCHEME'] == 'https') {
            $redirect = "http://" . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
            header("Location:$redirect");
      }
}
// baseconfig settings
BaseConfig::$licence_id = $licence->getId();
LicenceManager::setCustomizedData($licence);
BaseConfig::$domain_name = SystemConfig::protocol() . $domain_name;
BaseConfig::$if_customized = $licence->getIfCustomized() ? TRUE : FALSE;
//    WhatsAppLogManager::sendWhatsAppMessageThroughGreenApi();
//    exit;

if (BaseConfig::$if_customized) {
      BaseConfig::$customization_box = $licence->getCustomizationBox();
}
if ((BaseConfig::$if_customized) && BaseConfig::DB_SERVER != '192.168.1.34') {
      require_once BaseConfig::SITE_PATH . BaseConfig::$customization_box . '/custom/bootstrap.inc.php';
}

include_once BaseConfig::SITE_PATH . BaseConfig::SITE_FOLDER . '/api/phpqrcode/qrlib.php';

// Database connection
//    $inventory = new Inventory(4202);
//    $variation = new InventorySetVariation($inventory->getIsvid());
//    $inventory->setSellingPrice($inventory->getSellingPrice()+ 100);
//    $p = InventoryManager::getDecideTaxDisplay($variation, $inventory->getSellingPrice());
//    hprint($p);
//    
//    $pp = $p - $inventory->getPrice();
//    $pp = ($pp /$inventory->getPrice()) *100;
//    hprint($pp);
//    
//    exit;
/* if (!isset($_POST['submit']) || !isset($_GET["columns"]))
      {
      // MaskConfig
      $mask_mappings = LicenceManager::getMaskMapping($licence->getId());
      if ($mask_mappings)
      {
      global $masking;
      $masking = $mask_mappings;
      }

      /*
     * Adding notification plugins
     */
$theme->addScript('https://www.gstatic.com/firebasejs/8.2.9/firebase-app.js');
$theme->addScript('https://www.gstatic.com/firebasejs/8.2.9/firebase-messaging.js');

//permissions files
$theme->addScript(SystemConfig::scriptsUrl() . "admin_user/permission.js");
$theme->addCss(SystemConfig::stylesUrl() . "admin_user/permission.css");

//system preferences files
$theme->addScript(SystemConfig::scriptsUrl() . "system_preferences/preferences.js");
$theme->addCss(SystemConfig::stylesUrl() . "system_preferences/preferences.css");

//datatabale report files

$theme->addScript(SystemConfig::scriptsUrl() . "report_table/report_table.js");
$theme->addCss(SystemConfig::stylesUrl() . "report_table/report_table.css");

//notification files
$theme->addScript(SystemConfig::scriptsUrl() . "followup_timeline/followup_timeline.js");
$theme->addCss(SystemConfig::stylesUrl() . "followup_timeline/followup_timeline.css");

//Module Approval files
$theme->addCss(SystemConfig::stylesUrl() . "module/module.css");

/* If in specific module check for permissions */

$modules = array("followup", "vendor", "customer", "workorder", "jobcard", "tax", "testimonial", "product", "frontend", "socialwidget", "purchase", "company", "brand", "showcase", "sitevariables", "admin_user", "accounting", "lead", "distributor", "ticket", "category", "measurement", "warehouse", "enquiry", "chkorder", "quotation", "invoice", "account", "credit-note", "debit-note", "order", "claim", "my-warehouse", "consignment", "hrm", "payroll", "mom", "user_issue", "tracker", "daily-movement-tracking", "projectmanagement", "services", "invoice_reports", 'pos_receipt', 'campaign', "payment_planner", 'todo', "sales_return_request", "walkin", "assets_issue", "service_request", "joborder", 'sticker', 'system-wizard');
$exclude_modules = array("settings", "activity", "stock", "tds", "payment", "cost_analysis", 'department', 'template', 'preview', "dispatch-againt-consignment-render");

if (isset($url[1]) && $url[1] === "stock" && isset($url[2])) {
      if (!isset($_POST['submit'])) {
            switch ($url[2]) {
                  case "conversion":
                  case "request":
                        unset($exclude_modules[2]);
                        break;
            }
      }
}
$perm = hasPermission(getPermission($url, isset($_POST['submit']) ? explode('-', $_POST['submit']) : array()));

if ($url[0] == 'upload_customer' && isset($_GET['filename'])) {

      echo (CustomerManager::updateCustomerPriceGroupMapping($_GET['filename']));
      exit();
}
if ($url[0] == 'upload_class' && isset($_GET['filename'])) {
      echo (CustomerManager::updateClassPriceGroupMapping($_GET['filename']));
      exit();
}

if (in_array($url[0], $modules, true) && !$perm && (isset($url[1]) && count($exclude_modules) > 0 && !in_array($url[1], $exclude_modules, TRUE))) {
      if (!isset($_POST['submit'])) {
            if (isset($_GET["columns"])) {
                  Utility::ajaxResponseFalse("You don't have the permission to access this resources....");
            } else {

                  ScreenMessage::setMessage("You don't have the permission to access this resources");
                  System::redirectInternal("403");
            }
      }
}


if (!Session::isLoggedIn(true)) {
      if ((isset($_POST['stgid']) && isset($_POST['auth_token'])) || (isset($_GET['stgid']))) {

            include_once 'biometric/biometric.inc.php';
            exit();
      }

      switch ($url[0]) {
            case "banklink":
                  include_once 'banklink/banklink.inc.php';
                  break;
            case "send_alert_message_for_enquiry_schedule_to_assigned_user":
                  send_alert_message_for_enquiry_schedule_to_assigned_user();
                  System::redirectInternal("home");
                  break;
            case "attendance":
                  include_once 'attendance/attendance.inc.php';
                  exit;
                  break;
            case "login":
                  include_once 'login.inc.php';
                  break;
            case "customer_registration":
                  include_once 'customer_registration.inc.php';
                  break;
            case "customer_feedback":
                  include_once 'customer_feedback/customer_feedback.inc.php';
                  break;
            case "client_verification":
                  include_once 'client_verification.inc.php';
                  break;
            case "invoice_service":

                  include_once 'invoice_service/invoice_service.inc.php';
                  exit();
            case "invoice_service_new":

                  include_once 'invoice_service/invoice_service_new.inc.php';
                  exit();
            case "service":
                  if (BaseConfig::$if_customized) {
                        require_once BaseConfig::SITE_PATH . BaseConfig::$customization_box . '/custom/bootstrap.inc.php';
                  }

                  include_once 'service/service.inc.php';
                  exit();
            case "customer_service":

                  exit();
            case "service_api_call":
                  include_once 'service_api_call/service_api_call.inc.php';
                  exit();
            case "table-column":

                  include_once 'table_column.php';
                  exit();
            case "biometric":
                  include_once 'biometric/biometric.inc.php';
                  exit();
            case "webhook":
                  include_once 'webhook/webhook.inc.php';
                  exit();
            case "lock":
            default:
                  if (Session::isUserLocked()) {
                        include_once 'lock.inc.php';
                  } else {

                        include_once 'login.inc.php';
                  }
                  break;
      }
      if (isset($_GET["columns"]) || (isset($_POST['submit']) && !in_array($_POST['submit'], array("login", "two-step-verification-form-submit", "resend-login-otp", "forgot-password", "reset-password-link", "lock")))) {
            $_SESSION["ajax-login"] = TRUE;
            $request_url = $_SERVER["SERVER_ADDR"] . $_SERVER["REQUEST_URI"];
            $login_form = new Template(SiteConfig::templatesPath() . "forms/login");
            $login_form->ajax_login = TRUE;
            $login_form->footer = FALSE;
            Utility::ajaxResponseFalse(
                  "You've been logged out of 6Orbit, please login again to continue!",
                  array(
                        "action" => "login",
                        "url" => $request_url
                  ),
                  $login_form->parse()
            );
      }
} else {

      //        if ($_GET['attendance-a'] == 1)
      //        {
      //            $db = Rapidkart::getInstance()->getDB();
      //            $db->autoCommit(true);
      //            $company_id = BaseConfig::$company_id;
      //            $sql = " SELECT areid, empid, absent_date, punch_in_utc_time, punch_out_utc_time FROM hr_attendance_record  WHERE company_id=" . $company_id . " ORDER BY absent_date ASC LIMIT 1000000";
      //            $res = $db->query($sql);
      //            if (!$res || $db->resultNumRows($res) < 1)
      //            {
      //                echo 'No record found!';
      //            }
      //            $ret = array();
      //            while ($row = $db->fetchObject($res))
      //            {
      //                $data = AttendanceManager::getRuleData($row->empid, $row->punch_in_utc_time, $row->punch_out_utc_time);
      //                $sql1 = "UPDATE hr_attendance_record SET color='" . $data['color'] . "', punch_in_type='" . $data['punch_in_type'] . "', short_leave='" . $data['short_leave'] . "'  WHERE areid=" . $row->areid;
      //                $res1 = $db->query($sql1);
      //                echo "Updated record for " . $row->empid . " against date " . $row->absent_date . "<br />";
      //            }
      //
      //
      //            exit();
      //        }
      //        echo implode(",",AccountGroupManager::getAllGroups(410004009, 1, 1));exit();
      $session_company_id = Session::getSessionVariable()['company_id'];
      if (BaseConfig::$company_id > 0 && $session_company_id > 0 && $session_company_id != BaseConfig::$company_id) {
            BaseConfig::$company_id = $session_company_id;
      }

      BaseConfig::$company_id = $session_company_id;
      BaseConfig::$company_start_date = LicenceManager::getLicenceCompanyStartDate();

      if (SystemConfig::getUser()->getUstatusId() != 1 && SystemConfig::getUser()->getUstatusId() != 4) {
            Session::logoutUser();
            System::redirectInternal("home");
      }
      if (BaseConfig::$company_id <= 0) {
            Session::logoutUser();
            System::redirectInternal("home");
      }
      if (getSettings("IS_PERMISSION_SECURITY_ENABLE")) {
            $ip_address = $_SERVER['REMOTE_ADDR'];
            if (isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
                  $ip_address = $_SERVER['HTTP_X_FORWARDED_FOR'];
            }
            $user_ip_address = AdminUserManager::getUserIPAddress(Session::loggedInUid(), BaseConfig::$company_id);

            if ($user_ip_address && is_array($user_ip_address) && count($user_ip_address) > 0 && SystemConfig::getUser()->getIsAdmin() != 1) {
                  $time_val = time();

                  $time = date('H:i:s', $time_val);

                  $logout_bit = 1;
                  foreach ($user_ip_address as $ip => $user_ip_addr) {
                        $valid_ip = 0;
                        if (strlen($ip) > 0 && filter_var($ip, FILTER_VALIDATE_IP)) {
                              $valid_ip = 1;
                              if ($ip == $ip_address) {
                                    $logout_bit = 0;
                              } else {
                                    $logout_bit = 1;
                              }
                        } else {
                              $logout_bit = 0;
                        }


                        if ($logout_bit == 0) {
                              foreach ($user_ip_addr as $inf) {
                                    $from_time = $inf->from_time;
                                    $to_time = $inf->to_time;
                                    if (valid($from_time) && valid($to_time) && $from_time != '00:00:00' && $to_time != '00:00:00') {
                                          if (((strtotime($time) >= strtotime($from_time)) && (strtotime($time) <= strtotime($to_time)))) {
                                                $logout_bit = 0;
                                                break;
                                          } else {
                                                $logout_bit = 1;
                                          }
                                    }
                              }
                        }
                        if ($logout_bit == 0) {
                              break;
                        }
                  }

                  if ($logout_bit) {

                        ScreenMessage::setMessage("You are not authorized to access the system.", ScreenMessage::MESSAGE_TYPE_ERROR);
                        Session::logoutUser();
                        System::redirectInternal("home");
                  }
            }
      }
      $password_update_days = getSettings("IS_PASSWORD_UPDATE_DAYS");
      if ($password_update_days > 0) {
            $password_log = AdminUserManager::getPasswordLog();
            $password_reset = 0;
            if (!$password_log) {
                  $password_reset = 1;
            } else {
                  $current_time = strtotime(date('Y-m-d H:i:s'));
                  $last_login_time = strtotime($password_log);
                  $for_log_time = round(abs($current_time - $last_login_time) / (60 * 60 * 24), 2);
                  if ($for_log_time > $password_update_days) {
                        $password_reset = 1;
                  }
            }
            if ($password_reset) {
                  if (isset($url[0]) && $url[0] == "admin_user" && isset($url[1]) && $url[1] == "settings" && isset($url[2]) && $url[2] == "security" && isset($url[3]) && $url[3] == "password") {
                  } else {
                        System::redirect(JPath::fullUrl("admin_user/settings/security/password"));
                  }
            }
      }
      if (isset($_SESSION['last_logged_in'])) {

            $current_time = strtotime(date('Y-m-d H:i:s'));
            $last_login_time = strtotime($_SESSION['last_logged_in']);
            $for_log_time = round(abs($current_time - $last_login_time) / 60, 2);
            $setting_min = getSettings("IS_INACTIVE_SESSION_TIME_IN_MINUTE");
            if ($setting_min > 0 && $for_log_time > $setting_min) {
                  Session::logoutUser();
                  System::redirectInternal("home");
            }
            if ($for_log_time > 0) {
                  $_SESSION['last_logged_in'] = date('Y-m-d H:i:s');
            }
      } else {
            $_SESSION['last_logged_in'] = date('Y-m-d H:i:s');
      }

      $variable_mappings = SiteVariableManager::getVariableConfig(BaseConfig::$company_id);
      if ($variable_mappings) {
            global $variable_config;
            $variable_config = $variable_mappings;
      }

      if (isset($_POST['submit'])) {
            if (!in_array($url[0], $masking) && $masking_status) {
                  Utility::permissionDenied();
            }
            switch ($_POST['submit']) {
                  case "upload-attachement-show-files":
                        upload_attachement_show_files($_POST);
                        break;
                  case "upload-attachement-files":
                        UploadItemFile::upload_attachement_file($_FILES, $_POST);
                        break;
                  case "attachement-delete-file":
                        UploadItemFile::attachement_delete_file($_POST);
                        break;
                  case "attachments-upload-file-submit":
                        attachments_upload_file_submit($_POST);
                        break;
                  case "show-max-discount-items-pop-up":
                        show_max_discount_items_pop_up($_POST);
                        break;
                  case "approval-blocking-conditions-submit":
                        ModuleManager::insertApprovalBlockingConditions($_POST);
                        break;
                  case "module-wise-config-submit":
                        ModuleManager::insertModuleConfig($_POST);
                        break;
                  case "reconcile-transaction-form":
                        AccountVoucherManager::reconcileTransactionForm($_POST);
                        break;
                  case "reconcile-transaction-submit":
                        AccountVoucherManager::reconcileTransactionSubmit($_POST);
                        break;
                  case "einvoicing-response-upload":
                        GstApiManager::EInvoicingResponseUpload($_POST);
                        break;
                  case "einvoice-upload":
                        GstApiManager::EInvoiceUpload($_POST, $_FILES);
                        break;
                  case "einvoice-remove":
                        GstApiManager::EInvoiceRemove($_POST, $_FILES);
                        break;
                  case "einvoicing-upload-submit":
                        GstApiManager::EInvoicingUploadSubmit($_POST);
                        break;
                  case "batch-item-code-unique":
                        InventorySetItemAttributeAttributeValueManager::batchItemCodeUniqueCheck($_POST);
                        break;
                  case "get-automatic-document-series-number":
                        DocumentSeriesManager::getAutoMaticDocumentSeriesNumber($_POST);
                        break;
                  case "terms-conditions-token-search":
                        TermsConditionsStaticManager::searchValue($_POST);
                        break;
                  case "terms-conditions-static-value-save":
                        TermsConditionsStaticManager::saveValue($_POST);
                        break;
                  case "custom-item-token-search":
                        CustomItemFieldStaticManager::searchValue($_POST);
                        break;
                  case "custom-item-field-static-value-save":
                        CustomItemFieldStaticManager::saveValue($_POST);
                        break;
                  case "get-customer-variation-group-wise-discount":
                        VariationPriceGroupPriceListItemsManager::getCategoryDiscount($_POST);
                        break;
                  case 'navbar-search-submit':
                        echo navbar_search($_POST);
                        exit();
                        break;
                  case 'datatable-column-update-submit':
                        echo json_encode(array("success" => DataTable::updateUserPreferences($_POST), "data" => "done"));
                        exit();
                        break;
                  case 'dashboard-customization':
                        dashboard_customization($_POST, $masking, $masking_status);
                        break;
                  case 'dashboard-customize-counter-submit':
                        dashboard_customize_counter_submit($_POST);
                        break;
                  case 'dashboard-customize-graph-submit':
                        dashboard_customize_graph_submit($_POST);
                        break;
                  case 'dashboard-customize-link-submit':
                        dashboard_customize_link_submit($_POST);
                        break;
                  case 'dashboard-customize-table-submit':
                        dashboard_customize_table_submit($_POST);
                        break;
                  case "outlet-bank-view":
                        OutletBankManager::outletBankView($_POST);
                        break;
                  case "logout-all-users":
                        logout_all_users($_POST);
                        break;
                  case "switch-assign-company":
                        switch_assign_company($_POST);
                        break;
                  case "switch-assign-company-submit":
                        switch_assign_company_submit($_POST);
                        break;
                  case "verify-gstin":
                        verify_gstin($_POST);
                        break;
                  case "session-overall-outlet-change":
                        Session::updateOutlet($_POST);
                        break;
                  case "update-ledger-performance":
                        TransactionReferenceManager::updateLedgerPerformance($_POST);
                        break;
                  case "edit-transaction-reference":
                        TransactionReferenceManager::editReferenceModal($_POST);
                        break;
                  case "edit-transaction-reference-submit":
                        TransactionReferenceManager::editReferenceSubmit($_POST);
                        break;
                  case "transaction-reference-assign-liability":
                        TransactionReferenceManager::assignLiability($_POST);
                        break;
                  case "transaction-invoice-reference-submit":
                        TransactionReferenceManager::assignLiabilitySubmit($_POST);
                        break;
                  case "transaction-reference-assign-order-liability":
                        TransactionReferenceManager::assignOrderLiability($_POST);
                        break;
                  case "transaction-invoice-order-reference-submit":
                        TransactionReferenceManager::assignOrderLiabilitySubmit($_POST);
                        break;
                  case "breakdown-details-transaction-reference":
                        TransactionReferenceSettlementManager::getBreakDownDetails($_POST);
                        break;
                  case "unlink-advance-transaction-ref":
                        TransactionReferenceSettlementManager::unlinkSettlement($_POST);
                        break;
                  case "load-transaction-ref-total-amount":
                        TransactionReferenceManager::getTotalAdvance($_POST);
                        break;
                  case "breakdown-details-print-transaction-reference":
                        TransactionReferenceSettlementManager::printBreakDownDetails($_POST);
                        break;
                  case "transaction-reference-assign-order":
                        TransactionReferenceManager::transactionReferenceAssignToOrder($_POST);
                        break;
                  case "order-transaction-reference-submit":
                        TransactionReferenceManager::transactionReferenceOrderSubmit($_POST);
                        break;
                  case "transaction-reference-release":
                        TransactionReferenceManager::releaseRef($_POST);
                        break;
                  case "invoice-readjustment":
                        $party_id = isset($_POST['party_id']) ? $_POST['party_id'] : 0;
                        TransactionReferenceSettlementManager::reAdjustment($party_id);
                        break;
                  case "advance-search-reference-number-searchable":
                        TransactionReferenceSettlementManager::getReferenceNoSearchForm($_POST);
                        break;
            }
      }

      if (isset($_GET['submit'])) {
            switch ($_GET['submit']) {
                  case "view-module-approval-blocking-modal":
                        ModuleManager::getModuleApprovalBlockingModal($_GET);
                        break;
                  case "view-module-configs-modal":
                        ModuleManager::getModuleConfigModal($_GET);
                        break;
                  case "view-module-notifications-modal":
                        ModuleManager::getModuleNotificationsModal($_GET);
                        break;
            }
      }

      if (!isset($url[0])) {
            $url[0] = "home";
      }
      if (!in_array($url[0], $masking) && $masking_status) {

            $theme->setContent("content", load_403());
      } else {

            if (getSettings("IS_ORDER_CSS_INCLUDE")) {
                  $theme->addCss(SystemConfig::stylesUrl() . "chkorder/chkorder.css");
            }

            if (getSettings("IS_FONT_SIZE_INCLUDE")) {
                  $theme->addCss(SystemConfig::stylesUrl() . "font_size.css");
            }

            switch ($url[0]) {
                  case 'sanity-test':
                        include_once 'sanity_test/sanity_test.inc.php';
                        break;
                  case "prototype_management":
                        include_once 'prototype_management/prototype_management.inc.php';
                        break;
                  case "bug-report":
                        include_once 'bug_report/bug_report.inc.php';
                        break;
                  case "graph-creator":
                        include_once 'graph-creator/graph-creator.inc.php';
                        break;
                  case "dashboard":
                        include_once 'dashboard/dashboard.inc.php';
                        break;
                  case "secondary_system":
                        include_once 'secondary_system_enable_disable.inc.php';
                        break;
                  case "contract_pricing":
                        include_once 'contract_pricing/contract_pricing.inc.php';
                        break;
                  case "employeeT":
                        include_once "employeeT/employeeT.inc.php";
                        break;
                  case "employee_test":
                        include_once 'employee_test/employee_test.inc.php';
                        break;
                  case "video":
                        include_once "video/video.inc.php";
                        break;
                  case "feedback":
                        include_once 'customer_feedback_template/customer_feedback_template.inc.php';
                        break;
                  case "whatsapp":
                        include_once 'whatsapp/whatsapp.inc.php';
                        break;
                  case "video":
                        include_once 'video/video.inc.php';
                        break;
                  case "training_series":
                        include_once 'training_series/training_series.inc.php';
                        break;
                  case "temporary_test":
                        include_once 'temporary_test/temporary_test.inc.php';
                        break;
                  case "outcome":
                        include_once 'outcome/outcome.inc.php';
                        break;
                  case "delivery_gatepass_package_transport":
                        include_once 'delivery_gatepass_package_transport/delivery_gatepass_package_transport.inc.php';
                        break;
                  case "package_box_dimensions":
                        include_once 'package_box_dimensions/package_box_dimensions.inc.php';
                        break;
                  case "report_custom":
                        include_once 'report_custom/report_custom.inc.php';
                        break;
                  case "custom_item_field":
                        include_once 'custom_item_field/custom_item_field.inc.php';
                        break;
                  case "terms_conditions":
                        include_once 'terms_conditions/terms_conditions.inc.php';
                        break;
                  case "report_generator":
                        include_once 'report_generator/report_generator.inc.php';
                        break;
                  case "credit_request_reason_static":
                        include_once 'credit_request_reason_static/credit_request_reason_static.inc.php';
                        break;
                  case "shopify_credentials":
                        include_once 'shopify_credentials/shopify_credentials.inc.php';
                        break;
                  case "indiamart":
                        include_once 'indiamart/indiamart.inc.php';
                        break;
                  case "email_domain":
                        include_once 'email_domain/email_domain.inc.php';
                        break;
                  case "franchise":
                        include_once 'franchise/franchise.inc.php';
                        break;
                  case "shiprocket":
                        include_once 'shiprocket/shiprocket.inc.php';
                        break;
                  case "request_callback":
                        include_once 'request_callback/request_callback.inc.php';
                        break;
                  case "evideo":
                        include_once 'evideo/evideo.inc.php';
                        break;
                  case "instagram_reels":
                        include_once 'instagram_reels/instagram_reels.inc.php';
                        break;
                  case "inspiration_gallery":
                        include_once 'inspiration_gallery/inspiration_gallery.inc.php';
                        break;
                  case "notification_configuration":
                        include_once 'notification_configuration/notification_configuration.inc.php';
                        break;
                  case "form_preferences":
                        include_once 'form_preferences/form_preferences.inc.php';
                        break;
                  case "featured_products":
                        include_once 'featured_products/featured_products.inc.php';
                        break;
                  case "payment_gateway":
                        include_once 'payment_gateway/payment_gateway.inc.php';
                        break;
                  case "packing-list":
                        include_once 'packing-list/packing-list.inc.php';
                        break;
                  case "annual_maintenance":
                        include_once 'annual_maintenance/annual_maintenance.inc.php';
                        break;
                  case "sales_return_consignment":
                        include_once 'sales_return_consignment/sales_return_consignment.inc.php';
                        break;
                  case "service_request":
                        include_once 'service_request/service_request.inc.php';
                        break;
                  case "assets_issue":
                        include_once 'assets_issue/assets_issue.inc.php';
                        break;
                  case "jobwork_activity":
                        include_once 'jobwork_activity/jobwork_activity.inc.php';
                        break;
                  case "category_stage":
                        include_once 'category_stage/category_stage.inc.php';
                        break;
                  case "vendor_support_rate":
                        include_once 'vendor_support_rate/vendor_support_rate.inc.php';
                        break;
                  case "batch_item_code":
                        include_once 'batch_item_code/batch_item_code.inc.php';
                        break;
                  case "followup_activity_type":
                        include_once 'followup_activity_type/followup_activity_type.inc.php';
                        break;
                  case "lead_type":
                        include_once 'lead_type/lead_type.inc.php';
                        break;
                  case "rejected_reason":
                        include_once 'rejected_reason/rejected_reason.inc.php';
                        break;
                  case "damaged_reason":
                        include_once 'damaged_reason/damaged_reason.inc.php';
                        break;
                  case "indent_direct":
                        include_once 'indent/indent.inc.php';
                        break;
                  case "sales_return_consignment":
                        include_once 'sales_return_consignment/sales_return_consignment.inc.php';
                        break;
                  case "exotel":
                        include_once 'exotel/exotel.inc.php';
                        break;
                  case "driver":
                        include_once 'driver/driver.inc.php';
                        break;
                  case "todo_task_activity":
                        include_once 'todo_task_activity/todo_task_activity.inc.php';
                        break;
                  case "todo_task_category":
                        include_once 'todo_task_category/todo_task_category.inc.php';
                        break;
                  case "document_series":
                        include_once 'document_series/document_series.inc.php';
                        break;
                  case "promocode":
                        include_once 'promocode/promocode.inc.php';
                        break;
                  case "kyc_details":
                        include_once 'kyc_details/kyc_details.inc.php';
                        break;
                  case "followup_reason":
                        include_once 'followup_reason/followup_reason.inc.php';
                        break;
                  case "automation_config":
                        include_once 'automation_config/automation_config.inc.php';
                        break;
                  case "customer_territory":
                        include_once 'customer_territory/customer_territory.inc.php';
                        break;
                  case "request_custom":
                        include_once 'request_custom/request_custom.inc.php';
                        break;
                  case "sales_category_type":
                        include_once 'sales_category_type/sales_category_type.inc.php';
                        break;
                  case "purchase_category_type":
                        include_once 'purchase_category_type/purchase_category_type.inc.php';
                        break;
                  case "biometric_service_provider":
                        include_once 'biometric_service_provider/biometric_service_provider.inc.php';
                        break;
                  case "employee":
                        include_once 'employee/employee.inc.php';
                        break;

                  case "image-processing":
                        $variations = InventorySetVariationManager::getVariationList();
                        exit();
                  case "gst_api":
                        include_once 'gst_api/gst_api.inc.php';
                        break;
                  case "jobwork":
                        include_once 'jobwork/jobwork.inc.php';
                        break;
                  case "joborder":
                        include_once 'joborder/joborder.inc.php';
                        break;
                  case "daily-movement-tracking":
                        include_once 'daily-movement-tracking/daily-movement-tracking.inc.php';
                        break;
                  case "service-memo":
                        include_once 'service-memo/service-memo.inc.php';
                        break;
                  case "send_alert_message_for_enquiry_schedule_to_assigned_user":
                        send_alert_message_for_enquiry_schedule_to_assigned_user();
                        System::redirectInternal("home");
                        break;
                  case "booking":
                        require_once "y11.php";
                        break;
                  case "custom":
                        include_once 'custom_design/custom.inc.php';
                        break;
                  case "chat":
                        include_once 'chat/chat.inc.php';
                        break;
                  case "seller":
                        include_once 'seller/seller.inc.php';
                        break;
                  case "outlet":
                        include_once 'outlet/outlet.inc.php';
                        break;
                  case "pincode":
                        include_once 'pincode/pincode.inc.php';
                        break;
                  case "chat_message":
                        include_once 'chat_message/chat_message.inc.php';
                        break;
                  case "training":
                        include_once 'training/training.inc.php';
                        break;
                  case "dnd":
                        include_once 'dnd/dnd.inc.php';
                        break;
                  case "member":
                        include_once 'member/member.inc.php';
                        break;
                  case "notifications":
                        include_once 'notification/notification.inc.php';
                        break;
                  case "faq":
                        include_once "faq/faq.inc.php";
                        break;
                  case "hub":
                        include_once 'hub/hub.inc.php';
                        break;
                  case "manage":
                        include_once 'manage/manage.inc.php';
                        break;
                  case "email":
                        include_once 'email/custom_email.inc.php';
                        break;
                  case "reports":
                        include_once 'reports/reports.inc.php';
                        break;
                  case "sms":
                        include_once 'sms/sms.inc.php';
                        break;
                  case "newsletter":
                        include_once 'newsletter/newsletter.inc.php';
                        break;
                  case "repair_service":
                        include_once 'repair_service/repair_service.inc.php';
                        break;

                  case "y":
                        getAllContent();
                        break;
                  case "z":
                        getSidebarDetails();
                        break;
                  case "x":
                        resetTableSettings();
                        break;
                  //                case "product":
                  //                    include_once "item/item.inc.php";
                  //                    break;
                  case "product":
                        include_once "product/product.inc.php";
                        break;
                  case "sticker":
                        include_once "sticker/sticker.inc.php";
                        break;
                  case "jobcard":
                        include_once "jobcard/jobcard.inc.php";
                        break;
                  case "myjobcard":
                        include_once 'jobcard/my_jobcard.inc.php';
                        break;
                  case "brand":
                        include_once 'brand/brand.inc.php';
                        break;
                  case "company":
                        include_once 'company/company.inc.php';
                        break;
                  case "category":
                        include_once 'category/category.inc.php';
                        break;
                  case "admin_user":
                        include_once 'admin_user/admin_user.inc.php';
                        break;
                  case "inventory-mgmt":
                        include_once 'inventory/inventory.inc.php';
                        break;
                  case "order":
                        include_once 'order/order.inc.php';
                        break;
                  case "ticket":
                        include_once 'ticket/ticket.inc.php';
                        break;
                  case "help-desk-call-log":
                        include_once 'help_desk_call_log/help_desk_call_log.inc.php';
                        break;
                  case "fancy_images":
                        include_once 'fancy_images/fancy_images.inc.php';
                        break;
                  case "frontend":
                        include_once 'frontend_components/frontend.inc.php';
                        break;
                  case "offer":
                        include_once 'offer/offer.inc.php';
                        break;
                  case "measurement":
                        include_once 'measurement/measurement.inc.php';
                        break;
                  case "logout":
                        Session::logoutUser();
                        System::redirectInternal("home");
                        break;

                  case "home":
                        if (isset($_POST['type'])) {
                              switch ($_POST['type']) {
                                    case "api":
                                          include_once 'api/api.inc.php';
                                          break;
                              }
                        }
                        if (isset($_POST['submit'])) {
                              switch ($_POST['submit']) {
                                    case "help":
                                          help_dashboard();
                                          break;
                              }
                        }
                        if (isset($url[1])) {
                              if (isset($url[2])) {
                                    switch ($url[2]) {
                                          case "distributor-block-inventory-list-render-page":
                                                DataTable::tableRender(get_block_inventory_table($url[1]), "block_inventory_view", "binvid", $_GET['columns'], " distid = $url[1]", $_GET);
                                                break;
                                    }
                              }
                        } else {
                              include_once 'dashboard/dashboard.inc.php';
                              // $theme->addScript(SystemConfig::scriptsUrl() . "home/home.js");
                              // $theme->setContent("full_main", load_home($masking, $masking_status));
                        }
                        break;
                  case "group_wise_enquiry_table_render":
                        DataTable::tableRender(get_enquiry_department_wise_table(), "enquiry_group_wise_view", "sgid", $_GET['columns'], "sgsid ='1'", $_GET);
                        break;
                  case "config":
                        include_once BaseConfig::SITE_PATH . BaseConfig::CONFIG_FOLDER . '/config/config.inc.php';
                        break;
                  case "staticpages":
                        include_once 'staticpages/pages.inc.php';
                        break;
                  case "socialwidget":
                        include_once 'social_widgets/widgets.inc.php';
                        break;
                  case "sitevariables":
                        include_once 'site_variables/variables.inc.php';
                        break;
                  case "factory":
                        include_once 'factory/factory.inc.php';
                        break;
                  case "shipping":
                        include_once 'shipping/shipping.inc.php';
                        break;
                  case "payment":
                        include_once 'payment_modes/payment_mode.inc.php';
                        break;
                  case "transaction":
                        include_once 'transaction/transaction.inc.php';
                        break;
                  case "wallet":
                        include_once 'wallet/wallet.inc.php';
                        break;
                  case "customer":
                        include_once 'customer/customer.inc.php';
                        break;
                  case "frames":
                        include_once 'frames/frames.inc.php';
                        break;
                  case "customer_group":
                        include_once 'customer_group/customer_group.inc.php';
                        break;
                  case "customer_sales_type":
                        include_once 'customer_sales_type/customer_sales_type.inc.php';
                        break;
                  case "truck_movement":
                        include_once 'truck_movement/truck_movement.inc.php';
                        break;
                  case "quotation":
                        include_once 'quotation/quotation.inc.php';
                        break;
                  case "sales_return_request":
                        include_once 'sales_return_request/sales_return_request.inc.php';
                        break;
                  case "404":
                        $theme->setContent("content", load_404());
                        break;
                  case "403":
                        $theme->setContent("content", load_403());
                        break;
                  case "promo-code":
                        include_once 'promo_code/promo_code.inc.php';
                        break;
                  case "coverage":
                        include_once 'coverage_area/coverage.inc.php';
                        break;
                  case "tax":
                        include_once 'tax/tax.inc.php';
                        break;
                  case "testimonial":
                        include_once 'testimonial/testimonial.inc.php';
                        break;
                  case "raw-materials":
                        include_once 'raw_materials/raw_materials.inc.php';
                        break;
                  case "showcase":
                        include_once 'showcase/showcase.inc.php';
                        break;
                  case "vendor":
                        include_once 'vendor/vendor.inc.php';
                        break;
                  case "pfs":
                        include_once 'pfs/pfs.inc.php';
                        break;
                  case "sms-provider":
                        include_once 'sms_provider/sms_provider.inc.php';
                        break;
                  case "sticker-printing":
                        include_once 'sticker_printing/sticker_printing.inc.php';
                        break;
                  case "print-template":
                        include_once 'print_template/print_template.inc.php';
                        break;
                  case "email-template":
                        include_once 'email_template/email_template.inc.php';
                        break;
                  case "sms-template":
                        include_once 'sms_template/sms_template.inc.php';
                        break;
                  case "sms-report":
                        include_once 'sms_report/sms_report.inc.php';
                        break;
                  case "email-report":
                        include_once 'email_report/email_report.inc.php';
                        break;
                  case "lead":
                        include_once 'lead/lead.inc.php';
                        break;
                  case "vastra":
                        include_once 'vastra/vastra.inc.php';
                        break;
                  case "followup":
                        include_once 'followup/followup.inc.php';
                        break;
                  case "enquiry":
                        include_once 'enquiry/enquiry.inc.php';
                        break;
                  case "chkorder":
                        include_once 'chkorder/chkorder.inc.php';
                        break;
                  case "purchase":
                        include_once 'purchase/purchase.inc.php';
                        break;
                  case "accounting":
                        include_once 'accounting/accounting.inc.php';
                        break;
                  case "account":
                        include_once 'account/account.inc.php';
                        break;
                  case "asset":
                        include_once 'asset/asset.inc.php';
                        break;
                  case "insurance":
                        include_once 'insurance/insurance.inc.php';
                        break;
                  case "import":
                        include_once 'import/import.inc.php';
                        break;
                  case "distributor":
                        include_once 'distributor/distributor.inc.php';
                        break;
                  case "package":
                        include_once 'package/package.inc.php';
                        break;
                  case "lock":
                        Session::lockUser();
                        include_once 'lock.inc.php';
                        break;
                  case "login":
                        if (isset($_POST['submit'])) {
                              Utility::ajaxResponseTrue("Loggedin successfully", JPath::fullUrl("home"), "", FALSE);
                        }
                        System::redirectInternal("home");
                        break;
                  case "customer_registration":
                        if (isset($_POST['submit'])) {
                              Utility::ajaxResponseTrue("Loggedin successfully", JPath::fullUrl("home"), "", FALSE);
                        }
                        System::redirectInternal("home");
                        break;
                  case "helpdesk":
                        include_once 'helpdesk/helpdesk.inc.php';
                        break;
                  case "help_desk_call_log":
                        include_once 'help_desk_call_log/help_desk_call_log.inc.php';
                        break;
                  case "per":
                        include_once 'permission.php';
                        break;
                  case "my-package":
                        include_once 'package/my_package.inc.php';
                        break;
                  case "block-inventory":
                        include_once 'block_inventory/block_inventory.inc.php';
                        break;
                  case "email-log":
                        include_once 'email_log/email_log.inc.php';
                        break;
                  case "activity":
                        include_once 'activity/activity.inc.php';
                        break;
                  case "country":
                        include_once 'country/country.inc.php';
                        break;
                  case "state":
                        include_once 'state/state.inc.php';
                        break;
                  case "city":
                        include_once 'city.php';
                        break;
                  case "checkpoint":
                        include_once 'checkpoint/checkpoint.inc.php';
                        break;
                  case "warehouse":
                        include_once 'warehouse/warehouse.inc.php';
                        break;
                  case "outlet":
                        include 'outlet/outlet.inc.php';
                        break;
                  case "workorder":
                        include_once 'workorder/workorder.inc.php';
                        break;
                  case "workorder_service":
                        include_once 'workorder_service/workorder_service.inc.php';
                        break;
                  case 'my-warehouse':
                        include_once 'warehouse/my_warehouse.inc.php';
                        break;
                  case "stock":
                        include_once 'stock/stock.inc.php';
                        break;
                  case "stock_conversion_type":
                        include_once 'stock_conversion_type/stock_conversion_type.inc.php';
                        break;
                  case "extra_charges":
                        include_once 'extra_charges/extra_charges.inc.php';
                        break;
                  case "menu":
                        include_once 'menu/menu.inc.php';
                        break;
                  case "preview":
                        include_once 'preview/preview.inc.php';
                        break;
                  case "pos":
                        include_once 'pos/pos.inc.php';
                        break;
                  case "sales":
                        include_once 'sales/sales.inc.php';
                        break;
                  case "formulae":
                        include_once 'formulae/formulae.inc.php';
                        break;
                  case "formulaee":
                        include_once 'formulaee/formulaee.inc.php';
                        break;
                  case 'pallet_type':
                        include_once 'pallet_type/pallet_type.inc.php';
                        break;
                  case 'biometric_device':
                        include_once 'biometric_device/biometric_device.inc.php';
                        break;
                  case "error-logs":
                        include_once 'error_logs/error_logs.inc.php';
                        break;
                  case "hrm":
                        if (hasPermission(USER_PERMISSION_HRM_EMPLOYEE) || hasPermission(USER_PERMISSION_PAYROLL)) {
                              include_once 'hrm/hrm.inc.php';
                        } else {
                              System::redirectInternal("403");
                        }
                        break;
                  case "system-wizard":
                        include_once 'system_wizard/system_wizard.inc.php';
                        break;
                  case "ui_field_type_static":
                        include_once 'ui_field_type_static/ui_field_type_static.inc.php';
                        break;
                  case "x":
                        resetTableSettings();
                        break;
                  case "advanced-search":
                        include_once 'advanced_search/advanced_search.inc.php';
                        break;
                  case "payroll":
                        include_once 'payroll/payroll.inc.php';
                        break;
                  case "upload-items":
                        include_once 'item/upload_item.inc.php';
                        break;
                  case "upload-file":
                        include_once 'upload/upload_file.inc.php';
                        break;
                  case 'system-revert':
                        include_once 'system_revert/system_revert.inc.php';
                        break;
                  case "upload-variations-vendor-mapping":
                        include_once 'product/upload_variations_vendor_mapping.inc.php';
                        break;
                  case "custom-attributes":
                        include_once 'custom_attribute/custom_attribute.inc.php';
                        break;
                  case "comment":
                        include_once 'comment/comment.inc.php';
                        break;
                  case "training":
                        include_once 'training/training.inc.php';
                        break;
                  case "contact-detail":
                        include_once 'contact_detail/contact_detail.inc.php';
                        break;
                  case "project":
                        include_once 'project/project.inc.php';
                        break;
                  case "projectmanagement":
                        include_once 'projectmanagement/projectmanagement.inc.php';
                        break;
                  case "trainingcategory":
                        include_once 'training/training_category.inc.php';
                        break;
                  case "preferences":
                        include_once 'system_preferences/system_preferences.inc.php';
                        break;
                  case "invoices":
                        include_once 'invoices/invoices.inc.php';
                        break;
                  case "invoice":
                        include_once 'invoice/invoice.inc.php';
                        break;
                  case "transporter":
                        include_once 'transporter/transporter.inc.php';
                        break;
                  case "invoice_reports":
                        include_once 'invoice/invoice_reports.inc.php';
                        break;
                  case "credit-note":
                        include_once 'credit_note/credit_note.inc.php';
                        break;
                  case "package-invoice":
                        require_once 'package-invoice.php';
                        break;
                  case "ordertransaction":
                        require_once 'ordertransaction.php';
                        break;
                  case "invoicetransaction":
                        require_once 'invoicetransaction.php';
                        break;
                  case "debit-note":
                        include_once 'debit_note/debit_note.inc.php';
                        break;
                  //                case "purchase-invoice":
                  //                    require_once 'purchase_invoice/purchase_invoice.inc.php';
                  //                    break;
                  case "xml":
                        generate_xml();
                        break;
                  case "item-script":
                        include_once 'item_script.php';
                        break;
                  case "process_script":
                        include_once 'process_script.php';
                        break;
                  case "item-script":
                        include_once 'item_script.php';
                        break;
                  case "stock-transfer-rate-update":
                        include_once 'stock_transfer_rate_update.php';
                        break;
                  case "remove-duplicate":
                        remove_duplicate();
                        exit();
                        break;
                  case 'customer-script':
                        include_once 'customer_script.php';
                        break;
                  case 'vendor-script':
                        include_once 'vendor_script.php';
                        break;
                  case 'tax-script':
                        include_once 'tax_script.php';
                        break;
                  case "sundrydebtor":
                        include_once 'sundrydebtor.php';
                        break;
                  case "account-mapping":
                        include_once 'account_voucher_mapping.php';
                        break;
                  case "invoice-column":
                        include_once 'invoice_column.php';
                        break;
                  case "credit":
                        include_once 'credit.php';
                        break;
                  case "quotation-order-mapping":
                        include_once 'quotation_order_mapping.php';
                        break;
                  case "pay":
                        payment_terms_update();
                        break;
                  case "check-item-availability":
                        include_once 'check_item_availability/check_item_availability.inc.php';
                        break;
                  case "consignment":
                        require_once 'consignment/consignment.inc.php';
                        break;
                  case "excel":
                        generate_excel();
                        break;
                  case "json":
                        generate_json();
                        break;
                  case "settle-invoices":
                        include_once 'settle_invoices.php';
                        break;
                  case "extra_discount":
                        include_once 'extra_discount/extra_discount.inc.php';
                        break;
                  case "rooms":
                        include_once 'rooms/rooms.inc.php';
                        break;
                  case "ship":
                        include_once 'shipcompany/ship.inc.php';
                        break;
                  case "card":
                        include_once 'card/card.inc.php';
                        break;
                  case "brandmerge":
                        include_once 'brandmerge/brand.inc.php';
                        break;
                  case "companymerge":
                        include_once 'companymerge/company.inc.php';
                        break;
                  case "categorymerge":
                        include_once 'categorymerge/category.inc.php';
                        break;
                  case "schemes":
                        include_once 'schemes/schemes.inc.php';
                        break;
                  case "pfs":
                        include_once 'pfs/pfs.inc.php';
                        break;
                  case "cess":
                        include_once 'cess/cess.inc.php';
                        break;
                  case "cost_analysis":
                        include_once 'cost_analysis/cost_analysis.inc.php';
                        break;
                  case "services":
                        include_once 'services/services.inc.php';
                        break;
                  case "testpractise":
                        include_once 'testpractise/testpractise.inc.php';
                        break;
                  case "cc":
                        include_once 'cc/cc.inc.php';
                        break;
                  case "commission":
                        include_once 'commission/commission.inc.php';
                        break;
                  case "ecommerce":
                        include_once 'ecommerce/ecommerce.inc.php';
                        break;
                  case "log":
                        include_once 'log/log.inc.php';
                        break;
                  case "sample-module":
                        include_once 'sample-module/sample-module.inc.php';
                        break;
                  case "customer_industry_type":
                        include_once 'customer_industry_type.inc.php';
                        break;
                  case "test":
                        include_once 'test/test.inc.php';
                        break;
                  case "cash_discount":
                        include_once 'cash_discount/cash_discount.inc.php';
                        break;
                  case 'voucher-restrict':
                        include_once 'voucher_restrict/voucher_restrict.inc.php';
                        break;
                  case 'item-profiling':
                        include_once 'item_profiling/item_profiling.inc.php';
                        break;
                  case 'item-profiling-template':
                        include_once 'item_profiling_template/item_profiling_template.inc.php';
                        break;
                  case 'cost-category':
                        include_once 'cost_category/cost_category.inc.php';
                        break;
                  case 'cost-center':
                        include_once 'cost_center/cost_center.inc.php';
                        break;
                  case 'call_log':
                        include_once 'call_log/call_log.inc.php';
                        break;
                  case 'walkin':
                        include_once 'walkin/walkin.inc.php';
                        break;
                  case 'application_type':
                        include_once 'application_type/application_type.inc.php';
                        break;
                  case 'price_profiling':
                        include_once 'price_profiling/price_profiling.inc.php';
                        break;
                  case 'payment_planner':
                        include_once 'payment_planner/payment_planner.inc.php';
                        break;
                  case 'category-script':
                        include_once 'category_script.php';
                        break;
                  case 'price_profiling':
                        include_once 'price_profiling/price_profiling.inc.php';
                        break;
                  case "pos_receipt":
                        include_once 'pos_receipt/pos.inc.php';
                        break;
                  case "pos_receipt":
                        include_once 'pos_receipt/pos.inc.php';
                        break;
                  case "campaign":
                        include_once 'campaign/campaign.inc.php';
                        break;
                  case "resources":
                        include_once 'resources/resources.inc.php';
                        break;
                  case "todo":
                        include_once 'todo/todo.inc.php';
                        break;
                  case "customer_vendor":
                        include_once 'customer_vendor/customer_vendor.inc.php';
                        break;
                  case "customer_contract":
                        include_once 'customer_contract/customer_contract.inc.php';
                        break;
                  case 'progressive_discount':
                        include_once 'progressive_discount/progressive_discount.inc.php';
                        break;
                  case 'ecom_offers':
                        include_once 'ecom_management/ecom_offers/ecom_offers.inc.php';
                        break;
                  case 'delivery_coverage':
                        include_once 'ecom_management/delivery_coverage/delivery_coverage.inc.php';
                        break;
                  case 'knowledge_centre':
                        include_once 'ecom_management/knowledge_centre/knowledge_centre.inc.php';
                        break;
                  case "vehicle":
                        include_once 'vehicle/vehicle.inc.php';
                        break;
                  case 'ecom_configurations':
                        include_once 'ecom_management/ecom_configurations/ecom_configurations.inc.php';
                        break;
                  case 'ecom_carts':
                        include_once 'ecom_carts/ecom_carts.inc.php';
                        break;
                  case 'ecom_item_reviews':
                        include_once 'ecom_management/ecom_item_reviews/ecom_item_reviews.inc.php';
                        break;
                  case 'application_area_config':
                        include_once
                              'ecom_management/application_area_config/application_area_config.inc.php';
                        break;
                  case 'ecom_store_projects':
                        include_once 'ecom_management/ecom_store_project_mgmt/ecom_store_project_mgmt.inc.php';
                        break;
                  default:
                        System::redirectInternal("404");
                        break;
            }
      }
}

function generate_excel()
{
      global $url;
      if (!isset($url[1])) {
            exit();
      }
      $start_date = 0;
      $end_date = 0;
      if (isset($url[2]) && $url[2] != '') {
            $start_date = date('Y-m-d 00:00:00', strtotime($url[2]));
      }
      if (isset($url[3]) && $url[3] != '') {
            $end_date = date('Y-m-d 23:59:59', strtotime($url[3]));
      }
      $id = $_GET['id'];

      $chkid_array = array();
      if (isset($_GET['chkid'])) {
            $outlet = explode(',', $_GET['chkid']);
            $outlet = array_filter($outlet);
            if (is_array($outlet) && !empty($outlet)) {
                  $chkid_array = $outlet;
            }
      }
      $gid = 0;
      if (isset($_GET['gid'])) {
            $gid = $_GET['gid'];
      }
      if ($gid > 0 && empty($chkid_array) && getSettings('IS_OUTLET_ENABLE')) {
            $outlets = OutletManager::getUserCheckPoint(Session::loggedInUid(), null, null, $gid);
            if ($outlets) {
                  foreach ($outlets as $outlet) {
                        $chkid_array[] = $outlet['chkid'];
                  }
            }
      }


      switch ($url[1]) {
            case 'b2b':
                  AccountTaxManager::getB2BGSTR1CSV($start_date, $end_date, $id, $chkid_array);
                  break;
            case 'b2ba':
                  AccountTaxManager::getB2BAGSTR1CSV($start_date, $end_date, $id);
                  break;
            case "b2c":
                  AccountTaxManager::getB2CLGSTR1CSV($start_date, $end_date, $id, $chkid_array);
                  break;
            case "b2ca":
                  AccountTaxManager::getB2CLAGSTR1CSV($start_date, $end_date, $id);
                  break;
            case "hsn":
                  AccountTaxManager::getHSNGSTR1CSV($start_date, $end_date, $id, $chkid_array, 1);
                  break;
            case "hsn-b2c":
                  AccountTaxManager::getHSNGSTR1CSV($start_date, $end_date, $id, $chkid_array);
                  break;
            case "docs":
                  AccountTaxManager::getDocsGSTR1CSV($start_date, $end_date, $id, $chkid_array);
                  break;
            case "purchase":
                  ob_end_clean();
                  ob_start();
                  $excel = PurchaseInvoiceManager::generateExcel($start_date, $end_date, $_GET['id']);
                  header('Content-Type: application/vnd.ms-excel');
                  header('Content-Disposition: attachment;filename="purchase.xls"');
                  header('Cache-Control: max-age=0');
                  $objWriter = PHPExcel_IOFactory::createWriter($excel, 'Excel5');
                  $objWriter->save('php://output');
                  break;
            case "b2cs":
                  AccountTaxManager::getB2CSGSTR1CSV($start_date, $end_date, $id, $chkid_array);
                  break;
            case "b2csa":
                  AccountTaxManager::getB2CSAGSTR1CSV($start_date, $end_date, $id);
                  break;
            case "exp":
                  AccountTaxManager::getEXPGSTR1CSV($start_date, $end_date, $id, $chkid_array);
                  break;
            case "expa":
                  AccountTaxManager::getEXPAGSTR1CSV($start_date, $end_date, $id);
                  break;
            case "at":
                  AccountTaxManager::getATGSTR1CSV($start_date, $end_date, $id);
                  break;
            case "ata":
                  AccountTaxManager::getATAGSTR1CSV($start_date, $end_date, $id);
                  break;
            case "atadj":
                  AccountTaxManager::getATADJGSTR1CSV($start_date, $end_date, $id);
                  break;
            case "atadja":
                  AccountTaxManager::getATADJAGSTR1CSV($start_date, $end_date, $id);
                  break;
            case "exemp":
                  AccountTaxManager::getEXPEMPGSTR1CSV($start_date, $end_date, $id, $chkid_array);
                  break;
            case "cdnr":
                  AccountTaxManager::getCDNRGSTR1CSV($start_date, $end_date, $id, $chkid_array);
                  break;
            case "cdnra":
                  AccountTaxManager::getCDNRAGSTR1CSV($start_date, $end_date, $id);
                  break;
            case "cdnur":
                  AccountTaxManager::getCDNURGSTR1CSV($start_date, $end_date, $id, $chkid_array);
                  break;
            case "cdnura":
                  AccountTaxManager::getCDNURAGSTR1CSV($start_date, $end_date, $id);
                  break;
            case "gstr2-b2b":
                  AccountTaxManager::getB2BGSTR2CSV($start_date, $end_date, $id, $chkid_array);
                  break;
            case "gstr2-b2bur":
                  AccountTaxManager::getB2BURGSTR2CSV($start_date, $end_date, $id, $chkid_array);
                  break;
            case "gstr2-imps":
                  AccountTaxManager::getIMPSGSTR2CSV($start_date, $end_date, $id, $chkid_array);
                  break;
            case "gstr2-impg":
                  AccountTaxManager::getIMPGGSTR2CSV($start_date, $end_date, $id, $chkid_array);
                  break;
            case "gstr2-cdnr":
                  AccountTaxManager::getCDNRGSTR2CSV($start_date, $end_date, $id, $chkid_array);
                  break;
            case "gstr2-cdnur":
                  AccountTaxManager::getCDNURGSTR2CSV($start_date, $end_date, $id, $chkid_array);
                  break;
            case "gstr2-at":
                  AccountTaxManager::getATGSTR2CSV($start_date, $end_date, $id);
                  break;
            case "gstr2-atadj":
                  AccountTaxManager::getATADJGSTR2CSV($start_date, $end_date, $id);
                  break;
            case "gstr2-exemp":
                  AccountTaxManager::getEXPEMPGSTR2CSV($start_date, $end_date, $id, $chkid_array);
                  break;
            case "gstr2-itcr":
                  AccountTaxManager::getITCRGSTR2CSV($start_date, $end_date, $id);
                  break;
            case "gstr2-hsn":
                  AccountTaxManager::getHSNGSTR2CSV($start_date, $end_date, $id, $chkid_array);
                  break;
            case "gstr3b":

                  $month = date('M', strtotime($start_date));
                  list($start, $end) = AccountReportManager::getFinancialYear("april", $start_date);
                  $year = date('Y', strtotime($start)) . "-" . date('Y', strtotime($end));

                  $global_tax_profile = new GlobalBusinessTaxProfile($id);

                  AccountTaxManager::createInvoiceGstView($start_date, $end_date, $chkid_array);
                  PurchaseInvoiceManager::createPurchaseInvoiceGstView($start_date, $end_date, $chkid_array);
                  PurchaseInvoiceManager::createPurchaseInvoiceGstView($start_date, $end_date, $chkid_array, true);
                  PurchaseInvoiceImportManager::createPurchaseInvoiceImportGstView($start_date, $end_date, $chkid_array);
                  CheckPointOrderCreditNoteManager::createCreditNoteGstView($start_date, $end_date, $chkid_array);
                  CheckPointOrderCreditNoteManager::createCreditNoteGstView($start_date, $end_date, $chkid_array, true);
                  PurchaseOrderDebitNoteManager::createDebitNoteGstView($start_date, $end_date, $chkid_array);
                  PurchaseOrderDebitNoteManager::createDebitNoteGstView($start_date, $end_date, false, $chkid_array, true);
                  $ineligible_sec = AccountTaxManager::getAllInEligbleITC($start_date, $end_date, $id, 1, $chkid_array);
                  $ineligible_38 = AccountTaxManager::getAllInEligbleITC($start_date, $end_date, $id, 3, $chkid_array);
                  $ineligible_42 = AccountTaxManager::getAllInEligbleITC($start_date, $end_date, $id, 4, $chkid_array);
                  $ineligible_43 = AccountTaxManager::getAllInEligbleITC($start_date, $end_date, $id, 5, $chkid_array);
                  $ineligible_others = AccountTaxManager::getAllInEligbleITC($start_date, $end_date, $id, 2, $chkid_array);

                  $outward_supplies_taxable = AccountTaxManager::getOutwardSuppliesTaxable($start_date, $end_date, $id, $chkid_array);

                  $outward_supplies_nil_exempted = AccountTaxManager::getOutwardSuppliesNilExempted($start_date, $end_date, $id, $chkid_array);
                  $outward_supplies_zero_rated = AccountTaxManager::getOutwardSuppliesZeroRated($start_date, $end_date, $id, $chkid_array);
                  $outward_supplies_non_gst = AccountTaxManager::getOutwardSuppliesNonGST($start_date, $end_date, $id, $chkid_array);

                  $inward_liable_reverse_charge = AccountTaxManager::getInwardSuppliesLiabletoRCM($start_date, $end_date, $id, $chkid_array);

                  $outward_supplies_unregistered = AccountTaxManager::getOutwardSuppliesUnregistered($start_date, $end_date, $id, $chkid_array);

                  $other_itc = AccountTaxManager::getAllOtherITC($start_date, $end_date, $id, $chkid_array);

                  $inward_nil_rated = AccountTaxManager::getInwardSuppliesNilExempted($start_date, $end_date, $id, $chkid_array);

                  $inward_non_gst = AccountTaxManager::getInwardSuppliesNonGST($start_date, $end_date, $id, $chkid_array);

                  $import_of_goods = AccountTaxManager::getImportsOfGoods($start_date, $end_date, $id);

                  $total_outward_inward_taxable_value = $total_outward_inward_taxable_igst = $total_outward_inward_taxable_cgst = $total_outward_inward_taxable_sgst = $total_outward_inward_taxable_tax = $total_outward_inward_taxable_cess = 0;

                  $total_itc_taxable_value = $total_itc_taxable_igst = $total_itc_taxable_cgst = $total_itc_taxable_sgst = $total_itc_taxable_tax = $total_itc_taxable_cess = 0;

                  $total_outward_unreg_taxable_value = $total_outward_unreg_taxable_igst = $total_outward_unreg_taxable_cgst = $total_outward_unreg_taxable_sgst = $total_outward_unreg_taxable_tax = $total_outward_unreg_taxable_cess = 0;

                  $total_nil_exemp_taxable_value = $total_nil_exemp_taxable_igst = $total_nil_exemp_taxable_cgst = $total_nil_exemp_taxable_sgst = $total_nil_exemp_taxable_tax = $total_nil_exemp_taxable_cess = 0;

                  $outward_decrease_liability = AccountTaxManager::getJournalVouchers($id, $start_date, $end_date, SystemTablesStatus::DB_TBL_DECREASE_OF_TAX_LIABILITY, SystemTablesStatus::DB_TBL_1_ADJUSTMENT_AGAINST_CREDIT, 1, $chkid_array);

                  $outward_increase_liability = AccountTaxManager::getJournalVouchers($id, $start_date, $end_date, SystemTablesStatus::DB_TBL_INCREASE_OF_TAX_LIABILITY, SystemTablesStatus::DB_TBL_3_OTHERS, 2, $chkid_array);

                  $outward_reversal = AccountTaxManager::getJournalVouchers($id, $start_date, $end_date, SystemTablesStatus::DB_TBL_REVERSAL_OF_TAX_LIABILITY_INPUT_TAX_CREDIT, SystemTablesStatus::DB_TBL_9_ISD_CREDIT_NOTE_RULE_39_1_J, 1, $chkid_array);

                  if ($outward_supplies_taxable) {
                        if ($outward_decrease_liability) {
                              $outward_supplies_taxable->cgst -= $outward_decrease_liability->cgst;
                              $outward_supplies_taxable->sgst -= $outward_decrease_liability->sgst;
                              $outward_supplies_taxable->igst -= $outward_decrease_liability->igst;
                              $outward_supplies_taxable->tax_amount -= $outward_decrease_liability->tax_amount;
                              $outward_supplies_taxable->taxable_value -= $outward_decrease_liability->taxable_value;
                        }

                        if ($outward_increase_liability) {
                              $outward_supplies_taxable->cgst += $outward_increase_liability->cgst;
                              $outward_supplies_taxable->sgst += $outward_increase_liability->sgst;
                              $outward_supplies_taxable->igst += $outward_increase_liability->igst;
                              $outward_supplies_taxable->tax_amount += $outward_increase_liability->tax_amount;
                              $outward_supplies_taxable->taxable_value += $outward_increase_liability->taxable_value;
                        }

                        if ($outward_reversal) {
                              $outward_supplies_taxable->cgst -= $outward_reversal->cgst;
                              $outward_supplies_taxable->sgst -= $outward_reversal->sgst;
                              $outward_supplies_taxable->igst -= $outward_reversal->igst;
                              $outward_supplies_taxable->tax_amount -= $outward_reversal->tax_amount;
                              $outward_supplies_taxable->taxable_value -= $outward_reversal->taxable_value;
                        }

                        $total_outward_inward_taxable_value += $outward_supplies_taxable->taxable_value;
                        $total_outward_inward_taxable_cgst += $outward_supplies_taxable->cgst;
                        $total_outward_inward_taxable_sgst += $outward_supplies_taxable->sgst;
                        $total_outward_inward_taxable_igst += $outward_supplies_taxable->igst;
                        $total_outward_inward_taxable_tax += $outward_supplies_taxable->tax_amount;
                        $total_outward_inward_taxable_cess += $outward_supplies_taxable->cess_amount;
                  }
                  if ($outward_supplies_zero_rated) {
                        $total_outward_inward_taxable_value += $outward_supplies_zero_rated->taxable_value;
                        $total_outward_inward_taxable_cgst += $outward_supplies_zero_rated->cgst;
                        $total_outward_inward_taxable_sgst += $outward_supplies_zero_rated->sgst;
                        $total_outward_inward_taxable_igst += $outward_supplies_zero_rated->igst;
                        $total_outward_inward_taxable_tax += $outward_supplies_zero_rated->tax_amount;
                        $total_outward_inward_taxable_cess += $outward_supplies_zero_rated->cess_amount;
                  }

                  $reversal_tax_liability_capital = AccountTaxManager::getJournalVouchers($id, $start_date, $end_date, SystemTablesStatus::DB_TBL_REVERSAL_OF_TAX_LIABILITY_INPUT_TAX_CREDIT, SystemTablesStatus::DB_TBL_9_CAPITAL_CREDIT_DUE_TO_EXEMPTED_SUPPLIES_RULE_43_1_H, 1, $chkid_array);

                  $reversal_tax_liability_exempt = AccountTaxManager::getJournalVouchers($id, $start_date, $end_date, SystemTablesStatus::DB_TBL_REVERSAL_OF_TAX_LIABILITY_INPUT_TAX_CREDIT, SystemTablesStatus::DB_TBL_9_EXEMPT_AND_NONBUSINESS_SUPPLIES_RULE_42_1_M, 1, $chkid_array);

                  if ($outward_supplies_nil_exempted) {

                        if ($reversal_tax_liability_capital) {
                              $outward_supplies_nil_exempted->cgst -= $reversal_tax_liability_capital->cgst;
                              $outward_supplies_nil_exempted->sgst -= $reversal_tax_liability_capital->sgst;
                              $outward_supplies_nil_exempted->igst -= $reversal_tax_liability_capital->igst;
                              $outward_supplies_nil_exempted->tax_amount -= $reversal_tax_liability_capital->tax_amount;
                              $outward_supplies_nil_exempted->taxable_value -= $reversal_tax_liability_capital->taxable_value;
                        }

                        if ($reversal_tax_liability_exempt) {
                              $outward_supplies_nil_exempted->cgst -= $reversal_tax_liability_exempt->cgst;
                              $outward_supplies_nil_exempted->sgst -= $reversal_tax_liability_exempt->sgst;
                              $outward_supplies_nil_exempted->igst -= $reversal_tax_liability_exempt->igst;
                              $outward_supplies_nil_exempted->tax_amount -= $reversal_tax_liability_exempt->tax_amount;
                              $outward_supplies_nil_exempted->taxable_value -= $reversal_tax_liability_exempt->taxable_value;
                        }

                        $total_outward_inward_taxable_value += $outward_supplies_nil_exempted->taxable_value;
                        $total_outward_inward_taxable_cgst += $outward_supplies_nil_exempted->cgst;
                        $total_outward_inward_taxable_sgst += $outward_supplies_nil_exempted->sgst;
                        $total_outward_inward_taxable_igst += $outward_supplies_nil_exempted->igst;
                        $total_outward_inward_taxable_tax += $outward_supplies_nil_exempted->tax_amount;
                        $total_outward_inward_taxable_cess += $outward_supplies_nil_exempted->cess_amount;
                  }


                  if ($outward_supplies_non_gst) {
                        $total_outward_inward_taxable_value += $outward_supplies_non_gst->taxable_value;
                        $total_outward_inward_taxable_cgst += $outward_supplies_non_gst->cgst;
                        $total_outward_inward_taxable_sgst += $outward_supplies_non_gst->sgst;
                        $total_outward_inward_taxable_igst += $outward_supplies_non_gst->igst;
                        $total_outward_inward_taxable_tax += $outward_supplies_non_gst->tax_amount;
                        $total_outward_inward_taxable_cess += $outward_supplies_non_gst->cess_amount;
                  }

                  $cancellation = AccountTaxManager::getJournalVouchers($id, $start_date, $end_date, SystemTablesStatus::DB_TBL_DECREASE_OF_TAX_LIABILITY, SystemTablesStatus::DB_TBL_1_CANCELLATION_OF_ADVANCE_PAYMENT_UNDER_REVERSE_CHARGE, 1, $chkid_array);

                  $purchase = AccountTaxManager::getJournalVouchers($id, $start_date, $end_date, SystemTablesStatus::DB_TBL_DECREASE_OF_TAX_LIABILITY, SystemTablesStatus::DB_TBL_1_PURCHASE_AGAINST_ADVANCE_PAYMENT, 1, $chkid_array);

                  $imports_services = AccountTaxManager::getJournalVouchers($id, $start_date, $end_date, SystemTablesStatus::DB_TBL_INCREASE_OF_TAX_LIABILITY, SystemTablesStatus::DB_TBL_3_IMPORTS_OF_SERVICES, 2, $chkid_array);

                  $imports = AccountTaxManager::getJournalVouchers($id, $start_date, $end_date, SystemTablesStatus::DB_TBL_INCREASE_OF_TAX_LIABILITY, SystemTablesStatus::DB_TBL_3_IMPORTS_OF_GOODS, 2, $chkid_array);

                  $imports_goods = AccountTaxManager::getJournalVouchers($id, $start_date, $end_date, SystemTablesStatus::DB_TBL_INCREASE_OF_TAX_LIABILITY, SystemTablesStatus::DB_TBL_3_IMPORTS_OF_CAPTIAL_GOODS, 2, $chkid_array);

                  $purchase_reverse = AccountTaxManager::getJournalVouchers($id, $start_date, $end_date, SystemTablesStatus::DB_TBL_INCREASE_OF_TAX_LIABILITY, SystemTablesStatus::DB_TBL_3_PURCHASE_UNDER_REVERSE_CHARGE, 2, $chkid_array);

                  $tax_paid_reverse = AccountTaxManager::getJournalVouchers($id, $start_date, $end_date, SystemTablesStatus::DB_TBL_INCREASE_OF_TAX_LIABILITY, SystemTablesStatus::DB_TBL_3_TAX_PAID_ON_ADVANCE_UNDER_REVERSE_CHARGE, 2, $chkid_array);

                  $increase_purchase_reverse = AccountTaxManager::getJournalVouchers($id, $start_date, $end_date, SystemTablesStatus::DB_TBL_INCREASE_OF_TAX_LIABILITY_INPUT_TAX_CREDIT, SystemTablesStatus::DB_TBL_4_PURCHASE_UNDER_REVERSE_CHARGE, 2, $chkid_array);

                  $increase_imports_services = AccountTaxManager::getJournalVouchers($id, $start_date, $end_date, SystemTablesStatus::DB_TBL_INCREASE_OF_TAX_LIABILITY_INPUT_TAX_CREDIT, SystemTablesStatus::DB_TBL_4_IMPORTS_OF_SERVICES, 2, $chkid_array);

                  $reversal_imports_services = AccountTaxManager::getJournalVouchers($id, $start_date, $end_date, SystemTablesStatus::DB_TBL_REVERSE_OF_TAX_LIABILITY, SystemTablesStatus::DB_TBL_8_IMPORTS_OF_SERVICES, 1, $chkid_array);

                  $reversal_purchase = AccountTaxManager::getJournalVouchers($id, $start_date, $end_date, SystemTablesStatus::DB_TBL_REVERSE_OF_TAX_LIABILITY, SystemTablesStatus::DB_TBL_8_PURCHASE_UNDER_REVERSE_CHARGE, 1, $chkid_array);

                  $reversal_input_purchase = AccountTaxManager::getJournalVouchers($id, $start_date, $end_date, SystemTablesStatus::DB_TBL_REVERSAL_OF_TAX_LIABILITY_INPUT_TAX_CREDIT, SystemTablesStatus::DB_TBL_9_PURCHASE_UNDER_REVERSE_CHARGE, 1, $chkid_array);

                  $reversal_input_imports_services = AccountTaxManager::getJournalVouchers($id, $start_date, $end_date, SystemTablesStatus::DB_TBL_REVERSAL_OF_TAX_LIABILITY_INPUT_TAX_CREDIT, SystemTablesStatus::DB_TBL_9_IMPORTS_OF_SERVICES, 1, $chkid_array);

                  if ($inward_liable_reverse_charge) {
                        if ($cancellation) {
                              $inward_liable_reverse_charge->cgst -= $cancellation->cgst;
                              $inward_liable_reverse_charge->sgst -= $cancellation->sgst;
                              $inward_liable_reverse_charge->igst -= $cancellation->igst;
                              $inward_liable_reverse_charge->tax_amount -= $cancellation->tax_amount;
                              $inward_liable_reverse_charge->taxable_value -= $cancellation->taxable_value;
                        }
                        if ($reversal_input_imports_services) {
                              $inward_liable_reverse_charge->cgst -= $reversal_input_imports_services->cgst;
                              $inward_liable_reverse_charge->sgst -= $reversal_input_imports_services->sgst;
                              $inward_liable_reverse_charge->igst -= $reversal_input_imports_services->igst;
                              $inward_liable_reverse_charge->tax_amount -= $reversal_input_imports_services->tax_amount;
                              $inward_liable_reverse_charge->taxable_value -= $reversal_input_imports_services->taxable_value;
                        }
                        if ($increase_imports_services) {
                              $inward_liable_reverse_charge->cgst += $increase_purchase_reverse->cgst;
                              $inward_liable_reverse_charge->sgst += $increase_purchase_reverse->sgst;
                              $inward_liable_reverse_charge->igst += $increase_purchase_reverse->igst;
                              $inward_liable_reverse_charge->tax_amount += $increase_purchase_reverse->tax_amount;
                              $inward_liable_reverse_charge->taxable_value += $increase_purchase_reverse->taxable_value;
                        }
                        if ($reversal_input_purchase) {
                              $inward_liable_reverse_charge->cgst -= $reversal_input_purchase->cgst;
                              $inward_liable_reverse_charge->sgst -= $reversal_input_purchase->sgst;
                              $inward_liable_reverse_charge->igst -= $reversal_input_purchase->igst;
                              $inward_liable_reverse_charge->tax_amount -= $reversal_input_purchase->tax_amount;
                              $inward_liable_reverse_charge->taxable_value -= $reversal_input_purchase->taxable_value;
                        }
                        if ($reversal_purchase) {
                              $inward_liable_reverse_charge->cgst -= $reversal_purchase->cgst;
                              $inward_liable_reverse_charge->sgst -= $reversal_purchase->sgst;
                              $inward_liable_reverse_charge->igst -= $reversal_purchase->igst;
                              $inward_liable_reverse_charge->tax_amount -= $reversal_purchase->tax_amount;
                              $inward_liable_reverse_charge->taxable_value -= $reversal_purchase->taxable_value;
                        }
                        if ($reversal_imports_services) {
                              $inward_liable_reverse_charge->cgst -= $reversal_imports_services->cgst;
                              $inward_liable_reverse_charge->sgst -= $reversal_imports_services->sgst;
                              $inward_liable_reverse_charge->igst -= $reversal_imports_services->igst;
                              $inward_liable_reverse_charge->tax_amount -= $reversal_imports_services->tax_amount;
                              $inward_liable_reverse_charge->taxable_value -= $reversal_imports_services->taxable_value;
                        }
                        if ($increase_purchase_reverse) {
                              $inward_liable_reverse_charge->cgst += $increase_purchase_reverse->cgst;
                              $inward_liable_reverse_charge->sgst += $increase_purchase_reverse->sgst;
                              $inward_liable_reverse_charge->igst += $increase_purchase_reverse->igst;
                              $inward_liable_reverse_charge->tax_amount += $increase_purchase_reverse->tax_amount;
                              $inward_liable_reverse_charge->taxable_value += $increase_purchase_reverse->taxable_value;
                        }
                        if ($tax_paid_reverse) {
                              $inward_liable_reverse_charge->cgst += $tax_paid_reverse->cgst;
                              $inward_liable_reverse_charge->sgst += $tax_paid_reverse->sgst;
                              $inward_liable_reverse_charge->igst += $tax_paid_reverse->igst;
                              $inward_liable_reverse_charge->tax_amount += $tax_paid_reverse->tax_amount;
                              $inward_liable_reverse_charge->taxable_value += $tax_paid_reverse->taxable_value;
                        }
                        if ($purchase_reverse) {
                              $inward_liable_reverse_charge->cgst += $purchase_reverse->cgst;
                              $inward_liable_reverse_charge->sgst += $purchase_reverse->sgst;
                              $inward_liable_reverse_charge->igst += $purchase_reverse->igst;
                              $inward_liable_reverse_charge->tax_amount += $purchase_reverse->tax_amount;
                              $inward_liable_reverse_charge->taxable_value += $purchase_reverse->taxable_value;
                        }
                        if ($imports_goods) {
                              $inward_liable_reverse_charge->cgst += $imports_goods->cgst;
                              $inward_liable_reverse_charge->sgst += $imports_goods->sgst;
                              $inward_liable_reverse_charge->igst += $imports_goods->igst;
                              $inward_liable_reverse_charge->tax_amount += $imports_goods->tax_amount;
                              $inward_liable_reverse_charge->taxable_value += $imports_goods->taxable_value;
                        }
                        if ($imports_services) {
                              $inward_liable_reverse_charge->cgst += $imports_services->cgst;
                              $inward_liable_reverse_charge->sgst += $imports_services->sgst;
                              $inward_liable_reverse_charge->igst += $imports_services->igst;
                              $inward_liable_reverse_charge->tax_amount += $imports_services->tax_amount;
                              $inward_liable_reverse_charge->taxable_value += $imports_services->taxable_value;
                        }
                        if ($imports) {
                              $inward_liable_reverse_charge->cgst += $imports->cgst;
                              $inward_liable_reverse_charge->sgst += $imports->sgst;
                              $inward_liable_reverse_charge->igst += $imports->igst;
                              $inward_liable_reverse_charge->tax_amount += $imports->tax_amount;
                              $inward_liable_reverse_charge->taxable_value += $imports->taxable_value;
                        }
                        if ($purchase) {
                              $inward_liable_reverse_charge->cgst -= $purchase->cgst;
                              $inward_liable_reverse_charge->sgst -= $purchase->sgst;
                              $inward_liable_reverse_charge->igst -= $purchase->igst;
                              $inward_liable_reverse_charge->tax_amount -= $purchase->tax_amount;
                              $inward_liable_reverse_charge->taxable_value -= $purchase->taxable_value;
                        }
                        if ($cancellation) {
                              $inward_liable_reverse_charge->cgst -= $cancellation->cgst;
                              $inward_liable_reverse_charge->sgst -= $cancellation->sgst;
                              $inward_liable_reverse_charge->igst -= $cancellation->igst;
                              $inward_liable_reverse_charge->taxable_value -= $cancellation->taxable_value;
                        }
                        $total_outward_inward_taxable_value += $inward_liable_reverse_charge->taxable_value;
                        $total_outward_inward_taxable_cgst += $inward_liable_reverse_charge->cgst;
                        $total_outward_inward_taxable_sgst += $inward_liable_reverse_charge->sgst;
                        $total_outward_inward_taxable_igst += $inward_liable_reverse_charge->igst;
                        $total_outward_inward_taxable_tax += $inward_liable_reverse_charge->tax_amount;
                        $total_outward_inward_taxable_cess += $inward_liable_reverse_charge->cess_amount;
                  }

                  if ($outward_supplies_unregistered) {
                        $total_outward_unreg_taxable_value += $outward_supplies_unregistered->taxable_value;
                        $total_outward_unreg_taxable_cgst += $outward_supplies_unregistered->cgst;
                        $total_outward_unreg_taxable_sgst += $outward_supplies_unregistered->sgst;
                        $total_outward_unreg_taxable_igst += $outward_supplies_unregistered->igst;
                        $total_outward_unreg_taxable_tax += $outward_supplies_unregistered->tax_amount;
                        $total_outward_unreg_taxable_cess += $outward_supplies_unregistered->cess_amount;
                  }

                  $purchase_from_sez = AccountTaxManager::getJournalVouchers($id, $start_date, $end_date, SystemTablesStatus::DB_TBL_INCREASE_OF_INPUT_TAX_CREDIT, SystemTablesStatus::DB_TBL_2_PURCHASE_FROM_SEZ, 1, $chkid_array);

                  $reclaim_buyer = AccountTaxManager::getJournalVouchers($id, $start_date, $end_date, SystemTablesStatus::DB_TBL_INCREASE_OF_INPUT_TAX_CREDIT, SystemTablesStatus::DB_TBL_2_RECLAIM_OF_REVERSAL_ITC_ON_ACCOUNT_OF_BUYER_PAYMENT, 1, $chkid_array);

                  $reclaim_itc = AccountTaxManager::getJournalVouchers($id, $start_date, $end_date, SystemTablesStatus::DB_TBL_INCREASE_OF_INPUT_TAX_CREDIT, SystemTablesStatus::DB_TBL_2_RECLAIM_OF_REVERSAL_ITC_RULE_42_2_B, 1, $chkid_array);
                  $increase_input_goods = AccountTaxManager::getJournalVouchers($id, $start_date, $end_date, SystemTablesStatus::DB_TBL_INCREASE_OF_INPUT_TAX_CREDIT, SystemTablesStatus::DB_TBL_2_IMPORTS_OF_GOODS, 1, $chkid_array);

                  $increase_input_capital_goods = AccountTaxManager::getJournalVouchers($id, $start_date, $end_date, SystemTablesStatus::DB_TBL_INCREASE_OF_INPUT_TAX_CREDIT, SystemTablesStatus::DB_TBL_2_IMPORTS_OF_CAPTIAL_GOODS, 1, $chkid_array);
                  $increase_input_services = AccountTaxManager::getJournalVouchers($id, $start_date, $end_date, SystemTablesStatus::DB_TBL_INCREASE_OF_INPUT_TAX_CREDIT, SystemTablesStatus::DB_TBL_2_IMPORTS_OF_SERVICES, 1, $chkid_array);

                  $increase_input_tcs = AccountTaxManager::getJournalVouchers($id, $start_date, $end_date, SystemTablesStatus::DB_TBL_INCREASE_OF_INPUT_TAX_CREDIT, SystemTablesStatus::DB_TBL_2_TCS_ADJUSTMENT, 1, $chkid_array);

                  if ($import_of_goods) {
                        if ($purchase_from_sez) {
                              $import_of_goods->cgst += $purchase_from_sez->cgst;
                              $import_of_goods->sgst += $purchase_from_sez->sgst;
                              $import_of_goods->igst += $purchase_from_sez->igst;
                              $import_of_goods->tax_amount += $purchase_from_sez->tax_amount;
                              $import_of_goods->taxable_value += $purchase_from_sez->taxable_value;
                        }
                        if ($reclaim_buyer) {
                              $import_of_goods->cgst += $reclaim_buyer->cgst;
                              $import_of_goods->sgst += $reclaim_buyer->sgst;
                              $import_of_goods->igst += $reclaim_buyer->igst;
                              $import_of_goods->tax_amount += $reclaim_buyer->tax_amount;
                              $import_of_goods->taxable_value += $reclaim_buyer->taxable_value;
                        }
                        //                    if ($reclaim_itc)
                        //                    {
                        //                        $import_of_goods->cgst += $reclaim_itc->cgst;
                        //                        $import_of_goods->sgst += $reclaim_itc->sgst;
                        //                        $import_of_goods->igst += $reclaim_itc->igst;
                        //                        $import_of_goods->tax_amount += $reclaim_itc->tax_amount;
                        //                        $import_of_goods->taxable_value += $reclaim_itc->taxable_value;
                        //                    }
                        if ($increase_input_goods) {
                              $import_of_goods->cgst += $increase_input_goods->cgst;
                              $import_of_goods->sgst += $increase_input_goods->sgst;
                              $import_of_goods->igst += $increase_input_goods->igst;
                              $import_of_goods->tax_amount += $increase_input_goods->tax_amount;
                              $import_of_goods->taxable_value += $increase_input_goods->taxable_value;
                        }
                        if ($increase_input_capital_goods) {
                              $import_of_goods->cgst += $increase_input_capital_goods->cgst;
                              $import_of_goods->sgst += $increase_input_capital_goods->sgst;
                              $import_of_goods->igst += $increase_input_capital_goods->igst;
                              $import_of_goods->tax_amount += $increase_input_capital_goods->tax_amount;
                              $import_of_goods->taxable_value += $increase_input_capital_goods->taxable_value;
                        }

                        if ($increase_input_tcs) {
                              $import_of_goods->cgst += $increase_input_tcs->cgst;
                              $import_of_goods->sgst += $increase_input_tcs->sgst;
                              $import_of_goods->igst += $increase_input_tcs->igst;
                              $import_of_goods->tax_amount += $increase_input_tcs->tax_amount;
                              $import_of_goods->taxable_value += $increase_input_tcs->taxable_value;
                        }
                        $total_itc_taxable_value += $import_of_goods->taxable_value;
                        $total_itc_taxable_cgst += $import_of_goods->cgst;
                        $total_itc_taxable_sgst += $import_of_goods->sgst;
                        $total_itc_taxable_igst += $import_of_goods->igst;
                        $total_itc_taxable_tax += $import_of_goods->tax_amount;
                  }


                  if ($increase_input_services) {
                        $total_itc_taxable_cgst += $increase_input_services->cgst;
                        $total_itc_taxable_sgst += $increase_input_services->sgst;
                        $total_itc_taxable_igst += $increase_input_services->igst;
                        $total_itc_taxable_tax += $increase_input_services->tax_amount;
                        $total_itc_taxable_value += $increase_input_services->taxable_value;
                  }

                  $increase_input_purchase = AccountTaxManager::getJournalVouchers($id, $start_date, $end_date, SystemTablesStatus::DB_TBL_INCREASE_OF_INPUT_TAX_CREDIT, SystemTablesStatus::DB_TBL_2_PURCHASE_UNDER_REVERSE_CHARGE, 1, $chkid_array);

                  $increase_credit_purchase = AccountTaxManager::getJournalVouchers($id, $start_date, $end_date, SystemTablesStatus::DB_TBL_INCREASE_OF_TAX_LIABILITY_INPUT_TAX_CREDIT, SystemTablesStatus::DB_TBL_4_PURCHASE_UNDER_REVERSE_CHARGE, 1, $chkid_array);
                  $increase_credit_import_services = AccountTaxManager::getJournalVouchers($id, $start_date, $end_date, SystemTablesStatus::DB_TBL_INCREASE_OF_TAX_LIABILITY_INPUT_TAX_CREDIT, SystemTablesStatus::DB_TBL_4_IMPORTS_OF_SERVICES, 1, $chkid_array);

                  if ($increase_input_purchase) {
                        if ($increase_credit_purchase) {
                              $increase_input_purchase->cgst += $increase_credit_purchase->cgst;
                              $increase_input_purchase->sgst += $increase_credit_purchase->sgst;
                              $increase_input_purchase->igst += $increase_credit_purchase->igst;
                              $increase_input_purchase->tax_amount += $increase_credit_purchase->tax_amount;
                              $increase_input_purchase->taxable_value += $increase_credit_purchase->taxable_value;
                        }

                        if ($increase_credit_import_services) {
                              $increase_input_purchase->cgst += $increase_credit_import_services->cgst;
                              $increase_input_purchase->sgst += $increase_credit_import_services->sgst;
                              $increase_input_purchase->igst += $increase_credit_import_services->igst;
                              $increase_input_purchase->tax_amount += $increase_credit_import_services->tax_amount;
                              $increase_input_purchase->taxable_value += $increase_credit_import_services->taxable_value;
                        }

                        $total_itc_taxable_cgst += $increase_input_purchase->cgst;
                        $total_itc_taxable_sgst += $increase_input_purchase->sgst;
                        $total_itc_taxable_igst += $increase_input_purchase->igst;
                        $total_itc_taxable_tax += $increase_input_purchase->tax_amount;
                        $total_itc_taxable_value += $increase_input_purchase->taxable_value;
                  }

                  $increase_input_isd = AccountTaxManager::getJournalVouchers($id, $start_date, $end_date, SystemTablesStatus::DB_TBL_INCREASE_OF_INPUT_TAX_CREDIT, SystemTablesStatus::DB_TBL_2_ISD_TRANSFER, 1, $chkid_array);

                  if ($increase_input_isd) {
                        $total_itc_taxable_cgst += $increase_input_isd->cgst;
                        $total_itc_taxable_sgst += $increase_input_isd->sgst;
                        $total_itc_taxable_igst += $increase_input_isd->igst;
                        $total_itc_taxable_tax += $increase_input_isd->tax_amount;
                        $total_itc_taxable_value += $increase_input_isd->taxable_value;
                  }

                  $increase_input_tds = AccountTaxManager::getJournalVouchers($id, $start_date, $end_date, SystemTablesStatus::DB_TBL_INCREASE_OF_INPUT_TAX_CREDIT, SystemTablesStatus::DB_TBL_2_TDS_ADJUSTMENT, 1, $chkid_array);

                  $increase_input_transitional = AccountTaxManager::getJournalVouchers($id, $start_date, $end_date, SystemTablesStatus::DB_TBL_INCREASE_OF_INPUT_TAX_CREDIT, SystemTablesStatus::DB_TBL_2_TRANSITIONAL_CREDIT, 1, $chkid_array);

                  $increase_input_other = AccountTaxManager::getJournalVouchers($id, $start_date, $end_date, SystemTablesStatus::DB_TBL_INCREASE_OF_INPUT_TAX_CREDIT, SystemTablesStatus::DB_TBL_2_OTHER, 1, $chkid_array);

                  if ($other_itc) {
                        if ($increase_input_tds) {
                              $other_itc->cgst += $increase_input_tds->cgst;
                              $other_itc->sgst += $increase_input_tds->sgst;
                              $other_itc->igst += $increase_input_tds->igst;
                              $other_itc->tax_amount += $increase_input_tds->tax_amount;
                              $other_itc->taxable_value += $increase_input_tds->taxable_value;
                        }
                        if ($increase_input_transitional) {
                              $other_itc->cgst += $increase_input_transitional->cgst;
                              $other_itc->sgst += $increase_input_transitional->sgst;
                              $other_itc->igst += $increase_input_transitional->igst;
                              $other_itc->tax_amount += $increase_input_transitional->tax_amount;
                              $other_itc->taxable_value += $increase_input_transitional->taxable_value;
                        }

                        if ($increase_input_other) {
                              $other_itc->cgst += $increase_input_other->cgst;
                              $other_itc->sgst += $increase_input_other->sgst;
                              $other_itc->igst += $increase_input_other->igst;
                              $other_itc->tax_amount += $increase_input_other->tax_amount;
                              $other_itc->taxable_value += $increase_input_other->taxable_value;
                        }

                        $total_itc_taxable_value += $other_itc->taxable_value;
                        $total_itc_taxable_cgst += $other_itc->cgst;
                        $total_itc_taxable_sgst += $other_itc->sgst;
                        $total_itc_taxable_igst += $other_itc->igst;
                        $total_itc_taxable_tax += $other_itc->tax_amount;
                        $total_itc_taxable_cess += $other_itc->cess_amount;
                  }

                  $total_nil_inter = $total_intra = 0;
                  $total_nil_intra = 0;
                  if ($inward_nil_rated) {
                        $total_nil_exemp_taxable_value += $inward_nil_rated->taxable_value;
                        $total_nil_exemp_taxable_cgst += $inward_nil_rated->cgst;
                        $total_nil_exemp_taxable_sgst += $inward_nil_rated->sgst;
                        $total_nil_exemp_taxable_igst += $inward_nil_rated->igst;
                        $total_nil_exemp_taxable_tax += $inward_nil_rated->tax_amount;
                        $total_nil_exemp_taxable_cess += $inward_nil_rated->cess_amount;

                        $total_nil_inter += $inward_nil_rated->inter;
                        $total_nil_intra += $inward_nil_rated->intra;
                  }


                  if ($inward_non_gst) {
                        $total_nil_exemp_taxable_value += $inward_non_gst->taxable_value;
                        $total_nil_exemp_taxable_cgst += $inward_non_gst->cgst;
                        $total_nil_exemp_taxable_sgst += $inward_non_gst->sgst;
                        $total_nil_exemp_taxable_igst += $inward_non_gst->igst;
                        $total_nil_exemp_taxable_tax += $inward_non_gst->tax_amount;
                        $total_nil_exemp_taxable_cess += $inward_non_gst->cess_amount;

                        $total_nil_inter += $inward_non_gst->inter;
                        $total_nil_intra += $inward_non_gst->intra;
                  }

                  $increase_input_claim = AccountTaxManager::getJournalVouchers($id, $start_date, $end_date, SystemTablesStatus::DB_TBL_REVERSAL_OF_TAX_LIABILITY_INPUT_TAX_CREDIT, SystemTablesStatus::DB_TBL_9_ON_ACCOUNT_OF_CLAIMING_MORE_RULE_42_2_A, 2, $chkid_array);

                  $increase_input_capital_credit = AccountTaxManager::getJournalVouchers($id, $start_date, $end_date, SystemTablesStatus::DB_TBL_REVERSAL_OF_TAX_LIABILITY_INPUT_TAX_CREDIT, SystemTablesStatus::DB_TBL_9_CAPITAL_CREDIT_DUE_TO_EXEMPTED_SUPPLIES_RULE_43_1_H, 2, $chkid_array);

                  $increase_input_exempt = AccountTaxManager::getJournalVouchers($id, $start_date, $end_date, SystemTablesStatus::DB_TBL_REVERSAL_OF_TAX_LIABILITY_INPUT_TAX_CREDIT, SystemTablesStatus::DB_TBL_9_EXEMPT_AND_NONBUSINESS_SUPPLIES_RULE_42_1_M, 2, $chkid_array);

                  $increase_credit_ineligible_credit = AccountTaxManager::getJournalVouchers($id, $start_date, $end_date, SystemTablesStatus::DB_TBL_REVERSAL_OF_TAX_LIABILITY_INPUT_TAX_CREDIT, SystemTablesStatus::DB_TBL_9_INELIGIBLE_CREDIT, 2, $chkid_array);
                  $total_itc_reversed = new stdClass();
                  $total_itc_reversed->cgst = $total_itc_reversed->sgst = $total_itc_reversed->igst = 0;
                  $total_itc_reversed->tax_amount = 0;
                  $total_itc_reversed->taxable_value = 0;
                  if ($increase_input_claim) {
                        if ($ineligible_38) {
                              $increase_input_claim->cgst += $ineligible_38->cgst;
                              $increase_input_claim->sgst += $ineligible_38->sgst;
                              $increase_input_claim->igst += $ineligible_38->igst;
                              $increase_input_claim->tax_amount += $ineligible_38->tax_amount;
                              $increase_input_claim->taxable_value += $ineligible_38->taxable_value;
                        }
                        if ($ineligible_42) {
                              $increase_input_claim->cgst += $ineligible_42->cgst;
                              $increase_input_claim->sgst += $ineligible_42->sgst;
                              $increase_input_claim->igst += $ineligible_42->igst;
                              $increase_input_claim->tax_amount += $ineligible_42->tax_amount;
                              $increase_input_claim->taxable_value += $ineligible_42->taxable_value;
                        }
                        if ($ineligible_43) {
                              $increase_input_claim->cgst += $ineligible_43->cgst;
                              $increase_input_claim->sgst += $ineligible_43->sgst;
                              $increase_input_claim->igst += $ineligible_43->igst;
                              $increase_input_claim->tax_amount += $ineligible_43->tax_amount;
                              $increase_input_claim->taxable_value += $ineligible_43->taxable_value;
                        }
                        if ($ineligible_sec) {
                              $increase_input_claim->cgst += $ineligible_sec->cgst;
                              $increase_input_claim->sgst += $ineligible_sec->sgst;
                              $increase_input_claim->igst += $ineligible_sec->igst;
                              $increase_input_claim->tax_amount += $ineligible_sec->tax_amount;
                              $increase_input_claim->taxable_value += $ineligible_sec->taxable_value;
                        }
                        if ($increase_input_capital_credit) {
                              $increase_input_claim->cgst += $increase_input_capital_credit->cgst;
                              $increase_input_claim->sgst += $increase_input_capital_credit->sgst;
                              $increase_input_claim->igst += $increase_input_capital_credit->igst;
                              $increase_input_claim->tax_amount += $increase_input_capital_credit->tax_amount;
                              $increase_input_claim->taxable_value += $increase_input_capital_credit->taxable_value;
                        }
                        if ($increase_input_exempt) {
                              $increase_input_claim->cgst += $increase_input_exempt->cgst;
                              $increase_input_claim->sgst += $increase_input_exempt->sgst;
                              $increase_input_claim->igst += $increase_input_exempt->igst;
                              $increase_input_claim->tax_amount += $increase_input_exempt->tax_amount;
                              $increase_input_claim->taxable_value += $increase_input_exempt->taxable_value;
                        }
                        if ($increase_credit_ineligible_credit) {
                              $total_count += $increase_credit_ineligible_credit->count;
                              $total_voucher_count += $increase_credit_ineligible_credit->count;
                              $increase_input_claim->cgst += $increase_credit_ineligible_credit->cgst;
                              $increase_input_claim->sgst += $increase_credit_ineligible_credit->sgst;
                              $increase_input_claim->igst += $increase_credit_ineligible_credit->igst;
                              $increase_input_claim->tax_amount += $increase_credit_ineligible_credit->tax_amount;
                              $increase_input_claim->taxable_value += $increase_credit_ineligible_credit->taxable_value;
                        }
                        $total_itc_reversed->cgst += $increase_input_claim->cgst;
                        $total_itc_reversed->sgst += $increase_input_claim->sgst;
                        $total_itc_reversed->igst += $increase_input_claim->igst;
                        $total_itc_reversed->tax_amount += $increase_input_claim->tax_amount;
                        $total_itc_reversed->taxable_value += $increase_input_claim->taxable_value;

                        $total_itc_taxable_cgst -= $increase_input_claim->cgst;
                        $total_itc_taxable_sgst -= $increase_input_claim->sgst;
                        $total_itc_taxable_igst -= $increase_input_claim->igst;
                        $total_itc_taxable_tax -= $increase_input_claim->tax_amount;
                        $total_itc_taxable_value -= $increase_input_claim->taxable_value;
                  }

                  $increase_credit_ineligible_credit = new stdClass();
                  $increase_credit_ineligible_credit->tax_amount = 0;
                  $increase_credit_ineligible_credit->cgst = 0;
                  $increase_credit_ineligible_credit->sgst = 0;
                  $increase_credit_ineligible_credit->igst = 0;
                  $increase_credit_ineligible_credit->taxable_value = 0;

                  $increase_credit_isd_credit = AccountTaxManager::getJournalVouchers($id, $start_date, $end_date, SystemTablesStatus::DB_TBL_REVERSAL_OF_TAX_LIABILITY_INPUT_TAX_CREDIT, SystemTablesStatus::DB_TBL_9_ISD_CREDIT_NOTE_RULE_39_1_J, 2, $chkid_array);
                  $increase_credit_non_payment = AccountTaxManager::getJournalVouchers($id, $start_date, $end_date, SystemTablesStatus::DB_TBL_REVERSAL_OF_TAX_LIABILITY_INPUT_TAX_CREDIT, SystemTablesStatus::DB_TBL_9_NON_PAYMENT_TO_THE_BUYER_RULE_37_2, 2, $chkid_array);
                  $increase_credit_purchase_reverse = AccountTaxManager::getJournalVouchers($id, $start_date, $end_date, SystemTablesStatus::DB_TBL_REVERSAL_OF_TAX_LIABILITY_INPUT_TAX_CREDIT, SystemTablesStatus::DB_TBL_9_PURCHASE_UNDER_REVERSE_CHARGE, 2, $chkid_array);
                  $increase_credit_others = AccountTaxManager::getJournalVouchers($id, $start_date, $end_date, SystemTablesStatus::DB_TBL_REVERSAL_OF_TAX_LIABILITY_INPUT_TAX_CREDIT, SystemTablesStatus::DB_TBL_9_OTHERS, 2, $chkid_array);
                  $increase_credit_not_applicable = AccountTaxManager::getJournalVouchers($id, $start_date, $end_date, SystemTablesStatus::DB_TBL_REVERSAL_OF_TAX_LIABILITY_INPUT_TAX_CREDIT, SystemTablesStatus::DB_TBL_9_NOT_APPLICABLE, 2, $chkid_array);
                  $increase_input_credit_import_services = AccountTaxManager::getJournalVouchers($id, $start_date, $end_date, SystemTablesStatus::DB_TBL_REVERSAL_OF_TAX_LIABILITY_INPUT_TAX_CREDIT, SystemTablesStatus::DB_TBL_9_IMPORTS_OF_SERVICES, 2, $chkid_array);

                  if ($increase_credit_ineligible_credit) {
                        if ($ineligible_others) {
                              $increase_credit_ineligible_credit->cgst += $ineligible_others->cgst;
                              $increase_credit_ineligible_credit->sgst += $ineligible_others->sgst;
                              $increase_credit_ineligible_credit->igst += $ineligible_others->igst;
                              $increase_credit_ineligible_credit->tax_amount += $ineligible_others->tax_amount;
                              $increase_credit_ineligible_credit->taxable_value += $ineligible_others->taxable_value;
                        }
                        if ($increase_credit_isd_credit) {
                              $increase_credit_ineligible_credit->cgst += $increase_credit_isd_credit->cgst;
                              $increase_credit_ineligible_credit->sgst += $increase_credit_isd_credit->sgst;
                              $increase_credit_ineligible_credit->igst += $increase_credit_isd_credit->igst;
                              $increase_credit_ineligible_credit->tax_amount += $increase_credit_isd_credit->tax_amount;
                              $increase_credit_ineligible_credit->taxable_value += $increase_credit_isd_credit->taxable_value;
                        }
                        if ($increase_credit_non_payment) {
                              $increase_credit_ineligible_credit->cgst += $increase_credit_non_payment->cgst;
                              $increase_credit_ineligible_credit->sgst += $increase_credit_non_payment->sgst;
                              $increase_credit_ineligible_credit->igst += $increase_credit_non_payment->igst;
                              $increase_credit_ineligible_credit->tax_amount += $increase_credit_non_payment->tax_amount;
                              $increase_credit_ineligible_credit->taxable_value += $increase_credit_non_payment->taxable_value;
                        }
                        if ($increase_credit_purchase_reverse) {
                              $increase_credit_ineligible_credit->cgst += $increase_credit_purchase_reverse->cgst;
                              $increase_credit_ineligible_credit->sgst += $increase_credit_purchase_reverse->sgst;
                              $increase_credit_ineligible_credit->igst += $increase_credit_purchase_reverse->igst;
                              $increase_credit_ineligible_credit->tax_amount += $increase_credit_purchase_reverse->tax_amount;
                              $increase_credit_ineligible_credit->taxable_value += $increase_credit_purchase_reverse->taxable_value;
                        }
                        if ($increase_credit_others) {
                              $increase_credit_ineligible_credit->cgst += $increase_credit_others->cgst;
                              $increase_credit_ineligible_credit->sgst += $increase_credit_others->sgst;
                              $increase_credit_ineligible_credit->igst += $increase_credit_others->igst;
                              $increase_credit_ineligible_credit->tax_amount += $increase_credit_others->tax_amount;
                              $increase_credit_ineligible_credit->taxable_value += $increase_credit_others->taxable_value;
                        }
                        if ($increase_credit_not_applicable) {
                              $increase_credit_ineligible_credit->cgst += $increase_credit_not_applicable->cgst;
                              $increase_credit_ineligible_credit->sgst += $increase_credit_not_applicable->sgst;
                              $increase_credit_ineligible_credit->igst += $increase_credit_not_applicable->igst;
                              $increase_credit_ineligible_credit->tax_amount += $increase_credit_not_applicable->tax_amount;
                              $increase_credit_ineligible_credit->taxable_value += $increase_credit_not_applicable->taxable_value;
                        }
                        if ($increase_input_credit_import_services) {
                              $increase_credit_ineligible_credit->cgst += $increase_input_credit_import_services->cgst;
                              $increase_credit_ineligible_credit->sgst += $increase_input_credit_import_services->sgst;
                              $increase_credit_ineligible_credit->igst += $increase_input_credit_import_services->igst;
                              $increase_credit_ineligible_credit->tax_amount += $increase_input_credit_import_services->tax_amount;
                              $increase_credit_ineligible_credit->taxable_value += $increase_input_credit_import_services->taxable_value;
                        }
                        $total_itc_reversed->cgst += $increase_credit_ineligible_credit->cgst;
                        $total_itc_reversed->sgst += $increase_credit_ineligible_credit->sgst;
                        $total_itc_reversed->igst += $increase_credit_ineligible_credit->igst;
                        $total_itc_reversed->tax_amount += $increase_credit_ineligible_credit->tax_amount;
                        $total_itc_reversed->taxable_value += $increase_credit_ineligible_credit->taxable_value;

                        $total_itc_taxable_cgst -= $increase_credit_ineligible_credit->cgst;
                        $total_itc_taxable_sgst -= $increase_credit_ineligible_credit->sgst;
                        $total_itc_taxable_igst -= $increase_credit_ineligible_credit->igst;
                        $total_itc_taxable_tax -= $increase_credit_ineligible_credit->tax_amount;
                        $total_itc_taxable_value -= $increase_credit_ineligible_credit->taxable_value;
                  }



                  $import_of_services = $increase_input_services;
                  $imports_reverse_charge = $increase_input_purchase;
                  $increase_input_isd = $increase_input_isd;
                  $itc_reverse_cgst = $increase_input_claim;

                  $itc_reverse_other = $increase_credit_ineligible_credit;

                  $total_ineligible_taxable_value = $total_ineligible_taxable_igst = $total_ineligible_taxable_cgst = $total_ineligible_taxable_sgst = $total_ineligible_taxable_tax = $total_ineligible_taxable_cess = 0;
                  //                if ($ineligible_sec)
                  //                {
                  //                    $total_ineligible_taxable_value += $ineligible_sec->taxable_value;
                  //                    $total_ineligible_taxable_igst += $ineligible_sec->igst;
                  //                    $total_ineligible_taxable_cgst += $ineligible_sec->cgst;
                  //                    $total_ineligible_taxable_sgst += $ineligible_sec->sgst;
                  //                    $total_ineligible_taxable_tax += $ineligible_sec->tax_amount;
                  //                    $total_ineligible_taxable_cess += $ineligible_sec->cess_amount;
                  //                }
                  //                if ($ineligible_others)
                  //                {
                  //                    $total_ineligible_taxable_value += $ineligible_others->taxable_value;
                  //                    $total_ineligible_taxable_igst += $ineligible_others->igst;
                  //                    $total_ineligible_taxable_cgst += $ineligible_others->cgst;
                  //                    $total_ineligible_taxable_sgst += $ineligible_others->sgst;
                  //                    $total_ineligible_taxable_tax += $ineligible_others->tax_amount;
                  //                    $total_ineligible_taxable_cess += $ineligible_others->cess_amount;
                  //                }

                  $ineligible_16 = AccountTaxManager::getAllInEligbleITC($start_date, $end_date, $id, 6, $chkid_array);
                  $ineligible_pos = AccountTaxManager::getAllInEligbleITC($start_date, $end_date, $id, 7, $chkid_array);
                  if ($reclaim_itc) {
                        $total_ineligible_taxable_value += $reclaim_itc->taxable_value;
                        $total_ineligible_taxable_igst += $reclaim_itc->igst;
                        $total_ineligible_taxable_cgst += $reclaim_itc->cgst;
                        $total_ineligible_taxable_sgst += $reclaim_itc->sgst;
                        $total_ineligible_taxable_tax += $reclaim_itc->tax_amount;
                  }
                  if ($ineligible_16) {
                        if ($ineligible_pos) {
                              $ineligible_16->cgst += $ineligible_pos->cgst;
                              $ineligible_16->sgst += $ineligible_pos->sgst;
                              $ineligible_16->igst += $ineligible_pos->igst;
                              $ineligible_16->tax_amount += $ineligible_pos->tax_amount;
                              $ineligible_16->taxable_value += $ineligible_pos->taxable_value;
                              $ineligible_16->cess_amount += $ineligible_pos->cess_amount;
                        }
                        $total_ineligible_taxable_value += $ineligible_16->taxable_value;
                        $total_ineligible_taxable_igst += $ineligible_16->igst;
                        $total_ineligible_taxable_cgst += $ineligible_16->cgst;
                        $total_ineligible_taxable_sgst += $ineligible_16->sgst;
                        $total_ineligible_taxable_tax += $ineligible_16->tax_amount;
                        $total_ineligible_taxable_cess += $ineligible_16->cess_amount;
                  }


                  $phpExcelObject = new PHPExcel();

                  $styleArrayCenter = array(
                        'alignment' => array(
                              'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
                        )
                  );
                  $styleBoldArray = array(
                        'font' => array(
                              'bold' => true,
                        )
                  );

                  $styleBoldItalicArray = array(
                        'font' => array(
                              'bold' => true,
                              'italic' => TRUE,
                        )
                  );
                  $styleItalicArray = array(
                        'font' => array(
                              'italic' => TRUE,
                        )
                  );

                  $unregistered_details = AccountTaxManager::getOutwardSuppliesUnregisteredDetails($start_date, $end_date, $id);

                  $sheetIncrement = 0;

                  $phpExcelObject->setActiveSheetIndex($sheetIncrement);

                  $phpExcelObject->getActiveSheet()->setTitle('GSTR3B');

                  $countheader = 1;
                  $phpExcelObject->getActiveSheet()->SetCellValue('C1', "GSTR3B");
                  $phpExcelObject->getActiveSheet()->getStyle('C1')->applyFromArray($styleArrayCenter);
                  $phpExcelObject->getActiveSheet()->getStyle('C1')->applyFromArray($styleBoldItalicArray);

                  $phpExcelObject->getActiveSheet()->SetCellValue('A5', "GSTIN");
                  $phpExcelObject->getActiveSheet()->getStyle('A5')->applyFromArray($styleArrayCenter);
                  $phpExcelObject->getActiveSheet()->getStyle('A5')->applyFromArray($styleBoldItalicArray);
                  $phpExcelObject->getActiveSheet()->SetCellValue('B5', Utility::variableGet("gstin"));
                  $phpExcelObject->getActiveSheet()->getStyle('B5')->applyFromArray($styleArrayCenter);
                  $phpExcelObject->getActiveSheet()->getStyle('B5')->applyFromArray($styleItalicArray);

                  $phpExcelObject->getActiveSheet()->SetCellValue('C5', "Year");
                  $phpExcelObject->getActiveSheet()->getStyle('C5')->applyFromArray($styleArrayCenter);
                  $phpExcelObject->getActiveSheet()->getStyle('C5')->applyFromArray($styleBoldItalicArray);
                  $phpExcelObject->getActiveSheet()->SetCellValue('D5', $year);
                  $phpExcelObject->getActiveSheet()->getStyle('D5')->applyFromArray($styleArrayCenter);

                  $phpExcelObject->getActiveSheet()->SetCellValue('A6', "Legal name of the registered person");
                  $phpExcelObject->getActiveSheet()->getStyle('A6')->applyFromArray($styleArrayCenter);
                  $phpExcelObject->getActiveSheet()->getStyle('A6')->applyFromArray($styleBoldItalicArray);
                  $phpExcelObject->getActiveSheet()->SetCellValue('B6', Utility::variableGet("company_name"));
                  $phpExcelObject->getActiveSheet()->getStyle('B6')->applyFromArray($styleArrayCenter);
                  $phpExcelObject->getActiveSheet()->getStyle('B6')->applyFromArray($styleItalicArray);

                  $phpExcelObject->getActiveSheet()->SetCellValue('C6', "Month");
                  $phpExcelObject->getActiveSheet()->getStyle('C6')->applyFromArray($styleArrayCenter);
                  $phpExcelObject->getActiveSheet()->getStyle('C6')->applyFromArray($styleBoldItalicArray);

                  $phpExcelObject->getActiveSheet()->SetCellValue('D6', $month);
                  $phpExcelObject->getActiveSheet()->getStyle('D6')->applyFromArray($styleArrayCenter);
                  $phpExcelObject->getActiveSheet()->getStyle('D6')->applyFromArray($styleItalicArray);

                  $phpExcelObject->getActiveSheet()->SetCellValue('C8', "3.1 Details of Outward Supplies and inward supplies liable to reverse charge");
                  $phpExcelObject->getActiveSheet()->getStyle('C8')->applyFromArray($styleArrayCenter);
                  $phpExcelObject->getActiveSheet()->getStyle('C8')->applyFromArray($styleBoldArray);

                  $phpExcelObject->getActiveSheet()->SetCellValue('A9', 'Nature of Supplies');
                  $phpExcelObject->getActiveSheet()->getStyle('A9')->applyFromArray($styleArrayCenter);
                  $phpExcelObject->getActiveSheet()->getStyle('A9')->applyFromArray($styleBoldItalicArray);

                  $phpExcelObject->getActiveSheet()->SetCellValue('B9', 'Total Taxable value');
                  $phpExcelObject->getActiveSheet()->getStyle('B9')->applyFromArray($styleArrayCenter);
                  $phpExcelObject->getActiveSheet()->getStyle('B9')->applyFromArray($styleBoldItalicArray);

                  $phpExcelObject->getActiveSheet()->SetCellValue('C9', 'Integrated Tax');
                  $phpExcelObject->getActiveSheet()->getStyle('C9')->applyFromArray($styleArrayCenter);
                  $phpExcelObject->getActiveSheet()->getStyle('C9')->applyFromArray($styleBoldItalicArray);

                  $phpExcelObject->getActiveSheet()->SetCellValue('D9', 'Central Tax');
                  $phpExcelObject->getActiveSheet()->getStyle('D9')->applyFromArray($styleArrayCenter);
                  $phpExcelObject->getActiveSheet()->getStyle('D9')->applyFromArray($styleBoldItalicArray);

                  $phpExcelObject->getActiveSheet()->SetCellValue('E9', 'State/UT Tax');
                  $phpExcelObject->getActiveSheet()->getStyle('E9')->applyFromArray($styleArrayCenter);
                  $phpExcelObject->getActiveSheet()->getStyle('E9')->applyFromArray($styleBoldItalicArray);

                  $phpExcelObject->getActiveSheet()->SetCellValue('F9', 'Cess');
                  $phpExcelObject->getActiveSheet()->getStyle('F9')->applyFromArray($styleArrayCenter);
                  $phpExcelObject->getActiveSheet()->getStyle('F9')->applyFromArray($styleBoldItalicArray);

                  $phpExcelObject->getActiveSheet()->SetCellValue('A11', '(a) Outward Taxable  supplies  (other than zero rated, nil rated and exempted)');

                  if (isset($outward_supplies_taxable) && $outward_supplies_taxable) {
                        $phpExcelObject->getActiveSheet()->setCellValue('B11', round($outward_supplies_taxable->taxable_value, 2));
                        $phpExcelObject->getActiveSheet()->setCellValue('C11', round($outward_supplies_taxable->igst, 2));

                        $phpExcelObject->getActiveSheet()->setCellValue('D11', round($outward_supplies_taxable->cgst, 2));
                        $phpExcelObject->getActiveSheet()->setCellValue('E11', round($outward_supplies_taxable->sgst, 2));

                        $phpExcelObject->getActiveSheet()->setCellValue('F11', round($outward_supplies_taxable->cess_amount, 2));
                  }

                  $phpExcelObject->getActiveSheet()->SetCellValue('A12', '(b) Outward Taxable  supplies  (zero rated )');
                  if (isset($outward_supplies_zero_rated) && $outward_supplies_zero_rated) {
                        $phpExcelObject->getActiveSheet()->setCellValue('B12', round($outward_supplies_zero_rated->taxable_value, 2));
                        $phpExcelObject->getActiveSheet()->setCellValue('C12', round($outward_supplies_zero_rated->igst, 2));
                        $phpExcelObject->getActiveSheet()->setCellValue('D12', round($outward_supplies_zero_rated->cgst, 2));
                        $phpExcelObject->getActiveSheet()->setCellValue('E12', round($outward_supplies_zero_rated->sgst, 2));
                        $phpExcelObject->getActiveSheet()->setCellValue('F12', round($outward_supplies_zero_rated->cess_amount, 2));
                  }
                  $phpExcelObject->getActiveSheet()->SetCellValue('A13', '(c) Other Outward Taxable  supplies (Nil rated, exempted)');

                  if (isset($outward_supplies_nil_exempted) && $outward_supplies_nil_exempted) {
                        $phpExcelObject->getActiveSheet()->setCellValue('B13', round($outward_supplies_nil_exempted->taxable_value, 2));
                        $phpExcelObject->getActiveSheet()->setCellValue('C13', round($outward_supplies_nil_exempted->igst, 2));
                        $phpExcelObject->getActiveSheet()->setCellValue('D13', round($outward_supplies_nil_exempted->cgst, 2));
                        $phpExcelObject->getActiveSheet()->setCellValue('E13', round($outward_supplies_nil_exempted->sgst, 2));
                        $phpExcelObject->getActiveSheet()->setCellValue('F13', round($outward_supplies_nil_exempted->cess_amount, 2));
                  }
                  $phpExcelObject->getActiveSheet()->SetCellValue('A14', '(d) Inward supplies (liable to reverse charge)');
                  if (isset($inward_liable_reverse_charge) && $inward_liable_reverse_charge) {
                        $phpExcelObject->getActiveSheet()->setCellValue('B14', round($inward_liable_reverse_charge->taxable_value, 2));
                        $phpExcelObject->getActiveSheet()->setCellValue('C14', round($inward_liable_reverse_charge->igst, 2));
                        $phpExcelObject->getActiveSheet()->setCellValue('D14', round($inward_liable_reverse_charge->cgst, 2));
                        $phpExcelObject->getActiveSheet()->setCellValue('E14', round($inward_liable_reverse_charge->sgst, 2));
                        $phpExcelObject->getActiveSheet()->setCellValue('F14', round($inward_liable_reverse_charge->cess_amount, 2));
                  }
                  $phpExcelObject->getActiveSheet()->SetCellValue('A15', '(e) Non-GST Outward supplies');

                  if (isset($outward_supplies_non_gst) && $outward_supplies_non_gst) {
                        $phpExcelObject->getActiveSheet()->setCellValue('B15', round($outward_supplies_non_gst->taxable_value, 2));
                        $phpExcelObject->getActiveSheet()->setCellValue('C15', round($outward_supplies_non_gst->igst, 2));
                        $phpExcelObject->getActiveSheet()->setCellValue('D15', round($outward_supplies_non_gst->cgst, 2));
                        $phpExcelObject->getActiveSheet()->setCellValue('E15', round($outward_supplies_non_gst->sgst, 2));
                        $phpExcelObject->getActiveSheet()->setCellValue('F15', round($outward_supplies_non_gst->cess_amount, 2));
                  }
                  $phpExcelObject->getActiveSheet()->SetCellValue('A16', 'Total');
                  $phpExcelObject->getActiveSheet()->getStyle('A16')->applyFromArray($styleArrayCenter);
                  $phpExcelObject->getActiveSheet()->getStyle('A16')->applyFromArray($styleBoldArray);

                  if (isset($total_outward_inward_taxable_value) && $total_outward_inward_taxable_value) {
                        $phpExcelObject->getActiveSheet()->setCellValue('B16', round($total_outward_inward_taxable_value, 2));
                        $phpExcelObject->getActiveSheet()->setCellValue('C16', round($total_outward_inward_taxable_igst, 2));
                        $phpExcelObject->getActiveSheet()->setCellValue('D16', round($total_outward_inward_taxable_cgst, 2));
                        $phpExcelObject->getActiveSheet()->setCellValue('E16', round($total_outward_inward_taxable_sgst, 2));
                        $phpExcelObject->getActiveSheet()->setCellValue('F16', round($total_outward_inward_taxable_cess, 2));
                  }

                  $phpExcelObject->getActiveSheet()->SetCellValue('A18', '3.2  Of the supplies shown in 3.1 (a), details of inter-state supplies made to unregistered persons, composition taxable person and UIN holders');
                  $phpExcelObject->getActiveSheet()->getStyle('A18')->applyFromArray($styleArrayCenter);
                  $phpExcelObject->getActiveSheet()->getStyle('A18')->applyFromArray($styleBoldArray);

                  $phpExcelObject->getActiveSheet()->SetCellValue('A19', 'Place of Supply(State/UT)');
                  $phpExcelObject->getActiveSheet()->getStyle('A19')->applyFromArray($styleArrayCenter);
                  $phpExcelObject->getActiveSheet()->getStyle('A19')->applyFromArray($styleBoldItalicArray);

                  $phpExcelObject->getActiveSheet()->mergeCells('B19:C19');
                  $phpExcelObject->getActiveSheet()->SetCellValue('B19', 'Supplies made to Unregistered Persons');
                  $phpExcelObject->getActiveSheet()->getStyle('B19')->applyFromArray($styleArrayCenter);
                  $phpExcelObject->getActiveSheet()->getStyle('B19')->applyFromArray($styleBoldItalicArray);

                  $phpExcelObject->getActiveSheet()->mergeCells('D19:E19');
                  $phpExcelObject->getActiveSheet()->SetCellValue('D19', 'Supplies made to Composition Taxable Persons');
                  $phpExcelObject->getActiveSheet()->getStyle('D19')->applyFromArray($styleArrayCenter);
                  $phpExcelObject->getActiveSheet()->getStyle('D49')->applyFromArray($styleBoldItalicArray);

                  $phpExcelObject->getActiveSheet()->mergeCells('F19:G19');
                  $phpExcelObject->getActiveSheet()->SetCellValue('F19', 'Supplies made to UIN holders');

                  $phpExcelObject->getActiveSheet()->getStyle('F19')->applyFromArray($styleArrayCenter);
                  $phpExcelObject->getActiveSheet()->getStyle('F19')->applyFromArray($styleBoldItalicArray);

                  $phpExcelObject->getActiveSheet()->SetCellValue('B20', 'Total Taxable value');
                  $phpExcelObject->getActiveSheet()->getStyle('B20')->applyFromArray($styleArrayCenter);
                  $phpExcelObject->getActiveSheet()->getStyle('B20')->applyFromArray($styleBoldItalicArray);

                  $phpExcelObject->getActiveSheet()->SetCellValue('C20', 'Amount of Integrated Tax');
                  $phpExcelObject->getActiveSheet()->getStyle('C20')->applyFromArray($styleArrayCenter);
                  $phpExcelObject->getActiveSheet()->getStyle('C20')->applyFromArray($styleBoldItalicArray);

                  $phpExcelObject->getActiveSheet()->SetCellValue('D20', 'Total Taxable value');
                  $phpExcelObject->getActiveSheet()->getStyle('D20')->applyFromArray($styleArrayCenter);
                  $phpExcelObject->getActiveSheet()->getStyle('D50')->applyFromArray($styleBoldItalicArray);

                  $phpExcelObject->getActiveSheet()->SetCellValue('E20', 'Amount of Integrated Tax');
                  $phpExcelObject->getActiveSheet()->getStyle('E20')->applyFromArray($styleArrayCenter);
                  $phpExcelObject->getActiveSheet()->getStyle('E20')->applyFromArray($styleBoldItalicArray);

                  $phpExcelObject->getActiveSheet()->SetCellValue('F20', 'Total Taxable value');
                  $phpExcelObject->getActiveSheet()->getStyle('F20')->applyFromArray($styleArrayCenter);
                  $phpExcelObject->getActiveSheet()->getStyle('F20')->applyFromArray($styleBoldItalicArray);

                  $phpExcelObject->getActiveSheet()->SetCellValue('G20', 'Amount of Integrated Tax');
                  $phpExcelObject->getActiveSheet()->getStyle('G20')->applyFromArray($styleArrayCenter);
                  $phpExcelObject->getActiveSheet()->getStyle('G20')->applyFromArray($styleBoldItalicArray);

                  $i = 22;
                  $total_unregistered_taxable_value = $total_unregistered_igst = 0;
                  if ($unregistered_details) {
                        foreach ($unregistered_details as $key => $unregistered_detail) {
                              $total_unregistered_taxable_value += $unregistered_detail['taxable_value'];
                              $total_unregistered_igst += $unregistered_detail['igst'];
                              $phpExcelObject->getActiveSheet()->SetCellValue('A' . $i, $key);
                              $phpExcelObject->getActiveSheet()->SetCellValue('B' . $i, $unregistered_detail['taxable_value']);
                              $phpExcelObject->getActiveSheet()->SetCellValue('C' . $i, $unregistered_detail['igst']);
                              $i++;
                        }
                  }

                  $i++;
                  $phpExcelObject->getActiveSheet()->SetCellValue('A' . $i, 'Total');
                  $phpExcelObject->getActiveSheet()->getStyle('A' . $i)->applyFromArray($styleArrayCenter);
                  $phpExcelObject->getActiveSheet()->getStyle('A' . $i)->applyFromArray($styleBoldArray);

                  $phpExcelObject->getActiveSheet()->SetCellValue('B' . $i, round($total_unregistered_taxable_value, 2));
                  $phpExcelObject->getActiveSheet()->getStyle('B' . $i)->applyFromArray($styleArrayCenter);
                  $phpExcelObject->getActiveSheet()->getStyle('B' . $i)->applyFromArray($styleBoldArray);

                  $phpExcelObject->getActiveSheet()->SetCellValue('C' . $i, round($total_unregistered_igst, 2));
                  $phpExcelObject->getActiveSheet()->getStyle('C' . $i)->applyFromArray($styleArrayCenter);
                  $phpExcelObject->getActiveSheet()->getStyle('C' . $i)->applyFromArray($styleBoldArray);

                  $phpExcelObject->getActiveSheet()->SetCellValue('D' . $i, '0');
                  $phpExcelObject->getActiveSheet()->SetCellValue('E' . $i, '0');

                  $phpExcelObject->getActiveSheet()->SetCellValue('F' . $i, '0');
                  $phpExcelObject->getActiveSheet()->SetCellValue('G' . $i, '0');

                  $i += 2;
                  $phpExcelObject->getActiveSheet()->SetCellValue('A' . $i, '4. Eligible ITC');
                  $phpExcelObject->getActiveSheet()->getStyle('A' . $i)->applyFromArray($styleArrayCenter);
                  $phpExcelObject->getActiveSheet()->getStyle('A' . $i)->applyFromArray($styleBoldArray);
                  $i++;
                  $phpExcelObject->getActiveSheet()->SetCellValue('A' . $i, 'Details');
                  $phpExcelObject->getActiveSheet()->getStyle('A' . $i)->applyFromArray($styleArrayCenter);
                  $phpExcelObject->getActiveSheet()->getStyle('A' . $i)->applyFromArray($styleBoldItalicArray);
                  $phpExcelObject->getActiveSheet()->SetCellValue('B' . $i, 'Integrated Tax');
                  $phpExcelObject->getActiveSheet()->getStyle('B' . $i)->applyFromArray($styleArrayCenter);
                  $phpExcelObject->getActiveSheet()->getStyle('B' . $i)->applyFromArray($styleBoldItalicArray);
                  $phpExcelObject->getActiveSheet()->SetCellValue('C' . $i, 'Central Tax');
                  $phpExcelObject->getActiveSheet()->getStyle('C' . $i)->applyFromArray($styleArrayCenter);
                  $phpExcelObject->getActiveSheet()->getStyle('C' . $i)->applyFromArray($styleBoldItalicArray);
                  $phpExcelObject->getActiveSheet()->SetCellValue('D' . $i, 'State/UT Tax');
                  $phpExcelObject->getActiveSheet()->getStyle('D' . $i)->applyFromArray($styleArrayCenter);
                  $phpExcelObject->getActiveSheet()->getStyle('D' . $i)->applyFromArray($styleBoldItalicArray);
                  $phpExcelObject->getActiveSheet()->SetCellValue('E' . $i, 'Cess');
                  $phpExcelObject->getActiveSheet()->getStyle('E' . $i)->applyFromArray($styleArrayCenter);
                  $phpExcelObject->getActiveSheet()->getStyle('E' . $i)->applyFromArray($styleBoldItalicArray);

                  $i += 2;
                  $phpExcelObject->getActiveSheet()->SetCellValue('A' . $i, '(A) ITC Available (Whether in full or part)');
                  $phpExcelObject->getActiveSheet()->getStyle('A' . $i)->applyFromArray($styleBoldArray);
                  $i++;
                  $phpExcelObject->getActiveSheet()->SetCellValue('A' . $i, '(1) Import of goods');
                  if (isset($import_of_goods) && $import_of_goods) {
                        $phpExcelObject->getActiveSheet()->SetCellValue('B' . $i, round($import_of_goods->igst, 2));
                        $phpExcelObject->getActiveSheet()->SetCellValue('C' . $i, round($import_of_goods->cgst, 2));
                        $phpExcelObject->getActiveSheet()->SetCellValue('D' . $i, round($import_of_goods->sgst, 2));
                        $phpExcelObject->getActiveSheet()->SetCellValue('E' . $i, 0);
                  }
                  $i++;
                  $phpExcelObject->getActiveSheet()->SetCellValue('A' . $i, '(2) Import of services');

                  if (isset($import_of_services) && $import_of_services) {
                        $phpExcelObject->getActiveSheet()->SetCellValue('B' . $i, round($import_of_services->igst, 2));
                        $phpExcelObject->getActiveSheet()->SetCellValue('C' . $i, round($import_of_services->cgst, 2));
                        $phpExcelObject->getActiveSheet()->SetCellValue('D' . $i, round($import_of_services->sgst, 2));
                        $phpExcelObject->getActiveSheet()->SetCellValue('E' . $i, 0);
                  }

                  $i++;
                  $phpExcelObject->getActiveSheet()->SetCellValue('A' . $i, '(3) Inward supplies liable to reverse charge (other than 1 &2 above)');

                  if (isset($imports_reverse_charge) && $imports_reverse_charge) {
                        $phpExcelObject->getActiveSheet()->SetCellValue('B' . $i, round($imports_reverse_charge->igst, 2));
                        $phpExcelObject->getActiveSheet()->SetCellValue('C' . $i, round($imports_reverse_charge->cgst, 2));
                        $phpExcelObject->getActiveSheet()->SetCellValue('D' . $i, round($imports_reverse_charge->sgst, 2));
                        $phpExcelObject->getActiveSheet()->SetCellValue('E' . $i, 0);
                  }

                  $i++;
                  $phpExcelObject->getActiveSheet()->SetCellValue('A' . $i, '(4) Inward supplies from ISD');
                  if (isset($increase_input_isd) && $increase_input_isd) {
                        $phpExcelObject->getActiveSheet()->SetCellValue('B' . $i, round($increase_input_isd->igst, 2));
                        $phpExcelObject->getActiveSheet()->SetCellValue('C' . $i, round($increase_input_isd->cgst, 2));
                        $phpExcelObject->getActiveSheet()->SetCellValue('D' . $i, round($increase_input_isd->sgst, 2));
                        $phpExcelObject->getActiveSheet()->SetCellValue('E' . $i, 0);
                  }

                  $i++;
                  $phpExcelObject->getActiveSheet()->SetCellValue('A' . $i, '(5) All other ITC');
                  if (isset($other_itc) && $other_itc) {
                        $phpExcelObject->getActiveSheet()->SetCellValue('B' . $i, round($other_itc->igst, 2));
                        $phpExcelObject->getActiveSheet()->SetCellValue('C' . $i, round($other_itc->cgst, 2));
                        $phpExcelObject->getActiveSheet()->SetCellValue('D' . $i, round($other_itc->sgst, 2));
                        $phpExcelObject->getActiveSheet()->SetCellValue('E' . $i, round($other_itc->cess_amount, 2));
                  }
                  $i++;
                  $phpExcelObject->getActiveSheet()->SetCellValue('A' . $i, '(B) ITC Reversed');
                  $phpExcelObject->getActiveSheet()->getStyle('A' . $i)->applyFromArray($styleBoldArray);
                  if (isset($total_itc_reversed) && $total_itc_reversed) {
                        $phpExcelObject->getActiveSheet()->SetCellValue('B' . $i, round($total_itc_reversed->igst, 2));
                        $phpExcelObject->getActiveSheet()->SetCellValue('C' . $i, round($total_itc_reversed->cgst, 2));
                        $phpExcelObject->getActiveSheet()->SetCellValue('D' . $i, round($total_itc_reversed->sgst, 2));
                        $phpExcelObject->getActiveSheet()->SetCellValue('E' . $i, 0);
                  }

                  $i++;
                  $phpExcelObject->getActiveSheet()->SetCellValue('A' . $i, '(1) As per rules 38, 42 & 43 of CGST Rules and Section 17(5)');
                  if (isset($itc_reverse_cgst) && $itc_reverse_cgst) {

                        $phpExcelObject->getActiveSheet()->SetCellValue('B' . $i, round($itc_reverse_cgst->igst, 2));
                        $phpExcelObject->getActiveSheet()->SetCellValue('C' . $i, round($itc_reverse_cgst->cgst, 2));
                        $phpExcelObject->getActiveSheet()->SetCellValue('D' . $i, round($itc_reverse_cgst->sgst, 2));
                        $phpExcelObject->getActiveSheet()->SetCellValue('E' . $i, 0);
                  }

                  $i++;
                  $phpExcelObject->getActiveSheet()->SetCellValue('A' . $i, '(2) Others');
                  if (isset($itc_reverse_other) && $itc_reverse_other) {
                        $phpExcelObject->getActiveSheet()->SetCellValue('B' . $i, round($itc_reverse_other->igst, 2));
                        $phpExcelObject->getActiveSheet()->SetCellValue('C' . $i, round($itc_reverse_other->cgst, 2));
                        $phpExcelObject->getActiveSheet()->SetCellValue('D' . $i, round($itc_reverse_other->sgst, 2));
                        $phpExcelObject->getActiveSheet()->SetCellValue('E' . $i, 0);
                  }

                  $i++;
                  $phpExcelObject->getActiveSheet()->SetCellValue('A' . $i, '(C) Net ITC Available (A)-(B)');

                  if (isset($total_itc_taxable_value) && $total_itc_taxable_value) {
                        $phpExcelObject->getActiveSheet()->SetCellValue('B' . $i, round($total_itc_taxable_igst, 2));
                        $phpExcelObject->getActiveSheet()->SetCellValue('C' . $i, round($total_itc_taxable_cgst, 2));
                        $phpExcelObject->getActiveSheet()->SetCellValue('D' . $i, round($total_itc_taxable_sgst, 2));
                        $phpExcelObject->getActiveSheet()->SetCellValue('E' . $i, round($total_itc_taxable_cess, 2));
                  }
                  $phpExcelObject->getActiveSheet()->getStyle('A' . $i)->applyFromArray($styleBoldArray);
                  $i++;
                  $phpExcelObject->getActiveSheet()->SetCellValue('A' . $i, '(D) Other Details');
                  $phpExcelObject->getActiveSheet()->getStyle('A' . $i)->applyFromArray($styleBoldArray);
                  $excel_sheet = $phpExcelObject->getActiveSheet();
                  if ($total_ineligible_taxable_value) {

                        $excel_sheet->setCellValue('B' . $i, round($total_ineligible_taxable_igst, 2));
                        $excel_sheet->setCellValue('C' . $i, round($total_ineligible_taxable_cgst, 2));
                        $excel_sheet->setCellValue('D' . $i, round($total_ineligible_taxable_sgst, 2));
                        $excel_sheet->setCellValue('E' . $i, round($total_ineligible_taxable_cess, 2));
                  }
                  $i++;
                  $phpExcelObject->getActiveSheet()->SetCellValue('A' . $i, '(1) ITC reclaimed which was reversed under Table 4(B)(2) in earlier tax period');
                  if ($reclaim_itc) {

                        $excel_sheet->setCellValue('B' . $i, round($reclaim_itc->igst, 2));
                        $excel_sheet->setCellValue('C' . $i, round($reclaim_itc->cgst, 2));
                        $excel_sheet->setCellValue('D' . $i, round($reclaim_itc->sgst, 2));
                  }
                  $i++;
                  $phpExcelObject->getActiveSheet()->SetCellValue('A' . $i, '(2) Ineligible ITC under section 16(4) & ITC restricted to Pos rules.');

                  if ($ineligible_16) {

                        $excel_sheet->setCellValue('B' . $i, round($ineligible_16->igst, 2));
                        $excel_sheet->setCellValue('C' . $i, round($ineligible_16->cgst, 2));
                        $excel_sheet->setCellValue('D' . $i, round($ineligible_16->sgst, 2));
                        $excel_sheet->setCellValue('E' . $i, round($ineligible_16->cess_amount, 2));
                  }

                  $i += 3;
                  $phpExcelObject->getActiveSheet()->SetCellValue('A' . $i, '5. Values of exempt, Nil-rated and non-GST inward supplies');
                  $phpExcelObject->getActiveSheet()->getStyle('A' . $i)->applyFromArray($styleArrayCenter);
                  $phpExcelObject->getActiveSheet()->getStyle('A' . $i)->applyFromArray($styleBoldArray);
                  $i++;
                  $phpExcelObject->getActiveSheet()->SetCellValue('A' . $i, 'Nature of supplies');
                  $phpExcelObject->getActiveSheet()->getStyle('A' . $i)->applyFromArray($styleArrayCenter);
                  $phpExcelObject->getActiveSheet()->getStyle('A' . $i)->applyFromArray($styleBoldItalicArray);
                  $i++;
                  $phpExcelObject->getActiveSheet()->SetCellValue('B' . $i, 'Inter-State supplies');
                  $phpExcelObject->getActiveSheet()->getStyle('B' . $i)->applyFromArray($styleArrayCenter);
                  $phpExcelObject->getActiveSheet()->getStyle('B' . $i)->applyFromArray($styleBoldItalicArray);

                  $phpExcelObject->getActiveSheet()->SetCellValue('C' . $i, 'Intra-state supplies');
                  $phpExcelObject->getActiveSheet()->getStyle('C' . $i)->applyFromArray($styleArrayCenter);
                  $phpExcelObject->getActiveSheet()->getStyle('C' . $i)->applyFromArray($styleBoldItalicArray);

                  $i++;
                  $phpExcelObject->getActiveSheet()->SetCellValue('A' . $i, 'From a supplier under composition scheme, Exempt  and Nil rated supply');

                  if (isset($inward_nil_rated) && $inward_nil_rated) {
                        $phpExcelObject->getActiveSheet()->SetCellValue('B' . $i, round($inward_nil_rated->inter, 2));
                        $phpExcelObject->getActiveSheet()->SetCellValue('C' . $i, round($inward_nil_rated->intra, 2));
                  }

                  $i++;
                  $phpExcelObject->getActiveSheet()->SetCellValue('A' . $i, 'Non GST supply');
                  if (isset($inward_non_gst) && $inward_non_gst) {
                        $phpExcelObject->getActiveSheet()->SetCellValue('B' . $i, round($inward_non_gst->inter, 2));
                        $phpExcelObject->getActiveSheet()->SetCellValue('C' . $i, round($inward_non_gst->intra, 2));
                  }

                  $i++;
                  $phpExcelObject->getActiveSheet()->SetCellValue('A' . $i, 'Total');
                  $phpExcelObject->getActiveSheet()->getStyle('A' . $i)->applyFromArray($styleArrayCenter);
                  $phpExcelObject->getActiveSheet()->getStyle('A' . $i)->applyFromArray($styleBoldArray);

                  if (isset($total_nil_inter) && $total_nil_intra) {
                        $phpExcelObject->getActiveSheet()->SetCellValue('B' . $i, round($total_nil_inter, 2));
                        $phpExcelObject->getActiveSheet()->SetCellValue('C' . $i, round($total_nil_intra, 2));
                  }

                  $i += 2;
                  $phpExcelObject->getActiveSheet()->SetCellValue('A' . $i, '5.1 Interest & late fee payable');
                  $phpExcelObject->getActiveSheet()->getStyle('A' . $i)->applyFromArray($styleArrayCenter);
                  $phpExcelObject->getActiveSheet()->getStyle('A' . $i)->applyFromArray($styleBoldArray);
                  $i++;
                  $phpExcelObject->getActiveSheet()->SetCellValue('A' . $i, 'Description');
                  $phpExcelObject->getActiveSheet()->getStyle('A' . $i)->applyFromArray($styleArrayCenter);
                  $phpExcelObject->getActiveSheet()->getStyle('A' . $i)->applyFromArray($styleBoldArray);
                  $phpExcelObject->getActiveSheet()->SetCellValue('B' . $i, 'Integrated Tax');
                  $phpExcelObject->getActiveSheet()->getStyle('B' . $i)->applyFromArray($styleArrayCenter);
                  $phpExcelObject->getActiveSheet()->getStyle('B' . $i)->applyFromArray($styleBoldArray);
                  $phpExcelObject->getActiveSheet()->SetCellValue('C' . $i, 'Central Tax');
                  $phpExcelObject->getActiveSheet()->getStyle('C' . $i)->applyFromArray($styleArrayCenter);
                  $phpExcelObject->getActiveSheet()->getStyle('C' . $i)->applyFromArray($styleBoldArray);
                  $phpExcelObject->getActiveSheet()->SetCellValue('D' . $i, 'State/UT Tax');
                  $phpExcelObject->getActiveSheet()->getStyle('D' . $i)->applyFromArray($styleArrayCenter);
                  $phpExcelObject->getActiveSheet()->getStyle('D' . $i)->applyFromArray($styleBoldArray);
                  $phpExcelObject->getActiveSheet()->SetCellValue('E' . $i, 'Cess');
                  $phpExcelObject->getActiveSheet()->getStyle('E' . $i)->applyFromArray($styleArrayCenter);
                  $phpExcelObject->getActiveSheet()->getStyle('E' . $i)->applyFromArray($styleBoldArray);

                  $i += 2;
                  $phpExcelObject->getActiveSheet()->SetCellValue('A' . $i, 'Interest');
                  ob_end_clean();
                  ob_start();

                  header('Content-Type: application/vnd.ms-excel');
                  header('Content-Disposition: attachment;filename="GSTR3B.xls"');
                  header('Cache-Control: max-age=0');
                  $objWriter = PHPExcel_IOFactory::createWriter($phpExcelObject, 'Excel5');
                  ob_end_clean();
                  $objWriter->save('php://output');

                  break;
            //
            case "gstr1":
                  //                ob_start();
                  ini_set('max_execution_time', 15555555);
                  ob_start();
                  $excel = AccountTaxManager::getGSTR1Excel($start_date, $end_date, $id, $chkid_array);
                  ob_end_clean();
                  //                ob_flush();
                  header('Content-Type: application/vnd.ms-excel');
                  header('Content-Disposition: attachment;filename="GSTR1.xls"');
                  header('Cache-Control: max-age=0');
                  ob_clean();
                  $objWriter = PHPExcel_IOFactory::createWriter($excel, 'Excel5');
                  $objWriter->save('php://output');

                  break;
            case "gstr2":
                  ob_start();
                  $excel = AccountTaxManager::getGSTR2Excel($start_date, $end_date, $id, $chkid_array);
                  //                ob_end_clean();
                  //                ob_flush();
                  //                ob_start();
                  header('Content-Type: application/vnd.ms-excel');
                  header('Content-Disposition: attachment;filename="GSTR2.xls"');
                  header('Cache-Control: max-age=0');
                  ob_clean();
                  $objWriter = PHPExcel_IOFactory::createWriter($excel, 'Excel5');
                  $objWriter->save('php://output');

                  break;
            default:
                  exit();
                  break;
      }
      exit;
}

function generate_json()
{
      global $url;
      if (!isset($url[1])) {
            exit();
      }
      $start_date = 0;
      $end_date = 0;
      if (isset($url[2]) && $url[2] != '') {
            $start_date = date('Y-m-d 00:00:00', strtotime($url[2]));
      }
      if (isset($url[3]) && $url[3] != '') {
            $end_date = date('Y-m-d 23:59:59', strtotime($url[3]));
      }
      $id = $_GET['id'];
      $chkid_array = array();
      if (isset($_GET['chkid'])) {
            $outlet = explode(',', $_GET['chkid']);
            $outlet = array_filter($outlet);
            if (is_array($outlet) && !empty($outlet)) {
                  $chkid_array = $outlet;
            }
      }
      $gid = 0;
      if (isset($_GET['gid'])) {
            $gid = $_GET['gid'];
      }
      if ($gid > 0 && empty($chkid_array) && getSettings('IS_OUTLET_ENABLE')) {
            $outlets = OutletManager::getUserCheckPoint(Session::loggedInUid(), null, null, $gid);
            if ($outlets) {
                  foreach ($outlets as $outlet) {
                        $chkid_array[] = $outlet['chkid'];
                  }
            }
      }
      switch ($url[1]) {
            case "gstr3b":

                  AccountTaxManager::createInvoiceGstView($start_date, $end_date, $chkid_array);
                  PurchaseInvoiceManager::createPurchaseInvoiceGstView($start_date, $end_date, $chkid_array);
                  PurchaseInvoiceImportManager::createPurchaseInvoiceImportGstView($start_date, $end_date, $chkid_array);
                  PurchaseInvoiceManager::createPurchaseInvoiceGstView($start_date, $end_date, $chkid_array, true);
                  CheckPointOrderCreditNoteManager::createCreditNoteGstView($start_date, $end_date, $chkid_array);
                  CheckPointOrderCreditNoteManager::createCreditNoteGstView($start_date, $end_date, $chkid_array, true);
                  PurchaseOrderDebitNoteManager::createDebitNoteGstView($start_date, $end_date, false, $chkid_array);
                  PurchaseOrderDebitNoteManager::createDebitNoteGstView($start_date, $end_date, false, $chkid_array, true);
                  $ineligible_sec = AccountTaxManager::getAllInEligbleITC($start_date, $end_date, $id, 1, $chkid_array);
                  $ineligible_others = AccountTaxManager::getAllInEligbleITC($start_date, $end_date, $id, 2, $chkid_array);
                  $ineligible_38 = AccountTaxManager::getAllInEligbleITC($start_date, $end_date, $id, 2, $chkid_array);
                  $ineligible_42 = AccountTaxManager::getAllInEligbleITC($start_date, $end_date, $id, 2, $chkid_array);
                  $ineligible_43 = AccountTaxManager::getAllInEligbleITC($start_date, $end_date, $id, 2, $chkid_array);

                  $outward_supplies_taxable = AccountTaxManager::getOutwardSuppliesTaxable($start_date, $end_date, $id);

                  $outward_supplies_nil_exempted = AccountTaxManager::getOutwardSuppliesNilExempted($start_date, $end_date, $id);
                  $outward_supplies_zero_rated = AccountTaxManager::getOutwardSuppliesZeroRated($start_date, $end_date, $id, $chkid_array);
                  $outward_supplies_non_gst = AccountTaxManager::getOutwardSuppliesNonGST($start_date, $end_date, $id);

                  $inward_liable_reverse_charge = AccountTaxManager::getInwardSuppliesLiabletoRCM($start_date, $end_date, $id);

                  $outward_supplies_unregistered = AccountTaxManager::getOutwardSuppliesUnregistered($start_date, $end_date, $id);

                  $other_itc = AccountTaxManager::getAllOtherITC($start_date, $end_date, $id);

                  $inward_nil_rated = AccountTaxManager::getInwardSuppliesNilExempted($start_date, $end_date, $id);

                  $inward_non_gst = AccountTaxManager::getInwardSuppliesNonGST($start_date, $end_date, $id);

                  $import_of_goods = AccountTaxManager::getImportsOfGoods($start_date, $end_date, $id);

                  $unregistred_supplies = AccountTaxManager::getOutwardSuppliesInterstateUnreg($start_date, $end_date, $id);
                  $total_outward_inward_taxable_value = $total_outward_inward_taxable_igst = $total_outward_inward_taxable_cgst = $total_outward_inward_taxable_sgst = $total_outward_inward_taxable_tax = $total_outward_inward_taxable_cess = 0;

                  $total_itc_taxable_value = $total_itc_taxable_igst = $total_itc_taxable_cgst = $total_itc_taxable_sgst = $total_itc_taxable_tax = $total_itc_taxable_cess = 0;

                  $total_outward_unreg_taxable_value = $total_outward_unreg_taxable_igst = $total_outward_unreg_taxable_cgst = $total_outward_unreg_taxable_sgst = $total_outward_unreg_taxable_tax = $total_outward_unreg_taxable_cess = 0;

                  $total_nil_exemp_taxable_value = $total_nil_exemp_taxable_igst = $total_nil_exemp_taxable_cgst = $total_nil_exemp_taxable_sgst = $total_nil_exemp_taxable_tax = $total_nil_exemp_taxable_cess = 0;

                  $outward_decrease_liability = AccountTaxManager::getJournalVouchers($id, $start_date, $end_date, SystemTablesStatus::DB_TBL_DECREASE_OF_TAX_LIABILITY, SystemTablesStatus::DB_TBL_1_ADJUSTMENT_AGAINST_CREDIT, 1, $chkid_array);

                  $outward_increase_liability = AccountTaxManager::getJournalVouchers($id, $start_date, $end_date, SystemTablesStatus::DB_TBL_INCREASE_OF_TAX_LIABILITY, SystemTablesStatus::DB_TBL_3_OTHERS, 2, $chkid_array);

                  $outward_reversal = AccountTaxManager::getJournalVouchers($id, $start_date, $end_date, SystemTablesStatus::DB_TBL_REVERSAL_OF_TAX_LIABILITY_INPUT_TAX_CREDIT, SystemTablesStatus::DB_TBL_9_ISD_CREDIT_NOTE_RULE_39_1_J, 1, $chkid_array);

                  if ($outward_supplies_taxable) {
                        if ($outward_decrease_liability) {
                              $outward_supplies_taxable->cgst -= $outward_decrease_liability->cgst;
                              $outward_supplies_taxable->sgst -= $outward_decrease_liability->sgst;
                              $outward_supplies_taxable->igst -= $outward_decrease_liability->igst;
                              $outward_supplies_taxable->tax_amount -= $outward_decrease_liability->tax_amount;
                              $outward_supplies_taxable->taxable_value -= $outward_decrease_liability->taxable_value;
                        }

                        if ($outward_increase_liability) {
                              $outward_supplies_taxable->cgst += $outward_increase_liability->cgst;
                              $outward_supplies_taxable->sgst += $outward_increase_liability->sgst;
                              $outward_supplies_taxable->igst += $outward_increase_liability->igst;
                              $outward_supplies_taxable->tax_amount += $outward_increase_liability->tax_amount;
                              $outward_supplies_taxable->taxable_value += $outward_increase_liability->taxable_value;
                        }

                        if ($outward_reversal) {
                              $outward_supplies_taxable->cgst -= $outward_reversal->cgst;
                              $outward_supplies_taxable->sgst -= $outward_reversal->sgst;
                              $outward_supplies_taxable->igst -= $outward_reversal->igst;
                              $outward_supplies_taxable->tax_amount -= $outward_reversal->tax_amount;
                              $outward_supplies_taxable->taxable_value -= $outward_reversal->taxable_value;
                        }

                        $total_outward_inward_taxable_value += $outward_supplies_taxable->taxable_value;
                        $total_outward_inward_taxable_cgst += $outward_supplies_taxable->cgst;
                        $total_outward_inward_taxable_sgst += $outward_supplies_taxable->sgst;
                        $total_outward_inward_taxable_igst += $outward_supplies_taxable->igst;
                        $total_outward_inward_taxable_tax += $outward_supplies_taxable->tax_amount;
                        $total_outward_inward_taxable_cess += $outward_supplies_taxable->cess_amount;
                  }

                  if ($outward_supplies_zero_rated) {
                        $total_outward_inward_taxable_value += $outward_supplies_zero_rated->taxable_value;
                        $total_outward_inward_taxable_cgst += $outward_supplies_zero_rated->cgst;
                        $total_outward_inward_taxable_sgst += $outward_supplies_zero_rated->sgst;
                        $total_outward_inward_taxable_igst += $outward_supplies_zero_rated->igst;
                        $total_outward_inward_taxable_tax += $outward_supplies_zero_rated->tax_amount;
                        $total_outward_inward_taxable_cess += $outward_supplies_zero_rated->cess_amount;
                  }

                  $reversal_tax_liability_capital = AccountTaxManager::getJournalVouchers($id, $start_date, $end_date, SystemTablesStatus::DB_TBL_REVERSAL_OF_TAX_LIABILITY_INPUT_TAX_CREDIT, SystemTablesStatus::DB_TBL_9_CAPITAL_CREDIT_DUE_TO_EXEMPTED_SUPPLIES_RULE_43_1_H, 1, $chkid_array);

                  $reversal_tax_liability_exempt = AccountTaxManager::getJournalVouchers($id, $start_date, $end_date, SystemTablesStatus::DB_TBL_REVERSAL_OF_TAX_LIABILITY_INPUT_TAX_CREDIT, SystemTablesStatus::DB_TBL_9_EXEMPT_AND_NONBUSINESS_SUPPLIES_RULE_42_1_M, 1, $chkid_array);

                  if ($outward_supplies_nil_exempted) {

                        if ($reversal_tax_liability_capital) {
                              $outward_supplies_nil_exempted->cgst -= $reversal_tax_liability_capital->cgst;
                              $outward_supplies_nil_exempted->sgst -= $reversal_tax_liability_capital->sgst;
                              $outward_supplies_nil_exempted->igst -= $reversal_tax_liability_capital->igst;
                              $outward_supplies_nil_exempted->tax_amount -= $reversal_tax_liability_capital->tax_amount;
                              $outward_supplies_nil_exempted->taxable_value -= $reversal_tax_liability_capital->taxable_value;
                        }

                        if ($reversal_tax_liability_exempt) {
                              $outward_supplies_nil_exempted->cgst -= $reversal_tax_liability_exempt->cgst;
                              $outward_supplies_nil_exempted->sgst -= $reversal_tax_liability_exempt->sgst;
                              $outward_supplies_nil_exempted->igst -= $reversal_tax_liability_exempt->igst;
                              $outward_supplies_nil_exempted->tax_amount -= $reversal_tax_liability_exempt->tax_amount;
                              $outward_supplies_nil_exempted->taxable_value -= $reversal_tax_liability_exempt->taxable_value;
                        }

                        $total_outward_inward_taxable_value += $outward_supplies_nil_exempted->taxable_value;
                        $total_outward_inward_taxable_cgst += $outward_supplies_nil_exempted->cgst;
                        $total_outward_inward_taxable_sgst += $outward_supplies_nil_exempted->sgst;
                        $total_outward_inward_taxable_igst += $outward_supplies_nil_exempted->igst;
                        $total_outward_inward_taxable_tax += $outward_supplies_nil_exempted->tax_amount;
                        $total_outward_inward_taxable_cess += $outward_supplies_nil_exempted->cess_amount;
                  }


                  if ($outward_supplies_non_gst) {
                        $total_outward_inward_taxable_value += $outward_supplies_non_gst->taxable_value;
                        $total_outward_inward_taxable_cgst += $outward_supplies_non_gst->cgst;
                        $total_outward_inward_taxable_sgst += $outward_supplies_non_gst->sgst;
                        $total_outward_inward_taxable_igst += $outward_supplies_non_gst->igst;
                        $total_outward_inward_taxable_tax += $outward_supplies_non_gst->tax_amount;
                        $total_outward_inward_taxable_cess += $outward_supplies_non_gst->cess_amount;
                  }

                  $cancellation = AccountTaxManager::getJournalVouchers($id, $start_date, $end_date, SystemTablesStatus::DB_TBL_DECREASE_OF_TAX_LIABILITY, SystemTablesStatus::DB_TBL_1_CANCELLATION_OF_ADVANCE_PAYMENT_UNDER_REVERSE_CHARGE, 1, $chkid_array);

                  $purchase = AccountTaxManager::getJournalVouchers($id, $start_date, $end_date, SystemTablesStatus::DB_TBL_DECREASE_OF_TAX_LIABILITY, SystemTablesStatus::DB_TBL_1_PURCHASE_AGAINST_ADVANCE_PAYMENT, 1, $chkid_array);

                  $imports_services = AccountTaxManager::getJournalVouchers($id, $start_date, $end_date, SystemTablesStatus::DB_TBL_INCREASE_OF_TAX_LIABILITY, SystemTablesStatus::DB_TBL_3_IMPORTS_OF_SERVICES, 2, $chkid_array);

                  $imports = AccountTaxManager::getJournalVouchers($id, $start_date, $end_date, SystemTablesStatus::DB_TBL_INCREASE_OF_TAX_LIABILITY, SystemTablesStatus::DB_TBL_3_IMPORTS_OF_GOODS, 2, $chkid_array);

                  $imports_goods = AccountTaxManager::getJournalVouchers($id, $start_date, $end_date, SystemTablesStatus::DB_TBL_INCREASE_OF_TAX_LIABILITY, SystemTablesStatus::DB_TBL_3_IMPORTS_OF_CAPTIAL_GOODS, 2, $chkid_array);

                  $purchase_reverse = AccountTaxManager::getJournalVouchers($id, $start_date, $end_date, SystemTablesStatus::DB_TBL_INCREASE_OF_TAX_LIABILITY, SystemTablesStatus::DB_TBL_3_PURCHASE_UNDER_REVERSE_CHARGE, 2, $chkid_array);

                  $tax_paid_reverse = AccountTaxManager::getJournalVouchers($id, $start_date, $end_date, SystemTablesStatus::DB_TBL_INCREASE_OF_TAX_LIABILITY, SystemTablesStatus::DB_TBL_3_TAX_PAID_ON_ADVANCE_UNDER_REVERSE_CHARGE, 2, $chkid_array);

                  $increase_purchase_reverse = AccountTaxManager::getJournalVouchers($id, $start_date, $end_date, SystemTablesStatus::DB_TBL_INCREASE_OF_TAX_LIABILITY_INPUT_TAX_CREDIT, SystemTablesStatus::DB_TBL_4_PURCHASE_UNDER_REVERSE_CHARGE, 2, $chkid_array);

                  $increase_imports_services = AccountTaxManager::getJournalVouchers($id, $start_date, $end_date, SystemTablesStatus::DB_TBL_INCREASE_OF_TAX_LIABILITY_INPUT_TAX_CREDIT, SystemTablesStatus::DB_TBL_4_IMPORTS_OF_SERVICES, 2, $chkid_array);

                  $reversal_imports_services = AccountTaxManager::getJournalVouchers($id, $start_date, $end_date, SystemTablesStatus::DB_TBL_REVERSE_OF_TAX_LIABILITY, SystemTablesStatus::DB_TBL_8_IMPORTS_OF_SERVICES, 1, $chkid_array);

                  $reversal_purchase = AccountTaxManager::getJournalVouchers($id, $start_date, $end_date, SystemTablesStatus::DB_TBL_REVERSE_OF_TAX_LIABILITY, SystemTablesStatus::DB_TBL_8_PURCHASE_UNDER_REVERSE_CHARGE, 1, $chkid_array);

                  $reversal_input_purchase = AccountTaxManager::getJournalVouchers($id, $start_date, $end_date, SystemTablesStatus::DB_TBL_REVERSAL_OF_TAX_LIABILITY_INPUT_TAX_CREDIT, SystemTablesStatus::DB_TBL_9_PURCHASE_UNDER_REVERSE_CHARGE, 1, $chkid_array);

                  $reversal_input_imports_services = AccountTaxManager::getJournalVouchers($id, $start_date, $end_date, SystemTablesStatus::DB_TBL_REVERSAL_OF_TAX_LIABILITY_INPUT_TAX_CREDIT, SystemTablesStatus::DB_TBL_9_IMPORTS_OF_SERVICES, 1, $chkid_array);

                  if ($inward_liable_reverse_charge) {
                        if ($cancellation) {
                              $inward_liable_reverse_charge->cgst -= $cancellation->cgst;
                              $inward_liable_reverse_charge->sgst -= $cancellation->sgst;
                              $inward_liable_reverse_charge->igst -= $cancellation->igst;
                              $inward_liable_reverse_charge->tax_amount -= $cancellation->tax_amount;
                              $inward_liable_reverse_charge->taxable_value -= $cancellation->taxable_value;
                        }
                        if ($reversal_input_imports_services) {
                              $inward_liable_reverse_charge->cgst -= $reversal_input_imports_services->cgst;
                              $inward_liable_reverse_charge->sgst -= $reversal_input_imports_services->sgst;
                              $inward_liable_reverse_charge->igst -= $reversal_input_imports_services->igst;
                              $inward_liable_reverse_charge->tax_amount -= $reversal_input_imports_services->tax_amount;
                              $inward_liable_reverse_charge->taxable_value -= $reversal_input_imports_services->taxable_value;
                        }
                        if ($reversal_input_purchase) {
                              $inward_liable_reverse_charge->cgst -= $reversal_input_purchase->cgst;
                              $inward_liable_reverse_charge->sgst -= $reversal_input_purchase->sgst;
                              $inward_liable_reverse_charge->igst -= $reversal_input_purchase->igst;
                              $inward_liable_reverse_charge->tax_amount -= $reversal_input_purchase->tax_amount;
                              $inward_liable_reverse_charge->taxable_value -= $reversal_input_purchase->taxable_value;
                        }
                        if ($reversal_purchase) {
                              $inward_liable_reverse_charge->cgst -= $reversal_purchase->cgst;
                              $inward_liable_reverse_charge->sgst -= $reversal_purchase->sgst;
                              $inward_liable_reverse_charge->igst -= $reversal_purchase->igst;
                              $inward_liable_reverse_charge->tax_amount -= $reversal_purchase->tax_amount;
                              $inward_liable_reverse_charge->taxable_value -= $reversal_purchase->taxable_value;
                        }
                        if ($reversal_imports_services) {
                              $inward_liable_reverse_charge->cgst -= $reversal_imports_services->cgst;
                              $inward_liable_reverse_charge->sgst -= $reversal_imports_services->sgst;
                              $inward_liable_reverse_charge->igst -= $reversal_imports_services->igst;
                              $inward_liable_reverse_charge->tax_amount -= $reversal_imports_services->tax_amount;
                              $inward_liable_reverse_charge->taxable_value -= $reversal_imports_services->taxable_value;
                        }
                        if ($increase_purchase_reverse) {
                              $inward_liable_reverse_charge->cgst += $increase_purchase_reverse->cgst;
                              $inward_liable_reverse_charge->sgst += $increase_purchase_reverse->sgst;
                              $inward_liable_reverse_charge->igst += $increase_purchase_reverse->igst;
                              $inward_liable_reverse_charge->tax_amount += $increase_purchase_reverse->tax_amount;
                              $inward_liable_reverse_charge->taxable_value += $increase_purchase_reverse->taxable_value;
                        }
                        if ($tax_paid_reverse) {
                              $inward_liable_reverse_charge->cgst += $tax_paid_reverse->cgst;
                              $inward_liable_reverse_charge->sgst += $tax_paid_reverse->sgst;
                              $inward_liable_reverse_charge->igst += $tax_paid_reverse->igst;
                              $inward_liable_reverse_charge->tax_amount += $tax_paid_reverse->tax_amount;
                              $inward_liable_reverse_charge->taxable_value += $tax_paid_reverse->taxable_value;
                        }
                        if ($purchase_reverse) {
                              $inward_liable_reverse_charge->cgst += $purchase_reverse->cgst;
                              $inward_liable_reverse_charge->sgst += $purchase_reverse->sgst;
                              $inward_liable_reverse_charge->igst += $purchase_reverse->igst;
                              $inward_liable_reverse_charge->tax_amount += $purchase_reverse->tax_amount;
                              $inward_liable_reverse_charge->taxable_value += $purchase_reverse->taxable_value;
                        }
                        if ($imports_goods) {
                              $inward_liable_reverse_charge->cgst += $imports_goods->cgst;
                              $inward_liable_reverse_charge->sgst += $imports_goods->sgst;
                              $inward_liable_reverse_charge->igst += $imports_goods->igst;
                              $inward_liable_reverse_charge->tax_amount += $imports_goods->tax_amount;
                              $inward_liable_reverse_charge->taxable_value += $imports_goods->taxable_value;
                        }
                        if ($imports_services) {
                              $inward_liable_reverse_charge->cgst += $imports_services->cgst;
                              $inward_liable_reverse_charge->sgst += $imports_services->sgst;
                              $inward_liable_reverse_charge->igst += $imports_services->igst;
                              $inward_liable_reverse_charge->tax_amount += $imports_services->tax_amount;
                              $inward_liable_reverse_charge->taxable_value += $imports_services->taxable_value;
                        }
                        if ($imports) {
                              $inward_liable_reverse_charge->cgst += $imports->cgst;
                              $inward_liable_reverse_charge->sgst += $imports->sgst;
                              $inward_liable_reverse_charge->igst += $imports->igst;
                              $inward_liable_reverse_charge->tax_amount += $imports->tax_amount;
                              $inward_liable_reverse_charge->taxable_value += $imports->taxable_value;
                        }
                        if ($purchase) {
                              $inward_liable_reverse_charge->cgst -= $purchase->cgst;
                              $inward_liable_reverse_charge->sgst -= $purchase->sgst;
                              $inward_liable_reverse_charge->igst -= $purchase->igst;
                              $inward_liable_reverse_charge->tax_amount -= $purchase->tax_amount;
                              $inward_liable_reverse_charge->taxable_value -= $purchase->taxable_value;
                        }
                        if ($cancellation) {
                              $inward_liable_reverse_charge->cgst -= $cancellation->cgst;
                              $inward_liable_reverse_charge->sgst -= $cancellation->sgst;
                              $inward_liable_reverse_charge->igst -= $cancellation->igst;
                              $inward_liable_reverse_charge->taxable_value -= $cancellation->taxable_value;
                        }
                        $total_outward_inward_taxable_value += $inward_liable_reverse_charge->taxable_value;
                        $total_outward_inward_taxable_cgst += $inward_liable_reverse_charge->cgst;
                        $total_outward_inward_taxable_sgst += $inward_liable_reverse_charge->sgst;
                        $total_outward_inward_taxable_igst += $inward_liable_reverse_charge->igst;
                        $total_outward_inward_taxable_tax += $inward_liable_reverse_charge->tax_amount;
                        $total_outward_inward_taxable_cess += $inward_liable_reverse_charge->cess_amount;
                  }

                  if ($outward_supplies_unregistered) {
                        $total_outward_unreg_taxable_value += $outward_supplies_unregistered->taxable_value;
                        $total_outward_unreg_taxable_cgst += $outward_supplies_unregistered->cgst;
                        $total_outward_unreg_taxable_sgst += $outward_supplies_unregistered->sgst;
                        $total_outward_unreg_taxable_igst += $outward_supplies_unregistered->igst;
                        $total_outward_unreg_taxable_tax += $outward_supplies_unregistered->tax_amount;
                        $total_outward_unreg_taxable_cess += $outward_supplies_unregistered->cess_amount;
                  }

                  $purchase_from_sez = AccountTaxManager::getJournalVouchers($id, $start_date, $end_date, SystemTablesStatus::DB_TBL_INCREASE_OF_INPUT_TAX_CREDIT, SystemTablesStatus::DB_TBL_2_PURCHASE_FROM_SEZ, 1, $chkid_array);

                  $reclaim_buyer = AccountTaxManager::getJournalVouchers($id, $start_date, $end_date, SystemTablesStatus::DB_TBL_INCREASE_OF_INPUT_TAX_CREDIT, SystemTablesStatus::DB_TBL_2_RECLAIM_OF_REVERSAL_ITC_ON_ACCOUNT_OF_BUYER_PAYMENT, 1, $chkid_array);

                  $reclaim_itc = AccountTaxManager::getJournalVouchers($id, $start_date, $end_date, SystemTablesStatus::DB_TBL_INCREASE_OF_INPUT_TAX_CREDIT, SystemTablesStatus::DB_TBL_2_RECLAIM_OF_REVERSAL_ITC_RULE_42_2_B, 1, $chkid_array);
                  $increase_input_goods = AccountTaxManager::getJournalVouchers($id, $start_date, $end_date, SystemTablesStatus::DB_TBL_INCREASE_OF_INPUT_TAX_CREDIT, SystemTablesStatus::DB_TBL_2_IMPORTS_OF_GOODS, 1, $chkid_array);

                  $increase_input_capital_goods = AccountTaxManager::getJournalVouchers($id, $start_date, $end_date, SystemTablesStatus::DB_TBL_INCREASE_OF_INPUT_TAX_CREDIT, SystemTablesStatus::DB_TBL_2_IMPORTS_OF_CAPTIAL_GOODS, 1, $chkid_array);
                  $increase_input_services = AccountTaxManager::getJournalVouchers($id, $start_date, $end_date, SystemTablesStatus::DB_TBL_INCREASE_OF_INPUT_TAX_CREDIT, SystemTablesStatus::DB_TBL_2_IMPORTS_OF_SERVICES, 1, $chkid_array);

                  $increase_input_tcs = AccountTaxManager::getJournalVouchers($id, $start_date, $end_date, SystemTablesStatus::DB_TBL_INCREASE_OF_INPUT_TAX_CREDIT, SystemTablesStatus::DB_TBL_2_TCS_ADJUSTMENT, 1, $chkid_array);

                  if ($import_of_goods) {
                        if ($purchase_from_sez) {
                              $import_of_goods->cgst -= $purchase_from_sez->cgst;
                              $import_of_goods->sgst -= $purchase_from_sez->sgst;
                              $import_of_goods->igst -= $purchase_from_sez->igst;
                              $import_of_goods->tax_amount -= $purchase_from_sez->tax_amount;
                              $import_of_goods->taxable_value -= $purchase_from_sez->taxable_value;
                        }
                        if ($reclaim_buyer) {
                              $import_of_goods->cgst -= $reclaim_buyer->cgst;
                              $import_of_goods->sgst -= $reclaim_buyer->sgst;
                              $import_of_goods->igst -= $reclaim_buyer->igst;
                              $import_of_goods->tax_amount -= $reclaim_buyer->tax_amount;
                              $import_of_goods->taxable_value -= $reclaim_buyer->taxable_value;
                        }
                        //                    if ($reclaim_itc)
                        //                    {
                        //                        $import_of_goods->cgst -= $reclaim_itc->cgst;
                        //                        $import_of_goods->sgst -= $reclaim_itc->sgst;
                        //                        $import_of_goods->igst -= $reclaim_itc->igst;
                        //                        $import_of_goods->tax_amount -= $reclaim_itc->tax_amount;
                        //                        $import_of_goods->taxable_value -= $reclaim_itc->taxable_value;
                        //                    }
                        if ($increase_input_goods) {
                              $import_of_goods->cgst -= $increase_input_goods->cgst;
                              $import_of_goods->sgst -= $increase_input_goods->sgst;
                              $import_of_goods->igst -= $increase_input_goods->igst;
                              $import_of_goods->tax_amount -= $increase_input_goods->tax_amount;
                              $import_of_goods->taxable_value -= $increase_input_goods->taxable_value;
                        }
                        if ($increase_input_capital_goods) {
                              $import_of_goods->cgst -= $increase_input_capital_goods->cgst;
                              $import_of_goods->sgst -= $increase_input_capital_goods->sgst;
                              $import_of_goods->igst -= $increase_input_capital_goods->igst;
                              $import_of_goods->tax_amount -= $increase_input_capital_goods->tax_amount;
                              $import_of_goods->taxable_value -= $increase_input_capital_goods->taxable_value;
                        }

                        if ($increase_input_tcs) {
                              $import_of_goods->cgst -= $increase_input_tcs->cgst;
                              $import_of_goods->sgst -= $increase_input_tcs->sgst;
                              $import_of_goods->igst -= $increase_input_tcs->igst;
                              $import_of_goods->tax_amount -= $increase_input_tcs->tax_amount;
                              $import_of_goods->taxable_value -= $increase_input_tcs->taxable_value;
                        }
                        $total_itc_taxable_value += $import_of_goods->taxable_value;
                        $total_itc_taxable_cgst += $import_of_goods->cgst;
                        $total_itc_taxable_sgst += $import_of_goods->sgst;
                        $total_itc_taxable_igst += $import_of_goods->igst;
                        $total_itc_taxable_tax += $import_of_goods->tax_amount;
                        $total_itc_taxable_value += $import_of_goods->taxable_value;
                  }


                  if ($increase_input_services) {
                        $total_itc_taxable_cgst += $increase_input_services->cgst;
                        $total_itc_taxable_sgst += $increase_input_services->sgst;
                        $total_itc_taxable_igst += $increase_input_services->igst;
                        $total_itc_taxable_tax += $increase_input_services->tax_amount;
                        $total_itc_taxable_value += $increase_input_services->taxable_value;
                  }

                  $increase_input_purchase = AccountTaxManager::getJournalVouchers($id, $start_date, $end_date, SystemTablesStatus::DB_TBL_INCREASE_OF_INPUT_TAX_CREDIT, SystemTablesStatus::DB_TBL_2_PURCHASE_UNDER_REVERSE_CHARGE, 1, $chkid_array);

                  $increase_credit_purchase = AccountTaxManager::getJournalVouchers($id, $start_date, $end_date, SystemTablesStatus::DB_TBL_INCREASE_OF_TAX_LIABILITY_INPUT_TAX_CREDIT, SystemTablesStatus::DB_TBL_4_PURCHASE_UNDER_REVERSE_CHARGE, 1, $chkid_array);
                  $increase_credit_import_services = AccountTaxManager::getJournalVouchers($id, $start_date, $end_date, SystemTablesStatus::DB_TBL_INCREASE_OF_TAX_LIABILITY_INPUT_TAX_CREDIT, SystemTablesStatus::DB_TBL_4_IMPORTS_OF_SERVICES, 1, $chkid_array);

                  if ($increase_input_purchase) {
                        if ($increase_credit_purchase) {
                              $increase_input_purchase->cgst -= $increase_credit_purchase->cgst;
                              $increase_input_purchase->sgst -= $increase_credit_purchase->sgst;
                              $increase_input_purchase->igst -= $increase_credit_purchase->igst;
                              $increase_input_purchase->tax_amount -= $increase_credit_purchase->tax_amount;
                              $increase_input_purchase->taxable_value -= $increase_credit_purchase->taxable_value;
                        }

                        if ($increase_credit_import_services) {
                              $increase_input_purchase->cgst -= $increase_credit_import_services->cgst;
                              $increase_input_purchase->sgst -= $increase_credit_import_services->sgst;
                              $increase_input_purchase->igst -= $increase_credit_import_services->igst;
                              $increase_input_purchase->tax_amount -= $increase_credit_import_services->tax_amount;
                              $increase_input_purchase->taxable_value -= $increase_credit_import_services->taxable_value;
                        }

                        $total_itc_taxable_cgst += $increase_input_purchase->cgst;
                        $total_itc_taxable_sgst += $increase_input_purchase->sgst;
                        $total_itc_taxable_igst += $increase_input_purchase->igst;
                        $total_itc_taxable_tax += $increase_input_purchase->tax_amount;
                        $total_itc_taxable_value += $increase_input_purchase->taxable_value;
                  }

                  $increase_input_isd = AccountTaxManager::getJournalVouchers($id, $start_date, $end_date, SystemTablesStatus::DB_TBL_INCREASE_OF_INPUT_TAX_CREDIT, SystemTablesStatus::DB_TBL_2_ISD_TRANSFER, 1, $chkid_array);

                  if ($increase_input_isd) {
                        $total_itc_taxable_cgst -= $increase_input_isd->cgst;
                        $total_itc_taxable_sgst -= $increase_input_isd->sgst;
                        $total_itc_taxable_igst -= $increase_input_isd->igst;
                        $total_itc_taxable_tax -= $increase_input_isd->tax_amount;
                        $total_itc_taxable_value -= $increase_input_isd->taxable_value;
                  }

                  $increase_input_tds = AccountTaxManager::getJournalVouchers($id, $start_date, $end_date, SystemTablesStatus::DB_TBL_INCREASE_OF_INPUT_TAX_CREDIT, SystemTablesStatus::DB_TBL_2_TDS_ADJUSTMENT, 1, $chkid_array);

                  $increase_input_transitional = AccountTaxManager::getJournalVouchers($id, $start_date, $end_date, SystemTablesStatus::DB_TBL_INCREASE_OF_INPUT_TAX_CREDIT, SystemTablesStatus::DB_TBL_2_TRANSITIONAL_CREDIT, 1, $chkid_array);

                  $increase_input_other = AccountTaxManager::getJournalVouchers($id, $start_date, $end_date, SystemTablesStatus::DB_TBL_INCREASE_OF_INPUT_TAX_CREDIT, SystemTablesStatus::DB_TBL_2_OTHER, 1, $chkid_array);

                  if ($other_itc) {
                        if ($increase_input_tds) {
                              $other_itc->cgst -= $increase_input_tds->cgst;
                              $other_itc->sgst -= $increase_input_tds->sgst;
                              $other_itc->igst -= $increase_input_tds->igst;
                              $other_itc->tax_amount -= $increase_input_tds->tax_amount;
                              $other_itc->taxable_value -= $increase_input_tds->taxable_value;
                        }
                        if ($increase_input_transitional) {
                              $other_itc->cgst -= $increase_input_transitional->cgst;
                              $other_itc->sgst -= $increase_input_transitional->sgst;
                              $other_itc->igst -= $increase_input_transitional->igst;
                              $other_itc->tax_amount -= $increase_input_transitional->tax_amount;
                              $other_itc->taxable_value -= $increase_input_transitional->taxable_value;
                        }

                        if ($increase_input_other) {
                              $other_itc->cgst -= $increase_input_other->cgst;
                              $other_itc->sgst -= $increase_input_other->sgst;
                              $other_itc->igst -= $increase_input_other->igst;
                              $other_itc->tax_amount -= $increase_input_other->tax_amount;
                              $other_itc->taxable_value -= $increase_input_other->taxable_value;
                        }

                        $total_itc_taxable_value += $other_itc->taxable_value;
                        $total_itc_taxable_cgst += $other_itc->cgst;
                        $total_itc_taxable_sgst += $other_itc->sgst;
                        $total_itc_taxable_igst += $other_itc->igst;
                        $total_itc_taxable_tax += $other_itc->tax_amount;
                        $total_itc_taxable_cess += $other_itc->cess_amount;
                  }

                  if ($inward_nil_rated) {
                        $total_nil_exemp_taxable_value += $inward_nil_rated->taxable_value;
                        $total_nil_exemp_taxable_cgst += $inward_nil_rated->cgst;
                        $total_nil_exemp_taxable_sgst += $inward_nil_rated->sgst;
                        $total_nil_exemp_taxable_igst += $inward_nil_rated->igst;
                        $total_nil_exemp_taxable_tax += $inward_nil_rated->tax_amount;
                        $total_nil_exemp_taxable_cess += $inward_nil_rated->cess_amount;
                  }


                  if ($inward_non_gst) {
                        $total_nil_exemp_taxable_value += $inward_non_gst->taxable_value;
                        $total_nil_exemp_taxable_cgst += $inward_non_gst->cgst;
                        $total_nil_exemp_taxable_sgst += $inward_non_gst->sgst;
                        $total_nil_exemp_taxable_igst += $inward_non_gst->igst;
                        $total_nil_exemp_taxable_tax += $inward_non_gst->tax_amount;
                        $total_nil_exemp_taxable_cess += $inward_non_gst->cess_amount;
                  }

                  $increase_input_claim = AccountTaxManager::getJournalVouchers($id, $start_date, $end_date, SystemTablesStatus::DB_TBL_REVERSAL_OF_TAX_LIABILITY_INPUT_TAX_CREDIT, SystemTablesStatus::DB_TBL_9_ON_ACCOUNT_OF_CLAIMING_MORE_RULE_42_2_A, 2, $chkid_array);

                  $increase_input_capital_credit = AccountTaxManager::getJournalVouchers($id, $start_date, $end_date, SystemTablesStatus::DB_TBL_REVERSAL_OF_TAX_LIABILITY_INPUT_TAX_CREDIT, SystemTablesStatus::DB_TBL_9_CAPITAL_CREDIT_DUE_TO_EXEMPTED_SUPPLIES_RULE_43_1_H, 2, $chkid_array);

                  $increase_input_exempt = AccountTaxManager::getJournalVouchers($id, $start_date, $end_date, SystemTablesStatus::DB_TBL_REVERSAL_OF_TAX_LIABILITY_INPUT_TAX_CREDIT, SystemTablesStatus::DB_TBL_9_EXEMPT_AND_NONBUSINESS_SUPPLIES_RULE_42_1_M, 2, $chkid_array);
                  $increase_credit_ineligible_credit = AccountTaxManager::getJournalVouchers($id, $start_date, $end_date, SystemTablesStatus::DB_TBL_REVERSAL_OF_TAX_LIABILITY_INPUT_TAX_CREDIT, SystemTablesStatus::DB_TBL_9_INELIGIBLE_CREDIT, 2, $chkid_array);
                  $total_itc_reversed = new stdClass();
                  $total_itc_reversed->cgst = $total_itc_reversed->sgst = $total_itc_reversed->igst = 0;
                  $total_itc_reversed->tax_amount = 0;
                  $total_itc_reversed->taxable_value = 0;
                  if ($increase_input_claim) {
                        if ($ineligible_38) {
                              $increase_input_claim->cgst += $ineligible_38->cgst;
                              $increase_input_claim->sgst += $ineligible_38->sgst;
                              $increase_input_claim->igst += $ineligible_38->igst;
                              $increase_input_claim->tax_amount += $ineligible_38->tax_amount;
                              $increase_input_claim->taxable_value += $ineligible_38->taxable_value;
                        }
                        if ($ineligible_42) {
                              $increase_input_claim->cgst += $ineligible_42->cgst;
                              $increase_input_claim->sgst += $ineligible_42->sgst;
                              $increase_input_claim->igst += $ineligible_42->igst;
                              $increase_input_claim->tax_amount += $ineligible_42->tax_amount;
                              $increase_input_claim->taxable_value += $ineligible_42->taxable_value;
                        }
                        if ($ineligible_43) {
                              $increase_input_claim->cgst += $ineligible_43->cgst;
                              $increase_input_claim->sgst += $ineligible_43->sgst;
                              $increase_input_claim->igst += $ineligible_43->igst;
                              $increase_input_claim->tax_amount += $ineligible_43->tax_amount;
                              $increase_input_claim->taxable_value += $ineligible_43->taxable_value;
                        }
                        if ($ineligible_sec) {
                              $increase_input_claim->cgst += $ineligible_sec->cgst;
                              $increase_input_claim->sgst += $ineligible_sec->sgst;
                              $increase_input_claim->igst += $ineligible_sec->igst;
                              $increase_input_claim->tax_amount += $ineligible_sec->tax_amount;
                              $increase_input_claim->taxable_value += $ineligible_sec->taxable_value;
                        }
                        if ($increase_input_capital_credit) {
                              $increase_input_claim->cgst += $increase_input_capital_credit->cgst;
                              $increase_input_claim->sgst += $increase_input_capital_credit->sgst;
                              $increase_input_claim->igst += $increase_input_capital_credit->igst;
                              $increase_input_claim->tax_amount += $increase_input_capital_credit->tax_amount;
                              $increase_input_claim->taxable_value += $increase_input_capital_credit->taxable_value;
                        }
                        if ($increase_input_exempt) {
                              $increase_input_claim->cgst += $increase_input_exempt->cgst;
                              $increase_input_claim->sgst += $increase_input_exempt->sgst;
                              $increase_input_claim->igst += $increase_input_exempt->igst;
                              $increase_input_claim->tax_amount += $increase_input_exempt->tax_amount;
                              $increase_input_claim->taxable_value += $increase_input_exempt->taxable_value;
                        }
                        if ($increase_credit_ineligible_credit) {
                              $total_count += $increase_credit_ineligible_credit->count;
                              $total_voucher_count += $increase_credit_ineligible_credit->count;
                              $increase_input_claim->cgst += $increase_credit_ineligible_credit->cgst;
                              $increase_input_claim->sgst += $increase_credit_ineligible_credit->sgst;
                              $increase_input_claim->igst += $increase_credit_ineligible_credit->igst;
                              $increase_input_claim->tax_amount += $increase_credit_ineligible_credit->tax_amount;
                              $increase_input_claim->taxable_value += $increase_credit_ineligible_credit->taxable_value;
                        }


                        $total_itc_reversed->cgst += $increase_input_claim->cgst;
                        $total_itc_reversed->sgst += $increase_input_claim->sgst;
                        $total_itc_reversed->igst += $increase_input_claim->igst;
                        $total_itc_reversed->tax_amount += $increase_input_claim->tax_amount;
                        $total_itc_reversed->taxable_value += $increase_input_claim->taxable_value;

                        $total_itc_taxable_cgst -= $increase_input_claim->cgst;
                        $total_itc_taxable_sgst -= $increase_input_claim->sgst;
                        $total_itc_taxable_igst -= $increase_input_claim->igst;
                        $total_itc_taxable_tax -= $increase_input_claim->tax_amount;
                        $total_itc_taxable_value -= $increase_input_claim->taxable_value;
                  }
                  $increase_credit_ineligible_credit = new stdClass();
                  $increase_credit_ineligible_credit->tax_amount = 0;
                  $increase_credit_ineligible_credit->cgst = 0;
                  $increase_credit_ineligible_credit->sgst = 0;
                  $increase_credit_ineligible_credit->igst = 0;
                  $increase_credit_ineligible_credit->taxable_value = 0;

                  $increase_credit_isd_credit = AccountTaxManager::getJournalVouchers($id, $start_date, $end_date, SystemTablesStatus::DB_TBL_REVERSAL_OF_TAX_LIABILITY_INPUT_TAX_CREDIT, SystemTablesStatus::DB_TBL_9_ISD_CREDIT_NOTE_RULE_39_1_J, 2, $chkid_array);
                  $increase_credit_non_payment = AccountTaxManager::getJournalVouchers($id, $start_date, $end_date, SystemTablesStatus::DB_TBL_REVERSAL_OF_TAX_LIABILITY_INPUT_TAX_CREDIT, SystemTablesStatus::DB_TBL_9_NON_PAYMENT_TO_THE_BUYER_RULE_37_2, 2, $chkid_array);
                  $increase_credit_purchase_reverse = AccountTaxManager::getJournalVouchers($id, $start_date, $end_date, SystemTablesStatus::DB_TBL_REVERSAL_OF_TAX_LIABILITY_INPUT_TAX_CREDIT, SystemTablesStatus::DB_TBL_9_PURCHASE_UNDER_REVERSE_CHARGE, 2, $chkid_array);
                  $increase_credit_others = AccountTaxManager::getJournalVouchers($id, $start_date, $end_date, SystemTablesStatus::DB_TBL_REVERSAL_OF_TAX_LIABILITY_INPUT_TAX_CREDIT, SystemTablesStatus::DB_TBL_9_OTHERS, 2, $chkid_array);
                  $increase_credit_not_applicable = AccountTaxManager::getJournalVouchers($id, $start_date, $end_date, SystemTablesStatus::DB_TBL_REVERSAL_OF_TAX_LIABILITY_INPUT_TAX_CREDIT, SystemTablesStatus::DB_TBL_9_NOT_APPLICABLE, 2, $chkid_array);
                  $increase_input_credit_import_services = AccountTaxManager::getJournalVouchers($id, $start_date, $end_date, SystemTablesStatus::DB_TBL_REVERSAL_OF_TAX_LIABILITY_INPUT_TAX_CREDIT, SystemTablesStatus::DB_TBL_9_IMPORTS_OF_SERVICES, 2, $chkid_array);

                  if ($increase_credit_ineligible_credit) {
                        if ($ineligible_others) {
                              $increase_credit_ineligible_credit->cgst += $ineligible_others->cgst;
                              $increase_credit_ineligible_credit->sgst += $ineligible_others->sgst;
                              $increase_credit_ineligible_credit->igst += $ineligible_others->igst;
                              $increase_credit_ineligible_credit->tax_amount += $ineligible_others->tax_amount;
                              $increase_credit_ineligible_credit->taxable_value += $ineligible_others->taxable_value;
                        }
                        if ($increase_credit_isd_credit) {
                              $increase_credit_ineligible_credit->cgst += $increase_credit_isd_credit->cgst;
                              $increase_credit_ineligible_credit->sgst += $increase_credit_isd_credit->sgst;
                              $increase_credit_ineligible_credit->igst += $increase_credit_isd_credit->igst;
                              $increase_credit_ineligible_credit->tax_amount += $increase_credit_isd_credit->tax_amount;
                              $increase_credit_ineligible_credit->taxable_value += $increase_credit_isd_credit->taxable_value;
                        }
                        if ($increase_credit_non_payment) {
                              $increase_credit_ineligible_credit->cgst += $increase_credit_non_payment->cgst;
                              $increase_credit_ineligible_credit->sgst += $increase_credit_non_payment->sgst;
                              $increase_credit_ineligible_credit->igst += $increase_credit_non_payment->igst;
                              $increase_credit_ineligible_credit->tax_amount += $increase_credit_non_payment->tax_amount;
                              $increase_credit_ineligible_credit->taxable_value += $increase_credit_non_payment->taxable_value;
                        }
                        if ($increase_credit_purchase_reverse) {
                              $increase_credit_ineligible_credit->cgst += $increase_credit_purchase_reverse->cgst;
                              $increase_credit_ineligible_credit->sgst += $increase_credit_purchase_reverse->sgst;
                              $increase_credit_ineligible_credit->igst += $increase_credit_purchase_reverse->igst;
                              $increase_credit_ineligible_credit->tax_amount += $increase_credit_purchase_reverse->tax_amount;
                              $increase_credit_ineligible_credit->taxable_value += $increase_credit_purchase_reverse->taxable_value;
                        }
                        if ($increase_credit_others) {
                              $increase_credit_ineligible_credit->cgst += $increase_credit_others->cgst;
                              $increase_credit_ineligible_credit->sgst += $increase_credit_others->sgst;
                              $increase_credit_ineligible_credit->igst += $increase_credit_others->igst;
                              $increase_credit_ineligible_credit->tax_amount += $increase_credit_others->tax_amount;
                              $increase_credit_ineligible_credit->taxable_value += $increase_credit_others->taxable_value;
                        }
                        if ($increase_credit_not_applicable) {
                              $increase_credit_ineligible_credit->cgst += $increase_credit_not_applicable->cgst;
                              $increase_credit_ineligible_credit->sgst += $increase_credit_not_applicable->sgst;
                              $increase_credit_ineligible_credit->igst += $increase_credit_not_applicable->igst;
                              $increase_credit_ineligible_credit->tax_amount += $increase_credit_not_applicable->tax_amount;
                              $increase_credit_ineligible_credit->taxable_value += $increase_credit_not_applicable->taxable_value;
                        }
                        if ($increase_input_credit_import_services) {
                              $increase_credit_ineligible_credit->cgst += $increase_input_credit_import_services->cgst;
                              $increase_credit_ineligible_credit->sgst += $increase_input_credit_import_services->sgst;
                              $increase_credit_ineligible_credit->igst += $increase_input_credit_import_services->igst;
                              $increase_credit_ineligible_credit->tax_amount += $increase_input_credit_import_services->tax_amount;
                              $increase_credit_ineligible_credit->taxable_value += $increase_input_credit_import_services->taxable_value;
                        }
                        $total_itc_reversed->cgst += $increase_credit_ineligible_credit->cgst;
                        $total_itc_reversed->sgst += $increase_credit_ineligible_credit->sgst;
                        $total_itc_reversed->igst += $increase_credit_ineligible_credit->igst;
                        $total_itc_reversed->tax_amount += $increase_credit_ineligible_credit->tax_amount;
                        $total_itc_reversed->taxable_value += $increase_credit_ineligible_credit->taxable_value;

                        $total_itc_taxable_cgst -= $increase_credit_ineligible_credit->cgst;
                        $total_itc_taxable_sgst -= $increase_credit_ineligible_credit->sgst;
                        $total_itc_taxable_igst -= $increase_credit_ineligible_credit->igst;
                        $total_itc_taxable_tax -= $increase_credit_ineligible_credit->tax_amount;
                        $total_itc_taxable_value -= $increase_credit_ineligible_credit->taxable_value;
                  }

                  $import_of_services = $increase_input_services;
                  $imports_reverse_charge = $increase_input_purchase;
                  $increase_input_isd = $increase_input_isd;
                  $itc_reverse_cgst = $increase_input_claim;
                  $itc_reverse_other = $increase_credit_ineligible_credit;

                  $ret_period = date('mY', strtotime($start_date));

                  $sup_details = array();
                  $sup_details['osup_det'] = array();
                  $sup_details['osup_det']['txval'] = 0.00;
                  $sup_details['osup_det']['iamt'] = 0.00;
                  $sup_details['osup_det']['camt'] = 0.00;
                  $sup_details['osup_det']['samt'] = 0.00;
                  $sup_details['osup_det']['csamt'] = 0.00;

                  if ($outward_supplies_taxable) {
                        $sup_details['osup_det']['txval'] = round($outward_supplies_taxable->taxable_value, 2);
                        $sup_details['osup_det']['iamt'] = round($outward_supplies_taxable->igst, 2);
                        $sup_details['osup_det']['camt'] = round($outward_supplies_taxable->cgst, 2);
                        $sup_details['osup_det']['samt'] = round($outward_supplies_taxable->sgst, 2);
                        $sup_details['osup_det']['csamt'] = round($outward_supplies_taxable->cess_amount, 2);
                  }

                  $sup_details['osup_zero'] = array();
                  $sup_details['osup_zero']['txval'] = 0.00;
                  $sup_details['osup_zero']['iamt'] = 0.00;
                  $sup_details['osup_zero']['camt'] = 0.00;
                  $sup_details['osup_zero']['samt'] = 0.00;
                  $sup_details['osup_zero']['csamt'] = 0.00;

                  $sup_details['osup_nil_exmp'] = array();
                  $sup_details['osup_nil_exmp']['txval'] = 0.00;
                  $sup_details['osup_nil_exmp']['iamt'] = 0.00;
                  $sup_details['osup_nil_exmp']['camt'] = 0.00;
                  $sup_details['osup_nil_exmp']['samt'] = 0.00;
                  $sup_details['osup_nil_exmp']['csamt'] = 0.00;

                  if ($outward_supplies_zero_rated) {
                        $sup_details['osup_zero']['txval'] = round($outward_supplies_zero_rated->taxable_value, 2);
                        $sup_details['osup_zero']['iamt'] = round($outward_supplies_zero_rated->igst, 2);
                        $sup_details['osup_zero']['camt'] = round($outward_supplies_zero_rated->cgst, 2);
                        $sup_details['osup_zero']['samt'] = round($outward_supplies_zero_rated->sgst, 2);
                        $sup_details['osup_zero']['csamt'] = round($outward_supplies_zero_rated->cess_amount, 2);
                  }
                  if ($outward_supplies_nil_exempted) {
                        $sup_details['osup_nil_exmp']['txval'] = round($outward_supplies_nil_exempted->taxable_value, 2);
                        $sup_details['osup_nil_exmp']['iamt'] = round($outward_supplies_nil_exempted->igst, 2);
                        $sup_details['osup_nil_exmp']['camt'] = round($outward_supplies_nil_exempted->cgst, 2);
                        $sup_details['osup_nil_exmp']['samt'] = round($outward_supplies_nil_exempted->sgst, 2);
                        $sup_details['osup_nil_exmp']['csamt'] = round($outward_supplies_nil_exempted->cess_amount, 2);
                  }

                  $sup_details['isup_rev'] = array();
                  $sup_details['isup_rev']['txval'] = 0.00;
                  $sup_details['isup_rev']['iamt'] = 0.00;
                  $sup_details['isup_rev']['camt'] = 0.00;
                  $sup_details['isup_rev']['samt'] = 0.00;
                  $sup_details['isup_rev']['csamt'] = 0.00;

                  if ($inward_liable_reverse_charge) {
                        $sup_details['isup_rev']['txval'] = round($inward_liable_reverse_charge->taxable_value, 2);
                        $sup_details['isup_rev']['iamt'] = round($inward_liable_reverse_charge->igst, 2);
                        $sup_details['isup_rev']['camt'] = round($inward_liable_reverse_charge->cgst, 2);
                        $sup_details['isup_rev']['samt'] = round($inward_liable_reverse_charge->sgst, 2);
                        $sup_details['isup_rev']['csamt'] = round($inward_liable_reverse_charge->cess_amount, 2);
                  }

                  $sup_details['osup_nongst'] = array();
                  $sup_details['osup_nongst']['txval'] = 0.00;
                  $sup_details['osup_nongst']['iamt'] = 0.00;
                  $sup_details['osup_nongst']['camt'] = 0.00;
                  $sup_details['osup_nongst']['samt'] = 0.00;
                  $sup_details['osup_nongst']['csamt'] = 0.00;

                  if ($outward_supplies_non_gst) {
                        $sup_details['osup_nongst']['txval'] = round($outward_supplies_non_gst->taxable_value, 2);
                        $sup_details['osup_nongst']['iamt'] = round($outward_supplies_non_gst->igst, 2);
                        $sup_details['osup_nongst']['camt'] = round($outward_supplies_non_gst->cgst, 2);
                        $sup_details['osup_nongst']['samt'] = round($outward_supplies_non_gst->sgst, 2);
                        $sup_details['osup_nongst']['csamt'] = round($outward_supplies_non_gst->cess_amount, 2);
                  }

                  $itc_elg = array();
                  $itc_elg['itc_avl'] = array();
                  $itc_elg['itc_avl'][0] = array("ty" => "IMPG", "iamt" => 0.00, "camt" => 0.00, "samt" => 0.00, "csamt" => 0.00);
                  if ($import_of_goods) {
                        $itc_elg['itc_avl'][0]['iamt'] = round($import_of_goods->igst, 2);
                        $itc_elg['itc_avl'][0]['camt'] = round($import_of_goods->cgst, 2);
                        $itc_elg['itc_avl'][0]['samt'] = round($import_of_goods->sgst, 2);
                  }

                  $itc_elg['itc_avl'][1] = array("ty" => "IMPS", "iamt" => 0.00, "camt" => 0.00, "samt" => 0.00, "csamt" => 0.00);
                  if ($import_of_services) {
                        $itc_elg['itc_avl'][1]['iamt'] = round($import_of_services->igst, 2);
                        $itc_elg['itc_avl'][1]['camt'] = round($import_of_services->cgst, 2);
                        $itc_elg['itc_avl'][1]['samt'] = round($import_of_services->sgst, 2);
                  }
                  $itc_elg['itc_avl'][2] = array("ty" => "ISRC", "iamt" => 0.00, "camt" => 0.00, "samt" => 0.00, "csamt" => 0.00);

                  if ($imports_reverse_charge) {
                        $itc_elg['itc_avl'][2]['iamt'] = round($imports_reverse_charge->igst, 2);
                        $itc_elg['itc_avl'][2]['camt'] = round($imports_reverse_charge->cgst, 2);
                        $itc_elg['itc_avl'][2]['samt'] = round($imports_reverse_charge->sgst, 2);
                  }
                  $itc_elg['itc_avl'][3] = array("ty" => "ISD", "iamt" => 0.00, "camt" => 0.00, "samt" => 0.00, "csamt" => 0.00);

                  if ($increase_input_isd) {
                        $itc_elg['itc_avl'][3]['iamt'] = round($increase_input_isd->igst, 2);
                        $itc_elg['itc_avl'][3]['camt'] = round($increase_input_isd->cgst, 2);
                        $itc_elg['itc_avl'][3]['samt'] = round($increase_input_isd->sgst, 2);
                  }

                  $itc_elg['itc_avl'][4] = array("ty" => "OTH", "iamt" => 0.00, "camt" => 0.00, "samt" => 0.00, "csamt" => 0.00);

                  if ($other_itc) {
                        $itc_elg['itc_avl'][4]['iamt'] = round($other_itc->igst, 2);
                        $itc_elg['itc_avl'][4]['camt'] = round($other_itc->cgst, 2);
                        $itc_elg['itc_avl'][4]['samt'] = round($other_itc->sgst, 2);
                        $itc_elg['itc_avl'][4]['csamt'] = round($other_itc->cess_amount, 2);
                  }


                  $itc_elg['itc_rev'] = array();
                  $itc_elg['itc_rev'][0] = array("ty" => "RUL", "iamt" => 0.00, "camt" => 0.00, "samt" => 0.00, "csamt" => 0.00);

                  if ($itc_reverse_cgst) {
                        $itc_elg['itc_rev'][0]['iamt'] = round($itc_reverse_cgst->igst, 2);
                        $itc_elg['itc_rev'][0]['camt'] = round($itc_reverse_cgst->cgst, 2);
                        $itc_elg['itc_rev'][0]['samt'] = round($itc_reverse_cgst->sgst, 2);
                  }

                  $itc_elg['itc_rev'][1] = array("ty" => "OTH", "iamt" => 0.00, "camt" => 0.00, "samt" => 0.00, "csamt" => 0.00);

                  if ($itc_reverse_other) {
                        $itc_elg['itc_rev'][0]['iamt'] = round($itc_reverse_other->igst, 2);
                        $itc_elg['itc_rev'][0]['camt'] = round($itc_reverse_other->cgst, 2);
                        $itc_elg['itc_rev'][0]['samt'] = round($itc_reverse_other->sgst, 2);
                  }

                  $itc_elg['itc_net'] = array("iamt" => round($total_itc_taxable_igst, 2), "camt" => round($total_itc_taxable_cgst, 2), "samt" => round($total_itc_taxable_sgst, 2), "csamt" => round($total_itc_taxable_cess, 2));

                  $itc_elg['itc_inelg'] = array();
                  $sec_iamt = $sec_cgst = $sec_sgst = $sec_cess = 0.00;
                  $oth_iamt = $oth_cgst = $oth_sgst = $oth_cess = 0.00;
                  $ineligible_16 = AccountTaxManager::getAllInEligbleITC($start_date, $end_date, $id, 6, $chkid_array);
                  $ineligible_pos = AccountTaxManager::getAllInEligbleITC($start_date, $end_date, $id, 7, $chkid_array);
                  if ($reclaim_itc) {
                        //                    $total_ineligible_taxable_value += $reclaim_itc->taxable_value;
                        //                    $total_ineligible_taxable_igst += $reclaim_itc->igst;
                        //                    $total_ineligible_taxable_cgst += $reclaim_itc->cgst;
                        //                    $total_ineligible_taxable_sgst += $reclaim_itc->sgst;
                        //                    $total_ineligible_taxable_tax += $reclaim_itc->tax_amount;
                        $sec_iamt = round($reclaim_itc->igst, 2);
                        $sec_cgst = round($reclaim_itc->cgst, 2);
                        $sec_sgst = round($reclaim_itc->sgst, 2);
                        $sec_cess = 0;
                  }
                  if ($ineligible_16) {
                        if ($ineligible_pos) {
                              $ineligible_16->cgst += $ineligible_pos->cgst;
                              $ineligible_16->sgst += $ineligible_pos->sgst;
                              $ineligible_16->igst += $ineligible_pos->igst;
                              $ineligible_16->tax_amount += $ineligible_pos->tax_amount;
                              $ineligible_16->taxable_value += $ineligible_pos->taxable_value;
                              $ineligible_16->cess_amount += $ineligible_pos->cess_amount;
                        }
                        $oth_iamt = round($ineligible_16->igst, 2);
                        $oth_cgst = round($ineligible_16->cgst, 2);
                        $oth_sgst = round($ineligible_16->sgst, 2);
                        $oth_cess = round($ineligible_16->cess_amount, 2);
                  }
                  //                if ($ineligible_sec)
                  //                {
                  //                    $sec_iamt = round($ineligible_sec->igst, 2);
                  //                    $sec_cgst = round($ineligible_sec->cgst, 2);
                  //                    $sec_sgst = round($ineligible_sec->sgst, 2);
                  //                    $sec_cess = round($ineligible_sec->cess_amount, 2);
                  //                }
                  //                if ($ineligible_others)
                  //                {
                  //                    $oth_iamt = round($ineligible_others->igst, 2);
                  //                    $oth_cgst = round($ineligible_others->cgst, 2);
                  //                    $oth_sgst = round($ineligible_others->sgst, 2);
                  //                    $oth_cess = round($ineligible_others->cess_amount, 2);
                  //                }
                  $itc_elg['itc_inelg'][0] = array("ty" => "RUL", "iamt" => $sec_iamt, "camt" => $sec_cgst, "samt" => $sec_sgst, "csamt" => $sec_cess);
                  $itc_elg['itc_inelg'][1] = array("ty" => "OTH", "iamt" => $oth_iamt, "camt" => $oth_cgst, "samt" => $oth_sgst, "csamt" => $oth_cess);
                  $inward_sup = array();
                  $inward_sup['isup_details'] = array();
                  $inward_sup['isup_details'][0] = array("ty" => "GST", "inter" => 0.00, "intra" => 0.00);

                  if ($inward_nil_rated) {
                        $inward_sup['isup_details'][0]['inter'] = round($inward_nil_rated->igst, 2);
                        $inward_sup['isup_details'][0]['intra'] = round($inward_nil_rated->cgst, 2) + round($inward_nil_rated->sgst, 2);
                  }


                  $inward_sup['isup_details'][1] = array("ty" => "NONGST", "inter" => 0.00, "intra" => 0.00);

                  if ($inward_non_gst) {
                        $inward_sup['isup_details'][1]['inter'] = round($inward_non_gst->igst, 2);
                        $inward_sup['isup_details'][1]['intra'] = round($inward_non_gst->cgst, 2) + round($inward_non_gst->sgst, 2);
                  }

                  $intr_ltfee = array();
                  $intr_ltfee['intr_details'] = array("iamt" => 0.00, "camt" => 0.00, "samt" => 0.00, "csamt" => 0.00);
                  $intr_ltfee['ltfee_details'] = new stdClass();

                  $inter_sup = array();
                  $inter_sup['unreg_details'] = array();

                  if ($unregistred_supplies) {
                        foreach ($unregistred_supplies as $key => $unregistred_supply) {
                              if ($unregistred_supply['taxable_value'] > 0) {
                                    $inter_sup['unreg_details'][] = array("pos" => (string) ($key), "txval" => round($unregistred_supply['taxable_value'], 2), 'iamt' => round($unregistred_supply['igst'], 2));
                              }
                        }
                  } else {
                        $inter_sup['unreg_details'][] = array("pos" => 0.00, "txval" => 0.00, 'iamt' => 0.00);
                  }


                  $inter_sup['comp_details'] = array();
                  $inter_sup['uin_details'] = array();

                  $json_array = array();
                  $json_array['gstin'] = Utility::variableGet("gstin");
                  $json_array['ret_period'] = $ret_period;
                  $json_array['sup_details'] = $sup_details;
                  $json_array['itc_elg'] = $itc_elg;
                  $json_array['inward_sup'] = $inward_sup;
                  $json_array['intr_ltfee'] = $intr_ltfee;
                  $json_array['inter_sup'] = $inter_sup;

                  $data = json_encode($json_array);
                  $month = date('M', strtotime($start_date));
                  $financial_year = AccountReportManager::getFinancialYear("april", $start_date);
                  $financial_start_year = date('Y', strtotime($financial_year[0]));
                  $financial_end_year = date('y', strtotime($financial_year[1]));

                  $file_name = "GSTR-3B_" . Utility::variableGet("gstin") . "_" . $month . '_' . $financial_start_year . "-" . $financial_end_year;
                  header('Content-Disposition: attachment; filename=' . $file_name . "." . "json");
                  header('Content-Type: application/json');
                  echo $data;
                  exit;
                  break;

            case "gstr1":

                  //common json data//
                  $hsn_bit = 0;
                  if (isset($_GET['hsn'])) {
                        $hsn_bit = 1;
                  }
                  $json_data = GstManager::getGSTR1JSON($id, $gid, $start_date, $end_date, $chkid_array, $hsn_bit);
                  $data = $json_data['data'];
                  $hsnTeaxt = $json_data['hsnTeaxt'];
                  $month = date('M', strtotime($start_date));
                  $financial_year = AccountReportManager::getFinancialYear("april", $start_date);
                  $financial_start_year = date('Y', strtotime($financial_year[0]));
                  $financial_end_year = date('y', strtotime($financial_year[1]));
                  //                $gstin = Utility::variableGet("gstin");
                  $file_name = "GSTR-1" . $json_data['gstin'] . "_" . $month . '_' . $financial_start_year . "-" . $financial_end_year . '_' . $hsnTeaxt;
                  header('Content-Disposition: attachment; filename=' . $file_name . "." . "json");
                  header('Content-Type: application/json');
                  echo $data;
                  exit;
                  break;

            case "gstr2":


                  //common json data//

                  $json_array = array();
                  $json_array['gstin'] = Utility::variableGet("gstin");
                  $json_array['fp'] = date("mY");

                  ///////// GSTR1 B2B////////

                  $hsnTeaxt = "";
                  $b2b = array();
                  ///b2b starts here///
                  PurchaseInvoiceManager::createPurchaseInvoiceGstView($start_date, $end_date);
                  PurchaseOrderDebitNoteManager::createDebitNoteGstView($start_date, $end_date);

                  $getB2BInvoiceDetaails = AccountTaxManager::getListAsPerGst($start_date, $end_date, $id, 'gstr2b2b'); //registred vendors purchase invoice details 

                  ini_set('memory_limit', '-1');

                  if (!empty($getB2BInvoiceDetaails)) {
                        $i = 0;
                        $key = 0;

                        foreach ($getB2BInvoiceDetaails as $key => $getB2BInvoiceDetaail) {

                              $b2b[$i]['ctin'] = $getB2BInvoiceDetaails[$key]->ctin;
                              $b2b[$i]['inv'] = array();
                              $getInvoiceDetails = AccountTaxManager::getB2BGSTR2JSON($start_date, $end_date, $id, $getB2BInvoiceDetaails[$key]->ctin, FALSE);

                              if (!empty($getInvoiceDetails)) {

                                    foreach ($getInvoiceDetails as $key => $getInvoiceDetail) {

                                          $b2b[$i]['inv'][$key]['inum'] = $getInvoiceDetail->invoice_n . "(" . $getInvoiceDetail->pinvid . ")";
                                          $b2b[$i]['inv'][$key]['idt'] = $getInvoiceDetail->invoice_date;
                                          $b2b[$i]['inv'][$key]['val'] = $getInvoiceDetail->invoice_value;
                                          $b2b[$i]['inv'][$key]['pos'] = $getInvoiceDetail->pos;
                                          $b2b[$i]['inv'][$key]['rchrg'] = $getInvoiceDetail->reverse_charge;
                                          $b2b[$i]['inv'][$key]['inv_typ'] = $getInvoiceDetail->invoice_type;
                                          $b2b[$i]['inv'][$key]['cflag'] = "N"; ///donot know , 
                                          $b2b[$i]['inv'][$key]['updby'] = "R"; ///donot know , 
                                          $b2b[$i]['inv'][$key]['itms'] = array();
                                          $j = 0;
                                          $getInvoiceItemDetails = AccountTaxManager::getB2BGSTR2JSON($start_date, $end_date, $id, FALSE, $getInvoiceDetail->pinvid);

                                          foreach ($getInvoiceItemDetails as $getInvoiceItemDetail) {
                                                $b2b[$i]['inv'][$key]['itms'][$j]['num'] = PurchaseInvoiceItemManager::countClubbedPurchaseInvoiceItemAsPerTax($getInvoiceDetail->pinvid, $getInvoiceItemDetail->Rate);
                                                $b2b[$i]['inv'][$key]['itms'][$j]['itm_det'] = array();
                                                $b2b[$i]['inv'][$key]['itms'][$j]['itm_det']['txval'] = round($getInvoiceItemDetail->tv, 2);
                                                $b2b[$i]['inv'][$key]['itms'][$j]['itm_det']['rt'] = $getInvoiceItemDetail->Rate;
                                                $b2b[$i]['inv'][$key]['itms'][$j]['itm_det']['iamt'] = round($getInvoiceItemDetail->igst, 2);
                                                $b2b[$i]['inv'][$key]['itms'][$j]['itm_det']['camt'] = round($getInvoiceItemDetail->cgst, 2);
                                                $b2b[$i]['inv'][$key]['itms'][$j]['itm_det']['samt'] = round($getInvoiceItemDetail->sgst, 2);
                                                $b2b[$i]['inv'][$key]['itms'][$j]['itm_det']['csamt'] = round($getInvoiceDetail->cess_paid, 2);
                                                ///itc//

                                                $b2b[$i]['inv'][$key]['itms'][$j]['itc'] = array();
                                                $b2b[$i]['inv'][$key]['itms'][$j]['itc']['elg'] = $getInvoiceItemDetail->elg; //donot know , doubts
                                                $b2b[$i]['inv'][$key]['itms'][$j]['itc']['tx_i'] = $getInvoiceItemDetail->Rate;
                                                $b2b[$i]['inv'][$key]['itms'][$j]['itc']['tx_c'] = round($getInvoiceItemDetail->igst, 2);
                                                $b2b[$i]['inv'][$key]['itms'][$j]['itc']['tx_s'] = round($getInvoiceItemDetail->cgst, 2);
                                                $b2b[$i]['inv'][$key]['itms'][$j]['itc']['tx_cs'] = round($getInvoiceDetail->available_itc_cess, 2);

                                                $j++;
                                          }
                                    }
                              }
                              $i++;
                        }
                  }
                  $json_array['b2b'] = $b2b;

                  //////b2b ends here ////////
                  //////////////IMPG starts here////////
                  $impg = array();
                  $key = 0;
                  PurchaseInvoiceImportManager::createPurchaseInvoiceImportGstView($start_date, $end_date);
                  $getImpgDetails = AccountTaxManager::getListAsPerGst($start_date, $end_date, $id, 'gstr2impg');
                  if (!empty($getImpgDetails)) {
                        foreach ($getImpgDetails as $key => $getImpgDetail) {
                              $impg[$key]['boe_num'] = $getImpgDetail->bill_of_entry;
                              $impg[$key]['boe_dt'] = $getImpgDetail->entry_date;
                              $impg[$key]['boe_val'] = $getImpgDetail->bill_of_entry_value;
                              $impg[$key]['is_sez'] = 'N';
                              $impg[$key]['port_code'] = $getImpgDetail->port_code;

                              $impg[$key]['itms'] = array();

                              // get items details//
                              $getImpgItemDetails = AccountTaxManager::getB2BIMPGJSON($start_date, $end_date, $id, $getImpgDetail->pinvimpid);
                              if (!empty($getImpgItemDetails)) {
                                    foreach ($getImpgItemDetails as $key1 => $getImpgItemDetail) {
                                          $impg[$key]['itms'][$key1]['txval'] = $getImpgItemDetail->taxable_value;
                                          $impg[$key]['itms'][$key1]['rt'] = $getImpgItemDetail->Rate;
                                          $impg[$key]['itms'][$key1]['iamt'] = $getImpgItemDetail->iamt;
                                          $impg[$key]['itms'][$key1]['csamt'] = 0;
                                          $impg[$key]['itms'][$key1]['elg'] = $getImpgItemDetail->elg; //
                                          $impg[$key]['itms'][$key1]['tx_i'] = $getImpgItemDetail->tax_i;
                                          $impg[$key]['itms'][$key1]['tx_cs'] = 0; //cess amount
                                          $impg[$key]['itms'][$key1]['num'] = $getImpgItemDetail->pinvimpid; //doubt
                                    }
                              }
                        }
                  }

                  $json_array['imp_g'] = $impg;

                  ////////////////IMPG ends here /////////////
                  ///////////////////////// Debit note starts here //////////

                  $cdn = array();

                  $getGstr2CdnDetails = AccountTaxManager::getListAsPerGst($start_date, $end_date, $id, 'gstr2cdnr');

                  if (!empty($getGstr2CdnDetails)) {
                        $i = 0;
                        $key = 0;

                        foreach ($getGstr2CdnDetails as $key => $getGstr2CdnDetail) {

                              $cdn[$i]['ctin'] = $getGstr2CdnDetail->gstin;
                              $cdn[$i]['nt'] = array();

                              $getInvoiceDetailsCdn = AccountTaxManager::getCDNGSTR2JSON($start_date, $end_date, $id, $getGstr2CdnDetail->gstin, FALSE);

                              if (!empty($getInvoiceDetailsCdn)) {

                                    foreach ($getInvoiceDetailsCdn as $key => $getInvoiceDetailCdn) {
                                          $cdn[$i]['nt'][$key]['nt_num'] = $getInvoiceDetailCdn->note_number;
                                          $cdn[$i]['nt'][$key]['nt_dt'] = $getInvoiceDetailCdn->nt_date;
                                          $cdn[$i]['nt'][$key]['ntty'] = "C"; //$getInvoiceDetailCdn->document_type
                                          $cdn[$i]['nt'][$key]['val'] = $getInvoiceDetailCdn->val;
                                          $cdn[$i]['nt'][$key]['inum'] = $getInvoiceDetailCdn->purchase_invoice_n;
                                          $cdn[$i]['nt'][$key]['idt'] = $getInvoiceDetailCdn->idt;
                                          $cdn[$i]['nt'][$key]['rsn'] = $getInvoiceDetailCdn->rsn;

                                          $cdn[$i]['nt'][$key]['p_gst'] = $getInvoiceDetailCdn->pre_gst;
                                          $cdn[$i]['nt'][$key]['cflag'] = "N"; //doubt
                                          $cdn[$i]['nt'][$key]['updby'] = "R"; //doubt




                                          $cdn[$i]['nt'][$key]['itms'] = array();

                                          $getNoteItemDetails = AccountTaxManager::getCDNGSTR2JSON($start_date, $end_date, $id, FALSE, $getInvoiceDetailCdn->purordebnoteid);
                                          $j = 0;

                                          foreach ($getNoteItemDetails as $getNoteItemDetail) {
                                                $cdn[$i]['nt'][$key]['itms'][$j]['num'] = PurchaseOrderDebitNoteManager::countClubbedDebitNoteItemAsPerTax($getNoteItemDetail->purordebnoteid, $getNoteItemDetail->Rate);
                                                $cdn[$i]['nt'][$key]['itms'][$j]['itm_det'] = array();
                                                $cdn[$i]['nt'][$key]['itms'][$j]['itm_det']['txval'] = round($getNoteItemDetail->tv, 2);
                                                $cdn[$i]['nt'][$key]['itms'][$j]['itm_det']['rt'] = $getNoteItemDetail->Rate;

                                                $cdn[$i]['nt'][$key]['itms'][$j]['itm_det']['iamt'] = round($getNoteItemDetail->igst, 2);
                                                $cdn[$i]['nt'][$key]['itms'][$j]['itm_det']['camt'] = round($getNoteItemDetail->cgst, 2);
                                                $cdn[$i]['nt'][$key]['itms'][$j]['itm_det']['samt'] = round($getNoteItemDetail->sgst, 2);
                                                $cdn[$i]['nt'][$key]['itms'][$j]['itm_det']['csamt'] = round($getNoteItemDetail->cess_paid, 2);
                                                //////itc//

                                                $cdn[$i]['nt'][$key]['itms'][$j]['itc'] = array();
                                                $cdn[$i]['nt'][$key]['itms'][$j]['itc']['elg'] = $getNoteItemDetail->elg; //donot know , doubts
                                                $cdn[$i]['nt'][$key]['itms'][$j]['itc']['tx_i'] = round($getNoteItemDetail->tx_i, 2);
                                                $cdn[$i]['nt'][$key]['itms'][$j]['itc']['tx_c'] = round($getNoteItemDetail->tx_c, 2);
                                                $cdn[$i]['nt'][$key]['itms'][$j]['itc']['tx_s'] = round($getNoteItemDetail->tx_s, 2);
                                                $cdn[$i]['nt'][$key]['itms'][$j]['itc']['tx_cs'] = round($getNoteItemDetail->available_itc_cess, 2);

                                                $j++;
                                          }
                                    }
                              }

                              $i++;
                        }
                  }


                  $json_array['cdn'] = $cdn;

                  ////////////////////////Debit note ends here /////////////
                  ///////////NiL SUPPLIERS  GSTR1 STARTS HERE it contains only intra and inter no registration /////////


                  $nil_supplies = array();
                  PurchaseInvoiceItemManager::createPurchaseInvoiceItemView($start_date, $end_date);
                  PurchaseOrderDebitNoteManager::debitNoteItemView($start_date, $end_date);

                  $getGstr2NilDetails = AccountTaxManager::getListAsPerGst($start_date, $end_date, $id, 'gstr2nil_supplies'); //for purchaseinvoice only 
                  $getGstr2NilDnDetails = AccountTaxManager::getListAsPerGst($start_date, $end_date, $id, 'gstr2nil_suppliesDn'); //for debit note only        

                  if (!empty($getGstr2NilDetails) && !empty($getGstr2NilDnDetails) && count($getGstr2NilDetails) >= count($getGstr2NilDnDetails)) {

                        foreach ($getGstr2NilDetails as $key => $getGstr2NilDetail) {

                              $nil_supplies[$getGstr2NilDetail->sply_ty]['cpddr'] = 0; // donot know doubt//
                              $nil_supplies[$getGstr2NilDetail->sply_ty]['exptdsply'] = $getGstr2NilDetail->expt_amt;
                              $nil_supplies[$getGstr2NilDetail->sply_ty]['ngsply'] = $getGstr2NilDetail->ngsup_amt;
                              $nil_supplies[$getGstr2NilDetail->sply_ty]['nilsply'] = $getGstr2NilDetail->nil_amt;

                              if (isset($getGstr2NilDnDetails[$key]) && isset($getGstr2NilDetails[$key]) && $getGstr2NilDetail->sply_ty == $getGstr2NilDnDetails[$key]->sply_ty) {
                                    $nil_supplies[$getGstr2NilDetail->sply_ty]['exptdsply'] = $getGstr2NilDetail->expt_amt + $getGstr2NilDnDetails[$key]->expt_amt;
                              }


                              if (isset($getGstr2NilDnDetails[$key]) && isset($getGstr2NilDetails[$key]) && $getGstr2NilDetail->sply_ty == $getGstr2NilDnDetails[$key]->sply_ty) {
                                    $nil_supplies[$getGstr2NilDetail->sply_ty]['nilsply'] = $getGstr2NilDetail->nil_amt + $getGstr2NilDnDetails[$key]->nil_amt;
                              }

                              if (isset($getGstr2NilDnDetails[$key]) && isset($getGstr2NilDetails[$key]) && $getGstr2NilDetail->sply_ty == $getGstr2NilDnDetails[$key]->sply_ty) {
                                    $nil_supplies[$getGstr2NilDetail->sply_ty]['ngsply'] = $getGstr2NilDetail->ngsup_amt - $getGstr2NilDnDetails[$key]->ngsup_amt;
                              }
                        }
                  }
                  //if invoice array count is less than cn array count 
                  if (!empty($getGstr2NilDetails) && !empty($getGstr2NilDnDetails) && count($getGstr2NilDetails) < count($getGstr2NilDnDetails)) {

                        foreach ($getGstr2NilDnDetails as $key => $getGstr2NilDnDetail) {

                              $nil_supplies[$getGstr2NilDnDetail->sply_ty]['cpddr'] = 0; // donot know doubt//
                              $nil_supplies[$getGstr2NilDnDetail->sply_ty]['exptdsply'] = $getGstr2NilDnDetail->expt_amt;
                              $nil_supplies[$getGstr2NilDnDetail->sply_ty]['ngsply'] = $getGstr2NilDnDetail->ngsup_amt;
                              $nil_supplies[$getGstr2NilDnDetail->sply_ty]['nilsply'] = $getGstr2NilDnDetail->nil_amt;

                              if (isset($getGstr2NilDnDetails[$key]) && isset($getGstr2NilDetails[$key]) && $getGstr2NilDnDetail->sply_ty == $getGstr2NilDetails[$key]->sply_ty) {
                                    $nil_supplies[$getGstr2NilDnDetail->sply_ty]['exptdsply'] = $getGstr2NilDnDetail->expt_amt + $getGstr2NilDetails[$key]->expt_amt;
                              }


                              if (isset($getGstr2NilDnDetails[$key]) && isset($getGstr2NilDetails[$key]) && $getGstr2NilDnDetail->sply_ty == $getGstr2NilDetails[$key]->sply_ty) {
                                    $nil_supplies[$getGstr2NilDnDetail->sply_ty]['nilsply'] = $getGstr2NilDnDetail->nil_amt + $getGstr2NilDetails[$key]->nil_amt;
                              }

                              if (isset($getGstr2NilDnDetails[$key]) && isset($getGstr2NilDetails[$key]) && $getGstr2NilDnDetail->sply_ty == $getGstr2NilDetails[$key]->sply_ty) {
                                    $nil_supplies[$getGstr2NilDnDetail->sply_ty]['ngsply'] = $getGstr2NilDnDetail->ngsup_amt - $getGstr2NilDetails[$key]->ngsup_amt;
                              }
                        }
                  }


                  $json_array['nil_supplies'] = $nil_supplies;
                  /////////////NILRATED GSTR1 ENDS HERE ////////
                  ///////////////////////GSTR2 - HSN////////////////////////

                  if (!empty($_GET['hsn'])) {
                        $hsnTeaxt = "HSN";
                        $hsnsum = array();
                        $getGstr2HsnDetails = AccountTaxManager::getListAsPerGst($start_date, $end_date, $id, "hsn-gstr2");

                        $getGstr2HsnDetailsDebitNoteWise = AccountTaxManager::getListAsPerGst($start_date, $end_date, $id, "hsn-debit-note-wise");

                        if (!empty($getGstr2HsnDetails)) {

                              foreach ($getGstr2HsnDetails as $key => $getGstr2HsnDetail) {
                                    $hsnsum['det'][$key]['butapid'] = $getGstr2HsnDetail->butapid;
                                    // $hsnsum['det'][$key]['num'] = $getGstr2HsnDetail->HSN;
                                    $hsnsum['det'][$key]['desc'] = $getGstr2HsnDetail->Description;
                                    $hsnsum['det'][$key]['uqc'] = $getGstr2HsnDetail->UQC;
                                    ///second loop will start here ///

                                    $hsnsum['det'][$key]['qty'] = Round($getGstr2HsnDetail->total_quantity, 3);
                                    $hsnsum['det'][$key]['val'] = Round($getGstr2HsnDetail->taxable_value, 2);
                                    $hsnsum['det'][$key]['iamt'] = Round($getGstr2HsnDetail->igst, 2);
                                    $hsnsum['det'][$key]['camt'] = Round($getGstr2HsnDetail->cgst, 2);
                                    $hsnsum['det'][$key]['samt'] = Round($getGstr2HsnDetail->sgst, 2);
                                    $hsnsum['det'][$key]['csamt'] = round($getGstr2HsnDetail->cess_amount, 2);
                                    $hsnsum['det'][$key]['num'] = $getGstr2HsnDetail->HSN;
                                    foreach ($getGstr2HsnDetailsDebitNoteWise as $getGstr2HsnDetailDebitNoteWise) {
                                          if ($getGstr2HsnDetailDebitNoteWise->HSN == $getGstr2HsnDetail->HSN && $getGstr2HsnDetailDebitNoteWise->butapid == $getGstr2HsnDetail->butapid) {
                                                $hsnsum['det'][$key]['qty'] = Round(($getGstr2HsnDetail->total_quantity + $getGstr2HsnDetailDebitNoteWise->total_quantity), 3);
                                                $hsnsum['det'][$key]['val'] = Round(($getGstr2HsnDetail->taxable_value + $getGstr2HsnDetailDebitNoteWise->taxable_value), 2);
                                                $hsnsum['det'][$key]['iamt'] = Round(($getGstr2HsnDetail->igst + $getGstr2HsnDetailDebitNoteWise->igst), 2);
                                                $hsnsum['det'][$key]['camt'] = Round(($getGstr2HsnDetail->cgst + $getGstr2HsnDetailDebitNoteWise->cgst), 2);
                                                $hsnsum['det'][$key]['samt'] = Round(($getGstr2HsnDetail->sgst + $getGstr2HsnDetailDebitNoteWise->sgst), 2);
                                                $hsnsum['det'][$key]['csamt'] = Round(($getGstr2HsnDetail->cess_amount + $getGstr2HsnDetailDebitNoteWise->cess_amount), 2);
                                          }
                                    }
                                    /////end of loop///
                              }
                        }


                        $json_array['hsnsum'] = $hsnsum;
                  }

                  ///////////////////////////GSTR2 - HSN ENDS HERE//////////


                  $data = json_encode($json_array);
                  $month = date('M', strtotime($start_date));
                  $financial_year = AccountReportManager::getFinancialYear("april", $start_date);
                  $financial_start_year = date('Y', strtotime($financial_year[0]));
                  $financial_end_year = date('y', strtotime($financial_year[1]));

                  $file_name = "GSTR-2" . Utility::variableGet("gstin") . "_" . $month . '_' . $financial_start_year . "-" . $financial_end_year . '_' . $hsnTeaxt;
                  header('Content-Disposition: attachment; filename=' . $file_name . "." . "json");
                  header('Content-Type: application/json');
                  echo $data;
                  exit;
                  break;

                  break;
            default:
                  exit();
                  break;
      }
      exit;
}

function generate_xml()
{
      global $url;
      if (!isset($url[1])) {
            exit();
      }
      $starttime = 0;
      $endtime = 0;
      if (isset($url[2]) && $url[2] != '') {
            $starttime = date('Y-m-d 00:00:00', strtotime($url[2]));
      }
      if (isset($url[3]) && $url[3] != '') {
            $endtime = date('Y-m-d 23:59:59', strtotime($url[3]));
      }

      switch ($url[1]) {
            case 'inter_state_purchase':
                  PurchaseInvoiceManager::generateInterStatePurchaseXml($starttime, $endtime);
                  break;
            case 'purchase':
                  PurchaseInvoiceManager::generatePurchaseXml($starttime, $endtime);
                  break;
            case 'sale':
                  InvoiceManager::generateSaleXml($starttime, $endtime);
                  break;
            case "inter_state_sale":
                  InvoiceManager::generateInterstateSaleXml($starttime, $endtime);
                  break;
            case "credit_note":
                  CheckPointOrderCreditNoteManager::generateCreditNoteXml($starttime, $endtime);
                  break;
            case "inter_state_credit_note":
                  CheckPointOrderCreditNoteManager::generateInterstateCreditNoteXml($starttime, $endtime);
                  break;
            case "debit_note":
                  PurchaseOrderDebitNoteManager::generateDebitNoteXml($starttime, $endtime);
                  break;
            case "inter_state_debit_note":
                  PurchaseOrderDebitNoteManager::generateInterstateDebitNoteXml($starttime, $endtime);
                  break;
            default:
                  exit();
                  break;
      }



      exit;
}

function navbar_search($data)
{
      ini_set('display_errors', 0);
      $resp = new AjaxResponse(true);
      $tpl = new Template(SystemConfig::templatesPath() . "search/views/navbar-search-result");
      $tpl->is_result = false;
      $text = trim($data['text']);
      $result = false;
      //Search variation with name
      $search_variation_found = stripos($text, "ISV:");
      if ($search_variation_found !== FALSE) {
            //            $replace_code = str_replace(Utility::variableGet('site_code'), "", strtoupper($text));
            $replace = str_replace("ISV:", "", strtoupper($text));
            if ($replace != "") {
                  $data = array();
                  $data['name'] = $replace;
                  $data['search'] = $replace;
                  $variations = InventorySetVariationManager::search_item_name_variations($data);
                  $variation_search_name_result_tpl = new Template(SystemConfig::templatesPath() . "search/views/navbar-variation-search-name-result");
                  $variation_search_name_result_tpl->variations = $variations;
                  $tpl->variation_name_show = $variation_search_name_result_tpl->parse();
                  $result = TRUE;
            }
      }
      $search_batch_found = stripos($text, "BATCH:");
      if ($search_batch_found !== FALSE) {
            //            $replace_code = str_replace(Utility::variableGet('site_code'), "", strtoupper($text));
            $replace = str_replace("BATCH:", "", strtoupper($text));
            if ($replace != "") {
                  $data = array();
                  $data['name'] = $replace;
                  $data['search'] = $replace;
                  $inventories = InventoryManager::searchWithBatch($data);
                  $inventory_set_search_name_result_tpl = new Template(SystemConfig::templatesPath() . "search/views/navbar-inventory-search-name-result");
                  $inventory_set_search_name_result_tpl->inventories = $inventories;
                  $tpl->inventory_set_name_show = $inventory_set_search_name_result_tpl->parse();
                  $result = TRUE;
            }
      }

      $search_group_found = stripos($text, "GROUP:");
      if ($search_group_found !== FALSE) {
            $replace = str_replace("GROUP:", "", strtoupper($text));
            if ($replace != "") {
                  $data = array();
                  $data['name'] = $replace;
                  $data['search'] = $replace;
                  $inventories = InventoryManager::searchWithBatch($data, 1);
                  $inventory_set_search_name_result_tpl = new Template(SystemConfig::templatesPath() . "search/views/navbar-inventory-search-name-group-result");
                  $inventory_set_search_name_result_tpl->inventories = $inventories;
                  $tpl->inventory_set_name_show = $inventory_set_search_name_result_tpl->parse();
                  $result = TRUE;
            }
      }

      //Invoice Search
      $search_invoice_found = stripos($text, "INV:");
      if ($search_invoice_found !== FALSE) {
            $replace_code = strtoupper($text);
            $replace = str_replace("INV:", "", strtoupper($replace_code));
            $replace = trim($replace);
            if ($replace != "") {
                  $data = array();
                  $data['name'] = $replace;
                  $data['search'] = $replace;
                  $invoice = InvoiceManager::searchInvoiceNumber($data);
                  if ($invoice) {
                        require_once 'api/Image/QrCode/QrBarCode.php';
                        require_once('api/Image/BarcodeGenerator/barcode/autoload.php');
                        $tpl->invoice_show = InvoiceManager::getInvoicePrintTpl($invoice);
                        $result = true;
                  }
            }
      }
      //Barcode Search
      $search_barcode_found = stripos($text, "B:");
      if ($search_barcode_found !== FALSE) {
            $replace_code = strtoupper($text);
            $replace = str_replace("B:", "", strtoupper($replace_code));
            $replace = trim($replace);
            if ($replace != "") {
                  $data = array();
                  $data['name'] = $replace;
                  $data['search'] = $replace;
                  $barcode = InventoryManager::searchBarcode($data);
                  if ($barcode) {
                        $barcode_tpl = new Template(SystemConfig::templatesPath() . "search/views/navbar-barcode-search-result");
                        $barcode_tpl->barcodes = $barcode;
                        $tpl->barcode_show = $barcode_tpl->parse();
                        $result = true;
                  }
            }
      }

      if (!$result) {
            //module search

            $links = ModuleManager::searchModule($text);
            $tpl->links = $links;
            //search customer
            if (hasPermission(USER_PERMISSION_CUSTOMER_VIEW)) {
                  $search_customer = str_replace(array(',', '.'), array(' ', ''), $text);
                  $limit = 8;
                  if (SystemConfig::getUser()->getLicid() == 21 || SystemConfig::getUser()->getLicid() == 30 || SystemConfig::getUser()->getLicid() == 65 || SystemConfig::getUser()->getLicid() == 50 || SystemConfig::getUser()->getLicid() == 23 || SystemConfig::getUser()->getLicid() == 71 || SystemConfig::getUser()->getLicid() == 84 || SystemConfig::getUser()->getLicid() == 92) {
                        $limit = 16;
                  }
                  if (SystemConfig::getUser()->getLicid() == 149 || SystemConfig::getUser()->getLicid() == 83) {
                        $limit = 100;
                  }
                  $inactive_customer_search = false;
                  if (getSettings('IN_ACTIVE_CUSTOMER_SEARCH_IN_GLOBAL_SEARCH')) {
                        $inactive_customer_search = true;
                  }
                  $customer_limit = getSettings("IS_GLOBAL_CUSTOMER_SEARCH_VALUE") > 0 ? getSettings("IS_GLOBAL_CUSTOMER_SEARCH_VALUE") : $limit;
                  $attendee = getSettings('SHOW_ONLY_ATTENDEE_CUSTOMERS_IN_GLOBAL_SEARCH') && SystemConfig::getUser()->getIsAdmin() != 1 ? SystemConfig::getUser()->getUid() : NULL;
                  $attend_condition = NULL;
                  if (isset($attendee) && $attendee > 0) {
                        $attend_condition .= " AND c.cuid IN (select " . SystemTables::DB_TBL_CUSTOMER_USER_MAPPING . ".cuid from " . SystemTables::DB_TBL_CUSTOMER_USER_MAPPING . " where uid in ($attendee) AND company_id = " . BaseConfig::$company_id . " )";
                  }
                  $customers = CustomerManager::searchCustomers($search_customer, $customer_limit, $where = $attend_condition, $address = FALSE, $balance = FALSE, $uid = FALSE, $linked_bit = false, $to_approved_customers = false, $chkid = NULL, $avoid_branch = FALSE, $inactive_customer_search, false, false, 1, $attendee);
                  $tpl->customers = $customers;
            } else {
                  $tpl->customers = array();
            }

            //search vendors
            if (hasPermission(USER_PERMISSION_VENDOR_VIEW)) {
                  $limit = 8;
                  if (SystemConfig::getUser()->getLicid() == 21 || SystemConfig::getUser()->getLicid() == 30 || SystemConfig::getUser()->getLicid() == 65 || SystemConfig::getUser()->getLicid() == 71) {
                        $limit = 16;
                  }
                  if (SystemConfig::getUser()->getLicid() == 149) {
                        $limit = 100;
                  }
                  $vendor_limit = getSettings("IS_GLOBAL_VENDOR_SEARCH_VALUE") > 0 ? getSettings("IS_GLOBAL_VENDOR_SEARCH_VALUE") : $limit;
                  $vendors = VendorManager::searchVendors($text, $vendor_limit);
                  $tpl->vendors = $vendors;
            } else {
                  $tpl->vendors = array();
            }
            if (hasPermission(USER_PERMISSION_ACCOUNT_LEDGER_VIEW)) {
                  $tds_limit = getSettings("IS_GLOBAL_TDS_SEARCH_VALUE") > 0 ? getSettings("IS_GLOBAL_TDS_SEARCH_VALUE") : 7;
                  $ledgers = AccountLedgerManager::searchGlobalAccounts($text, NULL, NULL, NULL, $tds_limit);
                  $tpl->ledgers = $ledgers;
            } else {
                  $tpl->ledgers = array();
            }
      }
      $data = array();
      $data['html'] = utf8_encode($tpl->parse());
      $data['search'] = $text;
      $resp->setData($data);
      return $resp->getOutput();
}

function load_home($masking, $masking_status)
{
      Utility::setHeading("My Dashboard", "Hey! " . SystemConfig::getUser()->getName());
      if (getSettings("HEADER_BUTTONS")) {
            $header_button = array(array("link" => Jpath::fullUrl("enquiry/add"), "color" => 'text-primary', "icon" => 'fa-info', "text" => 'Create Enquiry'), array("link" => Jpath::fullUrl("quotation/add/178"), "color" => 'text-success', "icon" => 'fa-quote-left', "text" => 'Create Quotation'), array("link" => Jpath::fullUrl("pos/create"), "color" => 'text-danger', "icon" => 'fa-fax', "text" => 'Create Invoice'), array("link" => Jpath::fullUrl("my-warehouse/view/7"), "color" => 'text-warning', "icon" => 'fa-industry', "text" => 'Cottonpet'), array("link" => Jpath::fullUrl("my-warehouse/view/8"), "color" => 'text-black', "icon" => 'fa-industry', "text" => 'Mysore Road'));
            Utility::setHeaderButtons($header_button);
      }
      $counter = 0;
      $tpl = new Template(SystemConfig::templatesPath() . "home/dashboard");
      $tpl->masking = $masking;
      $tpl->masking_status = $masking_status;

      //        if (!(!in_array("order", $masking, TRUE) && $masking_status) && $counter < 4)
      //        {
      //            $orders = OrderManager::getNewOrders();
      //            $tpl->orders = ($orders) ? $orders->count : 0;
      //            $counter++;
      //        }
      //
      //        if (!(!in_array("seller", $masking, TRUE) && $masking_status) && $counter < 4)
      //        {
      //            $sellers = SellerManager::getSellerCount();
      //            $tpl->sellers = ($sellers) ? $sellers : 0;
      //            $counter++;
      //        }
      //
      //        if (!(!in_array("store", $masking, TRUE) && $masking_status) && $counter < 4)
      //        {
      //            $stores = SellerStoreManager::getStoreCount();
      //            $tpl->stores = ($stores) ? $stores : 0;
      //            $counter++;
      //        }
      //
      //        if (!(!in_array("call", $masking, TRUE) && $masking_status) && $counter < 4)
      //        {
      //            $call_count = CallLogManager::getRoutedCallCount();
      //            $tpl->call_count = $call_count;
      //            $counter++;
      //        }


      if (!(!in_array("sms", $masking, TRUE) && $masking_status) && $counter < 4) {
            $sms = SmsLogManager::getSmsCount(2);
            $tpl->sms = $sms ? $sms : 0;
            $sent_sms = SmsLogManager::getSmsCount(1);
            $tpl->sent_sms = ($sent_sms) ? $sent_sms : 0;
            $counter++;
      }

      if (!(!in_array("distributor", $masking, TRUE) && $masking_status)) {
            $distid = DistributorManager::getUserDistributor();
            if ($distid) {
                  $tpl->blocked_inventory = get_block_inventory_table($distid);
            }
      }

      $counters = DashboardManager::getUserSelection(Session::loggedInUid(), 2);
      $html = null;
      if (!empty($counters) && valid($counters)) {
            foreach ($counters as $counter) {
                  $html = call_user_func($counter->callback, $masking, $masking_status);
            }
            if ($html) {
                  $tpl->counterHTML = $html->publish();
            }
      }

      $graphs = DashboardManager::getUserSelection(Session::loggedInUid(), 1);

      if (!empty($graphs) && valid($graphs)) {
            foreach ($graphs as $graph) {
                  //                if (!(!in_array(strtolower($graph->name), $masking, true) && $masking_status))
                  {

                        call_user_func($graph->callback, $masking, $masking_status);
                  }
            }
      }
      if (!getSettings("HEADER_BUTTONS")) {
            $links = DashboardManager::getUserModuleTaskLink(Session::loggedInUid());
            $linkarray = array();
            if (!empty($links) && valid($links)) {
                  foreach ($links as $link) {
                        $module = new Module($link->mid);
                        $url = $module->getUrl();
                        $d = explode("/", $url);
                        if (is_array($d) && !empty($d)) {
                              //                        if ($url !== "" && (!(!in_array(strtolower($d[0]), $masking, true) && $masking_status)))
                              if ($url != '') {
                                    $linkarray[] = array("link" => JPath::fullUrl($link->link), "color" => $link->color, "icon" => $link->icon, "text" => $link->name);
                              }
                        }
                  }
            }
            Utility::setHeaderButtons($linkarray);
      }
      $tpl->graphs = DashboardManager::$graphs;
      $tables = DashboardManager::getUserSelection(Session::loggedInUid(), 4);
      $tables_array = array();
      if (is_array($tables) && !empty($tables)) {
            foreach ($tables as $table) {
                  if (!(!in_array(strtolower($table->name), $masking, true) && $masking_status)) {
                        $tables_array[] = $table;
                  }
            }
      }
      $tpl->tables = $tables_array;
      $tpl->department_enquiry_table = get_enquiry_department_wise_table();
      return $tpl->parse();
}

function get_enquiry_department_wise_table()
{
      $table = new DataTable();
      $table->setTableId('group-enquiry-table');
      $default = array("name", "active_enquiries", "on_hold_enquiries", "converted_enquiries", "cancelled_enquiries");
      $columns = array(
            "name" => array("name" => "name", "title" => "Department Name", "description" => "Department Name"),
            "pending_enquiries" => array("name" => "pending_enquiries", "title" => "Pending Enquiries", "description" => "Pending Enquiries"),
            "converted_enquiries" => array("name" => "converted_enquiries", "title" => "Converted Enquiries", "description" => "Converted Enquiries"),
            "cancelled_enquiries" => array("name" => "cancelled_enquiries", "title" => "Canceled Enquiries", "description" => "Canceled Enquiries"),
      );
      $table->setDefaultColumns($default);
      $table->setColumns($columns);
      $table->setIfDetails(FALSE);
      $table->setIfTask(FALSE);
      $table->setIfSerial(TRUE);
      $table->setIfRealtime(true);
      $table->setRealtimeUrl(Jpath::fullUrl("group_wise_enquiry_table_render"));
      $table->setIfExportable(true);
      $table->setIfHeader(true);
      $table->setIfFooter(false);
      $table->setIfAction(FALSE);
      $table->setExtra(array("department_id" => "sgid"));
      return $table;
}

function load_404()
{
      $tpl = new Template(SystemConfig::templatesPath() . "404");
      return $tpl->parse();
}

function load_403()
{
      $tpl = new Template(SystemConfig::templatesPath() . "403");
      return $tpl->parse();
}

function help_dashboard()
{
      $resp = new AjaxResponse(true);
      $tpl = new Template(SystemConfig::templatesPath() . "home/help/tour");
      $resp->setData($tpl->parse());
      echo $resp->getOutput();
      exit();
}

function get_block_inventory_table($id)
{
      $table = new DataTable();
      $table->setTableId('block-inventory-list-table');
      $table->setTableClass('display');
      $table->setDefaultColumns(
            array(
                  array("name" => "item_name", "title" => "Item Name", "description" => "Name of the Item"),
                  array("name" => "variation_name", "title" => "Variation Name", "description" => "Name of the Item Variation"),
                  array("name" => "qty", "title" => "Blocked Stock", "description" => "Blocked quantity of the item"),
                  array("name" => "expiry_date", "title" => "Expiry Date", "description" => "Blocked Expiry Date"),
                  array("name" => "status_name", "title" => "Status", "description" => "Status Of Blocked Item"),
                  array("name" => "created_ts", "title" => "Blocked On", "description" => "Blocked Date"),
            )
      );
      $table->setColumns(
            array(
                  array("name" => "item_name", "title" => "Item Name", "description" => "Name of the Item"),
                  array("name" => "variation_name", "title" => "Variation Name", "description" => "Name of the Item Variation"),
                  array("name" => "qty", "title" => "Blocked Stock", "description" => "Blocked quantity of the item", "callback" => "DistributorManager::getDistributorBlockedStock"),
                  array("name" => "expiry_date", "title" => "Expiry Date", "description" => "Blocked Expiry Date", "callback" => "Utility::getDateFormat"),
                  array("name" => "status_name", "title" => "Status", "description" => "Status Of Blocked Item"),
                  array("name" => "created_ts", "title" => "Blocked On", "description" => "Blocked Date", "callback" => "Utility::getTimeFormat"),
            )
      );
      $table->setIfDetails(FALSE);
      $table->setIfTask(FALSE);
      $table->setIfSerial(TRUE);
      $table->setIfRealtime(true);
      $table->setIfExportable(true);
      $table->setRealtimeUrl(Jpath::fullUrl("home/$id/distributor-block-inventory-list-render-page"));
      $table->setIfHeader(true);
      $table->setIfFooter(false);
      $table->setIfAction(FALSE);
      $table->setOnDrawCallback("distributorExpiryDate");

      /* This is a random comment to change this file */

      $table->setExtra(array('meaid' => 'meaid'));
      return $table;
}

function resetTableSettings()
{
      $db = Rapidkart::getInstance()->getDB();
      $factory_static = 1;
      if (isset($_GET['status']) && $_GET['status'] > 0) {
            $factory_static = 0;
      }
      //        $sql = "SELECT CONCAT('`',TABLE_SCHEMA,'`.`',TABLE_NAME,'`') as TABLE_NAME FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_TYPE = 'BASE TABLE' AND (TABLE_SCHEMA='rapidkart_factory' ) AND (TABLE_NAME LIKE '%status%' OR TABLE_NAME LIKE '%type%'  OR TABLE_NAME = 'gender' OR TABLE_NAME='salutation'   OR TABLE_NAME IN (    'package_delivery_gatepass_package_transport' , 'email_template_placeholder' , 'email_template_type', 'email_template_type_placeholder_mapping' , 'sms_template_placeholder' , 'sms_template_type'  , 'sms_template_type_placeholder_mapping' , 'business_tax_profile_round' , 'measurement' , 'unit_quantity_code' , 'customer_payment_option' , 'customer_payment_option_attribute'))";
      if ($factory_static) {
            $sql = "SELECT CONCAT('`',TABLE_SCHEMA,'`.`',TABLE_NAME,'`') as TABLE_NAME FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_TYPE = 'BASE TABLE' AND ( TABLE_SCHEMA='rapidkart_factory_static') AND (  TABLE_NAME = 'apermission' OR TABLE_NAME = 'module'  OR TABLE_NAME IN ( 'module_task'))";

            $res = $db->query($sql);
            if (!$res) {
                  return FALSE;
            }
            $ret = array();
            while ($row = $db->fetchObject($res)) {
                  if ($row->TABLE_NAME !== "" . BaseConfig::DB_NAME . ".`static_page`" && $row->TABLE_NAME !== "" . BaseConfig::DB_NAME . ".`static_page_category`") {
                        $ret[] = $row->TABLE_NAME;
                  }
            }
            foreach ($ret as $table) {
                  $sql1 = "SHOW COLUMNS FROM " . $table;
                  $res1 = $db->query($sql1);
                  if (!$res1) {
                        continue;
                  }
                  $ret1 = array();
                  $pri = null;
                  while ($row1 = $db->fetchObject($res1)) {
                        $ret1[] = $row1->Field;
                        if ($row1->Key === 'PRI') {
                              $pri = $row1->Field;
                        }
                  }
                  $sql2 = "SELECT * FROM " . $table;
                  $res2 = $db->query($sql2);
                  if (!$res2) {
                        continue;
                  }
                  $ret2 = array();
                  while ($row2 = $db->fetchObject($res2)) {
                        $ret2[] = $row2;
                  }
                  foreach ($ret2 as $ret3) {
                        $column_name = array();
                        $value_name = array();
                        $query = "INSERT IGNORE INTO " . $table . " (";
                        foreach ($ret1 as $field) {
                              $value = ($ret3->$field !== '') ? $ret3->$field : 'NULL';
                              $column_name[] = '`' . $field . '`';
                              if ($value !== NULL) {
                                    $value_name[] = "'" . str_replace('\'', '\\\'', $value) . "'";
                              } elseif ($value === NULL) {
                                    $value_name[] = "NULL";
                              }
                        }
                        $query .= implode(',', $column_name);
                        $query .= ') VALUES (';
                        $query .= implode(',', $value_name);
                        $query .= ');';
                        echo $query . '<br>';
                        $query2 = "UPDATE " . $table . " SET ";
                        $column_array = array();
                        foreach ($ret1 as $field) {
                              $value = ($ret3->$field !== '') ? $ret3->$field : 'NULL';
                              if ($value !== "" && $value !== "0000-00-00 00:00:00" && $value !== NULL) {
                                    $column_array[] = '`' . $field . "` = '" . str_replace('\'', '\\\'', $value) . "'";
                              } elseif ($value === NULL) {
                                    $column_array[] = '`' . $field . "` = NULL";
                              }
                        }
                        $query2 .= implode(', ', $column_array) . " WHERE `" . $pri . "` = '" . $ret3->$pri . "';<br>";
                        echo $query2;
                  }
            }
      }
      if (isset($_GET['status']) && $_GET['status'] > 0) {
            $query2 = '';
            $sql = "SELECT CONCAT('`', TABLE_SCHEMA, '`.`', TABLE_NAME, '`') AS TABLE_NAME 
FROM INFORMATION_SCHEMA.TABLES 
WHERE TABLE_TYPE = 'BASE TABLE' 
AND TABLE_SCHEMA = 'rapidkart_factory' 
AND (
    TABLE_NAME IN (        
        'whatsapp_template_type_placeholder_mapping', 
        'print_template_extra_fields', 
        'print_template_item_columns', 
        'print_template_type_extra_fields_mapping', 
        'print_template_type_item_column_mapping', 
        'print_template_type_raw_item_column_mapping', 
        'print_template_type_variable_mapping',  
        'email_template_placeholder','email_template_type', 'email_template_type_placeholder_mapping',
        'sms_template_placeholder','sms_template_type', 'sms_template_type_placeholder_mapping',
        'whatsapp_template_placeholder','whatsapp_template_type', 'whatsapp_template_type_placeholder_mapping',
        'print_template_variables', 
        'whatsapp_template_placeholder', 
        'whatsapp_template_type', 
        'uploading_type', 
        'uploading_type_columns', 
        'erp_form',         
        'mask_config', 
        'system_preferences', 
        'erp_form_column',
        'approval_blocking_conditions',
        'approval_blocking_conditions_group',
        'approval_blocking_conditions_module_mapping',
        'system_preferences_group',
        'system_preferences_module_mapping'
    ) 
   OR RIGHT(TABLE_NAME, 6) = 'status'
)";

            $res = $db->query($sql);
            $ret = array();
            if ($res && $db->resultNumRows($res) > 0) {

                  while ($row = $db->fetchObject($res)) {
                        if ($row->TABLE_NAME !== "" . BaseConfig::DB_NAME . ".`static_page`" && $row->TABLE_NAME !== "" . BaseConfig::DB_NAME . ".`static_page_category`") {
                              $ret[] = $row->TABLE_NAME;
                        }
                  }

                  foreach ($ret as $table) {
                        $sql1 = "SHOW COLUMNS FROM " . $table;
                        $res1 = $db->query($sql1);
                        if (!$res1) {
                              continue;
                        }
                        $ret1 = array();
                        $pri = null;
                        while ($row1 = $db->fetchObject($res1)) {
                              $ret1[] = $row1->Field;
                              if ($row1->Key === 'PRI') {
                                    $pri = $row1->Field;
                              }
                        }
                        $sql2 = "SELECT * FROM " . $table;
                        $res2 = $db->query($sql2);
                        if (!$res2) {
                              continue;
                        }
                        $ret2 = array();
                        while ($row2 = $db->fetchObject($res2)) {
                              $ret2[] = $row2;
                        }
                        foreach ($ret2 as $ret3) {
                              $column_name = array();
                              $value_name = array();
                              $query = "INSERT IGNORE INTO " . $table . " (";
                              foreach ($ret1 as $field) {
                                    $value = ($ret3->$field !== '') ? $ret3->$field : 'NULL';
                                    $column_name[] = '`' . $field . '`';
                                    if ($value !== NULL) {
                                          $value_name[] = "'" . str_replace('\'', '\\\'', $value) . "'";
                                    } elseif ($value === NULL) {
                                          $value_name[] = "NULL";
                                    }
                              }
                              $query .= implode(',', $column_name);
                              $query .= ') VALUES (';
                              $query .= implode(',', $value_name);
                              $query .= ');';
                              echo $query . '<br>';
                              $query2 = "UPDATE " . $table . " SET ";
                              $column_array = array();
                              foreach ($ret1 as $field) {
                                    $value = ($ret3->$field !== '') ? $ret3->$field : 'NULL';
                                    if ($value !== "" && $value !== "0000-00-00 00:00:00" && $value !== NULL) {
                                          $column_array[] = '`' . $field . "` = '" . str_replace('\'', '\\\'', $value) . "'";
                                    } elseif ($value === NULL) {
                                          $column_array[] = '`' . $field . "` = NULL";
                                    }
                              }
                              $query2 .= implode(', ', $column_array) . " WHERE `" . $pri . "` = '" . $ret3->$pri . "';<br>";
                              echo $query2;
                        }
                  }
            }
      }
      exit();
}

function upgrade_longtext()
{
      $db = Rapidkart::getInstance()->getDB();
      $sql = 'select TABLE_NAME, COLUMN_NAME from information_schema.columns where table_schema = \'' . BaseConfig::DB_NAME . '\' AND
            (DATA_TYPE = \'longtext\') AND TABLE_NAME NOT LIKE \'%view%\''
            . ' order by table_name,ordinal_position';
      $res = $db->query($sql);
      if (!$res || $db->resultNumRows($res) < 1) {
            return FALSE;
      }
      $ret = array();
      while ($row = $db->fetchObject($res)) {
            //echo 'ALTER TABLE `' . $row->TABLE_NAME . '` CHANGE `' . $row->COLUMN_NAME . '` `' . $row->COLUMN_NAME . '` DECIMAL(28,8) NOT NULL;<br>';
            echo 'ALTER TABLE `' . $row->TABLE_NAME . '` CHANGE `' . $row->COLUMN_NAME . '` `' . $row->COLUMN_NAME . '` LONGTEXT CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL;<br>';
      }
      exit();
}

function dashboard_customization($data, $masking, $masking_status)
{
      $resp = new AjaxResponse(true);
      $tpl = new Template(SystemConfig::templatesPath() . "home/dashboard-customize");
      $tpl->masking = $masking;
      $tpl->masking_status = $masking_status;
      $tpl->counters = DashboardManager::getCounters();
      $tpl->usercounters = DashboardManager::getUserSelection(Session::loggedInUid(), 2);
      $tpl->graphs = DashboardManager::getGraphs();
      $tpl->usergraphs = DashboardManager::getUserSelection(Session::loggedInUid(), 1);
      $tpl->links = DashboardManager::getLinks();
      $tpl->userlinks = DashboardManager::getUserModuleTaskLink(Session::loggedInUid());
      $tpl->tables = DashboardManager::getTables();
      $tpl->usertables = DashboardManager::getUserSelection(Session::loggedInUid(), 4);
      $resp->setData($tpl->parse());
      echo $resp->getOutput();
      exit();
}

function dashboard_customize_counter_submit($data)
{
      $resp = new AjaxResponse(false);
      $values = $data['vals'];
      if (count($values) > 8) {
            ScreenMessage::setMessage("You can select only 8 counters", ScreenMessage::MESSAGE_TYPE_ERROR);
            $resp->setScreenMessages(ScreenMessage::getMessages());
            echo $resp->getOutput();
            exit();
      }
      $mapping = new DashboardUserMapping();
      $uid = Session::loggedInUid();
      $mapping->delete($uid, 2);
      if (!empty($values) && valid($values)) {
            foreach ($values as $value) {
                  $mapping->setUid($uid);
                  $mapping->setDawiid($value);
                  $mapping->insert();
            }
      }
      $resp->setSuccess(true);
      ScreenMessage::setMessage("Dashboard updated successfully", ScreenMessage::MESSAGE_TYPE_SUCCESS);
      $resp->setScreenMessages(ScreenMessage::getMessages());
      echo $resp->getOutput();
      exit();
}

function dashboard_customize_graph_submit($data)
{
      $resp = new AjaxResponse(false);
      $values = $data['vals'];
      if (count($values) > 3) {
            ScreenMessage::setMessage("You can select only 2 Graphs", ScreenMessage::MESSAGE_TYPE_ERROR);
            $resp->setScreenMessages(ScreenMessage::getMessages());
            echo $resp->getOutput();
            exit();
      }
      $mapping = new DashboardUserMapping();
      $uid = Session::loggedInUid();
      $mapping->delete($uid, 1);
      if (!empty($values) && valid($values)) {
            foreach ($values as $value) {
                  $mapping->setUid($uid);
                  $mapping->setDawiid($value);
                  $mapping->insert();
            }
      }
      $resp->setSuccess(true);
      ScreenMessage::setMessage("Dashboard updated successfully", ScreenMessage::MESSAGE_TYPE_SUCCESS);
      $resp->setScreenMessages(ScreenMessage::getMessages());
      echo $resp->getOutput();
      exit();
}

function dashboard_customize_link_submit($data)
{
      $resp = new AjaxResponse(false);
      $values = $data['vals'];
      $mapping = new ModuleTaskUserMapping();
      $uid = Session::loggedInUid();
      $mapping->delete($uid, 3);
      if (!empty($values) && valid($values)) {
            foreach ($values as $value) {
                  $mapping->setUid($uid);
                  $mapping->setMotaid($value);
                  $mapping->insert();
            }
      }
      $resp->setSuccess(true);
      ScreenMessage::setMessage("Dashboard updated successfully", ScreenMessage::MESSAGE_TYPE_SUCCESS);
      $resp->setScreenMessages(ScreenMessage::getMessages());
      echo $resp->getOutput();
      exit();
}

function dashboard_customize_table_submit($data)
{
      $resp = new AjaxResponse(false);
      $values = $data['vals'];

      if (!empty($values) && count($values) > 2) {
            ScreenMessage::setMessage("You can select only 2 Tables", ScreenMessage::MESSAGE_TYPE_ERROR);
            $resp->setScreenMessages(ScreenMessage::getMessages());
            echo $resp->getOutput();
            exit();
      }
      $mapping = new DashboardUserMapping();
      $uid = Session::loggedInUid();
      $mapping->delete($uid, 4);
      if (!empty($values) && valid($values)) {
            foreach ($values as $value) {
                  $mapping->setUid($uid);
                  $mapping->setDawiid($value);
                  $mapping->insert();
            }
      }
      $resp->setSuccess(true);
      ScreenMessage::setMessage("Dashboard updated successfully", ScreenMessage::MESSAGE_TYPE_SUCCESS);
      $resp->setScreenMessages(ScreenMessage::getMessages());
      echo $resp->getOutput();
      exit();
}

function getAllContent()
{
      header('Content-type: text/html');
      $content = file_get_contents(SiteConfig::templatesPath() . "menus/sidebar.tpl.php", TRUE);
      $matches = array();
      $new_matches = array();
      preg_match_all('/Jpath::fullUrl\(\"[a-zA-Z-_\/\(\)]{1,255}\"/i', $content, $matches);
      preg_match_all("/<span class=\"sidebar-text link-url\".*span>/", $content, $text);
      $new_text_new = array();
      if ($text) {
            foreach ($text as $te) {
                  foreach ($te as $t) {
                        $new_text = str_replace('<span class="sidebar-text link-url">', '', $t);
                        $new_text = str_replace('<i class="fa fa-angle-double-right"></i>', '', $new_text);
                        $new_text = str_replace('<i class = "fa fa-angle-double-right"></i>', '', $new_text);
                        $new_text_new[] = str_replace('</span>', '', $new_text);
                  }
            }
      }

      if ($matches) {
            foreach ($matches as $match) {
                  foreach ($match as $ma) {
                        $new_match = str_replace('Jpath::fullUrl("', '', $ma);
                        $new_ma = str_replace('"', '', $new_match);
                        $new_matches[] = str_replace(')', '', $new_ma);
                  }
            }
      }
      $res = array_combine(array_intersect_key($new_text_new, $new_matches), array_intersect_key($new_matches, $new_text_new));
      if ($res) {
            foreach ($res as $na => $news) {
                  $data = explode('/', $news);
                  $new_name = str_replace(' ', '_', trim(strtolower($na)));
                  $moduels[] = array('mid' => $data[0], 'url' => $news, 'name' => $new_name, 'title' => trim($na));
            }
      }
      $db = Rapidkart::getInstance()->getDB();
      if ($moduels) {
            foreach ($moduels as $key => $value) {
                  $name = $value['mid'];
                  $sql = "SELECT mid FROM `module` WHERE `title` LIKE '%::name%'";
                  $res1 = $db->query($sql, array('::name' => $name));
                  if (!$res1) {
                        return FALSE;
                  }
                  $row = $db->fetchObject($res1);
                  if ($row->mid) {
                        $mids[] = array('mid' => $row->mid, 'url' => $value['url'], 'name' => $value['name'], 'title' => $value['title']);
                  }
            }
      }
      foreach ($moduels as $mid) {
            $names[] = $mid['name'];
      }
      $sql1 = "SELECT * FROM module_task mt INNER JOIN sidebar_menu sm ON (mt.name = sm.name)";
      $res2 = $db->query($sql1);
      if (!$res2 || $db->resultNumRows($res2) < 1) {
            return false;
      }
      $ret = array();
      while ($row = $db->fetchObject($res2)) {
            $ret[] = $row->name;
      }
      hprint(array_values(array_diff($ret, $names)));
      hprint(array_values(array_diff($names, $ret)));
      //        foreach ($names as $n)
      //        {
      //            if(!in_array($n, $ret))
      //            {
      //                $non[] = $n;
      //            }
      //        }
      //        hprint($non);
      //        foreach ($mids as $k => $v)
      //        {
      //            //echo "INSERT INTO module_task (mid, pid , motasid, motatid,url , title , name) VALUES ('" . $v['mid'] . "', NULL , 1 , 1, '" . $v['url'] . "' , '" . $v['title'] . "' , '" . $v['name'] . "');<br>";
      //        }
      exit();
}

function getSidebarDetails()
{
      $db = Rapidkart::getInstance()->getDB();
      echo "SELECT * FROM module_task mt INNER JOIN sidebar_menu sm ON (mt.name = sm.name)";
      exit();
}

function remove_duplicate()
{
      $db = Rapidkart::getInstance()->getDB();
      $sql = "DELETE cus1 FROM customer AS cus1 INNER JOIN customer AS cus2 WHERE cus1.cuid < cus2.cuid AND cus1.mobile = cus2.mobile;";
      $res = $db->query($sql);
      if ($db->affectedRows($res) < 1) {
            echo "No duplicate to remove";
            exit();
      } else {
            echo "Total " . $db->affectedRows($res) . " duplicates removed.";
      }
}

function payment_terms_update()
{
      $db = Rapidkart::getInstance()->getDB();
      $sql = "SELECT * FROM " . SystemTables::DB_TBL_PURCHASE_INVOICE . " WHERE iscid > 0 ";
      $res = $db->query($sql);
      if (!$res || $db->resultNumRows($res) < 1) {
            $db->rollBack();
            $db->autoCommit(TRUE);
            echo 'No Consignment is available';
            exit;
      }
      while ($row = $db->fetchObject($res)) {
            $consignment = new InventorySetContainer($row->iscid);
            $purchase_invoice = new PurchaseInvoice($row->pinvid);
            if ($consignment->getPurorid() > 0 && PurchaseOrder::isExistent($consignment->getPurorid())) {
                  $purchase_order = new PurchaseOrder($consignment->getPurorid());
                  $purchase_invoice->setPaymentTerms($purchase_order->getPaymentTerms());
                  $purchase_invoice->setPaymentDay($purchase_order->getPaymentDay());
                  if (!$purchase_invoice->update()) {
                        $db->rollBack();
                        $db->autoCommit(TRUE);
                        echo 'fail to update';
                        exit;
                  }
            }
      }
      $db->commit();
      echo 'updated successfully';
      exit;
}

function logout_all_users($data)
{
      $user = new AdminUser(Session::loggedInUid());

      if ($user->getIsAdmin()) {
            $all_users = AdminUserManager::logoutAllUsers();
            if ($all_users) {
                  Session::logoutUser();
                  Utility::ajaxResponseTrue("Logout Successfully", JPath::fullUrl("home"));
            }
      } else {
            Utility::ajaxResponseTrue("Logout Successfully", JPath::fullUrl("logout"));
      }
}

function switch_assign_company($data)
{
      if (!AdminUser::isExistent($data['id'])) {
            Utility::ajaxResponseFalse("Invalid User");
      }
      $user = new AdminUser($data['id']);
      $form = new GenericForm('assign-company-form');
      $form->setSubmitCallback('switchAssignCompanySubmit');

      $form_id = new FormInputBox('user_hidden_id');
      $form_id->setVal($user->getId());
      $form_id->setType('hidden');
      $form->addChild($form_id->publishXml());

      $companies = new FormSelectPicker('company', 'Company', TRUE, 'company');
      $licence_cos = AdminUserManager::getUserCompanyMappingList($user->getId());

      if ($licence_cos) {
            foreach ($licence_cos as $licence_co) {
                  $companies->addItem($licence_co->getId(), $licence_co->getName(), $licence_co->getId() == $user->getCompanyId());
            }
      }

      $form->addChild($companies->publishXml());

      $form_btn = new FormBtn('submit', 'switch-assign-company-submit', 'Submit');
      $form_btn->setCntlClass('m-t-10');
      $form->addChild($form_btn->publishXml());

      $panel = new Panel('assign-company-wrapper', 'fa-check-square-o', 'bg-blue', 'Select Company', "", TRUE);
      $panel->setCustomHtml($form->publish());

      Utility::ajaxResponseTrue("", $panel->publish());
}

function switch_assign_company_submit($data)
{
      if (!AdminUser::isExistent($data['user_hidden_id'])) {
            Utility::ajaxResponseFalse("Invalid User");
      }
      $db = Rapidkart::getInstance()->getDB();
      $db->autoCommit(FALSE);
      $user = new AdminUser($data['user_hidden_id']);
      $user->setCompanyId($data['company']);
      $_SESSION['company_id'] = $data['company'];
      BaseConfig::$company_id = $data['company'];
      BaseConfig::$company_start_date = LicenceManager::getLicenceCompanyStartDate();
      if (!$user->update()) {

            $db->rollBack();
            $db->autoCommit(TRUE);
            Utility::ajaxResponseFalse("Fail to update");
      }

      Session::updateCompanyId($data);
      $db->commit();
      Utility::ajaxResponseTrue("User Company Mapping Updated Successfully");
}

function verify_gstin($data)
{
      Utility::ajaxResponseTrue("");
      $gstin = trim(str_replace(" ", "", $data['gstin']));
      $mainPath = 'https://commonapi.mastersindia.co/oauth/access_token';
      $url = $mainPath;
      $header = array("Content-Type: application/json");

      $data = array(
            "username" => "sunoop@domus.asia",
            "password" => "Domus@123",
            "client_id" => "zYJQQXuYUxojRubjBp",
            "client_secret" => "y9p2Ij3JLjiFDFa4JFBpUZMM",
            "grant_type" => "password"
      );
      $data_string = json_encode($data);

      $ch = curl_init();
      curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
      curl_setopt($ch, CURLOPT_POSTFIELDS, $data_string);
      curl_setopt($ch, CURLOPT_ENCODING, "gzip");
      curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
      curl_setopt($ch, CURLOPT_URL, $url);
      curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
      curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.1; SV1; .NET CLR 1.0.3705; .NET CLR 1.1.4322)');
      $response = curl_exec($ch);
      if (!$response) {
            Utility::ajaxResponseFalse("Invalid GSTIN");
      }
      $response_decode = json_decode($response, TRUE);
      if (isset($response_decode['error']) && $response_decode['error'] == 1) {
            Utility::ajaxResponseFalse($response_decode['message']);
      }

      if (!isset($response_decode['access_token'])) {
            Utility::ajaxResponseFalse('Invalid Access Token');
      }
      $access_token = $response_decode['access_token'];
      curl_close($ch);

      $ch = curl_init();
      $params = "?gstin=" . $gstin;
      $mainPath = "https://commonapi.mastersindia.co/commonapis/searchgstin";
      $url = $mainPath . '' . $params;
      $header = array(
            "Content-Type: application/json",
            "Authorization: Bearer " . $access_token,
            "client_id: zYJQQXuYUxojRubjBp"
      );
      curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
      curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "GET");
      curl_setopt($ch, CURLOPT_ENCODING, "gzip");
      curl_setopt($ch, CURLOPT_URL, $url);
      curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
      curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.1; SV1; .NET CLR 1.0.3705; .NET CLR 1.1.4322)');
      $response = curl_exec($ch);
      //        hprint($response);
      if (!$response) {
            Utility::ajaxResponseFalse("GSTIN not valid");
      }
      $response_decode = json_decode($response, TRUE);
      if (isset($response_decode['error']) && $response_decode['error'] == 1) {
            Utility::ajaxResponseFalse($response_decode['data']);
      }

      $info = $response_decode['data'];
      $legal = $info['lgnm'];
      $state = $info['stj'];
      $centre = $info['ctj'];
      $reg = date('Y-m-d', strtotime($info['rgdt']));
      $cons = $info['ctb'];
      $type = $info['dty'];
      $status = $info['sts'];

      $cancel = $info['cxdt'];
      if (!valid($cancel) || $cancel == "" || $cancel == NULL) {
            $cancel = "";
      } else {
            $cancel = date('Y-m-d', strtotime($cancel));
            Utility::ajaxResponseFalse("GSTIN is unregistered");
      }

      Utility::ajaxResponseTrue("", array("legal" => $legal, "state" => $state, "centre" => $centre, "reg" => $reg, "cons" => $cons, "type" => $type, "status" => $status, "cancel" => $cancel));
}

function show_max_discount_items_pop_up($data)
{
      if (!isset($data['cart_discounts'])) {
            Utility::ajaxResponseFalse('Invalid Items available');
      }
      if (!isset($data['cart_discounts'])) {
            Utility::ajaxResponseFalse('Invalid Items available');
      }
      $cart_discounts = $data['cart_discounts'];
      $form = new GenericForm('max-discount-items-form');
      $form_combo = new FormComboTableV2('showItemsWithMaxDiscount');
      $form_combo->addHeaderElem(' Sl.No', 1, 1, 1, 1);
      $form_combo->addHeaderElem(' Name', 4, 4, 4, 4);
      $form_combo->addHeaderElem(' Min Percentage', 2, 2, 2, 2, 'text-center');
      $form_combo->addHeaderElem(' Max Percentage', 2, 2, 2, 2, 'text-center');
      $form_combo->addHeaderElem(' Percentage Provided', 3, 3, 3, 3, 'text-center');
      $i = 1;
      foreach ($cart_discounts as $cart_discount) {
            if (!InventorySetVariation::isExistent($cart_discount['isvid'])) {
                  Utility::ajaxResponseFalse("Invalid Variation");
            }
            $variation = new InventorySetVariation($cart_discount['isvid']);
            if ($variation->getMaxDiscountPercent() != 0 && $variation->getMaxDiscountPercent() > $cart_discount['effective_discount_percent']) {
                  continue;
            }
            $p = new FormDisplayHtml('p');
            $p->setValue($i++ . '.');
            $form_combo->addChild($p->publishXml());

            $p = new FormDisplayHtml('p');
            $p->setValue($variation->getName());
            $form_combo->addChild($p->publishXml());

            $p = new FormDisplayHtml('p');
            $p->setValue(Utility::getPriceFormat($variation->getMinDiscountPercent()) . ' %');
            $p->setCntlClass('text-center');
            $form_combo->addChild($p->publishXml());

            $p = new FormDisplayHtml('p');
            $p->setValue(Utility::getPriceFormat($variation->getMaxDiscountPercent()) . ' %');
            $p->setCntlClass('text-center');
            $form_combo->addChild($p->publishXml());

            $p = new FormDisplayHtml('p');
            $p->setValue(Utility::getPriceFormat($cart_discount['effective_discount_percent']) . ' %');
            $p->setCntlClass('text-center');
            $form_combo->addChild($p->publishXml());

            $form_combo->setAction(FALSE);
      }
      $form->addChild($form_combo->publishXml());

      $panel = new Panel('Max Discount Items wrapper', 'fa-list', "bg-teal", 'Max Discount reached items', '', TRUE);
      $panel->setCustomHtml($form->publish());
      Utility::ajaxResponseFalse("", $panel->publish());
}

function upload_attachement_show_files($data)
{

      //        1. Sales order, 2. Quotation, 3. Purchase invoice, 4.Purchase Order, 5. Credit Note, 6. Debit Note, 7. Journal or Contra Voucher, 8. Receipt, 9.Payment
      $module = $getFiles = $folder = $fileDirectory = '';
      if (isset($data['bit']) && $data['bit'] !== '') {
            switch ($data['bit']) {
                  case 1:
                        if (!CheckPointOrder::isExistent($data['id'])) {
                              Utility::ajaxResponseFalse("Invalid Sales Order");
                        }
                        $module = new CheckPointOrder($data['id']);
                        $getFiles = $module->getFiles();
                        $fileDirectory = BaseConfig::FILES_DIR . "chkorder/";
                        $folder = "chkorder";
                        break;
                  case 2:
                        if (!Quotation::isExistent($data['id'])) {
                              Utility::ajaxResponseFalse("Invalid Quotation");
                        }
                        $module = new Quotation($data['id']);
                        $getFiles = $module->getFiles();
                        $fileDirectory = BaseConfig::FILES_DIR . "quotation/";
                        $folder = "quotation";
                        break;
                  case 3:
                        if (!PurchaseInvoice::isExistent($data['id'])) {
                              Utility::ajaxResponseFalse("Invalid Purchase Invoice");
                        }
                        $module = new PurchaseInvoice($data['id']);
                        $getFiles = $module->getFiles();
                        $fileDirectory = BaseConfig::FILES_DIR . "purchase/";
                        $folder = "purchase";
                        break;
                  case 4:
                        if (!PurchaseOrder::isExistent($data['id'])) {
                              Utility::ajaxResponseFalse("Invalid Purchase Order");
                        }
                        $module = new PurchaseOrder($data['id']);
                        $getFiles = $module->getFiles();
                        $fileDirectory = BaseConfig::FILES_DIR . "po/";
                        $folder = "po";
                        break;
                  case 5:
                        if (!CheckPointOrderCreditNote::isExistent($data['id'])) {
                              Utility::ajaxResponseFalse("Invalid Credit Note");
                        }
                        $module = new CheckPointOrderCreditNote($data['id']);
                        $getFiles = CheckPointOrderCreditNoteFileManager::getImage($data['id']);
                        $fileDirectory = BaseConfig::FILES_DIR . "order_items/";
                        $folder = "order_items";
                        break;
                  case 6:
                        if (!PurchaseOrderDebitNote::isExistent($data['id'])) {
                              Utility::ajaxResponseFalse("Invalid Debit Note");
                        }
                        $module = new PurchaseOrderDebitNote($data['id']);
                        $getFiles = PurchaseOrderDebitNoteManager::getImage($data['id']);
                        $fileDirectory = BaseConfig::FILES_DIR . "debit_note/";
                        $folder = "debit_note";
                        break;
                  case 7:
                        if (!AccountVoucher::isExistent($data['id'])) {
                              Utility::ajaxResponseFalse("Invalid Voucher");
                        }
                        $module = new AccountVoucher($data['id']);
                        $getFiles = AccountVoucherManager::loadAccountVoucherFiles($module->getId());
                        $fileDirectory = BaseConfig::FILES_DIR . "account/voucher/" . AccountVoucher::IMG_SIZE_LARGE . "/";
                        $folder = "account/voucher/" . AccountVoucher::IMG_SIZE_LARGE;
                        break;
                  case 8:
                        if (!Transaction::isExistent($data['id'])) {
                              Utility::ajaxResponseFalse("Invalid Transaction");
                        }
                        $module = new Transaction($data['id']);
                        $getFiles = TransactionManager::getImage($module->getId());
                        $fileDirectory = BaseConfig::FILES_DIR . "invoice/";
                        $folder = "invoice";
                        break;
                  case 9:
                        if (!Transaction::isExistent($data['id'])) {
                              Utility::ajaxResponseFalse("Invalid Transaction");
                        }
                        $module = new Transaction($data['id']);
                        $getFiles = TransactionManager::getFiles($module->getId());
                        $fileDirectory = BaseConfig::FILES_DIR . "invoice/";
                        $folder = "invoice";
                        break;
            }
      }


      $panel = new Panel('view-upload-attachments-files-wrapper', 'fa-upload', 'bg-torques', " Documents ", "", TRUE);
      $form = new GenericForm('upload-attachements-form');
      $form->setSubmitCallback("uploadAttachementsSubmit");

      $formrow = new FormRow();
      $form_input = new FormInputBox('type-id');
      $form_input->setVal($data['bit']);
      $form_input->setCntlClass('hide');
      $form->addChild($form_input->publishXml());
      $module_input = new FormInputBox('module-id');
      $module_input->setVal($module->getId());
      $module_input->setCntlClass('hide');
      $form->addChild($module_input->publishXml());

      $formGrp = new FormGroup();
      $formrow->addChild($formGrp->publishXml(), 12, 12, 12, 12);

      $prepopulate = array();
      if (is_array($getFiles) && !empty($getFiles)) {
            foreach ($getFiles as $attachment) {
                  $id = '';
                  if ($data['bit'] == 1 || $data['bit'] == 2 || $data['bit'] == 4) {
                        $attachFile = $attachment->file;
                        $attachName = $attachment->name;
                        $attachFormat = $attachment->format;
                        $fileDir = $fileDirectory . $attachment->file;
                        $fileName = $attachment->name . '.' . $attachment->format;
                  }
                  switch ($data['bit']) {
                        case 1: // Sales order
                              $id = $attachment->chkofid;
                              break;
                        case 2: // Quotation
                              $id = $attachment->qofid;
                              break;
                        case 3: // Purchase invoice
                              $id = $attachment->pinvfid;
                              $attachFile = $attachment->thumbnail;
                              $folderOrginalName = explode(".", $attachment->name);
                              $attachName = $folderOrginalName[0];
                              $attachFormat = $folderOrginalName[1];
                              $fileDir = $fileDirectory . $attachment->thumbnail;
                              $fileName = $attachment->name;
                              break;
                        case 4: // Purchase Order
                              $id = $attachment->purorfid;
                              break;
                        case 5: // Credit Note
                              $id = $attachment->crdofid;
                              $attachFile = $attachment->thumbnail;
                              $folderOrginalName = explode(".", $attachment->name);
                              $attachName = $folderOrginalName[0];
                              $fileDir = $fileDirectory . $attachment->thumbnail;
                              $fileName = $attachment->name;
                              $attachFormat = $attachment->format;
                              break;
                        case 6: // Debit Note
                              $id = $attachment->purorfid;
                              $attachFile = $attachment->thumbnail;
                              $folderOrginalName = explode(".", $attachment->name);
                              $attachName = $folderOrginalName[0];
                              $fileDir = $fileDirectory . $attachment->thumbnail;
                              $fileName = $attachment->name;
                              $attachFormat = $attachment->format;
                              break;
                        case 7: // Journal or Contra Voucher
                              $id = $attachment->avaid;
                              $attachFile = $attachment->name;
                              $folderOrginalName = explode(".", $attachment->name);
                              $attachName = $folderOrginalName[0];
                              $fileDir = $fileDirectory . $attachment->name;
                              $fileName = $attachment->name;
                              $attachFormat = $attachment->format;
                              $fileCheck = $_SERVER['DOCUMENT_ROOT'] . BaseConfig::FILES_URL . "account/voucher/large/" . $attachFile;
                              if (!file_exists($fileCheck)) {
                                    $folder = "account/voucher/";
                                    $fileDirectory = BaseConfig::FILES_DIR . "account/voucher/";
                              }
                              break;
                        case 8: // Receipt 
                              $id = $attachment->rfid;
                              $attachFile = $attachment->thumbnail;
                              $folderOrginalName = explode(".", $attachment->thumbnail);
                              $attachName = $folderOrginalName[0];
                              $fileDir = $fileDirectory . $attachment->thumbnail;
                              $fileName = $attachment->thumbnail;
                              $attachFormat = $attachment->format;
                              break;
                        case 9: // Payment 
                              $id = $attachment->tid;
                              $attachFile = $attachment->thumbnail;
                              $folderOrginalName = explode(".", $attachment->thumbnail);
                              $attachName = $folderOrginalName[0];
                              $fileDir = $fileDirectory . $attachment->thumbnail;
                              $fileName = $attachment->thumbnail;
                              $attachFormat = $attachment->format;
                              break;
                  }
                  $prepopulate[] = array('addRemoveLinks' => true, 'id' => $id, 'name' => $fileName, 'size' => '12546', 'actual_name' => $fileName, 'newname' => $attachFile, 'old_name' => $attachName, 'url' => BaseConfig::FILES_URL . $folder, "type" => $attachFormat, 'download_url' => '/rpkfiles/' . $folder . '/' . $attachFile, "response" => array("images" => array(0 => array("id" => "image-divs", 'delete_file' => true, 'download_file' => true, 'actual_name' => $fileName, 'name' => $fileDir, 'image' => '/rpkfiles/' . $folder . '/' . $attachFile, 'filepath' => $fileDir, 'external_name' => FALSE, 'basepath' => SystemConfig::basePath()))));
            }
      }

      $fileUploader = new FormFileUploader('uploader', 'Upload Documents');
      $fileUploader->setCntlClass('variation-image-uploader');
      $fileUploader->setPrepopulate($prepopulate);
      $fileUploader->setMaxFiles(100);
      $fileUploader->setMaxFileSize(5);
      $fileUploader->setOnUploadSubmit('upload-attachement-files');
      $fileUploader->setOnDeleteSubmit('attachement-delete-file');
      $fileUploader->setParams(array(array("key" => "module_type", "value" => $data['bit']), array("key" => 'module_id', "value" => $module->getId())));
      $formrow->addChild($fileUploader->publishXml(), 12, 12, 12, 12);
      $form->addChild($formrow->publishXml());

      $formBtn = new FormBtn('submit', 'attachments-upload-file-submit', 'Submit');
      $form->addChild($formBtn->publishXml());
      $panel->setCustomHtml($form->publish());
      Utility::ajaxResponseTrue("", $panel->publish());
}

function attachments_upload_file_submit($data)
{
      //        1. Sales order, 2. Quotation, 3. Purchase invoice, 4.Purchase Order, 5. Credit Note, 6. Debit Note, 7. Journal or Contra Voucher, 8. Payment or Receipt
      $db = Rapidkart::getInstance()->getDB();
      $db->autoCommit(FALSE);
      $previous_files_array = array();
      $previous_files = array();

      $files = array();
      if (isset($data['files']) && is_array($data['files']) && !empty($data['files'])) {
            $files = $data['files'];
      }

      if (!empty($files)) {
            foreach ($files as $file) {
                  if (isset($file['id'])) {
                        unset($previous_files[$file['id']]);
                  }
            }
      }

      if (isset($data['type-id'])) {
            switch ($data['type-id']) {
                  case 1:
                        $moduleId = $data['module-id'];
                        if (!CheckPointOrder::isExistent($moduleId)) {
                              Utility::ajaxResponseFalse("Invalid sales order");
                        }

                        $previous_files_array = CheckPointOrderManager::loadFiles($moduleId);
                        if (!empty($previous_files_array)) {
                              foreach ($previous_files_array as $filesId) {
                                    $previous_files[] = $filesId->chkofid;
                              }
                        }

                        if (!empty($files)) {
                              if (!UploadItemFile::insertAttachmentFiles($files, $moduleId, 1)) {
                                    $db->rollBack();
                                    $db->autoCommit(FALSE);
                                    Utility::ajaxResponseFalse("Fail to attach Sales Order Files");
                              }
                        }
                        break;
                  case 2:
                        $moduleId = $data['module-id'];
                        if (!Quotation::isExistent($moduleId)) {
                              Utility::ajaxResponseFalse("Invalid Quotation");
                        }

                        $previous_files_array = QuotationManager::loadFiles($moduleId);
                        if (!empty($previous_files_array)) {
                              foreach ($previous_files_array as $filesId) {
                                    $previous_files[] = $filesId->qofid;
                              }
                        }

                        if (!empty($files)) {
                              if (!UploadItemFile::insertAttachmentFiles($files, $moduleId, 2)) {
                                    $db->rollBack();
                                    $db->autoCommit(FALSE);
                                    Utility::ajaxResponseFalse("Fail to attach Quotation files");
                              }
                        }
                        break;
                  case 3:
                        $moduleId = $data['module-id'];
                        if (!PurchaseInvoice::isExistent($moduleId)) {
                              Utility::ajaxResponseFalse("Invalid Purchase Invoice");
                        }

                        $previous_files_array = PurchaseInvoiceManager::loadFiles($moduleId);
                        if (!empty($previous_files_array)) {
                              foreach ($previous_files_array as $filesId) {
                                    $previous_files[] = $filesId->pinvfid;
                              }
                        }

                        if (!empty($files)) {
                              if (!UploadItemFile::insertAttachmentFiles($files, $moduleId, 3)) {
                                    $db->rollBack();
                                    $db->autoCommit(FALSE);
                                    Utility::ajaxResponseFalse("Fail to attach Purchase Invoice file");
                              }
                        }
                        break;
                  case 4:
                        $moduleId = $data['module-id'];
                        if (!PurchaseOrder::isExistent($moduleId)) {
                              Utility::ajaxResponseFalse("Invalid Purchase Order");
                        }

                        $previous_files_array = PurchaseOrderManager::loadFiles($moduleId);
                        if (!empty($previous_files_array)) {
                              foreach ($previous_files_array as $filesId) {
                                    $previous_files[] = $filesId->purorfid;
                              }
                        }
                        if (!empty($files)) {
                              if (!UploadItemFile::insertAttachmentFiles($files, $moduleId, 4)) {
                                    $db->rollBack();
                                    $db->autoCommit(FALSE);
                                    Utility::ajaxResponseFalse("Fail to attach Purchase Order files");
                              }
                        }
                        break;
                  case 5:
                        $moduleId = $data['module-id'];
                        if (!CheckPointOrderCreditNote::isExistent($moduleId)) {
                              Utility::ajaxResponseFalse("Invalid Credit Note");
                        }

                        $previous_files_array = CheckPointOrderCreditNoteFileManager::getImage($moduleId);
                        if (!empty($previous_files_array)) {
                              foreach ($previous_files_array as $filesId) {
                                    $previous_files[] = $filesId->crdofid;
                              }
                        }

                        if (!empty($files)) {
                              if (!UploadItemFile::insertAttachmentFiles($files, $moduleId, 5)) {
                                    $db->rollBack();
                                    $db->autoCommit(FALSE);
                                    Utility::ajaxResponseFalse("Fail to attach Credit Note file");
                              }
                        }
                        break;
                  case 6:
                        $moduleId = $data['module-id'];
                        if (!PurchaseOrderDebitNote::isExistent($moduleId)) {
                              Utility::ajaxResponseFalse("Invalid Debit Note");
                        }

                        $previous_files_array = PurchaseOrderDebitNoteManager::getImage($moduleId);
                        if (!empty($previous_files_array)) {
                              foreach ($previous_files_array as $filesId) {
                                    $previous_files[] = $filesId->purorfid;
                              }
                        }

                        if (!empty($files)) {
                              if (!UploadItemFile::insertAttachmentFiles($files, $moduleId, 6)) {
                                    $db->rollBack();
                                    $db->autoCommit(FALSE);
                                    Utility::ajaxResponseFalse("Fail to attach Debit Note file");
                              }
                        }
                        break;
                  case 7:
                        $moduleId = $data['module-id'];
                        if (!AccountVoucher::isExistent($moduleId)) {
                              Utility::ajaxResponseFalse("Invalid Voucher");
                        }

                        $previous_files_array = AccountVoucherManager::loadAccountVoucherFiles($moduleId);
                        if (!empty($previous_files_array)) {
                              foreach ($previous_files_array as $filesId) {
                                    $previous_files[] = $filesId->avaid;
                              }
                        }

                        if (!empty($files)) {
                              if (!UploadItemFile::insertAttachmentFiles($files, $moduleId, 7)) {
                                    $db->rollBack();
                                    $db->autoCommit(FALSE);
                                    Utility::ajaxResponseFalse("Fail to attach Debit Note file");
                              }
                        }
                        break;
                  case 8:
                        $moduleId = $data['module-id'];
                        if (!Transaction::isExistent($moduleId)) {
                              Utility::ajaxResponseFalse("Invalid Receipt Transaction");
                        }
                        $transaction = new Transaction($moduleId);
                        $deleteExisitngfiles = TransactionManager::deleteTransactionFiles($transaction->getId(), 1);
                        if (!$deleteExisitngfiles) {
                              $db->rollBack();
                              $db->autoCommit(TRUE);
                              Utility::ajaxResponseFalse("Fail to upload files");
                        }

                        if (!empty($files)) {
                              if (!UploadItemFile::insertAttachmentFiles($files, $transaction->getId(), 8, $transaction->getAvid())) {
                                    $db->rollBack();
                                    $db->autoCommit(FALSE);
                                    Utility::ajaxResponseFalse("Fail to attach Receipt file");
                              }
                        }
                        break;
                  case 9:
                        $moduleId = $data['module-id'];
                        if (!Transaction::isExistent($moduleId)) {
                              Utility::ajaxResponseFalse("Invalid Payment Transaction");
                        }
                        $transaction = new Transaction($moduleId);
                        $deleteExisitngfiles = TransactionManager::deleteTransactionFiles($transaction->getId(), 2);
                        if (!$deleteExisitngfiles) {
                              $db->rollBack();
                              $db->autoCommit(TRUE);
                              Utility::ajaxResponseFalse("Fail to upload files");
                        }

                        if (!empty($files)) {
                              if (!UploadItemFile::insertAttachmentFiles($files, $transaction->getId(), 9, $transaction->getAvid())) {
                                    $db->rollBack();
                                    $db->autoCommit(FALSE);
                                    Utility::ajaxResponseFalse("Fail to attach Payment file");
                              }
                        }
                        break;
            }
      }
      if (!empty($previous_files)) {
            foreach ($previous_files as $previous_file) {
                  switch ($data['type-id']) {
                        case 1:
                              $deleteFiles = CheckPointOrderManager::deleteCheckPointOrderFile($moduleId, $previous_file);
                              break;
                        case 2:
                              $deleteFiles = QuotationManager::deleteQuotationFile($moduleId, $previous_file);
                              break;
                        case 3:
                              $deleteFiles = PurchaseInvoiceManager::deleteExistingUploadFile($moduleId, $previous_file);
                              break;
                        case 4:
                              $deleteFiles = PurchaseOrderManager::deletePurchaseOrderFile($moduleId, $previous_file);
                              break;
                        case 5:
                              $deleteFiles = CheckPointOrderCreditNoteFileManager::deleteExistingUploadFile($moduleId, $previous_file);
                              break;
                        case 6:
                              $deleteFiles = PurchaseOrderDebitNoteManager::deleteExistingUploadFile($moduleId, $previous_file);
                              break;
                        case 7:
                              $deleteFiles = AccountVoucherManager::addAttachments($moduleId, $attachs = array(), true, $previous_file);
                              break;
                  }
                  if (!$deleteFiles) {
                        $db->rollBack();
                        $db->autoCommit(TRUE);
                        Utility::ajaxResponseFalse("Fail to upload files");
                  }
            }
      }

      if ($data['type-id'] == 3) {
            $purchaseInvoice = new PurchaseInvoice($moduleId);
            $imageCount = PurchaseInvoiceManager::getImages($purchaseInvoice->getId());
            $fileCount = 0;
            if (!empty($imageCount)) {
                  $fileCount = count($imageCount);
            }
            $purchaseInvoice->setPinvfidCount($fileCount);
            if (!$purchaseInvoice->update()) {
                  $db->rollBack();
                  $db->autoCommit(TRUE);
                  Utility::ajaxResponseFalse("Failed to file count in purchase invoice");
            }
      } else if ($data['type-id'] == 9 || $data['type-id'] == 8) {
            $transaction = new Transaction($moduleId);
            $imageCount = TransactionManager::getFiles($transaction->getId());
            $fileCount = 0;
            if (!empty($imageCount)) {
                  $fileCount = count($imageCount);
            }
            $transaction->setTfidCount($fileCount);

            if (!$transaction->update()) {
                  $db->rollBack();
                  $db->autoCommit(TRUE);
                  Utility::ajaxResponseFalse("Failed to update file count");
            }
      }

      $db->commit();
      $db->autoCommit(TRUE);
      Utility::ajaxResponseTrue("Files Updated Successfully");
}

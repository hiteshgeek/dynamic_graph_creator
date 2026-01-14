<?php

/**
 * Class providing utility support methods for Rapidkart
 *
 * @author Sohil Gupta
 * @since 20140616
 */
class Utility
{

      public static $cur_array = array();

      /**
       * Logs a message to the database
       * 
       * @param $type The type of message to log
       * @param $message
       */
      public static function log($type, $message)
      {
            $db = Rapidkart::getInstance()->getDB();

            $res = $db->query("INSERT INTO system_log (type, message) VALUES (':type', ':message')", array(":type" => $type, ":message" => $message));
            return ($res) ? true : false;
      }

      /**
       * Set a variable in the site table that can be used later 
       * 
       * @param $vid The id by which to store the variable
       * @param $value The actual value to store
       */
      public static function variableSet($vid, $value)
      {
            $db = Rapidkart::getInstance()->getDB();

            $args = array("::vid" => $vid, "::value" => $value);
            $sql = "INSERT INTO variable (vid, value) VALUES ('::vid', '::value')
                ON DUPLICATE KEY UPDATE value='::value'";
            $res = $db->query($sql, $args);
            return $res;
      }

      /**
       * Retrieves a variable that was set earlier in the site variable table
       * 
       * @param $vid The id by of the variable to retrieve
       */
      public static function convertMonthIntoWeek($year, $month)
      {
            $date = new DateTime("$year-$month-01");
            $firstDayOfMonth = $date->format('N');
            $startOfWeek = clone $date;
            $startOfWeek->modify('last Sunday');
            $weeks = [];
            while ($startOfWeek->format('Y-m') <= $date->format('Y-m')) {
                  $endOfWeek = clone $startOfWeek;
                  $endOfWeek->modify('next Saturday');
                  $weeks[] = [
                        'start' => $startOfWeek->format('Y-m-d'),
                        'end' => $endOfWeek->format('Y-m-d')
                  ];
                  $startOfWeek->modify('next Sunday');
            }
            foreach ($weeks as &$week) {
                  $weekStart = new DateTime($week['start']);
                  $weekEnd = new DateTime($week['end']);

                  if ($weekStart < $date) {
                        $week['start'] = $date->format('Y-m-d');
                  }
                  if ($weekEnd->format('Y-m') != $date->format('Y-m')) {
                        $week['end'] = (new DateTime("$year-$month-01"))->modify('last day of this month')->format('Y-m-d');
                  }
            }
            return $weeks;
      }

      public static function isJson($string)
      {
            if (!is_string($string)) return false;
            json_decode($string);
            return (json_last_error() == JSON_ERROR_NONE);
      }

      public static function variableGet($vid, $comapany_id = NULL)
      {
            global $variable_config;
            if (isset($variable_config[$vid])) {
                  return $variable_config[$vid];
            }
            $db = Rapidkart::getInstance()->getDB();

            $vid = $db->escapeString($vid);
            $sql = "SELECT variable_value as value FROM variable_company_mapping WHERE vid='::vid' AND company_id = '::company_id' ";
            $args = array("::vid" => $vid, "::company_id" => $comapany_id ? $comapany_id : BaseConfig::$company_id);
            $res = $db->query($sql, $args);
            $variable = $db->fetchObject($res);
            if (isset($variable->value)) {
                  return $variable->value;
            } else {
                  return false;
            }
      }

      public static function variableCompanyGet($vid, $comapany_id = NULL)
      {

            $db = Rapidkart::getInstance()->getDB();

            $vid = $db->escapeString($vid);
            $sql = "SELECT variable_value as value FROM variable_company_mapping WHERE vid='::vid' AND company_id = '::company_id' ";
            $args = array("::vid" => $vid, "::company_id" => $comapany_id ? $comapany_id : BaseConfig::$company_id);
            $res = $db->query($sql, $args);
            $variable = $db->fetchObject($res);
            if (isset($variable->value)) {
                  return $variable->value;
            } else {
                  return false;
            }
      }

      public static function getCountryName($ctid)
      {
            $db = Rapidkart::getInstance()->getDB();

            $sql = "SELECT name  FROM country WHERE ctid ='::ctid' ";
            $args = array("::ctid" => $ctid);
            $res = $db->query($sql, $args);
            $country = $db->fetchObject($res);
            if (isset($country->name)) {
                  return $country->name;
            } else {
                  return false;
            }
      }

      public static function isDivisible($num, $divisor)
      {
            if ($divisor == 0) {
                  return 0; // Avoid division by zero
            }
            return ($num % $divisor === 0) ? 1 : 0;
      }

      public static function getCashDiscountStatus($status)
      {

            switch ($status) {
                  case 1:
                        return '<label class="label label-success">Active</label>';
                        break;
                  case 2:
                        return '<label class="label label-danger">Inactive</label>';
                        break;
                  case 3:
                        return '<label class="label label-default">Deleted</label>';
                        break;
            }
      }

      public static function getVariationStatus($status)
      {

            switch ($status) {
                  case 1:
                        return '<label class="label label-success">Active</label>';
                        break;
                  case 2:
                        return '<label class="label label-danger">Inactive</label>';
                        break;
                  case 3:
                        return '<label class="label label-default">Deleted</label>';
                        break;
                  case 4:
                        return '<label class="label label-default">To be Approved</label>';
                        break;
            }
      }

      public static function getPrintTemplateStatus($status)
      {

            switch ($status) {
                  case 1:
                        return '<label class="label label-success">Active</label>';
                        break;
                  case 2:
                        return '<label class="label label-danger">Inactive</label>';
                        break;
                  case 3:
                        return '<label class="label label-default">Deleted</label>';
                        break;
            }
      }

      /**
       * @return The website's name
       */
      public static function getSiteName()
      {
            return self::variableGet("sitename");
      }

      public static function getSiteAdminName()
      {
            return self::variableGet("site_admin_portal");
      }

      /**
       *
       */
      public static function setHeading($heading, $subheading = null, $icon = null)
      {
            /* load the template */
            $theme = Rapidkart::getInstance()->getThemeRegistry();
            $theme->setContent("heading", $heading);
            if (isset($subheading)) {
                  $theme->setContent("subheading", $subheading);
            }
            if (isset($icon)) {
                  $theme->setContent("icon", $icon);
            }
      }

      public static function setHeaderButtons($header_button)
      {
            $theme = Rapidkart::getInstance()->getThemeRegistry();
            $theme->addVariable("header_buttons", $header_button);
      }

      public static function setHeaderCounters($header_counter)
      {
            $theme = Rapidkart::getInstance()->getThemeRegistry();
            $theme->addVariable("header_counter", $header_counter);
      }

      public static function setHeaderGraphs($header_graphs)
      {
            $theme = Rapidkart::getInstance()->getThemeRegistry();
            $theme->addVariable("header_graphs", $header_graphs);
      }

      public static function loadIframe($url, $type = "right", $width = 500, $height = 400)
      {
            if (!isset($url)) {
                  $code = "<div class='label label-danger'>No Valid Url Found</div>";
            } else {
                  $code = "<iframe src='$url' width='$width' height='$height'></iframe>";
            }
            $theme = Rapidkart::getInstance()->getThemeRegistry();
            $theme->setContent($type, $code);
      }

      /**
       * get the custom labels
       */
      public static function getLabel($str, $status = 1)
      {
            $label = "primary";
            if (isset($status) && $status == 1) {
                  $label = "success";
            } else if (isset($status) && $status == 2) {
                  $label = "danger";
            }
            return '<label class="label label-' . $label . '">' . $str . '</label>';
      }

      public static function getLiquidationLabel($d)
      {
            $liquid_str = 'No';
            $liquid_design = 'label label-danger';
            if ($d > 0) {
                  $liquid_design = "label label-success";
                  $liquid_str = 'Yes';
            }
            return "<label class='label label-" . $liquid_design . "'>" . $liquid_str . "</label>";
      }

      public static function ajaxResponseTrue($msg, $data = null, $custom_data = null, $unset = TRUE, AjaxResponse &$resp = NULL)
      {
            if (!$resp) {
                  $resp = new AjaxResponse(TRUE);
            } else {
                  $resp->setSuccess(TRUE);
            }

            ScreenMessage::setMessage($msg, ScreenMessage::MESSAGE_TYPE_SUCCESS);
            if (isset($data)) {
                  $resp->setData($data);
            } else {
                  $resp->setData("");
            }
            if (isset($custom_data)) {
                  $resp->setCustomData($custom_data);
            }
            if ($unset) {
                  $resp->setScreenMessages(ScreenMessage::getMessages());
            }



            echo $resp->getOutput();
            exit();
      }

      public static function ajaxResponseFalseWithJsonSend($msg)
      {
            ScreenMessage::setMessage($msg, ScreenMessage::MESSAGE_TYPE_ERROR);
            $tpl = new Template(SiteConfig::templatesPath() . "inner/screen-messages");
            $tpl->messages = ScreenMessage::getMessages();
            echo json_encode(array("success" => false, 'screen_message' => $tpl->parse()));
            exit();
      }

      public static function ajaxResponseFalse($msg, $data = null, $custom_data = NULL, AjaxResponse &$resp = NULL)
      {
            if (!$resp) {
                  $resp = new AjaxResponse(FALSE);
            } else {
                  $resp->setSuccess(FALSE);
            }

            ScreenMessage::setMessage($msg, ScreenMessage::MESSAGE_TYPE_ERROR);
            if (isset($data)) {
                  $resp->setData($data);
            } else {
                  $resp->setData("");
            }
            if (isset($custom_data)) {
                  $resp->setCustomData($custom_data);
            }
            $resp->setScreenMessages(ScreenMessage::getMessages());
            echo $resp->getOutput();
            exit();
      }

      public static function GenerateBarcode($str, $folder_name)
      {
            $filename = sha1($str) . ".png";
            if (!file_exists(BaseConfig::FILES_DIR . "$folder_name" . "/" . $filename)) {
                  $type = 'code128';
                  $imgtype = 'png';
                  $bSendToBrowser = FALSE;
                  $height = 60;
                  $width = 2;
                  $img = Image_Barcode2::draw($str, $type, $imgtype, $bSendToBrowser, $height, $width);
                  file_put_contents(BaseConfig::FILES_DIR . "$folder_name" . "/" . $filename, "");
                  imagepng($img, BaseConfig::FILES_DIR . "$folder_name" . "/" . $filename);
            }

            return ("<img class='img-responsive' src= " . Utility::ifImageExists($folder_name . "/", $filename) . ">");
      }

      /**
       * get Contated Price with currency symbol
       * 
       * @param String $amount Amount
       * @param Boolean $applySpace Optional wheather to apply space between currency and amount or not
       * 
       * @author Mayur
       */
      public static function getconcatenatedCurrencyPrice($amount, $applySpace = true)
      {
            $price_currency_concate = ($applySpace) ? Utility::variableGet('CURRENCY_SYMBOL') . ' ' . $amount : Utility::variableGet('CURRENCY_SYMBOL') . '' . $amount;
            return $price_currency_concate;
      }

      public static function ifImageExists($file_path, $image_name, $absolute = FALSE, $op = 0)
      {

            if ($file_path && $image_name && file_exists(BaseConfig::FILES_DIR . $file_path . $image_name)) {
                  if ($absolute && !BaseConfig::FILES_URL_RELATIVE) {
                        return BaseConfig::$domain_name . BaseConfig::FILES_URL . $file_path . $image_name;
                  }
                  return BaseConfig::FILES_URL . $file_path . $image_name;
            }
            if ($absolute && !BaseConfig::FILES_URL_RELATIVE) {
                  return BaseConfig::$domain_name . BaseConfig::FILES_URL . "no-image.jpg";
            }
            if ($op == 0) {
                  return BaseConfig::FILES_URL . "no-image.jpg";
            } else {
                  return BaseConfig::FILES_URL . "no-image.png";
            }
      }

      public static function deleteImage($file_path, $image_name)
      {
            if (!isset($image_name) || !isset($file_path) || !file_exists(BaseConfig::FILES_DIR . $file_path . $image_name)) {
                  return false;
            }
            return unlink(BaseConfig::FILES_DIR . $file_path . $image_name);
      }

      /**
       * cut short the long description if it is more then 60 characters
       */
      public static function subStrDescription($string)
      {
            return (strlen($string) > 60) ? substr($string, 0, 60) . "..." : $string;
      }

      /**
       * Genrate ticket id on the basis of the id and date
       * 
       * @param Integer $id id of the ticket
       * @param Date $date date on which the ticket was inserted into the database
       */
      public static function generateTicket($id, $date)
      {
            $formated_date = date("Ymd", strtotime($date));
            $id = str_pad($id, 6, 0, STR_PAD_LEFT);
            return "AGT" . $formated_date . $id;
      }

      public static function permissionDenied()
      {
            $resp = new AjaxResponse(false);
            $resp->setData("403, Permission Denied");
            ScreenMessage::setMessage("Error 403, Permission Denied", ScreenMessage::MESSAGE_TYPE_ERROR);
            $resp->setScreenMessages(ScreenMessage::getMessages());
            echo $resp->getOutput();
            exit();
      }

      public static function pickup_today()
      {
            $pick_up_details = array();
            $current_date = new DateTime();
            $hstslid_array = HubServiceTypeSlotManager::getAvailableServiceTypeSlots();
            if ($hstslid_array) {
                  $pick_up_details["today"] = true;
                  $pick_up_details["hstslids"] = implode(",", array_keys($hstslid_array));
                  $pick_up_details["pickup_start_date"] = $current_date->format('m/d/Y H:i:s');
                  return $pick_up_details;
            }
            $pick_up_details["today"] = false;
            $pick_up_details["hstslids"] = '';
            $pick_up_details["pickup_start_date"] = $current_date->modify(" +1 day")->format('m/d/Y H:i:s');
            return $pick_up_details;
      }

      /**
       * Get The HasTag URL with or without href
       *
       * @author Sohil Gupta
       * @since 20150714
       * @todo Need to check for permissions for the connected users
       */
      public static function getHashTagURL($tag)
      {
            $module = ModuleManager::getModuleByCode(substr($tag, 4, 2));

            if (!$module) {
                  return $tag;
            }
            //            if (!hasPermission($module->pid))
            //            {
            //                return $tag;
            //            }
            return '<a target="_blank"  href="' . JPath::fullUrl($module->url . "/" . intval(substr($tag, 6))) . '">' . $tag . '</a>';
      }

      /**
       * Get The HasTag URL for Customer or Admin with or without href
       *
       * @author Sohil Gupta
       * @since 20150714
       * @todo Need to check for permissions for the connected users
       */
      public static function getCustHashTagURL($tag)
      {
            if (substr($tag, 4, 2) === 'AU') {

                  if (!AdminUser::isExistent(substr($tag, 6))) {
                        return '<span style="text-decoration: line-through;">' . $tag . '</span>';
                  }
                  $user = new AdminUser(substr($tag, 6));
                  //                if (!hasPermission(USER_PERMISSION_ADMINUSER_VIEW))
                  //                {
                  //                    return "@" . $user->getName();
                  //                }
                  return '<a target="_blank" href="' . JPath::fullUrl("admin_user/user/view/" . $user->getId()) . '"> @' . $user->getName() . '</a>';
            } elseif (substr($tag, 4, 2) === 'CU') {
                  if (!Customer::isExistent(substr($tag, 6))) {
                        return '<span style="text-decoration: line-through;">' . $tag . '</span>';
                  }
                  $customer = new Customer(substr($tag, 6));
                  //                if (!hasPermission(USER_PERMISSION_CUSTOMER_USER_VIEW_DETAILS))
                  //                {
                  //                    return "@" . $customer->getFname() . "" . $customer->getLname();
                  //                }
                  return '<a target="_blank" href="' . JPath::fullUrl("customer/view/" . $customer->getId()) . '">  @ ' . $customer->getFname() . "" . $customer->getLname() . '</a>';
            }

            return $tag;
      }

      /**
       * Get time difference
       *
       * @author Sohil Gupta
       * @since 20150714
       */
      public static function get_time_difference_php($created_time)
      {
            date_default_timezone_set('Asia/Kolkata'); //Change as per your default time
            $str = strtotime($created_time);
            $today = strtotime(date('Y-m-d H:i:s'));

            // It returns the time difference in Seconds...
            $time_differnce = $today - $str;

            // To Calculate the time difference in Years...
            $years = 60 * 60 * 24 * 365;

            // To Calculate the time difference in Months...
            $months = 60 * 60 * 24 * 30;

            // To Calculate the time difference in Days...
            $days = 60 * 60 * 24;

            // To Calculate the time difference in Hours...
            $hours = 60 * 60;

            // To Calculate the time difference in Minutes...
            $minutes = 60;

            if (intval($time_differnce / $years) > 1) {
                  return intval($time_differnce / $years) . " years ago";
            } else if (intval($time_differnce / $years) > 0) {
                  return intval($time_differnce / $years) . " year ago";
            } else if (intval($time_differnce / $months) > 1) {
                  return intval($time_differnce / $months) . " months ago";
            } else if (intval(($time_differnce / $months)) > 0) {
                  return intval(($time_differnce / $months)) . " month ago";
            } else if (intval(($time_differnce / $days)) > 1) {
                  return intval(($time_differnce / $days)) . " days ago";
            } else if (intval(($time_differnce / $days)) > 0) {
                  return intval(($time_differnce / $days)) . " day ago";
            } else if (intval(($time_differnce / $hours)) > 1) {
                  return intval(($time_differnce / $hours)) . " hours ago";
            } else if (intval(($time_differnce / $hours)) > 0) {
                  return intval(($time_differnce / $hours)) . " hour ago";
            } else if (intval(($time_differnce / $minutes)) > 1) {
                  return intval(($time_differnce / $minutes)) . " minutes ago";
            } else if (intval(($time_differnce / $minutes)) > 0) {
                  return intval(($time_differnce / $minutes)) . " minute ago";
            } else if (intval(($time_differnce)) > 1) {
                  return intval(($time_differnce)) . " seconds ago";
            } else {
                  return "few seconds ago";
            }
      }

      public static function getWeekStartAndEnd($past_weeks)
      {
            $relative_time = time();
            $weeksarr = array();
            $start = '';
            $end = '';
            $weeks = array();
            for ($week_count = 0; $week_count < $past_weeks; $week_count++) {
                  $monday = strtotime("last Monday", $relative_time);
                  $sunday = strtotime("Sunday", $monday);
                  array_push($weeks, date("Y-m-d", $monday));

                  if ($week_count == 0) {
                        $end = date("Y-m-d", $sunday);
                  }

                  if ($week_count == $past_weeks - 1) {
                        $start = date("Y-m-d", $monday);
                  }

                  $weeksarr[] = array(date("Y-m-d", $monday), date("Y-m-d", $sunday));
                  $relative_time = $monday;
            }
            return array("weeks" => $weeks, "start" => $start, "end" => $end, "weekDetails" => $weeksarr);
      }

      /**
       * Ajax false response.
       * @param type $data
       * @param type $message
       */
      public static function failureResponse($data, $message)
      {
            $resp = new AjaxResponse(false);
            $resp->setData($data);
            ScreenMessage::setMessage($message, ScreenMessage::MESSAGE_TYPE_ERROR);
            $resp->setScreenMessages(ScreenMessage::getMessages());
            echo $resp->getOutput();
            exit();
      }

      /**
       * Ajax Success Response.
       * @param type $data
       * @param type $message
       */
      public static function successResponse($data, $message)
      {
            $resp = new AjaxResponse(true);
            $resp->setData($data);
            ScreenMessage::setMessage($message, ScreenMessage::MESSAGE_TYPE_SUCCESS);
            $resp->setScreenMessages(ScreenMessage::getMessages());
            echo $resp->getOutput();
            exit();
      }

      public static function getCurrencyFormat($n, $round_off = FALSE)
      {
            // first strip any formatting;
            $n = (0 + str_replace(",", "", $n));

            if (!is_numeric($n)) {
                  return false;
            }
            if ($n > 10000000) return round(($n / 10000000), 1) . ' Cr';
            else if ($n > 100000) return round(($n / 100000), 1) . ' Lac';
            else if ($n > 1000) return round(($n / 1000), 1) . ' Th';
            return ($round_off) ? number_format_custom($n, 2) : number_format_custom($n);
      }

      public static function getNumberCurrencyFormat($id)
      {
            $num = str_replace(",", "", trim($id));
            return number_format_custom(is_numeric($num) ? $num : 0, 2);
      }

      public static function getBalanceCurrencyFormat($id)
      {
            if ($id < 0) {
                  $num = str_replace(",", "", abs(trim($id)));
                  return number_format_custom(is_numeric($num) ? $num : 0, 2) . ' Cr';
            } else {
                  $num = str_replace(",", "", trim($id));
                  return number_format_custom(is_numeric($num) ? $num : 0, 2) . ' Dr';
            }
      }

      public static function getDiscountFormat($id, $row = NULL, $row_id = NULL)
      {
            switch (isset($row["discount_type"]) ? $row["discount_type"] : SystemTablesStatus::DISCOUNT_TYPE_PERCENT) {
                  case SystemTablesStatus::DISCOUNT_TYPE_PERCENT:
                  default:
                        $discount = round(isset($row["discount_percent"]) ? $row["discount_percent"] : $row["discount"], 2) . ' <i class="fa fa-percent small"></i> ';
                        break;
                  case SystemTablesStatus::DISCOUNT_TYPE_INR:
                        $discount = '<strong>&#x20b9;</strong> ' . round($row["discount_amount"], 2);
                        break;
            }
            return $discount;
      }

      public static function getColumnPercentageFormat($id, $row = NULL, $row_id = NULL)
      {
            $num = str_replace(",", "", trim($id));
            $str = number_format_custom(is_numeric($num) ? $num : 0, 2);
            return $str . ' <i class="fa fa-fw fa-percent small"></i>';
      }

      public static function getExcelColumnPercentageFormat($id, $row = NULL, $row_id = NULL)
      {
            $num = str_replace(",", "", trim($id));
            $str = number_format_custom(is_numeric($num) ? $num : 0, 2);
            return $str;
      }

      public static function getExcelColumnDateFormat($date, $row = NULL, $row_id = NULL)
      {
            if (!$date || is_null($date) || $date === "0000-00-00" || $date === "0000-00-00 00:00:00" || strtotime($date) <= 0) {
                  return "NA";
            }
            return date('d M, Y', strtotime($date));
      }

      public static function getColumnDateFormat($date, $row = NULL, $row_id = NULL)
      {
            if (!$date || is_null($date) || $date === "0000-00-00" || $date === "0000-00-00 00:00:00" || strtotime($date) <= 0) {
                  return "NA";
            }
            return str_replace(' ', ' ', date('d M, Y', strtotime($date)));
      }

      public static function getColumnYearMonthFormat($date, $row = NULL, $row_id = NULL)
      {
            if (!$date || is_null($date) || $date === "0000-00-00") {
                  return "NA";
            }
            return date('M Y', strtotime($date));
      }

      public static function getColumnYearMonthFormatText($date, $row = NULL, $row_id = NULL)
      {
            if (!$date || is_null($date) || $date === "0000-00-00") {
                  return "NA";
            }
            return date('y/M', strtotime($date));
      }

      public static function getColumnMonthYearFormatText($date, $row = NULL, $row_id = NULL)
      {
            if (!$date || is_null($date) || $date === "0000-00-00") {
                  return "NA";
            }
            return date('M/y', strtotime($date));
      }

      public static function getColumnTimeFormat($date, $row = NULL, $row_id = NULL)
      {
            if (!$date || is_null($date) || $date === "0000-00-00 00:00:00" || $date === '0000-00-00') {
                  return "NA";
            }
            return date('h:i A, d M Y ', strtotime($date));
      }

      public static function getColumnCurrencyFormat($id, $row = NULL, $row_id = NULL)
      {
            $num = str_replace(",", "", trim($id));
            $str = number_format_custom(is_numeric($num) ? $num : 0, 2);
            if (getSettings('HIDE_CURRENCY_SYMBOL')) {
                  return $str;
            }
            return '&#x20b9;&nbsp;' . $str;
      }

      public static function getColumnDrCrCurrencyFormat($id, $row = NULL, $row_id = NULL)
      {

            $num = str_replace(",", "", trim($id));
            $str = number_format_dr_cr_custom(is_numeric($num) ? $num : 0, 2);
            return '&#x20b9; ' . $str;
      }

      public static function getColumnMultiCurrencyFormat($id, $row = NULL, $row_id = NULL)
      {
            if ($row['currency_bit']) {
                  if ($row['cur_conversion_rate'] > 0) {
                        if (isset(Utility::$cur_array[$row['curid']])) {
                              $cur = Utility::$cur_array[$row['curid']];
                        } else {
                              $cur = CurrencyManager::getCurrenciesValue($row['curid']);
                              Utility::$cur_array[$row['curid']] = $cur;
                        }

                        $num = str_replace(",", "", trim($id)) / $row['cur_conversion_rate'];
                        $str = number_format_custom(is_numeric($num) ? $num : 0, 2);
                        return $cur->currency_symbol . '&nbsp;' . $str;
                  } else {
                        return 'NA';
                  }
                  $num = str_replace(",", "", trim($id));
                  $str = number_format_custom(is_numeric($num) ? $num : 0, 2);
                  return '&#x20b9;&nbsp;' . $str;
            } else {
                  $num = str_replace(",", "", trim($id));
                  $str = number_format_custom(is_numeric($num) ? $num : 0, 2);
                  return '&#x20b9;&nbsp;' . $str;
            }
      }

      public static function getColumnCurrencyFormatRoundOff($id)
      {
            $num = str_replace(",", "", trim($id));
            $str = round(is_numeric($num) ? $num : 0, 2);
            if (getSettings('HIDE_CURRENCY_SYMBOL')) {
                  return $str;
            }
            return '&#x20b9;&nbsp;' . $str;
      }

      public static function getExcelColumnCurrencyFormatRoundOff($id)
      {
            $num = str_replace(",", "", trim($id));
            $str = round(is_numeric($num) ? $num : 0, 2);
            return $str;
      }

      public static function getTextCurrencyFormat($id, $currency_symbol = "")
      {

            $num = str_replace(",", "", trim($id));
            $str = number_format_custom(is_numeric($num) ? $num : 0, 2);
            if (!$currency_symbol) {
                  return '&#x20b9;&nbsp;' . $str;
            }
            if ($currency_symbol && !is_numeric($currency_symbol)) {
                  return $currency_symbol . ' ' . $str;
            }
            return '&#x20b9;&nbsp;' . $str;
      }

      public static function getServicePoint($id, $option)
      {

            if ($option == 'service_point') {
                  if ($id == 1) {
                        return '<label class="label label-success"> In-house </label> ';
                  }
                  if ($id == 2) {
                        return '<label class="label label-primary"> Manufacturer </label> ';
                  }
                  if ($id == 3) {
                        return '<label class="label label-default"> Outsourcing </label> ';
                  }
            } else {
                  if ($id == 1) {
                        return '<label class="label label-success"> Pickup drop </label> ';
                  }
                  if ($id == 2) {
                        return '<label class="label label-primary"> Onsite </label> ';
                  }
                  if ($id == 3) {
                        return '<label class="label label-default"> Customer drop in </label> ';
                  }
            }
      }

      public static function getServicePointText($id, $option)
      {

            if ($option == 'service_point') {
                  if ($id == 1) {
                        return 'In-house ';
                  }
                  if ($id == 2) {
                        return ' Manufacturer  ';
                  }
                  if ($id == 3) {
                        return ' Outsourcing  ';
                  }
            } else {
                  if ($id == 1) {
                        return ' Pickup drop  ';
                  }
                  if ($id == 2) {
                        return ' Customer location  ';
                  }
                  if ($id == 3) {
                        return ' Customer drop  ';
                  }
            }
      }

      public static function getTimeFormat($d, $row = NULL, $row_id = NULL)
      {
            if ($d !== NULL && $d !== '0000-00-00 00:00:00') {
                  if (getSettings("IS_DATE_FORMAT_SHOW")) {
                        return date('h:i A - d M, Y ', strtotime($d));
                  } else {
                        return '<span  title="' . date('h:i A - d M, Y ', strtotime($d)) . '" style="cursor:help;"><i class="fa fa-clock-o"></i>&nbsp;' . get_time_difference_php($d) . '</span>';
                  }
            }
            return 'NA';
      }

      public static function getDateFormat($d, $row = NULL, $row_id = NULL)
      {
            if ($d !== NULL && $d != '' && $d !== '0000-00-00') {
                  if (getSettings("IS_DATE_FORMAT_SHOW")) {
                        return date('d M, Y ', strtotime($d));
                  } else {
                        return '<span  title="' . date('d M, Y ', strtotime($d)) . '" style="cursor:help;"><i class="fa fa-clock-o"></i>&nbsp;' . get_date_difference_php($d) . '</span>';
                  }
            }
            return 'NA';
      }

      public static function getDateFormatExcel($d, $row = NULL, $row_id = NULL)
      {
            if ($d !== NULL && $d != '' && $d !== '0000-00-00') {
                  if (getSettings("IS_DATE_FORMAT_SHOW")) {
                        return date('d M, Y ', strtotime($d));
                  } else {
                        return get_date_difference_php($d);
                  }
            }
            return 'NA';
      }

      public static function getDateFormatExcels($d, $row = NULL, $row_id = NULL)
      {
            if ($d !== NULL && $d != '' && $d !== '0000-00-00') {
                  if (getSettings("IS_DATE_FORMAT_SHOW")) {
                        return date('d M, Y ', strtotime($d));
                  } else {
                        return get_date_difference_php($d);
                  }
            }
            return '';
      }

      public static function getIpAddress()
      {
            $ipaddress = '';

            if (isset($_SERVER['HTTP_CLIENT_IP'])) {
                  $ipaddress = $_SERVER['HTTP_CLIENT_IP'];
            } else if (isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
                  $ipaddress = $_SERVER['HTTP_X_FORWARDED_FOR'];
            } else if (isset($_SERVER['HTTP_X_FORWARDED'])) {
                  $ipaddress = $_SERVER['HTTP_X_FORWARDED'];
            } else if (isset($_SERVER['HTTP_FORWARDED_FOR'])) {
                  $ipaddress = $_SERVER['HTTP_FORWARDED_FOR'];
            } else if (isset($_SERVER['HTTP_FORWARDED'])) {
                  $ipaddress = $_SERVER['HTTP_FORWARDED'];
            } else if (isset($_SERVER['REMOTE_ADDR'])) {
                  $ipaddress = $_SERVER['REMOTE_ADDR'];
            }

            if (!filter_var($ipaddress, FILTER_VALIDATE_IP)) {
                  $ipaddress = 'UNKNOWN';
            }

            return $ipaddress;
      }

      public static function informNodeServer($data, $path)
      {
            $curl = curl_init();
            curl_setopt($curl, CURLOPT_TIMEOUT, 30);
            curl_setopt($curl, CURLOPT_POST, 1);
            curl_setopt($curl, CURLOPT_URL, BaseConfig::NOTIFICATION_SERVER . $path);
            curl_setopt($curl, CURLOPT_POSTFIELDS, http_build_query($data));
            curl_exec($curl);
            curl_close($curl);
      }

      public static function requestMasterServer($service, $data = NULL)
      {
            $url = ServerConfig::getServerUrl("services");
            $post_data = array(
                  "service" => $service,
                  "supportUserEmail" => Utility::variableGet("support_username"),
                  "supportUserPassword" => Utility::variableGet("support_password"),
                  "data" => is_array($data) ? json_encode($data) : ""
            );

            $curl = curl_init($url);
            curl_setopt($curl, CURLOPT_TIMEOUT, 30);
            curl_setopt($curl, CURLOPT_POST, 1);
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($curl, CURLOPT_POSTFIELDS, $post_data);
            $response = curl_exec($curl);
            curl_close($curl);

            return json_decode($response, TRUE);
      }

      public static function getDateFromDateFormat($id, $row = NULL, $row_id = NULL)
      {
            if ($id === '' || $id === '0000-00-00') {
                  return '';
            }
            return date('d M Y', strtotime($id . ' 00:00:00'));
      }

      public static function getQtyNumberFormat($d, $row = NULL, $row_id = null)
      {
            if ($d <= 0 || !is_numeric($d)) {
                  return 0;
            }
            $str = round($d, 3);
            if (isset($row['show_package_qty']) && $row['show_package_qty'] > 0 && isset($row['variation_id']) > 0 && isset($row['unit_id']) && $row['unit_id'] > 0) {
                  $variation = new InventorySetVariation($row['isvid']);
                  if ($variation->getPackageQuantity() > 0 && $variation->getPackageMeaid() > 0) {
                        $str .= self::getBoxQtyFormat($variation->getPackageQuantity(), $variation->getCPackageMeasurement(), $d, $row['unit_id']);
                  }
            }
            return $str;
      }

      public static function getAllocatedItems($d, $row, $row_id)
      {
            $str = round($d, 3);
            if (isset($row['show_package_qty']) && $row['show_package_qty'] > 0 && isset($row['variation_id']) > 0 && isset($row['unit_id']) && $row['unit_id'] > 0) {
                  $variation = new InventorySetVariation($row['isvid']);
                  if ($variation->getPackageQuantity() > 0 && $variation->getPackageMeaid() > 0) {
                        $str .= self::getBoxQtyFormat($variation->getPackageQuantity(), $variation->getCPackageMeasurement(), $d, $row['unit_id']);
                  }
            }
            return '<a class="item-show-stock" title="Show Stock Details" data-batch="' . $row['batch'] . '" data-section="' . $row['waseid'] . '" data-chkid="' . $row['chkid'] . '" data-id="' . $row['variation_id'] . '">' . $str . '</a>';
      }

      public static function getThresholdNumberFormat($d)
      {
            return number_format_custom($d, 2);
      }

      public static function getMeasurementFormat($quantity)
      {
            $q = explode(" ", $quantity);
            $num = number_format_custom($q[0], 2);
            return $num . " " . $q[1];
      }

      public static function getPriceFormat($id)
      {
            if ($id == "-" || $id == "") {
                  return 0.00;
            }
            if (abs($id) > 0) {
                  return number_format_custom($id, 2);
            }
            return 0.00;
      }

      public static function getRoundFormat($id)
      {
            if (abs($id) > 0) {
                  return round($id, 2);
            }
            return 0.00;
      }

      public static function replaceCompanyName()
      {
            return Utility::variableGet('company_name');
      }

      public static function getLastMaxId($number = null, $table_name = null, $extra_condition = "")
      {
            $db = Rapidkart::getInstance()->getDB();

            $sql = "SELECT MAX($number) as $number FROM $table_name WHERE company_id = " . BaseConfig::$company_id . " ";
            if ($extra_condition) {
                  $sql .= $extra_condition;
            }
            $res = $db->query($sql);
            if (!$res || $db->resultNumRows($res) < 1) {
                  return 0;
            }
            $row = $db->fetchObject($res);
            if ($row->$number == '' || $row->$number == NULL || empty($row->$number)) {
                  return 0;
            }

            return $row->$number;
      }

      public static function getOrderMeasurementFormat($id, $row, $row_id)
      {
            if ($id <= 0) {
                  if (!isset($row['show_negative'])) {
                        return 0;
                  }
            }
            return round($id / (isset($row['c_conversion_rate']) && $row['c_conversion_rate'] > 0 ? $row['c_conversion_rate'] : 1), 4) . " " . $row['c_measurement'];
      }

      public static function getQtyMeasurementFormat($id, $row, $row_id = 0, $bit = 0)
      {

            if ($id <= 0 || $id == "" || floatval($id) <= 0) {
                  if (!isset($row['show_negative'])) {
                        return 0;
                  }
            }
            if (abs($id) == 0) {
                  return 0;
            }
            $qty = round($id / (isset($row['c_conversion_rate']) && $row['c_conversion_rate'] > 0 ? $row['c_conversion_rate'] : 1), 4);

            $box_qty_append = '';
            $box_qty = '';
            if (!empty($row['boxsdfss']) && $row['boxsdfss'] == 1) {
                  $variation = new InventorySetVariation($row['isvid']);
                  $measurement = new Measurement($variation->getMeasuredUnit());
                  if ($id > 0 && $variation->getMeasuredQty() > 0 && $measurement->getConversionRate() > 0) {
                        $box_qty = round((($id) * $variation->getMeasuredQty()) / $measurement->getConversionRate(), 4);
                  }

                  if ($box_qty > 0) {
                        $box_qty = " (" . $box_qty . "  " . $measurement->getName() . ") ";
                  } else {
                        $box_qty = '';
                  }
            }

            if (isset($row['selected_type']) && isset($row['selected_meaid']) && isset($row['meaid']) && $row['selected_type'] > 0 && $row['selected_meaid'] > 0 && $row['meaid'] > 0 && $row['selected_meaid'] != $row['meaid'] && $row['selected_type'] == 2 && isset($row['selected_package_qty']) && $row['selected_package_qty'] > 0) {
                  $measurement = new Measurement($row['selected_meaid']);
                  $box_qty_append = self::getBoxQtyFormat($row['selected_package_qty'], $measurement->getName(), $qty, $row['meaid']);
            } else if (isset($row['selected_type']) && isset($row['selected_meaid']) && isset($row['meaid']) && $row['selected_type'] > 0 && $row['selected_meaid'] > 0 && $row['meaid'] > 0 && $row['selected_meaid'] != $row['meaid'] && $row['selected_type'] == 2 && isset($row['variant'])) {
                  $class_name = get_class($row['variant']);
                  if ($class_name == "InventorySetVariation" && $row['variant']->getPackageQuantity() != NULL && $row['variant']->getPackageQuantity() > 0) {
                        $measurement = new Measurement($row['selected_meaid']);
                        $box_qty_append = self::getBoxQtyFormat($row['variant']->getPackageQuantity(), $measurement->getName(), $qty, $row['meaid']);
                  }
            } else if (isset($row['variation_id']) && isset($row['show_package_qty']) && $row['show_package_qty'] == 1) {
                  $variation = new InventorySetVariation($row['variation_id']);
                  $box_qty_append = self::getBoxQtyFormat($variation->getPackageQuantity(), $variation->getCPackageMeasurement(), $qty, $variation->getMeaid(), $variation->getCMeasurement());
            }
            if (getSettings("IS_SELECTED_QTY_UNIT_SHOW_IN_DETAILED_VIEW") && isset($row['selected_qty']) && $row['selected_qty'] > 0 && $bit == 1 && isset($row['selected_meaid'])) {
                  $measurement = new Measurement($row['selected_meaid']);
                  return self::getQtyDisplayFormat(round($row['selected_qty'], 1)) . " " . $measurement->getName() . " " . $box_qty;
            } else {
                  return self::getQtyDisplayFormat($qty) . " " . ((isset($row['c_measurement_unit']) && strlen($row['c_measurement_unit']) > 0) ? $row['c_measurement_unit'] : "PCS") . $box_qty_append . " " . $box_qty;
            }
      }

      public static function getBoxQtyFormat($box_qty, $box_qty_unit, $qty, $meaid, $meaid_name = "")
      {
            if (abs($qty) == 0 || $box_qty <= 0) {
                  return '';
            }
            if (intval(abs($qty)) == 0 || intval($box_qty) <= 0) {
                  return '';
            }
            $str = ' (';
            $box_qty_value = floor($qty / $box_qty);
            $str .= $box_qty_value . ' ' . $box_qty_unit;

            $modulus = fmod($qty, $box_qty);
            if ($modulus > 0) {
                  $m_name = $meaid_name;
                  if (strlen($meaid_name) <= 0) {
                        $m = new Measurement($meaid);
                        $m_name = $m->getName();
                  }
                  $str .= ' ' . round($modulus, 0) . ' ' . $m_name;
            }
            $str .= ')';
            return $str;
      }

      public static function getQtyWithPackageQty($id, $row)
      {
            $qty_explode = explode(" ", $id);
            $qty = $qty_explode[0];
            $unit = (isset($qty_explode[1]) ? $qty_explode[1] : "");
            $str = $qty . " " . $unit;
            if (isset($row['show_package_qty']) && $row['show_package_qty'] > 0 && isset($row['variation_id']) > 0 && isset($row['unit_meaid']) && $row['unit_meaid'] > 0) {
                  $variation = new InventorySetVariation($row['variation_id']);
                  if ($variation->getPackageQuantity() > 0 && $variation->getPackageMeaid() > 0) {
                        $str .= self::getBoxQtyFormat($variation->getPackageQuantity(), $variation->getCPackageMeasurement(), $qty, $row['unit_meaid']);
                  }
            }

            return $str;
      }

      public static function getQtyFormat($id, $row, $row_id, $asLink = false)
      {
            if (!is_bool($asLink)) {
                  $asLink = false;
            }
            if ($id <= 0) {
                  if (!isset($row['show_negative'])) {
                        return 0;
                  }
            }
            $qty = round($id / (isset($row['c_conversion_rate']) && $row['c_conversion_rate'] > 0 ? $row['c_conversion_rate'] : 1), 4);
            $str = $qty . " " . (isset($row['c_measurement_unit']) ? $row['c_measurement_unit'] : "");
            if (isset($row['show_package_qty']) && $row['show_package_qty'] > 0 && isset($row['variation_id']) > 0 && isset($row['unit_id']) && $row['unit_id'] > 0) {
                  $variation = new InventorySetVariation($row['isvid']);
                  if ($variation->getPackageQuantity() > 0 && $variation->getPackageMeaid() > 0) {
                        $str .= self::getBoxQtyFormat($variation->getPackageQuantity(), $variation->getCPackageMeasurement(), $qty, $row['unit_id']);
                  }
            }
            if ($asLink && isset($row['isvid'])) {
                  return "<a href='javascript:void(0)' class='inventory-orderwise-details cursor-pointer' data-bit='1' data-id='"
                        . $row['isvid'] . "'>"
                        . $str .
                        "</a>";
            }



            return $str;
      }

      public static function getJsonDecodeData($id)
      {
            $data = json_decode($id, true);
            if (isset($data[0]['remarks'])) {
                  return $data[0]['remarks'];
            }
            return '';
      }

      public static function thousandToLakhsConversion($amount)
      {
            //1T = 0.01L
            $converted_amount = 0;
            if ($amount > 0) {
                  $converted_amount = $amount / 100000;
            }
            //
            //
            return round($converted_amount, 4);
      }

      public static function getRoundOffedAmount($d)
      {
            return round($d);
      }

      public static function getRoundOffAmountWithMoneyFormat($id)
      {
            return (round($id, 0));
      }

      public static function getJsonDecodeDatas($id)
      {
            $data = json_decode($id, true);
            $cat = "";
            if (!empty($data) && count($data) > 0) {
                  $cat = implode(", ", $data);
            }


            return $cat;
      }

      public static function getJsonDecodeDatasId($id)
      {

            //  $id = '[{"id":"3900187"},{"id":"1000058"},{"id":"1000059"}]';

            $data = json_decode($id, true);
            $cat = "";
            if (is_array($data) && !empty($data)) {
                  foreach ($data as $key => $value) {
                        reset($value);
                        // hprint($value['id']);
                        $cat .= $value['id'] . ",";
                  }
            }
            $cats = rtrim($cat, ",");

            return $cats;
      }

      public static function getAmountinWords($amount, $round_off_disable = 0)
      {
            include_once '/usr/share/php/Numbers/Words.php';
            $str = "";
            $rupee = floor($amount);
            $paise = round(($amount - $rupee) * 100, 2); // and set pricision two
            $method_name = "";
            if (is_callable(array('Numbers_Words', 'toWords'))) {

                  $text = ucwords(Numbers_Words::toWords(round($amount), 'en_IN'));
                  if ($round_off_disable) {
                        $text = ucwords(Numbers_Words::toWords(round($amount, 2), 'en_IN'));
                  }
                  $price_in_paise = ucwords(Numbers_Words::toWords(round($paise), 'en_IN'));
            } else {
                  $no_words = new Numbers_Words();
                  $text = ucwords($no_words->toWords(round($amount), 'en_IN'));
                  if ($round_off_disable) {
                        $text = ucwords($no_words->toWords(round($amount, 2), 'en_IN'));
                  }
                  $price_in_paise = ucwords($no_words->toWords(round($paise), 'en_IN'));
            }
            $str = $text;
            if ($paise > 0) {
                  $str .= " Rupees and " . $price_in_paise . " Paise";
            }
            $str .= " Only";
            return $str;
      }

      public static function getTimeFormatCallback($d, $row = NULL, $row_id = NULL)
      {
            if ($d !== NULL && $d !== '0000-00-00 00:00:00') {
                  return '<span  title="' . date('g:i a', strtotime($d)) . '" style="cursor:help;"><i class="fa "></i>&nbsp;' . date('g:i a', strtotime($d)) . '</span>';
            }
            return 'NA';
      }

      public static function replaceLink()
      {
            return "link";
      }

      public static function returnDateFormat($date)
      {
            if (strlen($date) > 0 && $date != "") {
                  $date_explode = explode(" ", $date);
                  if ($date_explode) {
                        if (is_array($date_explode) && !empty($date_explode)) {
                              $date_f = explode("/", $date_explode[0]);
                              $date_str = $date_f[2] . "-" . $date_f[1] . "-" . substr($date_f[0], -2);
                              $date_val = date('Y-m-d', strtotime($date_str));
                              return $date_val;
                        }
                  }
            }
            return $date;
      }

      public static function returnBrandFormat($data)
      {
            if ($data == 1) return "Philips";
            if ($data == 2) return "Osram";
            if ($data == 3) return "Eveready";
            if ($data == 4) return "Wipro";
            if ($data == 5) return "Oreva";
            if ($data == 6) return "Bajaj";
            if ($data == 7) return "SYSKA";
            if ($data == 8) return "Surya";
            if ($data == 9) return "Crompton";
            if ($data == 10) return "Charlston";
      }

      public static function returnShapeFormat($data)
      {

            if ($data == 1) return "Square";
            if ($data == 2) return "Rectangle";
            if ($data == 3) return "Round";
            if ($data == 4) return "Other";
      }

      public static function returnSizeFormat($data)
      {

            if ($data == 1) return "Small";
            if ($data == 2) return "Medium";
            if ($data == 3) return "Large";
            if ($data == 4) return "Extra large";
      }

      public static function getPartName($part_id)
      {
            $db = Rapidkart::getInstance()->getDB();
            $sql = "SELECT part_name FROM spareparts WHERE part_id=" . $part_id;
            $res = $db->query($sql);

            if (!$res || $db->resultNumRows($res) < 1) {
                  return 0;
            }
            $row = $db->fetchObject($res);
            if ($row->part_name == '' || $row->part_name == NULL || empty($row->part_name)) {
                  return 0;
            }

            return $row->part_name;
      }

      public static function getValidityLabel($validity)
      {
            if ($validity == "Not available") {
                  return "<label class='label label-danger'>" . $validity;
            } else {
                  return "<label class='label label-success'>" . $validity;
            }
      }

      public static function getnetsalemonthformat($month)
      {
            $month = explode(" ", $month);
            return date('M', strtotime($month[0] . "-" . $month[1] . "-01")) . " " . $month[0];
      }

      public static function getAmountChargeableInWords($amount, $curid = 0)
      {
            include_once '/usr/share/php/Numbers/Words.php';
            $str = "";
            $rupee = floor($amount);
            $paise = round(($amount - $rupee) * 100, 2); // and set pricision two
            $method_name = "";

            $currency_text = "en_IN";
            $paise_text = "Paise";
            $rs_text = "Rupees";
            $text = '';
            if ($curid > 0) {
                  switch ($curid) {
                        case 2: // USD
                              $currency_text = "en_US";
                              $paise_text = "Cents";
                              $rs_text = "Dollars";
                              break;
                        case 3: // Euro
                              $currency_text = 'en_GB';
                              $paise_text = 'Cents';
                              $rs_text = 'Euro';
                              break;
                        case 4: // GBP
                              $currency_text = "en_GB";
                              $paise_text = "Pence";
                              $rs_text = "Pounds";
                              break;
                        case 5: // CHF
                              $currency_text = 'en_US';
                              $paise_text = 'Rappen';
                              $rs_text = 'Francs';
                              break;
                        case 6: // AED
                              $currency_text = 'en_US';
                              $paise_text = 'Fils';
                              $rs_text = 'Dirhams';
                              break;
                        case 7: // CAD
                              $currency_text = "en_US";
                              $paise_text = "Cents";
                              $rs_text = "Dollars";
                              break;
                  }
            }
            $rupee = floor($amount);
            $paise = round(($amount - $rupee) * 100, 2); // and set pricision two
            if (is_callable(array('Numbers_Words', 'toWords')))
            //            {
            //                $text = ucwords(Numbers_Words::toWords(round($rupee), $currency_text));
            //                $price_in_paise = ucwords(Numbers_Words::toWords(round($paise), $currency_text));
            //                $no = new Numbers_Words();
            //                $text = ucwords($no->toWords(round($rupee), $currency_text));
            //                $price_in_paise = ucwords($no->toWords(round($paise), $currency_text));
            //            }
            //            else
            {
                  $no = new Numbers_Words();
                  $text = ucwords($no->toWords(round($rupee), $currency_text));
                  $price_in_paise = ucwords($no->toWords(round($paise), $currency_text));
            }

            $str = ucwords($text) . " " . $rs_text;
            if ($paise > 0) {
                  $str .= " and " . $price_in_paise . " " . $paise_text;
            }
            if (getSettings('IS_NOT_CAMELCASE_IN_AMOUNT_IN_WORDS')) {
                  $str = strtolower($str);
                  $str = ucfirst($str);
            }
            return $str;
      }

      public static function getCDtype($type)
      {
            if ($type == 1) {
                  return "Output";
            } else {
                  return "Input";
            }
      }

      public static function getPaymentterm($type)
      {
            if ($type == 1) {
                  return "First payment";
            } else if ($type == 2) {
                  return "Last payment";
            } else {
                  return "Weighted Average";
            }
      }

      public static function getCreditNotes($data)
      {
            $credit = array();
            if (!empty($data)) {
                  $d = unserialize($data);
                  foreach ($d as $k => $v) {

                        $credit[] = CheckPointOrderCreditNoteManager::getFullId($v, 1);
                  }
                  return $credit;
            } else {
                  return "";
            }
      }

      public static function getInventoryRemarks($data)
      {

            //   $data = str_replace("\'", "", $data);
            $remarks = "";
            if (isset($data) && strlen(trim($data)) > 0) {
                  $array = json_decode($data, true);
                  if (is_array($array) && !empty($array)) {
                        if (isset($array['remarks'])) {
                              $remarks = $array['remarks'];
                        }
                  }
            }
            $json = json_decode($data, TRUE);
            if (strlen($remarks) <= 0 && isset($json[0]['remarks'])) {
                  $remarks = $json[0]['remarks'];
            }

            if (strlen($remarks) > 0) {
                  return $remarks;
            }
            $fixed = preg_replace_callback('/"item_inventory":"({.*?})"/', function ($matches) {
                  return '"item_inventory":"' . addslashes($matches[1]) . '"';
            }, $data);

            $data = $fixed;

            $remarks = "";
            if (isset($data) && strlen(trim($data)) > 0) {
                  $array = json_decode($data, true);
                  if (is_array($array) && !empty($array)) {
                        if (isset($array['remarks'])) {
                              $remarks = $array['remarks'];
                        } elseif (isset($array[0]['remarks'])) {
                              $remarks = $array[0]['remarks'];
                        }
                  }
            }
            $data = explode(",", $data);
            if (is_array($data) && !empty($data) && strlen($remarks) <= 0) {

                  foreach ($data as $data1) {
                        $d = json_decode($data1, true);
                        if (!empty($d) && is_array($d) && isset($d[0]['remarks']) && strlen($d[0]['remarks'])) {
                              $remarks .= $d[0]['remarks'] . ", ";
                        }
                        if (!empty($d) && is_array($d) && isset($d['remarks']) && strlen($d['remarks'])) {
                              $remarks .= $d['remarks'] . ", ";
                        }
                  }
                  $remarks = rtrim($remarks, ", ");
            }

            return $remarks;
      }

      public static function getInventoryBatch($data)
      {
            //            hprint($data);
            $remarks = "";
            $data = explode(",", $data);
            foreach ($data as $k => $v) {
                  if ($v != "," && !empty($v)) {
                        $remarks .= $v . ",";
                  }
            }
            $remarks = rtrim($remarks, ", ");
            return $remarks;
      }

      public static function getCustomerSalesType($data)
      {
            if ($data == 1) {
                  return "Retail";
            } else if ($data == 2) {
                  return "Dealer";
            } else if ($data == 3) {
                  return "Project";
            } else {
                  return "NA";
            }
      }

      public static function getMRPLabel()
      {
            $mrp_label = "MRP";
            $return_data = getSettings("IS_MRP_LABEL");
            if (strlen($return_data) > 0) {
                  $mrp_label = $return_data;
            }
            return $mrp_label;
      }

      public static function getDealerPriceLabel()
      {
            $dealer_price_label = "Dealer Price";
            $return_data = getSettings("IS_DEALER_PRICE_LABEL");
            if (strlen($return_data) > 0) {
                  $dealer_price_label = $return_data;
            }
            return $dealer_price_label;
      }

      public static function getTodoSubjectLabelName()
      {
            $dealer_price_label = "Subject";
            $return_data = getSettings("IS_TODO_SUBJECT_LABEL_NAME");
            if (strlen($return_data) > 0) {
                  $dealer_price_label = $return_data;
            }
            return $dealer_price_label;
      }

      public static function getTodoSubjectPlaceHolder()
      {
            return "Enter " . self::getTodoSubjectLabelName();
      }

      public static function getOrderStatus($d, $row = NULL, $row_id = NULL)
      {
            if ($row['pending'] > 0) {
                  return "<label class='label label-primary'>Not Ready</label>";
            } else if ($row['pending'] <= 0) {
                  return "<label class='label label-success'>Ready</label>";
            }
      }

      public static function getTimeConvertedFormat($id, $row = NULL, $row_id = NULL)
      {
            if ($id == NULL || $id == '0000-00-00 00:00:00') {
                  return 'NA';
            }
            return date('h:i a', strtotime($id));
      }

      public static function getCreatedUSer($id)
      {
            $user = new AdminUser($id);

            $user_name = $user->getName();
            return $user_name;
      }

      public static function getDiscountPercentFormat($id, $row, $row_id)
      {
            return round($id, 3);
      }

      public static function getAddressFilterCondition($data, &$condition)
      {
            if (isset($data['ctid']) && $data['ctid'] > 0 && $data['coverid'] != 'null') {
                  $condition .= " AND ctid = '" . $data['ctid'] . "'";
            }

            if (isset($data['stid']) && $data['stid'] > 0 && $data['coverid'] != 'null') {
                  $condition .= " AND stid = '" . $data['stid'] . "'";
            }

            if (isset($data['coverid']) && $data['coverid'] > 0 && $data['coverid'] != 'null') {
                  $condition .= " AND coverid = '" . $data['coverid'] . "'";
            }
      }

      public static function getSessionOutletId($outlet_id_return = false)
      {
            $selected_session_chkid = 0;
            $session_outlet_chkid = Session::getSessionVariable()['outlet_chkid'];
            if (isset($session_outlet_chkid) && $session_outlet_chkid > 0) {
                  $selected_session_chkid = $session_outlet_chkid;
                  if ($selected_session_chkid > 0 && $outlet_id_return) {
                        $outlet_obj = CheckPointManager::getCheckpoint($selected_session_chkid);
                        if ($outlet_obj) {
                              $selected_session_chkid = $outlet_obj->getId();
                        }
                  }
            }
            return $selected_session_chkid;
      }

      public static function getViewOutletList($outlets, $outlet_obj_bit = 0, &$outlet_filter = null, $outlet_sub_code_bit = 0, &$outlet_sub_code_filter = null, $avtid = 0)
      {
            $outlet_dropdown_array = array();
            $outlet_restrict = getSettings("IS_OUTLET_RESTRICT_IN_LIST") ? 1 : 0;
            $selected_session_chkid = 0;
            $session_outlet_chkid = Session::getSessionVariable()['outlet_chkid'];
            if (isset($session_outlet_chkid) && $session_outlet_chkid > 0) {
                  $selected_session_chkid = $session_outlet_chkid;
                  $session_chkid = $session_outlet_chkid;
                  if (!isset($outlet_dropdown_array[$session_chkid])) {
                        $outlet_dropdown_array[$session_chkid] = $session_chkid;
                  }
            }

            $show_all_option = 1;
            if (!$outlet_restrict) {
                  $outlet_dropdown_array = array();
            } else {
                  if ($selected_session_chkid > 0) {
                        $show_all_option = 0;
                  }
            }
            $final_outlet_array = array();
            $outlet_str = "";
            if ($show_all_option) {
                  if ($outlet_obj_bit) {
                        $outlet_filter->addItem("", "All Outlets", $selected_session_chkid <= 0);
                  }
                  $outlet_str .= '<option value=""' . ($selected_session_chkid > 0 ? "" : "selected") . '>All Outlets</option>';
            }
            foreach ($outlets as $outlet) {
                  $show_option_outlet = 1;
                  $outlet_id = 0;
                  if (is_array($outlet)) {
                        $outlet_chkid = $outlet['chkid'];
                        $outlet_id = $outlet['id'];
                        $outlet_name = $outlet['name'];
                  } else {
                        $outlet_chkid = $outlet->getChkid();
                        $outlet_id = $outlet->getId();
                        $outlet_name = $outlet->getName();
                  }
                  if (is_array($outlet_dropdown_array) && !empty($outlet_dropdown_array) && !isset($outlet_dropdown_array[$outlet_chkid])) {
                        $show_option_outlet = 0;
                  }
                  if ($show_option_outlet) {
                        if ($outlet_obj_bit) {
                              $outlet_filter->addItem($outlet_chkid, ucwords($outlet_name), $selected_session_chkid == $outlet_chkid);

                              if ($outlet_sub_code_bit) {
                                    $sub_codes = OutletManager::getSubCodes($outlet_id, $avtid);
                                    if ($sub_codes) {
                                          foreach ($sub_codes as $sub_code) {
                                                $outlet_sub_code_filter->addItem($sub_code->outcoid, ucwords($outlet_name) . " - " . ucwords($sub_code->code));
                                          }
                                    }
                              }
                        }
                        $outlet_str .= '<option value="' . $outlet_chkid . '"' . ($selected_session_chkid == $outlet_chkid ? "selected" : "") . '>' . ucwords($outlet_name) . '</option>';
                  }
            }
            return $outlet_str;
      }

      public static function getFormOutletList($outlets, $selected_chkid = 0, $selected_outlid = 0)
      {

            $outlet_dropdown_array = array();
            if ($selected_chkid > 0) {
                  $outlet_dropdown_array[$selected_chkid] = $selected_chkid;
            } elseif ($selected_outlid > 0) {
                  $outlet_obj = new Outlet($selected_outlid);
                  $selected_chkid = $outlet_obj->getChkid();
                  $outlet_dropdown_array[$selected_chkid] = $selected_chkid;
            }
            $outlet_restrict = getSettings("IS_OUTLET_RESTRICT_IN_FORM") ? 1 : 0;
            $session_outlet_chkid = Session::getSessionVariable()['outlet_chkid'];
            if (isset($session_outlet_chkid) && $session_outlet_chkid > 0) {
                  $session_chkid = $session_outlet_chkid;
                  if ($selected_chkid <= 0) {
                        $selected_chkid = $session_chkid;
                  }
                  if (!isset($outlet_dropdown_array[$session_chkid])) {
                        $outlet_dropdown_array[$session_chkid] = $session_chkid;
                  }
            }
            if (!$outlet_restrict) {
                  $outlet_dropdown_array = array();
            }
            $final_outlets = array();
            $l = 1;
            foreach ($outlets as $outlet) {
                  $show_option_outlet = 1;
                  if (is_array($outlet)) {
                        $outlet_chkid = $outlet['chkid'];
                  } else {
                        $outlet_chkid = $outlet->getChkid();
                  }
                  if (is_array($outlet_dropdown_array) && !empty($outlet_dropdown_array) && !isset($outlet_dropdown_array[$outlet_chkid])) {
                        $show_option_outlet = 0;
                  }
                  if ($show_option_outlet) {
                        $final_outlets[] = $outlet;
                  }
                  if ($selected_chkid <= 0 && $l == 1) {
                        $selected_chkid = $outlet_chkid;
                  }
                  $l++;
            }
            return array('outlets' => $final_outlets, 'selected_chkid' => $selected_chkid);
      }

      public static function getSelectedQtyCallback($id, $row)
      {
            $qty = $id;
            $m_name = 'PCS';
            $meaid = 0;
            if (isset($row['variation_meaid']) && $row['variation_meaid'] > 0) {
                  $meaid = $row['variation_meaid'];
                  $m = new Measurement($row['variation_meaid']);
                  $qty = $qty / $m->getConversionRate();
                  $m_name = $m->getName();
            }
            $quantity = $qty;
            $qty_display = '';
            if (isset($row['variation_id'])) {
                  if (isset($row['selected_qty_meaid']) && $row['selected_qty_meaid'] > 0 && $meaid != $row['selected_qty_meaid']) {
                        $variation = new InventorySetVariation($row['variation_id']);
                        $mm = new Measurement($row['selected_qty_meaid']);
                        $selected_unit_name = $mm->getName();
                        if ($variation->getCIitid() == 1) {
                              if ($variation->getPackageMeaid() == $mm->getId()) {
                                    $quantity = $quantity / $variation->getPackageQuantity();
                              } else {
                                    if ($variation->getMeasuredQty() > 0) {
                                          $c = $mm->getConversionRate() / ($variation->getMeasuredQty());
                                          $quantity = $quantity / $c;
                                    }
                              }
                        } else {
                              $variation_mm = new Measurement($variation->getMeaid());
                              $converted = $variation_mm->getConversionRate() / $mm->getConversionRate();
                              $quantity = $quantity * $converted;
                        }

                        if ($quantity > 0) {
                              $qty_display = " (" . round($quantity, 4) . " " . $selected_unit_name . ")";
                        }
                  }
            }
            return round($qty, 4) . " " . $m_name . $qty_display;
      }

      public static function getMonthRangeArray($month_count)
      {
            $month_array = array();
            $month_name = date('M');
            $date = date('Y-m-01');
            $col = 'AA';
            $count = 1;
            $month_array[] = array('month' => $month_name, 'value' => $col, 'count' => $count, 'date' => $date);
            $count++;
            $col++;
            $month_count--;
            while ($month_count > 0) {
                  $month_name = date('M', strtotime($date . "-1 months"));
                  $date = date('Y-m-01', strtotime($date . "-1 months"));
                  $month_array[] = array('month' => $month_name, 'value' => $col, 'count' => $count, 'date' => $date);
                  $col++;
                  $count++;
                  $month_count--;
            }
            return $month_array;
      }

      public static function calculateWorkingNoofDays($from_date, $to_date)
      {

            $startDate = strtotime($from_date);
            $endDate = strtotime($to_date);
            $workingDays = 0;
            for ($i = $startDate; $i <= $endDate; $i = $i + (60 * 60 * 24)) {
                  if (date("N", $i) <= 5) {
                        $workingDays = $workingDays + 1;
                  }
            }

            return $workingDays;
      }

      public static function checkBackDateApproval($sales = 1, $purchase = 0, $date, $approval_config_bit = 0, $no_of_days = 0, $receipt = 0, $payment = 0, $journal = 0, $contra = 0)
      {
            $approval = 0;
            $approval_days = 0;
            if ($approval_config_bit) {
                  $approval_days = $no_of_days;
            } else {
                  if ($sales) {
                        $approval_days = getSettings("IS_SALES_BACKDATE_APPROVAL_DAYS");
                  } elseif ($purchase) {
                        $approval_days = getSettings("IS_PURCHASE_BACKDATE_APPROVAL_DAYS");
                  } elseif ($receipt) {
                        $approval_days = getSettings("IS_RECEIPT_BACKDATE_APPROVAL_DAYS");
                  } elseif ($payment) {
                        $approval_days = getSettings("IS_PAYMENT_BACKDATE_APPROVAL_DAYS");
                  } elseif ($contra) {
                        $approval_days = getSettings('IS_CONTRA_BACKDATE_APPROVAL_DAYS');
                  } elseif ($journal) {
                        $approval_days = getSettings('IS_JOURNAL_BACKDATE_APPROVAL_DAYS');
                  }
            }
            if ($approval_days <> 0) {
                  $today_date = date("Y-m-d");
                  $today_date = strtotime($today_date);
                  $sales_date = strtotime($date);
                  $date_diff = $today_date - $sales_date;
                  $days = round($date_diff / (60 * 60 * 24));
                  if ($days >= $approval_days) {
                        $approval = 1;
                  }
            }
            return $approval;
      }

      public static function getBoxQtyCallback($id, $row)
      {
            $str = "NA";
            if (isset($row['isvid']) && $row['isvid']) {
                  $variation = new InventorySetVariation($row['isvid']);
                  $str = self::getBoxQtyFormat($variation->getPackageQuantity(), $variation->getCPackageMeasurement(), $id, $variation->getMeaid());
                  $str = str_replace(array('(', ')'), array('', ''), $str);
            }
            return $str;
      }

      public static function getMeasuredQtyCallback($id, $row)
      {
            $str = "NA";
            if (isset($row['isvid']) && $row['isvid']) {
                  $variation = new InventorySetVariation($row['isvid']);
                  $measured_qty = $variation->getMeasuredBoxQty();
                  if ($variation->getMeasuredUnit() > 0 && $measured_qty > 0) {
                        $str = round($id * $measured_qty, 4) . " " . $variation->getCMeasuredMeasurement();
                  }
            }
            return $str;
      }

      public static function getExcelDateSlashFormat($date, $row = NULL, $row_id = NULL)
      {
            if (!$date || is_null($date) || $date === "0000-00-00" || $date === "0000-00-00 00:00:00" || strtotime($date) <= 0) {
                  return "NA";
            }
            return date('d M Y', strtotime($date));
      }

      public static function fetchCustomReportStockClosingPackageQtyValues($id, $row, $row_id = NULL)
      {

            $variation = new InventorySetVariation($row['isvid']);
            $quantity = $id;
            $meaid_name = "PCS";
            $box_qty_append = '';

            if ($variation->getItemDetails()->getIitid() === '2' && $variation->getMeaid() > 0 && $id != 0) {
                  $m = new Measurement($variation->getMeaid());
                  $meaid_name = $m->getName();
            } elseif ($variation->getMeaid() > 0) {
                  $m = new Measurement($variation->getMeaid());
                  $meaid_name = $m->getName();
            }
            $box_qty_append = self::getBoxQtyFormat($variation->getPackageQuantity(), $variation->getCPackageMeasurement(), $quantity, $variation->getMeaid(), $variation->getCMeasurement());
            $box_qty_append = str_replace(array('(', ')'), array('', ''), $box_qty_append);

            return $quantity > 0 ? $box_qty_append : 0;
      }

      public static function fetchCustomReportClosingStockBoxQtyValues($id, $row, $row_id = NULL)
      {

            $variation = new InventorySetVariation($row['isvid']);
            $quantity = $id;
            $meaid_name = "PCS";
            $box_qty = '';

            if ($variation->getItemDetails()->getIitid() === '2' && $variation->getMeaid() > 0 && $id != 0) {
                  $m = new Measurement($variation->getMeaid());
                  $meaid_name = $m->getName();
            } elseif ($variation->getMeaid() > 0) {
                  $m = new Measurement($variation->getMeaid());
                  $meaid_name = $m->getName();
            }

            $measurement = new Measurement($variation->getMeasuredUnit());
            if ($quantity > 0 && $variation->getMeasuredQty() > 0 && $measurement->getConversionRate() > 0) {
                  $box_qty = round((($quantity) * $variation->getMeasuredQty()) / $measurement->getConversionRate(), 4);
            }

            if ($box_qty > 0) {
                  $box_qty = $box_qty . "  " . $measurement->getName();
            } else {
                  $box_qty = '';
            }

            return (abs($quantity) > 0) ? $box_qty : 0;
      }

      public static function fetchCustomReportClosingStockQtyValues($id, $row, $row_id = NULL)
      {

            $variation = new InventorySetVariation($row['isvid']);
            $quantity = $id;
            $meaid_name = "PCS";
            $box_qty = '';

            if ($variation->getItemDetails()->getIitid() === '2' && $variation->getMeaid() > 0 && $id != 0) {
                  $m = new Measurement($variation->getMeaid());
                  $meaid_name = $m->getName();
            } elseif ($variation->getMeaid() > 0) {
                  $m = new Measurement($variation->getMeaid());
                  $meaid_name = $m->getName();
            }

            $stock_list = 0;
            if (abs($quantity) > 0) {
                  $m = new Measurement($variation->getMeaid());
                  $stock_list = ($quantity == 0 && !is_null($row_id)) ? "-" : round($quantity, 4) . " " . $meaid_name;
            }

            return (abs($quantity) > 0) ? $stock_list : 0;
      }

      public static function getDateFormatSlash($d, $row = NULL, $row_id = NULL)
      {
            if ($d !== NULL && $d != '' && $d !== '0000-00-00') {
                  return date('d/m/Y', strtotime($d));
            }
            return 'NA';
      }

      public static function replaceNullValues(&$array)
      {
            foreach ($array as $key => &$value) {
                  if (is_array($value)) {
                        self::replaceNullValues($value);
                  } elseif (is_null($value)) {
                        $array[$key] = '';
                  }
            }
      }

      public static function secondsToHumanReadable($seconds)
      {
            $intervals = [
                  'year' => 31536000, // 60 * 60 * 24 * 365
                  'month' => 2592000, // 60 * 60 * 24 * 30
                  'day' => 86400, // 60 * 60 * 24
                  'hour' => 3600, // 60 * 60
                  'minute' => 60, // 60
                  'second' => 1
            ];

            $result = [];
            foreach ($intervals as $name => $duration) {
                  if ($seconds >= $duration) {
                        $value = floor($seconds / $duration);
                        $seconds %= $duration;
                        $result[] = $value . ' ' . $name . ($value > 1 ? 's' : '');
                  }
            }

            return $result ? implode(', ', $result) : '0 seconds';
      }

      public static function getFileCountDisplay($d, $row = NULL, $row_id = NULL)
      {
            if ($d != '') {
                  return '<i class="fa fa-file cursor-pointer " style="color: #238ae6;"></i> - ' . $d;
            }
            return '<i class="fa fa-file cursor-pointer " style="color: #238ae6;"></i> - 0';
      }

      /**
       * This function will clean the name of the product for csv format.
       * This will replace comma with semi colon and 2 spaces to one space.
       *
       * @param string $_productName
       * @return string
       */
      public static function cleanProductNameForCSV($_productName)
      {
            $productName = preg_replace('/\s+/', ' ', $_productName);
            return trim(str_replace(',', ';', $productName));
      }

      /**
       * This function will make the name of the product to code.
       * This will remove any space if present in the name and convert to
       * lower case.
       *
       * @param string $_name
       * @return string
       */
      public static function convertNameToCode($_name)
      {
            return strtolower(self::removeWhiteSpace($_name));
      }

      /**
       * This function will remove any space if present in the name
       *
       * @param string $_data
       * @return string
       */
      public static function removeWhiteSpace($_data)
      {
            return preg_replace('/\s+/', '', $_data);
      }

      /**
       * This function will validate name and return the result.
       *
       * @param string $_name
       * @return bool
       */
      public static function validateName($_name)
      {
            return preg_match('/^[a-zA-Z\s]+$/', $_name);
      }

      public static function formatFloat($number, $decimal = 2)
      {
            if (fmod($number, 1) == 0) {
                  return number_format($number, 0, '.', '');
            } else {
                  return rtrim(rtrim(number_format($number, $decimal, '.', ''), '0'), '.');
            }
      }

      public static function getSubstrateValue($id)
      {
            $substrateName = '';
            $substrateArray = array(
                  array("Substrate" => "PET", "Density" => 1.4, "Rate" => 120),
                  array("Substrate" => "METPET/ SMPF", "Density" => 1.4, "Rate" => 130),
                  array("Substrate" => "Matt PET", "Density" => 1.4, "Rate" => 170),
                  array("Substrate" => "BOPP", "Density" => 0.9, "Rate" => 160),
                  array("Substrate" => "Matt BOPP", "Density" => 0.9, "Rate" => 135),
                  array("Substrate" => "Paper", "Density" => 1, "Rate" => 130),
                  array("Substrate" => "Pearlised BOPP", "Density" => 0.75, "Rate" => 155),
                  array("Substrate" => "Foil", "Density" => 2.7, "Rate" => 400),
                  array("Substrate" => "LD/ Poly 3 Natural", "Density" => 0.92, "Rate" => 122),
                  array("Substrate" => "MET BOPP", "Density" => 0.9, "Rate" => 145),
                  array("Substrate" => "None", "Density" => 0, "Rate" => 0),
                  array("Substrate" => "PVC Cast", "Density" => 1.4, "Rate" => 190),
                  array("Substrate" => "High Dart LD", "Density" => 0.92, "Rate" => 140),
                  array("Substrate" => "HST BOPP", "Density" => 0.9, "Rate" => 145),
                  array("Substrate" => "Bio Film", "Density" => 1.35, "Rate" => 300),
                  array("Substrate" => "White LD 3 Layer", "Density" => 0.93, "Rate" => 135),
                  array("Substrate" => "CPP", "Density" => 0.91, "Rate" => 120),
                  array("Substrate" => "BOPA", "Density" => 1.14, "Rate" => 180),
                  array("Substrate" => "LD/ Poly 5 Natural", "Density" => 0.92, "Rate" => 155),
                  array("Substrate" => "White LD/ Poly 5", "Density" => 0.93, "Rate" => 165),
                  array("Substrate" => "Metallised CPP", "Density" => 0.91, "Rate" => 140),
                  array("Substrate" => "PET-G", "Density" => 1.23, "Rate" => 400),
                  array("Substrate" => "HST Paper (Java Advanced)", "Density" => 1, "Rate" => 540),
                  array("Substrate" => "Soft Touch PET", "Density" => 1.23, "Rate" => 580)
            );
            if ($id) {
                  $id = $id - 1;
                  if (isset($substrateArray[$id])) {
                        $substrateName = $substrateArray[$id]['Substrate'];
                  }
            }
            return $substrateName;
      }

      public static function getOutletDropdownArray($outlets)
      {
            $session_chkid = Session::getSessionVariable()['outlet_chkid'] ? Session::getSessionVariable()['outlet_chkid'] : 0;
            $outlet_array = [];

            foreach ($outlets as $outlet) {
                  if (is_array($outlet)) {
                        $chkid = $outlet['chkid'];
                        $name = $outlet['name'];
                  } else {
                        $chkid = $outlet->getChkid();
                        $name = $outlet->getName();
                  }

                  $outlet_array[$chkid] = ucwords($name);
            }

            return $outlet_array;
      }

      public static function isValidImageUrl($url)
      {
            $url = trim($url);
            $data = @file_get_contents($url);

            if ($data !== false) {
                  $imageInfo = @getimagesizefromstring($data);
                  if ($imageInfo !== false && strpos($imageInfo['mime'], 'image/') === 0) {
                        return true;
                  }
            }

            // If file_get_contents fails (403 or remote server blocks it), try cURL
            $ch = curl_init($url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
            curl_setopt($ch, CURLOPT_TIMEOUT, 10);
            curl_setopt($ch, CURLOPT_RANGE, '0-1024');
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // Optional if using HTTPS
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);

            // Dynamic Referer based on URL
            $parsedUrl = parse_url($url);
            $referer = '';
            if (isset($parsedUrl['scheme'], $parsedUrl['host'])) {
                  $referer = $parsedUrl['scheme'] . '://' . $parsedUrl['host'];
            }

            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                  'Referer: ' . $referer,
                  'User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64)',
                  'Accept: image/avif,image/webp,image/apng,image/*,*/*;q=0.8',
                  'Accept-Language: en-US,en;q=0.9',
            ]);

            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $contentType = curl_getinfo($ch, CURLINFO_CONTENT_TYPE);
            curl_close($ch);

            return ($httpCode === 200 || $httpCode === 206) &&
                  strpos($contentType, 'image/') === 0;
      }

      public static function getQtyDisplayFormat($id)
      {
            $round_off = 4;
            $QTY_DISPLAY_DECIMALS = getSettings("QTY_DISPLAY_DECIMALS");
            if ($QTY_DISPLAY_DECIMALS > 0) {
                  $round_off = $QTY_DISPLAY_DECIMALS;
            }
            return round($id, $round_off);
      }

      public static function addFormHeadingButtons(&$form, $type)
      {
            $form->setCntlClass("filter_form");
            // Sticky button group (kept outside the scrollable group)
            $form_group = new FormGroup();
            $form_group->setCntlClass("{$type}-filter-group sticky-filter-buttons");

            $apply_filter_button = new FormBtn("apply-{$type}-filter", 'submit', 'Apply Filter');
            $apply_filter_button->setCntlClass("btn-sm");

            $reset_filter_button = new FormBtn("reset-{$type}-filter", 'submit', 'Reset');
            $reset_filter_button->setType('reset');
            $reset_filter_button->setCntlClass("btn-sm");
            $reset_filter_button->setDesignClass("btn btn-default");

            $close_filter_button = new FormBtn("close-{$type}-filter", 'submit', 'Close');
            $close_filter_button->setCntlClass("rightbar_close_button");
            $close_filter_button->setDesignClass("btn btn-sm btn-danger pull-right");

            $formrow = new FormRow();
            $formrow->setCntlClass("text-center ");
            $formrow->addChild($apply_filter_button->publishXml(), 7, 7, 7, 7);

            $right_button_div = new FormDisplayHtml("div", "", "filter_right_buttons");
            $right_button_div->addChild($reset_filter_button->publishXml());
            $right_button_div->addChild($close_filter_button->publishXml());

            $formrow->addChild($right_button_div->publishXml(), 5, 5, 5, 5);

            $form_group->addChild($formrow->publishXml());

            $form->addChild($form_group->publishXml());
      }
}

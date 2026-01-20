<?php

/*
     * @author Sohil Gupta
     * @date Very Long Ago
     * @file This file contains general functions that are not specific to any class or application
     */

//    global $masking_array;
//    $masking_array = $masking;
//    global $masking_status_value;
//    $masking_status_value = $masking_status;

if (!function_exists('money_format')) {

    function money_format($type, $number)
    {
        return number_format($number, 2);
    }
}

function replaceCharactersFromItemName($name, $short_item_name_print)
{
    if ($short_item_name_print) {
        return preg_replace('/[^A-Za-z0-9\-]/', '', $name);
    }
    return $name;
}

function clean($string)
{
    $string = str_replace(' ', '-', $string); // Replaces all spaces with hyphens.

    return preg_replace('/[^A-Za-z0-9\-]/', '', $string); // Removes special chars.
}

function bytesToSize($bytes, $precision = 2)
{
    $kilobyte = 1024;
    $megabyte = $kilobyte * 1024;
    $gigabyte = $megabyte * 1024;
    $terabyte = $gigabyte * 1024;

    if (($bytes >= 0) && ($bytes < $kilobyte)) {
        return $bytes . ' B';
    } elseif (($bytes >= $kilobyte) && ($bytes < $megabyte)) {
        return round($bytes / $kilobyte, $precision) . ' KB';
    } elseif (($bytes >= $megabyte) && ($bytes < $gigabyte)) {
        return round($bytes / $megabyte, $precision) . ' MB';
    } elseif (($bytes >= $gigabyte) && ($bytes < $terabyte)) {
        return round($bytes / $gigabyte, $precision) . ' GB';
    } elseif ($bytes >= $terabyte) {
        return round($bytes / $terabyte, $precision) . ' TB';
    } else {
        return $bytes . ' B';
    }
}

/**
 * @desc Checks the validity of an expression
 * @return Boolean Whether the expression is valid or not
 */
function getWeekForamtOfDate($past_weeks)
{

    $relative_time = time();
    $weeksarr = array();
    $start = '';
    $end = '';
    $weeks = array();
    for (
        $week_count = 0;
        $week_count < $past_weeks;
        $week_count++
    ) {
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
    return array("weeks" => $weeks, "start" => $start, "end" => $end);
}

function valid($expression = "")
{
    if (!isset($expression)) {
        return false;
    }

    if (is_array($expression) || is_object($expression)) {
        return true;
    }

    $ex = trim($expression);
    if (isset($ex) && !is_null($ex) && $ex != "") {
        return true;
    }
    return false;
}

function sortByVariationName($a, $b)
{
    return strcmp($a->getName(), $b->getName());
}

function sortByChkoiid($a, $b)
{
    return $a->getId() - $b->getId();
}

function hprint($data, $show_html = false)
{
    /*
         * Takes in an array or an object and prints it out hiearchically to the screen
         */
    if ($show_html) {
        /* If html is needed to be shown, html elements needs to be sanitized to be displayed on the screen */
        print htmlentities('<pre>' . print_r($data, TRUE) . '</pre>');
    } else {
        print '<pre>' . print_r($data, TRUE) . '</pre>';
    }
}

function str_replace_name($name)
{
    return str_replace("&amp;", "&", $name);
}

function random_alphanumeric_string($length = 12)
{
    /* Function that returns a random AlphaNumeric String of a specified length */
    $alphNums = "0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ";
    $newString = str_shuffle(str_repeat($alphNums, rand(1, $length)));
    return substr($newString, rand(0, strlen($newString) - $length), $length);
}

function random_password($length = 0)
{
    /* Function that generates a random password of a specified length */
    $length = ($length == 0) ? 10 : $length;
    $characters = "1234567890qwertyuiopasdfghjklzxcvbnmQWERTYUIOPASDFGHJKLZXCVBNM!@#$%^&*()_+=";
    $token = "";
    for (
        $i = 0;
        $i < $length;
        $i++
    ) {
        $value = strlen($characters) - 1;
        $token .= $characters[rand(0, $value)];
    }
    return $token;
}

function string_teaser($string, $length, $add_dots = false, $concat_end = "")
{
    /*
         * Functions that trims a string to a specific length
         * @params
         *  $string - The string to trim
         *  $length - the length to trim this string to
         *  $add_dots - add dots to the end of the trimmed string ?
         *  $concat_end - any value to concatenate to the end of the trimmed string
         */
    if ($add_dots) {
        $end = " ...";
    }
    if (strlen($string) > $length) {
        return substr($string, 0, $length) . @$end . " $concat_end";
    } else {
        return $string . " $concat_end";
    }
}

function get_ordinal_number($number)
{
    if ($number == 1) {
        return "first";
    } else if ($number == 2) {
        return "second";
    } else if ($number == 3) {
        return "third";
    } else if ($number == 4) {
        return "fourth";
    } else if ($number == 5) {
        return "fifth";
    } else if ($number == 6) {
        return "sixth";
    }
}

function get_age_years($dob)
{
    list($Y, $m, $d) = explode("-", $dob);
    return (date("md") < $m . $d ? date("Y") - $Y - 1 : date("Y") - $Y);
}

function hasPermission($id, $user = null)
{

    if ($id == 0) {
        return true;
    }
    $bool = NULL;
    $level = 'o';
    if (!$user) {
        $user = SystemConfig::getUser();
        $perm = $user->getPermission();
    } else {

        $user->load();
        $perm = $user->getPermission();
    }

    if (is_array($id)) {
        if (isset($perm[$id['o']]) || $user->getIsAdmin() === '1') {
            $bool = true;
            $level = 'o';
        } else {
            if (isset($perm[$id['g']])) {
                $bool = true;
                $level = 'g';
            }
            if (isset($perm[$id['u']])) {
                $bool = true;
                $level = 'u';
            }
            $bool = false;
        }
        return array("perm" => $bool, "level" => $level);
    } else {
        if (isset($perm[$id]) || $user->getIsAdmin() === '1') {

            $bool = true;
        } else {

            $bool = false;
        }
        return $bool;
    }
}

function dateCompiler($date, $type)
{
    $temp = '';
    $temp1 = '';
    if ($type === 1) {
        $temp = date('Y-m-d 00:00:00', strtotime($date));
        $temp1 = date('Y-m-d H:i:s', strtotime($temp));
    } else if ($type === 2) {
        $temp = date('Y-m-d 23:59:59', strtotime($date));
        $temp1 = date('Y-m-d H:i:s', strtotime($temp));
    } else {
        return false;
    }
    return $temp1;
}

function get_time_difference_php($created_time)
{

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

function getDayForamtOfDate($past_days)
{
    $start = '';
    $end = '';
    $days = array();
    for (
        $day_count = 1;
        $day_count <= $past_days;
        $day_count++
    ) {
        $day = strtotime(-$day_count . "day");
        array_push($days, date("Y-m-d", $day));
        if ($day_count == 1) {
            $end = date("Y-m-d", $day);
        }

        if ($day_count == $past_days - 1) {
            $start = date("Y-m-d", $day);
        }
    }
    return array("days" => $days, "start" => $start, "end" => $end);
}

function get_date_difference_php($created_date)
{

    $str = $created_date;

    $today = date('Y-m-d');

    $later = "later";

    $difference = strtotime($str) - strtotime($today);
    if ($difference < 0) {
        $later = "ago";
    }

    // It returns the time difference in Seconds...
    $date_differnce = abs(strtotime($str) - strtotime($today));

    // To Calculate the time difference in Years...
    $years = 60 * 60 * 24 * 365;

    // To Calculate the time difference in Months...
    $months = 60 * 60 * 24 * 30;

    // To Calculate the time difference in Days...
    $days = 60 * 60 * 24;

    if (intval($date_differnce / $years) > 1) {
        return intval($date_differnce / $years) . " years " . $later;
    } else if (intval($date_differnce / $years) > 0) {
        return intval($date_differnce / $years) . " year  " . $later;
    } else if (intval($date_differnce / $months) > 1) {
        return intval($date_differnce / $months) . " months " . $later;
    } else if (intval(($date_differnce / $months)) > 0) {
        return intval(($date_differnce / $months)) . " month " . $later;
    } else if (intval(($date_differnce / $days)) > 1) {
        return intval(($date_differnce / $days)) . " days " . $later;
    } else if (intval(($date_differnce / $days)) > 0) {
        return intval(($date_differnce / $days)) . " day " . $later;
    } else {
        return "today only";
    }
}

/**
 * @desc Checks the spwcial characters existance of an expression
 * @return Boolean Whether the expression is valid or not
 */
function check_special_chars($expression = "")
{
    if (!isset($expression)) {
        return false;
    }

    if (preg_match('/[\'^£$%&*"()}{@#~?><>,|=_+¬-]/', $expression)) {
        return true;
    }
    return false;
}

function getPermission($url1, $submit = array())
{

    $bad_words = array("my", "print", "submit", "search", "modal", "render", "upload", "remove", "photo", "mapping", "misc", "table", "list", "excel", "download", "name", "check", "header", "preview", "code");
    $alias_array = array(
        "chkorder" => "order",
        "myjobcard" => "jobcard",
    );

    $ends_with_num = array();
    preg_match_all("/\d+$/", implode("_", $url1), $ends_with_num);

    $url = explode("_", str_replace("-", "_", strtolower(implode("_", $url1))));
    if (empty($submit)) {
        foreach ($url as $key => $u) {
            if (array_key_exists($u, $alias_array)) {
                $url[$key] = $alias_array[$u];
                $u = $alias_array[$u];
            }
            if (in_array($u, $bad_words, TRUE) || (preg_match('/\d/', $u) && in_array("edit", $url)) || (preg_match('/\d/', $u) && in_array("add", $url))) {
                unset($url[$key]);
            }
            if (preg_match('/\d/', $u)) {
                if (array_search("view", $url) && isset($ends_with_num[0][0]) && $ends_with_num[0][0] === $u) {
                    $url[$key] = 'details';
                } else {
                    unset($url[$key]);
                }
            }
        }
        $array = array_unique($url);
    } else {
        foreach ($submit as $key => $s) {
            if (array_key_exists($s, $alias_array)) {
                $submit[$key] = $alias_array[$s];
                $s = $alias_array[$s];
            }
            if (in_array($s, $bad_words, TRUE)) {
                unset($submit[$key]);
            }
            if (preg_match('/\d/', $s)) {
                if (array_search("view", $submit) && isset($ends_with_num[0][0]) && $ends_with_num[0][0] === $s) {
                    $submit[$key] = 'details';
                } else {
                    unset($submit[$key]);
                }
            }
        }
        $array = array_unique($submit);
    }
    $x = 'USER_PERMISSION_' . strtoupper(implode('_', $array));

    $db = Rapidkart::getInstance()->getDB();
    $sql = "SELECT pid FROM  " . SystemTables::DB_TBL_PERMISSION . " WHERE name = '::name'";
    $res = $db->query($sql, array('::name' => strtoupper(implode('_', $array))));

    $pid = -1;
    if (!$res || $db->resultNumRows($res) < 1) {
        if (is_array($submit) && count($submit) > 0) {
            $permission = 0;
        } else {
            $permission = -1;
        }
    } else {
        $row = $db->fetchObject($res);
        $pid = $row->pid;
    }
    $permission = array();
    if (defined($x)) {
        $permission = constant($x);
    } else {
        if ($pid > 0) {
            $permission = $pid;
        } else {
            if (is_array($submit) && count($submit) > 0) {
                $permission = 0;
            } else {
                $permission = -1;
            }
        }
    }

    return $permission;
}

function getSettings($title)
{

    $settings = SystemPreferencesManager::getTitleValue($title);

    if (!$settings) {
        return FALSE;
    }
    $return_data = 0;
    switch ($settings->sptid) {
        case 1:
        case 2:
            $return_data = strlen($settings->data) > 0 ? $settings->data : (defined($title) ? constant($title) : '');
            break;
        case 3:
            $return_data = $settings->data > 0 ? TRUE : FALSE;
            break;
        default:
            $return_data = strlen($settings->data) > 0 ? $settings->data : (defined($title) ? constant($title) : '');
    }

    return $return_data;
}

function checkModuleExistInMasking($module_name)
{
    global $masking;
    global $masking_status;
    if (!(!in_array($module_name, $masking, TRUE) && $masking_status)) {
        return TRUE;
    }
    return FALSE;
}

function GstDate()
{
    return "2017-07-01 00:00:00";
}

function number_format_without_thousands($amount)
{
    $str = number_format($amount, 2, ".", "");
    return $str;
}

function dr_cr_format($amount)
{
    if (getSettings('IS_SHOW_BALANCE_IN_DR_CR')) {
        if ($amount < 0) {
            $str = money_format('%!.' . $decimal . 'n', abs($amount));
            return $str . ' Cr';
        } else {
            $str = money_format('%!.' . $decimal . 'n', abs($amount));
            return $str . ' Dr';
        }
    }
}

function number_format_dr_cr_custom($amount, $decimal = 2, $dec_point = '.', $thousand_sep = ',')
{
    $amount = str_replace(",", "", $amount);
    if ($amount == "") {
        $amount = 0;
    }
    $str = "";
    if (getSettings('IS_SHOW_BALANCE_IN_DR_CR')) {
        if ($amount < 0) {
            $str = money_format('%!.' . $decimal . 'n', abs($amount));
            return $str . '&nbsp;Cr';
        } elseif ($amount == 0) {
            return $str . '&nbsp;--';
        } else {
            $str = money_format('%!.' . $decimal . 'n', abs($amount));
            return $str . '&nbsp;Dr';
        }
    } else {
        $str = money_format('%!.' . $decimal . 'n', $amount);
        return $str;
    }
}

function number_format_custom($amount, $decimal = 2, $dec_point = '.', $thousand_sep = ',')
{

    $str = money_format('%!.' . $decimal . 'n', $amount);
    return $str;
}

function invoice_number_replace($number)
{
    return trim(preg_replace('/[^A-Za-z0-9]/', '', $number));
}

function name_replace($name)
{
    return substr(trim(preg_replace('/[^A-Za-z ]/', '', $name)), 0, 30);
}

function address_replace($address)
{
    if (strlen($address) > 0) {
        return substr(trim(preg_replace('/[^A-Za-z0-9, \.\s]/', '', $address)), 0, 150);
    }
    return ".";
}

function is_valid_gstin($gstin)
{
    //        $regex = "/^([0][1-9]|[1-2][0-9]|[3][0-7])([a-zA-Z]{5}[0-9]{4}[a-zA-Z]{1}[1-9a-zA-Z]{1}[zZ]{1}[0-9a-zA-Z]{1})+$/";
    //
    //        return preg_match($regex, $gstin);
    if (!preg_match("/^([0][1-9]|[1-2][0-9]|[3][0-7])([a-zA-Z]{5}[0-9]{4}[a-zA-Z]{1}[1-9a-zA-Z]{1}[a-zA-Z]{1}[0-9a-zA-Z]{1})+$/", $gstin)) {
        return false;
    }
    return TRUE;
}

function is_valid_pan($pan)
{
    if (!preg_match("/^([a-zA-Z]){5}([0-9]){4}([a-zA-Z]){1}?$/", $pan)) {
        return false;
    }
    return TRUE;
}

function is_valid_aadhar($aadhar)
{
    if (!preg_match("/^([2-9]{1})([0-9]{3})([0-9]{4})([0-9]{4})?$/", $aadhar)) {
        return false;
    }
    return TRUE;
}

function getNameFromNumberExcel($num)
{
    $numeric = $num % 26;
    $letter = chr(65 + $numeric);
    $num2 = intval($num / 26);
    if ($num2 > 0) {
        return getNameFromNumberExcel($num2 - 1) . $letter;
    } else {
        return $letter;
    }
}

function getQtyRemoveUnit($qty)
{
    if ($qty != "0") {
        return round(substr($qty, 0, strpos($qty, " ")), 4);
    } else {
        return 0;
    }
}

function checkTodayRecord($bit, &$condition)
{
    if (getSettings("IS_SHOW_TODAY_RECORD_ENABLE") && SystemConfig::getUser()->getIsAdmin() != 1) {
        if ($bit != 2) {
            $condition .= " AND DATE(";
            switch ($bit) {
                case 1: //Invoice
                case 4: //Payment Voucher
                case 6: //Receipt Voucher
                case 8: //POS Receipt Voucher
                    $condition .= "date";
                    break;

                case 3: //Payment List
                    $condition .= "payment_date";
                    break;
                case 5: //Payment Refr.
                case 7: //Receipt Refr.
                    $condition .= "c_transaction_date";
                    break;
                case 9: //Credit Note
                case 10: //Debit Note
                    $condition .= "return_date";
                    break;
            }
            $condition .= ") = '" . date('Y-m-d') . "'";
        }
        switch ($bit) {
            case 2: //Purchase Invoice
                $condition .= " AND DATE(gst_date) BETWEEN '" . date('Y-m-01') . "' AND '" . date('Y-m-d') . "'";
                break;
        }
    }
}

function totalWorkingHours($start, $end)
{
    $start_date = $start;
    $end_date = $end;
    $total_hours = 0;
    while (1) {
        $date = date('Y-m-d', strtotime($end_date));
        $total_hours += 8;
        if ($start_date == $end_date) {
            break;
        }
        $end_date = date('Y-m-d', strtotime($end_date . "-1 day"));
    }
    return $total_hours;
}

function monthNames($start, $end)
{
    $month_array = array();
    $end_date_month = date('m', strtotime($end));
    $date_val = $start;
    while (1) {
        $month = date('m', strtotime($date_val));
        $month_name = date('F', strtotime($date_val));
        $date = date('m', strtotime($date_val));
        $month_array[] = array('month_id' => $month, 'month_name' => $month_name, 'date' => $date);
        $date_val = date('Y-m-d', strtotime($date_val . " +1 month"));
        if ($month == $end_date_month) {
            break;
        }
    }
    return $month_array;
}

function monthYearNames($start, $end)
{
    $month_array = array();
    $end_date_month = date('m-Y', strtotime($end));
    $date_val = $start;
    while (1) {
        $month = date('m-Y', strtotime($date_val));
        $year = date('Y', strtotime($date_val));
        $month_name = date('M-y', strtotime($date_val));
        $date = date('m-Y', strtotime($date_val));
        $month_array[] = array('month_id' => intval($month) . "-" . $year, 'month_name' => $month_name, 'date' => $date);
        $date_val = date('Y-m-d', strtotime($date_val . " +1 month"));
        if ($month == $end_date_month) {
            break;
        }
    }
    return $month_array;
}

function convertCustomDateFormat($date, $format = 'Y-m-d')
{
    $date = trim($date);
    $format = trim($format);
    $date_v = DateTime::createFromFormat($format, $date);
    if (!$date_v) {
        return "0000-00-00";
    }
    return $date_v->format('Y-m-d H:i:s');
}

function arrayMergeFunction(&$final_array, $array)
{
    if (is_array($array) && !empty($array)) {
        foreach ($array as $key => $ar) {
            if (!isset($final_array[$key])) {
                $final_array[$key] = $ar;
            }
        }
    }
}

function removeCarriageReturnBreakFromString($string)
{
    $string = str_replace(array("\n", "\t", "\r"), array('', '', ''), $string);
    return trim($string);
}

function customOverallRoundOff($amount, $round_off_places = 0)
{
    $round_off = 0;
    if ($round_off_places > 0) {
        $round_off = round($amount / $round_off_places) * $round_off_places; //round($amount, $round_off_places);
    } else if ($round_off_places < 0) {

        $abs_round_off = abs($round_off_places);
        $value = pow(10, $abs_round_off);
        $round_off = round($amount / $value) * $value;
    } else {
        $round_off = round($amount, $round_off_places);
    }
    return $round_off;
}

function htmlTableDisplay($item_column_list, $outlet_group_bit, $column_list, $currency_bit, $invoices_array, $credit_notes_array, $purchase_invoice_array, $debit_note_array, $package_array, $invoice_extra_charges_array, $credit_note_extra_charges_array, $purchase_invoice_extra_charges_array, $debit_note_extra_charges_array, $package_extra_charges_array, $show_narration, $hide_narration_for_invoice, $show_delivery_address_for_invoice, $show_billing_address_for_invoice, $show_order_id_for_invoice, $hide_item_details, $show_discount_percent, $include_tax_in_item_price_and_total_amount, $show_extra_charges, $show_discount_amount, $hide_ledger_details, $show_sku_code, $show_financial_year_wise_summary, $cheque_reconcilation_waiting, $chkid_key, $show_transaction_settled, $financial_year_separation, $show_with_without_opening, $curr_syb, $item_wise_ledger_remark, $show_product_details_in_one_cell, $template_config = '')
{
    //        $str = '<table class="display table table-bordered" style="margin-bottom:0px !important; padding-bottom: 0px;"><tbody>';
    $str = '<table class="display table table-bordered tall-rows"
            style="width:100% !important; table-layout:fixed !important; border-collapse:collapse; margin-bottom:0px !important;"
            width="100%" cellspacing="0" cellpadding="3">
        ';
    global $total_debits;
    global $total_credits;
    global $total_debits_value;
    global $opening_balance;
    global $total_credits_value;
    global $running_balance;
    global $total_debit_value;
    global $total_credit_value;
    global $customer_wallet_details;
    $opening_balance = $running_balance;
    $opening_balance_type = 1;

    if ($opening_balance > 0) {
        $opening_balance_type = 2;
    }
    if ($opening_balance < 0) {
        $total_debits += abs($opening_balance);
    } else {
        $total_credits += $opening_balance;
    }
    $running_balance = (abs($opening_balance) > 0) ? ($opening_balance) : 0;
    $tpl = new Template(SystemConfig::templatesPath() . "customer/views/customer-ledger-statement-transaction-details");
    $tpl->template_config = $template_config;
    $tpl->item_column_list = $item_column_list;
    $tpl->outlet_group_bit = $outlet_group_bit;
    $tpl->opening_balance_type = $opening_balance_type;
    $tpl->column_list = $column_list;
    $tpl->wallet_details = $customer_wallet_details;
    $tpl->currency_bit = $currency_bit;
    $tpl->invoices_array = $invoices_array;
    $tpl->credit_notes_array = $credit_notes_array;
    $tpl->purchase_invoice_array = $purchase_invoice_array;
    $tpl->debit_note_array = $debit_note_array;
    $tpl->package_array = $package_array;
    $tpl->invoice_extra_charges_array = $invoice_extra_charges_array;
    $tpl->credit_note_extra_charges_array = $credit_note_extra_charges_array;
    $tpl->purchase_invoice_extra_charges_array = $purchase_invoice_extra_charges_array;
    $tpl->debit_note_extra_charges_array = $debit_note_extra_charges_array;
    $tpl->package_extra_charges_array = $package_extra_charges_array;
    $tpl->show_narration = $show_narration;
    $tpl->hide_narration_for_invoice = $hide_narration_for_invoice;
    $tpl->show_delivery_address_for_invoice = $show_delivery_address_for_invoice;
    $tpl->show_billing_address_for_invoice = $show_billing_address_for_invoice;
    $tpl->show_order_id_for_invoice = $show_order_id_for_invoice;
    $tpl->hide_item_details = $hide_item_details;
    $tpl->show_product_details_in_one_cell = $show_product_details_in_one_cell;
    $tpl->show_discount_percent = $show_discount_percent;
    $tpl->include_tax_in_item_price_and_total_amount = $include_tax_in_item_price_and_total_amount;
    $tpl->show_extra_charges = $show_extra_charges;
    $tpl->show_discount_amount = $show_discount_amount;
    $tpl->hide_ledger_details = $hide_ledger_details;
    $tpl->show_transaction_settled = $show_transaction_settled;
    $tpl->show_financial_year_wise_summary = $show_financial_year_wise_summary;
    $tpl->financial_year_separation = $financial_year_separation;
    $tpl->cheque_reconcilation_waiting = $cheque_reconcilation_waiting;
    $tpl->show_sku_code = $show_sku_code;
    $tpl->item_wise_ledger_remark = $item_wise_ledger_remark;
    $tpl->curr_syb = $curr_syb;
    $str .= $tpl->parse();

    $tpl = new Template(SystemConfig::templatesPath() . "customer/views/customer-ledger-statement-closing");
    $tpl->item_column_list = $item_column_list;
    $tpl->cheque_reconcilation_waiting = $cheque_reconcilation_waiting;
    $tpl->outlet_group_bit = $outlet_group_bit;
    $tpl->opening_balance_type = $opening_balance_type;
    $tpl->column_list = $column_list;
    $tpl->wallet_details = $customer_wallet_details;
    $tpl->currency_bit = $currency_bit;
    $tpl->invoices_array = $invoices_array;
    $tpl->credit_notes_array = $credit_notes_array;
    $tpl->purchase_invoice_array = $purchase_invoice_array;
    $tpl->debit_note_array = $debit_note_array;
    $tpl->package_array = $package_array;
    $tpl->invoice_extra_charges_array = $invoice_extra_charges_array;
    $tpl->credit_note_extra_charges_array = $credit_note_extra_charges_array;
    $tpl->purchase_invoice_extra_charges_array = $purchase_invoice_extra_charges_array;
    $tpl->debit_note_extra_charges_array = $debit_note_extra_charges_array;
    $tpl->package_extra_charges_array = $package_extra_charges_array;
    $tpl->show_narration = $show_narration;
    $tpl->show_with_without_opening = $show_with_without_opening;
    $tpl->hide_narration_for_invoice = $hide_narration_for_invoice;
    $tpl->show_delivery_address_for_invoice = $show_delivery_address_for_invoice;
    $tpl->show_billing_address_for_invoice = $show_billing_address_for_invoice;
    $tpl->show_order_id_for_invoice = $show_order_id_for_invoice;
    $tpl->hide_item_details = $hide_item_details;
    $tpl->show_discount_percent = $show_discount_percent;
    $tpl->include_tax_in_item_price_and_total_amount = $include_tax_in_item_price_and_total_amount;
    $tpl->show_extra_charges = $show_extra_charges;
    $tpl->show_discount_amount = $show_discount_amount;
    $tpl->hide_ledger_details = $hide_ledger_details;
    $tpl->show_transaction_settled = $show_transaction_settled;
    $tpl->show_financial_year_wise_summary = $show_financial_year_wise_summary;
    $tpl->curr_syb = $curr_syb;

    $tpl->show_sku_code = $show_sku_code;
    $str .= $tpl->parse();
    $str .= ' </tbody>';
    $str .= '</table>';
    return $str;
}

function recursiveFunction(
    $item_column_list,
    $outlet_group_bit,
    $column_list,
    $currency_bit,
    $invoices_array,
    $credit_notes_array,
    $purchase_invoice_array,
    $debit_note_array,
    $package_array,
    $invoice_extra_charges_array,
    $credit_note_extra_charges_array,
    $purchase_invoice_extra_charges_array,
    $debit_note_extra_charges_array,
    $package_extra_charges_array,
    $show_narration,
    $hide_narration_for_invoice,
    $show_delivery_address_for_invoice,
    $show_billing_address_for_invoice,
    $show_order_id_for_invoice,
    $hide_item_details,
    $show_discount_percent,
    $include_tax_in_item_price_and_total_amount,
    $show_extra_charges,
    $show_discount_amount,
    $hide_ledger_details,
    $show_sku_code,
    &$resp,
    $show_financial_year_wise_summary,
    $cheque_reconcilation_waiting,
    $chkid_key,
    $show_transaction_settled,
    $financial_year_separation,
    $show_with_without_opening,
    $iinc = 1,
    $curr_syb = " ",
    $item_wise_ledger_remark = null,
    $show_product_details_in_one_cell = null,
    $template_config = ""
) {
    global $customer_wallet_details;
    global $opening_balance;
    global $running_balance;
    global $total_debits, $total_credits, $total_debits_value, $total_credits_value;
    global $total_debit_value, $total_credit_value;
    global $balance_colspan, $debit_col, $credit_col, $columnArrayNameShowEmpty;

    // =================== RESET EVERYTHING – THIS FIXES THE MISALIGNED COLUMNS ===================
    $balance_colspan = 0;
    $debit_col = 0;
    $credit_col = 0;
    $columnArrayNameShowEmpty = array();
    $total_debits = 0;
    $total_credits = 0;
    $total_debits_value = 0;
    $total_credits_value = 0;
    $total_debit_value = 0;
    $total_credit_value = 0;
    $running_balance = $opening_balance;        // very important!
    // =========================================================================================

    if (!empty($customer_wallet_details) || abs($opening_balance) > 0) {
        $display_bit = 1;
        if (empty($customer_wallet_details) && $iinc != 1) {
            $display_bit = 0;
        }
        if ($display_bit) {
            $response = htmlTableDisplay($item_column_list, $outlet_group_bit, $column_list, $currency_bit, $invoices_array, $credit_notes_array, $purchase_invoice_array, $debit_note_array, $package_array, $invoice_extra_charges_array, $credit_note_extra_charges_array, $purchase_invoice_extra_charges_array, $debit_note_extra_charges_array, $package_extra_charges_array, $show_narration, $hide_narration_for_invoice, $show_delivery_address_for_invoice, $show_billing_address_for_invoice, $show_order_id_for_invoice, $hide_item_details, $show_discount_percent, $include_tax_in_item_price_and_total_amount, $show_extra_charges, $show_discount_amount, $hide_ledger_details, $show_sku_code, $show_financial_year_wise_summary, $cheque_reconcilation_waiting, $chkid_key, $show_transaction_settled, $financial_year_separation, $show_with_without_opening, $curr_syb, $item_wise_ledger_remark, $show_product_details_in_one_cell, $template_config);

            $resp .= $response;
            $iinc++;
            recursiveFunction($item_column_list, $outlet_group_bit, $column_list, $currency_bit, $invoices_array, $credit_notes_array, $purchase_invoice_array, $debit_note_array, $package_array, $invoice_extra_charges_array, $credit_note_extra_charges_array, $purchase_invoice_extra_charges_array, $debit_note_extra_charges_array, $package_extra_charges_array, $show_narration, $hide_narration_for_invoice, $show_delivery_address_for_invoice, $show_billing_address_for_invoice, $show_order_id_for_invoice, $hide_item_details, $show_discount_percent, $include_tax_in_item_price_and_total_amount, $show_extra_charges, $show_discount_amount, $hide_ledger_details, $show_sku_code, $resp, $show_financial_year_wise_summary, $cheque_reconcilation_waiting, $chkid_key, $show_transaction_settled, $financial_year_separation, $show_with_without_opening, $iinc, $curr_syb = " ", $item_wise_ledger_remark, $show_product_details_in_one_cell, $template_config);
        }
    }
}

function generateRandomAlphanumeric($length = 10)
{
    $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $charactersLength = strlen($characters);
    $randomString = '';
    for ($i = 0; $i < $length; $i++) {
        $randomString .= $characters[rand(0, $charactersLength - 1)];
    }
    return $randomString;
}

function getQuaters($startDate, $endDate)
{
    $startDate = new DateTime($startDate);
    $endDate = new DateTime($endDate);
    $quarters = [];

    // Loop through each quarter between the start and end dates
    $currentDate = $startDate;
    while ($currentDate <= $endDate) {
        // Calculate the current quarter and the end date for that quarter
        $year = $currentDate->format('Y');
        $month = (int) $currentDate->format('m');

        if ($month >= 4 && $month <= 6) {
            $quarter = 'Q1';
            $quarterEnd = new DateTime($year . '-06-30');
        } elseif ($month >= 7 && $month <= 9) {
            $quarter = 'Q2';
            $quarterEnd = new DateTime($year . '-09-30');
        } elseif ($month >= 10 && $month <= 12) {
            $quarter = 'Q3';
            $quarterEnd = new DateTime($year . '-12-31');
        } else {
            $quarter = 'Q4';
            $quarterEnd = new DateTime($year . '-03-31');
        }

        // Add the quarter to the result array
        $s = $currentDate->format('Y-m-d') . "_" . $quarterEnd->format('Y-m-d');
        $quarters[$s] = [
            'Quarter' => $quarter,
            'Start' => $currentDate->format('Y-m-d'),
            'End' => $quarterEnd->format('Y-m-d'),
            'FinancialYear' => $month >= 4 ? $year . '-' . ($year + 1) : ($year - 1) . '-' . $year,
        ];

        // Move the current date to the start of the next quarter
        $currentDate = clone $quarterEnd;
        $currentDate->modify('+1 day');
    }


    return $quarters;
    // Output the result
    //        foreach ($quarters as $q)
    //        {
    //            echo "Financial Year: " . $q['FinancialYear'] . " | " . $q['Quarter'] . " | Start: " . $q['Start'] . " | End: " . $q['End'] . "<br>";
    //        }
}

function getMonthFormatOfDate($past_months)
{
    $relative_time = time();
    $monthsarr = array();
    $start = '';
    $end = '';
    $months = array();

    for ($i = 0; $i < $past_months; $i++) {
        // Get first day of current month
        $first_day = date("Y-m-01", $relative_time);
        // Get last day of current month
        $last_day = date("Y-m-t", $relative_time);
        // Add month label (e.g., 2025-06)
        $months[] = date("Y-m", $relative_time);

        if ($i == 0) {
            $end = $last_day;
        }

        if ($i == $past_months - 1) {
            $start = $first_day;
        }

        $monthsarr[] = array($first_day, $last_day);

        // Go back 1 month
        $relative_time = strtotime("-1 month", $relative_time);
    }

    return array("months" => $months, "start" => $start, "end" => $end, "month_ranges" => $monthsarr);
}

function gstinCheckVerify($gstin)
{
    if (!is_string($gstin)) return false;

    $gstin = strtoupper(trim($gstin));
    $gstin = preg_replace('/[^A-Z0-9]/', '', $gstin); // remove unwanted chars

    if (strlen($gstin) !== 15) return false;

    $base = 36;
    $chars = str_split($gstin);
    $first14 = array_slice($chars, 0, 14);
    $checkChar = array_pop($chars); // last char
    $sum = 0;
    $factor = 2;

    // process from right to left (IMPORTANT)
    for ($i = count($chars) - 1; $i >= 0; $i--) {
        $ch = $chars[$i];

        if ($ch >= '0' && $ch <= '9') {
            $val = ord($ch) - 48;
        } else {
            $val = ord($ch) - 55; // A=10 ... Z=35
        }

        $product = $val * $factor;

        if ($product >= $base) {
            $product = ($product % $base) + 1;
        }

        $sum += $product;

        // alternate factor 2,1
        $factor = ($factor == 2) ? 1 : 2;
    }

    $remainder = $sum % $base;
    $checkCode = ($base - $remainder) % $base;

    $expected = ($checkCode < 10) ? (string) $checkCode : chr($checkCode + 55);

    return $checkChar === $expected;
}

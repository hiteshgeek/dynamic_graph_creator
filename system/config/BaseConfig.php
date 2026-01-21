<?php

/**
 * Base Configuration - Copied from live project (rapidkartprocessadminv2)
 * Some constants commented out as they use hardcoded values or are not needed in DGC
 *
 * @author Sohil Gupta
 * @since 20140621
 */
class BaseConfig
{
    const QUERY_RESULT_PAGE_SIZE = 10;

    /** Is the site in a specific folder within your web directory */
    const SITE_PATH = "/var/www/html/";
    // const SITE_FOLDER = "rapidkartprocessadminv2";
    // const CONFIG_FOLDER = "rapidkartprocessadminv2";
    const SITE_FOLDER = "dynamic_graph_creator";
    const CONFIG_FOLDER = "dynamic_graph_creator";

    public static $domain_name = "http://localhost";

    /* Home URL */
    const HOME_URL = "home";

    public static $customization_box = "StencilCustomizationBox";
    public static $if_customized = TRUE;

    const LIVE_NOTIFICATION = false;

    /* Notification Server */
    const NOTIFICATION_SERVER = "";
    const IF_BILL_PENDING = FALSE;

    /* Database Access Information - Using SystemConfig instead */
    // const DB_SERVER = "developerdb.sixorbit.com";
    // const DB_USER = "sixorbit_admin";
    // const DB_PASS = "sixorbit_admin";
    // const DB_NAME = "rapidkart_factory";

    const ORDER_PREFIX = "TRY";

    /** Query result page size (moved back from LocalProjectConfig) */

    /* Themes Information */
    const THEME = "default";
    const ADMIN_THEME = "default";

    /* Value used to as a salt when hashing passwords */
    const PASSWORD_SALT = "K<47`5n9~8H5`*^Ks.>ie5&";

    /**
     * Files directory and whether the given directory is relative to the base directory of the system.
     */
    // const FILES_DIR = "/var/www/html/rpkfiles/";
    // const FILES_DIR_RELATIVE = false;
    // const FILES_URL = "/rpkfiles/";
    // const FILES_URL_RELATIVE = false;

    const MONETARY_LOCAL = "en_IN";

    public static $company_id = 0;
    public static $licence_id = 0;
    public static $google_cloud_platform_server_key = "AIzaSyBIQr4PLy8xxksNf0t_9RF-m2m1PC3JvxU";
    public static $google_cloud_platform_android_key = "AIzaSyBE7AW8LDJEFHYTjK_brLwE-Th7Zlankps";
    public static $ecom_customer_id = 410055775;
    public static $ecom_bank_alid = 410067483;
    public static $shipping_ecid = 410000177;
    public static $giftwrap_ecid = 410000178;
    public static $sales_channel = "Amazon.com";
    public static $company_start_date = "";
    public static $company_gstr_date = "";

    /*
         * FCM
         */
    public static $fcm_server_key = 'AAAAd_cpPDE:APA91bEpTza56uKsRnWdAYotH0n8JYl6pYv3ZlhgXszV45rOTitvL_Z_FfL8pZG5fPtX4l54CCF3Zpdymk8c33Ui7Fat2rXEdirpZvtsDEaOPElzXf894ezTklDF_Fl6c9ZAydQtkscG';

    /*
         * Gst credentials
         */
    public static $server_ip = '122.171.142.163';
    public static $gst_cliend_id = "808d7ac5-d25c-4716-bb74-2cc152b5d67f";
    public static $gst_cliend_secret = "1cf24584-e9ac-45bf-9f43-5c650cd9a851";
    public static $gst_email = "rajeev@accreteglobus.com";

    /**
     * Eway Credentials
     */
    public static $eway_client_id = '816d9831-169f-4108-8260-66efab02b282';
    public static $eway_client_secret = '4b32c788-64bb-471c-8b65-cfd15fc1f144';

    /**
     * E-Invoicing Credentials
     */
    public static $einvoicing_client_id = "331f7a16-5f4f-4d8e-83ed-982b5164a0d7";
    public static $einvoicing_client_secret = "35393408-083a-4a77-a4f9-36b50a2548e6";

    /*
         * Wondersoft Credentials
         */
    public static $wondersoft_url = 'http://103.99.148.217:5002/eShopaidAPI/eShopaidService.svc/';
    public static $wondersoft_username = 'WondersoftTally';
    public static $wondersoft_password = 'Wondersoft#12';
}

// Define DB constants from LocalProjectConfig for DGC dev environment
define('DB_SERVER', LocalProjectConfig::getDbHost());
define('DB_USER', LocalProjectConfig::getDbUser());
define('DB_PASS', LocalProjectConfig::getDbPass());
define('DB_NAME', LocalProjectConfig::getDbName());

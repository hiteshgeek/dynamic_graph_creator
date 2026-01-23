<?php

    /**
     * Provides system level functionality
     *
     * @author Sohil Gupta
     * @since 20140624
     */
    class System
    {

        /**
         * Redirects the user to a specified URL. 
         * If no URL is set, redirects the user to any return URL or to the home page
         * 
         * @param [optional] $url A URL to redirect to in the case no parameter is set
         */
        public static function redirect($url = null)
        {
            if (isset($url))
            {
                $location = $url;
            }
            else if (isset($_GET['return_url']))
            {
                $location = $_GET['return_url'];
            }
            else
            {
                $location = SystemConfig::baseUrl();
            }

            /* Redirect to the specified URL */
            header("Location: $location");
            exit();
        }

        /**
         * Redirects the user to a specified URL
         * 
         * @param String $url An internal URL to redirect the user to
         */
        public static function redirectInternal($url)
        {
            $redirect_url = JPath::fullUrl($url);
            self::redirect($redirect_url);
        }

        /**
         * Loads all the permission of the logged in user
         */
        public static function loadPids()
        {
            $db = Rapidkart::getInstance()->getDB();
            $uid = session::loggedInUid();
            $sql = "SELECT ap.pid FROM " . SystemTables::DB_TBL_USER_ROLE . " ar INNER JOIN " . SystemTables::DB_TBL_ROLE_PERMISSION . " ap ON ar.rid = ap.rid WHERE ar.uid = ::uid";
            $rs = $db->query($sql, array('::uid' => $uid));

            $pid = array();

            while ($row = $db->fetchArray($rs))
            {
                $pid[$row['pid']] = true;
            }
            return $pid;
        }

        /**
         * Load all the Rids of the user
         */
        public static function loadUserRids()
        {
            $db = Rapidkart::getInstance()->getDB();
            $sql = "SELECT rid FROM " . SystemTables::DB_TBL_USER_ROLE . " WHERE uid = '::uid'";
            $rs = $db->query($sql, array("::uid" => Session::loggedInUid()));
            $rid = array();
            if (!$rs)
            {
                return false;
            }
            while ($row = $db->fetchObject($rs))
            {
                $rid[$row->rid] = true;
            }
            return $rid;
        }

        /**
         * stores the activity log by the user
         */
        public static function saveActivityLogs($id)
        {
            
        }

    }
    
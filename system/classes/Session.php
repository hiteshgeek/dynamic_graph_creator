<?php

    /**
     * Manages the current php session
     * 
     * @author Sohil Gupta
     * @since 20121212
     * @updated 20140616
     */
    class Session
    {

        /**
         * Initialize the session
         */
        public static function init()
        {
            session_start();

            /* Load user data from cookies if the user is not logged in */
            if (!Session::isLoggedIn())
            {
                /* If the user is not logged in, try loading the session and login data from cookies */
                Session::loadDataFromCookies();
            }
            else
            {
//                $users = AdminUserManager::getAllUsers();
//                if (!$users)
//                {
//                    /* The session is non-existent, delete it */
//                    session_destroy();
//                }
//                else
//                {
//                    $a = array();
//                    foreach ($users as $user)
//                    {
//                        $a[] = $user->uid;
//                    }
//                    if (!in_array(Session::loggedInUid(), $a))
//                    {
////                        session_destroy();
//                    }
//                }
//                Session::checkCookies();
            }
        }

        /**
         * Destroy the current session 
         */
        public static function destroy()
        {
            session_destroy();
        }

        public static function checkCookies()
        {
            if (!isset($_COOKIE['jsmartsid']))
            {
                setcookie("jsmartsid", session_id(), time() + 3600 * 300, "/");
            }
            if (!isset($_COOKIE['user-id']))
            {
                setcookie("user-id", Session::loggedInUid(), time() + 3600 * 300, "/");
            }
            if (!isset($_COOKIE['session-id']))
            {
                setcookie("session-id", SessionsManager::getAuserSessionId(), time() + 3600 * 300, "/");
            }
            if (!isset($_COOKIE['node-server']))
            {
                setcookie("node-server", BaseConfig::NOTIFICATION_SERVER, time() + 3600 * 300, "/");
            }
            return TRUE;
        }

        // For Service Api
        public static function loginMobileUser($user, $mac_addr = '', $fcm_token = '', $fcmskid = 0)
        {
            $sid = rand(3989937964795391492, 6989937964795391492);
            $args = array(
                "::uid" => $user->getId(),
                "::sid" => $sid,
                "::ipaddress" => $_SERVER['REMOTE_ADDR'],
                "::ussid" => 1,
                "::login_type" => 2,
                "::data" => json_encode($_SERVER),
                "::mac_addr" => $mac_addr,
                "::fcm_token" => $fcm_token,
                "::fcmskid" => $fcmskid,
                '::compid' => BaseConfig::$company_id
            );

            $licence_company = new LicenceCompanies(BaseConfig::$company_id);
            $args['::licid'] = $licence_company->getLicid();
            $db = Rapidkart::getInstance()->getDB();
            $sql = "INSERT INTO " . SystemTables::DB_TBL_USER_SESSION . " (uid, sid, ipaddress, ussid, login_type, data, mac_addr,company_id,fcm_token, fcmskid, licid) VALUES('::uid', '::sid', '::ipaddress', '::ussid', '::login_type', '::data', '::mac_addr','::compid','::fcm_token', '::fcmskid', '::licid')";
            $res = $db->query($sql, $args);
            if (!$res)
            {
                return FALSE;
            }

            return $sid;
        }

        public static function getUsid($sid, $uid)
        {
            $args = array(
                "::sid" => $sid,
                "::uid" => $uid,
                '::licid' => BaseConfig::$licence_id
            );
            $db = Rapidkart::getInstance()->getDB();
            $sql = "SELECT * FROM " . SystemTables::DB_TBL_USER_SESSION . " WHERE uid = '::uid' AND sid = '::sid' AND licid= '::licid'";
            $res = $db->query($sql, $args);
            if (!$res || $db->resultNumRows() < 1)
            {
                return FALSE;
            }
            $row = $db->fetchObject($res);
            return $row->usid;
        }

        public static function checkLogin($user, $access_token)
        {
            $args = array(
                "::uid" => $user,
                "::sid" => $access_token,
                "::ussid" => 1,
                '::licid' => BaseConfig::$licence_id
            );
            $db = Rapidkart::getInstance()->getDB();
            $sql = "SELECT * FROM " . SystemTables::DB_TBL_USER_SESSION . " WHERE uid = '::uid' AND sid = '::sid' AND ussid='::ussid' AND licid= '::licid'";
            $res = $db->query($sql, $args);
            if (!$res || $db->resultNumRows() < 1)
            {
                return FALSE;
            }
            return TRUE;
        }

        public static function updateSessionFCMToken($user, $access_token, $fcm_token)
        {
            $args = array(
                "::uid" => $user,
                "::sid" => $access_token,
                "::fcm_token" => $fcm_token,
                '::licid' => BaseConfig::$licence_id
            );
            $db = Rapidkart::getInstance()->getDB();
            /* Set the session's ussid to 0 in the database */
            $db->query("UPDATE " . SystemTables::DB_TBL_USER_SESSION . " SET fcm_token = '::fcm_token' WHERE uid='::uid' AND sid='::sid' AND licid= '::licid' ", $args);
            if ($db->affectedRows() > 0)
            {
                return true;
            }
            else
            {
                return false;
            }
        }

        public static function isLogout($data)
        {
             $args = array(
                "::uid" => $data['user_id'],
                "::sid" => $data['access_token'],
                '::licid' => BaseConfig::$licence_id
            );
            $db = Rapidkart::getInstance()->getDB();
            /* Set the session's ussid to 0 in the database */
            $db->query("UPDATE " . SystemTables::DB_TBL_USER_SESSION . " SET ussid = 2 WHERE uid='::uid' AND sid='::sid' AND licid= '::licid' ", $args);
            if ($db->affectedRows() > 0)
            {
                return true;
            }
            else
            {
                return false;
            }
        }

        public static function checkLoginUserExistent($user)
        {
            $args = array(
                "::uid" => $user,
                "::ussid" => 1,
                "::login_type" => 2,
                '::licid' => BaseConfig::$licence_id
            );
            $db = Rapidkart::getInstance()->getDB();
            $sql = "SELECT * FROM " . SystemTables::DB_TBL_USER_SESSION . " WHERE uid = '::uid' AND ussid='::ussid' AND login_type='::login_type' AND licid= '::licid'";
            $res = $db->query($sql, $args);
            if (!$res || $db->resultNumRows() < 1)
            {
                return FALSE;
            }
            return TRUE;
        }

        /**
         * Creates a new session and logs in a user
         * 
         * @param User The user to log in
         */
        public static function loginUser($user, $verify_otp = FALSE)
        {
            session_regenerate_id(true);
            $_SESSION['uid'] = $user->getId();
            if (!$verify_otp)
            {
                $_SESSION['logged_in'] = TRUE;
                $_SESSION['ussid'] = 1;
            }
            else
            {
                $_SESSION['logged_in'] = FALSE;
                $_SESSION['ussid'] = 2;
            }
            $session_id = session_id();
            $_SESSION['company_id'] = $user->getCompanyId();
            BaseConfig::$company_id = $user->getCompanyId();
            $_SESSION['licence_id'] = BaseConfig::$licence_id;
            $_SESSION['logged_in_email'] = $user->getEmail();
            /* Add the necessary data to the class */
            $_SESSION['ipaddress'] = $_SERVER['REMOTE_ADDR'];
            $_SESSION['accessToken'] = $session_id;
            $_SESSION['outlet_chkid'] = 0;

            /* Now we create the necessary cookies for the user and save the session data */
            setcookie("jsmartsid", $session_id, time() + 3600 * 300, "/");

            $_SESSION['verify_otp'] = $verify_otp;

            /* Save the entire session data to the database */
            $args = array(
                "::uid" => $_SESSION['uid'],
                "::sid" => $session_id,
                "::ipaddress" => $_SESSION['ipaddress'],
                "::ussid" => $_SESSION['ussid'],
                "::data" => json_encode($_SESSION),
                '::compid' => $user->getCompanyId(),
                "::licid" => BaseConfig::$licence_id
            );

            /* Save the session data to the database */
            $db = Rapidkart::getInstance()->getDB();
            $sql = "INSERT INTO " . SystemTables::DB_TBL_USER_SESSION . " (uid, sid, ipaddress, ussid, data,company_id , licid) VALUES('::uid', '::sid', '::ipaddress', '::ussid', '::data','::compid', '::licid')";
            $db->query($sql, $args);

            $_SESSION['usid'] = SessionsManager::getAuserSessionId();
            $_SESSION['last_logged_in'] = date('Y-m-d H:i:s');
            setcookie("user-id", Session::loggedInUid(), time() + 3600 * 300, "/");
            setcookie("session-id", $_SESSION['usid'], time() + 3600 * 300, "/");
            setcookie("node-server", BaseConfig::NOTIFICATION_SERVER, time() + 3600 * 300, "/");
            setcookie("last-usage", time(), time() + 3600 * 300, "/");
        }

        /**
         * @return The access Token of the logged in user
         */
        public static function loggedInAccessToken()
        {
            return isset($_SESSION['accessToken']) ? $_SESSION['accessToken'] : false;
        }

        /**
         * Function that validates access token of a user
         * 
         * @param $uid The id of the user
         * @param $accessToken The access token
         */
        public static function validateAccessToken($uid, $accessToken)
        {
            if (!valid($uid) || !valid($accessToken))
            {
                return false;
            }

            $db = Rapidkart::getInstance()->getDB();

            $sql = "SELECT * FROM " . SystemTables::DB_TBL_USER_SESSION . " WHERE sid = '::sid' AND ussid = '1' AND uid = '::uid' AND licid= '::licid' LIMIT 1";
            $args = array(
                "::sid" => $accessToken,
                "::uid" => $uid,
                '::licid' => BaseConfig::$licence_id
            );

            $res = $db->query($sql, $args);
            if (!$res || $db->resultNumRows($res) < 1)
            {
                return FALSE;
            }
            return TRUE;
        }

        /**
         * Try to load the user's data from cookies 
         * 
         * @return Boolean whether the load was successful or not
         */
        public static function loadDataFromCookies()
        {
            if (!isset($_COOKIE['jsmartsid']))
            {
                return false;
            }

            /* If there is a cookie, check if there exists a valid database session and load it */
            $db = Rapidkart::getInstance()->getDB();

            $res = $db->query("SELECT * FROM " . SystemTables::DB_TBL_USER_SESSION . " WHERE sid='::sid'  LIMIT 1", array("::sid" => $_COOKIE['jsmartsid'], '::compid' => BaseConfig::$company_id));
            if ($db->resultNumRows() < 1)
            {
                /* The session is non-existent, delete it */
                self::invalidateSessionCookie();
                return false;
            }

            /* Session is existent, lets get it's data */

            $row = $db->fetchObject($res);

            if ($row->ussid != 1)
            {
                /* Session hasexipred, invalidate it */
                self::invalidateSessionDB($_COOKIE['jsmartsid']);
                self::invalidateSessionCookie();
                return false;
            }


            if (!self::checkLastUsage())
            {
                return false;
            }

            /* The session is valid, Load all of the data into session, generate a new sid and update it in the database */
            $data = json_decode($row->data, true);
            foreach ($data as $key => $value)
            {
                $_SESSION[$key] = $value;
            }

            /* Add the necessary data to the class */
            session_regenerate_id(true);
            $_SESSION['ipaddress'] = $_SERVER['REMOTE_ADDR'];
            $session_id = session_id();

            /* update the session id to the database */
//            $args = array("::usid" => $row->usid, "::sid" => $session_id);
//            $res = $db->query("UPDATE " . SystemTables::DB_TBL_USER_SESSION . " SET sid = '::sid' WHERE usid='::usid'", $args);
//            if (!$res)
            {
//                return FALSE;
            }

//            setcookie("jsmartsid", $session_id, time() + 3600 * 300, "/");
            setcookie("user-id", Session::loggedInUid(), time() + 3600 * 300, "/");
            setcookie("session-id", SessionsManager::getAuserSessionId(), time() + 3600 * 300, "/");
            setcookie("node-server", BaseConfig::NOTIFICATION_SERVER, time() + 3600 * 300, "/");
            setcookie("last-usage", time(), time() + 3600 * 300, "/");
            return TRUE;
        }

        /**
         * Logout the user and destroy the session 
         */
        public static function logoutUser()
        {
            /* Invalidate the database session */
            self::invalidateSessionDB($_COOKIE['jsmartsid']);
            self::invalidateSessionCookie();

            /* Destroy the session variables */
            unset($_SESSION['uid']);
            unset($_SESSION['logged_in']);
            unset($_SESSION['logged_in_email']);
            unset($_SESSION['ipaddress']);
            unset($_SESSION['ussid']);
            unset($_SESSION['user-id']);
            unset($_SESSION['chat-id']);
            unset($_SESSION['session-id']);

            /* Destroy the PHP Session */
            self::destroy();
        }

        public static function lockUser()
        {
            $_SESSION['logged_in'] = false;
            self::invalidateSessionDB($_COOKIE['jsmartsid']);
            self::invalidateSessionCookie();

            /* Destroy the session variables */
            unset($_SESSION['logged_in']);
        }

        public static function isUserLocked()
        {
            return (isset($_SESSION['uid']) && isset($_SESSION['ussid']) && $_SESSION['ussid'] === 1);
        }

        /**
         * Invalidate a session from the database
         * 
         * @param $session_id The id of the session to invalidate
         */
        public static function invalidateSessionDB($session_id)
        {
            $db = Rapidkart::getInstance()->getDB();
            /* Set the session's ussid to 0 in the database */
            $db->query("UPDATE " . SystemTables::DB_TBL_USER_SESSION . " SET ussid = '2' WHERE sid='::sid' AND licid= '::licid' ", array("::sid" => $session_id, '::licid' => BaseConfig::$licence_id));
        }

        /**
         * Invalidate the current session cookie
         */
        public static function invalidateSessionCookie()
        {
            setcookie("user-id", "", time() - 3600);
            setcookie("session-id", "", time() - 3600);
            setcookie("node-server", "", time() - 3600);
            setcookie("jsmartsid", "", time() - 3600);
        }

        /**
         * Checks whether a user is logged in
         * 
         * @return Boolean - Whether the user is logged in or not
         */
        public static function checkLastUsage()
        {
            $setting_min = getSettings("IS_INACTIVE_SESSION_TIME_IN_MINUTE");
            if ($setting_min > 0)
            {
                $min_interval = $setting_min * 60; // 30 minutes in seconds
                // Check if the 'last-usage' cookie is set
                if (isset($_COOKIE['last-usage']) && $_COOKIE['last-usage'] > 0)
                {
                    // Get the current time and the last usage time from the cookie
                    $current_time = time();
                    $last_usage = $_COOKIE['last-usage'];

                    // Calculate the time difference
                    $time_difference = $current_time - $last_usage;

                    // Check if the time difference is greater than 30 minutes
                    if ($time_difference > $min_interval)
                    {
                        self::logoutUser();
                        return false;
                    }
                }
//                else
//                {
//                    self::logoutUser();
//                    return false;
//                }
                setcookie("last-usage", time(), time() + 3600 * 300, "/");
                return true;
            }
            else
            {
                setcookie("last-usage", time(), time() + 3600 * 300, "/");
                return true;
            }
        }

        public static function isLoggedIn($check_usage = false)
        {
            if (isset($_SESSION['logged_in']) && ($_SESSION['logged_in'] === true))
            {
                if ($check_usage)
                {
                    if (self::checkLastUsage())
                    {
                        return true;
                    }
                    else
                    {

                        return false;
                    }
                }
                else
                {
                    return true;
                }
            }
            else
            {
                return false;
            }
        }

        /**
         * @return The uid of the logged in user
         */
        public static function loggedInUid()
        {
            return isset($_SESSION['uid']) ? $_SESSION['uid'] : false;
        }

        /**
         * Check whether the two step verification is enabled or not
         * 
         * @return boolean
         */
        public static function isSecondStepVerification()
        {
            return isset($_SESSION['verify_otp']) ? $_SESSION['verify_otp'] : false;
        }

        public static function updateOutlet($data)
        {
            $session_id = $_COOKIE['jsmartsid'];
            $db = Rapidkart::getInstance()->getDB();
            $_SESSION['outlet_chkid'] = $data['val'];
            $sql = "UPDATE " . SystemTables::DB_TBL_USER_SESSION . " SET outlet_chkid = '::chkid' WHERE sid='::sid' AND licid= '::licid' ";
            $args = array("::sid" => $session_id, '::licid' => BaseConfig::$licence_id, '::chkid' => $data['val']);
            /* Set the session's ussid to 0 in the database */
            $res = $db->query($sql, $args);
            Utility::ajaxResponseTrue("Outlet Updated Successfully");
        }

        public static function updateCompanyId($data)
        {
            $session_id = $_COOKIE['jsmartsid'];
            $db = Rapidkart::getInstance()->getDB();
            $sql = "UPDATE " . SystemTables::DB_TBL_USER_SESSION . " SET company_id = '::company_id' WHERE sid='::sid' AND licid= '::licid' ";
            $args = array("::sid" => $session_id, '::licid' => BaseConfig::$licence_id, '::company_id' => $data['company']);
            /* Set the session's ussid to 0 in the database */
            $res = $db->query($sql, $args);
            return $res ? true : false;
        }

        public static function getSessionVariable()
        {
            $session_id = isset($_COOKIE['jsmartsid']) ? $_COOKIE['jsmartsid'] : 0;
            $db = Rapidkart::getInstance()->getDB();
            $sql = "SELECT company_id , outlet_chkid FROM " . SystemTables::DB_TBL_USER_SESSION . " WHERE sid='::sid' AND licid= '::licid' ";
            $args = array("::sid" => $session_id, '::licid' => BaseConfig::$licence_id);
            /* Set the session's ussid to 0 in the database */
            $res = $db->query($sql, $args);
            if (!$res || $db->resultNumRows($res) < 1)
            {
                return array('company_id' => 0, 'outlet_chkid' => 0);
            }
            $row = $db->fetchObject($res);
            return array('company_id' => $row->company_id, 'outlet_chkid' => $row->outlet_chkid);
        }

        public static function updateLastLoggedIn()
        {
            $session_id = $_COOKIE['jsmartsid'];
            $db = Rapidkart::getInstance()->getDB();
            $sql = "UPDATE " . SystemTables::DB_TBL_USER_SESSION . " SET last_logged_bit = 1 WHERE sid='::sid' AND licid= '::licid' ";
            $args = array("::sid" => $session_id, '::licid' => BaseConfig::$licence_id);
            /* Set the session's ussid to 0 in the database */
            $res = $db->query($sql, $args);
            return $res ? true : false;
        }
    }
    

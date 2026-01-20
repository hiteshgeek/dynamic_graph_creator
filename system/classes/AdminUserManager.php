<?php

    /**
     * Manages Users on the basis of Filters
     *
     * @author Sohil Gupta
     * @since 20140629
     * @updatedBy Sohil Gupta @UpdatedOn 21/09/2015
     * @updtaedBy Sohil Gupta @updatedOn 24/09/2015
     * @updtaedBy Chethan @updatedOn 14/12/2015
     */
    class AdminUserManager
    {

        /**
         * Get All the Admin Users
         * 
         * @return Array Set of Users
         */
        public static function sendNotifications($user, $title = '', $message = '', $custom_data = array(), $type = 100)
        {
//            return true;
            if (strlen($message) == 0 || strlen($title) == 0 || $user <= 0)
            {
                return false;
            }
            $user = new AdminUser($user);
            $user_data = AdminUserManager::getActiveAppUsersSessionsById($user->getId());
            $fcm_token_arr = array();
            if ($user_data && is_array($user_data) && count($user_data) > 0)
            {
                foreach ($user_data as $uKey => $uVal)
                {
                    if (!empty($uVal->fcm_token))
                    {
                        $fcm_token_arr[] = $uVal->fcm_token;
                    }
                }
            }


            //Set Message                    
            $notification_msg = $message;
            if (!is_array($custom_data))
            {
                $custom_data = array();
            }
            $custom_data['company_id'] = BaseConfig::$company_id;

            $fcm_obj = new Fcm();
            $fcm_obj->setTo($fcm_token_arr);
            $fcm_obj->setType($type);
            $fcm_obj->setMsg($notification_msg);
            $fcm_obj->setCustomData(($custom_data));
            $fcm_obj->setTitle($title);
            $fcm_obj->setIcon('ic_launcher');
            if ($fcm_obj->sendNotification())
            {
                return true;
            }
            else
            {
                return false;
            }
        }

        public static function getUsers($name = null, $active = FALSE, $uid = NULL, $licid = false, $is_admin = false, $customer_attendee = false, $order_attendee = false, $report_to_attendee = false, $invoice_attendee = false, $licence_all_users = false, $not_admin = false)
        {
            $companies = LicenceManager::getCompanies(BaseConfig::$licence_id);
            $count = 0;
            if ($companies)
            {
                $count = count($companies);
            }
            $db = Rapidkart::getInstance()->getDB();
            $args = array();
            $sql = "SELECT uid,name FROM " . SystemTables::DB_TBL_USER . " WHERE ustatusid!= '3'  ";
            if ($licence_all_users)
            {
                $sql .= " AND licid = " . BaseConfig::$licence_id;
            }
            elseif ($licid)
            {
                $sql .= " AND licid = " . BaseConfig::$licence_id;

                if ($count > 1)
                {
                    $sql .= " AND uid IN(SELECT uid FROM  " . SystemTables::DB_TBL_USER_COMPANY_MAPPING . ' WHERE company_id=' . BaseConfig::$company_id . ")";
                }
            }
            else
            {
                $sql .= " AND company_id = " . BaseConfig::$company_id;
            }
            $company_based = 0;
            $extra_condition = "";
            if (getSettings("IS_COMPANY_BASED_USERS_SEARCH"))
            {
                $company_based = 1;
                $extra_condition = " AND company_id = " . BaseConfig::$company_id;
                $sql .= " AND company_id = " . BaseConfig::$company_id;
            }
            if ($name)
            {
                $args['::name'] = $name;
                $sql .= "  AND name LIKE('%::name%')";
            }
            if ($active)
            {
                $sql .= "  AND ustatusid!= '2'";
            }
            if ($uid)
            {
                $args['::uid'] = $uid;
                $sql .= " AND uid != '::uid'";
            }
            if ($is_admin)
            {
                $sql .= " AND is_admin = 1 ";
            }
            if ($not_admin)
            {
                $sql .= " AND is_admin <> 1 ";
            }
            if ($customer_attendee)
            {
                $sql .= " AND uid IN(SELECT uid FROM customer_user_mapping WHERE uid > 0 $extra_condition)";
            }
            if ($order_attendee)
            {
                $sql .= " AND uid IN(SELECT assigned_uid FROM checkpoint_order_attendee_mapping WHERE assigned_uid > 0 $extra_condition)";
            }
            if ($report_to_attendee)
            {
                $sql .= " AND uid IN(SELECT report_to FROM auser WHERE report_to > 0 $extra_condition)";
            }
            if ($invoice_attendee)
            {
                $sql .= " AND uid IN(SELECT assigned_uid FROM invoice_attendee_mapping WHERE assigned_uid > 0 $extra_condition)";
            }
            $res = $db->query($sql, $args);
            $users = array();
            if (!$res || $db->resultNumRows() < 1)
            {
                return false;
            }
            if ($name)
            {
                while ($row = $db->fetchObject($res))
                {
                    $users[] = array("id" => $row->uid, "name" => $row->name);
                }
            }
            else
            {
                while ($row = $db->fetchObject($res))
                {
                    $users[$row->uid] = new AdminUser($row->uid);
                }
            }

            return $users;
        }

        /**
         * Get All the Admin Users of a specifice role
         * 
         * @param Integer $rid Role Id
         * 
         * @return Array List of Users 
         */
        public static function getUsersFilteredName($name = NULL)
        {
            $db = Rapidkart::getInstance()->getDB();

            $users = array();
            $args = array("::name" => "%" . $name . "%", '::licid' => BaseConfig::$licence_id);
            if ($name)
            {
                $sql = "SELECT u.uid FROM " . SystemTables::DB_TBL_USER . " u LEFT JOIN " . SystemTables::DB_TBL_USER_ROLE . " ur ON ((u.uid = ur.uid) AND (u.company_id = ur.company_id)) where name LIKE('::name') and (ustatusid='1' OR ustatusid  = '4') AND u.company_id = " . BaseConfig::$company_id . " AND licid = '::licid'";
            }
            else
            {
                $sql = "SELECT u.uid FROM " . SystemTables::DB_TBL_USER . " u LEFT JOIN " . SystemTables::DB_TBL_USER_ROLE . " ur ON ((u.uid = ur.uid) AND (u.company_id = ur.company_id)) where (ustatusid='1' OR ustatusid  = '4') AND u.company_id = " . BaseConfig::$company_id . " AND licid = '::licid'";
            }
            $res = $db->query($sql, $args);

            if (!$res || $db->resultNumRows($res) < 1)
            {
                return false;
            }
            while ($row = $db->fetchObject($res))
            {
                $users[$row->uid] = new AdminUser($row->uid);
            }
            return $users;
        }

        /**
         * Get all the statuses from the database
         * 
         * @return Array of the objects of statuses
         */
        public static function getStatuses()
        {
            $db = Rapidkart::getInstance()->getDB();
            $sql = "SELECT ustatusid,name,description FROM " . SystemTables::DB_TBL_USER_STATUS . " WHERE ustatustid = 0";
            $res = $db->query($sql);
            if (!$res || $db->resultNumRows($res) < 1)
            {
                return false;
            }
            $status = array();
            while ($row = $db->fetchObject($res))
            {
                $status[$row->ustatusid] = $row;
            }
            return $status;
        }

        /**
         * Get the status name of a specific status id
         * 
         * @param Integer Status Id The Status Id to get the name for
         * 
         * @return String Name of the status id
         */
        public static function getStatusNameById($ustatusid)
        {
            $db = Rapidkart::getInstance()->getDB();
            $args = array("::ustatusid" => $ustatusid);
            $sql = "SELECT name FROM " . SystemTables::DB_TBL_USER_STATUS . " WHERE ustatusid='::ustatusid' AND ustatustid = 0";
            $res = $db->query($sql, $args);

            if (!$res || $db->resultNumRows($res) < 1)
            {
                return false;
            }
            $row = $db->fetchObject($res)->name;
            return $row;
        }

        public static function getAdminUserActivityLogs()
        {
            $sql = "SELECT log.aulid, au.name, ac.action, log.data, log.created_ts
                        FROM " . SystemTables::DB_TBL_USER_LOG . " log, " . SystemTables::DB_TBL_USER . " au, " . SystemTables::DB_TBL_USER_ACTION . " ac
                            WHERE log.auid = au.auid
                                AND ac.aulaid = log.aulaid AND au.company_id = " . BaseConfig::$company_id . " ";
            $db = Rapidkart::getInstance()->getDB();
            $rs = $db->query($sql);
            if (!$rs || $db->resultNumRows($rs) < 1)
            {
                return false;
            }
            $logs = array();

            while ($row = $db->fetchObject($rs))
            {
                $logs[$row->aulid] = $row;
            }
            return $logs;
        }

        /**
         * fetch out the udid on the basis of uid
         * 
         * @param $uid User Id
         */
        public static function getUdid($uid)
        {
            $db = Rapidkart::getInstance()->getDB();
//            $rs = $db->getFieldValue(SystemTables::DB_TBL_USER_DETAILS, "udid", "uid = $uid");
            return 0;
        }

        public static function getAdminUsersCount($condition = NULL)
        {
            $db = Rapidkart::getInstance()->getDB();
            if ($condition)
            {
                $sql = " SELECT count(uid) as count FROM " . SystemTables::DB_TBL_USER . " WHERE (" . $condition . ") AND company_id = " . BaseConfig::$company_id . "";
                $res = $db->query($sql);
            }
            else
            {
                $sql = " SELECT count(uid) as count FROM " . SystemTables::DB_TBL_USER . " WHERE company_id = " . BaseConfig::$company_id . "";

                $res = $db->query($sql);
            }
            if (!$res || $db->resultNumRows($res) < 1)
            {
                return FALSE;
            }

            $ret = $db->fetchObject($res);

            return $ret->count;
        }

        public static function getUserByrole()
        {
            $db = Rapidkart::getInstance()->getDB();
            $sql = "SELECT uid FROM " . SystemTables::DB_TBL_USER_ROLE . " WHERE rid = 10 AND company_id = " . BaseConfig::$company_id . "";
            $res = $db->query($sql);
            if (!$res || $db->resultNumRows($res) < 1)
            {
                return FALSE;
            }
            $ret = [];
            while ($row = $db->fetchObject($res))
            {
                $ret[$row->uid] = new AdminUser();
                $ret[$row->uid]->parse($row);
            }
            return $ret;
        }

        public static function searchAdminUsers($data = NUll, $forgotPassFlag = FALSE, $isadmin = NULL)
        {
            $db = Rapidkart::getInstance()->getDB();

            $userStatus = '1,2,4';
            if (getSettings("IS_ENABLED_TO_HIDE_INACTIVE_USER_IN_USER_SEARCH"))
            {
                $userStatus = '1,4';
            }

            if (!empty($data))
            {
                $sql = "SELECT uid,name,email,mobile,company_id , is_multipler FROM " . SystemTables::DB_TBL_USER . " WHERE  ustatusid IN(" . $userStatus . ") AND (name LIKE ('%::search%') OR email LIKE ('%::search%')) AND licid = '::licid'  ";
//                $sql .= ($forgotPassFlag) ? " " : " AND company_id = '::company_id' ";
                if (BaseConfig::$licence_id == 23 || BaseConfig::$licence_id == 122 || getSettings("IS_COMPANY_BASED_USERS_SEARCH"))
                {
                    $sql .= ($forgotPassFlag) ? " " : " AND company_id = '::company_id' ";
                }
                else
                {
                    $companies = LicenceManager::getCompanies(BaseConfig::$licence_id);
                    $count = 0;
                    if ($count > 1)
                    {
                        $sql .= " AND uid IN(SELECT uid FROM  " . SystemTables::DB_TBL_USER_COMPANY_MAPPING . ' WHERE company_id=' . BaseConfig::$company_id . ")";
                    }
                }
                if ($isadmin)
                {
                    $sql .= " AND is_admin = 1";
                }
                $args = array("::search" => $data, "::company_id" => BaseConfig::$company_id, '::licid' => BaseConfig::$licence_id);
                $res = $db->query($sql, $args);
            }
            else
            {
                $sql = "SELECT uid,name,email,mobile,company_id , is_multipler FROM " . SystemTables::DB_TBL_USER . " WHERE ustatusid IN(" . $userStatus . ") AND company_id = '::company_id' AND licid = '::licid' ";
                if ($isadmin)
                {
                    $sql .= " AND is_admin = 1";
                }
                $res = $db->query($sql, array("::company_id" => BaseConfig::$company_id, '::licid' => BaseConfig::$licence_id));
            }
            if (!$res || $db->resultNumRows($res) < 1)
            {
                return false;
            }
            $ret = array();
            while ($row = $db->fetchObject($res))
            {
                $ret[] = array("id" => $row->uid, "name" => $row->name, "email" => $row->email, "mobile" => $row->mobile, "company_id" => $row->company_id, 'is_multipler' => $row->is_multipler);
            }
            return $ret;
        }

        public static function CheckForExistingEid($eid, $udid = NULL)
        {
            $db = Rapidkart::getInstance()->getDB();
            $sql = "SELECT udid FROM " . SystemTables::DB_TBL_USER . " WHERE empid = '::eid' AND company_id = " . BaseConfig::$company_id . " ";
            $args = array('::eid' => $eid);
            if ($udid)
            {
                $sql .= " AND udid != '::udid'";
                $args['::udid'] = $udid;
            }
            $res = $db->query($sql, $args);

            if (!$res || $db->resultNumRows() < 1)
            {
                return false;
            }
            return true;
        }

        public static function isEmailInUse($email, $uid = NULL)
        {
            /* Checks if the email is available */
            $db = Rapidkart::getInstance()->getDB();
            if ($uid)
            {
                $args = array("::email" => $email, '::uid' => $uid);
                $sql = "SELECT email FROM " . SystemTables::DB_TBL_USER . " WHERE email = '::email' AND uid != '::uid' AND ustatusid != '3' AND company_id = " . BaseConfig::$company_id . "";
            }
            else
            {
                $args = array("::email" => $email);
                $sql = "SELECT email FROM " . SystemTables::DB_TBL_USER . " WHERE email = '::email' AND ustatusid != '3' AND company_id = " . BaseConfig::$company_id . "";
            }

            $res = $db->query($sql, $args);
            if (!$res || $db->resultNumRows($res) < 1)
            {
                return false;
            }
            else
            {
                return true;
            }
        }

        /**
         * Check whether the email is in use or not
         * 
         * @param string $email
         * @param int $uid
         * @param boolean $exclude_me
         * @return boolean
         */
        public static function isEmailIdInUse($email)
        {
            $db = Rapidkart::getInstance()->getDB();

            $sql1 = "SELECT email, 'A' AS table_name, uid FROM " . SystemTables::DB_TBL_USER . " WHERE email = '::email' AND company_id = " . BaseConfig::$company_id . " ";
            $sql2 = "SELECT email, 'B' AS table_name, uid FROM " . SystemTables::DB_TBL_AUSER_EMAIL_VERIFICATION . " WHERE email = '::email' AND company_id = " . BaseConfig::$company_id . " ";

            $args = array("::email" => $email);
            $sql = "(" . $sql1 . ") UNION (" . $sql2 . ")";

            $res = $db->query($sql, $args);
            if (!$res || $db->resultNumRows($res) < 1)
            {
                return FALSE;
            }

            $row = $db->fetchObject($res);
            return $row;
        }

        public static function isMobileNumberInUse($mobile)
        {
            $db = Rapidkart::getInstance()->getDB();

            $sql1 = "SELECT mobile, 'A' AS table_name, uid FROM " . SystemTables::DB_TBL_USER . " WHERE mobile = '::mobile' AND company_id = " . BaseConfig::$company_id . "";
            $sql2 = "SELECT mobile, 'B' AS table_name, uid FROM " . SystemTables::DB_TBL_AUSER_MOBILE_VERIFICATION . " WHERE mobile = '::mobile' AND company_id = " . BaseConfig::$company_id . "";

            $args = array("::mobile" => $mobile);
            $sql = "(" . $sql1 . ") UNION (" . $sql2 . ")";

            $res = $db->query($sql, $args);
            if (!$res || $db->resultNumRows($res) < 1)
            {
                return FALSE;
            }

            $row = $db->fetchObject($res);
            return $row;
        }

        public static function loadRoles($uid)
        {
            $db = Rapidkart::getInstance()->getDB();
            $args = array('::uid' => $uid);
            $sql = "SELECT ur.rid, r.name FROM " . SystemTables::DB_TBL_USER_ROLE . " ur LEFT JOIN " . SystemTables::DB_TBL_ROLE . " r ON ((r.rid = ur.rid) and r.licid = '::licid') WHERE uid='::uid'";
            $args['::licid'] = BaseConfig::$licence_id;
            $roles = $db->query($sql, $args);
            if (!$roles || $db->resultNumRows($roles) < 1)
            {
                return FALSE;
            }
            $ret = array();
            while ($role = $db->fetchObject($roles))
            {
                $ret[$role->rid] = $role->name;
            }
            return $ret;
        }

        public static function getUserRoles($uid)
        {
            $db = Rapidkart::getInstance()->getDB();
            $args = array('::uid' => $uid);
            $sql = "SELECT ur.rid, r.name FROM " . SystemTables::DB_TBL_USER_ROLE . " ur LEFT JOIN " . SystemTables::DB_TBL_ROLE . " r ON ((r.rid = ur.rid) AND (ur.company_id = r.company_id)) WHERE uid='::uid' AND ur.company_id = " . BaseConfig::$company_id . "";
            $roles = $db->query($sql, $args);
            if (!$roles || $db->resultNumRows($roles) < 1)
            {
                return array();
            }
            $ret = array();
            while ($role = $db->fetchObject($roles))
            {
                $ret[] = $role;
            }
            return $ret;
        }

        public static function loadPermissions($roles)
        {
            /* Load this user Permissions */

            $db = Rapidkart::getInstance()->getDB();

            $rids = implode(", ", array_keys($roles));
            $sql = "SELECT pid FROM " . SystemTables::DB_TBL_ROLE_PERMISSION . " WHERE rid IN ($rids) AND company_id = " . BaseConfig::$company_id . " AND licid = '::licid' ";
            $rs = $db->query($sql, array('::licid' => BaseConfig::$licence_id));
            if (!$rs || $db->resultNumRows($rs) < 1)
            {
                return FALSE;
            }
            $ret = array();
            while ($perm = $db->fetchObject($rs))
            {
                $ret[$perm->pid] = $perm;
            }
            return $ret;
        }

        public static function loadSessionDetails($uid)
        {
            $db = Rapidkart::getInstance()->getDB();

            $sql = "SELECT * FROM " . SystemTables::DB_TBL_USER_SESSION . " WHERE uid = '::uid' AND company_id = " . BaseConfig::$company_id . " order by created_ts DESC LIMIT 10";
            $arg = array("::uid" => $uid);

            $res = $db->query($sql, $arg);
            if (!$res || $db->resultNumRows($res) < 1)
            {
                return false;
            }

            $ret = array();
            while ($row = $db->fetchObject($res))
            {
                $ret[$row->usid] = new SessionDetails();
                $ret[$row->usid]->parse($row);
            }
            return $ret;
        }

        public static function getHref($id, $row, $row_id)
        {
            $link = new Link(JPath::absoluteUrl('admin_user/user/view/' . $row['user_id']), "", self::createInvoice($row['user_id']));
            return $link->publish();
        }

        public static function getStatusLabel($id, $row, $row_id)
        {
            $label = "";
            if ($id !== "")
            {
                switch ($id)
                {
                    case 1:
                        $label = "<span class='label label-success'>Active</span>";
                        break;
                    case 2:
                        $label = "<span class='label label-danger'>Inactive</span>";
                        break;
                    case 3:
                        $label = "<span class='label label-default'>Deleted</span>";
                        break;
                    case 4:
                        $label = "<span class='label label-warning'>Static</span>";
                        break;
                }
            }
            return $label;
        }

        public static function getOnlineUserCount()
        {
            $db = Rapidkart::getInstance()->getDB();
            $sql = " SELECT count(*) as count FROM  " . SystemTables::DB_TBL_USER_SESSION . " WHERE ussid = 1 AND date(created_ts) = CURDATE() AND company_id = " . BaseConfig::$company_id . "";
            $res = $db->query($sql);
            if (!$res || $db->resultNumRows($res) < 1)
            {
                return FALSE;
            }
            $row = $db->fetchObject($res);
            return $row->count;
        }

        public static function createInvoice($id)
        {
            return Utility::variableGet("site_code") . "AU" . str_pad($id, 6, "0", STR_PAD_LEFT);
        }

        public static function getAdminUserNameLink($admin_id, $row = NULL, $row_id = NULL)
        {
            if (!AdminUser::isExistent($admin_id))
            {
                return "-";
            }
            $auser = new AdminUser($admin_id);
            $link = new Link(JPath::absoluteUrl('admin_user/user/view/' . $admin_id), "", $auser->getName());
            return $link->publish();
        }

        /**
         * Get all the alternate emails
         * 
         * @param int $uid Logged in user is assumed by default
         * @return boolean|\AuserEmail
         */
        public static function getAdminUserEmails($uid = NULL)
        {
            $uid = $uid ? $uid : Session::loggedInUid();

            $db = Rapidkart::getInstance()->getDB();

            $sql = "SELECT * FROM " . SystemTables::DB_TBL_AUSER_EMAIL_VERIFICATION . " WHERE uid = '::uid' AND company_id = " . BaseConfig::$company_id . " ";
            $res = $db->query($sql, array('::uid' => $uid));
            if (!$res || $db->resultNumRows($res) < 1)
            {
                return FALSE;
            }

            $array = array();
            while ($row = $db->fetchObject($res))
            {
                $array[$row->auevid] = new AdminUserEmail();
                $array[$row->auevid]->parse($row);
            }
            return $array;
        }

        /**
         * Get all the alternate mobile numbers
         * 
         * @param int $uid Logged in user is assumed by default
         * @return boolean|\AuserMobile
         */
        public static function getAdminUserMobiles($uid = NULL)
        {
            $uid = $uid ? $uid : Session::loggedInUid();

            $db = Rapidkart::getInstance()->getDB();

            $sql = "SELECT * FROM " . SystemTables::DB_TBL_AUSER_MOBILE_VERIFICATION . " WHERE uid = '::uid' AND company_id = " . BaseConfig::$company_id . "";
            $res = $db->query($sql, array('::uid' => $uid));
            if (!$res || $db->resultNumRows($res) < 1)
            {
                return FALSE;
            }

            $array = array();
            while ($row = $db->fetchObject($res))
            {
                $array[$row->aumvid] = new AdminUserMobile();
                $array[$row->aumvid]->parse($row);
            }
            return $array;
        }

        /**
         * Get primary email from alternate emails
         * 
         * @param int $uid Logged in user is assumed by default
         * @return boolean|\AuserEmail
         */
        public static function getPrimaryEmail($uid = NULL)
        {
            $uid = $uid ? $uid : Session::loggedInUid();

            $db = Rapidkart::getInstance()->getDB();

            $sql = "SELECT * FROM " . SystemTables::DB_TBL_AUSER_EMAIL_VERIFICATION . " WHERE uid = '::uid' AND primary_email = '::primary_email' AND company_id = " . BaseConfig::$company_id . "";
            $args = array(
                '::uid' => $uid,
                '::primary_email' => ADMIN_USER_EMAIL_PRIMARY_STATE
            );

            $res = $db->query($sql, $args);
            if (!$res || $db->resultNumRows($res) < 1)
            {
                return FALSE;
            }

            $row = $db->fetchObject($res);
            $primary_email = new AdminUserEmail();
            $primary_email->parse($row);
            return $primary_email;
        }

        /**
         * Get primary mobile number from alternate numbers
         * 
         * @param int $uid Logged in user is assumed by default
         * @return boolean|\AuserMobile
         */
        public static function getPrimaryMobile($uid = NULL)
        {
            $uid = $uid ? $uid : Session::loggedInUid();

            $db = Rapidkart::getInstance()->getDB();

            $sql = "SELECT * FROM " . SystemTables::DB_TBL_AUSER_MOBILE_VERIFICATION . " WHERE uid = '::uid' AND primary_no = '::primary_no' AND company_id = " . BaseConfig::$company_id . "";
            $args = array(
                '::uid' => $uid,
                '::primary_no' => ADMIN_USER_MOBILE_PRIMARY_STATE
            );

            $res = $db->query($sql, $args);
            if (!$res || $db->resultNumRows($res) < 1)
            {
                return FALSE;
            }

            $row = $db->fetchObject($res);
            $primary_no = new AdminUserMobile();
            $primary_no->parse($row);
            return $primary_no;
        }

        /**
         * Make default email(Static) as primary
         * By removing primary email from alternate emails
         * 
         * @param int $uid Logged in user is assumed by default
         * @return boolean
         */
        public static function makeDefaultEmailAsPrimary($uid = NULL)
        {
            $uid = $uid ? $uid : Session::loggedInUid();

            $db = Rapidkart::getInstance()->getDB();

            $sql = "UPDATE " . SystemTables::DB_TBL_AUSER_EMAIL_VERIFICATION . " SET primary_email = '::primary_email' WHERE uid = '::uid' AND company_id = " . BaseConfig::$company_id . "";
            $args = array(
                '::uid' => $uid,
                '::primary_email' => ADMIN_USER_EMAIL_DEFAULT_STATE
            );

            $res = $db->query($sql, $args);
            if (!$res)
            {
                return FALSE;
            }
            return TRUE;
        }

        /**
         * Make default mobile number(Static) as primary
         * By removing primary number from alternate numbers
         * 
         * @param int $uid Logged in user is assumed by default
         * @return boolean
         */
        public static function makeDefaultMobileAsPrimary($uid = NULL)
        {
            $uid = $uid ? $uid : Session::loggedInUid();

            $db = Rapidkart::getInstance()->getDB();

            $sql = "UPDATE " . SystemTables::DB_TBL_AUSER_MOBILE_VERIFICATION . " SET primary_no = '::primary_no' WHERE uid = '::uid' AND company_id = " . BaseConfig::$company_id . "";
            $args = array(
                '::uid' => $uid,
                '::primary_no' => ADMIN_USER_MOBILE_DEFAULT_STATE
            );

            $res = $db->query($sql, $args);
            if (!$res)
            {
                return FALSE;
            }
            return TRUE;
        }

        /**
         * Get email verification statuses
         * 
         * @return boolean|array Array of objects
         */
        public static function getEmailStatuses()
        {
            $db = Rapidkart::getInstance()->getDB();

            $sql = "SELECT * FROM " . SystemTables::DB_TBL_AUSER_EMAIL_VERIFICATION_STATUS;

            $res = $db->query($sql);
            if (!$res || $db->resultNumRows($res) < 1)
            {
                return FALSE;
            }

            $array = array();
            while ($row = $db->fetchObject($res))
            {
                $array[$row->auevsid] = $row;
            }
            return $array;
        }

        /**
         * Get mobile verification statuses
         * 
         * @return boolean|array Array of objects
         */
        public static function getMobileStatuses()
        {
            $db = Rapidkart::getInstance()->getDB();

            $sql = "SELECT * FROM " . SystemTables::DB_TBL_AUSER_MOBILE_VERIFICATION_STATUS;

            $res = $db->query($sql);
            if (!$res || $db->resultNumRows($res) < 1)
            {
                return FALSE;
            }

            $array = array();
            while ($row = $db->fetchObject($res))
            {
                $array[$row->aumvsid] = $row;
            }
            return $array;
        }

        public static function isMobileAvailable($mobile, $uid = NULL)
        {
            $db = Rapidkart::getInstance()->getDB();

            $args = array("::mobile" => $mobile);
            $sql = " SELECT mobile FROM " . SystemTables::DB_TBL_USER . " WHERE mobile = '::mobile' AND company_id = " . BaseConfig::$company_id . "";

            if ($uid)
            {
                $sql .= " AND uid = '::uid'";
                $args['::uid'] = $uid;
            }

            $res = $db->query($sql, $args);
            if (!$res || !$db->resultNumRows($res) > 0)
            {
                return true;
            }
            else
            {
                return false;
            }
        }

        public static function getAdminUserUrl($id, $row = NULL, $row_id = NULL)
        {
            $link_href = "";
            $title = "";
            if (isset($row['uid']) && $row['uid'])
            {
                $link_href = JPath::absoluteUrl('admin_user/user/view/' . $row['uid']);
                $title = $id;
            }
            elseif (isset($row['created_uid']) && $row['created_uid'])
            {
                $link_href = JPath::absoluteUrl('admin_user/user/view/' . $row['created_uid']);
                $title = $id;
            }
            elseif (isset($row['updated_uid']) && $row['updated_uid'])
            {
                $link_href = JPath::absoluteUrl('admin_user/user/view/' . $row['updated_uid']);
                $title = $id;
            }
            elseif (isset($row['assigned_uid']) && $row['assigned_uid'])
            {
                $link_href = JPath::absoluteUrl('admin_user/user/view/' . $row['assigned_uid']);
                $title = $id;
            }
            $user = json_decode($id);
            if (is_object($user))
            {
                $link_href = JPath::absoluteUrl('admin_user/user/view/' . $user->id);
                $title = $user->name;
            }
            if ($link_href && $title)
            {
                $link = new Link($link_href, "", $title);
                return $link->publish();
            }
            return "NA";
        }

        public static function getAssigneeUserName($id, $row, $row_id)
        {
            return self::getAdminUserUrl($id, array('assigned_uid' => $row['assigned_uid']));
        }

        public static function getUpdatedUserName($id, $row, $row_id)
        {
            return self::getAdminUserUrl($id, array('updated_uid' => $row['updated_uid']));
        }

        public static function getOnlineUsersGraph($uid = NULL, $start, $end)
        {
            $db = Rapidkart::getInstance()->getDB();
            $condition = " AND 1";
            if ($uid)
            {
                $condition = " AND uid = '$uid'  ";
            }
            $sql = "SELECT count(DISTINCT (uid)) as count, DATE(created_ts) as st_date "
                    . " FROM auser_session  WHERE  "
                    . " DATE(created_ts) >= '" . $start . "' AND DATE(created_ts) < '" . $end . "' " .
                    $condition . " AND company_id = " . BaseConfig::$company_id . " "
                    . " GROUP BY WEEK(created_ts)";

            $res = $db->query($sql);
            if (!$res || $db->resultNumRows() < 1)
            {
                return FALSE;
            }
            $ret = array();
            while ($row = $db->fetchObject($res))
            {
                $ret[$row->st_date] = array("a" => $row->count);
            }
            return $ret;
        }

        public static function checkSuperAdminOrIsAdmin($id)
        {
            if ($id > 0)
            {
                return "Yes";
            }
            return "No";
        }

        public static function getAdminName($id)
        {
            $db = Rapidkart::getInstance()->getDB();
            if ($id <= 0)
            {
                return "";
            }
            $users_explode = explode(',', $id);
            $str = "-";
            if (is_array($users_explode) && !empty($users_explode) && array_filter($users_explode))
            {
                $str = '';
                foreach ($users_explode as $users_expl)
                {
                    $res = $db->getFieldValue(SystemTables::DB_TBL_USER, "name", "uid = " . $users_expl . " AND licid = " . BaseConfig::$licence_id . " ");
                    if ($res)
                    {
                        $str .= $res . ", ";
                    }
                }
                $str = rtrim($str, ", ");
            }
            return $str;
        }

        // public static function getAdminMobile($id)
        // {
        //     $db = Rapidkart::getInstance()->getDB();
        //     if ($id <= 0)
        //     {
        //         return '-';
        //     }
        //     $res = $db->getFieldValue(SystemTables::DB_TBL_USER, "mobile", "uid = " . $id);
        //     if ($res)
        //     {
        //         return $res;
        //     }
        //     return '-';
        // }

        public static function getAdminMobile($id)
        {
            $db = Rapidkart::getInstance()->getDB();
            if ($id <= 0)
            {
                return "";
            }
            $users_explode = explode(',', $id);
            $str = "-";
            if (is_array($users_explode) && !empty($users_explode) && array_filter($users_explode))
            {
                $str = '';
                foreach ($users_explode as $users_expl)
                {
                    $res = $db->getFieldValue(SystemTables::DB_TBL_USER, "mobile", "uid = " . (int) $users_expl . " AND licid = " . BaseConfig::$licence_id . " ");
                    if ($res)
                    {
                        $str .= $res . ", ";
                    }
                }
                $str = rtrim($str, ", ");
            }
            return $str;
        }

        // public static function getAdminEmail($id)
        // {
        //     $db = Rapidkart::getInstance()->getDB();
        //     if ($id <= 0)
        //     {
        //         return '-';
        //     }
        //     $res = $db->getFieldValue(SystemTables::DB_TBL_USER, "email", "uid = " . $id);
        //     if ($res)
        //     {
        //         return $res;
        //     }
        //     return '-';
        // }

        public static function getAdminEmail($id)
        {
            $db = Rapidkart::getInstance()->getDB();
            if ($id <= 0)
            {
                return "";
            }
            $users_explode = explode(',', $id);
            $str = "-";
            if (is_array($users_explode) && !empty($users_explode) && array_filter($users_explode))
            {
                $str = '';
                foreach ($users_explode as $users_expl)
                {
                    $res = $db->getFieldValue(SystemTables::DB_TBL_USER, "email", "uid = " . (int) $users_expl . " AND licid = " . BaseConfig::$licence_id . " ");
                    if ($res)
                    {
                        $str .= $res . ", ";
                    }
                }
                $str = rtrim($str, ", ");
            }
            return $str;
        }

        // public static function getAdminSignature($id)
        // {
        //     $db = Rapidkart::getInstance()->getDB();
        //     if ($id <= 0)
        //     {
        //         return '-';
        //     }
        //     $res = $db->getFieldValue(SystemTables::DB_TBL_USER, "email_signature", "uid = " . $id);
        //     if ($res)
        //     {
        //         return $res;
        //     }
        //     return '-';
        // }

        public static function getAdminSignature($id)
        {
            $db = Rapidkart::getInstance()->getDB();
            if ($id <= 0)
            {
                return "";
            }
            $users_explode = explode(',', $id);
            $str = "-";
            if (is_array($users_explode) && !empty($users_explode) && array_filter($users_explode))
            {
                $str = '';
                foreach ($users_explode as $users_expl)
                {
                    $res = $db->getFieldValue(SystemTables::DB_TBL_USER, "email_signature", "uid = " . (int) $users_expl . " AND licid = " . BaseConfig::$licence_id . " ");
                    if ($res)
                    {
                        $str .= $res . ", ";
                    }
                }
                $str = rtrim($str, ", ");
            }
            return $str;
        }

        public static function getAdminUserClaimExpenseTypes()
        {
            $db = Rapidkart::getInstance()->getDB();

            $sql = "SELECT * FROM " . SystemTables::DB_TBL_AUSER_EXPENSE_CLAIM_TYPE . " WHERE company_id = " . BaseConfig::$company_id . "";

            $res = $db->query($sql);
            if (!$res || $db->resultNumRows($res) < 1)
            {
                return FALSE;
            }

            $array = array();
            while ($row = $db->fetchObject($res))
            {
                $array[$row->user_expense_claim_tid] = $row;
            }
            return $array;
        }

        public static function getAdminUserClaimExpenseStatuses()
        {
            $db = Rapidkart::getInstance()->getDB();

            $sql = "SELECT * FROM " . SystemTables::DB_TBL_AUSER_EXPENSE_CLAIM_STATUS . " WHERE user_expense_claim_stid = 0 AND company_id = " . BaseConfig::$company_id . " ";

            $res = $db->query($sql);
            if (!$res || $db->resultNumRows($res) < 1)
            {
                return FALSE;
            }

            $array = array();
            while ($row = $db->fetchObject($res))
            {
                $array[$row->user_expense_claim_sid] = $row;
            }
            return $array;
        }

        public static function getAdminUserByEmail($email = NULL)
        {
            $db = Rapidkart::getInstance()->getDB();

            $sql = "SELECT * FROM " . SystemTables::DB_TBL_USER . " WHERE email = '::email' AND licid = " . BaseConfig::$licence_id . " ";
            $res = $db->query($sql, array('::email' => $email));
            if (!$res || $db->resultNumRows($res) < 1)
            {
                return FALSE;
            }
            $row = $db->fetchObject($res);
            return $row;
        }

        public static function logoutAllUsers($uid = NULL)
        {
            $db = Rapidkart::getInstance()->getDB();

            $sql = "UPDATE " . SystemTables::DB_TBL_USER_SESSION . "
                    SET ussid = 2 ";
            if ($uid)
            {
                $sql .= " WHERE uid = '::uid'";
            }
            $sql .= ((empty($uid)) ? " WHERE " : " AND ") . " company_id = " . BaseConfig::$company_id . "";
            $args = array('::uid' => $uid);

            $res = $db->query($sql, $args);
            if (!$res)
            {
                return false;
            }
            return true;
        }

        public static function getAllUsers()
        {
            $db = Rapidkart::getInstance()->getDB();

            $sql = "SELECT *  FROM " . SystemTables::DB_TBL_USER_SESSION . " WHERE ussid = 1 AND company_id = " . BaseConfig::$company_id;

            $res = $db->query($sql);
            if (!$res || $db->resultNumRows($res) < 1)
            {
                return FALSE;
            }

            $array = array();
            while ($row = $db->fetchObject($res))
            {
                $array[] = $row;
            }
            return $array;
        }

        public static function getUserSession($uid)
        {
            $db = Rapidkart::getInstance()->getDB();
            $args = array('::uid' => $uid);
            $sql = "SELECT usid, ipaddress, ussid, login_type, created_ts as time FROM " . SystemTables::DB_TBL_USER_SESSION . " WHERE uid='::uid' AND company_id = " . BaseConfig::$company_id . " ORDER BY `usid` DESC";
            $res = $db->query($sql, $args);
            if (!$res || $db->resultNumRows($res) < 1)
            {
                return FALSE;
            }
            $ret = array();
            while ($row = $db->fetchObject($res))
            {
                $ret[] = $row;
            }
            return $ret;
        }

        public static function getUserByEmail($email = NULL)
        {
            $db = Rapidkart::getInstance()->getDB();

            $sql = "SELECT uid FROM " . SystemTables::DB_TBL_USER . " WHERE email = '::email' AND company_id = " . BaseConfig::$company_id . " ";
            $res = $db->query($sql, array('::email' => $email));
            if (!$res || $db->resultNumRows($res) < 1)
            {
                return FALSE;
            }
            $row = $db->fetchObject($res);
            return $row->uid;
        }

        public static function fetchUserProfile($user)
        {
            $db = Rapidkart::getInstance()->getDB();

            $sql = "SELECT u.uid, u.name, u.email, u.mobile, u.ustatusid, u.is_admin, u.empid, u.report_to, u.description, u.designation, u.address_line_1, u.address_line_2, COALESCE(u.coverid, 0) as coverid, COALESCE(cr.city,'') as city , COALESCE(u.coverlid , 0) as coverlid, COALESCE(cl.locality_name,'') as locality_name , u.genderid, u.stid, st.state, u.ctid, ct.name as country, ct.iso_code, u.zip_code, u.photo, u.date_of_birth, u.date_of_joining, u.date_of_leaving, u.created_uid, u.updated_uid, u.created_ts, u.updated_ts, MAX(us.created_ts) as last_seen  FROM " . SystemTables::DB_TBL_USER . " u LEFT JOIN " . SystemTables::DB_TBL_COVERAGE_LOCALITY . " cl ON cl.coverlid = u.coverlid LEFT JOIN " . SystemTables::DB_TBL_COVERAGE . " cr ON cr.coverid = u.coverid and cr.company_id = '::compid' LEFT JOIN " . SystemTables::DB_TBL_STATE . " st ON st.stid = u.stid LEFT JOIN " . SystemTables::DB_TBL_COUNTRY . " ct ON ct.ctid = u.ctid LEFT JOIN " . SystemTables::DB_TBL_USER_SESSION . " us ON (us.uid = u.uid AND us.company_id = u.company_id) WHERE u.uid = '::uid' AND u.company_id = '::compid'";
            $res = $db->query($sql, array('::uid' => $user, '::compid' => BaseConfig::$company_id));
            if (!$res || $db->resultNumRows($res) < 1)
            {
                return FALSE;
            }
            $row = $db->fetchObject($res);
            $row->photo = ($row->photo ? "http://" . $_SERVER['HTTP_HOST'] . BaseConfig::FILES_URL . "admin_user/small/" . $row->photo : "");
            $row->roles = AdminUserManager::getUserRoles($row->uid);
            return $row;
        }

        public static function isOtpEmailExist($email, $uid)
        {
            $db = Rapidkart::getInstance()->getDB();

            $sql = "SELECT * FROM " . SystemTables::DB_TBL_AUSER_EMAIL_VERIFICATION . " WHERE email = '::email' AND uid = '::uid' AND company_id ='::compid' ";

            $args = array("::email" => $email, "::uid" => $uid, '::compid' => BaseConfig::$company_id);
            $result = $db->query($sql, $args);

            if (!$result || $db->resultNumRows($result) < 1)
            {
                return false;
            }
            return TRUE;
        }

        public static function isOtpMobileExist($mobile, $uid)
        {
            $db = Rapidkart::getInstance()->getDB();

            $sql = "SELECT * FROM " . SystemTables::DB_TBL_AUSER_MOBILE_VERIFICATION . " WHERE mobile = '::mobile' AND uid = '::uid' AND company_id ='::compid' ";

            $args = array("::mobile" => $mobile, "::uid" => $uid, '::compid' => BaseConfig::$company_id);
            $result = $db->query($sql, $args);

            if (!$result || $db->resultNumRows($result) < 1)
            {
                return false;
            }
            return TRUE;
        }

        public static function getUserOtpEmail($uid)
        {
            $db = Rapidkart::getInstance()->getDB();
            $sql = "SELECT * FROM " . SystemTables::DB_TBL_AUSER_EMAIL_VERIFICATION . " WHERE uid = '::uid' AND company_id ='::compid'";
            $args = array('::uid' => $uid, '::compid' => BaseConfig::$company_id);
            $res = $db->query($sql, $args);
            if (!$res || $db->resultNumRows($res) < 1)
            {
                return array();
            }
            $ret = array();
            while ($row = $db->fetchObject($res))
            {
                $ret[] = $row;
            }
            return $ret;
        }

        public static function getUserOtpMobile($uid)
        {
            $db = Rapidkart::getInstance()->getDB();
            $sql = "SELECT * FROM " . SystemTables::DB_TBL_AUSER_MOBILE_VERIFICATION . " WHERE uid = '::uid' AND company_id ='::compid'";
            $args = array('::uid' => $uid, '::compid' => BaseConfig::$company_id);
            $res = $db->query($sql, $args);
            if (!$res || $db->resultNumRows($res) < 1)
            {
                return array();
            }
            $ret = array();
            while ($row = $db->fetchObject($res))
            {
                $ret[] = $row;
            }
            return $ret;
        }

        public static function generatePasswordLink($email)
        {

            // New entry for password request.
            $token = md5(uniqid(mt_rand(), true));
            $passwordLink = new AdminUserForgotPassword();
            $passwordLink->setEmail($email);
            $passwordLink->setLinkid($token);
            return ($passwordLink->insert()) ? $passwordLink->getUfpid() : false;
        }

        public static function isExistentOtp($uid)
        {
            /* Checks if this is a user of the system */
            if (!$uid)
            {
                return false;
            }

            $db = Rapidkart::getInstance()->getDB();

            $args = array("::uid" => $uid, '::compid' => BaseConfig::$company_id);
            $sql = "SELECT uid FROM " . SystemTables::DB_TBL_AUSER_OTP_SETTINGS . " WHERE uid='::uid' AND company_id ='::compid'";
            $res = $db->query($sql, $args);
            if (!$res || $db->resultNumRows($res) < 1)
            {
                return FALSE;
            }
            return TRUE;
        }

        public static function getUserOtpSettingChange($uid, $value)
        {
            $db = Rapidkart::getInstance()->getDB();

//            if (!self::isExistentOtp($uid))
//            {
//                return false;
//            }
            if (self::isExistentOtp($uid))
            {
                $sql = "UPDATE " . SystemTables::DB_TBL_AUSER_OTP_SETTINGS . " SET enable = '::enable' WHERE uid = '::uid' AND company_id ='::compid'";
            }
            else
            {
                $sql = "INSERT INTO " . SystemTables::DB_TBL_AUSER_OTP_SETTINGS . " (uid, enable,company_id) VALUES ('::uid', '::enable', '::compid')";
            }
            $args = array(
                "::uid" => $uid,
                "::enable" => $value,
                '::compid' => BaseConfig::$company_id
            );

            $res = $db->query($sql, $args);
            if (!$res)
            {
                return FALSE;
            }
            return TRUE;
        }

        public static function getAdminUserLoginView()
        {
            $db = Rapidkart::getInstance()->getDB();
            $sql = "CREATE OR REPLACE ALGORITHM = UNDEFINED DEFINER = root@localhost SQL SECURITY DEFINER VIEW admin_user_last_login_view_" . BaseConfig::$company_id . " AS select `auser_session`.`uid` AS `uid`,max(`auser_session`.`created_ts`) AS `created_ts` from `auser_session` WHERE licid = '" . BaseConfig::$licence_id . "' group by `auser_session`.`uid`";
            $res = $db->query($sql);

            if (!$res)
            {
                ScreenMessage::setMessage("Fail to Load pagde", ScreenMessage::MESSAGE_TYPE_ERROR);
                System::redirectInternal("home");
            }
            return TRUE;
        }

        public static function userView()
        {
            $db = Rapidkart::getInstance()->getDB();
            if (!self::getAdminUserLoginView())
            {
                ScreenMessage::setMessage("Fail to Load pagde", ScreenMessage::MESSAGE_TYPE_ERROR);
                System::redirectInternal("home");
            }
            $sql = "CREATE OR REPLACE ALGORITHM = UNDEFINED DEFINER = root@localhost SQL SECURITY DEFINER VIEW `admin_user_view_" . BaseConfig::$company_id . "` AS  select `a`.`company_id`,`a`.`uid` AS `uid`,group_concat(distinct `ar`.`rid` separator ',') AS `roleids`,group_concat(distinct `r`.`name` separator ',') AS `rolenames`,group_concat(distinct `asg`.`sgid` separator ',') AS `sgids`,group_concat(distinct `s`.`name` separator ',') AS `groupnames`,`a`.`name` AS `name`,`a`.`email` AS `email`,`a`.`ustatusid` AS `ustatusid`,`ss`.`created_ts` AS `last_login`,`us`.`name` AS `status_name`,`a`.`mobile` AS `mobile`,`a`.`is_admin` AS `is_admin`,`a`.`mail_box_hostname` AS `mail_box_hostname`,`a`.`mail_box_port` AS `mail_box_port`,`a`.`mail_box_service` AS `mail_box_service`,`a`.`mail_box_username` AS `mail_box_username`,`a`.`empid` AS `empid`,`a`.`description` AS `description`,`a`.`designation` AS `designation`,`a`.`email_signature` AS `email_signature`,`a`.`date_of_birth` AS `date_of_birth`,`a`.`date_of_joining` AS `date_of_joining`,`a`.`date_of_leaving` AS `date_of_leaving`,`a`.`created_uid` AS `created_uid`,`a`.`updated_uid` AS `updated_uid`,`a`.`created_ts` AS `created_ts`,`a`.`updated_ts` AS `updated_ts`,`a`.`report_to` AS `report_to`,`re`.`name` AS `report_name` , lc.name as company_name from (((((((" . BaseConfig::DB_NAME . ".`auser` `a` JOIN licence_companies lc ON(lc.liccoid = a.company_id) left join `rapidkart_factory_static`.`auser_status` `us` on((`us`.`ustatusid` = `a`.`ustatusid`))) left join " . BaseConfig::DB_NAME . ".`auser_role` `ar` on((`ar`.`uid` = `a`.`uid`) AND (`ar`.`company_id` = `a`.`company_id`))) left join " . BaseConfig::DB_NAME . ".`arole` `r` on((`r`.`rid` = `ar`.`rid`) AND (`r`.`company_id` = `a`.`company_id`))) left join " . BaseConfig::DB_NAME . ".`auser_system_group` `asg` on((`asg`.`uid` = `a`.`uid`) AND (`asg`.`company_id` = `a`.`company_id`))) left join " . BaseConfig::DB_NAME . ".`system_group` `s` on((`s`.`sgid` = `asg`.`sgid`) AND (`s`.`company_id` = `a`.`company_id`))) left join " . BaseConfig::DB_NAME . ".`auser` `re` on((`re`.`uid` = `a`.`report_to`))) left join " . BaseConfig::DB_NAME . ".`admin_user_last_login_view_" . BaseConfig::$company_id . "` `ss` on((`a`.`uid` = `ss`.`uid`))) WHERE a.licid = " . BaseConfig::$licence_id;
            $companies = LicenceManager::getCompanies(BaseConfig::$licence_id);
            $count = 0;
            if ($companies)
            {
                $count = count($companies);
            }
            if ($count > 1)
            {
                //  $sql .= " AND a.uid IN(SELECT uid FROM  " . SystemTables::DB_TBL_USER_COMPANY_MAPPING . ' WHERE company_id=' . BaseConfig::$company_id . ")";
            }

            $sql .= " group by `a`.`uid`,`a`.`name`,`a`.`email`,`a`.`ustatusid`,`us`.`name` ";
            $res = $db->query($sql);

            if (!$res)
            {
                ScreenMessage::setMessage("Fail to Load page", ScreenMessage::MESSAGE_TYPE_ERROR);
                System::redirectInternal("home");
            }
        }

        public static function replaceAdminUserNumber($id)
        {
            $user = new AdminUser($id);
            if ($user->getMobile() != '')
            {
                return $user->getMobile();
            }
            else
            {
                return ' - ';
            }
        }

        public static function userExistOrNot($id)
        {
            $db = Rapidkart::getInstance()->getDB();
            $sql = "SELECT *  FROM " . SystemTables::DB_TBL_CHECKPOINT_ORDER . " WHERE (created_uid  = '::id' OR updated_uid = '::id' OR assigned_uid = '::id') AND company_id = '::company'";
            $res = $db->query($sql, array('::company' => BaseConfig::$company_id, '::id' => $id));

            if (!$res || $db->resultNumRows($res) < 1)
            {
                return FALSE;
            }
            else
            {
                return true;
            }
        }

        public static function userExistOrNotInvoice($id)
        {
            $db = Rapidkart::getInstance()->getDB();
            $sql = "SELECT *  FROM " . SystemTables::DB_TBL_INVOICE . " WHERE (created_uid  = '::id' OR updated_uid = '::id' OR assigned_uid = '::id') AND company_id = '::company'";
            $res = $db->query($sql, array('::company' => BaseConfig::$company_id, '::id' => $id));

            if (!$res || $db->resultNumRows($res) < 1)
            {
                return FALSE;
            }
            else
            {
                return true;
            }
        }

        public static function replaceAdminUserName($id)
        {
            $user = new AdminUser($id);
            if ($user->getName() != '')
            {
                return $user->getName();
            }
            else
            {
                return ' - ';
            }
        }

        public static function deleteUserCompanyMapping($uid)
        {
            $db = Rapidkart::getInstance()->getDB();
            $sql = "DELETE  FROM " . SystemTables::DB_TBL_USER_COMPANY_MAPPING . " WHERE uid = '::uid'";
            $res = $db->query($sql, array('::uid' => $uid));
            return $res ? TRUE : FALSE;
        }

        public static function insertUserCompanyMapping($user, $companys)
        {
            $db = Rapidkart::getInstance()->getDB();
            $str = array();
            if ($companys)
            {
                foreach ($companys as $company)
                {
                    $str[] = "('" . $user->getId() . "','" . $user->getLicid() . "','" . $company . "')";
                }
            }
            if (!empty($str))
            {
                $sql = " INSERT INTO " . SystemTables::DB_TBL_USER_COMPANY_MAPPING . " (uid , licid , company_id) Values " . implode(",", $str);
                $res = $db->query($sql);
                if (!$res)
                {
                    return FALSE;
                }
            }
            return TRUE;
        }

        public static function getUserCompanyMappingList($uid)
        {
            $db = Rapidkart::getInstance()->getDB();
            $sql = "SELECT *  FROM " . SystemTables::DB_TBL_USER_COMPANY_MAPPING . " WHERE uid = '::uid' AND company_id IN(SELECT liccoid FROM  " . SystemTables::DB_TBL_LICENCE_COMPANIES . " WHERE liccosid = 1) ";
            $res = $db->query($sql, array('::uid' => $uid));
            if (!$res || $db->resultNumRows($res) < 1)
            {
                return FALSE;
            }
            $ret = array();
            while ($row = $db->fetchObject($res))
            {
                $ret[$row->company_id] = new LicenceCompanies($row->company_id);
            }
            return $ret;
        }

        public static function getSubOrdinatesList($uid, &$ret, &$user_list)
        {
            $db = Rapidkart::getInstance()->getDB();
            $user_list[$uid] = $uid;
            $sql = "SELECT uid FROM " . SystemTables::DB_TBL_USER . " WHERE ustatusid = 1 and report_to = '::uid' and company_id = '::company' and licid = '::licid' ";
            $res = $db->query($sql, array('::company' => BaseConfig::$company_id, '::licid' => BaseConfig::$licence_id, '::uid' => $uid));

            if ($res && $db->resultNumRows($res) > 0)
            {
                while ($row = $db->fetchObject($res))
                {
                    $ret[$row->uid] = new AdminUser($row->uid);
                    if (!isset($user_list[$row->uid]))
                    {
                        self::getSubOrdinatesList($row->uid, $ret, $user_list);
                    }
                }
            }
        }

        public static function getSubOrdinates($uid)
        {
            $ret = array();
            $user_list = array();
            self::getSubOrdinatesList($uid, $ret, $user_list);
            if (empty($ret))
            {
                return false;
            }
            return $ret;
        }

        public static function getAttendeeName($uid = NULL)
        {
            $db = Rapidkart::getInstance()->getDB();
            $sql = "SELECT name FROM auser WHERE uid IN(::id) AND ustatusid <> 3";
            $args = array('::id' => $uid);
            $res = $db->query($sql, $args);
            if (!$res || $db->resultNumRows($res) < 1)
            {
                return false;
            }
            $ret = array();
            while ($row = $db->fetchObject($res))
            {
                $ret[] = $row->name;
            }
            return implode(", ", $ret);
        }

        public static function getAttendeeEmail($uid = NULL)
        {
            $db = Rapidkart::getInstance()->getDB();
            $sql = "SELECT email FROM auser WHERE uid IN(::id) AND ustatusid <> 3";
            $args = array('::id' => $uid);
            $res = $db->query($sql, $args);
            if (!$res || $db->resultNumRows($res) < 1)
            {
                return false;
            }
            $ret = array();
            while ($row = $db->fetchObject($res))
            {
                $ret[] = $row->email;
            }
            return implode(", ", $ret);
        }

        public static function getAttendeeMobile($uid = NULL)
        {
            $db = Rapidkart::getInstance()->getDB();
            $sql = "SELECT mobile FROM auser WHERE uid IN(::id) AND ustatusid <> 3";
            $args = array('::id' => $uid);
            $res = $db->query($sql, $args);
            if (!$res || $db->resultNumRows($res) < 1)
            {
                return false;
            }
            $ret = array();
            while ($row = $db->fetchObject($res))
            {
                $ret[] = $row->mobile;
            }

            return implode(", ", $ret);
        }

        public static function getUserName($uid = NULL, $rows = NULL)
        {
            $db = Rapidkart::getInstance()->getDB();
            if ($uid > 0 && is_numeric($uid))
            {
                $sql = "SELECT name FROM auser WHERE uid = '::uid' AND company_id = '::compid'";
                $args = array('::uid' => $uid, '::compid' => BaseConfig::$company_id);
                $res = $db->query($sql, $args);
                if (!$res || $db->resultNumRows() < 1)
                {
                    return FALSE;
                }
                while ($row = $db->fetchObject($res))
                {
                    if ($rows)
                    {
                        return $row->name;
                    }
                    return $row;
                }
            }
            else
            {
                if ($rows)
                {
                    return '-';
                }
                return FALSE;
            }
        }

        public static function getUserReportedPerson($reportdto = NULL)
        {
            $db = Rapidkart::getInstance()->getDB();
            if ($reportdto > 0 && is_numeric($reportdto))
            {
                $sql = "SELECT uid FROM auser WHERE report_to = '::reportuid' AND company_id = '::compid'";
                $args = array('::reportuid' => $reportdto, '::compid' => BaseConfig::$company_id);
                $res = $db->query($sql, $args);
                $ret = array();
                while ($row = $db->fetchObject($res))
                {
                    $ret[$row->uid] = $row->uid;
                }
                return $ret;
            }
        }

        public static function getUserId($id)
        {
            return ($id > 0) ? Utility::variableGet("site_code") . "AU" . str_pad($id, 6, "0", STR_PAD_LEFT) : '';
        }

        public static function checkLoginUserStatus($user, $status = 1)
        {
            $status_check = array(1, 4);
            $args = array(
                "::uid" => $user,
                "::ustatusid" => implode(",", $status_check),
                '::compid' => BaseConfig::$company_id
            );
            $db = Rapidkart::getInstance()->getDB();
            $sql = "SELECT * FROM " . SystemTables::DB_TBL_USER . " WHERE uid = '::uid' AND ustatusid IN(::ustatusid) AND company_id= '::compid'";
            $res = $db->query($sql, $args);
            if (!$res || $db->resultNumRows() < 1)
            {
                return FALSE;
            }
            return TRUE;
        }

        public static function getUserIPMappingList($uid)
        {
            $db = Rapidkart::getInstance()->getDB();
            $sql = "SELECT *  FROM " . SystemTables::DB_TBL_AUSER_PERMISSION_SECURITY . " WHERE uid = '::uid'";
            $res = $db->query($sql, array('::uid' => $uid));
            if (!$res || $db->resultNumRows($res) < 1)
            {
                return FALSE;
            }
            $ret = array();
            while ($row = $db->fetchObject($res))
            {
                $ret[$row->pseid] = $row;
            }
            return $ret;
        }

        public static function deleteUserIpMapping($uid)
        {
            $db = Rapidkart::getInstance()->getDB();
            $sql = "DELETE  FROM " . SystemTables::DB_TBL_AUSER_PERMISSION_SECURITY . " WHERE uid = '::uid'";
            $res = $db->query($sql, array('::uid' => $uid));
            return $res ? TRUE : FALSE;
        }

        public static function insertUserIPMapping($user, $ips)
        {
            $db = Rapidkart::getInstance()->getDB();
            $str = array();
            if ($ips)
            {
                foreach ($ips as $ip)
                {
                    $pseid = PermissionSecurityManager::getPseid($ip);
                    //  hprint($pseid);exit;
                    $str[] = "('" . $user->getId() . "','" . BaseConfig::$company_id . "','" . $ip . "','" . 0 . "')";
                }
            }

            if (!empty($str))
            {
                // hprint($str);exit;
                $sql = " INSERT INTO " . SystemTables::DB_TBL_AUSER_PERMISSION_SECURITY . " (uid , company_id , pseid,pseipid) Values " . implode(",", $str);
                $res = $db->query($sql);
                if (!$res)
                {
                    return FALSE;
                }
            }
            return TRUE;
        }

        public static function getUserIPAddress($uid, $company_id)
        {
            $db = Rapidkart::getInstance()->getDB();
            $sql = "SELECT p.ip , p.from_time , p.to_time FROM " . SystemTables::DB_TBL_AUSER_PERMISSION_SECURITY . " s JOIN  " . SystemTables::DB_TBL_PERMISSION_SECURITY . " p ON(p.company_id = '::company' and p.pseid = s.pseid and p.psesid = 1) WHERE s.company_id = '::company' and  s.uid = '::uid' ";
            $args = array('::uid' => $uid, '::company' => $company_id);
            $res = $db->query($sql, $args);
            if (!$res || $db->resultNumRows($res) < 1)
            {
                return array();
            }
            $ret = array();

            while ($row = $db->fetchObject($res))
            {
                $ips = json_decode($row->ip, true);
                if ($ips && is_array($ips) && !empty($ips))
                {
                    foreach ($ips as $ip)
                    {
                        $ret[trim($ip)][] = $row;
                    }
                }
                else
                {
                    $ret[][] = $row;
                }
//                if (strlen($ips) > 3)
//                {
//                    $ret[$ips][] = $row;
//                }
//                else
//                {
//                    $ret[][] = $row;
//                }
            }

            return $ret;
        }

        public static function getActiveAppUsersSessions()
        {
            $db = Rapidkart::getInstance()->getDB();
            $sql = "SELECT DISTINCT(us.fcm_token), us.uid, usr.is_admin, us.fcmskid FROM " . SystemTables::DB_TBL_USER_SESSION . " AS us LEFT JOIN " . SystemTables::DB_TBL_USER . " AS usr ON (usr.uid = us.uid) WHERE us.login_type = 2 AND us.ussid = 1 AND us.company_id = " . BaseConfig::$company_id;
            $res = $db->query($sql);

            if (!$res || $db->resultNumRows() < 1)
            {
                return FALSE;
            }
            $ret = array();
            while ($row = $db->fetchObject($res))
            {
                $ret[] = $row;
            }
            return $ret;
        }

        public static function getActiveAppUsersSessionsById($uid)
        {
            $db = Rapidkart::getInstance()->getDB();
            $sql = "SELECT DISTINCT(us.fcm_token), us.uid, usr.is_admin, us.fcmskid FROM " . SystemTables::DB_TBL_USER_SESSION . " AS us LEFT JOIN " . SystemTables::DB_TBL_USER . " AS usr ON (usr.uid = us.uid) WHERE us.fcm_token<>'' AND usr.uid='::uid' AND us.login_type = 2 AND us.ussid = 1 AND us.company_id = " . BaseConfig::$company_id . " ORDER BY us.usid DESC LIMIT 5";
            $args = array('::uid' => $uid);
            $res = $db->query($sql, $args);
            if (!$res || $db->resultNumRows() < 1)
            {
                return FALSE;
            }
            $ret = array();
            while ($row = $db->fetchObject($res))
            {
                $ret[] = $row;
            }
            return $ret;
        }

        public static function getCheckedByUsers($type)
        {
            $db = Rapidkart::getInstance()->getDB();
            $id = 0;
            switch ($type)
            {
                case 1:
                    $id = USER_PERMISSION_QUOTATION_CHECKED_BY;
                    break;
                case 2:
                    $id = USER_PERMISSION_ORDER_CHECKED_BY;
                    break;
                case 3:
                    $id = USER_PERMISSION_INVOICE_CHECKED_BY;
                    break;
            }

            $sql = 'SELECT uid , name FROM ' . SystemTables::DB_TBL_USER . ' WHERE licid = "::licid" AND  ustatusid <> 3 AND uid IN(SELECT uid FROM ' . SystemTables::DB_TBL_USER_ROLE . ' WHERE  rid IN (SELECT rid FROM ' . SystemTables::DB_TBL_ROLE_PERMISSION . ' WHERE pid = "::pid") ) ';
            if (getSettings("IS_COMPANY_BASED_USERS_SEARCH"))
            {
                $sql .= " AND company_id = " . BaseConfig::$company_id;
            }
            $args = array('::licid' => BaseConfig::$licence_id, '::pid' => $id);
            $res = $db->query($sql, $args);
            if (!$res || $db->resultNumRows($res) < 1)
            {
                return false;
            }
            $ret = array();
            while ($row = $db->fetchObject($res))
            {
                $ret[] = $row;
            }
            return $ret;
        }

        public static function getRUsers($uid, &$array)
        {
            $users = self::getSubOrdinates($uid);
            if ($users)
            {
                foreach ($users as $user)
                {
                    $array[] = $user->getId();
                    self::getRUsers($user->getId(), $array);
                }
            }
            else
            {
                return $array;
            }
        }

        public static function insertPasswordLog()
        {
            $db = Rapidkart::getInstance()->getDB();
            $sql = "INSERT INTO user_password_log (uid , uplogsid) VALUES ('::uid', '::status') ";
            $args = array('::uid' => Session::loggedInUid(), '::status' => 1);
            $res = $db->query($sql, $args);
            return $res ? true : false;
        }

        public static function getPasswordLog()
        {
            $db = Rapidkart::getInstance()->getDB();
            $sql = "SELECT MAX(created_ts) as last FROM user_password_log WHERE uid = '::uid'";
            $args = array('::uid' => Session::loggedInUid());
            $res = $db->query($sql, $args);
            echo $db->getMysqlError();
            if (!$res || $db->resultNumRows($res) < 1)
            {
                return false;
            }
            $row = $db->fetchObject($res);
            return $row->last;
        }

        public static function getAdimUsersHierarchyData()
        {
            $db = Rapidkart::getInstance()->getDB();
            $sql = "SELECT uid, name, report_to FROM auser WHERE licid = '::company' AND ustatusid IN(1,4)";
            $args = array('::company' => BaseConfig::$licence_id);
            $res = $db->query($sql, $args);
            if (!$res || $db->resultNumRows($res) < 1)
            {
                return false;
            }
            $hierarchy = array();
            while ($row = $db->fetchObject($res))
            {
                $hierarchy[] = [
                    'id' => $row->uid,
                    'name' => $row->name,
                    'manager' => $row->report_to ? $row->report_to : null
                ];
            }
            return $hierarchy;
        }

        public static function getUserByName($name = NULL)
        {
            $db = Rapidkart::getInstance()->getDB();

            $sql = "SELECT uid FROM " . SystemTables::DB_TBL_USER . " WHERE name = '::name' AND company_id = " . BaseConfig::$company_id;
            $res = $db->query($sql, array('::name' => $name));
            if (!$res || $db->resultNumRows($res) < 1)
            {
                return FALSE;
            }
            $row = $db->fetchObject($res);
            return $row->uid;
        }

        public static function getUserById($uid)
        {
            if (!$uid || $uid <= 0)
            {
                return false;
            }

            if (!AdminUser::isExistent($uid))
            {
                return false;
            }

            return new AdminUser($uid);
        }

        public static function getUserNamesByUIDs($uids)
        {
            $db = Rapidkart::getInstance()->getDB();
            $uidArray = array_filter(array_map('trim', explode(',', $uids)));
            if (empty($uidArray)) 
            {
                return '';
            }
            $safeUIDs = array();
            foreach ($uidArray as $uid) 
            {
                if (is_numeric($uid)) 
                {
                    $safeUIDs[] = (int)$uid;
                }
            }

            if (empty($safeUIDs)) 
            {
                return '';
            }
            $uidList = implode(',', $safeUIDs);
            $sql = "SELECT name FROM " . SystemTables::DB_TBL_USER . "
                    WHERE uid IN (::uid_array)
                    AND company_id = '::compid'";
            $args = array('::compid' => BaseConfig::$company_id, '::uid_array' => $uidList);
            $res = $db->query($sql, $args);
            if (!$res || $db->resultNumRows($res) < 1) 
            {
                return '';
            }
            $names = array();
            while ($row = $db->fetchObject($res)) 
            {
                $names[] = $row->name;
            }
            return implode(', ', $names);
        }


    }
    

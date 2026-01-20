<?php

    /**
     * Representation of an Administrative User
     * 
     * @author Sohil Gupta
     * 
     * @since 20120624
     * @updated 20140703  
     * @updated 20150818 @updatedBy : Priya Gupta
     */
    class AdminUser implements User, UniqueIdentifier
    {

        /**
         * Admin User Attributes
         */
        private $uid;
        private $name;
        private $email;
        private $ustatusid;
        private $password;
        private $created_ts;
        private $updated_ts;
        private $mail_box_hostname;
        private $mail_box_port;
        private $mail_box_service;
        private $mail_box_username;
        private $mail_box_password;
        private $is_admin;
        private $is_super;
        private $empid;
        private $address_line_1;
        private $address_line_2;
        private $coverid;
        private $coverlid;
        private $rid;
        private $stid;
        private $ctid;
        private $zip_code;
        private $photo = "default.png";
        private $date_of_joining;
        private $date_of_birth;
        private $date_of_leaving;
        private $designation;
        private $report_to;
        private $mobile;
        private $description;
        private $email_signature;
        private $created_uid;
        private $updated_uid;
        private $company_id;
        private $licid;
        private $soft_limit_cron_date;
        private $hard_limit_cron_date;
        private $soft_limit;
        private $hard_limit;

        /**
         * External
         */
        private $roles = array();
        private $permissions = array();
        private $sessionDetails;
        private $country;
        private $state;
        private $city;
        private $locality;
        private $sub_ordinates = array();

        /**
         * Constants
         */
        const IMG_WIDTH_SMALL = "200";
        const IMG_HEIGHT_SMALL = "200";
        const IMG_WIDTH_MEDIUM = "600";
        const IMG_HEIGHT_MEDIUM = "600";

        function getSoftLimitCronDate()
        {
            return $this->soft_limit_cron_date;
        }

        function getHardLimitCronDate()
        {
            return $this->hard_limit_cron_date;
        }

        function setSoftLimitCronDate($soft_limit_cron_date)
        {
            $this->soft_limit_cron_date = $soft_limit_cron_date;
        }

        function setHardLimitCronDate($hard_limit_cron_date)
        {
            $this->hard_limit_cron_date = $hard_limit_cron_date;
        }

        function getSoftLimit()
        {
            return $this->soft_limit;
        }

        function getHardLimit()
        {
            return $this->hard_limit;
        }

        function setSoftLimit($soft_limit)
        {
            $this->soft_limit = $soft_limit;
        }

        function setHardLimit($hard_limit)
        {
            $this->hard_limit = $hard_limit;
        }

        public function getIsAdmin()
        {
            return $this->is_admin;
        }

        public function getIsSuper()
        {
            return $this->is_super;
        }

        public function getCreatedUid()
        {
            return $this->created_uid;
        }

        public function getUpdatedUid()
        {
            return $this->updated_uid;
        }

        public function setIsAdmin($is_admin)
        {
            $this->is_admin = $is_admin;
        }

        public function setIsSuper($is_super)
        {
            $this->is_super = $is_super;
        }

        public function setCreatedUid($created_uid)
        {
            $this->created_uid = $created_uid;
        }

        public function setUpdatedUid($updated_uid)
        {
            $this->updated_uid = $updated_uid;
        }

        public function getMailBoxHostname()
        {
            return $this->mail_box_hostname;
        }

        public function getMailBoxPort()
        {
            return $this->mail_box_port;
        }

        public function getMailBoxService()
        {
            return $this->mail_box_service;
        }

        public function getMailBoxUsername()
        {
            return $this->mail_box_username;
        }

        public function getMailBoxPassword()
        {
            return $this->mail_box_password;
        }

        public function setMailBoxHostname($mail_box_hostname)
        {
            $this->mail_box_hostname = $mail_box_hostname;
        }

        public function setMailBoxPort($mail_box_port)
        {
            $this->mail_box_port = $mail_box_port;
        }

        public function setMailBoxService($mail_box_service)
        {
            $this->mail_box_service = $mail_box_service;
        }

        public function setMailBoxUsername($mail_box_username)
        {
            $this->mail_box_username = $mail_box_username;
        }

        public function setMailBoxPassword($mail_box_password)
        {
            $this->mail_box_password = $mail_box_password;
        }

        public function getUpdatedTs()
        {
            return $this->updated_ts;
        }

        public function setUpdatedTs($updated_ts)
        {
            $this->updated_ts = $updated_ts;
        }

        public function getUstatusId()
        {
            return $this->ustatusid;
        }

        public function setUstatusId($status)
        {
            $this->ustatusid = $status;
        }

        public function getDescription()
        {
            return $this->description;
        }

        public function setDescription($description)
        {
            $this->description = $description;
        }

        public function getUid()
        {
            return $this->uid;
        }

        public function getEmpid()
        {
            return $this->empid;
        }

        function getAddressLine1()
        {
            return $this->address_line_1;
        }

        function getAddressLine2()
        {
            return $this->address_line_2;
        }

        function getCoverid()
        {
            return $this->coverid;
        }

        function getCoverlid()
        {
            return $this->coverlid;
        }

        function getRid()
        {
            return $this->rid;
        }

        function getStid()
        {
            return $this->stid;
        }

        function getCtid()
        {
            return $this->ctid;
        }

        function getZipCode()
        {
            return $this->zip_code;
        }

        public function getMobile()
        {
            return $this->mobile;
        }

        public function getDateOfBirth()
        {
            return $this->date_of_birth;
        }

        public function getDateOfJoining()
        {
            return $this->date_of_joining;
        }

        public function getDateOfLeaving()
        {
            return $this->date_of_leaving;
        }

        public function getDesignation()
        {
            return $this->designation;
        }

        public function getReportTo()
        {
            return new AdminUser($this->report_to);
        }

        public function getReport()
        {
            return $this->report_to;
        }

        public function getPhoto()
        {
            return $this->photo;
        }

        public function setUid($uid)
        {
            $this->uid = $uid;
        }

        public function setEmpid($empid)
        {
            $this->empid = $empid;
        }

        function setAddressLine1($address_line_1)
        {
            $this->address_line_1 = $address_line_1;
        }

        function setAddressLine2($address_line_2)
        {
            $this->address_line_2 = $address_line_2;
        }

        function setCoverid($coverid)
        {
            $this->coverid = $coverid;
        }

        function setCoverlid($coverlid)
        {
            $this->coverlid = $coverlid;
        }

        function setRid($rid)
        {
            $this->rid = $rid;
        }

        function setStid($stid)
        {
            $this->stid = $stid;
        }

        function setCtid($ctid)
        {
            $this->ctid = $ctid;
        }

        function setZipCode($zip_code)
        {
            $this->zip_code = $zip_code;
        }

        public function setMobile($mobile)
        {
            $this->mobile = $mobile;
        }

        public function setDateOfBirth($date_of_birth)
        {
            $this->date_of_birth = $date_of_birth;
        }

        public function setDateOfJoining($date_of_joining)
        {
            $this->date_of_joining = $date_of_joining;
        }

        public function setDateOfLeaving($date_of_leaving)
        {
            $this->date_of_leaving = $date_of_leaving;
        }

        public function setDesignation($designation)
        {
            $this->designation = $designation;
        }

        public function setReportTo($report_to)
        {
            $this->report_to = $report_to;
        }

        public function setPhoto($photo)
        {
            $this->photo = $photo;
        }

        function getCompanyId()
        {
            return $this->company_id;
        }

        function setCompanyId($company_id)
        {
            $this->company_id = $company_id;
        }

        function getEmailSignature()
        {
            return $this->email_signature;
        }

        function setEmailSignature($email_signature)
        {
            $this->email_signature = $email_signature;
        }

        /**
         * Construct an admin user object and load the user from the database if a valid id is given
         * 
         * @param $uid The admin user's ID
         * 
         * @return Boolean Whether the user object was successfully loaded from the database
         */
        public function __construct($uid = null, $licid = null)
        {
            if (isset($uid))
            {
                $this->uid = $uid;
                $this->licid = $licid;

                $this->load();
            }
        }

        function getLicid()
        {
            return $this->licid;
        }

        function setLicid($licid)
        {
            $this->licid = $licid;
        }

        public static function isExistent($uid)
        {
            /* Checks if this is a user of the system */
            if (!$uid)
            {
                return false;
            }

            $db = Rapidkart::getInstance()->getDB();

            $args = array("::uid" => $uid, '::licid' => BaseConfig::$licence_id);
            $sql = "SELECT uid FROM " . SystemTables::DB_TBL_USER . " WHERE uid='::uid' AND ustatusid !='3' AND licid = '::licid'";
            $res = $db->query($sql, $args);
            if (!$res || $db->resultNumRows($res) < 1)
            {
                return FALSE;
            }
            return TRUE;
        }

        /**
         * Public method that can be called to load a user
         * 
         * @param $uid The id of the admin user to load
         */
        public function load($uid = null)
        {
            $db = Rapidkart::getInstance()->getDB();
            /* If the UID is set, load this user's information and return true */
            $args = array('::uid' => $this->uid, '::company_id' => BaseConfig::$company_id, '::licid' => ($this->licid > 0) ? $this->licid : BaseConfig::$licence_id);

            if ($this->uid <= 0)
            {
                return false;
            }
            /* Loading the personal Information */
            $sql = "SELECT * FROM " . SystemTables::DB_TBL_USER . "  WHERE uid= '::uid'  AND licid = '::licid' LIMIT 1";
            $rs = $db->query($sql, $args);
            if (!$rs || $db->resultNumRows($rs) < 1)
            {
                return false;
            }

            /* Load the data into this object */
            $data = $db->fetchObject($rs);
            foreach ($data as $key => $value)
            {
                $this->$key = $value;
            }
            return true;
        }

        public function getPermissions()
        {
            if (!$this->permissions || empty($this->permissions))
            {
                $this->loadPermissions();
            }
            return $this->permissions;
        }

        private function loadSessionDetails()
        {
            $ret = AdminUserManager::loadSessionDetails($this->uid);
            $this->sessionDetails = $ret;
        }

        public function getSessionDetails()
        {
            if (!$this->sessionDetails)
            {
                $this->loadSessionDetails();
            }
            return $this->sessionDetails;
        }

        /**
         * Load this user's roles 
         * 
         * @return Boolean If the operation was successful or not
         */
        private function loadRoles()
        {
            $this->roles = AdminUserManager::loadRoles($this->uid);
        }

        /**
         * @return Array A set of roles this user has
         */
        public function getRoles()
        {
            if (!$this->roles && $this->uid)
            {
                $this->loadRoles();
            }
            return $this->roles;
        }

        /**
         * Load the set of permissions of this user
         * 
         * @return Boolean Depending upon whether the operation was successful or not
         */
        private function loadPermissions()
        {
            /* Load this user Permissions */
            if (empty($this->roles))
            {
                return false;
            }
                $this->permissions = AdminUserManager::loadPermissions($this->roles);
            }

        /**
         * Set a new password to the user object. This new password is however not saved to the database.
         * 
         * @param $password The new password
         */
        public function setPassword($password)
        {
            $this->password = self::hashPassword($password);
        }

        /**
         * Here we check if this password given here is that of the user
         */
        public function isUserPassword($password)
        {
            if (!$this->password)
            {
                return false;
            }
            return ($this->password == $this->hashPassword($password));
        }

        /**
         * Hash the password using both md5 and sha1.
         * The hashing uses a salt to prevent dictionary attacks.
         * 
         * @param $password The password to hash
         * 
         * @return String The hashed password
         */
        public static function hashPassword($password)
        {
            $salt = md5(BaseConfig::PASSWORD_SALT);
            return sha1($salt . $password);
        }

        /**
         * @todo Check other mandatory fields
         */
        public function hasMandatoryData()
        {
            if (!isset($this->email) || !valid($this->email))
            {
                return false;
            }

            return;
        }

        public function insert()
        {
            /* Get a database instance */
            $db = Rapidkart::getInstance()->getDB();

            /* Add user to database */
            $sql = "INSERT INTO " . SystemTables::DB_TBL_USER . " (name, email, ustatusid, password, mail_box_hostname, mail_box_port, mail_box_service, mail_box_username, mail_box_password, empid, photo, designation, report_to , address_line_1, address_line_2, coverid, coverlid, rid, stid, ctid, zip_code, date_of_birth , date_of_joining, date_of_leaving, mobile , description, email_signature, created_uid , is_admin , company_id, licid , hard_limit , soft_limit) "
                    . " VALUES('::name', '::email', '::ustatusid', '::password', '::mail_box_hostname', '::mail_box_port', '::mail_box_service', '::mail_box_username', '::mail_box_password', '::empid', '::photo', '::designation', '::report_to', '::address_line_1', '::address_line_2', '::coverid', '::coverlid', '::rid', '::stid', '::ctid', '::zip_code', '::birth', '::joining', '::leaving', '::mobile', '::description', '::signature', '::created_uid' , '::admin' , '::compid', '::licid' , '::hard', '::soft')";
            $args = array(
                "::name" => $this->name,
                '::hard' => $this->hard_limit,
                '::soft' => $this->soft_limit,
                "::licid" => BaseConfig::$licence_id,
                "::email" => $this->email,
                '::mobile' => $this->mobile,
                "::ustatusid" => $this->ustatusid,
                "::password" => $this->password,
                '::mail_box_hostname' => $this->mail_box_hostname,
                '::mail_box_port' => $this->mail_box_port,
                '::mail_box_service' => $this->mail_box_service,
                '::mail_box_username' => $this->mail_box_username,
                '::mail_box_password' => $this->mail_box_password,
                "::empid" => $this->empid,
                "::report_to" => $this->report_to,
                "::photo" => $this->photo,
                "::designation" => $this->designation,
                "::address_line_1" => $this->address_line_1,
                "::address_line_2" => $this->address_line_2,
                "::coverid" => $this->coverid,
                "::coverlid" => $this->coverlid,
                "::rid" => $this->rid,
                "::stid" => $this->stid,
                "::ctid" => $this->ctid,
                "::zip_code" => $this->zip_code,
                "::joining" => $this->date_of_joining,
                "::leaving" => $this->date_of_leaving,
                '::description' => $this->description,
                "::signature" => $this->email_signature,
                '::created_uid' => $this->created_uid,
                "::birth" => $this->date_of_birth,
                "::admin" => $this->is_admin,
                "::compid" => BaseConfig::$company_id
            );

            $res = $db->query($sql, $args);
            if (!$res || $db->affectedRows() < 1)
            {
                return false;
            }

            $this->uid = $db->lastInsertId();
            return true;
        }

        public function update()
        {
            /* Check user validity */
            if (!self::isExistent($this->uid))
            {
                return false;
            }

            /* Get a database instance */
            $db = Rapidkart::getInstance()->getDB();

            /* Update the user */
            $sql = "UPDATE " . SystemTables::DB_TBL_USER . "
                    SET name = '::name', email = '::email', ustatusid = '::ustatusid', password = '::password', mail_box_hostname = '::mail_box_hostname', mail_box_port = '::mail_box_port', mail_box_service = '::mail_box_service', mail_box_username = '::mail_box_username', mail_box_password = '::mail_box_password', empid = '::empid', address_line_1 = '::address_line_1', address_line_2 = '::address_line_2', coverid = '::coverid', coverlid = '::coverlid', rid = '::rid', stid = '::stid', ctid = '::ctid', zip_code = '::zip_code', photo = '::photo', date_of_joining = '::date_of_joining', date_of_birth = '::date_of_birth', date_of_leaving = '::date_of_leaving', designation = '::designation', report_to = '::report_to', mobile = '::mobile', description = '::description', email_signature = '::signature', updated_uid = '::updated_uid' , is_admin= '::admin' , company_id = '::company' , hard_limit  = '::hard' , soft_limit = '::soft' WHERE uid = '::uid' AND licid = '::licid'";
            $args = array(
                "::uid" => $this->uid,
                '::hard' => $this->hard_limit,
                "::soft" => $this->soft_limit,
                "::company" => $this->company_id,
                "::name" => $this->name,
                "::licid" => BaseConfig::$licence_id,
                "::email" => $this->email,
                '::mobile' => $this->mobile,
                "::ustatusid" => $this->ustatusid,
                "::password" => $this->password,
                '::mail_box_hostname' => $this->mail_box_hostname,
                '::mail_box_port' => $this->mail_box_port,
                '::mail_box_service' => $this->mail_box_service,
                '::mail_box_username' => $this->mail_box_username,
                '::mail_box_password' => $this->mail_box_password,
                "::empid" => $this->empid,
                "::report_to" => $this->report_to,
                "::photo" => $this->photo,
                "::designation" => $this->designation,
                "::address_line_1" => $this->address_line_1,
                "::address_line_2" => $this->address_line_2,
                "::coverid" => $this->coverid,
                "::coverlid" => $this->coverlid,
                "::rid" => $this->rid,
                "::stid" => $this->stid,
                "::ctid" => $this->ctid,
                "::zip_code" => $this->zip_code,
                "::date_of_birth" => $this->date_of_birth,
                "::date_of_joining" => $this->date_of_joining,
                "::date_of_leaving" => $this->date_of_leaving,
                '::description' => $this->description,
                "::signature" => $this->email_signature,
                '::updated_uid' => $this->updated_uid,
                "::admin" => $this->is_admin,
            );
            $res = $db->query($sql, $args);
            if (!$res)
            {
                return false;
            }
            return true;
        }

        /**
         * Check if the username and password is valid.
         * 
         * If the data is valid, the user's id and status is added to this object
         * 
         * @return Whether the given username and password is valid
         */
        public function authenticate()
        {
            /* Get a database instance */
            $db = Rapidkart::getInstance()->getDB();

            /* Load the data from the database */
            $sql = "SELECT * FROM " . SystemTables::DB_TBL_USER . " WHERE email = '::email' AND password = '::password' AND (ustatusid NOT IN (2,3) OR ustatusid IS NULL) AND licid = '::licid' LIMIT 1";
            $args = array(
                "::email" => $this->email,
                '::password' => $this->password,
                '::ustatusid' => SystemTablesStatus::ADMIN_USER_DELETED_STATE,
                '::compid' => BaseConfig::$company_id,
                "::licid" => BaseConfig::$licence_id
            );

            $res = $db->query($sql, $args);
            if (!$res || $db->resultNumRows($res) < 1)
            {
                return false;
            }

            /* Load the data into this object */
            $data = $db->fetchObject($res);
            foreach ($data as $key => $value)
            {
                $this->$key = $value;
            }
            return true;
        }

        /**
         * Set the user status to deleted
         * 
         * @param Integer $uid
         * 
         * @return boolean Whether the operation was successful or not
         */
        public static function delete($uid)
        {
            if (!self::isExistent($uid))
            {
                return false;
            }

            $db = Rapidkart::getInstance()->getDB();

            $sql = "UPDATE " . SystemTables::DB_TBL_USER . " SET ustatusid = '::ustatusid' WHERE uid = '::uid' AND company_id = '::compid' AND licid  = '::licid' ";
            $args = array(
                "::uid" => $uid,
                "::licid" => BaseConfig::$licence_id,
                "::ustatusid" => SystemTablesStatus::ADMIN_USER_DELETED_STATE,
                "::compid" => BaseConfig::$company_id
            );

            return $db->query($sql, $args);
        }

        public function setEmail($email)
        {
            if (isset($email) && valid($email))
            {
                $this->email = $email;
                return true;
            }
            else
            {
                return false;
            }
        }

        public function getStatus()
        {
            return $this->ustatusid;
        }

        public function setStatus($ustatusid)
        {
            $this->ustatusid = $ustatusid;
        }

        public function getEmail()
        {
            return $this->email;
        }

        public function getId()
        {
            return $this->uid;
        }

        public function setName($name)
        {
            $this->name = $name;
        }

        public function getName()
        {
            return $this->name;
        }

        public function getCreatedTimestamp()
        {
            return $this->created_ts;
        }

        public function setCreatedTimestamp($created_ts)
        {
            $this->created_ts = $created_ts;
        }

        public function getPermission()
        {
            if (!$this->permissions)
            {
                $this->loadPermissions();
            }
            return $this->permissions;
        }

        public function __toString()
        {
            $ret = array();
            foreach ($this as $key => $val)
            {
                $ret[$key] = $val;
            }
            return json_encode($ret);
        }

        public function createInvoice()
        {
            return Utility::variableGet("site_code") . "AU" . str_pad(session::loggedInUid(), 6, "0", STR_PAD_LEFT);
        }

        public function parse($obj)
        {
            if (is_object($obj))
            {
                foreach ($obj as $key => $value)
                {
                    $this->$key = $value;
                }
            }
        }

        function getCountry()
        {
            if (!$this->country)
            {
                $this->loadCountry();
            }
            return $this->country;
        }

        function loadCountry()
        {
            $this->country = new Country($this->ctid);
        }

        function getState()
        {
            if (!$this->state)
            {
                $this->loadState();
            }
            return $this->state;
        }

        function loadState()
        {
            $this->state = new State($this->stid);
        }

        function getCity()
        {
            if (!$this->city)
            {
                $this->loadCity();
            }
            return $this->city;
        }

        function loadCity()
        {
            $this->city = new Coverage($this->coverid);
        }

        function getLocality()
        {
            if (!$this->locality)
            {
                $this->loadLocality();
            }
            return $this->locality;
        }

        function loadLocality()
        {
            $this->locality = new CoverageLocality($this->coverlid);
        }

        public static function getLogoUrl($size = "medium")
        {

            if ($size == 'medium')
            {
                return "/admin_user/medium/";
            }
            else
            {
                return "/admin_user/small/";
            }
        }

        public static function photosDir($size = "medium")
        {
            if ($size === "medium")
            {
                return SiteConfig::filesDirectory() . "admin_user/medium/";
            }
            else
            {
                return SiteConfig::filesDirectory() . "admin_user/small/";
            }
        }

        public function createFullId()
        {
            
        }

        public function getFullId()
        {
            
        }

        public function setFullId()
        {
            
        }

        public function getSubOrdinates()
        {
            if (!$this->sub_ordinates)
            {
                $this->loadSubOrdinates();
            }
            return $this->sub_ordinates;
        }

        private function loadSubOrdinates()
        {
            $this->sub_ordinates = AdminUserManager::getSubOrdinates($this->uid);
        }

    }
    
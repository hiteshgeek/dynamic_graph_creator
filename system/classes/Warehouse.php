<?php

    /*
     * To change this license header, choose License Headers in Project Properties.
     * To change this template file, choose Tools | Templates
     * and open the template in the editor.
     */

    /**
     * Description of Warehouse
     *
     * @author karthik
     * @since 20150907
     */
    class Warehouse implements DatabaseObject
    {
        /*
         * Internal variables.
         */

        private $waid, $name, $chkid, $checkpoint_code, $address_line_1, $address_line_2;
        private $contact, $alt_contact, $city, $stid, $ctid, $zip_code, $longitude, $latitude, $landmark;
        private $coverlid, $wasid, $watid;
        private $created_ts, $updated_ts;
        private $created_uid, $updated_uid;
        private $outlid;
        private $type;
        private $is_default;

        /*
         * External
         */
        private $users;
        private $state;
        private $city_id;
        private $country;
        private $locality;

        public function __construct($waid = null)
        {
            if ($waid)
            {
                $this->waid = $waid;
                $this->load();
            }
        }

        function getWaid()
        {
            return $this->waid;
        }

        function getName()
        {
            return $this->name;
        }

        function getIsDefault()
        {
            return $this->is_default;
        }

        function setIsDefault($is_default)
        {
            $this->is_default = $is_default;
        }

        function getChkid()
        {
            return $this->chkid;
        }

        function getCheckpointCode()
        {
            return $this->checkpoint_code;
        }

        function getAddressLine1()
        {
            return $this->address_line_1;
        }

        function getAddressline2()
        {
            return $this->address_line_2;
        }

        function getContact()
        {
            return $this->contact;
        }

        function getCity()
        {
            return $this->city;
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

        function getLongitude()
        {
            return $this->longitude;
        }

        function getLatitude()
        {
            return $this->latitude;
        }

        function getLandmark()
        {
            return $this->landmark;
        }

        function getCoverlid()
        {
            return $this->coverlid;
        }

        function getWasid()
        {
            return $this->wasid;
        }

        function getWatid()
        {
            return $this->watid;
        }

        function getCreatedTs()
        {
            return $this->created_ts;
        }

        function getUpdatedTs()
        {
            return $this->updated_ts;
        }

        function setWaid($waid)
        {
            $this->waid = $waid;
        }

        function setName($name)
        {
            $this->name = $name;
        }

        function setChkid($chkid)
        {
            $this->chkid = $chkid;
        }

        function setCheckpointCode($checkpoint_code)
        {
            $this->checkpoint_code = $checkpoint_code;
        }

        function setAddressLine1($address_line_1)
        {
            $this->address_line_1 = $address_line_1;
        }

        function setAddressLine2($address_line_2)
        {
            $this->address_line_2 = $address_line_2;
        }

        function setContact($contact)
        {
            $this->contact = $contact;
        }

        function setCity($city)
        {
            $this->city = $city;
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

        function setLongitude($longitude)
        {
            $this->longitude = $longitude;
        }

        function setLatitude($latitude)
        {
            $this->latitude = $latitude;
        }

        function setLandmark($landmark)
        {
            $this->landmark = $landmark;
        }

        function setCoverlid($coverlid)
        {
            $this->coverlid = $coverlid;
        }

        function setWasid($wasid)
        {
            $this->wasid = $wasid;
        }

        function setWatid($watid)
        {
            $this->watid = $watid;
        }

        function setCreatedTs($created_ts)
        {
            $this->created_ts = $created_ts;
        }

        function setUpdatedTs($updated_ts)
        {
            $this->updated_ts = $updated_ts;
        }

        public function getLocality()
        {
            return $this->locality;
        }

        public function getUsers()
        {
            return $this->users;
        }

        private function loadUsers()
        {
            $this->users = new AdminUser($this->uid);
        }

        public function getState()
        {
            return $this->state;
        }

        public function getCityId()
        {
            return $this->city_id;
        }

        public function setCityId($city_id)
        {
            $this->city_id = $city_id;
        }

        public function getCountry()
        {
            return $this->country;
        }

        public function getId()
        {
            return $this->waid;
        }

        public function getAltContact()
        {
            return $this->alt_contact;
        }

        public function setAltContact($alt_contact)
        {
            $this->alt_contact = $alt_contact;
        }

        function getOutlid()
        {
            return $this->outlid;
        }

        function setOutlid($outlid)
        {
            $this->outlid = $outlid;
        }

        function getType()
        {
            return $this->type;
        }

        function setType($type)
        {
            $this->type = $type;
        }

        public function hasMandatoryData()
        {
            if (!$this->name || !$this->checkpoint_code || !$this->address_line_1 || !$this->contact || !$this->city || !$this->stid || !$this->ctid || !$this->wasid)
            {
                return false;
            }

            return true;
        }

        public function load()
        {
            $db = Rapidkart::getInstance()->getDB();

            $args = array("::waid" => $this->waid, '::company_id' => BaseConfig::$company_id);
            $sql = " SELECT * FROM " . SystemTables::DB_TBL_WAREHOUSE . " w "
                    . " WHERE w.waid = '::waid' AND company_id ='::company_id' LIMIT 1";
            $res = $db->query($sql, $args);

            if (!$res || $db->resultNumRows($res) < 1)
            {
                return false;
            }

            $row = $db->fetchObject($res);
            foreach ($row as $key => $value)
            {
                $this->$key = $value;
            }
            return true;
        }

        public function loadExtra()
        {
            $db = Rapidkart::getInstance()->getDB();

            $args = array("::waid" => $this->waid, '::company_id' => BaseConfig::$company_id);
            $sql = " SELECT cnt.name as country,st.state as state,cov.coverid as city_id,covl.locality_name as locality,concat('[',group_concat('{\"',au.uid ,'\":\"',au.name,'\"}'),']') as users FROM " . SystemTables::DB_TBL_WAREHOUSE . " w "
                    . " INNER JOIN " . SystemTables::DB_TBL_COUNTRY . " cnt ON (cnt.ctid = w.ctid ) "
                    . " INNER JOIN " . SystemTables::DB_TBL_STATE . " st ON (st.stid = w.stid ) "
                    . " INNER JOIN " . SystemTables::DB_TBL_COVERAGE . " cov ON (cov.city LIKE w.city AND cov.company_id ='::company_id' ) "
                    . " LEFT JOIN " . SystemTables::DB_TBL_COVERAGE_LOCALITY . " covl ON (covl.coverlid = w.coverlid )"
                    . " LEFT JOIN " . SystemTables::DB_TBL_WAREHOUSE_USER_MAPPING . " wum ON (wum.waid = w.waid AND wum.company_id ='::company_id' ) "
                    . " LEFT JOIN " . SystemTables::DB_TBL_USER . " au ON (au.uid = wum.uid  AND au.company_id ='::company_id' )  "
                    . " WHERE w.waid = '::waid'   AND w.company_id ='::company_id'  LIMIT 1";
            $res = $db->query($sql, $args);
            if (!$res || $db->resultNumRows($res) < 1)
            {
                return false;
            }

            $row = $db->fetchObject($res);
            foreach ($row as $key => $value)
            {
                $this->$key = $value;
            }
            return true;
        }

        public function insert()
        {
            if (!$this->hasMandatoryData())
            {
                return false;
            }

            $db = Rapidkart::getInstance()->getDB();
            $args = array("::name" => $this->name, "::uid" => Session::loggedInUid(), "::code" => $this->checkpoint_code, "::address1" => $this->address_line_1, "::address2" => $this->address_line_2, "::contact" => $this->contact, "::city" => $this->city, "::stid" => $this->stid, "::ctid" => $this->ctid, "::zip_code" => $this->zip_code, "::wasid" => $this->wasid, '::watid' => $this->watid, "::longitude" => $this->longitude, "::latitude" => $this->latitude, '::coverlid' => $this->coverlid, "::alt_contact" => $this->alt_contact, '::outlet' => $this->outlid > 0 ? $this->outlid : 'NULL', '::compid' => BaseConfig::$company_id, '::type' => $this->type > 0 ? $this->type : 3);
            $sql = " INSERT INTO " . SystemTables::DB_TBL_WAREHOUSE . " (name,created_uid,checkpoint_code,address_line_1,address_line_2,contact,alt_contact,city,stid,ctid,zip_code,wasid,watid,longitude,latitude,coverlid , outlid, company_id , type) VALUES ('::name',::uid,'::code','::address1','::address2','::contact','::alt_contact','::city','::stid','::ctid','::zip_code','::wasid','::watid','::longitude','::latitude' ,::coverlid , ::outlet, '::compid' , '::type')";

            $res = $db->query($sql, $args);

            if (!$res)
            {
                return false;
            }

            $this->waid = $db->lastInsertId();
            return true;
        }

        public function update()
        {
            if (!$this->hasMandatoryData())
            {
                return false;
            }

            $db = Rapidkart::getInstance()->getDB();
            $args = array("::id" => $this->waid, "::name" => $this->name, "::uid" => Session::loggedInUid(), "::code" => $this->checkpoint_code, "::address1" => $this->address_line_1, "::address2" => $this->address_line_2, "::contact" => $this->contact, "::city" => $this->city, "::stid" => $this->stid, "::ctid" => $this->ctid, "::zip_code" => $this->zip_code, "::wasid" => $this->wasid, "::watid" => $this->watid, "::longitude" => $this->longitude, "::latitude" => $this->latitude, '::coverlid' => $this->coverlid, "::created_ts" => $this->created_ts, "::updated_ts" => date('Y-m-d H:i:s'), "::alt_contact" => $this->alt_contact, '::outlet' => $this->outlid > 0 ? $this->outlid : 'NULL', '::compid' => BaseConfig::$company_id, '::type' => $this->type > 0 ? $this->type : 3);
            $sql = " UPDATE " . SystemTables::DB_TBL_WAREHOUSE . " SET name='::name', updated_uid=::uid,checkpoint_code='::code', address_line_1='::address1',address_line_2='::address2',contact='::contact',alt_contact='::alt_contact',city='::city',stid='::stid',ctid='::ctid',zip_code='::zip_code',wasid='::wasid',watid = '::watid',longitude='::longitude',latitude='::latitude' , coverlid=::coverlid , created_ts = '::created_ts' ,updated_ts = '::updated_ts'  , outlid = ::outlet , type = '::type' WHERE waid = '::id' AND company_id = '::compid'";
            $res = $db->query($sql, $args);
            if (!$res)
            {
                return false;
            }
            return true;
        }

        public function updateChkid()
        {
            if (!$this->hasMandatoryData())
            {
                return false;
            }

            $db = Rapidkart::getInstance()->getDB();
            $args = array("::id" => $this->waid, "::chkid" => $this->chkid, '::compid' => BaseConfig::$company_id);
            $sql = " UPDATE " . SystemTables::DB_TBL_WAREHOUSE . " SET chkid='::chkid' WHERE waid = '::id' AND company_id = '::compid'";
            $res = $db->query($sql, $args);
            if (!$res || $db->affectedRows() < 1)
            {
                return false;
            }
            return true;
        }

        /*
         * Update Warehouse Status..
         */

        public function updateStatus($status)
        {
            $db = Rapidkart::getInstance()->getDB();
            $args = array("::waid" => $this->waid, "::status" => $status, '::compid' => BaseConfig::$company_id);
            $sql = "UPDATE " . SystemTables::DB_TBL_WAREHOUSE . " SET wasid=::status WHERE waid = ::waid AND company_id = '::compid'";
            $res = $db->query($sql, $args);
            if (!$res)
            {
                return false;
            }
            return true;
        }

        public static function delete($id)
        {
            $db = Rapidkart::getInstance()->getDB();

            $args = array("::id" => $id, '::compid' => BaseConfig::$company_id);
            $sql = " UPDATE " . SystemTables::DB_TBL_WAREHOUSE . " SET wasid = '3'  WHERE waid = '::id' AND company_id = '::compid'";
            $res = $db->query($sql, $args);

            if (!$res)
            {
                return false;
            }

            return true;
        }

        public static function isExistent($id)
        {
            $db = Rapidkart::getInstance()->getDB();

            $args = array("::id" => $id, '::compid' => BaseConfig::$company_id);
            $sql = " SELECT waid FROM " . SystemTables::DB_TBL_WAREHOUSE . " WHERE waid = '::id' AND wasid!='3' AND company_id = '::compid'";

            $res = $db->query($sql, $args);

            if (!$res || $db->resultNumRows() < 1)
            {
                return false;
            }

            return true;
        }

        public function getCreatedUid()
        {
            return $this->created_uid;
        }

        public function getUpdatedUid()
        {
            return $this->updated_uid;
        }

        public function setCreatedUid($created_uid)
        {
            $this->created_uid = $created_uid;
        }

        public function setUpdatedUid($updated_uid)
        {
            $this->updated_uid = $updated_uid;
        }

        public function __toString()
        {
            $array = array();
            foreach ($this as $key => $val)
            {
                $array[$key] = $val;
            }
            return json_encode($array);
        }

        public function parse($obj)
        {
            if (is_object($obj) || is_array($obj))
            {
                foreach ($obj as $key => $value)
                {
                    $this->$key = $value;
                }
            }
        }

    }
    
<?php

    /*
     * To change this license header, choose License Headers in Project Properties.
     * To change this template file, choose Tools | Templates
     * and open the template in the editor.
     */

    /**
     * Description of Licence
     *
     * @author accrete
     */
    class Licence implements DatabaseObject
    {

        private $licid, $name, $licence_number, $licsid, $start_date, $created_uid, $updated_uid, $created_ts, $updated_ts, $ssl_status, $customer_id, $printid, $prsegid, $custom_data, $gst_api;
        private $if_customized, $customization_box, $logo;
        private $users, $deferred_users, $floating_users;
        private $user_count, $deferred_count, $floating_user_count;

        public function __toString()
        {
            
        }

        public function getId()
        {
            return $this->licid;
        }

        function __construct($licid = NULL)
        {
            if ($licid)
            {
                $this->licid = $licid;
                $this->load();
            }
        }

        function getName()
        {
            return $this->name;
        }

        function getLicenceNumber()
        {
            return $this->licence_number;
        }

        function getLicsid()
        {
            return $this->licsid;
        }

        function getPrintId()
        {
            return $this->printid;
        }

        function getPrsegId()
        {
            return $this->prsegid;
        }

        function getStartDate()
        {
            return $this->start_date;
        }

        function getCreatedUid()
        {
            return $this->created_uid;
        }

        function getUpdatedUid()
        {
            return $this->updated_uid;
        }

        function getCreatedTs()
        {
            return $this->created_ts;
        }

        function getUpdatedTs()
        {
            return $this->updated_ts;
        }

        function getCustomerId()
        {
            return $this->customer_id;
        }

        function setName($name)
        {
            $this->name = $name;
        }

        function setLicenceNumber($licence_number)
        {
            $this->licence_number = $licence_number;
        }

        function setLicsid($licsid)
        {
            $this->licsid = $licsid;
        }

        function setPrintId($printid)
        {
            $this->printid = $printid;
        }

        function setPrsegId($prsegid)
        {
            $this->prsegid = $prsegid;
        }

        function setStartDate($start_date)
        {
            $this->start_date = $start_date;
        }

        function setCreatedUid($created_uid)
        {
            $this->created_uid = $created_uid;
        }

        function setUpdatedUid($updated_uid)
        {
            $this->updated_uid = $updated_uid;
        }

        function setCreatedTs($created_ts)
        {
            $this->created_ts = $created_ts;
        }

        function setUpdatedTs($updated_ts)
        {
            $this->updated_ts = $updated_ts;
        }

        function setCustomerId($customer_id)
        {
            $this->customer_id = $customer_id;
        }

        function getIfCustomized()
        {
            return $this->if_customized;
        }

        function getCustomizationBox()
        {
            return $this->customization_box;
        }

        function getLogo()
        {
            return $this->logo;
        }

        function setIfCustomized($if_customized)
        {
            $this->if_customized = $if_customized;
        }

        function setCustomizationBox($customization_box)
        {
            $this->customization_box = $customization_box;
        }

        function setLogo($logo)
        {
            $this->logo = $logo;
        }

        function getCustomData()
        {
            return $this->custom_data;
        }

        function setCustomData($custom_data)
        {
            $this->custom_data = $custom_data;
        }

        function getSSLStatus()
        {
            return $this->ssl_status;
        }

        function setSSLStatus($ssl_status)
        {
            $this->ssl_status = $ssl_status;
        }

        function getGstApi()
        {
            return $this->gst_api;
        }

        function setGstApi($gst_api)
        {
            $this->gst_api = $gst_api;
        }

        public function getUsers()
        {
            return $this->users;
        }

        public function getDeferredUsers()
        {
            return $this->deferred_users;
        }

        public function getFloatingUsers()
        {
            return $this->floating_users;
        }

        public function setUsers($users)
        {
            $this->users = $users;
            return $this;
        }

        public function setDeferredUsers($deferred_users)
        {
            $this->deferred_users = $deferred_users;
            return $this;
        }

        public function setFloatingUsers($floating_users)
        {
            $this->floating_users = $floating_users;
            return $this;
        }

        public function hasMandatoryData()
        {
            
        }

        public function insert()
        {
            $db = Rapidkart::getInstance()->getDB();
            $sql = "INSERT INTO " . SystemTables::DB_TBL_LICENCE . " (name , licence_number , start_date  , licsid, customer_id, created_uid,printid,prsegid) VALUES ('::name', '::licence', '::start', '::licsid', '::customer', '::uid', '::printid','::prsegid') ";
            $args = array('::name' => $this->name, '::licence' => $this->licence_number, '::start' => $this->start_date, '::licsid' => $this->licsid, '::customer' => $this->customer_id, '::uid' => $this->created_uid, '::printid' => $this->printid, '::prsegid' => $this->prsegid);
            $res = $db->query($sql, $args);
            //echo $db->getLastQuery();die;
            if (!$res)
            {
                return FALSE;
            }
            $this->licid = $db->lastInsertId();
            return TRUE;
        }

        public function load()
        {
            $db = Rapidkart::getInstance()->getDB();
            $sql = "SELECT * FROM " . SystemTables::DB_TBL_LICENCE . " WHERE licid = '::id' ";
            $res = $db->query($sql, array('::id' => $this->licid));
            if (!$res || $db->resultNumRows($res) < 1)
            {
                return FALSE;
            }
            $row = $db->fetchObject($res);
            foreach ($row as $key => $value)
            {
                $this->$key = $value;
            }
            return TRUE;
        }

        public function parse($obj)
        {
            
        }

        public function update()
        {
            $db = Rapidkart::getInstance()->getDB();
            $sql = "UPDATE " . SystemTables::DB_TBL_LICENCE . " SET name  = '::name', customer_id = '::customer' , licence_number = '::licence' , licsid = '::licsid' , start_date = '::start' , updated_uid = '::uid' ,printid='::printid',prsegid='::prsegid' WHERE licid ='::id'";
            $args = array('::name' => $this->name, '::licence' => $this->licence_number, '::start' => $this->start_date, '::licsid' => $this->getLicsid(), '::customer' => $this->customer_id, '::uid' => $this->created_uid, '::printid' => $this->printid, '::prsegid' => $this->prsegid, '::id' => $this->licid);
            $res = $db->query($sql, $args);
            return $res ? TRUE : FALSE;
        }

        public static function delete($id)
        {
            $db = Rapidkart::getInstance()->getDB();
            $sql = "UPDATE " . SystemTables::DB_TBL_LICENCE . " SET licsid = '3' WHERE licid = '::id' ";
            $res = $db->query($sql, array('::id' => $id));
            return $res ? TRUE : FALSE;
        }

        public static function isExistent($id)
        {
            $db = Rapidkart::getInstance()->getDB();
            $sql = "SELECT * FROM " . SystemTables::DB_TBL_LICENCE . " WHERE licid = '::id' AND licsid != '3' ";
            $res = $db->query($sql, array('::id' => $id));
            if (!$res || $db->resultNumRows($res) < 1)
            {
                return FALSE;
            }
            return TRUE;
        }

    }
    
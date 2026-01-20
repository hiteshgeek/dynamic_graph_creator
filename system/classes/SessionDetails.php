<?php

    /*
     * To change this license header, choose License Headers in Project Properties.
     * To change this template file, choose Tools | Templates
     * and open the template in the editor.
     */

    /**
     * Description of SessionDetails
     *
     * @author sohil
     */
    class SessionDetails implements DatabaseObject
    {

        private $usid;
        private $uid;
        private $sid;
        private $ipaddress;
        private $ussid;
        private $created_ts;
        private $login_type;
        private $updated_ts;
        private $mac_addr;

        public function getUid()
        {
            return $this->uid;
        }

        public function getSid()
        {
            return $this->sid;
        }

        public function getIpaddress()
        {
            return $this->ipaddress;
        }

        public function getUssid()
        {
            return $this->ussid;
        }

        public function getCreatedTs()
        {
            return $this->created_ts;
        }

        function getUpdatedTs()
        {
            return $this->updated_ts;
        }

        public function setUid($uid)
        {
            $this->uid = $uid;
        }

        public function setSid($sid)
        {
            $this->sid = $sid;
        }

        public function setIpaddress($ipaddress)
        {
            $this->ipaddress = $ipaddress;
        }

        public function setUssid($ussid)
        {
            $this->ussid = $ussid;
        }

        public function setCreatedTs($created_ts)
        {
            $this->created_ts = $created_ts;
        }

        function setUpdatedTs($updated_ts)
        {
            $this->updated_ts = $updated_ts;
        }

        public function __construct($usid = null)
        {
            if (isset($usid) && self::isExistent($usid))
            {
                $this->usid = $usid;
                $this->load();
            }
        }

        public function getId()
        {
            return $this->usid;
        }

        function getLoginType()
        {
            return $this->login_type;
        }

        function setLoginType($login_type)
        {
            $this->login_type = $login_type;
        }

        function getMacAddr()
        {
            return $this->mac_addr;
        }

        function setMacAddr($mac_addr)
        {
            $this->mac_addr = $mac_addr;
        }

        public function hasMandatoryData()
        {
            
        }

        public function insert()
        {
            throw new UnsupportedMethodException();
        }

        public function load()
        {
            $db = Rapidkart::getInstance()->getDB();
            $sql = "SELECT * FROM " . SystemTables::DB_TBL_USER_SESSION . " WHERE usid = '::usid'";
            $args = array("::usid" => $this->usid);
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

        public function update()
        {
            
        }

        public static function delete($id)
        {
            
        }

        public static function isExistent($id)
        {
            $db = Rapidkart::getInstance()->getDB();
            $sql = "SELECT usid FROM " . SystemTables::DB_TBL_USER_SESSION . " WHERE usid = '::usid' AND ussid!='3'";
            $args = array("::usid" => $id);
            $res = $db->query($sql, $args);
            if (!$res || $db->resultNumRows($res) < 1)
            {
                return FALSE;
            }
            return TRUE;
        }

        public function __toString()
        {
            
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

    }
    
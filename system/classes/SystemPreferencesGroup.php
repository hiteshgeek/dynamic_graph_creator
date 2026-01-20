<?php

    /*
     * To change this license header, choose License Headers in Project Properties.
     * To change this template file, choose Tools | Templates
     * and open the template in the editor.
     */

    /**
     * Description of SystemPreferences
     *
     * @author Aditya Sikarwar
     */
    class SystemPreferencesGroup implements DatabaseObject
    {

        private $spgid;
        private $name;
        private $spgsid;
        private $created_uid;
        private $updated_uid;
        private $created_ts;
        private $updated_ts;

        /**
         * 
         * External
         */
        function getName()
        {
            return $this->name;
        }

        function getSpgsid()
        {
            return $this->spgsid;
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

        function setName($name)
        {
            $this->name = $name;
        }

        function setSpgsid($spgsid)
        {
            $this->spgsid = $spgsid;
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

        public function __construct($spgid = null)
        {
            if (isset($spgid) && valid($spgid))
            {
                $this->spgid = $spgid;
                $this->load();
                return true;
            }
            else
            {
                return false;
            }
        }

        public function getId()
        {
            return $this->spgid;
        }

        public function hasMandatoryData()
        {
            
        }

        public function insert()
        {
            $db = Rapidkart::getInstance()->getDB();
            $sql = "INSERT INTO " . SystemTables::DB_TBL_SYSTEM_PREFERENCES_GROUP . " (name, spgsid, created_uid, updated_uid) VALUES('::name', '::spgsid', '::created_uid', '::updated_uid')";
            $args = array(
                '::name' => $this->name,
                '::spgsid' => $this->spgsid,
                '::created_uid' => $this->created_uid,
                '::updated_uid' => $this->updated_uid,
            );
            $res = $db->query($sql, $args);

            if (!$res)
            {
                return false;
            }

            /* Insertion successful... lets proceed */
            $this->spgid = $db->lastInsertId();
            return true;
        }

        public function load()
        {
            $db = Rapidkart::getInstance()->getDB();
            $sql = "SELECT * FROM " . SystemTables::DB_TBL_SYSTEM_PREFERENCES_GROUP . " WHERE spgid='::spgid'";
            $args = array("::spgid" => $this->spgid);
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
            $db = Rapidkart::getInstance()->getDB();
            $sql = "UPDATE  " . SystemTables::DB_TBL_SYSTEM_PREFERENCES_GROUP . " SET name = '::name', spgsid = '::spgsid', created_uid = '::created_uid', updated_uid = '::updated_uid', updated_ts = '::time'  WHERE spgid = '::spgid'";
            $args = array(
                '::name' => $this->name,
                '::spgid' => $this->spgid,
                '::spgsid' => $this->spgsid,
                '::created_uid' => $this->created_uid,
                '::updated_uid' => $this->updated_uid,
                '::time' => date('Y-m-d H:i:s', time())
            );
            $res = $db->query($sql, $args);
            return ($res);
        }

        public static function delete($id)
        {
            $db = Rapidkart::getInstance()->getDB();
            $args = array("::spgid" => $id);
            $sql = "UPDATE " . SystemTables::DB_TBL_SYSTEM_PREFERENCES_GROUP . " SET spgsid ='3' WHERE spgid='::spgid' LIMIT 1";
            $res = $db->query($sql, $args);
            return isset($res) ? true : false;
        }

        public static function isExistent($id)
        {
            $db = Rapidkart::getInstance()->getDB();
            $sql = "SELECT * FROM " . SystemTables::DB_TBL_SYSTEM_PREFERENCES_GROUP . " WHERE spgid='::spgid' AND spgsid != '3'";
            $args = array("::spgid" => $id);
            $res = $db->query($sql, $args);
            if (!$res || $db->resultNumRows($res) < 1)
            {
                return false;
            }
            return TRUE;
        }

        public function __toString()
        {
            
        }

        public function parse($obj)
        {
            
        }

    }
    
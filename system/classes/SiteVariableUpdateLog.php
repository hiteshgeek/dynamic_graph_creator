<?php

    /*
     * To change this license header, choose License Headers in Project Properties.
     * To change this template file, choose Tools | Templates
     * and open the template in the editor.
     */

    /**
     * Description of SiteVariableUpdateLog
     *
     * @author Sohil Gupta
     */
    class SiteVariableUpdateLog implements DatabaseObject
    {

        private $vulid, $vid, $uid, $previous_value, $new_value, $created_ts;

        function __construct($vulid = NULL)
        {
            if ($vulid)
            {
                $this->vulid = $vulid;
                $this->load();
            }
        }

        public function getId()
        {
            return $this->vulid;
        }

        function getVid()
        {
            return $this->vid;
        }

        function getUid()
        {
            return $this->uid;
        }

        function getPreviousValue()
        {
            return $this->previous_value;
        }

        function getNewValue()
        {
            return $this->new_value;
        }

        function getCreatedTs()
        {
            return $this->created_ts;
        }

        function setVid($vid)
        {
            $this->vid = $vid;
        }

        function setUid($uid)
        {
            $this->uid = $uid;
        }

        function setPreviousValue($previous_value)
        {
            $this->previous_value = $previous_value;
        }

        function setNewValue($new_value)
        {
            $this->new_value = $new_value;
        }

        function setCreatedTs($created_ts)
        {
            $this->created_ts = $created_ts;
        }

        /**
         * 
         * @return boolean insert variable update log
         */
        public function insert()
        {
            $db = Rapidkart::getInstance()->getDB();
            $args = array('::vid' => $this->vid, '::previous' => $this->previous_value, '::new' => $this->new_value, '::uid' => $this->uid);
            $sql = "INSERT INTO " . SystemTables::DB_TBL_VARIABLE_UPDATE_LOG . "(vid,previous_value,new_value , uid, company_id) VALUES ('::vid','::previous','::new' , '::uid', '::company')";
            $args['::company'] = BaseConfig::$company_id;
            $res = $db->query($sql, $args);
            if ($res)
            {
                $this->vulid = $db->lastInsertId();
                return true;
            }
            return false;
        }

        /**
         * 
         * @return boolean check for mandatory fields
         */
        public function hasMandatoryData()
        {
            if (!$this->vid && !$this->previous_value && !$this->new_value && !$this->uid)
            {
                return FALSE;
            }
            return true;
        }

        /**
         * 
         * @return boolean fetch all records of particular varaible update log id
         */
        public function load()
        {
            $db = Rapidkart::getInstance()->getDB();

            $sql = "SELECT * FROM " . SystemTables::DB_TBL_VARIABLE_UPDATE_LOG . " WHERE vulid = '::vulid' LIMIT 1";

            $result = $db->query($sql, array("::vulid" => $this->vulid));

            if (!$result || $db->resultNumRows($result) < 1)
            {
                return false;
            }
            $res = $db->fetchObject($result);

            foreach ($res as $key => $value)
            {
                $this->$key = $value;
            }

            return true;
        }

        /**
         *  update log
         */
        public function update()
        {
            
        }

        /**
         * 
         * @param type $id delete log
         */
        public static function delete($id)
        {
            
        }

        /**
         * 
         * @param type $id
         * @return boolean check vulid exists or not
         */
        public static function isExistent($id)
        {
            $db = Rapidkart::getInstance()->getDB();

            $sql = "SELECT * FROM " . SystemTables::DB_TBL_VARIABLE_UPDATE_LOG . " WHERE vulid = '::vulid' LIMIT 1";

            $result = $db->query($sql, array("::vulid" => $id));

            if (!$result || $db->resultNumRows($result) < 1)
            {
                return false;
            }


            return true;
        }

        public function __toString()
        {
            
        }

        public function parse($obj)
        {
            
        }

    }
    
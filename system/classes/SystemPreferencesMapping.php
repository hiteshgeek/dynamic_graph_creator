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
    class SystemPreferencesMapping implements DatabaseObject
    {

        private $licspmid;
        private $licid;
        private $spid;
        private $value;
        public $created_ts;

        public function __construct($licspmid = null)
        {
            if (isset($licspmid))
            {
                $this->licspmid = $licspmid;
                $this->load();
            }
        }

        function getLicspmid()
        {
            return $this->licspmid;
        }

        function getLicid()
        {
            return $this->licid;
        }

        function getSpid()
        {
            return $this->spid;
        }

        function getValue()
        {
            return $this->value;
        }

        function getCreated_ts()
        {
            return $this->created_ts;
        }

        function setLicspmid($licspmid)
        {
            $this->licspmid = $licspmid;
        }

        function setLicid($licid)
        {
            $this->licid = $licid;
        }

        function setSpid($spid)
        {
            $this->spid = $spid;
        }

        function setValue($value)
        {
            $this->value = $value;
        }

        function setCreated_ts($created_ts)
        {
            $this->created_ts = $created_ts;
        }

        public function __toString()
        {
            
        }

        public function getId()
        {
            return $this->licspmid;
        }

        public function hasMandatoryData()
        {
            
        }

        public function insert()
        {
            
        }

        public function load()
        {
            $db = Rapidkart::getInstance()->getDB();
            $args = array('::licspmid' => $this->licspmid);
            $sql = "SELECT * FROM " . SystemTables::DB_TBL_LICENCE_SYSTEM_PREFERENCES_MAPPING . "  WHERE licspmid= '::licspmid' LIMIT 1";
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

        public function parse($obj)
        {
            
        }

        public function update()
        {
            
        }

        public static function delete($id)
        {
            
        }

        public static function isExistent($id)
        {
            
        }

    }
    
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
    class SystemPreferences implements DatabaseObject
    {

        private $spid;
        private $mid;
        private $name;
        private $title;
        private $description;
        private $sptid;
        private $data;
        private $spgid;
        private $spsid;
        private $spcid;
        private $created_uid;
        public $updated_uid;
        public $created_ts;
        public $updated_ts;

        public function __construct($spid = null)
        {
            if (isset($spid))
            {
                $this->spid = $spid;
                $this->load();
            }
        }

        function getName()
        {
            return $this->name;
        }

        function getSpcid()
        {
            return $this->spcid;
        }

        function setSpcid($spcid)
        {
            $this->spcid = $spcid;
        }

        function getTitle()
        {
            return $this->title;
        }

        function setName($name)
        {
            $this->name = $name;
        }

        function setTitle($title)
        {
            $this->title = $title;
        }

        function getMid()
        {
            return $this->mid;
        }

        function getDescription()
        {
            return $this->description;
        }

        function getSptid()
        {
            return $this->sptid;
        }

        function getData()
        {
            return $this->data;
        }

        function getSpgid()
        {
            return $this->spgid;
        }

        function getSpsid()
        {
            return $this->spsid;
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

        function setMid($mid)
        {
            $this->mid = $mid;
        }

        function setDescription($description)
        {
            $this->description = $description;
        }

        function setSptid($sptid)
        {
            $this->sptid = $sptid;
        }

        function setData($data)
        {
            $this->data = $data;
        }

        function setSpgid($spgid)
        {
            $this->spgid = $spgid;
        }

        function setSpsid($spsid)
        {
            $this->spsid = $spsid;
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

        public function __toString()
        {
            
        }

        public function getId()
        {
            return $this->spid;
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
            $args = array('::spid' => $this->spid);
            $sql = "SELECT * FROM " . SystemTables::DB_TBL_SYSTEM_PREFERENCES . "  WHERE spid= '::spid' LIMIT 1";
            $rs = $db->query($sql, $args);
            if (!$rs || $db->resultNumRows($rs) != 1)
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
            $args = array(
                '::data' => $this->getData(),
                '::id' => $this->spid
            );

            $sql = "UPDATE " . SystemTables::DB_TBL_SYSTEM_PREFERENCES . " SET data = '::data' WHERE spid = '::id' ";
            $db = Rapidkart::getInstance()->getDB();
            $res = $db->query($sql, $args);
            // echo $db->getLastQuery();
            if (!$res)
            {
                return FALSE;
            }
            return TRUE;
        }

        public static function delete($id)
        {
            
        }

        public static function isExistent($id)
        {
            
        }
    }
    
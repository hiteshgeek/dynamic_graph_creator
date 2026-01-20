<?php

    /*
     * To change this license header, choose License Headers in Project Properties.
     * To change this template file, choose Tools | Templates
     * and open the template in the editor.
     */

    /**
     * Description of LicenceCompanies
     *
     * @author accrete
     */
    class LicenceCompanies implements DatabaseObject
    {

        private $liccoid, $name, $created_uid, $created_ts, $licid, $start_date;
        private $lock_date;
        private $gstr_date;

        //put your code here
        public function __toString()
        {
            
        }

        function getStartDate()
        {
            return $this->start_date;
        }

        function setStartDate($start_date)
        {
            $this->start_date = $start_date;
        }

        function getLicid()
        {
            return $this->licid;
        }

        function setLicid($licid)
        {
            $this->licid = $licid;
        }

        public function getId()
        {
            return $this->liccoid;
        }

        function __construct($liccoid = NULL)
        {
            if ($liccoid)
            {
                $this->liccoid = $liccoid;
                $this->load();
            }
        }

        function getName()
        {
            return $this->name;
        }

        function getCreatedUid()
        {
            return $this->created_uid;
        }

        function getCreatedTs()
        {
            return $this->created_ts;
        }

        function setName($name)
        {
            $this->name = $name;
        }

        function setCreatedUid($created_uid)
        {
            $this->created_uid = $created_uid;
        }

        function setCreatedTs($created_ts)
        {
            $this->created_ts = $created_ts;
        }

        public function hasMandatoryData()
        {
            
        }

        function getLockDate()
        {
            return $this->lock_date;
        }

        function setLockDate($lock_date)
        {
            $this->lock_date = $lock_date;
            return $this;
        }

        function getGstrDate()
        {
            return $this->gstr_date;
        }

        function setGstrDate($gstr_date)
        {
            $this->gstr_date = $gstr_date;
            return $this;
        }

        public function insert()
        {
            $db = Rapidkart::getInstance()->getDB();
            $sql = "INSERT INTO " . SystemTables::DB_TBL_LICENCE_COMPANIES . " (licid , name) VALUES ('::licid', '::name') ";
            $args = array('::licid' => $this->licid, '::name' => $this->name);
            $res = $db->query($sql, $args);
            if (!$res)
            {
                return FALSE;
            }
            $this->liccoid = $db->lastInsertId();
            return TRUE;
        }

        public function load()
        {
            $db = Rapidkart::getInstance()->getDB();
            $sql = "SELECT * FROM " . SystemTables::DB_TBL_LICENCE_COMPANIES . " WHERE liccoid= '::id'  ";
            $res = $db->query($sql, array('::id' => $this->liccoid));
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
            $sql = "UPDATE " . SystemTables::DB_TBL_LICENCE_COMPANIES . " SET name  = '::name', licid = '::licid', lock_date = '::lock_date' WHERE liccoid ='::id'";
            $args = array('::name' => $this->name, '::licid' => $this->licid, '::id' => $this->liccoid, '::lock_date' => $this->lock_date);
            $res = $db->query($sql, $args);
            return $res ? TRUE : FALSE;
        }

        public static function delete($id)
        {
            $db = Rapidkart::getInstance()->getDB();

            $sql = "DELETE FROM " . SystemTables::DB_TBL_LICENCE_COMPANIES . " WHERE liccoid = '::liccoid'";
            $args = array(
                "::liccoid" => $id
            );

            $res = $db->query($sql, $args);
            if (!$res)
            {
                return FALSE;
            }
            return TRUE;
        }

        public static function isExistent($id)
        {
            $db = Rapidkart::getInstance()->getDB();
            $sql = "SELECT * FROM " . SystemTables::DB_TBL_LICENCE_COMPANIES . " WHERE liccoid= '::id'  ";
            $res = $db->query($sql, array('::id' => $id));
            if (!$res || $db->resultNumRows($res) < 1)
            {
                return FALSE;
            }
            return TRUE;
        }

    }
    
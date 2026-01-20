<?php

    /*
     * To change this license header, choose License Headers in Project Properties.
     * To change this template file, choose Tools | Templates
     * and open the template in the editor.
     */

    /**
     * Description of LicenceDomain
     *
     * @author accrete
     */
    class LicenceDomain implements DatabaseObject
    {

        private $licdoid, $licid, $url, $created_uid, $created_ts;

        function __construct($licdoid = NULL)
        {
            if ($licdoid)
            {
                $this->licdoid = $licdoid;
                $this->load();
            }
        }

        public function __toString()
        {
            
        }

        function getLicid()
        {
            return $this->licid;
        }

        function getUrl()
        {
            return $this->url;
        }

        function getCreatedUid()
        {
            return $this->created_uid;
        }

        function getCreatedTs()
        {
            return $this->created_ts;
        }

        function setLicid($licid)
        {
            $this->licid = $licid;
        }

        function setUrl($url)
        {
            $this->url = $url;
        }

        function setCreatedUid($created_uid)
        {
            $this->created_uid = $created_uid;
        }

        function setCreatedTs($created_ts)
        {
            $this->created_ts = $created_ts;
        }

        public function getId()
        {
            return $this->licdoid;
        }

        public function hasMandatoryData()
        {
            
        }

        public function insert()
        {
            $db = Rapidkart::getInstance()->getDB();
            $sql = "INSERT INTO " . SystemTables::DB_TBL_LICENCE_DOMAIN . " (licid , url) VALUES ('::licid', '::url') ";
            $args = array('::licid' => $this->licid, '::url' => $this->url);
            $res = $db->query($sql, $args);
            //echo $db->getLastQuery();die;
            if (!$res)
            {
                return FALSE;
            }
            $this->licdoid = $db->lastInsertId();
            return TRUE;
        }

        public function load()
        {
            $db = Rapidkart::getInstance()->getDB();
            $sql = "SELECT * FROM " . SystemTables::DB_TBL_LICENCE_DOMAIN . " WHERE licdoid= '::id'  ";
            $res = $db->query($sql, array('::id' => $this->licdoid));
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
            
        }

        public static function delete($id)
        {
            
        }

        public static function isExistent($id)
        {
            
        }

    }
    
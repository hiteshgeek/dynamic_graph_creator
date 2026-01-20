<?php

    /**
     * SiteVariable Manager Class that manages the all Site variables
     * 
     * @author Asharani
     * @since 20140820
     */
    class SiteVariableManager
    {

        /**
         * Load all the Site Variables Details
         * 
         * @returns Map[Integer, Sitvariable] all the SiteVariables and its values
         */
        public static function getSiteVariables()
        {
            $db = Rapidkart::getInstance()->getDB();

            $sql = "SELECT * FROM " . SystemTables::DB_TBL_VARIABLE . " WHERE vid NOT IN (SELECT vid FROM " . SystemTables::DB_TBL_VARIABLE . " WHERE vid = 'session_lifetime')";

            $result = $db->query($sql);

            if (!$result)
            {
                return false;
            }
            $sitevariables = array();

            while ($row = $db->fetchObject($result))
            {
                $sitevariables[$row->vid] = new SiteVariable($row->vid);
            }

            return $sitevariables;
        }

        /**
         * 
         * @param type $vid
         * @return type fetch value corresponding to variable id
         */
        public static function getValueById($vid)
        {
            $db = Rapidkart::getInstance()->getDB();
            $sql = "SELECT value from " . SystemTables::DB_TBL_VARIABLE . " WHERE vid = '$vid'  AND company_id ='::company_id' ";
            $result = $db->query($sql, array('::company_id' => BaseConfig::$company_id));
            if ($result)
            {
                $row = $db->fetchObject($result);
                return (!empty($row->value)) ? $row->value : "";
            }
        }

        public static function getVariables()
        {
            $db = Rapidkart::getInstance()->getDB();
            $sql = "SELECT *  from " . SystemTables::DB_TBL_VARIABLE . " v LEFT JOIN " . SystemTables::DB_TBL_VARIABLE_CATEGORY . " vc ON v.vcid = vc.vcid ";
            $result = $db->query($sql, array('::company' => BaseConfig::$company_id));
            $sitevariables = array();

            while ($row = $db->fetchObject($result))
            {
                $sitevariables[$row->vid] = new SiteVariable($row->vid);
            }
            return $sitevariables;
        }

        public static function getVariableValue($vid)
        {
            $db = Rapidkart::getInstance()->getDB();
            $sql = "SELECT * FROM " . SystemTables::DB_TBL_VARIABLE_COMPANY_MAPPING . " WHERE vid = '::vid' and company_id = '::company' ";
            $res = $db->query($sql, array('::vid' => $vid, '::company' => BaseConfig::$company_id));
            if (!$res || $db->resultNumRows($res) < 1)
            {
                return FALSE;
            }
            $row = $db->fetchObject($res);
            return $row;
        }

        public static function getVariableConfig($company_id)
        {
            $db = Rapidkart::getInstance()->getDB();
            $sql = "SELECT * FROM " . SystemTables::DB_TBL_VARIABLE_COMPANY_MAPPING . " WHERE  company_id = '::company' ";
            $res = $db->query($sql, array('::company' => $company_id));
            if (!$res || $db->resultNumRows($res) < 1)
            {
                return FALSE;
            }
            $ret = array();
            while ($row = $db->fetchObject($res))
            {
                $ret[$row->vid] = $row->variable_value;
            }
            return $ret;
        }

    }
    
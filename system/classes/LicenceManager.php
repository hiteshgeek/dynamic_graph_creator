<?php

    /*
     * To change this license header, choose License Headers in Project Properties.
     * To change this template file, choose Tools | Templates
     * and open the template in the editor.
     */

    /**
     * Description of LicenceManager]
     *
     * @author accrete
     */
    class LicenceManager
    {

        public static $is_ecom = 0;

        public static function insertDomains($licid, $domains = array(), $delete = FALSE)
        {
            $db = Rapidkart::getInstance()->getDB();
            if ($delete)
            {
                $sql = "DELETE FROM " . SystemTables::DB_TBL_LICENCE_DOMAIN . " WHERE licid = '::licid' ";
                $res = $db->query($sql, array('::licid' => $licid));
                if (!$res)
                {
                    return FALSE;
                }
            }
            if (!empty($domains))
            {
                $str = array();
                foreach ($domains as $domain)
                {
                    $str[] = "('" . $domain['domain'] . "','" . $licid . "','" . Session::loggedInUid() . "')";
                }
                if (!empty($str))
                {
                    $sql = "INSERT INTO " . SystemTables::DB_TBL_LICENCE_DOMAIN . " (url , licid, created_uid) VALUES " . implode(",", $str);
                    $res = $db->query($sql);
                    if (!$res)
                    {
                        return FALSE;
                    }
                }
            }
            return TRUE;
        }

        public static function insertCompanies($licid, $companies = array(), $delete = FALSE)
        {
            $db = Rapidkart::getInstance()->getDB();
            if ($delete)
            {
                $sql = "DELETE FROM " . SystemTables::DB_TBL_LICENCE_COMPANIES . " WHERE licid = '::licid' ";
                $res = $db->query($sql, array('::licid' => $licid));
                if (!$res)
                {
                    return FALSE;
                }
            }
            if (!empty($companies))
            {
                $str = array();
                foreach ($companies as $company)
                {
                    $str[] = "('" . $company['company'] . "','" . $licid . "','" . Session::loggedInUid() . "')";
                }
                if (!empty($str))
                {
                    $sql = "INSERT INTO " . SystemTables::DB_TBL_LICENCE_COMPANIES . " (name , licid, created_uid) VALUES " . implode(",", $str);
                    $res = $db->query($sql);
                    if (!$res)
                    {
                        return FALSE;
                    }
                }
            }
            return TRUE;
        }

        public static function checkLicenceNumberExists($number, $licid)
        {
            $db = Rapidkart::getInstance()->getDB();
            $sql = "SELECT * FROM " . SystemTables::DB_TBL_LICENCE . " WHERE licence_number = '::number' ";
            $sql .= " AND licsid <>3";
            $args = array('::number' => $number);

            if ($licid)
            {
                $sql .= " AND licid != '::licid' ";
                $args['::licid'] = $licid;
            }

            $res = $db->query($sql, $args);
            if (!$res || $db->resultNumRows($res) < 1)
            {
                return FALSE;
            }
            return TRUE;
        }

        public static function getFullId($id, $row = NULL, $row_id = NULL)
        {
            return Utility::variableGet('site_code') . "CLI" . str_pad($id, 6, 0, STR_PAD_LEFT);
        }

        public static function getDomains($id)
        {
            $db = Rapidkart::getInstance()->getDB();
            $sql = "SELECT licdoid FROM " . SystemTables::DB_TBL_LICENCE_DOMAIN . " WHERE licid = '::id' ";
            $args = array('::id' => $id);
            $res = $db->query($sql, $args);
            if (!$res || $db->resultNumRows($res) < 1)
            {
                return FALSE;
            }
            $ret = array();
            while ($row = $db->fetchObject($res))
            {
                $ret[$row->licdoid] = new LicenceDomain($row->licdoid);
            }
            return $ret;
        }

        public static function getCompanies($id = NULL)
        {
            $db = Rapidkart::getInstance()->getDB();
            $sql = "SELECT liccoid FROM " . SystemTables::DB_TBL_LICENCE_COMPANIES . "  WHERE  liccosid = 1 ";
            if (!empty($id) && $id > 0)
            {
                $sql .= " AND licid = '::id' ";
                if ($id == 86)
                {
                    $sql .= " ORDER BY liccoid DESC ";
                }
                $args = array('::id' => $id);
                $res = $db->query($sql, $args);
            }
            else
            {
                $res = $db->query($sql);
            }


            if (!$res || $db->resultNumRows($res) < 1)
            {
                return FALSE;
            }
            $ret = array();
            while ($row = $db->fetchObject($res))
            {
                $ret[$row->liccoid] = new LicenceCompanies($row->liccoid);
            }
            return $ret;
        }

        public static function getCompaniesForReport($report_id = NULL)
        {
            $db = Rapidkart::getInstance()->getDB();
            $sql = "SELECT liccoid FROM " . SystemTables::DB_TBL_LICENCE_COMPANIES . "  WHERE liccosid = 1 AND liccoid IN (SELECT DISTINCT(company_id) FROM `sms_report_log` WHERE smsrepoid='::report_id') ";

            $args = array('::report_id' => $report_id);
            $res = $db->query($sql, $args);
            if (!$res || $db->resultNumRows($res) < 1)
            {
                return FALSE;
            }
            $ret = array();
            while ($row = $db->fetchObject($res))
            {
                $ret[$row->liccoid] = new LicenceCompanies($row->liccoid);
            }
            return $ret;
        }

        public static function checkDomainExists($name)
        {
            $db = Rapidkart::getInstance()->getDB();
            $sql = "SELECT ld.licid FROM " . SystemTables::DB_TBL_LICENCE_DOMAIN . " ld JOIN licence l ON(l.licid = ld.licid and l.licsid = 1 )  WHERE ld.url = '::url'";
            $res = $db->query($sql, array('::url' => $name));
            if (!$res || $db->resultNumRows($res) < 1)
            {
                return FALSE;
            }
            $row = $db->fetchObject($res);
            return new Licence($row->licid);
        }

        public static function getAllMasking()
        {
            $db = Rapidkart::getInstance()->getDB();
            $sql = "SELECT *  FROM " . SystemTables::DB_TBL_MASK_CONFIG;
            $res = $db->query($sql);
            if (!$res || $db->resultNumRows($res) < 1)
            {
                return FALSE;
            }
            $ret = array();
            while ($row = $db->fetchObject($res))
            {
                $ret[] = $row;
            }
            return $ret;
        }

        public static function getMaskMapping($licid, $key = false)
        {
            $db = Rapidkart::getInstance()->getDB();
            $sql = "SELECT m.title , m.maskcid , licmaskmpid FROM " . SystemTables::DB_TBL_LICENCE_MASK_CONFIG_MAPPING . " lm JOIN " . SystemTables::DB_TBL_MASK_CONFIG . " m ON(m.maskcid = lm.maskcid)  WHERE lm.licid = '::licid'";
            $res = $db->query($sql, array('::licid' => $licid));
            if (!$res || $db->resultNumRows($res) < 1)
            {
                return FALSE;
            }
            $ret = array();
            while ($row = $db->fetchObject($res))
            {
                if ($key)
                {
                    $ret[$row->maskcid] = $row;
                }
                else
                {
                    $ret[] = $row->title;
                }
            }
            return $ret;
        }

        public static function updateLicenceMasking($data)
        {
            $db = Rapidkart::getInstance()->getDB();

            $licid = BaseConfig::$licence_id;
            $pmid = isset($data['licmaskmpid']) ? $data['licmaskmpid'] : 0;
            $maskcid = isset($data['maskcid']) ? $data['maskcid'] : 0;
            $uid = Session::loggedInUid();
            if ($pmid <= 0)
            {
                $data['insert'] = 1;
            }
            $return_pmid = 0;
            if (isset($data['insert']) && $data['insert'] > 0)
            {

                $sql = " INSERT INTO `licence_mask_config_mapping`(`licid`, `maskcid`) VALUES ($licid , $maskcid) ";
                $res = $db->query($sql);
                if (!$res)
                {
                    $db->rollBack();
                    $db->autoCommit(true);
                    Utility::ajaxResponseFalse("Fail to insert");
                }
                $return_pmid = $db->lastInsertId();
            }
            else
            {
                $sql = " DELETE FROM  `licence_mask_config_mapping` WHERE maskcid = $maskcid AND licid = " . $licid;
                $res = $db->query($sql);
                if (!$res)
                {
                    $db->rollBack();
                    $db->autoCommit(true);
                    Utility::ajaxResponseFalse("Fail to insert");
                }
            }
            $db->commit();
            Utility::ajaxResponseTrue("Updated Successfully", $return_pmid);
        }

        public static function getInvoiceConfig($licid)
        {
            $db = Rapidkart::getInstance()->getDB();
            $sql = "SELECT iv.title as invoice_config_value_title, i.title as invoice_config_title FROM " . SystemTables::DB_TBL_LICENCE_INVOICE_CONFIG_MAPPING . " lm JOIN " . SystemTables::DB_TBL_INVOICE_CONFIG_VALUES . " iv ON(iv.invcvid = lm.invcvid) LEFT JOIN " . SystemTables::DB_TBL_INVOICE_CONFIG . " i ON(i.invcid = lm.invcid)   WHERE lm.licid = '::licid'";
            $res = $db->query($sql, array('::licid' => $licid));
            if (!$res || $db->resultNumRows($res) < 1)
            {
                return FALSE;
            }
            $ret = array();
            while ($row = $db->fetchObject($res))
            {
                $ret[$row->invoice_config_title] = $row->invoice_config_value_title;
            }
            return $ret;
        }

        public static function getSystemConfig($licid)
        {
            $db = Rapidkart::getInstance()->getDB();
            $sql = "SELECT iv.title as preferences_config_value_title, lm.value  as data , iv.sptid FROM " . SystemTables::DB_TBL_LICENCE_SYSTEM_PREFERENCES_MAPPING . " lm JOIN " . SystemTables::DB_TBL_SYSTEM_PREFERENCES . " iv ON(iv.spid = lm.spid) WHERE lm.licid = '::licid' and iv.spsid != 3 ";
            $res = $db->query($sql, array('::licid' => $licid));
            if (!$res || $db->resultNumRows($res) < 1)
            {
                return FALSE;
            }
            $ret = array();
            while ($row = $db->fetchObject($res))
            {
                $return_data = 0;
                $str = '"' . $row->preferences_config_value_title . '"';
                switch ($row->sptid)
                {
                    case 1:
                    case 2:
                        $return_data = (strlen($row->data) > 0) ? $row->data : (defined($str) ? constant($str) : "");
                        break;
                    case 3:
                        $return_data = $row->data > 0 ? TRUE : FALSE;
                        break;
                    default :
                        $return_data = strlen($row->data) > 0 ? $row->data : (defined($str) ? constant($str) : "");
                }
                $ret[$row->preferences_config_value_title] = $return_data;
            }
            return $ret;
        }

        public static function getLicenceCompanyStartDate()
        {
            OutletManager::getSecondaryOutlet();
            WarehouseManager::getSecondaryWarehouse();
            $db = Rapidkart::getInstance()->getDB();
            $sql = "SELECT start_date , is_ecom FROM " . SystemTables::DB_TBL_LICENCE_COMPANIES . " WHERE liccosid = 1 AND licid = '::licid' and liccoid = '::id' ";
            $args = array('::licid' => BaseConfig::$licence_id, '::id' => BaseConfig::$company_id);
            $res = $db->query($sql, $args);
            if (!$res || $db->resultNumRows($res) < 1)
            {
                return FALSE;
            }
            $row = $db->fetchObject($res);
            self::$is_ecom = $row->is_ecom;
            return $row->start_date;
        }

        public static function getLicenceCompanyGSTRDate()
        {
            $db = Rapidkart::getInstance()->getDB();
            $sql = "SELECT gstr_date FROM " . SystemTables::DB_TBL_LICENCE_COMPANIES . " WHERE liccosid = 1 AND licid = '::licid' and liccoid = '::id' ";
            $args = array('::licid' => BaseConfig::$licence_id, '::id' => BaseConfig::$company_id);
            $res = $db->query($sql, $args);
            if (!$res || $db->resultNumRows($res) < 1)
            {
                return FALSE;
            }
            $row = $db->fetchObject($res);
            return $row->gstr_date;
        }

        public static function getLicenceMeasurements()
        {
            $db = Rapidkart::getInstance()->getDB();
            $sql = "SELECT DISTINCT(meaid) AS meaids FROM " . SystemTables::DB_TBL_LICENCE_MEASUREMENT_MAPPING . " WHERE licid = '::licid' AND licmeamsid = '1' ";
            $res = $db->query($sql, array('::licid' => BaseConfig::$licence_id));
            if (!$res || $db->resultNumRows($res) < 0)
            {
                return FALSE;
            }
            $arr = array();
            while ($row = $db->fetchObject($res))
            {
                $arr[] = $row->meaids;
            }
            return $arr;
        }

        public static function getMeasurementType()
        {
            $db = Rapidkart::getInstance()->getDB();
            $sql = "SELECT DISTINCT(meatid) AS meatid FROM " . SystemTables::DB_TBL_LICENCE_MEASUREMENT_MAPPING . " WHERE licid = '::licid' AND licmeamsid = '1'";
            $res = $db->query($sql, array('::licid' => BaseConfig::$licence_id));
            if (!$res || $db->resultNumRows($res) < 1)
            {
                return FALSE;
            }
            $arr = array();
            while ($row = $db->fetchObject($res))
            {
                $arr[] = $row->meatid;
            }
            return $arr;
        }

        public static function setCustomizedData($licence)
        {
            if (strlen($licence->getCustomData()) > 0)
            {
                $custom_data = json_decode($licence->getCustomData(), TRUE);
                if ($custom_data)
                {
                    foreach ($custom_data as $custom)
                    {
                        switch ($custom)
                        {
                            case 1:
                                SystemTables::$inventory_set = "inventory_set_" . BaseConfig::$licence_id;
                                break;
                            case 2:
                                SystemTables::$inventory_set_closed_stock_log = "inventory_set_closed_stock_log_" . BaseConfig::$licence_id;
                                break;
                        }
                    }
                }
            }
            else
            {
                SystemTables::$inventory_set = "inventory_set";
                SystemTables::$inventory_set_closed_stock_log = "inventory_set_closed_stock_log";
            }
        }

        public static function checkLicenceUsersCount(&$db)
        {
            $count = 0;
            $licence = new Licence(BaseConfig::$licence_id);
            $users = $licence->getUsers() + $licence->getFloatingUsers() + $licence->getDeferredUsers();

            if ($users > 0)
            {
                $args = array('::licid' => $licence->getId());
                $condition = " ustatusid = 1 AND licid = '::licid'";
                $sql = " SELECT count(uid) as count FROM " . SystemTables::DB_TBL_USER . " WHERE (" . $condition . ")";
                $res = $db->query($sql, $args);
                if ($res && $db->resultNumRows($res) > 0)
                {
                    $row = $db->fetchObject($res);
                    $user_count = $row->count;
                    if ($user_count > $users)
                    {
                        $db->rollBack();
                        $db->autoCommit(TRUE);
                        Utility::ajaxResponseFalse("Licence User Limit Crossed");
                    }
                }
            }
        }
    }
    
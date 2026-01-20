<?php

    /*
     * To change this license header, choose License Headers in Project Properties.
     * To change this template file, choose Tools | Templates
     * and open the template in the editor.
     */

    /**
     * Description of SystemPreferencesManager
     *
     * @author Aditya Sikarwar
     */
    class SystemPreferencesManager
    {

        public static function getPreferences()
        {
            $db = Rapidkart::getInstance()->getDB();
            $sql = "SELECT * FROM " . SystemTables::DB_TBL_SYSTEM_PREFERENCES . " WHERE spsid != 3 ORDER BY mid";

            $res = $db->query($sql);
            if (!$res || $db->resultNumRows() < 0)
            {
                return array();
            }
            $arr = array();

            while ($row = $db->fetchObject($res))
            {
                $arr[$row->spid] = new SystemPreferences($row->spid);
            }
            return $arr;
        }

        public static function getSystemPreferenceModules($spid)
        {
            $db = Rapidkart::getInstance()->getDB();
            $sql = "SELECT * FROM " . SystemTables::DB_TBL_SYSTEM_PREFERENCES_MODULE_MAPPING . " WHERE spid = '::spid' ";
            $args = array('::spid' => $spid);

            $res = $db->query($sql, $args);
            if (!$res || $db->resultNumRows() < 0)
            {
                return array();
            }
            $arr = array();

            while ($row = $db->fetchObject($res))
            {
                $arr[] = intval($row->mid);
            }
            return $arr;
        }

        public static function getSingleSystemPreference($spid)
        {
            $db = Rapidkart::getInstance()->getDB();
            $sql = "
        SELECT 
            * 
        FROM 
            " . SystemTables::DB_TBL_SYSTEM_PREFERENCES . " sp
        WHERE 
            sp.spsid != 3 AND 
            sp.spid = '::spid' 
        ORDER BY 
            sp.mid
        ";
            $args = array('::spid' => $spid);

            $res = $db->query($sql, $args);
            if (!$res || $db->resultNumRows() < 0)
            {
                return null;
            }
            $arr = null;

            while ($row = $db->fetchObject($res))
            {
                $arr = new SystemPreferences($row->spid);
            }
            return $arr;
        }

        public static function getSystemPreferenceCategories()
        {
            $db = Rapidkart::getInstance()->getDB();
            $sql = "SELECT * FROM " . SystemTables::DB_TBL_SYSTEM_PREFERENCES_CATEGORY . " WHERE spcsid != 3 ORDER BY name";

            $res = $db->query($sql);
            if (!$res || $db->resultNumRows() < 0)
            {
                return array();
            }
            $arr = array();

            while ($row = $db->fetchObject($res))
            {
                $arr[] = $row;
            }
            return $arr;
        }

        public static function getTitleValue($title)
        {
            $db = Rapidkart::getInstance()->getDB();
            $sql = "SELECT lm.value as data , s.sptid FROM " . SystemTables::DB_TBL_LICENCE_SYSTEM_PREFERENCES_MAPPING . " lm  JOIN " . SystemTables::DB_TBL_SYSTEM_PREFERENCES . "  s ON(lm.spid = s.spid) WHERE s.spsid != 3 and lm.licid = '::licid' AND s.title = '::title'";
            $args = array('::title' => $title, '::licid' => BaseConfig::$licence_id);
            $res = $db->query($sql, $args);

            if (!$res || $db->resultNumRows() < 1)
            {
                return FALSE;
            }
            $row = $db->fetchObject($res);
            return $row;
        }

        public static function UpdateSystemPreference($spid, $name, $description, $spcid)
        {
            $db = Rapidkart::getInstance()->getDB();
            $args = array(
                '::name' => $name,
                '::spid' => $spid,
                '::description' => $description
            );

            $update_fields = [
                "name = '::name'",
                "description = '::description'"
            ];

            if (intval($spcid) > 0)
            {
                $args['::spcid'] = $spcid;
                $update_fields[] = "spcid = '::spcid'";
            }
            else
            {
                $update_fields[] = "spcid = NULL";
            }

            $sql = "
        UPDATE 
            " . SystemTables::DB_TBL_SYSTEM_PREFERENCES . " 
        SET 
            " . implode(", ", $update_fields) . " 
        WHERE 
            spid = '::spid' ";

            $res = $db->query($sql, $args);

            if (!$res)
            {
                return FALSE;
            }

            return TRUE;
        }

        public static function InsertSystemPreferenceModuleMapping($spid, $preference_modules)
        {
            foreach ($preference_modules as $module)
            {
                $db = Rapidkart::getInstance()->getDB();
                $args = array(
                    '::spid' => $spid,
                    '::mid' => $module
                );

                $sql = "
            INSERT INTO 
                " . SystemTables::DB_TBL_SYSTEM_PREFERENCES_MODULE_MAPPING . " 
            (spid, mid) 
            VALUES 
            ('::spid', '::mid') ";

                $res = $db->query($sql, $args);

                if (!$res)
                {
                    return FALSE;
                }
            }

            return TRUE;
        }

        public static function DeleteSystemPreferenceModuleMapping($spid)
        {
            $db = Rapidkart::getInstance()->getDB();
            $args = array(
                '::spid' => $spid
            );

            $sql = "
        DELETE FROM 
            " . SystemTables::DB_TBL_SYSTEM_PREFERENCES_MODULE_MAPPING . " 
        WHERE 
            spid = '::spid' ";

            $res = $db->query($sql, $args);

            if (!$res)
            {
                return FALSE;
            }

            return TRUE;
        }

        public static function UpdateSystemPreferenceVerification($spid)
        {
            $db = Rapidkart::getInstance()->getDB();
            $args = array(
                '::spid' => $spid
            );

            $sql = "
        UPDATE 
            " . SystemTables::DB_TBL_SYSTEM_PREFERENCES . " 
        SET 
            verified_flag = 0 
        WHERE 
            spid = '::spid' ";

            $res = $db->query($sql, $args);
            if (!$res)
            {
                return FALSE;
            }

            return TRUE;
        }

        public static function VefirySystemPreference($spid)
        {
            $db = Rapidkart::getInstance()->getDB();
            $args = array(
                '::spid' => $spid
            );

            $sql = "
        UPDATE 
            " . SystemTables::DB_TBL_SYSTEM_PREFERENCES . " 
        SET 
            verified_flag = 1
        WHERE 
            spid = '::spid' ";

            $res = $db->query($sql, $args);
            if (!$res)
            {
                return FALSE;
            }

            return TRUE;
        }

        public static function Insertsystempreference($id, $field_value, $check = 1, $pid = NULL, $old_value = '')
        {
            $db = Rapidkart::getInstance()->getDB();
            $licspmid = NULL;

            if ($pid && $pid > 0)
            {
                $licspmid = $pid;
            }
            elseif ($check)
            {
                $Licencepreference = SystemPreferencesManager::getLicenceMappingSpid($id);
                $licspmid = $Licencepreference->licspmid;
            }
            $args = array(
                '::field_value' => $field_value,
                '::id' => $id,
                '::licid' => BaseConfig::$licence_id
            );

            if ($licspmid)
            {
                $sql = "UPDATE " . SystemTables::DB_TBL_LICENCE_SYSTEM_PREFERENCES_MAPPING . " SET value = '::field_value' WHERE licspmid = '::licspmid' ";
                $args['::licspmid'] = $licspmid;
            }
            else
            {
                $sql = "INSERT INTO " . SystemTables::DB_TBL_LICENCE_SYSTEM_PREFERENCES_MAPPING . " (licid ,spid,value) VALUES ('::licid' ,'::id','::field_value')";
            }

            $res = $db->query($sql, $args);
            if (!$res)
            {
                return FALSE;
            }

            if ($field_value != $old_value)
            {
                $args['::old'] = $old_value;
                $args['::uid'] = Session::loggedInUid();
                $args['::user'] = SystemConfig::getUser()->getName();
                $sql = "INSERT INTO `system_preferences_licence_history`(`spid`, `licid`, `old`, `new`, `created_uid`, `created_user`) VALUES ('::id' , '::licid' , '::old', '::field_value', '::uid' , '::user')";
                $res = $db->query($sql, $args);
                if (!$res)
                {
                    return FALSE;
                }
            }
            return TRUE;
        }

        public static function getLicenceMappingSpid($id)
        {

            $db = Rapidkart::getInstance()->getDB();
            $sql = "SELECT * FROM " . SystemTables::DB_TBL_LICENCE_SYSTEM_PREFERENCES_MAPPING . " WHERE spid = ::spid and licid = '::licid' LIMIT 1";
            $args = array('::spid' => $id, '::licid' => BaseConfig::$licence_id);
            $res = $db->query($sql, $args);
            if (!$res || $db->resultNumRows($res) < 1)
            {
                return FALSE;
            }
            $row = $db->fetchObject($res);

            return $row;
        }

        public static function getAllTitleValues()
        {
            $db = Rapidkart::getInstance()->getDB();
            $sql = "SELECT lm.value , s.title FROM " . SystemTables::DB_TBL_LICENCE_SYSTEM_PREFERENCES_MAPPING . " lm  JOIN " . SystemTables::DB_TBL_SYSTEM_PREFERENCES . "  s ON(lm.spid = s.spid) WHERE s.spsid != 3 and lm.licid = '::licid' ";
            $args = array('::licid' => BaseConfig::$licence_id);
            $res = $db->query($sql, $args);
            if (!$res || $db->resultNumRows() < 1)
            {
                return FALSE;
            }
            $ret = array();
            while ($row = $db->fetchObject($res))
            {
                $arr = array();
                $arr['title'] = $row->title;
                $arr['value'] = $row->value;
                $ret[] = $arr;
            }
            return $ret;
        }

        public static function getPreferencesView()
        {
            $db = Rapidkart::getInstance()->getDB();
            // $sql = "CREATE OR REPLACE ALGORITHM = UNDEFINED DEFINER = root@localhost SQL SECURITY DEFINER VIEW settings_view_" . BaseConfig::$company_id . " AS  SELECT s.spid , REPLACE(s.name, '_' , ' ') as name , s.title AS preference_details, s.title , s.description , s.sptid , lm.licid , lm.value as oo , (CASE WHEN s.sptid IN(2, 3) AND lm.value > 0 THEN lm.value WHEN s.sptid = 1 AND LENGTH(lm.value) > 0 THEN lm.value WHEN s.sptid IN(2,3) AND (lm.value <=0 OR lm.value IS NULL) THEN 0 WHEN s.sptid = 1 AND (LENGTH(lm.value) <= 0 OR lm.value IS NULL) THEN '' ELSE 0 END)  as value , COALESCE(lm.licspmid,0) as pid, COALESCE(s.verified_flag,-1) AS verified_flag  FROM  " . SystemTables::DB_TBL_SYSTEM_PREFERENCES . " s  LEFT JOIN " . SystemTables::DB_TBL_LICENCE_SYSTEM_PREFERENCES_MAPPING . " lm  ON(lm.spid = s.spid AND lm.licid = '::lic') WHERE s.spsid != 3 GROUP BY s.spid ORDER BY s.spid ASC ";
            $sql = "
    CREATE OR REPLACE ALGORITHM = UNDEFINED 
    DEFINER = root@localhost 
    SQL SECURITY DEFINER 
    VIEW settings_view_" . BaseConfig::$company_id . " AS  
    SELECT 
        s.spid, 
        REPLACE(s.name, '_', ' ') AS name, 
        s.title AS preference_details, 
        s.title, 
        s.description, 
        s.sptid, 
        s.spcid, 
        spc.name as category_name,
        lm.licid, 
        lm.value AS oo, 
        (CASE 
            WHEN s.sptid IN (2, 3) AND lm.value > 0 THEN lm.value 
            WHEN s.sptid = 1 AND LENGTH(lm.value) > 0 THEN lm.value 
            WHEN s.sptid IN (2,3) AND (lm.value <= 0 OR lm.value IS NULL) THEN 0 
            WHEN s.sptid = 1 AND (LENGTH(lm.value) <= 0 OR lm.value IS NULL) THEN '' 
            ELSE 0 
        END) AS value, 
        COALESCE(lm.licspmid, 0) AS pid, 
        COALESCE(s.verified_flag, -1) AS verified_flag,
        COALESCE(GROUP_CONCAT(m.name ORDER BY m.name SEPARATOR ','), '') AS module_names,
        COALESCE(GROUP_CONCAT(m.mid ORDER BY m.mid SEPARATOR ','), '') AS module_ids
    FROM  
        " . SystemTables::DB_TBL_SYSTEM_PREFERENCES . " s  
    LEFT JOIN 
        " . SystemTables::DB_TBL_LICENCE_SYSTEM_PREFERENCES_MAPPING . " lm  
    ON 
        (lm.spid = s.spid AND lm.licid = '::lic') 
    LEFT JOIN
        " . SystemTables::DB_TBL_SYSTEM_PREFERENCES_CATEGORY . " spc 
    ON
        (s.spcid = spc.spcid)
    LEFT JOIN 
        " . SystemTables::DB_TBL_SYSTEM_PREFERENCES_MODULE_MAPPING . " spm 
    ON 
        s.spid = spm.spid
    LEFT JOIN 
        " . SystemTables::DB_TBL_MODULE . " m 
    ON 
        spm.mid = m.mid
    WHERE 
        s.spsid != 3 
    GROUP BY 
        s.spid 
    ORDER BY 
        s.spid ASC
    ";

            $res = $db->query($sql, array('::lic' => BaseConfig::$licence_id));
            return $res ? true : FALSE;
        }

        public static function limitCheck($id, $gstin = "", $amount = 0, $cpoid = 0)
        {
            // 1 => Invoice , 2 => Receipt ,  3 => Payment
            $limit_bit = 0;
            $limit_msg = "";
            switch ($id)
            {
                case 1:

                    if (strlen($gstin) > 0)
                    {
                        $gst_limit = getSettings("IS_SALES_INVOICE_GST_LIMIT");
                        if ($gst_limit > 0 && $amount > $gst_limit)
                        {
                            $limit_bit = 1;
                            $limit_msg = "You can not create invoice more than " . $gst_limit . " for GST customer";
                        }
                    }
                    else
                    {
                        $without_gst_limit = getSettings("IS_SALES_INVOICE_WITHOUT_GST_LIMIT");
                        if ($without_gst_limit > 0 && $amount > $without_gst_limit)
                        {
                            $limit_bit = 1;
                            $limit_msg = "You can not create invoice more than " . $without_gst_limit . " for Non-GST customer";
                        }
                    }
                    break;
                case 2:
                    if ($cpoid == 5)
                    {
                        if (strlen($gstin) > 0)
                        {
                            $gst_limit = getSettings("IS_RECEIPT_GST_CASH_LIMIT");
                            if ($gst_limit > 0 && $amount > $gst_limit)
                            {
                                $limit_bit = 1;
                                $limit_msg = "You can not create Receipt more than " . $gst_limit . " for GST Party";
                            }
                        }
                        else
                        {
                            $without_gst_limit = getSettings("IS_RECEIPT_WITHOUT_GST_CASH_LIMIT");
                            if ($without_gst_limit > 0 && $amount > $without_gst_limit)
                            {
                                $limit_bit = 1;
                                $limit_msg = "You can not create Receipt more than " . $without_gst_limit . " for Non-GST Party";
                            }
                        }
                    }
                    break;
                case 3:
                    if ($cpoid == 5)
                    {
                        $limit = getSettings("IS_PAYMENT_CASH_LIMIT");
                        if ($limit > 0 && $amount > $limit)
                        {
                            $limit_bit = 1;
                            $limit_msg = "You can not create Payment more than " . $limit . " for Party";
                        }
                    }
            }

            return array("limit_bit" => $limit_bit, "limit_msg" => $limit_msg);
        }

        public static function getSystemPreferencesModuleMapping($mid, $config = 1, $notification = 0)
        {
            $db = Rapidkart::getInstance()->getDB();
            $sql = " SELECT s.spid , s.spgid , s.name, s.title , s.description, s.sptid, sg.spgid , sg.name as `group` ,  (CASE 
            WHEN s.sptid IN (2, 3) AND lm.value > 0 THEN lm.value 
            WHEN s.sptid = 1 AND LENGTH(lm.value) > 0 THEN lm.value 
            WHEN s.sptid IN (2,3) AND (lm.value <= 0 OR lm.value IS NULL) THEN 0 
            WHEN s.sptid = 1 AND (LENGTH(lm.value) <= 0 OR lm.value IS NULL) THEN '' 
            ELSE 0 
        END) AS value, 
        COALESCE(lm.licspmid, 0) AS pid FROM `system_preferences_module_mapping` sm JOIN system_preferences s ON(s.spid = sm.spid ";

            if ($config)
            {
                $sql .= " AND s.spcid = 4";
            }
            if ($notification)
            {
                $sql .= " AND s.spcid = 5";
            }

            $sql .= ") JOIN system_preferences_group sg ON(sg.spgid = s.spgid)  LEFT JOIN 
        " . SystemTables::DB_TBL_LICENCE_SYSTEM_PREFERENCES_MAPPING . " lm  ON (lm.spid = s.spid AND lm.licid = '::lic')  WHERE sm.mid = $mid GROUP BY s.spid ";
            $res = $db->query($sql, array('::lic' => BaseConfig::$licence_id));
            echo $db->getMysqlError();

            if (!$res || $db->resultNumRows($res) < 1)
            {
                return false;
            }
            $ret = array();
            $groupNames = array();
            while ($row = $db->fetchObject($res))
            {
                if (!isset($groupNames[$row->spgid]))
                {
                    $groupNames[$row->spgid] = $row->group;
                }
                $ret[$row->spgid][] = $row;
            }
            return array('ret' => $ret, 'group' => $groupNames);
        }
    }
    
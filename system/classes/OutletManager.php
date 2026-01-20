<?php

    /*
     * To change this license header, choose License Headers in Project Properties.
     * To change this template file, choose Tools | Templates
     * and open the template in the editor.
     */

    class OutletManager
    {

        public static $secondary_outlet_chkid = 0;

        public static function checkOutletTransaction($chkid)
        {
            $db = Rapidkart::getInstance()->getDB();
            $sql = "SELECT 
    CASE 
        WHEN EXISTS (
            SELECT 1
            FROM enquiry 
            WHERE chkid = '::chkid' AND ensid IN (1, 2, 5, 6) AND company_id = '::comp'
        )
        OR EXISTS (
            SELECT 1
            FROM account_voucher 
            WHERE chkid = '::chkid' AND avsid IN (1, 2) AND company_id = '::comp'
        )
        OR EXISTS (
            SELECT 1
            FROM quotation 
            WHERE chkid = '::chkid' AND qosid IN (1, 2, 4, 6, 7, 8) AND company_id = '::comp'
        )
        OR EXISTS (
            SELECT 1
            FROM checkpoint_order 
            WHERE chkid = '::chkid' AND chkosid IN (1, 2, 3, 4, 56, 7, 8, 9, 12, 13, 14, 15, 16, 17) AND company_id = '::comp'
        )
    THEN FALSE
    ELSE TRUE
END AS result";
            $args = array('::chkid' => $chkid, '::comp' => BaseConfig::$company_id);

            $res = $db->query($sql, $args);
            if (!$res)
            {
                return FALSE; // Return FALSE if the query fails
            }

// Fetch the result as an associative array
            $row = $db->fetchObject($res);
            if ($row && isset($row->result))
            {
                return $row->result; // Return TRUE or FALSE based on the SQL result
            }

            return FALSE;
        }

        public static function getItems($iids)
        {
            $db = Rapidkart::getInstance()->getDB();
            $sql = "SELECT ii.iid, ii.created_ts as time, ii.price,ii.name as item, ip.ipid, ip.image FROM " . SystemTables::DB_TBL_ITEM
                    . " ii LEFT JOIN (SELECT MIN(ipid) as ipid,image,iid FROM " . SystemTables::DB_TBL_ITEM_PHOTO . " GROUP BY iid) as ip ON (ip.iid = ii.iid)"
                    . " WHERE ii.iid IN (" . implode(',', $iids) . ")";
            $res = $db->query($sql);
            if (!$res || $db->resultNumRows($res) < 1)
            {
                return FALSE;
            }
            $ret = array();
            while ($row = $db->fetchObject($res))
            {
                $ret[$row->iid] = $row;
            }
            return $ret;
        }

        public static function getItemAttributes($iids = null)
        {
            if (isset($iids) && !empty($iids))
            {
                $db = Rapidkart::getInstance()->getDB();
                $iidStr = "AND i.iid IN(";
                foreach ($iids as $iid):
                    $iidStr .= $iid . ",";
                endforeach;
                $iidStr = rtrim($iidStr, ',');
                $iidStr .= " )";
                $sql = "SELECT i.iid, ia.name, ia.aid, group_concat('\"',iav.avid, '\" :\"', iav.value,'\"') as attributes "
                        . " FROM  " . SystemTables::DB_TBL_ITEM_ITEM_ATTRIBUTE_VALUE . " iiav "
                        . " INNER JOIN " . SystemTables::DB_TBL_ITEM . " i ON i.iid = iiav.iid "
                        . " INNER JOIN " . SystemTables::DB_TBL_ATTRIBUTE . " ia  ON ia.aid = iiav.aid AND ia.filter = 1 "
                        . " INNER JOIN " . SystemTables::DB_TBL_ATTRIBUTE_VALUE . " iav ON iav.avid = iiav.avid"
                        . " WHERE i.istatusid = 1 $iidStr GROUP BY iav.aid";
                $rs = $db->query($sql);
                $attributes = array();
                while ($row = $db->fetchObject($rs))
                {
                    $row->attributes = json_decode("{" . $row->attributes . "}");
                    $attributes[$row->aid] = $row;
                }
                return $attributes;
            }
        }

        public static function getItemsCategory()
        {
            $db = Rapidkart::getInstance()->getDB();
            $sql = "SELECT c.catid,c.name FROM " . SystemTables::DB_TBL_CATEGORY . " c 
                INNER JOIN " . SystemTables::DB_TBL_GI_CATEGORY . " igc ON  c.catid = igc.catid and parent = 0 AND c.catsid = 1
                GROUP BY c.catid
               ";
            $rs = $db->query($sql);
            $item = array();
            while ($row = $db->fetchObject($rs))
            {
                $item[$row->catid] = $row->name;
            }
            return $item;
        }

        public static function getAllParentCategories($catid = null)
        {

            $db = Rapidkart::getInstance()->getDB();
            $sql = " SELECT catid FROM " . SystemTables::DB_TBL_CATEGORY . " WHERE catid IN "
                    . "  ( SELECT c.main_parent as catid from category c ,item_gi_category igc"
                    . "    WHERE c.catid = igc.catid AND c.catsid = 1  GROUP BY c.main_parent  )";

            $res = $db->query($sql);
            if (!$res || $db->resultNumRows($res) == 0)
            {
                return array();
            }
            $arr = array();
            $categories = array();
            while ($row = $db->fetchObject($res))
            {
                $categories[$row->catid] = $row;
            }
            return $categories;
        }

        public static function getItemCategories($catid = false, $children = false, $levels = false)
        {
            if ($levels)
            {
                $catids = self::getAllParentCategories();
            }
            $categories = array();
            $db = Rapidkart::getInstance()->getDB();
            $condition_1 = "";
            $condition_2 = "";
            if ($catid != false)
            {
                $condition_1 = ' AND a.catid = ' . $catid . ' AND b.parent = ' . $catid . ' ';
            }
            else
            {
                $condition_2 = ' AND a.parent = 0 AND b.parent=0';
            }
            $condition_3 = '';
            if (isset($catids) && is_array($catids))
            {
                if (empty($catids))
                {
                    return $categories;
                }
                $condition_3 = ' AND a.catid in (' . implode(",", array_keys($catids))
                        . ') AND b.catid in (' . implode(",", array_keys($catids)) . ') ';
            }

            $args = array(
                "::cat_status" => 1
            );
            $sql = "SELECT b.catid,b.name,b.parent,b.main_parent,b.description FROM " . SystemTables::DB_TBL_CATEGORY . " a 
                                INNER JOIN " . SystemTables::DB_TBL_CATEGORY . " b ON   b.main_parent = a.main_parent $condition_1 $condition_3
                                      WHERE b.catsid = ::cat_status AND a.catsid = ::cat_status $condition_2 GROUP BY b.catid";

            $res = $db->query($sql, $args);
            if (!$res || $db->resultNumRows($res) == 0)
            {
                return array();
            }

            while ($row = $db->fetchObject($res))
            {
                if ($children == true)
                {
                    $categories[$row->catid] = array_merge($arr = array("catid" => $row->catid,
                        "parent" => $row->parent,
                        "name" => $row->name,
                        "main_parent" => $row->main_parent,
                        "description" => $row->description), array("children" => self::getItemCategories($row->catid, $children, false)));
                }
                else
                {
                    $categories[$row->catid] = array("catid" => $row->catid, "parent" => $row->parent,
                        "name" => $row->name, "description" => $row->description
                    );
                }
            }
            return $categories;
        }

        public static function getItemDetails($iid = null)
        {
            if (!isset($iid))
            {
                return false;
            }

            $db = Rapidkart::getInstance()->getDB();
            $args = array('::iid' => $iid);
            $sql = "SELECT i.*,c1.catid ,c1.name as category,c1.main_parent,c1.parent,c2.name as main_category,group_concat(iip.image) as images "
                    . " FROM " . SystemTables::DB_TBL_ITEM . " i "
                    . " INNER JOIN " . SystemTables::DB_TBL_ITEM_PHOTO . " iip ON iip.iid = i.iid "
                    . " INNER JOIN " . SystemTables::DB_TBL_GI_CATEGORY . " igc ON igc.giid = i.giid"
                    . " INNER JOIN " . SystemTables::DB_TBL_CATEGORY . " c1 ON c1.catid = igc.catid "
                    . " INNER JOIN " . SystemTables::DB_TBL_CATEGORY . " c2 ON c2.catid = c1.main_parent "
                    . " WHERE i.iid= ::iid AND i.istatusid = 1";
            $res = $db->query($sql, $args);
            if (!$res || $db->resultNumRows($res) == 0)
            {
                return array();
            }
            $item = array();
            while ($row = $db->fetchObject($res))
            {
                if ($row->iid)
                {
                    $row->images = explode(',', $row->images);
                    $item[$row->iid] = $row;
                }
            }
            return $item;
        }

        public static function getRelatedItems($iid, $catid, $main_parent)
        {
            $db = Rapidkart::getInstance()->getDB();
            if (!(isset($catid, $main_parent)))
            {
                return false;
            }
            $sql = " SELECT *,i.name as name FROM " . SystemTables::DB_TBL_ITEM . "  i "
                    . " INNER JOIN " . SystemTables::DB_TBL_GI_CATEGORY . "  igc ON igc.giid = i.giid"
                    . " INNER JOIN " . SystemTables::DB_TBL_CATEGORY . "  c ON c.catid =igc.catid"
                    . " INNER JOIN " . SystemTables::DB_TBL_ITEM_PHOTO . "  iip ON iip.iid = i.iid "
                    . " WHERE c.catid = '::catid' AND c.catsid = 1 AND c.main_parent ='::main_parent'"
                    . " AND i.istatusid = 1 AND i.iid != $iid "
                    . " GROUP BY i.iid LIMIT 6";
            $args = array('::catid' => $catid, '::main_parent' => $main_parent, '::iid' => $iid);
            $res = $db->query($sql, $args);
            if (!$res || $db->resultNumRows($res) == 0)
            {
                return array();
            }
            while ($row = $db->fetchObject($res))
            {
                $item[$row->iid] = $row;
            }
            return $item;
        }

        public static function checkItemStock($iid)
        {
            $db = Rapidkart::getInstance()->getDB();
            $sql = "";
        }

        public static function timeElapsedString($datetime, $full = false)
        {
            $now = new DateTime;
            $ago = new DateTime($datetime);
            $diff = $now->diff($ago);

            $diff->w = floor($diff->d / 7);
            $diff->d -= $diff->w * 7;

            $string = array(
                'y' => 'year',
                'm' => 'month',
                'w' => 'week',
                'd' => 'day',
                'h' => 'hour',
                'i' => 'minute',
                's' => 'second',
            );
            foreach ($string as $k => &$v)
            {
                if ($diff->$k)
                {
                    $v = $diff->$k . ' ' . $v . ($diff->$k > 1 ? 's' : '');
                }
                else
                {
                    unset($string[$k]);
                }
            }

            if (!$full) $string = array_slice($string, 0, 1);
            return $string ? implode(', ', $string) . ' ago' : 'just now';
        }

        public static function getInventorySetItems($isid, $iid, $cuid = null)
        {
            $customerStr = "";
            if (isset($cuid) && $cuid != null)
            {
                $customerStr = " AND ci.cuid ='$cuid'";
            }
            $inventoryStr = "";
            $bookingStr = " AND cob.isid = ins.isid ";
            if (isset($isid) && $isid > 0 && is_int($isid))
            {
                $inventoryStr = " AND ins.isid = '::isid'";
                $bookingStr = " AND cob.isid = '::isid'";
            }
            $db = Rapidkart::getInstance()->getDB();
            $sql = "   SELECT ins.*,i.name as item_name, count(isi.isid) as total_stock, "
                    . " count(isi.isid) - COALESCE((SELECT sum(qty) FROM " . SystemTables::DB_TBL_CHECKPOINT_ORDER_BOOKING . " cob WHERE cob.iid = '::iid' $bookingStr AND cob.chkobooksid = 1 ),0) - "
                    . " COALESCE((SELECT sum(qty) FROM " . SystemTables::DB_TBL_CUSTOMER_INVENTORY . " ci WHERE ci.isid = ins.isid),0)   as available_stock ,"
                    . " COALESCE((SELECT sum(qty) FROM " . SystemTables::DB_TBL_CUSTOMER_INVENTORY . " ci WHERE ci.isid = ins.isid $customerStr ),0) as blocked ,"
                    . " COALESCE((SELECT sum(qty) FROM " . SystemTables::DB_TBL_CHECKPOINT_ORDER_BOOKING . " cob WHERE cob.iid = '::iid' $bookingStr AND cob.chkobooksid = 1 ),0) as booked "
                    . " FROM " . SystemTables::$inventory_set . " ins "
                    . " INNER JOIN " . SystemTables::DB_TBL_ITEM . " i ON i.iid = ins.iid AND i.iid ='::iid'"
                    . " LEFT JOIN " . SystemTables::DB_TBL_INVENTORY_SET_ITEM . " isi ON isi.isid = ins.isid AND isi.isisid = 1"
                    . " WHERE ins.issid = 1 $inventoryStr GROUP BY isi.isid,ins.isid ORDER BY ins.isid";
            $args = array('::isid' => $isid,
                '::iid' => $iid
            );
            $res = $db->query($sql, $args);
            $inventory = array();
            if (!$res || $db->resultNumRows($res) == 0)
            {
                return array();
            }
            $max = 0;
            while ($row = $db->fetchObject($res))
            {
                if ($row->price > $max && $row->available_stock > 0)
                {
                    $inventory = $row;
                    $max = $row->price;
                }
            }
            return $inventory;
        }

        public static function getUserCheckPoint($uid, $outlid = null, $chkid = null, $gid = null, $gbutapid = null, $group_by_chkid = NULL, $group_by_outlid = NULL, $companies_array = array())
        {
            $db = Rapidkart::getInstance()->getDB();
            $user = new AdminUser($uid);
            if ($user->getIsAdmin() == 1)
            {
                $cond = "";
                if (isset($outlid) && $outlid != null && $outlid != false)
                {
                    $cond = " AND outlid = $outlid ";
                }
                if (isset($chkid) && $chkid != null && $chkid != false)
                {
                    $cond .= " AND chkid = $chkid ";
                }
                if ($gid)
                {
                    $cond .= " AND gbutacomgsdid = $gid";
                }
                if ($gbutapid)
                {

                    $cond .= " AND gbutapid = $gbutapid";
                }
                $cond .= "  AND company_id IN(::company_id) ";
                $sql = "SELECT outlid as id , name ,  chkid , sale_alid , purchase_alid , expense_alid, stid , pos_cuid , branch_sale_alid ,gbutapid , gbutacomgsdid , disable_tcs, is_secondary, is_default FROM " . SystemTables::DB_TBL_OUTLET . " WHERE  ((outlsid = 1)) $cond  ";
                $sql .= " ORDER BY is_default DESC";
            }
            else
            {
                $cond = "";
                if (isset($outlid) && $outlid != null && $outlid != false)
                {
                    $cond = " AND outlet.outlid = $outlid ";
                }
                if (isset($chkid) && $chkid != null && $chkid != false)
                {
                    $cond .= " AND outlet.chkid = $chkid ";
                }
                if ($gid)
                {
                    $cond .= " AND gbutacomgsdid = $gid";
                }

                if ($gbutapid)
                {

                    $cond .= " AND gbutapid = $gbutapid";
                }
                $cond .= " AND outlet.company_id IN(::company_id)  ";

                $sql = " SELECT o_user.outlid as id, outlet.name, outlet.chkid , outlet.sale_alid , outlet.purchase_alid , outlet.expense_alid, outlet.stid , pos_cuid , branch_sale_alid , gbutapid , gbutacomgsdid,disable_tcs, outlet.is_secondary, outlet.is_default FROM " . SystemTables::DB_TBL_OUTLET_USER_MAPPING . " o_user"
                        . " INNER JOIN " . SystemTables::DB_TBL_USER . " user ON user.uid = o_user.uid AND user.uid = ::uid "
                        . " INNER JOIN " . SystemTables::DB_TBL_OUTLET . " outlet ON o_user.outlid = outlet.outlid"
                        . " WHERE ((outlet.outlsid = 1))  $cond  ";

                $sql .= " ORDER BY is_default DESC";
            }
            $args = array('::uid' => $uid, '::company_id' => BaseConfig::$company_id);
            if ($companies_array && !empty($companies_array))
            {
                $args['::company_id'] = implode(',', $companies_array);
            }
            $res = $db->query($sql, $args);
            if (!$res || $db->resultNumRows($res) < 1)
            {
                return array();
            }
            $outlets = array();
            while ($row = $db->fetchObject($res))
            {
                if ($group_by_chkid)
                {
                    $outlets[$row->chkid] = array("id" => $row->id, "name" => $row->name, "chkid" => $row->chkid, "sale_alid" => $row->sale_alid, 'purchase_alid' => $row->purchase_alid, 'expense_alid' => $row->expense_alid, "stid" => $row->stid, 'pos_cuid' => $row->pos_cuid, 'branch_sale_alid' => $row->branch_sale_alid, 'gbutapid' => $row->gbutapid, 'gbutacomgsdid' => $row->gbutacomgsdid, 'disable_tcs' => $row->disable_tcs, 'is_secondary' => $row->is_secondary, 'is_default' => $row->is_default);
                }
                elseif ($group_by_outlid)
                {
                    $outlets[$row->id] = array("id" => $row->id, "name" => $row->name, "chkid" => $row->chkid, "sale_alid" => $row->sale_alid, 'purchase_alid' => $row->purchase_alid, 'expense_alid' => $row->expense_alid, "stid" => $row->stid, 'pos_cuid' => $row->pos_cuid, 'branch_sale_alid' => $row->branch_sale_alid, 'gbutapid' => $row->gbutapid, 'gbutacomgsdid' => $row->gbutacomgsdid, 'disable_tcs' => $row->disable_tcs, 'is_secondary' => $row->is_secondary, 'is_default' => $row->is_default);
                }
                else
                {
                    $outlets[] = array("id" => $row->id, "name" => $row->name, "chkid" => $row->chkid, "sale_alid" => $row->sale_alid, 'purchase_alid' => $row->purchase_alid, 'expense_alid' => $row->expense_alid, "stid" => $row->stid, 'pos_cuid' => $row->pos_cuid, 'branch_sale_alid' => $row->branch_sale_alid, 'gbutapid' => $row->gbutapid, 'gbutacomgsdid' => $row->gbutacomgsdid, 'disable_tcs' => $row->disable_tcs, 'is_secondary' => $row->is_secondary, 'is_default' => $row->is_default);
                }
            }
            return $outlets;
        }

        /*         * outlet.inc.php* */

        public static function getOutletLink($id, $row)
        {

            $link = new Link(JPath::absoluteUrl('outlet/view/' . $row["outlid"]), "", $id);
            return $link->publish();
        }

        public static function getOutletLinkForCode($id, $row)
        {
            $link = new Link(JPath::absoluteUrl('outlet/view/' . $row["outlid"]), "", $row["checkpoint_code"]);
            return $link->publish();
        }

        public static function getStatus($id)
        {
            switch ($id)
            {
                case 1:
                    return '<label class="label label-success">Active</label>';
                    break;
                case 2:
                    return '<label class="label label-danger">Inactive</label>';
                    break;
                case 3:
                    return '<label class="label label-default">Deleted</label>';
                    break;
            }
        }

        public static function getOutletUsers($outlid)
        {
            if (!Outlet::isExistent($outlid))
            {
                return false;
            }
            $db = Rapidkart::getInstance()->getDB();
            $sql = " SELECT user.uid, user.name FROM " . SystemTables::DB_TBL_OUTLET_USER_MAPPING . " o_user"
                    . " INNER JOIN " . SystemTables::DB_TBL_USER . " user ON user.uid = o_user.uid  "
                    . " WHERE o_user.company_id = '::company' and o_user.outlid IN ( '::outlid')";
            $res = $db->query($sql, array('::outlid' => $outlid, '::company' => BaseConfig::$company_id));
            if (!$res || $db->resultNumRows($res) < 1)
            {
                return false;
            }
            $users = array();
            while ($row = $db->fetchObject($res))
            {
                $users[] = array("id" => $row->uid, "name" => $row->name);
            }
            return $users;
        }

        public static function getOutletUsersWithoutAdmin($outlid)
        {
            if (!Outlet::isExistent($outlid))
            {
                return false;
            }
            $db = Rapidkart::getInstance()->getDB();
            $sql = " SELECT user.uid, user.name FROM " . SystemTables::DB_TBL_OUTLET_USER_MAPPING . " o_user"
                    . " INNER JOIN " . SystemTables::DB_TBL_USER . " user ON user.uid = o_user.uid  "
                    . " WHERE o_user.company_id = '::company' and o_user.outlid = '::outlid' and user.is_admin = 0";
            $res = $db->query($sql, array('::outlid' => $outlid, '::company' => BaseConfig::$company_id));
            if (!$res || $db->resultNumRows($res) < 1)
            {
                return false;
            }
            $users = array();
            while ($row = $db->fetchObject($res))
            {
                $users[] = array("id" => $row->uid, "name" => $row->name);
            }
            return $users;
        }

        public static function updateOutletUsers($outlid, $users = array())
        {
            if (!isset($outlid) || !Outlet::isExistent($outlid) || empty($users))
            {
                return false;
            }
            $userStr = "";
            foreach ($users as $key => $user)
            {
                $userStr .= '(' . $outlid . ',' . $user . ',' . BaseConfig::$company_id . '),';
            }
            $userStr = rtrim($userStr, ',');

            self::flushOutletUsers($outlid); // Flush existing users.

            $db = Rapidkart::getInstance()->getDB();
            $sql = " INSERT INTO " . SystemTables::DB_TBL_OUTLET_USER_MAPPING . " (outlid, uid,company_id) VALUES $userStr";
            $res = $db->query($sql);
            if (!$res)
            {
                return false;
            }
            return true;
        }

        public static function flushOutletUsers($outlid)
        {
            if (!Outlet::isExistent($outlid))
            {
                return false;
            }
            $db = Rapidkart::getInstance()->getDB();
            $sql = "DELETE FROM " . SystemTables::DB_TBL_OUTLET_USER_MAPPING . " WHERE outlid = '::outlid' ";
            $res = $db->query($sql, array('::outlid' => $outlid, '::company' => BaseConfig::$company_id));
            if (!$res)
            {
                return false;
            }
            return true;
        }

        public static function createCheckpointMapping($outlid, $chktid, $stid)
        {
            if (!Outlet::isExistent($outlid))
            {
                return false;
            }
            $db = Rapidkart::getInstance()->getDB();
            $sql = " INSERT INTO " . SystemTables::DB_TBL_CHECKPOINT_MAPPING . " (outlid,chktid,company_id , stid) VALUES ('::outlid','::chktid','::compid' , '::stid')";
            $args = array('::outlid' => $outlid, '::chktid' => $chktid, '::compid' => BaseConfig::$company_id, '::stid' => $stid);
            $res = $db->query($sql, $args);
            if (!$res)
            {
                return false;
            }
            return $db->lastInsertId();
        }

        public static function getAllOutlets($chkid = false, $byname = false, $lower = false, $all = false, $gbutacomgsdid = NULL, $by_code = false)
        {
            $db = Rapidkart::getInstance()->getDB();
            $sql = "SELECT outlid , chkid , name , is_prefix_affix , stid , sale_alid , purchase_alid, checkpoint_code FROM " . SystemTables::DB_TBL_OUTLET . " WHERE outlsid = '1' AND company_id = " . BaseConfig::$company_id . " ";
            $args = array();
            if ($gbutacomgsdid)
            {
                $sql .= " AND gbutacomgsdid = '::gbutacomgsdid'";
                $args['::gbutacomgsdid'] = $gbutacomgsdid;
            }
            $sql .= " ORDER BY is_default DESC";
            $res = $db->query($sql, $args);
            if (!$res || $db->resultNumRows($res) < 1)
            {
                return FALSE;
            }
            $ret = array();
            while ($row = $db->fetchObject($res))
            {
                if ($byname)
                {
                    $name = trim($row->name);
                    if ($lower)
                    {
                        $name = strtolower($name);
                    }
                    $ret[$name] = ($all ? $row : $row->chkid);
                }
                elseif($by_code)
                {
                    $code = trim($row->checkpoint_code);
                    if ($lower)
                    {
                        $code = strtolower($code);
                    }
                    $ret[$code] = $row;
                }
                else
                {
                    if ($chkid)
                    {
                        $ret[$row->chkid] = $row;
                    }
                    else
                    {
                        $ret[$row->outlid] = new Outlet($row->outlid);
                    }
                }
            }
            return $ret;
        }

        public static function searchOutlets($name)
        {
            $db = Rapidkart::getInstance()->getDB();
            $sql = "SELECT * FROM " . SystemTables::DB_TBL_OUTLET . " WHERE outlsid = '1' and company_id = '::company_id' and (name LIKE('%::name%') or checkpoint_code LIKE('%::name%') )";
            $res = $db->query($sql, array('::name' => $name, '::company_id' => BaseConfig::$company_id));
            if (!$res || $db->resultNumRows($res) < 1)
            {
                return FALSE;
            }
            $ret = array();
            while ($row = $db->fetchObject($res))
            {
                $ret[] = array("id" => $row->outlid, "name" => $row->name, "chkid" => $row->chkid);
            }
            return $ret;
        }

        public static function getUserNames($uid)
        {
            $db = Rapidkart::getInstance()->getDB();
            $sql = "SELECT uid, name FROM " . SystemTables::DB_TBL_USER . " WHERE uid = '::uid' AND company_id = " . BaseConfig::$company_id . " ";
            $res = $db->query($sql, array('::uid' => $uid));
            if (!$res || $db->resultNumRows($res) < 1)
            {
                return FALSE;
            }
            $row = $db->fetchObject($res);
            return $row->name;
        }

        public static function searchOutletsSalesInvoiceReport($name)
        {
            $db = Rapidkart::getInstance()->getDB();

            $uid = Session::loggedInUid();
            $user = new AdminUser($uid);
            if ($user->getIsAdmin() == 1)
            {
                $sql = "SELECT * FROM " . SystemTables::DB_TBL_OUTLET . " WHERE outlsid = '1' and company_id = '::company_id' and (name LIKE('%::name%') or checkpoint_code LIKE('%::name%') )";
                $res = $db->query($sql, array('::name' => $name, '::company_id' => BaseConfig::$company_id));
                if (!$res || $db->resultNumRows($res) < 1)
                {
                    return FALSE;
                }
                $ret = array();
                while ($row = $db->fetchObject($res))
                {
                    $ret[] = array("id" => $row->outlid, "name" => $row->name, "chkid" => $row->chkid);
                }
                return $ret;
            }
            else
            {
                $sql = "SELECT t1.* FROM " . SystemTables::DB_TBL_OUTLET . " AS t1 LEFT JOIN outlet_user_mapping t2 ON t1.outlid = t2.outlid WHERE t1.outlsid = '1' and t1.company_id = '::company_id' and t2.uid = '::loggedinuserid' and (t1.name LIKE('%::name%') or t1.checkpoint_code LIKE('%::name%'))";

                $res = $db->query($sql, array('::name' => $name, '::company_id' => BaseConfig::$company_id, '::loggedinuserid' => Session::loggedInUid()));
                if (!$res || $db->resultNumRows($res) < 1)
                {
                    return FALSE;
                }
                $ret = array();
                while ($row = $db->fetchObject($res))
                {
                    $ret[] = array("id" => $row->outlid, "name" => $row->name, "chkid" => $row->chkid);
                }
                return $ret;
            }
        }

        public static function insertOutletTaxMapping($mappings, $tax_id, $delete = FALSE)
        {
            $db = Rapidkart::getInstance()->getDB();
            if ($delete)
            {
                $sql = " DELETE FROM " . SystemTables::DB_TBL_OUTLET_BUSINESS_TAX_PROFILE_MAPPING . " WHERE butapid = '::id' and company_id = '::company'";
                $args = array('::id' => $tax_id, '::company' => BaseConfig::$company_id);
                $res = $db->query($sql, $args);
                if (!$res)
                {
                    return FALSE;
                }
            }
            if (is_array($mappings) && !empty($mappings))
            {
                $str = array();
                foreach ($mappings as $mapping)
                {
                    $outlet = new Outlet($mapping);
                    $str[] = "('" . $outlet->getId() . "','" . $outlet->getChkid() . "','" . $tax_id . "','" . BaseConfig::$company_id . "')";
                }
                if (!empty($str))
                {
                    $sql1 = "INSERT INTO " . SystemTables::DB_TBL_OUTLET_BUSINESS_TAX_PROFILE_MAPPING . " (outlid,chkid , butapid, company_id) VALUES " . implode(",", $str);
                    $res1 = $db->query($sql1);
                    if (!$res1)
                    {
                        return FALSE;
                    }
                }
            }
            return TRUE;
        }

        public static function getOutletTaxMapping($tax_id)
        {
            $db = Rapidkart::getInstance()->getDB();
            $sql = "SELECT * FROM " . SystemTables::DB_TBL_OUTLET_BUSINESS_TAX_PROFILE_MAPPING . " b  LEFT JOIN  " . SystemTables::DB_TBL_OUTLET . " o ON (o.outlid = b.outlid AND o.company_id = b.company_id)   WHERE  b.butapid = '::id' AND  o.company_id = '::company_id'";
            $res = $db->query($sql, array('::id' => $tax_id, '::company_id' => BaseConfig::$company_id));
            if (!$res || $db->resultNumRows($res) < 1)
            {
                return FALSE;
            }
            $ret = array();
            while ($row = $db->fetchObject($res))
            {
                $ret[] = array("id" => $row->outlid, "name" => $row->name, "chkid" => $row->chkid);
            }
            return $ret;
        }

        public static function getOutletsFromTax($gbutapid)
        {
            $db = Rapidkart::getInstance()->getDB();
            $sql = "SELECT * FROM " . SystemTables::DB_TBL_OUTLET . " WHERE gbutapid = '::gbutapid' AND company_id = '::company'";
            $res = $db->query($sql, array('::gbutapid' => $gbutapid, '::company' => BaseConfig::$company_id));
            if (!$res || $db->resultNumRows($res) < 1)
            {
                return FALSE;
            }
            $ret = array();
            while ($row = $db->fetchObject($res))
            {
                $ret[] = array("id" => $row->outlid, "name" => $row->name, "chkid" => $row->chkid);
            }
            return $ret;
        }

        //invoice service
        public static function getUserOutlet($uid)
        {
            $db = Rapidkart::getInstance()->getDB();
            $sql = "SELECT outlid FROM " . SystemTables::DB_TBL_OUTLET_USER_MAPPING . " WHERE uid = ::uid AND company_id = '::company_id' and outlid IN(SELECT outlid from " . SystemTables::DB_TBL_OUTLET . " WHERE outlsid = 1) LIMIT 1";
            $res = $db->query($sql, array('::uid' => $uid, '::company_id' => BaseConfig::$company_id));
            if (!$res || $db->resultNumRows($res) < 1)
            {
                return FALSE;
            }


            $row = $db->fetchObject($res);
            $array = array();
            $array["Id"] = ($row->outlid);
            $outlet = new Outlet($row->outlid);
            $count = InvoiceManager::getServiceOutletInvoices($outlet->getChkid(), '2021-04-01');
            $card_count = CardManager::getServiceOutletCards($outlet->getChkid(), '2021-04-01');
//            $cash_count = InvoiceManager::getServiceOutletCashInvoices($outlet->getChkid(), '2021-04-01');
            $total_invoices = 0;
            if ($count > 0)
            {
                $total_invoices = intval($count);
            }
            $total_cards = 0;
            if ($card_count > 0)
            {
                $total_cards = intval($card_count);
            }


            $array['chkid'] = $outlet->getChkid();
            $array['code'] = $outlet->getCheckpointCode();
            $array['name'] = $outlet->getName();
            $array['total_invoice'] = $total_invoices;
            $array['total_card'] = $total_cards;
//            $array['cash_count'] = $cash_count;
            $ret[] = $array;
            return $array;
        }

        public static function insertOutletHeaderFiles($id, $files, $start, $end)
        {
            if (empty($files))
            {
                return FALSE;
            }
            $str = array();
            foreach ($files as $file)
            {
                if (isset($file['response']['images'][0]))
                {
                    $f = $file['response']['images'][0];
                    $str[] = "('" . $id . "','" . $start . "','" . $end . "','" . $f['filepath'] . "','" . $f['name'] . "','" . $f['type'] . "'," . BaseConfig::$company_id . ")";
                }
            }
            if (!empty($str))
            {
                $db = Rapidkart::getInstance()->getDB();
                $sql = "INSERT INTO  " . SystemTables::DB_TBL_OUTLET_HEADER_IMAGES . " (outlid , start_date , end_date,  file , name , format,company_id) VALUES " . implode(",", $str);
                $res = $db->query($sql);
                if (!$res)
                {
                    return FALSE;
                }
            }
            return TRUE;
        }

        public static function insertOutletFooterFiles($id, $files, $start, $end)
        {
            if (empty($files))
            {
                return FALSE;
            }
            $str = array();
            foreach ($files as $file)
            {
                if (isset($file['response']['images'][0]))
                {
                    $f = $file['response']['images'][0];
                    $str[] = "('" . $id . "','" . $start . "','" . $end . "','" . $f['filepath'] . "','" . $f['name'] . "','" . $f['type'] . "'," . BaseConfig::$company_id . ")";
                }
            }
            if (!empty($str))
            {
                $db = Rapidkart::getInstance()->getDB();
                $sql = "INSERT INTO  " . SystemTables::DB_TBL_OUTLET_FOOTER_IMAGES . " (outlid , start_date , end_date,  file , name , format,company_id) VALUES " . implode(",", $str);
                $res = $db->query($sql);
                if (!$res)
                {
                    return FALSE;
                }
            }
            return TRUE;
        }

        public static function insertOutletStoreFiles($id, $files)
        {
            if (empty($files)) {
                return FALSE;
            }
            
            $str = array();
            $addedNames = array();

            foreach ($files as $file) {
                if (isset($file['response']['images']) && is_array($file['response']['images'])) {
                    foreach ($file['response']['images'] as $img) {
                        if (!in_array($img['name'], $addedNames)) {
                            $str[] = "('" . $id . "','" . $img['filepath'] . "','" . $img['name'] . "','" . $img['type'] . "'," .
                                     BaseConfig::$company_id . ")";
                            $addedNames[] = $img['name'];
                        }
                    }
                }
            }

            if (!empty($str)) {
                $db = Rapidkart::getInstance()->getDB();
                $sql = "INSERT INTO " . SystemTables::DB_TBL_OUTLET_STORE_IMAGES .
                       " (outlid, file, name, format, company_id) VALUES " . implode(",", $str);
                $res = $db->query($sql);

                if (!$res) {
                    return false;
                }
            }

            return TRUE;
        }

        public static function insertOutletLogoFiles($id, $files, $start, $end)
        {
            if (empty($files))
            {
                return FALSE;
            }
            $str = array();
            foreach ($files as $file)
            {
                if (isset($file['response']['images'][0]))
                {
                    $f = $file['response']['images'][0];
                    $str[] = "('" . $id . "','" . $start . "','" . $end . "','" . $f['filepath'] . "','" . $f['name'] . "','" . $f['type'] . "'," . BaseConfig::$company_id . ")";
                }
            }
            if (!empty($str))
            {
                $db = Rapidkart::getInstance()->getDB();
                $sql = "INSERT INTO  " . SystemTables::DB_TBL_OUTLET_LOGO_IMAGES . " (outlid , start_date , end_date,  file , name , format,company_id) VALUES " . implode(",", $str);
                $res = $db->query($sql);
                if (!$res)
                {
                    return FALSE;
                }
            }
            return TRUE;
        }

        public static function getHeaderFiles($id, $date = "")
        {
            $db = Rapidkart::getInstance()->getDB();
            $sql = " SELECT * FROM  " . SystemTables::DB_TBL_OUTLET_HEADER_IMAGES . " WHERE outlid = '::id'   AND company_id ='::company_id' ";
            $args = array('::id' => $id, '::company_id' => BaseConfig::$company_id);
            if ($date)
            {
                $sql .= " AND DATE(start_date) <= '::date' AND DATE(end_date) >='::date' ";
                $sql .= " LIMIT 1 ";
                $args['::date'] = $date;
            }

            $res = $db->query($sql, $args);
            if (!$res || $db->resultNumRows($res) < 1)
            {
                return FALSE;
            }
            $ret = array();
            while ($row = $db->fetchObject($res))
            {
                $ret[$row->outhiid] = $row;
            }
            return $ret;
        }

        public static function getFooterFiles($id, $date = "")
        {
            $db = Rapidkart::getInstance()->getDB();
            $sql = " SELECT * FROM  " . SystemTables::DB_TBL_OUTLET_FOOTER_IMAGES . " WHERE outlid = '::id'   AND company_id ='::company_id' ";
            $args = array('::id' => $id, '::company_id' => BaseConfig::$company_id);
            if ($date)
            {
                $sql .= " AND DATE(start_date) <= '::date' AND DATE(end_date) >='::date' ";
                $sql .= " LIMIT 1 ";
                $args['::date'] = $date;
            }

            $res = $db->query($sql, $args);
            if (!$res || $db->resultNumRows($res) < 1)
            {
                return FALSE;
            }
            $ret = array();
            while ($row = $db->fetchObject($res))
            {
                $ret[$row->outfiid] = $row;
            }
            return $ret;
        }

        public static function getOutletStoreFiles($id)
        {
            $db = Rapidkart::getInstance()->getDB();
            $sql = " SELECT * FROM  " . SystemTables::DB_TBL_OUTLET_STORE_IMAGES . " WHERE outlid = '::id' AND company_id ='::company_id' ";
            $args = array('::id' => $id, '::company_id' => BaseConfig::$company_id);

            $res = $db->query($sql, $args);
            if (!$res || $db->resultNumRows($res) < 1)
            {
                return FALSE;
            }
            $ret = array();
            while ($row = $db->fetchObject($res))
            {
                $ret[$row->outsiid] = $row;
            }
            return $ret;
        }

        public static function deleteOutletStoreFiles($id)
        {
            $db = Rapidkart::getInstance()->getDB();
            $sql = " DELETE FROM  " . SystemTables::DB_TBL_OUTLET_STORE_IMAGES . " WHERE outsiid = '::id'";
            $res = $db->query($sql, array('::id' => $id));
            return $res ? TRUE : FALSE;
        }

        public static function deleteFooterFiles($id)
        {
            $db = Rapidkart::getInstance()->getDB();
            $sql = " DELETE FROM  " . SystemTables::DB_TBL_OUTLET_FOOTER_IMAGES . " WHERE outfiid = '::id'";
            $res = $db->query($sql, array('::id' => $id));
            return $res ? TRUE : FALSE;
        }

        public static function deleteHeaderFiles($id)
        {
            $db = Rapidkart::getInstance()->getDB();
            $sql = " DELETE FROM  " . SystemTables::DB_TBL_OUTLET_HEADER_IMAGES . " WHERE outhiid = '::id'";
            $res = $db->query($sql, array('::id' => $id));
            return $res ? TRUE : FALSE;
        }

        public static function getLogoFiles($id = NULL, $date = "")
        {
            $db = Rapidkart::getInstance()->getDB();
            $sql = " SELECT * FROM  " . SystemTables::DB_TBL_OUTLET_LOGO_IMAGES . " WHERE company_id ='::company_id' ";
            if($id )
            {
                $sql.= " AND outlid = '::id' ";
            }
            $args = array('::id' => $id, '::company_id' => BaseConfig::$company_id);
            if ($date)
            {
                $sql .= " AND DATE(start_date) <= '::date' AND DATE(end_date) >='::date' ";
                $args['::date'] = $date;
                $sql .= " LIMIT 1 ";
            }
            $res = $db->query($sql, $args);
            if (!$res || $db->resultNumRows($res) < 1)
            {
                return FALSE;
            }
            $ret = array();
            while ($row = $db->fetchObject($res))
            {
                $ret[$row->outloid] = $row;
            }
            return $ret;
        }

        public static function deleteLogoFiles($id)
        {
            $db = Rapidkart::getInstance()->getDB();
            $sql = " DELETE FROM  " . SystemTables::DB_TBL_OUTLET_LOGO_IMAGES . " WHERE outloid = '::id'";
            $res = $db->query($sql, array('::id' => $id));
            return $res ? TRUE : FALSE;
        }

        public static function getUsersMultipleOutlet($uid)
        {
            if ($uid <= 0 || !AdminUser::isExistent($uid))
            {
                return FALSE;
            }

            $db = Rapidkart::getInstance()->getDB();
            $sql = "SELECT * FROM " . SystemTables::DB_TBL_OUTLET_USER_MAPPING . " WHERE uid = '::uid' and company_id = '::company'";
            $args = array("::uid" => $uid, '::company' => BaseConfig::$company_id);
            $res = $db->query($sql, $args);
            if (!$res || $db->resultNumRows($res) < 1)
            {
                return FALSE;
            }
            $ret = array();
            while ($row = $db->fetchObject($res))
            {
                $ret[] = $row->outlid;
            }

            return $ret;
        }

        public static function getOutletName($chkid)
        {

            if (empty($chkid))
            {
                return FALSE;
            }
            $db = Rapidkart::getInstance()->getDB();
            $sql = "SELECT name FROM " . BaseConfig::DB_NAME . "." . SystemTables::DB_TBL_OUTLET . " WHERE company_id = '::company' and chkid IN (::chkid)";
            $args = array('::chkid' => $chkid, '::company' => BaseConfig::$company_id);
            $outletname = '';
            $res = $db->query($sql, $args);
            while ($row = $db->fetchObject($res))
            {
                $outletname = $row->name;
            }

            return $outletname;
        }

        public static function getOutletNameData($chkid)
        {

            if (empty($chkid))
            {
                return FALSE;
            }
            $outlet = new Outlet($chkid);

            return $outlet->getName();
        }

        public static function getQrCode($chkid)
        {
            if (empty($chkid))
            {
                return FALSE;
            }
            $db = Rapidkart::getInstance()->getDB();
            $sql = "SELECT qr_code FROM " . BaseConfig::DB_NAME . "." . SystemTables::DB_TBL_OUTLET . " WHERE company_id = '::company' and chkid IN (::chkid)";
            $args = array('::chkid' => $chkid, '::company' => BaseConfig::$company_id);
            $qr_code = '';
            $res = $db->query($sql, $args);
            if (!$res || $db->resultNumRows($res) < 1)
            {
                return false;
            }
            $row = $db->fetchObject($res);
            $qr_code = $row->qr_code;
            return $qr_code;
        }

        public static function getOutletIdByChkid($chkid)
        {
            if (empty($chkid))
            {
                return FALSE;
            }
            $db = Rapidkart::getInstance()->getDB();
            $sql = "SELECT outlid FROM " . BaseConfig::DB_NAME . "." . SystemTables::DB_TBL_OUTLET . " WHERE company_id = '::company' and chkid IN (::chkid)";
            $args = array('::chkid' => $chkid, '::company' => BaseConfig::$company_id);
            $res = $db->query($sql, $args);
            if (!$res || $db->resultNumRows($res) < 1)
            {
                return FALSE;
            }

            $row = $db->fetchObject($res);
            return $row->outlid;
        }

        public static function getOutletId($chkid)
        {
            if (empty($chkid))
            {
                return FALSE;
            }
            $db = Rapidkart::getInstance()->getDB();
            $sql = "SELECT outlid FROM " . BaseConfig::DB_NAME . "." . SystemTables::DB_TBL_OUTLET . " WHERE company_id = '::company' and chkid IN (::chkid)";
            $args = array('::chkid' => implode(',', $chkid), '::company' => BaseConfig::$company_id);

            $outlid = '';
            $res = $db->query($sql, $args);
//                 echo $db->getLastQuery();
            if (!$res || $db->resultNumRows($res) < 1)
            {
                return FALSE;
            }

            foreach ($res as $value)
            {
                $outlid .= "" . $value['outlid'] . ",";
            }

            $outlid = rtrim($outlid, ",");

            $sql_wachkid = "SELECT waid,chkid FROM " . BaseConfig::DB_NAME . "." . SystemTables::DB_TBL_WAREHOUSE . " WHERE outlid IN (::outlid)";
            $arg = array('::outlid' => $outlid);

            $result = $db->query($sql_wachkid, $arg);
            if (!$result || $db->resultNumRows($res) < 1)
            {
                return FALSE;
            }

            $ret = array();
            while ($row = $db->fetchObject($result))
            {
                $ret[] = $row->chkid;
            }

            return $ret;
        }

        // service function
        public static function getOutletTaxByOutlid($outlid)
        {
            $db = Rapidkart::getInstance()->getDB();
            $sql = "SELECT butapid FROM " . SystemTables::DB_TBL_OUTLET_BUSINESS_TAX_PROFILE_MAPPING . " WHERE outlid='::outlid' AND company_id='::compid' AND butapid != 'NULL'";
            $arg = array('::outlid' => $outlid, '::compid' => BaseConfig::$company_id);
            $res = $db->query($sql, $arg);
            if (!$res || $db->resultNumRows() < 1)
            {
                return FALSE;
            }
            $ret = array();
            while ($row = $db->fetchObject($res))
            {
                $ret[] = $row->butapid;
            }
            return $ret;
        }

        public static function updateOutletPaymentDetails($outlid, $payments = array())
        {
            if (!isset($outlid) || !Outlet::isExistent($outlid))
            {
                return false;
            }
            $paymentStr = "";
            foreach ($payments as $key => $payment)
            {

                $payment_type = $payment['payment_type'];
                $ledger = $payment['account-credit'];
                if (empty($payment_type) || empty($ledger))
                {
                    return false;
                }
                $paymentStr .= '(' . $outlid . ',' . $payment_type . ',' . $ledger . ',' . BaseConfig::$company_id . '),';
            }
            $paymentStr = rtrim($paymentStr, ',');

            self::flushOutletPaymentDetails($outlid); // Flush existing users.

            $db = Rapidkart::getInstance()->getDB();
            $sql = " INSERT INTO " . SystemTables::DB_TBL_OUTLET_PAYMENT_MAPPING . " (outlid, cpoid, alid, company_id) VALUES $paymentStr";
            $res = $db->query($sql);
            if (!$res)
            {
                return false;
            }
            return true;
        }

        public static function flushOutletPaymentDetails($outlid)
        {
            if (!Outlet::isExistent($outlid))
            {
                return false;
            }
            $db = Rapidkart::getInstance()->getDB();
            $sql = "DELETE FROM " . SystemTables::DB_TBL_OUTLET_PAYMENT_MAPPING . " WHERE outlid = '::outlid' and company_id = '::company'";
            $res = $db->query($sql, array('::outlid' => $outlid, '::company' => BaseConfig::$company_id));
            if (!$res)
            {
                return false;
            }
            return true;
        }

        public static function getOutletPaymentDetail($outlid, $cpoid = NULL)
        {
            if (!Outlet::isExistent($outlid))
            {
                return false;
            }
            $db = Rapidkart::getInstance()->getDB();
            $sql = " SELECT al.alid, al.name, cpo.cpoid, cpo.name as cpo_name FROM " . SystemTables::DB_TBL_OUTLET_PAYMENT_MAPPING . " o_payment"
                    . " INNER JOIN " . SystemTables::DB_TBL_ACCOUNT_LEDGER . " al ON al.alid = o_payment.alid  "
                    . " INNER JOIN " . SystemTables::DB_TBL_CUSTOMER_PAYMENT_OPTION . " cpo ON cpo.cpoid = o_payment.cpoid "
                    . " WHERE o_payment.outlid = '::outlid' AND o_payment.company_id = '::company_id'";
            $args = array('::outlid' => $outlid, '::company_id' => BaseConfig::$company_id);
            if ($cpoid)
            {
                $sql .= " AND o_payment.cpoid = '::cpoid'";
                $args['::cpoid'] = $cpoid;
            }
            $res = $db->query($sql, $args);
            if (!$res || $db->resultNumRows($res) < 1)
            {
                return FALSE;
            }
            $payments = array();
            while ($row = $db->fetchObject($res))
            {
                $payments[] = array("alid" => $row->alid, "ledger_name" => $row->name, "cpoid" => $row->cpoid, "payment_name" => $row->cpo_name);
            }
            return $payments;
        }

        public static function getOutletAddress($chkid = NULL)
        {
            $db = Rapidkart::getInstance()->getDB();
            $sql = " SELECT address_line_1,address_line_2,city,zip_code,contact FROM outlet WHERE chkid = '::chkid' AND company_id = '::company_id'";
            $args = array('::chkid' => $chkid, '::company_id' => BaseConfig::$company_id);
            $res = $db->query($sql, $args);
            if (!$res || $db->resultNumRows($res) < 1)
            {
                return FALSE;
            }
            $result = array();
            while ($row = $db->fetchObject($res))
            {
                $result[] = array("address_line_1" => $row->address_line_1, "address_line_2" => $row->address_line_2, "city" => $row->city, "zip_code" => $row->zip_code, "contact" => $row->contact);
            }
            return $result;
        }

        public static function updateStateofCheckPoint($chkid, $stid)
        {
            $db = Rapidkart::getInstance()->getDB();
            $sql = "UPDATE " . SystemTables::DB_TBL_CHECKPOINT_MAPPING . " SET stid = '::stid' WHERE chkid = '::chkid' and company_id = '::company' ";
            $args = array('::stid' => $stid, '::chkid' => $chkid, '::company' => BaseConfig::$company_id);
            $res = $db->query($sql, $args);
            return $res ? TRUE : FALSE;
        }

        public static function getOutletExtraChargesMapping($ecid)
        {
            $db = Rapidkart::getInstance()->getDB();
            $sql = "SELECT * FROM " . SystemTables::DB_TBL_OUTLET_EXTRA_CHARGES_MAPPING . " b  LEFT JOIN  " . SystemTables::DB_TBL_OUTLET . " o ON (o.outlid = b.outlid AND o.company_id = b.company_id)   WHERE  b.ecid = '::id' AND  o.company_id = '::company_id'";
            $res = $db->query($sql, array('::id' => $ecid, '::company_id' => BaseConfig::$company_id));
            if (!$res || $db->resultNumRows($res) < 1)
            {
                return FALSE;
            }
            $ret = array();
            while ($row = $db->fetchObject($res))
            {
                $ret[] = array("id" => $row->outlid, "name" => $row->name, "chkid" => $row->chkid);
            }
            return $ret;
        }

        public static function insertOutletChargesMapping($mappings, $ecid, $delete = FALSE)
        {
            $db = Rapidkart::getInstance()->getDB();
            if ($delete)
            {
                $sql = " DELETE FROM " . SystemTables::DB_TBL_OUTLET_EXTRA_CHARGES_MAPPING . " WHERE ecid = '::id' and company_id = '::company'";
                $args = array('::id' => $ecid, '::company' => BaseConfig::$company_id);
                $res = $db->query($sql, $args);
                if (!$res)
                {
                    return FALSE;
                }
            }
            if (is_array($mappings) && !empty($mappings))
            {
                $str = array();
                foreach ($mappings as $mapping)
                {
                    $outlet = new Outlet($mapping);
                    $str[] = "('" . $outlet->getId() . "','" . $outlet->getChkid() . "','" . $ecid . "','" . BaseConfig::$company_id . "')";
                }
                if (!empty($str))
                {
                    $sql1 = "INSERT INTO " . SystemTables::DB_TBL_OUTLET_EXTRA_CHARGES_MAPPING . " (outlid,chkid , ecid, company_id) VALUES " . implode(",", $str);
                    $res1 = $db->query($sql1);
                    if (!$res1)
                    {
                        return FALSE;
                    }
                }
            }
            return TRUE;
        }

        public static function getSubCodes($outlid, $type = 0)
        {
            $db = Rapidkart::getInstance()->getDB();
            $sql = "SELECT * FROM outlet_code WHERE outlid = '::outlid' AND outcosid = 1";
            $args = array('::outlid' => $outlid);
            if ($type > 0)
            {
                $sql .= " AND avtid = '::avtid'";
                $args['::avtid'] = $type;
            }
            $res = $db->query($sql, $args);
            if (!$res || $db->resultNumRows($res) < 1)
            {
                return FALSE;
            }
            $ret = array();
            while ($row = $db->fetchObject($res))
            {
                $ret[$row->outcoid] = $row;
            }
            return $ret;
        }

        public static function getSubCodeValue($outcoid)
        {
            $db = Rapidkart::getInstance()->getDB();
            $res = $db->getFieldValue("outlet_code", "code", " outcoid=" . $outcoid);
            if (!$res)
            {
                return "";
            }
            else
            {
                return $res;
            }
        }

        public static function getChkidCondition(&$query, $column_name)
        {
            $chkid_array = self::getUserCheckPoint(Session::loggedInUid());
            $chkid = array();
            if ($chkid_array)
            {
                foreach ($chkid_array as $chkid_arr)
                {
                    $chkid[] = $chkid_arr['chkid'];
                }
            }

            if (!empty($chkid))
            {
                $query .= " AND  " . $column_name . " IN (" . implode(",", $chkid) . ")";
            }
        }

        public static function getOutletNames($chkid)
        {
            $str = '';
            if (is_array($chkid) && !empty($chkid) && array_filter($chkid))
            {
                $chkid = array_filter($chkid);
                $db = Rapidkart::getInstance()->getDB();
                $sql = "SELECT GROUP_CONCAT(name) as name FROM outlet WHERE chkid IN(::chid)";
                $res = $db->query($sql, array('::chid' => implode(",", $chkid)));
                if ($res && $db->resultNumRows($res) > 0)
                {
                    $row = $db->fetchObject($res);
                    $str = " (" . $row->name . ")";
                }
            }
            return $str;
        }

        public static function updateDefaultOutlet($id)
        {
            $db = Rapidkart::getInstance()->getDB();
            $sql = " UPDATE " . SystemTables::DB_TBL_OUTLET . " SET is_default = 0 WHERE company_id = '::company' ";
            $args = array('::company' => BaseConfig::$company_id);
            $res = $db->query($sql, $args);
            if (!$res)
            {
                return FALSE;
            }
            $sql1 = " UPDATE " . SystemTables::DB_TBL_OUTLET . " SET is_default = 1 WHERE company_id = '::company' and outlid = '::outlid' ";
            $args['::outlid'] = $id;
            $res1 = $db->query($sql1, $args);
            return $res1 ? TRUE : FALSE;
        }

        public static function updateSecondaryOutlet($id)
        {
            $db = Rapidkart::getInstance()->getDB();
            $sql = " UPDATE " . SystemTables::DB_TBL_OUTLET . " SET is_secondary = 0 WHERE company_id = '::company' ";
            $args = array('::company' => BaseConfig::$company_id);
            $res = $db->query($sql, $args);
            if (!$res)
            {
                return FALSE;
            }
            $sql1 = " UPDATE " . SystemTables::DB_TBL_OUTLET . " SET is_secondary = 1 WHERE company_id = '::company' and outlid = '::outlid' ";
            $args['::outlid'] = $id;
            $res1 = $db->query($sql1, $args);
            return $res1 ? TRUE : FALSE;
        }

        public static function getOutletByGbutacomgsdid($id)
        {
            $db = Rapidkart::getInstance()->getDB();
            $sql = "SELECT * FROM " . SystemTables::DB_TBL_OUTLET . " WHERE gbutacomgsdid= '::id' ";
            $res = $db->query($sql, array('::id' => $id));
            if (!$res || $db->resultNumRows($res) < 1)
            {
                return false;
            }
            $row = $db->fetchObject($res);
            return $row;
        }

        public static function getOutletSubCode($type = 0)
        {
            $db = Rapidkart::getInstance()->getDB();
            $sql = "SELECT * FROM outlet_code WHERE 1";
            $args = array();
            if ($type > 0 && is_numeric($type))
            {
                $sql .= " AND avtid = '::avtid'";
                $args['::avtid'] = $type;
            }
            if (is_array($type) && !empty($type))
            {
                $sql .= " AND avtid IN(::avtid)";
                $args['::avtid'] = implode(",", $type);
            }
            $res = $db->query($sql, $args);
            if (!$res || $db->resultNumRows($res) < 1)
            {
                return FALSE;
            }
            $ret = array();
            while ($row = $db->fetchObject($res))
            {
                $ret[$row->outcoid] = $row;
            }
            return $ret;
        }

        public static function getOutletPaymentMappingByAlid($alid)
        {
            $db = Rapidkart::getInstance()->getDB();
            $sql = "SELECT * FROM " . SystemTables::DB_TBL_OUTLET_PAYMENT_MAPPING . " WHERE alid = '::alid'  AND cpoid  = 5";
            $args = array('::alid' => $alid);
            $res = $db->query($sql, $args);
            if (!$res || $db->resultNumRows($res) < 1)
            {
                return false;
            }
            $ret = array();
            while ($row = $db->fetchObject($res))
            {
                $ret[$row->outlid] = $row;
            }
            return $ret;
        }

        public static function getReceiptOutletMappingExisting($outlid, $cpoid, $alid)
        {
            $db = Rapidkart::getInstance()->getDB();
            if ($cpoid != 5)
            {
                return true;
            }

            $sql = "SELECT * FROM " . SystemTables::DB_TBL_OUTLET_PAYMENT_MAPPING . " WHERE outlid = '::outlid' AND cpoid = '::cpoid' ";
            $res = $db->query($sql, array('::outlid' => $outlid, '::cpoid' => $cpoid));
            if ($res && $db->resultNumRows($res) > 0)
            {
                $ret = array();
                while ($row = $db->fetchObject($res))
                {
                    $ret[$row->alid] = $row;
                }
                if (!isset($ret[$alid]))
                {
                    return false;
                }
            }
            return true;
        }

        public static function getMsmeType($id)
        {
            switch ($id)
            {
                case 1:
                    return '<label>Micro</label>';
                    break;
                case 2:
                    return '<label>Medium</label>';
                    break;
                case 3:
                    return '<label>Small</label>';
                    break;
                case 4:
                    return '<label>Not Registered</label>';
                    break;
                case 5:
                    return '<label>Large</label>';
                    break;
                default:
                    return '<label>NA</label>';
                    break;
            }
        }

        public static function getSecondaryOutlet()
        {
            $db = Rapidkart::getInstance()->getDB();
            $sql = "SELECT chkid FROM outlet WHERE outlsid = 1 AND is_secondary > 0 AND company_id = '::company'";
            $res = $db->query($sql, array('::company' => BaseConfig::$company_id));
            if (!$res || $db->resultNumRows($res) < 1)
            {
                self::$secondary_outlet_chkid = 0;
            }
            else
            {
                $row = $db->fetchObject($res);
                self::$secondary_outlet_chkid = $row->chkid;
            }
        }

        public static function getCustomerCheckPoint($cuid, $group_by_chkid = NULL, $group_by_outlid = NULL)
        {
            $db = Rapidkart::getInstance()->getDB();
            $sql = " SELECT outlet.outlid as id , outlet.name, outlet.chkid , outlet.sale_alid , outlet.purchase_alid , outlet.expense_alid , outlet.stid , pos_cuid , branch_sale_alid , gbutapid , gbutacomgsdid , disable_tcs , outlet.is_secondary , outlet.is_default FROM customer_outlet_mapping co_map INNER JOIN " . SystemTables::DB_TBL_CUSTOMER . " cus ON cus.cuid = co_map.cuid AND cus.cuid = ::cuid INNER JOIN " . SystemTables::DB_TBL_OUTLET . " outlet ON co_map.chkid = outlet.chkid INNER JOIN " . SystemTables::DB_TBL_OUTLET_USER_MAPPING . " o_user ON outlet.outlid = o_user.outlid WHERE ((outlet.outlsid = 1)) AND outlet.company_id IN(::company_id) AND uid = '::uid' ORDER BY is_default DESC";
            $args = array('::cuid' => $cuid, '::company_id' => BaseConfig::$company_id, '::uid' => Session::loggedInUid());
            $res = $db->query($sql, $args);
            if (!$res || $db->resultNumRows($res) < 1)
            {
                return array();
            }
            $outlets = array();
            while ($row = $db->fetchObject($res))
            {
                if ($group_by_chkid)
                {
                    $outlets[$row->chkid] = array("id" => $row->id, "name" => $row->name, "chkid" => $row->chkid, "sale_alid" => $row->sale_alid, 'purchase_alid' => $row->purchase_alid, 'expense_alid' => $row->expense_alid, "stid" => $row->stid, 'pos_cuid' => $row->pos_cuid, 'branch_sale_alid' => $row->branch_sale_alid, 'gbutapid' => $row->gbutapid, 'gbutacomgsdid' => $row->gbutacomgsdid, 'disable_tcs' => $row->disable_tcs, 'is_secondary' => $row->is_secondary, 'is_default' => $row->is_default);
                }
                elseif ($group_by_outlid)
                {
                    $outlets[$row->id] = array("id" => $row->id, "name" => $row->name, "chkid" => $row->chkid, "sale_alid" => $row->sale_alid, 'purchase_alid' => $row->purchase_alid, 'expense_alid' => $row->expense_alid, "stid" => $row->stid, 'pos_cuid' => $row->pos_cuid, 'branch_sale_alid' => $row->branch_sale_alid, 'gbutapid' => $row->gbutapid, 'gbutacomgsdid' => $row->gbutacomgsdid, 'disable_tcs' => $row->disable_tcs, 'is_secondary' => $row->is_secondary, 'is_default' => $row->is_default);
                }
                else
                {
                    $outlets[] = array("id" => $row->id, "name" => $row->name, "chkid" => $row->chkid, "sale_alid" => $row->sale_alid, 'purchase_alid' => $row->purchase_alid, 'expense_alid' => $row->expense_alid, "stid" => $row->stid, 'pos_cuid' => $row->pos_cuid, 'branch_sale_alid' => $row->branch_sale_alid, 'gbutapid' => $row->gbutapid, 'gbutacomgsdid' => $row->gbutacomgsdid, 'disable_tcs' => $row->disable_tcs, 'is_secondary' => $row->is_secondary, 'is_default' => $row->is_default);
                }
            }
            return $outlets;
        }
        public static function loadPresentationImages($outlid)
        {
            $db = Rapidkart::getInstance()->getDB();
            $sql = " SELECT * FROM outlet_presentation_images WHERE outlid = '::outlid' AND company_id = '::company' ORDER BY created_ts DESC";
            $args = array("::outlid" => $outlid, '::company' => BaseConfig::$company_id);
            $res = $db->query($sql, $args);
            if (!$res || $db->resultNumRows() < 1)
            {
                return false;
            }
            $ret = array();
            while ($row = $db->fetchObject($res))
            {
                $ret[] = $row;
            }
            return $ret;
        }
    }
    

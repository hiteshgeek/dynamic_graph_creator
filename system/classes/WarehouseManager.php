<?php

    /*
     * To change this license header, choose License Headers in Project Properties.
     * To change this template file, choose Tools | Templates
     * and open the template in the editor.
     */

    /**
     * Description of WarehouseManager
     *
     * @author karthik
     * @Since 20150907
     */
    class WarehouseManager
    {

        public static $secondary_warehouse_chkid = 0;
        public static $secondary_warehouse_id = 0;

        /**
         * Get all the warehouses
         * 
         * @return \Warehouse|boolean
         */
        public static function getWarehouses($outlet_specific = FALSE, $status = NULL, $chkid = NULL, $type = NULL, $by_name = false, $waid = NULL, $outlid = NULL, $warehouse_user_specific = false, $return_as_token = false, $display_warehouse_consider = true)
        {
            $db = Rapidkart::getInstance()->getDB();

            $outlets_array = array();
            if ($outlet_specific && getSettings('IS_OUTLET_ENABLE') && !getSettings("IS_WAREHOUSE_LIST_NOT_OUTLET_BASIS"))
            {
                $outlets = OutletManager::getUserCheckPoint(Session::loggedInUid());
                if ($outlets)
                {
                    foreach ($outlets as $outlet)
                    {
                        $outlets_array[] = $outlet['id'];
                    }
                }
            }

            $sql = " SELECT * FROM " . SystemTables::DB_TBL_WAREHOUSE . " WHERE wasid = 1  AND company_id ='::company_id' ";
            if (!empty($outlets_array))
            {
                $sql .= " AND outlid IN (" . implode(",", $outlets_array) . ")";
            }

            $args = array('::company_id' => BaseConfig::$company_id);
            if ($warehouse_user_specific)
            {
                $sql .= " AND waid IN(SELECT waid FROM " . SystemTables::DB_TBL_WAREHOUSE_USER_MAPPING . " WHERE uid = " . Session::loggedInUid() . ")";
            }
            if ($type)
            {
                $sql .= " AND type = '::type'";
                $args['::type'] = $type;
            }
            if ($waid)
            {
                $sql .= " AND waid IN(::waid) ";
                $args['::waid'] = $waid;
            }
            if ($outlid)
            {
                $sql .= " AND outlid = '::outlet'";
                $args['::outlet'] = $outlid;
            }
            if (!$display_warehouse_consider)
            {
                $sql .= " AND type <> 4";
            }
            $sql .= " ORDER BY is_default DESC ";
            $res = $db->query($sql, $args);

            if (!$res || $db->resultNumRows($res) < 1)
            {
                return false;
            }
            $ret = array();

            while ($row = $db->fetchObject($res))
            {
                if ($chkid)
                {
                    $ret[$row->chkid] = new Warehouse($row->waid);
                    $ret[$row->chkid]->loadExtra();
                }
                else
                {
                    if ($by_name)
                    {
                        $ret[strtolower(trim($row->name))] = $row;
                    }
                    elseif ($return_as_token)
                    {
                        $ret[] = array("id" => $row->waid, "name" => $row->name, "chkid" => $row->chkid, 'type' => $row->type);
                    }
                    else
                    {
                        $ret[$row->waid] = new Warehouse($row->waid);
                        $ret[$row->waid]->loadExtra();
                    }
                }
            }

            return $ret;
        }

        public static function checkWarehouseTransaction($waid)
        {
            $db = Rapidkart::getInstance()->getDB();
            $sql = "SELECT 
    CASE 
        WHEN EXISTS (
            SELECT 1 
            FROM " . SystemTables::$inventory_set . " 
            WHERE waid = '::waid' AND issid IN (1, 4) AND company_id = '::comp'
        )
        OR EXISTS (
            SELECT 1 
            FROM purchase_order 
            WHERE waid = '::waid' AND purorsid IN (1, 2, 3, 6, 7, 8) AND company_id = '::comp'
        )
    THEN FALSE
    ELSE TRUE
END AS result";
            $args = array('::waid' => $waid, '::comp' => BaseConfig::$company_id);

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

        public static function getWarehouseByChkid($chkid)
        {
            $db = Rapidkart::getInstance()->getDB();
            if ($chkid <= 0)
            {
                return FALSE;
            }
            $args = array("::chkid" => $chkid, '::company' => BaseConfig::$company_id);

            $sql = " SELECT waid FROM " . SystemTables::DB_TBL_WAREHOUSE
                    . " WHERE wasid = '1' AND chkid = ::chkid AND company_id = '::company' ORDER BY is_default DESC ";
            $res = $db->query($sql, $args);
            if (!$res || $db->resultNumRows() < 1)
            {
                return false;
            }

            $warehouse = new Warehouse($db->fetchObject($res)->waid);
            $warehouse->loadExtra();
            return $warehouse;
        }

        public static function getWarehouseByMultipeChkid($chkid, $uid = NULL)
        {
            $db = Rapidkart::getInstance()->getDB();
            if ($chkid <= 0)
            {
                return FALSE;
            }
            $args = array("::chkid" => $chkid, '::company' => BaseConfig::$company_id);

            $user = new AdminUser($uid);
            if ($uid && $uid != NULL && $uid > 0)
            {
                $args['::uid'] = $uid;
                $user = new AdminUser($uid);
                if ($user->getIsAdmin() == 1)
                {
                    $sql = " SELECT * FROM " . SystemTables::DB_TBL_WAREHOUSE . " WHERE wasid = '1' AND chkid IN (::chkid) AND company_id = '::company' ORDER BY is_default DESC ";
                }
                else
                {
                    $sql = " SELECT w.* FROM " . SystemTables::DB_TBL_WAREHOUSE_USER_MAPPING . " wum LEFT JOIN " . SystemTables::DB_TBL_WAREHOUSE . " w ON(w.waid = wum.waid) WHERE wum.uid = '::uid' AND w.wasid = '1' AND w.chkid IN (::chkid) AND w.company_id = '::company' ORDER BY is_default DESC ";
                }
            }
            else
            {
                $sql = " SELECT * FROM " . SystemTables::DB_TBL_WAREHOUSE . " WHERE wasid = '1' AND chkid IN (::chkid) AND company_id = '::company' ORDER BY is_default DESC ";
            }
            $res = $db->query($sql, $args);
            if (!$res || $db->resultNumRows($res) < 1)
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

        public static function getAlloactedDataDetails($chkid, $isvid)
        {
            $db = Rapidkart::getInstance()->getDB();
            if ($chkid <= 0)
            {
                return FALSE;
            }
            $args = array("::chkid" => $chkid, "::isvid" => $isvid);

            $sql = " SELECT * FROM inventory_set_variation_order_details_view_" . BaseConfig::$company_id
                    . " WHERE chkid = ::chkid AND isvid = ::isvid AND (alloted_qty > total_exe) ";
            $res = $db->query($sql, $args);
            if (!$res || $db->resultNumRows($res) < 1)
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

        public static function getItemByVariationNumber($variation_number)
        {
            $db = Rapidkart::getInstance()->getDB();
            $args = array("::variation_number" => $variation_number, '::company' => BaseConfig::$company_id);

            $sql = " SELECT isvid, name FROM " . SystemTables::DB_TBL_INVENTORY_SET_VARIATIONS
                    . " WHERE isvsid = '1' AND variation_number = ::variation_number AND company_id = '::company'";
            $res = $db->query($sql, $args);
            if (!$res || $db->resultNumRows() < 1)
            {
                return false;
            }
            while ($row = $db->fetchObject($res))
            {
                return $row;
            }
        }

        public static function getItemDataForDispatchOrder($isvid)
        {
            $db = Rapidkart::getInstance()->getDB();

            $sql = " SELECT * FROM " . SystemTables::DB_TBL_ORDER_ITEM
                    . " WHERE isvid = '::isvid' AND company_id = '::company'";
            $args = array("::isvid" => $isvid, '::company' => BaseConfig::$company_id);
            $res = $db->query($sql, $args);
            if (!$res || $db->resultNumRows() < 1)
            {
                return false;
            }
            $row = $db->fetchObject($res);
            $orderitem = new OrderItem($row->oiid);
            return $orderitem;
        }

        public static function getWarehouseNameByChkid($chkid, $row = NULL)
        {
            $db = Rapidkart::getInstance()->getDB();
            if ($chkid <= 0)
            {
                return "";
            }
            $args = array("::chkid" => $chkid, '::company' => BaseConfig::$company_id);

            $sql = " SELECT waid FROM " . SystemTables::DB_TBL_WAREHOUSE
                    . " WHERE company_id  = '::company' and wasid = '1' AND chkid = ::chkid";
            $res = $db->query($sql, $args);
//            echo $db->getLastQuery();
            if (!$res || $db->resultNumRows() < 1)
            {
                if ($row)
                {
                    return "NA";
                }
                else
                {
                    return false;
                }
            }
            $row = $db->fetchObject($res);
            $warehouse = new Warehouse($row->waid);
            return $warehouse->getName();
        }

        public static function getWarehouseByOutlid($outlid)
        {
            $db = Rapidkart::getInstance()->getDB();
            if ($outlid <= 0)
            {
                return FALSE;
            }
            $args = array("::outlid" => $outlid, '::company' => BaseConfig::$company_id);

            $sql = " SELECT waid FROM " . SystemTables::DB_TBL_WAREHOUSE . " WHERE wasid = '1' AND outlid = '::outlid' AND company_id = '::company' LIMIT 1";
            $res = $db->query($sql, $args);
            if (!$res || $db->resultNumRows() < 1)
            {
                return false;
            }
            $warehouse = new Warehouse($db->fetchObject($res)->waid);
            $warehouse->loadExtra();
            return $warehouse;
        }

        public static function getWarehouseByOutletid($outlid, $waid = NULL, $user_specific = false, $singleWarehouse = 0)
        {
            $db = Rapidkart::getInstance()->getDB();
            if ($outlid <= 0)
            {
                return FALSE;
            }
            $args = array("::outlid" => $outlid, '::company' => BaseConfig::$company_id);

            $sql = " SELECT waid FROM " . SystemTables::DB_TBL_WAREHOUSE
                    . " WHERE wasid = '1' AND outlid = '::outlid' AND company_id = '::company' AND type <> 4 ";
            if ($waid)
            {
                $sql .= " AND waid IN('::waid')";
                $args['::waid'] = $waid;
            }
            if ($user_specific)
            {
                $sql .= " AND waid IN(SELECT waid FROM   " . SystemTables::DB_TBL_WAREHOUSE_USER_MAPPING . " WHERE uid=" . Session::loggedInUid() . ")";
            }
            $sql .= " ORDER BY is_default DESC";
            $res = $db->query($sql, $args);

            if (!$res || $db->resultNumRows() < 1)
            {
                return false;
            }
            $ret = array();
            if ($singleWarehouse > 0)
            {
                $row = $db->fetchObject($res);
                $warehouse = new Warehouse($row->waid);
                $warehouse->loadExtra();
                $ret = $warehouse;
            }
            else
            {
                while ($row = $db->fetchObject($res))
                {
                    $warehouse = new Warehouse($row->waid);
                    $warehouse->loadExtra();
                    $ret[] = $warehouse;
                }
            }

            return $ret;
        }

        public static function getWarehouseCount($status = null)
        {
            $db = Rapidkart::getInstance()->getDB();
            if ($status)
            {

                $sql = "SELECT count(*) AS count FROM " . SystemTables::DB_TBL_WAREHOUSE . " WHERE company_id = '::company' and wasid=$status";
            }
            else
            {
                $sql = "SELECT count(*) AS count FROM " . SystemTables::DB_TBL_WAREHOUSE . " WHERE company_id = '::company' and wasid!='3'";
            }
            $res = $db->query($sql, array('::company' => BaseConfig::$company_id));
            if (!$res || $db->resultNumRows($res) < 1)
            {
                return false;
            }
            $row = $db->fetchObject($res);
            return $row->count;
        }

        /**
         * Update the warehouse users.
         * @param type $waid - A valid Warehouse Id.
         * @param type $users - Array of the users.
         * @return boolean - Inserted or not.
         */
        public static function updateWarehouseUsers($waid, $users = array())
        {
            if (!isset($waid) || !warehouse::isExistent($waid) || empty($users))
            {
                return false;
            }
            $userStr = "";
            foreach ($users as $key => $user)
            {
//                if (!AdminUser::isExistent($user))
//                {
//                    return false;
//                }
                $userStr .= '(' . $waid . ',' . $user . ', ' . BaseConfig::$company_id . '),';
            }
            $userStr = rtrim($userStr, ',');

            self::flushWarehouseUsers($waid); // Flush existing users.

            $db = Rapidkart::getInstance()->getDB();
            $sql = " INSERT INTO " . SystemTables::DB_TBL_WAREHOUSE_USER_MAPPING . " (waid,uid, company_id) VALUES $userStr";
            $res = $db->query($sql);
            if (!$res)
            {
                return false;
            }
            return true;
        }

        /**
         * Flush all the Users from the Warehouse.
         * @param type $waid - A valid Warehouse Id.
         * @return boolean
         */
        public static function flushWarehouseUsers($waid)
        {
            if (!Warehouse::isExistent($waid))
            {
                return false;
            }
            $db = Rapidkart::getInstance()->getDB();
            $sql = "DELETE FROM " . SystemTables::DB_TBL_WAREHOUSE_USER_MAPPING . " WHERE waid = '::waid' ";
            $res = $db->query($sql, array('::waid' => $waid, '::compid' => BaseConfig::$company_id));
            if (!$res || $db->affectedRows() < 1)
            {
                return false;
            }
            return true;
        }

        /**
         * Get all the warehouse users.
         * @param type $waid
         * @return boolean
         */
        public static function getWarehouseUsers($waid)
        {
            if (!Warehouse::isExistent($waid))
            {
                return false;
            }
            $db = Rapidkart::getInstance()->getDB();
            $sql = " SELECT user.uid,user.name FROM " . SystemTables::DB_TBL_WAREHOUSE_USER_MAPPING . " w_user"
                    . " INNER JOIN " . SystemTables::DB_TBL_USER . " user ON (user.uid = w_user.uid) "
                    . " WHERE w_user.waid = '::waid' AND w_user.company_id = '::compid'";
            $res = $db->query($sql, array('::waid' => $waid, '::compid' => BaseConfig::$company_id));
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

        public static function getUserWarehouses($uid, $waid = null, $chkid = null, $return_obj = FALSE, $group_by_chkid = false)
        {
            if (!AdminUser::isExistent($uid))
            {
                return array();
            }
            $db = Rapidkart::getInstance()->getDB();

            $cond = "";
            if (isset($waid) && is_array($waid) && !empty($waid) && array_filter($waid))
            {
                $cond = " AND warehouse.waid IN(" . implode(",", $waid) . ") ";
            }
            elseif (isset($waid) && is_numeric($waid) && $waid > 0 && $waid != null && $waid != false)
            {
                $cond = " AND warehouse.waid = $waid ";
            }
            if (isset($chkid) && $chkid != null && $chkid != false)
            {
                $cond .= " AND warehouse.chkid = $chkid ";
            }

            $sql = " SELECT w_user.waid as id, warehouse.name, warehouse.chkid , warehouse.type  FROM " . SystemTables::DB_TBL_WAREHOUSE_USER_MAPPING . " w_user"
                    . " INNER JOIN " . SystemTables::DB_TBL_USER . " user ON user.uid = w_user.uid AND user.uid = ::uid    "
                    . " INNER JOIN " . SystemTables::DB_TBL_WAREHOUSE . " warehouse ON w_user.waid = warehouse.waid   AND warehouse.company_id ='::company_id' "
                    . " WHERE (warehouse.created_uid = ::uid  OR w_user.company_id ='::company_id') AND (warehouse.wasid = 1 ) $cond Order By is_default DESC ";
            $res = $db->query($sql, array('::uid' => $uid, '::company_id' => BaseConfig::$company_id));

            if (!$res || $db->resultNumRows($res) < 1)
            {
                return array();
            }
            $warehouses = array();
            while ($row = $db->fetchObject($res))
            {
                if ($return_obj)
                {
                    $warehouses[$row->id] = new Warehouse($row->id);
                }
                elseif ($group_by_chkid)
                {
                    $warehouses[$row->chkid] = array("id" => $row->id, "name" => $row->name, "chkid" => $row->chkid, 'type' => $row->type);
                }
                else
                {
                    $warehouses[] = array("id" => $row->id, "name" => $row->name, "chkid" => $row->chkid, 'type' => $row->type);
                }
            }
            return $warehouses;
        }

        public static function isMyCheckpoint($chkid)
        {
            $warehouses = self::getUserWarehouses(Session::loggedInUid());
            $checkpoints = array();
            if (!empty($warehouses))
            {
                foreach ($warehouses as $warehouse)
                {
                    $checkpoints[] = $warehouse['chkid'];
                }
            }
            return in_array($chkid, $checkpoints);
        }

        /*
         * getstatus for showing labels against a warehouse status
         */

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

        /*
         * get warehouse link on warehouse name
         */

        public static function getWarehouseLink($id, $row)
        {
            $link = new Link(JPath::absoluteUrl('warehouse/view/' . $row["waid"]), "", $id);
            return $link->publish();
        }

        /*
         * get warehouse link on warehouse Code
         */

        public static function getWarehouseLinkForCode($id, $row)
        {
            $link = new Link(JPath::absoluteUrl('warehouse/view/' . $row["waid"]), "", $row["checkpoint_code"]);
            return $link->publish();
        }

        public static function getWarehouseStockByIsvid($id, $row)
        {

            $item = new Item($row["iid"]);
            $variation = new InventorySetVariation($row['isvid']);
            $stock = InventorySetVariationManager::getStockValue($row["iid"], $item->getIitid(), $row["isvid"], $variation->getMeaid(), FALSE, $row["chkid"]);
            if (is_object($stock))
            {
                $totalStock = ($stock->available_stock - $stock->running_stock - $stock->blocked_stock);
                if ($item->getIitid() == 1)
                {
                    return round($totalStock, 0) . " " . $stock->unit;
                }
                return round($totalStock, 4) . " " . $stock->unit;
            }

            return 0;
        }

        public static function getWarehousePhysicalStockByIsvid($id, $row)
        {

            $item = new Item($row["iid"]);
            $variation = new InventorySetVariation($row['isvid']);
            $stock = InventorySetVariationManager::getStockValue($row["iid"], $item->getIitid(), $row["isvid"], $variation->getMeaid(), FALSE, $row["chkid"]);
            if (is_object($stock))
            {
                $totalStock = ($stock->stock);
                $cr = 1;
                if ($variation->getMeaid() > 0)
                {
                    $m = new Measurement($variation->getMeaid());
                    $cr = $m->getConversionRate();
                }
                if ($item->getIitid() == 1)
                {
                    return round($totalStock / $cr, 0) . " " . $stock->unit;
                }
                return round($totalStock / $cr, 4) . " " . $stock->unit;
            }

            return 0;
        }

        public static function getWarehouseBlockedStockByIsvid($id, $row)
        {

//            $item = new Item($row["iid"]);
            $variation = new InventorySetVariation($row['isvid']);
            $iid = $variation->getIid();
            $item = new Item($iid);
            $stock = InventorySetVariationManager::getStockValue($iid, $item->getIitid(), $row["isvid"], $variation->getMeaid(), FALSE, $row["chkid"]);
            $box_qty = '';
            if (isset($row['c_conversion_rate']) && $row['c_conversion_rate'] > 0)
            {
                $id = $id / $row['c_conversion_rate'];
            }
            if (!empty($row['boxsdfss']) && $row['boxsdfss'] == 1)
            {
                $variation = new InventorySetVariation($row['isvid']);
                $measurement = new Measurement($variation->getMeasuredUnit());
                if ($id > 0 && $variation->getMeasuredQty() > 0 && $measurement->getConversionRate() > 0)
                {
                    $box_qty = round((($id) * $variation->getMeasuredQty()) / $measurement->getConversionRate(), 4);
                }

                if ($box_qty > 0)
                {
                    $box_qty = " (" . $box_qty . "  " . $measurement->getName() . ") ";
                }
                else
                {
                    $box_qty = '';
                }
            }
            $box_qty_append = '';
            if (isset($row['variation_id']) && isset($row['show_package_qty']) && $row['show_package_qty'] == 1)
            {
                $variation = new InventorySetVariation($row['variation_id']);
                $box_qty_append = Utility::getBoxQtyFormat($variation->getPackageQuantity(), $variation->getCPackageMeasurement(), $id, $variation->getMeaid(), $variation->getCMeasurement());
            }
            if (is_object($stock))
            {
                $totalStock = ($stock->running_stock);
                if (isset($row['not_pick_from_stock']) && $row['not_pick_from_stock'] > 0)
                {
                    $totalStock = $id;
                }
                $str = '';
                if ($item->getIitid() == 1)
                {
                    $str = round($totalStock, 0) . " " . $stock->unit;
                }
                else
                {
                    $str = round($totalStock, 4) . " " . $stock->unit;
                }
                if (isset($row['link_bit']) && $row['link_bit'] > 0)
                {
                    return $str . " " . $box_qty_append . " " . $box_qty;
                }
                else
                {

                    return '<a class="variation-item-warehouse-show-allocatedstock" title="Show Allocated Stock Details" href="javascript:void" data-chkid="' . $row['chkid'] . '" data-id="' . $row['isvid'] . '">' . $str . " " . $box_qty_append . " " . $box_qty . '</a>';
                }
            }

            return 0;
        }

        /**
         * create the checkpoint mapping.
         * @param type $waid
         * @param type $chktid
         * @return boolean
         */
        public static function createCheckpointMapping($waid, $chktid, $stid)
        {
            if (!Warehouse::isExistent($waid))
            {
                return false;
            }
            $db = Rapidkart::getInstance()->getDB();
            $sql = " INSERT INTO " . SystemTables::DB_TBL_CHECKPOINT_MAPPING . " (waid,chktid , company_id , stid) VALUES ('::waid','::chktid', '::company', '::stid')";
            $args = array('::waid' => $waid, '::chktid' => $chktid, '::company' => BaseConfig::$company_id, '::stid' => $stid);
            $res = $db->query($sql, $args);
            if (!$res)
            {
                return false;
            }
            return $db->lastInsertId();
        }

        public static function getCheckpointsInventoryValue($chkids, $iids = array())
        {
            $db = Rapidkart::getInstance()->getDB();
            $cond = " ";
            $grpcond = " ";
            if (isset($chkids) && !empty($chkids))
            {
                $cond = $cond . " AND invset.chkid IN (" . implode(',', $chkids) . ") ";
                $grpcond = $grpcond . " GROUP BY invset.chkid";
            }

            if (isset($iids) && !empty($iids))
            {
                $cond = $cond . " AND invset.iid IN (" . implode(',', $iids) . ") ";
            }

            $sql = "SELECT invset.chkid, invset.price * count(invsetitem.isiid) as price, count(invsetitem.isiid) as total_items  "
                    . " FROM " . SystemTables::$inventory_set . " invset "
                    . " INNER JOIN " . SystemTables::DB_TBL_INVENTORY_SET_ITEM . " invsetitem ON invsetitem.isid = invset.isid AND invsetitem.isisid = 1"
                    . " WHERE invset.issid = 1 $cond $grpcond";
            $res = $db->query($sql);
            if (!$res || $db->resultNumRows() < 1)
            {
                return array();
            }
            $checkpointPrices = array();
            while ($row = $db->fetchObject($res))
            {
                array_push($checkpointPrices, $row);
            }
            return $checkpointPrices;
        }

        public static function getCheckpointsForWarehouses($waids)
        {
            $db = Rapidkart::getInstance()->getDB();
            $waidsStr = " WHERE  company_id = '::company' ";
            $args = array('::company' => BaseConfig::$company_id);
            if (is_array($waids) && !empty(array_filter($waids)))
            {
                $waidsStr .= " AND waid IN (" . implode(',', $waids) . ") ";
            }
            $sql = "SELECT chkid FROM " . SystemTables::DB_TBL_WAREHOUSE . " $waidsStr";
            $res = $db->query($sql, $args);
            if (!$res || $db->resultNumRows() < 1)
            {
                return array();
            }
            $checkpoints = array();
            while ($row = $db->fetchObject($res))
            {
                array_push($checkpoints, $row->chkid);
            }
            return $checkpoints;
        }

        public static function getCheckpointWarehouse($id)
        {
            $user = new AdminUser(intval(Session::loggedInUid()));
            $user->warehouses = WarehouseManager::getUserWarehouses($user->getId());
            $warehouse = array();
            if (!empty($user->warehouses))
            {
                foreach ($user->warehouses as $userWarehouse)
                {
                    if ($id === $userWarehouse['chkid'])
                    {
                        $warehouse = new Warehouse($userWarehouse['id']);
                        $warehouse->loadExtra();
                    }
                }
            }
            else
            {
                ScreenMessage::setMessage("oops user dont have any warehouses", ScreenMessage::MESSAGE_TYPE_ERROR);
                System::redirectInternal("warehouse/view");
            }
            if (!is_object($warehouse))
            {
                ScreenMessage::setMessage("This checkpoint does not belong to you", ScreenMessage::MESSAGE_TYPE_ERROR);
                System::redirectInternal("warehouse/view");
            }
            return $warehouse;
        }

        public static function isCheckpointMappingExist($chkid)
        {
            $db = Rapidkart::getInstance()->getDB();
            $sql = "SELECT * from " . SystemTables::DB_TBL_CHECKPOINT_MAPPING . " WHERE chkid = '::chkid'  AND company_id ='::company_id' ";
            $args = array('::chkid' => $chkid, '::company_id' => BaseConfig::$company_id);
            $res = $db->query($sql, $args);

            if (!$res || $db->resultNumRows() < 1)
            {
                return false;
            }
            return True;
        }

        public static function getCheckpointTypeName($chkid)
        {
            $db = Rapidkart::getInstance()->getDB();
            $sql = "SELECT chkt.name from " . SystemTables::DB_TBL_CHECKPOINT_MAPPING . " chk "
                    . " INNER  JOIN " . SystemTables::DB_TBL_CHECKPOINT_TYPE . " chkt ON chk.chktid = chkt.chktid"
                    . " WHERE chk.company_id = '::company' and chkid = ::chkid ";
            $args = array('::chkid' => $chkid, '::company' => BaseConfig::$company_id);
            $res = $db->query($sql, $args);
            if (!$res || $db->resultNumRows() < 1)
            {
                return false;
            }
            $row = $db->fetchObject($res);
            return $row->name;
        }

        public static function getFullId($id)
        {
            if (isset($id))
            {
                return Utility::variableGet("site_code") . "WH" . str_pad($id, 6, "0", STR_PAD_LEFT);
            }
        }

        public static function getWarehouseUrl($id, $row, $row_id)
        {
            if (hasPermission(USER_PERMISSION_WAREHOUSE_VIEW))
            {
                if (isset($row['warehouse_id']))
                {
                    $link = new Link(JPath::fullUrl('warehosue/view/' . $row['warehosue_id']), "", self::getFullId($row['warehosue_id']));
                    return $link->publish();
                }
                else if (isset($row['waid']))
                {
                    $link = new Link(JPath::fullUrl('warehosuer/view/' . $row['waid']), "", self::getFullId($row['waid']));
                    return $link->publish();
                }
            }
            return self::getFullId($id);
        }

        public static function getWarehousePurchaseOrderLink($id, $row = NULL, $row_id = NULL, $op = 0)
        {

            $purchase_order = new PurchaseOrder($id);
            if ($op == 0)
            {
                if ($row['purorid'])
                {
                    if (getSettings("IS_PROJECT_CODE_IN_PURCHASE_ORDER") && !getSettings("IS_PURCHASE_ORDER_PROJECT_SERIES_DISABLED"))
                    {
                        $pmid = $purchase_order->getPmid();
                        if ($pmid > 0)
                        {
                            $project = new ProjectManagement($pmid);
                            if (!empty($project->getPcode()))
                            {
                                $code = $project->getPcode();
                            }
                            else
                            {
                                $code = Utility::variableGet('site_code');
                            }
                        }
                        else
                        {
                            $code = Utility::variableGet('site_code');
                        }
                        $financial_year = InvoiceManager::getOnlyFinancialYear();
                        if ($pmid > 0)
                        {
                            $link = new Link(JPath::absoluteUrl('my-warehouse/purchase/view&chkid=' . $row['chkid'] . '&id=' . $id), "", $code . $financial_year . "/" . str_pad($purchase_order->getPurchaseOrderNumber(), 4, '0', STR_PAD_LEFT));
                        }
                        else
                        {
                            $link = new Link(JPath::absoluteUrl('my-warehouse/purchase/view&chkid=' . $row['chkid'] . '&id=' . $id), "", $code . "PO" . str_pad($purchase_order->getPurchaseOrderNumber(), 4, '0', STR_PAD_LEFT));
                        }

                        return $link->publish();
                    }
                    else
                    {
                        $link = new Link(JPath::absoluteUrl('my-warehouse/purchase/view&chkid=' . $row['chkid'] . '&id=' . $id), "", Utility::variableGet('site_code') . 'PO' . str_pad($purchase_order->getPurchaseOrderNumber(), 6, '0', STR_PAD_LEFT));
                        return $link->publish();
                    }
                }
            }
            else
            {
                if ((isset($row->purorid) && $row->purorid) || (isset($row['purorid'])))
                {
                    if (getSettings("IS_PROJECT_CODE_IN_PURCHASE_ORDER") && !getSettings("IS_PURCHASE_ORDER_PROJECT_SERIES_DISABLED"))
                    {
                        $pmid = $purchase_order->getPmid();
                        if ($pmid > 0)
                        {
                            $project = new ProjectManagement($pmid);
                            if (!empty($project->getPcode()))
                            {
                                $code = $project->getPcode();
                            }
                            else
                            {
                                $code = Utility::variableGet('site_code');
                            }
                        }
                        else
                        {
                            $code = Utility::variableGet('site_code');
                        }
                        $financial_year = InvoiceManager::getOnlyFinancialYear();

                        $link = $code . $financial_year . "/" . str_pad($purchase_order->getPurchaseOrderNumber(), 4, '0', STR_PAD_LEFT);

                        return $link;
                    }
                    else
                    {
                        $link = Utility::variableGet('site_code') . 'PO' . str_pad($purchase_order->getPurchaseOrderNumber(), 6, '0', STR_PAD_LEFT);
                        return $link;
                    }
                }
            }
        }

        public static function getPoNumberLink($id, $row, $row_id)
        {
            $purchase_order = new PurchaseOrder($row['purorid']);
            if (isset($row['purorid']) && $row['purorid'])
            {
                if (getSettings("IS_PROJECT_CODE_IN_PURCHASE_ORDER") && !getSettings("IS_PURCHASE_ORDER_PROJECT_SERIES_DISABLED"))
                {
                    $pmid = $purchase_order->getPmid();
                    if ($pmid > 0)
                    {
                        $project = new ProjectManagement($pmid);
                        if (!empty($project->getPcode()))
                        {
                            $code = $project->getPcode();
                        }
                        else
                        {
                            $code = Utility::variableGet('site_code');
                        }
                    }
                    else
                    {
                        $code = Utility::variableGet('site_code');
                    }
                    $financial_year = InvoiceManager::getOnlyFinancialYear();
                    if ($row_id == NULL)
                    {
                        return Utility::variableGet('site_code') . 'PO' . str_pad($purchase_order->getPurchaseOrderNumber(), 6, '0', STR_PAD_LEFT);
                    }
                    else
                    {
                        $link = new Link(JPath::absoluteUrl('my-warehouse/purchase/view&chkid=' . $row['chkid'] . '&id=' . $row['id']), "", $code . $financial_year . "/" . str_pad($purchase_order->getPurchaseOrderNumber(), 4, '0', STR_PAD_LEFT));
                        return $link->publish();
                    }
                }
                else
                {
                    $link = new Link(JPath::absoluteUrl('my-warehouse/purchase/view&chkid=' . $row['chkid'] . '&id=' . $row['id']), "", Utility::variableGet('site_code') . 'PO' . str_pad($purchase_order->getPurchaseOrderNumber(), 6, '0', STR_PAD_LEFT));
                    return $link->publish();
                }
            }
        }

        public static function getWarehouseLinkByCheckpoint($chkid, $row)
        {
            $warehouse = self::getWarehouseByChkid($chkid);
            if (!is_object($warehouse))
            {
                return "";
            }

            $link_href = JPath::fullUrl('warehouse/view/' . $warehouse->getId());
            $link = new Link($link_href, USER_PERMISSION_WAREHOUSE_VIEW, $warehouse->getName(), TRUE, FALSE, '');
            return $link->publish();
        }

        public static function getWarehouseIdUrl($id, $row, $row_id)
        {
            $link_href = '';
            if (isset($row['warehouse_id']))
            {
                $link_href = JPath::fullUrl('warehouse/view/' . $row['warehouse_id']);
            }
            else if (isset($row['waid']))
            {
                $link_href = JPath::fullUrl('warehouse/view/' . $row['waid']);
            }
            $link = new Link($link_href, USER_PERMISSION_WAREHOUSE_VIEW, $id, TRUE, FALSE, '');
            return $link->publish();
        }

        public static function getItemIsvidStock($id, $row)
        {
            $item = new Item($row['iid']);
            $variation = new InventorySetVariation($row['isvid']);
            $stock = InventorySetVariationManager::getStockValue($row['iid'], $item->getIitid(), $variation->getId(), NULL, FALSE, $row['chkid']);
            return is_object($stock) ? round(($stock->available_stock - $stock->running_stock - $stock->blocked_stock), 4) . " " . $stock->unit : 0;
        }

        public static function getCheckPointTypeAndName($chkid)
        {

            $type = self::getCheckpointTypeName($chkid);

            $args = array(
                "::chkid" => $chkid
            );
            $condition = " 1 ";
            switch ($type)
            {
                case "Warehouse" :
                    $args["::id"] = "waid";
                    $args["::name"] = "name";
                    $args["::table"] = SystemTables::DB_TBL_WAREHOUSE;
                    $condition .= " AND company_id = '::company' ";
                    $args['::company'] = BaseConfig::$company_id;
                    break;
            }

            if (!isset($args["::id"]) || !isset($args["::name"]) || !isset($args["::table"]))
            {
                return false;
            }

            $db = Rapidkart::getInstance()->getDB();

            $sql = " SELECT ::id as id , ::name as name FROM ::table WHERE $condition AND chkid = '::chkid'";
            $res = $db->query($sql, $args);
            if (!$res || $db->resultNumRows() < 1)
            {
                return false;
            }
            $arr = array();
            $arr['object'] = $db->fetchObject($res);
            $arr['type'] = $type;
            return $arr;
        }

        public static function getCheckPointsDetails($chkid = null, $isGroup = false, $inventory_type = array())
        {

            $chks = self::getCheckPoints($chkid);

            if (!is_array($chks) || empty($chks))
            {
                return false;
            }

            $db = Rapidkart::getInstance()->getDB();
            $checkpoints = array();

            foreach ($chks as $chk)
            {
                $type = $chk->chktid;

                $args = array(
                    "::chkid" => $chk->chkid,
                    "::chktid" => $type
                );
                $condition = " 1 ";
                switch ($type)
                {
                    case 1 :
                        $args["::id"] = "waid";
                        $args["::name"] = "name";
                        $args["::chkname"] = "Warehouse";
                        $args["::status"] = "wasid";
                        $args["::table"] = SystemTables::DB_TBL_WAREHOUSE;
                        $condition .= " AND company_id = '::company' ";
                        $args['::company'] = BaseConfig::$company_id;
                        break;
                }

                if ($inventory_type && !empty($inventory_type))
                {
                    $condition .= " AND type IN(::type) ";
                    $args['::type'] = implode(",", $inventory_type);
                }

                if (!isset($args["::id"]) || !isset($args["::name"]) || !isset($args["::table"]))
                {
                    return false;
                }

                $sql = " SELECT ::id as id , ::name as name, '::chkname' as chkname, chkid, ::chktid as chktid FROM ::table WHERE $condition AND chkid = ::chkid AND ::status = 1 ";
                $res = $db->query($sql, $args);

                if (!$res || $db->resultNumRows() < 1)
                {
                    continue;
                }
                while ($row = $db->fetchObject($res))
                {
                    if ($isGroup != false)
                    {
                        if (!isset($checkpoints[$row->chkname]))
                        {
                            $checkpoints[$row->chkname] = array();
                        }
                        $checkpoints[$row->chkname][$row->chkid] = $row;
                    }
                    else
                    {
                        $checkpoints[$row->chkid] = $row;
                    }
                }
            }
            return $checkpoints;
        }

        public static function getCheckPoints($chkid = null)
        {
            $args = array();

            $where = " WHERE waid IS NOT NULL ";
            if (isset($chkid) && $chkid != NULL)
            {
                $where .= " AND chkid = ::chkid";
                $args["::chkid"] = $chkid;
            }
            $where .= " AND company_id = '::company' ";
            $args['::company'] = BaseConfig::$company_id;

            $db = Rapidkart::getInstance()->getDB();

            $sql = " SELECT * from " . SystemTables::DB_TBL_CHECKPOINT_MAPPING
                    . " $where";
            $res = $db->query($sql, $args);

            if (!$res || $db->resultNumRows() < 1)
            {
                return false;
            }
            $arr = array();

            while ($row = $db->fetchObject($res))
            {
                $arr[$row->chkid] = $row;
            }
            return $arr;
        }

        public static function getCheckpointTypeUrl($chkid)
        {
            $chekpoint = self::getCheckPointTypeAndName($chkid);
            if (is_array($chekpoint) && !empty($chekpoint))
            {
                $obj = $chekpoint['object'];
                if (!is_object($obj))
                {
                    return false;
                }
                $type = $chekpoint['type'];
                switch ($type)
                {
                    case "Warehouse" :
                        $link = new Link(JPath::fullUrl('warehouse/view/' . $obj->id), "", $obj->name);
                        return $link->publish();
                        break;
                }
            }
            else
            {
                return FALSE;
            }
        }

        public static function getStageInventoryRequestLink($id, $row)
        {
            $getWorkOrderDetails = new WorkOrder($id);
            $link = new Link(Jpath::fullUrl("my-warehouse/workorder/allot&woid=" . $row['workorder'] . "&chkid=" . $row['chkid']), "", Utility::variableGet('site_code') . 'WO' . str_pad($getWorkOrderDetails->getWorkorderNumber(), 6, '0', STR_PAD_LEFT));
            return $link->publish();
        }

        public static function getStageInventoryAllotedLink($id, $row)
        {
            $getWorkOrderDetails = new WorkOrder($id);
            $link = new Link(Jpath::fullUrl("my-warehouse/workorder/alloted&woid=" . $row['workorder'] . "&chkid=" . $row['chkid']), ">", Utility::variableGet('site_code') . 'WO' . str_pad($getWorkOrderDetails->getWorkorderNumber(), 6, '0', STR_PAD_LEFT));
            return $link->publish();
        }

        public static function getItemStockAndBoxQty($id, $row)
        {
            $variation = new InventorySetVariation($row['isvid']);

            $item = new Item($variation->getIid());
            $warehouse_checkpoint = isset($_GET['checkpoint']) ? $_GET['checkpoint'] : NULL;
            if (!$warehouse_checkpoint && isset($row['warehouse_checkpoint']))
            {
                $warehouse_checkpoint = $row['warehouse_checkpoint'];
            }
            if (!$warehouse_checkpoint && isset($row['chkid']))
            {
                $warehouse_checkpoint = $row['chkid']
                ;
            }
            $box_qty = "";
            if ($item->getIitid() == 1)
            {
                $meaid = ($variation->getMeaid() && Measurement::isExistent($variation->getMeaid())) ? $variation->getMeaid() : NULL;
                $stock = InventorySetVariationManager::getStockValue($item->getId(), $item->getIitid(), $variation->getId(), $meaid, FALSE, $warehouse_checkpoint);

                if ($stock && !is_null($variation->getMeasuredQty()) && $variation->getMeasuredQty() > 0 && Measurement::isExistent($variation->getMeasuredUnit()))
                {
                    $measurement = new Measurement($variation->getMeasuredUnit());
                    $box_qty = " (" . round((($stock->available_stock - $stock->running_stock) * $variation->getMeasuredQty()) / $measurement->getConversionRate(), 4) . "  " . $measurement->getName() . ") ";
                }
            }
            else
            {
                $meaid = ($variation->getMeaid() && Measurement::isExistent($variation->getMeaid())) ? $variation->getMeaid() : NULL;
                $stock = InventorySetVariationManager::getStockValue($item->getId(), $item->getIitid(), $variation->getId(), $meaid, FALSE, $warehouse_checkpoint);
            }
            return is_object($stock) ? round(($stock->available_stock - $stock->running_stock), 4) . "  " . $stock->unit . " " . $box_qty : 0;
        }

        public static function getItemStock($id, $row)
        {
            $variation = new InventorySetVariation($row['isvid']);

            $item = new Item($variation->getIid());
            $warehouse_checkpoint = (isset($row['warehouse_checkpoint']) && CheckPointManager::isChkidExistent($row['warehouse_checkpoint'])) ? $row['warehouse_checkpoint'] : NULL;
            if ($item->getIitid() == 1)
            {
                $stock = InventorySetVariationManager::getStockValue($item->getId(), $item->getIitid(), $variation->getId(), NULL, FALSE, $warehouse_checkpoint);
            }
            else
            {
                $meaid = ($item->getMeaid() && Measurement::isExistent($item->getMeaid())) ? $item->getMeaid() : NULL;
                if (isset($row['bom_meaid']) && $row['bom_meaid'] > 0)
                {
                    $meaid = $row['bom_meaid'];
                }
                $stock = InventorySetVariationManager::getStockValue($item->getId(), $item->getIitid(), $variation->getId(), $meaid, FALSE, $warehouse_checkpoint);
            }
            return is_object($stock) ? Utility::getQtyDisplayFormat(round(($stock->available_stock - $stock->running_stock), 4)) . " " . $stock->unit : 0;
        }

        public static function getItemPrice($id, $row)
        {
            return Utility::getColumnCurrencyFormat($row["purchase_price"]);
        }

        public static function getItemStockValue($id, $row)
        {

            $variation = new InventorySetVariation($row['isvid']);

            $item = new Item($variation->getIid());
            $warehouse_checkpoint = (isset($row['warehouse_checkpoint']) && CheckPointManager::isChkidExistent($row['warehouse_checkpoint'])) ? $row['warehouse_checkpoint'] : NULL;

            $groupBy = "isvid";
//            $res = self::getInventorySetStock($item->getId(), $item->getIitid(), $groupBy, $variation->getId(), $warehouse_checkpoint, NULL);
            $s = InventorySetVariationManager::getStockValue($item->getId(), $item->getIitid(), $variation->getId(), NULL, FALSE, $warehouse_checkpoint);
            $total_stock_value = 0;
            if ($s)
            {
                $total_stock_value = $s->available_stock - $s->running_stock - $s->blocked_stock;
            }
//            $db = Rapidkart::getInstance()->getDB();
//            if (!$res || $db->resultNumRows() < 1)
//            {
//                return FALSE;
//            }
//            $row = $db->fetchObject($res);
//            $total_stock_value = 0;
//            if ($row)
//            {
//                if ($item->getIitid() == 2)
//                {
//                    $measurement = new Measurement($item->getMeaid());
//                    $row->available_stock_value = $row->available_stock_value / $measurement->getConversionRate();
//                    $row->running_stock_value = $row->running_stock_value / $measurement->getConversionRate();
//                    $row->initial_stock = $row->initial_stock / $measurement->getConversionRate();
//                    $row->blocked_stock_value = $row->blocked_stock_value / $measurement->getConversionRate();
//                    $row->unit = $measurement->getName();
//                    $row->measurement = $measurement;
//                }
//                $total_stock_value = $row->available_stock_value - $row->running_stock_value - $row->blocked_stock_value;
//            }
            return Utility::getColumnCurrencyFormat($total_stock_value);
        }

        public static function isMyWarehouse($id, $waid)
        {
            $warehouses = self::getUserWarehouses($id);
            if (!empty($warehouses))
            {
                foreach ($warehouses as $warehouse)
                {
                    if ($waid === $warehouse['id'])
                    {
                        return TRUE;
                    }
                }
            }
            return FALSE;
        }

        public static function getWarehouseCallback($cat)
        {
            $user = SystemConfig::getUser();
            $warehouses = WarehouseManager::getUserWarehouses($user->getId());
            $data = "";
            if ($warehouses)
            {
                foreach ($warehouses as $warehouse)
                {
                    $data .= '<li data-jstree=\'{"icon":"' . $cat->getIcon() . '","mid":"' . $cat->getMid() . '","simeid":"' . $cat->getId() . '","motaid":"' . $cat->getMotaid() . '","readonly":"' . ($cat->getHr() === '1' ? TRUE : FALSE) . '"}\'>' . $warehouse['name'];
                }
            }
            return $data;
        }

        public static function getWarehouseStockIssueAttributes($data, $row = NULL)
        {
            if(isset($row['pmid']) && $row['pmid'] > 0)
            {
                $project = new ProjectManagement($row['pmid']);
                return $project->getPname();
            }
            if (!empty($data))
            {
                $attrs = json_decode($data, true);
                if (isset($attrs['custom_attr']) && !empty($attrs['custom_attr']) && is_array($attrs['custom_attr']) && count($attrs['custom_attr']) > 0)
                {
                    foreach ($attrs['custom_attr'] as $key => $value)
                    {
                        return $value;
                    }
                }
                return "-";
            }
            else
            {
                return "-";
            }
        }

        public static function getWarehouseStockReceiveAttributes($data)
        {
            if (!empty($data))
            {
                $attrs = json_decode($data);
                if (isset($attrs->custom_attr) && !empty($attrs->custom_attr))
                {
                    foreach ($attrs->custom_attr as $key => $value)
                    {
                        return $value;
                    }
                }
                return "-";
            }
            else
            {
                return "-";
            }
        }

        public static function getWarehouseStockDataTable($isvid, $chkids = "")
        {
            $table = new DataTable();
            $table->setTableId('warehouse-table');
            $table->setDefaultColumns(
                    array(
                        array("name" => "name", "title" => "Warehouse Name", "description" => "Name of the warehouse"),
                        array("name" => "city", "title" => "Physical Stock", "description" => "Physical Stock"),
                        array("name" => "stid", "title" => "Allocated Stock", "description" => "Allocated Stock"),
                        array("name" => "ctid", "title" => "Available Stock", "description" => "Available Stock")
                    )
            );
            $table->setColumns(
                    array(
                        array("name" => "name", "title" => "Warehouse Name", "description" => "Name of the warehouse", "callback" => "WarehouseManager::getWarehouseLink"),
                        array("name" => "city", "title" => "Physical Stock", "description" => "Physical Stock", "callback" => "WarehouseManager::getWarehousePhysicalStockByIsvid"),
                        array("name" => "stid", "title" => "Allocated Stock", "description" => "Allocated Stock", "callback" => "WarehouseManager::getWarehouseBlockedStockByIsvid"),
                        array("name" => "ctid", "title" => "Available Stock", "description" => "Available Stock", "callback" => "WarehouseManager::getWarehouseStockByIsvid")
                    )
            );
            $table->setIfDetails(FALSE);
            $table->setIfTask(FALSE);
            $table->setIfSerial(TRUE);
            $table->setIfRealtime(true);
            $table->setIfExportable(true);
            $variation = new InventorySetVariation($isvid);
            $iid = $variation->getIid();
            $table->setRealtimeUrl(Jpath::fullUrl("warehouse/warehouse-view-render&isvid=" . $isvid . "&chkids=" . $chkids . ""));
            $table->setIfHeader(true);
            $table->setIfFooter(false);
            $table->setIfAction(true);
            $table->setIfSearch(False);
            $table->setIfInfo(False);
            $table->setIfpaging(False);
            $table->setIfColumnSelectable(true);
            $table->setIfExportable(False);
            $actions = array();

            if (hasPermission(USER_PERMISSION_WAREHOUSE_VIEW))
            {
                array_push($actions, array(
                    "title" => "View Warehouse",
                    "icon" => "fa fa-fw fa-film text-primary",
                    "class" => "view-warehouse",
                    "color" => "",
                    "type" => "link",
                    "link_url" => JPath::absoluteUrl('warehouse/view/[id]'),
                    "action_id" => array(array("key" => "id", "value" => "waid"))
                        )
                );
            }
            array_push($actions, array
                (
                "title" => "View Inventory",
                "icon" => "fa fa-fw fa-truck text-warning",
                "type" => "link",
                "link_url" => JPath::absoluteUrl("my-warehouse/inventory-mgmt/variation/view&isvid=" . $isvid . "&chkid=[chkid]"),
                "color" => "",
                "action_id" => array(array("key" => "id", "value" => "waid"), array("key" => "chkid", "value" => "chkid"))
                    )
            );

            if (!empty($actions))
            {
                $table->setActionButtons($actions);
            }

            $table->setExtra(array("status_id" => "wasid", "waid" => "waid", "chkid" => "chkid", "iid" => $iid, "isvid" => $isvid));
            return $table;
        }

        public static function getInventorySetStock($iid, $iitid, $grpby, $isvid = NULL, $chkid = NULL, $isid = NULL)
        {
            $args = array(
                '::iid' => $iid
            );
            $isvidcond = '';
            if ($isvid)
            {
                $isvidcond = " AND ins.isvid = '::isvid'";
                $args['::isvid'] = $isvid;
            }
            $chkidcond = '';
            $orderchkidcond = '';

            if ($chkid)
            {
                $chkidcond = " AND ins.chkid = '::chkid'";
                $orderchkidcond = " AND chkid = '::chkid'";

                $args['::chkid'] = $chkid;
            }

            $isidcond = '';
            if ($isid)
            {
                $isidcond = " AND ins.isid = '::isid'";
                $args['::isid'] = $isid;
            }

            if ($iitid == 1)
            {

                $sql = "SELECT ins.isid,ins.isvid,ins.chkid, SUM(COALESCE((SELECT count(isid.isiid) as initial_stock FROM inventory_set_item isid  WHERE isid.isisid <> '3' AND isid.isid = ins.isid GROUP BY  isid.isid  ), 0)) as initial_stock,  SUM(COALESCE((SELECT count(isid.isiid) as available_stock FROM inventory_set_item isid  WHERE isid.isisid = '1' AND isid.isid = ins.isid GROUP BY  isid.isid ), 0)) as available_stock, SUM(COALESCE((SELECT count(isid.isiid) as available_stock_value FROM inventory_set_item isid  WHERE isid.isisid = '1' AND isid.isid = ins.isid GROUP BY  isid.isid ), 0)* ins.price) as available_stock_value, iid, SUM(COALESCE((SELECT (CASE WHEN ioie.exe_o IS NULL THEN SUM(ioi.quantity) ELSE SUM(ioi.quantity) - SUM(ioie.exe_o) END) as ordered FROM item_order_item ioi  LEFT JOIN ( SELECT count(exe.oiid) as exe_o , exe.oiid    FROM item_order_item_execution exe  GROUP BY exe.oiid ) as ioie ON(ioie.oiid = ioi.oiid) WHERE ioi.isid != 'NULL' $orderchkidcond AND ioi.isvid = ins.isvid AND ioi.isid = ins.isid GROUP BY ioi.isid ), 0)) as running_stock , SUM(COALESCE((SELECT (CASE WHEN ioie.exe_o IS NULL THEN SUM(ioi.quantity) ELSE SUM(ioi.quantity) - SUM(ioie.exe_o) END) as ordered FROM item_order_item ioi  LEFT JOIN ( SELECT count(exe.oiid) as exe_o , exe.oiid    FROM item_order_item_execution exe  GROUP BY exe.oiid ) as ioie ON(ioie.oiid = ioi.oiid) WHERE ioi.isid != 'NULL' $orderchkidcond AND ioi.isvid = ins.isvid AND ioi.isid = ins.isid GROUP BY ioi.isid ), 0) * ins.price) as running_stock_value, SUM(COALESCE((SELECT SUM(bl.qty) as blocked FROM block_inventory bl WHERE bl.binvsid = 1 AND ins.isvid= bl.isvid AND bl.isid = ins.isid GROUP BY bl.isid), 0)) as blocked_stock, SUM(COALESCE((SELECT SUM(bl.qty) as blocked FROM block_inventory bl WHERE bl.binvsid = 1 AND ins.isvid= bl.isvid AND bl.isid = ins.isid GROUP BY bl.isid), 0)*ins.price) as blocked_stock_value FROM  " . SystemTables::$inventory_set . " ins WHERE iid = '::iid' $isvidcond $chkidcond $isidcond GROUP BY $grpby";
            }
            else
            {
                $sql = "SELECT stock.isid, stock.meaid, ins.chkid, ins.iid,  SUM(COALESCE(CASE WHEN stock.isiostid = 2  THEN stock.qty END,0)) total_debits , SUM(COALESCE(CASE WHEN stock.isiostid = 1 THEN stock.qty END,0)) total_credits ,SUM(COALESCE(CASE WHEN stock.isiostid = 1 THEN stock.qty END,0)) initial_stock,SUM(COALESCE(CASE WHEN stock.isiostid = 1 THEN stock.qty END,0)) - SUM(COALESCE(CASE WHEN stock.isiostid = 2 and (stock.status !='2'  OR stock.status IS NULL) THEN stock.qty END,0)) available_stock, SUM(COALESCE(CASE WHEN stock.isiostid = 1 THEN stock.qty END,0)*ins.price) - SUM(COALESCE(CASE WHEN stock.isiostid = 2 and (stock.status!='2' or stock.status IS NULL) THEN stock.qty END,0)*ins.price) available_stock_value ,COALESCE(ordered,0) as running_stock,COALESCE(ordered,0)*ins.price as running_stock_value, COALESCE(blocked , 0) as blocked_stock, COALESCE(blocked , 0)*ins.price as blocked_stock_value  FROM inventory_set_item_open_stock as stock INNER JOIN " . SystemTables::$inventory_set . " as ins ON iid = '::iid' AND ins.isid =  stock.isid $isvidcond $chkidcond $isidcond LEFT JOIN ( SELECT (CASE WHEN ioie.exe_o IS NULL THEN SUM(ioi.quantity) ELSE SUM(ioi.quantity) - SUM(ioie.exe_o) END) as ordered , SUM(ioi.quantity), ioi.isvid , ioi.isid , ioi.oiid FROM item_order_item ioi LEFT JOIN ( SELECT SUM(isios.qty) as exe_o , exe.oiid FROM item_order_item_execution exe  LEFT JOIN inventory_set_item_open_stock isios ON (isios.isiosid = exe.isiid) GROUP BY exe.oiid ) as ioie ON(ioie.oiid = ioi.oiid) WHERE ioi.isid != 'NULL' $orderchkidcond GROUP BY ioi.isid ) as io ON (io.isvid = ins.isvid AND io.isid = ins.isid) LEFT JOIN ( SELECT SUM(bl.qty) as blocked , bl.isvid , bl.isid , bl.binvid FROM block_inventory bl WHERE bl.binvsid = 1  GROUP BY bl.isid ) as block ON(ins.isvid= block.isvid AND block.isid = ins.isid)  GROUP BY ins.$grpby";
            }
            $db = Rapidkart::getInstance()->getDB();
            $res = $db->query($sql, $args);

            return $res;
        }

        public static function getWarehouseShippingAddressForm(Warehosue $warehouse = NULL, $cols = FALSE)
        {

            $column = "12 , 12 , 12 , 12";
            if ($cols)
            {
                $column = "6 , 6  , 12 , 12";
            }
            $formGroupAdd = new FormGroup();
            $formGroup = new FormGroup();
            $formRow = new FormRow();
            $formGroupAdd->setHeading('Address');
            $formGroupAdd->setDescription('Please fill LINE1, COUNTRY, STATE and CITY to save address!');
            $x = ('Office');
            $sitename = new FormInputBox('add-sitename', $x . ' Name', "Enter " . $x . " Name", FALSE, "add-sitename");
//            if ($said)
//            {
//                $sitename->setVal($said->getSiteName());
//            }
            $formRow->addChild($sitename->publishXml(), 12, 12, 12, 12);
            $formGroupAdd->addChild($formRow->publishXml());
            $formRow->refreshDom();
            if (defined('WAREHOUSE_SHIPPING_ADDRESS_CONTACT_DETAILS_SHOW') && getSettings("WAREHOUSE_SHIPPING_ADDRESS_CONTACT_DETAILS_SHOW"))
            {
                $fname = new FormInputBox("add-fname", " Contact Person: First Name", "Enter First Name", FALSE, "add-fname");
                $lname = new FormInputBox("add-lname", " Contact Person: Last Name", "Enter Last Name", FALSE, "add-lname");
                $phone_number = new FormInputBox("add-mobile", " Contact Person: Phone Number", "Enter Phone Number", FALSE, "add-mobile");
//                if ($said)
//                {
//                    $fname->setVal($said->getFirstName());
//                    $lname->setVal($said->getLastName());
//                    $phone_number->setVal($said->getMobile());
//                }
                $formRow->addChild($fname->publishXml(), 3, 3, 3, 6);
                $formRow->addChild($lname->publishXml(), 3, 3, 3, 6);

                $formRow->addChild($phone_number->publishXml(), 6, 6, 6, 12);
                $formGroupAdd->addChild($formRow->publishXml());

                $formRow->refreshDom();
            }
            $line_1 = new FormInputBox("line1", "Line 1", "Enter Line 1", FALSE, "line1");
            $line_2 = new FormInputBox("line2", "Line 2", "Enter Line 2", FALSE, "line2");

//            $ctid = ($said) ? $said->getCtid() : 0;
            $countrySelect = new FormSelectPicker("ctid", "Country", TRUE);
            $countrySelect->setOnchangeCallback("vendorCountryChange");

            $country_list = CountryManager::getCountries();
            if (defined('DEFAULT_COUNTRY') && defined('DEFAULT_STATE'))
            {
                $default_country_available = getSettings("DEFAULT_COUNTRY");
            }
            if (valid($country_list) && !empty($country_list))
            {
                $countrySelect->addItem("", "Select", ($said) ? FALSE : TRUE);
                foreach ($country_list as $country)
                {
                    $selected = ($default_country_available) ? (($default_country_available == $country->getId()) ? TRUE : FALSE) : FALSE;
                    if ($selected)
                    {
                        $ctid = getSettings("DEFAULT_COUNTRY");
                    }
                    $countrySelect->addItem($country->getId(), $country->getName(), $selected);
                }
            }

            $stid = ($said) ? $said->getStid() : 0;
            $default_state_available = false;
            $stateSelect = new FormSelectPicker("stid", "State", TRUE);
            $stateSelect->setOnchangeCallback("vendorStateChange");
            if ($said)
            {
                $state_list = StateManager::getStates("ctid='" . $ctid . "'");
                foreach ($state_list as $state)
                {
                    if ($stid == $state->getId())
                    {
                        $stateSelect->addItem($state->getId(), $state->getState(), TRUE);
                    }
                    else
                    {
                        $stateSelect->addItem($state->getId(), $state->getState(), FALSE);
                    }
                }
            }
            else if (defined('DEFAULT_COUNTRY') && defined('DEFAULT_STATE'))
            {
                $default_state_available = getSettings("DEFAULT_STATE");
                $state_list = StateManager::getStates('ctid = ' . getSettings("DEFAULT_COUNTRY"));
                foreach ($state_list as $state_obj)
                {
                    $selected = ($default_state_available) ? (($default_state_available == $state_obj->getId()) ? TRUE : FALSE) : FALSE;
                    if ($selected)
                    {
                        $stid = getSettings("DEFAULT_STATE");
                    }
                    $stateSelect->addItem($state_obj->getId(), $state_obj->getState(), $selected);
                }
            }
            else
            {
                $stateSelect->addItem(0, 'Select country first', TRUE);
            }

            $city = new FormTokenInput('coverid', 'City', 'user-search', 'form-control');
            $city->setUrl('?urlq=coverage');
            $city->setQuerySubmit('get-cities');
            $value = array('stid' => $stid, 'ctid' => $ctid);
            $city->setOptions('queryType', $value);
            $city->setTokenLimit(1);
            $city->setAddCallback('addCityForVendor');
            $city->setDropdownMethod('noVendorFound');
            $city->setOptions('addNew', 'true');
            if ($said && $said->getCity() > 0)
            {
                $coverage = $said->getCity();
                $coverage = new Coverage($coverage);
                if ($coverage->getId() == $said->getCity())
                {
                    $pre_populate = array(array('id' => $coverage->getId(), 'name' => $coverage->getCity()));
                    $city->setPrePopulate(json_encode($pre_populate));
                }
            }
            else
            {
                $disabled = TRUE;
                if ($ctid > 0 && $stid > 0)
                {
                    $disabled = FALSE;
                    if (defined('DEFAULT_CITY'))
                    {
                        $cities = CoverageManager::loadCoverages("ctid = '" . getSettings("DEFAULT_COUNTRY") . "' AND stid = '" . getSettings("DEFAULT_STATE") . "' AND coverid ='" . getSettings("DEFAULT_CITY") . "'");  //getting coverage according to states
                        if (is_array($cities) && !empty($cities))
                        {
                            foreach ($cities as $coverage)
                            {
                                $pre_populate[] = array('id' => $coverage->getId(), 'name' => $coverage->getCity());
                            }
                            $city->setPrePopulate(json_encode($pre_populate));
                        }
                    }
                }
                $city->setOptions('disabled', $disabled);
            }
            $pincode = new FormInputBox("pincode", "PIN/ZIP Code", "Enter PIN/ZIP Code", FALSE, "pincode");
            if ($said)
            {
                $line_1->setVal($said->getLine1());
                $line_2->setVal($said->getLine2());
                $pincode->setVal($said->getZipCode());
            }
            $formRow->addChild($line_1->publishXml(), $column);

            $formRow->addChild($line_2->publishXml(), $column);
            $formGroupAdd->addChild($formRow->publishXml());

            $formRow->refreshDom();
            $formRow->addChild($countrySelect->publishXml(), $column);

            $formRow->addChild($stateSelect->publishXml(), $column);
            $formGroupAdd->addChild($formRow->publishXml());

            $formRow->refreshDom();
            $formRow->addChild($city->publishXml(), $column);

            $formRow->addChild($pincode->publishXml(), $column);
            $formGroupAdd->addChild($formRow->publishXml());

            $formGroup->addChild($formGroupAdd->publishXml());
            return $formGroup;
        }

        public static function getChkidByWarehouse($waid)
        {
            $db = Rapidkart::getInstance()->getDB();
            $sql = " SELECT * FROM " . SystemTables::DB_TBL_WAREHOUSE . " WHERE company_id = '::company' and waid = '::waid' ";
            $args = array("::waid" => $waid, '::company' => BaseConfig::$company_id);
            $res = $db->query($sql, $args);
            if (!$res)
            {
                return false;
            }
            $row = $db->fetchObject($res);
            return $row;
        }

        public static function getWarehouseStockValue($id, $row, $row_id)
        {
            $variation = new InventorySetVariation($row['isvid']);

            $item = new Item($variation->getIid());
            $warehouse_checkpoint = (isset($row['warehouse_checkpoint']) && CheckPointManager::isChkidExistent($row['warehouse_checkpoint'])) ? $row['warehouse_checkpoint'] : NULL;
            $box_qty = "";
            if ($item->getIitid() == 1 && !is_null($variation->getMeasuredQty()) && $variation->getMeasuredQty() > 0 && Measurement::isExistent($variation->getMeasuredUnit()))
            {
                $meaid = ($variation->getMeaid() && Measurement::isExistent($variation->getMeaid())) ? $variation->getMeaid() : NULL;
                $stock = InventorySetVariationManager::getStockValue($item->getId(), $item->getIitid(), $variation->getId(), $meaid, FALSE, $warehouse_checkpoint);
                $measurement = new Measurement($variation->getMeasuredUnit());
                if ($stock)
                {
                    $box_qty = " (" . round((($stock->available_stock - $stock->running_stock) * $variation->getMeasuredQty()) / $measurement->getConversionRate(), 4) . "  " . $measurement->getName() . ") ";
                }
            }
            else
            {
                $meaid = ($variation->getMeaid() && Measurement::isExistent($variation->getMeaid())) ? $variation->getMeaid() : NULL;
                $stock = InventorySetVariationManager::getStockValue($item->getId(), $item->getIitid(), $variation->getId(), $meaid, FALSE, $warehouse_checkpoint);
            }
            $stock_val = is_object($stock) ? round(($stock->available_stock - $stock->running_stock), 4) . "  " . $stock->unit . " " . $box_qty : 0;
            return round($row["purchase_price_avg"] * $stock_val, 2);
        }

        // service function
        public static function getAllInventories($last_fetch = NULL, $traversal = NULL, $search = NULL)
        {
            $db = Rapidkart::getInstance()->getDB();
            $args = array();
            $sql = " SELECT * FROM warehouse_inventory_view_" . BaseConfig::$company_id . " WHERE 1=1 ";
            if (!empty($last_fetch))
            {
                if ($traversal == 1)
                {
                    $sql .= " AND created_ts  >= '::last_fetch'";
                    $args['::last_fetch'] = $last_fetch;
                }
                if ($traversal == 2)
                {
                    $sql .= " AND created_ts  < '::last_fetch'";
                    $args['::last_fetch'] = $last_fetch;
                }
            }
            if (!empty($search))
            {
                $sql .= " AND (item_name LIKE '%::search%' OR company LIKE '%::search%' OR brand LIKE '%::search%' OR internal_code LIKE '%::search%' ) ";
                $args['::search'] = $search;
            }
            $sql .= " ORDER BY created_ts DESC LIMIT 25 ";
            $res = $db->query($sql, $args);
            if (!$res)
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

        public static function getWarehouseInventoryStockView($status = NULL, $put_away = 0, $inventory_bit = 0, $grouping = 0, $stock_check = 1, $check_custom_qty = 0)
        {
            $db = Rapidkart::getInstance()->getDB();
            $view_name = "warehouse_inventory_stock_view_" . BaseConfig::$company_id;
            if ($inventory_bit)
            {
                $view_name .= "_inventory";
            }
            $sql = "CREATE OR REPLACE ALGORITHM = UNDEFINED DEFINER = root@localhost SQL SECURITY DEFINER VIEW $view_name AS ";
            if ($put_away)
            {
                $sql .= " SELECT '0' as is_sticker_bit, '0' as total_amount, '0' AS 'transportation_charges', 0 as iscpuiid , `iio`.`isciid` AS `isid`,`iio`.`isvid` AS `isvid`,variation.name AS variation_name,`isc`.`iscid` AS `iscid`,`iio`.`iid` AS `iid`,`isc`.`venid` AS `venid`,0 AS `product_rank`,'' AS `name`,coalesce(`isc`.`iscsid`,0) AS `iscsid`,`iscs`.`name` AS `container_status`,'' AS `description`,`isc`.`chkid` AS `chkid`,`chk`.`chktid` AS `chktid`,0 AS `shipping_cost`,`iio`.`price` AS `price`,`iio`.`price` AS `mrp`,0 AS `ships_in`,'' AS `available_from`,1 AS `issid`,`iio`.`data` AS `data`,`isc`.`purchase_date` AS `created_ts`,`isc`.`updated_ts`  AS `updated_ts`,'0' AS `stock`, iio.quantity AS `quantity`,`vendor`.`name` AS `seller`,`variation`.`internal_code` AS `sku_code` , '' as isid_number , '' as manufacturing_date , '' as expiry_date, '' as `barcode`, `rejected_qty` as `rejected_qty`, `rejected_reason` as `rejected_reason`, `comment` as `comment` , ws.name as  `section` , '' as available , '' as allocated , iio.physical_count , iio.physical_count as c_pcount , iio.waseid , iio.damaged_qty , iio.damaged_reason , isc.jobwoid , iio.qc_approved_qty , iio.qc_rejected_qty , iio.putaway_qty, iio.putaway_complete_qty , variation.weight , (CASE WHEN  variation.weight > 0 THEN  (iio.quantity/COALESCE(m.conversion_rate,1))*variation.weight ELSE 0 END)  as total_weight  , 0 as value_bit, '' as dimensions, '' as packing_remarks, '' as inv_weight from ((((( inventory_set_container_items iio LEFT JOIN `inventory_set_container` `isc`  ON(isc.iscsid != 3 and isc.iscid = iio.iscid) join `inventory_set_variations` `variation` on(((`variation`.`isvid` = `iio`.`isvid` and variation.company_id = '::company') and (`variation`.`isvsid` <> 3)))) left join `vendor` on(( `vendor`.`venid` = `isc`.`venid` and vendor.company_id ='::company'))) join `checkpoint_mapping` `chk` on((`chk`.`chkid` = `isc`.`chkid` and chk.company_id = '::company'))) ) left join `inventory_set_container_status` `iscs` on((`isc`.`iscsid` = `iscs`.`iscsid`))) LEFT JOIN warehouse_section ws ON(ws.waseid = iio.waseid) left join measurement m ON(m.meaid = variation.meaid)  where isc.company_id = '::company' group by `iio`.`isciid`";
            }
            elseif ($status)
            {

                $sql .= " SELECT '0' as is_sticker_bit, '0' as total_amount, '0' AS 'transportation_charges', `iio`.`iscpuiid` ,  `iio`.`iscpuiid` AS `isid`,`iio`.`isvid` AS `isvid`,variation.name AS variation_name,`isc`.`iscid` AS `iscid`,`iio`.`iid` AS `iid`,`isc`.`venid` AS `venid`,0 AS `product_rank`,'' AS `name`,coalesce(`isc`.`iscsid`,0) AS `iscsid`,`iscs`.`name` AS `container_status`,'' AS `description`,`isc`.`chkid` AS `chkid`,`chk`.`chktid` AS `chktid`,0 AS `shipping_cost`,`iio`.`price` AS `price`,`iio`.`price` AS `mrp`,0 AS `ships_in`,'' AS `available_from`,1 AS `issid`,`iio`.`data` AS `data`,`isc`.`purchase_date` AS `created_ts`,`isc`.`updated_ts`  AS `updated_ts`,'0' AS `stock`, iio.quantity AS `quantity`,`vendor`.`name` AS `seller`,`variation`.`internal_code` AS `sku_code` , '' as isid_number , '' as manufacturing_date , '' as expiry_date, '' as `barcode`, `rejected_qty` as `rejected_qty`, `rejected_reason` as `rejected_reason`, `comment` as `comment` , ws.name as  `section` , '' as available , '' as allocated , iio.physical_count , iio.physical_count as c_pcount , iio.waseid , iio.damaged_qty , iio.damaged_reason , isc.jobwoid , variation.weight , (CASE WHEN  variation.weight > 0 THEN  (iio.quantity/COALESCE(m.conversion_rate,1))*variation.weight ELSE 0 END)  as total_weight  , 0 as value_bit , '' as dimensions, '' as packing_remarks, '' as inv_weight from ((((( inventory_set_consignment_purchase_item iio LEFT JOIN `inventory_set_container` `isc`  ON(isc.iscsid != 3 and isc.iscid = iio.iscid) join `inventory_set_variations` `variation` on(((`variation`.`isvid` = `iio`.`isvid` and variation.company_id = '::company') and (`variation`.`isvsid` <> 3)))) left join `vendor` on(( `vendor`.`venid` = `isc`.`venid` and vendor.company_id ='::company'))) join `checkpoint_mapping` `chk` on((`chk`.`chkid` = `isc`.`chkid` and chk.company_id = '::company'))) ) left join `inventory_set_container_status` `iscs` on((`isc`.`iscsid` = `iscs`.`iscsid`))) LEFT JOIN warehouse_section ws ON(ws.waseid = iio.waseid)  left join measurement m ON(m.meaid = variation.meaid) where isc.company_id = '::company' group by `iio`.`iscpuiid`";
            }
            else
            {
                $sql .= " SELECT invset.is_sticker_bit AS 'is_sticker_bit', invset.total_amount,(CASE WHEN (invset.total_amount <> 0) THEN invset.total_amount-invset.price ELSE 0 END)AS 'transportation_charges'  , invset.iscpuiid ,`invset`.`isid` AS `isid`,`invset`.`isvid` AS `isvid`,variation.name AS variation_name,`invset`.`iscid` AS `iscid`,`invset`.`iid` AS `iid`,`invset`.`venid` AS `venid`,`invset`.`product_rank` AS `product_rank`,`invset`.`name` AS `name`,coalesce(`isc`.`iscsid`,0) AS `iscsid`,`iscs`.`name` AS `container_status`,`invset`.`description` AS `description`,`invset`.`chkid` AS `chkid`,`chk`.`chktid` AS `chktid`,`invset`.`shipping_cost` AS `shipping_cost`,`invset`.`price` AS `price`,`invset`.`mrp` AS `mrp`,`invset`.`ships_in` AS `ships_in`,`invset`.`available_from` AS `available_from`,`invset`.`issid` AS `issid`,`invset`.`data` AS `data`,`invset`.`created_ts` AS `created_ts`,`invset`.`updated_ts`  AS `updated_ts`,'0' AS `stock`,'0' AS `quantity`,`vendor`.`name` AS `seller`,`variation`.`internal_code` AS `sku_code` , invset.isid_number ,invset.manufacturing_date,invset.expiry_date, invset.barcode, invset.rejected_qty, invset.rejected_reason, COALESCE(iii.comment, '')  as `comment`, `isc`.`container_number` , ws.name as  `section` , '' as available , '' as allocated , invset.physical_count  , invset.c_pcount , invset.waseid  , invset.c_damage_qty as damaged_qty , invset.damaged_reason, isc.jobwoid , variation.weight , (CASE WHEN  variation.weight > 0 THEN (CASE WHEN invset.c_actual > 0 THEN (invset.c_actual/COALESCE(invset.c_conversion_rate,1))*variation.weight ELSE (invset.c_initial/COALESCE(invset.c_conversion_rate,1))*variation.weight END) ELSE 0 END)  as total_weight ,   invset.value_bit as value_bit , '' as dimensions, invset.packing_remarks, invset.weight as inv_weight from (((((" . SystemTables::$inventory_set . " `invset` join `inventory_set_variations` `variation`  on(((`variation`.`isvid` = `invset`.`isvid` and variation.company_id = '::company') and (`variation`.`isvsid` <> 3)))) left join `vendor` on((`vendor`.`venid` = `invset`.`venid` and vendor.company_id ='::company'))) join `checkpoint_mapping` `chk` on((`chk`.`chkid` = `invset`.`chkid` and chk.company_id = '::company'))) left join `inventory_set_container` `isc` on((isc.iscsid != 3 and `isc`.`iscid` = `invset`.`iscid` and isc.company_id = '::company'))) left join `inventory_set_container_status` `iscs` on((`isc`.`iscsid` = `iscs`.`iscsid`)))  LEFT JOIN warehouse_section ws ON(ws.waseid = invset.waseid) LEFT JOIN inventory_set_consignment_purchase_item iii ON(iii.iscpuiid = invset.iscpuiid) where  invset.disposition!=3 AND invset.company_id = '::company' ";
                if (BaseConfig::$licence_id == 192 && $stock_check)
                {
                    $sql .= " AND invset.c_stock>0 ";
                }
                $sql .= " group by ";
                if ($grouping)
                {
                    if ($check_custom_qty)
                    {
                        $sql .= " (CASE WHEN variation.is_quantity_calculate_enable > 0 THEN invset.isid ELSE  `invset`.`iscpuiid` END)";
                    }
                    else
                    {
                        $sql .= " `invset`.`iscpuiid`";
                    }
                }
                else
                {
                    $sql .= "`invset`.`isid`";
                }
            }
            $res = $db->query($sql, array('::company' => BaseConfig::$company_id));
//            echo $db->getLastQuery();exit();
//            echo $db->getMysqlError();

            if (!$res)
            {
                return FALSE;
            }
            return TRUE;
        }

        public static function getWarehouseInventoryView($chkid = NULL)
        {
            $waid = 0;
            $warehouse = CheckPointManager::getCheckpoint($chkid);
            if ($warehouse)
            {
                $waid = $warehouse->getId();
            }

//            InventoryManager::availableStockAndStockValue($waid);
            $db = Rapidkart::getInstance()->getDB();
            $view_name = "warehouse_inventory_view_" . BaseConfig::$company_id;
            $sql = " CREATE OR REPLACE ALGORITHM = UNDEFINED DEFINER = root@localhost SQL SECURITY DEFINER  VIEW $view_name AS SELECT
  
  invsv.isvid,
  invsv.company_id,
  invsv.name AS item_name,
  invsv.internal_code,
  invsv.c_company AS company,
  invsv.c_compid AS compid,
  invsv.c_brand AS brand,
   invsv.c_bid AS brand_bid,
   invsv.created_ts AS created_ts,
   invsv.mrp AS variation_mrp,
   invsv.rack_code AS rack_code,
  
   GROUP_CONCAT(
    DISTINCT " . BaseConfig::DB_NAME . ".`invsv`.`c_category` SEPARATOR ','
  ) AS `category`,
  GROUP_CONCAT(
    DISTINCT " . BaseConfig::DB_NAME . ".`invsv`.`c_catid` SEPARATOR ','
  ) AS `category_id`,
    invsv.c_category AS category_name,
  
   '0' AS `quantity`,
   invsv.isvsid,
   i.chkid,
   i.waid,
   `invsv`.`iid` AS `item_id`
   , '0' AS `stock_value`,
   
0 as available_stock,
 0 as allocated_stock,
 0 as net_available,
 invsv.c_measurement as c_measurement
  
   
FROM
  inventory_set_variations invsv 
   LEFT JOIN " . SystemTables::$inventory_set . " i ON(i.company_id = '::company_id' and invsv.isvid = i.isvid)
  
    
  
  
WHERE
  invsv.isvid > 0 AND invsv.isvsid =1 AND invsv.isvid IS NOT NULL AND invsv.company_id = '::company_id' AND i.waid = '::waid' "
                    . " GROUP BY invsv.isvid ";

            $args = array("::company_id" => BaseConfig::$company_id, '::waid' => $waid);
            $res = $db->query($sql, $args);
            if (!$res)
            {
                return FALSE;
            }
            return TRUE;

//            $sql = "CREATE OR REPLACE ALGORITHM = UNDEFINED DEFINER = root@localhost  SQL SECURITY DEFINER VIEW $view_name AS SELECT group_concat(distinct `invset`.`isid` separator ',`') AS `isid`,`item`.`iid` AS `item_id`,`variation`.`name` AS `item_name`,`variation`.`internal_code` AS `internal_code`,`variation`.`default_venid` AS `default_venid`,`variation`.`c_vendor` AS `default_vendor`,`variation`.`isvsid` AS `variation_status`,`iss`.`name` AS `item_status_name`,`iss`.`issid` AS `status_id`,group_concat(distinct `invset`.`chkid` separator ',') AS `chkid`,group_concat(distinct `variation`.`isvid` separator ',') AS `isvid`,group_concat(distinct `category`.`name` separator ',') AS `category`,group_concat(distinct `category`.`catid` separator ',') AS `category_id`,\"0\" AS `quantity`,`variation`.`price` AS `price`, \"0\" AS `stock_value`,`brand`.`bid` AS `brand_bid`,`brand`.`name` AS `brand`,`company`.`name` AS `company`,`ismp`.`purchase_price` AS `purchase_price` from (((((((((`inventory_set_variations` `variation` join `item_item` `item` on(((`item`.`iid` = `variation`.`iid` and item.company_id = '::company') and (`item`.`istatusid` = 1)))) join `item_gi` `gi` on(((`gi`.`giid` = `item`.`giid` and gi.company_id = '::company') and (`gi`.`gisid` = 1)))) join `inventory_set` `invset` on((`variation`.`isvid` = `invset`.`isvid` and invset.company_id= '::company'))) left join `item_brand` `brand` on(((`brand`.`bid` = `gi`.`bid` and brand.company_id = '::company') and (`brand`.`bsid` = 1)))) left join `item_company` `company` on(((`company`.`compid` = `brand`.`compid` and company.company_id = '::company') and (`company`.`compsid` = 1)))) left join `item_gi_category` `gicat` on((`gicat`.`giid` = `gi`.`giid` and gicat.company_id = '::company'))) left join `category` on(((`category`.`catid` = `gicat`.`catid` and category.company_id = '::company') and (`category`.`catsid` = 1)))) left join `inventory_set_status` `iss` on((`invset`.`issid` = `iss`.`issid`))) left join inventory_set_max_price_view_" . BaseConfig::$company_id . " `ismp` on((`ismp`.`isvid` = `variation`.`isvid` and ismp.company_id = '::company'))) where variation.company_id = '::company' group by `variation`.`isvid` order by `variation`.`created_ts` desc";
        }

        public static function getOutletSpecificWarehouses($key = true)
        {
            $db = Rapidkart::getInstance()->getDB();
            $sql = " SELECT o.chkid as outlet_chkid , o.name as outlet_name , o.checkpoint_code as code , w.* FROM  " . SystemTables::DB_TBL_WAREHOUSE . " w JOIN outlet o ON(o.company_id = '::company' and o.outlsid != 3 and o.outlid = w.outlid) WHERE w.company_id = '::company' and w.wasid != 3 ";
            $res = $db->query($sql, array('::company' => BaseConfig::$company_id));

            if (!$res || $db->resultNumRows($res) < 1)
            {
                return FALSE;
            }
            $ret = array();
            while ($row = $db->fetchObject($res))
            {
                if ($key)
                {
                    $ret[$row->outlet_chkid] = $row;
                }
                else
                {
                    $ret[] = $row;
                }
            }
            return $ret;
        }

        public static function updateStateofCheckPoint($chkid, $stid)
        {
            $db = Rapidkart::getInstance()->getDB();
            $sql = "UPDATE " . SystemTables::DB_TBL_CHECKPOINT_MAPPING . " SET stid = '::stid' WHERE chkid = '::chkid' and company_id = '::company' ";
            $args = array('::stid' => $stid, '::chkid' => $chkid, '::company' => BaseConfig::$company_id);
            $res = $db->query($sql, $args);
            return $res ? TRUE : FALSE;
        }

        public static function getStatesofCheckPoint($stid = array(), $user_basis = false)
        {
            $db = Rapidkart::getInstance()->getDB();
            $sql = "SELECT DISTINCT c.stid , s.state , GROUP_CONCAT(c.waid) as waids , GROUP_CONCAT(c.outlid) as outlids , GROUP_CONCAT(CASE WHEN c.waid > 0 THEN  c.chkid END) as warehouse_chkids , GROUP_CONCAT(CASE WHEN c.outlid > 0 THEN  c.chkid END) as outlet_chkids FROM " . SystemTables::DB_TBL_CHECKPOINT_MAPPING . " c LEFT JOIN state s ON(s.stid = c.stid)  WHERE c.company_id = '::company'   ";
            $args = array('::company' => BaseConfig::$company_id);
            if ($stid)
            {
                $sql .= " AND c.stid IN(::stid) ";
                $args['::stid'] = implode(",", $stid);
            }
            if ($user_basis && SystemConfig::getUser()->getIsAdmin() != 1)
            {
                $sql .= " AND  (CASE WHEN c.outlid > 0 THEN c.outlid IN(SELECT outlid FROM outlet_user_mapping WHERE uid ='::uid') ELSE c.waid IN(SELECT waid FROM warehouse_user_mapping WHERE uid = '::uid' ) END)  ";
//                $sql .= " AND  (CASE WHEN c.outlid > 0 THEN c.outlid IN(SELECT outlid FROM outlet_user_mapping WHERE uid ='::uid' AND outlid IN(SELECT outlid from outlet where outlsid = '1' )) ELSE c.waid IN(SELECT waid FROM warehouse_user_mapping WHERE uid = '::uid' AND waid IN(SELECT waid from warehouse where wasid = '1' and type <> 4) ) END)  ";
                $args['::uid'] = Session::loggedInUid();
            }
            else
            {
//                $sql .= " AND (CASE WHEN c.outlid > 0 THEN c.outlid IN(SELECT outlid from outlet where outlsid = '1' ) ELSE c.waid IN(SELECT waid from warehouse where wasid = '1' and type <> 4) END) ";
            }
            $sql .= " GROUP BY c.stid ";

            $res = $db->query($sql, $args);
            if (!$res || $db->resultNumRows($res) < 1)
            {
                return FALSE;
            }
            $ret = array();
            while ($row = $db->fetchObject($res))
            {
                if ($row->stid > 0)
                {
                    $ret[$row->stid] = $row;
                }
            }
            return $ret;
        }

        public static function getWarehoueWithSections($isvid)
        {
            $db = Rapidkart::getInstance()->getDB();
            $sql = " SELECT i.waid , ws.waseid ,  i.warehouse , ws.name , i.data, i.barcode FROM " . SystemTables::$inventory_set . " i  LEFT JOIN warehouse_section ws on(ws.waseid = i.waseid) WHERE i.company_id = '::company' and isvid  = '::isvid' and (c_stock - (c_assigned - c_executed)) > 0 ";
            $args = array('::company' => BaseConfig::$company_id, '::isvid' => $isvid);
            $res = $db->query($sql, $args);
            if (!$res || $db->resultNumRows($res) < 1)
            {
                return FALSE;
            }
            $warehouse = array();
            $section = array();
            $remarks = array();
            $barcode = array();
            while ($row = $db->fetchObject($res))
            {
                if ($row->waid > 0)
                {
                    $warehouse [$row->waid] = $row->warehouse;
                }
                if ($row->waseid > 0)
                {
                    $section [$row->waseid] = $row->name;
                }
                if (strlen($row->barcode) > 0)
                {
                    $barcode[$row->barcode] = $row->barcode;
                }
                if (strlen($row->data) > 0)
                {
                    $re = json_decode($row->data, TRUE);
                    if (is_array($re) && !empty($re) && isset($re['remarks']) && strlen($re['remarks']))
                    {
                        $remarks[] = $re['remarks'];
                    }
                }
            }
            return array("warehouse" => $warehouse, "section" => $section, "remarks" => $remarks, 'barcode' => $barcode);
        }

        public static function updateDefaultWarehouse($id)
        {
            $db = Rapidkart::getInstance()->getDB();
            $sql = " UPDATE " . SystemTables::DB_TBL_WAREHOUSE . " SET is_default = 0 WHERE company_id = '::company' ";
            $args = array('::company' => BaseConfig::$company_id);
            $res = $db->query($sql, $args);
            if (!$res)
            {
                return FALSE;
            }
            $sql1 = " UPDATE " . SystemTables::DB_TBL_WAREHOUSE . " SET is_default = 1 WHERE company_id = '::company' and waid = '::waid' ";
            $args['::waid'] = $id;
            $res1 = $db->query($sql1, $args);
            return $res1 ? TRUE : FALSE;
        }

        public static function updateSecondaryWarehouse($id)
        {
            $db = Rapidkart::getInstance()->getDB();
            $sql = " UPDATE " . SystemTables::DB_TBL_WAREHOUSE . " SET is_secondary = 0 WHERE company_id = '::company' ";
            $args = array('::company' => BaseConfig::$company_id);
            $res = $db->query($sql, $args);
            if (!$res)
            {
                return FALSE;
            }
            $sql1 = " UPDATE " . SystemTables::DB_TBL_WAREHOUSE . " SET is_secondary = 1 WHERE company_id = '::company' and waid = '::waid' ";
            $args['::waid'] = $id;
            $res1 = $db->query($sql1, $args);
            return $res1 ? TRUE : FALSE;
        }

        public static function getWarehouseDefault($id)
        {
            if ($id > 0)
            {
                return "<i class='fa fa-2x fa-check text-teal'></i>";
            }
            return "";
        }

        public static function warehouseExcelDownload($data)
        {
            if (isset($data['array']))
            {
                $array = json_decode($data['array'], TRUE);
            }
            $db = Rapidkart::getInstance()->getDB();

            $ret = array();
            if ($data['dispatch_setting'] == 1)
            {
                $sql = "SELECT chkoid FROM dispatch_order WHERE dioid IN(::dioid) AND company_id = '::company'";
                $res = $db->query($sql, array('::dioid' => implode(",", $array), '::company' => BaseConfig::$company_id));
                while ($row = $db->fetchObject($res))
                {
                    $ret[] = $row->chkoid;
                }
            }
            else
            {
                $ret = $array;
            }
//            $sql1 = "SELECT ioi.isvid as 'Id', ioi.c_name as 'Item Name' , SUM(ioi.quantity-ioi.c_executed_qty) AS Quantity, c.c_customer_name AS Customer , ioi.c_conversion_rate , ioi.c_measurement ,  GROUP_CONCAT( ioi.chkoid,  '(', ioi.quantity - ioi.c_executed_qty,  ')' ) AS orders  , GROUP_CONCAT(c.c_customer_name,  '(', ioi.quantity - ioi.c_executed_qty,  ')' ) AS orders  ";
            $sql1 = " SELECT isv.pack1 as inner_packing, isv.pack2 as outer_packing, ioi.*, isv.name as c_name,c.*,inv.barcode,inv.isid_number,ws.name as location , COALESCE(cc.name) as category , inv.waseid , isv.c_iitid, isv.measured_qty as measured_qty, isv.measured_unit as mes_meaid,  isv.c_measured_measurement as measured_unit, m.conversion_rate as measured_conversion_rate,  m.name as unit_name, inv.variation_internal_code as sku ";
            $sql1 .= " FROM item_order_item ioi LEFT JOIN inventory_set_variations isv ON(isv.isvid = ioi.isvid) LEFT JOIN measurement m ON(m.meaid = isv.measured_unit)
                
                        JOIN checkpoint_order c ON (ioi.chkoid = c.chkoid) LEFT JOIN " . SystemTables::$inventory_set . " AS inv ON (ioi.isid=inv.isid) LEFT  JOIN checkpoint_order_category_type cc ON(cc.ocatid = c.category_type) LEFT JOIN warehouse_section AS ws ON(inv.waseid=ws.waseid)
                        WHERE ioi.oisid 
                        IN (1, 2) 
                        AND c.company_id =  '::company'
                        AND ioi.chkoid IN(::chkoid) ";
            if ($data['dispatch_setting'] == 1)
            {
                $sql1 .= " AND ioi.dispatched_qty-ioi.c_executed_qty > 0 ";
            }
            else
            {
                $sql1 .= " AND ioi.quantity-ioi.c_executed_qty > 0 ";
            }
            $sql1 .= " AND ioi.chkid = '::chkid' ";

            $result = $db->query($sql1, array('::company' => BaseConfig::$company_id, '::chkoid' => implode(",", $ret), '::chkid' => $data['chkid']));
//echo $db->getLastQuery();
//echo $db->getMysqlError();
//hprint($result);
//die;

            if (!$result)
            {
                ScreenMessage::setMessage("Fail to Download", ScreenMessage::MESSAGE_TYPE_ERROR);
                System::redirectInternal("my-warehouse/view/1");
            }
            $headers = $result->fetch_fields();
            $photo_show = getSettings("IS_PICK_LIST_VARIATION_PHOTO_SHOW") ? 1 : 0;
            $pack1_enable = getSettings('IS_PACKAGE_QTY_1_ENABLE') ? 1 : 0;
            $pack2_enable = getSettings('IS_PACKAGE_QTY_2_ENABLE') ? 1 : 0;
            $head = array("Item Name", 'Barcode', "Blocked Qty", "Order", "Customer", "Batch Details", "Batch Id", "Location");
            if ($pack1_enable)
            {
                array_push($head, "Inner Packing");
            }
            if ($pack2_enable)
            {
                array_push($head, "Outer Packing");
            }
            array_push($head, "SKU Code", "Remarks", "Delivery Date");
            if ($photo_show)
            {
                array_unshift($head, "Image");
            }

            $category_settings = 0;
            if (getSettings("SHOW_CATEGORY_TYPE"))
            {
                $category_settings = 1;
                $head[] = 'Category';
            }

            $array = array();

            while ($row = $db->fetchObject($result))
            {

                if (!isset($array[$row->isvid]))
                {
                    $array[$row->isvid] = array();
                    if ($photo_show)
                    {
                        $array[$row->isvid]['image'] = '';
                    }

                    $array[$row->isvid]["name"] = str_replace('"', "", $row->c_name);
                    $array[$row->isvid]['barcode'] = $row->barcode;
                    $array[$row->isvid]["qty"] = 0;
                    $array[$row->isvid]['orders'] = array();
                    $array[$row->isvid]['customers'] = array();
                    $array[$row->isvid]['batch_detail_array'] = array();
                    $array[$row->isvid]['batch_id_array'] = array();
                    $array[$row->isvid]['location_array'] = array();
                    if ($pack1_enable)
                    {
                        $array[$row->isvid]['inner_packing'] = $row->inner_packing;
                    }
                    if ($pack2_enable)
                    {
                        $array[$row->isvid]['outer_packing'] = $row->outer_packing;
                    }
                    $array[$row->isvid]['sku'] = $row->sku;
                    $array[$row->isvid]['checkpoint_order_remarks'] = array();
                    $array[$row->isvid]['delivery_date'] = array();

                    if ($category_settings)
                    {
                        $category_array[$row->isvid]['categories'][$row->category] = $row->category;
                    }
                }
                else
                {
                    if ($category_settings)
                    {
                        if (!isset($category_array[$row->isvid]['categories'][$row->category]))
                        {
                            $category_array[$row->isvid]['categories'][$row->category] = $row->category;
                        }
                    }
                }
                // Order Info
                if (!isset($array[$row->isvid]['orders'][$row->chkoid]))
                {
                    $order = new CheckPointOrder($row->chkoid);
                    $array[$row->isvid]['orders'][$row->chkoid] = array('id' => '', 'qty' => 0, 'm_name' => $row->c_measurement, 'assignee' => $order->getCAssignedUidName(), 'date' => $order->getTakenDate());
                }

                $qty_val = ($row->quantity - $row->c_executed_qty) / $row->c_conversion_rate;

                $array[$row->isvid]['orders'][$row->chkoid]['id'] = CheckPointOrderManager::getOrderUrl($row->chkoid);
                $array[$row->isvid]['orders'][$row->chkoid]['qty'] += $qty_val;

                // Customer Info
                if (!isset($array[$row->isvid]['customers'][$row->cuid]))
                {
                    $array[$row->isvid]['customers'][$row->cuid] = array('id' => '', 'qty' => 0, 'm_name' => $row->c_measurement);
                }

                $array[$row->isvid]['customers'][$row->cuid]['id'] = $row->c_customer_name;
                $array[$row->isvid]['customers'][$row->cuid]['qty'] += $qty_val;

//                
                if (!isset($array[$row->isvid]['batch_detail_array'][$row->barcode]))
                {
                    $array[$row->isvid]['batch_detail_array'][$row->barcode] = array('id' => '', 'qty' => 0, 'm_name' => $row->c_measurement);
                }
                $array[$row->isvid]['batch_detail_array'][$row->barcode]['id'] = $row->barcode;
                $array[$row->isvid]['batch_detail_array'][$row->barcode]['qty'] += $qty_val;

                //batch_id_array
                if (!isset($array[$row->isvid]['batch_id_array'][$row->isid_number]))
                {
                    $array[$row->isvid]['batch_id_array'][$row->isid_number] = array('id' => '', 'qty' => 0, 'm_name' => $row->c_measurement);
                }


                $array[$row->isvid]['batch_id_array'][$row->isid_number]['id'] = $row->isid_number;
                $array[$row->isvid]['batch_id_array'][$row->isid_number]['qty'] += $qty_val;

                //location_array

                if (!isset($array[$row->isvid]['location_array'][$row->waseid]))
                {
                    $array[$row->isvid]['location_array'][$row->waseid] = array('id' => '', 'qty' => 0, 'm_name' => $row->c_measurement);
                }
                $array[$row->isvid]['location_array'][$row->waseid]['id'] = $row->location;
                $array[$row->isvid]['location_array'][$row->waseid]['qty'] += $qty_val;
                $array[$row->isvid]['qty'] += $row->quantity - $row->c_executed_qty;

                $array[$row->isvid]['m_qty'] = ($row->measured_qty > 0 && $row->mes_meaid > 0 ? round(($row->measured_qty / $row->measured_conversion_rate), 4) : 0);
                $array[$row->isvid]['m_unit'] = $row->measured_unit;
                if (!isset($array[$row->isvid]['delivery_date'][$row->delivery_date]))
                {
                    $array[$row->isvid]['delivery_date'][$row->delivery_date] = array('m_name' => Utility::getDateFormat($row->delivery_date));
                }
                $array[$row->isvid]['delivery_date'][$row->delivery_date]['m_name'] = Utility::getDateFormat($row->delivery_date);

                if (!isset($array[$row->isvid]['checkpoint_order_remarks'][$row->checkpoint_order_remarks]))
                {
                    $array[$row->isvid]['checkpoint_order_remarks'][$row->checkpoint_order_remarks] = array('m_name' => $row->checkpoint_order_remarks);
                }
            }


            $fp = fopen('php://output', 'w');
            if ($fp && $result)
            {

                header('Content-Type: text/csv');
                header('Content-Disposition: attachment; filename="warehouse_export.csv"');
                header('Pragma: no-cache');
                header('Expires: 0');

                fwrite($fp, implode(',', $head) . "\r\n");

                foreach ($array as $key => $a)
                {

                    $measurement_name = "PCS";
                    $cr = 1;
                    $variation = new InventorySetVariation($key);
                    if ($photo_show)
                    {
                        $images = $variation->loadImages();
                        $image = "";

                        if ($images)
                        {
                            $img = reset($images);
                            if (strlen($img->image) > 0)
                            {
                                $image = Utility::ifImageExists("variations/medium/", $img->image);
                                $image = BaseConfig::$domain_name . $image;
                            }
                            elseif (strlen($img->public_link) > 0)
                            {
                                $image = $img->public_link;
                            }
                        }
                        else
                        {
                            $img = $variation->getItemDetails()->getImage();
                            if ($img):
                                $image = Utility::ifImageExists("item/medium/", $img->image);
                                $image = BaseConfig::$domain_name . $image;
                            endif;
                        }
                        $a['image'] = $image;
                    }
                    $qty = $a['qty'];

                    if ($variation->getMeaid() > 0)
                    {
                        $m = new Measurement($variation->getMeaid());
                        $measurement_name = $m->getName();
                        $cr = $m->getConversionRate();
                    }
                    $a['qty'] = round($qty / $cr, 4) . " " . $measurement_name . "(" . $a['qty'] * $a['m_qty'] . " " . $a['m_unit'] . ")";

                    $orders = $a['orders'];
                    $order_str = '';
                    if ($orders)
                    {
                        foreach ($orders as $order)
                        {
                            $order_q = round($order['qty'], 4);
                            $append_str = "";
                            if ($a['m_qty'] > 0)
                            {
                                $append_str .= " ( " . $order_q * $a['m_qty'] . " " . $a['m_unit'] . "))";
                            }

                            $order_q .= " " . $order['m_name'] . ' ' . $append_str;
                            $order_str .= $order['id'] . '(Qty: ' . $order_q . ', Date: ' . Utility::getDateFormat($order['date']) . ', Assignee: ' . $order['assignee'] . ')' . ", ";
                        }
                    }
                    $order_str = rtrim($order_str, ", ");
                    $a['orders'] = $order_str;

                    $orders = $a['customers'];
                    $order_str = '';
                    if ($orders)
                    {
                        foreach ($orders as $order)
                        {
                            $order_q = round($order['qty'], 4);
                            $append_str = "";
                            if ($a['m_qty'] > 0)
                            {
                                $append_str .= " ( " . $order_q * $a['m_qty'] . " " . $a['m_unit'] . ")";
                            }
                            $order_q .= " " . $order['m_name'] . ' ' . $append_str;
                            $order_str .= $order['id'] . '(' . $order_q . ')';
                        }
                    }
                    $order_str = rtrim($order_str, ", ");
                    $a['customers'] = $order_str;

                    $orders = $a['batch_detail_array'];
                    $order_str = '';
                    if ($orders)
                    {
                        foreach ($orders as $order)
                        {
                            $order_q = round($order['qty'], 4);
                            $append_str = "";
                            if ($a['m_qty'] > 0)
                            {
                                $append_str .= " ( " . $order_q * $a['m_qty'] . " " . $a['m_unit'] . ")";
                            }
                            $order_q .= " " . $order['m_name'] . '' . $append_str;
                            $order_str .= $order['id'] . '(' . $order_q . ')';
                        }
                    }
                    $order_str = rtrim($order_str, ", ");
                    $a['batch_detail_array'] = $order_str;

                    $orders = $a['batch_id_array'];
                    $order_str = '';
                    if ($orders)
                    {
                        foreach ($orders as $order)
                        {
                            $order_q = round($order['qty'], 4);
                            $append_str = "";
                            if ($a['m_qty'] > 0)
                            {
                                $append_str .= " ( " . $order_q * $a['m_qty'] . " " . $a['m_unit'] . ")";
                            }
                            $order_q .= " " . $order['m_name'] . ' ' . $append_str;
                            $order_str .= $order['id'] . '(' . $order_q . ' ' . ')';
                        }
                    }
                    $order_str = rtrim($order_str, ", ");
                    $a['batch_id_array'] = $order_str;

                    $orders = $a['location_array'];
                    $order_str = '';
                    if ($orders)
                    {
                        foreach ($orders as $order)
                        {
                            $order_q = round($order['qty'], 4);
                            $append_str = "";
                            if ($a['m_qty'] > 0)
                            {
                                $append_str .= " ( " . $order_q * $a['m_qty'] . " " . $a['m_unit'] . ")";
                            }
                            $order_q .= " " . $order['m_name'] . ' ' . $append_str;
                            $order_str .= $order['id'] . '(' . $order_q . ')';
                        }
                    }
                    $order_str = rtrim($order_str, ", ");
                    $a['location_array'] = $order_str;

                    if ($category_settings)
                    {
                        if (isset($category_array[$key]['categories']))
                        {
                            $a['category'] = implode(', ', array_values($category_array[$key]['categories']));
                        }
                    }
                    if (isset($a['m_qty']) && $a['m_qty'] > 0)
                    {
                        unset($a['m_qty']);
                    }

                    if (isset($a['m_unit']))
                    {
                        unset($a['m_unit']);
                    }

                    $order_remarks = $a['checkpoint_order_remarks'];
                    $order_strs = '';
                    $order_qs = '';
                    if ($order_remarks)
                    {
                        foreach ($order_remarks as $order_remark)
                        {
                            $order_qs .= $order_remark['m_name'] . ' ' . ",";
                            $order_strs .= '(' . $order_qs . ')' . ", ";
                        }
                    }
                    $order_strss = rtrim($order_qs, ", ");
                    $a['checkpoint_order_remarks'] = $order_strss;

                    $delivery_date = $a['delivery_date'];
                    $order_date = '';
                    $order_d = '';
                    if ($delivery_date)
                    {
                        foreach ($delivery_date as $d)
                        {
                            $order_d .= $d['m_name'] . ' ' . ",";
                            $order_date .= '(' . $order_d . ')' . ", ";
                        }
                    }
                    $order_dates = rtrim($order_d, ", ");
                    $a['delivery_date'] = $order_dates;
                    unset($a['m_qty']);
                    unset($a['m_unit']);
                    fputcsv($fp, $a);
                }
                exit;
            }
        }

        public static function warehouseGetDetails($data)
        {
            if (isset($data['array']))
            {
                $array = json_decode($data['array'], TRUE);
            }
            if (!isset($array))
            {
                return false;
            }
            if (is_array($array) && empty($array))
            {
                return false;
            }
            $db = Rapidkart::getInstance()->getDB();

            $ret = array();
            if ($data['dispatch_setting'] == 1)
            {
                $sql = "SELECT chkoid FROM dispatch_order WHERE dioid IN(::dioid) AND company_id = '::company'";
                $res = $db->query($sql, array('::dioid' => implode(",", $array), '::company' => BaseConfig::$company_id));
                while ($row = $db->fetchObject($res))
                {
                    $ret[] = $row->chkoid;
                }
            }
            else
            {
                $ret = $array;
            }
//            $sql1 = "SELECT ioi.isvid as 'Id', ioi.c_name as 'Item Name' , SUM(ioi.quantity-ioi.c_executed_qty) AS Quantity, c.c_customer_name AS Customer , ioi.c_conversion_rate , ioi.c_measurement ,  GROUP_CONCAT( ioi.chkoid,  '(', ioi.quantity - ioi.c_executed_qty,  ')' ) AS orders  , GROUP_CONCAT(c.c_customer_name,  '(', ioi.quantity - ioi.c_executed_qty,  ')' ) AS orders  ";
//            $sql1 = " SELECT *  "; //Commented
//            $sql1 .= " FROM item_order_item ioi
//                        JOIN checkpoint_order c ON (ioi.chkoid = c.chkoid) 
//                        WHERE ioi.oisid
//                        IN (1, 2) 
//                        AND c.company_id =  '::company'
//                        AND ioi.chkoid IN(::chkoid) AND ioi.quantity-ioi.c_executed_qty > 0 ";
//            $sql1 .= " FROM item_order_item ioi
//                        JOIN checkpoint_order c ON (ioi.chkoid = c.chkoid) JOIN ".SystemTables::$inventory_set." AS inv ON (ioi.isid=inv.isid) LEFT JOIN warehouse_section AS ws ON(inv.waseid=ws.waseid)
//                        WHERE ioi.oisid 
//                        IN (1, 2) 
//                        AND c.company_id =  '::company'
//                        AND ioi.chkoid IN(::chkoid) AND ioi.quantity-ioi.c_executed_qty > 0 ";


            $sql1 = " SELECT isv.pack1 as inner_packing, isv.pack2 as outer_packing, ioi.* , isv.name as c_name , c.*,inv.barcode,inv.isid_number,ws.name as location , COALESCE(cc.name , '') as category , inv.waseid , inv.variation_internal_code as sku, isv.c_iitid, isv.measured_qty as measured_qty, isv.measured_unit as mes_meaid,  isv.c_measured_measurement as measured_unit, m.conversion_rate as measured_conversion_rate,  m.name as unit_name  ";
            $sql1 .= " FROM item_order_item ioi LEFT JOIN inventory_set_variations isv ON(isv.isvid = ioi.isvid) LEFT JOIN measurement m ON(m.meaid = isv.measured_unit) 
                        JOIN checkpoint_order c ON (ioi.chkoid = c.chkoid) LEFT JOIN " . SystemTables::$inventory_set . " AS inv ON (ioi.isid=inv.isid) LEFT JOIN checkpoint_order_category_type cc ON(cc.ocatid = c.category_type) LEFT JOIN warehouse_section AS ws ON(inv.waseid=ws.waseid)
                        WHERE ioi.oisid 
                        IN (1, 2) 
                        AND c.company_id =  '::company'
                        AND ioi.chkoid IN(::chkoid) ";
            if ($data['dispatch_setting'] == 1)
            {
                $sql1 .= " AND ioi.dispatched_qty-ioi.c_executed_qty > 0 ";
            }
            else
            {
                $sql1 .= " AND ioi.quantity-ioi.c_executed_qty > 0 ";
            }
            $sql1 .= " AND ioi.chkid = '::chkid'";

            $result = $db->query($sql1, array('::company' => BaseConfig::$company_id, '::chkoid' => implode(",", $ret), '::chkid' => $data['chkid']));
            if (!$result)
            {
                return false;
            }
            $category_settings = 0;
            if (getSettings("SHOW_CATEGORY_TYPE"))
            {
                $category_settings = 1;
            }

            $array = array();

            while ($row = $db->fetchObject($result))
            {


                if (!isset($array[$row->isvid]))
                {
                    $array[$row->isvid] = array("name" => $row->c_name, 'barcode' => $row->barcode, "qty" => 0, 'orders' => array(), 'customers' => array(), 'batch_detail_array' => array(), 'batch_id_array' => array(), 'location_array' => array(), 'sku' => $row->sku, 'delivery_date' => array());

                    if ($category_settings)
                    {
                        $category_array[$row->isvid]['categories'][$row->category] = $row->category;
                    }
                }
                else
                {
                    if ($category_settings)
                    {
                        if (!isset($category_array[$row->isvid]['categories'][$row->category]))
                        {
                            $category_array[$row->isvid]['categories'][$row->category] = $row->category;
                        }
                    }
                }
                // Order Info
                if (!isset($array[$row->isvid]['orders'][$row->chkoid]))
                {
                    $order = new CheckPointOrder($row->chkoid);
                    $array[$row->isvid]['orders'][$row->chkoid] = array('id' => '', 'qty' => 0, 'm_name' => $row->c_measurement, 'date' => $order->getTakenDate(), 'assignee' => $order->getCAssignedUidName());
                }
                $qty_val = ($row->quantity - $row->c_executed_qty) / $row->c_conversion_rate;
                $array[$row->isvid]['orders'][$row->chkoid]['id'] = CheckPointOrderManager::getOrderUrl($row->chkoid);
                $array[$row->isvid]['orders'][$row->chkoid]['qty'] += $qty_val;

                // Customer Info
                if (!isset($array[$row->isvid]['customers'][$row->cuid]))
                {
                    $array[$row->isvid]['customers'][$row->cuid] = array('id' => '', 'qty' => 0, 'm_name' => $row->c_measurement);
                }
                $array[$row->isvid]['customers'][$row->cuid]['id'] = $row->c_customer_name;
                $array[$row->isvid]['customers'][$row->cuid]['qty'] += $qty_val;

//                
                if (!isset($array[$row->isvid]['batch_detail_array'][$row->barcode]))
                {
                    $array[$row->isvid]['batch_detail_array'][$row->barcode] = array('id' => '', 'qty' => 0, 'm_name' => $row->c_measurement);
                }
                $array[$row->isvid]['batch_detail_array'][$row->barcode]['id'] = $row->barcode;
                $array[$row->isvid]['batch_detail_array'][$row->barcode]['qty'] += $qty_val;
                //batch_id_array
                if (!isset($array[$row->isvid]['batch_id_array'][$row->isid_number]))
                {
                    $array[$row->isvid]['batch_id_array'][$row->isid_number] = array('id' => '', 'qty' => 0, 'm_name' => $row->c_measurement);
                }
                $array[$row->isvid]['batch_id_array'][$row->isid_number]['id'] = $row->isid_number;
                $array[$row->isvid]['batch_id_array'][$row->isid_number]['qty'] += $qty_val;

                //location_array

                if (!isset($array[$row->isvid]['location_array'][$row->waseid]))
                {
                    $array[$row->isvid]['location_array'][$row->waseid] = array('id' => '', 'qty' => 0, 'm_name' => $row->c_measurement);
                }

                if (!isset($array[$row->isvid]['checkpoint_order_remarks'][$row->checkpoint_order_remarks]))
                {
                    $array[$row->isvid]['checkpoint_order_remarks'][$row->checkpoint_order_remarks] = array('m_name' => $row->checkpoint_order_remarks);
                }
                if (!isset($array[$row->isvid]['delivery_date'][$row->delivery_date]))
                {
                    $array[$row->isvid]['delivery_date'][$row->delivery_date] = array('m_name' => Utility::getDateFormat($row->delivery_date));
                }

                $array[$row->isvid]['delivery_date'][$row->delivery_date]['m_name'] = Utility::getDateFormat($row->delivery_date);

                $array[$row->isvid]['location_array'][$row->waseid]['id'] = $row->location;
                $array[$row->isvid]['location_array'][$row->waseid]['qty'] += $qty_val;
                $array[$row->isvid]['qty'] += $row->quantity - $row->c_executed_qty;

                $array[$row->isvid]['m_qty'] = ($row->measured_qty > 0 && $row->mes_meaid > 0 ? round(($row->measured_qty / $row->measured_conversion_rate), 4) : 0);
                $array[$row->isvid]['m_unit'] = $row->measured_unit;
                $array[$row->isvid]['inner_packing'] = $row->inner_packing;
                $array[$row->isvid]['outer_packing'] = $row->outer_packing;
            }

            return array('array' => $array, 'category' => $category_array);
        }

        public static function warehouseGetDetails_backup($data)
        {
            if (isset($data['array']))
            {
                $array = json_decode($data['array'], TRUE);
            }
            $db = Rapidkart::getInstance()->getDB();

//            $sql1 = "SELECT ioi.isvid as 'Id', ioi.c_name as 'Item Name' , SUM(ioi.quantity-ioi.c_executed_qty) AS Quantity, c.c_customer_name AS Customer , ioi.c_conversion_rate , ioi.c_measurement ,  GROUP_CONCAT( ioi.chkoid,  '(', ioi.quantity - ioi.c_executed_qty,  ')' ) AS orders  , GROUP_CONCAT(c.c_customer_name,  '(', ioi.quantity - ioi.c_executed_qty,  ')' ) AS orders  ";
//            $sql1 = " SELECT *  "; //Commented
//            $sql1 .= " FROM item_order_item ioi
//                        JOIN checkpoint_order c ON (ioi.chkoid = c.chkoid) 
//                        WHERE ioi.oisid
//                        IN (1, 2) 
//                        AND c.company_id =  '::company'
//                        AND ioi.chkoid IN(::chkoid) AND ioi.quantity-ioi.c_executed_qty > 0 ";
//            $sql1 .= " FROM item_order_item ioi
//                        JOIN checkpoint_order c ON (ioi.chkoid = c.chkoid) JOIN ".SystemTables::$inventory_set." AS inv ON (ioi.isid=inv.isid) LEFT JOIN warehouse_section AS ws ON(inv.waseid=ws.waseid)
//                        WHERE ioi.oisid 
//                        IN (1, 2) 
//                        AND c.company_id =  '::company'
//                        AND ioi.chkoid IN(::chkoid) AND ioi.quantity-ioi.c_executed_qty > 0 ";


            $sql1 = " SELECT ioi.*,c.*,inv.barcode,inv.isid_number,ws.name as location  ";
            $sql1 .= " FROM item_order_item ioi
                        JOIN checkpoint_order c ON (ioi.chkoid = c.chkoid) JOIN " . SystemTables::$inventory_set . " AS inv ON (ioi.isid=inv.isid) LEFT JOIN warehouse_section AS ws ON(inv.waseid=ws.waseid)
                        WHERE ioi.oisid 
                        IN (1, 2) 
                        AND c.company_id =  '::company'
                        AND ioi.chkoid IN(::chkoid) AND ioi.quantity-ioi.c_executed_qty > 0 ";

            $result = $db->query($sql1, array('::company' => BaseConfig::$company_id, '::chkoid' => implode(",", $array)));
            if (!$result)
            {
                return false;
            }

            $array = array();
            while ($row = $db->fetchObject($result))
            {
                if (!isset($array[$row->isvid]))
                {
                    $array[$row->isvid] = array("name" => $row->c_name, "qty" => 0, "order" => CheckPointOrderManager::getOrderUrl($row->chkoid) . "(" . (($row->quantity - $row->c_executed_qty) / $row->c_conversion_rate) . " " . $row->c_measurement . ")", "customer" => $row->c_customer_name . "(" . (($row->quantity - $row->c_executed_qty) / $row->c_conversion_rate) . " " . $row->c_measurement . ")", "batch_details" => $row->barcode . "(" . (($row->quantity - $row->c_executed_qty) / $row->c_conversion_rate) . " " . $row->c_measurement . ")", "batch_Id" => $row->isid_number . "(" . (($row->quantity - $row->c_executed_qty) / $row->c_conversion_rate) . " " . $row->c_measurement . ")", "location" => $row->location . "(" . (($row->quantity - $row->c_executed_qty) / $row->c_conversion_rate) . " " . $row->c_measurement . ")");
                }
                else
                {
                    $array[$row->isvid]['order'] .= ", " . CheckPointOrderManager::getOrderUrl($row->chkoid) . "(" . (($row->quantity - $row->c_executed_qty) / $row->c_conversion_rate) . " " . $row->c_measurement . ")";
                    $array[$row->isvid]['customer'] .= ", " . $row->c_customer_name . "(" . (($row->quantity - $row->c_executed_qty) / $row->c_conversion_rate) . " " . $row->c_measurement . ")";
                    $array[$row->isvid]['batch_details'] .= ", " . $row->barcode . "(" . (($row->quantity - $row->c_executed_qty) / $row->c_conversion_rate) . " " . $row->c_measurement . ")";
                    $array[$row->isvid]['batch_Id'] .= ", " . $row->isid_number . "(" . (($row->quantity - $row->c_executed_qty) / $row->c_conversion_rate) . " " . $row->c_measurement . ")";
                    if (!empty($row->location)) $array[$row->isvid]['location'] .= ", " . $row->location . "(" . (($row->quantity - $row->c_executed_qty) / $row->c_conversion_rate) . " " . $row->c_measurement . ")";
                }
                $array[$row->isvid]['qty'] += $row->quantity - $row->c_executed_qty;
            }
            return $array;
        }

        public static function getPhysicalStockVerificationReportView($chkid, $attributeFilter = array())
        {
            $db = Rapidkart::getInstance()->getDB();
            $view_name = "physical_stock_verification_report_view_" . BaseConfig::$company_id;
            $sql = "CREATE OR REPLACE ALGORITHM = UNDEFINED DEFINER = root@localhost SQL SECURITY DEFINER VIEW  $view_name AS SELECT iscsl.isvid as isvid, isv.variation_number, 
            iscsl.chkid as chkid,iscsl.meaid as meaid,inv.variation_meaid_name as variation_meaid_name,inv.item_name AS item_name,inv.variation_name AS variation_name,iscsl.quantity AS quantity,iscsl.iscslotid AS iscslotid,iscsl.date AS date,iscsl.price AS price,iscsl.created_uid AS created_uid,iscsl.created_ts AS created_ts , inv.c_bid as bid , inv.c_brand as brand, svi.actual_stock as actual_stock, (svi.actual_stock - iscsl.quantity) as left_quantity FROM " . SystemTables::$inventory_set_closed_stock_log . " AS iscsl JOIN " . SystemTables::$inventory_set . " AS inv ON iscsl.isid = inv.isid join stock_verification_item svi ON(svi.stveriid = iscsl.other_reference) LEFT JOIN " . SystemTables::DB_TBL_INVENTORY_SET_VARIATIONS . " AS isv ON (isv.isvid = iscsl.isvid ) WHERE iscsl.isstid = '14' AND iscsl.chkid='::chkid' AND iscsl.company_id = '::company_id'";

            $args = array('::chkid' => $chkid, '::company_id' => BaseConfig::$company_id);
            $attributes = $attributeFilter;
            if (isset($attributes))
            {
                $total_count = 0;
                $str = " AND (";
                if (is_array($attributes) && !empty($attributes) && array_filter($attributes))
                {
                    foreach ($attributes as $attribute)
                    {
                        $total_count += 1;
                        $values = explode(",", $attribute['value']);
                        if ($values)
                        {
                            $v = reset($values);
                            $att_aid = new AttributeValue($v);
                            $aid = $att_aid->getAid();
                            $str .= " (aid = $aid AND avid IN(" . $attribute['value'] . ")) ";
                            $str .= " OR ";
                        }
                    }
                    $str = rtrim($str, " OR ");
                    $str .= ")";
                }
                if ($total_count > 0)
                {
                    $sql .= " AND iscsl.isvid IN(SELECT isvid FROM " . SystemTables::DB_TBL_INVENTORY_SET_VARIATIONS_ATTRIBUTE_ATTRIBUTE_VALUE . " WHERE 1 " . $str . " GROUP BY isvid having count(isvid) =  $total_count) ";
                }
            }

            $res = $db->query($sql, $args);

            if (!$res)
            {
                return FALSE;
                ScreenMessage::setMessage("Fail to Load that page", ScreenMessage::MESSAGE_TYPE_ERROR);
            }
            return TRUE;
        }

        public static function getStatusLabel($id, $row, $row_id)
        {
            $label = "";
            if ($id !== "")
            {
                switch ($id)
                {
                    case 1:
                        $label = "Credit";
                        break;
                    case 2:
                        $label = "Debit";
                        break;
                }
            }
            return $label;
        }

        public static function getCreatedUser($id)
        {
            $user = new AdminUser($id);
            return $user->getName();
        }

        public static function getQtyCallback($id, $row, $row_id)
        {
            if ($row['meaid'] > 0)
            {
                $m = new Measurement($row['meaid']);
                $id = ($id / $m->getConversionRate());

                $id = round($id, 4) . ' ' . $row['variation_meaid_name'];
            }
            else
            {
                $id = round($id, 4) . ' ' . (strlen($row['variation_meaid_name']) > 0 ? $row['variation_meaid_name'] : 'PCS');
            }
            return $id;
        }

        public static function getPhysicalStockAttributeReportFilter()
        {
            $db = Rapidkart::getInstance()->getDB();
            $sql = " SELECT * FROM  " . SystemTables::DB_TBL_PHYSICAL_STOCK_VERIFICATION_REPORT_ATTRIBUTE_FILTER . " WHERE company_id = '::company' ORDER BY value ";
            $args = array('::company' => BaseConfig::$company_id);

            $res = $db->query($sql, $args);
            //  echo $db->getLastQuery();
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

        public static function insertAttributeFilter($ret)
        {
            $db = Rapidkart::getInstance()->getDB();

            $sql = "DELETE FROM " . SystemTables::DB_TBL_PHYSICAL_STOCK_VERIFICATION_REPORT_ATTRIBUTE_FILTER . " WHERE company_id = '::company'";

            $res1 = $db->query($sql, array('::company' => BaseConfig::$company_id));

            if (!$res1)
            {
                return FALSE;
            }

            //Inserting record to stock attribute filter
            if (!empty($ret))
            {
                $sql1 = "INSERT INTO " . SystemTables::DB_TBL_PHYSICAL_STOCK_VERIFICATION_REPORT_ATTRIBUTE_FILTER . " (`aid`, `value`, `attribute_name`, `company_id`) VALUES" . implode(",", $ret);

                $res = $db->query($sql1);
                if (!$res)
                {
                    return FALSE;
                }
            }
            return TRUE;
        }

        public static function getStatusNameCallback($id, $row, $row_id)
        {
            $str = 'NA';
            if (isset($row['warehouse_id']) && $row['warehouse_id'] > 0)
            {
                $warehouse = new Warehouse($row['warehouse_id']);
                $str = self::getStatus($warehouse->getWasid());
            }
            return $str;
        }

        public static function getTypeNameCallback($id, $row, $row_id)
        {
            $str = 'NA';
            if (isset($row['warehouse_id']) && $row['warehouse_id'] > 0)
            {
                $warehouse = new Warehouse($row['warehouse_id']);
                switch ($warehouse->getType())
                {
                    case 1:
                        $str = "Raw Materials";
                        break;
                    case 2:
                        $str = "Finished Goods";
                        break;
                    case 4:
                        $str = "Display";
                        break;
                    default :
                        $str = "R/F/D";
                }
            }
            return $str;
        }

        public static function getAcknowledgmentVoucherItems($acvid)
        {
            $db = Rapidkart::getInstance()->getDB();
            $sql = "SELECT acviid FROM " . SystemTables::DB_TBL_ACKNOWLEDGEMENT_VOUCHER_INVOICES . " WHERE acvid = '::id'";
            $args = array('::id' => $acvid);
            $res = $db->query($sql, $args);
            if (!$res || $db->resultNumRows($res) < 1)
            {
                return false;
            }
            $ret = array();
            while ($row = $db->fetchObject($res))
            {
                $ret[$row->acviid] = new AcknowledgementVoucherInvoices($row->acviid);
            }
            return $ret;
        }

        public static function getAcknowledgmentVoucherDebitItems($acvid)
        {
            $db = Rapidkart::getInstance()->getDB();
            $sql = "SELECT acvidebid FROM " . SystemTables::DB_TBL_ACKNOWLEDGEMENT_VOUCHER_DEBIT_NOTES . " WHERE acvid = '::id'";
            $args = array('::id' => $acvid);
            $res = $db->query($sql, $args);
            if (!$res || $db->resultNumRows($res) < 1)
            {
                return false;
            }
            $ret = array();
            while ($row = $db->fetchObject($res))
            {
                $ret[$row->acvidebid] = new AcknowledgementVoucherDebitNotes($row->acvidebid);
            }
            return $ret;
        }

        public static function getWarehouseState()
        {
            $db = Rapidkart::getInstance()->getDB();

            $sql = "SELECT stid FROM " . SystemTables::DB_TBL_WAREHOUSE . " WHERE company_id = '::company'";

            $args = array('::company' => BaseConfig::$company_id);

            $rs = $db->query($sql, $args);

            if (!$rs)
            {
                return false;
            }

            $state = array();

            while ($row = $db->fetchObject($rs))
            {
                $state[$row->stid] = new State($row->stid);
            }

            return $state;
        }

        public static function isNameAvailable($name, $waid = NULL, $waseid = NULL)
        {
            $db = Rapidkart::getInstance()->getDB();
            $sql = "SELECT * FROM " . SystemTables::DB_TBL_WAREHOUSE_SECTION . " WHERE name = '::name' AND company_id = '::company_id' ";
            if (isset($waid))
            {
                $sql .= " AND waid = '::waid' ";
            }
            $args = array("::name" => $name, "::waid" => $waid, "::company_id" => BaseConfig::$company_id);
            if ($waseid)
            {
                $sql .= " AND waseid != '::waseid'";
                $args['::waseid'] = $waseid;
            }
            $res = $db->query($sql, $args);
            if (!$res || $db->resultNumRows($res) > 0)
            {
                return false;
            }
            else
            {
                return true;
            }
        }

        public static function getWarehouseAllocatedStockByIsvid($id, $row)
        {
            return '<a class="item-show-allocatedstock" title="Show Allocated Stock Details" href="javascript:void"  data-id="' . $row['isvid'] . '">' . round($id, 4) . '</a>';
        }

        public static function getUserAccessForWarehouse($waid)
        {
            $db = Rapidkart::getInstance()->getDB();
            $sql = "SELECT waid FROM " . SystemTables::DB_TBL_WAREHOUSE_USER_MAPPING . " WHERE waid = '::id' AND company_id = '::compid' AND uid = '::uid'";
            $args = array('::id' => $waid, '::compid' => BaseConfig::$company_id, '::uid' => Session::loggedInUid());
            $res = $db->query($sql, $args);
            echo $db->getMysqlError();

            if (!$res || $db->resultNumRows($res) < 1)
            {
                return false;
            }
            return true;
        }

        public static function getSecondaryWarehouse()
        {
            $db = Rapidkart::getInstance()->getDB();
            $sql = "SELECT waid, chkid FROM warehouse WHERE wasid = 1 AND is_secondary > 0 AND company_id = '::company'";
            $res = $db->query($sql, array('::company' => BaseConfig::$company_id));
            if (!$res || $db->resultNumRows($res) < 1)
            {
                self::$secondary_warehouse_chkid = 0;
                self::$secondary_warehouse_id = 0;
            }
            else
            {
                $row = $db->fetchObject($res);
                self::$secondary_warehouse_chkid = $row->chkid;
                self::$secondary_warehouse_id = $row->waid;
            }
        }

        public static function getDefaultWarehouse()
        {
            $db = Rapidkart::getInstance()->getDB();
            $sql = "SELECT waid FROM warehouse WHERE wasid = 1 AND is_default = 1 AND company_id = '::company'";
            $res = $db->query($sql, array('::company' => BaseConfig::$company_id));
            if (!$res || $db->resultNumRows($res) < 1)
            {
                $sql1 = "SELECT waid FROM warehouse WHERE wasid = 1 AND company_id = '::company' LIMIT 1";
                $res1 = $db->query($sql1, array('::company' => BaseConfig::$company_id));
                $row1 = $db->fetchObject($res1)->waid;
                return $row1;
            }
            $row = $db->fetchObject($res)->waid;
            return $row;
        }

        public static function getwarehouseValues($chkids)
        {
            $db = Rapidkart::getInstance()->getDB();
            if ($chkids <= 0)
            {
                return FALSE;
            }
            $args = array("::chkid" => $chkids, '::company' => BaseConfig::$company_id);

            $sql = " SELECT * FROM " . SystemTables::DB_TBL_WAREHOUSE
                    . " WHERE wasid = '1' AND chkid IN (::chkid) AND company_id = '::company' ORDER BY is_default ASC ";
            $res = $db->query($sql, $args);
            if (!$res || $db->resultNumRows($res) < 1)
            {
                return false;
            }
            $ret = array();
            $row = $db->fetchObject($res);
            $ret[] = $row;
            return $ret;
        }

        public static function getOrderStatusByChkoid($chkoid)
        {
            $db = Rapidkart::getInstance()->getDB();
            $sql = " SELECT COUNT(quantity) as count FROM checkpoint_order_item WHERE ((quantity - canceled_qty) > c_allocated) AND chkoid = '::chkoid' AND company_id = '::company' ";
            $args = array("::chkoid" => $chkoid, '::company' => BaseConfig::$company_id);
            $res = $db->query($sql, $args);
            if (!$res)
            {
                return false;
            }
            $row = $db->fetchObject($res);
            switch ($row->count)
            {
                case 0:
                    return '<label class="label label-success">Complete Order</label>';
                    break;
                default:
                    return '<label class="label label-danger">Partial Order</label>';
                    break;
            }
        }

        public static function getStockVerificationQuaterly($start, $end, $chkid)
        {
            $db = Rapidkart::getInstance()->getDB();
            $sql = "SELECT i.isvid , i.variation_name ";
            $i = 1;
            list($end_start_year, $end_end_year) = AccountReportManager::getFinancialYear("april", $end);
            $company_start_date = BaseConfig::$company_start_date;
            list($company_start_year, $company_end_year) = AccountReportManager::getFinancialYear("april", $company_start_date);
            while ($start <= $end)
            {
                list($_start_year, $_end_year) = AccountReportManager::getFinancialYear("april", $start);
                $quaters = self::getFinancialYearQuarters($_start_year);
                if ($quaters)
                {
                    foreach ($quaters as $quater)
                    {
                        $str = "";
                        $closing_str = '';
                        if ($i == 1)
                        {
                            $divide_start_quater = explode('-', $quater['start']);
                            $start_range = '0000-00-00'; // '1970-' . $divide_start_quater[1] . "-" . $divide_start_quater[2];
                            $divide_end_quater = explode('-', $quater['end']);
                            $end_range = date('Y', strtotime($quater['end'] . "-1 year")) . '-' . $divide_end_quater[1] . "-" . $divide_end_quater[2];
                            $end_range = date('Y-m-d', strtotime($quater['start'] . "-1 day"));
                            $str .= " AND DATE(date) BETWEEN  '$start_range' AND '$end_range'";

                            $closing_str .= " AND DATE(c.date) BETWEEN '" . $start_range . "' AND '" . $quater['end'] . "'";
                        }
                        else
                        {
                            $divide_start_quater = explode('-', $quater['start']);
                            $start_range = date('Y-m-d', strtotime($quater['start'] . "-1 year"));
                            $divide_end_quater = explode('-', $quater['end']);
                            $end_range = date('Y-m-d', strtotime($quater['end'] . "-1 year"));
                            $end_range = date('Y-m-d', strtotime($quater['start'] . "-1 day"));
                            $str .= " AND DATE(date) <= '$end_range'";

                            $closing_str .= "  AND DATE(c.date) <= '" . $quater['end'] . "'";
                        }
                        $sql .= " ,  SUM(CASE WHEN iscslotid = 1 $str THEN quantity WHEN iscslotid = 2 $str THEN 0-quantity END) as `" . $quater['start'] . "_opening`";
                        $sql .= " ,  SUM(CASE WHEN iscslotid = 1 $closing_str  THEN quantity WHEN iscslotid = 2 $closing_str THEN 0-quantity END) as `" . $quater['start'] . "_closing`";
                    }
                }

                $start = date('Y-m-d', strtotime($_end_year . " + 1 day"));
                $i++;
            }
            $sql .= " FROM " . SystemTables::$inventory_set_closed_stock_log . " c JOIN " . SystemTables::$inventory_set . " i ON(i.isid = c.isid AND c.company_id = '::company') WHERE i.disposition NOT IN(4,5) AND c.isvid = 100832 GROUP BY c.isvid";
            $args = array('::company' => BaseConfig::$company_id);
            $res = $db->query($sql);
            echo $db->getMysqlError();
            echo $db->getLastQuery();
        }

        public static function getFinancialYearQuarters($financialYearStart)
        {
            // Convert the financial year start date to a DateTime object
            $start = new DateTime($financialYearStart);
            $year = $start->format('Y');

            // Define the quarters based on the financial year
            $quarters = [
                'Q1' => [
                    'start' => new DateTime("$year-04-01"),
                    'end' => new DateTime("$year-06-30")
                ],
                'Q2' => [
                    'start' => new DateTime("$year-07-01"),
                    'end' => new DateTime("$year-09-30")
                ],
                'Q3' => [
                    'start' => new DateTime("$year-10-01"),
                    'end' => new DateTime("$year-12-31")
                ],
                'Q4' => [
                    'start' => new DateTime("$year-01-01"),
                    'end' => new DateTime("$year-03-31")
                ]
            ];

            // Adjust Q4 to the next calendar year
            $quarters['Q4']['start']->modify('+1 year');
            $quarters['Q4']['end']->modify('+1 year');

            // Format dates as strings for easier output
            foreach ($quarters as $key => $quarter)
            {
                $quarters[$key]['start'] = $quarter['start']->format('Y-m-d');
                $quarters[$key]['end'] = $quarter['end']->format('Y-m-d');
            }

            return $quarters;
        }

        public static function getWarehouseNameByWaid($waid)
        {
            $warehouse = new Warehouse($waid);
            return $warehouse->getName();
        }

        public static function getAllWarehouseByOutletid($outlid, $waid = NULL, $user_specific = false, $singleWarehouse = 0)
        {
            $db = Rapidkart::getInstance()->getDB();
            if ($outlid <= 0)
            {
                return FALSE;
            }
            $args = array("::outlid" => $outlid, '::company' => BaseConfig::$company_id);

            $sql = " SELECT waid FROM " . SystemTables::DB_TBL_WAREHOUSE
                    . " WHERE outlid = ::outlid AND company_id = '::company' ";
            if ($waid)
            {
                $sql .= " AND waid IN(::waid)";
                $args['::waid'] = $waid;
            }
            if ($user_specific)
            {
                $sql .= " AND waid IN(SELECT waid FROM   " . SystemTables::DB_TBL_WAREHOUSE_USER_MAPPING . " WHERE uid=" . Session::loggedInUid() . ")";
            }
            $sql .= " ORDER BY is_default DESC";
            $res = $db->query($sql, $args);

            if (!$res || $db->resultNumRows() < 1)
            {
                return false;
            }
            $ret = array();
            if ($singleWarehouse > 0)
            {
                $row = $db->fetchObject($res);
                $warehouse = new Warehouse($row->waid);
                $warehouse->loadExtra();
                $ret = $warehouse;
            }
            else
            {
                while ($row = $db->fetchObject($res))
                {
                    $warehouse = new Warehouse($row->waid);
                    $warehouse->loadExtra();
                    $ret[] = $warehouse;
                }
            }

            return $ret;
        }

        public static function updateDefaultWarehouseLocation($id, $waid)
        {
            $db = Rapidkart::getInstance()->getDB();

            $sql = "UPDATE " . SystemTables::DB_TBL_WAREHOUSE_SECTION . " 
        SET is_default = CASE WHEN is_default = 1 THEN 0 ELSE 1 END 
        WHERE waid = :waid AND company_id = :company AND waseid = :waseid";

            $args = array(':company' => BaseConfig::$company_id, ':waid' => $waid, ':waseid' => $id);

            $res = $db->query($sql, $args);

            return $res ? TRUE : FALSE;
        }
    }
    
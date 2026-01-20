<?php

    /**
     * Class that acts as a manager for all user sessions
     * 
     * @author Sohil Gupta
     * @since 20131210
     * @updated 20140623
     */
    class SessionsManager
    {

        /**
         * Invalidate all sessions for user sessions that have passed the session lifetime of the site 
         */
        public static function updateSessions()
        {
            $session_lifetime = Utility::variableGet("session_lifetime");
            $old_session_ts = time() - $session_lifetime;
            $old_session_dt = date("Y-m-d H:i:s", $old_session_ts);
            $sql = "UPDATE " . SystemTables::DB_TBL_USER_SESSION . " SET ussid='0' WHERE create_ts < '$old_session_dt'";

            $db = Rapidkart::getInstance()->getDB();
            return $db->query($sql);
        }

        public static function getAuserSessionId($uid = null)
        {
            $uid = $uid ? $uid : Session::loggedInUid();

            $db = Rapidkart::getInstance()->getDB();

            $sql = "SELECT usid FROM " . SystemTables::DB_TBL_USER_SESSION . " WHERE uid = '::uid' AND ussid = '::ussid' and company_id = '::company' ORDER BY updated_ts DESC LIMIT 1";
            $args = array(
                "::uid" => $uid,
                '::company' => BaseConfig::$company_id,
                "::ussid" => SystemTablesStatus::ADMIN_USER_SESSION_ACTIVE
            );

            $res = $db->query($sql, $args);

            if (!$res || $db->resultNumRows($res) < 1)
            {
                return false;
            }

            $row = $db->fetchObject($res);
            return $row->usid;
        }

        public static function getAuserSessionStatus($ussid, $row, $row_id)
        {
            $db = Rapidkart::getInstance()->getDB();

            $query = "SELECT * FROM " . SystemTables::DB_TBL_USER_SESSION_STATUS . " WHERE ussid = '::ussid'";
            $args = array("::ussid" => $ussid);

            $res = $db->query($query, $args);
            if (!$res || $db->resultNumRows($res) < 1)
            {
                return false;
            }

            $row = $db->fetchObject($res);
            $label = ($ussid == SystemTablesStatus::ADMIN_USER_SESSION_ACTIVE) ? "success" : "danger";

            return "<label class='label label-" . $label . "'>" . $row->name . "</label>";
        }

        public static function inactiveOtherSessions($uid, $sid)
        {
            $db = Rapidkart::getInstance()->getDB();
            $sql = "UPDATE " . SystemTables::DB_TBL_USER_SESSION . " SET ussid = 2 WHERE uid = '::uid' and sid != '::sid' ";
            $res = $db->query($sql, array('::company' => BaseConfig::$company_id, '::sid' => $sid, '::uid' => $uid));
            return $res ? TRUE : FALSE;
        }

    }
    
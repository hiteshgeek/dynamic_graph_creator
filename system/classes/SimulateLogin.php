<?php

/**
 * SimulateLogin - Static helper class for login operations
 * For development/testing purposes
 */
class SimulateLogin
{
    /**
     * Get user ID by email address
     *
     * @param string $email User email
     * @return int User ID or 0 if not found
     */
    public static function getUserIdByEmail($email)
    {
        $db = Rapidkart::getInstance()->getDB();
        $sql = "SELECT uid FROM " . SystemTables::DB_TBL_USER . " WHERE email = '::email' AND (ustatusid NOT IN (2,3) OR ustatusid IS NULL) LIMIT 1";
        $res = $db->query($sql, ['::email' => $email]);

        if ($res && $db->resultNumRows($res) > 0) {
            $row = $db->fetchObject($res);
            return (int) $row->uid;
        }
        return 0;
    }
}

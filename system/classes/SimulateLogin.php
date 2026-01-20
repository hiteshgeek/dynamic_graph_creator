<?php

/**
 * SimulateLogin - Static helper class for login bootstrap operations
 *
 * This class helps bootstrap login in DGC by setting up BaseConfig values
 * similar to how the live project does it via domain lookup in system.inc.php
 *
 * In the live project:
 * - BaseConfig::$licence_id is set from domain lookup (LicenceManager::checkDomainExists)
 * - BaseConfig::$company_id is set from authenticated user's company_id
 *
 * For DGC (since we don't have domain-based licensing), this class provides
 * methods to fetch user config data and set up BaseConfig before login.
 */
class SimulateLogin
{
    /**
     * Get user ID by email address (without requiring licence_id)
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

    /**
     * Get user config data by user ID (without requiring BaseConfig to be set)
     * Returns uid, company_id, and licid needed to bootstrap login
     *
     * @param int $uid User ID
     * @return object|null User config data or null if not found
     */
    public static function getUserConfigById($uid)
    {
        $db = Rapidkart::getInstance()->getDB();
        $sql = "SELECT uid, company_id, licid FROM " . SystemTables::DB_TBL_USER . " WHERE uid = '::uid' AND (ustatusid NOT IN (2,3) OR ustatusid IS NULL) LIMIT 1";
        $res = $db->query($sql, ['::uid' => $uid]);

        if ($res && $db->resultNumRows($res) > 0) {
            return $db->fetchObject($res);
        }
        return null;
    }

    /**
     * Get user config data by email (without requiring BaseConfig to be set)
     *
     * @param string $email User email
     * @return object|null User config data or null if not found
     */
    public static function getUserConfigByEmail($email)
    {
        $db = Rapidkart::getInstance()->getDB();
        $sql = "SELECT uid, company_id, licid FROM " . SystemTables::DB_TBL_USER . " WHERE email = '::email' AND (ustatusid NOT IN (2,3) OR ustatusid IS NULL) LIMIT 1";
        $res = $db->query($sql, ['::email' => $email]);

        if ($res && $db->resultNumRows($res) > 0) {
            return $db->fetchObject($res);
        }
        return null;
    }

    /**
     * Setup BaseConfig from user data (mimics live project's system.inc.php + login.inc.php flow)
     * This must be called BEFORE Session::loginUser()
     *
     * @param int $uid User ID
     * @return bool True if setup successful, false otherwise
     */
    public static function setupBaseConfig($uid)
    {
        $userConfig = self::getUserConfigById($uid);
        if (!$userConfig) {
            return false;
        }

        // Set licence_id first (like live project's system.inc.php line 42)
        BaseConfig::$licence_id = (int) $userConfig->licid;

        // Set company_id (like live project's login.inc.php line 112)
        BaseConfig::$company_id = (int) $userConfig->company_id;

        // Set company_start_date if LicenceManager is available
        if (class_exists('LicenceManager') && method_exists('LicenceManager', 'getLicenceCompanyStartDate')) {
            BaseConfig::$company_start_date = LicenceManager::getLicenceCompanyStartDate();
        }

        return true;
    }

    /**
     * Setup BaseConfig from email (convenience method)
     *
     * @param string $email User email
     * @return bool True if setup successful, false otherwise
     */
    public static function setupBaseConfigByEmail($email)
    {
        $userConfig = self::getUserConfigByEmail($email);
        if (!$userConfig) {
            return false;
        }

        BaseConfig::$licence_id = (int) $userConfig->licid;
        BaseConfig::$company_id = (int) $userConfig->company_id;

        if (class_exists('LicenceManager') && method_exists('LicenceManager', 'getLicenceCompanyStartDate')) {
            BaseConfig::$company_start_date = LicenceManager::getLicenceCompanyStartDate();
        }

        return true;
    }

    /**
     * Login by user ID - sets up BaseConfig and calls Session::loginUser()
     * This is the recommended method for DGC login
     *
     * @param int $uid User ID
     * @return array ['success' => bool, 'message' => string]
     */
    public static function loginById($uid)
    {
        if (!self::setupBaseConfig($uid)) {
            return [
                'success' => false,
                'message' => 'User not found with ID: ' . $uid
            ];
        }

        $admin_user = new AdminUser($uid);
        Session::loginUser($admin_user);

        return [
            'success' => true,
            'message' => 'Logged in as: ' . $admin_user->getName()
        ];
    }

    /**
     * Login by email - sets up BaseConfig and calls Session::loginUser()
     * This is the recommended method for DGC login
     *
     * @param string $email User email
     * @return array ['success' => bool, 'message' => string]
     */
    public static function loginByEmail($email)
    {
        if (!self::setupBaseConfigByEmail($email)) {
            return [
                'success' => false,
                'message' => 'User not found with email: ' . $email
            ];
        }

        $uid = self::getUserIdByEmail($email);
        $admin_user = new AdminUser($uid);
        Session::loginUser($admin_user);

        return [
            'success' => true,
            'message' => 'Logged in as: ' . $admin_user->getName()
        ];
    }

    /**
     * Logout - calls Session::logoutUser()
     *
     * @return array ['success' => bool, 'message' => string]
     */
    public static function logout()
    {
        if (Session::isLoggedIn()) {
            Session::logoutUser();
            return [
                'success' => true,
                'message' => 'Logout successful'
            ];
        }
        return [
            'success' => false,
            'message' => 'No user is logged in'
        ];
    }
}

<?php

/**
 * SystemPlaceholderManager - Management and resolution of system placeholders
 *
 * This class has two responsibilities:
 * 1. CRUD operations for system placeholders (getAll, getById, etc.)
 * 2. Resolver methods that return actual system values at runtime
 *
 * The resolver_method field in the database maps to method names in this class.
 * For example: resolver_method = 'getLoggedInUid' calls SystemPlaceholderManager::getLoggedInUid()
 *
 * @author Dynamic Graph Creator
 */
class SystemPlaceholderManager
{
    // =============================================
    // CRUD Operations
    // =============================================

    /**
     * Get all active system placeholders
     *
     * @return array Array of SystemPlaceholder objects indexed by placeholder_key
     */
    public static function getAll()
    {
        $db = Rapidkart::getInstance()->getDB();
        $sql = "SELECT * FROM " . SystemTables::DB_TBL_SYSTEM_PLACEHOLDER . " WHERE spsid != 3 ORDER BY placeholder_label";
        $res = $db->query($sql);

        $placeholders = array();
        while ($row = $db->fetchAssocArray($res)) {
            $placeholder = new SystemPlaceholder();
            $placeholder->parse((object)$row);
            $placeholders[$row['placeholder_key']] = $placeholder;
        }
        return $placeholders;
    }

    /**
     * Get all active placeholders as array data (for JSON responses)
     *
     * @return array Array of placeholder data arrays
     */
    public static function getAllAsArray()
    {
        $db = Rapidkart::getInstance()->getDB();
        $sql = "SELECT * FROM " . SystemTables::DB_TBL_SYSTEM_PLACEHOLDER . " WHERE spsid != 3 ORDER BY placeholder_label";
        $res = $db->query($sql);

        $placeholders = array();
        while ($row = $db->fetchAssocArray($res)) {
            $placeholder = new SystemPlaceholder();
            $placeholder->parse((object)$row);
            $placeholders[] = $placeholder->toArray();
        }
        return $placeholders;
    }

    /**
     * Get a single placeholder by ID
     *
     * @param int $id Placeholder ID
     * @return SystemPlaceholder|null SystemPlaceholder object or null if not found
     */
    public static function getById($id)
    {
        if (!SystemPlaceholder::isExistent($id)) {
            return null;
        }
        return new SystemPlaceholder($id);
    }

    /**
     * Get a placeholder by its key
     *
     * @param string $key Placeholder key (without :: prefix)
     * @return SystemPlaceholder|null
     */
    public static function getByKey($key)
    {
        $db = Rapidkart::getInstance()->getDB();
        $sql = "SELECT * FROM " . SystemTables::DB_TBL_SYSTEM_PLACEHOLDER . "
                WHERE placeholder_key = '::key' AND spsid != 3 LIMIT 1";
        $res = $db->query($sql, array('::key' => $key));

        if (!$res || $db->resultNumRows($res) < 1) {
            return null;
        }

        $row = $db->fetchObject($res);
        $placeholder = new SystemPlaceholder();
        $placeholder->parse($row);
        return $placeholder;
    }

    /**
     * Get all placeholder keys (for quick lookup)
     *
     * @return array Array of placeholder keys (without :: prefix)
     */
    public static function getAllKeys()
    {
        $db = Rapidkart::getInstance()->getDB();
        $sql = "SELECT placeholder_key FROM " . SystemTables::DB_TBL_SYSTEM_PLACEHOLDER . " WHERE spsid != 3";
        $res = $db->query($sql);

        $keys = array();
        while ($row = $db->fetchAssocArray($res)) {
            $keys[] = $row['placeholder_key'];
        }
        return $keys;
    }

    // =============================================
    // Main Resolver
    // =============================================

    /**
     * Resolve a placeholder key to its actual value
     *
     * @param string $key Placeholder key (without :: prefix)
     * @return mixed Resolved value or null if not found
     */
    public static function resolve($key)
    {
        $placeholder = self::getByKey($key);
        if (!$placeholder) {
            return null;
        }

        $method = $placeholder->getResolverMethod();

        // Call the method dynamically
        if (method_exists(__CLASS__, $method)) {
            return self::$method();
        }

        return null;
    }

    /**
     * Resolve all system placeholders in a query string
     * Replaces ::placeholder_key with actual values
     *
     * @param string $query The query containing system placeholders
     * @return string Query with system placeholders replaced
     */
    public static function resolveInQuery($query)
    {
        $db = Rapidkart::getInstance()->getDB();
        $systemPlaceholders = self::getAll();

        foreach ($systemPlaceholders as $sp) {
            $placeholder = '::' . $sp->getPlaceholderKey();
            if (strpos($query, $placeholder) !== false) {
                $value = self::resolve($sp->getPlaceholderKey());
                // Escape the value for safe SQL usage
                $escapedValue = $db->escapeString($value);
                $query = str_replace($placeholder, $escapedValue, $query);
            }
        }

        return $query;
    }

    // =============================================
    // Resolver Methods
    // Each method name must match the resolver_method value in database
    // =============================================

    /**
     * Get the logged in user ID
     * resolver_method = 'getLoggedInUid'
     *
     * @return int|null User ID or null if not logged in
     */
    public static function getLoggedInUid()
    {
        return Session::loggedInUid();
    }

    /**
     * Get the logged in user's company ID
     * resolver_method = 'getLoggedInCompanyId'
     *
     * @return int|null Company ID
     */
    public static function getLoggedInCompanyId()
    {
        return BaseConfig::$company_id;
    }

    /**
     * Get the logged in user's licence ID
     * resolver_method = 'getLoggedInLicenceId'
     *
     * @return int|null Licence ID
     */
    public static function getLoggedInLicenceId()
    {
        return BaseConfig::$licence_id;
    }

    /**
     * Check if the logged in user is an admin
     * resolver_method = 'getLoggedInIsAdmin'
     *
     * @return int 1 if admin, 0 otherwise
     */
    public static function getLoggedInIsAdmin()
    {
        $user = SystemConfig::getUser();
        return ($user && $user->getIsAdmin() == 1) ? 1 : 0;
    }
}

<?php

/**
 * Rapidkart Stub Classes for DGC Development Environment
 * These stubs provide test data for company/outlet filters when running outside Rapidkart
 *
 * In the live Rapidkart environment, these classes already exist and will not be loaded.
 */

// Only define these classes if they don't already exist (i.e., we're in DGC dev mode)

if (!class_exists('Session')) {
    /**
     * Session stub - simulates logged-in user
     */
    class Session
    {
        private static $loggedInUid = 1; // Default test user ID

        public static function loggedInUid()
        {
            return self::$loggedInUid;
        }

        public static function isLoggedIn()
        {
            return true;
        }

        public static function setLoggedInUid($uid)
        {
            self::$loggedInUid = $uid;
        }

        public static function isUserLocked()
        {
            return false;
        }
    }
}

if (!class_exists('LicenceCompanies')) {
    /**
     * LicenceCompanies stub - represents a company
     */
    class LicenceCompanies
    {
        private $id;
        private $name;

        // Test companies data
        private static $testCompanies = [
            1 => 'Acme Corporation',
            2 => 'Global Industries',
            3 => 'Tech Solutions Ltd',
        ];

        public function __construct($id = null)
        {
            if ($id && isset(self::$testCompanies[$id])) {
                $this->id = $id;
                $this->name = self::$testCompanies[$id];
            }
        }

        public function getId()
        {
            return $this->id;
        }

        public function getName()
        {
            return $this->name;
        }

        public static function getTestCompanies()
        {
            return self::$testCompanies;
        }
    }
}

if (!class_exists('AdminUserManager')) {
    /**
     * AdminUserManager stub - provides user-company mappings
     */
    class AdminUserManager
    {
        /**
         * Get companies mapped to a user
         * @param int $uid User ID
         * @return array|false Array of LicenceCompanies objects keyed by company ID
         */
        public static function getUserCompanyMappingList($uid)
        {
            // Return all test companies for any user
            $companies = [];
            foreach (LicenceCompanies::getTestCompanies() as $id => $name) {
                $companies[$id] = new LicenceCompanies($id);
            }
            return $companies;
        }
    }
}

if (!class_exists('Outlet')) {
    /**
     * Outlet stub - represents an outlet/branch
     */
    class Outlet
    {
        private $id;
        private $name;
        private $companyId;
        private $chkid;

        public function __construct($data = null)
        {
            if ($data) {
                $this->id = $data['id'] ?? null;
                $this->name = $data['name'] ?? '';
                $this->companyId = $data['company_id'] ?? null;
                $this->chkid = $data['chkid'] ?? null;
            }
        }

        public function getId()
        {
            return $this->id;
        }

        public function getName()
        {
            return $this->name;
        }

        public function getCompanyId()
        {
            return $this->companyId;
        }

        public function getChkid()
        {
            return $this->chkid;
        }
    }
}

if (!class_exists('OutletManager')) {
    /**
     * OutletManager stub - provides outlet data
     */
    class OutletManager
    {
        // Test outlets data - grouped by company
        private static $testOutlets = [
            // Company 1 outlets
            ['id' => 1, 'name' => 'Main Branch - Delhi', 'company_id' => 1, 'chkid' => 101],
            ['id' => 2, 'name' => 'Branch - Mumbai', 'company_id' => 1, 'chkid' => 102],
            ['id' => 3, 'name' => 'Branch - Bangalore', 'company_id' => 1, 'chkid' => 103],
            // Company 2 outlets
            ['id' => 4, 'name' => 'Head Office', 'company_id' => 2, 'chkid' => 201],
            ['id' => 5, 'name' => 'Warehouse - Chennai', 'company_id' => 2, 'chkid' => 202],
            // Company 3 outlets
            ['id' => 6, 'name' => 'Tech Hub - Pune', 'company_id' => 3, 'chkid' => 301],
            ['id' => 7, 'name' => 'Development Center', 'company_id' => 3, 'chkid' => 302],
        ];

        /**
         * Get outlets for a user, optionally filtered by company
         * Returns array of objects with id, name, chkid properties
         */
        public static function getUserCheckPoint($uid, $outlid = null, $chkid = null, $gid = null, $gbutapid = null, $group_by_chkid = null, $group_by_outlid = null, $companies_array = [])
        {
            $outlets = [];

            foreach (self::$testOutlets as $outletData) {
                // Filter by company if specified
                if (!empty($companies_array) && !in_array($outletData['company_id'], $companies_array)) {
                    continue;
                }

                // Filter by outlet ID if specified
                if ($outlid !== null && $outletData['id'] != $outlid) {
                    continue;
                }

                // Filter by chkid if specified
                if ($chkid !== null && $outletData['chkid'] != $chkid) {
                    continue;
                }

                // Return as object with required properties
                $outlet = new stdClass();
                $outlet->id = $outletData['id'];
                $outlet->name = $outletData['name'];
                $outlet->chkid = $outletData['chkid'];
                $outlet->company_id = $outletData['company_id'];
                $outlets[] = $outlet;
            }

            return $outlets;
        }
    }
}

if (!class_exists('AdminUser')) {
    /**
     * AdminUser stub - represents a user
     */
    class AdminUser
    {
        private $uid;
        private $isAdmin = 1;
        private $companyId = 1;

        public function __construct($uid = null)
        {
            $this->uid = $uid;
        }

        public function getUid()
        {
            return $this->uid;
        }

        public function getIsAdmin()
        {
            return $this->isAdmin;
        }

        public function getCompanyId()
        {
            return $this->companyId;
        }
    }
}

if (!class_exists('BaseConfig')) {
    /**
     * BaseConfig stub - holds global configuration
     */
    class BaseConfig
    {
        public static $company_id = 1;
        public static $licence_id = 1;
    }
}

// Helper function for settings
if (!function_exists('getSettings')) {
    /**
     * Get a system setting value
     * @param string $key Setting key
     * @return mixed Setting value
     */
    function getSettings($key)
    {
        // Default settings for DGC dev environment
        $settings = [
            'IS_OUTLET_ENABLE' => true,
            'IS_MULTI_COMPANY' => true,
        ];

        return isset($settings[$key]) ? $settings[$key] : null;
    }
}

<?php

/**
 * Simulate Login/Logout for Dynamic Graph Creator
 *
 * This file provides simple functions to simulate login and logout
 * using the same mechanism as the live Rapidkart project.
 *
 * Usage:
 *   - Include this file in your test scripts
 *   - Call simulateLogin('email@example.com', 'password') to login
 *   - Call simulateLogout() to logout
 *   - Call isSimulatedLoggedIn() to check login status
 *
 * When moved to live project, the same Session class will work seamlessly.
 */

// Load required configuration and classes
require_once __DIR__ . '/system/utilities/SystemConfig.php';
require_once __DIR__ . '/system/utilities/SiteConfig.php';
require_once __DIR__ . '/system/config/BaseConfig.php';
require_once __DIR__ . '/system/utilities/SystemTables.php';
require_once __DIR__ . '/system/interfaces/DatabaseObject.php';
require_once __DIR__ . '/system/classes/SQLiDatabase.php';
require_once __DIR__ . '/system/classes/Rapidkart.php';
require_once __DIR__ . '/system/classes/Utility.php';
require_once __DIR__ . '/system/classes/AdminUser.php';
require_once __DIR__ . '/system/classes/AdminUserManager.php';
require_once __DIR__ . '/system/classes/Session.php';
require_once __DIR__ . '/system/classes/SessionsManager.php';
require_once __DIR__ . '/system/classes/SessionDetails.php';
require_once __DIR__ . '/system/classes/Licence.php';
require_once __DIR__ . '/system/classes/LicenceCompanies.php';
require_once __DIR__ . '/system/classes/LicenceManager.php';
require_once __DIR__ . '/system/classes/LicenceDomain.php';

// Initialize session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/**
 * Simulate login using email and password
 * Uses the same authentication mechanism as the live project
 *
 * @param string $email User email
 * @param string $password User password (plain text)
 * @return array ['success' => bool, 'message' => string, 'user' => AdminUser|null]
 */
function simulateLogin($email, $password)
{
    // Create AdminUser object and set credentials
    $admin_user = new AdminUser();
    $admin_user->setEmail($email);
    $admin_user->setPassword($password);

    // Authenticate against database
    if ($admin_user->authenticate()) {
        // Set company and licence IDs
        BaseConfig::$company_id = $admin_user->getCompanyId();

        // Get licence_id from company
        $licence_company = new LicenceCompanies($admin_user->getCompanyId());
        BaseConfig::$licence_id = $licence_company->getLicid();

        // Load full user data
        $admin_user->load();

        // Use Session class to login (same as live project)
        Session::loginUser($admin_user);

        return [
            'success' => true,
            'message' => 'Login successful',
            'user' => $admin_user
        ];
    } else {
        return [
            'success' => false,
            'message' => 'Invalid email or password',
            'user' => null
        ];
    }
}

/**
 * Simulate logout
 * Uses the same logout mechanism as the live project
 *
 * @return array ['success' => bool, 'message' => string]
 */
function simulateLogout()
{
    if (Session::isLoggedIn()) {
        Session::logoutUser();
        return [
            'success' => true,
            'message' => 'Logout successful'
        ];
    } else {
        return [
            'success' => false,
            'message' => 'No user is logged in'
        ];
    }
}

/**
 * Check if a user is currently logged in
 *
 * @return bool
 */
function isSimulatedLoggedIn()
{
    return Session::isLoggedIn();
}

/**
 * Get the currently logged in user ID
 *
 * @return int|false User ID or false if not logged in
 */
function getLoggedInUserId()
{
    return Session::loggedInUid();
}

/**
 * Get the currently logged in user object
 *
 * @return AdminUser|null
 */
function getLoggedInUser()
{
    $uid = Session::loggedInUid();
    if ($uid) {
        return new AdminUser($uid);
    }
    return null;
}


// ============================================================================
// EXAMPLE USAGE (uncomment to test)
// ============================================================================

/*
// Test login
$result = simulateLogin('test@example.com', 'password123');
if ($result['success']) {
    echo "Logged in as: " . $result['user']->getName() . "\n";
    echo "User ID: " . getLoggedInUserId() . "\n";
    echo "Is logged in: " . (isSimulatedLoggedIn() ? 'Yes' : 'No') . "\n";

    // Test logout
    $logoutResult = simulateLogout();
    echo $logoutResult['message'] . "\n";
    echo "Is logged in after logout: " . (isSimulatedLoggedIn() ? 'Yes' : 'No') . "\n";
} else {
    echo "Login failed: " . $result['message'] . "\n";
}
*/

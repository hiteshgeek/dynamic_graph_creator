# Deployment Checklist

This document lists important items to review and update when deploying to a live site.

## Database Configuration

### 1. Database Credentials
Update database connection settings in your configuration file:
- Host
- Database name
- Username
- Password

### 2. User ID Configuration
The application currently uses a hardcoded user ID for development. Update this for production:

**Location:** `system/classes/DashboardController.php`

```php
// TODO: Replace with actual user authentication
$userId = 1; // Currently hardcoded - update to use session/auth
```

**Required changes:**
- Implement proper user authentication
- Get user ID from session: `$_SESSION['user_id']`
- Or from your authentication system

### 3. Database Tables
Ensure all required tables exist:
- `dashboard` - Dashboard definitions
- `dashboard_template` - Template definitions
- `dashboard_template_category` - Template categories
- `graph` - Graph definitions
- `filter` - Filter definitions

## Security Considerations

### 1. Session Configuration
- Enable secure session cookies for HTTPS
- Set appropriate session timeout
- Implement CSRF protection if not already present

### 2. Input Validation
- All user inputs are sanitized with `htmlspecialchars()`
- SQL queries use prepared statements
- Review any raw SQL queries for injection vulnerabilities

### 3. File Permissions
- Ensure `system/` directory is not directly accessible
- Set appropriate permissions on configuration files
- Disable directory listing

## Asset Configuration

### 1. CSS/JS Versioning
The build system generates hashed filenames for cache busting:
- `common.[hash].css`
- `common.[hash].js`
- `dashboard.[hash].css`
- `dashboard.[hash].js`
- etc.

The `manifest.json` file maps these automatically.

### 2. CDN Resources
External resources loaded from CDNs:
- Bootstrap 5.3.2
- Font Awesome 6.5.1
- Google Fonts (Product Sans)

Consider self-hosting these for:
- Better reliability
- Privacy compliance (GDPR)
- Faster load times

## Environment-Specific Settings

### 1. Error Reporting
Disable detailed error messages in production:

```php
// Development
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Production
error_reporting(0);
ini_set('display_errors', 0);
ini_set('log_errors', 1);
```

### 2. Debug Mode
Search for and disable any debug code:
- `error_log()` statements
- `var_dump()` / `print_r()` calls
- Console logging in JavaScript

### 3. Base URL
Update base URL if the application is not at the domain root:
- Check `.htaccess` RewriteBase
- Update any hardcoded URLs

## Build Process

Before deployment, run the build:

```bash
npm run build
```

This compiles and minifies:
- SCSS to CSS
- JavaScript bundles
- Updates manifest.json

## Testing Checklist

- [ ] Database connection works
- [ ] User authentication works
- [ ] Dashboard CRUD operations work
- [ ] Template CRUD operations work
- [ ] Graph CRUD operations work
- [ ] Filter CRUD operations work
- [ ] All AJAX endpoints respond correctly
- [ ] CSS and JS assets load properly
- [ ] No console errors in browser

## Backup Recommendations

Before deploying updates:
1. Backup the database
2. Backup the current codebase
3. Test on staging environment first

## Post-Deployment

After deployment:
1. Clear any server-side caches
2. Test all critical functionality
3. Monitor error logs for issues
4. Verify asset caching headers are correct

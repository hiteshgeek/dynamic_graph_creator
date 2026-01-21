# Project Workflow Instructions

## Development Guidelines

- **Do not run npm scripts** after making changes - watch is already running
- After completing each task, **do not provide summary** unless asked
- Simply respond with "**Done**"

---

## Project Reference Information

### Paths & Locations

- **Live Project Path**: `/var/www/html/rapidkartprocessadminv2`

### Configuration & Structure

- **Database Credentials**: Use `.env` file
- **Project Structure Reference**: `PROJECT_STRUCTURE.md`
  - Check this file to identify which files should not be modified
- **Migration Updates**: `migrate.php`
  - Update this file for every change requiring migration
- **Build Configuration**: `build.js`

### Library Management

- **Location**: `themes/libraries/`
- **Naming Convention**: Suffix all new libraries with project initials

---

## Quick Reference

| Item              | Location/File          |
| ----------------- | ---------------------- |
| DB Credentials    | `.env`                 |
| Project Structure | `PROJECT_STRUCTURE.md` |
| Migrations        | `migrate.php`          |
| Build Setup       | `build.js`             |
| Libraries         | `themes/libraries/`    |

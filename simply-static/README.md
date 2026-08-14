# FanX Theme Yoast Sitemap Backup & Simply Static Integration

Yoast sitemap backup system for FanXTheme2026 with automatic daily backups and seamless Simply Static integration.

## Overview

This module provides:
- **Yoast Sitemap Backup** - Automatic daily backup of Yoast-generated sitemaps with restore capability
- **Simply Static Integration** - Hooks into Simply Static (both free and Pro versions) to ensure all URLs are crawlable
- **Versioned Backups** - Maintains current backup + 30-day archive for recovery
- **Admin Interface** - Quick actions available on Simply Static plugin settings page

## Features

### Yoast Sitemap Backup System
- **Automatic Daily Backups** - Runs at scheduled time via WordPress cron
- **Event-Triggered Backups** - On post updates, term updates, Yoast cache clear
- **Versioned Backups** - Current backup + archive of older versions
- **Retention Policy** - Keeps 30 days of backups, auto-cleans old files
- **Metadata Tracking** - Records backup times, files, WordPress version

### Simply Static Integration
- Hooks into Simply Static (free and Pro)
- Adds all discovered URLs to static export
- Includes sitemap files in export
- REST API endpoints for automation

## Directory Structure

```
/wp-content/
└── uploads/
    └── yoast-sitemap-backups/       # Yoast backup directory
        ├── current/                 # Latest backups
        │   ├── sitemap.xml
        │   ├── sitemap_index.xml
        │   └── metadata.json
        │
        └── archive/                 # Older backups (30-day retention)

/shared-themes/FanXTheme2026/
└── simply-static/
    ├── sitemap-integration.php      # Main integration file
    ├── system-level-exports.php     # System cron export & backup
    └── README.md                    # This file
```

## Admin Interface

### Admin Notice on Simply Static Settings

When viewing Simply Static plugin settings (admin-only), you'll see a blue information notice showing:
- Current Yoast backup status
- Latest backup timestamp
- Quick action buttons (Backup Now, Restore from Backup)

The notice appears only on the Simply Static settings page and provides:
- Real-time backup status updates
- One-click manual backup trigger
- One-click restore from latest backup

## WP-CLI Commands

All commands are under the `wp fanx simply-static-urls` namespace:

```bash
# Get Simply Static URL list (multiple formats)
wp fanx simply-static-urls
wp fanx simply-static-urls --format=csv
wp fanx simply-static-urls --format=list

# Regenerate static sitemaps
wp fanx simply-static-urls regenerate-sitemaps
wp fanx simply-static-urls regenerate-sitemaps --list

# List generated sitemap files
wp fanx simply-static-urls list-sitemaps

# Yoast Sitemap Backup Management
wp fanx simply-static-urls backup-yoast-sitemaps
wp fanx simply-static-urls backup-yoast-sitemaps --list
wp fanx simply-static-urls backup-status
wp fanx simply-static-urls restore-yoast-sitemaps

# Simply Static Pro Integration
wp fanx simply-static-urls add-urls-to-ssp
```

## REST API Endpoints

Access these endpoints from JavaScript or external tools (admin access required).

### Get Simply Static URLs
```
GET /wp-json/fanx/v1/simply-static-urls
```

Response includes URL count, Pro version info, and complete URL list.

### Regenerate Sitemaps
```
POST /wp-json/fanx/v1/regenerate-sitemaps
```

Immediately regenerates all static sitemaps. Returns count and location.

## Common Tasks

### Manual Backup Before Large Update

```bash
# Option 1: From WordPress Admin
- Go to Simply Static settings
- Click "Backup Yoast Sitemaps Now"

# Option 2: From command line
wp fanx backup-yoast-sitemaps --list
```

### Check Backup Age & Status

```bash
wp fanx backup-status
```

Output shows:
- Last backup timestamp
- Hours since backup
- Which files are backed up
- Warning if backup is >24 hours old

### Restore Sitemaps After Error

```bash
# Check current status
wp fanx backup-status

# If backup exists, restore it
wp fanx restore-yoast-sitemaps

# Verify restoration
wp fanx list-sitemaps
```

### Include Sitemaps in Static Export

1. Go to Simply Static settings page
2. The notice will show current backup status
3. Run "Backup Yoast Sitemaps Now" if desired
4. Run your Simply Static export
5. Static sitemaps are automatically included in `/sitemaps/` directory
6. Yoast sitemap backups are included if in `/wp-content/uploads/yoast-sitemap-backups/`

## Backup Metadata

Each backup records metadata in:
```
/wp-content/uploads/yoast-sitemap-backups/current/metadata.json
```

Contains:
- Last backup timestamp
- List of backed-up files
- List of any failed files
- Total files processed
- Site URL
- WordPress version

Example:
```json
{
  "last_backup": "2026-02-24 14:35:22",
  "timestamp": 1740408922,
  "backed_up_files": [
    "sitemap.xml",
    "sitemap_index.xml"
  ],
  "failed_files": [],
  "total_files": 2,
  "site_url": "https://example.com",
  "wordpress_version": "6.4.2"
}
```

## Automated Backup Schedule

By default, backups run automatically at:
- **Frequency**: Daily
- **Trigger Time**: Set by WordPress cron (typically night hours)
- **Throttle**: Post/term updates throttled to max 1 backup per 60 seconds

To adjust the schedule, you can:

```php
// Change to twice daily
wp_schedule_event(time(), 'twicedaily', 'fanx_backup_yoast_sitemaps');

// Or use custom interval (requires additional setup)
add_filter('cron_schedules', function($schedules) {
    $schedules['every_six_hours'] = array(
        'interval' => 6 * HOUR_IN_SECONDS,
        'display'  => 'Every 6 Hours'
    );
    return $schedules;
});
```

## Backup Recovery Scenarios

### Scenario 1: Live Sitemaps Corrupted
```bash
# Check backup status first
wp fanx backup-status

# Restore from backup
wp fanx restore-yoast-sitemaps

# Verify restoration
curl https://yoursite.com/sitemap.xml | head -5
```

### Scenario 2: Site Down - Use Static Files
If WordPress is completely down:
1. Access backups from `/wp-content/uploads/yoast-sitemap-backups/current/`
2. Copy to web root as fallback sitemaps
3. Search engines will use these temporary sitemaps
4. When site is back up, restore with `wp fanx restore-yoast-sitemaps`

### Scenario 3: Emergency Fallback Sitemap
```php
// Generate fallback sitemap from backup (via WP-CLI)
wp eval 'print_r(fanx_generate_fallback_sitemap_from_backup());'
```

Creates `/wp-content/uploads/yoast-sitemap-backups/current/sitemap-fallback.xml`

## Plugin Compatibility

### Required
- WordPress 5.9+
- PHP 7.4+

### Supported Simply Static Versions
- **Free**: All versions with filter support
- **Pro**: Latest versions with custom crawlers

### Yoast SEO
- All recent versions (v14.0+)
- Works independently if Yoast inactive
- Hooks into Yoast cache clear events

## Performance Notes

- Static sitemaps are lightweight (~100KB-1MB typical)
- Backup process runs in background with throttling
- No noticeable impact on post/term updates
- Daily cron runs during low-traffic periods

## Troubleshooting

### Backups Not Creating

Check:
```bash
# Verify directory exists and is writable
ls -la wp-content/uploads/yoast-sitemap-backups/

# Run manual backup
wp fanx backup-yoast-sitemaps

# Check for errors
wp eval 'print_r(fanx_backup_yoast_sitemaps());'
```

### Sitemaps Not Regenerating

Clear cache and trigger regeneration:
```bash
wp fanx regenerate-sitemaps --list

# Flush WordPress rewrite rules
wp rewrite flush
```

### Simply Static Not Including Sitemaps

Verify integration is active:
```bash
wp eval 'var_dump(class_exists("Simply_Static"));'

# Run manual export
wp fanx simply-static-urls
```

### Backup Fails to Restore

Check permissions and verify files exist:
```bash
# List backup files
ls -la wp-content/uploads/yoast-sitemap-backups/current/

# Check web root permissions
ls -la | grep sitemap

# Verify metadata
cat wp-content/uploads/yoast-sitemap-backups/current/metadata.json
```

## File Locations Reference

| Purpose | Location |
|---------|----------|
| Yoast Backups (Current) | `/wp-content/uploads/yoast-sitemap-backups/current/` |
| Yoast Backups (Archive) | `/wp-content/uploads/yoast-sitemap-backups/archive/` |
| Backup Metadata | `/wp-content/uploads/yoast-sitemap-backups/current/metadata.json` |
| Integration Code | `/shared-themes/FanXTheme2026/simply-static/sitemap-integration.php` |
| System Export Code | `/shared-themes/FanXTheme2026/simply-static/system-level-exports.php` |

## Security Notes

- Backup system runs with admin-level access required
- WP-CLI commands require admin privileges
- REST API endpoints require admin authentication
- All file operations verify permissions before proceeding
- Sitemaps are read-only for public access

## Version History

- **v1.0** (Feb 2026) - Initial release with Yoast backup system

## Support & Questions

For integration issues or feature requests, refer to:
- The main integration file: `sitemap-integration.php`
- Admin notice buttons for quick actions
- WP-CLI commands for automated workflows
- REST API for programmatic access

---

**Last Updated**: August 14, 2026
**Status**: Production Ready - Yoast Backup & Simply Static Integration
**Note**: Static XML sitemap generation feature not currently implemented (uses Yoast sitemaps instead)

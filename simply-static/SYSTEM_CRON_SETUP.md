# System Cron Setup for Simply Static Scheduled Exports and Backups

## Overview

This system uses system cron (instead of WordPress cron) to reliably schedule static site exports. Backups are automatically created as timestamped copies after each successful export.

**Two separate systems:**
1. **System-Level Scheduled Exports** (nightly midnight) - Automatic recurring exports, with backups automatically created on success
2. **User-Defined Scheduled Post Exports** (ad-hoc) - Would allow editors to schedule specific post exports via ACF button (currently disabled/placeholder)

**Note:** Exports are the scheduled process. Backups are automatic byproducts created after exports succeed.

## Two Scheduling Systems

### System-Level Scheduled Exports (Primary)
**Purpose:** Automatic, recurring static site exports on a nightly schedule. Backups are automatically created after each export.

**Current Schedule:**
- **FanX**: 12:00 AM (midnight) daily
- **ICC**: 12:00 AM (midnight) daily
- **TBCC**: 12:00 AM (midnight) daily

**How it works:**
- System cron runs `/bin/run-scheduled-backups.php` at midnight (00:00) via crontab entry
- Script loads WordPress for each site and triggers the static export
- After successful export, `df_backup_static_export()` automatically creates a timestamped backup
- Old backups automatically cleaned up (12-day retention via `df_cleanup_old_backups()`)
- No WordPress cron involvement

**Current Status:** ✓ **ACTIVE AND WORKING** — Cron executes nightly, exports succeed, backups created

### User-Defined Scheduled Post Exports (Future Feature)
**Purpose:** Would allow editors to schedule specific post exports via ACF button

**Current Status:** ✗ **DISABLED** — Code exists in `/shared-themes/FanXTheme2026/simply-static/schedule.php` but not actively scheduled

**How it would work (when activated):**
- Editors set `export_datetime` field and click "Schedule Export" button
- Export stored in WordPress options (`ssp_pending_exports`)
- System cron would check for pending exports and execute when time arrives
- Supports multiple simultaneous scheduled post exports
- Note: This is separate from system-level nightly exports

## Files in Current Use

**Active:**
- `/bin/run-scheduled-backups.php` - CLI script for system cron execution (RUNNING AT MIDNIGHT)
- `/shared-themes/FanXTheme2026/simply-static/system-level-exports.php` - Contains export and backup handlers (`ssp_run_static_backup_cron_cli()`, `df_backup_static_export()`, `df_cleanup_old_backups()`)
- `/shared-themes/FanXTheme2026/functions.php` - Includes the above files

**Inactive (User-Defined Exports):**
- `/shared-themes/FanXTheme2026/simply-static/schedule.php` - User-defined export code (disabled/placeholder)
- `/shared-themes/FanXTheme2026/simply-static/enqueue.php` - ACF button script enqueuing (unused)
- `/shared-themes/FanXTheme2026/simply-static/acf-export-actions.js` - ACF button event handler (unused)
- `/bin/run-scheduled-exports.php` - CLI runner exists but NOT in crontab

**Note:** The naming is confusing: `run-scheduled-backups.php` actually triggers **exports** which then automatically create backups

## Current System Architecture

### System Cron — Night Exports + Auto Backups

**Cron Invocation:**
```
0 0 * * * /usr/local/bin/php /home/ashelizmoore/bin/run-scheduled-backups.php fanx force
0 0 * * * /usr/local/bin/php /home/ashelizmoore/bin/run-scheduled-backups.php icc force
0 0 * * * /usr/local/bin/php /home/ashelizmoore/bin/run-scheduled-backups.php tbcc force
```

**Execution Chain:**
1. `run-scheduled-backups.php` loads WordPress and calls `ssp_run_static_backup_cron_cli()` (from `system-level-exports.php`)
2. This function runs the full static export via Simply Static plugin
3. On success, `df_backup_static_export()` automatically creates a timestamped backup
4. `df_cleanup_old_backups()` removes backups older than 12 days
5. Results logged to `/var/log/wp-backups.log`

**Functions Used:**
- `ssp_run_static_backup_cron_cli($site, $trigger)` - Main export trigger
- `df_backup_static_export()` - Creates timestamped backup copy after export
- `df_cleanup_old_backups()` - Maintenance: removes old backups

**Status:** ✓ WORKING — Currently executing every night at midnight for all sites

### User-Defined Post Exports (Disabled)

**Placeholder Code Location:** `/shared-themes/FanXTheme2026/simply-static/schedule.php`

**Functions Defined But Unused:**
- `ssp_ajax_schedule_export()` - Would handle ACF button click
- `ssp_ajax_cancel_export()` - Would handle cancel button
- `ssp_schedule_post_export()` - Would store export to `ssp_pending_exports` option
- `ssp_cancel_post_export()` - Would remove from queue
- `ssp_run_post_export_cron()` - Would execute queued export

**Why Disabled:** No system cron job scheduled these exports, and feature requires ACF button integration testing

**Current Status:** ✗ INACTIVE — Code present but not called, no pending exports, not in crontab

### 2. New CLI Runner Script

**Location:** `/bin/run-scheduled-backups.php`

**Purpose:** 
- Checks all WordPress sites for pending backups
- Executes backups that have reached their scheduled time
- Returns appropriate exit codes for monitoring

**Usage:**
```bash
php /bin/run-scheduled-backups.php [site|all]
php /bin/run-scheduled-backups.php all            # Check all sites
php /bin/run-scheduled-backups.php fanx           # Check FanX Salt Lake
php /bin/run-scheduled-backups.php icc            # Check Indiana Comic Convention
php /bin/run-scheduled-backups.php tbcc           # Check Tampa Bay Comic Convention
```

## Setup Instructions

### Step 1: Add Sites to CLI Runner

Edit `/bin/run-scheduled-backups.php` and verify the `$wp_sites` array has all your WordPress sites:

```php
$wp_sites = array(
    'fanx' => '/home/ashelizmoore/fillory/fanx/',
    'icc'  => '/home/ashelizmoore/fillory/ICC/',
    'tbcc' => '/home/ashelizmoore/fillory/TBCC/',
    // Note: ATL directory exists but is not a complete WordPress installation
    // To add ATL, ensure it has wp-config.php, wp-load.php, wp-settings.php
);
```

The key should be a short identifier, and the value should be the full path to the WordPress root directory.

### Step 2: Disable WordPress Cron (ALREADY CONFIGURED)

Add to **each site's** `wp-config.php` (before the "stop editing" comment):

```php
// Use system cron instead of wp-cron for reliability
define( 'DISABLE_WP_CRON', true );
```

**Configured locations:**
- `/home/ashelizmoore/fillory/fanx/wp-config.php` ✓
- `/home/ashelizmoore/fillory/ICC/wp-config.php` ✓
- `/home/ashelizmoore/fillory/TBCC/wp-config.php` ✓

### Step 3: System Cron Job (CURRENTLY ACTIVE)

The root crontab currently has:

```bash
0 0 * * * /usr/local/bin/php /home/ashelizmoore/bin/run-scheduled-backups.php fanx force >> /var/log/wp-backups.log 2>&1
0 0 * * * /usr/local/bin/php /home/ashelizmoore/bin/run-scheduled-backups.php icc force >> /var/log/wp-backups.log 2>&1
0 0 * * * /usr/local/bin/php /home/ashelizmoore/bin/run-scheduled-backups.php tbcc force >> /var/log/wp-backups.log 2>&1
```

**Explanation:**
- `0 0 * * *` - Every day at midnight (00:00)
- `force` - Forces export to run
- `run-scheduled-backups.php [site]` - Triggers static export + backup for specific site
- `>> /var/log/wp-backups.log 2>&1` - Logs to backup log (note: file named "backups" but logs export + backup creation)

### Step 4: Log Files (CURRENTLY ACTIVE)

Logs are written to `/var/log/wp-backups.log` — this file exists and is actively being written.

```bash
sudo tail -f /var/log/wp-backups.log
```

Output shows export execution and backup creation for each site at midnight.

## Testing

### View Recent Execution

Check the last runs:

```bash
tail -20 /var/log/wp-backups.log
```

Expected output:
```
[2026-04-09 00:00:01] [STATIC_BACKUP_CRON] Running scheduled full backup for fanx
[2026-04-09 00:00:09] [STATIC_BACKUP_CRON] Backup successful for fanx
...
```

### Manual Test (Force Export Now)

To trigger an export immediately:

```bash
php /home/ashelizmoore/bin/run-scheduled-backups.php fanx force
```

### Monitor Real-Time Execution

Watch the log while a scheduled export runs (at midnight):

```bash
tail -f /var/log/wp-backups.log
```

## Schedule Configuration

### Current Schedule

Exports run at **12:00 AM (midnight) every day** for all three sites via system cron.

**To change the scheduled time:**

1. Edit the crontab:
   ```bash
   sudo crontab -e
   ```

2. Modify the first number in `0 0` to the desired hour. Examples:
   - `0 2 * * *` — 2:00 AM
   - `30 1 * * *` — 1:30 AM
   - `0 18 * * *` — 6:00 PM (18:00)

**Note:** Changes to crontab take effect immediately but won't trigger until the next scheduled time.

## User-Defined On-Demand Exports (Disabled Feature)

If the user-defined post export feature is activated in the future:

```php
// Check pending user-defined exports
$pending = get_option( 'ssp_pending_exports', array() );
// Currently empty — no pending exports
// Format would be: array( post_id => array( 'scheduled_for' => 'Y-m-d H:i:s' ) )
```

**To activate:**
1. Add a system cron job to check for pending exports (every 5 minutes)
2. Enable ACF button integration (test enqueue.php and acf-export-actions.js)
3. Allow editors to set `export_datetime` and click "Schedule Export" on posts
4. Update documentation with timezone handling notes

## Troubleshooting

### Exports Not Running

1. **Verify system cron is set:**
   ```bash
   crontab -l | grep run-scheduled-backups.php
   ```
   Should show three entries (fanx, icc, tbcc) at `0 0 * * *`

2. **Verify wp-cron is disabled:**
   ```bash
   grep DISABLE_WP_CRON /path/to/wp-config.php
   ```
   Should output: `define( 'DISABLE_WP_CRON', true );`

3. **Check log file:**
   ```bash
   tail -50 /var/log/wp-backups.log
   ```
   Should show recent `[STATIC_BACKUP_CRON]` entries at midnight

4. **Check PHP path:**
   ```bash
   which php
   /usr/local/bin/php --version
   ```
   Should match the path in crontab (`/usr/local/bin/php`)

5. **Test manually:**
   ```bash
   php /home/ashelizmoore/bin/run-scheduled-backups.php fanx force
   ```
   Should execute export and backup, output to log

### Checking Pending User-Defined Exports

Query via WordPress CLI (if feature were activated):

```bash
wp --path=/home/ashelizmoore/fillory/fanx option get ssp_pending_exports --allow-root
```

Currently should be empty (feature disabled).

### Last Successful Export

Check when the last export/backup ran:

```bash
grep 'successful\|COMPLETED' /var/log/wp-backups.log | tail -5
```

## Advantages of System Cron

| Aspect | wp-cron | System Cron |
|--------|---------|------------|
| **Reliability** | Depends on site traffic | Always runs on schedule |
| **Performance** | Adds overhead to page requests | Lightweight, isolated |
| **Timing Control** | Loose, bounded by traffic | Precise via system scheduler |
| **Monitoring** | Difficult to troubleshoot | Standard system tools |
| **Scalability** | Scales with traffic | Consistent regardless of traffic |

## Performance Notes

- **Nightly execution:** Export runs once per day at midnight for each site
- **Export duration:** 5-60+ minutes depending on site size and complexity
- **Backup duration:** Minimal (file copy operation)
- **System impact:** Minimal — cron job runs independently, no page load overhead
- **Backup retention:** Automatic cleanup removes backups older than 12 days
- **Parallel execution:** Multiple sites' exports may run simultaneously (fanx, icc, tbcc all start at midnight)
- **Log file growth:** Monitor `/var/log/wp-backups.log` size; old entries should not accumulate indefinitely past midnight each day

## Security Considerations

- **CLI script validation:** `run-scheduled-backups.php` validates site paths before loading WordPress
- **System user:** Cron runs as `ashelizmoore` user (same as web/PHP user for proper file permissions)
- **File permissions:** Backup/export directories writable by same user (wwwuser or ashelizmoore)
- **Logging:** Uses `error_log()` function (respects WordPress error_log configuration)
- **Access control:** CLI script runs only through system cron (not web-accessible)
- **No hardcoded credentials:** All site configuration via `wp-config.php`
- **ACF field group:** Currently locked (editable only in code via `acfe-php/`)

## Future Activation: User-Defined Exports

To enable editors to schedule ad-hoc post exports in the future:

1. **Add cron job:** Create a cron entry to run every 5 minutes:
   ```bash
   */5 * * * * /usr/local/bin/php /home/ashelizmoore/bin/run-scheduled-exports.php all force >> /var/log/wp-exports.log 2>&1
   ```

2. **Test ACF integration:** Verify enqueue.php and acf-export-actions.js work with button clicks

3. **Timezone fix:** Update `schedule.php` functions to use `wp_strtotime()` and `current_time()` for proper WordPress timezone handling (currently uses `strtotime()` and `time()` which are timezone-unsafe)

4. **Security hardening:** Add nonce verification and capability checks to AJAX handlers

5. **Documentation:** Document timezone behavior and expected queue size for editors

## Potential Improvements

- Monitor `/var/log/wp-backups.log` size and implement log rotation
- Add email notifications on export failure
- Dashboard widget showing last export time and backup count per site
- Per-site configuration via WordPress admin (instead of code-based)
- Conditional export (only if posts changed since last export)
- Export status indicators in wp-admin for editors

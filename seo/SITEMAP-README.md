# FanX Theme - XML Sitemap Generator

## Overview

The FanX Theme includes a powerful, automatically-generated XML sitemap system that works with any WordPress website. It dynamically discovers and includes:

- **All Custom Post Types (CPTs)**
- **All Custom Taxonomies**
- **Proper XML sitemap structure** with change frequency and priority metadata
- **Large-scale support** with automatic pagination for sites with 50k+ URLs
- **SEO-optimized** output compatible with all major search engines

## Features

### 🎯 Automatic Discovery
The sitemap system automatically detects and includes:
- All public post types registered in WordPress (pages, posts, custom post types)
- All public taxonomies (categories, tags, custom taxonomies)
- Only published content with no search exclusions

### 📊 Smart Pagination
- Automatically splits sitemaps when they exceed Google's 50,000 URL limit
- Creates a comprehensive sitemap index for navigation
- Works seamlessly regardless of site size

### 🔍 Search Engine Friendly
- Valid XML Sitemap protocol (sitemap.org)
- Proper change frequency and priority settings
- Includes last modification dates for all URLs
- Compatible with Google Search Console, Bing Webmaster Tools, etc.

### 🎨 Browser Display
- Beautiful XSL stylesheet for viewing sitemaps in browsers
- Mobile-responsive design
- Easy navigation with link previews

## How It Works

### Access Your Sitemaps

**Main Sitemap Index:**
```
https://yoursite.com/sitemap.xml
```

**Individual Post Type Sitemaps:**
```
https://yoursite.com/sitemap-{post-type}.xml
https://yoursite.com/sitemap-post.xml
https://yoursite.com/sitemap-page.xml
https://yoursite.com/sitemap-guest.xml
https://yoursite.com/sitemap-feature.xml
```

**Individual Taxonomy Sitemaps:**
```
https://yoursite.com/sitemap-tax-{taxonomy}.xml
https://yoursite.com/sitemap-tax-category.xml
https://yoursite.com/sitemap-tax-post_tag.xml
https://yoursite.com/sitemap-tax-fandoms.xml
```

**Paginated Sitemaps (for large post types):**
```
https://yoursite.com/sitemap-{post-type}-1.xml
https://yoursite.com/sitemap-{post-type}-2.xml
https://yoursite.com/sitemap-{post-type}-3.xml
```

## File Structure

```
shared-themes/FanXTheme2026/
├── functions/
│   └── sitemap.php              # Main sitemap generator logic
├── simply-static/
│   ├── schedule.php             # Simply Static scheduling
│   └── sitemap-integration.php   # Simply Static URL discovery
├── seo/
│   ├── sitemap.xsl              # XSL stylesheet for browser display
│   └── SITEMAP-README.md         # This file
└── functions.php                # Updated to include sitemap.php
```

## Technical Details

### Included Files
- `functions/sitemap.php` - Core sitemap generation engine
- `simply-static/sitemap-integration.php` - Simply Static integration
- `seo/sitemap.xsl` - Optional XSL styling for browser viewing

### How It Integrates

1. **Rewrite Rules** - Adds custom rewrite rules for `/sitemap.xml` and `/sitemap-{type}.xml` URLs
2. **Query Variables** - Registers `fanx_sitemap` and `fanx_sitemap_type` query variables
3. **Template Filter** - Hijacks template loading to serve XML instead of HTML
4. **Dynamic Discovery** - Scans all registered post types and taxonomies at generation time

### Priority & Change Frequency

**Post Types:**
- Change Frequency: `weekly`
- Priority: `0.8`

**Taxonomies:**
- Change Frequency: `weekly`
- Priority: `0.6`

These values are hardcoded but can be customized via filters.

## Customization

### Modify Change Frequency & Priority

Add this to your code to customize priorities:

```php
// Filter post type URLs
add_filter('fanx_sitemap_post_type_priority', function($priority, $post) {
    if ($post->post_type === 'guest') {
        return '0.9'; // Higher priority for guest posts
    }
    return $priority;
}, 10, 2);

// Filter taxonomy priority
add_filter('fanx_sitemap_taxonomy_priority', function($priority, $term) {
    return $priority;
}, 10, 2);
```

### Exclude Specific Post Types

```php
// In functions.php, you can modify fanx_get_sitemap_post_types()
add_filter('fanx_sitemap_post_types', function($post_types) {
    unset($post_types['attachment']); // Already excluded by default
    return $post_types;
});
```

### Exclude Specific Taxonomies

```php
add_filter('fanx_sitemap_taxonomies', function($taxonomies) {
    unset($taxonomies['some_taxonomy']);
    return $taxonomies;
});
```

## Integration with SEO Plugins

### Yoast SEO
If Yoast SEO is active, it automatically takes over sitemap generation and the FanX sitemap system disables itself. This is the recommended approach for production sites using Yoast.

### Simply Static
The FanX sitemap integrates seamlessly with Simply Static (Free & Pro) for static site generation:

**Automatic Integration:**
- All CPTs and custom taxonomies are automatically discovered
- All published posts and taxonomy terms are added to the crawl list
- The sitemap URL is included for completeness
- No configuration needed!
- Compatible with both **Simply Static Free** and **Simply Static Pro** (tested with v2.1.6.1+)

**Advanced Features:**

Get all URLs via WP-CLI:
```bash
wp fanx simply-static-urls
wp fanx simply-static-urls --format=csv
wp fanx simply-static-urls --format=list
```

**For Simply Static Pro users:** If you find that URLs aren't being automatically discovered, you can manually add them to the database:
```bash
wp fanx add-urls-to-ssp
```

Get URL count and list via REST API (admin only):
```
GET /wp-json/fanx/v1/simply-static-urls
```

Response includes Pro detection:
```json
{
  "count": 1250,
  "pro_active": true,
  "pro_version": "2.1.6.1",
  "urls": [...]
}
```

**Custom Hooks for Developers:**
```php
// Filter all URLs before sending to Simply Static
add_filter('fanx_simply_static_urls', function($urls) {
    // Add custom URLs
    $urls[] = home_url('/custom-page/');
    return $urls;
});

// For Pro-specific hooks
add_filter('ssp_additional_urls', function($urls) {
    // Pro version specific
    return $urls;
});
```

### Other SEO Plugins
The FanX sitemap will work alongside other plugins. If you prefer to use a different plugin's sitemaps, you can disable the FanX system by removing the `functions/sitemap.php` include.

## robots.txt Integration

The system automatically adds the sitemap URL to `robots.txt`:

```
Sitemap: https://yoursite.com/sitemap.xml
```

If Yoast SEO is active, Yoast handles this integration.

## Performance Considerations

- **First Load:** Sitemaps are generated on-demand, not cached
- **Pagination:** Large sites are split into 50,000 URL chunks
- **Database Queries:** Optimized to minimize database hits
- **Memory Usage:** Handles large sites efficiently

For **very large sites** (100k+ URLs), consider:
1. Using Yoast SEO which includes caching
2. Adding server-level caching for `/sitemap*.xml`
3. Using a caching plugin with XML file caching

## Search Engine Submission

After deployment, submit your sitemap to search engines:

1. **Google Search Console**
   - Go to Sitemaps section
   - Add `https://yoursite.com/sitemap.xml`

2. **Bing Webmaster Tools**
   - Go to Sitemaps
   - Add `https://yoursite.com/sitemap.xml`

3. **Other Search Engines**
   - Most search engines support the standard Sitemap protocol
   - Submit `https://yoursite.com/sitemap.xml`

## Troubleshooting

### Sitemap Returns 404
- **Solution:** Regenerate WordPress rewrite rules by going to Settings > Permalinks and clicking "Save Changes"

### Sitemap is empty
- **Possibility:** No published content with the required post type/taxonomy
- **Solution:** Make sure you have published content of that type

### URLs aren't showing up
- **Check:** Is the content published and not marked as "exclude from search"?
- **Check:** Are the post type and taxonomy marked as publicly queryable?

### Rewrite rules not working
Make sure `.htaccess` is writable and URLs look like `/sitemap.xml` not `?p=123`

### Simply Static Pro not picking up URLs
If the automatic filter integration isn't working with Simply Static Pro:

1. **Check if pro is active:**
   ```bash
   wp eval 'echo defined("SIMPLY_STATIC_PRO_VERSION") ? SIMPLY_STATIC_PRO_VERSION : "not active";'
   ```

2. **Manually add URLs to the Pro database:**
   ```bash
   wp fanx add-urls-to-ssp
   ```

3. **Check the REST API to verify URLs are being generated:**
   ```bash
   wp eval 'echo count(fanx_generate_simply_static_url_list_simple());'
   ```

4. **Verify the integration file is loaded:**
   ```bash
   wp eval 'echo function_exists("fanx_simply_static_init") ? "loaded" : "not loaded";'
   ```

## API Reference

### Functions Available for Developers

```php
// Get all sitemap post types
fanx_get_sitemap_post_types()

// Get all sitemap taxonomies
fanx_get_sitemap_taxonomies()

// Generate sitemap index
fanx_generate_sitemap_index()

// Generate specific sitemap
fanx_generate_sitemap($type)

// Add post type URLs
fanx_add_post_type_urls($post_type, $page = 1)

// Add taxonomy URLs
fanx_add_taxonomy_urls($taxonomy)
```

## Support & Updates

The FanX sitemap system is lightweight and self-contained. It requires:
- WordPress 5.0+
- PHP 7.2+

No additional plugins required!

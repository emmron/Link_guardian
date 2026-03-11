# Link Guardian - Advanced Link Checker

Advanced Link Checker is a comprehensive, efficient, and user-friendly WordPress plugin designed to detect, manage, and resolve broken links across your website. Ensuring optimal performance, security, and compatibility, this plugin provides a complete solution for link management, enhancing your site's user experience and SEO.

## Features

- **Link Scanning Engine**: Scans all posts and pages for broken links with customizable scanning frequency and per-scan limits.
- **Broken Link Detection and Storage**: Identifies broken links by checking HTTP status codes and stores them in a custom database table with duplicate detection.
- **Broken Link Management Interface**: A dedicated admin page with statistics dashboard, status code distribution chart, and a sortable/paginated link table with row actions (recheck, resolve, delete).
- **Link Rechecking and Resolution**: Recheck individual links or all links at once via AJAX. Automatically removes links from the database when they return 200 OK.
- **Email Notifications**: Configurable email notification system with instant/daily/weekly frequency, custom recipients, and threshold settings.
- **Front-end Broken Link Highlighting**: Highlights broken links on the front-end with customizable color and underline style (wavy, solid, dashed, dotted). Includes a `[alc_highlight_broken_links]` shortcode.
- **Reporting and Analytics**: Generates detailed reports with CSV export, status code distribution visualization, and posts-with-most-broken-links charts.
- **URL Exclusion List**: Exclude URLs from scanning using wildcard patterns.
- **Settings and Configuration**: Full settings page with scanning, email notification, and front-end highlighting configuration sections.
- **Security**: Nonce verification, input sanitization, output escaping, capability checks, and Content Security Policy headers.
- **Performance**: Object caching, memory optimization during scans, and database query optimization.

## Installation

1. Download the plugin files.
2. Upload the `Link_guardian` folder to the `/wp-content/plugins/` directory.
3. Activate the plugin through the 'Plugins' menu in WordPress.
4. Visit **Settings > Link Checker Settings** to configure the plugin.

## Configuration

After activation, you can configure the plugin from **Settings > Link Checker Settings**:

### Scanning Settings
- **Scan Frequency**: Set how often to scan for broken links (1-168 hours).
- **Links Per Scan**: Maximum number of links to check per scan (10-5000).
- **Excluded URLs**: URL patterns to exclude from scanning (supports wildcards).

### Email Notifications
- **Enable/Disable**: Toggle email notifications on or off.
- **Recipients**: Set email addresses for notifications (comma-separated).
- **Frequency**: Choose between instant, daily, or weekly notifications.
- **Threshold**: Minimum number of broken links to trigger a notification.

### Front-end Highlighting
- **Enable/Disable**: Toggle broken link highlighting for site visitors.
- **Highlight Color**: Choose a custom color for broken links.
- **Highlight Style**: Choose between wavy, solid, dashed, or dotted underline styles.

## Usage

Once configured, the plugin automatically scans your website for broken links based on the set frequency. Detected broken links can be managed from the **Link Checker** admin page, where you can:

- View statistics and charts showing broken link distribution
- Recheck individual links or all links at once
- Mark links as resolved or delete them
- Export a CSV report of all broken links
- Run a manual scan with the "Scan Now" button

### Shortcode

Use the `[alc_highlight_broken_links]` shortcode to display a list of broken links for a specific post:

```
[alc_highlight_broken_links post_id="123"]
```

## Documentation

For more detailed information, please refer to the following documentation:

- [Installation Guide](docs/installation.md)
- [User Guide](docs/user-guide.md)
- [FAQ](docs/faq.md)

## Requirements

- WordPress 5.2 or higher
- PHP 7.2 or higher

## License

This plugin is licensed under GPL2.

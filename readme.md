# WP Revision List

WordPress plugin to show revisions when viewing lists of posts, pages, or custom post types in the admin dashboard

[![Code Climate](https://codeclimate.com/github/petenelson/wp-revision-list/badges/gpa.svg)](https://codeclimate.com/github/petenelson/wp-revision-list)

## Description

This WordPress plugin allows you to include a list of revisions when viewing a list of posts, pages, or custom post types in the admin dashboard.  It can be configured
to limit the number of revisions shown, the post types it is enabled for, and a prefix & suffix to wrap around the revision title to offset it from the rest of the list.

## Installation

1. Upload the wp-revision-list directory to the `/wp-content/plugins/` directory
2. Activate the plugin through the 'Plugins' menu in WordPress
3. Check Settings/WP Revision List to customize

## Frequently Asked Questions

*My custom post type does not show up in the settings?*
Only post types that support revisions work with this plugin.  Make sure you include "'supports' => array( 'revisions' )" in your register_post_type() call.


## Changelog

### 1.1.2 April 10, 2015
- Added user-configurable number of revisions in Screen Options

### v1.0 April 9 2015
- Initial Release

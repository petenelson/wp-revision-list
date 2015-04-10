=== WP Revision List ===
Contributors: gungeekatx
Tags: admin, post, page, custom post type, revisions
Donate link: http://petenelson.com/
Requires at least: 4.0
Tested up to: 4.2
Stable tag: 1.0.0
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Show revisions when viewing lists of posts, pages, or custom post types in the admin dashboard

== Description ==

This plugin allows you to include a list of revisions when viewing a list of posts, pages, or custom post types in the admin dashboard.  It can be configured
to limit the number of revisions shown, the post types it is enabled for, and a prefix & suffix to wrap around the revision title to offset it from the rest of the list.

== Installation ==

1. Upload the wp-revision-list directory to the `/wp-content/plugins/` directory
2. Activate the plugin through the 'Plugins' menu in WordPress
3. Check Settings/WP Revision List to customize


== Changelog ==

= 1.1.0 April 10, 2015 =
* Added user-configurable number of revisions in Screen Options

= 1.0.0 April 9, 2015 =
* Initial release


== Upgrade Notice ==

= 1.0.0 April 9, 2015 =
* Initial release


== Frequently Asked Questions ==

= My custom post type does not show up in the settings? =
Only post types that support revisions work with this plugin.  Make sure you include "'supports' => array( 'revisions' )" in your register_post_type() call.


== Screenshots ==

1. Posts showing revisions
2. Custom post type support
3. Plugin settings
4. Number of revisions configurable per user

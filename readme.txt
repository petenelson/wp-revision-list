=== WP Revision List ===
Contributors: gungeekatx
Tags: admin, post, page, custom post type, revisions
Donate link: http://petenelson.com/
Requires at least: 4.0
Tested up to: 4.7
Stable tag: 1.1.6
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Show revisions when viewing lists of posts, pages, or custom post types in the admin dashboard

== Description ==

This plugin allows you to include a list of revisions when viewing a list of posts, pages, or custom post types in the admin dashboard.  It can be configured
to limit the number of revisions shown, the post types it is enabled for, and a prefix & suffix to wrap around the revision title to offset it from the rest of the list.

Thanks to [Pat Ramsey](https://twitter.com/pat_ramsey), [Corey Ellis](https://twitter.com/zzramesses), and [Nick Batick](https://twitter.com/Nick_Batik) for
encouraging me to write this plugin, and [Kenzie Moss](https://twitter.com/kenziemoss) for our WordPress icon.

== Installation ==

1. Upload the wp-revision-list directory to the `/wp-content/plugins/` directory
2. Activate the plugin through the 'Plugins' menu in WordPress
3. Check Settings/WP Revision List to customize


== Changelog ==

= 1.1.6 April 5, 2017 =
* Fixed a PHP warning with the_title filter when global post was not set

= 1.1.5 December 7, 2015 =
* Fixed a PHP warning with the_title() on some installs

= 1.1.4 July 9, 2015 =
* Fixed a bug that was preventing a custom WP_Query from returning posts

= 1.1.3 April 10, 2015 =
* Added user-configurable number of revisions in Screen Options

= 1.0.0 April 9, 2015 =
* Initial release


== Upgrade Notice ==

= 1.1.6 April 5, 2017 =
* Fixed a PHP warning with the_title filter when global post was not set


== Frequently Asked Questions ==

= My custom post type does not show up in the settings? =
Only post types that support revisions work with this plugin.  Make sure you include "'supports' => array( 'revisions' )" in your register_post_type() call.


== Screenshots ==

1. Posts showing revisions
2. Custom post type support
3. Plugin settings
4. Number of revisions configurable per user

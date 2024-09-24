=== Simply add hidden menu items ===
Contributors: dballari
Donate link: https://pay.sumup.com/b2c/QFMKLYCT
Tags: site-editor, patterns, navigation, wp-admin, creator
Requires at least: 6.5
Tested up to: 6.6
Stable tag: 1.0.3
Requires PHP: 7.0
License: GPLv3
License URI: https://www.gnu.org/licenses/gpl-3.0.html

Adds the patterns and menus items in the WordPress administration menu with useful columns, and improves the WordPRess import plugin.

== Description ==

Adds the patterns and menus items in the WordPress administration menu with some useful columns and improves the import process of the WordPress Importer plugin, so that when synced patterns or menus are imported with a different id than the original XML file has, content is replaced with references to the new ids.

To test this plugin, you may use a demo site with the Twentytwentyfour theme activated, for instante.

1. Activate the sahmi plugin and go to the patterns section of the site editor.
2. Edit the Header template part and create a synced pattern from the group block that groups all the blocks included in the header.
3. Repeat the same thing with the Footer template part.
4. Go to the patterns & menus items that you will see bellow the comments menu item and you will find your two recently created synced patterns.
5. Now, you will be able to acces all your customizations from the main admin menu.
6. Feel free to move your customized content to another site with the import / export tool, the referencial integrity of synced patterns and menus will not be lost if you either delete first all the content of the destination site or you use the sahmi plugin.

![patterns](assets/screenshot-1.png)

![menus](assets/screenshot-2.png)

== Changelog ==

= 1.0.3 =
* and thanyou text in admin footer
* corrections after running plugin check

= 1.0.2 =
* fixed a bug on posts_included_in function

= 1.0.1 =
* removed not published posts from included in search query results

= 1.0.0 =
* Initial vesion released

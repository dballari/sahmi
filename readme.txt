=== Simply add hidden menu items ===
Contributors: dballari
Tags: site-editor, patterns, navigation, wp-admin, creator
Requires at least: 6.4
Stable tag: 1.0.0
Requires PHP: 7.0
License: GPLv3
License URI: https://www.gnu.org/licenses/gpl-3.0.html

Adds the patterns and menus items in the WordPress administration menu with some useful columns to the patterns and menus pages, and improves the import process of the WordPress Importer plugin.

== Description ==

Adds the patterns and menus items in the WordPress administration menu with some useful columns to the patterns and menus pages, and improves the import process of the WordPress Importer plugin, so that when synced patterns or menus are imported with a different id than the original XML file has, content is replaced with references to the new ids.

To test this plugin, you may use a demo site with the Twentytwentyfour theme activated, for instante.

1. Activate the sahmi plugin and go to the patterns section of the site editor.
2. Edit the Header template part and create a synced pattern from the group block that groups all the header blocks together.
3. Repeat the same thing with the Footer template part.
4. Go to the patterns & menus items that you will seen bellow the comments menu item and you will find your two recently crated synced patterns.
5. Now, managing the footer and header content is easier, just edit the synced pattern.
6. Feel free to move your customized content to another site with the import / export tool, the referencial integrity of synced patterns and menus will not be lostlost if you either delete first all the content of the destination site or you use the sahmin plugin.

![patterns](assets/screenshot-1.png)

![menus](assets/screenshot-2.png)

== Changelog ==

= 1.0.0 =
* Initial vesion released

<?php

/**
 * @wordpress-plugin
 * Plugin Name: Simply add hidden menu items
 * Plugin URI: https://ballarinconsulting.com/plugins/sahmi
 * Description: Adds some menu items that are normally hidden to speed up up the access to them (patterns & navigation)
 * Version: 1.0.0
 * Author: David B P (AI assisted)
 * Author URI: https://ballarinconsulting.com/acerca
 * License: GNU General Public License v2 or later
 * License URI: http://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: sahmi
 */


// Add a top-level menu item for Block editor
function patterns_menu_item() {
    add_menu_page(
        'All my patterns',              // Page title
        'Patterns',                     // Menu title
        'edit_theme_options',           // Capability
        'edit.php?post_type=wp_block',  // Menu slug
        '',                             // Callback function (empty for no callback)
        'dashicons-editor-table',       // Icon URL or dashicon class
        21                              // Position on the menu (adjust as needed)
    );
}
add_action('admin_menu', 'patterns_menu_item');

// Add a top-level menu item for Block editor
function navigation_menu_item() {
    add_menu_page(
        'Navigation menus',                 // Page title
        'Menus',                            // Menu title
        'edit_theme_options',               // Capability
        'edit.php?post_type=wp_navigation', // Menu slug
        '',                                 // Callback function (empty for no callback)
        'dashicons-list-view',              // Icon URL or dashicon class
        22                                  // Position on the menu (adjust as needed)
    );
}
add_action('admin_menu', 'navigation_menu_item');

// Add several capabilities to the 'editor' role
function add_theme_options_capability() {
    $role = get_role('editor');
    if ($role) {
        $role->add_cap('edit_theme_options');
        $role->add_cap('switch_themes');
        $role->add_cap('import');
        $role->add_cap('export');
    }
}
//add_action('admin_init', 'add_theme_options_capability');


// Function to remove the site-editor menu item for the 'editor' role
function remove_appearance_menu_for_editor() {
    if (current_user_can('creator')) {
        remove_submenu_page('themes.php', 'site-editor.php');
    }
}
//add_action('admin_menu', 'remove_appearance_menu_for_editor');

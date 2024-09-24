<?php
/**
 * @wordpress-plugin
 * Plugin Name: Simply add hidden menu items
 * Plugin URI: https://ballarinconsulting.com/plugins
 * Description: Adds the patterns and menus items in the WordPress administration menu with some useful columns to the patterns and menus pages, and improves the import process of the WordPress Importer plugin.
 * Version: 1.0.3
 * Requires at least: 6.5
 * Requires PHP: 7
 * Author: David Ballarin Prunera
 * Author URI: https://profiles.wordpress.org/dballari/
 * License: GNU General Public License v3
 * License URI: http://www.gnu.org/licenses/gpl-3.0.html
 * Text Domain: sahmi
 */


/*
Simply add hidden menu items is free software: you can redistribute
it and/or modify it under the terms of the GNU General Public 
License as published by the Free Software Foundation, either 
version 2 of the License, or any later version.

Simply add hidden menu items is distributed in the hope that it will
be useful, but WITHOUT ANY WARRANTY; without even the implied
warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
See the GNU General Public License for more details here: 
http://www.gnu.org/licenses/gpl-3.0.html
*/


namespace Sahmi;


/**
 * To use this plugin in debug mode, please, 
 * set the WP_DEBUG constant to true
 * in your wp-config.php file
 * That is, you have to add this line code:
 * define( 'WP_DEBUG', true );
 * And then the IMPORT_DEBUG constant will
 * be activated.
 */

/**
 * Create the creator role when plugin activated
 * like an editor with some more capabilities
 * 
 * @TODO: current issue when accessing the main site
 * editor page with the creator role, page does no load
 * probably due to some missing capability. But the
 * patterns and menus page of the site editar can be
 * accessed with this role.
 */
function add_creator_role() {
    add_role( 'creator', __('Creator', 'sahmi' ), 
        get_role( 'editor' )->capabilities
    );
    $role = get_role('creator');
    if ($role) {
        $role->add_cap('edit_theme_options');
        $role->add_cap('switch_themes');
        $role->add_cap('install_themes');
        $role->add_cap('import');
        $role->add_cap('export');
    }
    return true;
}
register_activation_hook( __FILE__, 
    __NAMESPACE__ . '\add_creator_role' );


/**
 * Remove creator role when plugin deactivated
 */
function remove_creator_role() {
    $role = get_role('creator');
    if($role) {
        $role->remove_cap('edit_theme_options');
        $role->remove_cap('switch_themes');
        $role->remove_cap('import');
        $role->remove_cap('export');
    }
    $result = remove_role( 'creator' );
    return $result;
}
register_deactivation_hook( __FILE__, 
    __NAMESPACE__ . '\remove_creator_role' );

/**
 * Add the plugin's functionality if we are in admin
 */
if( is_admin() ) {


    /**
     * Menu items and columns
     */
    add_action( 'admin_menu', __NAMESPACE__ . '\patterns_menu_item' );
    add_action( 'admin_menu', __NAMESPACE__ . '\navigation_menu_item' );
    add_filter('manage_wp_block_posts_columns', 
        __NAMESPACE__ . '\add_columns_to_patterns_page', 10, 1 );
    add_filter('manage_wp_navigation_posts_columns', 
        __NAMESPACE__ . '\add_columns_to_menus_page', 10, 1 );
    add_action( 'manage_wp_block_posts_custom_column', 
        __NAMESPACE__ . '\add_content_to_pattern_columns', 10, 2 );
    add_action( 'manage_wp_navigation_posts_custom_column', 
        __NAMESPACE__ . '\add_content_to_menus_columns', 10, 2 );
    add_filter('get_edit_post_link', 
        __NAMESPACE__ . '\modify_pattern_edit_link', 10, 3 );
    add_action( 'admin_enqueue_scripts', 
        __NAMESPACE__ . '\patterns_page_css' );
    add_filter( 'post_row_actions', 
        __NAMESPACE__ . '\remove_row_actions', 10, 1  );
    
    
    /**
     * XML Import enhancements: messages and search replace functionality
     */
    add_action( 'import_start',
        __NAMESPACE__ . '\initialize_plugin_options', 10, 0 );
    add_action( 'wp_import_insert_post',
        __NAMESPACE__ . '\add_ref_modified_message', 90, 4 );
    add_action( 'import_end',
        __NAMESPACE__ . '\replace_old_ids', 10, 0 );
}


/**
 * Add patterns menu items
 */
function patterns_menu_item() {
    add_menu_page(
        '',                             // Page title not needed
        __( 'Patterns' ),               // Menu title
        'edit_theme_options',           // Capability
        'edit.php?post_type=wp_block',  // Menu slug
        '',                             // Callback not needed
        'dashicons-editor-table',       // Icon URL
        21                              // Position on the menu
    );
    add_submenu_page(
        'edit.php?post_type=wp_block',
        '',
        __( 'Categories' ),
        'edit_theme_options',
        'edit-tags.php?post_type=wp_block&taxonomy=wp_pattern_category',
        ''
    );
    add_submenu_page(
        'edit.php?post_type=wp_block',
        '',
        __( 'Editor' ),
        'edit_theme_options',
        'site-editor.php?path=%2Fpatterns',
        ''
    );
}


/**
 * Add navigation menu item
 */
function navigation_menu_item() {
    add_menu_page(
        '',                                 // Page title not needed
        __( 'Menus' ),                      // Menu title
        'edit_theme_options',               // Capability
        'edit.php?post_type=wp_navigation', // Menu slug
        '',                                 // Callback not needed
        'dashicons-list-view',              // Icon URL
        22                                  // Position on the menu
    );
    add_submenu_page(
        'edit.php?post_type=wp_navigation',
        '',
        __( 'Editor' ),
        'edit_theme_options',
        'site-editor.php?path=%2Fnavigation',
        ''
    );
}


/**
 * Add Synced, ID (ref), included in and author columns to the patterns page
 */
function add_columns_to_patterns_page($columns) {
    $new_columns = $columns;
    return array_merge($new_columns, [
        'synced' => __( 'Synced' , 'sahmi' ),
        'postid' => __( 'ID (ref)' , 'sahmi' ),
        'included_in' => __( 'Included in', 'sahmi' ),
        'author' => __( 'Author' )
    ]);
}


/**
 * ID (ref), included in and author columns to the menus page
 */
function add_columns_to_menus_page($columns) {
    $new_columns = $columns;
    return array_merge($new_columns, [
        'postid' => __( 'ID (ref)' , 'sahmi' ),
        'included_in' => __( 'Included in', 'sahmi' ),
        'author' => __( 'Author' )
    ]);
}


/**
 * Add content to the columns added in the patterns page
 * Consider a pattern synced if it does not have a wp_pattern_sync_status
 * post_meta asigned to it
 */
function add_content_to_pattern_columns($column_key, $post_id) {
    $synced = !get_post_meta($post_id, 'wp_pattern_sync_status', true);
	if ($column_key == 'synced') {
		if ($synced) {
			echo '<span style="color:green;">'; esc_html_e('Synced', 'sahmi' ); echo '</span>';
		} else {
			echo '<span style="color:red;">'; esc_html_e('Unsynced', 'sahmi' ); echo '</span>';
		}
	}
    if ($column_key == 'postid') {
        echo '<span style="font-weight: 800;text-align: right;">'.esc_html($post_id); echo '</span>';
    }
    if($column_key == 'included_in' && $synced) {
        echo esc_html(posts_included_in($post_id, 'pattern'));
    }
}


/**
 * Add content to the columns added in the menus page
 */
function add_content_to_menus_columns($column_key, $post_id) {
    if ($column_key == 'postid') {
        echo '<span style="font-weight: 800;text-align: right;">'.esc_html($post_id); echo '</span>';
    }
    if($column_key == 'included_in') {
        echo esc_html(posts_included_in($post_id, 'menu'));
    }
}


/**
 * Finds out what posts include a reference to a synced pattern
 * or a reference to a navigation menu
 * by searching the content column of the posts table
 * and looking for somethig like <! wp:block {"ref:123"} /--> where
 * 123 would be the id of the pattern
 * or something like this <! wp:navigation {"ref:123 where
 * 123 would then be the id of the navigation menu
 */
function posts_included_in($ref, $type) {
    global $wpdb;
    if($type == 'pattern') {
        $string = '%<!-- wp:block {"ref":' . $ref . '} /-->%';
        $results = $wpdb->get_results(
            $wpdb->prepare(
                "SELECT ID, post_title, post_type 
                FROM {$wpdb->posts} 
                WHERE `post_content` LIKE %s
                AND `post_status` = 'publish'",
                $string
            )
        );
    }
    if($type == 'menu') {
        $string1 = '%<!-- wp:navigation {"ref":' . $ref . '}%';
        $string2 = '%<!-- wp:navigation {"ref":' . $ref . ',%';
        $results = $wpdb->get_results(
            $wpdb->prepare(
                "SELECT ID, post_title, post_type 
                FROM {$wpdb->posts} 
                WHERE (`post_content` LIKE %s OR `post_content` LIKE %s)
                AND `post_status` = 'publish'",
                $string1,
                $string2
            )
        );
    }
    $string_result = '';
    foreach($results as $result) {
        if ( $result->post_type != 'revision' ) {
            $string_result .= "<a href=\"".get_edit_post_link($result->ID)."\"/>".
            $result->post_title."</a>" .
            format_post_type($result->post_type) . " // ";
        }
    }
    // delete the last //
    $string_result = substr($string_result, 0, strlen($string_result) - 4);
    return $string_result;
}


/**
 * Gives format to the post_type to be shown in the column
 */
function format_post_type($post_type) {
    switch($post_type) {
        case 'wp_template_part':
            return " " . __('template part', 'dualtone');
        case 'wp_template':
            return " " . __('template', 'dualtone');
        case 'wp_block':
            return " " . __('synced pattern', 'dualtone');
        case 'post':
        case 'page':
            return "";
        default:
            return " " . $post_type;
    }
}


/**
 * Modify pattern edit link to go to site-editor instead
 * 
 * link to modify /wp-admin/post.php?post=147&action=edit
 * modified link /wp-admin/site-editor.php?postType=wp_block&postId=147&canvas=edit
 */
function modify_pattern_edit_link($link, $post_id, $context) {
    $post = get_post( $post_id );
    if ( !in_array( $post->post_type, array( 'wp_block' ) ) ) {
        return $link;
    }
    $post_type_object = get_post_type_object( $post->post_type );
    if ( 'display' === $context ) {
        $action = '&amp;canvas=edit';
        $post = '&amp;postId=%d';
    } else {
        $action = '&canvas=edit';
        $post = '&postId=%d';
    }
    $site_editor_link = str_replace(
        'post.php?post=%d',
        'site-editor.php?postType=wp_block', 
        $post_type_object->_edit_link
    );
    return admin_url( 
        sprintf( $site_editor_link . $post . $action, $post_id ) );
}


/**
 * Hide 'add pattern' button
 */
function patterns_page_css($hook) {
    //Enqueue Admin CSS on wp_block page only
    if ( isset( $_GET['post_type'] ) && $_GET['post_type'] == 'wp_block' ) {
        wp_enqueue_style( 'sahmi', 
            plugin_dir_url( __FILE__ ) . 'assets/css/sahmi.css',
            array(),
            get_file_data( __FILE__, array('Version'), 'plugin')
        );
    }
}


/**
 * Remove the action to trash a navigation menu
 * @TODO find out if this is necessary
 */
function remove_row_actions( $actions ) {
    if( get_post_type() === 'wp_navigation' ) {
        //unset( $actions['trash'] );
        //unset( $actions['inline hide-if-no-js'] );
    }   
    return $actions;
}


/**
 * Remove bulk actions for navigation menu posttype
 * https://wordpress.stackexchange.com/questions/18195/how-to-remove-bulk-edit-options
 * @TODO find out if this is necessary
 */
//add_filter( 'bulk_actions-' . 'edit-wp_navigation', '__return_empty_array' );
//$check = apply_filters( 'pre_trash_post', null, $post, $previous_status );
//add_filter( 'pre_trash_post', function($trash, $post, $previous_status) {
//    $post_type = get_post_type( $post->ID );
//    if( $post_type == 'wp_navigation') {
//        return false;
//    }
//    return true;
//}, 10, 3);
//do_action( 'trashed_post', $post_id, $previous_status );

/**
 * Add message when a synced pattern has been imported with a different ID or reference
 * do_action( 'wp_import_insert_post', $post_id, $original_post_id, $postdata, $post );
 * and update the plugin options with a register of the pair old/new ids so that the ids
 * may be replaced at the end of the import process in the content that references them
 */
function add_ref_modified_message(
    $post_id,
    $original_post_id,
    $postdata,
    $post
) {
    if ( $post_id != $original_post_id ) {
        $update = false;
        if(
            $post['post_type'] == 'wp_block' &&
            !is_unsynced($post)
        ) {
            printf(
                /* translators: 1, 2: ID or ref of a pattern */
                esc_html_e(
                    'Synced pattern %1$s imported with ID %2$u different than the original ID %3$u.', 
                    'sahmi'
                ),
                esc_html($post['post_title']),
                esc_html($post_id),
                esc_html($original_post_id)
            );
            $update = true;
            if ( defined( 'IMPORT_DEBUG' ) && IMPORT_DEBUG ) {
                echo '<pre>';
                var_dump($post);
                echo '</pre>';
            }
            echo '<br />';
        }
        if(
            $post['post_type'] == 'wp_navigation' &&
            $post_id != $original_post_id
        ) {
            printf(
                /* translators: 1: post title 2, 3: ID or ref of the menu */
                esc_html_e(
                    'Navigation menu %1$s imported with ID %2$u different than the original ID %3$u.', 
                    'sahmi'
                ),
                esc_html($post['post_title']),
                esc_html($post_id),
                esc_html($original_post_id)
            );
            $update = true;
            if ( defined( 'IMPORT_DEBUG' ) && IMPORT_DEBUG ) {
                echo '<pre>';
                var_dump($post);
                echo '</pre>';
            }
            echo '<br />';
        }
        if ( $update ) {
            update_plugin_options($post['post_type'], $original_post_id, $post_id);
        }
    }
    
}


/**
 * Adds a register to the plugin options with an id to replace when 
 * the importation process finishes
 */
function update_plugin_options(
    $post_type,
    $old_id,
    $new_id
) {
    $ids_to_replace = get_option( 'sahmi_options' );
    $ids_to_replace[] = [$post_type, $old_id, $new_id];
    $result = update_option( 'sahmi_options', $ids_to_replace );
    if ( defined( 'IMPORT_DEBUG' ) && IMPORT_DEBUG ) {
        echo '<p>SAHMI options updated</p>';
        echo '<pre>';
        var_dump($ids_to_replace);
        echo '</pre>';
    }
}

/**
 * Find if the post to be imported is synced or not
 * usign the data of the import plugin
 */
function is_unsynced(
    $post
) {
    // is unsynced if does not have a meta data with key wp_pattern_sync_status
    // or key value is not wp_pattern_sync_status - unsynced
    if(isset($post['postmeta'])) {
        foreach($post['postmeta'] as $meta_item) {
            if(
                $meta_item['key'] == 'wp_pattern_sync_status' &&
                $meta_item['value'] == 'unsynced'
            ) {
                return true;
            }
        }
        return false;
    }
    return false;
}

/**
 * Options API are used to stored the ids that have been saved as an array of arrays
 * Each array represents a post that has been imported with a new id with the format
 * [POST_TYPE, OLD_ID, NEW_ID]
 * At the end of the importation process, old ids will be replaced by the new ones in
 * the post_content column of the wp_posts table for every post that references each id
 */
function initialize_plugin_options() {
    $result = update_option( 'sahmi_options', [] );
    if ( defined( 'IMPORT_DEBUG' ) && IMPORT_DEBUG ) {
        echo '<p>SAHMI options initialized ' . esc_html($result) . '</p>';
    }
}

/**
 * Replaces old ids by new ones in the posts that they are referenced
 */
function replace_old_ids() {
    global $wpdb;
    $ids_to_replace = get_option( 'sahmi_options' );
    if ( !empty( $ids_to_replace ) ) {
        foreach ( $ids_to_replace as $id_to_replace ) {
            $string_to_find = '';
            if ( $id_to_replace[0] == 'wp_block' ) {
                $string_to_find = '<!-- wp:block {"ref":' . $id_to_replace[1];
                $string_to_replace = '<!-- wp:block {"ref":' . $id_to_replace[2];
            } elseif ( $id_to_replace['0'] == 'wp_navigation' ) {
                $string_to_find = '<!-- wp:navigation {"ref":' . $id_to_replace[1];
                $string_to_replace = '<!-- wp:navigation {"ref":' . $id_to_replace[2];
            }
            if( $string_to_find != '' ) {
                $results = $wpdb->get_results(
                    $wpdb->prepare(
                        "UPDATE {$wpdb->posts} 
                        SET post_content = REPLACE( post_content, %s, %s)",
                        $string_to_find, $string_to_replace
                    )
                );
                printf(
                    /* translators: 1: string to find 2: string to replpace */
                    esc_html_e( 'Replacement done: %1$s replaced by %2$s', 
                        'sahmi' ),
                    esc_html($string_to_find),
                    esc_html($string_to_replace)
                );
                echo '<br />';
            }
        }
    }
    delete_option( 'sahmi_options' );
}

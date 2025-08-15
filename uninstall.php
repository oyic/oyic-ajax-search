<?php
/**
 * Uninstall script for Oyic - AJAX Full-Screen Search
 * 
 * This file is executed when the plugin is deleted via the WordPress admin.
 * It removes all plugin data from the database.
 */

// If uninstall not called from WordPress, then exit
if (!defined('WP_UNINSTALL_PLUGIN')) {
    exit;
}

// Delete plugin options
delete_option('oyic_afs_search_cpts');
delete_option('oyic_afs_bg_color');
delete_option('oyic_afs_bg_opacity');
delete_option('oyic_afs_custom_icon');
delete_option('oyic_afs_icon_image');

// For multisite installations
if (is_multisite()) {
    $blog_ids = get_sites(['fields' => 'ids']);
    
    foreach ($blog_ids as $blog_id) {
        switch_to_blog($blog_id);
        
        delete_option('oyic_afs_search_cpts');
        delete_option('oyic_afs_bg_color');
        delete_option('oyic_afs_bg_opacity');
        delete_option('oyic_afs_custom_icon');
        delete_option('oyic_afs_icon_image');
        
        restore_current_blog();
    }
}

// Clear any cached data
wp_cache_flush();

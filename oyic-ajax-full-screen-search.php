<?php
/**
 * Plugin Name: Oyic - AJAX Full-Screen Search
 * Plugin URI:  https://oyic.dev/plugins/ajax-full-screen-search
 * Description: A modern, accessible full-screen AJAX search with custom post type filtering, pagination, and customizable appearance. Compatible with WordPress themes and navigation menus.
 * Version:     1.0.0
 * Requires at least: 5.0
 * Requires PHP: 7.4
 * Author:      Oyic Team
 * Author URI:  https://oyic.dev
 * License:     GPL v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: oyic-ajax-search
 * Domain Path: /languages
 * Network:     false
 * 
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 * 
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Check WordPress version
global $wp_version;
if (version_compare($wp_version, '5.0', '<')) {
    add_action('admin_notices', function () {
        echo '<div class="notice notice-error"><p>';
        esc_html_e('OYIC Ajax Search requires WordPress version 5.0 or higher.', 'oyic-ajax-search');
        echo '</p></div>';
    });
    return;
}

// Check PHP version
if (version_compare(PHP_VERSION, '7.4', '<')) {
    add_action('admin_notices', function () {
        echo '<div class="notice notice-error"><p>';
        esc_html_e('OYIC Ajax Search requires PHP version 7.4 or higher.', 'oyic-ajax-search');
        echo '</p></div>';
    });
    return;
}

// Define plugin version for cache busting and compatibility checks
if (!defined('OYIC_AFS_VERSION')) {
    define('OYIC_AFS_VERSION', '1.0.0');
}

// Define plugin constants
define('OYIC_AFS_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('OYIC_AFS_PLUGIN_URL', plugin_dir_url(__FILE__));

// Load Composer autoloader
if (file_exists(OYIC_AFS_PLUGIN_DIR . 'vendor/autoload.php')) {
    require_once OYIC_AFS_PLUGIN_DIR . 'vendor/autoload.php';
} else {
    add_action('admin_notices', function () {
        echo '<div class="notice notice-error"><p>';
        printf(
            esc_html__('Oyic Search: Missing autoloader. Please run %s in the plugin directory.', 'oyic-ajax-search'),
            '<code>composer install</code>'
        );
        echo '</p></div>';
    });
    return;
}

// Plugin activation hook
register_activation_hook(__FILE__, function() {
    // Check WordPress and PHP versions on activation
    if (version_compare($GLOBALS['wp_version'], '5.0', '<')) {
        deactivate_plugins(plugin_basename(__FILE__));
        wp_die(__('OYIC Ajax Search requires WordPress version 5.0 or higher.', 'oyic-ajax-search'));
    }
    
    if (version_compare(PHP_VERSION, '7.4', '<')) {
        deactivate_plugins(plugin_basename(__FILE__));
        wp_die(__('OYIC Ajax Search requires PHP version 7.4 or higher.', 'oyic-ajax-search'));
    }
});

// Initialize plugin
add_action('plugins_loaded', function () {
    // Load text domain for translations
    $plugin_rel_path = dirname(plugin_basename(__FILE__)) . '/languages';
    load_plugin_textdomain('oyic-ajax-search', false, $plugin_rel_path);
    
    // Initialize frontend search
    if (class_exists('\OYIC\AjaxSearch\Frontend\SearchPlugin')) {
        new \OYIC\AjaxSearch\Frontend\SearchPlugin();
    }

    // Initialize admin settings if class exists
    if (class_exists('\OYIC\AjaxSearch\Admin\Settings')) {
        \OYIC\AjaxSearch\Admin\Settings::init();
    }
});

// Load translations earlier
add_action('init', function() {
    // Load text domain on init as well for better compatibility
    if (!is_textdomain_loaded('oyic-ajax-search')) {
        $plugin_rel_path = dirname(plugin_basename(__FILE__)) . '/languages';
        load_plugin_textdomain('oyic-ajax-search', false, $plugin_rel_path);
    }
});

// Global shortcode registration as fallback
add_action('init', function() {
    if (!shortcode_exists('oyic_search_button')) {
        add_shortcode('oyic_search_button', function($atts) {
            $atts = shortcode_atts([
                'text' => __('Search', 'oyic-ajax-search'),
                'icon' => 'true',
                'class' => '',
                'style' => ''
            ], $atts, 'oyic_search_button');
            
            $show_icon = filter_var($atts['icon'], FILTER_VALIDATE_BOOLEAN);
            $custom_class = $atts['class'] ? ' ' . sanitize_html_class($atts['class']) : '';
            $custom_style = $atts['style'] ? ' style="' . esc_attr($atts['style']) . '"' : '';
            
            // Get search icon (fallback function)
            $icon = '';
            if ($show_icon) {
                // Check for custom SVG icon
                $custom_svg = get_option('oyic_afs_custom_icon', '');
                if (!empty($custom_svg)) {
                    $icon = wp_kses($custom_svg, [
                        'svg' => ['class' => [], 'width' => [], 'height' => [], 'viewBox' => [], 'fill' => [], 'stroke' => [], 'stroke-width' => [], 'stroke-linecap' => [], 'stroke-linejoin' => [], 'aria-hidden' => []],
                        'path' => ['d' => [], 'fill' => [], 'stroke' => []],
                        'circle' => ['cx' => [], 'cy' => [], 'r' => [], 'fill' => [], 'stroke' => []],
                        'rect' => ['x' => [], 'y' => [], 'width' => [], 'height' => [], 'fill' => [], 'stroke' => []],
                        'line' => ['x1' => [], 'y1' => [], 'x2' => [], 'y2' => [], 'stroke' => []],
                        'polyline' => ['points' => [], 'fill' => [], 'stroke' => []],
                        'polygon' => ['points' => [], 'fill' => [], 'stroke' => []]
                    ]);
                } else {
                    // Check for custom image icon
                    $custom_image = get_option('oyic_afs_icon_image', '');
                    if (!empty($custom_image)) {
                        $icon = sprintf(
                            '<img src="%s" alt="%s" class="oyic-search-icon" width="16" height="16" />',
                            esc_url($custom_image),
                            esc_attr__('Search', 'oyic-ajax-search')
                        );
                    } else {
                        // Default SVG icon
                        $icon = '<svg class="oyic-search-icon" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="11" cy="11" r="8"></circle><path d="m21 21-4.35-4.35"></path></svg>';
                    }
                }
            }
            
            $text_content = '';
            if (!empty($atts['text'])) {
                $text_content = '<span class="oyic-search-text">' . esc_html($atts['text']) . '</span>';
            }
            
            $button_id = 'oyic-search-btn-' . wp_rand(1000, 9999);
            
            $output = sprintf(
                '<button type="button" id="%s" class="oyic-search-trigger%s" aria-label="%s"%s>%s%s</button>',
                esc_attr($button_id),
                esc_attr($custom_class),
                esc_attr(!empty($atts['text']) ? $atts['text'] : __('Open search', 'oyic-ajax-search')),
                $custom_style,
                $icon,
                $text_content
            );
            
            // Apply filters to allow customization
            return apply_filters('oyic_search_button_html', $output, $atts);
        });
    }
});
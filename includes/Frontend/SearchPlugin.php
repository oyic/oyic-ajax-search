<?php

namespace OYIC\AjaxSearch\Frontend;

/**
 * Frontend Search Plugin Class
 */
class SearchPlugin {
    
    /**
     * Flag to track if shortcode is used
     */
    private static $shortcode_used = false;
    
    /**
     * Initialize the search plugin
     */
    public function __construct() {
        add_action('wp_ajax_oyic_ajax_search', [$this, 'ajax_search']);
        add_action('wp_ajax_nopriv_oyic_ajax_search', [$this, 'ajax_search']);
        add_action('wp_footer', [$this, 'render_search_modal']);
        
        // Register shortcode immediately
        $this->register_shortcode();
        
        // Add search to navigation menus
        add_filter('wp_nav_menu_items', [$this, 'add_search_to_menu'], 10, 2);
        
        // Only enqueue scripts when needed
        add_action('wp_enqueue_scripts', [$this, 'maybe_enqueue_scripts']);
    }
    
    /**
     * Maybe enqueue scripts and styles based on content
     */
    public function maybe_enqueue_scripts() {
        global $post;
        
        // Check if shortcode is used in content
        if ($post && has_shortcode($post->post_content, 'oyic_search_button')) {
            self::$shortcode_used = true;
            $this->enqueue_scripts();
            return;
        }
        
        // Check for alternative shortcode names
        if ($post && (has_shortcode($post->post_content, 'oyic_search') || has_shortcode($post->post_content, 'search_button'))) {
            self::$shortcode_used = true;
            $this->enqueue_scripts();
            return;
        }
        
        // Check if user explicitly wants to force load scripts everywhere
        $force_load = get_option('oyic_afs_force_load', false);
        if ($force_load) {
            $this->enqueue_scripts();
            return;
        }
        
        // Check if we're on a page that likely has navigation menus
        if (has_nav_menu('primary') || has_nav_menu('main') || has_nav_menu('header') || has_nav_menu('top')) {
            // Only load if this is the front page or a main content page
            if (is_front_page() || is_home()) {
                $this->enqueue_scripts();
                return;
            }
        }
        
        // DO NOT load by default - only load when explicitly needed
        // This prevents unwanted modal showing
    }
    
    /**
     * Enqueue scripts and styles
     */
    public function enqueue_scripts() {
        // Only enqueue on frontend
        if (is_admin()) {
            return;
        }
        
        wp_enqueue_style(
            'oyic-ajax-search-style',
            OYIC_AFS_PLUGIN_URL . 'src/css/search-style.css',
            [],
            OYIC_AFS_VERSION
        );
        
        wp_enqueue_script(
            'oyic-ajax-search-script',
            OYIC_AFS_PLUGIN_URL . 'src/js/search-script.js',
            [], // No dependencies - vanilla JS
            OYIC_AFS_VERSION,
            true
        );
        
        // Get overlay settings and convert to proper format
        $bg_color = get_option('oyic_afs_bg_color', '#000000');
        $bg_opacity = get_option('oyic_afs_bg_opacity', '0.8');
        
        // Convert hex color to rgba with opacity
        $overlay_bg = self::hex_to_rgba($bg_color, floatval($bg_opacity));
        $overlay_opacity = '1'; // Always 1 since opacity is built into rgba
        
        wp_localize_script('oyic-ajax-search-script', 'oyic_ajax_search', [
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('oyic_search_nonce'),
            'settings' => [
                'overlay_bg' => $overlay_bg,
                'overlay_opacity' => $overlay_opacity
            ],
            'strings' => [
                'no_results' => __('No results found', 'oyic-ajax-search'),
                'searching' => __('Searching...', 'oyic-ajax-search'),
                'search_placeholder' => __('Type to search...', 'oyic-ajax-search')
            ]
        ]);
    }
    
    /**
     * Handle AJAX search request
     */
    public function ajax_search() {
        check_ajax_referer('oyic_search_nonce', 'nonce');
        
        $search_term = sanitize_text_field($_POST['search_term'] ?? '');
        
        if (empty($search_term) || strlen($search_term) < 2) {
            wp_send_json_error(['message' => __('Search term must be at least 2 characters', 'oyic-ajax-search')]);
        }
        
        // Get selected post types from settings
        $selected_post_types = get_option('oyic_afs_search_cpts', ['post', 'page']);
        if (empty($selected_post_types) || !is_array($selected_post_types)) {
            $selected_post_types = ['post', 'page'];
        }
        
        $args = [
            's' => $search_term,
            'post_type' => $selected_post_types,
            'post_status' => 'publish',
            'posts_per_page' => apply_filters('oyic_search_results_per_page', 10),
            'meta_query' => [
                'relation' => 'OR',
                [
                    'key' => '_visibility',
                    'value' => 'visible',
                    'compare' => '='
                ],
                [
                    'key' => '_visibility',
                    'compare' => 'NOT EXISTS'
                ]
            ]
        ];
        
        // Allow filtering of search arguments
        $args = apply_filters('oyic_ajax_search_args', $args, $search_term);
        
        $query = new \WP_Query($args);
        $results = [];
        
        if ($query->have_posts()) {
            while ($query->have_posts()) {
                $query->the_post();
                $results[] = [
                    'id' => get_the_ID(),
                    'title' => wp_kses_post(get_the_title()),
                    'excerpt' => wp_kses_post(wp_trim_words(get_the_excerpt(), 20)),
                    'permalink' => esc_url(get_permalink()),
                    'post_type' => sanitize_key(get_post_type()),
                    'thumbnail' => esc_url(get_the_post_thumbnail_url(get_the_ID(), 'thumbnail') ?: '')
                ];
            }
            wp_reset_postdata();
        }
        
        // Allow filtering of search results
        $results = apply_filters('oyic_ajax_search_results', $results, $search_term, $query);
        
        wp_send_json_success([
            'results' => $results,
            'total' => $query->found_posts
        ]);
    }
    
    /**
     * Render search modal HTML
     */
    public function render_search_modal() {
        // Only render if scripts are actually enqueued (meaning they're needed)
        if (!wp_script_is('oyic-ajax-search-script', 'enqueued')) {
            return;
        }
        
        // Prevent duplicate rendering
        static $modal_rendered = false;
        if ($modal_rendered) {
            return;
        }
        $modal_rendered = true;
        ?>
        <div id="oyic-search-modal" class="oyic-search-modal" style="display: none;">
            <div class="oyic-search-overlay"></div>
            <div class="oyic-search-container">
                <div class="oyic-search-header">
                    <input type="text" id="oyic-search-input" placeholder="<?php esc_attr_e('Type to search...', 'oyic-ajax-search'); ?>" autocomplete="off">
                    <button class="oyic-search-close">&times;</button>
                </div>
                <div class="oyic-search-results">
                    <div class="oyic-search-loading" style="display: none;">
                        <?php esc_html_e('Searching...', 'oyic-ajax-search'); ?>
                    </div>
                    <div class="oyic-search-results-list"></div>
                </div>
            </div>
        </div>
        <?php
    }
    
    /**
     * Convert hex color to rgba with opacity
     */
    private static function hex_to_rgba($hex, $opacity) {
        // Remove # if present
        $hex = ltrim($hex, '#');
        
        // Convert hex to rgb
        if (strlen($hex) === 3) {
            $r = hexdec(substr($hex, 0, 1) . substr($hex, 0, 1));
            $g = hexdec(substr($hex, 1, 1) . substr($hex, 1, 1));
            $b = hexdec(substr($hex, 2, 1) . substr($hex, 2, 1));
        } else {
            $r = hexdec(substr($hex, 0, 2));
            $g = hexdec(substr($hex, 2, 2));
            $b = hexdec(substr($hex, 4, 2));
        }
        
        return sprintf('rgba(%d, %d, %d, %.2f)', $r, $g, $b, $opacity);
    }

    /**
     * Get search icon HTML
     */
    public static function get_search_icon($size = 16) {
        // Check for custom SVG icon
        $custom_svg = get_option('oyic_afs_custom_icon', '');
        if (!empty($custom_svg)) {
            return wp_kses($custom_svg, [
                'svg' => ['class' => [], 'width' => [], 'height' => [], 'viewBox' => [], 'fill' => [], 'stroke' => [], 'stroke-width' => [], 'stroke-linecap' => [], 'stroke-linejoin' => [], 'aria-hidden' => []],
                'path' => ['d' => [], 'fill' => [], 'stroke' => []],
                'circle' => ['cx' => [], 'cy' => [], 'r' => [], 'fill' => [], 'stroke' => []],
                'rect' => ['x' => [], 'y' => [], 'width' => [], 'height' => [], 'fill' => [], 'stroke' => []],
                'line' => ['x1' => [], 'y1' => [], 'x2' => [], 'y2' => [], 'stroke' => []],
                'polyline' => ['points' => [], 'fill' => [], 'stroke' => []],
                'polygon' => ['points' => [], 'fill' => [], 'stroke' => []]
            ]);
        }
        
        // Check for custom image icon
        $custom_image = get_option('oyic_afs_icon_image', '');
        if (!empty($custom_image)) {
            return sprintf(
                '<img src="%s" alt="%s" class="oyic-search-icon" width="%d" height="%d" />',
                esc_url($custom_image),
                esc_attr__('Search', 'oyic-ajax-search'),
                intval($size),
                intval($size)
            );
        }
        
        // Default SVG icon
        return sprintf(
            '<svg class="oyic-search-icon" width="%d" height="%d" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="11" cy="11" r="8"></circle><path d="m21 21-4.35-4.35"></path></svg>',
            intval($size),
            intval($size)
        );
    }
    
    /**
     * Add search trigger button
     */
    public static function search_button($text = '', $show_icon = true) {
        $text = $text ?: __('Search', 'oyic-ajax-search');
        
        $icon = '';
        if ($show_icon) {
            $icon = self::get_search_icon(16);
        }
        
        return sprintf(
            '<button class="oyic-search-trigger">%s%s</button>',
            $icon,
            $text ? '<span>' . esc_html($text) . '</span>' : ''
        );
    }
    
    /**
     * Register shortcode
     */
    public function register_shortcode() {
        add_shortcode('oyic_search_button', [$this, 'search_button_shortcode']);
        
        // Also register alternative shortcode names for compatibility
        add_shortcode('oyic_search', [$this, 'search_button_shortcode']);
        add_shortcode('search_button', [$this, 'search_button_shortcode']);
    }
    
    /**
     * Add search button to navigation menus
     */
    public function add_search_to_menu($items, $args) {
        // Check if auto navigation integration is enabled
        $auto_navigation = get_option('oyic_afs_auto_navigation', true);
        
        // Get allowed menu locations (filterable)
        $allowed_locations = apply_filters('oyic_search_menu_locations', ['primary', 'main', 'header', 'top', 'navigation', 'menu-1']);
        
        // Check if we should add to this menu
        $should_add = false;
        
        // Only automatically add if auto navigation is enabled
        if ($auto_navigation && isset($args->theme_location) && in_array($args->theme_location, $allowed_locations)) {
            $should_add = true;
        }
        
        // Also check for custom menu items with #search URL
        if (strpos($items, 'href="#search"') !== false) {
            $should_add = true;
            // Replace #search links with proper search buttons
            $items = $this->convert_search_menu_items($items);
        }
        
        // Allow filtering the decision
        $should_add = apply_filters('oyic_search_add_to_menu', $should_add, $items, $args);
        
        if ($should_add) {
            // Ensure scripts are loaded when menu search is used
            if (!wp_script_is('oyic-ajax-search-script', 'enqueued')) {
                $this->enqueue_scripts();
            }
            
            // Only add automatic search button if no #search links were found
            if (strpos($items, 'oyic-search-trigger') === false) {
                $search_button = $this->create_menu_search_item();
                $items .= $search_button;
                
                // Debug: Add HTML comment to show menu integration is working
                if (defined('WP_DEBUG') && WP_DEBUG) {
                    $items .= '<!-- OYIC Search: Menu integration active for location: ' . esc_html($args->theme_location ?? 'unknown') . ' -->';
                }
            }
        }
        
        return $items;
    }
    
    /**
     * Convert #search menu items to search triggers
     */
    private function convert_search_menu_items($items) {
        // Pattern to match menu items with #search links
        $pattern = '/<a[^>]*href=["\']#search["\'][^>]*>(.*?)<\/a>/i';
        
        $items = preg_replace_callback($pattern, function($matches) {
            $link_text = $matches[1];
            $icon = self::get_search_icon(16);
            
            // Create search button to replace the link
            return sprintf(
                '<button class="oyic-search-trigger menu-search-trigger" aria-label="%s">
                    %s
                    <span class="oyic-search-text">%s</span>
                </button>',
                esc_attr__('Open search', 'oyic-ajax-search'),
                $icon,
                wp_kses_post($link_text)
            );
        }, $items);
        
        return $items;
    }
    
    /**
     * Create menu search item
     */
    private function create_menu_search_item() {
        $icon = self::get_search_icon(18);
        
        return sprintf(
            '<li class="menu-item menu-item-search">
                <button class="oyic-search-trigger menu-search-trigger" aria-label="%s">
                    %s
                </button>
            </li>',
            esc_attr__('Search', 'oyic-ajax-search'),
            $icon
        );
    }
    
    /**
     * Shortcode callback
     */
    public function search_button_shortcode($atts) {
        // Mark that shortcode is used and ensure scripts are loaded
        self::$shortcode_used = true;
        if (!wp_script_is('oyic-ajax-search-script', 'enqueued')) {
            $this->enqueue_scripts();
        }
        
        // Parse shortcode attributes
        $atts = shortcode_atts([
            'text' => __('Search', 'oyic-ajax-search'),
            'icon' => 'true',
            'class' => '',
            'style' => ''
        ], $atts, 'oyic_search_button');
        
        $show_icon = filter_var($atts['icon'], FILTER_VALIDATE_BOOLEAN);
        $custom_class = $atts['class'] ? ' ' . sanitize_html_class($atts['class']) : '';
        $custom_style = $atts['style'] ? ' style="' . esc_attr($atts['style']) . '"' : '';
        
        $icon = '';
        if ($show_icon) {
            $icon = self::get_search_icon(16);
        }
        
        $text_content = '';
        if (!empty($atts['text'])) {
            $text_content = '<span class="oyic-search-text">' . esc_html($atts['text']) . '</span>';
        }
        
        // Generate unique ID for accessibility
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
    }
}

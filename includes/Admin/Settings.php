<?php
namespace OYIC\AjaxSearch\Admin;

class Settings {

    /**
     * Initialize admin hooks
     */
    public static function init() {
        add_action('admin_init', [self::class, 'register_settings']);
        add_action('admin_menu', [self::class, 'add_admin_menu']);
        add_action('admin_notices', [self::class, 'show_menu_debug_notice']);
    }

    /**
     * Register settings and fields
     */
    public static function register_settings() {
        register_setting('oyic_afs_settings_group', 'oyic_afs_search_cpts', [
            'type' => 'array',
            'sanitize_callback' => [self::class, 'sanitize_post_types'],
            'default' => ['post', 'page']
        ]);
        
        register_setting('oyic_afs_settings_group', 'oyic_afs_bg_color', [
            'type' => 'string',
            'sanitize_callback' => 'sanitize_hex_color',
            'default' => '#ffffff'
        ]);
        
        register_setting('oyic_afs_settings_group', 'oyic_afs_bg_opacity', [
            'type' => 'string',
            'sanitize_callback' => [self::class, 'sanitize_opacity'],
            'default' => '0.95'
        ]);
        
        register_setting('oyic_afs_settings_group', 'oyic_afs_custom_icon', [
            'type' => 'string',
            'sanitize_callback' => [self::class, 'sanitize_svg_icon'],
            'default' => ''
        ]);
        
        register_setting('oyic_afs_settings_group', 'oyic_afs_icon_image', [
            'type' => 'integer',
            'sanitize_callback' => 'absint',
            'default' => 0
        ]);
        
        register_setting('oyic_afs_settings_group', 'oyic_afs_force_load', [
            'type' => 'boolean',
            'sanitize_callback' => 'rest_sanitize_boolean',
            'default' => false
        ]);
        
        register_setting('oyic_afs_settings_group', 'oyic_afs_auto_navigation', [
            'type' => 'boolean',
            'sanitize_callback' => 'rest_sanitize_boolean',
            'default' => true
        ]);

        add_settings_section(
            'oyic_afs_main_section',
            __('Search Settings', 'oyic-ajax-search'),
            [self::class, 'render_main_section'],
            'oyic-afs-settings-page'
        );

        add_settings_field(
            'oyic_afs_search_cpts',
            __('Include Post Types', 'oyic-ajax-search'),
            [self::class, 'render_cpts_field'],
            'oyic-afs-settings-page',
            'oyic_afs_main_section'
        );

        add_settings_field(
            'oyic_afs_force_load',
            __('Load on All Pages', 'oyic-ajax-search'),
            [self::class, 'render_force_load_field'],
            'oyic-afs-settings-page',
            'oyic_afs_main_section'
        );

        add_settings_field(
            'oyic_afs_auto_navigation',
            __('Auto Navigation Integration', 'oyic-ajax-search'),
            [self::class, 'render_auto_navigation_field'],
            'oyic-afs-settings-page',
            'oyic_afs_main_section'
        );

        add_settings_section(
            'oyic_afs_appearance_section',
            __('Appearance Settings', 'oyic-ajax-search'),
            [self::class, 'render_appearance_section'],
            'oyic-afs-settings-page'
        );

        add_settings_field(
            'oyic_afs_bg_color',
            __('Background Color', 'oyic-ajax-search'),
            [self::class, 'render_bg_color_field'],
            'oyic-afs-settings-page',
            'oyic_afs_appearance_section'
        );

        add_settings_field(
            'oyic_afs_bg_opacity',
            __('Background Opacity', 'oyic-ajax-search'),
            [self::class, 'render_bg_opacity_field'],
            'oyic-afs-settings-page',
            'oyic_afs_appearance_section'
        );

        add_settings_field(
            'oyic_afs_custom_icon',
            __('Custom Search Icon (SVG)', 'oyic-ajax-search'),
            [self::class, 'render_custom_icon_field'],
            'oyic-afs-settings-page',
            'oyic_afs_appearance_section'
        );

        add_settings_field(
            'oyic_afs_icon_image',
            __('Icon Image (Alternative to SVG)', 'oyic-ajax-search'),
            [self::class, 'render_icon_image_field'],
            'oyic-afs-settings-page',
            'oyic_afs_appearance_section'
        );
    }

    /**
     * Render main section description
     */
    public static function render_main_section() {
        echo '<div style="background: #fff; border: 1px solid #c3c4c7; border-radius: 4px; padding: 15px; margin-bottom: 20px;">';
        echo '<h3 style="margin-top: 0;">' . esc_html__('How to Use', 'oyic-ajax-search') . '</h3>';
        echo '<p>' . esc_html__('Use the shortcode below to display the search icon anywhere on your site:', 'oyic-ajax-search') . '</p>';
        echo '<div style="background: #f6f7f7; border: 1px solid #dcdcde; border-radius: 4px; padding: 10px; font-family: Consolas, Monaco, monospace; font-size: 14px; position: relative;">';
        echo '<code id="oyic-shortcode" style="user-select: all; cursor: text;">[oyic_ajax_search]</code>';
        echo '<button type="button" onclick="copyShortcode()" style="position: absolute; right: 10px; top: 50%; transform: translateY(-50%); background: #2271b1; color: white; border: none; padding: 4px 8px; border-radius: 3px; font-size: 12px; cursor: pointer;">' . esc_html__('Copy', 'oyic-ajax-search') . '</button>';
        echo '</div>';
        echo '<p style="margin-bottom: 0;"><strong>' . esc_html__('For Navigation Menu:', 'oyic-ajax-search') . '</strong></p>';
        echo '<div style="background: #f0f6fc; border: 1px solid #c3c4c7; border-radius: 4px; padding: 10px; margin: 10px 0;">';
        echo '<p style="margin: 0 0 10px 0;"><strong>' . esc_html__('Method 1: Using Menu Editor (Recommended)', 'oyic-ajax-search') . '</strong></p>';
        echo '<ol style="margin: 0;">';
        echo '<li>Go to <a href="' . admin_url('nav-menus.php') . '">Appearance > Menus</a></li>';
        echo '<li>Add any menu item (Custom Link, Page, etc.)</li>';
        echo '<li>Check "Make this a search icon" checkbox in the menu item</li>';
        echo '<li>Save Menu</li>';
        echo '</ol>';
        echo '</div>';
        echo '<div style="background: #fff8e1; border: 1px solid #c3c4c7; border-radius: 4px; padding: 10px; margin: 10px 0;">';
        echo '<p style="margin: 0 0 10px 0;"><strong>' . esc_html__('Method 2: Manual CSS Class', 'oyic-ajax-search') . '</strong></p>';
        echo '<ol style="margin: 0;">';
        echo '<li>Add a Custom Link with URL: <code>#search</code></li>';
        echo '<li>Enable "CSS Classes" in Screen Options</li>';
        echo '<li>Add CSS Class: <code>oyic-search-menu-item</code></li>';
        echo '<li>Save Menu</li>';
        echo '</ol>';
        echo '</div>';
        echo '</div>';
        
        echo '<script>
        function copyShortcode() {
            const shortcodeElement = document.getElementById("oyic-shortcode");
            const text = shortcodeElement.textContent;
            navigator.clipboard.writeText(text).then(function() {
                const button = event.target;
                const originalText = button.textContent;
                button.textContent = "' . esc_js(__('Copied!', 'oyic-ajax-search')) . '";
                button.style.background = "#00a32a";
                setTimeout(function() {
                    button.textContent = originalText;
                    button.style.background = "#2271b1";
                }, 2000);
            });
        }
        </script>';
    }

    /**
     * Render appearance section description
     */
    public static function render_appearance_section() {
        echo '<p>' . esc_html__('Customize the appearance of your search overlay.', 'oyic-ajax-search') . '</p>';
    }

    /**
     * Render CPT selection checkboxes
     */
    public static function render_cpts_field() {
        // Get all public post types
        $all_post_types = get_post_types(['public' => true], 'objects');
        $post_types = [];
        
        // Filter to only include post types with editor and title support
        foreach ($all_post_types as $pt) {
            if (post_type_supports($pt->name, 'editor') && post_type_supports($pt->name, 'title')) {
                $post_types[$pt->name] = $pt;
            }
        }
        
        $selected = (array) get_option('oyic_afs_search_cpts', ['post', 'page']);

        echo '<fieldset>';
        foreach ($post_types as $pt) {
            $checked = in_array($pt->name, $selected) ? 'checked' : '';
            echo '<label style="display: block; margin-bottom: 8px;">';
            echo '<input type="checkbox" name="oyic_afs_search_cpts[]" value="' . esc_attr($pt->name) . '" ' . $checked . ' style="margin-right: 8px;">';
            echo '<strong>' . esc_html($pt->label) . '</strong>';
            echo '<span style="color: #666; font-size: 12px; margin-left: 10px;">(' . esc_html($pt->name) . ')</span>';
            echo '</label>';
        }
        echo '</fieldset>';
        echo '<p class="description">' . esc_html__('Select which post types should be included in the AJAX search. Only post types with title and editor support are shown.', 'oyic-ajax-search') . '</p>';
    }

    /**
     * Render force load field
     */
    public static function render_force_load_field() {
        $force_load = get_option('oyic_afs_force_load', false);
        echo '<label>';
        echo '<input type="checkbox" name="oyic_afs_force_load" value="1" ' . checked($force_load, true, false) . '>';
        echo ' ' . esc_html__('Enable search functionality on all pages', 'oyic-ajax-search');
        echo '</label>';
        echo '<p class="description">' . esc_html__('By default, search scripts only load on the front page and pages with shortcodes. Enable this to load search functionality on all pages (useful if your navigation menu appears site-wide).', 'oyic-ajax-search') . '</p>';
    }

    /**
     * Render auto navigation field
     */
    public static function render_auto_navigation_field() {
        $auto_navigation = get_option('oyic_afs_auto_navigation', true);
        echo '<label>';
        echo '<input type="checkbox" name="oyic_afs_auto_navigation" value="1" ' . checked($auto_navigation, true, false) . '>';
        echo ' ' . esc_html__('Automatically add search icon to navigation menus', 'oyic-ajax-search');
        echo '</label>';
        echo '<p class="description">' . esc_html__('When enabled, search icons will automatically appear in common navigation menu locations (primary, header, main, top). Disable this if you prefer to manually add search to specific menus only.', 'oyic-ajax-search') . '</p>';
    }

    /**
     * Render background color field
     */
    public static function render_bg_color_field() {
        $bg_color = get_option('oyic_afs_bg_color', '#ffffff');
        echo '<input type="color" name="oyic_afs_bg_color" value="' . esc_attr($bg_color) . '" />';
        echo '<p class="description">' . esc_html__('Choose the background color for the search overlay.', 'oyic-ajax-search') . '</p>';
    }

    /**
     * Render background opacity field
     */
    public static function render_bg_opacity_field() {
        $bg_opacity = get_option('oyic_afs_bg_opacity', '0.95');
        echo '<input type="range" name="oyic_afs_bg_opacity" min="0.1" max="1" step="0.05" value="' . esc_attr($bg_opacity) . '" id="oyic_afs_bg_opacity" />';
        echo ' <span id="opacity-value">' . esc_html($bg_opacity) . '</span>';
        echo '<p class="description">' . esc_html__('Set the opacity of the background overlay (0.1 = transparent, 1 = opaque).', 'oyic-ajax-search') . '</p>';
        echo '<script>
            document.addEventListener("DOMContentLoaded", function() {
                const slider = document.getElementById("oyic_afs_bg_opacity");
                const value = document.getElementById("opacity-value");
                if (slider && value) {
                    slider.addEventListener("input", function() {
                        value.textContent = this.value;
                    });
                }
            });
        </script>';
    }

    /**
     * Render custom icon field
     */
    public static function render_custom_icon_field() {
        $custom_icon = get_option('oyic_afs_custom_icon', '');
        echo '<textarea name="oyic_afs_custom_icon" rows="6" cols="50" placeholder="<svg>...</svg>">' . esc_textarea($custom_icon) . '</textarea>';
        echo '<p class="description">' . esc_html__('Enter custom SVG code for the search icon. SVG takes priority over image if both are provided.', 'oyic-ajax-search') . '</p>';
        echo '<p class="description"><strong>' . esc_html__('Example:', 'oyic-ajax-search') . '</strong> <code>&lt;svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"&gt;&lt;path d="..."&gt;&lt;/path&gt;&lt;/svg&gt;</code></p>';
    }

    /**
     * Render icon image field
     */
    public static function render_icon_image_field() {
        $icon_image = get_option('oyic_afs_icon_image', '');
        
        echo '<div class="oyic-image-upload">';
        echo '<input type="hidden" id="oyic_afs_icon_image" name="oyic_afs_icon_image" value="' . esc_attr($icon_image) . '" />';
        echo '<div class="image-preview" style="margin-bottom: 10px;">';
        
        if ($icon_image) {
            $image_url = wp_get_attachment_image_url($icon_image, 'thumbnail');
            if ($image_url) {
                echo '<img src="' . esc_url($image_url) . '" style="max-width: 100px; max-height: 100px; display: block; margin-bottom: 10px;" />';
            }
        }
        
        echo '</div>';
        echo '<button type="button" class="button upload-image-button">' . esc_html__('Choose Image', 'oyic-ajax-search') . '</button>';
        echo ' <button type="button" class="button remove-image-button" style="' . ($icon_image ? '' : 'display:none;') . '">' . esc_html__('Remove Image', 'oyic-ajax-search') . '</button>';
        echo '</div>';
        
        echo '<p class="description">' . esc_html__('Upload an image to use as the search icon. Recommended size: 24x24 pixels. Only used if SVG field above is empty.', 'oyic-ajax-search') . '</p>';
        
        // Add media uploader script
        echo '<script>
        jQuery(document).ready(function($) {
            var mediaUploader;
            
            $(".upload-image-button").click(function(e) {
                e.preventDefault();
                
                if (mediaUploader) {
                    mediaUploader.open();
                    return;
                }
                
                mediaUploader = wp.media({
                    title: "' . esc_js(__('Choose Search Icon', 'oyic-ajax-search')) . '",
                    button: {
                        text: "' . esc_js(__('Choose Image', 'oyic-ajax-search')) . '"
                    },
                    multiple: false,
                    library: {
                        type: "image"
                    }
                });
                
                mediaUploader.on("select", function() {
                    var attachment = mediaUploader.state().get("selection").first().toJSON();
                    $("#oyic_afs_icon_image").val(attachment.id);
                    $(".image-preview").html("<img src=\"" + attachment.sizes.thumbnail.url + "\" style=\"max-width: 100px; max-height: 100px; display: block; margin-bottom: 10px;\" />");
                    $(".remove-image-button").show();
                });
                
                mediaUploader.open();
            });
            
            $(".remove-image-button").click(function(e) {
                e.preventDefault();
                $("#oyic_afs_icon_image").val("");
                $(".image-preview").html("");
                $(this).hide();
            });
        });
        </script>';
    }

    /**
     * Sanitize post types selection
     */
    public static function sanitize_post_types($input) {
        if (!is_array($input)) {
            return ['post', 'page'];
        }
        
        $sanitized = [];
        foreach ($input as $post_type) {
            $post_type = sanitize_key($post_type);
            if (post_type_exists($post_type)) {
                $sanitized[] = $post_type;
            }
        }
        
        return empty($sanitized) ? ['post', 'page'] : $sanitized;
    }

    /**
     * Sanitize opacity value
     */
    public static function sanitize_opacity($input) {
        $opacity = floatval($input);
        return ($opacity >= 0.1 && $opacity <= 1.0) ? strval($opacity) : '0.95';
    }

    /**
     * Sanitize SVG icon
     */
    public static function sanitize_svg_icon($input) {
        if (empty($input)) {
            return '';
        }
        
        // Basic SVG validation - check if it contains SVG tags
        if (strpos($input, '<svg') === false) {
            return '';
        }
        
        // Use wp_kses to sanitize SVG content
        $allowed_svg = [
            'svg' => [
                'xmlns' => true, 'width' => true, 'height' => true, 'viewBox' => true,
                'fill' => true, 'stroke' => true, 'stroke-width' => true,
                'stroke-linecap' => true, 'stroke-linejoin' => true, 'class' => true,
                'style' => true
            ],
            'path' => ['d' => true, 'fill' => true, 'stroke' => true, 'class' => true, 'style' => true],
            'circle' => ['cx' => true, 'cy' => true, 'r' => true, 'fill' => true, 'stroke' => true, 'class' => true],
            'line' => ['x1' => true, 'y1' => true, 'x2' => true, 'y2' => true, 'stroke' => true, 'class' => true],
            'g' => ['class' => true, 'fill' => true, 'stroke' => true, 'style' => true],
            'rect' => ['x' => true, 'y' => true, 'width' => true, 'height' => true, 'fill' => true, 'stroke' => true, 'class' => true],
            'ellipse' => ['cx' => true, 'cy' => true, 'rx' => true, 'ry' => true, 'fill' => true, 'stroke' => true],
            'polygon' => ['points' => true, 'fill' => true, 'stroke' => true],
            'polyline' => ['points' => true, 'fill' => true, 'stroke' => true]
        ];
        
        return wp_kses($input, $allowed_svg);
    }

    /**
     * Add settings page to admin menu
     */
    public static function add_admin_menu() {
        add_options_page(
            __('Oyic Search Settings', 'oyic-ajax-search'),
            __('Oyic Search', 'oyic-ajax-search'),
            'manage_options',
            'oyic-afs-settings',
            [self::class, 'render_settings_page']
        );
    }

    /**
     * Render settings page
     */
    public static function render_settings_page() {
        if (!current_user_can('manage_options')) {
            wp_die(__('You do not have sufficient permissions to access this page.', 'oyic-ajax-search'));
        }
        
        // Enqueue media uploader
        wp_enqueue_media();
        
        ?>
        <div class="wrap">
            <h1><?php esc_html_e('Oyic - AJAX Full-Screen Search Settings', 'oyic-ajax-search'); ?></h1>
            
            <!-- Shortcode Helper Section -->
            <div class="postbox" style="margin-top: 20px;">
                <div class="postbox-header">
                    <h2 class="hndle"><?php esc_html_e('Shortcode Usage', 'oyic-ajax-search'); ?></h2>
                </div>
                <div class="inside">
                    <p><?php esc_html_e('Copy and paste these shortcodes into your posts, pages, or widgets:', 'oyic-ajax-search'); ?></p>
                    
                    <table class="form-table">
                        <tr>
                            <th scope="row"><?php esc_html_e('Basic Search Button', 'oyic-ajax-search'); ?></th>
                            <td>
                                <input type="text" value="[oyic_search_button]" readonly class="regular-text oyic-shortcode-input" />
                                <button type="button" class="button oyic-copy-shortcode" data-shortcode="[oyic_search_button]"><?php esc_html_e('Copy', 'oyic-ajax-search'); ?></button>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row"><?php esc_html_e('Custom Text', 'oyic-ajax-search'); ?></th>
                            <td>
                                <input type="text" value='[oyic_search_button text="Find Products"]' readonly class="regular-text oyic-shortcode-input" />
                                <button type="button" class="button oyic-copy-shortcode" data-shortcode='[oyic_search_button text="Find Products"]'><?php esc_html_e('Copy', 'oyic-ajax-search'); ?></button>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row"><?php esc_html_e('Icon Only', 'oyic-ajax-search'); ?></th>
                            <td>
                                <input type="text" value='[oyic_search_button text="" icon="true"]' readonly class="regular-text oyic-shortcode-input" />
                                <button type="button" class="button oyic-copy-shortcode" data-shortcode='[oyic_search_button text="" icon="true"]'><?php esc_html_e('Copy', 'oyic-ajax-search'); ?></button>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row"><?php esc_html_e('Text Only', 'oyic-ajax-search'); ?></th>
                            <td>
                                <input type="text" value='[oyic_search_button icon="false"]' readonly class="regular-text oyic-shortcode-input" />
                                <button type="button" class="button oyic-copy-shortcode" data-shortcode='[oyic_search_button icon="false"]'><?php esc_html_e('Copy', 'oyic-ajax-search'); ?></button>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row"><?php esc_html_e('With Custom Class', 'oyic-ajax-search'); ?></th>
                            <td>
                                <input type="text" value='[oyic_search_button class="my-custom-class"]' readonly class="regular-text oyic-shortcode-input" />
                                <button type="button" class="button oyic-copy-shortcode" data-shortcode='[oyic_search_button class="my-custom-class"]'><?php esc_html_e('Copy', 'oyic-ajax-search'); ?></button>
                            </td>
                        </tr>
                    </table>
                    
                    <div id="oyic-copy-notice" style="display: none; margin-top: 10px; padding: 8px 12px; background: #d4edda; border: 1px solid #c3e6cb; border-radius: 4px; color: #155724;">
                        <?php esc_html_e('Shortcode copied to clipboard!', 'oyic-ajax-search'); ?>
                    </div>
                </div>
            </div>
            
            <!-- Navigation Menu Setup Section -->
            <div class="postbox" style="margin-top: 20px;">
                <div class="postbox-header">
                    <h2 class="hndle"><?php esc_html_e('Navigation Menu Setup', 'oyic-ajax-search'); ?></h2>
                </div>
                <div class="inside">
                    <p><?php esc_html_e('Add search functionality to your website navigation in multiple ways:', 'oyic-ajax-search'); ?></p>
                    
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin: 15px 0;">
                        <!-- Method 1: Automatic -->
                        <div style="border: 2px solid #00a32a; border-radius: 8px; padding: 15px; background: #f0f6fc;">
                            <h4 style="margin-top: 0; color: #00a32a;">🎯 Automatic Integration (Recommended)</h4>
                            <p><strong>Control via settings!</strong> The search icon can automatically appear in common navigation menu locations:</p>
                            <ul style="margin: 10px 0; padding-left: 20px;">
                                <li>Primary navigation</li>
                                <li>Header menu</li>
                                <li>Main menu</li>
                                <li>Top navigation</li>
                            </ul>
                            <?php 
                            $auto_navigation = get_option('oyic_afs_auto_navigation', true);
                            if ($auto_navigation) {
                                echo '<p style="color: #00a32a; font-weight: bold;">✅ Currently ENABLED - Search icons will appear automatically</p>';
                            } else {
                                echo '<p style="color: #d63384; font-weight: bold;">❌ Currently DISABLED - Use manual setup below</p>';
                            }
                            ?>
                            <p style="color: #666; font-style: italic;">Toggle this in the "Auto Navigation Integration" setting above.</p>
                        </div>
                        
                        <!-- Method 2: Manual Menu -->
                        <div style="border: 2px solid #2271b1; border-radius: 8px; padding: 15px; background: #f6f7f7;">
                            <h4 style="margin-top: 0; color: #2271b1;">⚙️ Manual Menu Setup</h4>
                            <p><strong>For custom control:</strong></p>
                            <ol style="margin: 10px 0; padding-left: 20px;">
                                <li>Go to <a href="<?php echo admin_url('nav-menus.php'); ?>" target="_blank">Appearance → Menus</a></li>
                                <li>Add a <strong>Custom Link</strong></li>
                                <li>Set URL to: <code style="background: #f1f1f1; padding: 2px 4px;">#search</code></li>
                                <li>Set Link Text to: <code style="background: #f1f1f1; padding: 2px 4px;">Search</code></li>
                                <li>Save Menu</li>
                            </ol>
                            <p style="color: #666; font-style: italic;">The plugin automatically detects and converts these links to search buttons.</p>
                        </div>
                    </div>
                    
                    <div style="background: #fff3cd; border: 1px solid #ffeaa7; border-radius: 5px; padding: 12px; margin-top: 15px;">
                        <strong>💡 Pro Tip:</strong> Try the automatic integration first. If your theme doesn't support standard menu locations, use the manual setup method.
                    </div>
                    
                    <?php 
                    // Show current menu locations for debugging
                    $nav_menus = get_nav_menu_locations();
                    if (!empty($nav_menus)) {
                        echo '<div style="background: #e7f3ff; border: 1px solid #b3d9ff; border-radius: 5px; padding: 12px; margin-top: 15px;">';
                        echo '<strong>🔍 Debug Info - Current Menu Locations:</strong><br>';
                        echo '<ul style="margin: 5px 0; padding-left: 20px;">';
                        foreach ($nav_menus as $location => $menu_id) {
                            $menu = wp_get_nav_menu_object($menu_id);
                            echo '<li><code>' . esc_html($location) . '</code> → ' . ($menu ? esc_html($menu->name) : 'No menu assigned') . '</li>';
                        }
                        echo '</ul>';
                        $auto_nav_status = get_option('oyic_afs_auto_navigation', true) ? 'ENABLED' : 'DISABLED';
                        echo '<small>Auto Navigation Integration: <strong>' . $auto_nav_status . '</strong><br>';
                        echo 'Search icons will appear in: primary, main, header, top, navigation, menu-1 (if auto-enabled)</small>';
                        echo '</div>';
                    }
                    ?>
                </div>
            </div>
            
            <form method="post" action="options.php">
                <?php
                settings_fields('oyic_afs_settings_group');
                do_settings_sections('oyic-afs-settings-page');
                submit_button();
                ?>
            </form>
        </div>
        
        <script>
        document.addEventListener('DOMContentLoaded', function() {
            const copyButtons = document.querySelectorAll('.oyic-copy-shortcode');
            const notice = document.getElementById('oyic-copy-notice');
            
            copyButtons.forEach(button => {
                button.addEventListener('click', function() {
                    const shortcode = this.getAttribute('data-shortcode');
                    
                    // Create temporary textarea to copy text
                    const textarea = document.createElement('textarea');
                    textarea.value = shortcode;
                    document.body.appendChild(textarea);
                    textarea.select();
                    
                    try {
                        document.execCommand('copy');
                        
                        // Show success notice
                        notice.style.display = 'block';
                        setTimeout(() => {
                            notice.style.display = 'none';
                        }, 3000);
                        
                        // Update button text temporarily
                        const originalText = this.textContent;
                        this.textContent = '<?php esc_html_e('Copied!', 'oyic-ajax-search'); ?>';
                        this.disabled = true;
                        
                        setTimeout(() => {
                            this.textContent = originalText;
                            this.disabled = false;
                        }, 2000);
                        
                    } catch (err) {
                        console.error('Failed to copy shortcode:', err);
                        alert('<?php esc_html_e('Failed to copy. Please copy manually.', 'oyic-ajax-search'); ?>');
                    }
                    
                    document.body.removeChild(textarea);
                });
            });
        });
        </script>
        <?php
    }

    /**
     * Show debug notice for menu setup
     */
    public static function show_menu_debug_notice() {
        $screen = get_current_screen();
        
        // Only show on nav-menus.php page
        if ($screen && $screen->id === 'nav-menus') {
            echo '<div class="notice notice-info">';
            echo '<p><strong>OYIC Ajax Search:</strong> Three ways to add search to your site:</p>';
            echo '<div style="display: flex; gap: 20px; margin: 10px 0;">';
            
            // Method 1: Auto Navigation
            echo '<div style="flex: 1; border: 1px solid #ddd; padding: 15px; border-radius: 5px;">';
            echo '<h4 style="margin-top: 0;">📱 Automatic Navigation (Recommended)</h4>';
            echo '<p>Search is automatically added to common navigation menu locations including:</p>';
            echo '<ul><li>Primary menu</li><li>Header menu</li><li>Main navigation</li></ul>';
            echo '<p><em>No setup required - just ensure your theme uses standard menu locations.</em></p>';
            echo '</div>';
            
            // Method 2: Shortcode
            echo '<div style="flex: 1; border: 1px solid #ddd; padding: 15px; border-radius: 5px;">';
            echo '<h4 style="margin-top: 0;">🎯 Shortcode Method</h4>';
            echo '<p>Use shortcodes in posts, pages, or widgets:</p>';
            echo '<code>[oyic_search_button]</code><br>';
            echo '<code>[oyic_search_button text="Find Products"]</code><br>';
            echo '<code>[oyic_search_button icon="false"]</code>';
            echo '<p><a href="' . admin_url('admin.php?page=oyic-afs-settings') . '">View all shortcode examples →</a></p>';
            echo '</div>';
            
            // Method 3: Manual Menu
            echo '<div style="flex: 1; border: 1px solid #ddd; padding: 15px; border-radius: 5px;">';
            echo '<h4 style="margin-top: 0;">⚙️ Manual Menu Setup</h4>';
            echo '<ol style="margin: 0; padding-left: 20px;">';
            echo '<li>Add Custom Link with URL: <code>#search</code></li>';
            echo '<li>Set Link Text: <code>Search</code></li>';
            echo '<li>Save Menu</li>';
            echo '</ol>';
            echo '<p><em>The plugin will automatically detect and style these menu items.</em></p>';
            echo '</div>';
            
            echo '</div>';
            echo '</div>';
        }
    }
}
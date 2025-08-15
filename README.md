# Oyic - AJAX Full-Screen Search

A modern, accessible full-screen AJAX search with custom post type filtering, pagination, and customizable appearance.

## Features

- **Full-screen Search Modal**: Beautiful overlay search interface with customizable background and opacity
- **AJAX-powered**: Real-time search results without page refresh
- **Post Type Filtering**: Search only selected post types from admin settings
- **Flexible Icons**: Custom SVG icons, image icons, or default fallback
- **Responsive Design**: Works perfectly on desktop and mobile
- **Accessibility**: Full keyboard navigation and screen reader support
- **Customizable**: Hooks and filters for developers
- **Translation Ready**: Multilingual support with .pot file
- **Security**: Proper nonce verification and input sanitization
- **Error Handling**: Comprehensive error handling and user feedback
- **Vanilla JavaScript**: No jQuery dependency for better performance
- **Tailwind CSS**: Modern utility-first CSS framework integration
- **Dark/Light Mode**: Automatic theme detection with manual toggle support

## Installation

1. Upload the plugin files to `/wp-content/plugins/oyic-ajax-search/`
2. Run `composer install` in the plugin directory (if not already done)
3. Activate the plugin through the 'Plugins' menu in WordPress
4. Go to Settings > Search Settings to configure options

## Usage

### Adding Search Trigger

**Using Shortcode (Recommended):**
```
[oyic_search_button]                                    // Default button with icon and "Search" text
[oyic_search_button text="Search Site"]                // Custom text
[oyic_search_button text="Find" icon="false"]          // Text only, no icon
[oyic_search_button text="" icon="true"]               // Icon only button
[oyic_search_button class="my-custom-class"]           // Add custom CSS class
[oyic_search_button style="margin: 10px;"]             // Add custom inline styles

// Alternative shortcode names (same functionality):
[oyic_search]
[search_button]
```

**Using PHP in theme templates:**
```php
// With text and icon
echo \OYIC\AjaxSearch\Frontend\SearchPlugin::search_button('Search');

// Icon only
echo \OYIC\AjaxSearch\Frontend\SearchPlugin::search_button('', true);

// Text only
echo \OYIC\AjaxSearch\Frontend\SearchPlugin::search_button('Search', false);
```

**Using HTML directly:**
```html
<button class="oyic-search-trigger">
    <svg class="oyic-search-icon" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
        <circle cx="11" cy="11" r="8"></circle>
        <path d="m21 21-4.35-4.35"></path>
    </svg>
    <span>Search</span>
</button>
```

### Keyboard Shortcut

Users can press `Ctrl+K` (or `Cmd+K` on Mac) to open the search modal.

### Navigation Menu Integration

The plugin automatically adds a search icon to your navigation menus in these locations:
- `primary`
- `main`
- `header`
- `top`
- `navigation`
- `menu-1`

**Customize menu locations:**
```php
// Add search to specific menu locations
add_filter('oyic_search_menu_locations', function($locations) {
    return ['my-custom-menu', 'footer-menu'];
});

// Control which menus get search button
add_filter('oyic_search_add_to_menu', function($should_add, $items, $args) {
    // Only add to menus with more than 3 items
    return substr_count($items, '<li') > 3;
}, 10, 3);
```

### JavaScript Integration

```javascript
// Programmatically add search buttons (Vanilla JS)
oyicAddSearchButton('#my-menu', 'Search Site');

// Open search modal programmatically
document.querySelector('.oyic-search-trigger').click();

// Or trigger click event on multiple buttons
document.querySelectorAll('.oyic-search-trigger').forEach(btn => {
    btn.addEventListener('click', () => {
        // Custom logic before opening search
    });
});
```

## Customization

### Hooks and Filters

```php
// Modify search arguments
add_filter('oyic_ajax_search_args', function($args, $search_term) {
    // Only search posts and pages
    $args['post_type'] = ['post', 'page'];
    // Increase results per page
    $args['posts_per_page'] = 20;
    return $args;
}, 10, 2);

// Modify search results
add_filter('oyic_ajax_search_results', function($results, $search_term, $query) {
    // Add custom data to results
    foreach ($results as &$result) {
        $result['custom_field'] = get_post_meta($result['id'], 'custom_field', true);
        $result['author_name'] = get_the_author_meta('display_name', get_post($result['id'])->post_author);
    }
    return $results;
}, 10, 3);
```

### CSS Customization

The plugin uses CSS custom properties for easy theming:

```css
:root {
    /* Overlay customization */
    --oyic-search-overlay-bg: rgba(0, 0, 0, 0.8);
    --oyic-search-overlay-opacity: 1;
    --oyic-search-blur: 4px;
    
    /* Color scheme */
    --oyic-search-primary: #3b82f6;
    --oyic-search-background: #ffffff;
    --oyic-search-text: #111827;
    --oyic-search-border: #e5e7eb;
    --oyic-search-hover: #f9fafb;
}

/* Custom styling */
.oyic-search-container {
    border-radius: 20px;
    max-width: 800px;
}

.oyic-search-trigger {
    background: var(--oyic-search-primary);
    color: white;
    border: none;
    padding: 12px 24px;
    border-radius: 6px;
    cursor: pointer;
}
```

### Icon Customization

**Custom SVG Icon:**
```php
// Set custom SVG icon via admin options
update_option('oyic_afs_custom_icon', '<svg>...custom SVG code...</svg>');
```

**Custom Image Icon:**
```php
// Set custom image icon via admin options
update_option('oyic_afs_icon_image', 'https://example.com/custom-icon.png');
```

**Icon Priority:**
1. Custom SVG icon (if set)
2. Custom image icon (if set and no SVG)
3. Default SVG search icon (fallback)

### Dark Mode Support

The plugin includes comprehensive dark/light mode support:

**Automatic Detection:**
- Respects system `prefers-color-scheme` preference
- Automatically switches between light and dark themes
- Remembers user's manual theme selection

**Manual Control:**
```javascript
// Toggle dark/light mode programmatically
const darkMode = new DarkModeHandler();
darkMode.toggle(); // Switches between light and dark

// Force specific mode
darkMode.enableDarkMode();
darkMode.enableLightMode();
```

**Tailwind CSS Classes:**
The plugin uses Tailwind CSS utility classes for consistent theming:
```css
/* Light mode styles */
.bg-white .text-gray-900 .border-gray-200

/* Dark mode styles (automatic with 'dark:' prefix) */
.dark:bg-gray-800 .dark:text-gray-100 .dark:border-gray-700
```

## Security Features

- **Nonce Verification**: All AJAX requests are protected with WordPress nonces
- **Input Sanitization**: All user inputs are properly sanitized
- **Output Escaping**: All outputs are properly escaped
- **Permission Checks**: Admin functions require proper capabilities
- **Rate Limiting**: Built-in search delays prevent spam

## Performance

- **Debounced Search**: 300ms delay prevents excessive requests
- **Request Cancellation**: Previous requests are cancelled when new ones start
- **Optimized Queries**: Efficient database queries with proper indexing
- **Conditional Loading**: Scripts only load on frontend

## Troubleshooting

### Common Issues

1. **Search not working**: Check that jQuery is loaded and there are no JavaScript errors
2. **No results**: Verify post types and search permissions
3. **Styling issues**: Check for CSS conflicts and ensure styles are loading
4. **AJAX errors**: Check WordPress AJAX setup and nonce verification

### Debug Mode

Enable WordPress debug mode to see detailed error messages:

```php
// In wp-config.php
define('WP_DEBUG', true);
define('WP_DEBUG_LOG', true);
```

## Requirements

- WordPress 5.0+
- PHP 7.4+
- Modern browser with ES6 support
- Composer (for autoloading)

**No jQuery dependency** - Uses pure vanilla JavaScript for better performance!

## Browser Support

- Chrome/Edge 88+
- Firefox 85+
- Safari 14+
- iOS Safari 14+
- Android Chrome 88+

## License

GPL v2 or later

## Changelog

### 1.0.0
- Initial release
- Full-screen search modal
- AJAX search functionality
- Responsive design
- Accessibility features
- Security improvements
- Error handling
- Translation support
<?php

/**
 * Plugin Name: HDWebmobile Recent Sales Notifications
 * Plugin URI: https://hdwebmobile.com/plugins/hdwebmobile-recent-sales-notifications/
 * Description: A recent-purchase popup -- returns only pre-aggregated, non-identifying data, never a raw customer record.
 * Version: 1.0.0
 * Author: htrxuan - Han Tran
 * Author URI: https://hdwebmobile.com/
 * License: GPLv2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: hdwebmobile-recent-sales-notifications
 * Domain Path: /languages
 * Requires Plugins: woocommerce
 * Requires PHP: 7.4
 * Requires at least: 6.9
 */

namespace htrxuan\hdrsn;

if (!defined('ABSPATH')) {
    exit;
}

define('HDRSN_VERSION', '1.0.0');
define('HDRSN_PLUGIN_FILE', __FILE__);
define('HDRSN_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('HDRSN_PLUGIN_URL', plugin_dir_url(__FILE__));

require_once HDRSN_PLUGIN_DIR . 'includes/class-hdrsn-activator.php';

register_activation_hook(__FILE__, array(HDRSN_Activator::class, 'activate'));

add_action('before_woocommerce_init', function () {
    if (class_exists('\Automattic\WooCommerce\Utilities\FeaturesUtil')) {
        \Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility('custom_order_tables', HDRSN_PLUGIN_FILE, true);
    }
});

add_action('plugins_loaded', function () {
    require_once HDRSN_PLUGIN_DIR . 'includes/class-hdrsn-core.php';
    HDRSN_Core::get_instance();
});

add_filter('plugin_action_links_' . plugin_basename(__FILE__), function ($links) {
    $donate_link = '<a href="https://paypal.me/htrxuan/20" target="_blank" rel="noopener noreferrer">' . esc_html__('Donate', 'hdwebmobile-recent-sales-notifications') . '</a>';
    array_unshift($links, $donate_link);
    return $links;
});

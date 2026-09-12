<?php

namespace htrxuan\hdrsn;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * The hub tab. All settings are written through the WordPress Settings API (its own
 * `manage_options` + nonce check). "Show approximate country" defaults OFF -- the safest
 * default is no geographic information at all; an admin has to explicitly opt in to even the
 * coarsest signal (a two-letter country code, never a city, name, or address).
 */
class HDRSN_Admin
{
    const OPTION_KEY = 'hdrsn_settings';

    private static $instance = null;

    public static function get_instance()
    {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct()
    {
        require_once HDRSN_PLUGIN_DIR . 'includes/class-hdrsn-hub.php';
        add_filter('hdwebmobile_hub_tabs', array($this, 'register_hub_tabs'));
        add_action('admin_init', array($this, 'register_settings'));
        add_action('update_option_' . self::OPTION_KEY, array('htrxuan\hdrsn\HDRSN_Repository', 'clear_cache'));
    }

    public static function defaults()
    {
        return array(
            'enabled'          => 0,
            'show_country'     => 0,
            'lookback_days'    => 7,
            'max_notifications' => 15,
            'display_seconds'  => 5,
            'interval_seconds' => 8,
            'position'         => 'bottom-left',
        );
    }

    public static function get_options()
    {
        $opts = get_option(self::OPTION_KEY, array());
        return wp_parse_args(is_array($opts) ? $opts : array(), self::defaults());
    }

    public function register_settings()
    {
        register_setting(self::OPTION_KEY . '_group', self::OPTION_KEY, array(
            'type'              => 'array',
            'sanitize_callback' => array($this, 'sanitize'),
            'default'           => self::defaults(),
        ));
    }

    public function sanitize($input)
    {
        $d = self::defaults();
        return array(
            'enabled'           => !empty($input['enabled']) ? 1 : 0,
            'show_country'      => !empty($input['show_country']) ? 1 : 0,
            'lookback_days'     => isset($input['lookback_days']) ? max(1, min(90, absint($input['lookback_days']))) : $d['lookback_days'],
            'max_notifications' => isset($input['max_notifications']) ? max(1, min(50, absint($input['max_notifications']))) : $d['max_notifications'],
            'display_seconds'   => isset($input['display_seconds']) ? max(2, min(30, absint($input['display_seconds']))) : $d['display_seconds'],
            'interval_seconds'  => isset($input['interval_seconds']) ? max(4, min(120, absint($input['interval_seconds']))) : $d['interval_seconds'],
            'position'          => (isset($input['position']) && in_array($input['position'], array('bottom-left', 'bottom-right'), true)) ? $input['position'] : $d['position'],
        );
    }

    public function register_hub_tabs($tabs)
    {
        $tabs['recent-sales-notifications'] = array(
            'label'  => __('Recent Sales Notifications', 'hdwebmobile-recent-sales-notifications'),
            'order'  => 53,
            'render' => array($this, 'render_page'),
        );
        return $tabs;
    }

    public function render_page()
    {
        $o = self::get_options();
        ?>
        <p><?php esc_html_e('Show a small "Someone just bought..." popup based on recent real orders. The popup is built entirely on the server from a product name and a relative time -- there is no live endpoint a visitor\'s browser calls to fetch order data.', 'hdwebmobile-recent-sales-notifications'); ?></p>

        <form method="post" action="options.php">
            <?php settings_fields(self::OPTION_KEY . '_group'); ?>
            <table class="form-table" role="presentation">
                <tr>
                    <th scope="row"><?php esc_html_e('Enable', 'hdwebmobile-recent-sales-notifications'); ?></th>
                    <td><label><input type="checkbox" name="<?php echo esc_attr(self::OPTION_KEY); ?>[enabled]" value="1" <?php checked(!empty($o['enabled'])); ?> /> <?php esc_html_e('Show the recent-sales popup on the storefront', 'hdwebmobile-recent-sales-notifications'); ?></label></td>
                </tr>
                <tr>
                    <th scope="row"><?php esc_html_e('Show approximate country', 'hdwebmobile-recent-sales-notifications'); ?></th>
                    <td>
                        <label><input type="checkbox" name="<?php echo esc_attr(self::OPTION_KEY); ?>[show_country]" value="1" <?php checked(!empty($o['show_country'])); ?> /> <?php esc_html_e('Include the buyer\'s billing country (never a city, name, or address)', 'hdwebmobile-recent-sales-notifications'); ?></label>
                        <p class="description"><?php esc_html_e('Off by default. Even when on, only a two-letter country code is ever used -- nothing more precise.', 'hdwebmobile-recent-sales-notifications'); ?></p>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><label for="hdrsn_lookback"><?php esc_html_e('Look back this many days', 'hdwebmobile-recent-sales-notifications'); ?></label></th>
                    <td><input type="number" id="hdrsn_lookback" min="1" max="90" class="small-text" name="<?php echo esc_attr(self::OPTION_KEY); ?>[lookback_days]" value="<?php echo esc_attr($o['lookback_days']); ?>" /></td>
                </tr>
                <tr>
                    <th scope="row"><label for="hdrsn_max"><?php esc_html_e('Maximum notifications to cycle through', 'hdwebmobile-recent-sales-notifications'); ?></label></th>
                    <td><input type="number" id="hdrsn_max" min="1" max="50" class="small-text" name="<?php echo esc_attr(self::OPTION_KEY); ?>[max_notifications]" value="<?php echo esc_attr($o['max_notifications']); ?>" /></td>
                </tr>
                <tr>
                    <th scope="row"><label for="hdrsn_display"><?php esc_html_e('Seconds each notification is shown', 'hdwebmobile-recent-sales-notifications'); ?></label></th>
                    <td><input type="number" id="hdrsn_display" min="2" max="30" class="small-text" name="<?php echo esc_attr(self::OPTION_KEY); ?>[display_seconds]" value="<?php echo esc_attr($o['display_seconds']); ?>" /></td>
                </tr>
                <tr>
                    <th scope="row"><label for="hdrsn_interval"><?php esc_html_e('Seconds between notifications', 'hdwebmobile-recent-sales-notifications'); ?></label></th>
                    <td><input type="number" id="hdrsn_interval" min="4" max="120" class="small-text" name="<?php echo esc_attr(self::OPTION_KEY); ?>[interval_seconds]" value="<?php echo esc_attr($o['interval_seconds']); ?>" /></td>
                </tr>
                <tr>
                    <th scope="row"><label for="hdrsn_position"><?php esc_html_e('Position', 'hdwebmobile-recent-sales-notifications'); ?></label></th>
                    <td>
                        <select id="hdrsn_position" name="<?php echo esc_attr(self::OPTION_KEY); ?>[position]">
                            <option value="bottom-left" <?php selected($o['position'], 'bottom-left'); ?>><?php esc_html_e('Bottom left', 'hdwebmobile-recent-sales-notifications'); ?></option>
                            <option value="bottom-right" <?php selected($o['position'], 'bottom-right'); ?>><?php esc_html_e('Bottom right', 'hdwebmobile-recent-sales-notifications'); ?></option>
                        </select>
                    </td>
                </tr>
            </table>
            <?php submit_button(__('Save Settings', 'hdwebmobile-recent-sales-notifications')); ?>
        </form>
        <?php
    }
}

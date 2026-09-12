<?php

namespace htrxuan\hdrsn;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Renders the popup shell and hands the already-anonymised notification list to a small
 * script as inline JSON -- never as a live, fetchable endpoint. The script only ever reads
 * that JSON and writes it into the page with textContent (see assets/js/hdrsn.js); it never
 * requests anything from the server itself and never uses innerHTML.
 */
final class HDRSN_Frontend
{

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
        add_action('wp_footer', array($this, 'render'));
    }

    public function render()
    {
        $opts = HDRSN_Admin::get_options();
        if (empty($opts['enabled']) || is_admin()) {
            return;
        }
        $notifications = HDRSN_Repository::get_notifications();
        if (empty($notifications)) {
            return;
        }

        wp_enqueue_style('hdrsn', HDRSN_PLUGIN_URL . 'assets/css/hdrsn.css', array(), HDRSN_VERSION);
        wp_enqueue_script('hdrsn', HDRSN_PLUGIN_URL . 'assets/js/hdrsn.js', array(), HDRSN_VERSION, true);

        $payload = array();
        foreach ($notifications as $n) {
            $text = '' !== $n['country']
                /* translators: 1: product name, 2: two-letter country code, 3: relative time (e.g. "3 hours") */
                ? sprintf(__('Someone in %2$s bought %1$s %3$s ago', 'hdwebmobile-recent-sales-notifications'), $n['product_name'], $n['country'], $n['time_ago'])
                /* translators: 1: product name, 2: relative time (e.g. "3 hours") */
                : sprintf(__('Someone bought %1$s %2$s ago', 'hdwebmobile-recent-sales-notifications'), $n['product_name'], $n['time_ago']);

            $payload[] = array(
                'text' => wp_strip_all_tags($text),
                'url'  => esc_url_raw($n['product_url']),
            );
        }

        wp_add_inline_script(
            'hdrsn',
            'window.hdrsnData = ' . wp_json_encode(array(
                'items'         => $payload,
                'displayMs'     => max(2000, (int) $opts['display_seconds'] * 1000),
                'intervalMs'    => max(4000, (int) $opts['interval_seconds'] * 1000),
                'position'      => in_array($opts['position'], array('bottom-left', 'bottom-right'), true) ? $opts['position'] : 'bottom-left',
            )) . ';',
            'before'
        );

        echo '<div id="hdrsn-popup" class="hdrsn-popup hdrsn-popup--' . esc_attr($opts['position']) . '" aria-live="polite" hidden></div>';
    }
}

<?php

namespace htrxuan\hdrsn;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Builds the recent-sales notification list -- and is the ONLY place order data is ever
 * touched by this plugin.
 *
 * CVE-2025-12955 (CWE-862 Missing Authorization) in "Live sales notification for WooCommerce"
 * (<= 2.3.39): its `getOrders` function had no authorization check at all, letting an
 * unauthenticated visitor call it directly and extract sensitive customer information.
 *
 * This plugin closes that entire class of bug by construction, not by adding a check to an
 * endpoint: **there is no endpoint**. Nothing here is ever fetched live by client-side script.
 * get_notifications() runs only server-side, on page render, and the only fields it EVER
 * returns are a product name, a product permalink (both already public), a relative
 * "x minutes ago" phrase, and -- only if the admin has explicitly opted in -- a two-letter
 * billing country code. There is no customer name, email, address, order id, order total, or
 * anything else identifying anywhere in the data this method can produce; those fields are
 * never even read out of the order object in the first place, so there is nothing sensitive
 * for a future change to this class to accidentally leak downstream.
 */
class HDRSN_Repository
{
    const CACHE_KEY = 'hdrsn_cache';
    const CACHE_TTL = 10 * MINUTE_IN_SECONDS;

    /**
     * @return array<int, array{product_name:string, product_url:string, time_ago:string, country:string}>
     */
    public static function get_notifications()
    {
        $cached = get_transient(self::CACHE_KEY);
        if (is_array($cached)) {
            return $cached;
        }

        $opts = HDRSN_Admin::get_options();
        $orders = wc_get_orders(array(
            'status'       => array('completed', 'processing'),
            'limit'        => max(1, min(50, (int) $opts['max_notifications'])),
            'orderby'      => 'date',
            'order'        => 'DESC',
            'date_created' => '>' . (time() - max(1, (int) $opts['lookback_days']) * DAY_IN_SECONDS),
        ));

        $notifications = array();
        foreach ($orders as $order) {
            if (!$order instanceof \WC_Order) {
                continue;
            }
            $items = $order->get_items();
            $first_item = reset($items);
            if (!$first_item) {
                continue;
            }
            $product = $first_item->get_product();
            if (!$product || !$product->is_visible()) {
                continue; // Never surface a hidden/private product's name.
            }

            $entry = array(
                'product_name' => $product->get_name(),
                'product_url'  => $product->get_permalink(),
                'time_ago'     => human_time_diff($order->get_date_created()->getTimestamp(), time()),
                'country'      => '',
            );

            if (!empty($opts['show_country'])) {
                // A bare two-letter ISO country code -- never a city, name, or address.
                $country = strtoupper((string) $order->get_billing_country());
                if (preg_match('/^[A-Z]{2}$/', $country)) {
                    $entry['country'] = $country;
                }
            }

            $notifications[] = $entry;

            // Explicitly drop every reference to $order/$first_item/$product here -- nothing
            // beyond the four plain scalars above survives this loop iteration.
            unset($order, $first_item, $product);
        }

        set_transient(self::CACHE_KEY, $notifications, self::CACHE_TTL);
        return $notifications;
    }

    public static function clear_cache()
    {
        delete_transient(self::CACHE_KEY);
    }
}

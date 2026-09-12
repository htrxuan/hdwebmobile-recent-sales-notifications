=== HDWebmobile Recent Sales Notifications ===
Contributors: htrxuan
Donate link: https://paypal.me/htrxuan/20
Tags: woocommerce, social proof, sales notification, fomo, recent sales popup
Requires at least: 6.9
Tested up to: 7.1
Requires PHP: 7.4
Stable tag: 1.0.0
Requires Plugins: woocommerce
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

A "Someone just bought..." popup built entirely on the server -- there is no live endpoint a visitor's browser ever calls for order data.

== Description ==

HDWebmobile Recent Sales Notifications shows a small popup cycling through your recent real orders -- "Someone bought {Product} 2 hours ago". It's built from your store's own completed and processing orders, with no setup beyond turning it on.

= Why this plugin exists =
"Live sales notification for WooCommerce" (versions up to and including 2.3.39) shipped CVE-2025-12955 (CWE-862 Missing Authorization): its `getOrders` function had no authorization check at all, so any unauthenticated visitor could call it directly and extract sensitive customer information.

This plugin closes that entire class of bug by construction -- not by adding a check to an endpoint, but by having **no endpoint at all**:

* **Nothing is ever fetched live.** The notification list is computed once, server-side, on page render, and handed to a small script as a fixed, already-safe payload. There is no AJAX action, and no REST route, that returns order data to a browser -- there is simply nothing for an unauthenticated request to call.
* **Only two, deliberately narrow, fields ever leave the order.** A public product name (and its own public page URL) and a relative time ("2 hours ago"). No customer name, email, address, order id, or order total is ever read out of the order object by this plugin, let alone displayed.
* **Location is off by default, and coarse even when enabled.** An admin can opt in to showing the buyer's billing country -- and only a two-letter country code, never a city, region, or address. The default is no location information at all.
* **The popup script only ever writes plain text.** It never uses `innerHTML` and never fetches anything itself; it reads the fixed payload the server already built and writes it with `textContent`.

= Key Features =
* A small, unobtrusive "Someone just bought..." popup cycling through recent orders
* Configurable look-back window, display duration, and interval between notifications
* Optional (off by default) approximate country -- never anything more precise
* No custom database table; results are cached briefly so nothing hits the database on every page view
* Bottom-left or bottom-right placement

= Limitations (please read before installing) =
* Shows only the first product from each order, not every item
* No per-product opt-out of appearing in notifications in this version
* No admin preview beyond viewing the live storefront

== Installation ==

1. Upload the plugin files to the `/wp-content/plugins/hdwebmobile-recent-sales-notifications` directory, or install the plugin through the WordPress plugins screen directly.
2. Activate the plugin through the 'Plugins' screen in WordPress. WooCommerce must already be installed and active.
3. Go to **WooCommerce > HDWebmobile > Recent Sales Notifications**, tick "Enable", and save.

== How to Use ==

= 1. Turn it on =
On the Recent Sales Notifications tab, tick "Enable" and save. The popup starts appearing on your storefront using your store's own recent orders -- no further configuration required.

= 2. (Optional) Tune it =
Adjust the look-back window, how many notifications to cycle through, and how long each one shows.

== Screenshots ==

1. The recent-sales popup on the storefront.
2. The settings tab under WooCommerce > HDWebmobile.

== Changelog ==

= 1.0.0 =
* Initial release: server-rendered recent-sales popup with no live data-fetching endpoint, showing only a product name, relative time, and an optional, off-by-default, country-level location.

# HDWebmobile Recent Sales Notifications

A "Someone just bought…" popup built entirely on the server — there is no live endpoint a visitor's browser ever calls for order data.

- **WordPress.org:** https://wordpress.org/plugins/hdwebmobile-recent-sales-notifications/
- **Requires:** WordPress 6.9+, WooCommerce, PHP 7.4+
- **License:** GPLv2 or later

## Description

Shows a small popup cycling through recent real orders — "Someone bought {Product} 2 hours ago" — built from your store's own completed/processing orders.

## Why this plugin exists

"Live sales notification for WooCommerce" (≤ 2.3.39) shipped CVE-2025-12955 (CWE-862 Missing Authorization) — its `getOrders` function had no authorization check, letting unauthenticated visitors extract customer data.

Closed by construction — not by fixing an endpoint, but by having none:

* **Nothing is fetched live.** The list is computed server-side on render and handed to a script as a fixed payload — no AJAX action, no REST route, returns order data.
* **Only two narrow fields ever leave the order** — a public product name/URL and a relative time. No name, email, address, order id, or total is ever read.
* **Location is off by default, and coarse even when enabled** — only a two-letter country code, never a city/name/address.
* **The script only writes plain text** — never `innerHTML`, never fetches anything itself.

## Features

* Unobtrusive "Someone just bought…" popup
* Configurable look-back window, display duration, interval
* Optional (off by default) country-level location only
* No custom DB table; briefly cached results
* Bottom-left or bottom-right placement

## Limitations

* Shows only the first product per order
* No per-product opt-out in this version
* No admin preview beyond the live storefront

## Installation

1. Upload to `/wp-content/plugins/hdwebmobile-recent-sales-notifications`, or install through the WordPress plugins screen.
2. Activate. WooCommerce must already be installed and active.
3. Go to **WooCommerce > HDWebmobile > Recent Sales Notifications**, enable, and save.

## License

GPLv2 or later — https://www.gnu.org/licenses/gpl-2.0.html

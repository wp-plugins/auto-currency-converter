=== Auto Currency Converter ===
Contributors: akky
Version 1.0.4
Tags: currency, money, exchange, Japan, yen, Japanese, USA, dollar, JPY, USD
Home: http://wordpress.org/extend/plugins/auto-currency-converter
Support: @akky
Requires at least: 3.3.0
Tested up to: 4.2.2
Stable tag: trunk
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

The plugin makes it easy to add a price in the second currency. US dollars-Japanese Yen (planning to add more currencies) are automatically converted.

===================================================================

== Description ==

When money notations appear in your posts/page text, this plugin detects and adds the converted amount in foreign currency.

For now, this plugin only works between Japanese Yen and US dollars, in English and Japanese.

For example, sentence like "It costs $350." would be "It costs $350(32,854 Japanese yen)."

The conversion is looking up the latest foreign exchange rates.

For bloggers who already did such conversion manually, the plugin has an option to set a date only after when the conversion works.

== Frequently Asked Questions ==

= Some numbers are not detected. What are limitations? =

 - number in text are not detected. For example,
   - twenty dollars
   - few thousand dollars
   - a couple of dollars
 - numbers bigger than billion are not handled
 - billion/trillion are in US style, not UK style (because it can not be supported both notations simultaneously)

= How are Japanese zenkaku digits handled? =

 All Japanese zenkaku digits are converted, then checked and filtered. Because of that, all digits in the text, even though they are not money related, will be converted with regular ASCII digits.

== Installation ==

This section describes how to install the plugin and get it working.

1. Download the plug-in file, extract under your WordPress plugin folder.
2. Log into your Wordpress admin panel.
3. Go to Plug-ins and Activate the "Auto Currency Converter"

== Screenshots ==

== Changelog ==

= 1.0.5 =
* updated Composer autoloader to avoid collision with other plugins using later version of Composer

= 1.0.4 =
* make it work even allow_url_fopen is off.
* fixed - not caching the rates when data-fetching failed.

= 1.0.3 =
* stop trying to convert money in title. only money in body are processed.

= 1.0.2 =
* make the default cache period to one month, to avoid too much updates on feed

= 1.0.1 =
* changed to skip gracefully in case exchange rate are unavailable

= 1.0.0 =
* initial release

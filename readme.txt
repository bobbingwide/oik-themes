=== oik themes server ===
Contributors: bobbingwide
Donate link: http://www.oik-plugins.com/oik/oik-donate/
Tags:  themes, server, shortcodes, FREE, premium
Requires at least: 4.2
Tested up to: 4.7.2
Stable tag: 1.2.1
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

== Description ==
oik-themes server for FREE, Themium and Bespoke oik-themes


== Installation ==
1. Upload the contents of the oik-themes plugin to the `/wp-content/plugins/oik-themes' directory
1. Activate the oik-themes plugin through the 'Plugins' menu in WordPress
1. Visit Settings > Permalinks to properly register the new custom post types ( oik-themes, oik_themeversion and oik_themiumversion )
1. To support oik Premium themes use oik options > Server settings to define a secure folder used to store uploaded zip files
1. Also install and activate either oik-edd or oik-woo to allow the creation of API keys

== Frequently Asked Questions ==

= Can I deliver bespoke themes? =
Can I deliver unique themes to my customer without having the themes listed on the website?

Answer: Yes. You can have them listed as premium themes (themium) but don't associate them with a product.
No users will be able to purchase the product so only those with an API key and who already have the theme installed will be able to download the updates.
OR use Bespoke themes.

= How do I do this? =
One way of doing this:

1. create the product
2. create the theme attaching the product to the theme
3. buy the product
4. get an API key
5. unpublish the product
6. create the themium version
7. continue to create themium versions

only those users with API keys will be able to upgrade.

= Is there another way? =
Yes, classify the theme as "Bespoke"
This won't create a download button but the theme will be accessible if the user knows the theme name.

== Screenshots ==
1. oik themes server settings
2. edit oik-theme
3. display oik-theme

== Upgrade Notice ==
= 1.2.1 = 
Display of tabs now more inline with oik-plugins.

= 1.2.0 = 
Upgrade for improved display of tabs.

= 1.1.0 =
Changes for Genesis-a2z theme on WP-a2z. Tested with WordPress 4.5.2

= 1.0.0 = 
Pre-requisite to using the genesis-oik theme on oik-plugins.

= 0.7 = 
Needed to deliver theme upgrades when oik-bwtrace is activated.

= 0.6 =
Changed plugin dependency versions. Now supported on WordPress 3.9 or higher

= 0.5 = 
Required for oik-plugins.com. Now depends on oik v2.1

= 0.4 = 
Requires oik-plugins v1.4, oik v2.1-alpha.0927 and oik-fields v1.19 or higher

= 0.3 = 
After installing you need to set the "required_version" and "compatible_up_to" categories for each theme.
Depends on oik-plugins v1.2 or higher

= 0.2 = 
Dependent upon oik v2.1-alpha.0802, oik-fields v1.19.0802 and oik-plugins v1.2 

== Changelog ==
= 1.2.1 = 
* Changed: No longer display 'Purchasable product: FREE' [github bobbingwide oik-themes issue 7]
* Changed: Display tabbed information like oik-plugins [github bobbingwide oik-themes issue 2]
* Tested: With WordPress 4.7.2 and WordPress Multisite

= 1.2.0 = 
* Added: Improve support for Theme types [github bobbingwide oik-themes issue 5]
* Added: Improve display of tabs [github bobbingwide oik-themes issue 2]
* Added: Support REST for oik-themes [github bobbingwide oik-themes issue 6]
* Changed: Associate the _component_version virtual field to oik-themes [github bobbingwide oik-themes issue 4]

= 1.1.0 =
* Added: Cater for Template theme's shortcodes [github bobbingwide oik-themes issue 2]
*	Added: GitHub link field [github bobbingwide oik-themes issue 3]
* Changed: docblock improvements
* Fixed: oikth_download() may need to include bw_posts.inc [github bobbingwide oik-themes issue 1]

= 1.0.0 =
* Changed: [oikth_download] shortcode now allows download of a previous theme version
* Changed: Now using Semantic versioning
* Changed: Preparing for Internationalization and Localization
* Changed: Custom post types support has_archive
* Tested: With WordPress 4.2 and above 

= 0.7 = 
* Changed: Updated logic to handle display of titles in tables. Includes: title and excerpt
* Changed: oik-themes can now be shown in an archive
* Changed: oikp_lazy_redirect() sets DOING_AJAX to prevent oik-bwtrace from sending output back to the client 
* Dropped: Support for WordPress 3.9 or earlier
* Tested: With WordPress 4.1 and WordPress Multisite
* Changed: Now depends on oik v2.4, oik-plugins v1.10 and oik-fields v1.39 or higher

= 0.6 = 
* Changed: Plugin dependency versions.
* Changed: oikth_update_check() returns the theme's slug in $response->theme
* Changed: Use dashicons for oik-themes, oik_themeversion and oik_themiumversion 

= 0.5 =
* Changed: Renamed some functions causing "Cannot redeclare" fatal errors

= 0.4 =
* Added: Field _oikth_demo - for a link to a live demonstration of the theme
* Added: Field _oikth_template - noderef to "oik-themes" used for child themes
* Changed: Supports ( manual ) theming of download buttons; Use alink() rather than art_button() for download buttons. 
* Changed: Re-labelled "Other" as "Bespoke"

= 0.3 =
* Changed: Removed _oikth_requires and _oikth_tested fields - having previously switched to using Custom categories
* Changed: Added plugin dependency on oik-plugins:1.2
* Added: Started adding support for better display of theme details in an iframe - when Theme updates are being viewed. oikth_inspect_request_uri()
* Changed: oik-themes Custom Post type now supports Excerpt and Featured image
* Changed: Displays Description rather than Name in admin pages and [bw_table] shortcode
* Changed: Download buttons determined from the theme classification: CPT - oik_themeversion/oik_themiumversion - then Theme type (_oikth_type )
 
= 0.2 = 
* Changed: Now uses custom categories for "Requires" and "Compatible up to" 
* Note: Requires oik-plugins for definition of custom categories AND other functions to set server fields. 

= 0.1 =
* Added: First version for delivering oik-plugins's themes: oik2012, nivo2011 and oik20120


== Further reading ==
If you want to read more about the oik plugins then please visit the
[oik plugin](http://www.oik-plugins.com/oik) 


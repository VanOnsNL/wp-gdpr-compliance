=== WP GDPR Compliance ===
Contributors: donnyoexman, michaelvt, jeffreyvisser, van-ons
Tags: gdpr, law, regulations, compliance, data, protection, privacy, data protection, eu, avg, comments, woocommerce, wc, contact form 7, cf7
Requires at least: 4.5
Tested up to: 4.9.4
Requires PHP: 5.3
Stable tag: 1.3.1
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

This plugin assists website owners to comply with European privacy regulations (GDPR). By May 24th, 2018 you have to comply to avoid large fines.

== Description ==

ACTIVATING THIS PLUGIN DOES NOT GUARANTEE YOU FULLY COMPLY WITH GDPR. PLEASE CONTACT A GDPR CONSULTANT OR LAW FIRM TO ASSESS NECESSARY MEASURES.

This plugin assists website and webshop owners to comply with European privacy regulations known as GDPR. By May 24th, 2018 your site or shop has to comply to avoid large fines.

WP GDPR Compliance currently supports Contact Form 7 (>= 4.6), Gravity Forms (>= 1.9), WooCommerce (>= 2.5.0) and WordPress Comments. Additional plugin support will follow soon.

We constantly update this plugin to take care of more GDPR related issues. To check our development roadmap please visit [wpgdprc.com](https://www.wpgdprc.com/roadmap/ "Roadmap").

== Installation ==

1. Upload the plugin files to the `/wp-content/plugins/wp-gdpr-compliance` directory, or install the plugin through the WordPress plugins screen directly.
2. Activate the plugin through the 'Plugins' screen in WordPress
3. Go to Tools -> WP GDPR Compliance

== Frequently Asked Questions ==

You'll find answers to many of your questions on [wpgdprc.com](https://www.wpgdprc.com/faq/ "Frequently Asked Questions").

== Screenshots ==

1. Automatically add GDPR checkboxes to some of your favourite plugins.
2. Overview of the view and delete requests by your site's visitors.
3. Control the link to your privacy policy and activate the request user data page.

== Changelog ==

= 1.3.1 =
*Release date: May 8th, 2018*
* Shows an error message if the required request user data tables couldn't be created.

= 1.3 =
*Release date: May 7th, 2018*
* Added the request user data page. You can enable it in the Settings tab.
* The newly created page contains a shortcode which allows visitors to request their data. WordPress Users, WordPress Comments and WooCommerce orders linked to their email address are then send to that email address.
* The request user data page becomes the delete user page when visited through this email. The link in the email is available for 24 hours (cronjob) and linked to the visitors' IP and current session.
* Delete requests end up in the new Requests tab. Click on 'Manage' to view a request and tick the checkbox to anonymise. Make sure to take care of these requests as quickly as possible!
* For WordPress Users 'anonymise' means first and last name, display name, nickname and email address are substituted by the corresponding field name in the database.
* For WordPress Comments 'anonymise' means author name, email address and IP address are substituted by the corresponding field name in the database.
* For WooCommerce orders 'anonymise' means billing and shipping details are substituted by the corresponding field name in the database.

= 1.2.4 =
*Release date: April 3rd, 2018*
* Show a notice when Jetpack is installed
* Fixed a bug with WordPress Comments

= 1.2.3 =
*Release date: March 29th, 2018*
* Storage of explicit consent timestamp
* Return of the settings tab
* Added ability to include your privacy policy page
* Added a couple of apply_filters()
* Small styling changes
* Added .POT file to translate this plugin (Thanks for translating!)

= 1.2.2 =
*Release date: March 2nd, 2018*
* Fixed a bug with WordPress Comments
* Added countdown to GDPR deadline (May 25, 2018)
* Added ability to add custom error messages to Contact Form 7 and Gravity Forms
* Added ability to add HTML tags to the texts and error messages

= 1.2.1 =
*Release date: February 26th, 2018*
* Fixed a bug with WordPress comments
* Fixed a bug with default checkbox texts

= 1.2 =
*Release date: February 23rd, 2018*
* Limit data shared with WordPress when updating the core.
* Added minimum supported version for Contact Form 7 (version 4.6)
* Added minimum supported version for Gravity Forms (version 1.9)
* Added minimum supported version for WooCommerce (version 2.5.0)
* Delete all data created by the plugin after deactivating integrations or uninstalling the plugin.
* Moved the position of the GDPR checkbox in the WordPress Comments form. (directly above the submit button)
* Moved the position of the GDPR checkbox in WooCommerce. (directly above the submit button)
* Removed the global "Error message" field.
* Fixed an issue with an older version of Contact Form 7.
* Small styling changes.

= 1.1.2 =
*Release date: January 19th, 2018*
* Added default error message.
* Small bugfixes.

= 1.1.1 =
*Release date: January 16th, 2018*
* Added screenshots.
* Added textual changes.

= 1.1 =
*Release date: January 16th, 2018*
* Added 'Contact Form 7' integration.
* Added 'WooCommerce' integration.
* Added 'WordPress Comments' integration.
* Small bugfixes.

= 1.0 =
*Release date: November 4th, 2017*
* Initial release.
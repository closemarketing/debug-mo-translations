=== Debug MO Translations ===
Contributors: closemarketing, davidperez, pedromendonca
Tags: bootstrap, shortcodes, content, ui, bootstrap helper
Donate link: https://close.marketing/go/donate/
Requires at least: 3.0
Tested up to: 5.8.2
Stable tag: 1.2
Version: 1.2

Debugs all translated files that are loaded and not in Wordpress.

== Description ==
It gives you more information about all domains and translated mo files loaded in the actual installation, so you can debug what's the problem with a plugin, theme, etc.

Only shows the debug info if you're logged in in the footer.

[Official Repository Github](https://github.com/closemarketing/debug-mo-translations).
Fork and add make suggestions to the plugin!

== Installation ==

Extract the zip file and just drop the contents in the wp-content/plugins/ directory of your WordPress installation and then activate the Plugin from Plugins page.


== Developers ==
[Official Repository Github](https://github.com/closemarketing/debug-mo-translations)

== Changelog ==

= 1.2 =
*	Contributions from pedromendonca (Thanks!): Use in_admin_footer and wp_frontend hooks instead of shutdown hook and Prepare plugin for i18n.
*    MultilingualPress supported.

= 1.1 =
*	Fixed Return early during AJAX requests by [Nickcernis](https://github.com/nickcernis).

= 1.0 =
*	First released.

== Links ==
*	[Closemarketing](https://close.marketing/)
*    [Closemarketing plugins](https://profiles.wordpress.org/closemarketing/#content-plugins)

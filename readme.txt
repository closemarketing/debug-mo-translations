=== Debug MO Translations ===
Contributors: closemarketing, davidperez, pedromendonca
Tags: bootstrap, shortcodes, content, ui, bootstrap helper
Donate link: https://close.marketing/go/donate/
Requires at least: 4.6
Tested up to: 5.8.2
Stable tag: 1.3
Version: 1.3

Debugs all translated files that are loaded and not in WordPress.

== Description ==
It gives you more information about all domains and translated mo files loaded in the actual installation, so you can debug what's the problem with a plugin, theme, etc.

Only shows the debug info if you're logged in in the footer.

== Installation ==

Extract the zip file and just drop the contents in the wp-content/plugins/ directory of your WordPress installation and then activate the Plugin from Plugins page.

== Developers ==
[Official Repository GitHub](https://github.com/closemarketing/debug-mo-translations)

== Changelog ==
= 1.3 =
*	Contributions from pedromendonca (Thanks!): Better output in admin and frontend (see screenshots!).

= 1.2 =
*	Contributions from pedromendonca (Thanks!): Use in_admin_footer and wp_frontend hooks instead of shutdown hook and Prepare plugin for i18n.
*	MultilingualPress supported.

= 1.1 =
*	Fixed Return early during AJAX requests by [Nickcernis](https://github.com/nickcernis).

= 1.0 =
*	First released.

== Links ==
*	[Closemarketing](https://close.marketing/)
*	[Closemarketing plugins](https://profiles.wordpress.org/closemarketing/#content-plugins)

=== Plugin Name ===
Extended API over XMLRPC

Contributors: realflash
Donate link: https://humanism.org.uk/donate/
Tags: api, xml-rpc
Requires at least: 3.0.1
Tested up to: 4.5.3
Stable tag: 0.9.9
License: GPLv3 or later
License URI: http://www.gnu.org/licenses/gpl-3.0.html

A WordPress plugin that allows access to the whole WP API via XML-RPC.

== Description ==

## What does it do? ##

The full WP API is comprehensive, but most of it is only available to plugin code, not remotely. The official WP XML-RPC API is much smaller. This plugin opens up access to that full internal API remotely so that you can do whatever you want over XML-RPC. 

## Which methods does it allow me to call?

Anything in the [Wordpress Function Reference](https://codex.wordpress.org/Function_Reference) that your username has access to.

## How do I use it?

First you should make sure that you have working code that can interact with the standard supported methods in the [XML-RPC WordPress API](https://codex.wordpress.org/XML-RPC_WordPress_API). Once you do, you are ready to use this plugin. See also [About Wordpress XML-RPC Support](https://codex.wordpress.org/XML-RPC_Support).

Simply set the method name to wpext.callWpMethod, and then pass the WP API method name that you want to call as the first parameter, followed by any other parameters meeded by that method. Exactly how you do this depends upon the XML-RPC client library you are using in your code. So (for example), if you are currently using the XML-RPC API like this:

	$xmlrpc_client->call('wp.getComment', 5);

then instead do this:

	$xmlrpc_client->call('wpext.callWpMethod', 'wp_create_user', $new_username, $new_password, $new_email);

or

	$xmlrpc_client->call('wpext.callWpMethod', 'wp_delete_user', $id_to_delete);
	
These examples are pseudo-code: they do not relate to a specific XML-RPC client. 

## Settings

There is only one, and that is the list of functions that should be allowed over XML-RPC. See Settings > Extended API in your dashboard to set it. To allow one additional function, simply set it to the name of that function:

	wp_create_user

To specify multiple functions, separate with commas:

	wp_create_user,wp_delete_user[,method3]...

To protect you from yourself, this setting comes with a dummy value that does not correspond to a real method. This is to force you to think about what methods you really need to enable. If you set this value to the empty string (blank), all methods are allowed.

## Security

There are good reasons why the XML-RPC API only has limited methods. Think carefully about whether this plugin is right for your situation, and only enable the methods you really need.

== Installation ==

1. Upload the plugin files to the `/wp-content/plugins/plugin-name` directory, or install the plugin through the WordPress plugins screen directly.
1. Activate the plugin through the 'Plugins' screen in WordPress.
1. Use the Settings > Extended API screen to configure the plugin.

== Frequently Asked Questions ==

= Where are all the FAQs? =

Nobody has asked any questions yet, so there are none that are frequently asked.

== Screenshots ==

1. Example code - how I use this plugin. 

== Changelog ==

= 0.9.9 = 
* Corrected plugin version so that everything matches *

= 0.9.8 =
* Corrected plugin name

= 0.9.7 =
* Put an actual plugin name in the readme

= 0.9.6 =
* Handle methods that require a list of arguments instead of an array

= 0.9 =
* Added missing settings screen
* Removed namespace setting
* Defaulted to disallowing all methods
* Added documentation
* Separated "not permitted" and "doesn't exist" errors

= 0.5 =
* Original version by Michael Grosser

== Upgrade Notice ==

= 0.9 =
More secure default to disallow all methods


# extended-xmlrpc-api
A WordPress plugin that allows access to the whole WP API via XML-RPC, not just the approved methods.

## What does it do?

The full WP API is comprehensive, but most of it is only available to plugin code, not remotely. The official WP XML-RPC API is much smaller. This plugin opens up access to that full internal API remotely so that you can do whatever you want over XML-RPC. 

## Which methods does it allow me to call?

Anything in the [Wordpress Function Reference](https://codex.wordpress.org/Function_Reference) that your username has access to.

## How do I use it?

### Install

 * Search for this plugin in the WordPress plugin market place
 * Install it
 * Enable it
 * Choose which methods you want enabled in settings

### Usage

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

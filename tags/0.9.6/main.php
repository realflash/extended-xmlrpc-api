<?php
/*
Plugin Name: Extended API
Plugin URI: https://github.com/realflash/extended-xmlrpc-api
Description: Provides access to the entire WP API over XML RPC rather than being limited to using only the pre-defined WP XML-RPC methods.
Author: Ian Gibbs
Version: 0.9.6
Author URI: https://github.com/realflash
*/

//Check the WP version - Requires 3.0+
global $wp_version;
$exit_msg = 'Extended API Requires WordPress 3.0 or newer. You are currently running WordPress ' . $wp_version . '. <a href="http://codex.wordpress.org/Upgrading_WordPress">Please update!</a>';
if (version_compare($wp_version, "3.0", "<"))
{
    exit($exit_msg);
}

//Add a filter for XML RPC Methods
add_filter( 'xmlrpc_methods', 'extapi_createXmlRpcMethods' );

/**
 * Generate the Response
 *
 * @param methods Array - list of existing XMLRPC methods
 * @return methods Array - list of updated XMLRPC methods
 */
function extapi_createXmlRpcMethods($methods)
{
    $methods['wpext.callWpMethod'] = 'extapi_respondToCall';
    return $methods;
}

/**
 * Generate the Response
 *
 * @param Array (username, password, wp method name, arguments for method)
 * @return Mixed (response from WP method)
 */
function extapi_respondToCall($params)
{
    //Separate Params from Request
    $username = $params[0];
    $password = $params[1];
    $method   = $params[2];

    // List of Allowed WP Functions

    global $wp_xmlrpc_server;
    // Let's run a check to see if credentials are okay
    if( !$user = $wp_xmlrpc_server->login($username, $password) ) {
            return $wp_xmlrpc_server->error;
    }

    if(!function_exists($method))
	{
		return new IXR_Error( 401, __( "WP API method $method does not exist." ) );
 	}

    $extapi_allowed_functions = explode(",", get_option('extapi_allowed_functions'));
	if(isset($extapi_allowed_functions) && ! in_array($method, $extapi_allowed_functions))
    {
		return new IXR_Error( 401, __( "WP API method $method is not allowed by your current plugin settings. See Settings > Extended API to enable it." ) );
    } 
	else	
	{
    	$first_arg = $params[3];
		if(is_array($first_arg))
		{	# Assume all the args are in array, such as for wp_insert_user
        	return call_user_func($method, $first_arg);
		}
		else if(is_scalar($first_arg))
		{	# Assume all the args are a list of scalars, such as for add_user_meta
			# Make an array of the remaining arguments
			$args = [];
			for($i = 3; $i < count($params); $i++)
			{
				$args[] = $params[$i];
			}
			return call_user_func_array($method, $args);
		}
    }
}

/*
 * Add a Settings page for this Plugin.
 */
add_action('admin_menu', 'extapi_create_menu');
function extapi_create_menu()
{
    add_options_page( 'Extended API Settings', 'Extended API', 'manage_options', 'extapisettings', 'extapi_settings_page');
}

/*
 * Function to display the settings page.
 */
function extapi_settings_page()
{
?>
<div>
<h2>Extended API Settings</h2>
Options relating to the Extended API plugin.
<form action="options.php" method="post">
<?php settings_fields('extapi_settings'); ?>
<?php do_settings_sections('extapi_settings_page'); ?>
<input name="Submit" type="submit" value="<?php esc_attr_e('Save Changes'); ?>" />
</form></div>
<?php
}

/*
 * Register the custom options for this plugin.
 */
add_action( 'admin_init', 'extapi_register_settings' );
function extapi_register_settings()
{
    //register settings
    register_setting( 'extapi_settings', 'extapi_allowed_functions' );
	add_settings_section('extapi_settings_main', '', 'extapi_renderSettingsMain', 'extapi_settings_page');
	add_settings_field('extapi_allowed_functions', 'Allowed Functions', 'extapi_renderAllowedFunctions', 'extapi_settings_page', 'extapi_settings_main');
}

function extapi_renderSettingsMain() {}

function extapi_renderAllowedFunctions() {
	$functions = get_option('extapi_allowed_functions');
	
	echo "<input id='plugin_text_string' name='extapi_allowed_functions' size='80' type='text' value='{$functions}' /><br/>
Which additional WordPress API functions can be called through XMLRPC. Name only, no brackets or parameters. Separate functions with a comma. If this is blank, then all functions are allowed. Functions allowed through the WordPress XMLRPC API will still be allowed; this plugin does not block them. See the <a href=\"https://developer.wordpress.org/reference/\">WordPress Code Reference</a> for a list of functions. The function must be one your user has access to.<br/>
<i>Example: wp_create_user,wp_delete_user</i>";
}

/*
 * Run this when the plugin is activated. This will update the options with their
 * default values.
 */
register_activation_hook(__FILE__,'extapi_install');
function extapi_install()
{
    //Make sure settings are registered
    extapi_register_settings();

    //Setup Default Allowed Functions
    update_option('extapi_allowed_functions', 'dummy_value');
}


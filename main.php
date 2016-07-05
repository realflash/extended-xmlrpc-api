<?php
/*
Plugin Name: Extended API
Plugin URI: http://www.michaelgrosser.com
Description: This makes all of the common WordPress functions available via XML RPC rather than having to use pre-defined WP XML-RPC methods.
Author: Michael Grosser
Version: 0.5
Author URI: http://www.michaelgrosser.com
*/

//Check the WP version - Requires 3.0+
global $wp_version;
$exit_msg = 'Extended API Requires WordPress 3.0 or newer. You are currently running WordPress ' . $wp_version . '. <a href="http://codex.wordpress.org/Upgrading_WordPress">Please update!</a>';
if (version_compare($wp_version, "3.0", "<"))
{
    exit($exit_msg);
}

//Add a filter for XML RPC Methods
add_filter( 'xmlrpc_methods', 'createXmlRpcMethods' );

/**
 * Generate the Response
 *
 * @param methods Array - list of existing XMLRPC methods
 * @return methods Array - list of updated XMLRPC methods
 */
function createXmlRpcMethods($methods)
{
    $functions = get_defined_functions();

    $wp_functions = $functions['user'];
    $methods['wpext.callWpMethod'] = 'wpext_response';
    return $methods;
}

/**
 * Generate the Response
 *
 * @param Array (username, password, wp method name, arguments for method)
 * @return Mixed (response from WP method)
 */
function wpext_response($params)
{
    //Separate Params from Request
    $username = $params[0];
    $password = $params[1];
    $method   = $params[2];
    $args     = $params[3];

    // List of Allowed WP Functions
    $allowed_functions = get_option('allowed_functions');

    global $wp_xmlrpc_server;
    // Let's run a check to see if credentials are okay
    if ( !$user = $wp_xmlrpc_server->login($username, $password) ) {
            return $wp_xmlrpc_server->error;
    }

    if (function_exists($method) && in_array($method, $allowed_functions))
    {
        return call_user_func_array($method, $args);      
    } else {
	return new IXR_Error( 401, __( 'Sorry, this method does not exist or is not allowed.' ) );
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
    register_setting( 'extapi_settings', 'allowed_functions' );
    register_setting( 'extapi_settings', 'namespace', 'validate_namespace' );
	add_settings_section('extapi_settings_main', 'Main Settings', 'render_extapi_settings_main', 'extapi_settings_page');
	add_settings_field('allowed_functions', 'Allowed Functions', 'render_allowed_functions', 'extapi_settings_page', 'extapi_settings_main');
	add_settings_field('namespace', 'Namespace', 'render_namespace', 'extapi_settings_page', 'extapi_settings_main');
}

function render_extapi_settings_main() {
echo '<p>Main description of this section here.</p>';
}

function render_allowed_functions() {
	$options = get_option('allowed_functions');
	echo "<input id='plugin_text_string' name='allowed_functions[text_string]' size='40' type='text' value='{$options['text_string']}' />";
}

function render_namespace() {
	$options = get_option('namespace');
	echo "<input id='plugin_text_string' name='namespace[text_string]' size='40' type='text' value='{$options['text_string']}' />";
}

/*
 * If the user deletes the namespace, set it back to the default.
 */
function validate_namespace($input)
{
        $input = trim($input);
	if (empty($input))
            $input = 'extapi';
        
	return $input;
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

    //Setup Default Namespace
    $namespace = get_option('namespace');
    if (empty($namespace))
        update_option('namespace','extapi');

    //Setup Default Allowed Functions
    $allowed_functions = get_option('allowed_functions');
    if (empty($allowed_functions))
    {
        $allowed_functions = array();
        $functions = get_defined_functions();
        foreach ($functions['user'] as $function)
        {
            $allowed_functions[] = $function;
        }
        update_option('allowed_functions',$allowed_functions);
    }
}

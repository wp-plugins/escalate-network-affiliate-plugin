<?php
/*
Plugin Name: Escalate Network Affiliate Plugin
Plugin URI: http://www.escalatenetwork.com
Description: This Plugin Allows Affiliates of Escalate Network to access and post offers within WordPress. View a quick snapshot of your stats for today, yesterday, and month to date. No Need to login to the main system do it all from within your WordPress site.
Author: Escalate Network
Version: 1.0.6
Author URI: http://www.escalatenetwork.com
*/

######################################################################
# CLASS FOR SHARED ITEMS BETWEEN ADMIN AND FRONTEND
######################################################################
class escalate_network {
	######################################################################
	# VARIABLES
	######################################################################
	var $version = '1.0.6';
	var $pages = array('escalate-network-options');
	var $plugin_url;
	var $api_url = "http://escalatenetwork.com/api/1.0.6/";
	var $options;
	######################################################################
	# CONSTRUCT
	######################################################################
	function __construct() {
		$this->options = get_option('escalate_network');
		$this->plugin_url = plugins_url('', __FILE__);
		$this->plugin_basename = dirname(__FILE__);
	}
}
######################################################################
# INITIATE ADMIN CLASS OR FRONTEND CLASS
######################################################################
if(is_admin()):
	include (dirname (__FILE__) . '/core/admin.php');
	new escalate_network_admin();
else:
	include (dirname (__FILE__) . '/core/frontend.php');
	$test = new escalate_network_frontend();
endif;
######################################################################
# RESET THE CACHE INIT FLAG ON LOGOUT
######################################################################
add_action('wp_logout', 'remove_cache_init_flag');

function remove_cache_init_flag() {
    $current_options = get_option('escalate_network');
    $current_options['cache_init_run'] = 0;
    update_option('escalate_network', $current_options);
}
######################################################################
# UNINSTALL PLUGIN
######################################################################
if (function_exists('register_uninstall_hook')):
	register_uninstall_hook(__FILE__, 'escalate_network_uninstall');
endif;
function escalate_network_uninstall() {
	// Drop Table if Exists
	global $wpdb;
    $wpdb->query("DROP TABLE IF EXISTS escalate_offers, escalate_offer_files");

	// Remove Options
	delete_option('escalate_network');
}

/*
 * Triggering Javascript Previews for Optimum compatibility
 */


	add_filter('query_vars','plugin_add_trigger');
	function plugin_add_trigger($vars) {
		$vars[] = 'preview_offer';
	    return $vars;
	}
	 
	add_action('template_redirect', 'plugin_trigger_check', 1);
	function plugin_trigger_check() {
		global $wpdb;
		$id = get_query_var('preview_offer');
		
		if($id)
		{
			$files = $wpdb->get_results($wpdb->prepare("SELECT id, display, filename, code FROM escalate_offer_files WHERE id = %d", array($id)));
			if(!empty($files))
			{
				foreach($files as $file)
				{
					?><!DOCTYPE html>
<html>
<head><title>Previewing offer: <?php echo $file->display; ?></title></head>
<body>
<h3>Previewing offer: <?php echo $file->display; ?></h3>
<div>
<?php echo $file->code; ?>
</div></body>
</html><?php
				}
				exit;
			}
			else
			{
				echo "Failed to find offer";
			}
	    }
	}

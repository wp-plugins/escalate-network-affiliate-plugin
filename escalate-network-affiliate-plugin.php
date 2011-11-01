<?php
/*
Plugin Name: Escalate Network Affiliate Plugin
Plugin URI: http://www.escalatenetwork.com
Description: This Plugin Allows Affiliates of Escalate Network to access and post offers within WordPress. View a quick snapshot of your stats for today, yesterday, and month to date. No Need to login to the main system do it all from within your WordPress site.
Author: Escalate Network
Version: 1.0.4
Author URI: http://www.escalatenetwork.com
*/
######################################################################
# CLASS FOR SHARED ITEMS BETWEEN ADMIN AND FRONTEND
######################################################################
class escalate_network {
	######################################################################
	# VARIABLES
	######################################################################
	var $version = '1.0.4';
	var $db_version = '1.0.4';
	var $pages = array('escalate-network-options');
	var $plugin_url;
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

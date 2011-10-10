<?php
######################################################################
# ADMIN CLASS
######################################################################
class escalate_network_admin extends escalate_network {
	######################################################################
	# CONSTRUCT
	######################################################################
	function __construct() {
		parent::__construct (); // Grab Parent Class's Vars/Functions
		add_action('init', array($this, 'init')); // Initialize Plugin
	}
	######################################################################
	# INIT PLUGIN
	######################################################################
	function init() {
		// Check for Upgrades
		if($this->options):
			if(version_compare($this->options['version'], $this->version, '<')):
				$this->upgrade_check();
			endif;
			
		// Install Plugin
		else:
			include $this->plugin_basename . '/install.php';
			new escalate_network_install();
		endif;
		
		// Conditionally load methods based on Current Page
		global $pagenow;
		switch($pagenow) {
			// Load Dashboard Widget
			case 'index.php':
				$this->load_dashboard_widget();
			break;
			
			// Load Post Meta Box When Adding a Post
			case 'post-new.php':
				$this->load_meta_box();
			break;
			
			// Load Post Meta Box
			case 'post.php':
				$this->load_meta_box();
			break;
		}
		
		// Admin Menu
		add_action('admin_menu', array($this,'admin_menu'));
		
		// Load Admin Global CSS
		$this->css_files();
		
		// Load Admin Global JavaScript
		$this->admin_scripts();
		
		// Admin File Includes 
		$this->admin_includes();
		
		 // Admin AJAX Calls 
		$this->admin_ajax();
	}
	######################################################################
	# ESCALATE NETWORK DASHBOARD WIDGET
	######################################################################
	// Widget Display Markup
	function dashboard_widget_display() {
		echo '<div class="escalate-dashboard-loading">Loading Stats</div>';
	} 
	
	// Create the function use in the action hook
	function dashboard_widget() {
		wp_add_dashboard_widget('escalate_network_stats', 'Escalate Network Stats', array($this,'dashboard_widget_display'));	
	}
	
	// Load the Widget to Dashboard
	function load_dashboard_widget() {
		add_action('wp_dashboard_setup', array($this,'dashboard_widget'));
	}
	######################################################################
	# ESCALATE NETWORK POST META BOX
	######################################################################
	function load_meta_box(){
		add_action('add_meta_boxes', array($this, 'meta_box_hook'));
	}
	
	function meta_box_hook() {
        global $wpdb;
        if($this->options['last_cache']):
            $title = "Escalate Network Offers <em>Last Updated " . date("m/d/y @ h:i:s", $this->options['last_cache']) . "</em>";
        else:
            $title = 'Escalate Network Offers <em></em>';
        endif;
        add_meta_box('escalate_network_meta_box',$title,array($this, 'meta_box_content' ),'post','normal','default', '');
    }
    
    function meta_box_content() {
        echo 
        '<span class="hidden escalate-sort-default">' . $this->options['sort_offer_by'] . '</span>
        <div id="escalate_meta_nav">
        	<input type="text" name="search_escalate" value="Search Offers" size="50" />
			<button type="button" name="search-escalate-offers" class="button">Search</button>
        	<ul>
        		<li><a href="#" class="sort-escalate-offers-name">Sort by Name</a></li>
        		<li><a href="#"class="sort-escalate-offers-newest">Sort by Newest</a></li>
        	</ul>
        </div>
        <div class="escalate-meta-box-loading">Loading Offers</div>
        <ul id="escalate_meta_offers" style="height: ' . $this->options['offer_widget_height'] . 'px"></ul>';
    }
	######################################################################
	# UPGRADE PLUGIN
	######################################################################
	function upgrade_check() {
		include($this->plugin_basename . '/upgrade.php');
		new escalate_network_upgrade();
	}
	######################################################################
	# ADMIN INCLUDES
	######################################################################
	function admin_includes(){ 
		// General Include
		if(isset($_GET['page']) && in_array($_GET['page'], $this->pages)):
			include($this->plugin_basename . '/core/admin/inc/functions.php');
		endif;
	}
	######################################################################
	# ADMIN CSS AND JS STYLE/SCRIPT ENQUEUE - GLOBAL - ALL ADMIN PAGES
	######################################################################
	// Javascript Function to Enqueue JS Files
	function css_files() {
		wp_enqueue_style('escalate_network-admin-global', $this->plugin_url .'/css/styles-admin-global.css');
	}
	
	// Javascript Function to Enqueue JS Files
	function admin_scripts() {
		wp_enqueue_script('jquery');
		wp_enqueue_script('escalate_network-admin-js', $this->plugin_url . '/core/admin/js/admin.js', array('jquery'));
	}
	######################################################################
	# CREATE ADMIN MENU - ALSO LOADS ADMIN CSS/JS FUNCTIONS FROM ABOVE
	######################################################################
	function admin_menu() {
		// Set Admin Access Level
		if(!$this->options['access_level']): 
			$access = 'edit_dashboard';
		else: 
			$access = $this->options['access_level'];
		endif;
		
		// Create Menu Items
		add_options_page('Escalate Network', 'Escalate Network', $access, 'escalate-network-options', array($this, 'settings_page'));
	}
	// Set What Page to Load for Menu Callback Function
	function settings_page() { include($this->plugin_basename . '/core/admin/settings.php'); }
	######################################################################
	# AJAX REQUESTS FOR ADMIN
	######################################################################
	function escalate_network_admin_ajax_callback() { include($this->plugin_basename . '/core/admin/ajax/admin-ajax.php'); die(); }
	function admin_ajax() {
		add_action('wp_ajax_escalate_network_admin', array($this,'escalate_network_admin_ajax_callback'));
	}
}
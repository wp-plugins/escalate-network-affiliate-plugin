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
				if($this->authed_user()):
					$this->load_dashboard_widget();
				endif;
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
		if($this->authed_user()):
			add_action('admin_menu', array($this,'admin_menu'));
		endif;
		
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
	# CURRENT USER PERMISSIONS
	######################################################################
	function authed_user(){
		// Grab Current User Info
		global $current_user;
		get_currentuserinfo();
		
		$authed = TRUE;
		
		if(isset($this->options['user_access'])):
			if(!empty($this->options['user_access']) && !in_array($current_user->ID, $this->options['user_access'])):
				$authed = FALSE;
			endif;	
		endif;
		
		// return Authed
		return $authed;
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
		if(!empty($this->options['stats_last_cache'])):
			$last_cache = '<em>Last Updated ' . date("m/d/y @ h:i:s", $this->options['stats_last_cache']) . '</em>';
		else:
			$last_cache = '';
		endif;
		wp_add_dashboard_widget('escalate_network_stats', 'Escalate Network Stats'.$last_cache, array($this,'dashboard_widget_display'));	
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
        if($this->options['last_cache'] <> 0):
            $title = "Escalate Network Offers <em>Last Updated " . date("m/d/y @ h:i:s", $this->options['last_cache']) . "</em>";
        else:
            $title = 'Escalate Network Offers <em>Updating...</em>';
        endif;
        add_meta_box('escalate_network_meta_box',$title,array($this, 'meta_box_content' ),'post','normal','default', '');
    }
    
    function meta_box_content() {
    	// Used for Coupons.com Links
    	require_once($this->plugin_basename . '/core/admin/inc/coupons-com-feed-class.php');
    	$ccfc = new coupons_com_feed_class();
    ?>
        <div id="escalate-offers-container">
	        <span class="hidden escalate-sort-default"><?php echo $this->options['sort_offer_by'] ?></span>
	        <div id="escalate_meta_nav">
	        	<input type="text" name="search_escalate" value="Search Offers" size="50" />
				<button type="button" name="search-escalate-offers" class="button">Search</button>
	        	<ul>
	        		<li><a href="#" class="sort-escalate-offers-name">Sort by Name</a></li>
	        		<li><a href="#" class="sort-escalate-offers-newest">Sort by Newest</a></li>
	        		<li><a href="#" class="escalate-coupons-com">Coupons.com Links</a></li>
	        	</ul>
	        </div>
	        <div class="escalate-meta-box-loading">Loading Offers</div>
	        <ul id="escalate_meta_offers" style="height: <?php echo $this->options['offer_widget_height'] ?>px"></ul>
		</div>
		<script type="text/javascript">
			var wpurl = '<?php echo site_url(); ?>';
		</script>
		<div id="escalate-coupons-com-container">
			<div id="escalate_meta_nav">
				<p id="escalate-coupons-com-container-title">Coupons.com Direct Link</p>
				<ul class="escalate-coupons-com-sort">
					<li style="display: none;">
						Jump to:
						<select id="jump_to_brand"><?php foreach ($ccfc->brands as $brand => $count ): 
						$broken = explode(" ",$brand,4);
						if ($broken[3]) array_pop($broken);
						
						$trimmed = implode(" ", $broken);
						$trimmed .= " (".$count.")";
						?>
							<option value="<?php echo md5($brand);?>"><?php echo $trimmed;?></option><?php endforeach; ?>
						</select>
					</li>
					<li style="display: none;">
						Jump to:
						<select id="jump_to_cat"><?php foreach ($ccfc->categories as $cat => $count ): 
						$broken = explode(" ",$cat,4);
						if ($broken[3]) array_pop($broken);
						
						$trimmed = implode(" ", $broken);
						$trimmed .= " (".$count.")";
						?>
							<option value="<?php echo md5($cat);?>"><?php echo $trimmed;?></option><?php endforeach; ?>
						</select>
					</li>
					<li>
						Sort by
					</li>
					<li>
						<select class="escalate-coupons-com-sort">
							<option value="new">Newest First</option>
							<option value="cat">Sort by Category</option>
							<option value="old">Ending in 1 week</option>
							<option value="brand">Brand</option>
							<option value="value">Highest Value</option>
						</select>
					</li>
					<li><a href="#" class="escalate-go-back-to-offers">Go Back to Escalate Offers</a></li>
				</ul>
				<div style="float: left; width: 140px; text-align: left; clear: both; ">
					<input type="checkbox" name="escalate-coupons-com-toggle-all" value="toggle_all" title="Check / Uncheck All" /> Select / Deselect All
				</div>
	        </div>
			<ul id="escalate-coupons-com-links" style="height: <?php echo $this->options['offer_widget_height'] ?>px">
				<?php 
				foreach ($ccfc->items as $i => $item): 
				?>
				<li <?php echo ($i % 2 == 0 ? '' : 'class="alternate"'); ?>>
					<div class="escalate-coupon-com-li">
						<div class="escalate-coupons-com-checkbox">
							<input type="checkbox" name="escalate-coupons-com-checkbox" value="<?php echo $item['couponid']; ?>" title="<?php echo $item['description'];?>" />
							<input type="hidden" name="escalate-coupons-com-hidden-image" value="<?php echo $item['image'];?>" />
							<input type="hidden" name="escalate-coupons-com-hidden-link" value="<?php echo $item['link'];?>" />
						</div>
						<div class="escalate-coupons-com-image">
							<img src="<?php echo $item['image'];?>" width="263" height="128" alt="" />
						</div>
						<div class="escalate-coupons-com-details">
							<p class="escalate-coupons-com-title">
								<a href="<?php echo $item['link'];?>" target="_blank"><?php echo $item['description'];?></a>
							</p>
							<p class="escalate-coupons-com-source"><?php echo $item['brand'];?></p>
							<p class="escalate-coupons-com-field">
								<input type="text" size="50" name="escalate-coupons-com-field" value="<?php echo $item['link'];?>" />
							</p>
						</div>
					</div>
				</li>
				<?php endforeach;  ?>
			</ul>
			<div class="escalate-insert-error"></div>
            <div class="escalate-insert-success"></div>
			<div class="escalate-coupons-button">
				<strong>Insert Coupon(s) </strong>
                <button class="add-coupons-to-post button-primary" type="button" name="escalate-add-coupons-to-post">With Images</button>
				<button class="add-coupons-to-post-text button-primary" type="button" name="escalate-add-coupons-to-post-text">With Text Only</button>
            </div>
		</div>
	<?php
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



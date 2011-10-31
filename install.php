<?php
######################################################################
# INSTALL MYSQL TABLES
######################################################################
class escalate_network_install extends escalate_network {
	######################################################################
	# CONSTRUCT
	######################################################################
	function __construct() {
		parent::__construct (); // Grab Parent Class's Vars/Functions
		$this->set_options(); // Set Plugin Default Options
		$this->queries(); // Run Install Queries
	}
	######################################################################
	# PLUGIN DEFAULT OPTIONS
	######################################################################
	// Set Default Options
	function defaults() {
		$defaults = array(
			'version' => $this->version,
			'db_version' => $this->db_version,
			'access_level' => 'edit_dashboard',
			'username' => '',
			'password' => '',
			'offer_widget_height' => '310',
			'sort_offer_by' => 'newest',
			'affiliate_id' => '',
			'cache_frequency' => 30,
            'last_cache' => 0,
            'user_access' => '',
            'stats_last_cache' => '',
			'stats_data' => array(
				'today' => array(
					'clicks' => '',
					'payout' => ''
				),
				'yesterday' => array(
					'clicks' => '',
					'payout' => ''
				),
				'month' => array(
					'clicks' => '',
					'payout' => ''
				)
			)
		);
		return $defaults;
	}
	######################################################################
	# SET OPTIONS
	######################################################################
	function set_options() {
		$this->options = $this->defaults();
		add_option('escalate_network', $this->options);
	}
	######################################################################
	# RUN QUERIES
	######################################################################
	function queries(){
		global $wpdb;

		// Create Offers Table
		$wpdb->query(
			"CREATE TABLE IF NOT EXISTS `escalate_offers` (
			`id` INT NOT NULL ,
			`name` TEXT NOT NULL ,
			`default_payout` FLOAT NOT NULL ,
			`preview_url` TEXT NOT NULL ,
			`description` TEXT NOT NULL ,
			`modified` INT NOT NULL
			) ENGINE = InnoDB"
		);
		
		// Create Offer Files Table
		$wpdb->query(
			"CREATE TABLE IF NOT EXISTS `escalate_offer_files` (
			`id` INT NOT NULL ,
			`display` TEXT NOT NULL ,
			`filename` TEXT NOT NULL ,
			`code` TEXT NOT NULL ,
			`offer_id` INT NOT NULL
			) ENGINE = InnoDB;"
		);

	} // end queries function
} // end install class
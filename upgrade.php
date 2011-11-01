<?php
class escalate_network_upgrade extends escalate_network {
	function __construct() {
		parent::__construct(); // Grab Parent Class's Vars/Functions
		$this->check_upgrade(); // Check for Upgrades
	}

	function check_upgrade() {
		// Version Specific Upgrades
		if (version_compare($this->options['version'], '1.0.1', '<')) $this->upgrade('1.0.1');
		
		// Upgrade to Current if There is Not a Version Specific Upgrade
		if (version_compare($this->options['version'], $this->version, '<')) $this->upgrade('current');
	}

	function upgrade($ver) {
		global $wpdb;
		######################################################################
		# UPGRADE TO CURRENT VERSION
		######################################################################
		if($ver == 'current'):
			// Update Options
			$newopts = array('version' => $this->version);
			$this->options = array_merge($this->options, $newopts);
			update_option('escalate_network', $this->options);
		endif;
		######################################################################
		# UPGRADE TO VERSION 1.0.1
		######################################################################
		if($ver == '1.0.1'):
			// Run Queries
			// no queries to run for this update
			
			// Update Options
			$newopts = array(
				'version' => '1.0.1',
				'db_version' => '1.0.1',
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
			$this->options = array_merge($this->options, $newopts);
			update_option('escalate_network', $this->options);
		endif;
		$this->check_upgrade();
	}
}
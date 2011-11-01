<?php
######################################################################
# ADMIN CLASS
######################################################################
class escalate_network_frontend extends escalate_network {
	function __construct(){
		parent::__construct (); // Grab Parent Class's Vars/Functions
		$this->init();
	}
	
	function init(){
		wp_enqueue_style('escalate-network-frontend', $this->plugin_url .'/css/styles-frontend.css');
	}
}
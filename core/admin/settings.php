<?php $options = get_option('escalate_network'); ?>

<div class="wrap escalate-network">
	<div class="icon32" id="icon-edit-pages"><br /></div><h2>Escalate Network - Plugin Settings</h2><br />
	<div class="updated" id="message"></div>
	
	<!-- Settings Form -->
	<form method="post" action="#" id="settings_form" class="settings_form">
		<!-- General Settings -->
		<div id="poststuff">
			<div class="stuffbox">
				<h3>General Settings</h3>
				<div class="inside">
					<!-- Username -->
					<label>Username</label>
					<input type="text" value="<?php if($options['username']): echo decrypt($options['username'], EN_ENCRYPT_KEY); endif; ?>" size="50" name="settings[username]" />
					<p>This is the username provided by Escalate Network</p>
					
					<!-- Password -->
					<label>Password</label>
					<input type="password" value="<?php if($options['password']): echo decrypt($options['password'], EN_ENCRYPT_KEY); endif; ?>" size="50" name="settings[password]" />
					<p>This is the password provided by Escalate Network</p>
					
					<!-- Offer Widget Height -->
					<label>Offer Widget Height</label>
					<input type="text" value="<?php if($options['offer_widget_height']): echo $options['offer_widget_height']; endif; ?>" size="5" name="settings[offer_widget_height]" /> px
					<p>This setting is for the Escalate Network box that is displayed below the editor window on the post screen. Default is 310px. This field should only include the number.</p>
				
					<!-- Default Sort Order -->
					<label>Sort Offers by Default</label>
					<select name="settings[sort_offer_by]">
						<?php
						$opts = array('newest' => 'Sort By Newest', 'name' => 'Sort By Name');
						foreach($opts as $key => $val):
							if($options['sort_offer_by'] == $key) $selected = ' selected="selected"';
							else $selected = '';
							echo '<option value="' . $key . '"' . $selected . '>' . $val . '</option>';
						endforeach;
						?>
					</select>
					<p>This allows you to determine if you want your orders be automatically sorted by newest or by name when they are being loaded.</p>
				
					<!-- Permission Settings -->
					<label>Show Plugin Stats and Escalate Network Settings Page</label>
					
					<?php
					$admin_users = get_users(array('role' => 'administrator'));
					//var_dump($options);
					foreach($admin_users as $admin_user):
						$checked = '';
						if(isset($options['user_access']) && !empty($options['user_access'])):
							if(in_array($admin_user->ID, $options['user_access'])) $checked = ' checked="checked"';
						endif;
						echo '<input type="checkbox" name="settings[user_access][]" value="' . $admin_user->ID . '"' . $checked . ' /> ' . $admin_user->display_name . ' ';
					endforeach;
					?>
					<p>Those that are checked will be able see the stats on the dashboard and this settings page. If no admins are checked, all admins will have access by default.</p>
				</div>
			</div>
		</div>
		<input type="submit" value="Save Settings" class="button" name="submit">
		<br /><br />

	</form>
	
	<!-- Stats -->
	<div id="poststuff">
		<div class="stuffbox" id="escalate_network_stats">
			<?php
			if(!empty($this->options['stats_last_cache'])):
				$last_cache = '<em>Last Updated ' . date("m/d/y @ h:i:s", $this->options['stats_last_cache']) . '</em>';
			else:
				$last_cache = '';
			endif;
			?>
			<h3>Escalate Network Stats<?php echo $last_cache; ?></em></h3>
			<div class="inside">
				<div class="escalate-dashboard-loading">Loading Stats</div>
			</div>
		</div>
	</div>
	
	<!-- OodleTech Signage -->
	<br /><br />Plugin Developed by <a href="http://oodletech.com" target="_blank">OodleTech</a> for <a href="http://escalatenetwork.com" target="_blank">Escalate Network</a>.
</div>
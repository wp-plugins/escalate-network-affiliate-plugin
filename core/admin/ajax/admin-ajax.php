<?php
if(!empty($_POST) && $_POST['action'] == "escalate_network_admin" && is_admin()):
	if(isset($_POST['method'])):
		######################################################################
		# UNIVERSAL
		######################################################################
		global $wpdb; // WordPress DB Class
		$options = get_option('escalate_network');  // Get Options
		require_once(dirname(dirname(__FILE__)) . '/inc/functions.php');
		######################################################################
		# AJAX REQUEST POST METHODS
		######################################################################
		switch($_POST['method']) {
			######################################################################
			# DASHBOARD WIDGET
			######################################################################
			case 'load_dashboard':
				// Load API From Escalate Network
				$xml_url = 'http://escalatenetwork.com/api/get-stats.php?email=' . decrypt($this->options['username'], EN_ENCRYPT_KEY) . '&password=' . decrypt($this->options['password'], EN_ENCRYPT_KEY);
				$stats = simplexml_load_file($xml_url);
				
				// Set Money Format
				setlocale(LC_MONETARY, 'en_US');
				
				// Display Stats Dashboard Widget if Authed
				if(!$stats->error):
					$data['response'] = '<div class="escalate-dashboard">
					<ul>
						<li>Clicks Today: ' . $stats->today->clicks . '</li>
						<li>Clicks Yesterday: ' . $stats->yesterday->clicks . '</li>
						<li>Clicks This Month: ' . $stats->month->clicks . '</li>
					</ul>
					<ul>
						<li>Amount Today: ' . money_format('%n', (float)$stats->today->payout) . '</li>
						<li>Amount Yesterday: ' . money_format('%n', (float)$stats->yesterday->payout) . '</li>
						<li>Amount This Month: ' . money_format('%n', (float)$stats->month->payout) . '</li>
					</ul>
					</div>';
				// Else Load Auth Message
				else:
					$data['response'] = 'Either your Escalate Network login is incorrect or you have not yet filled in your settings. Please check the Escalate Network <a href="options-general.php?page=escalate-network-options">settings page</a>.';
				endif;
			break;
			######################################################################
			# POST META BOX
			######################################################################
			case 'load_meta_box':
                // Make sure the User Has Logged In
                if($options['username'] && $options['password']):

                	// See if It Needs to Cache Again
                    if(time() > strtotime('+30minute',$options['last_cache'])):
                    
	                    // Load API From Escalate Network
	                    $xml_url = 'http://escalatenetwork.com/api/get-offers.php?email=' . decrypt($this->options['username'], EN_ENCRYPT_KEY) . '&password=' . decrypt($this->options['password'], EN_ENCRYPT_KEY) . '&details=true&files=true';

	                    // Possible spot for session conditional statement
	                    $xml_data = simplexml_load_file($xml_url);
	                    
	                    // Make Sure the Feed is Fully Loaded
	                    if($xml_data->meta->complete):
                    	 
	                    	// Empty Tables
	                    	$wpdb->query("TRUNCATE TABLE `escalate_offers`");
	                    	$wpdb->query("TRUNCATE TABLE `escalate_offer_files`");
	                    	
	                    	// Insert Queries
	                    	foreach($xml_data->offers->offer as $offer):
	                    		// Insert Offers
	                    		$wpdb->query($wpdb->prepare(
	                    			"INSERT INTO escalate_offers (id, name, default_payout, preview_url, description, modified) VALUES (%d, %s, %s, %s, %s, %d)", 
	                    			array($offer->id, $offer->name, $offer->default_payout, $offer->preview_url, $offer->description, $offer->modified)
	                    		));
	                    		
	                    		// Insert Offer Files
	                    		foreach($offer->files->file as $file):
	                    			$wpdb->query($wpdb->prepare(
	                    				"INSERT INTO escalate_offer_files (id, display, filename, code, offer_id) VALUES (%d, %s, %s, %s, %d)", 
	                    				array($file->id, $file->display, $file->filename, $file->code, $offer->id)
	                    			));
	                    		endforeach;
	                    	endforeach;
	                    	
	                    	// Set Cache Timestamp + Affiliate ID
							$newopts = array('last_cache' => time(), 'affiliate_id' => (string)$xml_data->affiliate->id);
							$options = array_merge($options, $newopts);
							update_option('escalate_network', $options);

	                    // If the Feed didn't Fully Load
						else:
							$data['response'] = '<li>There was a problem loading the feed.</li>';
						endif;
					
					endif; // end checking if needs to cache again
					
					// Determine Sort Order
					if($_POST['sort'] == 'modified') $order = ' ORDER BY modified DESC'; else $order = '';
					
					// QUERY OFFERS
					if($_POST['search'])
						$offers = $wpdb->get_results($wpdb->prepare(
							"SELECT id, name, default_payout, preview_url, description, modified FROM escalate_offers WHERE name LIKE %s$order",
							array('%' . $_POST['search'] . '%')
						));
					else
						$offers = $wpdb->get_results("SELECT id, name, default_payout, preview_url, description, modified FROM escalate_offers$order");
					
					// Set Money Format
	                setlocale(LC_MONETARY, 'en_US');
					
					// Loop Through and Return Offer HTML
                    foreach($offers as $offer):
                        // Query Files for this Offer
                        $files = $wpdb->get_results($wpdb->prepare("SELECT id, display, filename, code FROM escalate_offer_files WHERE offer_id = %d", array($offer->id)));
                        
                        // File Vars
                        $creatives = '';
                        $creative_options = '';
                        
                        // Build Info for Browse All Lightbox + Insert to Post Code
                        foreach($files as $file):
                            $creative_options .= '<option value="' . $file->id . '">' . $file->display . '</option>';
                            if(!empty($file->display)):
                                $creatives .= '<h3>' . $file->display . ' - <a href="#" class="lightbox-insert-link">insert creative to post</a></h3><span title="' . $file->id . '" class="offer-info">' . $file->code . '</span><br /><br />';
                            endif;
                        endforeach;
                        
                        // Return Time Stamp
                        $data['time'] = 'Last Updated ' . date("m/d/y @ h:i:s", $options['last_cache']);

                        // Build HTML
                        $data['response'] .= 
                        '<li>
                            <div class="first-line">
                                <p class="offer-name"><a href="#">' . $offer->name . '</a></p>
                                <p class="offer-payout">Payout: ' . money_format('%n', (float)$offer->default_payout) . '</p>
                            </div>
                            <div class="second-line">
                                <p class="offer-description">' . $offer->description . '</p>
                                <p><a href="' . $offer->preview_url . '" target="_blank">Preview Offer</a></p>
                                <div class="insert-offer-to-post">
                                    <div class="offer-tracking"><input type="text" name="tracking" value="http://enlnks.com/aff_c?offer_id=' . $offer->id . '&aff_id=' . $options['affiliate_id'] . '" size="70" /></div>
                                    <select name="offer-creatives">
                                        <option>Creative Options</option>
                                        ' . $creative_options . '
                                    </select>';

                                    // Offer Browse All Link
                                    $data['response'] .= '<a href="#TB_inline?height=400&width=600&inlineId=creatives' . $offer->id . '" class="browse-offers-link thickbox">Browse All</a>';

                                    // Offer Error Box
                                    $data['response'] .= '<p class="offer-error"></p>';

                                    // Offer Button
                                    $data['response'] .=
                                    '<div class="offer-button">
                                        <button class="add-offer-to-post button" type="button" name="add-offer-to-post">Insert Into Post</button><br />
                                        <!--<div class="offer-copy-code-container"><a href="#" class="offer-copy-code">Copy Code</a></div>-->
                                    </div>';

                                    // HTML for Browse All Popup + Insert to Post Code
                                    $data['response'] .= '<div class="hide-creatives" id="creatives' . $offer->id . '">' . $creatives . '</div>';

                                $data['response'] .= '</div>
                            </div>
                        </li>';
                    endforeach;

				// If Login Cradentials Were Empty
				else:
					$data['response'] = '<li>Either your Escalate Network login is incorrect or you have not yet filled in your settings. Please check the Escalate Network <a href="options-general.php?page=escalate-network-options">settings page</a>.</li>';
                endif; // end checking for username/password validation
                
			break;
			######################################################################
			# SETTINGS PAGE
			######################################################################
			case 'update_settings':
				// Encrypt Username/Password Before Storing in DB
				$encrypt_options = array(
					'username' => encrypt($_POST['settings']['username'], EN_ENCRYPT_KEY),
					'password' => encrypt($_POST['settings']['password'], EN_ENCRYPT_KEY)
				);
				
				// Merge Encrypted Data with Post Data
				$parsed_options = array_merge($_POST['settings'], $encrypt_options);
				
				// Merge Default Options with Merged Array Above
				$new_options = array_merge($options, $parsed_options);
				
				// Update Options in Database
				update_option('escalate_network', $new_options);
				
				// Return JSON Data
				$data['settings'] = $_POST['settings'];
			break;
		######################################################################
		# RETURN JSON
		######################################################################
		}
		$json = json_encode($data);
		echo $json;
        
        // store the cached result in the sessions table
        
	else:
		echo 'No Method Specified';
	endif;
endif;
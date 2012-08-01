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
				// Load API From Escalate Network if Not Cached
				if(time() > strtotime('+30minute',(int)$options['stats_last_cache'])):
					$xml_url = $this->api_url.'get-stats.php?aff_id=' . $options['affiliate_id'];
					$stats_feed = simplexml_load_file($xml_url);
					
					if(!array_key_exists('error', $stats_feed)):
						// Set Money Format
						setlocale(LC_MONETARY, 'en_US');
						
						// Set Stat Option
						$stats_data = array(
							'today' => array(
								'clicks' => (int)$stats_feed->today->clicks,
								'payout' => number_format((float)$stats_feed->today->payout, 2)
							),
							'yesterday' => array(
								'clicks' => (int)$stats_feed->yesterday->clicks,
								'payout' => number_format((float)$stats_feed->yesterday->payout, 2)
							),
							'month' => array(
								'clicks' => (int)$stats_feed->month->clicks,
								'payout' => number_format((float)$stats_feed->month->payout, 2)
							)
						);
						
						// Set Stats Cache Timestamp + Stats Data
						$newopts = array('stats_last_cache' => time(), 'stats_data' => $stats_data);
						$options = array_merge($options, $newopts);		
						update_option('escalate_network', $options);
					endif;
				endif;
				
				// Display Stats Dashboard Widget if Authed
				if((isset($stats_feed) && !array_key_exists('error', $stats_feed)) || !isset($stats_feed)):
					$data['response'] = '<div class="escalate-dashboard">
					<ul>
						<li>Clicks Today: ' . $options['stats_data']['today']['clicks'] . '</li>
						<li>Clicks Yesterday: ' . $options['stats_data']['yesterday']['clicks'] . '</li>
						<li>Clicks This Month: ' . $options['stats_data']['month']['clicks'] . '</li>
					</ul>
					<ul>
						<li>Amount Today: $' . $options['stats_data']['today']['payout'] . '</li>
						<li>Amount Yesterday: $' . $options['stats_data']['yesterday']['payout'] . '</li>
						<li>Amount This Month: $' . $options['stats_data']['month']['payout'] . '</li>
					</ul>
					</div>';
					$data['time'] = '<em>Last Updated ' . date("m/d/y @ h:i:s", time()) . '</em>';
				// Else Load Auth Message
				else:
					$data['response'] = '<div class="notification error">Either your Escalate Network login is incorrect or you have not yet filled in your settings. Please check the Escalate Network <a href="options-general.php?page=escalate-network-options">settings page</a>.</div>';
				endif;
			break;
			######################################################################
			# FETCH AFFILIATE ID
			######################################################################			
			case 'load_affiliateid':
				$xml_url = $this->api_url.'authenticate.php?email=' . $_POST['user'] . '&password=' . $_POST['pass'];
				$xml_data = simplexml_load_file($xml_url);
				if($xml_data)
				{
					if ($xml_data->error)
					{
						$data['error'] = (string)$xml_data->error;
					}
					else
					{
						$data['affiliate_id'] = (int)$xml_data->id;
						$options['affiliate_id'] = (int)$xml_data->id;
						$options['username'] = null;
						$options['password'] = null;
						update_option('escalate_network', $options);
					}
				}
				else
				{
					$data['error'] = "Sorry, we were unable to connect to Escalate Networks. Please try again later";
				}
			break;
			######################################################################
			# POST META BOX
			######################################################################
			case 'load_meta_box':
			
				if ($_POST['sort'] == "modified")
				{
					$order = " ORDER BY modified";
				}
				else if ($_POST['sort'] == "name")
				{
					$order = " ORDER BY name";
				}
				
				if ($_POST['direction'] == "desc")
				{
					$order .= " DESC";
				}
				
                // Make sure the User Has Logged In
                if($options['affiliate_id']):

					/* Prepare the Queries
					 * @var $query - holds the prepared query
					 */
					if($_POST['search'])
						$query = $wpdb->prepare(
							"SELECT id, name, default_payout, preview_url, description, modified FROM escalate_offers WHERE name LIKE %s$order",
							array('%' . $_POST['search'] . '%')
						);
					else
						$query = "SELECT id, name, default_payout, preview_url, description, modified FROM escalate_offers$order";

						
					$offers = $wpdb->get_results($query);
					$data['nextcache'] = ($options['last_cache'] + 1800) - time();
					
                	/* Rebuild the cache and retry the query */
					if(time() > ($options['last_cache'] + 1800) || !count($offers))
					{
					
	                    $data['cachemsg']=cacheOffers($options, $this->api_url);
						$offers = $wpdb->get_results($query);

						// Set Cache Timestamp
						$options['last_cache'] = time();
						update_option('escalate_network', $options);
					}
					
					// Set Money Format
	                setlocale(LC_MONETARY, 'en_US');
					
					// Return Time Stamp
					$data['time'] = 'Last Updated ' . date("m/d/y @ h:i:s", $options['last_cache']);
					
					// Loop Through and Return Offer HTML
                    foreach($offers as $offer):
                        


                        // Build HTML
                        $data['response'] .= 
                        '<li rel="' . $offer->id . '">
                            <div class="first-line">
                                <p class="offer-name"><a href="#">' . $offer->name . '</a></p>
                                <p class="offer-payout">Payout: $' . number_format((float)$offer->default_payout,2) . '</p>
                            </div>
                            <div class="second-line">
                                <p class="offer-description">' . $offer->description . '</p>
                                <p><a href="' . $offer->preview_url . '" target="_blank">Preview Offer</a></p>
                                <div class="insert-offer-to-post">
                                    <div class="notification loading"> Loading creative options...</div>
								</div>
                            </div>
                        </li>';
                    endforeach;
					
				// If Login Cradentials Were Empty
				else:
					$data['response'] = '<li><div class="notification error">Either your Escalate Network Affiliate ID is incorrect or you have not yet filled in your settings. Please check the Escalate Network <a href="options-general.php?page=escalate-network-options">settings page</a>.</div></li>';
                endif; // end checking for username/password validation
                
			break;
			######################################################################
			# LOAD FILE
			######################################################################
			case 'load_file':
				if ($options['affiliate_id'] && $_POST['offer_id'])
				{
				
					// Okay, Cacheing aside, lets get to work
					$files = $wpdb->get_results($wpdb->prepare("SELECT id, display, filename, code FROM escalate_offer_files WHERE offer_id = %d", array($_POST['offer_id'])));
					
					
					/* If there are no rows cached in the database */
					if (!count($files)) 
					{
						$xml_url = $this->api_url.'get-files.php?offer_id=' . $_POST['offer_id'] . '&aff_id=' . $options['affiliate_id'];
						$xml_data = simplexml_load_file($xml_url);
						//var_dump($xml_data);
						if($xml_data)
						{
							if ($xml_data->error)
							{
								$data['error'] = (string)$xml_data->error;
							}
							else
							{
								foreach($xml_data->file as $file):
									$wpdb->query($wpdb->prepare(
										"INSERT INTO escalate_offer_files (id, display, filename, code, offer_id) VALUES (%d, %s, %s, %s, %d)", 
										array($file->id, $file->display, $file->filename, $file->code, $_POST['offer_id'])
									));
								endforeach;
							}
						}
						else
						{
							$data['response'] = '<li><div class="notification error">Either your Escalate Network Affiliate ID is incorrect or you have not yet filled in your settings. Please check the Escalate Network <a href="options-general.php?page=escalate-network-options">settings page</a>.</div></li>';
						}
						
						$files = $wpdb->get_results($wpdb->prepare("SELECT id, display, filename, code FROM escalate_offer_files WHERE offer_id = %d", array($_POST['offer_id'])));
					}
					
					
					// File Vars
					$creatives = '';
					$creative_options = '';
					
					// Build Info for Browse All Lightbox + Insert to Post Code
					foreach($files as $file):
						$class = "";
						$creative_options .= '<option value="' . $file->id . '">' . $file->display . '</option>';
						if(!empty($file->display)):
							if (strpos($file->code, 'text/javascript') || strpos($file->code, 'iframe'))
							{
								$file->code = base64_encode(str_replace(array("\n","\r"), '', $file->code))."</span><span class=\"notification\">This is a javascript Ad.
								<a href=\"#\" class=\"btn64\" rel=\"".$file->id."\">Click Here</a> to preview in a new window.";
								$class = " b64js hidden";
							}
							$creatives .= '<h3>' . $file->display . ' - <a href="#" class="lightbox-insert-link">insert creative to post</a></h3><span title="' . $file->id . '" class="offer-info'.$class.'">' . $file->code . '</span><br /><br />';
							
						endif;
					endforeach;
					
					
					$data['insert'] = '
						<div class="offer-tracking"><input type="text" name="tracking" value="http://enlnks.com/aff_c?offer_id=' . $_POST['offer_id'] . '&aff_id=' . $options['affiliate_id'] . '" size="70" /></div>
						<select name="offer-creatives">
							<option>Creative Options</option>
							' . $creative_options . '
						</select>
						<a href="#TB_inline?height=581&width=640&inlineId=creatives' . $_POST['offer_id'] . '" class="browse-offers-link thickbox">Browse All</a>
						<p class="offer-error"></p>
						<div class="offer-button">
							<button class="add-offer-to-post button" type="button" name="add-offer-to-post">Insert Into Post</button><br />
							<!--<div class="offer-copy-code-container"><a href="#" class="offer-copy-code">Copy Code</a></div>-->
						</div>
						<div class="hide-creatives" id="creatives' . $_POST['offer_id'] . '">' . $creatives . '</div>';
				}
				else
				{
					$data['insert'] = '<div class="notification error">Either your Escalate Network Affiliate ID is incorrect or you have not yet filled in your settings. Please check the Escalate Network <a href="options-general.php?page=escalate-network-options">settings page</a>.</div>';
				}
			break;
			######################################################################
			# SETTINGS PAGE    
			######################################################################
			case 'update_settings':
				// Encrypt Username/Password Before Storing in DB
				$encrypt_options = array(
					'username' => escalate_encrypt($_POST['settings']['username'], EN_ENCRYPT_KEY),
					'password' => escalate_encrypt($_POST['settings']['password'], EN_ENCRYPT_KEY)
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
			# COUPON.COM OFFERS 
			######################################################################			
			case 'load_coupons':
				require_once($this->plugin_basename . '/core/admin/inc/coupons-com-feed-class.php');
				$ccfc = new coupons_com_feed_class($_POST['sort']);
				
				ob_start();
				$cat = false;
				
				foreach ($ccfc->items as $i => $item): 
					// detect new category
					if ( ($_POST['sort'] == "cat") && ($item['majorCategory'] != $cat) )
					{
						$cat = $item['majorCategory'];
						?><li style="padding: 0px;"><h3><a name="<?php echo md5($item['majorCategory']); ?>"><?php echo $item['majorCategory']; ?></a></h3></li><?php
					}
					else if ( ($_POST['sort'] == "brand") && ($item['brand'] != $cat) )
					{
						$cat = $item['brand'];
						?><li style="padding: 0px;"><h3><a name="<?php echo md5($item['brand']); ?>"><?php echo $item['brand']; ?></a></h3></li><?php
					}
				?>
				<li <?php echo ($i % 2 == 0 ? '' : 'class="alternate"'); ?>>
					<div class="escalate-coupon-com-li">
						<div class="escalate-coupons-com-checkbox">
							<input type="checkbox" name="escalate-coupons-com-checkbox" value="<?php echo $item['couponid']; ?>" title="<?php echo $item['description'];?>" />
							<input type="hidden" name="escalate-coupons-com-hidden-image" value="<?php echo $item['image'];?>" />
							<input type="hidden" name="escalate-coupons-com-hidden-link" value="<?php echo $item['link'];?>" />
							
							
							<input type="hidden" name="escalate-coupons-com-sort-actdate" value="<?php echo $item['activedatestamp'];?>" />
							<input type="hidden" name="escalate-coupons-com-sort-expdate" value="<?php echo $item['expirationdatestamp'];?>" />
							<input type="hidden" name="escalate-coupons-com-sort-value" value="<?php echo $item['value'];?>" />
							<input type="hidden" name="escalate-coupons-com-sort-category" value="<?php echo $item['category'];?>" />
							<input type="hidden" name="escalate-coupons-com-sort-brand" value="<?php echo $item['brand'];?>" />
							
						</div>
						<div class="escalate-coupons-com-image">
							<img src="<?php echo $item['image'];?>" width="263" height="128" alt="" />
						</div>
						<div class="escalate-coupons-com-details">
							<p class="escalate-coupons-com-title">
								<a href="<?php echo $item['link'];?>" target="_blank"><?php echo $item['description'];?></a>
							</p>
							<p class="escalate-coupons-com-source"><?php echo $item['brand'];?> </p>
							<p class="escalate-coupons-com-field">
								<input type="text" size="50" name="escalate-coupons-com-field" value="<?php echo $item['link'];?>" />
							</p>
						</div>
					</div>
				</li>
				<?php endforeach;
				
				$data['insert']= ob_get_clean();
				
			break;
		######################################################################
		# RETURN JSON
		######################################################################
		}
		$json = json_encode($data);
		echo $json;
        
        // store the cached result in the sessions table
        
	else:
		echo json_encode(array('error'=>'No Method Specified'));
	endif;
endif;
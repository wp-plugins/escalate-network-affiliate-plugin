<?php

if(is_admin()):
	// Encrypt Function
	DEFINE('EN_ENCRYPT_KEY','\4l755fh.@0>t,U13T0uwv%g6l>r<5');
	function escalate_encrypt($input_string, $key){
		if(function_exists(mcrypt_get_iv_size)):
	    	$iv_size = mcrypt_get_iv_size(MCRYPT_RIJNDAEL_256, MCRYPT_MODE_ECB);
	    	$iv = mcrypt_create_iv($iv_size, MCRYPT_RAND);
	    	$h_key = hash('sha256', $key, TRUE);
	    	return base64_encode(mcrypt_encrypt(MCRYPT_RIJNDAEL_256, $h_key, $input_string, MCRYPT_MODE_ECB, $iv));
	    else:
	    	return base64_encode($input_string);
	    endif;
	}
	
	// Decrypt Function
	function escalate_decrypt($encrypted_input_string, $key){
		if(function_exists(mcrypt_get_iv_size)):
		    $iv_size = mcrypt_get_iv_size(MCRYPT_RIJNDAEL_256, MCRYPT_MODE_ECB);
		    $iv = mcrypt_create_iv($iv_size, MCRYPT_RAND);
		    $h_key = hash('sha256', $key, TRUE);
		    return trim(mcrypt_decrypt(MCRYPT_RIJNDAEL_256, $h_key, base64_decode($encrypted_input_string), MCRYPT_MODE_ECB, $iv));
		else:
			return base64_decode($input_string);
		endif;
	}
	
	/*
	 * This method has been included in order to perform more
	 * precise logic when and why to cache offers.
	 * 
	 *
	 * @method cacheOffers
	 * @author David Koenig
	 * @since 1.0.6
	 * @see admin-ajax.php (included inline)
	 *
	 */
	
	function cacheOffers ($options, $api_url)
	{
		global $wpdb;
		
		// Load API From Escalate Network
		$xml_url = $api_url.'get-offers.php?aff_id=' . $options['affiliate_id'] . '&details=true';
		$xml_data = simplexml_load_file($xml_url);
		
		// Make Sure the Feed is Fully Loaded
		if($xml_data->meta->complete && !$xml_data->error)
		{
		 
			// Empty Tables
			$wpdb->query("TRUNCATE TABLE `escalate_offers`");
			$wpdb->query("TRUNCATE TABLE `escalate_offer_files`");
			
			// Insert Queries
			
				
			foreach($xml_data->offers->offer as $offer)
			{
				// Insert Offers
				$wpdb->query($wpdb->prepare(
					"INSERT INTO escalate_offers (id, name, default_payout, preview_url, description, modified) VALUES (%d, %s, %s, %s, %s, %d)", 
					array($offer->id, $offer->name, $offer->default_payout, $offer->preview_url, $offer->description, $offer->modified)
				));
			}

			return true;
		}
		else /* If the Feed didn't Fully Load */
		{
			return 'We have encountered an error retrieving the latest offers.';
		}
	
	}
	
endif;
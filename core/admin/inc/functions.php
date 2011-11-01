<?php if(is_admin()):
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
endif;
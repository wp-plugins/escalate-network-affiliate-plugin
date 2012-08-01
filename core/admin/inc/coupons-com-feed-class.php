<?php
// Originally Developed by Escalate Network, Turned into a Class by OodleTech
class coupons_com_feed_class {
	var $items, $brands, $categories; 
	
	function __construct($method = 'new'){
		$this->items($method);
		$this->items;
	}
	//////////////////////////////////////////////////////////////////////////////////////////////////
	//////////////////////////////////////////////////////////////////////////////////////////////////
	function dateToTimeStamp($dateLine) {
		$dateLines = explode(" ", $dateLine);
		list($month, $day, $year) = explode("/", $dateLines['0']); // 5/1/2011
		list($hour, $mintues, $seconds) = explode(":", $dateLines['1']); // 1:39:00
		return mktime($hour, $mintues, $seconds, $month, $day, $year);
	}
	//////////////////////////////////////////////////////////////////////////////////////////////////
	//////////////////////////////////////////////////////////////////////////////////////////////////
	function resortItems($array, $method, $limit = false) {
	
		// Index the array as specified by $method

		switch ($method)
		{
			case 'new':
				foreach($array as $key => $val) {
					$sorted[$val['activedatestamp']][$key] = $val;
				}
				krsort($sorted);
			break;
			case 'old':
				foreach($array as $key => $val) {
					if ($val['expiration_unix'] < (time() + 604800))
					{
						$sorted[$val['expiration_unix']][$key] = $val;
					}
				}
				ksort($sorted);
			break;
			case 'cat':
				foreach($array as $key => $val) {
					$sorted[strtoupper($val['majorCategory'])][$key] = $val;
				}
				ksort($sorted);
				$limit = false;
			break;
			case 'value':
				foreach($array as $key => $val) {
					$sorted[$val['value']][$key] = $val;
				}
				krsort($sorted);
			break;
			case 'brand':
				foreach($array as $key => $val) {
					$sorted[strtoupper($val['brand'])][$key] = $val;
				}
				ksort($sorted);
				$limit = false;
			break;
			
		}
		$limit = false;
		// Nice lil' debug string for viewing how your sort logic worked :) 
		//print_r(array_keys($sorted));
		if ($sorted)
		{
			foreach ($sorted as $sortarray)
			{
				foreach($sortarray as $item) {
					$i++;
					$items[] = $item;
					$this->categories[ucwords($item['majorCategory'])]++;
					$this->brands[ucwords($item['brand'])]++;
			
					if ($limit and $limit == $i) {
						break;
					}
				}
			}
			ksort($this->brands);
			ksort($this->categories);
		}
		return $items;
	}
	//////////////////////////////////////////////////////////////////////////////////////////////////
	//////////////////////////////////////////////////////////////////////////////////////////////////
	function UTF8ToEntities ($string) {
	    /* note: apply htmlspecialchars if desired /before/ applying this function
	    /* Only do the slow convert if there are 8-bit characters */
	    /* avoid using 0xA0 (\240) in ereg ranges. RH73 does not like that */
	    if (! ereg("[\200-\237]", $string) and ! ereg("[\241-\377]", $string))
	        return $string;
	    
	    // reject too-short sequences
	    $string = preg_replace("/[\302-\375]([\001-\177])/", "&#65533;\\1", $string); 
	    $string = preg_replace("/[\340-\375].([\001-\177])/", "&#65533;\\1", $string); 
	    $string = preg_replace("/[\360-\375]..([\001-\177])/", "&#65533;\\1", $string); 
	    $string = preg_replace("/[\370-\375]...([\001-\177])/", "&#65533;\\1", $string); 
	    $string = preg_replace("/[\374-\375]....([\001-\177])/", "&#65533;\\1", $string); 
	    
	    // reject illegal bytes & sequences
	        // 2-byte characters in ASCII range
	    $string = preg_replace("/[\300-\301]./", "&#65533;", $string);
	        // 4-byte illegal codepoints (RFC 3629)
	    $string = preg_replace("/\364[\220-\277]../", "&#65533;", $string);
	        // 4-byte illegal codepoints (RFC 3629)
	    $string = preg_replace("/[\365-\367].../", "&#65533;", $string);
	        // 5-byte illegal codepoints (RFC 3629)
	    $string = preg_replace("/[\370-\373]..../", "&#65533;", $string);
	        // 6-byte illegal codepoints (RFC 3629)
	    $string = preg_replace("/[\374-\375]...../", "&#65533;", $string);
	        // undefined bytes
	    $string = preg_replace("/[\376-\377]/", "&#65533;", $string); 
	
	    // reject consecutive start-bytes
	    $string = preg_replace("/[\302-\364]{2,}/", "&#65533;", $string); 
	    
	    // decode four byte unicode characters
	    $string = preg_replace(
	        "/([\360-\364])([\200-\277])([\200-\277])([\200-\277])/e",
	        "'&#'.((ord('\\1')&7)<<18 | (ord('\\2')&63)<<12 |" .
	        " (ord('\\3')&63)<<6 | (ord('\\4')&63)).';'",
	    $string);
	    
	    // decode three byte unicode characters
	    $string = preg_replace("/([\340-\357])([\200-\277])([\200-\277])/e",
		"'&#'.((ord('\\1')&15)<<12 | (ord('\\2')&63)<<6 | (ord('\\3')&63)).';'",
	    $string);
	    
	    // decode two byte unicode characters
	    $string = preg_replace("/([\300-\337])([\200-\277])/e",
	    "'&#'.((ord('\\1')&31)<<6 | (ord('\\2')&63)).';'",
	    $string);
	    
	    // reject leftover continuation bytes
	    $string = preg_replace("/[\200-\277]/", "&#65533;", $string);
	    
	    return $string;
	}
	//////////////////////////////////////////////////////////////////////////////////////////////////
	//////////////////////////////////////////////////////////////////////////////////////////////////
	function items($method = 'new') {
		$options = get_option('escalate_network');
		$clean['extra_params'] = str_replace("%", "&amp;", $clean['extra_params']);	
		//$feedXml = "http://www.escalatenetwork.com/widget2/feeds.xml";
		
		$feedXml = "http://www.escalatenetwork.com/coupons/oodletech_external.xml?aff_id=".$options['affiliate_id'];
		
		if (is_object($results = simplexml_load_file($feedXml))) {
			foreach ($results->channel->item as $item) {
				$i++;
				$items[$i] = (array) $item;

				$items[$i]['description'] 	  = $this->UTF8ToEntities($items[$i]['title']);
				$items[$i]['brand'] 		  = $this->UTF8ToEntities($items[$i]['brand']);
				
				/* new with API 1.0.6 */
				$items[$i]['expiration_unix'] = $this->dateToTimeStamp($items[$i]['expiration']);

			}
			
		}
		
		unset($item, $i);
		$this->items = $this->resortItems($items, $method, false);
	}
}

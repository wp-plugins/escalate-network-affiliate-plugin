<?php
// Originally Developed by Escalate Network, Turned into a Class by OodleTech
class coupons_com_feed_class {
	var $items; 
	
	function __construct(){
		$this->items();
		$this->items;
	}
	//////////////////////////////////////////////////////////////////////////////////////////////////
	//////////////////////////////////////////////////////////////////////////////////////////////////
	function dateToTimeStamp($dateLine) {
		$dateLines = explode(" ", $dateLine);
		list($month, $day, $year) = explode("/", $dateLines['0']); // 5/1/2011
		list($hour, $mintues, $seconds) = explode(":", $dateLines['1']); // 1:39:00
		return mktime(11, 59, 59, $month, $day, $year);
	}
	//////////////////////////////////////////////////////////////////////////////////////////////////
	//////////////////////////////////////////////////////////////////////////////////////////////////
	function resortItems($array, $limit = false) {
		foreach($array as $key => $val) {
			$sorted[$val['activedatestamp'] + $key] = $val;
		}
	
		krsort($sorted);
		
		foreach($sorted as $item) {
			$i++;
			$items[] = $item;
	
			if ($limit and $limit == $i) {
				break;
			}
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
	function items() {
		$options = get_option('escalate_network');
		$clean['extra_params'] = str_replace("%", "&amp;", $clean['extra_params']);	
		$feedXml = "http://www.escalatenetwork.com/widget2/feeds.xml";
		if (is_object($results = simplexml_load_file($feedXml))) {
			foreach ($results->item as $item) {
				$i++;
				$items[$i] = (array) $item;
		
				// rewrite the links
				preg_match("#cid=(\d+)#i", $items[$i]['link'], $match);		
		
				$items[$i]['link'] = sprintf(
					'http://strk.enlnks.com/aff_c?offer_id=118&aff_id=%d&url_id=57&aff_sub5=%d%s', 
					$options['affiliate_id'], 
					$match['1'],
					$clean['extra_params']
				);
				
				$items[$i]['activedatestamp'] = $this->dateToTimeStamp($items[$i]['activedate']);
				$items[$i]['description'] 	  = $this->UTF8ToEntities($items[$i]['description']);
				$items[$i]['brand'] 		  = $this->UTF8ToEntities($items[$i]['brand']);
			}
			
		}
		
		unset($item, $i);
		$this->items = $this->resortItems($items, 30);
		//echo '<pre>'; var_dump($items); echo '</pre>';
	}
}
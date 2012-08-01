jQuery.base64=(function($){var _PADCHAR="=",_ALPHA="ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789+/",_VERSION="1.0";function _getbyte64(s,i){var idx=_ALPHA.indexOf(s.charAt(i));if(idx===-1){throw"Cannot decode base64"}return idx}function _decode(s){var pads=0,i,b10,imax=s.length,x=[];s=String(s);if(imax===0){return s}if(imax%4!==0){throw"Cannot decode base64"}if(s.charAt(imax-1)===_PADCHAR){pads=1;if(s.charAt(imax-2)===_PADCHAR){pads=2}imax-=4}for(i=0;i<imax;i+=4){b10=(_getbyte64(s,i)<<18)|(_getbyte64(s,i+1)<<12)|(_getbyte64(s,i+2)<<6)|_getbyte64(s,i+3);x.push(String.fromCharCode(b10>>16,(b10>>8)&255,b10&255))}switch(pads){case 1:b10=(_getbyte64(s,i)<<18)|(_getbyte64(s,i+1)<<12)|(_getbyte64(s,i+2)<<6);x.push(String.fromCharCode(b10>>16,(b10>>8)&255));break;case 2:b10=(_getbyte64(s,i)<<18)|(_getbyte64(s,i+1)<<12);x.push(String.fromCharCode(b10>>16));break}return x.join("")}function _getbyte(s,i){var x=s.charCodeAt(i);if(x>255){throw"INVALID_CHARACTER_ERR: DOM Exception 5"}return x}function _encode(s){if(arguments.length!==1){throw"SyntaxError: exactly one argument required"}s=String(s);var i,b10,x=[],imax=s.length-s.length%3;if(s.length===0){return s}for(i=0;i<imax;i+=3){b10=(_getbyte(s,i)<<16)|(_getbyte(s,i+1)<<8)|_getbyte(s,i+2);x.push(_ALPHA.charAt(b10>>18));x.push(_ALPHA.charAt((b10>>12)&63));x.push(_ALPHA.charAt((b10>>6)&63));x.push(_ALPHA.charAt(b10&63))}switch(s.length-imax){case 1:b10=_getbyte(s,i)<<16;x.push(_ALPHA.charAt(b10>>18)+_ALPHA.charAt((b10>>12)&63)+_PADCHAR+_PADCHAR);break;case 2:b10=(_getbyte(s,i)<<16)|(_getbyte(s,i+1)<<8);x.push(_ALPHA.charAt(b10>>18)+_ALPHA.charAt((b10>>12)&63)+_ALPHA.charAt((b10>>6)&63)+_PADCHAR);break}return x.join("")}return{decode:_decode,encode:_encode,VERSION:_VERSION}}(jQuery));

jQuery(function($) {
/*######################################################################
# UNIVERSAL FUNCTIONS
######################################################################*/
	// Box Heading Collapse
	if($(".escalate-network").length) {
		$(".stuffbox h3, .stuffbox .handlediv").live("click", function(){
			$(this).parent().find(".inside").toggle();
		});
	}
	
	// Current TimeStamp Function
	function theCurrentTime() {
	    var currentTime = new Date(); 
	    var month = currentTime.getMonth() + 1; 
	    var day = currentTime.getDate(); 
	    var year = currentTime.getFullYear(); 
	    var hour = currentTime.getHours(); 
	    var minute = currentTime.getMinutes();
	    var second = currentTime.getSeconds();
	    var meridiem = "am";
		if (hour > 11) { meridiem = "pm"; }
		if (hour > 12) { hour = hour - 12; }
		if (hour == 0) { hour = 12; }
		//if (hour < 10) { hour   = "0" + hour; }
		if (minute < 10) { minute = "0" + minute; }
		if (second < 10) { second = "0" + second; }
	    var timeString = month + "/" + day + "/" + year + " @ " + hour + ":" + minute + meridiem;
	    return timeString;
	};
/*######################################################################
# EXAMPLE WORDPRESS AJAX CALLS
######################################################################*/
	/*
	// Example 1: Post data on Form Submit Selector + Additional Fields
	myPostData = $(this).serializeArray();
	myPostData.push({name: "action", value: "escalate_network_admin"});
	myPostData.push({name: "method", value: "update_settings"});
	$.ajax({
		url: ajaxurl,
		type: 'POST', 
		dataType: 'json', 
		data: myPostData,
		success: function(data) {
			// Do something with data on callback
		}
	});
	
	// Example 2: No Post Data AJAX Call
	$.ajax({
		url: ajaxurl,
		type: 'POST', 
		dataType: 'json', 
		data: { 'action': 'escalate_network_admin', 'method': 'update_settings' },
		success: function(data) {
			// Do something with data on callback
		}
	});
	*/
/*######################################################################
# SETTINGS PAGE JAVASCRIPT
######################################################################*/
	$(".escalate-network #settings_form").submit(function() {
		myPostData = $(this).serializeArray();
		myPostData.push({name: "action", value: "escalate_network_admin"});
		myPostData.push({name: "method", value: "update_settings"});
		$.ajax({
			url: ajaxurl,
			type: 'POST', 
			dataType: 'json', 
			data: myPostData,
			success: function(data) {
				$(".escalate-network #message").text('Settings Saved').show();
			}
		}); // end AJAX request
		return false;
	});
	
	$("#userpasslookupbtn").click(function(e){
		$("#userpasslookup").toggle();
		return false;
	});
	
	$("#userpassquery").click(function(e){
		$("#userpass.notification").html("Loading...").removeClass('success error').toggleClass('loading').show();
		
		
		$.ajax({
			url: ajaxurl,
			type: 'POST', 
			dataType: 'json', 
			data: { 'action': 'escalate_network_admin', 'method': 'load_affiliateid', 'user' : $("input[name='settings[username]']").val(), 'pass' : $("input[name='settings[password]']").val()},
			success: function(data) {
				if (data.error)
				{
					$("#userpass.notification").toggleClass('loading error').html(data.error);
					return false;
				}
			
				$("input[name='affiliate_id']").attr("value",data.affiliate_id+" Okay").removeClass('error').addClass('okay');
				
				$("#userpass.notification").toggleClass('loading success').html("We have successfully retrieved your Affiliate ID.").fadeOut(5000);
				
				//$("#userpasslookup").hide();
			}
		}).fail(function() {
			$("#userpass.notification").toggleClass('loading error').html("Request Timed Out : Please wait a few minutes and try again.");
		});
		return false;
	});
/*######################################################################
# DASHBOARD WIDGET
######################################################################*/
	if($('#escalate_network_stats').length){
		$.ajax({
			url: ajaxurl,
			type: 'POST', 
			dataType: 'json', 
			data: { 'action': 'escalate_network_admin', 'method': 'load_dashboard' },
			success: function(data) {
				$('.escalate-dashboard-loading').remove();
				$('#escalate_network_stats .inside').append(data.response);
			},
			error: function(evt)
			{
				$('.escalate-dashboard-loading').remove();
				$('#escalate_network_stats .inside').append('An error has occurred processing this request. Please check the Escalate Network <a href="options-general.php?page=escalate-network-options">settings page</a>.')
			}
		});
	}
/*######################################################################
# POST META BOX - ESCALATE OFFERS
######################################################################*/
	if($('#escalate_network_meta_box').length){
		// Toggle Details for Offer
		$("#escalate_meta_offers .offer-name a").live("click", function(){
			var line = $(this).parents('li').find('.second-line');
			
			line.toggle();
			if ( line.css('display') == 'block' )
			{
				offer_id = $(this).parents('li').attr('rel');
				/* load file details */
				$.ajax({
					url: ajaxurl,
					type: 'POST',
					dataType: 'json',
					data: { 'action': 'escalate_network_admin', 'method': 'load_file', 'offer_id': offer_id },
					success: function(data)
					{
						
						if (data.error)
						{
							line.find('.insert-offer-to-post').html('<div class="notification success">' + data.error + '</div>');
						}
						else
						{
							line.find('.insert-offer-to-post').html(data.insert);
						}
					},
					error: function(evt)
					{
						//console.log(evt);
						line.find('.insert-offer-to-post').html('<div class="notification error">An error has occurred attempting to fetch the details. Please try again.</div>');
					}
				});
			}
				
			return false;
		});
		
		// Javascript Advertisement in Thickbox fix
		
		$("a.btn64").live("click", function(){
			var b64js = $(this).parents('span').find(".b64js");
			
			/*wp_url*/
			var nw = window.open( wpurl + "?preview_offer=" + $(this).attr('rel') , "preview");
			return false;
		});
		
		// Search Offers Function
		function loadOffers(search,sort,direction){
			// Hide Offers Box
			$("#escalate_meta_offers").hide();
			
			// Clear Current Offers
			$("#escalate_meta_offers li").remove();
			
			// Display Loading
			$('.escalate-meta-box-loading').show();
			
			// Build Data Object
			dataObject = { action: 'escalate_network_admin', method: 'load_meta_box' }
			
			// Add Search to Object if It's Set
			if(search) { dataObject['search'] = search; }
			
			// Add Sort if It's Set
			if(sort) { dataObject['sort'] = sort; }
			
			// Add Sort Direction if it's set
			if(direction) { dataObject['direction'] = direction; }
			
			$.ajax({
				url: ajaxurl,
				type: 'POST', 
				dataType: 'json', 
				//data: { 'action': 'escalate_network_admin', 'method': 'load_meta_box', 'search': search },
				data: dataObject,
				success: function(data) {
					// Hide AJAX Loading Screen
					$('.escalate-meta-box-loading').hide();
					
					// Populate Offers
					$('#escalate_meta_offers').append(data.response).show();
					
					// Display No Offers Found Text if None are Found
					if($("#escalate_meta_offers li").length == 0) {
						$("#escalate_meta_offers").html('<li><center>No Offers Found</center></li>');
					}
					
					// Style LI's
					$("#escalate_meta_offers li:odd").addClass('alternate');
					
					// Set Time
					if($("#escalate_network_meta_box .hndle em").text() != data.time){
						$("#escalate_network_meta_box .hndle em").text(data.time)
						//alert(data.time);
					}
				},
				error: function(evt)
				{
					$('.escalate-meta-box-loading').hide();
					$("#escalate_meta_offers").html('<li><center>No Offers Found</center><br/>Please refresh the page or check the Escalate Network <a href="options-general.php?page=escalate-network-options">settings page</a>.</li>');
				}
			});
		}
		
		// Load Offers on Page Load
		var sortDefault = $('.escalate-sort-default').text();
		if(sortDefault == 'name') {
			loadOffers();
		} else {
			loadOffers('', 'modified', 'desc');
		}		
		
		// Search Offers Enter Field
		$("input[name='search_escalate']").live("keypress", function(e) {
			if(e.keyCode == '13'){
				var search = $(this).val();
				loadOffers(search);
				return false;
			}
		});
		
		// Search Offers Button
		$("button[name='search-escalate-offers']").live("click", function(){
			var search = $(this).siblings().val();
			loadOffers(search);
		});
		
		// Search Text Clear
		$("input[name='search_escalate']").live("click", function(){
			if($(this).val() == 'Search Offers') {
				$(this).val('');
			}
		});
		
		// Sort By Name
		$(".sort-escalate-offers-name").click(function(){
			loadOffers('','name');
			return false;
		});
		
		// Sort By Newest
		$(".sort-escalate-offers-newest").click(function(){
			loadOffers('', 'modified', 'desc');
			return false;
		});
		
		// Standard View Insert Into Post
		$(".add-offer-to-post").live("click", function(){
			// Set Vars for Editor
			var visualEditor = $('#content_ifr').contents().find('.wp-editor');
			var htmlEditor = $('#wp-content-editor-container textarea');
			
			// Set Vars for Offer/Creative
			var pc = $(this).parents(".insert-offer-to-post");
			var fileMeta = pc.find($("select[name='offer-creatives']")).val();
			var offerID = pc.find("select").val()
			var insertCode = pc.find('span[title="' + offerID + '"]').html();
			
			// If Creative was not selected
			if(fileMeta == 'Creative Options') {
				// Display Error for Selecti
				pc.find('.offer-error').text('You must select a creative before inserting into the post.').show();
			// If Not on Visual Editor
			} else if(htmlEditor.is(':visible')) {
			
				if (pc.find('span[title="' + offerID + '"]').hasClass('hidden'))
				{
					pc.find('.offer-error').hide();

					htmlEditor.append( $('<div />').text($.base64.decode(insertCode)).html() );
					
				}
				else
				{
					pc.find('.offer-error').text('You must switch to the Visual Editor before inserting into the post. You are currently on the HTML editor.').show();
				}
			// If passed Validation	
			} else {

				if (pc.find('span[title="' + offerID + '"]').hasClass('hidden'))
				{
					pc.find('.offer-error').text('You must switch to the HTML Editor before inserting Javascript Code into the post. You are currently using the Visual Editor').show();
				}
				else
				{
					// Hide Error
					pc.find('.offer-error').hide();
					// Load Into Box
					visualEditor.append(insertCode);
				}
			}	
			return false;
		});
		
		// Lightbox Insert to Post
		$(".lightbox-insert-link, .offer-info").live("click", function(){
			// Set Vars for Editor
			var visualEditor = $('#content_ifr').contents().find('.wp-editor');
			var htmlEditor = $('#wp-content-editor-container textarea');
			
			// Find out which selector was clicked and set HTML to Insert
			if($(this).hasClass('lightbox-insert-link')) {
				var insertCode = $(this).parents('h3').next('.offer-info').html();
				var offerInfo = $(this).parents('h3').next('.offer-info');
			} else {
				var insertCode = $(this).html();
				var offerInfo = $(this);
			}
			//console.log( offerInfo );
			if(htmlEditor.is(':visible')) {
				if ( offerInfo.hasClass('hidden'))
				{

					htmlEditor.append( $('<div />').text($.base64.decode(insertCode)).html() );
					
				}
				//console.log( offerInfo );
				//pc.find('.offer-error').text('You must switch to the Visual Editor before inserting into the post. You are currently on the HTML editor.').show();
			// If passed Validation	
			} else {
				// Hide Error
				//pc.find('.offer-error').hide();
				
				if (offerInfo.hasClass('hidden'))
				{
					// Must be in Text Editor to insert JS
				}
				else
				{
				// Load Into Box
					visualEditor.append(insertCode);
				}
			}
			return false;
		});
	} // end if($('#escalate_network_meta_box').length)
	
/*######################################################################
# POST META BOX - COUPONS.COM LINKS
######################################################################*/
	if($('#escalate_network_meta_box').length){
		// Coupons.com - Show Coupons.com Links
		$(".escalate-coupons-com").live("click", function(){
			$("#escalate-offers-container").hide();
			$("#escalate-coupons-com-container").show();
			return false;
		});
		
		
		// Coupons.com - jQuery Meta Order
		$(".escalate-coupons-com-sort").change( function()
		{
			if ( $(this).val() )
			{
				if ( $(this).val() != "cat" )
				{
					$("#jump_to_cat").parent().hide();
				}
				else
				{
					$("#jump_to_cat").parent().show();
				}
				
				if ( $(this).val() != "brand" )
				{
					$("#jump_to_brand").parent().hide();
				}
				else
				{
					$("#jump_to_brand").parent().show();
				}
				
				
				$('ul#escalate-coupons-com-links').html('<li><div class="escalate-meta-box-loading">Loading Feed</div></li>');
				dataObject = { action: 'escalate_network_admin', method: 'load_coupons', sort: $(this).val() }
				
				// Add Search to Object if It's Set

				$.ajax({
					url: ajaxurl,
					type: 'POST', 
					dataType: 'json', 
					data: dataObject,
					success: function(data) {
						//console.log(data);
						$('ul#escalate-coupons-com-links').html( data.insert );
						
					},
					error: function(evt)
					{
						$('ul#escalate-coupons-com-links').html('<div class="notification error">We encountered an error loading that sort. Please try again!');
						//$("#escalate_meta_offers").html('<li><center>No Offers Found</center><br/>Please refresh the page or check the Escalate Network <a href="options-general.php?page=escalate-network-options">settings page</a>.</li>');
					}
				});
			}
		
		});
		
		
		// Coupons.com - Jump To Category
		$("#jump_to_cat").change(function(){
			window.location.hash = $(this).val();
		});
		
		$("#jump_to_brand").change(function(){
			window.location.hash = $(this).val();
		});
		
		// Coupons.com - Checkbox Toggle
		$("input[name='escalate-coupons-com-toggle-all']").live("click", function(){
			$("#escalate-coupons-com-links input").prop("checked", this.checked);
		});
		
		$(".escalate-coupons-com-image img").live("click", function(){
			var $check = $(this).parent().parent().find(':checkbox');
			//console.log($(this).parent().parent().find(':checkbox'));
			$check.attr('checked', !$check.attr('checked'));
		});
		
		// Coupons.com - Go back to Escalate Offers
		$(".escalate-go-back-to-offers").live("click", function(){
			$("#escalate-coupons-com-container").hide();
			$("#escalate-offers-container").show();
			return false;
		});
		
		// Coupons.com - Insert Into Post
		$(".add-coupons-to-post").live("click", function(){
			// Set Vars for Editor
			var visualEditor = $('#content_ifr').contents().find('.wp-editor');
			var htmlEditor = $('#wp-content-editor-container textarea');
			var selectedCoupons = $("#escalate-coupons-com-links input:checked");
			
			// Reset Notifications
			$(".escalate-insert-error").text('').hide();
			$(".escalate-insert-success").text('').hide();
			
			// If HTML Editor - Return Error
			if(htmlEditor.is(':visible')) {
				$(".escalate-insert-error").text('You must be on the visual editor before inserting the coupons. You are currently on the HTML editor.').show();
			
			// IF none were Selected
			} else if(!selectedCoupons.length) {
				$(".escalate-insert-error").text('You did not select any coupons to insert.').show();
				
			// Else Insert Into Editor
			} else {
				visualEditor.append('<!-- Start EscalateNetwork Coupons -->');
				selectedCoupons.each(function(){
					insertCode = '<div class="escalate-coupons-com-image-css-selector"><a target="_blank" href="' +
						$(this).parent().find("input[name='escalate-coupons-com-hidden-link']").val() + '" title="' +
						$(this).attr('title') + '"><img src="' +
						$(this).parent().find("input[name='escalate-coupons-com-hidden-image']").val() + '" alt="' +
						$(this).attr('title') + '" border="0" /></a></div>';
					visualEditor.append(insertCode);
				});
				visualEditor.append('<!-- End EscalateNetwork Coupons -->');
				$(".escalate-insert-success").text('Your selected coupons have been inserted.').show();
			}
			return false;
		});
		
		// Coupons.com - Insert Into Post -- TEXT Coupons
		$(".add-coupons-to-post-text").live("click", function(){
			// Set Vars for Editor
			var visualEditor = $('#content_ifr').contents().find('.wp-editor');
			var htmlEditor = $('#wp-content-editor-container textarea');
			var selectedCoupons = $("#escalate-coupons-com-links input:checked");
			
			// Reset Notifications
			$(".escalate-insert-error").text('').hide();
			$(".escalate-insert-success").text('').hide();
			
			// If HTML Editor - Return Error
			if(htmlEditor.is(':visible')) {
				$(".escalate-insert-error").text('You must be on the visual editor before inserting the coupons. You are currently on the HTML editor.').show();
			
			// IF none were Selected
			} else if(!selectedCoupons.length) {
				$(".escalate-insert-error").text('You did not select any coupons to insert.').show();
				
			// Else Insert Into Editor
			} else {
				visualEditor.append('<!-- Start EscalateNetwork Coupons -->');
				selectedCoupons.each(function(){
					insertCode = '<div class="escalate-coupons-com-link-css-selector"><a href="' + $(this).parent().find("input[name='escalate-coupons-com-hidden-link']").val() + '" target="_blank">' + $(this).attr('title') + '</a></div>';
					visualEditor.append(insertCode);
				});
				visualEditor.append('<!-- End EscalateNetwork Coupons -->');
				$(".escalate-insert-success").text('Your selected coupons have been inserted.').show();
			}
			return false;
		});
		
		
	}
/*######################################################################
# END
######################################################################*/
});
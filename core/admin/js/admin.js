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
			}
		});
	}
/*######################################################################
# POST META BOX - ESCALATE OFFERS
######################################################################*/
	if($('#escalate_network_meta_box').length){
		// Toggle Details for Offer
		$("#escalate_meta_offers .offer-name a").live("click", function(){
			$(this).parents('li').find('.second-line').toggle();
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
					}
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
		
		/*
		$('#content_tbl').live("click", function(){ console.log($('#content_ifr').contents().find('.wp-editor').html());
			//editorContents = $(this).contents().find('.wp-editor');
			//console.log(editorContents);
		});
		*/
		
		// Standard View Insert Into Post
		$(".add-offer-to-post").live("click", function(){
			// Set Vars for Editor
			var visualEditor = $('#content_ifr').contents().find('.wp-editor');
			var htmlEditor = $('#editorcontainer textarea');
			
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
				pc.find('.offer-error').text('You must switch to the Visual Editor before inserting into the post. You are currently on the HTML editor.').show();
			// If passed Validation	
			} else {
				// Hide Error
				pc.find('.offer-error').hide();
				
				// Load Into Box
				visualEditor.append(insertCode);
			}	
			return false;
		});
		
		// Lightbox Insert to Post
		$(".lightbox-insert-link, .offer-info").live("click", function(){
			// Set Vars for Editor
			var visualEditor = $('#content_ifr').contents().find('.wp-editor');
			var htmlEditor = $('#editorcontainer textarea');
			
			// Find out which selector was clicked and set HTML to Insert
			if($(this).hasClass('lightbox-insert-link')) {
				var insertCode = $(this).parents('h3').next('.offer-info').html();
			} else {
				var insertCode = $(this).html();
			}
			
			if(htmlEditor.is(':visible')) {
				//pc.find('.offer-error').text('You must switch to the Visual Editor before inserting into the post. You are currently on the HTML editor.').show();
			// If passed Validation	
			} else {
				// Hide Error
				//pc.find('.offer-error').hide();
				
				// Load Into Box
				visualEditor.append(insertCode);
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
		
		// Coupons.com - Select All Link
		$(".escalate-coupons-com-select-all").live("click", function(){
			$("#escalate-coupons-com-links input").prop("checked", true);
			return false;
		});
		
		// Coupons.com - Unelect All Link
		$(".escalate-coupons-com-unselect-all").live("click", function(){
			$("#escalate-coupons-com-links input").prop("checked", false);
			return false;
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
			var htmlEditor = $('#editorcontainer textarea');
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
				selectedCoupons.each(function(){
					insertCode = '<div class="escalate-coupons-com-image-css-selector"><a target="_blank" href="' + $(this).parent().find("input[name='escalate-coupons-com-hidden-link']").val() + '"><img src="' + $(this).parent().find("input[name='escalate-coupons-com-hidden-image']").val() + '" alt="" /></a></div><div class="escalate-coupons-com-link-css-selector"><a href="' + $(this).parent().find("input[name='escalate-coupons-com-hidden-link']").val() + '" target="_blank">' + $(this).attr('title') + '</a></div>';
					visualEditor.append(insertCode);
				});
				$(".escalate-insert-success").text('Your selected coupons have been inserted.').show();
			}
			return false;
		});
	}
/*######################################################################
# END
######################################################################*/
});
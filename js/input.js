(function($){
	
	/*
	*  acf/setup_fields
	*
	*  This event is triggered when ACF adds any new elements to the DOM.
	*
	*  @param	event		e: an event object. This can be ignored
	*  @param	Element		postbox: An element which contains the new HTML
	*/
	
	$(document).live('acf/setup_fields', function(e, postbox){
		
		$(postbox).find('.oembed').on('paste drop',function(){

			_.defer( function(context) {
				$.post( ajaxurl, {
					action : 'acf_field_oembed_fetch',
					url    : $(context).val(),
				}, function( response ) {

					var title_el = $( '#' + $(context).attr('id') + '-title' );

					if ( response.success ) {
						title_el.text( response.data.title );
					} else {
						// silently fail
					}

				}, 'json' );
			}, this );
			
		});
	
	});

})(jQuery);

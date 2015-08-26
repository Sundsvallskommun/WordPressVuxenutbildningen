var first_load = true;
var alvis_link = '<div class="of-icon"><i class="of-color-gronsta1"><svg viewBox="0 0 512 512"><use xlink:href="#checkmark"></use></svg></i><span><a href="' + course_basket_link + '">Gå till kurskorg</a></span></div>';

jQuery( '.add-to-basket' ).click( function( event ) {
	event.preventDefault();
	jQuery(this).parent().find('.add-to-basket-spinner').show().addClass('showing');
	jQuery(this).remove();
	loadAlvisIframe( 'alvis-container', jQuery(this).attr('href') );


	var course_id =  jQuery(this).attr('href').split('?add=')[1];

	jQuery.post( ajaxurl, {

		action: 'add_to_basket',
		course: jQuery('#course-form').serializeArray(),
		id: course_id

	}, function( response ) {

	}, 'json' );

});


jQuery( '.remove-course' ).click( function( event ) {
	event.preventDefault();
	jQuery(this).parent().parent().remove();
	loadAlvisIframe( 'alvis-container', jQuery(this).attr('href') );

	var course_id = jQuery(this).attr('href').split('&bort=')[1].split('&')[0];

	jQuery.post( ajaxurl, {

		action: 'remove_from_basket',
		id: course_id

	}, function( response ) {

	}, 'json' );

});


jQuery('.apply-at-alvis').click( function( event ) {

	event.preventDefault();
	var alvis_direct_link = jQuery(this).attr('href');

	if( alvis_direct_link != undefined ) {

		if( confirm( 'Du kommer nu att skickas vidare till Alvis för att ansöka. Din kurskorg kommer då att tömmas.' ) ) {

			jQuery.post( ajaxurl, {
				
				action: 'empty_basket'

			}, function( response ) {

				if( response.result ) {

					document.location.href = alvis_direct_link;

				} else {

					alert( 'Ett fel inträffade vid tömmning av korgen.' );

				}

			});

		}

	}

});


function loadAlvisIframe(iframeName, url) {

	first_load = false;

  var $iframe = jQuery('#' + iframeName );
  if ( $iframe.length ) {
  	
  	$iframe.attr('src', url );
    return false;

  }

  return true;
}

jQuery('#alvis-container').load( function() {
	
	if( !first_load ) {
		var td = jQuery('.add-to-basket-spinner.showing').parent();
		jQuery('.add-to-basket-spinner.showing').remove();

		td.html( alvis_link );
	}

});
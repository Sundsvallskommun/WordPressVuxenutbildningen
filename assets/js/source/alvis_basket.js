( function( $ ){ 

var alvis_link = '<a class="link-to-basket" href="' + course_basket_link + '"><span class="glyphicon glyphicon-ok"></span> Gå till kurskorg</a>';

$( '.add-to-basket' ).click( function( event ) {
	event.preventDefault();
	$(this).parent().find('.add-to-basket-spinner').show().addClass('showing');
	
	td = $(this).closest('td');
	course_id = td.find('.course_id').val();
	$(this).remove();
	console.log( course_id );

	var td = $('.add-to-basket-spinner.showing').parent();
	
	$.post( ajaxurl, {
		action: 'add_to_basket',
		course: $('#course-form').serializeArray(),
		id: course_id
	}, function( response ) {

		if( response.result == false ){
			alert( 'Error message: ' + response.message );
		}

		$('.add-to-basket-spinner.showing').remove();
		td.html( alvis_link );


	}, 'json' );
});



$( '.remove-course' ).on('click', function() {	
	var course_id = $(this).closest('tr').attr('ID').replace( 'course-', '' );
	
	$.post( ajaxurl, {
		action: 'remove_from_basket',
		id: course_id
	}, function( response ) {

		$('.apply-at-alvis').attr( 'href', response );

	});

	$(this).closest('tr').remove();


	if( $('.sk-entry-content').find('table tbody tr').length == 0 ){
		$('.sk-entry-content').find('.course-basket-wrapper').remove();
		$('.sk-entry-content').append('<div class="basket-empty alert alert-blank">Du har inga kurser i korgen</div>');
	}

	return false;

});


$('.apply-at-alvis').click( function( event ) {

	event.preventDefault();
	var alvis_direct_link = $(this).attr('href');

	if( alvis_direct_link != undefined ) {

			$.post( ajaxurl, {
				action: 'empty_basket'
			}, function( response ) {

				if( response.result ) {
					//window.open( alvis_direct_link, 'Alvis' );
					document.location.href = alvis_direct_link;
				} else {
					alert( 'Ett fel inträffade vid tömmning av korgen.' );
				}

			$('.sk-entry-content').find('.course-basket-wrapper').remove();
			$('.sk-entry-content').append('<div class="basket-empty"><p>Vidarebefordning till Alvis pågår, var god dröj.</p><p><span class="of-preloader" role="progressbar"></span></p></div>');

			});

	}

});
}( jQuery ));
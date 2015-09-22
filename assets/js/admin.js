jQuery(document).ready( function( $ ) {
	
	$('#manual-course-import').click( function( event ) {
		event.preventDefault();

		$('#manual-course-import').hide();
		$('#manual-import-result').show();

		$.post( ajaxurl, {

			action: 'manual_course_import'

		}, function( response ) {

			if( response.result === true ) {

				$('#manual-import-result img').remove();
				$('#manual-import-result').append( 'Allt gick bra. Antal importerade kurser: ' + response.num_courses );
				$('#latest-import-time').text( response.import_status );

			} else {

				$('#manual-import-result img').remove();
				$('#manual-import-result').append( 'Ett fel inträffade. Fel: ' + response.message );
				$('#latest-import-time').text( response.import_status );

			}

		}, 'json' );

	});

});

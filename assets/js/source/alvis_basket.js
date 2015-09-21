function launchApplication(l_url, l_windowName)
{
  if ( typeof launchApplication.winRefs == 'undefined' )
  {
    launchApplication.winRefs = {};
  }
  if ( typeof launchApplication.winRefs[l_windowName] == 'undefined' || launchApplication.winRefs[l_windowName].closed )
  {
  	/*
    var l_width = screen.availWidth;
    var l_height = screen.availHeight;

    var l_params = 'status=1' +
                   ',resizable=1' +
                   ',scrollbars=1' +
                   ',width=' + l_width +
                   ',height=' + l_height +
                   ',left=0' +
                   ',top=0';
*/
    launchApplication.winRefs[l_windowName] = window.open(l_url, l_windowName);
    //launchApplication.winRefs[l_windowName].moveTo(0,0);
    //launchApplication.winRefs[l_windowName].resizeTo(l_width, l_height);
  } else {
    launchApplication.winRefs[l_windowName].focus()
  }
}


var first_load = true;
var alvis_link = '<div class="of-icon"><i class="of-color-gronsta1"><svg viewBox="0 0 512 512"><use xlink:href="#checkmark"></use></svg></i><span><a href="' + course_basket_link + '">Gå till kurskorg</a></span></div>';
var preloader = '<span class="of-preloader" role="progressbar"><b></b></span>';
var alert_warning = '<div class="alert alert-blank"></div>';
var alert_success = '<div class="alert alert-blank"></div>';

var is_chrome = navigator.userAgent.indexOf('Chrome') > -1;
var is_explorer = navigator.userAgent.indexOf('MSIE') > -1;
var is_firefox = navigator.userAgent.indexOf('Firefox') > -1;
var is_safari = navigator.userAgent.indexOf("Safari") > -1;
var is_opera = navigator.userAgent.toLowerCase().indexOf("op") > -1;
if ((is_chrome)&&(is_safari)) {is_safari=false;}
if ((is_chrome)&&(is_opera)) {is_chrome=false;}

jQuery( '.add-to-basket' ).click( function( event ) {
	event.preventDefault();
	jQuery(this).parent().find('.add-to-basket-spinner').show().addClass('showing');
	jQuery(this).remove();
	
	
	var course_id =  jQuery(this).attr('href').split('?add=')[1];

	var td = jQuery('.add-to-basket-spinner.showing').parent();
	
	jQuery.post( ajaxurl, {
		action: 'add_to_basket',
		course: jQuery('#course-form').serializeArray(),
		id: course_id
	}, function( response ) {

		if( response.result == false ){
			alert( 'Error message: ' + response.message );
		}

		jQuery('.add-to-basket-spinner.showing').remove();
		td.html( alvis_link );


	}, 'json' );
});



jQuery( '.add-to-basket_old' ).click( function( event ) {
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

		if( response.result == false ){
			alert( 'Error message: ' + response.message );
		}

	}, 'json' );

});


jQuery( '.remove-course' ).click( function( event ) {
	event.preventDefault();
	
	var course_id = jQuery(this).closest('tr').attr('ID').replace( 'course-', '' );
	var course_id = jQuery(this).closest('tr').remove();

	if( jQuery('.sk-entry-content').find('table tbody tr').length == 0 ){
		jQuery('.sk-entry-content').find('.course-basket-wrapper').remove();
		jQuery('.sk-entry-content').append('<div class="basket-empty alert alert-blank">Du har inga kurser i korgen</div>');
	}
});

jQuery('#alvis-connection a').click( function( event ) {
	jQuery('#alvis-connection').html( alert_warning );
  jQuery('#alvis-connection .alert').append( preloader );
	
	window.open( 'https://sundsvall.alvis.gotit.se/student/kurskatalog.aspx', 'Alvis' );
	alert( 'Anslutning mot Alvis skapad genom att vi öppnat en ny tabb i din webbläsare. Klicka ok för att gå vidare.' );
	window.focus();
	

	jQuery('#alvis-connection').html( alert_success );
	jQuery('#alvis-connection .alert').html('<i class="glyphicon glyphicon-ok"></i> Anslutning mot Alvis är skapad. Steg 1 av 3.');
		

	jQuery('#alvis-add-courses').show();
});


jQuery('#add_to_alvis').click( function( event ) {

	 	jQuery('.course-basket-table tbody tr').each( function( element ) {
    	alvis_course_id = jQuery(this).attr('ID').replace( 'course-', '' );
    	alvis_url = 'https://sundsvall.alvis.gotit.se/student/laggtillkorg.aspx?add=' + alvis_course_id;
			loadAlvisIframe( 'alvis-container', alvis_url );
   	});

   	jQuery('#alvis-add-courses').html( alert_warning );
   	jQuery('#alvis-add-courses .alert').append( preloader );
   	
});

jQuery('.apply-at-alvis').click( function( event ) {

	event.preventDefault();
	var alvis_direct_link = jQuery(this).attr('href');

	if( alvis_direct_link != undefined ) {

		if( confirm( 'Du kommer nu att skickas vidare till Alvis för att gå vidare med din ansökan.' ) ) {

			jQuery.post( ajaxurl, {
				
				action: 'empty_basket'

			}, function( response ) {

				if( response.result ) {
					//window.open( alvis_direct_link, 'Alvis' );
					document.location.href = alvis_direct_link;
				} else {
					alert( 'Ett fel inträffade vid tömmning av korgen.' );
				}

			jQuery('.sk-entry-content').find('.course-basket-wrapper').remove();
			jQuery('.sk-entry-content').append('<div class="basket-empty">Du har inga kurser i korgen</div>');

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

		jQuery('#alvis-add-courses').html( alert_success );
		jQuery('#alvis-add-courses .alert').html('<i class="glyphicon glyphicon-ok"></i> Kurser tillagda till studentkonto hos Alvis. Steg 2 av 3.');
		

		jQuery('#alvis-proceed').show();
	}

});
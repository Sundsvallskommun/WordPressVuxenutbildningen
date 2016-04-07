jQuery( document ).ready( function( $ ) {

	// Filter the faq list
	$('.searchFilter').keyup(function(){
   
   var valThis = $(this).val().toLowerCase();
   $('.sk-faq-list>li').each(function() {
    	var text = $(this).find('h5 span').text().toLowerCase();
     	(text.indexOf(valThis) >= 0) ? $(this).fadeIn() : $(this).fadeOut();
   	});
	});

});
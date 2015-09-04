(function ($) {
  "use strict";
  
  $.fn.course_filter_search = function() {  
    var form = $(this).closest('form');

    if( $('#course-occupation').is(':hidden') ) {  
      form.find('#course-occupation input[type=text]').val('');
      form.find('#course-occupation input[type=checkbox]').prop('checked', false);
      form.find("#course-occupation select option").prop('selected', false);    
    }

    if( $('#course-single').is(':hidden') ) {
      form.find('#course-single input[type=text]').val('');
      form.find('#course-single input[type=checkbox]').prop('checked', false);
      form.find("#course-single select option").prop('selected', false);    
    }

    var post_object = $('#form-single-courses').serializeArray(); 

    var data = {
      'action': 'search_courses',
      data: post_object
    };

    $.post( ajax_object.ajaxurl, data, function( response ) {
      


      $('.sk-courselist-posts-block').empty();
      $('.sk-courselist-posts-block').append(response);
        
        $('.sk-courselist-posts-block .jscroll').jscroll({
          contentSelector: 'ul.sk-grid-list',
          loadingHtml: '',
          nextSelector: 'a.next-scroll-block',
          refresh: true
        });

        
    }).error(function(){
      alert ("Problem calling: " + action + "\nCode: " + this.status + "\nException: " + this.statusText);
    });

  };

  $(function() {
    
    //$("body").scrollTop($("body").scrollTop() + 3000);

    $('.jscroll').jscroll({
      contentSelector: 'ul.sk-grid-list',
      loadingHtml: '',
      nextSelector: 'a.next-scroll-block',
      refresh: true,
      debug: false,
    });

    $( '#clear-courselist-filter' ).on('click', function() {

      var form = $(this).closest('form');
      form.find('input[type=text]').val('');
      form.find('input[type=checkbox]').prop('checked', false);
      form.find('#filter-taxonomy-amnesomrade option[value=""]').prop("selected", true);
      form.find('#filter-sortorder option[value="sort-alpha"]').prop("selected", true);


      // delete our transient
      var data = {
        'action': 'delete_session'
      };
      $.post( ajax_object.ajaxurl, data, function( response ) { 
      }).error(function(){
        alert ("Problem calling: " + action + "\nCode: " + this.status + "\nException: " + this.statusText);
      });


      $( '#btn-courselist-filter' ).click();

      return false;
    });

    $('#course-filter-search').keypress(function (e) {
      if (e.which == 13) {
        $( '#btn-courselist-filter' ).click();    
        return false;  
      }
    });

    $( '#btn-courselist-filter' ).on('click', function() {
      $(this).course_filter_search();
      return false;
    });

    $( '#filter-sortorder' ).on('change', function() {
      $(this).course_filter_search();
      return false;
    });

    $('.search-education-tabs li').on('click', function() {
      $('.sk-courselist-posts-block .jscroll').empty();

      var form = $(this).closest('form');
      form.find('input[type=text]').val('');
      form.find('input[type=checkbox]').prop('checked', false);
      form.find('#filter-taxonomy-amnesomrade option[value=""]').prop("selected", true);
      form.find('#filter-sortorder option[value="sort-alpha"]').prop("selected", true);
      


      if( $(this).attr('id') == 'educations-tab' ) {

        $('#filter-search-type').val('educations');

        /*
        $('#course-occupation input[type=checkbox]').each( function( element ) {
          //$(this).prop('checked', true);
        });
        */

      } else {

        $('#filter-search-type').val('courses');

      }

      var post_object = $('#form-single-courses').serializeArray();

      $('#course-occupation input[type=checkbox]').each( function( element ) {

        $(this).prop('checked', false );

      });

      var data = {
        action: 'search_courses',
        data: post_object
      };

      $.post( ajax_object.ajaxurl, data, function( response ) {

        $('.sk-courselist-posts-block').empty();
        $('.sk-courselist-posts-block').append(response);
        $('.sk-courselist-posts-block .jscroll').jscroll({
          contentSelector: 'ul.sk-grid-list',
          loadingHtml: '',
          nextSelector: 'a.next-scroll-block',
          refresh: true
        });      
        
      }).error(function(){
        alert ("Problem calling: " + action + "\nCode: " + this.status + "\nException: " + this.statusText);
      });

    });



    // Check for placeholder support
    $.support.placeholder = ('placeholder' in document.createElement('input'));

    // Slideshow, single item
    $('.owl-carousel.single').owlCarousel({
      items: 1,
      nav: false,
      margin: 0,
      video: true,
      lazyLoad: true
    });

    // Slideshow, multiple items
    $('.owl-carousel.multiple').owlCarousel({
      items: 1,
      loop: true,
      nav: true,
      margin: 0,
      video: true,
      lazyLoad: true
    });

    // Gallery popup
    $('.sk-gallery-wrap').magnificPopup({
      delegate: 'a.image', // child items selector, by clicking on it popup will open
      type: 'image',
      tClose: 'Stäng (Escape)',
      tLoading: 'Laddar...',
      gallery: {
        enabled: true,
        arrowMarkup: '<button title="%title%" type="button" class="mfp-arrow mfp-arrow-%dir%"></button>',
        tPrev: 'Föregående',
        tNext: 'Nästa',
        tCounter: '%curr% av %total%'
      },
      image: {      
        titleSrc: 'data-title'
      }
    });

    // Initialize sidebar menu advanced
    if($('.of-sidebar-menu-advanced').length > 0) {
      var sidebar_menu_advanced = new OF_Sidebar_Menu_Advanced('.of-sidebar-menu-advanced');
    }

    // Sticky menu
    $('.sk-main-menu-outer-wrap').waypoint(function(direction) {
      if(direction == 'down') {
        $('header.sk-site').addClass('sk-sticky-menu');
      }
      else {
        $('header.sk-site').removeClass('sk-sticky-menu');
      }
    });

    setTopBarHeight();

    $(document).on('click', '.js-mobile-menu-toggle', function() {
      $('body').removeClass('search-active');
    })
    .on('click', '.js-search-toggle', function(e) {
      e.preventDefault();
      var body = $('body');
      body.toggleClass('search-active');
      body.removeClass('of-sidebar-menu-advanced-active');
    });

    if (!$.support.placeholder) {
     $("[placeholder]").focus(function () {
         if ($(this).val() == $(this).attr("placeholder")) $(this).val("");
     }).blur(function () {
         if ($(this).val() == "") $(this).val($(this).attr("placeholder"));
     }).blur();

     $("[placeholder]").parents("form").submit(function () {
         $(this).find('[placeholder]').each(function() {
             if ($(this).val() == $(this).attr("placeholder")) {
                 $(this).val("");
             }
         });
     });
    }

  });
  
}(jQuery));

function setTopBarHeight() {
  return;
  var el = $('.sk-main-menu-wrap').find('.sk-top-bar');

  if(el.length === 0) {
    return;
  }

  // Set top bar height to match main-menu + header. Adding one extra pixel to compensate for the border
  el.css('height', $('.top-banner').outerHeight() + 1);

  return;
}
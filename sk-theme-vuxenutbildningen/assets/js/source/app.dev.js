(function ($) {
  "use strict";

  // Configure/customize these variables.
  var showChar = 110;  // How many characters are shown by default
  var ellipsestext = "...";
  var moretext = "Visa mer >";
  var lesstext = "Visa mindre";


  $('.read-more').each(function() {
    var content = $(this).html();

    if(content.length > showChar) {

      var c = content.substr(0, showChar);
      var h = content.substr(showChar, content.length - showChar);

      var html = c + '<span class="moreellipses">' + ellipsestext+ '&nbsp;</span><span class="morecontent"><span>' + h + '</span>&nbsp;&nbsp;<a href="" class="morelink">' + moretext + '</a></span>';

      $(this).html(html);

    }

  });

  $(".morelink").click(function(){
    if($(this).hasClass("less")) {
      $(this).removeClass("less");
      $(this).html(moretext);
    } else {
      $(this).addClass("less");
      $(this).html(lesstext);
    }
    $(this).parent().prev().toggle();
    $(this).prev().toggle();
    return false;
  });




  // needed to wrap inside a button, couldnt get it work. 
  var loading_html = '';
  loading_html += '<div class="jscroll-preloader">';
  loading_html += '<a href="#" class="of-btn" id="btn-courselist-filter">';
  loading_html += '<span class="of-preloader" role="progressbar"><b></b></span></div>';
  loading_html += '</a>';
  loading_html += '</div>';
  
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
      
      $('.sum-search-result-header').remove(); // remove element when ajax triggered to prevent duplicates.
      
      $('.sk-courselist-posts-block').empty();
      $('.sk-courselist-posts-block').append(response);
        
        $('.sk-courselist-posts-block .jscroll').jscroll({
          contentSelector: 'ul.sk-grid-list',
          loadingHtml: loading_html,
          nextSelector: 'a.next-scroll-block',
          refresh: true,
          callback: jscrollEnded
        });

    }).success(function(){
    })
    .error(function(){
      alert ("Problem calling: " + action + "\nCode: " + this.status + "\nException: " + this.statusText);
    });

  };

  function jscrollEnded(){
    $(this).closest('.sk-courselist-posts-block').find('.sum-search-result-footer').hide(); // remove element when ajax triggered to prevent duplicates.
    $(this).find('.sum-search-result-footer').show(); 

    /*
    if( $(this).find('.sum-search-result-ended-footer #sum-current').length == $(this).find('.sum-search-result-ended-footer #sum-total').length ){
     $('html, body').animate({scrollTop:$(document).height()}, 'slow');    
    }
    */

    if( $(this).find('.sum-search-result-ended-footer').length > 0 ){
      $(this).find('.sum-search-result-ended-footer #sum-current').text($('.sk-courselist-posts-block article').length); 
      $(this).find('.sum-search-result-ended-footer #sum-total').text($('.sk-courselist-posts-block article').length); 
    }
    else{
      $(this).find('.sum-search-result-footer #sum-current').text($('.sk-courselist-posts-block article').length); 
    }
    
  }

  $(function() {

    $('.jscroll').jscroll({
      contentSelector: 'ul.sk-grid-list',
      loadingHtml: loading_html,
      nextSelector: 'a.next-scroll-block',
      refresh: true,
      debug: false,
      callback: jscrollEnded
    });



    $( '#clear-courselist-filter' ).on('click', function() {

      var form = $(this).closest('form');
      form.find('input[type=text]').val('');
      form.find('input[type=checkbox]').prop('checked', false);
      form.find('#show-only-appliable').prop('checked', true);
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

      form.find('input[id^="filter-ort"]').prop("checked", true);

      $( '#btn-courselist-filter' ).click();

      return false;
    });

    $('.filter-search').keypress(function (e) {
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
      form.find('input[type=checkbox]:not("#show-only-appliable")').prop('checked', false);
      form.find('#filter-taxonomy-amnesomrade option[value=""]').prop("selected", true);
      form.find('#filter-sortorder option[value="sort-alpha"]').prop("selected", true);
      form.find('input[id^="filter-ort"]').prop("checked", true);
      
      if( $(this).attr('id') == 'educations-tab' ) {
        $('#filter-search-type').val('educations');
      } else {
        $('#filter-search-type').val('courses');
      }

      var post_object = $('#form-single-courses').serializeArray();

      $("#course-occupation input[type=checkbox]:not('input[id^=filter-ort]')").each( function( element ) {

        $(this).prop('checked', false );

      });

      var data = {
        action: 'search_courses',
        data: post_object
      };

      $.post( ajax_object.ajaxurl, data, function( response ) {

        $('.sum-search-result-header').remove(); // remove element when ajax triggered to prevent duplicates.

        $('.sk-courselist-posts-block').empty();
        $('.sk-courselist-posts-block').append(response);
        $('.sk-courselist-posts-block .jscroll').jscroll({
          contentSelector: 'ul.sk-grid-list',
          loadingHtml: loading_html,
          nextSelector: 'a.next-scroll-block',
          refresh: true,
          callback: jscrollEnded
        });      


      }).success(function(){

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

    // Set a randow start image in the carousel
    if ( $('.home').find('.owl-carousel.multiple').length ) {
      var num_images = $('.home').find('.owl-carousel.multiple').find('.owl-dot').length;
      var random_image = Math.floor(Math.random() * num_images);
      var carousel = $('.home').find('.owl-carousel.multiple').owlCarousel();
      carousel.trigger('to.owl.carousel', random_image);
    }

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
(function ($) {
  
  "use strict";
  
  $(function() {

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
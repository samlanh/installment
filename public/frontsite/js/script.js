/*-----------------------------------------------------------------------------------
/*
/* Script for Resume
/*
-----------------------------------------------------------------------------------*/


/*----------------------------------------------------*/
/* Preloader
------------------------------------------------------ */

  $(window).load(function(){

    $('.loader').fadeOut();    
    $('#preloader').delay(350).fadeOut('slow');    
    $('body').delay(350);   

    });


 jQuery(document).ready(function($) {
	 
	 $(".js-select2").select2({
	  placeholder: "",
	  width: '100%'
	 
	});

/*----------------------------------------------------*/
/* Initializing jQuery Nice Scroll
------------------------------------------------------ */

    $("html").niceScroll({
      cursorcolor:"#11abb0", // Set cursor color
      cursorwidth: "8", // Sety cursor width
      cursorborder: "" // Set cursor border color, default left none
    });


/*----------------------------------------------------*/
/* FitText Settings
------------------------------------------------------ */

    setTimeout(function() {
	   $('h1.responsive-headline').fitText(1, { minFontSize: '28px', maxFontSize: '72px' });
	 }, 100);





/*----------------------------------------------------*/
/* Appear Animation
------------------------------------------------------*/
  new WOW().init();

/*----------------------------------------------------*/
/* Parallax for Header Content
------------------------------------------------------*/
$(window).scroll(function(e){
  parallax();
});


function parallax() {
  //var scrollPosition = $(window).scrollTop();
 // $('.banner').css('margin-top', (0 - (scrollPosition * .8)) + 'px');
}



/*----------------------------------------------------*/
/*	Make sure that #header-background-image height is
/* equal to the browser height.
------------------------------------------------------ */

   $('header').css({ 'height': $(window).height() });
   $(window).on('resize', function() {

        $('header').css({ 'height': $(window).height() });
        $('body').css({ 'width': $(window).width() })
   });





/*----------------------------------------------------*/
/*	Fade In/Out Primary Navigation
------------------------------------------------------*/

  $('#menu').click(function(){
    $(document.body).toggleClass("show-menu");
    $('.menu').toggleClass("close-menu");
  });


  var Menu = {
    
    el: {
      ham: $('.menu'),
      menuTop: $('.menu-top'),
      menuMiddle: $('.menu-middle'),
      menuBottom: $('.menu-bottom')
    },
    
    init: function() {
      Menu.bindUIactions();
    },
    
    bindUIactions: function() {
      Menu.el.ham
          .on(
            'click',
          function(event) {
          Menu.activateMenu(event);
          event.preventDefault();
        }
      );
    },
    
    activateMenu: function() {
      Menu.el.menuTop.toggleClass('menu-top-click');
      Menu.el.menuMiddle.toggleClass('menu-middle-click');
      Menu.el.menuBottom.toggleClass('menu-bottom-click'); 
    }
  };

  Menu.init();

/* Animate Left Menu */ 


/*----------------------------------------------------*/
/*	Modal Popup
------------------------------------------------------*/

    $('.item-wrap a').magnificPopup({

       type:'inline',
       fixedContentPos: false,
       removalDelay: 200,
       showCloseBtn: false,
       mainClass: 'mfp-fade'

    });

    $(document).on('click', '.popup-modal-dismiss', function (e) {
    		e.preventDefault();
    		$.magnificPopup.close();
    });


/*----------------------------------------------------*/
/*  Owl Carousel
/*----------------------------------------------------*/


    $(document).ready(function() {
     
    $("#testimonial-slides").owlCarousel({
     
    navigation : false, // Show next and prev buttons
    slideSpeed : 300,
    paginationSpeed : 400,
    singleItem:true
     
    // "singleItem:true" is a shortcut for:
    // items : 1,
    // itemsDesktop : false,
    // itemsDesktopSmall : false,
    // itemsTablet: false,
    // itemsMobile : false
     
    });
     
    });





});









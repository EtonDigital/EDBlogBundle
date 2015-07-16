/**
 *  Document and Window states
 */

$(document).ready(function(){

	wrapVideo();
  checkboxInit();

  $( 'select' ).each(function( index ) {
    recolorSelect( $(this) );
  });

}); // End of $(document).ready()


// Resize or orientationchange of window
$(window).bind('ready load resize orientationchange', function(){
  $('.modal:visible').each(centerModal); 
  recalculateSidebar();
});

// Whenever an Ajax request completes, jQuery triggers the ajaxComplete event
$(document).ajaxComplete(function() {
  $('.hastooltip').tooltip();
  checkboxInit();
});


/**
 *  Functions
 */

function recalculateSidebar(){
  var alltheway = $('.dashboard-menu.alltheway');
  $(alltheway).removeAttr("style");
  var windowHeight = $(document).height();
  var headerHeight = $('header[role=banner]').outerHeight();
  var allthewayHeight = windowHeight - headerHeight;
  var dashboardContentHeight = $('.dashboard-content').height();
  if ( $(window).width() >= 992 ) {
    if ( dashboardContentHeight >= allthewayHeight ) {
      $(alltheway).css('min-height', dashboardContentHeight);
    } else {
      $(alltheway).css('min-height', allthewayHeight);
    }
  } else {
    $(alltheway).css('min-height', '1px');
  }
}

function centerModal() {
    $(this).css('display', 'block');
    var $dialog = $(this).find(".modal-dialog");
    var offset = ($(window).height() - $dialog.height()) / 2;
    // Center modal vertically in window with fix for modals with height greater than window, native behavior (with scroll) occurs
    if (offset > 30) { 
        $dialog.css("margin-top", offset);
    } else {
        setTimeout(function(){
            $dialog.parents('.modal').css('padding-left', '16px');
            $dialog.css("margin-top", 30);
        }, 500);
    }
}

function wrapVideo() {
  $('iframe').each(function() {
    var pad = Math.floor($(this).height()/$(this).width()*100) + '%';
    $(this).wrap("<div class='video-wrapper'/>");
    // Variant 1 is with padding-bottom given through css (always the same), and variant 2 is using dynamic value from this function
    //$(this).parent('.video-wrapper').css('padding-bottom', pad);

    //add wmode, so videos position below floating elements
    var url = $(this).attr("src")
    // just adding new attr wmode should be sufficient... altering url is a for nay case
    $(this).attr('src',url+'?wmode=opaque').attr('wmode', 'opaque');
  });
}

// When coming from article, we need to make table responsive 
function wrapTable() {
  $('.table-rework table').each(function() {
    $(this).wrap("<div class='table-responsive'/>");
  });
}

function checkboxCalculate($drop) {
  sel = $drop.find('select');
  opt = $drop.find('select > option');
  len = $drop.find('.chck:checked').length;
  if ( len > 0 ) {
    opt.html(len + ' selected');
    sel.removeClass('color-placeholder');
  } else {
    opt.html(sel.data('wording'));
    sel.addClass('color-placeholder');
  }
}

function checkboxInit(){
  $('.dropdown.keep-open').each(function() {
    checkboxCalculate($(this));
  });
}

// Color-placeholder and select elements
function recolorSelect( $this ) {
  var val = $this.val();
  $('#result').append('<div>value=' + val + '</div>');
  if ( val != '' ) {
    $this.removeClass('color-placeholder');
  } else {
    $this.addClass('color-placeholder');
  }
}


/**
 *  Custom actions
 */

 // Global toggle
$(document).on('click', '.js-toggler', function(e){
  e.preventDefault();
  $(this).toggleClass('on');
  $(this).parent().find('.toggled').slideToggle();
});

// Dropdown Keep-open toggle
// Close dropdown menu open after click outside
$(document).on('click', function (e) {
  var toggler = $('.dropdown-toggler');
  var toggled = $('.dropdown-toggled');
  if ( toggler.is(e.target) ) {
    toggled.toggleClass('hidden');
  } else if (!toggled.is(e.target) && toggled.has(e.target).length === 0) { // if the target of the click isn't the toggled, nor a descendant of the toggled
    toggled.addClass('hidden');
  }
});

// Count checked checkoxes and present the number
$(document).on('change', 'input[type=checkbox]',function(){
  drop = $(this).parents('.dropdown');
  checkboxCalculate(drop);
});

// Resize left sidebar after textarea for article content was manually resized
$(document).on('mouseleave', '.mce-resizehandle', function() {
  recalculateSidebar();
});
$(document).on('mouseenter', '.mce-container', function() {
  recalculateSidebar();
});

$(document).on('change', 'select', function() {
  recolorSelect( $(this) );
});

// placeholder for IE
if( $().placeholder ) {
  $('input, textarea').placeholder();
}

// tooltip (bootstrap)
$('.hastooltip').tooltip();

// Modal
$('.modal').on('show.bs.modal', centerModal);

// Dashboard Navigation toggle
$('.toggle--dashboard-menu').on('click',function(e){
  e.preventDefault();
  $(this).toggleClass('on');
  $('.dashboard-menu nav').slideToggle();
});
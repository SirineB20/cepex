  /*sticky navbar funtion*/
  $(window).scroll(function () {

    if ($(window).scrollTop() > 100) {
      $('.header').addClass('sticky');
    } else {
      $('.header').removeClass('sticky');
    }
  });
  // ---navbar---

$(".btnmenu").click(function () {

    $(".btnmenu").toggleClass("closebtn");
    $('.boxmenunav').toggleClass('boxmenunavblock');
    $('body').toggleClass('fixed');
  });
  
  $(".closemenu").click(function () {
  
    $(this).removeClass('closebtn');
    $('.boxmenunav').removeClass('boxmenunavblock');
    $('body').removeClass('fixed');
  });
  
  // ---navbar---

  // ---Search----

$(".buttonsearch").click(function () {
  $('body').toggleClass('fixed');
  $(".searchdiv").toggleClass("searchdivblock");
  $(".buttonsearch").toggleClass("buttonsearchblock"); 

});

$(document).on('click', function (event) {
  if ($(".searchdiv").hasClass("searchdivblock")) {
    if (!$(event.target).closest('.serachprod, .buttonsearch').length) {
      $(".searchdiv").removeClass("searchdivblock");
      $(".buttonsearch").removeClass("buttonsearchblock");
      $('body').toggleClass('fixed');
    };
  };
});

// ---Search----
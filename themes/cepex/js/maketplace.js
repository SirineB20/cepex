$(document).ready(function () {
  var speed = 100;
  $('.menu-categ-maketplace > li.have-children > a').click(function(e){
    e.preventDefault();
    if ( ! $(this).parent().hasClass('active') ){
      $('.menu-categ-maketplace li ul').slideUp(speed);
      $(this).next().slideToggle(speed);
      $('.menu-categ-maketplace li').removeClass('active');
      $(this).parent().addClass('active');
    } else {
      $(this).next().slideToggle(speed);
      $('.menu-categ-maketplace li').removeClass('active');
    }
  });
});

$(".click-all-categ").click(function () {
    $('.menu-categ-maketplace').toggleClass('menu-categ-maketplace-open');
  });


  var $swiperSelector = $('.swiper-container');

  $swiperSelector.each(function(index) {
      var $this = $(this);
      $this.addClass('swiper-slider-' + index);
      
      var dragSize = $this.data('drag-size') ? $this.data('drag-size') : 300;
      var freeMode = $this.data('free-mode') ? $this.data('free-mode') : false;
      var loop = $this.data('loop') ? $this.data('loop') : false;
      var slidesDesktop = $this.data('slides-desktop') ? $this.data('slides-desktop') : 3.5;
      var slidesTablet = $this.data('slides-tablet') ? $this.data('slides-tablet') : 3.5;
      var slidesMobile = $this.data('slides-mobile') ? $this.data('slides-mobile') : 2.5;
      var spaceBetween = $this.data('space-between') ? $this.data('space-between'): 20;
  
      var swiper = new Swiper('.swiper-slider-' + index, {
        direction: 'horizontal',
        loop: loop,
        freeMode: freeMode,
        spaceBetween: spaceBetween,
        breakpoints: {
          1920: {
            slidesPerView: slidesDesktop
          },
          992: {
            slidesPerView: slidesTablet
          },
          480: {
             slidesPerView: slidesMobile
          }
        },
        navigation: {
          nextEl: '.swiper-button-next',
          prevEl: '.swiper-button-prev'
        },
        scrollbar: {
          el: '.swiper-scrollbar',
          draggable: true,
          dragSize: dragSize
        }
     });
  });

  // -- Partage Sociaux --
  $(".icon-partage-marketplace").click(function () {
    $('.ul-partage').toggleClass('ul-partage-show');
  });


  // Slider Produit
$('.slider-produit-marketplace').slick({
  arrows: false,
  dots: true,
  slidesToShow: 4,
 /* draggable: false,*/
  responsive: [
    {
      breakpoint: 992,
      settings: {
        arrows: false,
        dots: true,
        slidesToShow: 3
      }
    },
    {
      breakpoint: 768,
      settings: {
        arrows: false,
        dots: true,
        slidesToShow: 2
      }
    },
    {
      breakpoint: 480,
      settings: {
        arrows: false,
        dots: true,
        slidesToShow: 1
      }
    }
  ]
});
// End Slider Produit
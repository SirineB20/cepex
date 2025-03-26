function initSliderActualite() {
  $('.carousel-actualite').slick({
    slidesToShow: 3, // Show 3 slides at a time
    slidesToScroll: 3, // Scroll 3 slides at a time
    dots: true,
    infinite: true,
    responsive: [
      {
        breakpoint: 768, // For smaller screens
        settings: {
          slidesToShow: 1, // Show 1 slide at a time
          slidesToScroll: 1, // Scroll 1 slide at a time
          dots: true,
        }
      },
      {
        breakpoint: 1024, // For medium screens
        settings: {
          slidesToShow: 2, // Show 2 slides at a time
          slidesToScroll: 2, // Scroll 2 slides at a time
          dots: true,
        }
      }
    ]
  });
}

function initSlidercertif() {
  $('.carousel-certif').slick({
    slidesToShow: 1,
      slidesToScroll: 1, // Défilement d'une seule slide à la fois
      autoplay: false,
      dots: true,
      centerMode: true,
      arrows: true,
      prevArrow: '<button class="slick-prev custom-arrow"><i class="arrow left"></i></button>',
      nextArrow: '<button class="slick-next custom-arrow"><i class="arrow right"></i></button>',
      responsive: [
        {
          breakpoint: 1024, // Pour écrans moyens
          settings: {
            slidesToShow: 1,
            slidesToScroll: 1,
            dots: true,
          }
        },
        {
          breakpoint: 768, // Pour écrans plus petits
          settings: {
            slidesToShow: 1,
            slidesToScroll: 1,
            dots: true,
          }
        }
      ]
  });
}

jQuery(document).ready(function () {
  initSliderActualite();
  initSlidercertif();
});
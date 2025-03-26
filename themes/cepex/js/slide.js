// Gestion des modales
$(".card-modal").click(function () {
  var controlledby = $(this).data('controlledby');
  $('#' + controlledby).toggleClass('boxmodal-open');
});

$(".btn-close").click(function () {
  $('.boxmodal').removeClass('boxmodal-open');
});

// Initialisation du carrousel Owl Carousel
var $owl = $('.owl-carousel');
$owl.children().each(function(index) {
  $(this).attr('data-position', index); // NB: .attr() instead of .data()
});

$owl.owlCarousel({
  loop: false,
  dots: true,
  dotsEach: true,
  nav: false,
  items: 1,
  autoplay: false,
  autoplayTimeout: 3000,
  autoplayHoverPause: true,
  slideBy: 1,
  responsive: {
    480: {
      items: 1
    },
    768: {
      items: 1
    },
    992: {
      items: 3
    }
  }
});

$(document).on('click', '.owl-item>div', function() {
  var $speed = 300; // in ms
  $owl.trigger('to.owl.carousel', [$(this).data('position'), $speed]);
});

// Initialisation du carrousel Slick pour les éléments avec la classe "cards-support"
$('.cards-support').slick({
  arrows: false,
  dots: true,
  slidesToShow: 3,
  responsive: [
    {
      breakpoint: 992,
      settings: {
        arrows: false,
        dots: true,
        slidesToShow: 2
      }
    },
    {
      breakpoint: 768,
      settings: {
        arrows: false,
        dots: true,
        slidesToShow: 1
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

// Réinitialisation de la position du carrousel Slick lors du changement d'onglet Bootstrap
$('a[data-toggle="tab"]').on('shown.bs.tab', function (e) {
  $('.cards-support').slick('setPosition');
});

// Défilement en douceur vers un élément spécifique lors du clic sur un lien
document.addEventListener('DOMContentLoaded', function() {
  var clickableULs = document.querySelectorAll('.section-services a');
  if (clickableULs.length > 0) {
    for (var i = 0; i < clickableULs.length; i++) {
      clickableULs[i].addEventListener('click', function() {
        var sliderElement = document.getElementById('myTabContent');
        if (sliderElement) {
          sliderElement.scrollIntoView({ behavior: 'smooth' });
        }
      });
    }
  }
});

/*---section foanctionalites tabs---*/




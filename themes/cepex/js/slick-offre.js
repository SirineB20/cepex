var TrandingSlider = new Swiper(".tranding-slider", {
    effect: "coverflow",
    grabCursor: true,
    centeredSlides: true,
    loop: true,
    slidesPerView: "auto",
    coverflowEffect: {
      rotate: 0,
      stretch: 0,
      depth: 100,
      modifier: 2.5
    },
    pagination: {
      el: ".swiper-pagination",
      clickable: true
    },
  });

  document.addEventListener('DOMContentLoaded', function() {
    var clickableULs = document.querySelectorAll('.flexbox-service-offre a');
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

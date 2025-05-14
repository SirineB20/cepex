jQuery(document).ready(function ($) {
 

  // Gestion des boutons "Fermer" dans le popup de connexion
  $('#popup-login .close').on('click', function () {
    $('#popup-login').fadeOut();
  });

  // Redirection vers la page de connexion depuis le popup
  $('#login-redirect-button').on('click', function () {
    window.location.href = '/fr/user/login';
  });

  // Fermeture du popup en cliquant en dehors de son contenu
  $(window).on('click', function (event) {
    if ($(event.target).is('#popup-login')) {
      $('#popup-login').fadeOut();
    }
  });

});
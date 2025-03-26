// ---navbar---
document.getElementById('burgerMenuIcon').addEventListener('click', function() {
  var burgerMenu = document.getElementById('burgerMenu');
  if (burgerMenu.classList.contains('open')) {
    burgerMenu.classList.remove('open');
  } else {
    burgerMenu.classList.add('open');
  }
});

document.getElementById('burgerMenuIcon').addEventListener('click', function() {
  var burgerMenuIcon = document.getElementById('burgerMenuIcon');
  if (burgerMenuIcon.classList.contains('open')) {
      burgerMenuIcon.classList.remove('open');
  } else {
      burgerMenuIcon.classList.add('open');
  }
});
// ---navbar---

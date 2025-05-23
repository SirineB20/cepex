const boutonMarketplace = document.getElementById('edit-type-1-produits-marketplace');
const boutonExportateur = document.getElementById('edit-type-1-profile-exportateur');

// Ajouter les écouteurs d'événements
boutonMarketplace.addEventListener('click', function() {
  boutonMarketplace.className = 'button-selected';
  boutonExportateur.className = 'button-unselected';
});

boutonExportateur.addEventListener('click', function() {
  boutonExportateur.className = 'button-selected';
  boutonMarketplace.className = 'button-unselected';
});


(function (Drupal) {
  Drupal.behaviors.rctGlobe = {
    attach: function (context, settings) {
      // S'assurer que l'élément existe et que nous ne l'initialisons qu'une fois
      var chartDiv = document.getElementById("chartdiv");
      if (!chartDiv || chartDiv.hasAttribute("data-amcharts-initialized")) {
        return;
      }
      
      // Marquer l'élément comme initialisé
      chartDiv.setAttribute("data-amcharts-initialized", "true");
      
      // Création de l'élément racine
      var root = am5.Root.new("chartdiv");

      // Définition du thème personnalisé
      var myTheme = am5.Theme.new(root);
      myTheme.rule("Label").setAll({
        fontSize: "1em",
      });

      // Application de tous les thèmes en un seul appel
      root.setThemes([
        am5themes_Animated.new(root),
        am5themes_Responsive.new(root),
        myTheme
      ]);

      // Création du graphique de carte
      var chart = root.container.children.push(am5map.MapChart.new(root, {
        panX: "rotateX",
        panY: "rotateY",
        projection: am5map.geoOrthographic(),
        minZoomLevel: 1,
        maxZoomLevel: 1,
        paddingBottom: 0,
        paddingTop: 0
      }));

      // Création des séries de polygones pour les pays
      var polygonSeries = chart.series.push(am5map.MapPolygonSeries.new(root, {
        geoJSON: am5geodata_worldLow
      }));
      polygonSeries.mapPolygons.template.setAll({
        tooltipText: "{name}",
        toggleKey: "active",
        interactive: true
      });
      polygonSeries.mapPolygons.template.states.create("hover", {
        fill: root.interfaceColors.get("primaryButtonHover")
      });
      polygonSeries.mapPolygons.template.states.create("active", {
        fill: root.interfaceColors.get("primaryButtonHover")
      });

      // Création de la série pour l'arrière-plan (blanc)
      var backgroundSeries = chart.series.push(am5map.MapPolygonSeries.new(root, {}));
      backgroundSeries.mapPolygons.template.setAll({
        fill: am5.color(0xffffff),
        fillOpacity: 1,
        strokeOpacity: 0.5
      });
      backgroundSeries.data.push({
        geometry: am5map.getGeoRectangle(90, 180, -90, -180)
      });

      // Création de la série pour les océans (en rouge)
      var oceanSeries = chart.series.push(am5map.MapPolygonSeries.new(root, {
        geoJSON: am5geodata_worldLow,
        exclude: ["AQ"] // Exclure l'Antarctique
      }));
      oceanSeries.mapPolygons.template.setAll({
        fill: am5.color(0xcb043d), // Couleur pour la mer
        fillOpacity: 1,
      });

      // Gestion des événements pour la sélection des pays
      var previousPolygon;
      polygonSeries.mapPolygons.template.on("active", function (active, target) {
        if (previousPolygon && previousPolygon != target) {
          previousPolygon.set("active", false);
        }
        if (target.get("active")) {
          var centroid = target.geoCentroid();
          if (centroid) {
            chart.animate({ key: "rotationX", to: -centroid.longitude, duration: 1500, easing: am5.ease.inOut(am5.ease.cubic) });
            chart.animate({ key: "rotationY", to: -centroid.latitude, duration: 1500, easing: am5.ease.inOut(am5.ease.cubic) });
          }
        }
        previousPolygon = target;
      });

      // Collecter les données des RCT depuis les éléments DOM avec la classe 'hello-pays'
      var rctData = [];
      console.log("Recherche des éléments .hello-pays...");
      var helloPaysElements = document.querySelectorAll('.hello-pays');
      console.log("Nombre d'éléments trouvés:", helloPaysElements.length);
      
      helloPaysElements.forEach(function(element) {
        console.log("Contenu de l'élément:", element.textContent);
        var data = element.textContent.split('|');
        if (data.length === 3) {
          var name = data[0].trim();
          var longitude = parseFloat(data[1].trim());
          var latitude = parseFloat(data[2].trim());
          
          console.log("Données extraites:", name, longitude, latitude);
          
          // Vérifier la validité des coordonnées
          if (!isNaN(longitude) && !isNaN(latitude)) {
            var countryId = name.toLowerCase().replace(/\s+/g, '-');
            
            // Trouver les éléments de détail
            var detailsElement = document.getElementById('details-' + countryId);
            var address = '';
            var phone = '';
            var email = '';
            
            if (detailsElement) {
              var addressElem = detailsElement.querySelector('.boxadr:first-child span');
              var phoneElem = detailsElement.querySelector('.boxadr:nth-child(3) span:last-child');
              var emailElem = detailsElement.querySelector('.boxadr:last-child span:last-child');
              
              address = addressElem ? addressElem.textContent.trim() : '';
              phone = phoneElem ? phoneElem.textContent.trim() : '';
              email = emailElem ? emailElem.textContent.trim() : '';
            }
            
            rctData.push({
              name: name,
              longitude: longitude,
              latitude: latitude,
              address: address,
              phone: phone,
              email: email,
              countryId: countryId
            });
          }
        }
      });
      
      console.log("Données RCT collectées:", rctData);
      
      // Si aucune donnée n'est trouvée, utiliser des données de test pour vérifier l'affichage
      if (rctData.length === 0) {
        console.log("Aucune donnée RCT trouvée, utilisation de données de test");
        // Ajouter quelques emplacements de test si aucune donnée n'est trouvée
        rctData = [
          {
            name: "RCT turkey",
            longitude: 38.9615155,
            latitude: 35.25175,
            address: "Atatürk Bulvarı Derman Sk. No: 29 06050 Opera, Ulus, ANKARA",
            phone: "+90 50 44 33 635",
            email: "turkey.rct@gmail.com",
            countryId: "Turquie"
          },
          {
            name: "Allemagne",
            longitude: 13.4050,
            latitude: 52.5200,
            address: "Bureau test à Berlin",
            phone: "+49 987654321",
            email: "berlin@example.com",
            countryId: "allemagne"
          }
        ];
      }

      // Création des points pour les marqueurs
      var pointSeries = chart.series.push(am5map.MapPointSeries.new(root, {
        latitudeField: "latitude",
        longitudeField: "longitude"
      }));
      
      // Utiliser les données collectées
      pointSeries.data.setAll(rctData);

      pointSeries.bullets.push(function() {
        // Utiliser un cercle comme marqueur pour s'assurer que quelque chose s'affiche
        var circle = am5.Circle.new(root, {
          radius: 7,
          fill: am5.color(0xffffff), // Jaune vif pour être bien visible
          tooltipText: "{name}",
          strokeWidth: 2,
          stroke: am5.color(0xcb043d) // Bordure noire
        });
        
        circle.events.on("click", function(ev) {
          var dataContext = ev.target.dataItem.dataContext;
          var countryId = dataContext.countryId || dataContext.name.toLowerCase().replace(/\s+/g, '-');
          
          console.log("Marqueur cliqué:", dataContext.name);
          
          // Masquer tous les détails
          document.querySelectorAll('.maps-details').forEach(function(el) {
            el.style.display = 'none';
          });
          
          // Afficher les détails du pays cliqué
          var detailsElement = document.getElementById('details-' + countryId);
          if (detailsElement) {
            detailsElement.style.display = 'block';
            console.log("Affichage des détails pour:", countryId);
            
            // Mettre à jour les informations de contact affichées (si présentes dans la page)
            var contactName = document.getElementById('contact-name');
            var contactAddress = document.getElementById('contact-address');
            var contactPhone = document.getElementById('contact-phone');
            var contactEmail = document.getElementById('contact-email');
            
            if (contactName) contactName.innerText = dataContext.name;
            if (contactAddress) contactAddress.innerText = dataContext.address || '';
            if (contactPhone) contactPhone.innerText = dataContext.phone || '';
            if (contactEmail) contactEmail.innerText = dataContext.email || '';
          } else {
            console.log("Aucun élément de détail trouvé pour:", countryId);
          }
        });
        
        return am5.Bullet.new(root, {
          sprite: circle,
     
        });
      });

      // Animation d'apparition du graphique
      chart.appear(1000, 100);
    }
  };
})(Drupal);
  /**
* ---------------------------------------
* This demo was created using amCharts 5.
* 
* For more information visit:
* https://www.amcharts.com/
* 
* Documentation is available at:
* https://www.amcharts.com/docs/v5/
* ---------------------------------------
*/
// Create root element
// https://www.amcharts.com/docs/v5/getting-started/#Root_element
var root = am5.Root.new("chartdiv");

root.setThemes([
  am5themes_Responsive.new(root)
]);


root.setThemes([
  am5themes_Responsive.newEmpty(root)
]);

const responsive = am5themes_Responsive.new(root);

responsive.addRule({
  relevant: am5themes_Responsive.widthL,
  applying: function() {
    chart.root.dom.style.height = "400px";
    }
  }
);



/*
root.themes.create("customTheme", {
  // Define the responsive rules
  "responsive": {
    // Adjust the height when the width is 600 or below
    "width <= 600": {
      "global": {
        "resizable": true // Allow resizing of the chart
      },
      "root": {
        "height": 400 // Set the desired height for the chart
      }
    }
  }
});
*/


// Set themes
// https://www.amcharts.com/docs/v5/concepts/themes/
var myTheme = am5.Theme.new(root);

myTheme.rule("Label").setAll({
fontSize: "1em",

});

myTheme.rule("MapPolygonSeries").setAll({
//fill: am5.color(0xedf7fa),
});

root.setThemes([
am5themes_Animated.new(root),
myTheme
]);


// Create the map chart
// https://www.amcharts.com/docs/v5/charts/map-chart/
var chart = root.container.children.push(am5map.MapChart.new(root, {
panX: "rotateX",
panY: "rotateY",
projection: am5map.geoOrthographic(),
minZoomLevel: 1,
maxZoomLevel: 1,
paddingBottom: 0,
paddingTop: 0
}));


// Create main polygon series for countries
// https://www.amcharts.com/docs/v5/charts/map-chart/map-polygon-series/
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


// Create series for background fill
// https://www.amcharts.com/docs/v5/charts/map-chart/map-polygon-series/#Background_polygon
var backgroundSeries = chart.series.push(am5map.MapPolygonSeries.new(root, {}));
backgroundSeries.mapPolygons.template.setAll({
  fill: am5.color(0xffffff),
  //stroke: am5.color(0xedf7fa),
fillOpacity: 1,
strokeOpacity: 0.5
});
backgroundSeries.data.push({

geometry: am5map.getGeoRectangle(90, 180, -90, -180)
});

var oceanSeries = chart.series.push(am5map.MapPolygonSeries.new(root, {
  geoJSON: am5geodata_worldLow,
  exclude: ["AQ"] // Exclure l'Antarctique
}));

// Couleur de la mer
oceanSeries.mapPolygons.template.setAll({
  fill: am5.color(0xcb043d), // Couleur pour la mer
  fillOpacity: 1,
});
// Set up events
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


// Create points
var pointSeries = chart.series.push(am5map.MapPointSeries.new(root, {
  latitudeField: "latitude",
  longitudeField: "longitude"
}));
pointSeries.data.setAll(drupalSettings.rctGlobe.data);

pointSeries.bullets.push(function() {
  // Create an image element
  var image = am5.Picture.new(root, {
    width: 30, // Adjust width as needed
    height: 30, // Adjust height as needed
    src: "/dist/assets/images/location.png", // Replace "url_to_your_image.png" with the URL of your image
    tooltipText: "{name}",
    centerX: am5.percent(50),
    centerY: am5.percent(70),
});

  // Add click event handler to the image
  image.events.on("click", function(ev) {
  var dataContext = ev.target.dataItem.dataContext;
  document.getElementById("contact-name").innerText = dataContext.name;
  document.getElementById("contact-address").innerText = dataContext.address;
  document.getElementById("contact-phone").innerText = dataContext.phone;
  document.getElementById("contact-email").innerText = dataContext.email;
});

  // Return the image as a bullet
  return am5.Bullet.new(root, {
    sprite: image
  });
});


chart.appear(1000, 100);

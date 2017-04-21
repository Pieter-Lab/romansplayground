// The Main Object
var LABMAP = {
    map: {
        obj: false,
        cnt: 'customgmap',
        cords: {
            startLat: 51.357541496257682,
            startLng: -0.811881675651667
        }
    },
    initMapObj: function () {
        // Create a map object and specify the DOM element for display.
        this.map.obj = new google.maps.Map(document.getElementById(this.map.cnt), {
            center: {lat: this.map.cords.startLat, lng: this.map.cords.startLng},
            scrollwheel: true,
            zoom: 11
        });
    },
    init: function(){
        //Inistantiate google map object
        this.initMapObj();
        //Get JSON Call
        $.getJSON( "search.php?postcode=SL", function( data ) {
            console.log(data);
            //Create polygon map feature
            var polydata = {
                "type": "FeatureCollection",
                "features": [
                    {
                        "type": "Feature",
                        "properties": {
                            "fillColor": "blue"
                        },
                        "geometry": data.polygon
                    }
                ]
            };
            //Add the data to the map
            LABMAP.map.obj.data.addGeoJson(polydata);
            //recenter the map
            LABMAP.map.obj.setCenter({lat: data.center.lat, lng: data.center.lng});
        });
    }
};
//When set to go
$(document).ready(function(){

});
/*
* console.log(this.geometricspoly);

 var innerCoords2 = [
 [
 {lat: 51.357541496258, lng: -0.81188167565167},
 {lat: 51.357541496258, lng: -0.83653688498112},
 {lat: 51.361813986496, lng: -0.82436718421104},
 {lat: 51.357938289967, lng: -0.82077530598321},
 {lat: 51.357541496258, lng: -0.81188167565167}
 ],
 [
 {lat: 51.357541496258, lng: -0.79675440517703},
 {lat: 51.357905863205, lng: -0.7946081627354},
 {lat: 51.359662598304, lng: -0.79056922066456},
 {lat: 51.361504173279, lng: -0.77137429085302},
 {lat: 51.361598336268, lng: -0.76835542552628},
 {lat: 51.36196888297, lng: -0.75452188214751},
 {lat: 51.357541496258, lng: -0.75452188214751},
 {lat: 51.357541496258, lng: -0.79675440517703}
 ]
 ];

 this.geometricspoly = [
 [
 [
 {lat: 51.357541496258, lng: -0.81188167565167},
 {lat: 51.357541496258, lng: -0.83653688498112},
 {lat: 51.361813986496, lng: -0.82436718421104},
 {lat: 51.357938289967, lng: -0.82077530598321},
 {lat: 51.357541496258, lng: -0.81188167565167}
 ]
 ],
 [
 [
 {lat: 51.357541496258, lng: -0.79675440517703},
 {lat: 51.357905863205, lng: -0.7946081627354},
 {lat: 51.359662598304, lng: -0.79056922066456},
 {lat: 51.361504173279, lng: -0.77137429085302},
 {lat: 51.361598336268, lng: -0.76835542552628},
 {lat: 51.36196888297, lng: -0.75452188214751},
 {lat: 51.357541496258, lng: -0.75452188214751},
 {lat: 51.357541496258, lng: -0.79675440517703}
 ]
 ]
 ];
 // this.map.obj.data.add({geometry: new google.maps.Data.Polygon(innerCoords2)})
 // this.map.obj.data.loadGeoJson('http://local.romansplayground.com/search.php');


 var paths = [
 [
 new google.maps.LatLng(51.357541496258,-0.81188167565167),
 new google.maps.LatLng(51.357541496258,-0.83653688498112),
 new google.maps.LatLng(51.361813986496,-0.82436718421104),
 new google.maps.LatLng(51.357938289967,-0.82077530598321),
 new google.maps.LatLng(51.357541496258,-0.81188167565167)
 ],
 [

 new google.maps.LatLng(51.357541496258,-0.79675440517703),
 new google.maps.LatLng(51.357905863205,-0.7946081627354),
 new google.maps.LatLng(51.359662598304,-0.79056922066456),
 new google.maps.LatLng(51.361504173279,-0.77137429085302),
 new google.maps.LatLng(51.361598336268,-0.76835542552628),
 new google.maps.LatLng(51.36196888297,-0.75452188214751),
 new google.maps.LatLng(51.357541496258,-0.75452188214751),
 new google.maps.LatLng(51.357541496258,-0.79675440517703)
 ]
 ];


 Poly14591 = new google.maps.Polygon({paths:paths,  strokeOpacity: 10.0, strokeWeight: 20, strokeColor: "#FF0000", fillColor: "#FF0000", fillOpacity: 0.35});

 // Poly14591.setMap(this.map.obj);

 // A MultiPolygon for German borders
 // see http://www.naturalearthdata.com/
 // var coordinates = [[[-0.84240225667756,51.543254937084],[-0.75452188214751,51.543254937084],[-0.75452188214751,51.465144938024],[-0.76829692133116,51.469745196883],[-0.77830523753598,51.472358632773],[-0.78891003175256,51.478592086625],[-0.79462017950908,51.487040406598],[-0.8035418924015,51.495053603475],[-0.80787061964816,51.500852462078],[-0.80826160317527,51.504093570235],[-0.81057149586078,51.507237115535],[-0.81014550693898,51.511423076205],[-0.80811377532204,51.515835440701],[-0.81077466183485,51.518254305238],[-0.81710669774976,51.520845025558],[-0.82448674322935,51.52643115288],[-0.84003041115956,51.541584217574],[-0.84240225667756,51.543254937084]]]; // incomplete
 //
 // var paths = _.map(coordinates, function(entry) {
 //     return _.reduce(entry, function(list, polygon) {
 //         // This map() only transforms the data.
 //         _.each(_.map(polygon, function(point) {
 //             // Important: the lat/lng are vice-versa in GeoJSON
 //             return new google.maps.LatLng(point[1], point[0]);
 //         }), function(point) {
 //             list.push(point);
 //         });
 //
 //         return list;
 //     }, []);
 // });
 //
 // console.log(paths);
 //
 // Poly14591 = new google.maps.Polygon({paths:paths,  strokeOpacity: 10.0, strokeWeight: 20, strokeColor: "#FF0000", fillColor: "#FF0000", fillOpacity: 0.35});
 //
 // Poly14591.setMap(this.map.obj);
* */
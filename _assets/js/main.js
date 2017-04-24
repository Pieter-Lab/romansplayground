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
    polyCaller: function (postCode){
        //Get JSON Call
        $.getJSON( "searchforpoly.php?postcode="+postCode, function( data ) {
            //remove other polys
            LABMAP.map.obj.data.forEach(function(feature) {
                //filter...
                LABMAP.map.obj.data.remove(feature);
            });
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
    },
    polyAction: function (val) {
        //Test
        if(val.length > 0){
            //Pass to poly function
            this.polyCaller(val);
        }
    },
    autoComp: {
        input: 'input#autoAreaLookup',
        resCnt : '#autoAreaLookupList',
        areaLookup: function (searchTerm) {
            //Get JSON Call
            $.getJSON( "sectorsearch.php?search="+searchTerm, function( data ) {
                //Test
                if(data.length > 0){
                    //Clean out container
                    $(""+LABMAP.autoComp.resCnt+"").html('');
                    //Set string holder
                    var HtmlString = '';
                    //set container
                    HtmlString += '<div class="list-group">';
                    //populate container
                    $(data).each(function(i,v){
                        //add as list
                        HtmlString += '<a areaitem="'+v+'" class="areaLookupItem list-group-item">'+v+'</a>';
                    });
                    //Close container
                    HtmlString += '</div>';
                    //Add to container
                    $(""+LABMAP.autoComp.resCnt+"").append(HtmlString);
                    //Show the autocomplete list
                    $(""+LABMAP.autoComp.resCnt+"").show();
                    //set function
                    $("a.areaLookupItem").on('click',function(){
                        //pass to poly action
                        LABMAP.polyAction($(this).attr('areaitem'));
                        //Stop default
                        return false;
                    })
                }
            });
        },
        init: function (){
            //Hide the autocomplete list
            $(""+LABMAP.autoComp.resCnt+"").hide();
            //Hook the text input auto lookup
            $(""+LABMAP.autoComp.input+"").on('keyup',function(){
                //Hide the autocomplete list
                $(""+LABMAP.autoComp.resCnt+"").hide();
                //Do the lookup
                LABMAP.autoComp.areaLookup($(this).val());
            });
        }
    },
    controls: {
        markers: [],
        createMarker: function (place) {
            //get place
            var placeLoc = place.geometry.location;
            //set the marker
            var marker = new google.maps.Marker({
                map: LABMAP.map.obj,
                position: place.geometry.location
            });
            //start info window
            infowindow = new google.maps.InfoWindow();
            //Add on click
            google.maps.event.addListener(marker, 'click', function() {
                //Set content
                infowindow.setContent(place.name);
                //set open call
                infowindow.open(LABMAP.map.obj, this);
            });
            //Push into markers container
            LABMAP.controls.markers.push(marker);
        },
        clearMarkers: function () {
            //Loop through all markers and clear them
            for (var i = 0; i < LABMAP.controls.markers.length; i++) {
                //Remove the marker from map
                LABMAP.controls.markers[i].setMap(null);
                //remove marker from array
                //LABMAP.controls.markers.splice([i]);
            }
        },
        center: function(controlDiv,callback,title,innerHtml){
            // Set CSS for the control border.
            var controlUI = document.createElement('div');
            controlUI.style.backgroundColor = '#fff';
            controlUI.style.border = '2px solid #fff';
            controlUI.style.borderRadius = '3px';
            controlUI.style.boxShadow = '0 2px 6px rgba(0,0,0,.3)';
            controlUI.style.cursor = 'pointer';
            controlUI.style.marginBottom = '22px';
            controlUI.style.textAlign = 'center';
            controlUI.title = title;
            controlDiv.appendChild(controlUI);
            // Set CSS for the control interior.
            var controlText = document.createElement('div');
            controlText.style.color = 'rgb(25,25,25)';
            controlText.style.fontFamily = 'Roboto,Arial,sans-serif';
            controlText.style.fontSize = '16px';
            controlText.style.lineHeight = '38px';
            controlText.style.paddingLeft = '5px';
            controlText.style.paddingRight = '5px';
            controlText.innerHTML = innerHtml;
            controlUI.appendChild(controlText);
            // Setup the click event listeners: simply set the map to Chicago.
            controlUI.addEventListener('click',callback);
        },
        init: function(){
            //Set the custom controls
            // Bus Stations ============================================================================================
                // Create the DIV to hold the control and call the CenterControl()
                // constructor passing in this DIV.
                var centerControlDiv = document.createElement('div');
                var centerControl = new LABMAP.controls.center(centerControlDiv,function(){
                    //Get center of the current position of map
                    var mapPostion = LABMAP.map.obj.getCenter();
                    //Create current postion object
                    var curPostion = new google.maps.LatLng(mapPostion.lat(),mapPostion.lng());
                    //Makes Places API request https://developers.google.com/maps/documentation/javascript/places#place_details
                    var request = {
                        location: curPostion,
                        radius: '500',
                        types: ['bus_station']
                    };
                    //Make service call
                    service = new google.maps.places.PlacesService(LABMAP.map.obj);
                    service.nearbySearch(request, function (results, status) {
                        //Clear existing markers
                        LABMAP.controls.clearMarkers();
                        //Add markers
                        if (status == google.maps.places.PlacesServiceStatus.OK) {
                            for (var i = 0; i < results.length; i++) {
                                var place = results[i];
                                LABMAP.controls.createMarker(results[i]);
                            }
                        }
                    });
                },'Bus and Train Stations','Transport');
                // pass control
                centerControlDiv.index = 1;
                LABMAP.map.obj.controls[google.maps.ControlPosition.BOTTOM_CENTER].push(centerControlDiv);
            // =========================================================================================================
            // Bus Stations ============================================================================================
                // Create the DIV to hold the control and call the CenterControl()
                // constructor passing in this DIV.
                var centerControlDiv = document.createElement('div');
                var centerControl = new LABMAP.controls.center(centerControlDiv,function(){
                    //Get center of the current position of map
                    var mapPostion = LABMAP.map.obj.getCenter();
                    //Create current postion object
                    var curPostion = new google.maps.LatLng(mapPostion.lat(),mapPostion.lng());
                    //Makes Places API request https://developers.google.com/maps/documentation/javascript/places#place_details
                    var request = {
                        location: curPostion,
                        radius: '500',
                        types: ['school','police','hospital']
                    };
                    //Make service call
                    service = new google.maps.places.PlacesService(LABMAP.map.obj);
                    service.nearbySearch(request, function (results, status) {
                        //Clear existing markers
                        LABMAP.controls.clearMarkers();
                        //Add markers
                        if (status == google.maps.places.PlacesServiceStatus.OK) {
                            for (var i = 0; i < results.length; i++) {
                                var place = results[i];
                                LABMAP.controls.createMarker(results[i]);
                            }
                        }
                    });
                },'Schools and Hospital','Schools');
                // pass control
                centerControlDiv.index = 1;
                LABMAP.map.obj.controls[google.maps.ControlPosition.BOTTOM_CENTER].push(centerControlDiv);
            // =========================================================================================================
        }
    },
    initMapObj: function () {
        // Create a map object and specify the DOM element for display.
        this.map.obj = new google.maps.Map(document.getElementById(this.map.cnt), {
            center: {lat: this.map.cords.startLat, lng: this.map.cords.startLng},
            scrollwheel: true,
            zoom: 13
        });
    },
    init: function(){
        //Inistantiate google map object
        this.initMapObj();
        //Start Autocomplete
        this.autoComp.init();
        //Setup the custom controls
        this.controls.init();

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
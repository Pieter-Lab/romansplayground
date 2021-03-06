// The Main Object
/**
 * Reference Links
 * https://developers.google.com/maps/documentation/javascript/examples/control-custom
 * https://developers.google.com/maps/documentation/javascript/controls#Adding_Controls_to_the_Map
 * https://developers.google.com/maps/documentation/javascript/examples/place-details
 * https://developers.google.com/maps/documentation/javascript/places#place_details
 * https://developers.google.com/maps/documentation/javascript/examples/place-search
 * https://developers.google.com/places/supported_types
 * http://map-icons.com/
 * @type {{map: {obj: boolean, cnt: string, cords: {startLat: number, startLng: number}}, polyCaller: LABMAP.polyCaller, polyAction: LABMAP.polyAction, autoComp: {input: string, resCnt: string, areaLookup: LABMAP.autoComp.areaLookup, init: LABMAP.autoComp.init}, controls: {markers: Array, createMarker: LABMAP.controls.createMarker, clearMarkers: LABMAP.controls.clearMarkers, create: LABMAP.controls.create, init: LABMAP.controls.init}, initMapObj: LABMAP.initMapObj, init: LABMAP.init}}
 */
var LABMAP = {
    map: {
        obj: false,
        cnt: 'customgmap',
        cords: {
            startLat: 51.357541496257682,
            startLng: -0.811881675651667
        },
        currentCords: {
            lat: null,
            lng: null,
        }
    },
    propertyMarkers: [],
    propertyCreateMarkers: function (data) {
        //Test data
        if(data){
            //Loop through all markers and clear them
            for (var i = 0; i < LABMAP.propertyMarkers.length; i++) {
                //Remove the marker from map
                LABMAP.propertyMarkers[i].setMap(null);
                //remove marker from array
                //LABMAP.controls.markers.splice([i]);
            }
            //Loop em
            $(data).each(function(i,v){
                //Test that the property has a location
                if(!isNaN(v.Latitude) && !isNaN(v.Longitude)){
                    //Create Map marker
                    var propMarker = new Marker({
                        title: v.HouseName,
                        map: LABMAP.map.obj,
                        position: new google.maps.LatLng(v.Latitude, v.Longitude),
                        icon: {
                            path: SQUARE_PIN,
                            fillColor: '#00CCBB',
                            fillOpacity: 1,
                            strokeColor: '',
                            strokeWeight: 0
                        },
                        map_icon_label: '<span class="map-icon 	map-icon-map-pin"></span>'
                    });
                    //start info window
                    infowindow = new google.maps.InfoWindow();
                    //Add on click
                    google.maps.event.addListener(propMarker, 'click', function() {
                        //create content string
                        var string  = '<div class="row">';
                                string += '<div class="col-md-12">';
                                //Set the info block
                                string += '<div class="row">';
                                    string += '<div class="col-md-12">';
                                        if(typeof v.HouseName !== 'undefined'){
                                            string += '<h3>'+v.HouseName+'</h3>';
                                        }
                                        string += '<h4>'+v.SalePriceString+'</h4>';
                                        string += '<p>'+v.Description+'</p>';
                                    string += '</div>';
                                string += '</div>';
                                //Set the images
                                if(typeof v.Image !== 'undefined'){
                                    if(v.Image.length > 0){
                                        var counter = 0;
                                        string += '<div class="row">';
                                        $(v.Image).each(function(index,value){
                                            if(counter == 3){
                                                string += '</div>';
                                                string += '<div class="row">';
                                            }
                                            string += '<div class="thumbnail col-md-4">';
                                            string += '<img class="img-responsive" src="'+value.Filepath+'" title="'+value.Caption+'" alt="'+value.Caption+'" style="max-width: 100%;" />';
                                            string += '</div>';
                                            counter++;
                                        });
                                        string += '</div>';
                                        console.log(counter);
                                    }
                                }
                                string += '</div>';
                            string += '</div>';
                        //Set content
                        infowindow.setContent(string);
                        //set open call
                        infowindow.open(LABMAP.map.obj, this);
                    });
                    //Push into markers container
                    LABMAP.propertyMarkers.push(propMarker);
                }
            });
        }
    },
    polyCaller: function (postCode){
        //Get JSON Call
        $.getJSON( "/searchforpoly.php?postcode="+postCode, function( data ) {
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
            //remember
            LABMAP.map.currentCords.lat = data.center.lat;
            LABMAP.map.currentCords.lng = data.center.lng;
            //recenter the map
            LABMAP.map.obj.setCenter({lat: data.center.lat, lng: data.center.lng});

            //Create Heat map
            if(typeof data.bound !== 'undefined'){

                console.log(data);

                LABMAP.controls.globalRadius = data.area;
                // $("#mapRadialSearch").val(LABMAP.controls.globalRadius);
                // $("#mapRadialSearchAmount").html(LABMAP.controls.globalRadius);

                // console.log(LABMAP.controls.globalRadius);
                // console.log(LABMAP.controls.globalRadius * 1609.34);

                var cityCircle = new google.maps.Circle({
                    strokeColor: '#FF0000',
                    strokeOpacity: 0.8,
                    strokeWeight: 2,
                    fillColor: '#FF0000',
                    fillOpacity: 0.35,
                    map: LABMAP.map.obj,
                    center: {lat: data.center.lat, lng: data.center.lng},
                    radius: LABMAP.controls.globalRadius
                });



            }
            //Test if we have properties for this
            if(typeof data.properties !== 'undefined'){
                LABMAP.propertyCreateMarkers(data.properties);
            }
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
            $.getJSON( "/sectorsearch.php?search="+searchTerm, function( data ) {
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
        globalRadius: '1200',
        markers: [],
        createMarker: function (place,iconClass) {
            //get place
            var placeLoc = place.geometry.location;
            //set the marker
            var marker = new Marker({
                title: place.name,
                map: LABMAP.map.obj,
                position: place.geometry.location,
                icon: {
                    path: SQUARE_ROUNDED,
                    fillColor: '#00CCBB',
                    fillOpacity: 1,
                    strokeColor: '',
                    strokeWeight: 0
                },
                map_icon_label: '<span class="map-icon '+iconClass+'"></span>'
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
        createButton: function(controlDiv,callback,title,innerHtml){
            // Set CSS for the control border.
            var controlUI = document.createElement('div');
            controlUI.style.backgroundColor = '#fff';
            controlUI.style.border = '2px solid #fff';
            controlUI.style.borderRadius = '3px';
            controlUI.style.boxShadow = '0 2px 6px rgba(0,0,0,.3)';
            controlUI.style.cursor = 'pointer';
            controlUI.style.marginBottom = '22px';
            controlUI.style.marginRight = '5px';
            controlUI.style.textAlign = 'center';
            controlUI.title = title;
            controlDiv.appendChild(controlUI);
            // Set CSS for the control interior.
            var controlText = document.createElement('div');
            controlText.style.color = 'rgb(25,25,25)';
            controlText.style.fontFamily = 'Roboto,Arial,sans-serif';
            controlText.style.fontSize = '16px';
            controlText.style.lineHeight = '29px';
            controlText.style.paddingLeft = '5px';
            controlText.style.paddingRight = '5px';
            controlText.innerHTML = innerHtml;
            controlUI.appendChild(controlText);
            // Setup the click event listeners: simply set the map to Chicago.
            controlUI.addEventListener('click',callback);
        },
        createControl: function (controlType,controlTitle,iconClass) {
            // Create the DIV to hold the control and call the CenterControl()
            // constructor passing in this DIV.
            var centerControlDiv = document.createElement('div');
            //Set the callback
            var centerControl = new LABMAP.controls.createButton(centerControlDiv,function(){
                //Get center of the current position of map
                if(LABMAP.map.currentCords.lat){
                    //Create current postion object
                    var curPostion = new google.maps.LatLng(LABMAP.map.currentCords.lat,LABMAP.map.currentCords.lng);
                }else{
                    var mapPostion = LABMAP.map.obj.getCenter();
                    //Create current postion object
                    var curPostion = new google.maps.LatLng(mapPostion.lat(),mapPostion.lng());
                }
                //Makes Places API request https://developers.google.com/maps/documentation/javascript/places#place_details
                var request = {
                    location: curPostion,
                    radius: LABMAP.controls.globalRadius,
                    types: [''+controlType+'']
                };
                //Make service call
                service = new google.maps.places.PlacesService(LABMAP.map.obj);
                service.nearbySearch(request, function (results, status) {
                    //Add markers
                    if (status == google.maps.places.PlacesServiceStatus.OK) {
                        for (var i = 0; i < results.length; i++) {
                            var place = results[i];
                            LABMAP.controls.createMarker(results[i],iconClass);
                        }
                    }
                });
            },controlTitle,'<span class="map-icon '+iconClass+' "></span>');
            // pass control
            centerControlDiv.index = 1;
            LABMAP.map.obj.controls[google.maps.ControlPosition.BOTTOM_CENTER].push(centerControlDiv);
        },
        init: function(){
            //Set the custom controls
            //Bus Stations ============================================================================================
            // LABMAP.controls.createControl('bus_station','Bus Stations','map-icon-bus-station');
            //Train Stations ==========================================================================================
            LABMAP.controls.createControl('train_station','Train Stations','map-icon-train-station');
            //Subway Stations =========================================================================================
            // LABMAP.controls.createControl('subway_station','Subway Stations','map-icon-subway-station');
            //Schools =================================================================================================
            LABMAP.controls.createControl('school','Schools','map-icon-school');
            //Police ==================================================================================================
            // LABMAP.controls.createControl('police','Police','map-icon-police');
            //Hospitals ===============================================================================================
            // LABMAP.controls.createControl('hospital','Hospitals','map-icon-hospital');
            // Clear ===================================================================================================
                // Create the DIV to hold the control and call the CenterControl()
                // constructor passing in this DIV.
                var centerControlDiv = document.createElement('div');
                var centerControl = new LABMAP.controls.createButton(centerControlDiv,function(){
                    //Clear markers
                    LABMAP.controls.clearMarkers();
                },'Clear Markers','X');
                // pass control
                centerControlDiv.index = 1;
                LABMAP.map.obj.controls[google.maps.ControlPosition.BOTTOM_CENTER].push(centerControlDiv);
            // =========================================================================================================
            // Radial Slider control ===================================================================================================
                //https://developers.google.com/maps/documentation/javascript/places#radar_search_requests
                // Create the DIV to hold the control and call the CenterControl()
                // constructor passing in this DIV.
                var centerControlDiv = document.createElement('div');
                var centerControl = new LABMAP.controls.createButton(centerControlDiv,function(){
                    //set the global radius
                    LABMAP.controls.globalRadius = $("#mapRadialSearch").val();
                    //TODO: Display Val converted to miles
                    $("#mapRadialSearchAmount").html(LABMAP.controls.globalRadius);

                    console.log(typeof LABMAP.controls.globalRadius);
                    console.log(LABMAP.controls.globalRadius);

                    var cityCircle = new google.maps.Circle({
                        strokeColor: '#FF0000',
                        strokeOpacity: 0.8,
                        strokeWeight: 2,
                        fillColor: '#FF0000',
                        fillOpacity: 0.35,
                        map: LABMAP.map.obj,
                        center: {lat: LABMAP.map.currentCords.lat, lng: LABMAP.map.currentCords.lng},
                        radius: parseInt(LABMAP.controls.globalRadius)
                    });

                },'Expand Search','<input id="mapRadialSearch" value="'+LABMAP.controls.globalRadius+'" type="range" min="10" max="50000" step="10" data-orientation="vertical" /><span id="mapRadialSearchAmount">'+LABMAP.controls.globalRadius+'</span>');
                // pass control
                centerControlDiv.index = 1;
                LABMAP.map.obj.controls[google.maps.ControlPosition.TOP_CENTER].push(centerControlDiv);
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
        //Load the Maps Icon helper, this is what gives us pretty map markers
        $.getScript('/_assets/maps-icons/js/map-icons.js', function()
        {
            //Setup the custom controls
            LABMAP.controls.init();
        });
    }
};
//When set to go
$(document).ready(function(){

});
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
            zoom: 8
        });
    },
    init: function(){
        //Inistantiate google map object
        this.initMapObj();


        var innerCoords2 = [
            {lat: -33.364, lng: 156.207},
            {lat: -34.364, lng: 156.207},
            {lat: -34.364, lng: 157.207},
            {lat: -33.364, lng: 157.207}
        ];




        this.map.obj.data.add({geometry: new google.maps.Data.Polygon([innerCoords2])})
    }
};
//When set to go
$(document).ready(function(){

});
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
    geometricspoly: [],
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

        // console.log(this.geometricspoly);
        //
        // var innerCoords2 = [
        //     {lat: 51.36196888297, lng: -0.75452188214751},
        //     {lat: 51.36196888297, lng: -0.75452188214751},
        //     {lat: 51.357541496258, lng: -0.83653688498112},
        //     {lat: 51.357541496258, lng: -0.83653688498112}
        // ];
        //
        // this.map.obj.data.add({geometry: new google.maps.Data.Polygon(this.geometricspoly)})

        var ctaLayer = new google.maps.KmlLayer({
            url: 'http://local.romansplayground.com/search.kml',
            map: this.map.obj
        });

    }
};
//When set to go
$(document).ready(function(){

});
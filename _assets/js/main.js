// The Main Object
var LABMAP = {
    map: {
        obj: false,
        cnt: 'customgmap'
    },
    init: function(){
        // Create a map object and specify the DOM element for display.
        this.map.obj = new google.maps.Map(document.getElementById(this.map.cnt), {
            center: {lat: 51.5057939, lng: -0.1259181},
            scrollwheel: true,
            zoom: 8
        });
    }
};
//When set to go
$(document).ready(function(){

});
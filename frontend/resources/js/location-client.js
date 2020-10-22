/**
 *      location client class
 */

const LocationClient = function() {
    this.position = null;
    this.options = {
        maximumAge: 5 * 60 * 1000,
	    timeout: 10 * 1000,
    };
    this.path = '/api/location-update';
};

// load location
LocationClient.prototype.load = function(success=null, error=console.log, options={}) {
    var that = this;
    var geoSuccess = function(position) {
        that.position = position;
        that.sendCoordsToAPI();
        if (typeof success === "function") success(position);
    };
    var geoError = function(err) {
        that.position = null;
        if (typeof error === "function") error(err);
    };
    var geoOptions = Object.assign(that.options, options);

    // check for Geolocation support
    if (navigator.geolocation) {
        console.log('Geolocation is supported!');
        navigator.geolocation.getCurrentPosition(geoSuccess, geoError, geoOptions);
    }
    else {
        console.log('Geolocation is not supported for this Browser/OS.');
    }
    
};

// send coordinates to api
LocationClient.prototype.sendCoordsToAPI = function(path=null) {
    var that = this;
    if (typeof path === "string") that.path = path;
    if (typeof api !== "undefined" && that.position && that.position.coords) {
        api.send({
            data : {
                latitude : that.position.coords.latitude,
                longitude : that.position.coords.longitude,
                api_token : api_token,
            },
            path : that.path,
        });
    }
};

var locationClient = new LocationClient();

window.onload = function() {
    locationClient.load();
};
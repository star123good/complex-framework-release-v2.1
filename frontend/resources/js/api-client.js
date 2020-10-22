
const ApiClient = function() {
    // base url
    this.baseUrl = BASE_URL;
    // type
    this.type = 'POST';
    // asynchronize
    this.async = true;
};

ApiClient.prototype.setBaseUrl = function(baseUrl) {
    this.baseUrl = baseUrl;
};

ApiClient.prototype.setType = function(type) {
    this.type = type;
};

ApiClient.prototype.setAsync = function(async) {
    this.async = async;
};

ApiClient.prototype.send = function(parameters) {
    const params = Object.assign({
        data : {},
        path : '',
        success : null,
        error : null,
        type : this.type,
    }, parameters);

    const url = this.baseUrl + params.path;

    // ajax send
    $.ajax({
        url: url,
        type: params.type,
        data: params.data,
        async: this.async,
        success: function(result){
            try {
                result = JSON.parse(result);
            }
            catch(e) {
                console.log("ajax result is not json.");
            }

            if(params.success !== undefined && typeof params.success === "function") params.success(result);
        },
        error: function(err) {
            if(params.error !== undefined && typeof params.error === "function") params.error(err);
            console.log("ajax failed.");
        }
    });
};


// api
var api = new ApiClient();
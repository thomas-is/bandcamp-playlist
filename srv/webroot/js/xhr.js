/**
*   HXR helpers
*/
function jget( url, onSuccess, onError = null ) {
    jxhr( null, url, onSuccess, onError, "GET", true);
}
function jpost( obj, url, onSuccess, onError = null ) {
    jxhr( obj, url, onSuccess, onError, "POST", true);
}
function jxhr( obj, url, onSuccess, onError, method, async ) {
    var xhr = new XMLHttpRequest();
    xhr.open(method, url, async);
    xhr.onreadystatechange = function() {
        if (this.readyState == 4) {
            if (this.status == 200) {
                if ( onSuccess ) { onSuccess(JSON.parse(this.responseText)); }
            } else {
                if ( onError ) { onError(this.status); }
            }
        }
    }
    obj ? xhr.send(JSON.stringify(obj)) : xhr.send();
}


